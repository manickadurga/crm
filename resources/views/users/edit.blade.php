@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit User</h1>
    <form action="{{ route('users.update', $user->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" class="form-control" id="username" name="username" value="{{ $user->username }}" required>
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" class="form-control" id="email" name="email" value="{{ $user->email }}" required>
        </div>
        <div class="form-group">
            <label for="password">Password (leave blank if not changing):</label>
            <input type="password" class="form-control" id="password" name="password">
        </div>
        <div class="form-group">
            <label for="role">Role:</label>
            <select class="form-control" id="role" name="role" required onchange="toggleCandidateFields()">
                @foreach($roles as $role)
                <option value="{{ $role }}" {{ $user->role == $role ? 'selected' : '' }}>{{ $role }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="first_name">First Name:</label>
            <input type="text" class="form-control" id="first_name" name="first_name" value="{{ $user->first_name }}">
        </div>
        <div class="form-group">
            <label for="last_name">Last Name:</label>
            <input type="text" class="form-control" id="last_name" name="last_name" value="{{ $user->last_name }}">
        </div>
        <div class="form-group">
            <label for="imageurl">Image URL:</label>
            <input type="url" class="form-control" id="imageurl" name="imageurl" value="{{ $user->imageurl }}">
        </div>
        <div class="form-group" id="candidate-fields" style="{{ ($user->role == 'CANDIDATE' || $user->role == 'EMPLOYEE') ? 'display:block;' : 'display:none;' }}">
            <label for="applied_date">Applied Date:</label>
            <input type="date" class="form-control" id="applied_date" name="applied_date" value="{{ $user->applied_date }}">
            <label for="rejection_date">Rejection Date:</label>
            <input type="date" class="form-control" id="rejection_date" name="rejection_date" value="{{ $user->rejection_date }}">
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div>

<script>
function toggleCandidateFields() {
    const role = document.getElementById('role').value;
    const candidateFields = document.getElementById('candidate-fields');
    if (role === 'CANDIDATE' || role === 'EMPLOYEE') {
        candidateFields.style.display = 'block';
    } else {
        candidateFields.style.display = 'none';
    }
}
document.getElementById('role').addEventListener('change', toggleCandidateFields);
window.onload = toggleCandidateFields; // Initialize on page load
</script>
@endsection
