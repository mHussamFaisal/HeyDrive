
<input type="hidden" name="settings_group" id="settings_group" value="debug">

<div class="form-group field-debug field-size-fw">
    <label for="debug" class="checkbox-inline">
        <input type="checkbox" name="debug" id="debug" value="1">Enable debug mode (keep this option off when in LIVE mode)
    </label>
</div>

<div class="form-group field-google_cache_expiry_time">
    <label for="google_cache_expiry_time">Google cache (minutes)</label>
    <input type="number" name="google_cache_expiry_time" id="google_cache_expiry_time" placeholder="0" value="0" required class="form-control" min="0" step="1">
</div>

@php
function debugGetPhpinfo() {
    ob_start();
    phpinfo();
    $phpinfo = ob_get_contents();
    ob_end_clean();

    $phpinfo = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $phpinfo);
    $phpinfo = "<style type='text/css'>
    #phpinfo {}
    #phpinfo pre {margin: 0; font-family: monospace;}
    #phpinfo a:link {color: #009; text-decoration: none; background-color: #fff;}
    #phpinfo a:hover {text-decoration: underline;}
    #phpinfo table {border-collapse: collapse; border: 0; width: 934px; box-shadow: 1px 2px 3px #ccc;}
    #phpinfo .center {text-align: center;}
    #phpinfo .center table {margin: 1em auto; text-align: left;}
    #phpinfo .center th {text-align: center !important;}
    #phpinfo td, th {border: 1px solid #666; font-size: 75%; vertical-align: baseline; padding: 4px 5px;}
    #phpinfo h1 {font-size: 150%;}
    #phpinfo h2 {font-size: 125%;}
    #phpinfo .p {text-align: left;}
    #phpinfo .e {background-color: #ccf; width: 300px; font-weight: bold;}
    #phpinfo .h {background-color: #99c; font-weight: bold;}
    #phpinfo .v {background-color: #ddd; max-width: 300px; overflow-x: auto; word-wrap: break-word;}
    #phpinfo .v i {color: #999;}
    #phpinfo img {float: right; border: 0;}
    #phpinfo hr {width: 934px; background-color: #ccc; border: 0; height: 1px;}
    </style>
    <div id='phpinfo'>
    $phpinfo
    </div>";
    return $phpinfo;
}

echo '<br>';

if(request('phpinfo')) {
    echo '<a href="'. url()->current() .'">Hide PHP info</a>';
    echo '<div style="width:100%; height:500px; border:1px #f2f2f2 solid; overflow-y: auto;">';
    echo debugGetPhpinfo();
    echo '</div>';
}
else {
    echo '<a href="'. url()->current() .'?phpinfo=1">Show PHP info</a>';
}
@endphp
