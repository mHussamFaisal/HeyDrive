<div class="row">
  <div class="col-xs-12 col-sm-6">

    <div class="form-group has-feedback{{ $errors->has('name') ? ' has-error' : '' }}">
        {!! Form::label('name', trans('admin/vehicles.name')) !!}
        {{-- <div class="input-group"> --}}
            {!! Form::text('name', old('name', $mode == 'edit' ? $vehicle->getOriginalForm('name') : null), ['id' => 'name', 'class' => 'form-control', 'placeholder' => trans('admin/vehicles.name'), 'required']) !!}
            {{-- <span class="input-group-addon"><i class="fa fa-pencil"></i></span> --}}
        {{-- </div> --}}
        @if( $errors->has('name') )
          <span class="help-block">{{ $errors->first('name') }}</span>
        @endif
    </div>

  </div>
  <div class="col-xs-12 col-sm-6">

    <div class="form-group has-feedback{{ $errors->has('user_id') ? ' has-error' : '' }}">
        {!! Form::label('user_id', trans('admin/vehicles.user')) !!}
        {{-- <div class="input-group"> --}}
            {!! Form::select('user_id', $users, old('user_id', $mode == 'create' ? '' : null), ['id' => 'user_id', 'class' => 'form-control select2', 'data-placeholder' => trans('admin/vehicles.user'), 'data-allow-clear' => 'true']) !!}
            {{-- <span class="input-group-addon"><i class="fa fa-pencil"></i></span> --}}
        {{-- </div> --}}
        @if( $errors->has('user_id') )
          <span class="help-block">{{ $errors->first('user_id') }}</span>
        @endif
    </div>

  </div>
</div>

<div class="form-group has-feedback{{ $errors->has('vehicle_type_id') ? ' has-error' : '' }}">
    {!! Form::label('user_id', trans('admin/vehicles.vehicle_type')) !!}
    <div class="input-group">
        {!! Form::select('vehicle_type_id', $vehicleTypes, old('vehicle_type_id', $mode == 'create' ? '' : null), ['id' => 'vehicle_type_id', 'class' => 'form-control select2', 'data-placeholder' => trans('admin/vehicles.vehicle_type'), 'data-allow-clear' => 'true']) !!}
        {{-- <span class="input-group-addon"><i class="fa fa-pencil"></i></span> --}}
        <span class="input-group-addon" data-toggle="popover" data-title="{{ trans('admin/vehicles.vehicle_type') }}" data-content="{{ trans('admin/vehicles.vehicle_type_help') }}">
            <i class="ion-ios-information-outline" style="font-size:18px; color:#636363"></i>
        </span>
    </div>
    @if( $errors->has('vehicle_type_id') )
      <span class="help-block">{{ $errors->first('vehicle_type_id') }}</span>
    @endif
</div>

<div class="form-group has-feedback{{ $errors->has('image') ? ' has-error' : '' }} placeholder-visible">
    {!! Form::label('image', trans('admin/vehicles.image_upload')) !!}
    {{-- <div class="input-group"> --}}
        {!! Form::file('image', ['id' => 'image', 'class' => 'form-control']) !!}
        {{-- <span class="input-group-addon"><i class="fa fa-upload"></i></span> --}}
    {{-- </div> --}}
    @if( $errors->has('image') )
        <span class="help-block">{{ $errors->first('image') }}</span>
    @endif
</div>

@if( !empty($vehicle->image) )
    <div class="form-group has-feedback{{ $errors->has('image_delete') ? ' has-error' : '' }} placeholder-disabled">
        <img src="{{ asset( $vehicle->getImagePath() ) }}" class="img-circle" alt="" style="max-width:100px; max-height:100px;">
        <div class="checkbox">
          <label>
            {!! Form::checkbox('image_delete', 1, old('image_delete', $mode == 'create' ? true : null)) !!} {{ trans('admin/vehicles.image_delete') }}
          </label>
        </div>
        @if( $errors->has('image_delete') )
            <span class="help-block">{{ $errors->first('image_delete') }}</span>
        @endif
    </div>
