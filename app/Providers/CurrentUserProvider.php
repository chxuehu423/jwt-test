<?php

namespace App\Providers;

use App\Models\User;
use App\Utils\BLogger;
use Illuminate\Support\ServiceProvider;
use Tymon\JWTAuth\JWTAuth;

class CurrentUserProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->singleton('App\Models\User', function($app) {
            BLogger::writeInfoLog('currentUserProvider:'.json_encode(auth('api')->user()));
            if (auth('api')->check()){
                return auth('api')->user();
            }else {
                return null;
            }
            /*$request = $app->make('request');
            $authToken = $request->header('X-Auth-Token');
            if(!empty($authToken)) {
                return User::where('authenticate_token', $authToken)->first();
            } else {
                return null;
            }*/
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
