<?php

use App\Http\Controllers\API\AddressController;
use App\Http\Controllers\API\BookingController;
use App\Http\Controllers\API\CityController;
use App\Http\Controllers\API\CompanyController;
use App\Http\Controllers\API\CountryController;
use App\Http\Controllers\API\EquipmentController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Laravel\Fortify\Http\Controllers\EmailVerificationNotificationController;
use Laravel\Fortify\Http\Controllers\PasswordResetLinkController;
use Laravel\Fortify\Http\Controllers\RegisteredUserController;

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

            // Route for user login
            Route::post('/login', [AuthenticatedSessionController::class, 'store'])
                ->middleware(array_filter([
                    'guest:' . config('fortify.guard'),  // Only guests (non-authenticated users) are allowed
                    $limiter ? 'throttle:' . $limiter : null,  // Throttle login attempts if limiter is configured
                ]));

            // Route for user registration
            Route::post('/register', [RegisteredUserController::class, 'store'])
                ->middleware('guest:' . config('fortify.guard'));  // Only guests (non-authenticated users) are allowed

            // Route for initiating password reset
//            Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
//                ->middleware('guest:' . config('fortify.guard'))  // Only guests (non-authenticated users) are allowed
//                ->name('password.email');  // Name for the route
        });

        // Route to resend email verification notification
        Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
            ->middleware([
                'throttle:' . $verificationLimiter // Throttle resend email attempts
            ]);

//        Route::post('/logout', [LogoutController::class, 'destroy']);
    });

});


Route::get('/countries', [CountryController::class, 'getCountries']);
Route::get('/cities/{countryId}', [CityController::class, 'getCitiesByCountryId'])->where('countryId', '[0-9]+');
Route::get('/companies/{cityId}', [CompanyController::class, 'getCompaniesByCityId'])->where('cityId', '[0-9]+');
Route::get('/address/{addressId}', [AddressController::class, 'getAddressByCompanyId'])->where('addressId', '[0-9]+');
Route::get('/addresses/{cityId}', [AddressController::class, 'getAddressByCityId'])->where('cityId', '[0-9]+');
Route::get('/equipment/{addressId}', [EquipmentController::class, 'getEquipmentsByAddressId'])->where('addressId', '[0-9]+');
Route::get('/city/{cityId}/company/{companyId}', [CompanyController::class, 'getCompanyAddressesInCity'])->where('cityId', '[0-9]+');

//Route::post('/booking', [BookingController::class, 'getBookingByAddressId']);
Route::post('/book', [BookingController::class, 'book']);

Route::get('/{slug}', [CompanyController::class, 'getCompany']);
