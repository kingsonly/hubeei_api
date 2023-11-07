<?php

use App\Http\Controllers\SiteController;
use App\Http\Controllers\HubController;
use Illuminate\Http\Request;
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
