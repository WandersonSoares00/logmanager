<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\MeliAuthController;

//Route::get('/', function () {
//    return view('welcome');
//});

Route::get('/auth/meli/redirect', [MeliAuthController::class, 'redirect'])->name('meli.auth.redirect');
Route::get('/auth/meli/callback', [MeliAuthController::class, 'callback'])->name('meli.auth.callback');
