<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use BigBlueButton\BigBlueButton;

class BigBlueButtonServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(BigBlueButton::class, function ($app) {
            $baseUrl = env('BBB_SERVER_BASE_URL');
            $secret = env('BBB_SECURITY_SALT');

            return new BigBlueButton($baseUrl, $secret);
        });
    }
}

