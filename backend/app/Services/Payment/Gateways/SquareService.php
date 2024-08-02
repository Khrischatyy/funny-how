<?php

namespace App\Services\Payment\Gateways;

use App\Interfaces\PaymentServiceInterface;
use App\Models\Address;
use App\Models\Booking;
use App\Models\Charge;
use App\Models\SquareToken;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Square\Models\CheckoutOptions;
use Square\Models\CreateLocationRequest;
use Square\Models\CreateOrderRequest;
use Square\Models\CreatePaymentLinkRequest;
use Square\Models\Order;
use Square\Models\OrderLineItem;
use Square\Models\QuickPay;
use Square\SquareClient;
use Square\Exceptions\ApiException;
use Square\Models\Money;

class SquareService implements PaymentServiceInterface
{
    protected $squareClient;

    public function __construct(SquareClient $squareClient)
    {
        $this->squareClient = $squareClient;
    }

    public function createPaymentSession(Booking $booking, int $amountOfMoney, User $studioOwner): array
    {
        $address = $booking->address;
        $applicationFeePercentage = 0.04; // 4% сервисный сбор
        $applicationFeeAmount = (int)($amountOfMoney * 100 * $applicationFeePercentage); // сумма в центах

        // Проверяем наличие location_id
        $squareLocation = $address->squareFirstLocation();
        if (!$squareLocation) {
            throw new \Exception('Address does not have a valid Square location.');
        }

        // Создаем order_id
        $orderId = $this->createOrder($squareLocation->location_id, $amountOfMoney);

        // Создаем объект Money для суммы заказа
        $priceMoney = new Money();
        $priceMoney->setAmount($amountOfMoney * 100); // сумма в центах
        $priceMoney->setCurrency('USD');

        // Создаем QuickPay объект
        $quickPay = new QuickPay(
            'Booking Payment for ' . $booking->id,
            $priceMoney,
            $squareLocation->location_id
        );

        // Установка комиссии приложения
        $appFeeMoney = new Money();
        $appFeeMoney->setAmount($applicationFeeAmount);
        $appFeeMoney->setCurrency('USD');

        // Создаем CheckoutOptions и устанавливаем redirect URL для обработки успешного платежа
        $checkoutOptions = new CheckoutOptions();
        $checkoutOptions->setRedirectUrl(route('payment.success', [
            'booking_id' => $booking->id,
            'order_id' => $orderId
        ]));
//        $checkoutOptions->setAppFeeMoney($appFeeMoney);

        // Создаем CreatePaymentLinkRequest и устанавливаем QuickPay и CheckoutOptions
        $createPaymentLinkRequest = new CreatePaymentLinkRequest();
        $createPaymentLinkRequest->setQuickPay($quickPay);
        $createPaymentLinkRequest->setCheckoutOptions($checkoutOptions);

        try {
            // Создаем клиент Square с явным указанием токена
            $squareToken = SquareToken::where('user_id', $studioOwner->id)->firstOrFail();
            $client = new SquareClient([
                'accessToken' => $squareToken->access_token,
                'environment' => env('SQUARE_ENVIRONMENT', 'sandbox')
            ]);

            $response = $client->getCheckoutApi()->createPaymentLink($createPaymentLinkRequest);

            if ($response->isError()) {
                Log::error('Square Checkout Error: ' . json_encode($response->getErrors()));
                throw new \Exception('Square Checkout Error');
            }

            $paymentLink = $response->getResult()->getPaymentLink();

            // Сохраняем order_id в базе данных
            $this->createCharge($booking, $orderId, $amountOfMoney, 'USD');

            return [
                'payment_url' => $paymentLink->getUrl(),
            ];

        } catch (ApiException $e) {
            Log::error('Square API Exception: ' . $e->getMessage());
            throw new \Exception('Square API Exception: ' . $e->getMessage());
        } catch (\Exception $e) {
            Log::error('General Exception: ' . $e->getMessage() . ' with request: ' . json_encode($createPaymentLinkRequest));
            throw new \Exception('General Exception: ' . $e->getMessage());
        }
    }

    public function createOrder(string $locationId, int $amountOfMoney): string
    {
        $money = new Money();
        $money->setAmount($amountOfMoney * 100); // сумма в центах
        $money->setCurrency('USD');

        $orderLineItem = new OrderLineItem('1'); // Количество 1
        $orderLineItem->setName('Booking Payment');
        $orderLineItem->setBasePriceMoney($money);

        $order = new Order($locationId); // передаем location_id как строку
        $order->setLineItems([$orderLineItem]);

        $createOrderRequest = new CreateOrderRequest();
        $createOrderRequest->setOrder($order);

        try {
            $squareToken = SquareToken::where('user_id', Auth::id())->firstOrFail();
            $client = new SquareClient([
                'accessToken' => $squareToken->access_token,
                'environment' => env('SQUARE_ENVIRONMENT', 'sandbox')
            ]);

            $response = $client->getOrdersApi()->createOrder($createOrderRequest);

            if ($response->isError()) {
                Log::error('Square Order Creation Error: ' . json_encode($response->getErrors()));
                throw new \Exception('Square Order Creation Error');
            }

            $orderId = $response->getResult()->getOrder()->getId();
            return $orderId;

        } catch (ApiException $e) {
            Log::error('Square API Exception: ' . $e->getMessage());
            throw new \Exception('Square API Exception: ' . $e->getMessage());
        }
    }


