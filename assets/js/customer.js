

var isWindowLoaded = 0;
var _token = $('meta[name="csrf-token"]').attr('content');

function etoLang(id) {
  var showTranslation = 0;
  var translation = '';

  if (showTranslation) {
    translation += '*';
  }

  if (typeof ETOLangOverride !== 'undefined' && ETOLangOverride[id]) {
    translation += ETOLangOverride[id];
  }
  else if (typeof ETOLang !== 'undefined' && ETOLang[id]) {
    translation += ETOLang[id];
  }
  else {
    translation += id;
  }

  if (showTranslation) {
    translation += '*';
  }

  return translation;
}

function printPartOfPage(elementId) {
  // var printHeader = '<html><head><title>' + etoLang('print_Heading') + '</title><style type="text/css">table td {padding:2px 2px; 2px 0px;}</style></head><body>';
  // var printFooter = '</body></html>';
  var printContent = document.getElementById(elementId).innerHTML;
  // var windowUrl = 'about:blank';
  // var windowName = 'Print' + new Date().getTime();
  // var printWindow = window.open(windowUrl, windowName, 'left=50000,top=50000,width=0,height=0');
  //
  // printWindow.document.write(printHeader + printContent + printFooter);
  // printWindow.document.close();
  // printWindow.focus();
  // printWindow.print();
  // printWindow.close();

  if (!$('#printIframeData').length) {
      $('body').append('<iframe id="printIframeData" style="display:none;"></iframe>');
  }
  $('#printIframeData').contents().find('body').html(printContent);
  printFrame('printIframeData');
}

function printFrame(id) {
  var printWindow = document.getElementById(id).contentWindow;
  printWindow.focus(); //IE fix
  printWindow.print();
  return false;
}

