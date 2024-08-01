<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Model\Menuitems;

class MenuController extends Controller
{
    public function getMenuItems()
    {
        $menuItems = [
            [
                'path' => '/',
                'icon' => 'AppstoreOutlined',
                'label' => 'Dashboard',
            ],
            [
                'path' => '/accounting',
                'icon' => 'ShopOutlined',
                'label' => 'Accounting',
                'children' => [
                    [
                        'path' => '/estimates',
                        'icon' => 'ShoppingCartOutlined',
                        'label' => 'Estimates',
                    ],
                    [
                        'path' => '/estimates-received',
                        'icon' => 'ShoppingCartOutlined',
                        'label' => 'Estimates Received',
                    ],
                    [
                        'path' => '/invoices',
                        'icon' => 'ShoppingCartOutlined',
                        'label' => 'Invoices',
                    ],
                    [
                        'path' => '/invoices-received',
                        'icon' => 'ShoppingCartOutlined',
                        'label' => 'Invoices Received',
                    ],
                    [
                        'path' => '/incomes',
                        'icon' => 'ShoppingCartOutlined',
                        'label' => 'Income',
                    ],
                    [
                        'path' => '/expenses',
                        'icon' => 'ShoppingCartOutlined',
                        'label' => 'Expenses',
                    ],
                    [
                        'path' => '/recurring-expenses',
                        'icon' => 'ShoppingCartOutlined',
                        'label' => 'Recurring Expenses',
                    ],
                    [
                        'path' => '/payments',
                        'icon' => 'ShoppingCartOutlined',
                        'label' => 'Payments',
                    ],
                ],
            ],
            [
                'path' => '/sales',
                'icon' => 'ShoppingCartOutlined',
                'label' => 'Sales',
                'children' => [
                    [
                        'path' => '/proposals',
                        'icon' => 'ShoppingCartOutlined',
                        'label' => 'Proposals',
                    ],
                    [
                        'path' => '/sales',
                        'icon' => 'ShoppingCartOutlined',
                        'label' => 'Sales',
                    ],
                    [
                        'path' => '/estimates-received',
                        'icon' => 'ShoppingCartOutlined',
                        'label' => 'Estimates Received',
                    ],
                    [
                        'path' => '/invoices',
                        'icon' => 'ShoppingCartOutlined',
                        'label' => 'Invoices',
                    ],
                    [
                        'path' => '/payments',
                        'icon' => 'ShoppingCartOutlined',
                        'label' => 'Payments',
                    ],
                    [
                        'path' => '/pipelines',
                        'icon' => 'ShoppingCartOutlined',
                        'label' => 'Pipelines',
                    ],
                ],
            ],
            [
                'path' => '/tasks',
                'icon' => 'FlagOutlined',
                'label' => 'Tasks',
                'children' => [
                    [
                        'path' => '/dashboard',
                        'icon' => 'ShoppingCartOutlined',
                        'label' => 'Dashboard',
                    ],
                    [
                        'path' => '/teamtasks',
                        'icon' => 'ShoppingCartOutlined',
                        'label' => 'Teams Tasks',
                    ],
                ],
            ],
            [
                'path' => '/jobs',
                'icon' => 'FlagOutlined',
                'label' => 'Jobs',
                'children' => [
                    [
                        'path' => '/employees',
                        'icon' => 'ShoppingCartOutlined',
                        'label' => 'Employees',
                    ],
                    [
                        'path' => '/proposal-template',
                        'icon' => 'ShoppingCartOutlined',
                        'label' => 'Proposal Template',
                    ],
                ],
            ],
            [
                'path' => '/employees',
                'icon' => 'FlagOutlined',
                'label' => 'Employees',
                'children' => [
                    [
                        'path' => '/manage',
                        'icon' => 'ShoppingCartOutlined',
                        'label' => 'Manage',
                    ],
                    [
                        'path' => '/time-activity',
                        'icon' => 'ShoppingCartOutlined',
                        'label' => 'Time & Activity',
                    ],
                    [
                        'path' => '/timesheets',
                        'icon' => 'ShoppingCartOutlined',
                        'label' => 'Timesheets',
                    ],
                    [
                        'path' => '/appointments',
                        'icon' => 'ShoppingCartOutlined',
                        'label' => 'Appointments',
                    ],
                    [
                        'path' => '/approvals',
                        'icon' => 'ShoppingCartOutlined',
                        'label' => 'Approvals',
                    ],
                    [
                        'path'=>'/employee-level',
                        'icon'=>'shoppingCarOutlined',
                        'label'=>'Employee Level'
                    ],
                    [
                        'path' => '/positions',
                        'icon' => 'ShoppingCartOutlined',
                        'label' => 'Positions',
                    ],
                    
                    [
                        'path' => '/time-off',
                        'icon' => 'ShoppingCartOutlined',
                        'label' => 'Time Off',
                    ],
                    [
                        'path' => '/recurring-expenses',
                        'icon' => 'ShoppingCartOutlined',
                        'label' => 'Recurring Expenses',
                    ],
                    [
                        'path' => '/candidates',
                        'icon' => 'ShoppingCartOutlined',
                        'label' => 'Candidates',
                    ],
                ],
            ],
            [
                'path' => '/organization',
                'icon' => 'FlagOutlined',
                'label' => 'Organization',
                'children' => [
                    [
                        'path' => '/manage',
                        'icon' => 'ShoppingCartOutlined',
                        'label' => 'Manage',
                    ],
                    [
                        'path' => '/equipments',
                        'icon' => 'ShoppingCartOutlined',
                        'label' => 'Equipment',
                    ],
                    [
                        'path' => '/inventory',
                        'icon' => 'ShoppingCartOutlined',
                        'label' => 'Inventory',
                    ],
                    [
                        'path' => '/tags',
                        'icon' => 'ShoppingCartOutlined',
                        'label' => 'Tags',
                    ],
                    [
                        'path' => '/vendors',
                        'icon' => 'ShoppingCartOutlined',
                        'label' => 'Vendors',
                    ],
                    [
                        'path' => '/projects',
                        'icon' => 'ShoppingCartOutlined',
                        'label' => 'Projects',
                    ],
                    [
                        'path' => '/departments',
                        'icon' => 'ShoppingCartOutlined',
                        'label' => 'Departments',
                    ],
                    [
                        'path' => '/teams',
                        'icon' => 'ShoppingCartOutlined',
                        'label' => 'Teams',
                    ],
                    [
                        'path' => '/documents',
                        'icon' => 'ShoppingCartOutlined',
                        'label' => 'Documents',
                    ],
                    [
                        'path' => '/employment-types',
                        'icon' => 'ShoppingCartOutlined',
                        'label' => 'Employment Types',
                    ],
                    [
                        'path' => '/expense-recurring',
                        'icon' => 'ShoppingCartOutlined',
                        'label' => 'Recurring Expenses',
                    ],
                    [
                        'path' => '/help-center',
                        'icon' => 'ShoppingCartOutlined',
                        'label' => 'Help Center',
                    ],
                ],
            ],
            [
                'path' => '/contacts',
                'icon' => 'UserOutlined',
                'label' => 'Contacts',
                'children' => [
                    [
                        'path' => '/visitors',
                        'icon' => 'ShoppingCartOutlined',
                        'label' => 'Visitors',
                    ],
                    [
                        'path' => '/leads',
                        'icon' => 'ShoppingCartOutlined',
                        'label' => 'Community',
                    ],
                    [
                        'path' => '/customers',
                        'icon' => 'ShoppingCartOutlined',
                        'label' => 'Customers',
                    ],
                    [
                        'path' => '/clients',
                        'icon' => 'ShoppingCartOutlined',
                        'label' => 'Clients',
                    ],
                ],
            ],
            [
                'path' => '/goals',
                'icon' => 'FlagOutlined',
                'label' => 'Goals',
                'children' => [
                    [
                        'path' => '/manage',
                        'icon' => 'ShoppingCartOutlined',
                        'label' => 'Manage',
                    ],
                    [
                        'path' => '/settings',
                        'icon' => 'ShoppingCartOutlined',
                        'label' => 'Settings',
                    ],
                ],
            ],
            [
                'path' => '/reports',
                'icon' => 'FlagOutlined',
                'label' => 'Reports',
                'children' => [
                    [
                        'path' => '/all',
                        'icon' => 'ShoppingCartOutlined',
                        'label' => 'All Reports',
                    ],
                    [
                        'path' => '/time-activity',
                        'icon' => 'ShoppingCartOutlined',
                        'label' => 'Time & Activity',
                    ],
                    [
                        'path' => '/weekly',
                        'icon' => 'ShoppingCartOutlined',
                        'label' => 'Weekly',
                    ],
                    [
                        'path' => '/apps-urls',
                        'icon' => 'ShoppingCartOutlined',
                        'label' => 'Apps & URLs',
                    ],
                    [
                        'path' => '/manual-time-edits',
                        'icon' => 'ShoppingCartOutlined',
                        'label' => 'Manual Time Edits',
                    ],
                    [
                        'path' => '/amounts-owed',
                        'icon' => 'ShoppingCartOutlined',
                        'label' => 'Amounts Owed',
                    ],
                    [
                        'path' => '/weekly-limits',
                        'icon' => 'ShoppingCartOutlined',
                        'label' => 'Weekly Limits',
                    ],
                    [
                        'path' => '/daily-limits',
                        'icon' => 'ShoppingCartOutlined',
                        'label' => 'Daily Limits',
                    ],
                    [
                        'path' => '/project-budgets',
                        'icon' => 'ShoppingCartOutlined',
                        'label' => 'Project Budgets',
                    ],
                    [
                        'path' => '/client-budgets',
                        'icon' => 'ShoppingCartOutlined',
                        'label' => 'Client Budgets',
                    ],
                        
                ],
            ],
            
        ];

        return response()->json($menuItems);
    }
}