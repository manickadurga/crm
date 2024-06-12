import React from "react";

import Dashboard from "../../Pages/Dashboard";

import Inventory from "../../Pages/Inventory";

import Customers from "../../Pages/Customers";
import CustomerForm from "../../Pages/Customers/form";
import Orders from "../../Pages/Orders";
import Invoices from "../../Pages/Invoices";
import InvoicesForm from "../../Pages/Invoices/form";

import Goals from "../../Pages/Goals";
import GoalsForm from "../../Pages/Goals/form";
//import EditableFormTable from "../../Pages/Customers/editableTable";



const routes = [
  { path: '/', exact: true, name: 'Home', element:<Dashboard/> },
  { path: '/inventory', name: 'Dashboard', element:<Inventory/> },
  { path: '/customers', exact: true, name: 'Home', element:<Customers/> },
  { path: '/customers/createform', exact: true, name: 'Customer Form', element: <CustomerForm/>},
  {path:'/customers/view', exact: true, name:'Update Customer Form',element:<CustomerForm/>},
  { path: '/invoices', name: 'Invoices', element:<Invoices/> },
  { path: '/customers/createform', name: 'Invoices Form', element:<InvoicesForm/> },
  { path: '/goals', name: 'Goals', element:<Goals/> },
  { path: '/goals/createform', name: 'Goals Form', element:<GoalsForm/> },
];

export default routes