@endif


<div class="row">
  <div class="col-xs-12 col-sm-6">

    <div class="form-group has-feedback{{ $errors->has('registration_mark') ? ' has-error' : '' }}">
        {!! Form::label('registration_mark', trans('admin/vehicles.registration_mark')) !!}
        {{-- <div class="input-group"> --}}
            {!! Form::text('registration_mark', old('registration_mark'), ['id' => 'registration_mark', 'class' => 'form-control', 'placeholder' => trans('admin/vehicles.registration_mark')]) !!}
            {{-- <span class="input-group-addon"><i class="fa fa-pencil"></i></span> --}}
        {{-- </div> --}}
        @if( $errors->has('registration_mark') )
          <span class="help-block">{{ $errors->first('registration_mark') }}</span>
        @endif
    </div>

  </div>
  <div class="col-xs-12 col-sm-6">

    <div class="form-group has-feedback{{ $errors->has('mot') ? ' has-error' : '' }}">
        {!! Form::label('mot', trans('admin/vehicles.mot')) !!}
        {{-- <div class="input-group"> --}}
            {!! Form::text('mot', old('mot'), ['id' => 'mot', 'class' => 'form-control', 'placeholder' => trans('admin/vehicles.mot')]) !!}
            {{-- <span class="input-group-addon"><i class="fa fa-pencil"></i></span> --}}
        {{-- </div> --}}
        @if( $errors->has('mot') )
          <span class="help-block">{{ $errors->first('mot') }}</span>
        @endif
    </div>

  </div>
</div>


<div class="row">
  <div class="col-xs-12 col-sm-6">

    <div class="form-group has-feedback{{ $errors->has('mot_expiry_date') ? ' has-error' : '' }}">
        {!! Form::label('mot_expiry_date', trans('admin/vehicles.mot_expiry_date')) !!}
        {{-- <div class="input-group"> --}}
            {!! Form::text('mot_expiry_date', old('mot_expiry_date', $mode == 'edit' ? $vehicle->mot_expiry_date : null), ['id' => 'mot_expiry_date', 'class' => 'form-control datepicker', 'placeholder' => trans('admin/vehicles.mot_expiry_date')]) !!}
            {{-- <span class="input-group-addon"><i class="fa fa-pencil"></i></span> --}}
        {{-- </div> --}}
        @if( $errors->has('mot_expiry_date') )
          <span class="help-block">{{ $errors->first('mot_expiry_date') }}</span>
        @endif
    </div>

  </div>
  <div class="col-xs-12 col-sm-6">

    <div class="form-group has-feedback{{ $errors->has('make') ? ' has-error' : '' }}">
        {!! Form::label('make', trans('admin/vehicles.make')) !!}
        {{-- <div class="input-group"> --}}
            {!! Form::text('make', old('make'), ['id' => 'make', 'class' => 'form-control', 'placeholder' => trans('admin/vehicles.make')]) !!}
            {{-- <span class="input-group-addon"><i class="fa fa-pencil"></i></span> --}}
        {{-- </div> --}}
        @if( $errors->has('make') )
          <span class="help-block">{{ $errors->first('make') }}</span>
        @endif
    </div>

  </div>
</div>


<div class="row">
  <div class="col-xs-12 col-sm-6">

    <div class="form-group has-feedback{{ $errors->has('model') ? ' has-error' : '' }}">
        {!! Form::label('model', trans('admin/vehicles.model')) !!}
        {{-- <div class="input-group"> --}}
            {!! Form::text('model', old('model'), ['id' => 'model', 'class' => 'form-control', 'placeholder' => trans('admin/vehicles.model')]) !!}
            {{-- <span class="input-group-addon"><i class="fa fa-pencil"></i></span> --}}
        {{-- </div> --}}
        @if( $errors->has('model') )
          <span class="help-block">{{ $errors->first('model') }}</span>
        @endif
    </div>

  </div>
  <div class="col-xs-12 col-sm-6">

    <div class="form-group has-feedback{{ $errors->has('colour') ? ' has-error' : '' }}">
        {!! Form::label('colour', trans('admin/vehicles.colour')) !!}
        {{-- <div class="input-group"> --}}
            {!! Form::text('colour', old('colour'), ['id' => 'colour', 'class' => 'form-control', 'placeholder' => trans('admin/vehicles.colour')]) !!}
            {{-- <span class="input-group-addon"><i class="fa fa-pencil"></i></span> --}}
        {{-- </div> --}}
        @if( $errors->has('colour') )
          <span class="help-block">{{ $errors->first('colour') }}</span>
        @endif
    </div>

  </div>
