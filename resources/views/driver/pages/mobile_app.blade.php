@extends('driver.index')

@section('title', trans('driver/pages.mobile_app.page_title'))
@section('subtitle', /*'<i class="fa fa-download"></i> '.*/ trans('driver/pages.mobile_app.page_title'))

@section('subcontent')
    <div id="mobile_app">
        <h4>{{ trans('driver/pages.mobile_app.how_to') }}</h4>

        @if( trans('driver/pages.mobile_app.steps') )
            <div class="app-steps">
                <ol>
                    @foreach (trans('driver/pages.mobile_app.steps') as $key => $value)
                        <li>{!! trans('driver/pages.mobile_app.steps.'. $key, [
                                'host_url' => url('/') .'/'
                            ]) !!}</li>
                    @endforeach
                </ol>
            </div>
        @endif

        <div class="app-download-container">
            {{--
            <div class="app-download-header">{{ trans('driver/pages.mobile_app.download') }}</div>
            --}}
            <a href="https://play.google.com/store/apps/details?id=com.etoengine.driver" target="_blank" title="{{ trans('driver/pages.mobile_app.download_google') }}" class="app-download-google-play">
                <img src="{{ asset_url('images','icons/google-play.svg') }}" alt="{{ trans('driver/pages.mobile_app.download_google') }}">
            </a>
            <a href="https://itunes.apple.com/us/app/eto-driver/id1297746688?ls=1&mt=8" target="_blank" title="{{ trans('driver/pages.mobile_app.download_apple') }}" class="app-download-apple-store">
                <img src="{{ asset_url('images','icons/app-store.svg') }}" alt="{{ trans('driver/pages.mobile_app.download_apple') }}">
            </a>
        </div>
    </div>
@stop
