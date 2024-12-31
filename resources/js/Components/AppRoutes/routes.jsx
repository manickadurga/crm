import React from "react";

import Dashboard from "../../Pages/Dashboard";

// import Customers from "../../Pages/Customers";
import Customers from "../../Pages/Contacts/Customers/index";
import CustomerForm from "../../Pages/Contacts/Customers/form";
import CustomerEditForm from "../../Pages/Contacts/Customers/form";
import CustomerView from "../../Pages/Contacts/Customers/view";

import Clients from "../../Pages/Contacts/Clients/index";
import ClientsForm from "../../Pages/Contacts/Clients/form";
import ClientsEditForm from "../../Pages/Contacts/Clients/form";
import ClientView from "../../Pages/Contacts/Clients/view";
// import ClientView from "../../Pages/Contacts/Clients/view";


import Proposals from "../../Pages/Proposals/index";
import ProposalsForm from "../../Pages/Proposals/form";


import Invoices from "../../Pages/Invoices/index";
import InvoicesForm from "../../Pages/Invoices/form";
import InvoicesEditForm from "../../Pages/Invoices/form";
import InvoiceView from "../../Pages/Invoices/view";
// import Payment from "../../Pages/Invoices/payment";


import Estimates from "../../Pages/Accounting/Estimates/index";
import EstimatesForm from "../../Pages/Accounting/Estimates/form";

import Tasks from "../../Pages/Tasks/index";
import TasksForm from "../../Pages/Tasks/form";

import TeamsTasks from "../../Pages/TeamsTasks/index";
import TeamsTasksForm from "../../Pages/TeamsTasks/form";
import TeamsTasksEditForm from "../../Pages/TeamsTasks/form";


import Leads from "../../Pages/Contacts/Leads/index";
import LeadsForm from "../../Pages/Contacts/Leads/form";
import LeadsEditForm from "../../Pages/Contacts/Leads/form";
import LeadView from "../../Pages/Contacts/Leads/view";
// import TeamsTasks from "../../Pages/TeamsTasks";
// import TeamsTasksForm from "../../Pages/TeamsTasks/form";


import Income from "../../Pages/Accounting/Income/index";
import IncomeForm from "../../Pages/Accounting/Income/form";
import IncomeEditForm from "../../Pages/Accounting/Income/form";

import Pipelines from "../../Pages/Sales/Pipelines/index";
import PipelinesForm from "../../Pages/Sales/Pipelines/form";

import Payment from "../../Pages/Accounting/Payments/index";
import PaymentForm from "../../Pages/Accounting/Payments/form";
import PaymentEditForm from "../../Pages/Accounting/Payments/form";

import Departments from "../../Pages/Organization/Departments/index";
import DepartmentForm from "../../Pages/Organization/Departments/form";

import Tags from '../../Pages/Organization/Tags/index'
import TagsForm from '../../Pages/Organization/Tags/form'

import Reports from "../../Pages/Reports/index";
//import ReportForm from "../../Pages/Reports/form";

//import Equipments from "../../Pages/Organization/Equipments/index";
import Equipments from "../../Pages/Organization/Equipments/index";
import EquipmentsForm from "../../Pages/Organization/Equipments/form";

import Teams from "../../Pages/Organization/Teams/index";
import TeamsForm from "../../Pages/Organization/Teams/form";

import Vendors from "../../Pages/Organization/Vendors/index";
import VendorsForm from "../../Pages/Organization/Vendors/form";
 
import Documents from '../../Pages/Organization/Documents/index';
import DocumentsForm from "../../Pages/Organization/Documents/form";

import Inventory from '../../Pages/Organization/Inventory/index';
import InventoryForm from "../../Pages/Organization/Inventory/form";

import  Employees from "../../Pages/Employees/Manage/index";
import EmployeesForm from "../../Pages/Employees/Manage/form";

import Approvals from '../../Pages/Employees/Approval/index';
import ApprovalForm from '../../Pages/Employees/Approval/form';

import Position from '../../Pages/Employees/Positions/index';
import PositionForm from '../../Pages/Employees/Positions/form';

import Employeelevel from '../../Pages/Employees/Employees Level/index';
import EmployeelevelForm from '../../Pages/Employees/Employees Level/form';


import AutoIndex from "../../Pages/Automation/AutoIndex";
import WorkFlow from "../../Pages/Automation/WorkFlow";
import NestWorkflow from "../../Pages/Automation/NestWorkFlow";

// import { useLocation } from "react-router-dom";

