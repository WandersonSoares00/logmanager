<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MeliNotificationController;
use App\Http\Controllers\Api\ShippingLabelController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/test', function () {
    return response()->json(['message' => 'LogManager API']);
});

Route::post('/notifications/meli', [MeliNotificationController::class, 'handle'])->name('meli.notifications');

Route::get('/orders/{order}/label', [ShippingLabelController::class, 'show'])
    ->name('orders.label.show')
    ->middleware('auth:sanctum');
