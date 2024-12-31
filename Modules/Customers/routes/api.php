<?php

use App\Http\Controllers\ActionController;
use App\Http\Controllers\AddHolidaysController;
use App\Http\Controllers\ApprovalPolicyController;
use App\Http\Controllers\ApprovalsController;
use App\Http\Controllers\EstimateController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FormFieldController;
use App\Http\Controllers\InvoicesController;
use App\Http\Controllers\LeadsController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TasksController;
use App\Http\Controllers\ClientsController;
use App\Http\Controllers\Profile2FieldController;
use App\Http\Controllers\Profile2TabController;
use App\Http\Controllers\ProjectsController;
use App\Http\Controllers\RolesController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomersController;
use App\Http\Controllers\CustomersInviteController;
use App\Http\Controllers\DepartmentsController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\EmployeesController;
use App\Http\Controllers\EmploymentTypesController;
use App\Http\Controllers\EquipmentsController;
use App\Http\Controllers\EquipmentsSharingController;
use App\Http\Controllers\PaymentsController;
use App\Http\Controllers\PipelinesController;
use App\Http\Controllers\EquipmentsSharingPolicyController;
use App\Http\Controllers\ExpensesController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ManageCategoriesController;
use App\Http\Controllers\MerchantsController;
use App\Http\Controllers\ProductCategoriesController;
use App\Http\Controllers\ProductTypesController;
use App\Http\Controllers\RecuringExpensesController;
use App\Http\Controllers\WarehousesController;
use App\Http\Controllers\VendorsController;
use App\Http\Controllers\Group2RsController;
use App\Http\Controllers\GroupRoleController;
use App\Http\Controllers\GroupsController;
use App\Http\Controllers\GrouptoGroupRelController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\OperationsController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\Profile2GlobalPermissionsController;
use App\Http\Controllers\Profile2StandardPermissionsController;
use App\Http\Controllers\ProfilesController;
use App\Http\Controllers\ProposalsController;
use App\Http\Controllers\ProposalTemplatesController;
use App\Http\Controllers\TagsController;
use App\Http\Controllers\TeamsController;
use App\Http\Controllers\SharingAccessController;
//use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\TeamTaskController;
use App\Http\Controllers\AttachmentsFolderController;
use App\Http\Controllers\AttachmentsFolderSeqController;
use App\Http\Controllers\AutomationTriggerController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\CandidatesController;
use App\Http\Controllers\ChartController;
use App\Http\Controllers\ChartReportController;
use App\Http\Controllers\CustomerChartsController;
use App\Http\Controllers\FieldController;
use App\Http\Controllers\HomeReportChartController;
use App\Http\Controllers\MailScannerFoldersController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReportDateFilterController;
use App\Http\Controllers\ReportFilterController;
use App\Http\Controllers\ReportFolderController;
use App\Http\Controllers\ReportGroupByColumnController;
use App\Http\Controllers\ReportModulesController;
use App\Http\Controllers\ReportShareGroupsController;
use App\Http\Controllers\ReportShareUsersController;
use App\Http\Controllers\ReportSharingController;
use App\Http\Controllers\ReportSortColController;
use App\Http\Controllers\ReportSummaryController;
use App\Http\Controllers\ReportTypeController;
use App\Http\Controllers\ScheduledReportsController;
use App\Http\Controllers\ScheduleReportsController;
use App\Http\Controllers\DetailReportController;
use App\Http\Controllers\EmailHistoryController;
use App\Http\Controllers\EmployeeLevelController;
use App\Http\Controllers\JoSelectcolumnController;
use App\Http\Controllers\OpportunityController;
use App\Http\Controllers\RelatedModulesController;
use App\Http\Controllers\RelCriteriaController;
use App\Http\Controllers\RelCriteriaGroupingController;
use App\Http\Controllers\ReportShareRolesController;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\TabsController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\TemplateHistoryController;
use App\Http\Controllers\TriggerController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WorkflowController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\WorkflowFieldController;

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


Route::get('/customer',[CustomersController::class,'index']);
Route::post('/customer',[CustomersController::class,'store']);


Route::get('tasks',[TasksController::class,'index']);
Route::post('tasks',[TasksController::class,'store']);

Route::get('employees',[EmployeesController::class,'index']);
Route::post('employees',[EmployeesController::class,'store']);


Route::get('projects',[ProjectsController::class,'index']);
Route::post('projects',[ProjectsController::class,'store']);


Route::get('expenses',[ExpensesController::class,'index']);
Route::post('expenses',[ExpensesController::class,'store']);

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

//download PDF
//Route::get('invoice/{id}/download', [InvoicesController::class, 'downloadInvoice']);

