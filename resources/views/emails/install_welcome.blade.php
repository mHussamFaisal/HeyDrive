@extends('emails.template')

@section('title', trans('emails.install_welcome.subject', ['name' => $appName ]) )

@section('content')

    {{ trans('emails.install_welcome.greeting'
    ) }},<br />

    @if( !empty($additionalMessage) )
        {!! $additionalMessage !!}
    @endif

    {{ trans('emails.install_welcome.license_key', ['key'=>$licenseKey]) }}<br><br>

    {{ trans('emails.install_welcome.login_data') }}<br><br>

    {{ trans('emails.install_welcome.login_email', ['email'=>$email]) }}<br>

    {{ trans('emails.install_welcome.login_password', ['password'=>$password]) }}<br>

@stop