</div>

<div class="row">
  <div class="col-xs-12 col-sm-6">

    <div class="form-group has-feedback{{ $errors->has('body_type') ? ' has-error' : '' }}">
        {!! Form::label('body_type', trans('admin/vehicles.body_type')) !!}
        {{-- <div class="input-group"> --}}
            {!! Form::text('body_type', old('body_type'), ['id' => 'body_type', 'class' => 'form-control', 'placeholder' => trans('admin/vehicles.body_type')]) !!}
            {{-- <span class="input-group-addon"><i class="fa fa-pencil"></i></span> --}}
        {{-- </div> --}}
        @if( $errors->has('body_type') )
          <span class="help-block">{{ $errors->first('body_type') }}</span>
        @endif
    </div>

  </div>
  <div class="col-xs-12 col-sm-6">

    <div class="form-group has-feedback{{ $errors->has('no_of_passengers') ? ' has-error' : '' }}">
        {!! Form::label('no_of_passengers', trans('admin/vehicles.no_of_passengers')) !!}
        {{-- <div class="input-group"> --}}
            {!! Form::text('no_of_passengers', old('no_of_passengers'), ['id' => 'no_of_passengers', 'class' => 'form-control', 'placeholder' => trans('admin/vehicles.no_of_passengers')]) !!}
            {{-- <span class="input-group-addon"><i class="fa fa-pencil"></i></span> --}}
        {{-- </div> --}}
        @if( $errors->has('no_of_passengers') )
          <span class="help-block">{{ $errors->first('no_of_passengers') }}</span>
        @endif
    </div>

  </div>
</div>


<div class="row">
  <div class="col-xs-12 col-sm-6">

    <div class="form-group has-feedback{{ $errors->has('registered_keeper_name') ? ' has-error' : '' }}">
        {!! Form::label('registered_keeper_name', trans('admin/vehicles.registered_keeper_name')) !!}
        {{-- <div class="input-group"> --}}
            {!! Form::text('registered_keeper_name', old('registered_keeper_name'), ['id' => 'registered_keeper_name', 'class' => 'form-control', 'placeholder' => trans('admin/vehicles.registered_keeper_name')]) !!}
            {{-- <span class="input-group-addon"><i class="fa fa-pencil"></i></span> --}}
        {{-- </div> --}}
        @if( $errors->has('registered_keeper_name') )
          <span class="help-block">{{ $errors->first('registered_keeper_name') }}</span>
        @endif
    </div>

  </div>
  <div class="col-xs-12 col-sm-6">

    <div class="form-group has-feedback{{ $errors->has('registered_keeper_address') ? ' has-error' : '' }}">
        {!! Form::label('registered_keeper_address', trans('admin/vehicles.registered_keeper_address')) !!}
        {{-- <div class="input-group"> --}}
            {!! Form::textarea('registered_keeper_address', old('registered_keeper_address'), ['id' => 'registered_keeper_address', 'class' => 'form-control', 'placeholder' => trans('admin/vehicles.registered_keeper_address'), 'rows' => '2']) !!}
            {{-- <span class="input-group-addon"><i class="fa fa-pencil"></i></span> --}}
        {{-- </div> --}}
        @if( $errors->has('registered_keeper_address') )
          <span class="help-block">{{ $errors->first('registered_keeper_address') }}</span>
        @endif
    </div>

  </div>
</div>