function etoAppV2(data) {
  moment.tz.setDefault(EasyTaxiOffice.timezone);

  var url = window.location.href.replace(window.location.hash, '');
  if(url == '') {
    url = 'customer';
  }

  this.runtime = {
    debug: 0,
    isMobileApp: false,
    horizontalNav: false,
    apiURL: EasyTaxiOffice.appPath +'/etov2?apiType=frontend',
    baseURL: url,
    appPath: EasyTaxiOffice.appPath,
    mainContainer: 'etoMainContainer',
    messageContainer: 'etoMessageContainer',
    message: {
      error: [],
      warning: [],
      success: []
    },
    config: [],
    siteId: 0,
    userId: 0,
    userName: '',
    userSince: '',
    previousCommand: ''
  };

  var $that = this;

  if (data) {
    jQuery.each(data, function(key, value) {
      $that.runtime[key] = value;
    });
  }

  $(window).on('hashchange', function() {
    var command = window.location.hash.replace('#', '');

    if ($that.runtime.previousCommand != command) {
        $that.init();
    }

    // Update URL
    if( command != 'booking/new' && !ETOTemplate ) {
        $.cookie('eto_redirect_customer_url', window.location.href, {path: EasyTaxiOffice.cookiePath, secure: EasyTaxiOffice.cookieSecure, same_site: EasyTaxiOffice.cookieSameSite});
    }
  });

  $('body').on('click', '.eto-btn-booking-tracking-customer', function () {
      if (ETO.model !== false) {
          if ($(".eto-tracking-panel").first().is(":hidden")) {
              $(".eto-tracking-panel").show();
              ETO.Routehistory.lastCoordinateTimestamp = 0;
              ETO.Routehistory.statuses = {};
              ETO.Routehistory.initMap();
              $('.eto-statuses-list').remove();
          } else {
              clearInterval(ETO.Routehistory.tracking);
              $(".eto-tracking-panel").hide();
          }
      }
  });


  this.etoScrollToTop = function(top) {
      var $that = this;
      var offset = (top ? top : 0);

      if ('parentIFrame' in window && $that.runtime.config.booking_scroll_to_top_enable) {
          if ($that.runtime.config.booking_scroll_to_top_offset) {
            offset += $that.runtime.config.booking_scroll_to_top_offset;
          }

          if(isWindowLoaded) {
            window.parentIFrame.scrollToOffset(0, offset);
          }

          isWindowLoaded = 1;
      }
      else {
          $('html, body').animate({scrollTop: offset}, 500);
      }
  }

  this.setMessage = function(message, action) {
    var $that = this;

    $that.runtime.message = {
      error: [],
      warning: [],
      success: []
    };

    if (message) {
      if (message.error && message.error.length > 0) {
        jQuery.each(message.error, function(key, value) {
          $that.runtime.message.error.push(value);
        });
      }

      if (message.warning && message.warning.length > 0) {
        jQuery.each(message.warning, function(key, value) {
          $that.runtime.message.warning.push(value);
        });
      }

      if (message.success && message.success.length > 0) {
        jQuery.each(message.success, function(key, value) {
          $that.runtime.message.success.push(value);
        });
      }
    }

    if (action == 1) {
      if ($that.runtime.message.error.length > 0 || $that.runtime.message.warning.length > 0 || $that.runtime.message.success.length) {
        $that.displayMessage();
      }
    } else {
      $that.displayMessage();
    }
  };

  this.displayMessage = function() {
    var $that = this;
    var html = '';

    if ($that.runtime.message.error && $that.runtime.message.error.length > 0) {
      var error = '';
      jQuery.each($that.runtime.message.error, function(key, value) {
        error += '<p>' + value + '</p>';
      });
      html += '<div class="alert alert-danger alert-dismissible" role="alert">' +
        '<button type="button" class="close" data-dismiss="alert" aria-label="' + etoLang('button_Close') + '">' +
        '<span aria-hidden="true">&times;</span>' +
        '</button>' + error +
        '</div>';
    }

    if ($that.runtime.message.warning && $that.runtime.message.warning.length > 0) {
      var warning = '';
      jQuery.each($that.runtime.message.warning, function(key, value) {
        warning += '<p>' + value + '</p>';
      });
      html += '<div class="alert alert-warning alert-dismissible" role="alert">' +
        '<button type="button" class="close" data-dismiss="alert" aria-label="' + etoLang('button_Close') + '">' +
        '<span aria-hidden="true">&times;</span>' +
        '</button>' + warning +
        '</div>';
    }

    if ($that.runtime.message.success && $that.runtime.message.success.length > 0) {
      var success = '';
      jQuery.each($that.runtime.message.success, function(key, value) {
        success += '<p>' + value + '</p>';
      });
      html += '<div class="alert alert-success alert-dismissible" role="alert">' +
        '<button type="button" class="close" data-dismiss="alert" aria-label="' + etoLang('button_Close') + '">' +
        '<span aria-hidden="true">&times;</span>' +
        '</button>' + success +
        '</div>';
    }

    if (!$('#' + $that.runtime.messageContainer).length) {
        $('#' + $that.runtime.mainContainer).before('<div id="'+ $that.runtime.messageContainer +'"></div>');
    }

    $('#' + $that.runtime.messageContainer).html(html);
  };

  this.init = function(command) {
    var $that = this;
    var $isReady = 1;

    if (!command) {
      command = window.location.hash.replace('#', '');
    }

    $that.runtime.previousCommand = command;

    if (typeof ETO.Routehistory != 'undefined' && typeof ETO.Routehistory.tracking != 'undefined') {
        clearInterval(ETO.Routehistory.tracking);
    }

    if ($isReady) {
      jQuery.ajax({
        headers: {
          'X-CSRF-TOKEN': _token
        },
        url: $that.runtime.apiURL,
        type: 'POST',
        data: 'task=init',
        dataType: 'json',
        cache: false,
        success: function(response) {
          if (response.message) {
            $that.setMessage(response.message, 1);
          }

          $that.runtime.userId = response.userId;
          $that.runtime.userAvatarPath = response.userAvatarPath;
          $that.runtime.userName = response.userName;
          $that.runtime.userSince = response.userSince;
          $that.runtime.config = response.config;

          if( $that.runtime.config.debug ) {
            $that.runtime.debug = $that.runtime.config.debug;
          }

          var $params = command.split('/');
          var $param1 = '';
          var $param2 = '';
          var $param3 = '';

          if ($params) {
            if ($params[0]) {
              $param1 = $params[0];
            }
            if ($params[1]) {
              $param2 = $params[1];
            }
            if ($params[2]) {
              $param3 = $params[2];
            }
          }

          if ($that.runtime.userId > 0) {
            if (jQuery.inArray($param1, ['register', 'login', 'password', '']) >= 0) {
              $param1 = 'booking'; // dashboard
              $param2 = $that.runtime.isMobileApp == true ? 'new' : 'list';
            }
          } else {
            if (jQuery.inArray($param1, ['register', 'login', 'password']) < 0) {
              if ($param1) {
                $that.setMessage({
                  warning: [etoLang('userMsg_NotLoggedIn')]
                });
              }
              $param1 = 'login';
            }
          }

          window.location.hash = '#' + command;

          switch ($param1) {
            case 'dashboard':
              $that.dashboard();
              break;
            case 'booking':

              $('.sidebar-menu li.active').removeClass('active');
              $('.sidebar-menu li.active').addClass('active');

              switch ($param2) {
                case 'list':
                  $that.bookingList();
                  break;
                case 'details':
                  $that.bookingDetails($param3);
                  break;
                case 'cancel':
                  $that.bookingCancel($param3);
                  break;
                case 'pay':
                  $that.bookingPay($param3);
                  break;
                case 'invoice':
                  $that.bookingInvoice($param3);
                  break;
                case 'edit':
                  $that.bookingEdit($param3);
                  break;
                case 'delete':
                  $that.bookingDelete($param3);
                  break;
                case 'finish':
                  $that.bookingFinish($param3);
                  break;
                case 'new':
                  $that.bookingNew();
                  break;
                default:
                  $that.bookingList();
                  break;
              }
              break;
            case 'user':
              switch ($param2) {
                case 'edit':
                  $that.userEdit();
                  break;
                default:
                  $that.user();
                  break;
              }
              break;
            case 'register':
              switch ($param2) {
                case 'activation':
                  $that.registerActivation($param3);
                break;
                case 'resend':
                  $that.registerResend($param3);
                break;
                default:
                  $('#' + $that.runtime.messageContainer).html('');
                  $that.register();
                break;
              }
            break;
            case 'login':
              $('#' + $that.runtime.messageContainer).html('');
              $that.login();
            break;
            case 'password':
              switch ($param2) {
                case 'new':
                  $that.passwordNew($param3);
                break;
                default:
                  $('#' + $that.runtime.messageContainer).html('');
                  $that.password();
                break;
              }
            break;
            case 'logout':
              $that.logout();
            break;
          }

          $('.language-switcher-customer').removeClass('hidden');

          $that.etoScrollToTop();

          if ($that.runtime.debug) {
            console.log(response);
          }
        },
        error: function(response) {
          $that.setMessage({
            error: ['AJAX error: Init']
          });
        },
        beforeSend: function() {
          $isReady = 0;
        },
        complete: function() {
          $isReady = 1;
        }
      });
    }
  };


  this.panel = function(className) {
    var $that = this;
    var className = className ? className : '';

    var html = '<div class="wrapper '+ className +'">\
        <header class="main-header">\
            <nav class="navbar navbar-static-top '+ ($that.runtime.horizontalNav == true ? 'eto-navbar-horizontal-nav' : '')+'">\
                <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">\
                    <span class="sr-only">' + etoLang('common.toggle_navigation') + '</span>\
                    <span class="icon-bar"></span>\
                    <span class="icon-bar"></span>\
                    <span class="icon-bar"></span>\
                </a>\
                <div class="navbar-custom-menu navbar-custom-menu-horizontal">\
                    <ul class="nav navbar-nav">\
                        <li><a href="' + $that.runtime.baseURL + '#booking/list"><span>' + etoLang('panel_Bookings') + '</span></a></li>\
                        <li><a href="' + $that.runtime.baseURL + '#booking/new"><span>' + etoLang('panel_NewBooking') + '</span></a></li>\
                        <li><a href="' + $that.runtime.baseURL + '#user"><span>' + etoLang('panel_Profile') + '</span></a></li>\
                      <ul>\
                </div>\
                <span class="main-page-title"></span>\
                <div class="navbar-custom-menu navbar-custom-menu-user">\
                    <ul class="nav navbar-nav">\
                        <li class="dropdown user user-menu">\
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">\
                                <img src="'+ $that.runtime.userAvatarPath +'" class="user-image" alt="">\
                                <span class="hidden-xs">' + $that.runtime.userName + '</span>\
                            </a>\
                            <ul class="dropdown-menu">\
                                <li class="user-header">\
                                    <p>' + $that.runtime.userName + '<small>' + $that.runtime.userSince + '</small></p>\
                                </li>\
                                <li class="user-footer">\
                                    <div class="pull-left">\
                                        <a href="' + $that.runtime.baseURL + '#user" class="btn btn-default btn-flat">\
                                            <span>' + etoLang('panel_Profile') + '</span>\
                                        </a>\
                                    </div>\
                                    <div class="pull-right logout-container">\
                                        <a href="' + $that.runtime.baseURL + '#logout" class="btn btn-default btn-flat">\
                                            <span>' + etoLang('panel_Logout') + '</span>\
                                        </a>\
                                    </div>\
                                </li>\
                            </ul>\
                        </li>\
                    </ul>\
                </div>\
            </nav>\
        </header>\
        <aside class="main-sidebar">\
            <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">\
                <span class="icon-toggle"></span>\
            </a>\
            <section class="sidebar">\
                <div class="user-panel">\
                    <div class="pull-left image">\
                        <img src="'+ $that.runtime.userAvatarPath +'" class="img-circle" alt="">\
                    </div>\
                    <div class="pull-left info">\
                        <p style="max-width:120px; overflow:hidden; white-space:nowrap; text-overflow:ellipsis; '+ (parseInt($that.runtime.config.locale_switcher_enabled) == 1 ? 'margin: 0 0 5px 0;' : 'margin: 10px 0 0 0;') +'">'+ $that.runtime.userName +'</a></p>\
                        <div class="user-panel-locale"></div>\
                    </div>\
                </div>\
                <ul class="sidebar-menu">\
                    <li class="eto-sidebar-menu-bookings"><a href="' + $that.runtime.baseURL + '#booking/list"><span>' + etoLang('panel_Bookings') + '</span></a></li>\
                    <li class="eto-sidebar-menu-booking-new"><a href="' + $that.runtime.baseURL + '#booking/new"><span>' + etoLang('panel_NewBooking') + '</span></a></li>\
                    <li class="eto-sidebar-menu-profile"><a href="' + $that.runtime.baseURL + '#user"><span>' + etoLang('panel_Profile') + '</span></a></li>\
                    <li class="eto-sidebar-menu-logout mobile-logout-hide"><a href="' + $that.runtime.baseURL + '#logout"><span>' + etoLang('panel_Logout') + '</span></a></li>\
                </ul>\
                <div class="copyright-box">\
                    ' + etoLang('common.powered_by') + ' <a href="https://easytaxioffice.com" target="_blank">EasyTaxiOffice</a>\
                </div>\
            </section>\
        </aside>\
        <div class="content-wrapper">\
            <section class="content">\
                <div id="etoPanelContent"></div>\
            </section>\
        </div>\
    </div>\
    <style>\
    body.customer-panel {padding: 0;}\
    .footer-branding, .language-switcher-customer {display:none !important;}\
    </style>';

    $('#' + $that.runtime.mainContainer).html(html);

    // Enable hide menu when clicking on the content-wrapper on small screens
    var screenSizes = $.AdminLTE.options.screenSizes;
    if (screenSizes) {
      $("body").on('click', ".sidebar-menu li a", function () {
        if ($(window).width() <= (screenSizes.sm - 1) && $("body").hasClass("sidebar-open")) {
          $("body").removeClass('sidebar-open');
        }
      });
    }

    if ($('#' + $that.runtime.messageContainer).length) {
        $('#etoPanelContent').closest('section.content').prepend($('#' + $that.runtime.messageContainer));
    }
    else {
        $('#etoPanelContent').before('<div id="'+ $that.runtime.messageContainer +'"></div>');
    }

    if (parseInt($that.runtime.config.locale_switcher_enabled) == 1) {
        var localeOpt = '';
        var localeActive = {};

        $.each($that.runtime.config.locales, function(key, val) {
          if ($that.runtime.config.locale_current == val.code) {
            localeActive = val;
          }
          if ($.inArray(val.code, $that.runtime.config.locale_active) >= 0) {
            localeOpt += '<li>\
              <a href="'+ EasyTaxiOffice.appPath +'/locale/'+ val.code +'" class="clearfix">\
                <img src="'+ EasyTaxiOffice.appPath +'/assets/images/flags/'+ val.code +'.png" class="eto-language-flag" />\
                <span class="eto-language-name">'+ val.native +'</span>\
              </a>\
            </li>';
          }
        });

        // <img src="'+ EasyTaxiOffice.appPath +'/assets/images/flags/'+ localeActive.code +'.png" class="eto-language-flag" />\
        var localeBtn = '<div class="btn-group">\
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">\
                <span class="eto-language-name">'+ localeActive.native +'</span>\
                <span class="caret"></span>\
            </button>\
            <ul class="dropdown-menu">'+ localeOpt +'</ul>\
        </div>';

        $('.user-panel-locale').html(localeBtn);
    }
    $('.copyright-box').html($('.footer-branding').html());

    function toggleChevron(e) {
      $(e.target)
      .prev('.panel-heading')
      .find("span.indicator")
      .toggleClass('glyphicon-chevron-down glyphicon-chevron-up');
    }
    $('#etoPanelNavigationMaster').on('hidden.bs.collapse', toggleChevron);
    $('#etoPanelNavigationMaster').on('shown.bs.collapse', toggleChevron);

    $('[title]').tooltip({
      html: true,
      placement: 'auto'
    });

    var $navigation = $('#' + $that.runtime.mainContainer + ' #etoPanelNavigation');
    var $command = window.location.hash.replace('#', '');

    var $params = $command.split('/');
    var $param1 = '';
    var $param2 = '';
    var $param3 = '';

    if ($params) {
      if ($params[0]) {
        $param1 = $params[0];
      }
      if ($params[1]) {
        $param2 = $params[1];
      }
      if ($params[2]) {
        $param3 = $params[2];
      }
    }

    if ($navigation.find('a[href*="#' + $param1 + '/' + $param2 + '"]').length > 0) {
      $navigation.find('a[href*="#' + $param1 + '/' + $param2 + '"]').parent('li').addClass('active');
    } else if ($param1 == 'booking') {
      $navigation.find('a[href*="#' + $param1 + '/list"]').parent('li').addClass('active');
    } else if ($param1 == '') {
      $navigation.find('a[href*="#booking/list"]').parent('li').addClass('active');
    } else {
      $navigation.find('a[href*="#' + $param1 + '"]').parent('li').addClass('active');
    }

    $navigation.find('a').click(function(event) {
      var $href = $(this).attr('href').split("#")[1].replace('#', '');

      switch ($href) {
        case 'dashboard':
          $that.init('dashboard');
          break;
        case 'booking/list':
          $that.init('booking/list');
          break;
        case 'booking/new':
          $that.init('booking/new');
          break;
        case 'user':
          $that.init('user');
          break;
        case 'logout':
          $that.init('logout');
          break;
      }

      event.preventDefault();
    });
  };


  this.dashboard = function() {
    var $that = this;
    $that.bookingList();
  };


  this.bookingList = function() {
    var $that = this;
    var $isReady = 1;

    $that.panel('eto-wrapper-booking-list');

    $('.sidebar-menu li.active').removeClass('active');
    $('.eto-sidebar-menu-bookings').addClass('active');
    $('.main-header .main-page-title').html(etoLang('bookingList_Heading'));

    var html = '<div class="etoBookingListContainer">\
        <div id="etoUserContent"></div>\
        <div class="eto-modal-booking-tracking modal" role="dialog" aria-hidden="true">\
            <div class="modal-dialog" style="width:90%">\
                <div class="modal-content">\
                    <div class="modal-header">\
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>\
                        <h4 class="modal-title">' + etoLang('booking_button_show_on_map') + '</h4>\
                    </div>\
                    <div class="modal-body">\
                        <div class="eto-booking-tracking-map" style="height: 500px"></div>\
                    </div>\
                </div>\
            </div>\
        </div>\
      </div>';

    $('#' + $that.runtime.mainContainer + ' #etoPanelContent').html(html);
      if(ETO.model !== false && typeof ETO.Routehistory != "undefined" && typeof ETO.Routehistory.init != "undefined") {
          ETO.Routehistory.init();
      }

    if ($isReady) {
      jQuery.ajax({
        headers: {
          'X-CSRF-TOKEN': _token
        },
        url: $that.runtime.apiURL,
        type: 'POST',
        data: 'task=booking&action=list',
        dataType: 'json',
        cache: false,
        success: function(response) {
          if (response.message) {
            $that.setMessage(response.message);
          }

          if (response.bookings.length > 0) {
            html = '<div class="table-responsive" style="min-height: 300px; width:1px; min-width:100%;">' +
              '<table class="table table-hover table-custom">' +
              '<thead>' +
              '<tr>' +
              '<th class="eto-show-booking-column-mobile1"></th>' +
              '<th style="min-width:180px;">' + etoLang('bookingField_Ref') + '</th>' +
              '<th style="min-width:180px;" class="eto-show-booking-column-mobile">' + etoLang('bookingField_Date') + '</th>' +
              '<th style="min-width:180px;">' + etoLang('bookingField_From') + '</th>' +
              '<th style="min-width:180px;">' + etoLang('bookingField_To') + '</th>' +
              '<th style="min-width:180px;">' + etoLang('bookingField_Via') + '</th>' +
              '<th style="min-width:180px;">' + etoLang('bookingField_Name') + '</th>' +
              '<th style="min-width:180px;">' + etoLang('bookingField_Email') + '</th>' +
              '<th style="min-width:180px;">' + etoLang('bookingField_PhoneNumber') + '</th>' +
              '<th class="eto-show-booking-column-mobile">' + etoLang('bookingField_Total') + '</th>' +
              '<th class="etoBookingListStatus">' + etoLang('bookingField_Status') + '</th>' +
              '</tr>'+
              '</thead>'+
              '<tbody>';

            jQuery.each(response.bookings, function(key, value) {
              var str = '';
              var rowCls = '';
              if(key > 1) { rowCls = 'dropup'; }

              html += '<tr>' +
                '<td class="eto-show-booking-column-mobile1">' +
                '<div class="btn-group" role="group" aria-label="..." style="width:70px;">'+
                '<a href="' + $that.runtime.baseURL + '#booking/details/' + value.id + '" class="btn btn-default btn-sm"><i class="fa fa-eye"></i></a>'+
                '<div class="btn-group '+ rowCls +' pull-left" role="group">' +
                '<button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-expanded="false">' +
                    '<span class="fa fa-angle-down"></span>' +
                '</button>' +
                '<ul class="dropdown-menu" role="menu">';
                  // html += '<li><a href="' + $that.runtime.baseURL + '#booking/details/' + value.id + '">' + etoLang('bookingButton_Details') + '</a></li>';
                  if (value.buttonPay) {
                    html += '<li><a href="' + $that.runtime.baseURL + '#booking/pay/' + value.id + '">' + etoLang('bookingButton_PayNow') + '</a></li>';
                  }
                  if (value.buttonInvoice) {
                    html += '<li class="etoInvoiceButton"><a href="' + $that.runtime.baseURL + '#booking/invoice/' + value.id + '">' + etoLang('bookingButton_Invoice') + '</a></li>';
                  }
                  if (value.buttonEdit) {
                    html += '<li><a href="#" data-toggle="modal" data-target="#etoBookingEditModal" data-booking-id="'+ value.id +'" onclick="return false;">' + etoLang('bookingButton_Edit') + '</a></li>';
                  }
                  if (value.buttonCancel) {
                    html += '<li><a href="#" data-toggle="modal" data-target="#etoBookingCancelModal" data-booking-id="'+ value.id +'" onclick="return false;">' + etoLang('bookingButton_Cancel') + '</a></li>';
                  }
                  if (value.buttonDelete) {
                    html += '<li><a href="' + $that.runtime.baseURL + '#booking/delete/' + value.id + '">' + etoLang('bookingButton_Delete') + '</a></li>';
                  }
                  if (value.feedbackLink) {
                    html += '<li><a href="' + value.feedbackLink + '" target="_blank">' + etoLang('bookingButton_Feedback') + '</a></li>';
                  }
                  html += '<li><a href="javascript:void(0)" class="eto-btn-booking-tracking eto-btn-booking-tracking-customer" data-eto-id="'+value.id+'">'+ etoLang('booking_button_show_on_map') +'</a></li>';
                html += '</ul>' +
                    '</div>' +
                '</div>' +
                '</td>' +
                '<td><a href="' + $that.runtime.baseURL + '#booking/details/' + value.id + '" title="' + str + '">' + value.refNumber + '</a></td>' +
                '<td class="eto-show-booking-column-mobile eto-show-booking-column-date"><a href="' + $that.runtime.baseURL + '#booking/details/' + value.id + '">' + value.date + '</a></td>' +
                '<td><div class="eto-address-more">' + value.from + '</div></td>' +
                '<td><div class="eto-address-more">' + value.to + '</div></td>' +
                '<td><div class="eto-address-more">' + value.waypoints + '</div></td>' +
                '<td><div class="eto-address-more">' + value.contact_name + '</div></td>' +
                '<td>' + value.contact_email + '</td>' +
                '<td>' + value.contact_mobile + '</td>' +
                '<td class="eto-show-booking-column-mobile eto-show-booking-column-total"><a href="' + $that.runtime.baseURL + '#booking/details/' + value.id + '">' + value.price + '</a></td>' +
                '<td class="etoBookingListStatus">' + value.status + '</td>' +
                '</tr>';
            });

            html += '</tbody>';
            html += '</table>' +
              '</div>';
          } else {
            html = '<p class="alert alert-warning">' + etoLang('bookingMsg_NoBookings') + '</p>';
          }

          html += '<div class="etoBookingListButtons">' +
              '<a href="' + $that.runtime.baseURL + '#booking/new" class="btn btn-primary">' + etoLang('bookingButton_NewBooking') + '</a>' +
            '</div>';

          var msg = etoLang('bookingMsgCancel')
            .replace(/\{link\}/g, '<a href="'+ ($that.runtime.config.url_terms ? $that.runtime.config.url_terms : '#') +'" target="_blank">')
            .replace(/\{\/link\}/g, '</a>');

          html += '<div id="etoBookingCancelModal" class="modal fade" role="dialog" aria-labelledby="etoBookingCancelModalTitle" aria-hidden="true">' +
            '<div class="modal-dialog">' +
              '<div class="modal-content">' +
                '<div class="modal-header">' +
                  '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>' +
                  '<h4 class="modal-title" id="etoBookingCancelModalTitle">'+ etoLang('bookingTitleCancel') +'</h4>' +
                '</div>' +
                (parseInt($that.runtime.config.terms_enable) == 1 ? ('<div class="modal-body">'+ msg +'</div>') : '') +
                '<div class="modal-footer">'+
                  '<button type="button" class="btn btn-danger" id="etoBookingCancelModalBtn">'+ etoLang('bookingYes') +'</button>'+
                  '<button type="button" class="btn btn-default" data-dismiss="modal">'+ etoLang('bookingNo') +'</button>'+
                '</div>' +
              '</div>' +
            '</div>' +
          '</div>';

          var msg = etoLang('bookingMsgEdit')
            .replace(/\{email\}/g, '<a href="mailto:'+ $that.runtime.config.company_email +'">'+ $that.runtime.config.company_email +'</a>')
            .replace(/\{phone\}/g, '<a href="tel:'+ $that.runtime.config.company_telephone +'">'+ $that.runtime.config.company_telephone +'</a>');

          html += '<div id="etoBookingEditModal" class="modal fade" role="dialog" aria-labelledby="etoBookingEditModalTitle" aria-hidden="true">' +
            '<div class="modal-dialog">' +
              '<div class="modal-content">' +
                '<div class="modal-header">' +
                  '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>' +
                  '<h4 class="modal-title" id="etoBookingEditModalTitle">'+ etoLang('bookingButton_Edit') +'</h4>' +
                '</div>' +
                '<div class="modal-body">'+ msg +'</div>'+
              '</div>' +
            '</div>' +
          '</div>';

          $('#' + $that.runtime.mainContainer + ' #etoUserContent').html(html);

          $('.eto-address-more').readmore({
            collapsedHeight: 40,
            moreLink: '<a href="#" class="eto-address-more-link">' + etoLang('booking.buttons.more') + '</a>',
            lessLink: '<a href="#" class="eto-address-more-link">' + etoLang('booking.buttons.less') + '</a>'
          });

          $('#etoBookingCancelModal').modal({
              show: false,
          })
          .on('show.bs.modal', function(event) {
              var id = $(event.relatedTarget).data('booking-id');
              $('#etoBookingCancelModalBtn').on('click', function(){
                  window.location.href = $that.runtime.baseURL + '#booking/cancel/'+ id;
                  $('#etoBookingCancelModal').modal('hide');
              });
              $that.etoScrollToTop();
          });

          $('#etoBookingEditModal').modal({
              show: false,
          })
          .on('show.bs.modal', function(event) {
              var id = $(event.relatedTarget).data('booking-id');
              $that.etoScrollToTop();
          });

          $('[title]').tooltip({
            html: true,
            placement: 'auto'
          });

          if ($that.runtime.debug) {
            console.log(response);
          }
        },
        error: function(response) {
          $that.setMessage({
            error: ['AJAX error: Booking List']
          });
        },
        beforeSend: function() {
          $isReady = 0;
        },
        complete: function() {
          $isReady = 1;
        }
      });
    }
  };


  this.bookingDetails = function(id) {
    var $that = this;
    var $isReady = 1;

    $that.panel();

    var backBtn = '<a href="' + $that.runtime.baseURL + '#booking/list" class="eto-booking-go-back" title="' + etoLang('bookingButton_Back') + '" data-placement="right"><span class="fa fa-arrow-circle-left"></span></a>';
    $('.sidebar-menu li.active').removeClass('active');
    $('.eto-sidebar-menu-bookings').addClass('active');
    $('.main-header .main-page-title').html(backBtn + etoLang('bookingDetails_Heading') +' <span id="etoBookingRef"></span>');

    var html = '<div>' +
        '<div id="etoUserContent"></div>' +
        '<div class="eto-tracking-panel" style="min-height: 500px; display:none;">' +
          '<div class="eto-booking-tracking-map" style="height: 500px"></div>' +
        '</div>' +
      '</div>';

    $('#' + $that.runtime.mainContainer + ' #etoPanelContent').html(html);
    $(".eto-tracking-panel").hide();
    if(ETO.model !== false && typeof ETO.Routehistory != "undefined" && typeof ETO.Routehistory.init != "undefined") {
        ETO.Routehistory.init();
    }

    if ($isReady) {
      jQuery.ajax({
        headers: {
          'X-CSRF-TOKEN': _token
        },
        url: $that.runtime.apiURL,
        type: 'POST',
        data: 'task=booking&action=details&id=' + id,
        dataType: 'json',
        cache: false,
        success: function(response) {
          if (response.message) {
            $that.setMessage(response.message);
          }

          html = '<div id="etoPrintContent">';

          if (response.success) {
            var $booking = response.booking;

            $('#' + $that.runtime.mainContainer + ' #etoBookingRef').html(' / ' + $booking.refNumber);

            html += '<div class="row">';
            html += '<div class="col-xs-12 col-sm-6">';

            html += '<p><b>' + etoLang('bookingHeading_JourneyDetails') + '</b></p>';
            html += '<table class="table table-condensed">';

            if ($booking.serviceType) {
                html += '<tr><td class="eto-booking-details-title">' + etoLang('bookingField_Services') + ':</td><td>' + $booking.serviceType + '</td></tr>';
            }
            if ($booking.serviceDuration) {
                html += '<tr><td class="eto-booking-details-title">' + etoLang('bookingField_ServicesDuration') + ':</td><td>' + $booking.serviceDuration + '</td></tr>';
            }

            html += '<tr><td class="eto-booking-details-title col-xs-4 col-sm-4">' + etoLang('bookingField_From') + ':</td><td class="col-xs-8 col-sm-8 col-md-8">' + $booking.from + '</td></tr>';
            html += '<tr><td class="eto-booking-details-title">' + etoLang('bookingField_Date') + ':</td><td>' + $booking.date + '</td></tr>';

            if ($booking.flightNumber) {
              html += '<tr><td class="eto-booking-details-title">' + etoLang('bookingField_FlightNumber') + ':</td><td>' + $booking.flightNumber + '</td></tr>';
            }
            if ($booking.flightLandingTime) {
              html += '<tr><td class="eto-booking-details-title">' + etoLang('bookingField_FlightLandingTime') + ':</td><td>' + $booking.flightLandingTime + '</td></tr>';
            }
            if ($booking.departureCity) {
              html += '<tr><td class="eto-booking-details-title">' + etoLang('bookingField_DepartureCity') + ':</td><td>' + $booking.departureCity + '</td></tr>';
            }
            if ($booking.waitingTime) {
              html += '<tr><td class="eto-booking-details-title">' + etoLang('bookingField_WaitingTime') + ':</td><td>' + $booking.waitingTime + ' ' + etoLang('bookingField_WaitingTimeAfterLanding') + '</td></tr>';
            }
            if ($booking.meetAndGreet) {
              html += '<tr><td class="eto-booking-details-title">' + etoLang('bookingField_MeetAndGreet') + ':</td><td>' + $booking.meetAndGreet + '</td></tr>';
            }
            if ($booking.meetingPoint) {
              html += '<tr><td class="eto-booking-details-title">' + etoLang('bookingField_MeetingPoint') + ':</td><td>' + $booking.meetingPoint + '</td></tr>';
            }
            if ($booking.waypoints) {
              html += '<tr><td class="eto-booking-details-title">' + etoLang('bookingField_Via') + ':</td><td>' + $booking.waypoints + '</td></tr>';
            }

            html += '<tr><td class="eto-booking-details-title">' + etoLang('bookingField_To') + ':</td><td>' + $booking.to + '</td></tr>';

            if ($booking.departureFlightNumber) {
              html += '<tr><td class="eto-booking-details-title">' + etoLang('bookingField_DepartureFlightNumber') + ':</td><td>' + $booking.departureFlightNumber + '</td></tr>';
            }
            if ($booking.departureFlightTime) {
              html += '<tr><td class="eto-booking-details-title">' + etoLang('bookingField_DepartureFlightTime') + ':</td><td>' + $booking.departureFlightTime + '</td></tr>';
            }
            if ($booking.departureFlightCity) {
              html += '<tr><td class="eto-booking-details-title">' + etoLang('bookingField_DepartureFlightCity') + ':</td><td>' + $booking.departureFlightCity + '</td></tr>';
            }

            html += '<tr><td class="eto-booking-details-title">' + etoLang('bookingField_Vehicle') + ':</td><td>' + $booking.vehicle + '</td></tr>';

            if ($booking.passengers) {
              html += '<tr><td class="eto-booking-details-title">' + etoLang('bookingField_Passengers') + ':</td><td>' + $booking.passengers + '</td></tr>';
            }
            if ($booking.childSeats) {
              html += '<tr><td class="eto-booking-details-title">' + etoLang('bookingField_ChildSeats') + ':</td><td>' + $booking.childSeats + '</td></tr>';
            }
            if ($booking.babySeats) {
              html += '<tr><td class="eto-booking-details-title">' + etoLang('bookingField_BabySeats') + ':</td><td>' + $booking.babySeats + '</td></tr>';
            }
            if ($booking.infantSeats) {
              html += '<tr><td class="eto-booking-details-title">' + etoLang('bookingField_InfantSeats') + ':</td><td>' + $booking.infantSeats + '</td></tr>';
            }
            if ($booking.wheelchair) {
              html += '<tr><td class="eto-booking-details-title">' + etoLang('bookingField_Wheelchair') + ':</td><td>' + $booking.wheelchair + '</td></tr>';
            }
            if ($booking.luggage) {
              html += '<tr><td class="eto-booking-details-title">' + etoLang('bookingField_Luggage') + ':</td><td>' + $booking.luggage + '</td></tr>';
            }
            if ($booking.handLuggage) {
              html += '<tr><td class="eto-booking-details-title">' + etoLang('bookingField_HandLuggage') + ':</td><td>' + $booking.handLuggage + '</td></tr>';
            }
            if ($booking.requirements) {
              html += '<tr><td class="eto-booking-details-title">' + etoLang('bookingHeading_SpecialInstructions') + ':</td><td>' + $booking.requirements + '</td></tr>';
            }
            html += '</table>';

            html += '<p><b>' + etoLang('bookingHeading_YourDetails') + '</b></p>' +
              '<table class="table table-condensed">' +
              '<tr><td class="eto-booking-details-title col-xs-4 col-sm-4">' + etoLang('bookingField_Name') + ':</td><td class="col-xs-8 col-sm-8 col-md-8">' + $booking.contactTitle + ' ' + $booking.contactName + '</td></tr>';
              if ($booking.contactEmail) {
                  html += '<tr><td class="eto-booking-details-title">' + etoLang('bookingField_Email') + ':</td><td>' + $booking.contactEmail + '</td></tr>';
              }
              if ($booking.contactMobile) {
                  html += '<tr><td class="eto-booking-details-title">' + etoLang('bookingField_PhoneNumber') + ':</td><td>' + $booking.contactMobile + '</td></tr>';
              }
              if ($booking.department) {
                  html += '<tr><td class="eto-booking-details-title">' + etoLang('bookingField_Department') + ':</td><td>' + $booking.department + '</td></tr>';
              }
            html += '</table>';

            if ($booking.leadPassenger) {
              html += '<p><b>' + etoLang('bookingHeading_LeadPassenger') + '</b></p>' +
                '<table class="table table-condensed">' +
                '<tr><td class="eto-booking-details-title col-xs-4 col-sm-4">' + etoLang('bookingField_Name') + ':</td><td class="col-xs-8 col-sm-8 col-md-8">' + $booking.leadPassengerTitle + ' ' + $booking.leadPassengerName + '</td></tr>';
                if ($booking.leadPassengerEmail) {
                    html += '<tr><td class="eto-booking-details-title">' + etoLang('bookingField_Email') + ':</td><td>' + $booking.leadPassengerEmail + '</td></tr>';
                }
                if ($booking.leadPassengerMobile) {
                    html += '<tr><td class="eto-booking-details-title">' + etoLang('bookingField_PhoneNumber') + ':</td><td>' + $booking.leadPassengerMobile + '</td></tr>';
                }
              html += '</table>';
            }

            html += '</div>';
            html += '<div class="col-xs-12 col-sm-6">';

            html += '<p><b>' + etoLang('bookingHeading_ReservationDetails') + '</b></p>' +
              '<table class="table table-condensed">' +
                  '<tr><td class="eto-booking-details-title col-xs-4 col-sm-4">' + etoLang('bookingField_Ref') + ':</td><td class="col-xs-8 col-sm-8 col-md-8">' + $booking.refNumber + '</td></tr>' +
                  '<tr><td class="eto-booking-details-title">' + etoLang('bookingField_CreatedDate') + ':</td><td>' + $booking.createdDate + '</td></tr>' +
                  '<tr><td class="eto-booking-details-title">' + etoLang('bookingField_Status') + ':</td><td>' + $booking.status + '</td></tr>'+
                  '<tr><td class="eto-booking-details-title">' + etoLang('bookingField_JourneyType') + ':</td><td>' + $booking.route + '</td></tr>';
            html += '</table><br />';

            html += '<table class="table table-condensed">'
                if ($booking.extraCharges && $that.runtime.config.booking_summary_enable) {
                  html += '<tr><td class="eto-booking-details-title">' + etoLang('bookingField_Summary') + ':</td><td>' + $booking.extraCharges + '</td></tr>';
                }

                if( $booking.discount || $booking.paymentCharge ) {
                    html += '<tr><td class="eto-booking-details-title">' + etoLang('bookingField_Price') + ':</td><td>' + $booking.totalPrice + '</td></tr>';
                }

                if( $booking.discount ) {
                    html += '<tr><td class="eto-booking-details-title">' + etoLang('bookingField_DiscountPrice') + ':</td><td>'+ $booking.discount;
                        if ( $booking.discountCode ) {
                          html += ' <span style="color:#888;" title="'+ etoLang('bookingField_DiscountCode') +'">( '+ $booking.discountCode + ' )</span>';
                        }
                    html += '</td></tr>';
                }

                if( $booking.paymentCharge ) {
                    html += '<tr><td class="eto-booking-details-title">' + etoLang('bookingField_PaymentPrice') + ':</td><td>' + $booking.paymentCharge + '</td></tr>';
                }

                html += '<tr><td class="eto-booking-details-title col-xs-4 col-sm-4">' + etoLang('bookingField_Total') + ':</td><td class="col-xs-8 col-sm-8 col-md-8">' + $booking.total + '</td></tr>';

                if( $booking.payments ) {
                    html += '<tr><td class="eto-booking-details-title">' + etoLang('bookingField_Payments') + ':</td><td>' + $booking.payments + '</td></tr>';
                }
            html += '</table>';

            if ($booking.driverId) {
                html += '<p><b>' + etoLang('bookingHeading_Driver') + '</b></p>';
                html += '<table class="table table-condensed">'
                    if ($booking.driverName) {
                        html += '<tr><td class="eto-booking-details-title col-xs-4 col-sm-4">' + etoLang('bookingField_DriverName') + ':</td><td class="col-xs-8 col-sm-8 col-md-8">'+ $booking.driverName +'</td></tr>';
                    }
                    if ($booking.driverAvatar) {
                        html += '<tr><td class="eto-booking-details-title col-xs-4 col-sm-4">'+ etoLang('bookingField_DriverAvatar') +':</td><td class="col-xs-8 col-sm-8 col-md-8"><img src="'+ $booking.driverAvatar +'" alt="" style="padding:0px; margin:0px; max-width:100px;" /></td></tr>';
                    }
                    if ($booking.driverPhone) {
                        html += '<tr><td class="eto-booking-details-title col-xs-4 col-sm-4">'+ etoLang('bookingField_DriverPhone') +':</td><td class="col-xs-8 col-sm-8 col-md-8">'+ $booking.driverPhone +'</td></tr>';
                    }
                    if ($booking.driverLicence) {
                        html += '<tr><td class="eto-booking-details-title col-xs-4 col-sm-4">'+ etoLang('bookingField_DriverLicence') +':</td><td class="col-xs-8 col-sm-8 col-md-8">'+ $booking.driverLicence +'</td></tr>';
                    }
                html += '</table>';
            }

            if ($booking.vehicleId) {
                html += '<p><b>' + etoLang('bookingHeading_Vehicle') + '</b></p>';
                html += '<table class="table table-condensed">'
                    if ($booking.vehicleRegistrationMark) {
                        html += '<tr><td class="eto-booking-details-title col-xs-4 col-sm-4">' + etoLang('bookingField_VehicleRegistrationMark') + ':</td><td class="col-xs-8 col-sm-8 col-md-8">'+ $booking.vehicleRegistrationMark +'</td></tr>';
                    }
                    if ($booking.vehicleMake) {
                        html += '<tr><td class="eto-booking-details-title col-xs-4 col-sm-4">'+ etoLang('bookingField_VehicleMake') +':</td><td class="col-xs-8 col-sm-8 col-md-8">'+ $booking.vehicleMake +'</td></tr>';
                    }
                    if ($booking.vehicleModel) {
                        html += '<tr><td class="eto-booking-details-title col-xs-4 col-sm-4">'+ etoLang('bookingField_VehicleModel') +':</td><td class="col-xs-8 col-sm-8 col-md-8">'+ $booking.vehicleModel +'</td></tr>';
                    }
                    if ($booking.vehicleColour) {
                        html += '<tr><td class="eto-booking-details-title col-xs-4 col-sm-4">'+ etoLang('bookingField_VehicleColour') +':</td><td class="col-xs-8 col-sm-8 col-md-8">'+ $booking.vehicleColour +'</td></tr>';
                    }
                html += '</table>';
            }

            html += $booking.statusHistory;

            html += '</div>';
            html += '</div>';
          } else {
            html += '<p class="alert alert-warning">' + etoLang('bookingMsg_NoBooking') + '</p>';
          }

          html += '</div>';

          if (response.success) {
            html += '<div class="row eto-booking-details-buttons">' +
              '<div class="col-xs-12">' +
              '<div class="btn-group eto-booking-details-btn-new" role="group" aria-label="...">'+
                // '<a href="' + $that.runtime.baseURL + '#booking/new" class="btn btn-primary">' + etoLang('bookingButton_NewBooking') + '</a>'+
                '<div class="btn-group dropup" role="group">' +
                  '<button type="button" class="btn btn-primary btn-block dropdown-toggle" data-toggle="dropdown" aria-expanded="false">' +
                    '<span class="fa fa-angle-down"></span>'+
                    ' ' + etoLang('bookingButton_More') +
                  '</button>' +
                  '<ul class="dropdown-menu" role="menu">';
                      html += '<li><a href="' + $that.runtime.baseURL + '#booking/print" onclick="printPartOfPage(\'etoPrintContent\'); return false;">' + etoLang('bookingButton_Print') + '</a></li>';

                      if ($booking.buttonPay) {
                          html += '<li><a href="' + $that.runtime.baseURL + '#booking/pay/' + $booking.id + '">' + etoLang('bookingButton_PayNow') + '</a></li>';
                      }
                      if ($booking.buttonInvoice) {
                          html += '<li class="etoInvoiceButton"><a href="' + $that.runtime.baseURL + '#booking/invoice/' + $booking.id + '">' + etoLang('bookingButton_Invoice') + '</a></li>';
                      }
                      if ($booking.buttonEdit) {
                          html += '<li><a href="#" data-toggle="modal" data-target="#etoBookingEditModal" data-booking-id="'+ $booking.id +'" onclick="return false;">' + etoLang('bookingButton_Edit') + '</a></li>';
                      }
                      if ($booking.buttonCancel) {
                          html += '<li><a href="#" data-toggle="modal" data-target="#etoBookingCancelModal" data-booking-id="'+ $booking.id +'" onclick="return false;">' + etoLang('bookingButton_Cancel') + '</a></li>';
                      }
                      if ($booking.buttonDelete) {
                          html += '<li><a href="' + $that.runtime.baseURL + '#booking/delete/' + $booking.id + '">' + etoLang('bookingButton_Delete') + '</a></li>';
                      }
                      if ($booking.feedbackLink) {
                          html += '<li><a href="' + $booking.feedbackLink + '" target="_blank">' + etoLang('bookingButton_Feedback') + '</a></li>';
                      }
                  html += '</ul>' +
                  '</div>' +
                '</div>' +
                '<a href="javascript:void(0)" class="btn btn-primary eto-btn-booking-tracking eto-btn-booking-tracking-customer" data-eto-id="'+ $booking.id +'">'+ etoLang('booking_button_show_on_map') +'</a>' +
                // '<a href="' + $that.runtime.baseURL + '#booking/new" class="btn btn-link">' + etoLang('bookingButton_NewBooking') + '</a>' +
                '<a href="' + $that.runtime.baseURL + '#booking/list" class="btn btn-link">' + etoLang('bookingButton_Back') + '</a>' +
              '</div>' +
              '</div>';
          }

          var msg = etoLang('bookingMsgCancel')
            .replace(/\{link\}/g, '<a href="'+ ($that.runtime.config.url_terms ? $that.runtime.config.url_terms : '#') +'" target="_blank">')
            .replace(/\{\/link\}/g, '</a>');

          html += '<div id="etoBookingCancelModal" class="modal fade" role="dialog" aria-labelledby="etoBookingCancelModalTitle" aria-hidden="true">' +
  					'<div class="modal-dialog">' +
  						'<div class="modal-content">' +
  							'<div class="modal-header">' +
  								'<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>' +
  								'<h4 class="modal-title" id="etoBookingCancelModalTitle">'+ etoLang('bookingTitleCancel') +'</h4>' +
  							'</div>' +
                (parseInt($that.runtime.config.terms_enable) == 1 ? ('<div class="modal-body">'+ msg +'</div>') : '') +
  							'<div class="modal-footer">'+
                  '<button type="button" class="btn btn-danger" id="etoBookingCancelModalBtn">'+ etoLang('bookingYes') +'</button>'+
                  '<button type="button" class="btn btn-default" data-dismiss="modal">'+ etoLang('bookingNo') +'</button>'+
  							'</div>' +
  						'</div>' +
  					'</div>' +
  				'</div>';

          var msg = etoLang('bookingMsgEdit')
            .replace(/\{email\}/g, '<a href="mailto:'+ $that.runtime.config.company_email +'">'+ $that.runtime.config.company_email +'</a>')
            .replace(/\{phone\}/g, '<a href="tel:'+ $that.runtime.config.company_telephone +'">'+ $that.runtime.config.company_telephone +'</a>');

          html += '<div id="etoBookingEditModal" class="modal fade" role="dialog" aria-labelledby="etoBookingEditModalTitle" aria-hidden="true">' +
  					'<div class="modal-dialog">' +
  						'<div class="modal-content">' +
  							'<div class="modal-header">' +
  								'<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>' +
  								'<h4 class="modal-title" id="etoBookingEditModalTitle">'+ etoLang('bookingButton_Edit') +'</h4>' +
  							'</div>' +
                '<div class="modal-body">'+ msg +'</div>'+
  						'</div>' +
  					'</div>' +
  				'</div>';

          $('#' + $that.runtime.mainContainer + ' #etoUserContent').html(html);

          $('#etoBookingCancelModal').modal({
              show: false,
          })
          .on('show.bs.modal', function(event) {
              var id = $(event.relatedTarget).data('booking-id');
              $('#etoBookingCancelModalBtn').on('click', function(){
                  window.location.href = $that.runtime.baseURL + '#booking/cancel/'+ id;
                  $('#etoBookingCancelModal').modal('hide');
              });
              $that.etoScrollToTop();
          });

          $('#etoBookingEditModal').modal({
              show: false,
          })
          .on('show.bs.modal', function(event) {
              var id = $(event.relatedTarget).data('booking-id');
              $that.etoScrollToTop();
          });

          $('[title]').tooltip({
            html: true,
            // placement: 'auto'
          });

          if ($that.runtime.debug) {
            console.log(response);
          }
        },
        error: function(response) {
          $that.setMessage({
            error: ['AJAX error: Booking Details']
          });
        },
        beforeSend: function() {
          $isReady = 0;
        },
        complete: function() {
          $isReady = 1;
        }
      });
    }
  };


  this.bookingCancel = function(id) {
    var $that = this;
    var $isReady = 1;

    var baseURL = $that.runtime.baseURL;
    if( $that.runtime.config.url_customer != '' ) {
      baseURL = $that.runtime.config.url_customer;
    }

    if ($isReady) {
      jQuery.ajax({
        headers: {
          'X-CSRF-TOKEN': _token
        },
        url: $that.runtime.apiURL,
        type: 'POST',
        data: 'task=booking&action=cancel&baseURL=' + baseURL + '&id=' + id,
        dataType: 'json',
        cache: false,
        success: function(response) {
          if (response.message) {
            $that.setMessage(response.message);
          }

          if (response.success) {
            $that.init('booking/details/' + id);
          }

          if ($that.runtime.debug) {
            console.log(response);
          }
        },
        error: function(response) {
          $that.setMessage({
            error: ['AJAX error: Booking Cancel']
          });
        },
        beforeSend: function() {
          $isReady = 0;
        },
        complete: function() {
          $isReady = 1;
        }
      });
    }
  };


  this.bookingPay = function(id) {
    var $that = this;
    var $isReady = 1;

    if ($isReady) {
      jQuery.ajax({
        headers: {
          'X-CSRF-TOKEN': _token
        },
        url: $that.runtime.apiURL,
        type: 'POST',
        data: 'task=booking&action=details&id=' + id,
        dataType: 'json',
        cache: false,
        success: function(response) {
          if (response.message) {
            $that.setMessage(response.message);
          }

          if (response.success) {
            var $booking = response.booking;

            var url = EasyTaxiOffice.appPath + '/booking' + '?finishType=payment&bID='+ $booking.uniqueKey;
            if( $booking.tID ) {
                url += '&tID='+ $booking.tID;
            }
            url = encodeURI(url);

            var a = document.createElement('a');
            a.href = url;
            a.target = '_top';
            document.body.appendChild(a);
            a.click();
          }

          if ($that.runtime.debug) {
            console.log(response);
          }
        },
        error: function(response) {
          $that.setMessage({
            error: ['AJAX error: Booking Details']
          });
        },
        beforeSend: function() {
          $isReady = 0;
        },
        complete: function() {
          $isReady = 1;
        }
      });
    }
  };


  this.bookingInvoice = function(id) {
    var $that = this;
    var $isReady = 1;

    $that.panel();

    var backBtn = '<a href="' + $that.runtime.baseURL + '#booking/details/'+ id +'" class="eto-booking-go-back" title="' + etoLang('bookingButton_Back') + '" data-placement="right"><span class="fa fa-arrow-circle-left"></span></a>';
    $('.sidebar-menu li.active').removeClass('active');
    $('.eto-sidebar-menu-bookings').addClass('active');
    $('.main-header .main-page-title').html(backBtn + etoLang('bookingInvoice_Heading') + ' <span id="etoBookingRef"></span>');

    var html = '<div class="etoBookingInvoiceContainer">' +
        '<div id="etoUserContent"></div>' +
      '</div>';

    $('#' + $that.runtime.mainContainer + ' #etoPanelContent').html(html);

    if ($isReady) {
      jQuery.ajax({
        headers: {
          'X-CSRF-TOKEN': _token
        },
        url: $that.runtime.apiURL,
        type: 'POST',
        data: 'task=booking&action=invoice&id='+ id,
        dataType: 'json',
        cache: false,
        success: function(response) {
          if (response.message) {
            $that.setMessage(response.message);
          }

          if (response.success) {
            $('#' + $that.runtime.mainContainer + ' #etoBookingRef').html(' / ' + response.invoice.refNumber);

            var url = $that.runtime.apiURL;
            if (url.indexOf('?') < 0) {
              url += '?';
            } else {
              url += '&';
            }
            url += 'task=booking&action=invoice&id='+ id;
            // src="'+ url +'&embed=1"
            var html = '';

            // html += '<div class="table-responsive">';
            html += '<iframe id="invoiceTmpl" height="500" width="100%" scrolling="auto" frameborder="0"></iframe>';
            // html += '</div>';

            html += '<div class="row etoBookingInvoiceButtons">';
              html += '<div class="col-xs-12 col-sm-12 col-md-12">';
                html += '<a href="#" class="btn btn-primary" onclick="printFrame(\'invoiceTmpl\'); return false;" target="_blank">' + etoLang('bookingButton_Print') + '</a>';
                html += '<a href="' + url + '&download=1" class="btn btn-primary" target="_blank">' + etoLang('bookingButton_Download') + '</a>';
                html += '<a href="' + $that.runtime.baseURL + '#booking/details/'+ id +'" class="btn btn-link">' + etoLang('bookingButton_Back') + '</a>';
              html += '</div>';
            html += '</div>';

            html += '<script data-cfasync="false" src="'+ EasyTaxiOffice.appPath +'/assets/plugins/iframe-resizer/iframeResizer.min.js"></script>';
            html += '<script>$(\'iframe#invoiceTmpl\').iFrameResize({heightCalculationMethod: \'lowestElement\', log: false, targetOrigin: \'*\', checkOrigin: false});</script>';

            $('#' + $that.runtime.mainContainer + ' #etoUserContent').html(html);

            var iContent = response.invoice.tmpl;

            iContent += '<style>@media (max-width:500px) {\
                  body.invoice table,\
                  body.invoice thead,\
                  body.invoice tbody,\
                  body.invoice tfoot,\
                  body.invoice tr,\
                  body.invoice th,\
                  body.invoice td {\
                    display: block !important;\
                    width: 100% !important;\
                    box-sizing: border-box !important;\
                    text-align: left !important;\
                  }\
                  body.invoice .small-devices {\
                    display: block !important;\
                  }\
                  body.invoice th {\
                    display: none !important;\
                  }\
                  body.invoice table.small-devices-innertable td {\
                    padding-left: 0px !important;\
                  }\
                  body.invoice>div::after {\
                    content: "" !important;\
                    display: block !important;\
                    clear: both !important;\
                  }\
                }</style>';

            if( 0 ) {
              //#zoom=85&scrollbar=0&toolbar=0&navpanes=0
              iContent = '<html><body>'+
              '<object data="'+ url +'&embed=1" height="95%" width="100%" type="application/pdf">'+
              '<embed src="'+ url +'&embed=1" height="98%" width="100%" type="application/pdf" />'+
              '</object>'+
              '</body></html>';
            }

            var iframe = document.getElementById('invoiceTmpl');
            var inter = window.setInterval(function() {
                var iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                if( iframeDoc.readyState == 'complete' ) {
                    window.clearInterval(inter);

                    iframeDoc.open();
                    iframeDoc.write(iContent);
                    iframeDoc.close();

                    var iFrameHead = iframeDoc.getElementsByTagName('head')[0];
                    var script = document.createElement('script');
                    script.type = 'text/javascript';
                    script.src = EasyTaxiOffice.appPath +'/assets/plugins/iframe-resizer/iframeResizer.contentWindow.min.js';
                    iFrameHead.appendChild(script);
                }
             }, 100);
          }

          if ($that.runtime.debug) {
            console.log(response);
          }
        },
        error: function(response) {
          $that.setMessage({
            error: ['AJAX error: Booking Invoice']
          });
        },
        beforeSend: function() {
          $isReady = 0;
        },
        complete: function() {
          $isReady = 1;
        }
      });
    }
  };


  this.bookingEdit = function(id) {
    var $that = this;
    $that.panel();
    var html = 'Booking edit';

    $('#' + $that.runtime.mainContainer + ' #etoPanelContent').html(html);
  };


  this.bookingDelete = function(id) {
    var $that = this;
    $that.panel();
    var html = 'Booking delete';

    $('#' + $that.runtime.mainContainer + ' #etoPanelContent').html(html);
  };


  this.bookingFinish = function(id) {
    var $that = this;
    $that.panel();
    var html = 'Booking finish';

    $('#' + $that.runtime.mainContainer + ' #etoPanelContent').html(html);
  };


  this.bookingNew = function() {
    var $that = this;
    var url = EasyTaxiOffice.appPath + '/booking';

    var a = document.createElement('a');
    a.href = url;
    a.target = '_top';
    document.body.appendChild(a);
    a.click();
  };


  this.user = function() {
    var $that = this;
    var $isReady = 1;

    $that.panel();

    $('.sidebar-menu li.active').removeClass('active');
    $('.eto-sidebar-menu-profile').addClass('active');
    $('.main-header .main-page-title').html(etoLang('userProfile_Heading'));

    var html = '<div class="row">\
      <div class="col-md-8 col-lg-6 col-md-offset-2 col-lg-offset-3">\
        <div id="etoUserContent"></div>\
      </div>\
    </div>';

    $('#' + $that.runtime.mainContainer + ' #etoPanelContent').html(html);

    if ($isReady) {
      jQuery.ajax({
        headers: {
          'X-CSRF-TOKEN': _token
        },
        url: $that.runtime.apiURL,
        type: 'POST',
        data: 'task=user&action=get',
        dataType: 'json',
        cache: false,
        success: function(response) {
          if (response.message) {
            $that.setMessage(response.message);
          }

          if (response.success) {
            var $user = response.user;

            html = '<div class="widget-user-2">\
              <div class="widget-user-header clearfix">\
                <div class="widget-user-image">\
                  <img class="img-circle" src="'+ $user.avatar_path +'" alt="">\
                </div>\
                <h3 class="widget-user-username">' + $user.title + ' ' + $user.firstName + ' ' + $user.lastName + '</h3>\
                <h5 class="widget-user-desc">' + $user.createdDateSince + '</h5>\
              </div>\
              <div>\
                <ul class="list-group list-group-unbordered details-list">';
                  if ($user.email) {
                    html += '<li class="list-group-item">\
                      <span class="details-list-title">' + etoLang('userField_Email') + ':</span>\
                      <span class="details-list-value">' + $user.email + '</span>\
                    </li>';
                  }
                  if ($user.mobileNumber) {
                    html += '<li class="list-group-item">\
                      <span class="details-list-title">' + etoLang('userField_MobileNumber') + ':</span>\
                      <span class="details-list-value">' + $user.mobileNumber + '</span>\
                    </li>';
                  }
                  if ($user.telephoneNumber) {
                    html += '<li class="list-group-item">\
                      <span class="details-list-title">' + etoLang('userField_TelephoneNumber') + ':</span>\
                      <span class="details-list-value">' + $user.telephoneNumber + '</span>\
                    </li>';
                  }
                  if ($user.emergencyNumber) {
                    html += '<li class="list-group-item">\
                      <span class="details-list-title">' + etoLang('userField_EmergencyNumber') + ':</span>\
                      <span class="details-list-value">' + $user.emergencyNumber + '</span>\
                    </li>';
                  }
                  if ($user.address) {
                    html += '<li class="list-group-item">\
                      <span class="details-list-title">' + etoLang('userField_Address') + ':</span>\
                      <span class="details-list-value">' + $user.address + '</span>\
                    </li>';
                  }
                  if ($user.city) {
                    html += '<li class="list-group-item">\
                      <span class="details-list-title">' + etoLang('userField_City') + ':</span>\
                      <span class="details-list-value">' + $user.city + '</span>\
                    </li>';
                  }
                  if ($user.postcode) {
                    html += '<li class="list-group-item">\
                      <span class="details-list-title">' + etoLang('userField_Postcode') + ':</span>\
                      <span class="details-list-value">' + $user.postcode + '</span>\
                    </li>';
                  }
                  if ($user.state) {
                    html += '<li class="list-group-item">\
                      <span class="details-list-title">' + etoLang('userField_County') + ':</span>\
                      <span class="details-list-value">' + $user.state + '</span>\
                    </li>';
                  }
                  if ($user.country) {
                    html += '<li class="list-group-item">\
                      <span class="details-list-title">' + etoLang('userField_Country') + ':</span>\
                      <span class="details-list-value">' + $user.country + '</span>\
                    </li>';
                  }

                  if( $user.isCompany == 1 ) {
                    if ($user.companyName) {
                      html += '<li class="list-group-item">\
                        <span class="details-list-title">' + etoLang('userField_CompanyName') + ':</span>\
                        <span class="details-list-value">' + $user.companyName + '</span>\
                      </li>';
                    }
                    if (parseInt($that.runtime.config.customer_allow_company_number) && $user.companyNumber) {
                      html += '<li class="list-group-item">\
                        <span class="details-list-title">' + etoLang('userField_CompanyNumber') + ':</span>\
                        <span class="details-list-value">' + $user.companyNumber + '</span>\
                      </li>';
                    }
                    if (parseInt($that.runtime.config.customer_allow_company_tax_number) && $user.companyTaxNumber) {
                      html += '<li class="list-group-item">\
                        <span class="details-list-title">' + etoLang('userField_CompanyTaxNumber') + ':</span>\
                        <span class="details-list-value">' + $user.companyTaxNumber + '</span>\
                      </li>';
                    }
                    if ($user.departments !== null && $user.departments.length > 0) {
                      html += '<li class="list-group-item">\
                        <span class="details-list-title">' + etoLang('userField_Departments') + ':</span>\
                        <span class="details-list-value">' + $user.departments.join('<br>') + '</span>\
                      </li>';
                    }
                  }
                  // if ($user.createdDate) {
                  //   html += '<li class="list-group-item">\
                  //     <span class="details-list-title">' + etoLang('userField_CreatedDate') + ':</span>\
                  //     <span class="details-list-value">' + $user.createdDate + '</span>\
                  //   </li>';
                  // }
            html += '</ul>\
                <div class="row">\
                  <div class="col-sm-12">\
                    <a href="' + $that.runtime.baseURL + '#user/edit" class="btn btn-primary">\
                      <span>' + etoLang('userButton_Edit') + '</span>\
                    </a>\
                  </div>\
                </div>\
              </div>\
            </div>';

            $('#' + $that.runtime.mainContainer + ' #etoUserContent').html(html);
            $('[title]').tooltip({
              html: true,
              placement: 'auto'
            });
          }

          if ($that.runtime.debug) {
            console.log(response);
          }
        },
        error: function(response) {
          $that.setMessage({
            error: ['AJAX error: User']
          });
        },
        beforeSend: function() {
          $isReady = 0;
        },
        complete: function() {
          $isReady = 1;
        }
      });
    }
  };


  this.userEdit = function(msg) {
    var $that = this;
    var $isReady = 1;

    $that.panel();

    $('.sidebar-menu li.active').removeClass('active');
    $('.eto-sidebar-menu-profile').addClass('active');
    $('.main-header .main-page-title').html(etoLang('userEdit_Heading'));

    var html = '<div class="row">\
      <div class="col-md-10 col-lg-8 col-md-offset-1 col-lg-offset-2">\
        <div id="etoUserContent"></div>\
      </div>\
    </div>';

    $('#' + $that.runtime.mainContainer + ' #etoPanelContent').html(html);

    if (msg) {
        $that.setMessage(msg);
    }

    if ($isReady) {
      jQuery.ajax({
        headers: {
          'X-CSRF-TOKEN': _token
        },
        url: $that.runtime.apiURL,
        type: 'POST',
        data: 'task=user&action=get',
        dataType: 'json',
        cache: false,
        success: function(response) {
          if (response.message) {
            if (response.message.error.length || response.message.warning.length || response.message.success.length) {
                $that.setMessage(response.message);
            }
          }

          if (response.success) {
              var $user = response.user;

              html = '<form role="form" id="etoUserEditForm" class="form-master">' +
                  '<div class="row">' +
                  '<div class="col-xs-12 col-md-4">' +
                  '<div class="form-group">' +
                  '<label for="title">' + etoLang('userField_Title') + '</label>' +
                  // '<div class="input-group">' +
                  '<input type="text" name="title" id="title" value="' + $user.title + '" placeholder="' + etoLang('userField_Title') + '" class="form-control" tabindex="1">' +
                  // '<span class="input-group-addon"><span class="ion-ios-person-outline"></span></span>' +
                  // '</div>' +
                  '</div>' +
                  '</div>' +
                  '<div class="col-xs-12 col-md-4">' +
                  '<div class="form-group">' +
                  '<label for="firstName">' + etoLang('userField_FirstName') + '</label>' +
                  // '<div class="input-group">' +
                  '<input type="text" name="firstName" id="firstName" value="' + $user.firstName + '" placeholder="' + etoLang('userField_FirstName') + '" class="form-control" tabindex="2">' +
                  // '<span class="input-group-addon"><span class="ion-ios-person-outline"></span></span>' +
                  // '</div>' +
                  '</div>' +
                  '</div>' +
                  '<div class="col-xs-12 col-md-4">' +
                  '<div class="form-group">' +
                  '<label for="lastName">' + etoLang('userField_LastName') + '</label>' +
                  // '<div class="input-group">' +
                  '<input type="text" name="lastName" id="lastName" value="' + $user.lastName + '" placeholder="' + etoLang('userField_LastName') + '" class="form-control" tabindex="3">' +
                  // '<span class="input-group-addon"><span class="ion-ios-person-outline"></span></span>' +
                  // '</div>' +
                  '</div>' +
                  '</div>' +
                  '</div>' +
                  '<div class="row">' +
                  '<div class="col-xs-12 col-md-6">' +
                  '<div class="form-group">' +
                  '<label for="mobileNumber">' + etoLang('userField_MobileNumber') + '</label>' +
                  // '<div class="input-group">' +
                  '<input type="text" name="mobileNumber" id="mobileNumber" value="' + $user.mobileNumber + '" placeholder="' + etoLang('userField_MobileNumber') + '" class="form-control" tabindex="4">' +
                  // '<span class="input-group-addon"><span class="ion-ios-telephone-outline"></span></span>' +
                  // '</div>' +
                  '</div>' +
                  '</div>' +
                  '<div class="col-xs-12 col-md-6">' +
                  '<div class="form-group">' +
                  '<label for="telephoneNumber">' + etoLang('userField_TelephoneNumber') + '</label>' +
                  // '<div class="input-group">' +
                  '<input type="text" name="telephoneNumber" id="telephoneNumber" value="' + $user.telephoneNumber + '" placeholder="' + etoLang('userField_TelephoneNumber') + '" class="form-control" tabindex="5">' +
                  // '<span class="input-group-addon"><span class="ion-ios-telephone-outline"></span></span>' +
                  // '</div>' +
                  '</div>' +
                  '</div>' +
                  '</div>' +
                  '<div class="row">' +
                  '<div class="col-xs-12 col-md-6">' +
                  '<div class="form-group">' +
                  '<label for="emergencyNumber">' + etoLang('userField_EmergencyNumber') + '</label>' +
                  // '<div class="input-group">' +
                  '<input type="text" name="emergencyNumber" id="emergencyNumber" value="' + $user.emergencyNumber + '" placeholder="' + etoLang('userField_EmergencyNumber') + '" class="form-control" tabindex="6">' +
                  // '<span class="input-group-addon"><span class="ion-ios-telephone-outline"></span></span>' +
                  // '</div>' +
                  '</div>' +
                  '</div>' +
                  '<div class="col-xs-12 col-md-6">' +
                  '<div class="form-group">' +
                  '<label for="address">' + etoLang('userField_Address') + '</label>' +
                  // '<div class="input-group">' +
                  '<input type="text" name="address" id="address" value="' + $user.address + '" placeholder="' + etoLang('userField_Address') + '" class="form-control" tabindex="8">' +
                  // '<span class="input-group-addon"><span class="ion-ios-location-outline"></span></span>' +
                  // '</div>' +
                  '</div>' +
                  '</div>' +
                  '</div>' +
                  '<div class="row">' +
                  '<div class="col-xs-12 col-md-6">' +
                  '<div class="form-group">' +
                  '<label for="postcode">' + etoLang('userField_Postcode') + '</label>' +
                  // '<div class="input-group">' +
                  '<input type="text" name="postcode" id="postcode" value="' + $user.postcode + '" placeholder="' + etoLang('userField_Postcode') + '" class="form-control" tabindex="9">' +
                  // '<span class="input-group-addon"><span class="ion-ios-location-outline"></span></span>' +
                  // '</div>' +
                  '</div>' +
                  '</div>' +
                  '<div class="col-xs-12 col-md-6">' +
                  '<div class="form-group">' +
                  '<label for="city">' + etoLang('userField_City') + '</label>' +
                  // '<div class="input-group">' +
                  '<input type="text" name="city" id="city" value="' + $user.city + '" placeholder="' + etoLang('userField_City') + '" class="form-control" tabindex="10">' +
                  // '<span class="input-group-addon"><span class="ion-ios-location-outline"></span></span>' +
                  // '</div>' +
                  '</div>' +
                  '</div>' +
                  '</div>' +
                  '<div class="row">' +
                  '<div class="col-xs-12 col-md-6">' +
                  '<div class="form-group">' +
                  '<label for="state">' + etoLang('userField_County') + '</label>' +
                  // '<div class="input-group">' +
                  '<input type="text" name="state" id="state" value="' + $user.state + '" placeholder="' + etoLang('userField_County') + '" class="form-control" tabindex="11">' +
                  // '<span class="input-group-addon"><span class="ion-ios-location-outline"></span></span>' +
                  // '</div>' +
                  '</div>' +
                  '</div>' +
                  '<div class="col-xs-12 col-md-6">' +
                  '<div class="form-group">' +
                  '<label for="country">' + etoLang('userField_Country') + '</label>' +
                  // '<div class="input-group">' +
                  '<input type="text" name="country" id="country" value="' + $user.country + '" placeholder="' + etoLang('userField_Country') + '" class="form-control" tabindex="12">' +
                  // '<span class="input-group-addon"><span class="ion-ios-location-outline"></span></span>' +
                  // '</div>' +
                  '</div>' +
                  '</div>' +
                  '</div>' +
                  '<div class="row">' +
                  '<div class="col-xs-12 col-md-12">' +
                  '<div class="form-group">' +
                  '<label for="email">' + etoLang('userField_Email') + '</label>' +
                  // '<div class="input-group">' +
                  '<input type="email" name="email" id="email" value="' + $user.email + '" placeholder="' + etoLang('userField_Email') + '" class="form-control" tabindex="13">' +
                  // '<span class="input-group-addon"><span class="ion-ios-email-outline"></span></span>' +
                  // '</div>' +
                  '</div>' +
                  '</div>' +
                  '</div>' +
                  '<div class="row">' +
                  '<div class="col-xs-12 col-md-6">' +
                  '<div class="form-group">' +
                  '<label for="password">' + etoLang('userField_Password') + '</label>' +
                  '<input type="password" name="password" id="password" autocomplete="new-password" placeholder="' + etoLang('userField_Password') + '" class="form-control" tabindex="14">' +
                  '</div>' +
                  '</div>' +
                  '<div class="col-xs-12 col-md-6">' +
                  '<div class="form-group">' +
                  '<label for="passwordConfirmation">' + etoLang('userField_ConfirmPassword') + '</label>' +
                  '<input type="password" name="passwordConfirmation" id="passwordConfirmation" autocomplete="new-password" placeholder="' + etoLang('userField_ConfirmPassword') + '" class="form-control" tabindex="15">' +
                  '</div>' +
                  '</div>' +
                  '</div>';

              if ($user.isCompany == 1) {
                  var cRegNumberAllow = parseInt($that.runtime.config.customer_allow_company_number);
                  var cTaxNumberAllow = parseInt($that.runtime.config.customer_allow_company_tax_number);
                  var cRegNumberCls = !cRegNumberAllow ? 'hidden' : '';
                  var cTaxNumberCls = !cTaxNumberAllow ? 'hidden' : '';

                  if (!cRegNumberAllow && !cTaxNumberAllow) {
                      var colSpan = 'col-md-12';
                  } else if ((!cRegNumberAllow && cTaxNumberAllow) || (cRegNumberAllow && !cTaxNumberAllow)) {
                      var colSpan = 'col-md-12';
                  } else {
                      var colSpan = 'col-md-6';
                  }

                  html += '<div class="row">' +
                      '<div class="col-xs-12">' +
                      '<div class="form-group">' +
                      '<label for="companyName">' + etoLang('userField_CompanyName') + '</label>' +
                      // '<div class="input-group">' +
                      '<input type="text" name="companyName" id="companyName" value="' + $user.companyName + '" placeholder="' + etoLang('userField_CompanyName') + '" class="form-control" tabindex="7">' +
                      // '<span class="input-group-addon"><span class="ion-ios-home-outline"></span></span>' +
                      // '</div>' +
                      '</div>' +
                      '</div>' +
                      '</div>' +
                      '<div class="row">' +
                      '<div class="col-xs-12 ' + colSpan + ' ' + cRegNumberCls + '">' +
                      '<div class="form-group">' +
                      '<label for="companyNumber">' + etoLang('userField_CompanyNumber') + '</label>' +
                      // '<div class="input-group">' +
                      '<input type="text" name="companyNumber" id="companyNumber" value="' + $user.companyNumber + '" placeholder="' + etoLang('userField_CompanyNumber') + '" class="form-control" tabindex="7">' +
                      // '<span class="input-group-addon"><span class="ion-ios-home-outline"></span></span>' +
                      // '</div>' +
                      '</div>' +
                      '</div>' +
                      '<div class="col-xs-12 ' + colSpan + ' ' + cTaxNumberCls + '">' +
                      '<div class="form-group">' +
                      '<label for="companyTaxNumber">' + etoLang('userField_CompanyTaxNumber') + '</label>' +
                      // '<div class="input-group">' +
                      '<input type="text" name="companyTaxNumber" id="companyTaxNumber" value="' + $user.companyTaxNumber + '" placeholder="' + etoLang('userField_CompanyTaxNumber') + '" class="form-control" tabindex="7">' +
                      // '<span class="input-group-addon"><span class="ion-ios-home-outline"></span></span>' +
                      // '</div>' +
                      '</div>' +
                      '</div>' +
                      '</div>' +
                      '<div class="row">' +
                      '<div class="form-group">' +
                      '<div class="col-xs-12 col-md-12 eto-title-department">' + etoLang('userField_Departments') + '</div>' +
                      '<div class="eto-departments-list clearfix">';
                  if (typeof $user.departments == 'object' && $user.departments.length > 0) {
                      $.each($user.departments, function (k, v) {
                          html += '<div class="field-department col-xs-12 col-md-6">\
                                      <input name="departments[]" id="department-' + k + '" placeholder="Department" class="form-control" value="' + v + '">\
                                      <i class="fa fa fa-trash eto-delete-department"></i>\
                                    </div>';
                      });
                  }
                  html += '</div>' +
                      '<div class="col-xs-12 col-md-12">' +
                      '<button type="button" class="btn btn-sm btn-default eto-add-department">' + etoLang('userButton_AddDepartment') + '</button>' +
                      '</div>' +
                      '</div>' +
                      '</div>';
              }

              html += '<div class="row">';

              var avatarCol = 12;

              if ($user.avatar != null) {
                  html += '<div class="col-xs-12 col-md-2">' +
                      '<img src="' + $user.avatar_path + '" class="img-circle" alt="" style="max-width:100px; max-height:100px; margin-bottom:20px;">' +
                      '</div>';
                  avatarCol = 10;
              }
              html += '<div class="col-xs-12 col-md-'+avatarCol+'">';

              if ($user.avatar != null) {
                  html += '<div class="form-group placeholder-disabled" style="margin-left: 20px">' +
                      '<div class="checkbox">' +
                        '<input id="avatar_delete" name="avatar_delete" type="checkbox" value="1"><label for="avatar_delete">' + etoLang('userField_DeleteAvatar') + '</label>' +
                      '</div>' +
                      '</div>';
              }
              html += '<div class="form-group placeholder-visible">' +
                          '<label for="avatar">' + etoLang('userField_Avatar') + '</label>'+
                          '<input type="file" name="avatar" id="avatar" class="form-control">' +
                      '</div>' +
                  '</div>' +
              '</div>'+
              '<div>' +
                '<input type="submit" id="saveButton" value="' + etoLang('userButton_Save') + '" class="btn btn-primary" tabindex="16">' +
                '<a href="' + $that.runtime.baseURL + '#user" id="cancelButton" class="btn btn-link" tabindex="17">' + etoLang('userButton_Cancel') + '</a>' +
              '</div>' +
              '</form>';

            $('#' + $that.runtime.mainContainer + ' #etoUserContent').html(html);
            $('[title]').tooltip({
                html: true,
                placement: 'auto'
            });

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

            var $form = $('#etoUserEditForm');
            var $isValid = 0;
            var $isReady = 1;

            $form.on('init.field.fv', function(e, data) {
              var $parent = data.element.parents('.form-group');
              var $icon = $parent.find('.form-control-feedback[data-fv-icon-for="' + data.field + '"]');

              $icon.on('click.clearing', function() {
                if ($icon.hasClass('ion-ios-close-empty')) {
                  data.fv.resetField(data.element);
                }
              });
            });

            $form.formValidation({
              framework: 'bootstrap',
              icon: {
                valid: 'ion-ios-checkmark-empty',
                invalid: 'ion-ios-close-empty',
                validating: 'ion-ios-refresh-empty'
              },
              excluded: ':disabled',
              fields: {
                // title: {
                //   validators: {
                //     notEmpty: {
                //       message: etoLang('userMsg_TitleRequired')
                //     }
                //   }
                // },
                firstName: {
                  validators: {
                    notEmpty: {
                      message: etoLang('userMsg_FirstNameRequired')
                    }
                  }
                },
                lastName: {
                  validators: {
                    notEmpty: {
                      message: etoLang('userMsg_LastNameRequired')
                    }
                  }
                },
                /*
                mobileNumber: {
                	validators: {
                		notEmpty: {
                			message: etoLang('userMsg_MobileNumberRequired')
                		}
                	}
                },
                telephoneNumber: {
                	validators: {
                		notEmpty: {
                			message: etoLang('userMsg_TelephoneNumberRequired')
                		}
                	}
                },
                emergencyNumber: {
                	validators: {
                		notEmpty: {
                			message: etoLang('userMsg_EmergencyNumberRequired')
                		}
                	}
                },
                */
                companyName: {
                    enabled: false,
                    validators: {
                        notEmpty: {
                            message: etoLang('userMsg_CompanyNameRequired'),
                        }
                    }
                },
                companyNumber: {
                    enabled: false,
                    validators: {
                        notEmpty: {
                            message: etoLang('userMsg_CompanyNumberRequired')
                        }
                    }
                },
                companyTaxNumber: {
                    enabled: false,
                    validators: {
                        notEmpty: {
                            message: etoLang('userMsg_CompanyTaxNumberRequired')
                        }
                    }
                },
                address: {
                  validators: {
                    notEmpty: {
                      message: etoLang('userMsg_AddressRequired')
                    }
                  }
                },
                postcode: {
                  validators: {
                    notEmpty: {
                      message: etoLang('userMsg_PostcodeRequired')
                    }
                  }
                },
                city: {
                  validators: {
                    notEmpty: {
                      message: etoLang('userMsg_CityRequired')
                    }
                  }
                },
                state: {
                  validators: {
                    notEmpty: {
                      message: etoLang('userMsg_CountyRequired')
                    }
                  }
                },
                country: {
                  validators: {
                    notEmpty: {
                      message: etoLang('userMsg_CountryRequired')
                    }
                  }
                },
                email: {
                  validators: {
                    notEmpty: {
                      message: etoLang('userMsg_EmailRequired')
                    },
                    emailAddress: {
                      message: etoLang('userMsg_EmailInvalid')
                    }
                  }
                },
                password: {
                  validators: {
                    stringLength: {
                      min: $that.runtime.config.password_length_min,
                      max: $that.runtime.config.password_length_max,
                      message: etoLang('userMsg_PasswordLength')
                        .replace(/\{passwordLengthMin\}/g, $that.runtime.config.password_length_min)
                        .replace(/\{passwordLengthMax\}/g, $that.runtime.config.password_length_max)
                    },
                    different: {
                      field: 'email',
                      message: etoLang('userMsg_PasswordSameAsEmail')
                    }
                  }
                },
                passwordConfirmation: {
                  validators: {
                    identical: {
                      field: 'password',
                      message: etoLang('userMsg_ConfirmPasswordNotEqual')
                    }
                  }
                }
              }
            });

            if( $user.isCompany == 1 ) {
                $form.formValidation('enableFieldValidators', 'companyName', true);

                var cRegNumberAllow = parseInt($that.runtime.config.customer_allow_company_number);
                var cRegNumberRequire = parseInt($that.runtime.config.customer_require_company_number);
                var cTaxNumberAllow = parseInt($that.runtime.config.customer_allow_company_tax_number);
                var cTaxNumberRequire = parseInt($that.runtime.config.customer_require_company_tax_number);

                if (cRegNumberAllow && cRegNumberRequire) {
                    $form.formValidation('enableFieldValidators', 'companyNumber', true);
                }
                if (cTaxNumberAllow && cTaxNumberRequire) {
                    $form.formValidation('enableFieldValidators', 'companyTaxNumber', true);
                }

                $form.on('click', '.eto-add-department', function() {
                    var uuid = ETO.uuidHTML();

                    $form.find('.eto-departments-list').append('<div class="field-department col-xs-12 col-md-6">\
                              <input name="departments[]" id="department-'+uuid+'" placeholder="Department" class="form-control">\
                              <i class="fa fa fa-trash eto-delete-department"></i>\
                            </div>');
                })
                .on('click', '.eto-delete-department', function() {
                    $(this).closest('.field-department').remove();
                });
            }

            $form.on('err.field.fv', function(e, data) {
              if (data.fv.getInvalidFields().length > 0) {
                data.fv.disableSubmitButtons(true);
                $isValid = 0;
              } else {
                data.fv.disableSubmitButtons(false);
                $isValid = 1;
              }
            });

            $form.on('success.field.fv', function(e, data) {
              if (data.fv.getInvalidFields().length > 0) {
                data.fv.disableSubmitButtons(true);
                $isValid = 0;
              } else {
                data.fv.disableSubmitButtons(false);
                $isValid = 1;
              }
            });

            $form.submit(function(event) {
              if ($isValid && $isReady) {
                  var params = {
                      task: 'user',
                      action: 'save'
                  };
                  ETO.ajaxWithFileUpload($form, $that.runtime.apiURL, params, {
                      success: function(response) {
                          if (response.success) {
                              //$that.init('user/edit');
                              setTimeout(function() {
                                  $that.userEdit(response.message);
                              }, 0);
                          }
                          else if (response.message) {
                              $that.setMessage(response.message);
                              $form.data('formValidation').disableSubmitButtons(false);
                          }

                          if ($that.runtime.debug) {
                              console.log(response);
                          }
                      },
                      error: function(response) {
                          $that.setMessage({
                              error: ['AJAX error: User Save']
                          });
                      },
                      beforeSend: function() {
                          $isReady = 0;
                      },
                      complete: function() {
                          $isReady = 1;
                          $that.etoScrollToTop();
                      }
                  });
              }
              event.preventDefault();
            });

            $form.find('#cancelButton').click(function(event) {
              $that.init('user');
              event.preventDefault();
            });
          }

          if ($that.runtime.debug) {
            console.log(response);
          }
        },
        error: function(response) {
          $that.setMessage({
            error: ['AJAX error: User Edit']
          });
        },
        beforeSend: function() {
          $isReady = 0;
        },
        complete: function() {
          $isReady = 1;
        }
      });
    }
  };


  this.register = function() {
    var $that = this;

    if ($that.runtime.config.register_enable == 0) {
      $that.setMessage({
        warning: [etoLang('userMsg_RegisterNotAvailable')]
      });
      return false;
    }

    var cRegNumberAllow = parseInt($that.runtime.config.customer_allow_company_number);
    var cTaxNumberAllow = parseInt($that.runtime.config.customer_allow_company_tax_number);
    var cRegNumberCls = !cRegNumberAllow ? 'hidden' : '';
    var cTaxNumberCls = !cTaxNumberAllow ? 'hidden' : '';

    if (cRegNumberAllow && cTaxNumberAllow) {
       var colSpan = 'col-sm-6';
    }
    else {
       var colSpan = 'col-sm-12';
    }

    var html = '<div class="row">' +
      '<div class="col-xs-12 col-sm-8 col-md-6 col-sm-offset-2 col-md-offset-3">' +
      '<form role="form" id="etoRegisterForm">' +
      '<h3>' + etoLang('userRegister_Heading') + '</h3>' +

      '<div class="row">' +
          '<div class="col-xs-12">' +
            '<div style="margin:10px 0px 5px 0;">' +
                  '<div class="radio" style="float:left; margin:0px 20px 10px 0;">' +
                    '<label><input type="radio" name="profileType" value="private" checked="checked"><span class="cr"><i class="cr-icon ion-record"></i></span>' + etoLang('userField_ProfileTypePrivate') + '</label>' +
                  '</div>' +
                  '<div class="radio" style="float:left; margin:0px 0px 10px 0;">' +
                    '<label><input type="radio" name="profileType" value="company"><span class="cr"><i class="cr-icon ion-record"></i></span>' + etoLang('userField_ProfileTypeCompany') + '</label>' +
                  '</div>' +
                  '<div class="clearfix"></div>' +
              '</div>' +
          '</div>' +
      '</div>' +

      '<div class="company-container">' +
          '<div class="form-group" title="' + etoLang('userField_CompanyName') + '">' +
              '<div class="input-group">' +
                  '<span class="input-group-addon"><span class="ion-ios-home-outline"></span></span>' +
                  '<input type="text" name="companyName" id="companyName" placeholder="' + etoLang('userField_CompanyName') + '" class="form-control" tabindex="4">' +
              '</div>' +
          '</div>' +
          '<div class="row">' +
              '<div class="col-xs-12 '+ colSpan +' '+ cRegNumberCls +'">' +
                  '<div class="form-group" title="' + etoLang('userField_CompanyNumber') + '">' +
                      '<div class="input-group">' +
                          '<span class="input-group-addon"><span class="ion-ios-home-outline"></span></span>' +
                          '<input type="text" name="companyNumber" id="companyNumber" placeholder="' + etoLang('userField_CompanyNumber') + '" class="form-control" tabindex="5">' +
                      '</div>' +
                  '</div>' +
              '</div>' +
              '<div class="col-xs-12 '+ colSpan +' '+ cTaxNumberCls +'">' +
                  '<div class="form-group" title="' + etoLang('userField_CompanyTaxNumber') + '">' +
                      '<div class="input-group">' +
                          '<span class="input-group-addon"><span class="ion-ios-home-outline"></span></span>' +
                          '<input type="text" name="companyTaxNumber" id="companyTaxNumber" placeholder="' + etoLang('userField_CompanyTaxNumber') + '" class="form-control" tabindex="6">' +
                      '</div>' +
                  '</div>' +
              '</div>' +
          '</div>' +
      '</div>' +

      '<div class="row">' +
          '<div class="col-xs-12 col-sm-6">' +
              '<div class="form-group" title="' + etoLang('userField_FirstName') + '">' +
                  '<div class="input-group">' +
                      '<span class="input-group-addon"><span class="ion-ios-person-outline"></span></span>' +
                      '<input type="text" name="firstName" id="firstName" placeholder="' + etoLang('userField_FirstName') + '" class="form-control" tabindex="1">' +
                  '</div>' +
              '</div>' +
          '</div>' +
          '<div class="col-xs-12 col-sm-6">' +
              '<div class="form-group" title="' + etoLang('userField_LastName') + '">' +
                  '<div class="input-group">' +
                      '<span class="input-group-addon"><span class="ion-ios-person-outline"></span></span>' +
                      '<input type="text" name="lastName" id="lastName" placeholder="' + etoLang('userField_LastName') + '" class="form-control" tabindex="2">' +
                  '</div>' +
              '</div>' +
          '</div>' +
      '</div>' +
      '<div class="form-group" title="' + etoLang('userField_Email') + '">' +
          '<div class="input-group">' +
              '<span class="input-group-addon"><span class="ion-ios-email-outline"></span></span>' +
              '<input type="email" name="email" id="email" placeholder="' + etoLang('userField_Email') + '" class="form-control" tabindex="3">' +
          '</div>' +
      '</div>' +

      '<div class="row">' +
      '<div class="col-xs-12 col-sm-6">' +
          '<div class="form-group" title="' + etoLang('userField_Password') + '">' +
              '<div class="input-group">' +
                '<span class="input-group-addon" title="' + etoLang('userField_Password') + '"><span class="ion-ios-locked-outline"></span></span>' +
                '<input type="password" name="password" id="password" placeholder="' + etoLang('userField_Password') + '" class="form-control" tabindex="8">' +
              '</div>' +
          '</div>' +
      '</div>' +
      '<div class="col-xs-12 col-sm-6">' +
          '<div class="form-group" title="' + etoLang('userField_ConfirmPassword') + '">' +
              '<div class="input-group">' +
                '<span class="input-group-addon"><span class="ion-ios-locked-outline"></span></span>' +
                '<input type="password" name="passwordConfirmation" id="passwordConfirmation" placeholder="' + etoLang('userField_ConfirmPassword') + '" class="form-control" tabindex="9">' +
              '</div>' +
          '</div>' +
      '</div>' +
      '</div>' +

      (parseInt($that.runtime.config.terms_enable) == 1 ? ('<div class="form-group form-group-terms">' +
        '<div class="checkbox">'+
            '<label for="terms">' +
                '<input type="checkbox" name="terms" id="terms" value="terms" tabindex="10" /><span class="cr"><i class="cr-icon ion-ios-checkmark-empty"></i></span> ' +
                '' + etoLang('userField_Agree') + ' <a href="' + $that.runtime.config.url_terms + '" target="_blank">' + etoLang('userField_TermsAndConditions') + '</a>' +
            '</label>' +
        '</div>' +
      '</div>') : '') +

      '<div class="row">' +
          '<div class="col-xs-12 col-sm-4 col-md-3">' +
            '<input type="submit" id="registerButton" value="' + etoLang('userButton_Register') + '" class="btn btn-primary btn-block" tabindex="7">' +
          '</div>' +
          '<div class="col-xs-12 col-sm-8 col-md-9">' +
            '<a href="' + $that.runtime.baseURL + '#login" id="loginButton" class="btn btn-link" tabindex="8">' + etoLang('userButton_Login') + '</a>' +
          '</div>' +
      '</div>' +
      '</form>' +
      '</div>' +
      '</div>';

    $('#' + $that.runtime.mainContainer).html(html);
    $('[title]').tooltip({
      html: true,
      placement: 'auto'
    });

    var $form = $('#etoRegisterForm');
    var $isValid = 0;
    var $isReady = 1;

    function initFormValidation(isCompany) {
        var cRegNumberAllow = parseInt($that.runtime.config.customer_allow_company_number);
        var cRegNumberRequire = parseInt($that.runtime.config.customer_require_company_number);
        var cTaxNumberAllow = parseInt($that.runtime.config.customer_allow_company_tax_number);
        var cTaxNumberRequire = parseInt($that.runtime.config.customer_require_company_tax_number);

        $isValid = 0;
        $form.formValidation('destroy');
        $form.off('err.form.fv');
        $form.off('success.form.fv');

        $form.formValidation({
          framework: 'bootstrap',
          icon: {
            valid: 'ion-ios-checkmark-empty',
            invalid: 'ion-ios-close-empty',
            validating: 'ion-ios-refresh-empty'
          },
          excluded: ':disabled',
          fields: {
            firstName: {
              validators: {
                notEmpty: {
                  message: etoLang('userMsg_FirstNameRequired')
                }
              }
            },
            lastName: {
              validators: {
                notEmpty: {
                  message: etoLang('userMsg_LastNameRequired')
                }
              }
            },
            email: {
              validators: {
                notEmpty: {
                  message: etoLang('userMsg_EmailRequired')
                },
                emailAddress: {
                  message: etoLang('userMsg_EmailInvalid')
                }
              }
            },
            companyName: {
                enabled: isCompany,
                validators: {
                    callback: {
                        message: etoLang('userMsg_CompanyNameRequired'),
                        callback: function(value, validator, $field) {
                            var profileType = $form.find('[name="profileType"]:checked').val();
                            return (profileType !== 'company') ? true : (value !== '');
                        }
                    }
                }
            },
            companyNumber: {
                enabled: isCompany ? (cRegNumberAllow && cRegNumberRequire ? true : false) : false,
                validators: {
                    callback: {
                        message: etoLang('userMsg_CompanyNumberRequired'),
                        callback: function(value, validator, $field) {
                            var profileType = $form.find('[name="profileType"]:checked').val();
                            return (profileType !== 'company') ? true : (value !== '');
                        }
                    }
                }
            },
            companyTaxNumber: {
                enabled: isCompany ? (cTaxNumberAllow && cTaxNumberRequire ? true : false) : false,
                validators: {
                    callback: {
                        message: etoLang('userMsg_CompanyTaxNumberRequired'),
                        callback: function(value, validator, $field) {
                            var profileType = $form.find('[name="profileType"]:checked').val();
                            return (profileType !== 'company') ? true : (value !== '');
                        }
                    }
                }
            },
            password: {
              validators: {
                notEmpty: {
                  message: etoLang('userMsg_PasswordRequired')
                },
                stringLength: {
                  min: $that.runtime.config.password_length_min,
                  max: $that.runtime.config.password_length_max,
                  message: etoLang('userMsg_PasswordLength')
                    .replace(/\{passwordLengthMin\}/g, $that.runtime.config.password_length_min)
                    .replace(/\{passwordLengthMax\}/g, $that.runtime.config.password_length_max)
                },
                different: {
                  field: 'email',
                  message: etoLang('userMsg_PasswordSameAsEmail')
                }
              }
            },
            passwordConfirmation: {
              validators: {
                notEmpty: {
                  message: etoLang('userMsg_ConfirmPasswordRequired')
                },
                identical: {
                  field: 'password',
                  message: etoLang('userMsg_ConfirmPasswordNotEqual')
                }
              }
            },
            terms: {
              enabled: parseInt($that.runtime.config.terms_enable) == 1 ? true : false,
              validators: {
                notEmpty: {
                  message: etoLang('userMsg_TermsAndConditionsRequired')
                }
              }
            }
          }
        });

        $form.on('init.field.fv', function(e, data) {
            var $parent = data.element.parents('.form-group');
            var $icon = $parent.find('.form-control-feedback[data-fv-icon-for="' + data.field + '"]');

            $icon.on('click.clearing', function() {
              if ($icon.hasClass('ion-ios-close-empty')) {
                data.fv.resetField(data.element);
              }
            });
        });

        $form.on('err.form.fv', function() {
            $isValid = 0;
        });

        $form.on('success.form.fv', function() {
            $isValid = 1;
        });
    }

    initFormValidation(false);

    $form.on('change', '[name="profileType"]', function(e) {
        $('#' + $that.runtime.messageContainer).html('');
        var profileType = $form.find('[name="profileType"]:checked').val();
        if( profileType === 'company' ) {
            initFormValidation(true);
        }
        else {
            initFormValidation(false);
        }
    });

    // Profile type
    function profileType() {
        if( $('input[name="profileType"]:checked').val() == 'company' ) {
            $('.company-container').show();
        } else {
            $('.company-container').hide();
        }
    }

    profileType();

    $('input[name="profileType"]').change(function() {
        profileType();
    });

    var baseURL = $that.runtime.baseURL;
    if( $that.runtime.config.url_customer != '' ) {
      baseURL = $that.runtime.config.url_customer;
    }

    $form.submit(function(event) {
      if ($isValid && $isReady) {
        jQuery.ajax({
          headers: {
            'X-CSRF-TOKEN': _token
          },
          url: $that.runtime.apiURL,
          type: 'POST',
          data: 'task=user&action=register&baseURL=' + baseURL + '&' + $($form).serialize(),
          dataType: 'json',
          cache: false,
          success: function(response) {
            if (response.message) {
              $that.setMessage(response.message);
            }

            if (response.success) {
              // gTag start - Register
              if ($that.runtime.config.google_analytics_tracking_id) {
                  gtag('event', 'register', {
                    'event_label': 'User registered',
                    'event_category': 'user',
                  });
              }

              if ($that.runtime.config.google_adwords_conversion_id &&
                  $that.runtime.config.google_adwords_conversions &&
                  $that.runtime.config.google_adwords_conversions.user_register) {
                gtag('event', 'conversion', {
                  'send_to': $that.runtime.config.google_adwords_conversion_id +'/'+ $that.runtime.config.google_adwords_conversions.user_register,
                });
              }
              // gTag end - Register

              $that.init('login');
            }

            if ($that.runtime.debug) {
              console.log(response);
            }
          },
          error: function(response) {
            $that.setMessage({
              error: ['AJAX error: Register']
            });
          },
          beforeSend: function() {
            $isReady = 0;
          },
          complete: function() {
            $isReady = 1;
          }
        });
      }
      event.preventDefault();
    });

    $form.find('#loginButton').click(function(event) {
      $that.init('login');
      event.preventDefault();
    });
  };


  this.registerActivation = function(token) {
    var $that = this;
    var $isReady = 1;

    var baseURL = $that.runtime.baseURL;
    if( $that.runtime.config.url_customer != '' ) {
      baseURL = $that.runtime.config.url_customer;
    }

    if ($isReady) {
      jQuery.ajax({
        headers: {
          'X-CSRF-TOKEN': _token
        },
        url: $that.runtime.apiURL,
        type: 'POST',
        data: 'task=user&action=registerActivation&baseURL=' + baseURL + '&token=' + token,
        dataType: 'json',
        cache: false,
        success: function(response) {
          if (response.message) {
            $that.setMessage(response.message);
          }

          if (response.success) {
            // gTag start - Activation
            if ($that.runtime.config.google_analytics_tracking_id) {
                gtag('event', 'activation', {
                  'event_label': 'User activated',
                  'event_category': 'user',
                });
            }

            if ($that.runtime.config.google_adwords_conversion_id &&
                $that.runtime.config.google_adwords_conversions &&
                $that.runtime.config.google_adwords_conversions.user_activation) {
              gtag('event', 'conversion', {
                'send_to': $that.runtime.config.google_adwords_conversion_id +'/'+ $that.runtime.config.google_adwords_conversions.user_activation,
              });
            }
            // gTag end - Activation

            $that.init('login');
          }

          if ($that.runtime.debug) {
            console.log(response);
          }
        },
        error: function(response) {
          $that.setMessage({
            error: ['AJAX error: Register Activation']
          });
        },
        beforeSend: function() {
          $isReady = 0;
        },
        complete: function() {
          $isReady = 1;
        }
      });
    }
  };


  this.registerResend = function(email) {
    var $that = this;
    var $isReady = 1;

    var baseURL = $that.runtime.baseURL;
    if( $that.runtime.config.url_customer != '' ) {
      baseURL = $that.runtime.config.url_customer;
    }

    if ($isReady) {
      jQuery.ajax({
        headers: {
          'X-CSRF-TOKEN': _token
        },
        url: $that.runtime.apiURL,
        type: 'POST',
        data: 'task=user&action=registerResend&baseURL=' + baseURL + '&email=' + email,
        dataType: 'json',
        cache: false,
        success: function(response) {
          if (response.message) {
            $that.setMessage(response.message);
          }

          if (response.success) {
            $that.init('login');
          }

          if ($that.runtime.debug) {
            console.log(response);
          }
        },
        error: function(response) {
          $that.setMessage({
            error: ['AJAX error: Register Resend']
          });
        },
        beforeSend: function() {
          $isReady = 0;
        },
        complete: function() {
          $isReady = 1;
        }
      });
    }
  };


  this.login = function() {
    var $that = this;

    if ($that.runtime.config.login_enable == 0) {
      $that.setMessage({
        warning: [etoLang('userMsg_LoginNotAvailable')]
      });
      return false;
    }

    var benefitsHtml = '';

    if ($that.runtime.config.booking_member_benefits_enable == 1 && $that.runtime.config.booking_member_benefits != '') {
      var benefitList = $that.runtime.config.booking_member_benefits.split('\n');
      var benefits = '';

      $.each(benefitList, function(benefitKey, benefitValue) {
          benefitValue = $.trim(benefitValue);
          if(benefitValue) {
              benefits += '<li>'+ benefitValue +'</li>';
          }
      });

      if(benefits) {
          benefits = '<ul>'+ benefits +'</ul>';
      }

      benefitsHtml = '<div class="eto-v2-section eto-v2-section-benefits">';
        benefitsHtml += '<div class="eto-v2-section-label">'+ etoLang('bookingMemberBenefits') +':</div>';
        benefitsHtml += '<div class="eto-v2-benefits-list clearfix">'+ benefits +'</div>';
      benefitsHtml += '</div>';
    }

    var html = '<div class="row">' +
      '<div class="col-xs-12 col-sm-6 col-sm-offset-3">' +
      '<form role="form" id="etoLoginForm">' +
      '<h3>' + etoLang('userLogin_Heading') + '</h3>' +
      '<div class="form-group" title="' + etoLang('userField_Email') + '">' +
          '<div class="input-group">' +
            '<span class="input-group-addon"><span class="ion-ios-email-outline"></span></span>' +
            '<input type="email" name="email" id="email_login" placeholder="' + etoLang('userField_Email') + '" class="form-control" tabindex="1">' +
          '</div>' +
      '</div>' +
      '<div class="form-group" title="' + etoLang('userField_Password') + '">' +
          '<div class="input-group">' +
            '<span class="input-group-addon"><span class="ion-ios-locked-outline"></span></span>' +
            '<input type="password" name="password" id="password_login" placeholder="' + etoLang('userField_Password') + '" class="form-control" tabindex="2">' +
          '</div>' +
      '</div>' +
      '<div class="row">' +
      '<div class="col-xs-12 col-sm-4 col-md-3">' +
      '<input type="submit" id="loginButton" class="btn btn-primary btn-block" value="' + etoLang('userButton_Login') + '" tabindex="3">' +
      '</div>' +
      '<div class="col-xs-12 col-sm-8 col-md-9">' +
      '<a href="' + $that.runtime.baseURL + '#register" id="registerButton" class="btn btn-link" tabindex="4">' + etoLang('userButton_Register') + '</a>' +
      '<a href="' + $that.runtime.baseURL + '#password" id="passwordButton" class="btn btn-link" tabindex="5">' + etoLang('userButton_LostPassword') + '</a>' +
      '</div>' +
      '</div>' +
      benefitsHtml +
      '</form>' +
      '</div>' +
      '</div>';

    $('#' + $that.runtime.mainContainer).html(html);
    $('[title]').tooltip({
      html: true,
      placement: 'auto'
    });

    var $form = $('#etoLoginForm');
    var $isValid = 0;
    var $isReady = 1;

    $form.on('init.field.fv', function(e, data) {
      var $parent = data.element.parents('.form-group');
      var $icon = $parent.find('.form-control-feedback[data-fv-icon-for="' + data.field + '"]');

      $icon.on('click.clearing', function() {
        if ($icon.hasClass('ion-ios-close-empty')) {
          data.fv.resetField(data.element);
        }
      });
    });

    $form.formValidation({
      framework: 'bootstrap',
      icon: {
        valid: 'ion-ios-checkmark-empty',
        invalid: 'ion-ios-close-empty',
        validating: 'ion-ios-refresh-empty'
      },
      excluded: ':disabled',
      fields: {
        email: {
          validators: {
            notEmpty: {
              message: etoLang('userMsg_EmailRequired')
            },
            emailAddress: {
              message: etoLang('userMsg_EmailInvalid')
            }
          }
        },
        password: {
          validators: {
            notEmpty: {
              message: etoLang('userMsg_PasswordRequired')
            }
          }
        }
      }
    });

    $form.on('success.field.fv', function(e, data) {
      if (data.fv.getInvalidFields().length > 0) {
        data.fv.disableSubmitButtons(true);
        $isValid = 0;
      } else {
        $isValid = 1;
      }
    });

    $form.submit(function(event) {
      if ($isValid && $isReady) {
        jQuery.ajax({
          headers: {
            'X-CSRF-TOKEN': _token
          },
          url: $that.runtime.apiURL,
          type: 'POST',
          data: 'task=user&action=login&' + $($form).serialize(),
          dataType: 'json',
          cache: false,
          success: function(response) {
            if (response.message) {
              $that.setMessage(response.message);
            }

            if (response.success) {
              // gTag start - Login
              if ($that.runtime.config.google_analytics_tracking_id) {
                  gtag('event', 'login', {
                    'event_label': 'User logged in',
                    'event_category': 'user',
                  });
              }

              if ($that.runtime.config.google_adwords_conversion_id &&
                  $that.runtime.config.google_adwords_conversions &&
                  $that.runtime.config.google_adwords_conversions.user_login) {
                gtag('event', 'conversion', {
                  'send_to': $that.runtime.config.google_adwords_conversion_id +'/'+ $that.runtime.config.google_adwords_conversions.user_login,
                });
              }
              // gTag end - Login

              $that.init('booking/list'); // dashboard
            }

            if ($that.runtime.debug) {
              console.log(response);
            }
          },
          error: function(response) {
            $that.setMessage({
              error: ['AJAX error: Login']
            });
          },
          beforeSend: function() {
            $isReady = 0;
          },
          complete: function() {
            $isReady = 1;
          }
        });
      }
      event.preventDefault();
    });

    $form.find('#registerButton').click(function(event) {
      $that.init('register');
      event.preventDefault();
    });

    $form.find('#passwordButton').click(function(event) {
      $that.init('password');
      event.preventDefault();
    });
  };


  this.password = function() {
    var $that = this;

    var html = '<div class="row">' +
      '<div class="col-xs-12 col-sm-6 col-sm-offset-3">' +
      '<form role="form" id="etoPasswordForm">' +
      '<h3>' + etoLang('userLostPassword_Heading') + '</h3>' +
      '<div class="form-group" title="' + etoLang('userField_Email') + '">' +
          '<div class="input-group">' +
            '<span class="input-group-addon"><span class="ion-ios-email-outline"></span></span>' +
            '<input type="email" name="email" id="email" placeholder="' + etoLang('userField_Email') + '" class="form-control" tabindex="1">' +
          '</div>' +
      '</div>' +
      '<div class="row">' +
      '<div class="col-xs-12 col-sm-4 col-md-3">' +
      '<input type="submit" id="resetButton" value="' + etoLang('userButton_Reset') + '" class="btn btn-primary btn-block" tabindex="2">' +
      '</div>' +
      '<div class="col-xs-12 col-sm-8 col-md-9">' +
      '<a href="' + $that.runtime.baseURL + '#login" id="loginButton" class="btn btn-link" tabindex="3">' + etoLang('userButton_Login') + '</a>' +
      '</div>' +
      '</div>' +
      '</form>' +
      '</div>' +
      '</div>';

    $('#' + $that.runtime.mainContainer).html(html);
    $('[title]').tooltip({
      html: true,
      placement: 'auto'
    });

    var $form = $('#etoPasswordForm');
    var $isValid = 0;
    var $isReady = 1;

    $form.on('init.field.fv', function(e, data) {
      var $parent = data.element.parents('.form-group');
      var $icon = $parent.find('.form-control-feedback[data-fv-icon-for="' + data.field + '"]');

      $icon.on('click.clearing', function() {
        if ($icon.hasClass('ion-ios-close-empty')) {
          data.fv.resetField(data.element);
        }
      });
    });

    $form.formValidation({
      framework: 'bootstrap',
      icon: {
        valid: 'ion-ios-checkmark-empty',
        invalid: 'ion-ios-close-empty',
        validating: 'ion-ios-refresh-empty'
      },
      excluded: ':disabled',
      fields: {
        email: {
          validators: {
            notEmpty: {
              message: etoLang('userMsg_EmailRequired')
            },
            emailAddress: {
              message: etoLang('userMsg_EmailInvalid')
            }
          }
        }
      }
    });

    $form.on('success.field.fv', function(e, data) {
      if (data.fv.getInvalidFields().length > 0) {
        data.fv.disableSubmitButtons(true);
        $isValid = 0;
      } else {
        $isValid = 1;
      }
    });

    var baseURL = $that.runtime.baseURL;
    if( $that.runtime.config.url_customer != '' ) {
      baseURL = $that.runtime.config.url_customer;
    }

    $form.submit(function(event) {
      if ($isValid && $isReady) {
        jQuery.ajax({
          headers: {
            'X-CSRF-TOKEN': _token
          },
          url: $that.runtime.apiURL,
          type: 'POST',
          data: 'task=user&action=password&baseURL=' + baseURL + '&' + $($form).serialize(),
          dataType: 'json',
          cache: false,
          success: function(response) {
            if (response.message) {
              $that.setMessage(response.message);
            }

            if (response.success) {
              $that.init('password/new');
            }

            if ($that.runtime.debug) {
              console.log(response);
            }
          },
          error: function(response) {
            $that.setMessage({
              error: ['AJAX error: Password']
            });
          },
          beforeSend: function() {
            $isReady = 0;
          },
          complete: function() {
            $isReady = 1;
          }
        });
      }
      event.preventDefault();
    });

    $form.find('#loginButton').click(function(event) {
      $that.init('login');
      event.preventDefault();
    });
  };


  this.passwordNew = function(token) {
    var $that = this;

    var html = '<div class="row">' +
      '<div class="col-xs-12 col-sm-6 col-sm-offset-3">' +
      '<form role="form" id="etoPasswordNewForm">' +
      '<h3>' + etoLang('userNewPassword_Heading') + '</h3>' +
      '<div class="form-group" title="' + etoLang('userField_Token') + '">' +
          '<div class="input-group">' +
            '<span class="input-group-addon"><span class="ion-ios-gear-outline"></span></span>' +
            '<input type="text" name="token" id="token" placeholder="' + etoLang('userField_Token') + '" class="form-control" tabindex="1">' +
          '</div>' +
      '</div>' +
      '<div class="form-group" title="' + etoLang('userField_Password') + '">' +
          '<div class="input-group">' +
            '<span class="input-group-addon"><span class="ion-ios-locked-outline"></span></span>' +
            '<input type="password" name="password" id="password" placeholder="' + etoLang('userField_Password') + '" class="form-control" tabindex="2">' +
          '</div>' +
      '</div>' +
      '<div class="form-group" title="' + etoLang('userField_ConfirmPassword') + '">' +
          '<div class="input-group">' +
            '<span class="input-group-addon"><span class="ion-ios-locked-outline"></span></span>' +
            '<input type="password" name="passwordConfirmation" id="passwordConfirmation" placeholder="' + etoLang('userField_ConfirmPassword') + '" class="form-control" tabindex="3">' +
          '</div>' +
      '</div>' +
      '<div class="row">' +
      '<div class="col-xs-12 col-sm-4 col-md-3">' +
      '<input type="submit" id="resetButton" value="' + etoLang('userButton_Update') + '" class="btn btn-primary btn-block" tabindex="4">' +
      '</div>' +
      '<div class="col-xs-12 col-sm-8 col-md-9">' +
      '<a href="' + $that.runtime.baseURL + '#login" id="loginButton" class="btn btn-link" tabindex="5">' + etoLang('userButton_Login') + '</a>' +
      '</div>' +
      '</div>' +
      '</form>' +
      '</div>' +
      '</div>';

    $('#' + $that.runtime.mainContainer).html(html);
    $('[title]').tooltip({
      html: true,
      placement: 'auto'
    });

    $('#' + $that.runtime.mainContainer + ' #etoPasswordNewForm #token').val(token);

    var $form = $('#etoPasswordNewForm');
    var $isValid = 0;
    var $isReady = 1;

    $form.on('init.field.fv', function(e, data) {
      var $parent = data.element.parents('.form-group');
      var $icon = $parent.find('.form-control-feedback[data-fv-icon-for="' + data.field + '"]');

      $icon.on('click.clearing', function() {
        if ($icon.hasClass('ion-ios-close-empty')) {
          data.fv.resetField(data.element);
        }
      });
    });

    $form.formValidation({
      framework: 'bootstrap',
      icon: {
        valid: 'ion-ios-checkmark-empty',
        invalid: 'ion-ios-close-empty',
        validating: 'ion-ios-refresh-empty'
      },
      excluded: ':disabled',
      fields: {
        token: {
          validators: {
            notEmpty: {
              message: etoLang('userMsg_TokenRequired')
            }
          }
        },
        password: {
          validators: {
            notEmpty: {
              message: etoLang('userMsg_PasswordRequired')
            },
            stringLength: {
              min: $that.runtime.config.password_length_min,
              max: $that.runtime.config.password_length_max,
              message: etoLang('userMsg_PasswordLength')
                .replace(/\{passwordLengthMin\}/g, $that.runtime.config.password_length_min)
                .replace(/\{passwordLengthMax\}/g, $that.runtime.config.password_length_max)
            }
          }
        },
        passwordConfirmation: {
          validators: {
            notEmpty: {
              message: etoLang('userMsg_ConfirmPasswordRequired')
            },
            identical: {
              field: 'password',
              message: etoLang('userMsg_ConfirmPasswordNotEqual')
            }
          }
        }
      }
    });

    $form.on('success.field.fv', function(e, data) {
      if (data.fv.getInvalidFields().length > 0) {
        data.fv.disableSubmitButtons(true);
        $isValid = 0;
      } else {
        $isValid = 1;
      }
    });

    $form.submit(function(event) {
      if ($isValid && $isReady) {
        jQuery.ajax({
          headers: {
            'X-CSRF-TOKEN': _token
          },
          url: $that.runtime.apiURL,
          type: 'POST',
          data: 'task=user&action=passwordNew&' + $($form).serialize(),
          dataType: 'json',
          cache: false,
          success: function(response) {
            if (response.message) {
              $that.setMessage(response.message);
            }

            if (response.success) {
              $that.init('login');
            }

            if ($that.runtime.debug) {
              console.log(response);
            }
          },
          error: function(response) {
            $that.setMessage({
              error: ['AJAX error: Password New']
            });
          },
          beforeSend: function() {
            $isReady = 0;
          },
          complete: function() {
            $isReady = 1;
          }
        });
      }
      event.preventDefault();
    });

    $form.find('#loginButton').click(function(event) {
      $that.init('login');
      event.preventDefault();
    });
  };


  this.logout = function() {
    var $that = this;
    var $isReady = 1;

    if ($isReady) {
      jQuery.ajax({
        headers: {
          'X-CSRF-TOKEN': _token
        },
        url: $that.runtime.apiURL,
        type: 'POST',
        data: 'task=user&action=logout',
        dataType: 'json',
        cache: false,
        success: function(response) {
          if (response.message) {
            $that.setMessage(response.message);
          }

          if (response.success) {
            // gTag start - Logout
            if ($that.runtime.config.google_analytics_tracking_id) {
              gtag('event', 'logout', {
                'event_label': 'User logged out',
                'event_category': 'user',
              });
            }

            if ($that.runtime.config.google_adwords_conversion_id &&
                $that.runtime.config.google_adwords_conversions &&
                $that.runtime.config.google_adwords_conversions.user_logout) {
              gtag('event', 'conversion', {
                'send_to': $that.runtime.config.google_adwords_conversion_id +'/'+ $that.runtime.config.google_adwords_conversions.user_logout,
              });
            }
            // gTag end - Logout

            $that.init('login');
          }

          if ($that.runtime.debug) {
            console.log(response);
          }
        },
        error: function(response) {
          $that.setMessage({
            error: ['AJAX error: Logout']
          });
        },
        beforeSend: function() {
          $isReady = 0;
        },
        complete: function() {
          $isReady = 1;
        }
      });
    }
  };
}
