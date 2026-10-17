<?php
use Illuminate\Support\Facades\Route;
use ME\SchoolManagement\Http\Controllers\SchoolManagementController;

Route::prefix('api/school-management')->group(function() {
    Route::get('/', [SchoolManagementController::class, 'index']);
});