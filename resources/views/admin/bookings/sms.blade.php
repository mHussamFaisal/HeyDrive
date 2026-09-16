@extends('admin.index')

@section('title', trans('admin/bookings.page_title') .' / '. $booking->getRefNumber() .' / '. trans('admin/bookings.subtitle.sms'))
@section('subtitle', /*'<i class="fa fa-commenting"></i> '*/ '<a href="'. route('admin.bookings.index') .'">'. trans('admin/bookings.page_title') .'</a> / <a href="'. route('admin.bookings.show', $booking->id) .'">'. $booking->getRefNumber() .'</a> / '. trans('admin/bookings.subtitle.sms') )


@section('subcontent')
<div id="booking-sms">
    @include('partials.alerts.success')
    @include('partials.alerts.errors')

    <form method="post" id="booking-sms-form" autocomplete="off">
        <div class="row">
            <div class="col-xs-12 col-sm-4">
                <div class="form-group field-from">
                    <label for="from">{{ trans('admin/bookings.sms.from') }} <span id="counter-from" title="{{ trans('admin/bookings.sms.counter') }}"></span></label>
                    <input type="text" name="from" id="from" value="{{ config('site.company_name') }}" required class="form-control">
                </div>
            </div>
            <div class="col-xs-12 col-sm-4">
                <div class="form-group field-to">
                    <label for="to">{{ trans('admin/bookings.sms.to') }}</label>
                    <input type="text" name="to" id="to" value="" required class="form-control">
                </div>
            </div>
            <div class="col-xs-12 col-sm-4">
                <div class="form-group field-contacts">
                    <label for="contacts">{{ trans('admin/bookings.sms.contacts') }}</label>
                    <select name="contacts" id="contacts" class="form-control">
                        @forelse ($contacts as $contact)
                            <option value="{{ $contact->value }}">{{ $contact->text }}</option>
                        @empty
                            <option value="">{{ trans('admin/bookings.sms.select') }}</option>
                        @endforelse
                    </select>
                </div>
            </div>
        </div>
        <div class="form-group field-message">
            <label for="message">{{ trans('admin/bookings.sms.message') }} <span id="counter-message" title="{{ trans('admin/bookings.sms.counter') }}"></span></label>
            <textarea name="message" id="message" required class="form-control"></textarea>
        </div>
        <button type="submit" id="button-send" class="btn btn-md btn-default">
            <span><i class="fa fa-comments"></i></span>
            <span>{{ trans('admin/bookings.sms.button.send') }}</span>
        </button>
        <div id="status-message"></div>
    </form>

    @if( empty(config('services.sms_service_type')) )
        <p class="text-yellow" style="margin-top:10px; margin-bottom:0px;">To send SMS messages you need to activate one of the SMS services in <a href="{{ route('admin.config.integration') }}">settings</a> tab first.</p>
    @endif
</div>
@stop


@section('subfooter')
<script>
function stripTags(string) {
    var decoded_string = $("<div/>").html(string).text();
    return $("<div/>").html(decoded_string).text();
}

function limitText(field, maxChar){
    var ref = $(field),
        val = ref.val();
    if ( val.length >= maxChar ){
        ref.val(function() {
            return val.substr(0, maxChar);
        });
    }
}

$(document).ready(function() {
    var form = $('#booking-sms-form');

    var maxCharsFrom = 11;
    limitText(form.find('#from'), maxCharsFrom);

    form.find('#from').keyup(function() {
        limitText(this, maxCharsFrom);
        var length = $(this).val().length;
        var countMsg = '('+ length +' / '+ maxCharsFrom +')';
        form.find('#counter-from').text(countMsg);
    })
    .trigger('keyup');

    var maxCharsMessage = 765;
    limitText(form.find('#message'), maxCharsMessage);

    form.find('#message').keyup(function() {
        limitText(this, maxCharsMessage);
        var length = $(this).val().length;
        var messagesCount = Math.ceil(length / 160);
        var countMsg = '('+ length +' / '+ maxCharsMessage +')';
        if( messagesCount > 0 ) {
            countMsg += ' ('+ messagesCount +' messages)';
        }
        form.find('#counter-message').text(countMsg);
    })
    .trigger('keyup');

    form.find('#contacts').change(function() {
        form.find('#to').val($(this).val());
    })
    .trigger('change');

    form.find('#button-send').click(function(event) {
        event.preventDefault();

        var from = form.find('#from').val();
        var to = form.find('#to').val();
        var msg = form.find('#message').val();

        if( !from ) {
            alert('Please enter from!');
        }
        else if( !to ) {
            alert('Please enter to!');
        }
        else if( !msg ) {
            alert('Please enter message');
        }
        else {
            $.ajax({
                headers : {
                    'X-CSRF-TOKEN': EasyTaxiOffice.csrfToken
                },
                url: EasyTaxiOffice.appPath +'/etov2?apiType=backend',
                type: 'POST',
                dataType: 'json',
                cache: false,
                data: {
                    task: 'bookings',
                    action: 'sendSMS',
                    from: from,
                    to: to,
                    msg: stripTags(msg)
                },
                success: function(response) {
                    if (response.error_message) {
                        alert(response.error_message);
                    }
                    else {
                        alert('SMS message has been sent.');
                    }
                },
                error: function(response) {
                    // Msg
                }
            });
        }
    });

});
</script>
@endsection
