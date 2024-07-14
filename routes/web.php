<?php

use Illuminate\Support\Facades\Route;

Route::get('/demo', [\App\Http\Controllers\DemoController::class, 'create']);
