@extends('layouts.app1')

@section('content')
<div class="container">
    <h2>Organizations</h2>
    <a href="{{ route('organizations.create') }}" class="btn btn-primary mb-3">Create New Organization</a>

    @if ($message = Session::get('success'))
        <div class="alert alert-success">
            <p>{{ $message }}</p>
        </div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>Organization Name</th>
                <th>Official Name</th>
                <th>Country</th>
                <th>City</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($organizations as $organization)
                <tr>
                    <td>{{ $organization->organizationid }}</td>
                    <td>{{ $organization->organization_name }}</td>
                    <td>{{ $organization->official_name }}</td>
                    <td>{{ $organization->country }}</td>
                    <td>{{ $organization->city }}</td>
                    <td>
                        <a href="{{ route('organizations.show', $organization->organizationid) }}" class="btn btn-info">Show</a>
                        <a href="{{ route('organizations.edit', $organization->organizationid) }}" class="btn btn-primary">Edit</a>
                        <form action="{{ route('organizations.destroy', $organization->organizationid) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
