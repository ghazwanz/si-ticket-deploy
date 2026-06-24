<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified', 'role:admin'])
    ->prefix('admin')
    ->name('api.admin.')
    ->group(function () {
        Route::apiResource('event-categories', \App\Http\Controllers\Api\Admin\EventCategoryController::class)
            ->except(['show']);
    });