    public function refundPayment($booking)
    {
        try {
            $charge = Charge::where('booking_id', $booking->id)->firstOrFail();
            $refundBody = new \Square\Models\CreateRefundRequest(
                $charge->stripe_session_id, // Use the Square payment ID
                uniqid(), // idempotency key
                new Money($charge->amount * 100, 'USD')
            );

            $response = $this->squareClient->getRefundsApi()->createRefund($refundBody);

            if ($response->isError()) {
                Log::error('Square Refund Error: ' . json_encode($response->getErrors()));
                throw new \Exception('Square Refund Error');
            }

            $refund = $response->getResult()->getRefund();
            $charge->update([
                'refund_id' => $refund->getId(),
                'refund_status' => $refund->getStatus(),
            ]);

            // Update balance and booking status
            $this->updateBalance($booking->address_id, -$charge->amount);
            $this->updateBookingStatus($booking->id, 3);

            return [
                'success' => true,
                'code' => 200,
                'message' => 'Refund processed successfully and booking status updated.',
            ];

        } catch (ApiException $e) {
            Log::error('Square API Exception: ' . $e->getMessage());
            throw new \Exception('Square API Exception: ' . $e->getMessage());
        }
    }

    public function verifyPaymentSession($orderId)
    {
        try {
            $squareToken = SquareToken::where('user_id', Auth::id())->firstOrFail();
            $client = new SquareClient([
                'accessToken' => $squareToken->access_token,
                'environment' => env('SQUARE_ENVIRONMENT', 'sandbox')
            ]);

            $response = $client->getOrdersApi()->retrieveOrder($orderId);

            if ($response->isError()) {
                Log::error('Square Payment Verification Error: ' . json_encode($response->getErrors()));
                return null;
            }

            return $response->getResult()->getOrder();
        } catch (ApiException $e) {
            Log::error('Square API Exception: ' . $e->getMessage());
            return null;
        }
    }

    public function processPaymentSuccess($orderId, $bookingId)
    {
        $payment = $this->verifyPaymentSession($orderId);

        if (!$payment) {
            Log::error('Payment verification failed: Payment is null.');
            return [
                'success' => false,
                'code' => 400,
                'error' => 'Payment verification failed.',
            ];
        }

        $validationResult = $this->validatePayment($payment);
        if (!$validationResult['success']) {
            return $validationResult;
        }

        $booking = $this->updateBookingStatus($bookingId, 2);
        $this->updateCharge($orderId, $payment->getId());

        $totalAmount = $payment->getAmountMoney()->getAmount(); // Amount in cents
        $serviceFee = $totalAmount * PaymentService::SERVICE_FEE_PERCENTAGE;
        $amountToStudio = $totalAmount - $serviceFee;

        // Update balance
        $this->updateBalance($booking->address_id, $amountToStudio);

        // Dispatch jobs
        $userWhoBooksEmail = $booking->user->email;
        $studioOwner = $booking->address->company->adminCompany->user;
        dispatch(new BookingConfirmationJob($booking, $userWhoBooksEmail, $totalAmount));
        dispatch(new BookingConfirmationOwnerJob($booking, $studioOwner, $totalAmount));

        return [
            'success' => true,
            'code' => 200,
            'message' => 'Payment successful and booking status updated.',
        ];
    }

    protected function createCharge(Booking $booking, string $orderId, int $amount, string $currency): void
    {
        Charge::create([
            'booking_id' => $booking->id,
//            'square_payment_id' => $paymentLinkId, // Use the Square payment ID
            'amount' => $amount,
            'order_id' => $orderId,
            'currency' => $currency,
        ]);
    }

    protected function updateBalance($addressId, $amount): void
    {
        $address = Address::findOrFail($addressId);
        $address->available_balance += $amount; // Сохраняем в центах, увеличение или уменьшение баланса
        $address->save();
    }

    protected function updateBookingStatus(int $bookingId, int $statusId): Booking
    {
        $booking = Booking::findOrFail($bookingId);
        $booking->status_id = $statusId;
        $booking->save();

        return $booking;
    }

    protected function updateCharge(string $sessionId, string $paymentIntent): void
    {
        $charge = Charge::where('stripe_session_id', $sessionId)->firstOrFail();
        $charge->update([
            'stripe_payment_intent' => $paymentIntent, // Use the Square payment ID
        ]);
    }

    protected function validatePayment($payment): array
    {
        if ($payment->getStatus() !== 'COMPLETED') {
            Log::error('Payment not completed.');
            return [
                'success' => false,
                'code' => 400,
                'error' => 'Payment not completed.',
            ];
        }

        return ['success' => true];
    }

    public function createLocation(Request $request)
    {
        $address = Address::findOrFail($request->input('address_id'));

        $squareAddress = new SquareAddress();
        $squareAddress->setAddressLine1($address->street);
        $squareAddress->setLocality($address->city);
        $squareAddress->setPostalCode($address->postal_code);
        $squareAddress->setAdministrativeDistrictLevel1($address->state);
        $squareAddress->setCountry('US'); // Установите вашу страну

        $location = new \Square\Models\Location();
        $location->setName($address->name);
        $location->setAddress($squareAddress);

        $createLocationRequest = new CreateLocationRequest($location);

        try {
            $response = $this->squareClient->getLocationsApi()->createLocation($createLocationRequest);

            if ($response->isSuccess()) {
                $locationId = $response->getResult()->getLocation()->getId();
                $address->square_location_id = $locationId;
                $address->save();

                return redirect()->route('dashboard')->with('success', 'Square location connected successfully.');
            } else {
                return redirect()->back()->with('error', 'Failed to connect Square location.');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Square API Exception: ' . $e->getMessage());
        }
    }
}