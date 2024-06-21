<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'ADMIN', 'SUPER_ADMIN', 'EMPLOYEE', 'CANDIDATE', 'MANAGER', 'VIEWER', 'INTERVIEWER', 'DATA_ENTRY'
        ];

        $permissions = [
            'View Admin Dashboard', 'View Team Dashboard', 'View Project Management Dashboard', 
            'View Time Tracking Dashboard', 'View Accounting Dashboard', 'View Human Resources Dashboard',
            'Change Selected Employee', 'Change Selected Candidate', 'Change Selected Organization',
            'View Job Employees', 'View Job Matching', 'Edit Organization Public Page', 'View Payments',
            'Create/Edit/Delete Payments', 'View All Expenses', 'Create/Edit/Delete Expenses', 
            'View All Employee Expenses', 'Create/Edit/Delete Employee Expenses', 'Create/Edit/Delete Incomes',
            'View All Incomes', 'Create/Edit/Delete Proposals Register', 'View Proposals Page',
            'View Proposal Templates Page', 'Create/Edit/Delete Proposal Templates', 'View Organization Employees',
            'Create Tasks', 'View Tasks', 'Edit Tasks', 'Delete Tasks', 'View Time Off Page', 'View Organization Invites',
            'Create/Resend/Delete Invites', 'View Time Off Policy', 'Edit Time Off Policy', 'Edit Time Off', 
            'Edit Approvals Policy', 'View Approvals Policy', 'Edit Approval Request', 'View Approval Request',
            'Access Private Projects', 'Edit Time in Timesheet', 'View Invoices', 'Edit Invoices Add', 'View Estimates',
            'Edit Estimates Add', 'View All Candidates Documents', 'Create/Edit Task', 'Create/Edit Interview',
            'View Interview', 'Create/Edit Interviewers', 'View Interviewers', 'Create/Edit/Delete Candidate Feedback',
            'View Organization Inventory', 'Management Product', 'Edit Tags', 'View All Emails', 
            'View All Emails Templates', 'Edit Organization Help Center', 'View Sales Pipelines', 'Edit Sales Pipelines',
            'Approve Timesheet', 'Create/Edit Sprints', 'View Sprints', 'Create Projects', 'View Projects', 
            'Edit Projects', 'Delete Projects', 'Create/Edit Contacts', 'View Contacts', 'Add Teams', 'View Teams',
            'Edit Teams', 'Delete Teams', 'Edit Active Tasks', 'Remove Account As Team Member', 'Task Settings', 
            'View Teams Join Requests', 'Delete Teams Join Requests', 'Create/Edit Contracts', 'View Event Types', 
            'Access Time Tracker', 'View Inventory Gallery', 'Edit Inventory Gallery', 'Add media gallery',
            'View media gallery', 'Edit media gallery', 'Delete media gallery', 'View Organization Equipment', 
            'Edit Organization Equipment', 'View Organization Equipment Sharing', 'Edit Organization Equipment Sharing',
            'Request Make Equipment Make', 'Request Approve Equipment', 'View Organization Product Types',
            'View Organization Product Categories', 'Edit Organization Product Categories', 'View All Accounting Templates',
            'Allow Delete Time', 'Allow Modify Time', 'Allow Manual Time', 'Allow Delete Screenshot', 'Access Delete Account',
            'View Last Log'
        ];

        foreach ($roles as $role) {
            Role::create(['name' => $role]);
        }

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }
    }
}