// CRUD routes for invoices
Route::post('invoice', [InvoicesController::class, 'store']);
Route::get('invoice', [InvoicesController::class, 'index']);
Route::get('/invoices/search', [InvoicesController::class, 'search']);
Route::put('invoice/{id}', [InvoicesController::class, 'update']);
Route::get('invoice/{id}', [InvoicesController::class, 'show']);
Route::delete('invoice/{id}', [InvoicesController::class, 'destroy']);


// Download PDF for specific invoice
Route::get('invoice/{id}/download', [InvoicesController::class, 'downloadInvoice'])->name('invoice.download');

// Additional routes for fetching data
Route::get('invoices', [InvoicesController::class, 'fetchData']);
Route::get('invoices/{type}/{value}', [InvoicesController::class, 'getDetails']);

// Post function modules for invoices
Route::post('invoices/{invoiceid}/tasks', [InvoicesController::class, 'addTasks']);
Route::post('invoices/{invoiceId}/product', [InvoicesController::class, 'addProducts']);
Route::post('invoices/{invoiceId}/employee', [InvoicesController::class, 'addEmpProducts']);
Route::post('invoices/{invoiceid}/projects', [InvoicesController::class, 'addProjects']);
Route::post('invoices/{invoiceid}/expenses', [InvoicesController::class, 'addExpenses']);

// Get function modules for invoices
Route::get('tasks/{id}', [InvoicesController::class, 'getTasks']);
Route::get('invoices/{id}', [InvoicesController::class, 'getProductDetails']); // Products
Route::get('employees/{id}', [InvoicesController::class, 'getEmpProducts']);
Route::get('projects/{id}', [InvoicesController::class, 'getProjects']);
Route::get('expenses/{id}', [InvoicesController::class, 'getExpenses']);



Route::get('organizations',[OrganizationController::class,'index']);
Route::post('organizations',[OrganizationController::class,'store']);


Route::post('estimates',[EstimateController::class,'store']);
Route::get('estimates',[EstimateController::class,'index']);
Route::post('/estimates/search', [EstimateController::class, 'search']);
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



//Customers Routes
Route::get('/customers',[CustomersController::class,'index']);
Route::post('/customers',[CustomersController::class,'store']);
Route::get('/customers/{id}', [CustomersController::class, 'show']);
Route::put('/customers/{id}', [CustomersController::class, 'update']);
Route::delete('/customers/{id}', [CustomersController::class, 'destroy']);
Route::post('customers/search', [CustomersController::class, 'search']);
use App\Http\Controllers\ContactController;
use App\Http\Controllers\EmailEventController;
use App\Http\Controllers\EmailTrackingController;
use App\Http\Controllers\JoFieldController;
use App\Http\Controllers\TrackingController;

Route::post('/customers/{contactId}/tags/{tagId}', [CustomersController::class, 'addTag']);

Route::delete('customers/{contactId}/tags/{tagId}', [CustomersController::class, 'removeTag']);


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
Route::get('/recurring-expenses',[RecuringExpensesController::class,'index']);
Route::post('/recurring-expenses',[RecuringExpensesController::class,'store']);
Route::get('/recurring-expenses/{id}',[RecuringExpensesController::class,'show']);
Route::put('/recurring-expenses/{id}',[RecuringExpensesController::class,'update']);
Route::delete('/recurring-expenses/{id}',[RecuringExpensesController::class,'destroy']);

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
Route::post('/leads/search',[LeadsController::class,'search']);
Route::post('/leads',[LeadsController::class,'store']);
Route::get('/leads/{id}',[LeadsController::class,'show']);
Route::put('/leads/{id}',[LeadsController::class,'update']);
Route::delete('/leads/{id}',[LeadsController::class,'destroy']);

//FormField Routes
Route::get('/form-fields', [FormFieldController::class, 'getFormFields']);
//Route::get('form-fields', [FormFieldsController::class, 'getFormFields']);

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

//Tasks Routes
Route::get('/tasks',[TasksController::class,'index']);
Route::post('tasks',[TasksController::class,'store']);
Route::get('/tasks/{id}',[TasksController::class,'show']);
Route::put('tasks/{id}',[TasksController::class,'update']);
Route::delete('tasks/{id}',[TasksController::class,'destroy']);

//Employment Types Routes
Route::get('/employment-types',[EmploymentTypesController::class,'index']);
Route::post('/employment-types',[EmploymentTypesController::class,'store']);
Route::get('/employment-types/{id}',[EmploymentTypesController::class,'show']);
Route::put('/employment-types/{id}',[EmploymentTypesController::class,'update']);
Route::delete('/employment-types/{id}',[EmploymentTypesController::class,'destroy']);

//Proposal Templates Routes
Route::get('/proposal-templates',[ProposalTemplatesController::class,'index']);
Route::post('/proposal-templates',[ProposalTemplatesController::class,'store']);
Route::get('/proposal-templates/{id}',[ProposalTemplatesController::class,'show']);
Route::put('/proposal-templates/{id}',[ProposalTemplatesController::class,'update']);
Route::delete('/proposal-templates/{id}',[ProposalTemplatesController::class,'destroy']);

