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
                        'icon' => 'FileOutlined',
                        'label' => 'Estimates',
                    ],
                    [
                        'path' => '/invoices',
                        'icon' => 'FileTextOutlined',
                        'label' => 'Invoices',
                    ],
                    [
                        'path' => '/incomes',
                        'icon' => 'PlusCircleFilled',
                        'label' => 'Income',
                    ],
                    [
                        'path' => '/expenses',
                        'icon' => 'MinusCircleFilled',
                        'label' => 'Expenses',
                    ],
                    [
                        'path' => '/recurring-expenses',
                        'icon' => 'SwapOutlined',
                        'label' => 'Recurring Expenses',
                    ],
                    [
                        'path' => '/payments',
                        'icon' => 'CreditCardOutlined',
                        'label' => 'Payments',
                    ],
                ],
            ],
            [
                'path' => '/sales',
                'icon' => 'LineChartOutlined',
                'label' => 'Sales',
                'children' => [
                    [
                        'path' => '/proposals',
                        'icon' => 'SendOutlined',
                        'label' => 'Proposals',
                    ],
                    [
                        'path' => '/sales',
                        'icon' => 'LineChartOutlined',
                        'label' => 'Sales',
                    ],
                    [
                        'path' => '/invoices',
                        'icon' => 'FileTextOutlined',
                        'label' => 'Invoices',
                    ],
                    [
                        'path' => '/payments',
                        'icon' => 'CreditCardOutlined',
                        'label' => 'Payments',
                    ],
                    [
                        'path' => '/pipelines',
                        'icon' => 'FilterFilled',
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
                        'icon' => 'TableOutlined',
                        'label' => 'Dashboard',
                    ],
                    [
                        'path' => '/teamtasks',
                        'icon' => 'TeamOutlined',
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
                        'icon' => 'TeamOutlined',
                        'label' => 'Employees',
                    ],
                    [
                        'path' => '/proposal-template',
                        'icon' => 'FileTextOutlined',
                        'label' => 'Proposal Template',
                    ],
                ],
            ],
            [
                'path' => '/employees',
                'icon' => 'TeamOutlined',
                'label' => 'Employees',
                'children' => [
                    [
                        'path' => '/manage',
                        'icon' => 'BarsOutlined',
                        'label' => 'Manage',
                    ],
                    [
                        'path' => '/time-activity',
                        'icon' => 'LineChartOutlined',
                        'label' => 'Time & Activity',
                    ],
                    [
                        'path' => '/timesheets',
                        'icon' => 'ClockCircleOutlined',
                        'label' => 'Timesheets',
                    ],
                    [
                        'path' => '/appointments',
                        'icon' => 'CalendarFilled',
                        'label' => 'Appointments',
                    ],
                    [
                        'path' => '/approvals',
                        'icon' => 'RetweetOutlined',
                        'label' => 'Approvals',
                    ],
                    [
                        'path'=>'/employee-level',
                        'icon'=>'BarChartOutlined',
                        'label'=>'Employee Level'
                    ],
                    [
                        'path' => '/positions',
                        'icon' => 'TagOutlined',
                        'label' => 'Positions',
                    ],
                    
                    [
                        'path' => '/time-off',
                        'icon' => 'CloseCircleOutlined',
                        'label' => 'Time Off',
                    ],
                    [
                        'path' => '/recurring-expenses',
                        'icon' => 'SwapOutlined',
                        'label' => 'Recurring Expenses',
                    ],
                    [
                        'path' => '/candidates',
                        'icon' => 'UserOutlined',
                        'label' => 'Candidates',
                    ],
                ],
            ],
            [
                'path' => '/organization',
                'icon' => 'BuildOutlined',
                'label' => 'Organization',
                'children' => [
                    [
                        'path' => '/manage',
                        'icon' => 'BuildOutlined',
                        'label' => 'Manage',
                    ],
                    [
                        'path' => '/equipments',
                        'icon' => 'TableOutlined',
                        'label' => 'Equipment',
                    ],
                    [
                        'path' => '/inventory',
                        'icon' => 'HolderOutlined',
                        'label' => 'Inventory',
                    ],
                    [
                        'path' => '/tags',
                        'icon' => 'TagFilled',
                        'label' => 'Tags',
                    ],
                    [
                        'path' => '/vendors',
                        'icon' => 'TruckFilled',
                        'label' => 'Vendors',
                    ],
                    [
                        'path' => '/projects',
                        'icon' => 'BookFilled',
                        'label' => 'Projects',
                    ],
                    [
                        'path' => '/departments',
                        'icon' => 'ShoppingFilled',
                        'label' => 'Departments',
                    ],
                    [
                        'path' => '/teams',
                        'icon' => 'TeamOutlined',
                        'label' => 'Teams',
                    ],
                    [
                        'path' => '/documents',
                        'icon' => 'FileTextOutlined',
                        'label' => 'Documents',
                    ],
                    [
                        'path' => '/employment-types',
                        'icon' => 'OrderedListOutlined',
                        'label' => 'Employment Types',
                    ],
                    [
                        'path' => '/expense-recurring',
                        'icon' => 'SwapOutlined',
                        'label' => 'Recurring Expenses',
                    ],
                    [
                        'path' => '/help-center',
                        'icon' => 'QuestionCircleOutlined',
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
                        'path' => '/leads',
                        'icon' => 'ContactsOutlined',
                        'label' => 'Leads',
                    ],
                    [
                        'path' => '/customers',
                        'icon' => 'ContactsOutlined',
                        'label' => 'Customers',
                    ],
                    [
                        'path' => '/clients',
                        'icon' => 'ContactsOutlined',
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
                        'icon' => 'UnorderedListOutlined',
                        'label' => 'Manage',
                    ],
                    [
                        'path' => '/settings',
                        'icon' => 'SettingFilled',
                        'label' => 'Settings',
                    ],
                ],
            ],
            [
                'path' => '/reports',
                'icon' => 'PieChartFilled',
                'label' => 'Reports',
            ],
            
        ];

        return response()->json($menuItems);
    }
}