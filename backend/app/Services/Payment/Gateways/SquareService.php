<?php

namespace App\Services\Payment\Gateways;

use App\Interfaces\PaymentServiceInterface;
use App\Jobs\BookingConfirmationJob;
use App\Jobs\BookingConfirmationOwnerJob;
use App\Models\Address;
use App\Models\Booking;
use App\Models\Charge;
use App\Models\SquareLocation;
use App\Models\SquareToken;
use App\Models\User;
use App\Services\Payment\PaymentService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Square\Models\CheckoutOptions;
use Square\Models\CreatePaymentLinkRequest;
use Square\Models\ObtainTokenRequest;
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
        $address = $booking->room->address;
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

        $this->validatePayment($paymentId, $studioOwner);

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

    public function getSquareRedirectUrl()
    {
        $clientId = env('SQUARE_APPLICATION_ID');
        $redirectUri = env('APP_URL') . '/auth/square';
        $scope = 'MERCHANT_PROFILE_READ PAYMENTS_WRITE PAYMENTS_READ ORDERS_WRITE ORDERS_READ';
        $squareappBaseUrl = env('SQUARE_ENVIRONMENT') == 'production' ? 'https://connect.squareup.com' : 'https://connect.squareupsandbox.com';

        return "{$squareappBaseUrl}/oauth2/authorize?client_id={$clientId}&scope={$scope}&session=false&redirect_uri={$redirectUri}";
    }

    public function handleSquareCallback(Request $request)
    {
        $code = $request->input('code');
        $redirectUri = env('APP_URL') . '/auth/square';

        if (!$code) {
            throw new Exception('Authorization code not found.', 400);
        }

        $client = new SquareClient([
            'environment' => env('SQUARE_ENVIRONMENT', 'sandbox')
        ]);

        $body = new ObtainTokenRequest(
            env('SQUARE_APPLICATION_ID'),
            'authorization_code'
        );

        $body->setClientSecret(env('SQUARE_CLIENT_SECRET'));
        $body->setCode($code);
        $body->setRedirectUri($redirectUri);

        $apiResponse = $client->getOAuthApi()->obtainToken($body);

        if ($apiResponse->isError()) {
            throw new Exception('Failed to obtain token.', 400, ['errors' => $apiResponse->getErrors()]);
        }

        $result = $apiResponse->getResult();
        $user = Auth::user();

        $user->payment_gateway = 'square';
        $user->save();

        $squareLocationId = $this->getLocation($user, $result->getAccessToken());

        // Save tokens in the database
        SquareToken::updateOrCreate(
            ['user_id' => $user->id],
            [
                'access_token' => $result->getAccessToken(),
                'refresh_token' => $result->getRefreshToken(),
                'expires_at' => $result->getExpiresAt(),
                'square_location_id' => $squareLocationId, // Save the square_location_id
            ]
        );

        return $user;
    }

    protected function getLocation($user, $accessToken)
    {
        try {
            $client = new SquareClient([
                'accessToken' => $accessToken,
                'environment' => env('SQUARE_ENVIRONMENT', 'sandbox')
            ]);

            $apiResponse = $client->getLocationsApi()->retrieveLocation('main');

            if ($apiResponse->isError()) {
                throw new Exception('Failed to retrieve location.', 400, ['errors' => $apiResponse->getErrors()]);
            }

            $location = $apiResponse->getResult()->getLocation();

            if (!$location) {
                throw new Exception('Main location not found.', 404);
            }

            $addressId = $user->company->addresses()->first()->id;

            // Save location in the database
            $squareLocation = SquareLocation::updateOrCreate(
                ['address_id' => $addressId],
                ['location_id' => $location->getId()]
            );

            return $squareLocation->id; // Return the id of the SquareLocation
        } catch (Exception $e) {
            throw new Exception('Failed to retrieve location.', 500, ['error' => $e->getMessage()]);
        }
    }

    public function retrieveAccount(User $user)
    {
        try {
            $squareToken = SquareToken::where('user_id', $user->id)->with('location')->firstOrFail();

            if (!$squareToken->square_location_id) {
                throw new Exception('Square Location ID not found for the user.', 404);
            }

            $client = new SquareClient([
                'accessToken' => $squareToken->access_token,
                'environment' => env('SQUARE_ENVIRONMENT', 'sandbox')
            ]);

            $response = $client->getLocationsApi()->retrieveLocation($squareToken->location->location_id);

            if ($response->isError()) {
                throw new Exception('Square location retrieval error: ' . json_encode($response->getErrors()), 500);
            }

            $location = $response->getResult()->getLocation();

            return $location;
        } catch (ApiException $e) {
            throw new Exception('Square API Exception: ' . $e->getMessage(), 500);
        } catch (Exception $e) {
            throw new Exception('Failed to retrieve Square location: ' . $e->getMessage(), 500);
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