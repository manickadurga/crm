<?php

use App\Http\Controllers\ClientsController;
use App\Http\Controllers\Profile2FieldController;
use App\Http\Controllers\Profile2TabController;
use App\Http\Controllers\ProjectsController;
use App\Http\Controllers\RolesController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomersController;
use App\Http\Controllers\CustomersInviteController;
use App\Http\Controllers\EmployeesController;
use App\Http\Controllers\EquipmentsController;
use App\Http\Controllers\EquipmentsSharingController;
use App\Http\Controllers\PaymentsController;
use App\Http\Controllers\PipelinesController;
use App\Http\Controllers\EquipmentsSharingPolicyController;
use App\Http\Controllers\ExpensesController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\LeadsController;
use App\Http\Controllers\ManageCategoriesController;
use App\Http\Controllers\MerchantsController;
use App\Http\Controllers\ProductCategoriesController;
use App\Http\Controllers\ProductTypesController;
use App\Http\Controllers\RecuringExpensesController;
use App\Http\Controllers\WarehousesController;
use App\Http\Controllers\VendorsController;
use App\Http\Controllers\FormFieldController;
use App\Http\Controllers\Group2RsController;
use App\Http\Controllers\GroupRoleController;
use App\Http\Controllers\GroupsController;
use App\Http\Controllers\GrouptoGroupRelController;
use App\Http\Controllers\OperationsController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\Profile2GlobalPermissionsController;
use App\Http\Controllers\Profile2StandardPermissionsController;
use App\Http\Controllers\ProfilesController;
use App\Http\Controllers\TagsController;
use App\Http\Controllers\TeamsController;
use App\Http\Controllers\SharingAccessController;
//use App\Http\Controllers\PurchaseController;


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
Route::post('customers/search', [CustomersController::class, 'search']);

//Payments Routes
Route::get('/payments',[PaymentsController::class,'index']);
Route::post('/payments',[PaymentsController::class,'store']);
Route::get('/payments/search',[PaymentsController::class,'search']);
Route::get('/payments/{id}', [PaymentsController::class, 'show']);
Route::put('payments/{id}', [PaymentsController::class,'update']);
Route::delete('/payments/{id}',[PaymentsController::class,'destroy']);

//Pipelines Routes
Route::get('/pipelines',[PipelinesController::class,'index']);
Route::post('/pipelines',[PipelinesController::class,'store']);
Route::get('/pipelines/search',[PipelinesController::class,'search']);
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
Route::get('/equipments/search', [EquipmentsController::class, 'search']);
Route::get('/equipments/{id}', [EquipmentsController::class, 'show']);
Route::put('/equipments/{id}',[EquipmentsController::class,'update']);
Route::delete('/equipments/{id}',[EquipmentsController::class,'destroy']);



//Equipments Sharing Routes
Route::get('/equipments-sharing', [EquipmentsSharingController::class, 'index']);
Route::post('/equipments-sharing', [EquipmentsSharingController::class, 'store']);
Route::get('/equipments-sharing/search', [EquipmentsSharingController::class, 'search']);
Route::get('/equipments-sharing/{id}', [EquipmentsSharingController::class, 'show']);
Route::put('/equipments-sharing/{id}', [EquipmentsSharingController::class, 'update']);
Route::delete('/equipments-sharing/{id}', [EquipmentsSharingController::class, 'destroy']);

//Equipments Sharing Policy Routes
Route::get('/equipments-sharing-policy', [EquipmentsSharingPolicyController::class, 'index']);
Route::post('/equipments-sharing-policy', [EquipmentsSharingPolicyController::class, 'store']);
Route::get('/equipments-sharing-policy/search', [EquipmentsSharingPolicyController::class, 'search']);
Route::get('/equipments-sharing-policy/{id}', [EquipmentsSharingPolicyController::class, 'show']);
Route::put('/equipments-sharing-policy/{id}', [EquipmentsSharingPolicyController::class, 'update']);
Route::delete('/equipments-sharing-policy/{id}', [EquipmentsSharingPolicyController::class, 'destroy']);

//Product Types Routes
Route::get('/product-types',[ProductTypesController::class,'index']);
Route::get('/product-types/search',[ProductTypesController::class,'search']);
Route::post('/product-types',[ProductTypesController::class,'store']);
Route::get('/product-types/{id}',[ProductTypesController::class,'show']);
Route::put('/product-types/{id}',[ProductTypesController::class,'update']);
Route::delete('/product-types/{id}',[ProductTypesController::class,'destroy']);
Route::get('product-types/{typeName}/products', [ProductTypesController::class, 'showProductsByType']);

