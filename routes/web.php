<?php
use App\Mail\InviteMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomersController;
use App\Http\Controllers\PipelinesController;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::view('/', 'app')
->where('any', '.*');
Route::group([], function () {
    Route::resource('customers', CustomersController::class)->names('customers');
});

Route::get('/csrf-token', function () {
    return response()->json(['csrfToken' => csrf_token()]);
});

// Display a listing of the customers
Route::get('/customers', [CustomersController::class, 'index']);

// Store a newly created customer
Route::post('/customers', [CustomersController::class, 'store']);


// Remove the specified customer
Route::delete('/customers/{id}', [CustomersController::class, 'destroy']);

// Display the specified customer
Route::get('/customers/{id}', [CustomersController::class, 'show']);

// Update the specified customer
Route::put('/customers/{id}', [CustomersController::class, 'update']);

// Show the form for editing the specified customer
Route::get('/customers/{id}/edit', [CustomersController::class, 'edit']);

Route::get('/pipelines',[PipelinesController::class,'index']);
Route::post('/pipelines',[PipelinesController::class,'store']);

