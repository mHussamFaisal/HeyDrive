<div class="row">
  <div class="col-xs-12 col-sm-6">

    <div class="form-group has-feedback {{ $errors->has('from') ? 'has-error' : '' }}">
      {!! Form::label('from', trans('admin/scheduled_routes.from')) !!}
      {{-- <div class="input-group"> --}}
        {!! Form::text('from', old('from', $mode == 'edit' && !empty($scheduledRoute->from->id) ? $scheduledRoute->from->address : ''), [
          'id' => 'from',
          'class' => 'form-control',
          'placeholder' => trans('admin/scheduled_routes.from'),
          'required',
        ]) !!}
        {{-- <span class="input-group-addon"><i class="fa fa-pencil"></i></span> --}}
      {{-- </div> --}}
      @if( $errors->has('from') )
        <span class="help-block">{{ $errors->first('from') }}</span>
      @endif
    </div>

  </div>
  <div class="col-xs-12 col-sm-6">

    <div class="form-group has-feedback {{ $errors->has('to') ? 'has-error' : '' }}">
      {!! Form::label('to', trans('admin/scheduled_routes.to')) !!}
      {{-- <div class="input-group"> --}}
        {!! Form::text('to', old('to', $mode == 'edit' && !empty($scheduledRoute->to->id) ? $scheduledRoute->to->address : ''), [
          'id' => 'to',
          'class' => 'form-control',
          'placeholder' => trans('admin/scheduled_routes.to'),
          'required',
        ]) !!}
        {{-- <span class="input-group-addon"><i class="fa fa-pencil"></i></span> --}}
      {{-- </div> --}}
      @if( $errors->has('to') )
        <span class="help-block">{{ $errors->first('to') }}</span>
      @endif
    </div>

  </div>
</div>

<div class="row">
  <div class="col-xs-12 col-sm-6">

    <div class="form-group has-feedback {{ $errors->has('driver_id') ? 'has-error' : '' }}">
      {!! Form::label('driver_id', trans('admin/scheduled_routes.driver_id')) !!}
      {{-- <div class="input-group"> --}}
        {!! Form::select('driver_id', $drivers, old('driver_id', $mode == 'edit' ? null : ''), [
          'id' => 'driver_id',
          'class' => 'form-control select2',
          'data-placeholder' => trans('admin/scheduled_routes.driver_id'),
          'data-allow-clear' => 'true',
        ]) !!}
        {{-- <span class="input-group-addon"><i class="fa fa-user"></i></span> --}}
      {{-- </div> --}}
      @if( $errors->has('driver_id') )
        <span class="help-block">{{ $errors->first('driver_id') }}</span>
      @endif
    </div>

  </div>
  <div class="col-xs-12 col-sm-6">

    <div class="form-group form-group-vehicle_id has-feedback {{ $errors->has('vehicle_id') ? 'has-error' : '' }}">
      {!! Form::label('vehicle_id', trans('admin/scheduled_routes.vehicle_id')) !!}
      {{-- <div class="input-group"> --}}
        {!! Form::select('vehicle_id', $vehicles, old('vehicle_id', $mode == 'edit' ? null : ''), [
          'id' => 'vehicle_id',
          'class' => 'form-control select2',
          'data-placeholder' => trans('admin/scheduled_routes.vehicle_id'),
          'data-allow-clear' => 'true',
          'data-minimum-results-for-search' => 'Infinity',
        ]) !!}
        {{-- <span class="input-group-addon"><i class="fa fa-car"></i></span> --}}
      {{-- </div> --}}
      @if( $errors->has('vehicle_id') )
        <span class="help-block">{{ $errors->first('vehicle_id') }}</span>
      @endif
    </div>

  </div>
</div>

