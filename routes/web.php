<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\RetentionRequestController;

// FIXME: default to 404 page
// FIXME: go to error page on database connection issues

Route::post('retention-requests', [RetentionRequestController::class, 'index']);

Route::get('{pageRoute}', function () {
    return view('app');
})->where('pageRoute', '^((?!api).)*$');

Route::post('api/retention-requests', [RetentionRequestController::class, 'store']);
Route::put('api/retention-requests/{id}', [RetentionRequestController::class, 'update']);

Route::get('api/departments', [DepartmentController::class, 'index']);
Route::get('api/departments/{id}', [DepartmentController::class, 'show']);