//Proposals Routes
Route::get('/proposals',[ProposalsController::class,'index']);
Route::get('/proposals/search', [ProposalsController::class, 'search']);
Route::post('/proposals',[ProposalsController::class,'store']);
Route::get('/proposals/{id}',[ProposalsController::class,'show']);
Route::put('/proposals/{id}',[ProposalsController::class,'update']);
Route::delete('/proposals/{id}',[ProposalsController::class,'destroy']);

//Departments Routes
Route::get('/departments',[DepartmentsController::class,'index']);
Route::get('/departments/search',[DepartmentsController::class,'search']);
Route::post('/departments',[DepartmentsController::class,'store']);
Route::get('/departments/{id}',[DepartmentsController::class,'show']);
Route::put('/departments/{id}',[DepartmentsController::class,'update']);
Route::delete('/departments/{id}',[DepartmentsController::class,'destroy']);

//Documents Routes 
Route::get('/documents',[DocumentController::class,'index']);
Route::get('/documents/search',[DocumentController::class,'search']);
Route::post('/documents',[DocumentController::class,'store']);
Route::get('/documents/{id}',[DocumentController::class,'show']);
Route::put('/documents/{id}',[DocumentController::class,'update']);
Route::delete('/documents/{id}',[DocumentController::class,'destroy']);

//Incomes Routes
Route::get('/incomes',[IncomeController::class,'index']);
Route::get('/incomes/search',[IncomeController::class,'search']);
Route::post('/incomes',[IncomeController::class,'store']);
Route::get('/incomes/{id}',[IncomeController::class,'show']);
Route::put('/incomes/{id}',[IncomeController::class,'update']);
Route::delete('/incomes/{id}',[IncomeController::class,'destroy']);

//Organization Routes
Route::get('/organization',[OrganizationController::class,'index']);
Route::get('/organization/search',[OrganizationController::class,'search']);
Route::post('/organization',[OrganizationController::class,'store']);
Route::get('/organization/{id}',[OrganizationController::class,'show']);
Route::put('/organization/{id}',[OrganizationController::class,'update']);
Route::delete('/organization/{id}',[OrganizationController::class,'destroy']);

//TeamTask Routes
Route::get('/teamtasks', [TeamTaskController::class, 'index']);
Route::post('/teamtasks', [TeamTaskController::class, 'store']);
Route::get('/teamtasks/search', [TeamTaskController::class, 'search']);
Route::put('/teamtasks/{id}', [TeamTaskController::class, 'update']);
Route::get('/teamtasks/{id}', [TeamTaskController::class, 'show']);
Route::delete('/teamtasks/{id}', [TeamTaskController::class, 'destroy']);

//FOLDERTABLE ROUTE:
//ATTACHMENTSFOLDER:

Route::get('/foldertable',[AttachmentsFolderController::class,'index']);
Route::post('/foldertable',[AttachmentsFolderController::class,'store']);
Route::get('/foldertable{id}',[AttachmentsFolderController::class,'show']);
Route::put('/folder{id}',[AttachmentsFolderController:: class ,'update']);
Route::delete('/foldertable{id}',[AttachmentsFolderController:: class, 'destroy']);


//ATTACHMENTSFOLDERSEQ:

Route::get('/attachmentsfolderseq',[AttachmentsFolderSeqController::class,'index']);
Route::post('/attachmentsfolderseq',[AttachmentsFolderSeqController::class,'store']);
Route::get('/attachmentsfolderseq{id}',[AttachmentsFolderSeqController::class,'show']);
Route::put('/attachmentsfolderseq{id}',[AttachmentsFolderSeqController:: class ,'update']);
Route::delete('/attachmentsfolderseq{id}',[AttachmentsFolderSeqController:: class, 'destroy']);

//MAILSCANNERFOLDERS:

Route::get('/mailscannerfolders',[MailScannerFoldersController::class,'index']);
Route::post('/mailscannerfolders',[MailScannerFoldersController::class,'store']);
Route::get('/mailscannerfolders/{id}',[MailScannerFoldersController::class,'show']);
Route::put('/mailscannerfolders/{id}',[MailScannerFoldersController:: class ,'update']);
Route::delete('/mailscannerfolders/{id}',[MailScannerFoldersController:: class, 'destroy']);


//REPORT FOLDER:
Route::get('/reportfolder',[ReportFolderController::class,'index']);
Route::post('/reportfolder',[ReportFolderController::class,'store']);
Route::get('/reportfolder{id}',[ReportFolderController::class,'show']);
Route::put('/reportfolder{id}',[ReportFolderController::class,'update']);
Route::delete('/reportfolder{id}',[ReportFolderController::class,'destroy']);

