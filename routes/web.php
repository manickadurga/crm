<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ModuleStudioController;


Route::view('/', 'app')
->where('any', '.*');

Route::get('login', [AuthController::class, 'index'])->name('login');

Route::post('post-login', [AuthController::class, 'postLogin'])->name('login.post'); 

Route::get('registration', [AuthController::class, 'registration'])->name('register');

Route::post('post-registration', [AuthController::class, 'postRegistration'])->name('register.post'); 

Route::get('dashboard', [AuthController::class, 'dashboard']); 

Route::get('logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/form/step1', [ModuleStudioController::class, 'step1'])->name('form');
Route::post('/form/step1', [ModuleStudioController::class, 'step1Post'])->name('form.step1.post');

Route::post('/check-module-name', [ModuleStudioController::class, 'checkModuleName'])->name('form.checkModuleName');

Route::get('/form/step2', [ModuleStudioController::class, 'step2'])->name('form.step2');
Route::post('/form/step2', [ModuleStudioController::class, 'step2Post'])->name('form.step2.post');

Route::get('/form/step3',[ModuleStudioController::class, 'step3'])->name('form.step3');
Route::post('/form/step3',[ModuleStudioController::class, 'step3Post'])->name('form.step3.post');

Route::get('/form/step4', [ModuleStudioController::class, 'step4'])->name('form.step4');
Route::post('/form/step4',[ModuleStudioController::class, 'step4Post'])->name('form.step4.post');

Route::get('/ajax', function () {
    return view('modulestudio::ajax');
});
Route::post('/ajax-request', [ModuleStudioController::class, 'step4Post'])->name('ajax.request');

Route::get('/form/success', [ModuleStudioController::class, 'success'])->name('form.success');


Route::resource('purchase', 'PurchaseController');