@extends('customers::layouts.master')

@section('content')
    <h1>Hello World</h1>

    <p>Module: {!! config('customers.name') !!}</p>
@endsection
