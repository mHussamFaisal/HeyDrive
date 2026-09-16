@if( config('site.branding') )
    <div style="margin:20px 0px 0px 0px; text-align:center; font-size:10px; color:#afafaf;" class="footer-branding">
        @if (session('isMobileApp'))
            {{ trans('common.powered_by') }} EasyTaxiOffice
        @else
            {{ trans('common.powered_by') }} <a href="https://easytaxioffice.com" target="_blank" style="color:#afafaf;">EasyTaxiOffice</a>
        @endif
    </div>
@endif
