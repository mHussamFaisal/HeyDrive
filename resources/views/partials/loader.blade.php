<div id="loader">
    <div class="loader-container">
        <i class="ion-ios-loop fa fa-spin loader-progress"></i>
        <div class="loader-text">
            {{ trans('common.loading') }}
        </div>
    </div>
</div>

{{-- <script>
$(document).ready(function(){
    setTimeout(function(){
        if ($('#loader').is(':visible')) {
            $('.loader-container .loader-text').append('<div style="margin:20px auto; width:600px; max-width:100%;">{{ trans('common.loading_warning') }}<br><a href="#" onclick="window.location.reload(); return false;" class="btn btn-primary btn-xs" style="margin-top:10px;">{{ trans('common.loading_reload') }}</a></div>');
        }
    }, 60 * 1000);
});
</script> --}}
