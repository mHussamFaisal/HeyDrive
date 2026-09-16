@extends('emails.template')

@section('title', $subject)

@section('content')

    {{ trans('emails.customer_account_activation.greeting', [
        'name' => $customerName
    ]) }},<br />

    @if( !empty($additionalMessage) )
        {!! $additionalMessage !!}
    @endif

    {!! trans('emails.customer_account_activation.line1', [
        'token' => '<strong>'. $token .'</strong>'
    ]) !!}<br /><br />

    <a href="{{ $link }}" target="_blank" style="color:#333;">{{ $link }}</a>

@stop
