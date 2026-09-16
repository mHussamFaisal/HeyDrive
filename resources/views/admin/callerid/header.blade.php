<link rel="stylesheet" href="{{ asset_url('plugins','jquery-popup-window/popupwindow.css') }}">
<script src="{{ asset_url('plugins','jquery-popup-window/popupwindow.min.js') }}"></script>

<style>
.popupwindow_overlay {
  background-color: rgba(0, 0, 0, 0.6);
}
.popupwindow_container {
  z-index: 1000;
}
.popupwindow {
  background-color: #ffffff;
  border: 1px solid #d2d2d2;
  box-shadow: 0px 0px 5px 1px rgba(0, 0, 0, 0.18);
}
.popupwindow_titlebar {
  background-color: #efefef;
  padding: 10px;
}
.popupwindow_titlebar_button {
  background-color: #efefef;
  border: 1px solid #d2d2d2;
}
.popupwindow_content {
  border-top: 1px solid #e4e4e4;
  padding: 10px;
}
.popupwindow_statusbar {
  background-color: #fff;
  border: 0;
}
.popupwindow_statusbar_handle {
  stroke: #5a5a5a;
}
</style>

<script>
$(document).ready(function() {
  @if( config('site.callerid_type') == 'ringcentral' )
    var params = "?redirectUri=https://ringcentral.github.io/ringcentral-embeddable/redirect.html";

    @if( config('services.ringcentral.environment') == 'sandbox' )
      params += "&appServer=https://platform.devtest.ringcentral.com";
    @else
      params += "&appServer=https://platform.ringcentral.com";
    @endif

    @if( config('services.ringcentral.app_key') )
      params += "&appKey={{ config('services.ringcentral.app_key') }}";
    @endif

    @if( config('services.ringcentral.app_secret') )
      params += "&appSecret={{ config('services.ringcentral.app_secret') }}";
    @endif

    // params += "&disableMessages=1";

    // Init RingCentral widget
    var rcs = document.createElement("script");
    rcs.src = "https://ringcentral.github.io/ringcentral-embeddable/adapter.js"+ params;
    var rcs0 = document.getElementsByTagName("script")[0];
    rcs0.parentNode.insertBefore(rcs, rcs0);
    if (window.RCAdapter) {
      window.RCAdapter.setMinimized(false);
    }

    // Popup
    window.addEventListener('message', function(e) {
      const data = e.data;
      if (data) {
        switch (data.type) {
          case 'rc-active-call-notify':

            if (data.call.telephonyStatus == 'Ringing') {
              var openWidget = 0;
              var openPopup = 0;

              if (data.call.direction == 'Outbound') {
                var direction = 'outbound';
                var phoneNumber = data.call.to.phoneNumber;

                @if( in_array(config('services.ringcentral.widget_open'), ['all', 'outbound']) )
                  openWidget = 1;
                @endif

                @if( in_array(config('services.ringcentral.popup_open'), ['all', 'outbound']) )
                  openPopup = 1;
                @endif
              }
              else {
                var direction = 'inbound';
                var phoneNumber = data.call.from.phoneNumber;

                @if( in_array(config('services.ringcentral.widget_open'), ['all', 'inbound']) )
                  openWidget = 1;
                @endif

                @if( in_array(config('services.ringcentral.popup_open'), ['all', 'inbound']) )
                  openPopup = 1;
                @endif
              }

              if (window.RCAdapter && openWidget) {
                window.RCAdapter.setMinimized(false);
              }

              if (phoneNumber && openPopup) {
                calleridPopup(phoneNumber);
              }
            }

          break;
          default:
            // console.log(data);
          break;
        }
      }
    });

    var popups = $.cookie('eto_admin_callerid_popup') ? JSON.parse($.cookie('eto_admin_callerid_popup')) : [];
    if (popups.length > 0) {
      $.each(popups, function(k, v) {
        calleridPopup(v.phone);
      });
    }
  @endif

  // calleridPopup('+442012345678');
  // handlePhoneNumber();
});

