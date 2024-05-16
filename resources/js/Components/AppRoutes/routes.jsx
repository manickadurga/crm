import React from "react";

import Dashboard from "../../Pages/Dashboard";

import Inventory from "../../Pages/Inventory";

import Customers from "../../Pages/Customers";
import CustomerForm from "../../Pages/Customers/form";

import Orders from "../../Pages/Orders";


import Goals from "../../Pages/Goals";
import GoalsForm from "../../Pages/Goals/form";


const routes = [
  { path: '/', exact: true, name: 'Home', element:<Dashboard/> },
  { path: '/inventory', name: 'Dashboard', element:<Inventory/> },
  { path: '/customers', exact: true, name: 'Home', element:<Customers/> },
  { path: '/customers/createform', exact: true, name: 'Customer Form', element: <CustomerForm/>},
  // { path: '/orders', name: 'Dashboard', element:<Orders/> },
  { path: '/goals', name: 'Goals', element:<Goals/> },
  { path: '/goals/createform', name: 'Goals Form', element:<GoalsForm/> },
];

export default routes