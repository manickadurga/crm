import React from "react";

import Dashboard from "../../Pages/Dashboard";

import Inventory from "../../Pages/Inventory";

// import Customers from "../../Pages/Customers";
import Customers from "../../Pages/Contacts/Customers";
import CustomerForm from "../../Pages/Contacts/Customers/form";
import CustomerEditForm from "../../Pages/Contacts/Customers/form";
import CustomerView from "../../Pages/Contacts/Customers/view";

import Clients from "../../Pages/Contacts/Clients";
import ClientsForm from "../../Pages/Contacts/Clients/form";
import ClientsEditForm from "../../Pages/Contacts/Clients/form";
import ClientView from "../../Pages/Contacts/Clients/view";
// import ClientView from "../../Pages/Contacts/Clients/view";


import Proposals from "../../Pages/Proposals";
import ProposalsForm from "../../Pages/Proposals/form";


import Invoices from "../../Pages/Invoices";
import InvoicesForm from "../../Pages/Invoices/form";
import InvoicesEditForm from "../../Pages/Invoices/form";
import InvoiceView from "../../Pages/Invoices/view";
// import Payment from "../../Pages/Invoices/payment";

import Estimates from "../../Pages/Accounting/Estimates";
import EstimatesForm from "../../Pages/Accounting/Estimates/from";

import Tasks from "../../Pages/Tasks";
import TasksForm from "../../Pages/Tasks/form";

import TeamsTasks from "../../Pages/TeamsTasks";
import TeamsTasksForm from "../../Pages/TeamsTasks/form";
import TeamsTasksEditForm from "../../Pages/TeamsTasks/form";


import Leads from "../../Pages/Contacts/Leads";
import LeadsForm from "../../Pages/Contacts/Leads/from";
import LeadsEditForm from "../../Pages/Contacts/Leads/from";
import LeadView from "../../Pages/Contacts/Leads/view";
// import TeamsTasks from "../../Pages/TeamsTasks";
// import TeamsTasksForm from "../../Pages/TeamsTasks/form";


import Income from "../../Pages/Accounting/Income";
import IncomeForm from "../../Pages/Accounting/Income/from";
import IncomeEditForm from "../../Pages/Accounting/Income/from";

// import Payment from "../../Pages/Accounting/Payments";
import PaymentForm from "../../Pages/Accounting/Payments/from";
import PaymentEditForm from "../../Pages/Accounting/Payments/from";
import { useLocation } from "react-router-dom";

// import IncomeForm from "../../Pages/Accounting/Income/from";
// import IncomeEditForm from "../../Pages/Accounting/Income/from";
  
const routes = [

  { path: '/', exact: true, name: 'Home', element:<Dashboard/> },
  { path: '/inventory', namclientse: 'Dashboard', element:<Inventory/> },
  { path: '/customers', exact: true, name: 'Customers', element:<Customers/> },
  { path: '/customers/createform', exact: true, name: 'Customer Form', element: <CustomerForm/>},
  { path: '/customers/edit/:id', exact: true, name: 'Customer Edit Form', element: <CustomerEditForm/>},
  { path: '/customers/view/:id', exact: true, name: 'Customer View Form', element: <CustomerView/>},
//  "/customers"

{ path: '/clients', exact: true, name: 'Clients', element:<Customers/> },
{ path: '/clients/ClientsForm', exact: true, name: 'Clients Form', element: <ClientsForm/>},
{ path: '/clients/edit/:id', exact: true, name: 'Clients Edit Form', element: <ClientsEditForm/>},  
{ path: '/clients/view/:id', exact: true, name: 'Clients View Form', element: <ClientView/>},

{path:'/leads', name:'Leads', element:<Customers/>},
{ path: '/leads/createform', exact: true, name: 'Leads Form', element: <LeadsForm/>},
{ path: '/leads/edit/:id', exact: true, name: 'Leads Edit Form', element: <LeadsEditForm/>},
{ path: '/leads/view/:id', exact: true, name: 'Leads View Form', element: <ClientView/>},


{ path: '/income', exact: true, name: 'Customers', element:<Income/> },
{ path: '/income/createform', name: 'Income Form', element:<IncomeForm/> },
{ path: '/income/edit/:id', exact: true, name: 'Income Edit Form', element: <IncomeEditForm/>},

{ path: '/payments', exact: true, name: 'Payment', element:<Customers/> },
{path:'/payments/createform', name:'Payment From', element:<PaymentForm/>},
{path:'/payments/edit/:id', name:'Payment Edit From', element:<PaymentForm/>},  


  { path: '/proposals', name: 'Dashboard', element:<Proposals/> },
  { path: '/proposals/createform', name: 'Invoices Form', element:<ProposalsForm/> },

  { path: '/invoices', name: 'Invoices', element:<Invoices/> },
  { path: '/invoices/createform', name: 'Invoices Form', element:<InvoicesForm/> },
  { path: '/invoices/edit/:id', exact: true, name: 'Invoices Edit Form', element: <InvoicesEditForm/>},
  { path: '/invoices/view/:id', exact: true, name: 'Invoices View Form', element: <InvoiceView/>},
  // { path: '/invoices/payment/:id', exact: true, name: 'Invoices Payment', element: <Payment/>},

  { path: '/estimates', name: 'Estimates', element:<Estimates/>},
  // { path: '/estimates/createForm', name: 'Estimates Form', element:<EstimatesForm/>},

  // { path: '/invoices/view', name: 'Invoices Form', element:<InvoicesForm/> },

  { path: '/tasks', name: 'Tasks', element:<Tasks/> },
  { path: '/tasks/createform', name: 'Tasks', element:<TasksForm/> },
  { path: '/tasks/view/', name: 'Tasks', element:<TasksForm/> },

  { path: '/tasks/teams', name: 'Tasks', element:<TeamsTasks/> },
  { path: '/tasks/teams/createform', name: 'Tasks', element:<TeamsTasksForm/> },
  { path: '/tasks/teams/editfrom/:id', exact: true, name: 'TeamTask Edit Form', element: <TeamsTasksEditForm/>},


];

export default routes




