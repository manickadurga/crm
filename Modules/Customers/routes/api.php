<?php

use App\Http\Controllers\ClientsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomersController;
use App\Http\Controllers\CustomersInviteController;
use App\Http\Controllers\EmployeesController;
use App\Http\Controllers\EquipmentsController;
use App\Http\Controllers\EquipmentsSharingController;
use App\Http\Controllers\PaymentsController;
use App\Http\Controllers\PipelinesController;
use App\Http\Controllers\EquipmentsSharingPolicyController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\LeadsController;
use App\Http\Controllers\ManageCategoriesController;
use App\Http\Controllers\MerchantsController;
use App\Http\Controllers\ProductCategoriesController;
use App\Http\Controllers\ProductTypesController;
use App\Http\Controllers\RecuringExpensesController;
use App\Http\Controllers\WarehousesController;
use App\Http\Controllers\VendorsController;

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

//Customers Routes
Route::get('/customers',[CustomersController::class,'index']);
Route::post('/customers',[CustomersController::class,'store']);
Route::get('/customers/{id}', [CustomersController::class, 'show']);
Route::put('/customers/{id}', [CustomersController::class, 'update']);
Route::delete('/customers/{id}', [CustomersController::class, 'destroy']);


//Payments Routes
Route::get('/payments',[PaymentsController::class,'index']);
Route::post('/payments',[PaymentsController::class,'store']);
Route::get('/payments/{id}', [PaymentsController::class, 'show']);
Route::put('payments/{id}', [PaymentsController::class,'update']);
Route::delete('/payments/{id}',[PaymentsController::class,'destroy']);

//Pipelines Routes
Route::get('/pipelines',[PipelinesController::class,'index']);
Route::post('/pipelines',[PipelinesController::class,'store']);
Route::get('/pipelines/{id}',[PipelinesController::class,'show']);
Route::put('/pipelines/{id}',[PipelinesController::class,'update']);
Route::delete('/pipelines/{id}',[PipelinesController::class,'destroy']);

//Customers Invite Routes
Route::get('/customersinvite',[CustomersInviteController::class,'index']);
Route::post('/customersinvite',[CustomersInviteController::class,'store']);
Route::get('/customersinvite/{id}',[CustomersInviteController::class,'show']);
Route::put('/customersinvite/{id}',[CustomersInviteController::class,'update']);
Route::delete('/customersinvite/{id}',[CustomersInviteController::class,'destroy']);


//Equipments Routes
Route::get('/equipments',[EquipmentsController::class,'index']);
Route::post('/equipments',[EquipmentsController::class,'store']);
Route::get('/equipments/{id}',[EquipmentsController::class,'show']);
Route::put('/equipments/{id}',[EquipmentsController::class,'update']);
Route::delete('/equipments/{id}',[EquipmentsController::class,'destroy']);


//Equipments Sharing Routes
Route::get('/equipments-sharing', [EquipmentsSharingController::class, 'index']);
Route::post('/equipments-sharing', [EquipmentsSharingController::class, 'store']);
Route::get('/equipments-sharing/{id}', [EquipmentsSharingController::class, 'show']);
Route::put('/equipments-sharing/{id}', [EquipmentsSharingController::class, 'update']);
Route::delete('/equipments-sharing/{id}', [EquipmentsSharingController::class, 'destroy']);

//Equipments Sharing Policy Routes
Route::get('/equipments-sharing-policy', [EquipmentsSharingPolicyController::class, 'index']);
Route::post('/equipments-sharing-policy', [EquipmentsSharingPolicyController::class, 'store']);
Route::get('/equipments-sharing-policy/{id}', [EquipmentsSharingPolicyController::class, 'show']);
Route::put('/equipments-sharing-policy/{id}', [EquipmentsSharingPolicyController::class, 'update']);
Route::delete('/equipments-sharing-policy/{id}', [EquipmentsSharingPolicyController::class, 'destroy']);

//Product Types Routes
Route::get('/product-types',[ProductTypesController::class,'index']);
Route::post('/product-types',[ProductTypesController::class,'store']);
Route::get('/product-types/{id}',[ProductTypesController::class,'show']);
Route::put('/product-types/{id}',[ProductTypesController::class,'update']);
Route::delete('/product-types/{id}',[ProductTypesController::class,'destroy']);

