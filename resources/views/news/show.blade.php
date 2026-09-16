@extends('admin.index')

@section('title', trans('news.page_title'))
@section('subtitle', /*'<i class="fa fa-arrow-circle-up"></i> '.*/ trans('news.page_title') )

@section('subcontent')
<div class="row">
    <div class="col-md-12">
        @if (!empty($item))
        <div class="box no-border" style="box-shadow:none;">
            <div class="box-header">
                <h3 class="box-title">{{ $item->name }}</h3>
                <div class="box-tools pull-right" title="{!! format_date_time($item->created_at) !!}">
                    {!! $item->created_at->diffForHumans() !!}
                </div>
            </div>
            <div class="box-body no-border">
                {!! $item->description !!}
            </div>
            <div class="box-footer no-border">
                <a href="{{ redirect()->back()->getTargetUrl() }}" class="btn btn-default btn-sm">{{ trans('news.buttons.go_back') }}</a>
            </div>
        </div>
        @endif
    </div>
</div>
@stop
