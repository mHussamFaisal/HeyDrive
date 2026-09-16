<div style="margin: 0px auto; max-width: 800px;">
    {{-- <h2 style="margin:0px 0px 30px 0px; text-align:center;">
        {{ trans('thank_you.booking.line1', [
            'company_name' => $company->name
        ]) }}
    </h2> --}}
    @if( $booking->status == 'requested' )
        {{-- <p class="alert alert-info">
            {!! trans('thank_you.booking.line2', [
                'status' => '<strong>'. trans('thank_you.booking.requested') .'</strong>',
                'time' => '<strong>'. $settings->booking_request_time .' h</strong>'
            ]) !!}<br />

            {{ trans('thank_you.booking.line3') }}
        </p> --}}
        <p style="text-align:center; font-size:18px; margin-bottom:40px;">
            {!! trans('thank_you.booking.awaits', [
                'status' => '<strong style="color:blue;">'. trans('thank_you.booking.requested') .'</strong>',
            ]) !!}
        </p>
    @else
        <p style="text-align:center; font-size:18px; margin-bottom:40px;">
            {!! trans('thank_you.booking.line4', [
                'status' => '<strong style="color:#008000;">'. trans('thank_you.booking.received') .'</strong>',
            ]) !!}
        </p>
    @endif
    <p>
        {!! trans('thank_you.booking.line5', [
            'ref_number' => '<strong style="color:#CE0000; font-size: 16px;">'. $booking->getRefNumber() .'</strong>'
        ]) !!}
    </p>

    @if(trans('thank_you.booking.line2') && $booking->status == 'requested')
    <p>
        {!! trans('thank_you.booking.line2', [
          'time' => '<strong>'. $settings->booking_request_time .'</strong>'
        ]) !!}
    </p>
    @endif

    <br />
    <p>
        {!! trans('thank_you.booking.line6'. ($booking->status == 'requested' ? '_awaits' : ''), [
            'company_email' => '<strong>'. $company->email .'</strong>'
        ]) !!}
        {!! trans('thank_you.booking.line7') !!}
    </p>

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

    <p>
        {!! trans('thank_you.booking.line8', [
            'link' => '<a href="'. $company->url_contact .'" target="_top">'. trans('thank_you.booking.line8_link') .'</a>'
        ]) !!}
    </p>

    @if( !empty($transaction) && $transaction->id )
    <p style="margin-top:30px;">
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

    <p style="margin:20px 0px 0px 0px;">
        @if( $booking->status == 'requested' )
            {!! trans('thank_you.booking.line3') !!}
        @else
            {!! trans('thank_you.booking.line9') !!}
        @endif
    </p>
    <p style="margin-top:10px;">
        <span>{{ $company->name }}</span> {{ trans('thank_you.booking.team') }}
    </p>
    <p style="margin:50px 0px 0px 0px; text-align:center; ">
        <a href="{{ route('booking.index') }}" target="_top" class="btn btn-primary btn-lg eto-v2-new-booking-link">{{ trans('thank_you.booking.button') }}</a>
    </p>
</div>