//Product Categories Routes
Route::get('/product-categories',[ProductCategoriesController::class,'index']);
Route::post('/product-categories',[ProductCategoriesController::class,'store']);
Route::get('/product-categories/search',[ProductCategoriesController::class,'search']);
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

//Products Routes
Route::get('/products',[ProductsController::class,'index']);
Route::post('/products',[ProductsController::class,'store']);
Route::get('/products/search', [ProductsController::class, 'search']);
Route::get('/products/{id}',[ProductsController::class,'show']);
Route::put('/products/{id}',[ProductsController::class,'update']);
Route::delete('/products/{id}',[ProductsController::class,'destroy']);
Route::get('/products/{typeName}', [ProductsController::class, 'showByType']);



//Recuring Expenses Routes
Route::get('/recuring-expenses',[RecuringExpensesController::class,'index']);
Route::post('/recuring-expenses',[RecuringExpensesController::class,'store']);
Route::get('/recuring-expenses/{id}',[RecuringExpensesController::class,'show']);
Route::put('/recuring-expenses/{id}',[RecuringExpensesController::class,'update']);
Route::delete('/recuring-expenses/{id}',[RecuringExpensesController::class,'destroy']);

//Manage Categories Routes
Route::get('/manage-categories',[ManageCategoriesController::class,'index']);
Route::post('/manage-categories',[ManageCategoriesController::class,'store']);
Route::get('/manage-categories/search',[ManageCategoriesController::class,'search']);
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

//FormField Routes
Route::get('/form-fields', [FormFieldController::class, 'getFormFields']);

//Tags Routes
Route::get('/tags',[TagsController::class,'index']);
Route::post('/tags',[TagsController::class,'store']);
Route::get('/tags/{id}',[TagsController::class,'show']);
Route::put('/tags/{id}',[TagsController::class,'update']);
Route::delete('/tags/{id}',[TagsController::class,'destroy']);

//Teams Routes
Route::get('/teams',[TeamsController::class,'index']);
Route::post('/teams',[TeamsController::class,'store']);
Route::get('/teams/{id}',[TeamsController::class,'show']);
Route::put('/teams/{id}',[TeamsController::class,'update']);
Route::delete('/teams/{id}',[TeamsController::class,'destroy']);

//Projects Routes
Route::get('/projects',[ProjectsController::class,'index']);
Route::post('/projects',[ProjectsController::class,'store']);
Route::get('/projects/{id}',[ProjectsController::class,'show']);
Route::get('/projects/{id}',[ProjectsController::class,'update']);
Route::get('/projects/{id}',[ProjectsController::class,'destroy']);

//Expenses Routes
Route::get('/expenses',[ExpensesController::class,'index']);
Route::post('/expenses',[ExpensesController::class,'store']);
Route::get('/expenses/search',[ExpensesController::class,'search']);
Route::get('/expenses/{id}',[ExpensesController::class,'show']);
Route::put('/expenses/{id}',[ExpensesController::class,'update']);
Route::delete('/expenses/{id}',[ExpensesController::class,'destroy']);

//Groups Routes
Route::get('/groups',[GroupsController::class,'index']);
Route::post('/groups',[GroupsController::class,'store']);
Route::get('/groups/search', [GroupsController::class, 'search']);
Route::get('/groups/{id}',[GroupsController::class,'show']);
Route::put('/groups/{id}',[GroupsController::class,'update']);
Route::delete('/groups/{id}',[GroupsController::class,'destroy']);


//Roles Routes
Route::get('/roles', [RolesController::class, 'index']);
Route::post('/roles', [RolesController::class, 'store']);
Route::get('/roles/{id}', [RolesController::class, 'show']);
Route::put('/roles/{id}', [RolesController::class, 'update']);
Route::delete('/roles/{id}', [RolesController::class, 'destroy']);

//GroupRole Routes
Route::get('/group-role',[GroupRoleController::class,'index']);
Route::post('/group-role',[GroupRoleController::class,'store']);
Route::get('/group-role/{id}',[GroupRoleController::class,'show']);
Route::put('/group-role/{id}',[GroupRoleController::class,'update']);
Route::delete('/group-role/{id}',[GroupRoleController::class,'destroy']);

//GrouptoGroupRel Routes
Route::get('/group-to-group-rel',[GrouptoGroupRelController::class,'index']);
Route::post('/group-to-group-rel',[GrouptoGroupRelController::class,'store']);
Route::get('/group-to-group-rel/{id}',[GrouptoGroupRelController::class,'show']);
Route::put('/group-to-group-rel/{id}',[GrouptoGroupRelController::class,'update']);
Route::delete('/group-to-group-rel/{id}',[GrouptoGroupRelController::class,'destroy']);