<div class="form-group has-feedback {{ $errors->has('vehicle_type_id') ? 'has-error' : '' }}">
  {!! Form::label('vehicle_type_id', trans('admin/scheduled_routes.vehicle_type_id')) !!}
  {{-- <div class="input-group"> --}}
    {!! Form::select('vehicle_type_id', $vehicleTypes, old('vehicle_type_id', $mode == 'edit' ? null : ''), [
      'id' => 'vehicle_type_id',
      'class' => 'form-control select2',
      'data-placeholder' => trans('admin/scheduled_routes.vehicle_type_id'),
      'data-allow-clear' => 'true',
      'required',
    ]) !!}
    {{-- <span class="input-group-addon"><i class="fa fa-pencil"></i></span> --}}
  {{-- </div> --}}
  @if( $errors->has('vehicle_type_id') )
    <span class="help-block">{{ $errors->first('vehicle_type_id') }}</span>
  @endif
</div>

<div class="row">
  <div class="col-xs-12 col-sm-6">

    <div class="form-group has-feedback {{ $errors->has('factor_value') ? 'has-error' : '' }}">
      {!! Form::label('factor_value', trans('admin/scheduled_routes.factor')) !!}
      <div class="input-group1 factor-container">
        <span class="factor-type hide">
          {!! Form::select('factor_type', [
            'addition' => trans('admin/scheduled_routes.factor_types.addition_symbol'),
            'multiplication' => trans('admin/scheduled_routes.factor_types.multiplication_symbol'),
          ], old('factor_type', $mode == 'edit' ? $params->factor_type : 'addition'), [
            'id' => 'factor_type',
            'class' => 'form-control select2',
            'data-placeholder' => trans('admin/scheduled_routes.factor_type'),
          ]) !!}
        </span>
        <span class="factor-value">
          {!! Form::number('factor_value', old('factor_value', $mode == 'edit' ? $params->factor_value : 0), [
            'id' => 'factor_value',
            'class' => 'form-control',
            'placeholder' => trans('admin/scheduled_routes.factor_value'),
            'min' => '0',
            'step' => '0.01',
            'required',
          ]) !!}
        </span>
        {{-- <span class="input-group-addon"><i class="fa fa-pencil"></i></span> --}}
      </div>
      @if( $errors->has('factor_value') )
        <span class="help-block">{{ $errors->first('factor_value') }}</span>
      @endif
    </div>

  </div>
  <div class="col-xs-12 col-sm-6">

    <div class="form-group has-feedback {{ $errors->has('commission') ? 'has-error' : '' }}">
      {!! Form::label('commission', trans('admin/scheduled_routes.commission')) !!}
      {{-- <div class="input-group"> --}}
        {!! Form::number('commission', old('commission', $mode == 'edit' ? $params->commission : 0), [
          'id' => 'commission',
          'class' => 'form-control',
          'placeholder' => trans('admin/scheduled_routes.commission'),
          'min' => '0',
          'step' => '0.01',
          'required',
        ]) !!}
        {{-- <span class="input-group-addon"><i class="fa fa-pencil"></i></span> --}}
      {{-- </div> --}}
      @if( $errors->has('commission') )
        <span class="help-block">{{ $errors->first('commission') }}</span>
      @endif
    </div>

  </div>
</div>

<div class="row">
  <div class="col-xs-12 col-sm-6">

    <div class="form-group has-feedback {{ $errors->has('start_at') ? 'has-error' : '' }}">
      {!! Form::label('start_at', trans('admin/scheduled_routes.start_at')) !!}
      {{-- <div class="input-group"> --}}
        {!! Form::text('start_at', old('start_at', $mode == 'edit' && !empty($scheduledRoute->event->id) ? $scheduledRoute->event->start_at : null), [
          'id' => 'start_at',
          'class' => 'form-control datepicker',
          'placeholder' => trans('admin/scheduled_routes.start_at')
        ]) !!}
        {{-- <span class="input-group-addon"><i class="fa fa-calendar"></i></span> --}}
      {{-- </div> --}}
      @if( $errors->has('start_at') )
        <span class="help-block">{{ $errors->first('start_at') }}</span>
      @endif
    </div>

  </div>
  <div class="col-xs-12 col-sm-6">

    <div class="form-group has-feedback {{ $errors->has('end_at') ? 'has-error' : '' }}">
      {!! Form::label('end_at', trans('admin/scheduled_routes.end_at')) !!}
      {{-- <div class="input-group"> --}}
        {!! Form::text('end_at', old('end_at', $mode == 'edit' && !empty($scheduledRoute->event->id) ? $scheduledRoute->event->end_at : null), [
          'id' => 'end_at',
          'class' => 'form-control datepicker',
          'placeholder' => trans('admin/scheduled_routes.end_at')
        ]) !!}
        {{-- <span class="input-group-addon"><i class="fa fa-calendar"></i></span> --}}
      {{-- </div> --}}
      @if( $errors->has('end_at') )
        <span class="help-block">{{ $errors->first('end_at') }}</span>
      @endif
    </div>

  </div>
