@extends('admin.index')

@section('title', trans('admin/calendar.page_title'))
@section('subtitle', /*'<i class="fa fa-calendar"></i> '.*/ trans('admin/calendar.page_title'))


@section('subheader')
<link rel="stylesheet" href="{{ asset_url('plugins','fullcalendar/fullcalendar.min.css') }}">
<link rel="stylesheet" href="{{ asset_url('plugins','bootstrap-daterangepicker/daterangepicker.css') }}">
@endsection


@section('subcontent')
<div id="calendar">
    @include('partials.loader')
    @include('partials.modals.popup')

    <div style="display:table; width:100%;">
        <div class="eto-fullcalendar">
            <div id="fullcalendar"></div>
        </div>
    </div>
</div>
@endsection


@section('subfooter')
<script src="{{ asset_url('plugins','moment/moment-with-locales.min.js') }}"></script>
<script src="{{ asset_url('plugins','fullcalendar/fullcalendar.min.js') }}"></script>
<script src="{{ asset_url('plugins','fullcalendar/locale-all.js') }}"></script>
<script src="{{ asset_url('plugins','bootstrap-daterangepicker/daterangepicker.js') }}"></script>

<script>
function isInteger(n){
    if (parseInt(n) === 0) {
        return true;
    }
    return typeof parseInt(n) == 'number' && typeof n != 'undefined' && n != '' && n != 'undefined' && parseInt(n) % 1 === 0;
}

