<div style="margin: 0px auto; max-width: 800px;">
    {{-- <h2 style="margin:0px 0px 30px 0px; text-align:center;">
        {{ trans('thank_you.quote.line1', [
            'company_name' => $company->name
        ]) }}
    </h2> --}}
    {{-- <p class="alert alert-info">
        {!! trans('thank_you.quote.line2', [
            'status' => '<strong>'. trans('thank_you.quote.requested') .'</strong>'
        ]) !!}<br />

        {!! trans('thank_you.quote.line3') !!}
    </p> --}}

    <p style="text-align:center; font-size:18px; margin-bottom:40px;">
      {!! trans('thank_you.quote.line2', [
          'status' => '<strong>'. trans('thank_you.quote.requested') .'</strong>'
      ]) !!}
    </p>

    <p>
        {!! trans('thank_you.quote.line4', [
            'ref_number' => '<strong style="color:#CE0000; font-size: 16px;">'. $booking->getRefNumber() .'</strong>'
        ]) !!}
    </p>

    @if(trans('thank_you.quote.line3'))
    <p>
        {!! trans('thank_you.quote.line3') !!}
    </p>
    @endif

    <br />
    <p>
        {!! trans('thank_you.quote.line5', [
            'company_email' => '<strong>'. $company->email .'</strong>'
        ]) !!}
    </p>

    @if(trans('thank_you.quote.line6'))
    <p>
        {{ trans('thank_you.quote.line6') }}
    </p>
    @endif

    <p style="margin:40px 0px 0px 0px;">
        {{ trans('thank_you.quote.line7') }}
    </p>
    <p style="margin-top:10px;">
        <span>{{ $company->name }}</span> {{ trans('thank_you.quote.team') }}
    </p>
    <p style="margin:50px 0px 0px 0px; text-align:center; ">
        <a href="{{ route('booking.index') }}" target="_top" class="btn btn-primary btn-lg eto-v2-new-booking-link">{{ trans('thank_you.quote.button') }}</a>
    </p>
</div>
