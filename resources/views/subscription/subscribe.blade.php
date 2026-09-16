<!-- subscribe.blade.php -->
@extends('layouts.app')

@section('content')
    <h1>Subscribe</h1>
    <form action="{{ url('/subscribe') }}" method="POST">
        @csrf
        <!-- Include subscription form fields -->
        <button type="submit">Subscribe</button>
    </form>
@endsection
