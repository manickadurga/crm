<?php

use Illuminate\Support\Facades\Route;
use Modules\ModuleStudio\Http\Controllers\ModuleStudioController;
use App\Http\Controllers\GoalController;


/*
 *--------------------------------------------------------------------------
 * API Routes
 *--------------------------------------------------------------------------
 *
 * Here is where you can register API routes for your application. These
 * routes are loaded by the RouteServiceProvider within a group which
 * is assigned the "api" middleware group. Enjoy building your API!
 *
*/

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('modulestudio', ModuleStudioController::class)->names('modulestudio');
});


// Route::post('/create-module', 'ModuleStudioController@store');
Route::post('/create-module', [ModuleStudioController::class, 'store']);
Route::get('/create-goal',[GoalController::class,'index']);
Route::post('/create-goal',[GoalController::class,'store']);
