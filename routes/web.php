<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\RetentionRequestController;

Route::get('departments', [DepartmentController::class, 'index']);

Route::get('/', [RetentionRequestController::class, 'index']);
Route::post('retention-requests', [RetentionRequestController::class, 'store']);
