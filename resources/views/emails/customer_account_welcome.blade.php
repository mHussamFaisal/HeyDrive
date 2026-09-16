@extends('emails.template')

@section('title', $subject)

@section('content')

    {{ trans('emails.customer_account_welcome.greeting', [
        'name' => $customerName
    ]) }},<br />

    @if( !empty($additionalMessage) )
        {!! $additionalMessage !!}
    @endif

    {{ trans('emails.customer_account_welcome.line1') }}<br /><br />

    <a href="{{ $link }}" target="_blank" style="color:#333;">{{ $link }}</a>

@stop