// {
//     "folderid": 1,
//     "foldername": "Organization and Contact Reports",
//     "description": "This is a sample folder description.",
//     "state": "Active"
// }
// {
//     "folderid": 12,
//     "foldername":"Email Report",
//     "description": "Email Reports .",
//     "state": "SAVED"
// }



// *********************************************************************************************************************

//REPORT TABLE:
//REPORT:


Route::get('/joreports', [ReportController::class, 'index']); // Index page for reports
Route::post('/joreports', [ReportController::class, 'store']);
Route::get('/joreports/{id}',[ReportController::class, 'show']); // Show a specific report
Route::put('/joreports/{id}',[ReportController::class, 'update']); // Update a specific report
Route::delete('/joreports/{id}',[ReportController::class, 'destroy']);
Route::post('/save-report', [ReportController::class, 'select'])->name('save.report');



// {
//     "reportid": 1,
//     "folderid": 12345,
//     "reportname": "Monthly Sales Report",
//     "description": "A detailed monthly sales report.",
//     "reporttype": "Sales",
//     "queryid": 67890,
//     "state": "active",
//     "customizable": 1,
//     "category": 2,
//     "owner": 3,
//     "sharingtype": "public"
// }


//REPORTSHAREGROUPS:

Route::get('/reportsharegroups',[ReportShareGroupsController::class,'index']);
Route::post('/reportsharegroups',[ReportShareGroupsController::class,'store']);
Route::get('/reportsharegroups/{id}',[ReportShareGroupsController::class,'show']);
Route::put('/reportsharegroups/{id}',[ReportShareGroupsController::class,'update']);
Route::delete('/reportsharegroups/{id}',[ReportShareGroupsController::class,'destroy']);


//REPORTSHAREUSERS:

Route::get('/reportshareusers',[ReportShareUsersController::class,'index']);
Route::post('/reportshareusers',[ReportShareUsersController::class,'store']);
Route::get('/reportshareusers{id}',[ReportShareUsersController::class,'show']);
Route::put('/reportshareusers{id}',[ReportShareUsersController::class,'update']);
Route::delete('/reportshareusers{id}',[ReportShareUsersController::class,'destroy']);


//REPORTDATEFILTER:

Route::get('/reportdatefilter', [ReportDateFilterController::class, 'index']);
Route::post('/reportdatefilter', [ReportDateFilterController::class, 'store']);
Route::get('/reportdatefilter/{id}', [ReportDateFilterController::class, 'show']);
Route::put('/reportdatefilter/{id}', [ReportDateFilterController::class, 'update']);
Route::delete('/reportdatefilter/{id}', [ReportDateFilterController::class, 'destroy']);

//REPORTFILTER

Route::get('/reportfilter', [ReportFilterController::class, 'index']);
Route::post('/reportfilter', [ReportFilterController::class, 'store']);
Route::get('/reportfilter/{id}', [ReportFilterController::class, 'show']);
Route::put('/reportfilter/{id}', [ReportFilterController::class, 'update']);
Route::delete('/reportfilter/{id}', [ReportFilterController::class, 'destroy']);


//REPORTGROUPBYCOLUMN

Route::get('/reportgroupbycolumn',[ReportGroupByColumnController::class,'index']);
Route::post('/reportgroupbycolumn',[ReportGroupByColumnController::class,'store']);
Route::get('/reportgroupbycolumn/{id}',[ReportGroupByColumnController::class,'show']);
Route::put('/reportgroupbycolumn/{id}',[ReportGroupByColumnController::class,'update']);
Route::delete('/reportgroupbycolumn/{id}',[ReportGroupByColumnController::class,'destroy']);

//REPORTMODULES:

Route::get('/reportmodules',[ReportModulesController::class,'index']);
Route::post('/reportmodules',[ReportModulesController::class,'store']);
Route::get('/reportmodules/{id}',[ReportModulesController::class,'show']);
Route::put('/reportmodules/{id}',[ReportModulesController::class,'update']);
Route::delete('/reportmodules/{id}',[ReportModulesController::class,'destroy']);

// {
//     "reportmodulesid": 1,
//     "primarymodule": "Sales Reports",
//     "secondarymodules": "Marketing Reports"
// }


//REPORTSHARING:

Route::get('/reportsharing',[ReportSharingController::class,'index']);
Route::post('/reportsharing',[ReportSharingController::class,'store']);
Route::get('/reportsharing/{id}',[ReportSharingController::class,'show']);
Route::put('/reportsharing/{id}',[ReportSharingController::class,'update']);
Route::delete('/reportsharing/{id}',[ReportSharingController::class,'destroy']);

//REPORTSORTCOL:

Route::get('/reportsortcol',[ReportSortColController::class,'index']);
Route::post('/reportsortcol',[ReportSortColController::class,'store']);
Route::get('/reportsortcol/{id}',[ReportSortColController::class,'show']);
Route::put('/reportsortcol/{id}',[ReportSortColController::class,'update']);
Route::delete('/reportsortcol/{id}',[ReportSortColController::class,'destroy']);


