<?php

use App\Http\Controllers\HubCategoryContentController;
use App\Http\Controllers\HubCategoryController;
use App\Http\Controllers\HubController;
use App\Http\Controllers\HubSettingsController;
use App\Http\Controllers\HubSubscriptionController;
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

Route::post('/register-settings-update', [SiteController::class, 'updateHubRegistrationSettings'])->name('updateHubRegistrationSettings');
Route::get('/register-settings-view/{id}', [SiteController::class, 'getHubRegistrationSettings'])->name('getHubRegistrationSettings');
Route::post('/register-settings-create', [SiteController::class, 'hubRegistrationSettings'])->name('hubRegistrationSettings');
Route::post('/register', [SiteController::class, 'register'])->name('register');
Route::get('content/engagmet-view/{id}', [HubCategoryContentController::class, 'getEngagementContentUsers'])->name('engagmet-view');
Route::post('content/respond-to-engagment/{id}', [HubCategoryContentController::class, 'respondToEngagment'])->name('respond-to-engagment');
Route::post('/content/save-views', [HubCategoryContentController::class, 'saveViews'])->name('save-views');
Route::post('/login', [SiteController::class, 'login'])->name('login');
Route::post('/subscription/register', [HubSubscriptionController::class, 'registration'])->name('subscription-register');
Route::post('/subscription/login', [HubSubscriptionController::class, 'login'])->name('subscription-login');
Route::get('/usershub/{id}', [HubController::class, 'getUsersHubs'])->name('users-hub');
Route::get('/category-content/{id}', [HubCategoryController::class, 'getCategoryWithContent'])->name('category-content');
Route::post('/create-category', [HubCategoryController::class, 'create'])->name('create-category');
Route::post('/content/create', [HubCategoryContentController::class, 'create'])->name('create-content');
Route::get('/content/view/{id}', [HubCategoryContentController::class, 'view'])->name('view-content');
Route::post('/content/change-content-position', [HubCategoryContentController::class, 'changeContentPosition'])->name('change-content-position');
Route::get('/content/get-spotlight-content/{id}', [HubCategoryContentController::class, 'getSpotlightContent'])->name('get-spotlight-content');
Route::get('/content/update-content-views/{id}', [HubCategoryContentController::class, 'updateContentViews'])->name('update-content-views');
Route::get('/content/get-top-ten-views/{id}', [HubCategoryContentController::class, 'getTopTenViews'])->name('update-content-views');
Route::post('/content/search/{id}', [HubCategoryContentController::class, 'search'])->name('content-search');
Route::get('/content/like-un-like/{id}', [HubCategoryContentController::class, 'likeUnlike'])->name('like-un-like');
Route::post('/content/update/{id}', [HubCategoryContentController::class, 'update'])->name('update-content');
Route::post('/content/delete/{id}', [HubCategoryContentController::class, 'delete'])->name('delete-content');
Route::get('/content/liked/{id}', [HubCategoryContentController::class, 'getLikedContent'])->name('get-liked-content');
Route::get('/dashboard/stats/{id}', [SiteController::class, 'dashboardCardsContent'])->name('dashboard-stats');
Route::post('/dashboard/hubs/settings/update', [HubSettingsController::class, 'updateSettings'])->name('settings-update');
Route::get('/gethubsettings/{id}', [HubSettingsController::class, 'getHubSettings'])->name('grt-hub-settings');
Route::get('/hub/get-users-hubs-by-hub-name/{id}', [HubController::class, 'getUsersHubsByHubName']);
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/hub/create', [HubController::class, 'create'])->name('hub-create');
});
