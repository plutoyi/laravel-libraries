<?php

namespace Patpat\ResponseCache;

use Illuminate\Support\ServiceProvider;
use Patpat\ResponseCache\CacheProfiles\CacheProfile;
use Patpat\ResponseCache\CacheProfiles\CacheAllSuccessfulGetRequests;

class ResponseCacheServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(CacheProfile::class, function ($app) {
            return $app->make(CacheAllSuccessfulGetRequests::class);

        });
    }
}
