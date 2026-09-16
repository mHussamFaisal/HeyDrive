@extends('layouts.app')

@section('title', trans('booking.page.pay.page_title'))

@section('header')
    <link rel="stylesheet" href="{{ asset_url('css','booking.css') }}?_dc={{ config('app.timestamp') }}">
    @include('partials.override_css')
@endsection

@section('content')
    <div id="booking-pay">
        @include('partials.loader')

        <div class="info-box bg-gray">
            <span class="info-box-icon">
                <i class="ion-ios-cart-outline"></i>
            </span>
            <div class="info-box-content">
                <span class="info-box-text">
                    {!! App\Helpers\SiteHelper::nl2br2(trans('booking.page.pay.title', [
                        'name' => $payment->name
                    ])) !!}
                </span>
                @if( trans('booking.page.pay.desc') )
                    <span class="progress-description">
                        {!! App\Helpers\SiteHelper::nl2br2(trans('booking.page.pay.desc')) !!}
                    </span>
                @endif
            </div>
        </div>

        @if( 0 )
            <div class="payment-history">
                @foreach ($transactions as $transaction)
                    {{ $transaction->booking->getRefNumber() }}, {{ $transaction->name }} {{ App\Helpers\SiteHelper::formatPrice($transaction->amount + $transaction->charge) }}, {{ $transaction->getPaymentMethod() }} - {{ $transaction->getStatus() }}<br>
                @endforeach
                {{ trans('booking.page.pay.total') }}: {{ App\Helpers\SiteHelper::formatPrice($total) }}
            </div>
        @endif

        {!! $html !!}

        @include('partials.branding')
    </div>
@endsection

@section('footer')
    <script>
    $(window).load(function() {
        $('#loader').hide();
    });

    $(document).ready(function() {
        // Tooltip
        $('#booking-pay [title]').tooltip({
            placement: 'auto',
            container: 'body',
            selector: '',
            html: true,
            trigger: 'hover',
            delay: {
                show: 500,
                hide: 100
            }
        });
    });
    </script>
@endsection
