<?php

namespace App\Services\Payment\Gateways;

use App\Interfaces\PaymentServiceInterface;
use App\Jobs\BookingConfirmationJob;
use App\Jobs\BookingConfirmationOwnerJob;
use App\Models\Address;
use App\Models\Booking;
use App\Models\Charge;
use App\Models\SquareToken;
use App\Models\User;
use App\Services\BookingService;
use App\Services\Payment\PaymentService;
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
        $checkoutOptions->setRedirectUrl(env('APP_URL') . "/api/v1/address/payment-success?booking_id={$booking->id}");
        $checkoutOptions->setAppFeeMoney($appFeeMoney);

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
                throw new \Exception('Square Checkout Error: ' . json_encode($response->getErrors()));
            }

            $paymentLink = $response->getResult()->getPaymentLink();
            $orderId = $paymentLink->getOrderId();

            // Сохраняем order_id в базе данных
            $this->createCharge($booking, $orderId, $amountOfMoney, 'USD');

            return [
                'payment_url' => $paymentLink->getUrl(),
            ];

        } catch (ApiException $e) {
            throw new \Exception('Square API Exception: ' . $e->getMessage());
        } catch (\Exception $e) {
            throw new \Exception('General Exception: ' . $e->getMessage() . ' with request: ' . json_encode($createPaymentLinkRequest));
        }
    }

//    public function createOrder(Address $address, int $amountOfMoney): string
//    {
//        $money = new Money();
//        $money->setAmount($amountOfMoney * 100); // сумма в центах
//        $money->setCurrency('USD');
//
//        $orderLineItem = new OrderLineItem('1'); // Количество 1
//        $orderLineItem->setName('Booking Payment');
//        $orderLineItem->setBasePriceMoney($money);
//
//        $order = new Order($address->squareFirstLocation()->location_id); // Get the location_id from the first SquareLocation
//        $order->setLineItems([$orderLineItem]);
//
//        $createOrderRequest = new CreateOrderRequest();
//        $createOrderRequest->setOrder($order);
//
//        try {
//            $squareToken = SquareToken::where('user_id', Auth::id())->firstOrFail();
//            $client = new SquareClient([
//                'accessToken' => $squareToken->access_token,
//                'environment' => env('SQUARE_ENVIRONMENT', 'sandbox')
//            ]);
//
//            $response = $client->getOrdersApi()->createOrder($createOrderRequest);
//
//            if ($response->isError()) {
//                Log::error('Square Order Creation Error: ' . json_encode($response->getErrors()));
//                throw new \Exception('Square Order Creation Error');
//            }
//
//            $orderId = $response->getResult()->getOrder()->getId();
//            return $orderId;
//
//        } catch (ApiException $e) {
//            Log::error('Square API Exception: ' . $e->getMessage());
//            throw new \Exception('Square API Exception: ' . $e->getMessage());
//        }
//    }



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

    public function verifyPaymentSession($orderId, $studioOwner)
    {
        try {
            $squareToken = SquareToken::where('user_id', $studioOwner->id)->firstOrFail();
            $client = new SquareClient([
                'accessToken' => $squareToken->access_token,
                'environment' => env('SQUARE_ENVIRONMENT', 'sandbox')
            ]);

            // Retrieve the order
            $orderResponse = $client->getOrdersApi()->retrieveOrder($orderId);

            if ($orderResponse->isError()) {
                throw new \Exception('Square Order Retrieval Error: ' . json_encode($orderResponse->getErrors()));
            }

            $order = $orderResponse->getResult()->getOrder();
            $orderState = $order->getState();

            if ($orderState !== 'COMPLETED') {
                throw new \Exception('Order state not completed.');
            }

            // Retrieve the payment associated with the first tender
            $tenders = $order->getTenders();
            if (!$tenders || count($tenders) == 0) {
                throw new \Exception('No tenders found for the order.');
            }

            $tenderId = $tenders[0]->getId();
            $paymentResponse = $client->getPaymentsApi()->getPayment($tenderId);
            if ($paymentResponse->isError()) {
                throw new \Exception('Square Payment Retrieval Error: ' . json_encode($paymentResponse->getErrors()));
            }

            return $paymentResponse->getResult()->getPayment();
        } catch (ApiException $e) {
            throw new \Exception('Square API Exception: ' . $e->getMessage());
        }
    }

    public function processPaymentSuccess($orderId, $bookingId, $studioOwner)
    {
        $payment = $this->verifyPaymentSession($orderId, $studioOwner);

        if (!$payment) {
            throw new \Exception('Payment verification failed: Payment is null.');
        }

        $validationResult = $this->validatePayment($payment);
        if (!$validationResult['success']) {
            return $validationResult;
        }

        $booking = $this->bookingService->updateBookingStatus($bookingId, 2);
        $this->updateCharge($orderId, $payment->getId());

        $totalAmount = $payment->getTotalMoney()->getAmount(); // Amount in cents
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

    protected function updateCharge(string $orderId, string $paymentId): void
    {
        $charge = Charge::where('order_id', $orderId)->firstOrFail();
        $charge->update([
            'square_payment_id' => $paymentId,
        ]);
    }

    protected function validatePayment($payment): array
    {
        if ($payment->getStatus() !== 'COMPLETED') {
            throw new \Exception('Payment not completed.');
        }

        return ['success' => true];
    }

//    public function createLocation(Request $request)
//    {
//        $address = Address::findOrFail($request->input('address_id'));
//
//        $squareAddress = new SquareAddress();
//        $squareAddress->setAddressLine1($address->street);
//        $squareAddress->setLocality($address->city);
//        $squareAddress->setPostalCode($address->postal_code);
//        $squareAddress->setAdministrativeDistrictLevel1($address->state);
//        $squareAddress->setCountry('US'); // Установите вашу страну
//
//        $location = new \Square\Models\Location();
//        $location->setName($address->name);
//        $location->setAddress($squareAddress);
//
//        $createLocationRequest = new CreateLocationRequest($location);
//
//        try {
//            $response = $this->squareClient->getLocationsApi()->createLocation($createLocationRequest);
//
//            if ($response->isSuccess()) {
//                $locationId = $response->getResult()->getLocation()->getId();
//                $address->square_location_id = $locationId;
//                $address->save();
//
//                return redirect()->route('dashboard')->with('success', 'Square location connected successfully.');
//            } else {
//                return redirect()->back()->with('error', 'Failed to connect Square location.');
//            }
//        } catch (\Exception $e) {
//            return redirect()->back()->with('error', 'Square API Exception: ' . $e->getMessage());
//        }
//    }
}