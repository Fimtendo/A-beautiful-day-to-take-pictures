<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MarkerController;
use App\Http\Controllers\Api\PhotoPostController;
use App\Http\Controllers\Api\PhotoDateController;
use App\Http\Controllers\Api\PostLikeController;
use App\Http\Controllers\Api\AttendeeController;
use App\Http\Controllers\Api\PostBookmarkController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\WeatherController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'me']);
    Route::apiResource('markers', MarkerController::class)->only(['store', 'update', 'destroy']);
    Route::post('/photo-posts', [PhotoPostController::class, 'store']);
    Route::delete('/photo-posts/{photo_post}', [PhotoPostController::class, 'destroy']);
    Route::post('/photo-posts/{photo_post}/likes', [PostLikeController::class, 'storeLike']);
    Route::delete('/photo-posts/{photo_post}/likes', [PostLikeController::class, 'destroyLike']);
    Route::post('/photo-posts/{photo_post}/bookmarks', [PostBookmarkController::class, 'storeBookmark']);
    Route::delete('/photo-posts/{photo_post}/bookmarks', [PostBookmarkController::class, 'destroyBookmark']);
    Route::post('/photo-dates', [PhotoDateController::class, 'store']);
    Route::post('/photo-dates/{photo_date}/attendees', [AttendeeController::class, 'addAttendee']);
    Route::delete('/photo-dates/{photo_date}/attendees', [AttendeeController::class, 'removeAttendee']);
    Route::put('/photo-dates/{photo_date}', [PhotoDateController::class, 'update']);
    Route::delete('/photo-dates/{photo_date}', [PhotoDateController::class, 'destroy']);
});

Route::get('/markers', [MarkerController::class, 'index']);
Route::get('/markers/{marker}', [MarkerController::class, 'show']);
Route::get('/photo-posts', [PhotoPostController::class, 'index']);
Route::get('/photo-posts/{photo_post}', [PhotoPostController::class, 'show']);
Route::get('/photo-dates', [PhotoDateController::class, 'index']);
Route::get('/photo-dates/{photo_date}', [PhotoDateController::class, 'show']);

// Simple admin endpoints for local DB inspection
Route::get('/admin/tables', [AdminController::class, 'tables']);
Route::get('/admin/table/{name}', [AdminController::class, 'table']);

Route::get('/weather', [WeatherController::class, 'getWeather']);