//REPORTSUMMARY:

Route::get('/reportsummary',[ReportSummaryController::class,'index']);
Route::post('/reportsummary',[ReportSummaryController::class,'store']);
Route::get('/reportsummary/{id}',[ReportSummaryController::class,'show']);
Route::put('/reportsummary/{id}',[ReportSummaryController::class,'update']);
Route::delete('/reportsummary/{id}',[ReportSummaryController::class,'destroy']);

//REPORTTYPE:

Route::get('/reporttype',[ReportTypeController::class,'index']);
Route::post('/reporttype',[ReportTypeController::class,'store']);
Route::get('/reporttype/{id}',[ReportTypeController::class,'show']);
Route::put('/reporttype/{id}',[ReportTypeController::class,'update']);
Route::delete('/reporttype/{id}',[ReportTypeController::class,'destroy']);

//SCHEDULEDREPORTS:

Route::get('/scheduledreports',[ScheduledReportsController::class,'index']);
Route::post('/scheduledreports',[ScheduledReportsController::class,'store']);
Route::get('/scheduledreports/{id}',[ScheduledReportsController::class,'show']);
Route::put('/scheduledreports/{id}',[ScheduledReportsController::class,'update']);
Route::delete('/scheduledreports{id}',[ScheduledReportsController::class,'destroy']);


//SCHEDULEREPORTS:

Route::get('/schedulereports',[ScheduleReportsController::class,'index']);
Route::post('/schedulereports',[ScheduleReportsController::class,'store']);
Route::get('/schedulereports/{id}',[ScheduleReportsController::class,'show']);
Route::put('/schedulereports/{id}',[ScheduleReportsController::class,'update']);
Route::delete('/schedulereports{id}',[ScheduleReportsController::class,'destroy']);


// {
//     "reportid": 1,
//     "scheduleid": 1,
//     "recipients": [
//         {
//             "Users": "john_doe", // Ensure this exists in the 'users' table
//             "Roles": "Organization",    // Ensure this exists in the 'jo_roles' table
//             "Groups": "Marketing Group" // Ensure this exists in the 'jo_groups' table
//         }
//     ],
//     "schdate": "2024-07-15",
//     "schtime": "08:00 AM",
//     "schdayoftheweek": "Monday",
//     "schdayofthemonth": "15th",
//     "schannualdates": "January 1st",
//     "specificemails": "john@example.com, jane@example.com",
//     "next_trigger_time": "2024-07-15 08:00 AM",
//     "fileformat": "PDF"
// }


//HOMEREPORTCHART:

Route::get('/homereportchart',[HomeReportChartController::class,'index']);
Route::post('/homereportchart',[HomeReportChartController::class,'store']);
Route::get('/homereportchart/{id}',[HomeReportChartController::class,'show']);
Route::put('/homereportchart/{id}',[HomeReportChartController::class,'update']);
Route::delete('/homereportchart/{id}',[HomeReportChartController::class,'destroy']);

//GROUPSCONTROLLER
Route::get('/groups',[GroupsController::class,'index']);
Route::post('/groups',[GroupsController::class,'store']);
Route::get('/groups/search', [GroupsController::class, 'search']);
Route::get('/groups/{id}',[GroupsController::class,'show']);
Route::put('/groups/{id}',[GroupsController::class,'update']);
Route::delete('/groups/{id}',[GroupsController::class,'destroy']);


// {
//     "group_name": "Team Selling",
//     "description": "This is a sample group",
//     "group_members": [
//         {
//             "Users": 2,
//             "Roles": 1
//         }
//     ]
// }


//ROLES CONTROLLER
Route::get('/roles', [RolesController::class, 'index']);
Route::post('/roles', [RolesController::class, 'store']);
Route::get('/roles/{id}', [RolesController::class, 'show']);
Route::put('/roles/{id}', [RolesController::class, 'update']);
Route::delete('/roles/{id}', [RolesController::class, 'destroy']);

// {
//     "roleid": "new_role_id",
//     "rolename": "New Role",
//     "parentrole": null,
//     "allowassignedrecordsto": 1
// }


//USER CONTROLLER
Route::get('/users', [UserController::class, 'index']);
Route::post('/users', [UserController::class, 'store']);
Route::get('/users/{id}', [UserController::class, 'show']);
Route::put('/users/{id}', [UserController::class, 'update']);
Route::delete('/users/{id}', [UserController::class, 'destroy']);

// {
//     "name": "John Doe",
//     "email": "john.doe@example.com",
//     "password": "secret123",
//     "role": "ADMIN",
//     "imageurl": "https://example.com/avatar.jpg",
//     "first_name": "John",
//     "last_name": "Doe",
//     "applied_date": "2024-07-17",
//     "rejection_date": null
// }



