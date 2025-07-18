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
    PaymentController,
    PayoutController,
    RoomController,
    SquareController,
    TeamController,
    StripeController,
    UserController,
    MessageController};
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\VerifyEmailController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\{AuthenticatedSessionController,
    EmailVerificationNotificationController,
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

Route::middleware(['auth:sanctum'])->group(function () {

    Route::prefix('auth')->group(function () {

        // Retrieve the verification limiter configuration for verification attempts
        $verificationLimiter = config('fortify.limiters.verification', '6,1');

        Route::withoutMiddleware('auth:sanctum')->group(function () {
            // Retrieve the limiter configuration for login attempts
            $limiter = config('fortify.limiters.login');

            Route::post('/login', [AuthenticatedSessionController::class, 'store']);

            Route::post('/register', [RegisteredUserController::class, 'store'])
                ->middleware('guest:' . config('fortify.guard'));  // Only guests (non-authenticated users) are allowed

            Route::post('/email/verify/{id}/{hash}', [VerifyEmailController::class, 'verify']);
//                ->middleware('guest:' . config('fortify.guard'));  // Only guests (non-authenticated users) are allowed

            Route::post('/forgot-password', [UserController::class, 'forgotPassword']);
            Route::post('/reset-password', [UserController::class, 'resetPassword']);
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

    //team
    Route::prefix('team')->group(function () {
        Route::prefix('member')->group(function () {
            Route::post('', [TeamController::class, 'addMember']);
            Route::get('', [TeamController::class, 'listMembers']);
            Route::delete('', [TeamController::class, 'removeMember']);
        });

        Route::get('email/check', [TeamController::class, 'checkMemberEmail']);
    });


    Route::prefix('address')->group(function () {



        Route::get('list', [AddressController::class, 'listAddresses']);

        //badges routes
        Route::get('{address_id}/badges', [BadgeController::class, 'getAddressBadges']);
        Route::post('{address_id}/badge', [BadgeController::class, 'setAddressBadge']);

        Route::post('add-studio', [AddressController::class, 'createAddress']);
        Route::post('delete-studio', [AddressController::class, 'deleteAddress']);

        Route::post('toggle-favorite-studio', [AddressController::class, 'toggleFavorite']);

        //prices
        Route::post('clients', [UserController::class, 'getClients']);

        //booking routes
        Route::post('operating-hours', [OperatingHourController::class, 'setOperatingHours']);
        Route::get('operating-hours', [OperatingHourController::class, 'getOperatingHours']);

        Route::put('/{address_slug}/update-slug', [AddressController::class, 'updateSlug']);


        Route::delete('{address_id}/equipment', [EquipmentController::class, 'deleteEquipment'])->where('address_id', '[0-9]+');
        Route::post('{address_id}/equipment', [EquipmentController::class, 'setEquipment'])->where('address_id', '[0-9]+');

        Route::withoutMiddleware('auth:sanctum')->group(function () {
            Route::post('payment-success', [BookingController::class, 'paymentSuccess'])->name('payment.success');
            Route::post('calculate-price', [BookingController::class, 'calculatePrice']);
            //reservation start, end time
            Route::get('reservation/start-time', [BookingController::class, 'getReservationAvailableStartTime']);
            Route::get('reservation/end-time', [BookingController::class, 'getReservationAvailableEndTime']);
            //get address
            Route::get('/equipment-type', [EquipmentController::class, 'getEquipmentType']);
            Route::get('/studio/{address_slug}', [AddressController::class, 'getAddressBySlug']);




            Route::get('{address_id}/equipment', [EquipmentController::class, 'getEquipmentsByAddressId'])->where('address_id', '[0-9]+');
        });
    });

    Route::prefix('room')->group(function () {

        Route::post('add-room', [RoomController::class, 'createRoom']);

        //prices
        Route::post('{room_id}/prices', [RoomController::class, 'createOrUpdatePrice']);
        Route::delete('prices', [RoomController::class, 'deletePrices']);
        Route::get('{room_id}/prices', [RoomController::class, 'getPrices'])->where('room_id', '[0-9]+');

        //booking routes
        Route::post('reservation', [BookingController::class, 'bookAddress']);
        Route::post('cancel-booking', [BookingController::class, 'cancelBooking']);

        Route::put('/{room}/update-name', [RoomController::class, 'updateName']);
    });

    Route::prefix('photos')->group(function () {
        Route::post('/upload', [RoomController::class, 'uploadPhotos']);
        Route::post('/update-index', [RoomController::class, 'updatePhotoIndex']);
    });


    Route::prefix('user')->group(function () {
        Route::post('set-role', [UserController::class, 'setRole']);
        Route::get('me', [UserController::class, 'getMe']);
        Route::put('update', [UserController::class, 'updateUser']);
        Route::post('update-photo', [UserController::class, 'updatePhoto']);
        Route::get('available-balance', [PayoutController::class, 'getAvailableBalance']);

        Route::prefix('payment')->group(function () {

            Route::prefix('stripe')->group(function () {
                Route::post('create-account', [StripeController::class, 'createAccount']);
                Route::get('refresh', [StripeController::class, 'refreshAccountLink']);
                Route::get('balance', [StripeController::class, 'retrieveBalance']);
            });

            Route::prefix('square')->group(function () {
                //done
                Route::get('redirect', [SquareController::class, 'redirectToSquare']);
                //done
                Route::post('callback', [SquareController::class, 'handleSquareCallback']);
            });

            Route::prefix('account')->group(function () {
                //pending
                Route::get('retrieve', [PaymentController::class, 'retrieveAccount']);
            });
        });

        Route::post('send-welcome-email-owner', [UserController::class, 'sendWelcomeEmailOwner']);
    });

    Route::get('/payouts', [PayoutController::class, 'index']);
    Route::post('/create-payout', [PayoutController::class, 'create']);

    Route::post('my-studios/filter', [AddressController::class, 'filterMyAddresses']);
    Route::post('history/filter', [BookingController::class, 'filterBookings'])->defaults('type', 'history');
    Route::post('booking-management/filter', [BookingController::class, 'filterBookings'])->defaults('type', 'future');

    Route::get('history', [BookingController::class, 'getBookings'])->defaults('type', 'history');
    Route::get('booking-management', [BookingController::class, 'getBookings'])->defaults('type', 'future');
    Route::get('my-studios/cities', [AddressController::class, 'getMyCities']);

    Route::get('menu', [MenuController::class, 'getMenu']);

    Route::get('company/{slug}', [CompanyController::class, 'getCompany']);
    Route::get('map/studios', [AddressController::class, '']);


    Route::get('operation-modes', [OperatingHourController::class, 'getOperationModes']);
    Route::post('brand', [AddressController::class, 'createBrand']); // company + address created

    Route::get('my-studios', [AddressController::class, 'getMyAddresses']);

    Route::get('{slug}/studios', [AddressController::class, 'getAddressesByCompanySlug']);

    Route::prefix('messages')->group(function () {
        Route::post('/', [MessageController::class, 'store']);
        Route::post('/history', [MessageController::class, 'history']);
        Route::get('/chats', [MessageController::class, 'chats']);
        Route::get('/chats/{id}', [MessageController::class, 'chatDetails']);
    });

});

Route::get('random-studio', [AddressController::class, 'getRandomStudio']);
Route::get('map/studios', [AddressController::class, 'getAllStudios']);
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
Route::post('/webhook/test', [BookingController::class, 'handleWebhook']);
