import React from "react";

import Dashboard from "../../Pages/Dashboard";

import Inventory from "../../Pages/Inventory";

import Customers from "../../Pages/Customers";
import CustomerForm from "../../Pages/Customers/form";

import Orders from "../../Pages/Orders";


import Invoices from "../../Pages/Invoices";
import InvoicesForm from "../../Pages/Invoices/form";
import UpdateInvoices from "../../Pages/Invoices/UpdateInvoices";

import Tasks from "../../Pages/Tasks";
import TasksForm from "../../Pages/Tasks/form";

import TeamsTasks from "../../Pages/TeamsTasks";
import TeamsTasksForm from "../../Pages/TeamsTasks/form";



const routes = [
  { path: '/', exact: true, name: 'Home', element:<Dashboard/> },
  { path: '/inventory', name: 'Dashboard', element:<Inventory/> },
  { path: '/customers', exact: true, name: 'Home', element:<Customers/> },
  { path: '/customers/createform', exact: true, name: 'Customer Form', element: <CustomerForm/>},
  // { path: '/orders', name: 'Dashboard', element:<Orders/> },
  { path: '/invoices', name: 'Invoices', element:<Invoices/> },
  { path: '/invoices/createform', name: 'Invoices Form', element:<InvoicesForm/> },
  { path: '/invoices/view/:id', name: 'Update Invoices', element: <UpdateInvoices /> },

  { path: '/tasks', name: 'Tasks', element:<Tasks/> },
  { path: '/tasks/createform', name: 'Tasks', element:<TasksForm/> },
  { path: '/tasks/view/', name: 'Tasks', element:<TasksForm/> },

  { path: '/tasks/teams', name: 'Tasks', element:<TeamsTasks/> },
  { path: '/tasks/teams/createform', name: 'Tasks', element:<TeamsTasksForm/> },

];

export default routes
