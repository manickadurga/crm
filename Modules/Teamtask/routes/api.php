<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\ClientsController;
use App\Http\Controllers\inviteClientController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\TeamTaskController;
use App\Http\Controllers\BlocksController;
use App\Http\Controllers\QuickcreateController;
use App\Http\Controllers\FormFieldController;
use App\Http\Controllers\MenuController;
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
Route::get('/teamtasks', [TeamTaskController::class, 'index']);
Route::post('/teamtasks', [TeamTaskController::class, 'store']);
Route::get('/teamtasks/search', [TeamTaskController::class, 'search']);
Route::put('/teamtasks/{id}', [TeamTaskController::class, 'update']);
Route::get('/teamtasks/{id}', [TeamTaskController::class, 'show']);
Route::delete('/teamtasks/{id}', [TeamTaskController::class, 'destroy']);
Route::get('/formfields', [FormFieldController::class, 'getFormFields']);



Route::get('/incomes', [IncomeController::class, 'index']);
Route::post('/incomes', [IncomeController::class, 'store']);
Route::delete('/incomes/{id}', [IncomeController::class, 'destroy']);
Route::get('/incomes/{id}', [IncomeController::class, 'show']);
Route::put('/incomes/{id}', [IncomeController::class, 'updateincome']);
Route::get('/incomes', [IncomeController::class, 'index']);
Route::get('/income/search', [IncomeController::class, 'search']);

Route::post('/income/clients', [IncomeController::class, 'storeClients']);
//Route::post('/leads', [IncomeController::class, 'storeLeads']);
//Route::post('/customers', [IncomeController::class, 'storeCustomers']);
Route::post('/manageemployees', [IncomeController::class, 'storeManageEmployees']);

Route::get('/clients', [ClientsController::class,'index']);
Route::post('/clients', [ClientsController::class,'store']);
Route::delete('/jo-clients/{id}', [ClientsController::class,'destroy']);
Route::get('/jo-clients/{id}', [ClientsController::class,'show']);
Route::put('/jo-clients/{id}', [ClientsController::class,'update']);
Route::get('/clients/search', [ClientsController::class, 'search']);

Route::get('/joinvite-clients', [inviteClientController::class, 'index']);
Route::post('/joinvite-clients', [inviteClientController::class, 'store']);

Route::get('/organizations', [OrganizationController::class, 'index']);
// Route::get('/organizations/{id}', [OrganizationController::class, 'show']);
Route::post('/organizations', [OrganizationController::class, 'store']);
Route::get('/organizations/search', [OrganizationController::class, 'search']);

Route::put('/organizations/{id}', [OrganizationController::class, 'update']);
Route::delete('/organizations/{id}', [OrganizationController::class, 'destroy']);
Route::get('/organizations/{id}/details', [OrganizationController::class, 'employeeDetails']);
Route::get('/clients-data', [OrganizationController::class, 'getClientData']);
Route::post('/clients/{id}/store', [OrganizationController::class, 'storeClientData']);


Route::get('/blocks/{module_name}', [BlocksController::class, 'getBlocksByModule']);
Route::post('/blocks/{module_name}', [BlocksController::class, 'addBlock']);
Route::delete('/blocks/{blockid}', [BlocksController::class,'deleteBlock']);
Route::put('/blocks/{moduleName}/sequence', [BlocksController::class, 'updateBlockSequence']);

Route::get('/field/{moduleName}', [BlocksController::class, 'getFieldsByModule']);
Route::post('/field/{moduleName}', [BlocksController::class, 'addField']);
Route::get('list-modules-blocks-field', [BlocksController::class, 'listModulesBlocksFields']);
Route::delete('/field/{fieldId}', [BlocksController::class,'deleteField']);
Route::put('/field/{moduleName}/sequence', [BlocksController::class, 'updateFieldSequence']);

Route::post('/quickcreate/teamtask',[QuickcreateController::class,'quickCreateTeamTask']);
Route::post('/quickcreate/incomes',[QuickcreateController::class,'quickCreateIncome']);
Route::post('/quickcreate/documents',[QuickcreateController::class,'quickCreateDocument']);
Route::post('/quickcreate/proposals',[QuickcreateController::class,'quickCreateProposals']);
Route::post('/quickcreate/Teams',[QuickcreateController::class,'quickCreateTeam']);
Route::post('/quickcreate/proposaltem',[QuickcreateController::class,'quickCreateProposalTemplate']);
Route::post('/quickcreate/tasks',[QuickcreateController::class,'quickCreateTasks']);
Route::post('/quickcreate/invoices',[QuickcreateController::class,'quickCreateInvoices']);
Route::post('/quickcreate/estimates',[QuickcreateController::class,'quickCreateEstimates']);
Route::post('/quickcreate/equipments',[QuickcreateController::class,'quickCreateEquipments']);
Route::post('/quickcreate/customers',[QuickcreateController::class,'quickCreateCustomers']);
Route::post('/quickcreate/expense',[QuickcreateController::class,'quickCreateExpenses']);
Route::post('/quickcreate/recuringexpense',[QuickcreateController::class,'quickCreateRecuringExpenses']);
Route::post('/quickcreate/pipelines',[QuickcreateController::class,'quickCreatePipelines']);
Route::post('/quickcreate/product',[QuickcreateController::class,'quickCreateProducts']);
Route::post('/quickcreate/payments',[QuickcreateController::class,'quickCreatePayment']);
Route::post('/quickcreate/Organization',[QuickcreateController::class,'quickCreateOrganization']);
Route::post('/quickcreate/clients',[QuickcreateController::class,'quickCreateClient']);
Route::post('/quickcreate/projects',[QuickcreateController::class,'quickCreateProjects']);
Route::post('/quickcreate/employements',[QuickcreateController::class,'quickCreateEmployementstypes']);
Route::post('/quickcreate/vendors',[QuickcreateController::class,'quickCreateVendor']);
Route::post('/quickcreate/tags',[QuickcreateController::class,'quickCreateTags']);
Route::post('/quickcreate/employments',[QuickcreateController::class,'quickCreateEmployeeTypes']);
Route::post('/quickcreate/leads',[QuickcreateController::class,'quickCreateLeads']);
Route::post('/quickcreate/projects',[QuickcreateController::class,'quickCreateprojects']);


Route::get('/menuitems', [MenuController::class, 'getMenuItems']);





