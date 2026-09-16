@extends('driver.index')

@section('title', trans('driver/calendar.page_title') .' / '. trans('driver/calendar.subtitle.edit'))
@section('subtitle', /*'<i class="fa fa-calendar"></i> '*/ '<a href="'. route('driver.calendar.index') .'">'. trans('driver/calendar.page_title') .'</a> / '. trans('driver/calendar.subtitle.edit') )

@section('subcontent')
<div id="calendar">
    @include('partials.alerts.success')
    @include('partials.alerts.errors')

    <div class="row">
        <div class="col-md-8 col-lg-6 col-md-offset-2 col-lg-offset-3">
            {!! Form::model($event, ['method' => 'patch', 'route' => ['driver.calendar.update', $event->id], 'files' => false, 'class' => 'form-master']) !!}
                @include('driver.calendar.form', ['mode' => 'edit'])
            {!! Form::close() !!}

            @permission('driver.calendar.destroy')
            <div style="margin-top:20px;" class="hide">
                {!! Form::open(['method' => 'delete', 'route' => ['driver.calendar.destroy', $event->id], 'class' => 'form-inline form-delete', 'id' => 'form-delete-event']) !!}
                    @if( request('tmpl') == 'body' )
                        <input type="hidden" name="tmpl" value="body" />
                    @endif
                    {!! Form::button('<i class="fa fa-trash-o"></i> <span>'. trans('driver/calendar.button.destroy') .'</span>', ['title' => trans('driver/calendar.button.destroy'), 'type' => 'submit', 'class' => 'btn btn-link delete', 'name' => 'delete_modal']) !!}
                {!! Form::close() !!}
            </div>
            @endpermission
        </div>
    </div>
</div>
@stop