function calleridPopup(phone) {
  var phone = phone ? phone : '';
  var popups = $.cookie('eto_admin_callerid_popup') ? JSON.parse($.cookie('eto_admin_callerid_popup')) : [];
  var popup = {id: uuid(), phone: phone, state: 'normal'};
  var exists = 0;

  $.each(popups, function(k, v) {
    if (v.phone == phone) {
      popup = v;
      exists = 1;
    }
  });

  if (!exists) {
    popups.push(popup);
  }

  $.cookie('eto_admin_callerid_popup', JSON.stringify(popups), {path: EasyTaxiOffice.cookiePath, secure: EasyTaxiOffice.cookieSecure, same_site: EasyTaxiOffice.cookieSameSite});

  var url = '{{ route('admin.callerid.index') }}?phoneNumber='+ encodeURIComponent(phone) +'&tmpl=body';
  var html = '<iframe src="'+ url +'" frameborder="0" height="500" width="100%" id="modal-callerid-iframe-'+ popup.id +'" scrolling="no"></iframe>';
  var callerid = $('#modal-callerid-'+ popup.id);

  if (!callerid.length) {
    $('body').append('<div id="modal-callerid-'+ popup.id +'" class="modal-callerid"></div>');

    var callerid = $('#modal-callerid-'+ popup.id);
    var calleridiframe = $('#modal-callerid-iframe-'+ popup.id);

    callerid.PopupWindow({
      title: '{{ trans('admin/callerid.page_title') }} '+ phone,
      modal: true,
      autoOpen: true,
      statusBar: false,
      animationTime: 200,
      height: 600,
      width: 800,
      dragOpacity: 1,
      resizeOpacity: 1,
      top: 'auto',
      left: 'auto',
      buttons: {
        collapse: false,
      },
    })
    .on('destroy.popupwindow', function() {
      var popups = $.cookie('eto_admin_callerid_popup') ? JSON.parse($.cookie('eto_admin_callerid_popup')) : [];
      var index = -1;

      $.each(popups, function(k, v) {
        if (v.id == popup.id) {
          index = k;
        }
      });

      if (index >= 0) {
        popups.splice(index, 1);
      }

      $.cookie('eto_admin_callerid_popup', JSON.stringify(popups), {path: EasyTaxiOffice.cookiePath, secure: EasyTaxiOffice.cookieSecure, same_site: EasyTaxiOffice.cookieSameSite});
    })
    .on('close.popupwindow', function() {
      setTimeout(function() {
        $('[data-toggle="tooltip"]').tooltip('hide');
        calleridiframe.remove();
        callerid.PopupWindow('destroy');
        callerid.remove();
      }, 250);
    })
    .on('minimize.popupwindow unminimize.popupwindow '+
        'maximize.popupwindow unmaximize.popupwindow', function(e) {
      $('[data-toggle="tooltip"]').tooltip('hide');
      var state = callerid.PopupWindow('getState');
      var popups = $.cookie('eto_admin_callerid_popup') ? JSON.parse($.cookie('eto_admin_callerid_popup')) : [];

      $.each(popups, function(k, v) {
        if (v.id == popup.id) {
          v.state = state;
          popups[k] = v;
        }
      });

      $.cookie('eto_admin_callerid_popup', JSON.stringify(popups), {path: EasyTaxiOffice.cookiePath, secure: EasyTaxiOffice.cookieSecure, same_site: EasyTaxiOffice.cookieSameSite});

      if (state == 'minimized') {
        callerid.PopupWindow('setTitle', phone);
        callerid.closest('.popupwindow_overlay').hide();
      }
      else {
        callerid.PopupWindow('setTitle', '{{ trans('admin/callerid.page_title') }} '+ phone);
        callerid.closest('.popupwindow_overlay').show();
      }

      if (e.type == 'minimize') {
          calleridiframe.remove();
      }
      else if (e.type == 'unminimize') {
          if(calleridiframe.length > 0) {
              calleridiframe.attr('src', url);
          }
          else {
              callerid.html(html);
              $('#modal-callerid-iframe-'+ popup.id).iFrameResize({
                  heightCalculationMethod: 'lowestElement',
                  log: false,
                  targetOrigin: '*',
                  checkOrigin: false
              });
          }
      }
    });

    callerid.PopupWindow('setState', popup.state);

    if (popup.state == 'minimized') {
        callerid.closest('.popupwindow_overlay').hide();
    }
    else {
        callerid.closest('.popupwindow_overlay').show();

        if(calleridiframe.length > 0) {
            calleridiframe.attr('src', url);
        }
        else {
            callerid.html(html);
            $('#modal-callerid-iframe-'+ popup.id).iFrameResize({
                heightCalculationMethod: 'lowestElement',
                log: false,
                targetOrigin: '*',
                checkOrigin: false
            });
        }
    }
  }
}

function handlePhoneNumber() {
  $('a[href^="tel:"]').on('click', function(e) {
    // console.log('clicked TEL');
    document.querySelector("#rc-widget-adapter-frame").contentWindow.postMessage({
      type: 'rc-adapter-new-call',
      phoneNumber: $(this).attr('href').replace('tel:', ''),
      toCall: true,
    }, '*');

    e.stopImmediatePropagation();
    e.stopPropagation();
  });

  $('a[href^="sms:"]').on('click', function(e) {
    // console.log('clicked SMS');
    document.querySelector("#rc-widget-adapter-frame").contentWindow.postMessage({
      type: 'rc-adapter-new-sms',
      phoneNumber: $(this).attr('href').replace('sms:', ''),
    }, '*');

    e.stopImmediatePropagation();
    e.stopPropagation();
  });
}

function uuid() {
  var dt = new Date().getTime();
  var uuid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
    var r = (dt + Math.random()*16)%16 | 0;
    dt = Math.floor(dt/16);
    return (c=='x' ? r :(r&0x3|0x8)).toString(16);
  });
  return uuid;
}
</script>