//Product Categories Routes
Route::get('/product-categories',[ProductCategoriesController::class,'index']);
Route::post('/product-categories',[ProductCategoriesController::class,'store']);
Route::get('/product-categories/{id}',[ProductCategoriesController::class,'show']);
Route::put('/product-categories/{id}',[ProductCategoriesController::class,'update']);
Route::delete('/product-categories/{id}',[ProductCategoriesController::class,'destroy']);

//Warehouses Routes
Route::get('/warehouses',[WarehousesController::class,'index']);
Route::post('/warehouses',[WarehousesController::class,'store']);
Route::get('/warehouses/{id}',[WarehousesController::class,'show']);
Route::put('/warehouses/{id}',[WarehousesController::class,'update']);
Route::delete('/warehouses/{id}',[WarehousesController::class,'destroy']);

//Merchants Routes
Route::get('/merchants',[MerchantsController::class,'index']);
Route::post('/merchants',[MerchantsController::class,'store']);
Route::get('/merchants/{id}',[MerchantsController::class,'show']);
Route::put('/merchants/{id}',[MerchantsController::class,'update']);
Route::delete('/merchants/{id}',[MerchantsController::class,'destroy']);

//Inventories Routes
Route::get('/inventories',[InventoryController::class,'index']);
Route::post('/inventories',[InventoryController::class,'store']);
Route::get('/inventories/{id}',[InventoryController::class,'show']);
Route::put('/inventories/{id}',[InventoryController::class,'update']);
Route::delete('/inventories/{id}',[InventoryController::class,'destroy']);

//Recuring Expenses Routes
Route::get('/recuring-expenses',[RecuringExpensesController::class,'index']);
Route::post('/recuring-expenses',[RecuringExpensesController::class,'store']);
Route::get('/recuring-expenses/{id}',[RecuringExpensesController::class,'show']);
Route::put('/recuring-expenses/{id}',[RecuringExpensesController::class,'update']);
Route::delete('/recuring-expenses/{id}',[RecuringExpensesController::class,'destroy']);

//Manage Categories Routes
Route::get('/manage-categories',[ManageCategoriesController::class,'index']);
Route::post('/manage-categories',[ManageCategoriesController::class,'store']);
Route::get('/manage-categories/{id}',[ManageCategoriesController::class,'show']);
Route::put('/manage-categories/{id}',[ManageCategoriesController::class,'update']);
Route::delete('/manage-categories/{id}',[ManageCategoriesController::class,'destroy']);

//Vendors Routes
Route::get('/vendors',[VendorsController::class,'index']);
Route::post('/vendors',[VendorsController::class,'store']);
Route::get('/vendors/{id}',[VendorsController::class,'show']);
Route::put('/vendors/{id}',[VendorsController::class,'update']);
Route::delete('/vendors/{id}',[VendorsController::class,'destroy']);

//Employees Routes
Route::get('/employees',[EmployeesController::class,'index']);
Route::post('/employees',[EmployeesController::class,'store']);
Route::get('/employees/{id}',[EmployeesController::class,'show']);
Route::put('/employees/{id}',[EmployeesController::class,'update']);
Route::delete('/employees/{id}',[EmployeesController::class,'destroy']);

//Clients Routes
Route::get('/clients',[ClientsController::class,'index']);
Route::post('/clients',[ClientsController::class,'store']);
Route::get('/clients/{id}',[ClientsController::class,'show']);
Route::put('/clients/{id}',[ClientsController::class,'update']);
Route::delete('/clients/{id}',[ClientsController::class,'destroy']);

//Leads Routes
Route::get('/leads',[LeadsController::class,'index']);
Route::post('/leads',[LeadsController::class,'store']);
Route::get('/leads/{id}',[LeadsController::class,'show']);
Route::put('/leads/{id}',[LeadsController::class,'update']);
Route::delete('/leads/{id}',[LeadsController::class,'destroy']);

use App\Http\Controllers\FormFieldController;

Route::get('/form-fields', [FormFieldController::class, 'getFormFields']);