<div class="row">
  <div class="col-xs-12 col-sm-6">

    <div class="form-group has-feedback{{ $errors->has('description') ? ' has-error' : '' }}">
        {!! Form::label('description', trans('admin/vehicles.description')) !!}
        {{-- <div class="input-group"> --}}
            {!! Form::textarea('description', old('description'), ['id' => 'description', 'class' => 'form-control', 'placeholder' => trans('admin/vehicles.description'), 'rows' => '2']) !!}
            {{-- <span class="input-group-addon"><i class="fa fa-pencil"></i></span> --}}
        {{-- </div> --}}
        @if( $errors->has('description') )
            <span class="help-block">{{ $errors->first('description') }}</span>
        @endif
    </div>

  </div>
  <div class="col-xs-12 col-sm-6">

    <div class="form-group has-feedback{{ $errors->has('status') ? ' has-error' : '' }}">
        {!! Form::label('status', trans('admin/vehicles.status')) !!}
        {{-- <div class="input-group"> --}}
            {!! Form::select('status', $status, old('status', $mode == 'create' ? 'activated' : null), ['id' => 'status', 'class' => 'form-control select2', 'data-placeholder' => trans('admin/vehicles.status'), 'required']) !!}
            {{-- <span class="input-group-addon"><i class="fa fa-eye"></i></span> --}}
        {{-- </div> --}}
        @if( $errors->has('status') )
            <span class="help-block">{{ $errors->first('status') }}</span>
        @endif
    </div>

  </div>
</div>

<div class="form-group has-feedback{{ $errors->has('selected') ? ' has-error' : '' }} placeholder-disabled">
    <div class="onoffswitch">
        {!! Form::checkbox('selected', 1, old('selected', $mode == 'create' ? false : null), ['id' => 'selected', 'class' => 'onoffswitch-input']) !!}
        <label class="onoffswitch-label" for="selected"></label>
    </div>
    <span class="onoffswitch-label-master">{{ trans('admin/vehicles.selected') }}</span>
    @if( $errors->has('selected') )
        <span class="help-block">{{ $errors->first('selected') }}</span>
    @endif
</div>

<div class="row">
    <div class="col-sm-12">
        {!! Form::button(/*'<i class="fa fa-check"></i> '. */($mode == 'edit' ? trans('admin/vehicles.button.update') : trans('admin/vehicles.button.create')), ['type' => 'submit', 'class' => 'btn btn-primary']) !!}
        <a href="{{ route('admin.vehicles.index') }}" class="btn btn-link">{{ trans('admin/vehicles.button.cancel') }}</a>
    </div>
</div>

@section('subheader')
  <link rel="stylesheet" href="{{ asset_url('plugins','bootstrap-daterangepicker/daterangepicker.css') }}">
@stop

@section('subfooter')
  <script src="{{ asset_url('plugins','autosize/autosize.min.js') }}"></script>
  <script src="{{ asset_url('plugins','moment/moment.min.js') }}"></script>
  <script src="{{ asset_url('plugins','bootstrap-daterangepicker/daterangepicker.js') }}"></script>

  <script type="text/javascript">
  $(document).ready(function() {
      // Tooltips
      $('[data-toggle="popover"]').popover({
          placement: 'auto right',
          container: 'body',
          trigger: 'click focus hover',
          html: true
      });

      // Date picker
      $('.form-master .datepicker').daterangepicker({
        singleDatePicker: true,
        showDropdowns: true,
        timePicker: true,
        timePicker24Hour: {{ config('site.time_format') == 'H:i' ? 'true' : 'false' }},
        autoUpdateInput: false,
        locale: {
          format: 'YYYY-MM-DD HH:mm',
          firstDay: {{ config('site.start_of_week') }}
        }
      })
      .on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD HH:mm')).change();
      });

      // Textarea auto height
      autosize($('textarea'));

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

      $('.form-master').find('input:not([type="submit"]), textarea, select').each(function() {
          updateFormPlaceholder(this);
      })
      .bind('change keyup', function(e) {
          updateFormPlaceholder(this);
      });
  });
  </script>
@stop
