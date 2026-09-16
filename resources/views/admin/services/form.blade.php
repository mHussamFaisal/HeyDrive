<div class="form-group has-feedback {{ $errors->has('name') ? 'has-error' : '' }}">
  {!! Form::label('name', trans('admin/services.name')) !!}
  {{-- <div class="input-group"> --}}
    {!! Form::text('name', old('name'), [
      'id' => 'name',
      'class' => 'form-control',
      'placeholder' => trans('admin/services.name'),
      'required',
    ]) !!}
    {{-- <span class="input-group-addon"><i class="fa fa-pencil"></i></span> --}}
  {{-- </div> --}}
  @if( $errors->has('name') )
    <span class="help-block">{{ $errors->first('name') }}</span>
  @endif
</div>

<div class="form-group has-feedback {{ $errors->has('description') ? 'has-error' : '' }}">
  {!! Form::label('description', trans('admin/services.description')) !!}
  {{-- <div class="input-group"> --}}
    {!! Form::textarea('description', old('description'), [
      'id' => 'description',
      'class' => 'form-control',
      'placeholder' => trans('admin/services.description'),
      'rows' => '2',
    ]) !!}
    {{-- <span class="input-group-addon"><i class="fa fa-pencil"></i></span> --}}
  {{-- </div> --}}
  @if( $errors->has('description') )
    <span class="help-block">{{ $errors->first('description') }}</span>
  @endif
</div>

