@extends('admin.index')

@section('title', trans('admin/pages.web_widget.page_title'))
@section('subtitle', /*'<i class="fa fa-plug"></i> '.*/ trans('admin/pages.web_widget.page_title'))

@section('subcontent')
<div id="web_widget">

    <h3 style="margin-top:0px; margin-bottom:15px;">Web Widgets</h3>
    This is a step by step guide explaining how to integrate Web Booking and Customer Account widgets with a website.<br><br>

    Full integration is a combination of three modules:<br><br>

    <span style="font-weight:bold;">Mini Web Booking widget</span> - is designed to engage customers by quick and simple check of journey price. Choose From, To, Date and Time and price is display. A full booking will be continued through Full Web Booking widget.<br>
    <span style="font-weight:bold;">Full Web Booking widget</span> - allows to make a complete booking and take payments.<br>
    <span style="font-weight:bold;">Customer account widget</span> - allows customer to open an account, makes new booking, manage existing bookings and user profile.<br><br><br>

    You need to create the following pages in your website and place the correct widget in it.<br><br>

    <span style="font-weight:bold;">Home</span> - mini web booking widget.<br>
    <span style="font-weight:bold;">Booking</span> - full web booking widget.<br>
    <span style="font-weight:bold;">My account</span> - customer account widget.<br><br><br>

    Once you have created all pages then you can use one of the integration methods below.<br><br>

    @php
    $type = !empty(request('type')) ? request('type') : '';
    $isAdvance = !empty(request('advance')) ? 1 : 0;
    $siteKey = session('admin_site_key') ? session('admin_site_key') : config('site.site_key');
    $advanceWpParams = '';
    $advanceUrlParams = '';

    if ($isAdvance) {
        $advanceWpParams = ' url="'. url('/') .'/"';
        $advanceWpParams .= ' site_key="'. $siteKey .'"';
    }

    $advanceUrlParams .= '?site_key='. $siteKey;
    @endphp

    <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">

      <div class="panel panel-default @if($type == 'wp') active @endif">
        <div class="panel-heading" role="tab" id="menu-wordpress">
          <h4 class="panel-title clearfix">
            <a role="button" data-toggle="collapse" data-parent="#accordion" href="#menu-wordpress-box" aria-expanded="false" aria-controls="menu-wordpress-box" class="collapsed">
              WordPress plugin
            </a>
            <a href="{{ route('admin.web-widget', ['type' => 'wp']) }}" class="web_widget_link" title="Direct link"><i class="fa fa-link"></i></a>
          </h4>
        </div>
        <div id="menu-wordpress-box" class="panel-collapse collapse @if($type == 'wp') in @endif" role="tabpanel" aria-labelledby="menu-wordpress" aria-expanded="false">
          <div class="panel-body">

            Please <a href="https://download.easytaxioffice.com/wp_easytaxioffice.zip?_dc={{ config('app.timestamp') }}" target="_blank">download</a> our free plugin, install and activate it in your website.<br>
            Next go to your WordPress admin panel -> Settings -> EasyTaxiOffice tab and enter the following settings in the form and save changes.<br><br>
            <div style="margin-bottom:10px;">Software URL: <code>{{ url('/') }}/</code></div>
            <div style="margin-bottom:10px;">Site Key: <code>{{ $siteKey }}</code></div>
            <br>
            The last thing you need to do is to add the widget shortcodes to your pages as shown below.<br><br>

            Home:<br>
            <code>[easytaxioffice type="booking-widget"{{ $advanceWpParams }}]</code><br><br>

            Booking:<br>
            <code>[easytaxioffice type="booking"{{ $advanceWpParams }}]</code><br><br>

            My account:<br>
            <code>[easytaxioffice type="customer"{{ $advanceWpParams }}]</code>

          </div>
        </div>
      </div>

      <div class="panel panel-default @if($type == 'html') active @endif">
        <div class="panel-heading" role="tab" id="menu-iframe">
          <h4 class="panel-title clearfix">
            <a role="button" data-toggle="collapse" data-parent="#accordion" href="#menu-iframe-box" aria-expanded="false" aria-controls="menu-iframe-box" class="collapsed">
              HTML
            </a>
            <a href="{{ route('admin.web-widget', ['type' => 'html']) }}" class="web_widget_link" title="Direct link"><i class="fa fa-link"></i></a>
          </h4>
        </div>
        <div id="menu-iframe-box" class="panel-collapse collapse @if($type == 'html') in @endif" role="tabpanel" aria-labelledby="menu-iframe" aria-expanded="false">
          <div class="panel-body">

            Add the widget code to your pages as shown below.<br><br>

            Home:<br>
            <code style="display:block;">
            &lt;iframe src="{{ url('/') }}/booking/widget{{ $advanceUrlParams }}" id="eto-iframe-booking-widget" allow="geolocation" width="100%" height="250" scrolling="no" frameborder="0" style="width:1px; min-width:100%; border:0;"&gt;&lt;/iframe&gt;<br>
            &lt;script src="{{ asset_url('plugins','iframe-resizer/iframeResizer.min.js') }}"&gt;&lt;/script&gt;<br>
            &lt;script&gt;iFrameResize({log:false, targetOrigin:'*', checkOrigin:false}, "iframe#eto-iframe-booking-widget");&lt;/script&gt;
            </code><br>

            Booking:<br>
            <code style="display:block;">
            &lt;iframe src="{{ url('/') }}/booking{{ $advanceUrlParams }}" id="eto-iframe-booking" allow="geolocation" width="100%" height="250" scrolling="no" frameborder="0" style="width:1px; min-width:100%; border:0;"&gt;&lt;/iframe&gt;<br>
            &lt;script src="{{ asset_url('plugins','iframe-resizer/iframeResizer.min.js') }}"&gt;&lt;/script&gt;<br>
            &lt;script&gt;iFrameResize({log:false, targetOrigin:'*', checkOrigin:false}, "iframe#eto-iframe-booking");&lt;/script&gt;
            </code><br>

            My account:<br>
            <code style="display:block;">
            &lt;iframe src="{{ url('/') }}/customer{{ $advanceUrlParams }}" id="eto-iframe-customer" allow="geolocation" width="100%" height="250" scrolling="no" frameborder="0" style="width:1px; min-width:100%; border:0;"&gt;&lt;/iframe&gt;<br>
            &lt;script src="{{ asset_url('plugins','iframe-resizer/iframeResizer.min.js') }}"&gt;&lt;/script&gt;<br>
            &lt;script&gt;iFrameResize({log:false, targetOrigin:'*', checkOrigin:false}, "iframe#eto-iframe-customer");&lt;/script&gt;
            </code>

          </div>
        </div>
      </div>

    </div>

    The last thing you need to do is to update page URLs in booking software admin panel.<br><br>

    1. To do so, go to <span style="font-weight:bold;">Admin -> Settings -> General -> URLs</span> section or click <a href="{{  route('admin.config.index') }}#general-urls">here</a>.<br>
    2. Update your website pages URLs, the ones you have created ealier on.<br>
    3. The last thing you need to do is to tick this option "Force widgets to be displayed in iframe". This option will force the widgets to display only in your website instead of new page.<br>
    4. That’s all. Enjoy!<br><br>

</div>
@stop
