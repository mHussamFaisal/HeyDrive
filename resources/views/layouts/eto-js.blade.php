{{--<script src="{{ asset_url('plugins','jquery-touchy/jquery.touch.js') }}?_dc={{ config('app.timestamp') }}"></script>--}}
<script src="{{ asset_url('plugins','jquery-webui-popover/jquery.webui-popover.js') }}?_dc={{ config('app.timestamp') }}"></script>
<script src="{{ asset_url('plugins','sweetalert2/sweetalert2.js') }}"></script>
<script src="{{ asset_url('js','eto/eto.js') }}?_dc={{ config('app.timestamp') }}"></script>
<script>
$(function() {
    ETO.setConfig({!! json_encode(\App\Helpers\SettingsHelper::getJsConfig()) !!});

    if ($.inArray(ETO.getBrowserName(), ['Edge', 'IE']) != -1) {
        $('body').append('<style>'+
            'aside.main-sidebar section.sidebar {overflow-y:auto !important;}'+
            'aside.main-sidebar div.slimScrollBar {display:none !important;}'+
        '</style>');
    }
});
</script>
