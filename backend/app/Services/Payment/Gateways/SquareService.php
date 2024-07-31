<?php

namespace App\Services\Payment\Gateways;

use App\Interfaces\PaymentServiceInterface;
use App\Models\Address;
use App\Models\Booking;
use App\Models\Charge;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Square\Models\CheckoutOptions;
use Square\Models\CreateLocationRequest;
use Square\Models\CreateOrderRequest;
use Square\Models\CreatePaymentLinkRequest;
use Square\Models\Location;
use Square\Models\Order;
use Square\Models\OrderLineItem;
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

        $money = new Money();
        $money->setAmount($amountOfMoney * 100); // сумма в центах
        $money->setCurrency('USD');

        $orderLineItem = new OrderLineItem('1'); // Количество 1
        $orderLineItem->setName('Booking Payment');
        $orderLineItem->setBasePriceMoney($money);

        $order = new Order($address->square_location_id);
        $order->setLineItems([$orderLineItem]);

        $createOrderRequest = new CreateOrderRequest();
        $createOrderRequest->setOrder($order);

        $checkoutOptions = new CheckoutOptions();
        // Устанавливаем redirect URL для обработки успешного платежа
        $checkoutOptions->setRedirectUrl(route('payment.success', ['booking_id' => $booking->id]));

        // Установка комиссии приложения
        $checkoutOptions->setApplicationFeeMoney((new Money())->setAmount($applicationFeeAmount)->setCurrency('USD'));

        $createPaymentLinkRequest = new CreatePaymentLinkRequest($createOrderRequest);
        $createPaymentLinkRequest->setCheckoutOptions($checkoutOptions);

        try {
            $response = $this->squareClient->getCheckoutApi()->createPaymentLink($createPaymentLinkRequest);

            if ($response->isError()) {
                Log::error('Square Checkout Error: ' . json_encode($response->getErrors()));
                throw new \Exception('Square Checkout Error');
            }

            $paymentLink = $response->getResult()->getPaymentLink();
            $this->createCharge($booking, $paymentLink->getId(), $amountOfMoney, 'USD');

            return [
                'payment_url' => $paymentLink->getUrl(),
            ];

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

    public function verifyPaymentSession($sessionId)
    {
        try {
            $response = $this->squareClient->getPaymentsApi()->getPayment($sessionId);

            if ($response->isError()) {
                Log::error('Square Payment Verification Error: ' . json_encode($response->getErrors()));
                return null;
            }

            return $response->getResult()->getPayment();
        } catch (ApiException $e) {
            Log::error('Square API Exception: ' . $e->getMessage());
            return null;
        }
    }

    public function processPaymentSuccess($sessionId, $bookingId)
    {
        $payment = $this->verifyPaymentSession($sessionId);

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
        $this->updateCharge($sessionId, $payment->getId());

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

    protected function createCharge(Booking $booking, string $sessionId, int $amount, string $currency): void
    {
        Charge::create([
            'booking_id' => $booking->id,
            'stripe_session_id' => $sessionId, // Use the Square payment ID
            'amount' => $amount,
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

    public function createLocation(Address $address): array
    {
        try {
            $location = new Location();
            $location->setName($address->slug); // Использование slug как имени локации
            $location->setAddressLine1($address->street);
            $location->setLocality($address->city->name);
            $location->setAdministrativeDistrictLevel1($address->city->state->name);
            $location->setPostalCode($address->city->postal_code);
            $location->setTimezone($address->timezone);
//            $location->setDescription($address->description); // Предполагается, что у вас есть поле description
            $location->setCapabilities(['CREDIT_CARD_PROCESSING']);

            $request = new CreateLocationRequest();
            $request->setLocation($location);

            $response = $this->squareClient->getLocationsApi()->createLocation($request);

            if ($response->isError()) {
                Log::error('Square Location Error: ' . json_encode($response->getErrors()));
                throw new \Exception('Square Location Error');
            }

            $location = $response->getResult()->getLocation();
            $address->square_location_id = $location->getId();
            $address->square_capabilities = $location->getCapabilities();
            $address->save();

            return [
                'success' => true,
                'location_id' => $location->getId(),
            ];
        } catch (ApiException $e) {
            Log::error('Square API Exception: ' . $e->getMessage());
            throw new \Exception('Square API Exception: ' . $e->getMessage());
        }
    }
}