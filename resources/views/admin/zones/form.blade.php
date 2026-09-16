<div class="form-group has-feedback{{ $errors->has('name') ? ' has-error' : '' }}">
    {!! Form::label('name', trans('admin/zones.name')) !!}
    {{-- <div class="input-group"> --}}
        {!! Form::text('name', old('name', $mode == 'edit' ? $zone->name : null), ['id' => 'name', 'class' => 'form-control', 'placeholder' => trans('admin/zones.name'), 'required']) !!}
        {{-- <span class="input-group-addon"><i class="fa fa-pencil"></i></span> --}}
    {{-- </div> --}}
    @if( $errors->has('name') )
        <span class="help-block">{{ $errors->first('name') }}</span>
    @endif
</div>

<div id="map" style="width:100%; height:300px; margin-bottom:20px;"></div>

<div class="form-group has-feedback{{ $errors->has('address') ? ' has-error' : '' }}">
    {!! Form::label('address', trans('admin/zones.address')) !!}
    <div class="input-group">
        {!! Form::text('address', old('address', $mode == 'edit' ? $zone->address : 'London, United Kingdom'), ['id' => 'address', 'class' => 'form-control', 'placeholder' => trans('admin/zones.address')]) !!}
        <span class="input-group-addon"><i class="fa fa-address-card-o"></i></span>
    </div>
    @if( $errors->has('address') )
        <span class="help-block">{{ $errors->first('address') }}</span>
    @endif
</div>

<div class="row">
  <div class="col-sm-4">

    <div class="form-group has-feedback{{ $errors->has('radius') ? ' has-error' : '' }}">
        {!! Form::label('radius', trans('admin/zones.radius') .' ('. (config('site.booking_distance_unit') == 0 ? 'mi' : 'km') .')') !!}
        <div class="input-group">
            {!! Form::number('radius', old('radius', $mode == 'edit' ? $zone->radius : 0), ['id' => 'radius', 'class' => 'form-control', 'placeholder' => trans('admin/zones.radius') .' ('. (config('site.booking_distance_unit') == 0 ? 'mi' : 'km') .')', 'required', 'min' => '0', 'step' => '0.01']) !!}
            <span class="input-group-addon"><i class="fa fa-circle-thin"></i></span>
        </div>
        @if( $errors->has('radius') )
            <span class="help-block">{{ $errors->first('radius') }}</span>
        @endif
    </div>

  </div>
  <div class="col-sm-4">

    <div class="form-group has-feedback{{ $errors->has('lat') ? ' has-error' : '' }}">
        {!! Form::label('lat', trans('admin/zones.lat')) !!}
        <div class="input-group">
            {!! Form::number('lat', old('lat', $mode == 'edit' ? $zone->lat : 0), ['id' => 'lat', 'class' => 'form-control', 'placeholder' => trans('admin/zones.lat'), 'required', 'step' => '0.000001']) !!}
            <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
        </div>
        @if( $errors->has('lat') )
            <span class="help-block">{{ $errors->first('lat') }}</span>
        @endif
    </div>

  </div>
  <div class="col-sm-4">

    <div class="form-group has-feedback{{ $errors->has('lng') ? ' has-error' : '' }}">
        {!! Form::label('lng', trans('admin/zones.lng')) !!}
        <div class="input-group">
            {!! Form::number('lng', old('lng', $mode == 'edit' ? $zone->lng : 0), ['id' => 'lng', 'class' => 'form-control', 'placeholder' => trans('admin/zones.lng'), 'required', 'step' => '0.000001']) !!}
            <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
        </div>
        @if( $errors->has('lng') )
            <span class="help-block">{{ $errors->first('lng') }}</span>
        @endif
    </div>

  </div>
</div>

<div class="row">
  <div class="col-sm-6">

    <div class="form-group has-feedback{{ $errors->has('order') ? ' has-error' : '' }}">
        {!! Form::label('order', trans('admin/zones.order')) !!}
        {{-- <div class="input-group"> --}}
            {!! Form::number('order', old('order', $mode == 'edit' ? $zone->order : 0), ['id' => 'order', 'class' => 'form-control', 'placeholder' => trans('admin/zones.order'), 'required', 'min' => '0', 'step' => '1']) !!}
            {{-- <span class="input-group-addon"><i class="fa fa-sort"></i></span> --}}
        {{-- </div> --}}
        @if( $errors->has('order') )
            <span class="help-block">{{ $errors->first('order') }}</span>
        @endif
    </div>

  </div>
  <div class="col-sm-6">

    <div class="form-group has-feedback{{ $errors->has('status') ? ' has-error' : '' }}">
        {!! Form::label('status', trans('admin/zones.status')) !!}
        {{-- <div class="input-group"> --}}
            {!! Form::select('status', $status, old('status', $mode == 'create' ? 'approved' : null), ['id' => 'status', 'class' => 'form-control select2', 'data-placeholder' => trans('admin/zones.status'), 'required']) !!}
            {{-- <span class="input-group-addon"><i class="fa fa-eye"></i></span> --}}
        {{-- </div> --}}
        @if( $errors->has('status') )
            <span class="help-block">{{ $errors->first('status') }}</span>
        @endif
    </div>

  </div>
</div>

<div class="row">
    <div class="col-sm-12">
        {!! Form::button(/*'<i class="fa fa-check"></i> '. */($mode == 'edit' ? trans('admin/zones.button.update') : trans('admin/zones.button.create')), ['type' => 'submit', 'class' => 'btn btn-primary']) !!}
        <a href="{{ route('admin.zones.index') }}" class="btn btn-link">{{ trans('admin/zones.button.cancel') }}</a>
    </div>