<div class="row">
  <div class="col-xs-12 col-sm-6">

    <div class="form-group has-feedback {{ $errors->has('type') ? 'has-error' : '' }}">
      {!! Form::label('type', trans('admin/services.type')) !!}
      {{-- <div class="input-group"> --}}
        {!! Form::select('type', [
          'standard' => trans('admin/services.types.standard'),
          'scheduled' => trans('admin/services.types.scheduled'),
        ], old('type', $mode == 'edit' ? null : 'standard'), [
          'id' => 'type',
          'class' => 'form-control select2',
          'data-placeholder' => trans('admin/services.type'),
          'required',
        ]) !!}
        {{-- <span class="input-group-addon"><i class="fa fa-pencil"></i></span> --}}
      {{-- </div> --}}
      @if( $errors->has('type') )
        <span class="help-block">{{ $errors->first('type') }}</span>
      @endif
    </div>

  </div>
  <div class="col-xs-12 col-sm-6">

    <div class="form-group has-feedback {{ $errors->has('factor_value') ? 'has-error' : '' }} standard-container">
      {!! Form::label('factor_value', trans('admin/services.factor')) !!}
      <div class="input-group1">
        <span class="factor-type">
          {!! Form::select('factor_type', [
            'addition' => trans('admin/services.factor_types.addition_symbol'),
            'multiplication' => trans('admin/services.factor_types.multiplication_symbol'),
          ], old('factor_type', $mode == 'edit' ? $service->params->factor_type : 'addition'), [
            'id' => 'factor_type',
            'class' => 'form-control select2',
            'data-placeholder' => trans('admin/services.factor_type'),
          ]) !!}
        </span>
        <span class="factor-value">
          {!! Form::number('factor_value', old('factor_value', $mode == 'edit' ? $service->params->factor_value : 0), [
            'id' => 'factor_value',
            'class' => 'form-control',
            'placeholder' => trans('admin/services.factor_value'),
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
</div>

<div class="form-group has-feedback {{ $errors->has('is_featured') ? 'has-error' : '' }} placeholder-disabled">
  <div class="onoffswitch">
    {!! Form::checkbox('is_featured', 1, old('is_featured', $mode == 'edit' ? null : false), [
      'id' => 'is_featured',
      'class' => 'onoffswitch-input',
    ]) !!}
    <label class="onoffswitch-label" for="is_featured"></label>
  </div>
  <span class="onoffswitch-label-master">{{ trans('admin/services.is_featured') }}</span>
  @if( $errors->has('is_featured') )
    <span class="help-block">{{ $errors->first('is_featured') }}</span>
  @endif
</div>

<div class="standard-container">

  <div class="form-group has-feedback {{ $errors->has('availability') ? 'has-error' : '' }} placeholder-disabled">
    <div class="onoffswitch">
      {!! Form::checkbox('availability', 1, old('availability', $mode == 'edit' ? (config('site.allow_driver_availability') ? $service->params->availability : false) : false), [
        'id' => 'availability',
        'class' => 'onoffswitch-input',
        'disabled' => !config('site.allow_driver_availability') ? true : false,
      ]) !!}
      <label class="onoffswitch-label" for="availability"></label>
    </div>
    <span class="onoffswitch-label-master">
      <span>{{ trans('admin/services.availability') }}</span>
      @if(!config('site.allow_driver_availability'))
        <i class="ion-ios-information-outline" style="display:inline-block; margin-left:10px; position: absolute; font-size:20px; line-height: 16px;" data-toggle="popover" data-title="" data-content='To use this module, you must first buy it. Please contact us for more information.'></i>
      @endif
    </span>
    @if( $errors->has('availability') )
      <span class="help-block">{{ $errors->first('availability') }}</span>
    @endif
  </div>

  <div class="form-group has-feedback {{ $errors->has('hide_location') ? 'has-error' : '' }} placeholder-disabled">
    <div class="onoffswitch">
      {!! Form::checkbox('hide_location', 1, old('hide_location', $mode == 'edit' ? $service->params->hide_location : false), [
        'id' => 'hide_location',
        'class' => 'onoffswitch-input',
      ]) !!}
      <label class="onoffswitch-label" for="hide_location"></label>
    </div>
    <span class="onoffswitch-label-master">{{ trans('admin/services.hide_location') }}</span>
    @if( $errors->has('hide_location') )
      <span class="help-block">{{ $errors->first('hide_location') }}</span>
    @endif
  </div>

  <div class="form-group has-feedback {{ $errors->has('duration') ? 'has-error' : '' }} placeholder-disabled">
    <div class="onoffswitch">
      {!! Form::checkbox('duration', 1, old('duration', $mode == 'edit' ? $service->params->duration : false), [
        'id' => 'duration',
        'class' => 'onoffswitch-input',
      ]) !!}
      <label class="onoffswitch-label" for="duration"></label>
    </div>
    <span class="onoffswitch-label-master">{{ trans('admin/services.duration') }}</span>
    @if( $errors->has('duration') )
      <span class="help-block">{{ $errors->first('duration') }}</span>
    @endif
  </div>

  <div class="row duration-container" style="display:none;">
    <div class="col-xs-12 col-sm-6">

      <div class="form-group has-feedback {{ $errors->has('duration_min') ? 'has-error' : '' }}">
        {!! Form::label('duration_min', trans('admin/services.duration_min')) !!}
        {{-- <div class="input-group"> --}}
          {!! Form::number('duration_min', old('duration_min', $mode == 'edit' ? $service->params->duration_min : 0), [
            'id' => 'duration_min',
            'class' => 'form-control',
            'placeholder' => trans('admin/services.duration_min'),
            'min' => '0',
            'required',
          ]) !!}
          {{-- <span class="input-group-addon"><i class="fa fa-clock-o"></i></span> --}}
        {{-- </div> --}}
        @if( $errors->has('duration_min') )
          <span class="help-block">{{ $errors->first('duration_min') }}</span>
        @endif
      </div>

    </div>
    <div class="col-xs-12 col-sm-6">

      <div class="form-group has-feedback {{ $errors->has('duration_max') ? 'has-error' : '' }}">
        {!! Form::label('duration_max', trans('admin/services.duration_max')) !!}
        {{-- <div class="input-group"> --}}
          {!! Form::number('duration_max', old('duration_max', $mode == 'edit' ? $service->params->duration_max : 0), [
            'id' => 'duration_max',
            'class' => 'form-control',
            'placeholder' => trans('admin/services.duration_max'),
            'min' => '0',
            'required',
          ]) !!}
          {{-- <span class="input-group-addon"><i class="fa fa-clock-o"></i></span> --}}
        {{-- </div> --}}
        @if( $errors->has('duration_max') )
          <span class="help-block">{{ $errors->first('duration_max') }}</span>
        @endif
      </div>

    </div>
  </div>

</div>

<div class="row">
  <div class="col-xs-12 col-sm-6 pull-right">

    <div class="form-group has-feedback {{ $errors->has('order') ? 'has-error' : '' }}">
      {!! Form::label('order', trans('admin/services.order')) !!}
      {{-- <div class="input-group"> --}}
        {!! Form::number('order', old('order', $mode == 'edit' ? null : 0), [
          'id' => 'order',
          'class' => 'form-control',
          'placeholder' => trans('admin/services.order'),
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
      {!! Form::label('status', trans('admin/services.status')) !!}
      {{-- <div class="input-group"> --}}
        {!! Form::select('status', [
          'active' => trans('admin/services.statuses.active'),
          'inactive' => trans('admin/services.statuses.inactive'),
        ], old('status', $mode == 'edit' ? null : 'active'), [
          'id' => 'status',
          'class' => 'form-control select2',
          'data-placeholder' => trans('admin/services.status'),
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
    {!! Form::button(/*'<i class="fa fa-check"></i> '. */trans('admin/services.button.'. ($mode == 'edit' ? 'update' : 'create')), [
      'type' => 'submit',
      'class' => 'btn btn-primary',
    ]) !!}
    <a href="{{ route('admin.services.index') }}" class="btn btn-link">{{ trans('admin/services.button.cancel') }}</a>
  </div>
</div>

@section('subfooter')
  <script src="{{ asset_url('plugins','autosize/autosize.min.js') }}"></script>

  <script type="text/javascript">
  $(document).ready(function() {

    $('[data-toggle="popover"]').popover({
        placement: 'auto right',
        container: 'body',
        trigger: 'click focus hover',
        html: true
    });

    var form = $('#services form.form-master');

    autosize(form.find('textarea'));

    form.find('#type').change(function(){
      if ($(this).val() == 'standard') {
        form.find('.standard-container').show();
      }
      else {
        form.find('.standard-container').hide();
      }
    })
    .change();

    form.find('#duration').change(function(){
      if ($(this).is(':checked')) {
        form.find('.duration-container').show();
      }
      else {
        form.find('.duration-container').hide();
      }
    })
    .change();

    function updateFormPlaceholder(that) {
      var container = $(that).closest('.form-group:not(.placeholder-disabled)');

      if( $(that).val() != '' || container.hasClass('placeholder-visible') ) {
        container.find('label').show();
      }
      else {
        container.find('label').hide();
      }
    }

    form.find('input:not([type="submit"]), textarea, select').each(function() {
      updateFormPlaceholder(this);
    })
    .bind('change keyup', function(e) {
      updateFormPlaceholder(this);
    });
  });
  </script>
@stop
