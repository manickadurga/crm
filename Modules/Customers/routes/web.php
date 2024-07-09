<?php

use Illuminate\Support\Facades\Route;
use Modules\Customers\Http\Controllers\CustomersController;

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

/*Route::group([], function () {
    Route::resource('customers', CustomersController::class)->names('customers');
});

Route::get('/csrf-token', function () {
    return response()->json(['csrfToken' => csrf_token()]);
});

Route::get('/customers',[CustomersController::class,'index']);

Route::post('/customers',[CustomersController::class,'store']);

Route::delete('/customers/{id}', [CustomersController::class, 'destroy']);

Route::get('/customers/{id}', [CustomersController::class, 'show']);

Route::put('/customers/{id}',[CustomersController::class,'update']);
Route::get('/customers/{id}/edit', [CustomersController::class, 'edit']);*/
