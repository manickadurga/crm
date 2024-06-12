import React from "react";

import Dashboard from "../../Pages/Dashboard";

import Inventory from "../../Pages/Inventory";

import Customers from "../../Pages/Customers";
import CustomerForm from "../../Pages/Customers/form";
import CustomerViewForm from "../../Pages/Customers/form";

import Proposals from "../../Pages/Proposals";
import ProposalsForm from "../../Pages/Proposals/form";


import Invoices from "../../Pages/Invoices";
import InvoicesForm from "../../Pages/Invoices/form";

import Tasks from "../../Pages/Tasks";
import TasksForm from "../../Pages/Tasks/form";

import TeamsTasks from "../../Pages/TeamsTasks";
import TeamsTasksForm from "../../Pages/TeamsTasks/form";



const routes = [
  { path: '/', exact: true, name: 'Home', element:<Dashboard/> },
  { path: '/inventory', name: 'Dashboard', element:<Inventory/> },
  { path: '/customers', exact: true, name: 'Home', element:<Customers/> },
  { path: '/customers/createform', exact: true, name: 'Customer Form', element: <CustomerForm/>},
  { path: '/customers/view', exact: true, name: 'Customer View Form', element: <CustomerViewForm/>},

  { path: '/proposals', name: 'Dashboard', element:<Proposals/> },
  { path: '/proposals/createform', name: 'Invoices Form', element:<ProposalsForm/> },

  { path: '/invoices', name: 'Invoices', element:<Invoices/> },
  { path: '/invoices/createform', name: 'Invoices Form', element:<InvoicesForm/> },
  // { path: '/invoices/view', name: 'Invoices Form', element:<InvoicesForm/> },

  { path: '/tasks', name: 'Tasks', element:<Tasks/> },
  { path: '/tasks/createform', name: 'Tasks', element:<TasksForm/> },
  { path: '/tasks/view/', name: 'Tasks', element:<TasksForm/> },

  { path: '/tasks/teams', name: 'Tasks', element:<TeamsTasks/> },
  { path: '/tasks/teams/createform', name: 'Tasks', element:<TeamsTasksForm/> },

];

export default routes
