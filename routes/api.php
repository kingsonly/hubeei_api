<?php

use App\Http\Controllers\HubCategoryContentController;
use App\Http\Controllers\HubCategoryController;
use App\Http\Controllers\HubController;
use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
 */

Route::post('/register', [SiteController::class, 'register'])->name('register');
Route::post('/login', [SiteController::class, 'login'])->name('login');
Route::get('/usershub/{id}', [HubController::class, 'getUsersHubs'])->name('users-hub');
Route::get('/category-content/{id}', [HubCategoryController::class, 'getCategoryWithContent'])->name('category-content');
Route::post('/create-category', [HubCategoryController::class, 'create'])->name('create-category');
Route::post('/content/create', [HubCategoryContentController::class, 'create'])->name('create-content');
Route::post('/content/change-content-position', [HubCategoryContentController::class, 'changeContentPosition'])->name('change-content-position');
Route::get('/content/get-spotlight-content/{id}', [HubCategoryContentController::class, 'getSpotlightContent'])->name('get-spotlight-content');
Route::get('/content/update-content-views/{id}', [HubCategoryContentController::class, 'updateContentViews'])->name('update-content-views');
Route::get('/content/get-top-ten-views/{id}', [HubCategoryContentController::class, 'getTopTenViews'])->name('update-content-views');
Route::get('/content/search/{id}/{search}', [HubCategoryContentController::class, 'search'])->name('content-search');
Route::get('/content/like-un-like/{id}', [HubCategoryContentController::class, 'likeUnlike'])->name('like-un-like');
Route::post('/content/update/{id}', [HubCategoryContentController::class, 'update'])->name('update-content');