</div>

<div class="form-group has-feedback {{ $errors->has('repeat_type') ? 'has-error' : '' }}">
  {!! Form::label('repeat_type', trans('admin/scheduled_routes.repeat_type')) !!}
  {{-- <div class="input-group"> --}}
    {!! Form::select('repeat_type', [
        'none' => trans('admin/scheduled_routes.repeat_types.none'),
        'daily' => trans('admin/scheduled_routes.repeat_types.daily'),
        'weekly' => trans('admin/scheduled_routes.repeat_types.weekly'),
        'monthly' => trans('admin/scheduled_routes.repeat_types.monthly'),
        'yearly' => trans('admin/scheduled_routes.repeat_types.yearly')
    ], old('repeat_type', $mode == 'edit' && !empty($scheduledRoute->event->id) ? $scheduledRoute->event->repeat_type : 'none'), [
      'id' => 'repeat_type',
      'class' => 'form-control select2',
      'data-placeholder' => trans('admin/scheduled_routes.repeat_type'),
      'required',
      'data-minimum-results-for-search' => 'Infinity'
    ]) !!}
    {{-- <span class="input-group-addon"><i class="fa fa-repeat"></i></span> --}}
  {{-- </div> --}}
  @if( $errors->has('repeat_type') )
    <span class="help-block">{{ $errors->first('repeat_type') }}</span>
  @endif
</div>

