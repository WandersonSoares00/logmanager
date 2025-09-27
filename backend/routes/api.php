<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MeliNotificationController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/test', function () {
    return response()->json(['message' => 'LogManager API']);
});

Route::post('/notifications/meli', [MeliNotificationController::class, 'handle'])->name('meli.notifications');
