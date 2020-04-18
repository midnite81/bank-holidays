<?php

namespace Midnite81\BankHolidays;

use Illuminate\Support\ServiceProvider;
use Midnite81\BankHolidays\Contracts\Drivers\ICache;
use Midnite81\BankHolidays\Contracts\IBankHoliday;
use Midnite81\BankHolidays\Contracts\Services\IClient;
use Midnite81\BankHolidays\Services\Client;

class BankHolidayServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     * @codeCoverageIgnore
     */
    public function boot()
    {
        $this->publishes([
            __DIR__
            . '/../config/bank-holidays.php' => config_path('bank-holidays.php')
        ]);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(IBankHoliday::class, function ($app) {
            return new BankHoliday(
                app(IClient::class),
                app(ICache::class),
                $app['config']
            );
        });

        $this->app->bind(IClient::class, function ($app) {

            $httpClient = empty($app['config']['bank-holidays']['http-client'])
                ? null : new $app['config']['bank-holidays']['http-client'];
            $requestFactory
                        = empty($app['config']['bank-holidays']['request-factory'])
                ? null : new $app['config']['bank-holidays']['request-factory'];

            return new Client(
                $httpClient,
                $requestFactory,
                $app['config']['bank-holidays']
            );
        });

        $this->app->bind(ICache::class, function ($app) {
            return $app['config']['cache-class'];
        });

        $this->mergeConfigFrom(
            __DIR__ . '/../config/bank-holidays.php',
            'bank-holidays'
        );
    }

}