<div class="repeat-container" style="display:none;">

  <div class="repeat-days-container">
    <div class="repeat-days-title">{{ trans('admin/scheduled_routes.repeat_days_title') }}</div>

    <div class="form-group has-feedback {{ $errors->has('repeat_days') ? 'has-error' : '' }} placeholder-disabled">
      <div class="onoffswitch">
        {!! Form::checkbox('repeat_days[]', 1, old('repeat_days', $mode == 'edit' ? (in_array(1, $repeatDays) ? true : false) : true), [
          'id' => 'repeat_days_1',
          'class' => 'onoffswitch-input'
        ]) !!}
        <label class="onoffswitch-label" for="repeat_days_1"></label>
      </div>
      <span class="onoffswitch-label-master">{{ trans('admin/scheduled_routes.repeat_days_1') }}</span>
    </div>

    <div class="form-group has-feedback {{ $errors->has('repeat_days') ? 'has-error' : '' }} placeholder-disabled">
      <div class="onoffswitch">
        {!! Form::checkbox('repeat_days[]', 2, old('repeat_days', $mode == 'edit' ? (in_array(2, $repeatDays) ? true : false) : true), [
          'id' => 'repeat_days_2',
          'class' => 'onoffswitch-input'
        ]) !!}
        <label class="onoffswitch-label" for="repeat_days_2"></label>
      </div>
      <span class="onoffswitch-label-master">{{ trans('admin/scheduled_routes.repeat_days_2') }}</span>
    </div>

    <div class="form-group has-feedback {{ $errors->has('repeat_days') ? 'has-error' : '' }} placeholder-disabled">
      <div class="onoffswitch">
        {!! Form::checkbox('repeat_days[]', 3, old('repeat_days', $mode == 'edit' ? (in_array(3, $repeatDays) ? true : false) : true), [
          'id' => 'repeat_days_3',
          'class' => 'onoffswitch-input'
        ]) !!}
        <label class="onoffswitch-label" for="repeat_days_3"></label>
      </div>
      <span class="onoffswitch-label-master">{{ trans('admin/scheduled_routes.repeat_days_3') }}</span>
    </div>

    <div class="form-group has-feedback {{ $errors->has('repeat_days') ? 'has-error' : '' }} placeholder-disabled">
      <div class="onoffswitch">
        {!! Form::checkbox('repeat_days[]', 4, old('repeat_days', $mode == 'edit' ? (in_array(4, $repeatDays) ? true : false) : true), [
          'id' => 'repeat_days_4',
          'class' => 'onoffswitch-input'
        ]) !!}
        <label class="onoffswitch-label" for="repeat_days_4"></label>
      </div>
      <span class="onoffswitch-label-master">{{ trans('admin/scheduled_routes.repeat_days_4') }}</span>
    </div>

    <div class="form-group has-feedback {{ $errors->has('repeat_days') ? 'has-error' : '' }} placeholder-disabled">
      <div class="onoffswitch">
        {!! Form::checkbox('repeat_days[]', 5, old('repeat_days', $mode == 'edit' ? (in_array(5, $repeatDays) ? true : false) : true), [
          'id' => 'repeat_days_5',
          'class' => 'onoffswitch-input'
        ]) !!}
        <label class="onoffswitch-label" for="repeat_days_5"></label>
      </div>
      <span class="onoffswitch-label-master">{{ trans('admin/scheduled_routes.repeat_days_5') }}</span>
    </div>

    <div class="form-group has-feedback {{ $errors->has('repeat_days') ? 'has-error' : '' }} placeholder-disabled">
      <div class="onoffswitch">
        {!! Form::checkbox('repeat_days[]', 6, old('repeat_days', $mode == 'edit' ? (in_array(6, $repeatDays) ? true : false) : true), [
          'id' => 'repeat_days_6',
          'class' => 'onoffswitch-input'
        ]) !!}
        <label class="onoffswitch-label" for="repeat_days_6"></label>
      </div>
      <span class="onoffswitch-label-master">{{ trans('admin/scheduled_routes.repeat_days_6') }}</span>
    </div>

    <div class="form-group has-feedback {{ $errors->has('repeat_days') ? 'has-error' : '' }} placeholder-disabled">
      <div class="onoffswitch">
        {!! Form::checkbox('repeat_days[]', 0, old('repeat_days', $mode == 'edit' ? (in_array(0, $repeatDays) ? true : false) : true), [
          'id' => 'repeat_days_0',
          'class' => 'onoffswitch-input'
        ]) !!}
        <label class="onoffswitch-label" for="repeat_days_0"></label>
      </div>
      <span class="onoffswitch-label-master">{{ trans('admin/scheduled_routes.repeat_days_0') }}</span>
    </div>

    @if( $errors->has('repeat_days') )
      <span class="help-block">{{ $errors->first('repeat_days') }}</span>
    @endif
  </div>

  <div class="form-group has-feedback {{ $errors->has('repeat_interval') ? 'has-error' : '' }}">
    {!! Form::label('repeat_interval', trans('admin/scheduled_routes.repeat_interval')) !!}
    {{-- <div class="input-group"> --}}
      {!! Form::number('repeat_interval', old('repeat_interval', $mode == 'edit' && !empty($scheduledRoute->event->id) ? $scheduledRoute->event->getOriginalForm('repeat_interval') : 1), [
        'id' => 'repeat_interval',
        'class' => 'form-control',
        'placeholder' => trans('admin/scheduled_routes.repeat_interval'),
        'min' => '1'
      ]) !!}
      {{-- <span class="input-group-addon"><i class="fa fa-pencil"></i></span> --}}
    {{-- </div> --}}
    @if( $errors->has('repeat_interval') )
      <span class="help-block">{{ $errors->first('repeat_interval') }}</span>
    @endif
  </div>

  <div class="row">
    <div class="col-xs-12 col-sm-6">

      <div class="form-group has-feedback {{ $errors->has('repeat_limit') ? 'has-error' : '' }}">
        {!! Form::label('repeat_limit', trans('admin/scheduled_routes.repeat_limit')) !!}
        {{-- <div class="input-group"> --}}
          {!! Form::number('repeat_limit', old('repeat_limit', $mode == 'edit' && !empty($scheduledRoute->event->id) ? $scheduledRoute->event->getOriginalForm('repeat_limit') : 0), [
            'id' => 'repeat_limit',
            'class' => 'form-control',
            'placeholder' => trans('admin/scheduled_routes.repeat_limit'),
            'min' => '0'
          ]) !!}
          {{-- <span class="input-group-addon"><i class="fa fa-pencil"></i></span> --}}
        {{-- </div> --}}
        @if( $errors->has('repeat_limit') )
          <span class="help-block">{{ $errors->first('repeat_limit') }}</span>
        @endif
      </div>

    </div>
    <div class="col-xs-12 col-sm-6">

      <div class="form-group has-feedback {{ $errors->has('repeat_end') ? 'has-error' : '' }}">
        {!! Form::label('repeat_end', trans('admin/scheduled_routes.repeat_end')) !!}
        {{-- <div class="input-group"> --}}
          {!! Form::text('repeat_end', old('repeat_end', $mode == 'edit' && !empty($scheduledRoute->event->id) ? $scheduledRoute->event->repeat_end : null), [
            'id' => 'repeat_end',
            'class' => 'form-control datepicker',
            'placeholder' => trans('admin/scheduled_routes.repeat_end')
          ]) !!}
          {{-- <span class="input-group-addon"><i class="fa fa-calendar"></i></span> --}}
        {{-- </div> --}}
        @if( $errors->has('repeat_end') )
          <span class="help-block">{{ $errors->first('repeat_end') }}</span>
        @endif
      </div>

    </div>
  </div>