//CUSTOMERCONTROLLER:
//EXCEL SHEET CONTROLLER:

Route::get('/customers',[CustomersController::class,'index']);
Route::post('/customers',[CustomersController::class,'store']);
Route::get('/customers/{id}', [CustomersController::class, 'show']);
Route::put('/customers/{id}', [CustomersController::class, 'update']);
Route::delete('/customers/{id}', [CustomersController::class, 'destroy']);
Route::post('/customers/search', [CustomersController::class, 'searched']);

//FORMFIELD:
Route::get('/show-tabid', [FormFieldController::class, 'showTabid']);

// http://127.0.0.1:8000/api/show-tabid?module=Customers


//TABSID
Route::get('/show', [TabsController::class, 'showTabid']);


//REPORT GENERATE FIELD ID:
Route::post('/reports', [ReportController::class, 'createReport']);
Route::get('/reports/{id}/generate', [ReportController::class, 'generateReport']);


//FIELD CONTROLLER:
Route::get('/fields/{id}', [FieldController::class, 'getFieldById']);
Route::get('/fields/{id}/customers/filter/{filterId}', [FieldController::class, 'getCustomersByField']);
Route::get('/fields/customers', [FieldController::class, 'getCustomersByField']);
Route::get('/field/{fieldId}', [FieldController::class, 'show']);


//CHART TYPE AND REQUEST CONTROLLER:

Route::post('/customers/chart-data', [CustomerChartsController::class, 'search']);

// {
//     "requests": [
//         {
//             "field_name": "primary_email",
//             "field_value": "s",
//             "condition": "starts_with",
//             "chart_type": "pie"
//         },
//         {
//             "field_name": "primary_phone",
//             "field_value": "8",
//             "condition": "starts_with",
//             "chart_type": "pie"
//         },
//         {
//             "field_name": "name",
//             "field_value": "s",
//             "condition": "starts_with",
//             "chart_type": "pie"
//         }
//     ],
//     "group_by_field": "name"
// }


//FINAL REPORT
Route::get('/report-index', [DetailReportController::class, 'index']);
Route::post('/report-create', [DetailReportController::class, 'stores']);
Route::get('/report-retrieve/{id}', [DetailReportController::class, 'shows']);
Route::put('/report-edit/{id}', [DetailReportController::class, 'updates']);
Route::delete('/report/delete/{id}', [DetailReportController::class, 'destroys']);
Route::get('/export-report/{reportId}', [DetailReportController::class, 'exportToExcel']);

Route::get('/report/chart/index', [ChartController::class, 'index']);
Route::post('/report/chart/create', [ChartController::class, 'store']);
Route::get('/report/chart/retrieve/{id}', [ChartController::class, 'show']);
Route::put('/report/chart/edit/{id}', [ChartController::class, 'update']);
Route::delete('/report/chart/delete/{id}', [ChartController::class, 'destroy']);



Route::get('/test-route', [ReportController::class, 'testRoute']);


//TEAM TASK

Route::get('/teamtasks', [TeamtaskController::class, 'index']);
Route::post('/teamtasks', [TeamtaskController::class, 'store']);
Route::get('/teamtasks/search', [TeamtaskController::class, 'search']);
Route::put('/teamtasks/{id}', [TeamtaskController::class, 'update']);
Route::get('/teamtasks/{id}', [TeamtaskController::class, 'show']);
Route::delete('/teamtasks/{id}', [TeamtaskController::class, 'destroy']);

//     {
    //     "tasknumber": 6,
    //     "projects": "Project 4",
    //     "status": "Completed",
    //     "teams": "Team B",
    //     "title": "Fix Bug Y",
    //     "priority": "Medium",
    //     "size": "Small",
    //     "tags": "Bug,Backend",
    //     "duedate": "2024-07-20",
    //     "estimate": 5,
    //     "description": "Detailed description of the bug fix"
    // }

//REL CRITERIA :



Route::get('/relcriteria', [RelCriteriaController::class, 'index']);
Route::post('/relcriteria', [RelCriteriaController::class, 'store']);
Route::get('/relcriteria/{id}', [RelCriteriaController::class, 'show']);
Route::put('/relcriteria/{id}', [RelCriteriaController::class, 'update']);
Route::delete('/relcriteria/{id}', [RelCriteriaController::class, 'destroy']);

// {
//     "queryid": 1,
//     "columnindex": 1,
//     "columnname": "example_column",
//     "comparator": "equals",
//     "value": "example_value",
//     "groupid": 1,
//     "column_condition": "and"
// }


//REL CRITERIA GROUPING:



Route::get('/relcriteria_grouping', [RelCriteriaGroupingController::class, 'index']);
Route::post('/relcriteria_grouping', [RelCriteriaGroupingController::class, 'store']);
Route::get('/relcriteria_grouping/{id}', [RelCriteriaGroupingController::class, 'show']);
Route::put('/relcriteria_grouping/{id}', [RelCriteriaGroupingController::class, 'update']);
Route::delete('/relcriteria_grouping/{id}', [RelCriteriaGroupingController::class, 'destroy']);


