@extends('admin.index')

@section('title', trans('admin/pages.mobile_app.page_title'))
@section('subtitle', /*'<i class="fa fa-download"></i> '.*/ trans('admin/pages.mobile_app.page_title'))

@section('subcontent')
<div id="mobile_app">

    <div class="row">
      <div class="col-md-6">

          <div class="section-main">
              <h3 class="section-header">{{ trans('admin/pages.mobile_app.customer.heading') }}</h3>

              @if (!config('site.allow_customer_app'))

                  @php
                  $section = 'benefits_user';
                  $steps = '';
                  foreach (explode("\r\n", trans('admin/pages.mobile_app.customer.'. $section .'.desc', [
                      'host_name' => config('site.company_name'),
                      'host_url' => url('/') .'/',
                      'admin_url' => url('/') .'/admin',
                      'driver_url' => url('/') .'/admin/users/create?role=driver',
                  ])) as $step) {
                      $steps .= '<li>'. $step .'</li>';
                  }
                  @endphp
                  <div class="section-box section-{{ $section }}">
                      <h4>{{ trans('admin/pages.mobile_app.customer.'. $section .'.heading') }}</h4>
                      @if ($steps)
                          <ol>{!! $steps !!}</ol>
                      @endif
                  </div>

                  @php
                  $section = 'benefits_company';
                  $steps = '';
                  foreach (explode("\r\n", trans('admin/pages.mobile_app.customer.'. $section .'.desc', [
                      'host_name' => config('site.company_name'),
                      'host_url' => url('/') .'/',
                      'admin_url' => url('/') .'/admin',
                      'driver_url' => url('/') .'/admin/users/create?role=driver',
                  ])) as $step) {
                      $steps .= '<li>'. $step .'</li>';
                  }
                  @endphp
                  <div class="section-box section-{{ $section }}">
                      <h4>{{ trans('admin/pages.mobile_app.customer.'. $section .'.heading') }}</h4>
                      @if ($steps)
                          <ol>{!! $steps !!}</ol>
                      @endif
                  </div>

                @endif

                @if (config('site.allow_customer_app'))

                  @php
                  $section = 'usage';
                  $steps = '';
                  foreach (explode("\r\n", trans('admin/pages.mobile_app.customer.'. $section .'.desc', [
                      'host_name' => config('site.company_name'),
                      'host_url' => url('/') .'/',
                      'admin_url' => url('/') .'/admin',
                      'driver_url' => url('/') .'/admin/users/create?role=driver',
                  ])) as $step) {
                      $steps .= '<li>'. $step .'</li>';
                  }
                  @endphp
                  <div class="section-box section-{{ $section }}">
                      <h4>{{ trans('admin/pages.mobile_app.customer.'. $section .'.heading') }}</h4>
                      @if (\Lang::has('admin/pages.mobile_app.customer.'. $section .'.subheading'))
                          <p>{{ trans('admin/pages.mobile_app.customer.'. $section .'.subheading') }}</p>
                      @endif
                      @if ($steps)
                          <ol>{!! $steps !!}</ol>
                      @endif
                  </div>

              @endif

              <div class="app-download-box">
                  <a href="https://play.google.com/store/apps/details?id=com.etoengine.customer" target="_blank" title="{{ trans('admin/pages.mobile_app.download_google') }}" class="app-download-google-play">
                      <img src="{{ asset_url('images','icons/google-play.svg') }}" alt="{{ trans('admin/pages.mobile_app.download_google') }}">
                  </a>
                  <a href="https://itunes.apple.com/us/app/eto-passenger/id1376499600?mt=8" target="_blank" title="{{ trans('admin/pages.mobile_app.download_apple') }}" class="app-download-apple-store">
                      <img src="{{ asset_url('images','icons/app-store.svg') }}" alt="{{ trans('admin/pages.mobile_app.download_apple') }}">
                  </a>
              </div>

              <div class="section-box section-note">
                  {!! trans('admin/pages.mobile_app.customer.note', [
                      'link' => '<a href="https://easytaxioffice.co.uk/pricing" target="_blank">'. trans('admin/pages.mobile_app.customer.note_link') .'</a>',
                  ]) !!}
              </div>

          </div>

      </div>
      <div class="col-md-6">

          <div class="section-main">
              <h3 class="section-header">
                  {{ trans('admin/pages.mobile_app.driver.heading') }}
              </h3>

              @php
              if (!empty(config('site.expiry_driver_app'))) {
                  if (\Carbon\Carbon::parse(config('site.expiry_driver_app'))->lte(\Carbon\Carbon::now())) {
                      $text = 'Your trial has expired. If you would like to keep using mobile app, please purchase the license at <a href="https://easytaxioffice.co.uk/pricing" target="_blank">https://easytaxioffice.co.uk/pricing</a>';
                  }
                  else {
                      $text = 'TRIAL (Expires on '. \App\Helpers\SiteHelper::formatDateTime(\Carbon\Carbon::parse(config('site.expiry_driver_app'))->toDateTimeString(), 'date') .')';
                  }

                  echo '<div style="margin-bottom:30px; font-size:20px; color:red;">'. $text .'</div>';
              }
              @endphp

              @if (!config('site.allow_driver_app'))

                  @php
                  $section = 'benefits_user';
                  $steps = '';
                  foreach (explode("\r\n", trans('admin/pages.mobile_app.driver.'. $section .'.desc', [
                      'host_name' => config('site.company_name'),
                      'host_url' => url('/') .'/',
                      'admin_url' => url('/') .'/admin',
                      'driver_url' => url('/') .'/admin/users/create?role=driver',
                  ])) as $step) {
                      $steps .= '<li>'. $step .'</li>';
                  }
                  @endphp
                  <div class="section-box section-{{ $section }}">
                      <h4>{{ trans('admin/pages.mobile_app.driver.'. $section .'.heading') }}</h4>
                      @if ($steps)
                          <ol>{!! $steps !!}</ol>
                      @endif
                  </div>

                  @php
                  $section = 'benefits_company';
                  $steps = '';
                  foreach (explode("\r\n", trans('admin/pages.mobile_app.driver.'. $section .'.desc', [
                      'host_name' => config('site.company_name'),
                      'host_url' => url('/') .'/',
                      'admin_url' => url('/') .'/admin',
                      'driver_url' => url('/') .'/admin/users/create?role=driver',
                  ])) as $step) {
                      $steps .= '<li>'. $step .'</li>';
                  }
                  @endphp
                  <div class="section-box section-{{ $section }}">
                      <h4>{{ trans('admin/pages.mobile_app.driver.'. $section .'.heading') }}</h4>

                      @if ($steps)
                          <ol>{!! $steps !!}</ol>
                      @endif
                  </div>

              @endif

              @if (config('site.allow_driver_app'))

                  @php
                  $section = 'usage';
                  $steps = '';
                  foreach (explode("\r\n", trans('admin/pages.mobile_app.driver.'. $section .'.desc', [
                      'host_name' => config('site.company_name'),
                      'host_url' => url('/') .'/',
                      'admin_url' => url('/') .'/admin',
                      'driver_url' => url('/') .'/admin/users/create?role=driver',
                  ])) as $step) {
                      $steps .= '<li>'. $step .'</li>';
                  }
                  @endphp
                  <div class="section-box section-{{ $section }}">
                      <h4>{{ trans('admin/pages.mobile_app.driver.'. $section .'.heading') }}</h4>
                      @if (\Lang::has('admin/pages.mobile_app.driver.'. $section .'.subheading'))
                          <p>{{ trans('admin/pages.mobile_app.driver.'. $section .'.subheading') }}</p>
                      @endif
                      @if ($steps)
                          <ol>{!! $steps !!}</ol>
                      @endif
                  </div>

              @endif

              <div class="app-download-box">
                  <a href="https://play.google.com/store/apps/details?id=com.etoengine.driver" target="_blank" title="{{ trans('admin/pages.mobile_app.download_google') }}" class="app-download-google-play">
                      <img src="{{ asset_url('images','icons/google-play.svg') }}" alt="{{ trans('admin/pages.mobile_app.download_google') }}">
                  </a>
                  <a href="https://itunes.apple.com/us/app/eto-driver/id1297746688?ls=1&mt=8" target="_blank" title="{{ trans('admin/pages.mobile_app.download_apple') }}" class="app-download-apple-store">
                      <img src="{{ asset_url('images','icons/app-store.svg') }}" alt="{{ trans('admin/pages.mobile_app.download_apple') }}">
                  </a>
              </div>

              <div class="section-box section-note">
                  {!! trans('admin/pages.mobile_app.driver.note', [
                      'link' => '<a href="https://easytaxioffice.co.uk/pricing" target="_blank">'. trans('admin/pages.mobile_app.driver.note_link') .'</a>',
                  ]) !!}
              </div>

          </div>

      </div>
    </div>

</div>
@stop
