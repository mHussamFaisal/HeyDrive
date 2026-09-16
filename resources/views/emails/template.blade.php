<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <title>@yield('title')</title>

    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="format-detection" content="telephone=no">

    <style type="text/css">
    body {
        margin: 0;
        padding: 0;
        -ms-text-size-adjust: 100%;
        -webkit-text-size-adjust: 100%;
        font-family: Helvetica, Arial, sans-serif;
        font-size: 12px;
        line-height: 20px;
    }
    table {
        mso-table-lspace: 0pt;
        mso-table-rspace: 0pt;
        border-spacing: 0;
    }
    table td {
        border-collapse: collapse;
    }
    img {
        -ms-interpolation-mode: bicubic;
    }
    a {
        color: #333333;
        text-decoration: none;
    }
    a:hover {
        color: #1C70B1;
    }
    @media screen and (max-width: 599px) {
        .force-row,
        .container {
            width: 100% !important;
            max-width: 100% !important;
        }
        .telephoneNumber {
            text-align: left !important;
        }
    }
    @media screen and (max-width: 400px) {
        .container-padding {
            padding-left: 12px !important;
            padding-right: 12px !important;
        }
    }
    </style>
</head>
<body style="margin:0; padding:0;" bgcolor="#F0F0F0" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">

    <table border="0" width="100%" height="100%" cellpadding="0" cellspacing="0" bgcolor="#F0F0F0">
        <tr>
            <td align="center" valign="top" bgcolor="#F0F0F0" style="background-color: #F0F0F0;">
                <br>

                <table border="0" width="800" cellpadding="0" cellspacing="0" class="container" bgcolor="#FFFFFF" style="background-color:#FFFFFF;border:1px #E8E8E8 solid;border-top:5px #DCDCDC solid;border-radius:5px;width:800px;max-width:800px">
                    <tr>
                        <td class="container-padding header" align="left" style="padding-top:24px;padding-bottom:0px;padding-left:24px;padding-right:20px">

                            <!--[if mso]>
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td width="50%" valign="top">
                            <![endif]-->

                            <table width="364" border="0" cellpadding="0" cellspacing="0" align="left" class="force-row">
                                <tr>
                                    <td class="col" valign="top" style="padding-bottom:24px;font-family:Helvetica, Arial, sans-serif;font-size:24px;line-height:24px;text-align:left;color:#333333;width:100%">

                                        @if( !empty($company) )
                                        @if( !empty($company->url_home) )
                                            <a href="{{ $company->url_home }}" target="_blank" style="display:inline-block;text-decoration:none;color:#333333;">
                                        @endif

                                            @if( config('site.logo') )
                                                <img src="{{ asset_url('uploads','logo/'. config('site.logo')) }}" alt="{{ $company->name }}" style="max-width:300px;" />
                                            @else
                                                {{ $company->name }}
                                            @endif

                                        @if( !empty($company->url_home) )
                                            </a>
                                        @endif
                                        @endif

                                    </td>
                                </tr>
                            </table>

                            <!--[if mso]>
                                </td>
                                <td width="50%" valign="top">
                            <![endif]-->

                            <table width="364" border="0" cellpadding="0" cellspacing="0" align="right" class="force-row">
                                <tr>
                                    <td class="col telephoneNumber" valign="top" style="padding-bottom:24px;font-family:Helvetica, Arial, sans-serif;font-size:16px;line-height:20px;text-align:right;color:#333333;width:100%">

                                        @if( !empty($company) && $company->phone )
                                            {{ trans('emails.header.phone') }}: <a href="tel:{{ $company->phone }}" style="text-decoration:none;color:#333333;">{{ $company->phone }}</a><br />
                                        @endif

                                    </td>
                                </tr>
                            </table>

                            <!--[if mso]>
                                    </td>
                                </tr>
                            </table>
                            <![endif]-->

                            <div class="hr" style="clear:both;height:1px;line-height:1px;font-size:1px;border-bottom:1px solid #E8E8E8">&nbsp;</div>

                        </td>
                    </tr>
                    <tr>
                        <td class="container-padding content" align="left" style="padding-left:24px;padding-right:24px;padding-top:24px;padding-bottom:24px;background-color:#ffffff;font-family:Helvetica, Arial, sans-serif;font-size:12px;line-height:20px;">

                            @yield('content')

                        </td>
                    </tr>
                    <tr>
                        <td class="container-padding footer-text" align="left" style="font-family:Helvetica, Arial, sans-serif;font-size:12px;line-height:20px;color:#888888;padding-top:0px;padding-bottom:24px;padding-left:24px;padding-right:24px">

                            <div class="hr" style="clear:both;height:1px;line-height:1px;font-size:1px;border-bottom:1px solid #E8E8E8;">&nbsp;</div>
                            <br>

                            @if( !empty($company->name) )
                                {{ $company->name }}<br />
                            @endif

                            @if( !empty($company->phone) )
                                {{ trans('emails.footer.phone') }}: <a href="tel:{{ $company->phone }}" style="text-decoration:none; color:#888888;">{{ $company->phone }}</a><br />
                            @endif

                            @if( !empty($company->email) )
                                {{ trans('emails.footer.email') }}: <a href="mailto:{{ $company->email }}" target="_blank" style="text-decoration:none; color:#888888;">{{ $company->email }}</a><br />
                            @endif

                            @if( !empty($company->url_home) )
                                {{ trans('emails.footer.site') }}: <a href="{{ $company->url_home }}" target="_blank" style="text-decoration:none; color:#888888;">{{ $company->url_home }}</a><br />
                            @endif

                            {{--
                            @if( !empty($company->url_feedback) )
                                <br />
                                {!! trans('emails.footer.feedback', [
                                    'link' => '<a href="'. $company->url_feedback .'" target="_blank" style="color:#888888;">'. trans('emails.footer.feedback_link') .'</a>'
                                ]) !!}<br />
                            @endif
                            --}}

                        </td>
                    </tr>
                </table>

                @if( config('site.branding') )
                    <div style="margin:20px 0px 20px 0px; text-align:center; font-family:Helvetica, Arial, sans-serif; font-size:10px; color:#888888;">
                        {{ trans('emails.powered_by') }} <a href="{{ config('app.url') }}" target="_blank" style="color:#808080;">EasyTaxiOffice</a>
                    </div>
                @endif

                <br>
            </td>
        </tr>
    </table>

</body>
</html>