// {
//     "groupid": 1,
//     "queryid": 1,
//     "group_condition": "AND",
//     "condition_expression": "expression"
// }

//SELECT COLUMN

Route::get('/jo_selectcolumns', [JoSelectcolumnController::class, 'index']);
Route::post('/jo_selectcolumns', [JoSelectcolumnController::class, 'store']);
Route::get('/jo_selectcolumns/{id}', [JoSelectcolumnController::class, 'show']);
Route::put('/jo_selectcolumns/{id}', [JoSelectcolumnController::class, 'update']);
Route::delete('/jo_selectcolumns/{id}', [JoSelectcolumnController::class, 'destroy']);

// {
//     "queryid": 1,
//     "columnindex": 1,
//     "columnname": "example_column"
// }

//Export Routes
Route::get('/export/{id}', [DetailReportController::class, 'export']);

//Related Modules Routes
Route::get('/related-modules',[RelatedModulesController::class,'index']);
Route::post('/related-modules',[RelatedModulesController::class,'store']);
Route::put('/related-modules/{id}',[RelatedModulesController::class,'update']);
Route::get('/related-modules/{id}',[RelatedModulesController::class,'show']);
Route::delete('/related-modules/{id}',[RelatedModulesController::class,'destroy']);

//Approval Policy Routes
Route::get('/approval-policy',[ApprovalPolicyController::class,'index']);
Route::post('/approval-policy',[ApprovalPolicyController::class,'store']);
Route::put('/approval-policy/{id}',[ApprovalPolicyController::class,'update']);
Route::get('/approval-policy/{id}',[ApprovalPolicyController::class,'show']);
Route::delete('/approval-policy/{id}',[ApprovalPolicyController::class,'destroy']);

//Report Share Roles Routes
Route::get('/report-shareroles',[ReportShareRolesController::class,'index']);
Route::post('/report-shareroles',[ReportShareRolesController::class,'store']);
Route::put('/report-shareroles/{id}',[ReportShareRolesController::class,'update']);
Route::get('/report-shareroles/{id}',[ReportShareRolesController::class,'show']);
Route::delete('/report-shareroles/{id}',[ReportShareRolesController::class,'destroy']);

//Chart Report Routes
Route::get('/chart-report',[ChartReportController::class,'index']);
Route::post('/chart-report',[ChartReportController::class,'store']);
Route::get('/chart-report/{id}',[ChartReportController::class,'show']);
Route::put('/chart-report/{id}',[ChartReportController::class,'update']);
Route::delete('/chart-report/{id}',[ChartReportController::class,'destroy']);

//Approvals Routes
Route::get('/approvals',[ApprovalsController::class,'index']);
Route::post('/approvals',[ApprovalsController::class,'store']);
Route::put('/approvals/{id}',[ApprovalsController::class,'update']);
Route::get('/approvals/{id}',[ApprovalsController::class,'show']);

//Employee Level Routes
Route::get('/employee-levels',[EmployeeLevelController::class,'index']);
Route::post('/employee-levels',[EmployeeLevelController::class,'store']);
Route::get('/employee-levels/{id}',[EmployeeLevelController::class,'show']);
Route::put('/employee-levels/{id}',[EmployeeLevelController::class,'update']);
Route::delete('/employee-levels/{id}',[EmployeeLevelController::class,'destroy']);

//Candidates Routes
Route::get('/candidates',[CandidatesController::class,'index']);
Route::post('/candidates',[CandidatesController::class,'store']);
Route::get('/candidates/{id}',[CandidatesController::class,'show']);
Route::put('/candidates/{id}',[CandidatesController::class,'update']);
Route::delete('/candidates/{id}',[CandidatesController::class,'destroy']);

//SMS Routes
Route::get('/send-sms/{customerId}', [SmsController::class, 'sendSmsToCustomer']);

//Opportunity Routes
Route::get('/opportunity',[OpportunityController::class,'index']);
Route::post('/opportunity',[OpportunityController::class,'store']);
Route::get('/opportunity/{id}',[OpportunityController::class,'show']);
Route::put('/opportunity/{id}',[OpportunityController::class,'update']);
Route::delete('/opportunity/{id}',[OpportunityController::class,'destroy']);

//Trigger Routes
Route::get('/triggers', [TriggerController::class, 'index']); // Fetch all triggers
Route::post('/triggers', [TriggerController::class, 'store']); // Create a new trigger
Route::get('/triggers/{id}', [TriggerController::class, 'show']); // Show a specific trigger
Route::put('/triggers/{id}', [TriggerController::class, 'update']); // Update a specific trigger
Route::delete('/triggers/{id}', [TriggerController::class, 'destroy']); // Delete a specific trigger

