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
    };
use App\Http\Controllers\VerifyEmailController;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\{AuthenticatedSessionController,
    EmailVerificationNotificationController,
    PasswordResetLinkController,
    RegisteredUserController};

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

// TODO переделать роуты в соответствии REST спецификацией

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
        Route::get('{address_id}/prices', [AddressController::class, 'getAddressPrices']);
        Route::post('{address_id}/prices', [AddressController::class, 'createOrUpdateAddressPrice']);
        Route::delete('/prices', [AddressController::class, 'deleteAddressPrices']);
        Route::get('/{address_id}/prices', [AddressController::class, 'getAddressPrices'])->where('address_id', '[0-9]+');

        //equipments
        Route::get('/{address_id}/equipment', [EquipmentController::class, 'getEquipmentsByAddressId'])->where('address_id', '[0-9]+');

        //booking routes
        Route::post('operating-hours', [OperatingHourController::class, 'setOperatingHours']);
        Route::post('reservation', [BookingController::class, 'bookAddress']);
        Route::post('payment-success', [BookingController::class, 'paymentSuccess']);
        Route::post('calculate-price', [BookingController::class, 'calculatePrice']);

        //reservation start, end time
        Route::withoutMiddleware('auth:sanctum')->group(function () {
            Route::get('reservation/start-time', [BookingController::class, 'getReservationAvailableStartTime']);
            Route::get('reservation/end-time', [BookingController::class, 'getReservationAvailableEndTime']);
        });
    });


    //settings/history
    Route::get('history', [BookingController::class, 'getBookingHistory']);
    Route::post('history/filter', [BookingController::class, 'filterBookingHistory']);

    Route::get('menu', [MenuController::class, 'getMenu']);


    Route::get('studios', [AddressController::class, 'getMyStudios'])->middleware('role:studio_owner');
    Route::get('booking-management', [BookingController::class, ''])->middleware('role:studio_owner');
//    Route::get('settings/profile', [RegisteredUserController::class, 'updateProfile']);

    Route::get('/company/{slug}', [CompanyController::class, 'getCompany']);
    Route::get('/operation-modes', [OperatingHourController::class, 'getOperationModes']);
    Route::post('/brand', [AddressController::class, 'createBrand']); // company + address created
});

//book address page
Route::prefix('countries')->group(function () {
    Route::get('/', [CountryController::class, 'getCountries']);
    Route::get('/{country_id}/cities', [CityController::class, 'getCitiesByCountryId'])->where('countryId', '[0-9]+');
});

Route::get('/companies/{city_id}', [CompanyController::class, 'getCompaniesByCityId'])->where('cityId', '[0-9]+');
Route::get('/city/{city_id}/studios', [AddressController::class, 'getAddressesInCity'])->where('cityId', '[0-9]+');


//subscription
//    Route::post('/subscribe', [SubscriptionController::class, 'subscribe']);
//    Route::get('register/company/{slug}', [CompanyController::class, 'getRegisterCompany'])->middleware('role:studio_owner');