</div>

<div class="form-group has-feedback {{ $errors->has('is_featured') ? 'has-error' : '' }} placeholder-disabled">
  <div class="onoffswitch">
    {!! Form::checkbox('is_featured', 1, old('is_featured', $mode == 'edit' ? null : false), [
      'id' => 'is_featured',
      'class' => 'onoffswitch-input',
    ]) !!}
    <label class="onoffswitch-label" for="is_featured"></label>
  </div>
  <span class="onoffswitch-label-master">{{ trans('admin/scheduled_routes.is_featured') }}</span>
  @if( $errors->has('is_featured') )
    <span class="help-block">{{ $errors->first('is_featured') }}</span>
  @endif
</div>

<div class="row">
  <div class="col-xs-12 col-sm-6 pull-right">

    <div class="form-group has-feedback {{ $errors->has('order') ? 'has-error' : '' }}">
      {!! Form::label('order', trans('admin/scheduled_routes.order')) !!}
      {{-- <div class="input-group"> --}}
        {!! Form::number('order', old('order', $mode == 'edit' ? null : 0), [
          'id' => 'order',
          'class' => 'form-control',
          'placeholder' => trans('admin/scheduled_routes.order'),
          'min' => '0',
          'required',
        ]) !!}
        {{-- <span class="input-group-addon"><i class="fa fa-sort"></i></span> --}}
      {{-- </div> --}}
      @if( $errors->has('order') )
        <span class="help-block">{{ $errors->first('order') }}</span>
      @endif
    </div>

  </div>
  <div class="col-xs-12 col-sm-6">

    <div class="form-group has-feedback {{ $errors->has('status') ? 'has-error' : '' }}">
      {!! Form::label('status', trans('admin/scheduled_routes.status')) !!}
      {{-- <div class="input-group"> --}}
        {!! Form::select('status', [
          'active' => trans('admin/scheduled_routes.statuses.active'),
          'inactive' => trans('admin/scheduled_routes.statuses.inactive'),
        ], old('status', $mode == 'edit' ? null : 'active'), [
          'id' => 'status',
          'class' => 'form-control select2',
          'data-placeholder' => trans('admin/scheduled_routes.status'),
          'required',
        ]) !!}
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
    {!! Form::button(/*'<i class="fa fa-check"></i> '. */trans('admin/scheduled_routes.button.'. ($mode == 'edit' ? 'update' : 'create')), [
      'type' => 'submit',
      'class' => 'btn btn-primary',
    ]) !!}
    <a href="{{ route('admin.scheduled-routes.index') }}" class="btn btn-link">{{ trans('admin/scheduled_routes.button.cancel') }}</a>
  </div>
