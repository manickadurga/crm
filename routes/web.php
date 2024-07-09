<?php

// use App\Http\Controllers\EstimateController;
use App\Http\Controllers\InvoicesController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::view('/', 'app')
->where('any', '.*');



Route::get('invoice',[InvoicesController::class,'index']);
Route::post('invoice',[InvoicesController::class,'store']);


Route::put('invoices/{id}',[InvoicesController::class,'update']);
Route::get('invoices/{id}',[InvoicesController::class,'show']);
Route::delete('invoices/{id}',[InvoicesController::class,'destroy']);





