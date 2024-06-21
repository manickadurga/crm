@extends('layouts.app1')

@section('content')
<div class="container">
    <h2>Edit Organization</h2>
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

    <form action="{{ route('organizations.update', $organization->organizationid) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="organization_name">Organization Name</label>
            <input type="text" name="organization_name" class="form-control" value="{{ $organization->organization_name }}" required>
        </div>
        <div class="form-group">
            <label for="currency">Currency</label>
            <select name="currency" class="form-control">
                @foreach($currencies as $currency)
                    <option value="{{ $currency }}" {{ $organization->currency == $currency ? 'selected' : '' }}>{{ $currency }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="official_name">Official Name</label>
            <input type="text" name="official_name" class="form-control" value="{{ $organization->official_name }}" required>
        </div>
        <div class="form-group">
            <label for="tax_id">Tax ID</label>
            <input type="text" name="tax_id" class="form-control" value="{{ $organization->tax_id }}">
        </div>
        <div class="form-group">
            <label for="tags">Tags</label>
            <input type="text" name="tags" class="form-control" value="{{ $organization->tags }}">
        </div>
        <div class="form-group">
            <label for="find_address">Find Address</label>
            <input type="text" name="find_address" class="form-control" value="{{ $organization->find_address }}">
        </div>
        <div class="form-group">
            <label for="country">Country</label>
            <input type="text" name="country" class="form-control" value="{{ $organization->country }}">
        </div>
        <div class="form-group">
            <label for="city">City</label>
            <input type="text" name="city" class="form-control" value="{{ $organization->city }}">
        </div>
        <div class="form-group">
            <label for="postcode">Postcode</label>
            <input type="text" name="postcode" class="form-control" value="{{ $organization->postcode }}">
        </div>
        <div class="form-group">
            <label for="address">Address</label>
            <input type="text" name="address" class="form-control" value="{{ $organization->address }}">
        </div>
        <div class="form-group">
            <label for="address_2">Address 2</label>
            <input type="text" name="address_2" class="form-control" value="{{ $organization->address_2 }}">
        </div>
        <div class="form-group">
            <label for="employee_bonus_type">Employee Bonus Type</label>
            <select name="employee_bonus_type" class="form-control">
                <option value="None" {{ $organization->employee_bonus_type == 'None' ? 'selected' : '' }}>None</option>
                <option value="Profit Based Bonus" {{ $organization->employee_bonus_type == 'Profit Based Bonus' ? 'selected' : '' }}>Profit Based Bonus</option>
                <option value="Revenue Based Bonus" {{ $organization->employee_bonus_type == 'Revenue Based Bonus' ? 'selected' : '' }}>Revenue Based Bonus</option>
            </select>
        </div>
        <div class="form-group">
            <label for="bonus_percentage">Bonus Percentage</label>
            <input type="number" name="bonus_percentage" class="form-control" min="0" max="100" value="{{ $organization->bonus_percentage }}">
        </div>
        <div class="form-group">
            <label for="timezone">Choose Time Zone</label>
            <select name="timezone" class="form-control">
                @foreach($timezones as $timezone)
                    <option value="{{ $timezone }}" {{ $organization->timezone == $timezone ? 'selected' : '' }}>{{ $timezone }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="start_week_on">Start Week On</label>
            <select name="start_week_on" class="form-control">
                @foreach($weekDays as $day)
                    <option value="{{ $day }}" {{ $organization->start_week_on == $day ? 'selected' : '' }}>{{ $day }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="default_date_type">Default Date Type</label>
            <select name="default_date_type" class="form-control">
                <option value="Today" {{ $organization->default_date_type == 'Today' ? 'selected' : '' }}>Today</option>
                <option value="End of Month" {{ $organization->default_date_type == 'End of Month' ? 'selected' : '' }}>End of Month</option>
                <option value="Start of Month" {{ $organization->default_date_type == 'Start of Month' ? 'selected' : '' }}>Start of Month</option>
            </select>
        </div>
        <div class="form-group">
            <label for="regions">Regions</label>
            <select name="regions" class="form-control">
                <option value="English (United States)" {{ $organization->regions == 'English (United States)' ? 'selected' : '' }}>English (United States)</option>
                <option value="English (United Kingdom)" {{ $organization->regions == 'English (United Kingdom)' ? 'selected' : '' }}>English (United Kingdom)</option>
            </select>
        </div>
        <div class="form-group">
            <label for="number_format">Select Number Format</label>
            <select name="number_format" class="form-control">
                <option value="$12,345.67" {{ $organization->number_format == '$12,345.67' ? 'selected' : '' }}>$12,345.67</option>
            </select>
        </div>
        <div class="form-group">
            <label for="date_format">Date Format</label>
            <select name="date_format" class="form-control">
                <option value="06/01/2024" {{ $organization->date_format == '06/01/2024' ? 'selected' : '' }}>06/01/2024</option>
                <option value="June 1,2024" {{ $organization->date_format == 'June 1,2024' ? 'selected' : '' }}>June 1,2024</option>
                <option value="Saturday,June 1,2024" {{ $organization->date_format == 'Saturday,June 1,2024' ? 'selected' : '' }}>Saturday,June 1,2024</option>
            </select>
        </div>
        <div class="form-group">
            <label for="fiscal_year_start_date">Fiscal Year Start Date</label>
            <input type="date" name="fiscal_year_start_date" class="form-control" value="{{ $organization->fiscal_year_start_date }}">
        </div>
        <div class="form-group">
            <label for="fiscal_year_end_date">Fiscal Year End Date</label>
            <input type="date" name="fiscal_year_end_date" class="form-control" value="{{ $organization->fiscal_year_end_date }}">
        </div>
        <div class="form-group">
            <label for="invite_expiry_period">Invite Expiry Period (in Days)</label>
            <input type="number" name="invite_expiry_period" class="form-control" min="1" value="{{ $organization->invite_expiry_period }}">
        </div>
        <div class="form-group form-check">
            <input type="checkbox" name="allow_users_to_send_invites" class="form-check-input" {{ $organization->allow_users_to_send_invites ? 'checked' : '' }}>
            <label class="form-check-label" for="allow_users_to_send_invites">Allow Users to Send Invites</label>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div>
@endsection
