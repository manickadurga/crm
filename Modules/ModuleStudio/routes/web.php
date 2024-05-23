<?php

use Illuminate\Support\Facades\Route;
use Modules\ModuleStudio\Http\Controllers\ModuleStudioController;

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
    Route::resource('modulestudio', ModuleStudioController::class)->names('modulestudio');
});

Route::get('/form/step1', [ModuleStudioController::class, 'step1'])->name('form.step1');
Route::post('/form/step1', [ModuleStudioController::class, 'step1Post'])->name('form.step1.post');

Route::get('/form/step2', [ModuleStudioController::class, 'step2'])->name('form.step2');
Route::post('/form/step2', [ModuleStudioController::class, 'step2Post'])->name('form.step2.post');

Route::get('/form/step3',[ModuleStudioController::class, 'step3'])->name('form.step3');
Route::post('/form/step3',[ModuleStudioController::class, 'step3Post'])->name('form.step3.post');

Route::get('/form/step4', [ModuleStudioController::class, 'step4'])->name('form.step4');
Route::post('/form/step4',[ModuleStudioController::class, 'step4Post'])->name('form.step4.post');

Route::get('/form/success', [ModuleStudioController::class, 'success'])->name('form.sucess');


