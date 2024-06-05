<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Factory;

class FirebaseServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('firebase', function ($app) {
            $serviceAccount = public_path('storage/firebase_credentials.json');
            
            $firebase = (new Factory)
                ->withServiceAccount($serviceAccount)
                ->withDatabaseUri('https://your-database-url.firebaseio.com'); // Ganti dengan URL Database Firebase kamu

            return $firebase;
        });

        $this->app->singleton('firebase.messaging', function ($app) {
            $firebase = $app->make('firebase');
            return $firebase->createMessaging();
        });
    }
}
