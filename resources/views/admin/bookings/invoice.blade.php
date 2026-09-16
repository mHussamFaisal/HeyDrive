@extends('admin.index')

@section('title', trans('admin/bookings.page_title') .' / '. $booking->getRefNumber() .' / '. trans('admin/bookings.subtitle.invoice'))
@section('subtitle', /*'<i class="fa fa-file-pdf-o"></i> '*/ '<a href="'. route('admin.bookings.index') .'">'. trans('admin/bookings.page_title') .'</a> / <a href="'. route('admin.bookings.show', $booking->id) .'">'. $booking->getRefNumber() .'</a> / '. trans('admin/bookings.subtitle.invoice') )

@section('subcontent')
<div id="booking-invoice">
    @include('partials.alerts.success')
    @include('partials.alerts.errors')

    <div id="invoice-container">
        @php
        $invoice = $booking->getInvoice();

        $style = '';
        preg_match("~<style.*?>(.*?)<\/style>~is", $invoice, $match);
        if( !empty($match[0]) ) {
            $style = preg_replace('/(<body.*?>)(.*?)(<\/body>)/s', '$2', $match[0]);
        }

        $body = '';
        preg_match("~<body.*?>(.*?)<\/body>~is", $invoice, $match);
        if( !empty($match[0]) ) {
            $body = preg_replace('/(<body.*?>)(.*?)(<\/body>)/s', '$2', $match[0]);
        }
        @endphp

        {!! $style !!}
        {!! $body !!}
    </div>

    <style>
    @media (max-width:500px) {
      #invoice-container table,
      #invoice-container thead,
      #invoice-container tbody,
      #invoice-container tfoot,
      #invoice-container tr,
      #invoice-container th,
      #invoice-container td {
        display: block !important;
        width: 100% !important;
        box-sizing: border-box !important;
        text-align: left !important;
      }
      #invoice-container .small-devices {
        display: block !important;
      }
      #invoice-container th {
        display: none !important;
      }
      #invoice-container table.small-devices-innertable td {
        padding-left: 0px !important;
      }
      #invoice-container>div::after {
        content: "" !important;
        display: block !important;
        clear: both !important;
      }
    }
    </style>

    <a href="{{ route('admin.bookings.invoice', ['id' => $booking->id, 'action' => 'download']) }}" class="btn btn-md btn-default">
        <i class="fa fa-download"></i> <span>{{ trans('admin/bookings.invoice.button.download') }}</span>
    </a>
    <a href="#" onclick="printContent('invoice-container'); return false;" class="btn btn-md btn-default">
        <i class="fa fa-print"></i> <span>{{ trans('admin/bookings.invoice.button.print') }}</span>
    </a>
</div>
@stop


@section('subfooter')
<script>
function printContent(el) {
    var restorepage = document.body.innerHTML;
    var printcontent = document.getElementById(el).innerHTML;
    document.body.innerHTML = printcontent;
    window.print();
    document.body.innerHTML = restorepage;
}
</script>
@endsection
