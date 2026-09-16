@extends('admin.index')

@section('title', trans('debug.page_title'))
@section('subtitle', /*'<i class="fa fa-bug"></i> '.*/ trans('debug.page_title') )

@section('subcontent')
<div class="box no-border" style="box-shadow:none;">
    <div class="box-header" style="padding: 0 0 10px 0;">
        <h3 class="box-title">{{ trans('debug.page_title') }}</h3>
    </div>

    @include('partials.alerts.success')
    @include('partials.alerts.errors')

    <div class="box-body1 no-border clearfix">
        @php
        function debugSetLine($name = '', $value = '') {
            if ($name == 'PHP version') {
                $phpVersion = $value;
                if (version_compare($phpVersion, '7.1', '<') || version_compare($phpVersion, '7.4', '>=')) {
                    $value = '<span style="color:red;">'. $phpVersion .'</span><span style="color:#b2b2b2; margin-left:5px;">(Required 7.1, 7.2 or 7.3)</span>';
                } else {
                    $value = '<span style="color:green;">'. $phpVersion .'</span>';
                }
            }
            echo '<tr class="eto-debug-line">
              <td class="eto-debug-name" style="min-width:130px;">'. $name .':</td>
              <td class="eto-debug-value">'. $value .'</td>
            </tr>';
        }
        function debugIsEnabled($status) {
            return $status ? '<span style="color:green;">ON</span>' : '<span style="color:red;">OFF</span>';
        }
        @endphp
        <table class="eto-debug-table">
            <tbody>
            {!! debugSetLine('PHP version', phpversion()) !!}
            {!! debugSetLine('fileinfo', debugIsEnabled(extension_loaded('fileinfo'))) !!}
            {!! debugSetLine('Openssl', debugIsEnabled(extension_loaded('openssl'))) !!}
            {!! debugSetLine('Pdo', debugIsEnabled(extension_loaded('pdo'))) !!}
            {!! debugSetLine('Mbstring', debugIsEnabled(extension_loaded('mbstring'))) !!}
            {!! debugSetLine('Tokenizer', debugIsEnabled(extension_loaded('tokenizer'))) !!}
            {!! debugSetLine('JSON', debugIsEnabled(extension_loaded('JSON'))) !!}
            {!! debugSetLine('cURL', debugIsEnabled(extension_loaded('cURL'))) !!}
            {!! debugSetLine('Zip', debugIsEnabled(extension_loaded('Zip'))) !!}
            {!! debugSetLine('ZipArchive', debugIsEnabled(class_exists('ZipArchive'))) !!}
            {!! debugSetLine('allow_url_fopen', debugIsEnabled(ini_get('allow_url_fopen'))) !!}
            {!! debugSetLine('file_get_contents', debugIsEnabled(function_exists('file_get_contents'))) !!}
            {!! debugSetLine('curl', debugIsEnabled(function_exists('curl_version'))) !!}
            {!! debugSetLine('mail', debugIsEnabled(function_exists('mail'))) !!}
            {!! debugSetLine('gethostbyname', debugIsEnabled(function_exists('gethostbyname'))) !!}
            {!! debugSetLine('mysqli_connect', debugIsEnabled(function_exists('mysqli_connect'))) !!}
            {!! debugSetLine('escapeshellarg', debugIsEnabled(function_exists('escapeshellarg'))) !!}
            {!! debugSetLine('escapeshellcmd', debugIsEnabled(function_exists('escapeshellcmd'))) !!}
            {!! debugSetLine('proc_open', debugIsEnabled(function_exists('proc_open'))) !!}
            {!! debugSetLine('proc_close', debugIsEnabled(function_exists('proc_close'))) !!}
            {!! debugSetLine('getservbyport', debugIsEnabled(function_exists('getservbyport'))) !!}
            {!! debugSetLine('Server IP', !empty($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '') !!}
            {!! debugSetLine('My IP', $_SERVER['REMOTE_ADDR']) !!}
            {!! request('advance') == '1' ? debugSetLine('disable_functions', '<span style="color:gray;">'. str_replace(',', ', ', ini_get('disable_functions')) .'</span>') : '' !!}
            </tbody>
        </table>
        <div class="eto-debug-buttons" style="float: left;">
            <div class="clearfix">
                <a href="{{ route('debug.index', ['action' => 'clear_cache']) }}" class="btn btn-default" style="float:left;">
                    <span>{{ trans('debug.button.clear_cache') }}</span>
                </a>
                <a style="float:left; margin: 5px 10px;" href="#" class="help-button" data-toggle="popover" data-title="" data-content="Clear app cache option will clear server side temporary files that are created in order to speed up the software. You can use this option in case you would like to free up some storage space on your hard drive.">
                    <i class="ion-ios-information-outline"></i>
                </a>
            </div>

            @if (request('advance') == '1')
                <div class="clearfix">
                    <a href="{{ route('debug.index', ['action' => 'clear_session']) }}" class="btn btn-default">
                        <span>{{ trans('debug.button.clear_session') }}</span>
                    </a>
                </div>
                <div class="clearfix">
                    <a href="{{ route('debug.index', ['action' => 'clear_view']) }}" class="btn btn-default">
                        <span>{{ trans('debug.button.clear_view') }}</span>
                    </a>
                </div>
                <div class="clearfix">
                    <a href="{{ route('debug.index', ['action' => 'clear_config']) }}" class="btn btn-default">
                        <span>{{ trans('debug.button.clear_config') }}</span>
                    </a>
                </div>
                <div class="clearfix">
                    <a href="{{ route('debug.index', ['action' => 'clear_tmp']) }}" class="btn btn-default">
                        <span>{{ trans('debug.button.clear_tmp') }}</span>
                    </a>
                </div>
                <div class="clearfix">
                    <a href="{{ route('debug.index', ['action' => 'reset_permissions']) }}" class="btn btn-default">
                        <span>{{ trans('debug.button.reset_permissions') }}</span>
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
$(document).ready(function(){
    $('[data-toggle="popover"]').popover({
        placement: 'auto right',
        container: 'body',
        trigger: 'hover',
        html: true
    });
});
</script>
@stop
