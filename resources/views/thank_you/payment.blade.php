<div style="margin: 0px auto; max-width: 800px;">
    {{-- <h2 style="margin:0px 0px 30px 0px; text-align:center;">
        {{ trans('thank_you.payment.line1', [
            'company_name' => $company->name
        ]) }}
    </h2> --}}

    @php
    $msg = '';
    $color = '';

    if( !empty($transaction) && !in_array($transaction->payment_method, ['cash', 'account', 'bacs', 'none']) && $transaction->status != 'paid' ) {
        $msg = $transaction->status;
        $color = $transaction->getStatus('color_value');

        if(\Lang::has('thank_you.payment.status_'. $transaction->status)) {
            $msg = trans('thank_you.payment.status_'. $transaction->status);
        }
    }
    @endphp

    @if( $msg )
        <p style="text-align:center; font-size:18px; margin-bottom:40px; color:{{ $color }};">
            {{ $msg }}
        </p>
    @elseif( $booking->status == 'requested' )
        {{-- <p class="alert alert-info">
            {!! trans('thank_you.payment.line2', [
                'status' => '<strong>'. trans('thank_you.payment.requested') .'</strong>',
                'time' => '<strong>'. $settings->booking_request_time .' h</strong>'
            ]) !!}<br />

            {{ trans('thank_you.payment.line3') }}
        </p> --}}
        <p style="text-align:center; font-size:18px; margin-bottom:40px;">
            {!! trans('thank_you.payment.awaits', [
                'status' => '<strong style="color:blue;">'. trans('thank_you.payment.requested') .'</strong>',
            ]) !!}
        </p>
    @else
        <p style="text-align:center; font-size:18px; margin-bottom:40px;">
            {!! trans('thank_you.payment.line4', [
                'status' => '<strong style="color:#008000;">'. trans('thank_you.payment.received') .'</strong>',
            ]) !!}
        </p>
    @endif

    @if( request('tMSG') )
        <div class="alert alert-warning">{{ request('tMSG') }}</div>
    @endif

    <p>
        {!! trans('thank_you.payment.line5', [
            'ref_number' => '<strong style="color:#CE0000; font-size: 16px;">'. $booking->getRefNumber() .'</strong>'
        ]) !!}
    </p>

    @if(trans('thank_you.payment.line2') && $booking->status == 'requested')
    <p>
        {!! trans('thank_you.payment.line2', [
          'time' => '<strong>'. $settings->booking_request_time .'</strong>'
        ]) !!}
    </p>
    @endif

    @if( !$msg )
    <br>
    <p>
        {!! trans('thank_you.payment.line6'. ($booking->status == 'requested' ? '_awaits' : ''), [
            'company_email' => '<strong>'. $company->email .'</strong>'
        ]) !!}
        {!! trans('thank_you.payment.line7') !!}
    </p>
    @endif

    @if( !empty(trans('thank_you.account_info_unregistered')) || !empty(trans('thank_you.account_info_registered')) )
        <div style="margin-top:30px;">
            @if ( !empty(trans('thank_you.account_info_unregistered')) )
                <p>{!! App\Helpers\SiteHelper::nl2br2(trans('thank_you.account_info_unregistered')) !!}</p>
            @endif
            @if ( !empty(trans('thank_you.account_info_registered')) )
                <p>{!! App\Helpers\SiteHelper::nl2br2(trans('thank_you.account_info_registered')) !!}</p>
            @endif
        </div>
    @endif

    @if( !empty($transaction) && $transaction->id )
    <p style="margin-top: 30px;">
        <span style="display:block; clear:both;" class="clearfix">
          <span style="display:inline-block; float:left; margin-right:10px;">
            {{ trans('thank_you.amount') }}: {{ App\Helpers\SiteHelper::formatPrice($transaction->amount + $transaction->payment_charge) }}
          </span>
          <span style="display:inline-block; float:left;">
            {{ trans('thank_you.payment_name') }}: {{ $transaction->payment_name }}
          </span>
        </span>
        @if( !empty($payment) && !empty($payment->params->additional_details) )
            <br><br>{!! App\Helpers\SiteHelper::nl2br2(App\Helpers\SiteHelper::translate($payment->params->additional_details)) !!}
        @endif
    </p>
    @endif

    <p style="margin:40px 0px 0px 0px;">
        @if( $booking->status == 'requested' )
            {!! trans('thank_you.payment.line3') !!}
        @else
            {!! trans('thank_you.payment.line8') !!}
        @endif
    </p>
    <p style="margin-top:10px;">
        <span>{{ $company->name }}</span> {{ trans('thank_you.payment.team') }}
    </p>
    <p style="margin:50px 0px 0px 0px; text-align:center; ">
        <a href="{{ route('booking.index') }}" target="_top" class="btn btn-primary btn-lg eto-v2-new-booking-link">{{ trans('thank_you.payment.button') }}</a>
    </p>
</div>
