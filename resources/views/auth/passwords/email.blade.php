@extends('layouts.app')

@section('title', trans('auth.page_title_password_email'))

@section('bodyClass', 'hold-transition login-page')

@section('content')
<div class="login-box">
    <div class="login-logo">
        @if ( config('site.logo') )
            <img src="{{ asset_url('uploads','logo/'. config('site.logo')) }}" alt="{{ config('app.name') }}">
        @else
            {{ config('app.name') }}
        @endif
    </div>
    <div class="login-box-body">

        @if ( trans('auth.intro_password_email') )
            <p class="login-box-msg">{{ trans('auth.intro_password_email') }}</p>
        @endif

        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

        <form role="form" method="POST" action="{{ url('/password/email') }}" class="form-master">
            {{ csrf_field() }}

            <div class="form-group form-group-lg has-feedback{{ $errors->has('email') ? ' has-error' : '' }}">
                <label for="email">{{ trans('auth.email') }}</label>
                <div>
                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" placeholder="{{ trans('auth.email') }}" required autofocus>
                    <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
                </div>
                @if ($errors->has('email'))
                    <span class="help-block">
                        {{ $errors->first('email') }}
                    </span>
                @endif
            </div>

            <div class="row">
                <div class="col-xs-6">
                    <a href="{{ route('login') }}" class="btn btn-link btn-flat">{{ trans('auth.login') }}</a>
                </div>
                <div class="col-xs-6">
                    <button type="submit" class="btn btn-primary btn-block btn-flat">{{ trans('auth.send') }}</button>
                </div>
            </div>
        </form>

    </div>
</div>
@endsection

@section('footer')
    <script type="text/javascript">
    $(document).ready(function() {
        // Placeholder
        function updateFormPlaceholder(that) {
            var $container = $(that).closest('.form-group:not(.placeholder-disabled)');

            if( $(that).val() != '' || $container.hasClass('placeholder-visible') ) {
                $container.find('label').show();
            }
            else {
                $container.find('label').hide();
            }
        }

        $('.form-master').find('input:not([type="submit"]), textarea, select').each(function() {
            updateFormPlaceholder(this);
        })
        .bind('change keyup', function(e) {
            updateFormPlaceholder(this);
        });
    });
    </script>
@stop
