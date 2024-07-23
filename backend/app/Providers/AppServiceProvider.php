<?php

namespace App\Providers;

use App\Services\CityService;
use App\Services\CountryService;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Intervention\Image\ImageManager;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(CountryService::class, function ($app) {
            return new CountryService();
        });

        $this->app->singleton(CityService::class, function ($app) {
            return new CityService($app->make(CountryService::class));
        });

        $this->app->singleton(ImageManager::class, function ($app) {
            return new ImageManager(config('image.driver'));
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }
}