</div>

@section('subheader')
  <link rel="stylesheet" href="{{ asset_url('plugins','bootstrap-daterangepicker/daterangepicker.css') }}">
  <style>
  .select2-container--default .select2-results__option[aria-disabled=true] {
    display: none !important;
  }
  </style>
@stop

@section('subfooter')
  <script src="{{ asset_url('plugins','moment/moment.min.js') }}"></script>
  <script src="{{ asset_url('plugins','bootstrap-daterangepicker/daterangepicker.js') }}"></script>

  <script type="text/javascript">
  function repeatType() {
    var form = $('#scheduled-routes form.form-master');
    var val = form.find('#repeat_type').val();

    if( val == 'none' ) {
      form.find('.repeat-container').hide();
    }
    else {
      form.find('.repeat-container').show();

      if( val == 'weekly' ) {
        form.find('.repeat-days-container').show();
      }
      else {
        form.find('.repeat-days-container').hide();
      }
    }
  }

  function updateFormPlaceholder(that) {
    var container = $(that).closest('.form-group:not(.placeholder-disabled)');

    if( $(that).val() != '' || container.hasClass('placeholder-visible') ) {
      container.find('label').show();
    }
    else {
      container.find('label').hide();
    }
  }

  function updateVehicles() {
    var form = $('#scheduled-routes form.form-master');
		var oldId = form.find('#vehicle_id').val() ? parseInt(form.find('#vehicle_id').val()) : 0;
    var newId = 0;
    var exists = 0;

		form.find('.form-group-vehicle_id').hide();

		form.find('#vehicle_id option').each(function() {
      $(this).removeAttr('selected');
			if (parseInt($(this).attr('value')) > 0) {
				$(this).attr('disabled', true);
			}
		});

		if (form.find('#driver_id').val() > 0) {
			form.find('#vehicle_id option').each(function() {
				if (parseInt($(this).attr('user_id')) == parseInt(form.find('#driver_id').val())) {
          var value = parseInt($(this).attr('value'));
          var is_featured = parseInt($(this).attr('is_featured'));
					if (newId == 0 || is_featured) { newId = value; }
          if (oldId == value) { exists = 1; }
          $(this).attr('disabled', false);
				}
			});
      if (!exists) { oldId = newId; }
			form.find('.form-group-vehicle_id').show();
		}
		else {
			oldId = 0;
		}

		form.find('#vehicle_id').select2('destroy').select2().val(oldId).change();
	}

  $(document).ready(function() {

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

    var form = $('#scheduled-routes form.form-master');

    var driversOptions = {!! json_encode($driversOptions) !!};
    var vehiclesOptions = {!! json_encode($vehiclesOptions) !!};

    $.each(driversOptions, function(k, v) {
        form.find('#driver_id option[value="'+ k +'"]').attr('commission', v.commission);
    });

    $.each(vehiclesOptions, function(k, v) {
        form.find('#vehicle_id option[value="'+ k +'"]').attr('user_id', v.user_id);
        form.find('#vehicle_id option[value="'+ k +'"]').attr('is_featured', v.is_featured);
    });

    form.find('#driver_id').change(function() {
        updateVehicles();
    }).change();

    form.find('#repeat_type').change(function() {
        repeatType();
    }).change();

    form.find('input:not([type="submit"]), textarea, select').each(function() {
      updateFormPlaceholder(this);
    })
    .bind('change keyup', function(e) {
      updateFormPlaceholder(this);
    });
  });
  </script>
@stop
