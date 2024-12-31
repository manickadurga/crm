<?php
use App\Mail\InviteMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ClientsController;
use App\Http\Controllers\CustomersController;
use App\Http\Controllers\EquipmentsController;
use App\Http\Controllers\EstimateController;
use App\Http\Controllers\ExpensesController;
use App\Http\Controllers\FormFieldController;
use App\Http\Controllers\InvoicesController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\ModuleStudioController;
use App\Http\Controllers\TeamTaskController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Log;


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

Route::get('/invoices',[InvoicesController::class,'index']);
Route::post('/invoices',[InvoicesController::class,'store']);

Route::get('/teamtasks',[TeamTaskController::class,'index']);
Route::post('/teamtasks',[TeamTaskController::class,'store']);

Route::get('/clients',[ClientsController::class,'index']);


use App\Http\Controllers\ImportController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\LeadsController;
use App\Http\Controllers\PaymentsController;
use App\Http\Controllers\PipelinesController;
use App\Http\Controllers\ProposalTemplatesController;

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
Route::get('/customers/{id}', [CustomersController::class, 'show']);
Route::put('/customers/{id}', [CustomersController::class, 'update']);
Route::delete('/customers/{id}',[CustomersController::class,'destroy']);

//Form Field Routes
Route::get('/form-fields', [FormFieldController::class, 'getFormFields']);

//Customers Routes
Route::get('customers',[CustomersController::class,'index']);
Route::post('customers',[CustomersController::class,'store']);
Route::put('/customers/{id}',[CustomersController::class,'update']);
Route::delete('/customers/{id}',[CustomersController::class,'destroy']);

//Clients Routes
Route::get('/clients',[ClientsController::class,'index']);
Route::post('/clients',[ClientsController::class,'store']);
Route::put('/customers/{id}',[CustomersController::class,'update']);
Route::delete('/customers/{id}',[CustomersController::class,'destroy']);

//Leads Routes
Route::get('/leads',[LeadsController::class,'index']);
Route::post('/leads',[LeadsController::class,'store']);
Route::put('/leads/{id}',[LeadsController::class,'update']);
Route::delete('/leads/{id}',[LeadsController::class,'destroy']);

//Teamtask Routes
Route::get('/teamtasks',[TeamTaskController::class,'index']);
Route::post('/teamtasks',[TeamTaskController::class,'store']);
Route::put('/teamtasks/{id}',[TeamTaskController::class,'update']);
Route::delete('/teamtasks/{id}',[TeamTaskController::class,'destroy']);

//Payments Routes
Route::get('/payments',[PaymentsController::class,'index']);
Route::post('/payments',[PaymentsController::class,'store']);
Route::put('/payments/{id}',[PaymentsController::class,'update']);
Route::delete('/payments/{id}',[PaymentsController::class,'destroy']);

//Estimates Routes
Route::get('/estimates',[EstimateController::class,'index']);
Route::post('/estimates',[EstimateController::class,'store']);
Route::get('/estimates/{id}',[EstimateController::class,'show']);
Route::put('/estimates/{id}',[EstimateController::class,'update']);
Route::delete('/estimates/{id}',[EstimateController::class,'destroy']);

//Incomes Routes
Route::get('/incomes',[IncomeController::class,'index']);
Route::post('/incomes',[IncomeController::class,'store']);
Route::put('/incomes/{id}',[IncomeController::class,'update']);
Route::delete('/incomes/{id}',[IncomeController::class,'destroy']);

//Expenses Routes
Route::get('/expenses',[ExpensesController::class,'index']);
Route::post('/expenses',[ExpensesController::class,'store']);
Route::put('/expenses/{id}',[ExpensesController::class,'update']);
Route::delete('/expenses/{id}',[ExpensesController::class,'destroy']);

//Proposal Template Routes
Route::get('/proposal-templates',[ProposalTemplatesController::class,'index']);
Route::post('/proposal-templates',[ProposalTemplatesController::class,'store']);
Route::put('/proposal-templates/{id}',[ProposalTemplatesController::class,'update']);
Route::delete('/proposal-templates/{id}',[ProposalTemplatesController::class,'destroy']);

//Pipelines Routes
Route::get('/pipelines',[PipelinesController::class,'index']);
Route::post('/pipelines',[PipelinesController::class,'store']);
Route::put('/pipelines/{id}',[PipelinesController::class,'update']);
Route::delete('/pipelines/{id}',[PipelinesController::class,'destroy']);

//Equipments Routes
Route::get('/equipments',[EquipmentsController::class,'index']);
Route::post('/equipments',[EquipmentsController::class,'store']);
Route::put('/equipments/{id}',[EquipmentsController::class,'update']);
Route::delete('/euipments/{id}',[EquipmentsController::class,'destroy']);

use App\Http\Controllers\EmailController;
use App\Http\Controllers\EmailTrackingController;
use App\Http\Controllers\TrackingController;

Route::post('/send-email', [EmailController::class, 'sendEmail']);


// use App\Http\Controllers\MailgunWebhookController;

// Route::post('/api/mailgun/webhook', [MailgunWebhookController::class, 'handle']);



// Route::post('/mailgun/webhook', [MailgunController::class, 'webhook'])->name('mailgun.webhook');
// 
// Route::post('/mailgun/webhook', [MailgunController::class, 'webhook']) ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]) ->name('mailgun.webhook');


// use App\Http\Controllers\MailgunController;

// Route::post('/mailgun/webhook', [MailgunController::class, 'webhook'])
//     ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
//     ->name('mailgun.webhook');



// Track the email open event
//7dec

// Route::get('/track/open/{mailId}', [EmailController::class, 'trackOpen'])->name('track.open');

// // Click tracking
// Route::get('/track/click/{mailId}', [EmailController::class, 'trackClick'])->name('track.click');

// routes/web.php

Route::get('/track_open', [TrackingController::class, 'trackOpen']);
Route::get('/track_click', [TrackingController::class, 'trackClick']);


