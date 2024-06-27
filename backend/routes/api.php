<?php


use App\Http\Controllers\API\{AddressController,
    BadgeController,
    BookingController,
    CityController,
    CompanyController,
    CountryController,
    EquipmentController,
    MenuController,
    OperatingHourController,
    UserController};
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\VerifyEmailController;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\{AuthenticatedSessionController,
    EmailVerificationNotificationController,
    PasswordResetLinkController,
    RegisteredUserController};
use Laravel\Socialite\Facades\Socialite;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('auth')->group(function () {

        // Retrieve the verification limiter configuration for verification attempts
        $verificationLimiter = config('fortify.limiters.verification', '6,1');

        Route::withoutMiddleware('auth:sanctum')->group(function () {
            // Retrieve the limiter configuration for login attempts
            $limiter = config('fortify.limiters.login');

            Route::post('/login', [AuthenticatedSessionController::class, 'store'])
                ->middleware(array_filter([
                    $limiter ? 'throttle:' . $limiter : null,  // Throttle login attempts if limiter is configured
                ]));

            Route::post('/register', [RegisteredUserController::class, 'store'])
                ->middleware('guest:' . config('fortify.guard'));  // Only guests (non-authenticated users) are allowed

            Route::post('/email/verify/{id}/{hash}', [VerifyEmailController::class, 'verify']);
//                ->middleware('guest:' . config('fortify.guard'));  // Only guests (non-authenticated users) are allowed

            Route::post('/forgot-password', [PasswordResetLinkController::class, 'store']);
//                ->middleware('guest:'.config('fortify.guard'));  // Only guests (non-authenticated users) are allowed
//                ->name('password.email');
        });

        // Route to resend email verification notification
        Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
            ->middleware([
                'throttle:' . $verificationLimiter // Throttle resend email attempts
            ]);

//        Route::post('/logout', [LogoutController::class, 'destroy']);
    });
    Route::prefix('address')->group(function () {

        //badges routes
        Route::get('{address_id}/badges', [BadgeController::class, 'getAddressBadges']);
        Route::post('{address_id}/badge', [BadgeController::class, 'setAddressBadge']);

        //prices
        Route::post('{address_id}/prices', [AddressController::class, 'createOrUpdateAddressPrice']);
        Route::delete('prices', [AddressController::class, 'deleteAddressPrices']);
        Route::get('{address_id}/prices', [AddressController::class, 'getAddressPrices'])->where('address_id', '[0-9]+');

        //booking routes
        Route::post('operating-hours', [OperatingHourController::class, 'setOperatingHours']);
        Route::post('reservation', [BookingController::class, 'bookAddress']);
        Route::post('cancel-booking', [BookingController::class, 'cancelBooking']);
        Route::post('payment-success', [BookingController::class, 'paymentSuccess']);
        Route::post('calculate-price', [BookingController::class, 'calculatePrice']);


        Route::delete('{address_id}/equipment', [EquipmentController::class, 'deleteEquipment'])->where('address_id', '[0-9]+');
        Route::post('{address_id}/equipment', [EquipmentController::class, 'setEquipment'])->where('address_id', '[0-9]+');

        Route::withoutMiddleware('auth:sanctum')->group(function () {
            //reservation start, end time
            Route::get('reservation/start-time', [BookingController::class, 'getReservationAvailableStartTime']);
            Route::get('reservation/end-time', [BookingController::class, 'getReservationAvailableEndTime']);
            //get address
            Route::get('/equipment-type', [EquipmentController::class, 'getEquipmentType']);
            Route::get('{address_id}', [AddressController::class, 'getAddressById']);



            Route::get('{address_id}/equipment', [EquipmentController::class, 'getEquipmentsByAddressId'])->where('address_id', '[0-9]+');
        });

    });
    Route::prefix('photos')->group(function () {
        Route::post('/upload', [AddressController::class, 'uploadAddressPhotos']);
        Route::post('/update-index', [AddressController::class, 'updatePhotoIndex']);
    });

    Route::get('history', [BookingController::class, 'getBookings'])->defaults('type', 'history');
    Route::post('history/filter', [BookingController::class, 'filterBookings'])->defaults('type', 'history');

    Route::get('booking-management', [BookingController::class, 'getBookings'])->defaults('type', 'future');
    Route::post('booking-management/filter', [BookingController::class, 'filterBookings'])->defaults('type', 'future');

    Route::get('menu', [MenuController::class, 'getMenu']);

    Route::get('company/{slug}', [CompanyController::class, 'getCompany']);
    Route::get('map/studios', [AddressController::class, '']);


    Route::get('operation-modes', [OperatingHourController::class, 'getOperationModes']);
    Route::post('brand', [AddressController::class, 'createBrand']); // company + address created

    Route::get('my-studios', [AddressController::class, 'getMyAddresses']);
    Route::get('{slug}/studios', [AddressController::class, 'getAddressesByCompanySlug']);
    Route::post('set-role', [UserController::class, 'setRole']);

    Route::get('/me', [UserController::class, 'getMe']);

});

Route::prefix('countries')->group(function () {
    Route::get('/', [CountryController::class, 'getCountries']);
    Route::get('{country_id}/cities', [CityController::class, 'getCitiesByCountryId'])->where('countryId', '[0-9]+');
});

Route::middleware(['web'])->group(function () {
    Route::prefix('/auth/google')->group(function () {
        Route::get('redirect', [GoogleController::class, 'redirectToProvider']);
        Route::get('callback', [GoogleController::class, 'handleGoogleCallback']);
    });
});





Route::get('city/{city_id}/studios', [AddressController::class, 'getAddressesInCity'])->where('cityId', '[0-9]+');