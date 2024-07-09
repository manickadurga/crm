@extends('layouts.app')

@section('content')
<div class="container">
    <h1>User Details</h1>
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">{{ $user->username }}</h5>
            <p class="card-text"><strong>Email:</strong> {{ $user->email }}</p>
            <p class="card-text"><strong>Role:</strong> {{ $user->role }}</p>
            <p class="card-text"><strong>First Name:</strong> {{ $user->first_name }}</p>
            <p class="card-text"><strong>Last Name:</strong> {{ $user->last_name }}</p>
            <p class="card-text"><strong>Image URL:</strong> {{ $user->imageurl }}</p>
            @if($user->role == 'CANDIDATE' || $user->role == 'EMPLOYEE')
            <p class="card-text"><strong>Applied Date:</strong> {{ $user->applied_date }}</p>
            <p class="card-text"><strong>Rejection Date:</strong> {{ $user->rejection_date }}</p>
            @endif
            <a href="{{ route('users.index') }}" class="btn btn-primary">Back</a>
        </div>
    </div>
</div>
@endsection
