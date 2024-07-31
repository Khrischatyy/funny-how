<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Связывание StripeService и SquareService
        $this->app->singleton(StripeService::class);
        $this->app->singleton(SquareService::class, function ($app) {
            $squareClient = new SquareClient([
                'accessToken' => env('SQUARE_ACCESS_TOKEN'),
                'environment' => env('SQUARE_ENVIRONMENT'),
            ]);
            return new SquareService($squareClient);
        });

        // Связывание основного PaymentService
        $this->app->singleton(PaymentService::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
