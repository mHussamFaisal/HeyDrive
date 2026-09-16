@extends('layouts.app')

@section('title', trans('admin/settings.notifications.subtitle'))

@section('header')
<link rel="stylesheet" href="{{ asset_url('css','admin.css') }}?_dc={{ config('app.timestamp') }}">
@endsection


@section('content')
<div id="notifications_preview">
    @if($channel == 'email')

        <div class="mockup-tablet">
            <div class="mockup-tablet-inner">
                <div class="mockup-tablet-header">
                    <div class="mockup-tablet-header-circle"></div>
                </div>
                <div class="mockup-tablet-body">
                    @if(!empty($from) || !empty($to) || !empty($subject))
                        <div class="mockup-tablet-body-switch">
                            <i class="fa fa-angle-double-down" data-toggle="tooltip" data-title="Show meta"></i>
                        </div>
                        <div class="mockup-tablet-body-meta swing-in-top-fwd">
                            @if(!empty($from))
                                <div class="mockup-tablet-body-meta-from">
                                    <span class="mockup-tablet-body-meta-title">From:</span> <span>{!! $from !!}</span>
                                </div>
                            @endif
                            @if(!empty($to))
                                <div class="mockup-tablet-body-meta-to">
                                    <span class="mockup-tablet-body-meta-title">To:</span> <span>{!! $to !!}</span>
                                </div>
                            @endif
                            @if(!empty($subject))
                                <div class="mockup-tablet-body-meta-subject">
                                    <span class="mockup-tablet-body-meta-title">Subject:</span> <span>{!! $subject !!}</span>
                                </div>
                            @endif
                        </div>
                    @endif
                    <div class="mockup-tablet-msg scale-in-center">
                        <iframe src="about:blank"></iframe>
                    </div>
                </div>
                <div class="mockup-tablet-footer">
                    <div class="mockup-tablet-footer-circle"></div>
                </div>
            </div>
        </div>

        <script>
        $(document).ready(function() {
            var html = "{{ trim(preg_replace('/\s+/', ' ', $body)) }}";
            var iframe = $('.mockup-tablet-msg iframe').contents().find('body').html($('<div />').html(html).text());

            $('.mockup-tablet-body-switch').toggle(
              function(){
                  $('[data-toggle="tooltip"]').tooltip('hide');
                  $('.mockup-tablet-body-meta').show();

                  setTimeout(function() {
                      $('.mockup-tablet-body-meta').addClass('swing-in-top-fwd');
                  }, 100);

                  $('.mockup-tablet-body-switch').html('<i class="fa fa-angle-double-up" data-toggle="tooltip" data-title="Hide meta"></i>');
              },
              function(){
                  $('[data-toggle="tooltip"]').tooltip('hide');
                  $('.mockup-tablet-body-meta').hide();
                  // $('.mockup-tablet-body-meta').removeClass('swing-in-top-fwd');
                  $('.mockup-tablet-body-switch').html('<i class="fa fa-angle-double-down" data-toggle="tooltip" data-title="Show meta"></i>');
              }
            );

            $('.mockup-tablet-footer-circle').click(function(){
                $('.mockup-tablet-msg').removeClass('scale-in-center');
                setTimeout(function() {
                    $('.mockup-tablet-msg').addClass('scale-in-center');
                }, 100);
            });
        });
        </script>

    @elseif($channel == 'sms' || $channel == 'push')

        <div class="mockup-phone">
            <div class="mockup-phone-inner">
              <div class="mockup-phone-header">
                  <div class="mockup-phone-header-line"></div>
                  <div class="mockup-phone-header-circle"></div>
              </div>
              <div class="mockup-phone-body">
                  @if($channel == 'push')
                      <div class="mockup-phone-msg mockup-phone-msg-push slide-in-top">
                          <div class="mockup-phone-msg-push-title">{!! $title !!}</div>
                          <div>{!! $body !!}</div>
                      </div>
                  @else
                      <div class="mockup-phone-msg clearfix scale-in-center">
                          <div>{!! $body !!}</div>
                      </div>
                      <div class="mockup-phone-msg-sms-time">{!! date('H:i') !!}</div>
                  @endif
              </div>
              <div class="mockup-phone-footer">
                  <div class="mockup-phone-footer-circle"></div>
              </div>
            </div>
        </div>

        <script>
        $(document).ready(function() {
            $('.mockup-phone-footer-circle').click(function(){
                @if($channel == 'push')
                    $('.mockup-phone-msg').removeClass('slide-in-top');
                    setTimeout(function() {
                        $('.mockup-phone-msg').addClass('slide-in-top');
                    }, 100);
                @else
                    $('.mockup-phone-msg').removeClass('scale-in-center');
                    setTimeout(function() {
                        $('.mockup-phone-msg').addClass('scale-in-center');
                    }, 100);
                @endif
            });
        });
        </script>

    @endif

    <style>
    body {
      background: #e4e4e4;
      background: linear-gradient(to bottom, #D5DEE7 0%, #E8EBF2 50%, #E2E7ED 100%),
        linear-gradient(to bottom, rgba(0, 0, 0, 0.02) 50%, rgba(255, 255, 255, 0.02) 61%, rgba(0, 0, 0, 0.02) 73%),
        linear-gradient(33deg, rgba(255, 255, 255, 0.20) 0%, rgba(0, 0, 0, 0.20) 100%);
      background-blend-mode: normal, color-burn;
      padding: 20px;
    }
    ::-webkit-scrollbar {
      width: 10px;
      height: 10px;
    }
    ::-webkit-scrollbar-track {
      background-color: rgba(0, 0, 0, 0.01);
    }
    ::-webkit-scrollbar-thumb {
      background-color: #dddddd;
    }
    @-webkit-keyframes slide-in-top {
      0% {
        -webkit-transform: translateY(-1000px);
        transform: translateY(-1000px);
        opacity: 0;
      }
      100% {
        -webkit-transform: translateY(0);
        transform: translateY(0);
        opacity: 1;
      }
    }
    @keyframes slide-in-top {
      0% {
        -webkit-transform: translateY(-1000px);
        transform: translateY(-1000px);
        opacity: 0;
      }
      100% {
        -webkit-transform: translateY(0);
        transform: translateY(0);
        opacity: 1;
      }
    }
    .slide-in-top {
      -webkit-animation: slide-in-top 0.5s cubic-bezier(0.250, 0.460, 0.450, 0.940) both;
      animation: slide-in-top 0.5s cubic-bezier(0.250, 0.460, 0.450, 0.940) both;
    }
    @-webkit-keyframes scale-in-center {
      0% {
        -webkit-transform: scale(0);
        transform: scale(0);
        opacity: 1;
      }
      100% {
        -webkit-transform: scale(1);
        transform: scale(1);
        opacity: 1;
      }
    }
    @keyframes scale-in-center {
      0% {
        -webkit-transform: scale(0);
        transform: scale(0);
        opacity: 1;
      }
      100% {
        -webkit-transform: scale(1);
        transform: scale(1);
        opacity: 1;
      }
    }
    .scale-in-center {
      -webkit-animation: scale-in-center 0.5s cubic-bezier(0.250, 0.460, 0.450, 0.940) both;
      animation: scale-in-center 0.5s cubic-bezier(0.250, 0.460, 0.450, 0.940) both;
    }
    @-webkit-keyframes swing-in-top-fwd {
      0% {
        -webkit-transform: rotateX(-100deg);
        transform: rotateX(-100deg);
        -webkit-transform-origin: top;
        transform-origin: top;
        opacity: 0;
      }
      100% {
        -webkit-transform: rotateX(0deg);
        transform: rotateX(0deg);
        -webkit-transform-origin: top;
        transform-origin: top;
        opacity: 1;
      }
    }
    @keyframes swing-in-top-fwd {
      0% {
        -webkit-transform: rotateX(-100deg);
        transform: rotateX(-100deg);
        -webkit-transform-origin: top;
        transform-origin: top;
        opacity: 0;
      }
      100% {
        -webkit-transform: rotateX(0deg);
        transform: rotateX(0deg);
        -webkit-transform-origin: top;
        transform-origin: top;
        opacity: 1;
      }
    }
    .swing-in-top-fwd {
      -webkit-animation: swing-in-top-fwd 0.5s cubic-bezier(0.175, 0.885, 0.320, 1.275) both;
      animation: swing-in-top-fwd 0.5s cubic-bezier(0.175, 0.885, 0.320, 1.275) both;
    }
    </style>

</div>
@endsection
