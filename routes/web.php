<?php

use Illuminate\Support\Facades\Route;

Route::get('/address-validate-example', [\App\Http\Controllers\TestController::class, 'validateAddress']);
