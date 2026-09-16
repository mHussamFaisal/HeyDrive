@extends('driver.index')

@section('title', trans('driver/calendar.page_title'))
@section('subtitle', /*'<i class="fa fa-calendar"></i> '.*/ trans('driver/calendar.page_title'))


@section('subheader')
<link rel="stylesheet" href="{{ asset_url('plugins','fullcalendar/fullcalendar.min.css') }}">
@stop


@section('subcontent')
<div id="calendar">
    @include('partials.loader')
    @include('partials.modals.popup')

    <div style="display:table; width:100%;">
        <div style="display:table-cell; width:100%; height:100%; position:relative;">
            <div id="fullcalendar"></div>
        </div>
    </div>
</div>
@stop


@section('subfooter')
<script src="{{ asset_url('plugins','moment/moment-with-locales.min.js') }}"></script>
<script src="{{ asset_url('plugins','fullcalendar/fullcalendar.min.js') }}"></script>
<script src="{{ asset_url('plugins','fullcalendar/locale-all.js') }}"></script>

<script>
$(document).ready(function() {
    $('#modal-popup').modal({
        show: false,
    });

    var calendar = $('#fullcalendar').fullCalendar({
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay,listDay,listWeek'
        },
        locale: '{{ app()->getLocale() }}',
        defaultView: (localStorage.getItem('fc_driver_current_view') !== null ? localStorage.getItem('fc_driver_current_view') : 'agendaDay'),
        defaultDate: (localStorage.getItem('fc_driver_current_date') !== null ? localStorage.getItem('fc_driver_current_date') : null),
        contentHeight: 'auto',
        // ignoreTimezone: true,
        // timezone: '{{ config('app.timezone') }}',
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
        viewRender: function (view, element) {
            localStorage.setItem('fc_driver_current_view', view.name);
            localStorage.setItem('fc_driver_current_date', view.intervalStart.format());
        },

        @if( config('site.allow_driver_availability') )
        selectable: true,
        selectHelper: true,
        select: function(start, end, allDay) {
            @permission('driver.calendar.create')
            // console.log(start, end, allDay);
            var url = '{!! route('driver.calendar.create') !!}';
            url += ((url.indexOf('?') < 0) ? '?' : '&') + 'tmpl=body&start='+ start.unix() +'&end='+ end.unix();

            var title = '{!! trans('driver/calendar.subtitle.create') !!} {!! trans('driver/calendar.subtitle.event') !!}';
            var html = '<iframe src="'+ url +'" frameborder="0" height="400" width="100%"></iframe>';
            var modal = $('#modal-popup');

            if(modal.find('iframe').length > 0) {
                modal.find('iframe').attr('src', url);
            }
            else {
                modal.find('.modal-body').html(html);
                modal.find('iframe').iFrameResize({
                    heightCalculationMethod: 'lowestElement',
                    log: false,
                    targetOrigin: '*',
                    checkOrigin: false
                });
            }

            modal.removeClass('eto-modal-popup-show-booking');
            modal.addClass('eto-modal-popup-add-event');
            modal.find('.modal-title').html(title);
            modal.modal('show')
                .off('hidden.bs.modal')
                .on('hidden.bs.modal', function(e) {
                    calendar.fullCalendar('refetchEvents');
                });

            calendar.fullCalendar('unselect');

            @endpermission
        },
        @endif

        events: function(start, end, timezone, callback) {
            // console.log(start, end, timezone);
            $.ajax({
                headers : {
                    'X-CSRF-TOKEN': EasyTaxiOffice.csrfToken
                },
                url: '{!! route('driver.calendar.index') !!}',
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
        eventRender: function(event, element) {
            var title = event.title;
            if( event.description ) {
                title = event.description;
            }
            $(element).tooltip({title: title});
        },
        dayClick: function(date, jsEvent, view) {
            if (view.name == 'month' || view.type == 'agendaWeek') {
                calendar.fullCalendar('gotoDate', date);
                calendar.fullCalendar('changeView', 'agendaDay');
            }
            return false;
        },
        eventClick: function(calEvent, jsEvent, view) {

            @permission('driver.calendar.edit')
            // console.log(calEvent, jsEvent, view);
            if( calEvent.event_type == 'job' ) {
                var url = '{!! route('driver.jobs.index') !!}/'+ calEvent.id;
                var title = '{!! trans('driver/calendar.subtitle.job') !!}';
            }
            else {
                var url = '{!! route('driver.calendar.index') !!}/'+ calEvent.id +'/edit';
                var title = '{!! trans('driver/calendar.subtitle.edit') !!} {!! trans('driver/calendar.subtitle.event') !!}';
            }

            url += ((url.indexOf('?') < 0) ? '?' : '&') + 'tmpl=body';

            var html = '<iframe src="'+ url +'" frameborder="0" height="400" width="100%"></iframe>';
            var modal = $('#modal-popup');

            if(modal.find('iframe').length > 0) {
                modal.find('iframe').attr('src', url);
            }
            else {
                modal.find('.modal-body').html(html);
                modal.find('iframe').iFrameResize({
                    heightCalculationMethod: 'lowestElement',
                    log: false,
                    targetOrigin: '*',
                    checkOrigin: false
                });
            }

            if( calEvent.event_type == 'job' ) {
                modal.removeClass('eto-modal-popup-add-event');
                modal.addClass('eto-modal-popup-show-booking');
            }
            else {
                modal.removeClass('eto-modal-popup-show-booking');
                modal.addClass('eto-modal-popup-add-event');
            }

            modal.find('.modal-title').html(title);
            modal.modal('show')
              .off('hidden.bs.modal')
              .on('hidden.bs.modal', function(e) {
                  calendar.fullCalendar('refetchEvents');
              });

            return false;

            @endpermission
        },
        loading: function(bool) {
            if (!bool) {
                $('#loader').hide();
            }
            else{
                $('#loader').show();
            }
        }
    });
});
</script>
@stop