// import IncomeForm from "../../Pages/Accounting/Income/from";
// import IncomeEditForm from "../../Pages/Accounting/Income/from";


  
const routes = [

  { path: '/', exact: true, name: 'Home', element:<Dashboard/> },

//  "/customers"
  { path: '/customers', exact: true, name: 'Customers', element:<Customers/> },
  { path: '/customers/createform', exact: true, name: 'Customer Form', element: <CustomerForm/>},
  { path: '/customers/edit/:id', exact: true, name: 'Customer Edit Form', element: <CustomerEditForm/>},
  { path: '/customers/view/:id', exact: true, name: 'Customer View Form', element: <CustomerView/>},

//clients
{ path: '/clients', exact: true, name: 'Clients', element:<Customers/> },
{ path: '/clients/createform', exact: true, name: 'Clients Form', element: <ClientsForm/>},
{ path: '/clients/edit/:id', exact: true, name: 'Clients Edit Form', element: <ClientsEditForm/>},  
{ path: '/clients/view/:id', exact: true, name: 'Clients View Form', element: <ClientView/>},

//leads
{path:'/leads', name:'Leads', element:<Customers/>},
{ path: '/leads/createform', exact: true, name: 'Leads Form', element: <LeadsForm/>},
{ path: '/leads/edit/:id', exact: true, name: 'Leads Edit Form', element: <LeadsEditForm/>},
{ path: '/leads/view/:id', exact: true, name: 'Leads View Form', element: <ClientView/>},

//incomes
{ path: '/income', exact: true, name: 'Customers', element:<Income/> },
{ path: '/income/createform', name: 'Income Form', element:<IncomeForm/> },
{ path: '/income/edit/:id', exact: true, name: 'Income Edit Form', element: <IncomeEditForm/>},

//payments
{ path: '/payments', exact: true, name: 'Payment', element:<Customers/> },
{path:'/payments/createform', name:'Payment From', element:<PaymentForm/>},
{path:'/payments/edit/:id', name:'Payment Edit From', element:<PaymentForm/>},  


  //Proposals
  { path: '/proposals', name: 'Dashboard', element:<Proposals/> },
  { path: '/proposals/createform', name: 'Invoices Form', element:<ProposalsForm/> },

  //pipelines
  { path: '/pipelines', name: 'Pipeline', element:<Pipelines/>},
  { path: '/pipelines/pipelinesform', name: 'Pipelines Form', element:<PipelinesForm/>},

  //invoices
  { path: '/invoices', name: 'Invoices', element:<Invoices/> },
  { path: '/invoices/createform', name: 'Invoices Form', element:<InvoicesForm/> },
  { path: '/invoices/edit/:id', exact: true, name: 'Invoices Edit Form', element: <InvoicesEditForm/>},
  { path: '/invoices/view/:id', exact: true, name: 'Invoices View Form', element: <InvoiceView/>},

  { path: '/invoices/payment/:id', exact: true, name: 'Invoices Payment', element: <Payment/>},

  //Estimates
  { path: '/estimates', name: 'Estimates', element:<Estimates/>},
  { path: '/estimates/createForm', name: 'Estimates Form', element:<EstimatesForm/>},

  { path: '/invoices/view', name: 'Invoices Form', element:<InvoicesForm/> },

  //Tasks
  { path: '/tasks', name: 'Tasks', element:<Tasks/> },
  { path: '/tasks/createform', name: 'Tasks', element:<TasksForm/> },
  { path: '/tasks/view/', name: 'Tasks', element:<TasksForm/> },

  //Teamtasks
  { path: '/teamtasks', name: 'Tasks', element:<TeamsTasks/> },
  { path: '/tasks/teams/createform', name: 'Tasks', element:<TeamsTasksForm/> },
  { path: '/tasks/teams/editfrom/:id', exact: true, name: 'TeamTask Edit Form', element: <TeamsTasksEditForm/>},

  //Departments
  { path: '/departments', name: 'Departments', element:<Departments/>},
  { path: '/departments/createform', name: 'Deparments Form', element:<DepartmentForm/>},

  //Reports
  { path: '/reports', name: 'Reports', element:<Reports/>},
  //{ path: '/reports/createform', name: 'Reports Form', element:<ReportForm/>},

  //Tags
  { path: '/tags', name: 'Tags', element:<Tags/>},
  { path: '/tags/createform', name: 'Tags Form', element:<TagsForm/>},
   
  //Equipments
  {path: '/equipments', name: 'Equipments', element:<Equipments/>},
  {path: '/equipments/createform', name: "Equipments Form", element:<EquipmentsForm/>},

  //Teams
  {path: '/teams', name: 'Teams', element:<Teams/>},
  {path: '/teams/createform', name: "Teams Form", element:<TeamsForm/>},

  //Vendors
  {path: '/vendors', name: 'Vendors', element:<Vendors/>},
  {path: '/vendors/createform', name: 'Vendors Form', element:<VendorsForm/>},

//Documents
  {path: '/documents', name: 'Documents', element: <Documents/>},
  {path: '/documents/createform', name: 'Documents Form', element: <DocumentsForm/>},

  //Inventory
  {path: 'inventory', name: 'Inventory', element: <Inventory/>},
  {path: 'inventory/createform', name: 'Inventory Form', element: <InventoryForm/>},

  //Employees
  {path: 'manage', name: 'Employees', element: <Employees/>},
  {path: 'manage/createform', name: 'Employees Form', element: <EmployeesForm/>},
  
  //Approval
  {path: 'approvals', name: 'Approvals', element: <Approvals/>},
  {path: 'approvals/createform', name: 'Approval Form', element: <ApprovalForm/>},

  //Position
  {path: 'positions', name: 'Positions', element: <Position/>},
  {path: 'positions/createform', name: 'Positions Form', element: <PositionForm/>},

  //Employee Level
  {path: 'employee-level', name: 'Employeelevel', element: <Employeelevel/>},
  {path: 'employees/createform', name: 'employeelevel Form', element: <EmployeelevelForm/>},

  //Automation
  {path: 'settings', name:'autoIndex', element:<AutoIndex/>},
  {path: 'workflow', name:'workFlow',element:<WorkFlow/>},
  {path: 'nestworkflow', name:'nestworkFlow', element:<NestWorkflow/>},
   
  {path:"/", element:<WorkFlow workFlow={WorkFlow}/>},
  {path:"/nestworkflow/:id", name:'nestworkFlow edit', element:<NestWorkflow />}  
];

export default routes;



