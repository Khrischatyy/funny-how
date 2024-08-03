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
use App\Services\Payment\PaymentService;
use Illuminate\Support\Facades\Log;
use Square\Models\CheckoutOptions;
use Square\Models\CreatePaymentLinkRequest;
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
        $checkoutOptions->setRedirectUrl(env('APP_URL') . "/payment-success?booking_id={$booking->id}");
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

//    public function refundPayment($booking, $studioOwner)
//    {
//        try {
//            $charge = Charge::where('booking_id', $booking->id)->firstOrFail();
//
//            $squareToken = SquareToken::where('user_id', $studioOwner->id)->firstOrFail();
//            $client = new SquareClient([
//                'accessToken' => $squareToken->access_token,
//                'environment' => env('SQUARE_ENVIRONMENT', 'sandbox')
//            ]);
//
//            // Retrieve the payment to check the available amount for refund
//            $paymentResponse = $client->getPaymentsApi()->getPayment($charge->square_payment_id);
//
//            if ($paymentResponse->isError()) {
//                throw new \Exception('Square Payment Retrieval Error: ' . json_encode($paymentResponse->getErrors()));
//            }
//
//            $payment = $paymentResponse->getResult()->getPayment();
//            $amountMoney = $payment->getAmountMoney();
//            $refundedMoney = $payment->getRefundedMoney();
//
//            if ($amountMoney === null || $amountMoney->getAmount() === null) {
//                throw new \Exception('Payment amount not found.');
//            }
//
//            $totalPaidAmount = $amountMoney->getAmount(); // Total amount paid in cents
//            $totalRefundedAmount = $refundedMoney ? $refundedMoney->getAmount() : 0; // Total amount refunded in cents
//
//            // Calculate the available amount to refund
//            $availableRefundAmount = $totalPaidAmount - $totalRefundedAmount;
//
//            // Calculate the refund amount
//            $refundAmount = $charge->amount * 100; // Convert to cents
//
//            // Debug information
//            Log::info('Total Paid Amount: ' . $totalPaidAmount);
//            Log::info('Total Refunded Amount: ' . $totalRefundedAmount);
//            Log::info('Available Refund Amount: ' . $availableRefundAmount);
//            Log::info('Refund Amount Requested: ' . $refundAmount);
//
//            // Check if the refund amount exceeds the available amount to refund
//            if ($refundAmount > $availableRefundAmount) {
//                throw new \Exception('Requested refund amount exceeds the available amount to refund.');
//            }
//
//            if ($availableRefundAmount <= 0) {
//                throw new \Exception('No available amount to refund.');
//            }
//
//            // Create the Money object for the refund amount
//            $refundMoney = new \Square\Models\Money();
//            $refundMoney->setAmount($refundAmount);
//            $refundMoney->setCurrency('USD');
//
//            // Create the refund request
//            $refundRequest = new \Square\Models\RefundPaymentRequest(
//                uniqid(), // Idempotency key
//                $refundMoney
//            );
//            $refundRequest->setPaymentId($charge->square_payment_id);
//
//            $response = $client->getRefundsApi()->refundPayment($refundRequest);
//
//            if ($response->isError()) {
//                throw new \Exception('Square Refund Error: ' . json_encode($response->getErrors()));
//            }
//
//            $refund = $response->getResult()->getRefund();
//            $charge->update([
//                'refund_id' => $refund->getId(),
//                'refund_status' => $refund->getStatus(),
//            ]);
//
//            // Update balance and booking status
//            $this->updateBalance($booking->address_id, -$charge->amount);
//            $this->updateBookingStatus($booking->id, 3);
//
//            return [
//                'success' => true,
//                'code' => 200,
//                'message' => 'Refund processed successfully and booking status updated.',
//            ];
//
//        } catch (ApiException $e) {
//            throw new \Exception('Square API Exception: ' . $e->getMessage());
//        } catch (\Exception $e) {
//            throw new \Exception('General Exception: ' . $e->getMessage());
//        }
//    }

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

            // Retrieve the payment associated with the first tender
            $tenders = $order->getTenders();
            if (!$tenders || count($tenders) == 0) {
                throw new \Exception('No tenders found for the order.');
            }

            $tender = $tenders[0];

            return $tender->getPaymentId();
        } catch (ApiException $e) {
            throw new \Exception('Square API Exception: ' . $e->getMessage());
        }
    }

    public function processPaymentSuccess($orderId, $bookingId, $studioOwner)
    {
        $paymentId = $this->verifyPaymentSession($orderId, $studioOwner);

        if (!$paymentId) {
            throw new \Exception('Payment verification failed: Payment is null.');
        }

        $validationResult = $this->validatePayment($paymentId, $studioOwner);
        if (!$validationResult['success']) {
            return $validationResult;
        }

        $booking = $this->updateBookingStatus($bookingId, 2);


        $this->updateCharge($orderId, $paymentId);


        $totalAmount = $this->getPaymentAmount($paymentId, $studioOwner); // Amount in cents
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

    public function refundPayment($booking, $studioOwner)
    {
        try {
            $charge = $this->getChargeForBooking($booking->id);
            $client = $this->createSquareClient($studioOwner->id);

            $payment = $this->retrievePayment($client, $charge->square_payment_id);
            $this->validateRefundAmount($payment, $charge->amount);

            $refund = $this->createRefund($client, $charge, $payment);
            $this->updateChargeAfterRefund($charge, $refund);
            $this->updateBalanceAndBookingStatus($booking, $charge->amount);

            return [
                'success' => true,
                'code' => 200,
                'message' => 'Refund processed successfully and booking status updated.',
            ];
        } catch (\Exception $e) {
            throw new \Exception('General Exception: ' . $e->getMessage());
        }
    }

    private function getChargeForBooking($bookingId)
    {
        return Charge::where('booking_id', $bookingId)->firstOrFail();
    }

    private function createSquareClient($userId)
    {
        $squareToken = SquareToken::where('user_id', $userId)->firstOrFail();
        return new SquareClient([
            'accessToken' => $squareToken->access_token,
            'environment' => env('SQUARE_ENVIRONMENT', 'sandbox')
        ]);
    }

    private function retrievePayment($client, $paymentId)
    {
        $paymentResponse = $client->getPaymentsApi()->getPayment($paymentId);

        if ($paymentResponse->isError()) {
            throw new \Exception('Square Payment Retrieval Error: ' . json_encode($paymentResponse->getErrors()));
        }

        return $paymentResponse->getResult()->getPayment();
    }

    private function validateRefundAmount($payment, $refundAmount)
    {
        $amountMoney = $payment->getAmountMoney();
        $refundedMoney = $payment->getRefundedMoney();

        if ($amountMoney === null || $amountMoney->getAmount() === null) {
            throw new \Exception('Payment amount not found.');
        }

        $totalPaidAmount = $amountMoney->getAmount(); // Total amount paid in cents
        $totalRefundedAmount = $refundedMoney ? $refundedMoney->getAmount() : 0; // Total amount refunded in cents

        $availableRefundAmount = $totalPaidAmount - $totalRefundedAmount;
        $refundAmountCents = $refundAmount * 100; // Convert to cents

        Log::info('Total Paid Amount: ' . $totalPaidAmount);
        Log::info('Total Refunded Amount: ' . $totalRefundedAmount);
        Log::info('Available Refund Amount: ' . $availableRefundAmount);
        Log::info('Refund Amount Requested: ' . $refundAmountCents);

        if ($refundAmountCents > $availableRefundAmount) {
            throw new \Exception('Requested refund amount exceeds the available amount to refund.');
        }

        if ($availableRefundAmount <= 0) {
            throw new \Exception('No available amount to refund.');
        }
    }

    private function createRefund($client, $charge, $payment)
    {
        $refundMoney = new \Square\Models\Money();
        $refundMoney->setAmount($charge->amount * 100); // Convert to cents
        $refundMoney->setCurrency('USD');

        $refundRequest = new \Square\Models\RefundPaymentRequest(
            uniqid(), // Idempotency key
            $refundMoney
        );
        $refundRequest->setPaymentId($charge->square_payment_id);

        $response = $client->getRefundsApi()->refundPayment($refundRequest);

        if ($response->isError()) {
            throw new \Exception('Square Refund Error: ' . json_encode($response->getErrors()));
        }

        return $response->getResult()->getRefund();
    }

    private function updateChargeAfterRefund($charge, $refund)
    {
        $charge->update([
            'refund_id' => $refund->getId(),
            'refund_status' => $refund->getStatus(),
        ]);
    }

    private function updateBalanceAndBookingStatus($booking, $amount)
    {
        $this->updateBalance($booking->address_id, -$amount);
        $this->updateBookingStatus($booking->id, 3);
    }

    protected function updateBookingStatus(int $bookingId, int $statusId): Booking
    {
        $booking = Booking::findOrFail($bookingId);
        $booking->status_id = $statusId;
        $booking->save();

        return $booking;
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

    protected function updateCharge(string $orderId, string $paymentId): void
    {
        $charge = Charge::where('order_id', $orderId)->firstOrFail();
        $charge->update([
            'square_payment_id' => $paymentId,
        ]);
    }

    protected function validatePayment($tenderId, $studioOwner): array
    {
        try {
            $squareToken = SquareToken::where('user_id', $studioOwner->id)->firstOrFail();
            $client = new SquareClient([
                'accessToken' => $squareToken->access_token,
                'environment' => env('SQUARE_ENVIRONMENT', 'sandbox')
            ]);

            // Retrieve the payment associated with the tender ID
            $paymentResponse = $client->getPaymentsApi()->getPayment($tenderId);
            if ($paymentResponse->isError()) {
                throw new \Exception('Square Payment Retrieval Error: ' . json_encode($paymentResponse->getErrors()));
            }

            $payment = $paymentResponse->getResult()->getPayment();
            if ($payment->getStatus() !== 'COMPLETED') {
                throw new \Exception('Payment not completed.');
            }

            return ['success' => true];
        } catch (ApiException $e) {
            throw new \Exception('Square API Exception: ' . $e->getMessage());
        }
    }

    protected function getPaymentAmount($paymentId, $studioOwner)
    {

        $squareToken = SquareToken::where('user_id', $studioOwner->id)->firstOrFail();
        $client = new SquareClient([
            'accessToken' => $squareToken->access_token,
            'environment' => env('SQUARE_ENVIRONMENT', 'sandbox')
        ]);

        try {
            $paymentResponse = $client->getPaymentsApi()->getPayment($paymentId);

            if ($paymentResponse->isError()) {
                throw new \Exception('Square Payment Retrieval Error: ' . json_encode($paymentResponse->getErrors()));
            }

            $payment = $paymentResponse->getResult()->getPayment();
            return $payment->getTotalMoney()->getAmount();
        } catch (ApiException $e) {
            throw new \Exception('Square API Exception: ' . $e->getMessage());
        }
    }
}