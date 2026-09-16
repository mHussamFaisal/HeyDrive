@extends('admin.index')

@section('title', trans('admin/zones.page_title') .' / '. $zone->getName())
@section('subtitle', /*'<i class="fa fa-map-marker"></i> '*/ '<a href="'. route('admin.zones.index') .'">'. trans('admin/zones.page_title') .'</a> / '. $zone->getName())

@section('subcontent')
    @include('partials.modals.delete')

    <div class="row">
        <div class="col-md-8 col-lg-6 col-md-offset-2 col-lg-offset-3">

            <div class="widget-user-2">
                <div class="widget-user-header clearfix">
                    <h3 class="widget-user-username" style="margin:0;">{{ $zone->getName() }}</h3>
                </div>
                <div>
                    <div id="map" style="width:100%; height:300px; margin-bottom:20px;"></div>

                    <ul class="list-group list-group-unbordered details-list">
                        <li class="list-group-item">
                            <span class="details-list-title">{{ trans('admin/zones.address') }}:</span>
                            <span class="details-list-value">{{ $zone->address }}</span>
                        </li>
                        <li class="list-group-item">
                            <span class="details-list-title">{{ trans('admin/zones.lat') }}:</span>
                            <span class="details-list-value">{{ $zone->lat }}</span>
                        </li>
                        <li class="list-group-item">
                            <span class="details-list-title">{{ trans('admin/zones.lng') }}:</span>
                            <span class="details-list-value">{{ $zone->lng }}</span>
                        </li>
                        <li class="list-group-item">
                            <span class="details-list-title">{{ trans('admin/zones.radius') }}:</span>
                            <span class="details-list-value">{{ $zone->radius }} {{ config('site.booking_distance_unit') == 0 ? 'mi' : 'km' }}</span>
                        </li>
                        {{-- <li class="list-group-item">
                            <span class="details-list-title">{{ trans('admin/zones.order') }}:</span>
                            <span class="details-list-value">{{ $zone->order }}</span>
                        </li> --}}
                        <li class="list-group-item">
                            <span class="details-list-title">{{ trans('admin/zones.status') }}:</span>
                            <span class="details-list-value">
                                @permission('admin.zones.edit')
                                    <a href="{{ route('admin.zones.status', [$zone->id, ($zone->status == 'active') ? 'inactive' : 'active']) }}" class="text-success status-icon">
                                        {!! $zone->getStatus('label') !!}
                                    </a>
                                @else
                                    {!! $zone->getStatus('label') !!}
                                @endpermission
                            </span>
                        </li>
                    </ul>

                    <div class="row">
                        <div class="col-sm-12">
                            @permission('admin.zones.edit')
                            <a href="{{ route('admin.zones.edit', $zone->id) }}" class="btn btn-primary">
                                {{-- <i class="fa fa-edit"></i>  --}}
                                <span>{{ trans('admin/zones.button.edit') }}</span>
                            </a>
                            @endpermission

                            @permission('admin.zones.destroy')
                            {!! Form::open(['method' => 'delete', 'route' => ['admin.zones.destroy', $zone->id], 'class' => 'form-inline form-delete']) !!}
                                {!! Form::button(/*'<i class="fa fa-trash-o"></i> <span>'. */trans('admin/zones.button.destroy') .'</span>', ['title' => trans('admin/zones.button.destroy'), 'type' => 'submit', 'class' => 'btn btn-default delete', 'name' => 'delete_modal']) !!}
                            {!! Form::close() !!}
                            @endpermission

                            @if( request('noBack') != '1' )
                            <a href="{{ url()->previous() != url()->full() ? url()->previous() : route('admin.zones.index') }}" class="btn btn-link">
                                {{-- <i class="fa fa-arrow-left"></i>  --}}
                                <span>{{ trans('admin/zones.button.back') }}</span>
                            </a>
                            @endif
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
@stop

@section('subfooter')
    <script src="//maps.googleapis.com/maps/api/js?v=3&key={{ config('site.google_maps_javascript_api_key') }}"></script>

    <script type="text/javascript">
    var formData = {
        map: null,
        marker: null,
        circle: null,
        address: '{{ $zone->address ? $zone->address : '' }}',
        lat: {{ $zone->lat ? $zone->lat : 0 }},
        lng: {{ $zone->lng ? $zone->lng : 0 }},
        radius: {{ $zone->radius ? $zone->radius : 0 }},
        units: {{ config('site.booking_distance_unit') == 0 ? 1609.34 : 1000 }}
    };

    function initMap() {
        formData.map = new google.maps.Map(document.getElementById('map'), {
            mapTypeId: 'roadmap',
            zoom: 10,
            center: {lat: formData.lat, lng: formData.lng}
        });

        formData.marker = new google.maps.Marker({
            map: formData.map,
            position: new google.maps.LatLng(formData.lat, formData.lng),
            title: formData.address,
            animation: google.maps.Animation.DROP,
            draggable: false,
        });

        formData.circle = new google.maps.Circle({
            map: formData.map,
            strokeColor: '#FF0000',
            strokeOpacity: 0.8,
            strokeWeight: 2,
            fillColor: '#FF0000',
            fillOpacity: 0.35,
            radius: formData.radius * formData.units, // in metres
            center: new google.maps.LatLng(formData.lat, formData.lng),
            editable: false
        });

        formData.marker.setPosition(new google.maps.LatLng(formData.lat, formData.lng));
        formData.marker.setTitle(formData.address);

        formData.circle.setCenter(new google.maps.LatLng(formData.lat, formData.lng));
        formData.circle.setRadius(formData.radius * formData.units);

        formData.map.setCenter(new google.maps.LatLng(formData.lat, formData.lng));
        formData.map.fitBounds(formData.circle.getBounds());
    }

    $(document).ready(function() {
        initMap();

        $('.form-delete').on('click', function(e){
            e.preventDefault();
            var $form = $(this);
            $('#modal-delete').modal().on('click', '#delete-btn', function(){
                $form.submit();
            });
        });
    });
    </script>
@stop
