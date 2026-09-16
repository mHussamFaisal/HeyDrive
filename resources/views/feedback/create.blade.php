@extends('layouts.app')

@section('title', trans('feedback.page_title') .' / '. trans('feedback.subtitle.create'))

@section('header')
  @include('partials.override_css')
@stop

@section('content')
  <div id="feedback">
    <div class="row">
      <div class="col-md-8 col-lg-6 col-md-offset-2 col-lg-offset-3">
        @include('partials.alerts.success', ['close' => false])
        @include('partials.alerts.errors', ['time' => 5000])

        <h3 style="margin-bottom: 15px;">{{ trans('feedback.subtitle.create') }}</h3>

        {!! Form::open(['method' => 'post', 'route' => 'feedback.store', 'class' => 'form-master']) !!}
          <div class="form-group has-feedback {{ $errors->has('type') ? 'has-error' : '' }} {{ in_array(request('type'), ['comment', 'lost_found', 'complaint']) ? 'hide' : '' }}">
            {{-- {!! Form::label('type', trans('feedback.type')) !!} --}}
            <div class="input-group">
              {!! Form::select('type', [
                'comment' => trans('feedback.types.comment'),
                'lost_found' => trans('feedback.types.lost_found'),
                'complaint' => trans('feedback.types.complaint'),
              ], old('type', 'comment'), [
                'id' => 'type',
                'class' => 'form-control select2',
                'data-placeholder' => trans('feedback.type'),
                'required',
              ]) !!}
              <span class="input-group-addon"><i class="fa fa-pencil"></i></span>
            </div>
            @if( $errors->has('type') )
              <span class="help-block">{{ $errors->first('type') }}</span>
            @endif
          </div>

          <div class="row">
            <div class="col-xs-12 col-sm-6">

              <div class="form-group has-feedback {{ $errors->has('name') ? 'has-error' : '' }}">
                {!! Form::label('name', trans('feedback.name')) !!}
                <div class="input-group">
                  {!! Form::text('name', old('name'), [
                    'id' => 'name',
                    'class' => 'form-control',
                    'placeholder' => trans('feedback.name'),
                    'required',
                  ]) !!}
                  <span class="input-group-addon"><i class="fa fa-user"></i></span>
                </div>
                @if( $errors->has('name') )
                  <span class="help-block">{{ $errors->first('name') }}</span>
                @endif
              </div>

            </div>
            <div class="col-xs-12 col-sm-6">

              <div class="form-group has-feedback {{ $errors->has('email') ? 'has-error' : '' }}">
                {!! Form::label('email', trans('feedback.email')) !!}
                <div class="input-group">
                  {!! Form::text('email', old('email'), [
                    'id' => 'email',
                    'class' => 'form-control',
                    'placeholder' => trans('feedback.email'),
                    'required',
                  ]) !!}
                  <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                </div>
                @if( $errors->has('email') )
                  <span class="help-block">{{ $errors->first('email') }}</span>
                @endif
              </div>

            </div>
          </div>


          <div class="row">
            <div class="col-xs-12 col-sm-6">

              <div class="form-group has-feedback {{ $errors->has('phone') ? 'has-error' : '' }}">
                {!! Form::label('phone', trans('feedback.phone')) !!}
                <div class="input-group">
                  {!! Form::text('phone', old('phone'), [
                    'id' => 'phone',
                    'class' => 'form-control',
                    'placeholder' => trans('feedback.phone'),
                  ]) !!}
                  <span class="input-group-addon"><i class="fa fa-phone"></i></span>
                </div>
                @if( $errors->has('phone') )
                  <span class="help-block">{{ $errors->first('phone') }}</span>
                @endif
              </div>

            </div>
            <div class="col-xs-12 col-sm-6">

              <div class="form-group has-feedback {{ $errors->has('ref_number') ? 'has-error' : '' }}">
                {!! Form::label('ref_number', trans('feedback.ref_number')) !!}
                <div class="input-group">
                  {!! Form::text('ref_number', old('ref_number'), [
                    'id' => 'ref_number',
                    'class' => 'form-control',
                    'placeholder' => trans('feedback.ref_number'),
                    'required',
                  ]) !!}
                  <span class="input-group-addon"><i class="fa fa-pencil"></i></span>
                </div>
                @if( $errors->has('ref_number') )
                  <span class="help-block">{{ $errors->first('ref_number') }}</span>
                @endif
              </div>

            </div>
          </div>

          <div class="form-group has-feedback {{ $errors->has('description') ? 'has-error' : '' }}">
            {!! Form::label('description', trans('feedback.description')) !!}
            <div class="input-group">
              {!! Form::textarea('description', old('description'), [
                'id' => 'description',
                'class' => 'form-control',
                'placeholder' => trans('feedback.description'),
                'rows' => '2',
                'required',
              ]) !!}
              <span class="input-group-addon"><i class="fa fa-comment"></i></span>
            </div>
            @if( $errors->has('description') )
              <span class="help-block">{{ $errors->first('description') }}</span>
            @endif
          </div>

          <div class="row">
            <div class="col-sm-12">
              {!! Form::button('<i class="fa fa-paper-plane"></i> '. trans('feedback.button.create'), [
                'type' => 'submit',
                'class' => 'btn btn-default',
              ]) !!}
              {{-- <a href="{{ route('feedback.index', request('type') ? ['type' => request('type')] : []) }}" class="btn btn-link">{{ trans('feedback.button.cancel') }}</a> --}}
            </div>
          </div>
        {!! Form::close() !!}
      </div>
    </div>
  </div>
@stop

@section('footer')
  <script src="{{ asset_url('plugins','autosize/autosize.min.js') }}"></script>

  <script type="text/javascript">
  $(document).ready(function() {
    var form = $('#feedback form.form-master');

    autosize(form.find('textarea'));

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
