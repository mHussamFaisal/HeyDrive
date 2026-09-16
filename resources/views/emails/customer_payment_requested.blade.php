@extends('emails.template')

@section('title', $subject)

@section('content')

    {{ trans('emails.customer_payment_requested.greeting', [
        'name' => $booking->getContactFullName()
    ]) }},<br />

    @if( !empty($additionalMessage) )
        {!! $additionalMessage !!}
    @endif

    {!! trans('emails.customer_payment_requested.line1', [
        'price' => '<strong>'. $price .'</strong>'
    ]) !!}<br />

    {!! trans('emails.customer_payment_requested.line2', [
        'link' => '<a href="'. $payUrl .'" target="_blank" style="color:#333; text-decoration:underline;">'. trans('emails.customer_payment_requested.link') .'</a>'
    ]) !!}

@stop
