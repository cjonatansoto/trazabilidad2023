<?php

namespace App\Http\Controllers;


class ConfigController extends Controller
{
    public function optimize()
    {
        \Artisan::call('optimize');
        \Artisan::call('cache:clear');
        \Artisan::call('route:clear');
        \Artisan::call('view:clear');
        \Artisan::call('config:clear');

        echo '********** APLICACIÓN OPTIMIZADA ************</br>';
        echo '**********    DEV JONATAN SOTO   ************</br>';
        echo '********** . . . . . . . . . . . ************</br>';
    }

}
