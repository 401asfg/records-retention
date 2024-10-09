<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\RetentionRequestController;

Route::get('{pageRoute}', function() {
    return view('app');
})->where('pageRoute', '^((?!api).)*$');

Route::post('api/retention-requests', [RetentionRequestController::class, 'store']);
Route::get('api/departments', [DepartmentController::class, 'index']);