//Actions Routes
Route::get('/actions', [ActionController::class, 'index']);
Route::post('/actions', [ActionController::class, 'store']);
Route::get('/actions/{id}', [ActionController::class, 'show'])->name('actions.show');
Route::put('/actions/{id}', [ActionController::class, 'update'])->name('actions.update');
Route::delete('/actions/{id}', [ActionController::class, 'destroy'])->name('actions.destroy');

//Add Workflow Routes
Route::get('/workflows', [WorkflowController::class, 'index']);
Route::post('/workflows', [WorkflowController::class, 'store']);
Route::get('/workflows/{id}', [WorkflowController::class, 'show']);
Route::put('/workflows/{id}', [WorkflowController::class, 'update']);
Route::delete('/workflows/{id}', [WorkflowController::class, 'destroy']);

//Template Routes
Route::get('/template',[TemplateController::class,'index']);
Route::post('/template',[TemplateController::class,'store']);
Route::get('/template/{id}',[TemplateController::class,'show']);
Route::put('/template/{id}',[TemplateController::class,'update']);
Route::delete('/template/{id}',[TemplateController::class,'destroy']);

//campaign Routes
Route::get('/campaigns',[CampaignController::class,'index']);
Route::post('/campaigns',[CampaignController::class,'store']);
Route::get('/campaigns/{id}',[CampaignController::class,'show']);
Route::put('/campaigns/{id}',[CampaignController::class,'update']);
Route::delete('/campaigns/{id}',[CampaignController::class,'destroy']);

// Route to send a campaign (send_now, schedule, batch_schedule)
Route::post('/campaigns/send', [CampaignController::class, 'send']);

//view history
Route::get('/template-histories', [TemplateHistoryController::class, 'index']);
Route::get('/email-history', [EmailHistoryController::class, 'showHistory']);

Route::post('/email-event-webhook', [EmailEventController::class, 'handleWebhook']);

// routes/web.php

Route::get('/track_open', [TrackingController::class, 'trackOpen']);
Route::get('/track_click', [TrackingController::class, 'trackClick']);

//Workflow formfield
Route::get('/formfields', [JoFieldController::class, 'getFormFields']);


use App\Http\Controllers\AdminController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\InviteCandidatesController;
use App\Http\Controllers\PositionsController;
use App\Http\Controllers\RequestTimeOffController;

Route::get('/workflowactions/{action_name}', [AdminController::class, 'show'])->name('admin.show');
Route::get('/workflowactions', [AdminController::class, 'index']);

use App\Http\Controllers\WorkflowTriggersController;

Route::get('workflowtriggers/{id}', [WorkflowTriggersController::class, 'show']);
Route::get('workflowtriggers', [WorkflowTriggersController::class, 'index']);

//calendar Routes
Route::get('/calendar',[CalendarController::class,'index']);
Route::post('/calendar',[CalendarController::class,'store']);
Route::get('/calendar/{id}',[CalendarController::class,'show']);
Route::put('/calendar/{id}',[CalendarController::class,'update']);
Route::delete('/calendar/{id}',[CalendarController::class,'destroy']);


//Add holiday Routes
Route::get('/add-holiday',[AddHolidaysController::class,'index']);
Route::post('/add-holiday',[AddHolidaysController::class,'store']);
Route::get('/add-holiday/{id}',[AddHolidaysController::class,'show']);
Route::put('/add-holiday/{id}',[AddHolidaysController::class,'update']);
Route::delete('/add-holiday/{id}',[AddHolidaysController::class,'destroy']);

//Candidates Invite Routes
Route::get('/candidatesinvite',[InviteCandidatesController::class,'index']);
Route::post('/candidatesinvite',[InviteCandidatesController::class,'store']);
Route::get('/candidatesinvite/{id}',[InviteCandidatesController::class,'show']);
Route::put('/candidatesinvite/{id}',[InviteCandidatesController::class,'update']);
Route::delete('/candidatesinvite/{id}',[InviteCandidatesController::class,'destroy']);

//Positions Routes
Route::get('/positions',[PositionsController::class,'index']);
Route::post('/positions',[PositionsController::class,'store']);
Route::get('/positions/{id}',[PositionsController::class,'show']);
Route::put('/positions/{id}',[PositionsController::class,'update']);
Route::delete('/positions/{id}',[PositionsController::class,'destroy']);

//Request Timeoff Routes
Route::get('/request-timeoff',[RequestTimeOffController::class,'index']);
Route::post('/request-timeoff',[RequestTimeoffController::class,'store']);
Route::get('/request-timeoff/{id}',[RequestTimeoffController::class,'show']);
Route::put('/request-timeoff/{id}',[RequestTimeoffController::class,'update']);
Route::delete('/request-timeoff/{id}',[RequestTimeoffController::class,'destroy']);