</div>

@section('subfooter')
    <script src="//maps.googleapis.com/maps/api/js?v=3&key={{ config('site.google_maps_javascript_api_key') }}&libraries=places,geometry"></script>

    <script type="text/javascript">

    var form = $('#zones');
    var formData = {
        map: null,
        search: null,
        geocoder: null,
        marker: null,
        circle: null,
        address: '{{ isset($zone->address) ? $zone->address : '' }}',
        lat: {{ isset($zone->lat) ? $zone->lat : 0 }},
        lng: {{ isset($zone->lng) ? $zone->lng : 0 }},
        radius: {{ isset($zone->radius) ? $zone->radius : 5 }},
        units: {{ config('site.booking_distance_unit') == 0 ? 1609.34 : 1000 }}
    };

    function initMap() {
        formData.map = new google.maps.Map(document.getElementById('map'), {
            mapTypeId: 'roadmap',
            zoom: 10,
            center: {lat: formData.lat, lng: formData.lng}
        });

        formData.search = new google.maps.places.SearchBox(document.getElementById('address'));

        formData.search.addListener('places_changed', function() {
            var results = formData.search.getPlaces();
            formData.address = results[0].formatted_address;
            formData.lat = parseFloat(results[0].geometry.location.lat()).toFixed(6);
            formData.lng = parseFloat(results[0].geometry.location.lng()).toFixed(6);
            codeAddress()
            updateMarker();
        });

        formData.geocoder = new google.maps.Geocoder();

        formData.marker = new google.maps.Marker({
            map: formData.map,
            position: new google.maps.LatLng(formData.lat, formData.lng),
            title: formData.address,
            animation: google.maps.Animation.DROP,
            draggable: true
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

        google.maps.event.addListener(formData.marker, 'dragend', function(marker) {
            var latLng = formData.marker.getPosition();

            formData.lat = parseFloat(latLng.lat()).toFixed(6);
            formData.lng = parseFloat(latLng.lng()).toFixed(6);
            formData.circle.setCenter(new google.maps.LatLng(formData.lat, formData.lng));

            form.find('#lat').val(formData.lat);
            form.find('#lng').val(formData.lng);
        });

        google.maps.event.addListener(formData.circle, 'mouseover', function() {
            formData.circle.set('editable', true);
        });

        google.maps.event.addListener(formData.circle, 'mouseout', function() {
            formData.circle.set('editable', false);
        });

        google.maps.event.addListener(formData.circle, 'radius_changed', function() {
            formData.radius = parseFloat(formData.circle.getRadius() / formData.units).toFixed(2);

            form.find('#radius').val(formData.radius);
        });

        google.maps.event.addListener(formData.circle, 'center_changed', function() {
            var latLng = formData.circle.getCenter();

            formData.lat = parseFloat(latLng.lat()).toFixed(6);
            formData.lng = parseFloat(latLng.lng()).toFixed(6);
            formData.marker.setPosition(new google.maps.LatLng(formData.lat, formData.lng));

            form.find('#lat').val(formData.lat);
            form.find('#lng').val(formData.lng);
        });

        form.find('#address').on('change', function(){
            formData.address = $(this).val();
            codeAddress();
        });

        form.find('#lat').on('change', function(){
            formData.lat = parseFloat($(this).val()).toFixed(6);
            updateMarker();
        });

        form.find('#lng').on('change', function(){
            formData.lng = parseFloat($(this).val()).toFixed(6);
            updateMarker();
        });

        form.find('#radius').on('change', function(){
            formData.radius = parseFloat($(this).val()).toFixed(2);
            updateMarker();
        });

        @if (!isset($zone))
            form.find('#address').change();
        @else
            updateMarker();
        @endif
    }

    function updateMarker() {
        formData.marker.setPosition(new google.maps.LatLng(formData.lat, formData.lng));
        formData.marker.setTitle(formData.address);

        formData.circle.setCenter(new google.maps.LatLng(formData.lat, formData.lng));
        formData.circle.setRadius(formData.radius * formData.units);

        formData.map.setCenter(new google.maps.LatLng(formData.lat, formData.lng));
        formData.map.fitBounds(formData.circle.getBounds());
    }

    function codeAddress() {
        if (!formData.address) {
            return false;
        }

        formData.geocoder.geocode({'address': formData.address}, function(results, status) {
            if (status === 'OK') {
                formData.address = results[0].formatted_address;
                formData.lat = parseFloat(results[0].geometry.location.lat()).toFixed(6);
                formData.lng = parseFloat(results[0].geometry.location.lng()).toFixed(6);
                updateMarker();
            }
            else {
                alert('Geocode was not successful for the following reason: '+ status);
            }
        });
    }

    $(document).ready(function() {
        initMap();

        // Placeholder
        function updateFormPlaceholder(that) {
            var $container = $(that).closest('.form-group:not(.placeholder-disabled)');

            if( $(that).val() != '' || $container.hasClass('placeholder-visible') ) {
                $container.find('label').show();
            }
            else {
                $container.find('label').hide();
            }
        }

        $('.form-master').find('input:not([type="submit"]), textarea, select')
          .each(function() {
              updateFormPlaceholder(this);
          })
          .bind('change keyup', function(e) {
              updateFormPlaceholder(this);
          });
    });
    </script>
@stop
