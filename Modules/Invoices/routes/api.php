<?php

use App\Http\Controllers\EstimateController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LeadsController;
use App\Http\Controllers\InviteleadsController;

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

// Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
//     Route::apiResource('invoices', InvoicesController::class)->names('invoices');
// });

Route::get('estimates',[EstimateController::class,'index']);
Route::post('estimates',[EstimateController::class,'store']);
Route::get('estimates/{id}',[EstimateController::class,'show']);
Route::delete('estimates/{id}',[EstimateController::class,'destroy']);
Route::put('estimates/{id}/update',[EstimateController::class,'update']);

Route::get('leads',[LeadsController::class,'index']);
Route::post('leads',[LeadsController::class,'store']);
Route::get('leads/{id}',[LeadsController::class,'show']);
Route::delete('leads/{id}',[LeadsController::class,'destroy']);
Route::put('leads/{id}/update',[LeadsController::class,'update']);

Route::post('invite',[InviteleadsController::class,'store']);
Route::get('invite',[InviteleadsController::class,'index']);
// Route::get('/send-test-email',[InviteleadsController::class,'send']);
// Route::post('/send-');