//Group2Rs Routes
Route::get('/group2rs',[Group2RsController::class,'index']);
Route::post('/group2rs',[Group2RsController::class,'store']);
Route::get('/group2rs/{id}',[Group2RsController::class,'show']);
Route::put('/group2rs/{id}',[Group2RsController::class,'update']);
Route::delete('/group2rs/{id}',[Group2RsController::class,'destroy']);

//Profile Routes
Route::get('/profiles',[ProfilesController::class,'index']);
Route::post('/profiles',[ProfilesController::class,'store']);
Route::get('/profiles/{id}',[ProfilesController::class,'show']);
Route::put('/profiles/{id}',[ProfilesController::class,'update']);
Route::delete('/profiles/{id}',[ProfilesController::class,'destroy']);

//Operations Routes
Route::get('/operations',[OperationsController::class,'index']);
Route::post('/operations',[OperationsController::class,'store']);
Route::get('/operations/{id}',[OperationsController::class,'show']);
Route::put('/operations/{id}',[OperationsController::class,'update']);
Route::delete('operations/{id}',[OperationsController::class,'destroy']);

//Profile2StandardPermissions Routes
Route::get('/permissions', [Profile2StandardPermissionsController::class, 'index']);
Route::post('/permissions', [Profile2StandardPermissionsController::class, 'store']);
Route::get('/permissions/{id}', [Profile2StandardPermissionsController::class, 'show']);
Route::put('/permissions/{id}', [Profile2StandardPermissionsController::class, 'update']);
Route::delete('/permissions/{id}', [Profile2StandardPermissionsController::class, 'destroy']);
Route::put('/profile2std/{id}/permissions', [Profile2StandardPermissionsController::class, 'updatePermissions']);

//Profile2Fields Routes
Route::get('/profile2field', [Profile2FieldController::class, 'index']);
Route::post('/profile2field', [Profile2FieldController::class, 'store']);
Route::get('/profile2field/{id}', [Profile2FieldController::class, 'show']);
Route::put('/profile2field/{id}', [Profile2FieldController::class, 'update']);
Route::delete('/profile2field/{id}', [Profile2FieldController::class, 'destroy']);

//Profile2Tab Routes
Route::get('/profile2tabs', [Profile2TabController::class, 'index']);
Route::post('/profile2tabs', [Profile2TabController::class, 'store']);
Route::get('/profile2tabs/{id}', [Profile2TabController::class, 'show']);
Route::put('/profile2tabs/{id}', [Profile2TabController::class, 'update']);
Route::delete('/profile2tabs/{id}', [Profile2TabController::class, 'destroy']);
Route::put('/profile2tab/{id}/permissions', [Profile2TabController::class, 'updatePermissions']);

//Profile2GlobalPermissions Routes
Route::get('/profile2globalpermissions',[Profile2GlobalPermissionsController::class,'index']);
Route::post('/profile2globalpermissions',[Profile2GlobalPermissionsController::class,'store']);
Route::get('/profile2globalpermissions/{id}',[Profile2GlobalPermissionsController::class,'show']);
Route::put('/profile2globalpermissions/{id}',[Profile2GlobalPermissionsController::class,'update']);
Route::delete('/profile2globalpermissions/{id}',[Profile2GlobalPermissionsController::class,'destroy']);

//Sharing Access Routes
    Route::get('/sharing-access', [SharingAccessController::class, 'index']);
    Route::post('/sharing-access', [SharingAccessController::class, 'store']);
    Route::get('/sharing-access/search',[SharingAccessController::class,'search']);
    Route::get('/sharing-access/{id}', [SharingAccessController::class, 'show']);
    Route::put('/sharing-access/{id}', [SharingAccessController::class, 'update']);
    Route::delete('/sharing-access/{id}', [SharingAccessController::class, 'destroy']);
    //Route::get('/sharing-access/{id}/customers', [SharingAccessController::class, 'getCustomers']);
   // Route::get('/customers/access/{id}', [SharingAccessController::class, 'getCustomersBySharingAccess']);
   //Route::get('/sharing-access/{sharingAccessId}/customers', [SharingAccessController::class, 'getCustomersBySharingAccessId']);
   Route::get('/sharing-access/customers/{sharingAccessId}',[SharingAccessController::class,'getCustomersBySharingAccessId']);
   //Route::post('/sharing-access/{sharingAccessId}/update-customers', [SharingAccessController::class, 'updateCustomersData']);
   Route::post('/sharing-access/{sharingAccessId}/update-customers',[SharingAccessController::class,'updateCustomersData']);





