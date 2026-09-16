@extends('emails.template')

@section('title', $subject)

@section('content')

    {{ trans('emails.customer_payment_confirmed.greeting', [
        'name' => $booking->getContactFullName()
    ]) }},<br />

    @if( !empty($additionalMessage) )
        {!! $additionalMessage !!}
    @endif

    @if( !empty($invoiceEnabled) )
        {{ trans('emails.customer_payment_confirmed.line1') }}<br /><br />

        {!! $invoice !!}
    @else
        {!! trans('emails.customer_payment_confirmed.line2', [
            'company_name' => $company->name
        ]) !!}<br />

        {!! trans('emails.customer_payment_confirmed.line3', [
            'ref_number' => '<strong>'. $booking->getRefNumber() .'</strong>',
            'status' => '<strong>'. trans('emails.customer_payment_confirmed.status') .'</strong>'
        ]) !!}
    @endif

@stop
