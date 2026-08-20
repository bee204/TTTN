<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\YogaClassController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\PublicCatalogController;
use App\Http\Controllers\UnifiedRegistrationController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ClassReviewController;

Route::prefix('public')->group(function () {
    Route::get('/teachers', [PublicCatalogController::class, 'teachers']);
    Route::get('/teachers/{teacher}', [PublicCatalogController::class, 'teacher']);
    Route::get('/classes',  [PublicCatalogController::class, 'classes']);
    Route::get('/classes/{class}',  [PublicCatalogController::class, 'class']);
});

// Allow registration without authentication
Route::post('/registrations', [UnifiedRegistrationController::class, 'store']);

Route::get('/ping', function () {
    return response()->json(['message' => 'API is working']);
});


Route::post('/login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {


    Route::get('/me', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [AuthController::class, 'logout']);


    Route::middleware('role:admin')->group(function () {
        Route::apiResource('teachers', TeacherController::class);
        Route::apiResource('classes', YogaClassController::class);
        Route::apiResource('customers', CustomerController::class);
        Route::apiResource('registrations', RegistrationController::class);
        Route::post('/registrations/{id}/confirm', [RegistrationController::class, 'confirm']);
        Route::post('/registrations/{id}/cancel', [RegistrationController::class, 'cancel']);
    });

    // CN01: attendance per session and attendance summaries
    Route::middleware('role:admin,teacher')->group(function () {
        Route::get('/attendance/summary', [AttendanceController::class, 'summary']);
        Route::apiResource('attendance', AttendanceController::class)->only(['index', 'store', 'update']);
    });

    // CN02: class reviews and ranking
    Route::get('/class-reviews/ranking', [ClassReviewController::class, 'ranking']);
    Route::apiResource('class-reviews', ClassReviewController::class)->only(['index', 'store', 'update', 'destroy']);

});
