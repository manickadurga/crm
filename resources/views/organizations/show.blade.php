@extends('layouts.app1')

@section('content')
<div class="container">
    <h2>Organization Details</h2>
    <a href="{{ route('organizations.index') }}" class="btn btn-secondary mb-3">Back to List</a>

    <div class="card">
        <div class="card-header">
            <h3>{{ $organization->organization_name }}</h3>
        </div>
        <div class="card-body">
            <p><strong>Official Name:</strong> {{ $organization->official_name }}</p>
            <p><strong>Currency:</strong> {{ $organization->currency }}</p>
            <p><strong>Tax ID:</strong> {{ $organization->tax_id }}</p>
            <p><strong>Tags:</strong> {{ $organization->tags }}</p>
            <p><strong>Find Address:</strong> {{ $organization->find_address }}</p>
            <p><strong>Country:</strong> {{ $organization->country }}</p>
            <p><strong>City:</strong> {{ $organization->city }}</p>
            <p><strong>Postcode:</strong> {{ $organization->postcode }}</p>
            <p><strong>Address:</strong> {{ $organization->address }}</p>
            <p><strong>Address 2:</strong> {{ $organization->address_2 }}</p>
            <p><strong>Employee Bonus Type:</strong> {{ $organization->employee_bonus_type }}</p>
            <p><strong>Bonus Percentage:</strong> {{ $organization->bonus_percentage }}%</p>
            <p><strong>Time Zone:</strong> {{ $organization->timezone }}</p>
            <p><strong>Start Week On:</strong> {{ $organization->start_week_on }}</p>
            <p><strong>Default Date Type:</strong> {{ $organization->default_date_type }}</p>
            <p><strong>Regions:</strong> {{ $organization->regions }}</p>
            <p><strong>Number Format:</strong> {{ $organization->number_format }}</p>
            <p><strong>Date Format:</strong> {{ $organization->date_format }}</p>
            <p><strong>Fiscal Year Start Date:</strong> {{ $organization->fiscal_year_start_date }}</p>
            <p><strong>Fiscal Year End Date:</strong> {{ $organization->fiscal_year_end_date }}</p>
            <p><strong>Invite Expiry Period (in Days):</strong> {{ $organization->invite_expiry_period }}</p>
            <p><strong>Allow Users to Send Invites:</strong> {{ $organization->allow_users_to_send_invites ? 'Yes' : 'No' }}</p>
        </div>
    </div>
</div>
@endsection
