<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MeliNotificationController;
use App\Http\Controllers\Api\ShippingLabelController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\MeliAccountController;


Route::post('/notifications/meli', [MeliNotificationController::class, 'handle'])->name('meli.notifications');

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/orders', [OrderController::class, 'index'])
        ->name('orders.index');

    Route::get('/orders/{order}/label', [ShippingLabelController::class, 'show'])
        ->name('orders.label.show');

    Route::get('/reports/shipped-today', [ReportController::class, 'shippedToday'])
        ->name('reports.shipped.today');

    Route::get('/reports/sla-weekly', [ReportController::class, 'weeklySla'])
        ->name('reports.sla.weekly');

    Route::get('/meli-accounts', [MeliAccountController::class, 'index'])
        ->name('meli-accounts.index');
});
