@include('partials.modals.delete')

@if( request('tmpl') == 'body' )
    <div style="margin-top:10px;"></div>
    <input type="hidden" name="tmpl" value="body" />
@endif

<div class="form-group has-feedback{{ $errors->has('name') ? ' has-error' : '' }}">
    {!! Form::label('name', trans('driver/calendar.name')) !!}
    {{-- <div class="input-group"> --}}
        {!! Form::text('name', old('name', $mode == 'edit' ? $event->getOriginalForm('name') : null), ['id' => 'name', 'class' => 'form-control', 'placeholder' => trans('driver/calendar.name'), 'required']) !!}
        {{-- <span class="input-group-addon"><i class="fa fa-pencil"></i></span> --}}
    {{-- </div> --}}
    @if( $errors->has('name') )
        <span class="help-block">{{ $errors->first('name') }}</span>
    @endif
</div>

<div class="form-group has-feedback{{ $errors->has('description') ? ' has-error' : '' }}">
    {!! Form::label('description', trans('driver/calendar.description')) !!}
    {{-- <div class="input-group"> --}}
        {!! Form::textarea('description', old('description'), ['id' => 'description', 'class' => 'form-control', 'placeholder' => trans('driver/calendar.description'), 'rows' => '2']) !!}
        {{-- <span class="input-group-addon"><i class="fa fa-pencil"></i></span> --}}
    {{-- </div> --}}
    @if( $errors->has('description') )
        <span class="help-block">{{ $errors->first('description') }}</span>
    @endif
</div>

<div class="form-group has-feedback{{ $errors->has('start_at') ? ' has-error' : '' }} placeholder-visible">
    {!! Form::label('start_at', trans('driver/calendar.start_at')) !!}
    {{-- <div class="input-group"> --}}
        {!! Form::text('start_at', old('start_at', $event->start_at), ['id' => 'start_at', 'class' => 'form-control datepicker', 'placeholder' => trans('driver/calendar.start_at')]) !!}
        {{-- <span class="input-group-addon"><i class="fa fa-pencil"></i></span> --}}
    {{-- </div> --}}
    @if( $errors->has('start_at') )
      <span class="help-block">{{ $errors->first('start_at') }}</span>
    @endif
</div>

<div class="form-group has-feedback{{ $errors->has('end_at') ? ' has-error' : '' }} placeholder-visible">
    {!! Form::label('end_at', trans('driver/calendar.end_at')) !!}
    {{-- <div class="input-group"> --}}
        {!! Form::text('end_at', old('end_at', $event->end_at), ['id' => 'end_at', 'class' => 'form-control datepicker', 'placeholder' => trans('driver/calendar.end_at')]) !!}
        {{-- <span class="input-group-addon"><i class="fa fa-pencil"></i></span> --}}
    {{-- </div> --}}
    @if( $errors->has('end_at') )
      <span class="help-block">{{ $errors->first('end_at') }}</span>
    @endif
</div>

<div class="form-group has-feedback{{ $errors->has('repeat_type') ? ' has-error' : '' }} hide1">
    {!! Form::label('repeat_type', trans('driver/calendar.repeat_type')) !!}
    {{-- <div class="input-group"> --}}
        {!! Form::select('repeat_type', $event->options->repeat_type, old('repeat_type', $mode == 'create' ? 'none' : null), ['id' => 'repeat_type', 'class' => 'form-control select2', 'data-placeholder' => trans('driver/calendar.repeat_type'), 'required', 'data-minimum-results-for-search' => 'Infinity']) !!}
        {{-- <span class="input-group-addon"><i class="fa fa-eye"></i></span> --}}
    {{-- </div> --}}
    @if( $errors->has('repeat_type') )
        <span class="help-block">{{ $errors->first('repeat_type') }}</span>
    @endif
</div>

