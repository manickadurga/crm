<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Organization;

class OrganizationController extends Controller
{
    public function index()
    {
        $organizations = Organization::all();
        // dd($organizations);
        return view('organizations.index', compact('organizations'));
    }

    public function create()
    {
        $currencies = ['USD', 'EUR', 'GBP']; // Populate with all country currencies
        $timezones = \DateTimeZone::listIdentifiers();
        $weekDays = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        return view('organizations.create', compact('currencies', 'timezones', 'weekDays'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'organization_name' => 'required|string|max:255',
            'currency' => 'required|string',
            'official_name' => 'required|string|max:255',
            // 'tax_id' => 'string|max:255',
            // 'country' => 'string|max:255',
            // 'city' => 'string|max:255',
            // 'postcode' => 'string|max:255',
            // 'address' => 'string|max:255',
            'employee_bonus_type' => 'string',
            'bonus_percentage' => 'integer|min:0|max:100',
            'timezone' => 'string',
            'start_week_on' => 'string',
            'default_date_type' => 'string',
            'regions' => 'string',
            'number_format' => 'string',
            'date_format' => 'string',
            // 'fiscal_year_start_date' => 'date',
            // 'fiscal_year_end_date' => 'date',
            'invite_expiry_period' => 'integer|min:1',
            'allow_users_to_send_invites' => 'boolean',
        ]);

        Organization::create($request->all());

        return redirect()->route('organizations.index')->with('success', 'Organization created successfully.');
    }

    public function show(Organization $organization)
    {
        return view('organizations.show', compact('organization'));
    }

    public function edit(Organization $organization)
    {
        $currencies = ['USD', 'EUR', 'GBP']; // Populate with all country currencies
        $timezones = \DateTimeZone::listIdentifiers();
        $weekDays = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        return view('organizations.edit', compact('organization', 'currencies', 'timezones', 'weekDays'));
    }

    public function update(Request $request, Organization $organization)
    {
        $request->validate([
            'organization_name' => 'string|max:255',
            'currency' => 'string',
            'official_name' => 'string|max:255',
            // 'tax_id' => 'string|max:255',
            // 'country' => 'string|max:255',
            // 'city' => 'string|max:255',
            // 'postcode' => 'string|max:255',
            // 'address' => 'string|max:255',
            'employee_bonus_type' => 'string',
            'bonus_percentage' => 'integer|min:0|max:100',
            'timezone' => 'string',
            'start_week_on' => 'string',
            'default_date_type' => 'string',
            'regions' => 'string',
            'number_format' => 'string',
            'date_format' => 'string',
            // 'fiscal_year_start_date' => 'date',
            // 'fiscal_year_end_date' => 'date',
            'invite_expiry_period' => 'integer|min:1',
            'allow_users_to_send_invites' => 'boolean',
        ]);

        $organization->update($request->all());

        return redirect()->route('organizations.index')->with('success', 'Organization updated successfully.');
    }

    public function destroy(Organization $organization)
    {
        $organization->delete();

        return redirect()->route('organizations.index')->with('success', 'Organization deleted successfully.');
    }
}
