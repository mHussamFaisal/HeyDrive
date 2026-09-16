@extends('layouts.app')

@section('title', trans('booking.page_title_availability'))

@section('header')
    <link rel="stylesheet" href="{{ asset_url('plugins','fullcalendar/fullcalendar.min.css') }}">
    <link rel="stylesheet" href="{{ asset_url('css','booking.css') }}?_dc={{ config('app.timestamp') }}">
@endsection

@section('content')
    <div id="calendar">
        @include('partials.loader')

        <div style="display:table; width:100%;">
            <div style="display:table-cell; width:100%; height:100%; position:relative;">
                <div id="fullcalendar"></div>
            </div>
        </div>
    </div>
@endsection

@section('footer')
    <script src="{{ asset_url('plugins','moment/moment-with-locales.min.js') }}"></script>
    <script src="{{ asset_url('plugins','fullcalendar/fullcalendar.min.js') }}"></script>
    <script src="{{ asset_url('plugins','jquery-scrollTo/jquery-scrollTo.min.js') }}"></script>

    <script type="text/javascript">
    $(document).ready(function() {
        // http://fullcalendar.io/docs/
        // http://momentjs.com/docs/#/displaying/format/

        var calendar = $('#fullcalendar').fullCalendar({
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'month,agendaWeek,agendaDay' //,basicWeek,basicDay,listDay,listWeek
            },
            locale: '{{ $locale }}',
            defaultView: 'agendaDay',
            defaultDate: '{{ $defaultDate }}',
            contentHeight: 'auto',
            displayEventTime: false,
            // ignoreTimezone: true,
            // timezone: '{{ config('app.timezone') }}',
            // aspectRatio: 1,
            nowIndicator: true,
            editable: false,
            eventLimit: true,
            eventLimitClick: 'agendaDay',
            views: {
                month: {
                    eventLimit: 2
                },
                basicWeek: {
                    eventLimit: 12
                },
                basicDay: {
                    eventLimit: 24
                },
                agendaWeek: {
                    buttonText: 'week'
                },
                agendaDay: {
                    buttonText: 'day'
                },
                listDay: {
                    buttonText: 'list day'
                },
                listWeek: {
                    buttonText: 'list week'
                }
            },
            firstDay: {{ config('site.start_of_week') }},
            slotLabelFormat: '{{ config('site.time_format') == 'H:i' ? 'HH(:mm)' : 'h(:mm)a' }}',
            timeFormat: '{{ config('site.time_format') == 'H:i' ? 'HH(:mm)' : 'h(:mm)a' }}',
            eventOrder: 'ordering,title',
            events: function(start, end, timezone, callback) {
                // console.log(start, end, timezone);
                $.ajax({
                    headers : {
                        'X-CSRF-TOKEN': EasyTaxiOffice.csrfToken
                    },
                    url: '{!! $url !!}',
                    dataType: 'json',
                    cache: false,
                    data: {
                        viewStart: start.unix(),
                        viewEnd: end.unix()
                    },
                    success: function(obj) {
                        callback(obj);
                    }
                });
            },
            dayClick: function(date, jsEvent, view) {
                if (view.name == 'month' || view.type == 'agendaWeek') {
                    calendar.fullCalendar('gotoDate', date);
                    calendar.fullCalendar('changeView', 'agendaDay');
                }
                return false;
            },
            eventClick: function(calEvent, jsEvent, view) {
                if (view.type == 'month' || view.type == 'agendaWeek') {
                    calendar.fullCalendar('gotoDate', calEvent.start);
                    calendar.fullCalendar('changeView', 'agendaDay');
                }
                return false;
            },
            eventAfterAllRender: function(view) {
                if ('agendaDay' === view.name) {
                    if($('.fc-time-grid-event').length>0) {
                        var renderedEvents = $('div.fc-event-container a');
                        var firstEventOffsetTop = renderedEvents && renderedEvents.length > 0 ?renderedEvents[0].offsetTop : 0;
                        $('body').scrollTo(firstEventOffsetTop+'px');
                    }
                }
            },
            viewRender: function(currentView){
                var minDate = moment(),
                maxDate = moment().add(1,'years');
                // Past
                if (minDate >= currentView.start && minDate <= currentView.end) {
                    $(".fc-prev-button").prop('disabled', true);
                    $(".fc-prev-button").addClass('fc-state-disabled');
                } else {
                    $(".fc-prev-button").removeClass('fc-state-disabled');
                    $(".fc-prev-button").prop('disabled', false);
                }
                // Future
                if (maxDate >= currentView.start && maxDate <= currentView.end) {
                    $(".fc-next-button").prop('disabled', true);
                    $(".fc-next-button").addClass('fc-state-disabled');
                } else {
                    $(".fc-next-button").removeClass('fc-state-disabled');
                    $(".fc-next-button").prop('disabled', false);
                }
            },
            loading: function(bool) {
                if( !bool ) {
                    $('#loader').hide();
                }
                else{
                    $('#loader').show();
                }
            }
        });
    });
    </script>
@endsection