<div class="repeat-container" style="display:none;">

    <div class="repeat-days-container">
        <div class="repeat-days-title">{{ trans('driver/calendar.repeat_days_title') }}</div>

        <div class="form-group has-feedback{{ $errors->has('repeat_days') ? ' has-error' : '' }} placeholder-disabled">
            <div class="onoffswitch">
                {!! Form::checkbox('repeat_days[]', 1, old('repeat_days', $mode == 'edit' ? (in_array(1, (array)$event->repeat_days) ? true : false) : true), ['id' => 'repeat_days_1', 'class' => 'onoffswitch-input']) !!}
                <label class="onoffswitch-label" for="repeat_days_1"></label>
            </div>
            <span class="onoffswitch-label-master">{{ trans('driver/calendar.repeat_days_1') }}</span>
        </div>

        <div class="form-group has-feedback{{ $errors->has('repeat_days') ? ' has-error' : '' }} placeholder-disabled">
            <div class="onoffswitch">
                {!! Form::checkbox('repeat_days[]', 2, old('repeat_days', $mode == 'edit' ? (in_array(2, (array)$event->repeat_days) ? true : false) : true), ['id' => 'repeat_days_2', 'class' => 'onoffswitch-input']) !!}
                <label class="onoffswitch-label" for="repeat_days_2"></label>
            </div>
            <span class="onoffswitch-label-master">{{ trans('driver/calendar.repeat_days_2') }}</span>
        </div>

        <div class="form-group has-feedback{{ $errors->has('repeat_days') ? ' has-error' : '' }} placeholder-disabled">
            <div class="onoffswitch">
                {!! Form::checkbox('repeat_days[]', 3, old('repeat_days', $mode == 'edit' ? (in_array(3, (array)$event->repeat_days) ? true : false) : true), ['id' => 'repeat_days_3', 'class' => 'onoffswitch-input']) !!}
                <label class="onoffswitch-label" for="repeat_days_3"></label>
            </div>
            <span class="onoffswitch-label-master">{{ trans('driver/calendar.repeat_days_3') }}</span>
        </div>

        <div class="form-group has-feedback{{ $errors->has('repeat_days') ? ' has-error' : '' }} placeholder-disabled">
            <div class="onoffswitch">
                {!! Form::checkbox('repeat_days[]', 4, old('repeat_days', $mode == 'edit' ? (in_array(4, (array)$event->repeat_days) ? true : false) : true), ['id' => 'repeat_days_4', 'class' => 'onoffswitch-input']) !!}
                <label class="onoffswitch-label" for="repeat_days_4"></label>
            </div>
            <span class="onoffswitch-label-master">{{ trans('driver/calendar.repeat_days_4') }}</span>
        </div>

        <div class="form-group has-feedback{{ $errors->has('repeat_days') ? ' has-error' : '' }} placeholder-disabled">
            <div class="onoffswitch">
                {!! Form::checkbox('repeat_days[]', 5, old('repeat_days', $mode == 'edit' ? (in_array(5, (array)$event->repeat_days) ? true : false) : true), ['id' => 'repeat_days_5', 'class' => 'onoffswitch-input']) !!}
                <label class="onoffswitch-label" for="repeat_days_5"></label>
            </div>
            <span class="onoffswitch-label-master">{{ trans('driver/calendar.repeat_days_5') }}</span>
        </div>

        <div class="form-group has-feedback{{ $errors->has('repeat_days') ? ' has-error' : '' }} placeholder-disabled">
            <div class="onoffswitch">
                {!! Form::checkbox('repeat_days[]', 6, old('repeat_days', $mode == 'edit' ? (in_array(6, (array)$event->repeat_days) ? true : false) : true), ['id' => 'repeat_days_6', 'class' => 'onoffswitch-input']) !!}
                <label class="onoffswitch-label" for="repeat_days_6"></label>
            </div>
            <span class="onoffswitch-label-master">{{ trans('driver/calendar.repeat_days_6') }}</span>
        </div>

        <div class="form-group has-feedback{{ $errors->has('repeat_days') ? ' has-error' : '' }} placeholder-disabled">
            <div class="onoffswitch">
                {!! Form::checkbox('repeat_days[]', 0, old('repeat_days', $mode == 'edit' ? (in_array(0, (array)$event->repeat_days) ? true : false) : true), ['id' => 'repeat_days_0', 'class' => 'onoffswitch-input']) !!}
                <label class="onoffswitch-label" for="repeat_days_0"></label>
            </div>
            <span class="onoffswitch-label-master">{{ trans('driver/calendar.repeat_days_0') }}</span>
        </div>

        @if( $errors->has('repeat_days') )
            <span class="help-block">{{ $errors->first('repeat_days') }}</span>
        @endif
    </div>

    <div class="form-group has-feedback{{ $errors->has('repeat_interval') ? ' has-error' : '' }}">
        {!! Form::label('repeat_interval', trans('driver/calendar.repeat_interval')) !!}
        {{-- <div class="input-group"> --}}
            {!! Form::number('repeat_interval', old('repeat_interval', $mode == 'edit' ? $event->getOriginalForm('repeat_interval') : null), ['id' => 'repeat_interval', 'class' => 'form-control', 'placeholder' => trans('driver/calendar.repeat_interval'), 'min' => '0']) !!}
            {{-- <span class="input-group-addon"><i class="fa fa-pencil"></i></span> --}}
        {{-- </div> --}}
        @if( $errors->has('repeat_interval') )
            <span class="help-block">{{ $errors->first('repeat_interval') }}</span>
        @endif
    </div>

    <div class="form-group has-feedback{{ $errors->has('repeat_limit') ? ' has-error' : '' }}">
        {!! Form::label('repeat_limit', trans('driver/calendar.repeat_limit')) !!}
        {{-- <div class="input-group"> --}}
            {!! Form::number('repeat_limit', old('repeat_limit', $mode == 'edit' ? $event->getOriginalForm('repeat_limit') : null), ['id' => 'repeat_limit', 'class' => 'form-control', 'placeholder' => trans('driver/calendar.repeat_limit'), 'min' => '0']) !!}
            {{-- <span class="input-group-addon"><i class="fa fa-pencil"></i></span> --}}
        {{-- </div> --}}
        @if( $errors->has('repeat_limit') )
            <span class="help-block">{{ $errors->first('repeat_limit') }}</span>
        @endif
    </div>

    <div class="form-group has-feedback{{ $errors->has('repeat_end') ? ' has-error' : '' }} placeholder-visible">
        {!! Form::label('repeat_end', trans('driver/calendar.repeat_end')) !!}
        {{-- <div class="input-group"> --}}
            {!! Form::text('repeat_end', old('repeat_end', $mode == 'edit' ? $event->repeat_end : null), ['id' => 'repeat_end', 'class' => 'form-control datepicker', 'placeholder' => trans('driver/calendar.repeat_end')]) !!}
            {{-- <span class="input-group-addon"><i class="fa fa-pencil"></i></span> --}}
        {{-- </div> --}}
        @if( $errors->has('repeat_end') )
          <span class="help-block">{{ $errors->first('repeat_end') }}</span>
        @endif
    </div>

