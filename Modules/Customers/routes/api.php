<?php

use App\Http\Controllers\EmployeesController;
use App\Http\Controllers\EstimateController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FormFieldController;
use App\Http\Controllers\FormFieldsController;
use App\Http\Controllers\InvoicesController;
use App\Http\Controllers\LeadsController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\ProductCategoriesController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\ProductTypesController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TasksController;

use Illuminate\Support\Facades\Route;
use Modules\Customers\Http\Controllers\CustomersController;

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

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('customers', CustomersController::class)->names('customers');
});

Route::get('/form-fields', [FormFieldController::class, 'getFormFields']);
// Route::get('/form-fields', [FormFieldsController::class, 'getFormField']);


Route::get('customer',[CustomersController::class,'index']);
Route::post('customer',[CustomersController::class,'store']);


Route::get('tasks',[TasksController::class,'index']);
Route::post('tasks',[TasksController::class,'store']);

Route::get('employees',[EmployeesController::class,'index']);
Route::post('employees',[EmployeesController::class,'store']);


Route::get('projects',[ProjectController::class,'index']);
Route::post('projects',[ProjectController::class,'store']);


Route::get('expenses',[ExpenseController::class,'index']);
Route::post('expenses',[ExpenseController::class,'store']);

Route::get('product',[ProductsController::class,'index']);
Route::post('product',[ProductsController::class,'store']);

Route::get('product-type',[ProductTypesController::class,'index']);
Route::post('product-type',[ProductTypesController::class,'store']);

Route::get('product-categories',[ProductCategoriesController::class,'index']);
Route::post('product-categories',[ProductCategoriesController::class,'store']);

Route::post('leads',[LeadsController::class,'store']);
Route::get('leads',[LeadsController::class,'index']);
Route::put('leads/{id}',[LeadsController::class,'update']);
Route::get('leads/{id}',[LeadsController::class,'show']);
Route::delete('leads/{id}',[LeadsController::class,'destroy']);



Route::post('invoice',[InvoicesController::class,'store']);
Route::get('invoice',[InvoicesController::class,'index']);
Route::put('invoice/{id}',[InvoicesController::class,'update']);
Route::get('invoice/{id}',[InvoicesController::class,'show']);
Route::delete('invoice/{id}',[InvoicesController::class,'destroy']);


//download PDF
Route::get('invoice/{id}/download', [InvoicesController::class, 'downloadInvoice']);

Route::get('invoices', [InvoicesController::class, 'fetchData']);
Route::get('invoices/{type}/{value}', [InvoicesController::class, 'getDetails']);

//post function modules Invoices
Route::post('invoices/{invoiceid}/tasks',[InvoicesController::class,'addTasks']);
Route::post('invoices/{invoiceId}/product', [InvoicesController::class, 'addProducts']);
Route::post('invoices/{invoiceId}/employee',[InvoicesController::class, 'addEmpProducts']);
Route::post('invoices/{invoiceid}/projects',[InvoicesController::class,'addProjects']);
Route::post('invoices/{invoiceid}/expenses',[InvoicesController::class,'addExpenses']);


//get function modules in Invoices
Route::get('tasks/{id}', [InvoicesController::class, 'getTasks']);
Route::get('invoices/{id}', [InvoicesController::class, 'getProductDetails']); //Products
Route::get('employees/{id}', [InvoicesController::class, 'getEmpProducts']);
Route::get('projects/{id}', [InvoicesController::class, 'getProjects']);
Route::get('expenses/{id}', [InvoicesController::class, 'getExpenses']);



Route::get('organizations',[OrganizationController::class,'index']);
Route::post('organizations',[OrganizationController::class,'store']);


Route::post('estimates',[EstimateController::class,'store']);
Route::get('estimates',[EstimateController::class,'index']);

Route::put('estimates/{id}',[EstimateController::class,'update']);
Route::get('estimates/{id}',[EstimateController::class,'show']);
Route::delete('estimates/{id}',[EstimateController::class,'destroy']);

//download PDF
Route::get('estimates/{id}/download', [EstimateController::class, 'downloadEstimate']);

Route::get('Estimates', [EstimateController::class, 'fetchData']);
Route::get('Estimates/{type}/{value}', [EstimateController::class, 'getDetails']);

//get function module in Estimates
Route::post('Estimates/{estimateId}/tasks',[EstimateController::class,'addTasks']);
Route::post('Estimates/{estimateId}/product', [EstimateController::class, 'addProducts']);
Route::post('Estimates/{estimateId}/employee',[EstimateController::class, 'addEmpProducts']);
Route::post('Estimates/{estimateId}/projects',[EstimateController::class,'addProjects']);
Route::post('Estimates/{estimateId}/expenses',[EstimateController::class,'addExpenses']);


//post function module in Estimates
Route::get('task/{id}', [EstimateController::class, 'getTasks']);
Route::get('Estimates/{id}', [EstimateController::class, 'getProductDetails']);
Route::get('employee/{id}', [EstimateController::class, 'getEmpProducts']);
Route::get('project/{id}', [EstimateController::class, 'getProjects']);
Route::get('expense/{id}', [EstimateController::class, 'getExpenses']);

