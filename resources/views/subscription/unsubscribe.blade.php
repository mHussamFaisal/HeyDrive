<!-- unsubscribe.blade.php -->
@extends('layouts.app')

@section('content')
    <h1>Unsubscribe</h1>
    <form action="{{ url('/cancel-subscription') }}" method="POST">
        @csrf
        <!-- Include cancellation confirmation fields -->
        <button type="submit">Unsubscribe</button>
    </form>
@endsection
