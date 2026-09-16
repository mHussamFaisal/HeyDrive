
<div class="form-group has-feedback{{ $errors->has('name') ? ' has-error' : '' }}">
    {!! Form::label('name', trans('teams.name')) !!}
    {{-- <div class="input-group"> --}}
        {!! Form::text('name', old('name', $mode == 'edit' ? $team->name : null), ['id' => 'name', 'class' => 'form-control', 'placeholder' => trans('teams.name'), 'required']) !!}
        {{-- <span class="input-group-addon"><i class="fa fa-pencil"></i></span> --}}
    {{-- </div> --}}
    @if( $errors->has('name') )
      <span class="help-block">{{ $errors->first('name') }}</span>
    @endif
</div>

<div class="form-group has-feedback{{ $errors->has('internal_note') ? ' has-error' : '' }}">
    {!! Form::label('internal_note', trans('teams.internal_note')) !!}
    {{-- <div class="input-group"> --}}
        {!! Form::textarea('internal_note', old('internal_note'), ['id' => 'internal_note', 'class' => 'form-control', 'placeholder' => trans('teams.internal_note'), 'rows' => '2']) !!}
        {{-- <span class="input-group-addon"><i class="fa fa-pencil"></i></span> --}}
    {{-- </div> --}}
    @if( $errors->has('internal_note') )
        <span class="help-block">{{ $errors->first('internal_note') }}</span>
    @endif
</div>


<div class="row">
  <div class="col-xs-12 col-sm-6">

    <div class="form-group has-feedback{{ $errors->has('status') ? ' has-error' : '' }}">
        {!! Form::label('status', trans('teams.status')) !!}
        {{-- <div class="input-group"> --}}
            {!! Form::select('status', $statusList, old('status', $mode == 'create' ? 1 : null), ['id' => 'status', 'class' => 'form-control select2', 'data-placeholder' => trans('teams.status'), 'required']) !!}
            {{-- <span class="input-group-addon"><i class="fa fa-eye"></i></span> --}}
        {{-- </div> --}}
        @if( $errors->has('status') )
            <span class="help-block">{{ $errors->first('status') }}</span>
        @endif
    </div>

  </div>
  <div class="col-xs-12 col-sm-6">

    <div class="form-group has-feedback{{ $errors->has('order') ? ' has-error' : '' }}">
        {!! Form::label('order', trans('teams.order')) !!}
        {{-- <div class="input-group"> --}}
            {!! Form::number('order', old('order', $mode == 'edit' ? $team->order : 0), ['id' => 'order', 'class' => 'form-control', 'placeholder' => trans('teams.order'), 'required', 'min' => '0', 'step' => '1']) !!}
            {{-- <span class="input-group-addon"><i class="fa fa-sort"></i></span> --}}
        {{-- </div> --}}
        @if( $errors->has('order') )
            <span class="help-block">{{ $errors->first('order') }}</span>
        @endif
    </div>

  </div>
</div>

<div class="row">
    <div class="col-sm-12">
        {!! Form::button(($mode == 'edit' ? trans('teams.button.update') : trans('teams.button.create')), ['type' => 'submit', 'class' => 'btn btn-primary']) !!}
        <a href="{{ route('teams.index') }}" class="btn btn-link">{{ trans('teams.button.cancel') }}</a>
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