$(document).ready(function() {
    var url = '{!! route('admin.calendar.events') !!}';

    if (ETO.model === false) {
        ETO.init({ config: ['subscription'], lang: [] }, 'calendar');
    }

    $('#modal-popup').modal({
        show: false,
    });

    var calendar = $('#fullcalendar').fullCalendar({
        customButtons: {
            popupInfo: {
                text: '[ i ]',
                click: function() {
                    var html = '';

                    @foreach((new\App\Models\BookingRoute)->options->status as $status)
                        html += '<div><span style="color:{{ $status['color'] }};">{{ $status['name'] }}</span></div>';
                    @endforeach

                    $('#modal-popup .modal-title').html('{{ trans('admin/calendar.help_title') }}');
                    $('#modal-popup .modal-body').html('{{ trans('admin/calendar.help_desc') }}:<br /><br />'+ html);

                    $('#modal-popup').modal('show').off('hidden.bs.modal');
                }
            },
        },
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'listSearch,month,agendaWeek,agendaDay,listDay,listWeek,popupInfo'
        },
        locale: '{{ app()->getLocale() }}',
        defaultView: (localStorage.getItem('fc_admin_current_view') !== null ? localStorage.getItem('fc_admin_current_view') : 'agendaDay'),
        defaultDate: (localStorage.getItem('fc_admin_current_date') !== null ? localStorage.getItem('fc_admin_current_date') : null),
        contentHeight: 'auto',
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
            },
            listSearch: {
                buttonText: '{{ trans('common.button.search') }}',
                type: 'list',
            }
        },
        firstDay: {{ config('site.start_of_week') }},
        slotLabelFormat: '{{ config('site.time_format') == 'H:i' ? 'HH(:mm)' : 'h(:mm)a' }}',
        timeFormat: '{{ config('site.time_format') == 'H:i' ? 'HH(:mm)' : 'h(:mm)a' }}',
        viewRender: function (view, element) {
            localStorage.setItem('fc_admin_current_view', view.name);
            localStorage.setItem('fc_admin_current_date', view.intervalStart.format());
        },
        events: function(start, end, timezone, callback) {
            var view = this.view.name,
                data = {},
                keywords = $('.eto-input-search').length > 0 ? $('.eto-input-search').val() : '',
                from = Date.parse($('.eto-input-from-hidden').val()+":00-0000")/1000,
                to = Date.parse($('.eto-input-to-hidden').val()+":00-0000")/1000;

            if (view == 'listSearch') {
                if (keywords.length > 0) {
                    data = {keywords: keywords};
                }
                if(isInteger(from)) {
                    data.viewStart = from;
                }
                if(isInteger(to)) {
                    data.viewEnd = to;
                }
            }

            if (view == 'listSearch' && keywords.length === 0 && isInteger(from) === false && isInteger(to) === false) {
                callback([]);
                return;
            }
            else if(view != 'listSearch') {
                data = {
                    viewStart: start.unix(),
                    viewEnd: end.unix()
                };
            }

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': EasyTaxiOffice.csrfToken
                },
                url: url,
                dataType: 'json',
                cache: false,
                data: data,
                success: function (obj) {
                    if(null === obj || obj.time.start === false) {
                        callback([]);
                        return;
                    }
                    else if (view == 'listSearch' && (keywords.length > 0 || $('.eto-input-from-hidden').val().length > 0 || $('.eto-input-to-hidden').val().length > 0)) {
                        var from = moment(obj.time.start).format('YYYY-MM-DD 00:00:00'),
                            to =  moment(obj.time.end).add(1,'day').format('YYYY-MM-DD 00:00:00');

                        $('#fullcalendar').fullCalendar('option', 'visibleRange', {
                            start: from,
                            end: to,
                        });
                    }
                    callback(obj.data);
                },
                error: function() {
                    $('#loader').show();
                }
            });
        },
        eventRender: function(event, element) {
            var title = event.title;
            if ( event.description ) {
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
            if (view.type == 'month') {
                calendar.fullCalendar('gotoDate', calEvent.start);
                calendar.fullCalendar('changeView', 'agendaDay');
            }
            else {
                var url = calEvent.url_show + ((calEvent.url_show.indexOf('?') < 0) ? '?' : '&') + 'tmpl=body';
                var title = '{{ trans('admin/bookings.button.show') }}';
                var html = '<iframe src="'+ url +'" frameborder="0" height="400" width="100%"></iframe>';
                var modal = $('#modal-popup');
                var iframe = modal.find('iframe');

                if (iframe.length > 0) {
                    iframe.attr('src', url);
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

                modal.find('.modal-title').html(title);
                modal.modal('show')
                  .off('hidden.bs.modal')
                  .on('hidden.bs.modal', function(e) {
                      calendar.fullCalendar('refetchEvents');
                  });
            }
            return false;
        },
        loading: function(bool) {
            if (!bool) {
                $('#loader').hide();
            }
            else{
                $('#loader').show();
            }
        },
        eventAfterAllRender : function (view) {
            if (view.name == 'listSearch') {
                if ($('.eto-input-search').length === 0) {
                    $('.fc-toolbar').after('<div class="row clearfix eto-search-form">\
                        <input type="hidden" class="eto-input-from-hidden">\
                        <input type="hidden" class="eto-input-to-hidden">\
                        <input placeholder="{{ trans('admin/calendar.search') }}" class="form-control eto-input-search pull-left">\
                        <input placeholder="{{ trans('admin/calendar.from') }}" class="form-control eto-input-from pull-left" type="text">\
                        <span class="pull-left"> <i class="fa fa-minus" style="margin: 10px 0 0 0;"></i> </span>\
                        <input placeholder="{{ trans('admin/calendar.to') }}" class="form-control eto-input-to pull-left" type="text">\
                        </div>');

                    var inputs = $('.eto-input-from, .eto-input-to'),
                        format = ETO.convertDate(ETO.config.date_format) + ' ' + ETO.convertTime(ETO.config.time_format);

                    inputs.each(function (key, input) {
                        $(input).daterangepicker({
                            singleDatePicker: true,
                            showDropdowns: true,
                            timePicker: true,
                            timePicker24Hour: ETO.config.time_format == 'H:i' ? true : false,
                            autoUpdateInput: false,
                            timePickerIncrement: 5,
                            locale: {
                                format: format,
                                firstDay: parseInt(ETO.config.date_start_of_week)
                            },
                            ranges: {
                                'Today': [moment(), moment()],
                                'Tomorrow': [moment().add(1, 'days'), moment().add(1, 'days')],
                                'After Tomorrow': [moment().add(2, 'days'), moment().add(2, 'days')],
                                'Next Week': [moment().add(7, 'days'), moment().add(7, 'days')],
                                'Next Month': [moment().add(1, 'months'), moment().add(1, 'months')]
                            },
                        })
                        .on('apply.daterangepicker', function(ev, picker) {
                            if($(input).hasClass('eto-input-from')) {
                                $(input).closest('.eto-search-form').find('.eto-input-from-hidden').val(picker.startDate.format('YYYY-MM-DD HH:mm')).change();
                            }
                            else if($(input).hasClass('eto-input-to')) {
                                $(input).closest('.eto-search-form').find('.eto-input-to-hidden').val(picker.startDate.format('YYYY-MM-DD HH:mm')).change();
                            }
                            $(input).val(picker.startDate.format(format)).change();
                        });
                    });
                }
                $('.eto-search-form').removeClass('hidden');
                $('#fullcalendar').fullCalendar('changeView', view.name);
            }
            else {
                $('.eto-search-form').addClass('hidden');
            }
        }
    });

    $('body').on('change', '.eto-search-form input', function (e) {
        if($(this).hasClass('eto-input-from') && $(this).val() == '') {
            $('.eto-input-from-hidden').val('').change();
        }
        else if($(this).hasClass('eto-input-to') && $(this).val() == '') {
            $('.eto-input-to-hidden').val('').change();
        }

        $('#fullcalendar').fullCalendar('refetchEvents');
    });
});
</script>
@endsection
