@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Create Organization</h2>
    <a href="{{ route('organizations.index') }}" class="btn btn-secondary mb-3">Back to List</a>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('organizations.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="organization_name">Organization Name</label>
            <input type="text" name="organization_name" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="currency">Currency</label>
            <select name="currency" class="form-control">
                @foreach($currencies as $currency)
                    <option value="{{ $currency }}">{{ $currency }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="official_name">Official Name</label>
            <input type="text" name="official_name" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="tax_id">Tax ID</label>
            <input type="text" name="tax_id" class="form-control">
        </div>
        <div class="form-group">
            <label for="tags">Tags</label>
            <input type="text" name="tags" class="form-control">
        </div>
        <div class="form-group">
            <label for="find_address">Find Address</label>
            <input type="text" name="find_address" class="form-control">
        </div>
        <div class="form-group">
            <label for="country">Country</label>
            <input type="text" name="country" class="form-control">
        </div>
        <div class="form-group">
            <label for="city">City</label>
            <input type="text" name="city" class="form-control">
        </div>
        <div class="form-group">
            <label for="postcode">Postcode</label>
            <input type="text" name="postcode" class="form-control">
        </div>
        <div class="form-group">
            <label for="address">Address</label>
            <input type="text" name="address" class="form-control">
        </div>
        <div class="form-group">
            <label for="address_2">Address 2</label>
            <input type="text" name="address_2" class="form-control">
        </div>
        <div class="form-group">
            <label for="employee_bonus_type">Employee Bonus Type</label>
            <select name="employee_bonus_type" class="form-control">
                <option value="None">None</option>
                <option value="Profit Based Bonus">Profit Based Bonus</option>
                <option value="Revenue Based Bonus">Revenue Based Bonus</option>
            </select>
        </div>
        <div class="form-group">
            <label for="bonus_percentage">Bonus Percentage</label>
            <input type="number" name="bonus_percentage" class="form-control" min="0" max="100" value="0">
        </div>
        <div class="form-group">
            <label for="timezone">Choose Time Zone</label>
            <select name="timezone" class="form-control">
                @foreach($timezones as $timezone)
                    <option value="{{ $timezone }}">{{ $timezone }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="start_week_on">Start Week On</label>
            <select name="start_week_on" class="form-control">
                @foreach($weekDays as $day)
                    <option value="{{ $day }}">{{ $day }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="default_date_type">Default Date Type</label>
            <select name="default_date_type" class="form-control">
                <option value="Today">Today</option>
                <option value="End of Month">End of Month</option>
                <option value="Start of Month">Start of Month</option>
            </select>
        </div>
        <div class="form-group">
            <label for="regions">Regions</label>
            <select name="regions" class="form-control">
                <option value="English (United States)">English (United States)</option>
                <option value="English (United Kingdom)">English (United Kingdom)</option>
            </select>
        </div>
        <div class="form-group">
            <label for="number_format">Select Number Format</label>
            <select name="number_format" class="form-control">
                <option value="$12,345.67">$12,345.67</option>
            </select>
        </div>
        <div class="form-group">
            <label for="date_format">Date Format</label>
            <select name="date_format" class="form-control">
                <option value="06/01/2024">06/01/2024</option>
                <option value="June 1,2024">June 1,2024</option>
                <option value="Saturday,June 1,2024">Saturday,June 1,2024</option>
            </select>
        </div>
        <div class="form-group">
            <label for="fiscal_year_start_date">Fiscal Year Start Date</label>
            <input type="date" name="fiscal_year_start_date" class="form-control">
        </div>
        <div class="form-group">
            <label for="fiscal_year_end_date">Fiscal Year End Date</label>
            <input type="date" name="fiscal_year_end_date" class="form-control">
        </div>
        <div class="form-group">
            <label for="invite_expiry_period">Invite Expiry Period (in Days)</label>
            <input type="number" name="invite_expiry_period" class="form-control" min="1" value="7">
        </div>
        <div class="form-group form-check">
            <input type="checkbox" name="allow_users_to_send_invites" class="form-check-input">
            <label class="form-check-label" for="allow_users_to_send_invites">Allow Users to Send Invites</label>
        </div>
        <button type="submit" class="btn btn-primary">Create</button>
    </form>
</div>
@endsection