</div>

<div class="form-group has-feedback{{ $errors->has('ordering') ? ' has-error' : '' }} hide">
    {!! Form::label('ordering', trans('driver/calendar.ordering')) !!}
    {{-- <div class="input-group"> --}}
        {!! Form::number('ordering', old('ordering', $mode == 'edit' ? $event->getOriginalForm('ordering') : null), ['id' => 'ordering', 'class' => 'form-control', 'placeholder' => trans('driver/calendar.ordering'), 'min' => '0']) !!}
        {{-- <span class="input-group-addon"><i class="fa fa-pencil"></i></span> --}}
    {{-- </div> --}}
    @if( $errors->has('ordering') )
        <span class="help-block">{{ $errors->first('ordering') }}</span>
    @endif
</div>

<div class="form-group has-feedback{{ $errors->has('status') ? ' has-error' : '' }}">
    {!! Form::label('status', trans('driver/calendar.status')) !!}
    {{-- <div class="input-group"> --}}
        {!! Form::select('status', $status, old('status', $mode == 'create' ? 'inactive' : null), ['id' => 'status', 'class' => 'form-control select2', 'data-placeholder' => trans('driver/calendar.status'), 'required']) !!}
        {{-- <span class="input-group-addon"><i class="fa fa-eye"></i></span> --}}
    {{-- </div> --}}
    @if( $errors->has('status') )
        <span class="help-block">{{ $errors->first('status') }}</span>
    @endif
</div>

<div class="row">
    <div class="col-xs-12">
        @if($mode == 'edit')
            @permission('driver.calendar.edit')
            {!! Form::button('<i class="fa fa-check"></i> '. trans('driver/calendar.button.update'), ['type' => 'submit', 'class' => 'btn btn-primary']) !!}
            @endpermission
        @else
            @permission('driver.calendar.create')
            {!! Form::button('<i class="fa fa-check"></i> '. trans('driver/calendar.button.create'), ['type' => 'submit', 'class' => 'btn btn-primary']) !!}
            @endpermission
        @endif

        @if( request('tmpl') != 'body' )
            <a href="{{ route('driver.calendar.index') }}" class="btn btn-link">{{ trans('driver/calendar.button.cancel') }}</a>
        @endif

        @permission('driver.calendar.destroy')
        @if( $mode == 'edit' )
            <div style="text-align:right; display:inline-block;">
                <a href="#" class="btn btn-link btnDeleteEvent">
                    {{-- <i class="fa fa-trash-o"></i>  --}}
                    <span>{{ trans('driver/calendar.button.destroy') }}</span>
                </a>
            </div>
        @endif
        @endpermission
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

        @if (request('tmpl') == 'body' && request('close') == 1)
            parent.$('#modal-popup').modal('hide');
        @endif

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

        // Repeat type
        function repeatType() {
            var val = $('select[name="repeat_type"]').val();

            if( val == 'none' ) {
                $('.repeat-container').hide();
            }
            else {
                $('.repeat-container').show();

                if( val == 'weekly' ) {
                    $('.repeat-days-container').show();
                }
                else {
                    $('.repeat-days-container').hide();
                }
            }
        }

        repeatType();

        $('select[name="repeat_type"]').change(function() {
            repeatType();
        });

        // Delete
        $('.form-delete').on('click', function(e){
            e.preventDefault();
            var $form = $(this);
            $('#modal-delete').modal().on('click', '#delete-btn', function(){
                $form.submit();
            });
        });

        $('.btnDeleteEvent').on('click', function(e){
            e.preventDefault();
            $('.form-delete').click();
        });
    });
    </script>
@stop
