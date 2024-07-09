<?php
use App\Mail\InviteMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use  App\Http\Controllers\TeamTaskController;



Route::view('/', 'app')->where('any', '.*');



 Route::get('/csrf-token', function () {
    return response()->json(['csrfToken' => csrf_token()]);
});

// Route::get('/tasks', [TeamTaskController::class, 'index']);
// Route::get('/tasks/teams/{id}', [TeamTaskController::class, 'show']);
// Route::post('/tasks', [TeamTaskController::class, 'store']);
// Route::put('/tasks/teams/{id}', [TeamTaskController::class, 'update']);
// Route::delete('/tasks/teams/{id}', [TeamTaskController::class, 'destroy']);
Route::get('/teamtasks', [TeamTaskController::class, 'index']);
Route::get('/teamtasks/{id}', [TeamTaskController::class, 'show']);
Route::post('/teamtasks', [TeamTaskController::class, 'store']);
Route::get('/teamtasks/search', [TeamTaskController::class, 'search']);
Route::put('/teamtasks/{id}', [TeamTaskController::class, 'update']);
Route::delete('/teamtasks/{id}', [TeamTaskController::class, 'destroy']);
Route::get('/formfields', [FormFieldController::class, 'getFormFields']);
