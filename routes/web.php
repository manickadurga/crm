<?php
use App\Mail\InviteMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ClientsController;
use App\Http\Controllers\CustomersController;
use App\Http\Controllers\FormFieldController;
use App\Http\Controllers\InvoicesController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\ModuleStudioController;
use App\Http\Controllers\TeamTaskController;
use App\Http\Controllers\UserController;


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

Route::resource('users', UserController::class);


Route::resource('purchase', 'PurchaseController');

Route::get('/customers',[CustomersController::class,'index']);
Route::post('/customers',[CustomersController::class,'store']);
Route::delete('/customers/{id}',[CustomersController::class,'destroy']);
Route::get('/formfields',[FormFieldController::class,'getFormFields']);
Route::get('/menuitems',[MenuController::class,'getMenuItems']);

Route::get('/invoice',[InvoicesController::class,'index']);
Route::post('/invoice',[InvoicesController::class,'store']);

Route::get('/teamtasks',[TeamTaskController::class,'index']);
Route::post('/teamtasks',[TeamTaskController::class,'store']);

Route::get('/clients',[ClientsController::class,'index']);


use App\Http\Controllers\ImportController;
// web.php (or your routes file)
Route::get('/import/{module}/form', [ImportController::class, 'showForm'])->name('import.form');
Route::post('/import/{module}', [ImportController::class, 'import'])->name('import.process');
Route::get('/import/{module}/step2', [ImportController::class, 'showStep2Form'])->name('import.step2');
Route::post('/import/{module}/step2', [ImportController::class, 'processStep2'])->name('import.step2.process');
Route::get('/import/{module}/field-mapping', [ImportController::class, 'showFieldMappingForm'])->name('import.fieldMapping');
Route::post('/import/{module}/field-mapping', [ImportController::class, 'processImport'])->name('import.processImport');
Route::get('/import/{module}/cancel', [ImportController::class, 'cancelImport'])->name('import.cancel');
Route::get('/imported-data/{module}', [ImportController::class, 'showImported'])->name('imported.data');
Route::get('/imported/{module}', [ImportController::class, 'showImported'])->name('imported.module');
Route::get('/import/summary/{module}', [ImportController::class, 'showSummary'])->name('import.summary');
//Route::get('/import/{module}/form', [ImportController::class, 'showImportForm'])->name('import.form');


Route::get('/customers',[CustomersController::class,'index']);
Route::post('/customers',[CustomersController::class,'store']);
// Update the specified customer
Route::get('/customers/{id}', [CustomersController::class, 'show']);
Route::put('/customers/{id}', [CustomersController::class, 'update']);
Route::delete('/customers/{id}',[CustomersController::class,'destroy']);
// Route::get('/form-fields', [FormFieldController::class, 'getFormFields']);

Route::get('/form-fields', [FormFieldController::class, 'getFormFields']);

Route::get('customers',[CustomersController::class,'index']);
Route::post('customers',[CustomersController::class,'store']);

