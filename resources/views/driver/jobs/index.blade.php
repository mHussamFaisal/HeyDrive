@extends('driver.index')

@section('title', trans('driver/jobs.page_title'))
@section('subtitle', /*'<i class="fa fa-tasks"></i> '.*/ trans('driver/jobs.page_title'))

@section('subheader')
    <link rel="stylesheet" href="{{ asset_url('plugins','data-tables/dataTables.bootstrap.css') }}">
    <link rel="stylesheet" href="{{ asset_url('plugins','data-tables/extensions/Buttons/css/buttons.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset_url('plugins','data-tables/extensions/Buttons/css/buttons.bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset_url('plugins','data-tables/extensions/ColReorder/css/colReorder.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset_url('plugins','data-tables/extensions/ColReorder/css/colReorder.bootstrap.min.css') }}">
@stop

@section('subcontent')
    @include('partials.alerts.success')
    @include('partials.alerts.errors')
    @include('partials.modals.delete')

    <div id="jobs">
        {!! $builder->table(['class' => 'table table-hover', 'width' => '100%', 'data-form' => 'deleteForm'], false) !!}
    </div>

    <div class="eto-modal-booking-tracking modal" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"></h4>
                </div>
                <div class="modal-body">
                    <div class="eto-booking-tracking-map" style="height: 500px"></div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('subfooter')
    <script src="{{ asset_url('plugins','moment/moment.min.js') }}"></script>
    <script src="{{ asset_url('plugins','moment/moment-timezone-with-data.min.js') }}"></script>
    <script src="{{ asset_url('plugins','data-tables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset_url('plugins','data-tables/dataTables.bootstrap.min.js') }}"></script>
    <script src="{{ asset_url('plugins','data-tables/extensions/Buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset_url('plugins','data-tables/extensions/Buttons/js/buttons.bootstrap.min.js') }}"></script>
    <script src="{{ asset_url('plugins','data-tables/extensions/Buttons/js/buttons.colVis.min.js') }}"></script>
    <script src="{{ asset_url('plugins','data-tables/extensions/Buttons/js/buttons.server-side.js') }}"></script>
    <script src="{{ asset_url('plugins','data-tables/extensions/ColReorder/js/dataTables.colReorder.min.js') }}"></script>
    <script src="{{ asset_url('plugins','jquery-readmore/readmore.min.js') }}"></script>
    <script src="{{ asset_url('plugins','jquery-simple-timer/jquery.simple.timer.js') }}"></script>

    <script src="//maps.googleapis.com/maps/api/js?v=3&key={{ config('site.google_maps_javascript_api_key') }}&libraries=places,geometry&language={{ app()->getLocale() }}"></script>
    <script src="{{ asset_url('plugins','markerwithlabel/markerwithlabel.js') }}"></script>
    <script src="{{ asset_url('js','eto/eto-routehistory.js') }}?_dc={{ config('app.timestamp') }}"></script>

    {!! $builder->scripts() !!}

    <script type="text/javascript">
    function dtCallback(type) {
        // if (type == 'init') {
        //     $(".dataTables_length select").select2({
        //         minimumResultsForSearch: 5
        //     });
        // }

        $('.eto-address-more').readmore({
            collapsedHeight: 40,
            moreLink: '<a href="#" class="eto-address-more-link">{{ trans('booking.buttons.more') }}</a>',
            lessLink: '<a href="#" class="eto-address-more-link">{{ trans('booking.buttons.less') }}</a>'
        });

        $('#jobs [data-toggle="tooltip"]').tooltip('hide');
        $('#jobs [title]').tooltip({
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

        $('.timer-countdown').html('').startTimer({
            onComplete: function(element) {
                element.addClass('timer-countdown-done').html('{{ trans('driver/jobs.auto_dispatch_time_up') }}');
            }
        });
    }

    function updateTableHeight() {
       var height = parseFloat($('.wrapper > .content-wrapper').css('min-height')) -
          $('#dataTableBuilder_wrapper > .topContainer').height() -
          $('#dataTableBuilder_wrapper > .bottomContainer').height() -
          $('.dataTables_scrollHead').height() - 50;

        if( height < 200 ) {
            height = 200;
        }
        // if( parseFloat($('.wrapper > .content-wrapper').css('min-height')) > $(window).height() ) {
        //  height = 0;
        // }
        $('#jobs .dataTables_scrollBody').css({'min-height': height +'px'});
    }

    $(document).ready(function() {
        if (typeof ETO.Routehistory != "undefined" && typeof ETO.Routehistory.init != "undefined") {
            ETO.Routehistory.init({
                init: ['google', 'icons'],
                lang: ['booking'],
            });
        } else {
            console.log('ETO.Routehistory is not initialized');
        }

        $('table[data-form="deleteForm"]').on('click', '.form-delete', function(e){
            e.preventDefault();
            var $form = $(this);
            $('#modal-delete').modal().on('click', '#delete-btn', function(){
                $form.submit();
            });
        });

        updateTableHeight();
    });

    $(window).resize(function() {
      updateTableHeight();
    });
    </script>
@stop
