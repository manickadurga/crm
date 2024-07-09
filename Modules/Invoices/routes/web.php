<?php

use App\Http\Controllers\EstimateController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvoicesController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group([], function () {
    Route::resource('invoices', InvoicesController::class)->names('invoices');
});

Route::get('invoice',[InvoicesController::class,'index']);
Route::post('invoice',[InvoicesController::class,'store']);


Route::put('invoices/{id}',[InvoicesController::class,'update']);
Route::get('invoices/{id}',[InvoicesController::class,'show']);
Route::delete('invoices/{id}',[InvoicesController::class,'destroy']);



