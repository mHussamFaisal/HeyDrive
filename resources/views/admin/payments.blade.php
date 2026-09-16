@extends('admin.index')

@section('title', 'Payment Methods')


@section('subheader')
  <link rel="stylesheet" href="{{ asset_url('plugins','data-tables/dataTables.bootstrap.css') }}">
  <link rel="stylesheet" href="{{ asset_url('plugins','select2/select2.min.css') }}">
  <link rel="stylesheet" href="{{ asset_url('plugins','bootstrap-touchspin/jquery.bootstrap-touchspin.min.css') }}">
  <link rel="stylesheet" href="{{ asset_url('plugins','form-validation/formValidation.min.css') }}">
@endsection


@section('subcontent')
  <div class="pageContainer" id="payment-methods">
    <div class="pageTitle">

      <a href="#" class="btn btn-default btn-sm pull-right btnFilters" title="Search">
        <i class="fa fa-search"></i>
      </a>
        @permission('admin.payments.create')
      <a href="#" onclick="updateRecord(); return false;" class="btn btn-success btn-sm pull-right btnAdd">
        <i class="fa fa-plus"></i> <span>Add new</span>
      </a>
        @endpermission
      <h3>Payment Methods</h3>
      <div style="margin: 10px 0 0 0; color: #f69f1e; margin-bottom:30px;">Need help with configuring payment method? Click <a href="{{ config('app.docs_url') }}/getting-started/payment-integration/" target="_blank" style="text-decoration: underline;">here</a> for more info.</div>
    </div>
    <div class="pageFilters pageFiltersHide">

      <form method="post" class="form-inline">
        <a href="#" class="pull-right btnClose" title="Close" onclick="$('.pageFilters').toggle(); return false;">
          <i class="fa fa-times"></i>
        </a>
        <div class="form-group field-keywords">
          <label for="filter-keywords">Keywords</label>
          <input type="text" name="filter-keywords" id="filter-keywords" class="form-control" placeholder="Keywords">
        </div>
        <div class="form-group field-btn-search">
          <button type="button" class="btn btn-default btnSearch" title=""><i class="fa fa-search"></i> <span>Search</span></button>
        </div>
        <div class="form-group field-btn-reset">
          <button type="button" class="btn btn-link btnReset" title=""><i class="fa fa-times"></i> <span>Reset</span></button>
        </div>
      </form>

    </div>
    <div class="pageContent">

      <table class="table table-hover" id="dtable" style="width:100%;"></table>

      <div id="dmodal" class="modal fade" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
              <h4 class="modal-title"></h4>
            </div>
            <div class="modal-body"></div>
            <div class="modal-overlay"><i class="fa fa-refresh fa-spin"></i></div>
          </div>
        </div>
      </div>

    </div>
  </div>
@endsection


@section('subfooter')
  <script src="{{ asset_url('plugins','moment/moment.min.js') }}"></script>
  <script src="{{ asset_url('plugins','data-tables/jquery.dataTables.min.js') }}"></script>
  <script src="{{ asset_url('plugins','data-tables/dataTables.bootstrap.min.js') }}"></script>
  <script src="{{ asset_url('plugins','select2/select2.full.min.js') }}"></script>
  <script src="{{ asset_url('plugins','bootstrap-touchspin/jquery.bootstrap-touchspin.min.js') }}"></script>
  <script src="{{ asset_url('plugins','form-validation/formValidation.min.js') }}"></script>
  <script src="{{ asset_url('plugins','form-validation/formValidation-bootstrap.min.js') }}"></script>
  <script src="{{ asset_url('plugins','jquery-form/jquery.form.min.js') }}"></script>

  <script>
  // Filter
  function filterTable(isDelete, isPageReset) {
      var values = $('.pageFilters form').serialize();
      var isDelete = isDelete ? true : false;
      var isPageReset = isPageReset ? true : false;
      var dTable = $('#dtable').DataTable();
      var dInfo = dTable.page.info();
      var dRows = dTable.rows({page:'current'}).nodes();
      // console.log(dRows.any(), dRows.length, isDelete, dInfo);

      dTable.search(values);
      if (dInfo.page > 0 && dRows.length - 1 <= 0 && isDelete === true) {
          dTable.page('previous');
      }
      dTable.draw(isPageReset);
  }

  // Delete
  function deleteRecord(id, method) {
    var html = '';
    var title = '';

    title = 'Delete payment method - <span style="text-transform:capitalize;">'+ method +'</span>';
    html = '<p style="margin-bottom:20px;">Are you sure you want to permanently delete this data?</p>\
            <button type="button" class="btn btn-danger btnConfirm" title="Delete"><i class="fa fa-trash"></i> Yes, delete</button>\
            <button type="button" class="btn btn-default btnCancel" title="Cancel">Cancel</button>\
            <span id="statusMsg"></span>';

    $('#dmodal .modal-title').html(title);
    $('#dmodal .modal-body').html(html);
    $('#dmodal').modal({
      show: true
    });

    $('#dmodal .btnConfirm').on('click', function() {
      $.ajax({
        headers : {
          'X-CSRF-TOKEN': EasyTaxiOffice.csrfToken
        },
        url: EasyTaxiOffice.appPath +'/etov2?apiType=backend',
        type: 'POST',
        dataType: 'json',
        cache: false,
        data: {
          task: 'payment',
          action: 'destroy',
          id: id
        },
        success: function(response) {
          if(response.success) {
            $('#dmodal #statusMsg').html('');
            $('#dmodal').modal('hide');
            filterTable(true);
          }
          else {
            $('#dmodal #statusMsg').html('<span class="text-danger"><i class="fa fa-exclamation-circle"></i> The data could not be deleted</span>');
          }
        },
        error: function(response) {
          $('#dmodal #statusMsg').html('<span class="text-danger"><i class="fa fa-exclamation-circle"></i> An error occurred while processing your request</span>');
        },
        beforeSend: function() {
          $('#dmodal #statusMsg').html('<i class="fa fa-spinner fa-spin"></i> Loading');
          $('#dmodal .modal-overlay').show();
        },
        complete: function() {
          $('#dmodal .modal-overlay').hide();
        }
      });
    });

    $('#dmodal .btnCancel').on('click', function() {
      $('#dmodal').modal('hide');
    });
  }

    // Load params
    function loadParams(method, params, vehicle_list) {
        // console.log(method, params);

        $('.payment-method-top-info').prepend('<div style="margin:10px 0 25px 0; color:#f69f1e;">Need help with configuring payment method? Click <a href="{{ config('app.docs_url') }}/getting-started/payment-integration/" target="_blank" style="text-decoration: underline;">here</a> for more info.</div>');

        var fields = {};
        if( !method ) { method = ''; }
        if( !params ) { params = {}; }
        if( !vehicle_list ) { vehicle_list = []; }

        switch (method) {
            case 'paypal':

                fields.test_mode = {
                    type: 'select',
                    name: 'Environment',
                    help: '<b>Live</b> - When you ready to go live.<br><b>Test</b> - When you want to do some testing.',
                    options: {
                        '0': 'Live',
                        '1': 'Test'
                    },
                    value: 0
                };
                fields.test_amount = {
                    name: 'Test amount',
                    class: 'touchspin',
                    attr: 'data-bts-step="0.01" data-bts-decimals="2" data-bts-min="0"',
                    value: 0,
                    help: 'Amount used for testing. It will override payment price. If it is set to zero, then booking price will be used.',
                    pclass: 'field-is-test-mode'
                };
                fields.paypal_email = {
                    name: 'PayPal Business Email',
                    value: ''
                };
                fields.currency_code = {
                    name: 'Currency code',
                    value: 'GBP'
                };
                fields.language_code = {
                    name: 'Language code',
                    help: 'Force language in payment provider page',
                    value: 'auto'
                };
                fields.deposit = {
                    type: 'select',
                    name: 'Deposit',
                    help: 'If you disable this option then no deposit will be taken for this payment method.',
                    options: {
                        '0': 'Disabled',
                        '1': 'Enabled'
                    },
                    value: 1
                };

            break;
            case 'epdq':

                fields.test_mode = {
                    type: 'select',
                    name: 'Environment',
                    help: '<b>Live</b> - When you ready to go live.<br><b>Test</b> - When you want to do some testing.',
                    options: {
                        '0': 'Live',
                        '1': 'Test'
                    },
                    value: 0
                };
                fields.test_amount = {
                    name: 'Test amount',
                    class: 'touchspin',
                    attr: 'data-bts-step="0.01" data-bts-decimals="2" data-bts-min="0"',
                    value: 0,
                    help: 'Amount used for testing. It will override payment price. If it is set to zero, then booking price will be used.',
                    pclass: 'field-is-test-mode'
                };
                fields.pspid = {
                    name: 'PSPID',
                    value: ''
                };
                fields.pass_phrase = {
                    type: 'password',
                    name: 'Pass phrase',
                    value: ''
                };
                fields.paramvar = {
                    name: 'Param var',
                    value: ''
                };
                fields.operation_mode = {
                    type: 'select',
                    name: 'Operation mode',
                    options: {
                        'SAL': 'Sale',
                        'RES': 'Authorisation'
                    },
                    value: 'SAL'
                };
                fields.currency_code = {
                    name: 'Currency code',
                    value: 'GBP'
                };
                fields.language_code = {
                    name: 'Language code',
                    help: 'Force language in payment provider page',
                    value: 'auto'
                };
                fields.deposit = {
                    type: 'select',
                    name: 'Deposit',
                    help: 'If you disable this option then no deposit will be taken for this payment method.',
                    options: {
                        '0': 'Disabled',
                        '1': 'Enabled'
                    },
                    value: 1
                };

            break;
            case 'payzone':

                fields.test_mode = {
                    type: 'select',
                    name: 'Environment',
                    help: '<b>Live</b> - When you ready to go live.<br><b>Test</b> - When you want to do some testing.',
                    options: {
                        '0': 'Live',
                        '1': 'Test'
                    },
                    value: 0
                };
                fields.test_amount = {
                    name: 'Test amount',
                    class: 'touchspin',
                    attr: 'data-bts-step="0.01" data-bts-decimals="2" data-bts-min="0"',
                    value: 0,
                    help: 'Amount used for testing. It will override payment price. If it is set to zero, then booking price will be used.',
                    pclass: 'field-is-test-mode'
                };
                fields.pre_shared_key = {
                    type: 'password',
                    name: 'Pre shared key',
                    value: '',
                    help: '<b>Where do I find my pre shared key?</b><br>Your pre shared key is available from <b>Account Admin</b> -> <b>Account Settings</b>. You can also reset the Pre shared key using this screen.'
                };
                fields.merchant_id = {
                    name: 'Merchant ID',
                    value: '',
                    help: '<b>Where do I find my Merchant ID?</b><br>Log into the MMS, under <b>Account Admin</b> -> <b>Gateway Account Admin</b>. The Merchant ID’s<br>are available in the <b>Gateway Account</b>: dropdown - in the format PAYZON-1234567.'
                };
                fields.password = {
                    type: 'password',
                    name: 'Password',
                    value: '',
                    help: '<b>Where do I find my merchant password</b><br>You can reset your Merchant Password by logging into the MMS, and going to <b>Account Admin</b> -> <b>Gateway Account Admin</b>. Select the relevant account in the <b>Gateway Account</b>: dropdown and ensure you tick ‘<b>Immediately Expire Old Password</b>’. Click change password and your password will be updated. <br><br>Please note: Each gateway account has a separate password and will need to be reset individually.'
                };
                fields.operation_mode = {
                    type: 'select',
                    name: 'Operation mode',
                    options: {
                        'SALE': 'Sale',
                        'PREAUTH': 'Authorization'
                    },
                    value: 'SALE'
                };
                fields.country_code = {
                    name: 'Country code',
                    value: '826'
                };
                fields.currency_code = {
                    name: 'Currency code',
                    value: '826'
                };
                fields.language_code = {
                    name: 'Language code',
                    help: 'Force language in payment provider page',
                    value: 'auto'
                };
                fields.deposit = {
                    type: 'select',
                    name: 'Deposit',
                    help: 'If you disable this option then no deposit will be taken for this payment method.',
                    options: {
                        '0': 'Disabled',
                        '1': 'Enabled'
                    },
                    value: 1
                };

            break;
            case 'cardsave':

                fields.test_mode = {
                    type: 'select',
                    name: 'Environment',
                    help: '<b>Live</b> - When you ready to go live.<br><b>Test</b> - When you want to do some testing.',
                    options: {
                        '0': 'Live',
                        '1': 'Test'
                    },
                    value: 0
                };
                fields.test_amount = {
                    name: 'Test amount',
                    class: 'touchspin',
                    attr: 'data-bts-step="0.01" data-bts-decimals="2" data-bts-min="0"',
                    value: 0,
                    help: 'Amount used for testing. It will override payment price. If it is set to zero, then booking price will be used.',
                    pclass: 'field-is-test-mode'
                };
                fields.pre_shared_key = {
                    type: 'password',
                    name: 'Pre shared key',
                    value: ''
                };
                fields.merchant_id = {
                    name: 'Merchant ID',
                    value: ''
                };
                fields.password = {
                    type: 'password',
                    name: 'Password',
                    value: ''
                };
                fields.operation_mode = {
                    type: 'select',
                    name: 'Operation mode',
                    options: {
                        'SALE': 'Sale'
                    },
                    value: 'SALE'
                };
                fields.country_code = {
                    name: 'Country code',
                    value: '826'
                };
                fields.currency_code = {
                    name: 'Currency code',
                    value: '826'
                };
                fields.language_code = {
                    name: 'Language code',
                    help: 'Force language in payment provider page',
                    value: 'auto'
                };
                fields.deposit = {
                    type: 'select',
                    name: 'Deposit',
                    help: 'If you disable this option then no deposit will be taken for this payment method.',
                    options: {
                        '0': 'Disabled',
                        '1': 'Enabled'
                    },
                    value: 1
                };

            break;
            case 'redsys':

                fields.test_mode = {
                    type: 'select',
                    name: 'Environment',
                    help: '<b>Live</b> - When you ready to go live.<br><b>Test</b> - When you want to do some testing.',
                    options: {
                        '0': 'Live',
                        '1': 'Test'
                    },
                    value: 0
                };
                fields.test_amount = {
                    name: 'Test amount',
                    class: 'touchspin',
                    attr: 'data-bts-step="0.01" data-bts-decimals="2" data-bts-min="0"',
                    value: 0,
                    help: 'Amount used for testing. It will override payment price. If it is set to zero, then booking price will be used.',
                    pclass: 'field-is-test-mode'
                };
                fields.merchant_id = {
                    name: 'Merchant ID',
                    value: ''
                };
                fields.terminal_id = {
                    name: 'Terminal ID',
                    value: '001'
                };
                fields.encryption_key = {
                    type: 'password',
                    name: 'Encryption key',
                    value: ''
                };
                fields.signature_version = {
                    name: 'Signature version',
                    value: 'HMAC_SHA256_V1'
                };
                fields.operation_mode = {
                    type: 'select',
                    name: 'Operation mode',
                    options: {
                        '0': 'Sale'
                    },
                    value: '0'
                };
                fields.currency_code = {
                    name: 'Currency code',
                    value: '978'
                };
                fields.language_code = {
                    name: 'Language code',
                    help: 'Force language in payment provider page',
                    value: 'auto'
                };
                fields.deposit = {
                    type: 'select',
                    name: 'Deposit',
                    help: 'If you disable this option then no deposit will be taken for this payment method.',
                    options: {
                        '0': 'Disabled',
                        '1': 'Enabled'
                    },
                    value: 1
                };

            break;
            case 'worldpay':

                fields.test_mode = {
                    type: 'select',
                    name: 'Environment',
                    help: '<b>Live</b> - When you ready to go live.<br><b>Test</b> - When you want to do some testing.',
                    options: {
                        '0': 'Live',
                        '1': 'Test'
                    },
                    value: 0
                };
                fields.test_amount = {
                    name: 'Test amount',
                    class: 'touchspin',
                    attr: 'data-bts-step="0.01" data-bts-decimals="2" data-bts-min="0"',
                    value: 0,
                    help: 'Amount used for testing. It will override payment price. If it is set to zero, then booking price will be used.',
                    pclass: 'field-is-test-mode'
                };
                fields.inst_id = {
                    name: 'Installation ID',
                    value: ''
                };
                fields.md5_secret = {
                    type: 'password',
                    name: 'MD5 secret',
                    value: ''
                };
                fields.signature_fields = {
                    name: 'Signature fields',
                    value: 'instId:amount:currency:cartId'
                };
                fields.callback_protocol = {
                    type: 'select',
                    name: 'Payment response protocol',
                    help: '<b>Auto</b> - currently used.<br><b>HTTP</b> - insecure connection.<br><b>HTTPS</b> - secure connection.',
                    options: {
                        '0': 'Auto',
                        '1': 'HTTP',
                        '2': 'HTTPS'
                    },
                    value: 0
                };
                fields.currency_code = {
                    name: 'Currency code',
                    value: 'GBP'
                };
                fields.language_code = {
                    name: 'Language code',
                    help: 'Force language in payment provider page',
                    value: 'auto'
                };
                fields.deposit = {
                    type: 'select',
                    name: 'Deposit',
                    help: 'If you disable this option then no deposit will be taken for this payment method.',
                    options: {
                        '0': 'Disabled',
                        '1': 'Enabled'
                    },
                    value: 1
                };

            break;
            case 'stripe_ideal':

                fields.test_mode = {
                    type: 'select',
                    name: 'Environment',
                    help: '<b>Live</b> - When you ready to go live.<br><b>Test</b> - When you want to do some testing.',
                    options: {
                        '0': 'Live',
                        '1': 'Test'
                    },
                    value: 0
                };
                fields.test_amount = {
                    name: 'Test amount',
                    class: 'touchspin',
                    attr: 'data-bts-step="0.01" data-bts-decimals="2" data-bts-min="0"',
                    value: 0,
                    help: 'Amount used for testing. It will override payment price. If it is set to zero, then booking price will be used.',
                    pclass: 'field-is-test-mode'
                };
                fields.pk_live = {
                    type: 'password',
                    name: 'Live publishable key',
                    value: '',
                    pclass: 'field-is-live-mode'
                };
                fields.sk_live = {
                    type: 'password',
                    name: 'Live secret key',
                    value: '',
                    pclass: 'field-is-live-mode'
                };
                fields.pk_test = {
                    type: 'password',
                    name: 'Test publishable key',
                    value: '',
                    pclass: 'field-is-test-mode'
                };
                fields.sk_test = {
                    type: 'password',
                    name: 'Test secret key',
                    value: '',
                    pclass: 'field-is-test-mode'
                };
                fields.deposit = {
                    type: 'select',
                    name: 'Deposit',
                    help: 'If you disable this option then no deposit will be taken for this payment method.',
                    options: {
                        '0': 'Disabled',
                        '1': 'Enabled'
                    },
                    value: 1
                };

            break;
            case 'stripe':

                $('.payment-method-top-info').append('<div class="field-is-not-sca-mode" style="margin-bottom:25px; color:#da4646;">Strong Customer Authentication (SCA), a new rule coming into effect on September 14, 2019, as part of PSD2 regulation in Europe, will require changes to how your European customers authenticate online payments. Card payments will require a different user experience, namely 3D Secure, in order to meet SCA requirements. Transactions that don’t follow the new authentication guidelines may be declined by your customers’ banks. <a href=\'https://stripe.com/docs/strong-customer-authentication\' target=\'_blank\'>Read more</a><br><br>You can activate SCA anytime using the option below.</div>');

                fields.test_mode = {
                    type: 'select',
                    name: 'Environment',
                    help: '<b>Live</b> - When you ready to go live.<br><b>Test</b> - When you want to do some testing.',
                    options: {
                        '0': 'Live',
                        '1': 'Test'
                    },
                    value: 0
                };
                fields.test_amount = {
                    name: 'Test amount',
                    class: 'touchspin',
                    attr: 'data-bts-step="0.01" data-bts-decimals="2" data-bts-min="0"',
                    value: 0,
                    help: 'Amount used for testing. It will override payment price. If it is set to zero, then booking price will be used.',
                    pclass: 'field-is-test-mode'
                };
                fields.pk_live = {
                    type: 'password',
                    name: 'Live publishable key',
                    value: '',
                    pclass: 'field-is-live-mode'
                };
                fields.sk_live = {
                    type: 'password',
                    name: 'Live secret key',
                    value: '',
                    pclass: 'field-is-live-mode'
                };
                fields.pk_test = {
                    type: 'password',
                    name: 'Test publishable key',
                    value: '',
                    pclass: 'field-is-test-mode'
                };
                fields.sk_test = {
                    type: 'password',
                    name: 'Test secret key',
                    value: '',
                    pclass: 'field-is-test-mode'
                };
                // fields.sca_sub_info = {
                //     type: 'html',
                //     name: '<div class="field-is-not-sca-mode" style="margin-bottom:25px; color:#da4646;">Strong Customer Authentication (SCA), a new rule coming into effect on September 14, 2019, as part of PSD2 regulation in Europe, will require changes to how your European customers authenticate online payments. Card payments will require a different user experience, namely 3D Secure, in order to meet SCA requirements. Transactions that don’t follow the new authentication guidelines may be declined by your customers’ banks. <a href=\'https://stripe.com/docs/strong-customer-authentication\' target=\'_blank\'>Read more</a><br><br>You can activate SCA anytime using the option below.</div>',
                // };
                fields.sca_mode = {
                    type: 'select',
                    name: 'Strong Customer Authentication (SCA)',
                    help: 'Here you can learn more about SCA <a href=\'https://stripe.com/docs/strong-customer-authentication\' target=\'_blank\'>https://stripe.com/docs/strong-customer-authentication</a>',
                    options: {
                        '0': 'Disabled',
                        '1': 'Enabled'
                    },
                    value: 0
                };
                fields.container_sca_mode_start = {
                    type: 'container_start',
                    pclass: 'field-is-sca-mode'
                };
                fields.container_enable_webhook_start = {
                    type: 'container_start',
                    pclass: 'hidden'
                };
                fields.live_enable_webhook = {
                    type: 'select',
                    name: 'Live webhook',
                    help: 'Webhook is needed for the system to receive payment status notification.',
                    options: {
                        '0': 'Disabled',
                        '1': 'Enabled'
                    },
                    value: 1,
                    pclass: 'field-is-live-mode'
                };
                fields.container_live_enable_webhook_start = {
                    type: 'container_start',
                    pclass: 'field-is-live_enable_webhook'
                };
                fields.live_webhook_id = {
                    type: 'text',
                    name: 'Live webhook id',
                    value: '',
                    pclass: 'field-is-live-mode'
                };
                fields.live_webhook_secret = {
                    type: 'password',
                    name: 'Live webhook key',
                    value: '',
                    pclass: 'field-is-live-mode'
                };
                fields.container_live_enable_webhook_end = {
                    type: 'container_end',
                };
                fields.test_enable_webhook = {
                    type: 'select',
                    name: 'Test webhook',
                    help: 'Webhook is needed for the system to receive payment status notification.',
                    options: {
                        '0': 'Disabled',
                        '1': 'Enabled'
                    },
                    value: 1,
                    pclass: 'field-is-test-mode'
                };
                fields.container_test_enable_webhook_start = {
                    type: 'container_start',
                    pclass: 'field-is-test_enable_webhook'
                };
                fields.test_webhook_id = {
                    type: 'text',
                    name: 'Test webhook id',
                    value: '',
                    pclass: 'field-is-test-mode'
                };
                fields.test_webhook_secret = {
                    type: 'password',
                    name: 'Test webhook key',
                    value: '',
                    pclass: 'field-is-test-mode'
                };
                fields.container_test_enable_webhook_end = {
                    type: 'container_end',
                };
                fields.container_enable_webhook_end = {
                    type: 'container_end',
                };
                fields.container_sca_mode_end = {
                    type: 'container_end',
                };

                // https://support.stripe.com/questions/what-is-avs
                fields.zip_code = {
                    type: 'select',
                    name: 'Zip code check',
                    help: 'For each card payment where we have an address supplied, Stripe checks to make sure that the ZIP/postal code matches the address for the customer on file with their bank.',
                    options: {
                        'false': 'Disabled',
                        'true': 'Enabled'
                    },
                    value: 'true',
                    pclass: 'field-is-not-sca-mode'
                };
                fields.three_d_secure = {
                    type: 'select',
                    name: '3D Secure',
                    help: '<b>When to use 3D Secure</b><br>3D Secure provides a layer of protection against fraudulent payments that is supported by most card issuers. Unlike regular card payments, 3D Secure requires cardholders to complete an additional verification step with the issuer. Users are covered by a liability shift against fraudulent payments that have been authenticated with 3D Secure as the card issuer assumes full responsibility.<br><br>While 3D Secure protects you from fraud, it requires your customers to complete additional steps during the payment process that could impact their checkout experience. For instance, if a customer does not know their 3D Secure information, they might not be able to complete the payment.<br><br>When considering the use of 3D Secure, you might find the right balance is to use it only in situations where there is an increased risk of fraud, or if the customer’s card would be declined without it.',
                    options: {
                        'false': 'Disabled',
                        'true': 'Enabled'
                    },
                    value: 'false',
                    pclass: 'field-is-not-sca-mode'
                };
                fields.currency_code = {
                    name: 'Currency code',
                    value: 'GBP'
                };
                fields.language_code = {
                    name: 'Language code',
                    help: 'Force language in payment provider page',
                    value: 'auto'
                };
                fields.deposit = {
                    type: 'select',
                    name: 'Deposit',
                    help: 'If you disable this option then no deposit will be taken for this payment method.',
                    options: {
                        '0': 'Disabled',
                        '1': 'Enabled'
                    },
                    value: 1
                };

            break;
            case 'wpop':

                fields.test_mode = {
                    type: 'select',
                    name: 'Environment',
                    help: '<b>Live</b> - When you ready to go live.<br><b>Test</b> - When you want to do some testing.',
                    options: {
                        '0': 'Live',
                        '1': 'Test'
                    },
                    value: 0
                };
                fields.pk_live = {
                    type: 'password',
                    name: 'Live client key',
                    value: '',
                    pclass: 'field-is-live-mode'
                };
                fields.sk_live = {
                    type: 'password',
                    name: 'Live service key',
                    value: '',
                    pclass: 'field-is-live-mode'
                };
                fields.template_code_live = {
                    name: 'Live card template code',
                    help: 'Here you can enter ID of custom form template that has been created in payment provider control panel. If you not sure what it is leave this field blank.',
                    value: '',
                    pclass: 'field-is-live-mode'
                };
                fields.pk_test = {
                    type: 'password',
                    name: 'Test client key',
                    value: '',
                    pclass: 'field-is-test-mode'
                };
                fields.sk_test = {
                    type: 'password',
                    name: 'Test service key',
                    value: '',
                    pclass: 'field-is-test-mode'
                };
                fields.template_code_test = {
                    name: 'Test card template code',
                    help: 'Here you can enter ID of custom form template that has been created in payment provider control panel. If you not sure what it is leave this field blank.',
                    value: '',
                    pclass: 'field-is-test-mode'
                };
                fields.test_amount = {
                    name: 'Test amount',
                    class: 'touchspin',
                    attr: 'data-bts-step="0.01" data-bts-decimals="2" data-bts-min="0"',
                    value: 0,
                    help: 'Amount used for testing. It will override payment price. If it is set to zero, then booking price will be used.',
                    pclass: 'field-is-test-mode'
                };
                // fields.three_d_secure = {
                //     type: 'select',
                //     name: '3D Secure',
                //     help: '<b>When to use 3D Secure</b><br>3D Secure provides a layer of protection against fraudulent payments that is supported by most card issuers. Unlike regular card payments, 3D Secure requires cardholders to complete an additional verification step with the issuer.',
                //     options: {
                //         'false': 'Disabled',
                //         'true': 'Enabled'
                //     },
                //     value: 'false'
                // };
                fields.currency_code = {
                    name: 'Currency code',
                    value: 'GBP'
                };
                // fields.language_code = {
                //     name: 'Language code',
                //     help: 'Force language in payment provider page',
                //     value: 'auto'
                // };
                fields.deposit = {
                    type: 'select',
                    name: 'Deposit',
                    help: 'If you disable this option then no deposit will be taken for this payment method.',
                    options: {
                        '0': 'Disabled',
                        '1': 'Enabled'
                    },
                    value: 1
                };

            break;
            case 'square':

                fields.test_mode = {
                    type: 'select',
                    name: 'Environment',
                    help: '<b>Production</b> - When you ready to go live.<br><b>Sandbox</b> - When you want to do some testing.',
                    options: {
                        '0': 'Production',
                        '1': 'Sandbox'
                    },
                    value: 0
                };
                fields.test_amount = {
                    name: 'Test amount',
                    class: 'touchspin',
                    attr: 'data-bts-step="0.01" data-bts-decimals="2" data-bts-min="0"',
                    value: 0,
                    help: 'Amount used for testing. It will override payment price. If it is set to zero, then booking price will be used.',
                    pclass: 'field-is-test-mode'
                };
                fields.live_access_token = {
                    type: 'password',
                    name: 'Personal Access Token',
                    value: '',
                    pclass: 'field-is-live-mode'
                };
                fields.live_location_id = {
                    name: 'Location ID',
                    value: '',
                    pclass: 'field-is-live-mode'
                };
                fields.test_access_token = {
                    type: 'password',
                    name: 'Sandbox Access Token',
                    value: '',
                    pclass: 'field-is-test-mode'
                };
                fields.test_location_id = {
                    name: 'Sandbox Location ID',
                    value: '',
                    pclass: 'field-is-test-mode'
                };
                fields.legacy_mode = {
                    type: 'select',
                    name: 'Sandbox Legacy mode',
                    help: 'Select NO if you are using new type of access token.<br>Select YES if you are using legacy type of token.',
                    options: {
                        '0': 'No',
                        '1': 'Yes'
                    },
                    value: 0,
                    pclass: 'field-is-test-mode'
                };
                fields.currency_code = {
                    name: 'Currency code',
                    value: 'GBP'
                };
                fields.deposit = {
                    type: 'select',
                    name: 'Deposit',
                    help: 'If you disable this option then no deposit will be taken for this payment method.',
                    options: {
                        '0': 'Disabled',
                        '1': 'Enabled'
                    },
                    value: 1
                };

            break;
            case 'gpwebpay':

                fields.test_mode = {
                    type: 'select',
                    name: 'Environment',
                    help: '<b>Live</b> - When you ready to go live.<br><b>Test</b> - When you want to do some testing.',
                    options: {
                        '0': 'Live',
                        '1': 'Test'
                    },
                    value: 0
                };
                fields.test_amount = {
                    name: 'Test amount',
                    class: 'touchspin',
                    attr: 'data-bts-step="0.01" data-bts-decimals="2" data-bts-min="0"',
                    value: 0,
                    help: 'Amount used for testing. It will override payment price. If it is set to zero, then booking price will be used.',
                    pclass: 'field-is-test-mode'
                };
                fields.merchant_number = {
                    name: 'Merchant Number',
                    value: '',
                };
                fields.private_key = {
                    name: 'Private key path',
                    value: '',
                };
                fields.private_key_password = {
                    type: 'password',
                    name: 'Private key password',
                    value: ''
                };
                fields.public_key = {
                    name: 'Public key path',
                    value: '',
                };
                fields.currency_code = {
                    name: 'Currency code',
                    value: '203'
                };
                fields.operation_mode = {
                    type: 'select',
                    name: 'Operation mode',
                    options: {
                        '1': 'Sale',
                        '0': 'Authorization',
                    },
                    value: '1'
                };
                fields.language_code = {
                    name: 'Language code',
                    help: 'Force language in payment provider page',
                    value: ''
                };
                fields.deposit = {
                    type: 'select',
                    name: 'Deposit',
                    help: 'If you disable this option then no deposit will be taken for this payment method.',
                    options: {
                        '0': 'Disabled',
                        '1': 'Enabled'
                    },
                    value: 1
                };

            break;
            case 'cash':
                // None
            break;
            case 'account':
                // None
            break;
            case 'bacs':
                fields.additional_details = {
                    type: 'textarea',
                    name: 'Additional info',
                    help: 'This info will appear in thank you page. You can enter here IBAN, ACCOUNT NUMBER, SWIFT / BIC etc.',
                    value: '',
                    attr: 'style="min-width:180px; min-height:80px;"'
                };
            break;
        }

        var parmsHtml = '';

        $.each(fields, function(key, value) {
            var html = '';
            var f_type = 'text';
            var f_name = '';
            var f_options = {};
            var f_pclass = '';
            var f_class = '';
            var f_attr = '';
            var f_help = '';
            var f_value = '';

            if( value.type ) {
                f_type = value.type;
            }
            if( value.name ) {
                f_name = value.name;
            }
            if( value.options ) {
                f_options = value.options;
            }
            if( value.pclass ) {
                f_pclass = value.pclass;
            }
            if( value.class ) {
                f_class = value.class;
            }
            if( value.attr ) {
                f_attr = value.attr;
            }
            if( value.help ) {
                f_help = value.help;
            }
            if( value.value ) {
                f_value = value.value;
            }

            if( f_type == 'html' ) {
                html += f_name;
            }
            else if( f_type == 'container_start' ) {
                html += '<div class="field-container_'+ key +' '+ f_pclass +'">';
            }
            else if( f_type == 'container_end' ) {
                html += '</div>';
            }
            else if( f_type == 'text' ) {
                html += '<div class="form-group field-param_'+ key +' '+ f_pclass +'">';
                    html += '<label for="param_'+ key +'">'+ f_name +'</label>';
                    if( f_help ) {
                        html += '<div class="input-group">';
                    }

                    html += '<input type="text" name="param_fields['+ key +']" id="param_'+ key +'" placeholder="'+ f_name +'" class="form-control '+ f_class +'" '+ f_attr +' autocomplete="off">';

                    if( f_help ) {
                            html += '<div class="input-group-addon" data-toggle="popover" data-title="" data-content="'+ f_help +'">';
                                html += '<i class="fa fa-info-circle"></i>';
                            html += '</div>';
                        html += '</div>';
                    }
                html += '</div>';
            }
            else if( f_type == 'textarea' ) {
                html += '<div class="form-group field-param_'+ key +' '+ f_pclass +'">';
                    html += '<label for="param_'+ key +'">'+ f_name +'</label>';
                    if( f_help ) {
                        html += '<div class="input-group">';
                    }

                    html += '<textarea name="param_fields['+ key +']" id="param_'+ key +'" placeholder="'+ f_name +'" class="form-control '+ f_class +'" '+ f_attr +' autocomplete="off">'+ f_value +'</textarea>';

                    if( f_help ) {
                            html += '<div class="input-group-addon" data-toggle="popover" data-title="" data-content="'+ f_help +'">';
                                html += '<i class="fa fa-info-circle"></i>';
                            html += '</div>';
                        html += '</div>';
                    }
                html += '</div>';
            }
            else if( f_type == 'password' ) {
                html += '<div class="form-group field-param_'+ key +' '+ f_pclass +'">';
                    html += '<label for="param_'+ key +'">'+ f_name +'</label>';
                    html += '<div class="input-group">';
                        html += '<input type="password" name="param_fields['+ key +']" id="param_'+ key +'" placeholder="'+ f_name +'" class="form-control '+ f_class +'" '+ f_attr +' autocomplete="new-password">';

                        html += '<div class="input-group-addon" title="Show/Hide" onclick="var type = $(this).parents(\'.form-group\').find(\'input\').attr(\'type\'); if( type == \'password\') { type = \'text\' } else { type = \'password\' } $(this).parents(\'.form-group\').find(\'input\').attr(\'type\', type);">';
                            html += '<i class="fa fa-eye"></i>';
                        html += '</div>';

                        if( f_help ) {
                            html += '<div class="input-group-addon" data-toggle="popover" data-title="" data-content="'+ f_help +'">';
                                html += '<i class="fa fa-info-circle"></i>';
                            html += '</div>';
                        }
                    html += '</div>';
                html += '</div>';
            }
            else if( f_type == 'select' ) {
                html += '<div class="form-group field-param_'+ key +' '+ f_pclass +'">';
                    html += '<label for="param_'+ key +'">'+ f_name +'</label>';
                    if( f_help ) {
                        html += '<div class="input-group">';
                    }

                    html += '<select name="param_fields['+ key +']" id="param_'+ key +'" placeholder="'+ f_name +'" class="form-control select2 '+ f_class +'" data-minimum-results-for-search="Infinity" '+ f_attr +'>';
                    $.each(f_options, function(key2, value2) {
                        html += '<option value="'+ key2 +'">'+ value2 +'</option>';
                    });
                    html += '</select>';

                    if( f_help ) {
                            html += '<div class="input-group-addon" data-toggle="popover" data-title="" data-content="'+ f_help +'">';
                                html += '<i class="fa fa-info-circle"></i>';
                            html += '</div>';
                        html += '</div>';
                    }
                html += '</div>';
            }

            if( html ) {
                parmsHtml += html;
            }
        });

        if( parmsHtml ) {
            $('#params-options').append(parmsHtml);
        }

        // Update values
        $.each(fields, function(key, value) {
            var f_value = '';

            if( value.value != 'undefined' ) {
                f_value = value.value;
            }

            if( $('#param_'+ key) ) {
                if( params ) {
                    $.each(JSON.parse(params), function(key2, value2) {
                        if( key == key2 ) {
                            f_value = value2;
                        }
                    });
                }
                $('#param_'+ key).val(f_value);
            }
        });

        // Display labels
        $('#params-options').find('input, select').on('change', function(e) {
          if( $(this).val() ) {
            $(this).parents('.form-group').find('label').show();
          }
          else {
            $(this).parents('.form-group').find('label').hide();
          }
          e.preventDefault();
        });

        // Hide Test amount
        $('#param_test_mode').change(function(){
            if( parseInt($(this).val()) == 1 ) {
                $('.field-is-live-mode').addClass('hidden');
                $('.field-is-test-mode').removeClass('hidden');
            }
            else {
                $('.field-is-live-mode').removeClass('hidden');
                $('.field-is-test-mode').addClass('hidden');
            }
        }).change();

        // Hide Stripe settings
        $('#param_sca_mode').change(function(){
            if( parseInt($(this).val()) == 1 ) {
                $('.field-is-sca-mode').removeClass('hidden');
                $('.field-is-not-sca-mode').addClass('hidden');
            }
            else {
                $('.field-is-sca-mode').addClass('hidden');
                $('.field-is-not-sca-mode').removeClass('hidden');
            }
        }).change();

        $('#param_live_enable_webhook').change(function(){
            if( parseInt($(this).val()) == 1 ) {
                $('.field-is-live_enable_webhook').removeClass('hidden');
            }
            else {
                $('.field-is-live_enable_webhook').addClass('hidden');
            }
        }).change();

        $('#param_test_enable_webhook').change(function(){
            if( parseInt($(this).val()) == 1 ) {
                $('.field-is-test_enable_webhook').removeClass('hidden');
            }
            else {
                $('.field-is-test_enable_webhook').addClass('hidden');
            }
        }).change();

        // Select
        $('#params-options .select2').select2();

        // Spinner
        $('#params-options input[type="text"].touchspin').TouchSpin({
          max: null,
          booster: true,
          boostat: 5,
          mousewheel: true,
          verticalbuttons: true,
          verticalupclass: 'fa fa-plus',
          verticaldownclass: 'fa fa-minus'
        });

        // Tooltip
        $('#params-options [title]').tooltip({
          placement: 'auto',
          container: 'body',
          selector: '',
          html: true,
          trigger: 'hover',
          delay: {
            'show': 500,
            'hide': 100
          }
        });

        $('[data-toggle="popover"]').popover({
            placement: 'auto right',
            container: 'body',
            trigger: 'click focus hover',
            html: true
        });
    }


  // Update
  function updateRecord(id, method) {
    var html = '';
    var title = '';
    var btnIcon = '';
    var btnTitle = '';
    var msgSuccess = '';
    var modalCls = '';

    if( id && id >= 0 ) {
      title = 'Edit - <span style="text-transform:capitalize;">'+ method +'</span>';
      btnIcon = 'fa fa-pencil-square-o';
      btnTitle = 'Save';
      msgSuccess = 'Saved';
      modalCls = 'modal-edit';
    }
    else {
      title = 'Add new';
      btnIcon = 'fa fa-plus';
      btnTitle = 'Add';
      msgSuccess = 'Added';
      modalCls = 'modal-add';
    }

    html = '<form method="post" enctype="multipart/form-data" autocomplete="off">\
            <input type="text" name="randomusernameremembered" id="randomusernameremembered" value="" style="width:0;height:0;visibility:hidden;position:absolute;left:0;top:0;margin:0;padding:0;border:0;background:none;">\
            <input type="password" name="randompasswordremembered" id="randompasswordremembered" value="" style="width:0;height:0;visibility:hidden;position:absolute;left:0;top:0;margin:0;padding:0;border:0;background:none;">\
            <input type="hidden" name="id" id="id" value="0">\
            <input type="hidden" name="site_id" id="site_id" value="0">\
            <div class="payment-method-top-info"></div>\
            <div class="form-group field-name">\
              <label for="name">Name</label>\
              <input type="text" name="name" id="name" placeholder="Name" required class="form-control">\
            </div>\
            <div class="form-group field-description">\
              <label for="description">Description</label>\
              <textarea name="description" id="description" placeholder="Description" class="form-control"></textarea>\
            </div>\
            <div class="form-group field-payment_page">\
              <label for="payment_page">Payment page HTML</label>\
              <textarea name="payment_page" id="payment_page" placeholder="Payment page HTML" class="form-control"></textarea>\
            </div>\
            <div id="image_preview"></div>\
            <input type="hidden" name="image" id="image" value="">\
            <div class="form-group field-image_type">\
                <label for="image_type_gallery" class="checkbox-inline" style="padding-left:0px;">\
                    <input type="radio" name="image_type" id="image_type_gallery" value="0" checked> Gallery\
                </label>\
                <label for="image_type_upload" class="checkbox-inline">\
                    <input type="radio" name="image_type" id="image_type_upload" value="1"> Upload\
                </label>\
            </div>\
            <div class="form-group field-image_gallery" style="margin-bottom:30px;">\
                <label for="image_gallery">Choose image</label>\
                <select name="image_gallery" id="image_gallery" data-placeholder="Choose image" data-allow-clear="true" class="form-control select2"></select>\
            </div>\
            <div class="form-group field-image_upload" style="margin-bottom:30px; display:none;">\
                <label for="image_upload" style="display:inline-block;">Upload image</label>\
                <input type="file" name="image_upload" id="image_upload" class="form-control" />\
            </div>\
            <div id="params-options"></div>\
            <div class="form-group field-params" style="display:none;">\
              <label for="params">Params</label>\
              <textarea name="params" id="params" placeholder="Params" required class="form-control">{}</textarea>\
            </div>\
            <div class="form-group field-method">\
              <label for="method">Method</label>\
              <input type="text" name="method" id="method" placeholder="Method" required class="form-control" autocomplete="off" readonly>\
            </div>\
            <div class="form-group field-factor_type">\
              <label for="factor_type">Payment charge type</label>\
              <select name="factor_type" id="factor_type" data-placeholder="Payment charge type" data-minimum-results-for-search="Infinity" required class="form-control select2">\
                <option value="0">Flat (+)</option>\
                <option value="1">Percent (%)</option>\
              </select>\
            </div>\
            <div class="form-group field-price">\
              <label for="price">Payment charge value</label>\
              <input type="text" name="price" id="price" placeholder="Payment charge value" value="0" required class="form-control touchspin" data-bts-step="0.01" data-bts-decimals="2" data-bts-min="0">\
            </div>\
            <div class="form-group field-ordering">\
              <label for="ordering">Ordering</label>\
              <input type="text" name="ordering" id="ordering" placeholder="Ordering" value="0" required class="form-control touchspin" data-bts-step="1" data-bts-min="0">\
            </div>\
            <div class="form-group field-service_ids" style="margin-top:20px; margin-bottom:20px; max-width:100%;">\
              <div style="margin-bottom:10px;">Assign to selected services (all if no options are selected)</div>\
              <div id="service_ids"></div>\
            </div>\
            <div class="form-group field-default">\
              <label for="default" class="checkbox-inline">\
                <input type="checkbox" name="default" id="default" value="1">Default\
              </label>\
            </div>\
            <div class="form-group field-published">\
              <label for="published" class="checkbox-inline">\
                <input type="checkbox" name="published" id="published" value="1" checked>Active\
              </label>\
            </div>\
            <div class="form-group field-is_backend">\
              <label for="is_backend">Display</label>\
              <select name="is_backend" id="is_backend" data-placeholder="Display" data-minimum-results-for-search="Infinity" required class="form-control select2">\
                <option value="0">Frontend & Backend</option>\
                <option value="1">Backend</option>\
              </select>\
            </div>\
            <div class="form-buttons">\
              <button type="submit" class="btn btn-success btnSave" title="'+ btnTitle +'"><i class="'+ btnIcon +'"></i> <span>'+ btnTitle +'</span></button>\
              <button type="button" class="btn btn-default btnCancel" title="Cancel"> <span>Cancel</span></button>\
              <span id="statusMsg"></span>\
            </div>\
          </form>';

    $('#dmodal').removeClass('modal-edit modal-add').addClass(modalCls);
    $('#dmodal .modal-title').html(title);
    $('#dmodal .modal-body').html(html);
    $('#dmodal').modal({
      show: true
    });

    // Load filters
    $.ajax({
      headers : {
        'X-CSRF-TOKEN': EasyTaxiOffice.csrfToken
      },
      url: EasyTaxiOffice.appPath +'/etov2?apiType=backend',
      type: 'POST',
      dataType: 'json',
      cache: false,
      async: false,
      data: {
        task: 'payment',
        action: 'init'
      },
      success: function(response) {
        // Images
        html = '';
        if (response.imagesList) {
          html += '<option value=""></option>';
          $.each(response.imagesList, function(index, item) {
            html += '<option value="'+ item.value +'">'+ item.text +'</option>';
          });
        }
        $('#dmodal #image_gallery').html(html);

        // Services
        html = '';
        if (response.servicesList) {
          $.each(response.servicesList, function(index, item) {
            html += '<div><label for="service_ids'+ item.id +'" class="checkbox-inline">';
              html += '<input type="checkbox" name="service_ids[]" id="service_ids'+ item.id +'" value="'+ item.id +'"> '+ item.name +'';
            html += '</label></div>';
          });
        }
        $('#dmodal #service_ids').html(html);

        if( html ) {
            $('#dmodal .field-service_ids').show();
        }
        else {
            $('#dmodal .field-service_ids').hide();
        }
      },
      error: function(response) {
        $('#dmodal #statusMsg').html('<span class="text-danger"><i class="fa fa-exclamation-circle"></i> An error occurred while processing your request</span>');
      }
    });

    // Load data
    if( id && id >= 0 ) {
      $.ajax({
        headers : {
          'X-CSRF-TOKEN': EasyTaxiOffice.csrfToken
        },
        url: EasyTaxiOffice.appPath +'/etov2?apiType=backend',
        type: 'POST',
        dataType: 'json',
        cache: false,
        data: {
          task: 'payment',
          action: 'read',
          id: id
        },
        success: function(response) {
          if( response.success ) {
            $('#dmodal #statusMsg').html('');

            if( response.record ) {
              $.each(response.record, function(key, value) {
                // console.log(key, value);

                var field = $('#dmodal form #'+ key);

                // if( key == 'method' ) {
                //     $('#payment-methods .field-name').before('<div style="margin-bottom:20px;">Method: <span style="text-transform:capitalize;">'+ value +'</span></div>');
                // }

                if( key == 'method' && value == 'stripe_ideal' ) {
                    $('#payment-methods .field-ordering').before('<div style="margin-bottom:20px;"><div style="margin-bottom:5px; color:#888;">Webhook URL:</div><code style="padding:5px 10px;">{{ url('/etov2') }}?apiType=frontend&task=notify&webhook=stripe</code></div>');
                }
                else if( key == 'method' && value == 'epdq' ) {
                    $('#payment-methods .field-ordering').before('<div style="margin-bottom:20px;"><div style="margin-bottom:5px; color:#888;">Software URL:</div><code style="padding:5px 10px;">{{ url('/') }}</code></div>');
                    $('#payment-methods .field-ordering').before('<div style="margin-bottom:20px;"><div style="margin-bottom:5px; color:#888;">Payment notification URL:</div><code style="padding:5px 10px;">{{ url('/etov2') }}?apiType=frontend&task=notify&pMethod=epdq</code></div>');
                }

                if( key == 'params' ) {
                    field.val(value);
                    loadParams(response.record.method, value, response.record.vehicle_list);
                }
                else if( field.hasClass('select2') ) {
                  field.val(value).trigger('change');
                }
                else if( field.is(':checkbox') ) {
                  if( parseInt(value) ) {
                    field.attr('checked', true);
                  }
                  else {
                    field.attr('checked', false);
                  }
                }
                else if( key == 'service_ids' ) {
                    $.each(value, function(key2, value2) {
                        if( $('#dmodal form #service_ids'+ value2).length > 0 ) {
                            $('#dmodal form #service_ids'+ value2).attr('checked', true);
                        }
                    });
                }
                else if( key == 'image' ) {
                    var filename = value;
                    var filepath = '{{ asset_url('uploads','payments') }}';
                    if( filename ) {
                        var image_preview = '<img src="'+ filepath +'/'+ filename +'" style="max-width:150px; max-height:150px; margin-bottom:10px;" />';
                        $('#dmodal form #image_preview').html(image_preview);
                    }
                }
                else {
                  field.val(value);
                }
              });

              // Placeholder
              $('#dmodal form').find('input:not([type="submit"]), textarea, select').each(function() {
                  updateFormPlaceholder(this);
              })
              .bind('change keyup', function(e) {
                  updateFormPlaceholder(this);
              });
            }
          }
          else {
            $('#dmodal #statusMsg').html('<span class="text-danger"><i class="fa fa-exclamation-circle"></i> The data could not be loaded</span>');
          }
        },
        error: function(response) {
          $('#dmodal #statusMsg').html('<span class="text-danger"><i class="fa fa-exclamation-circle"></i> An error occurred while processing your request</span>');
        },
        beforeSend: function() {
          $('#dmodal #statusMsg').html('<i class="fa fa-spinner fa-spin"></i> In progress');
          $('#dmodal .modal-overlay').show();
        },
        complete: function() {
          $('#dmodal .modal-overlay').hide();
        }
      });
    }

    // Image preview
    $('#dmodal form #image_gallery').change(function(){
        if( $(this).val() ) {
            var filename = $(this).val();
            var filepath = '{{ asset_url('images','payments') }}';
        }
        else {
            var filename = $('#dmodal form #image').val();
            var filepath = '{{ asset_url('uploads','payments') }}';
        }
        // console.log(filename, filepath);
        if( filename ) {
            var image_preview = '<img src="'+ filepath +'/'+ filename +'" style="max-width:150px; max-height:150px; margin-bottom:10px;" />';
            $('#dmodal form #image_preview').html(image_preview);
        }
    }).change();

    // Image type
    $('#dmodal form input[name="image_type"]').change(function(){
        if( $(this).val() == 1 ) {
            $('#dmodal .field-image_gallery').hide();
            $('#dmodal .field-image_upload').show();
        }
        else {
            $('#dmodal .field-image_gallery').show();
            $('#dmodal .field-image_upload').hide();
        }
    });

    // Select
    $('#dmodal form .select2').select2();

    // Spinner
    $('#dmodal form input[type="text"].touchspin').TouchSpin({
      max: null,
      booster: true,
      boostat: 5,
      mousewheel: true,
      verticalbuttons: true,
      verticalupclass: 'fa fa-plus',
      verticaldownclass: 'fa fa-minus'
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

    $('#dmodal form').find('input:not([type="submit"]), textarea, select').each(function() {
        updateFormPlaceholder(this);
    })
    .bind('change keyup', function(e) {
        updateFormPlaceholder(this);
    });


    // Form validation and submission
    var isReady = 1;

    $('#dmodal form').formValidation({
      framework: 'bootstrap',
      icon: {
        valid: 'glyphicon glyphicon-ok',
        invalid: 'glyphicon glyphicon-remove',
        validating: 'glyphicon glyphicon-refresh'
      },
      excluded: ':disabled'
    })
    .on('err.field.fv', function(e, data) {
      e.preventDefault();
      if( data.fv.getSubmitButton() ) {
        data.fv.disableSubmitButtons(false);
      }
    })
    .on('success.field.fv', function(e, data) {
      e.preventDefault();
      if( data.fv.getSubmitButton() ) {
        data.fv.disableSubmitButtons(false);
      }
    })
    .on('success.form.fv', function(e) {
      e.preventDefault();
      if( isReady ) {
        // https://jquery-form.github.io/form/options/#code-samples
        $('#dmodal form').ajaxSubmit({
            url: EasyTaxiOffice.appPath +'/etov2?apiType=backend&task=payment&action=update',
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    $('#dmodal #statusMsg').html('<span class="text-green"><i class="fa fa-check-circle"></i> '+ msgSuccess +'</span>');
                    if( parseInt($('#dmodal form #id').val()) <= 0 ) {
                        $('#dmodal form').trigger('reset');
                        $('#dmodal').modal('hide');
                        filterTable();
                    }
                    else {
                        if( response.id ) {
                            updateRecord(response.id, response.method);
                            filterTable();
                        }
                    }
                }
                else {
                    var msg = 'The data could not be updated';
                    if( response.errors ) {
                        msg = '';
                        $.each(response.errors, function(index, error) {
                            if( msg ) { msg += ', '; }
                            msg += error;
                        });
                    }
                    $('#dmodal #statusMsg').html('<span class="text-danger"><i class="fa fa-exclamation-circle"></i> '+ msg +'</span>');
                }
                isReady = 1;
            },
            error: function(response) {
                $('#dmodal #statusMsg').html('<span class="text-danger"><i class="fa fa-exclamation-circle"></i> An error occurred while processing your request</span>');
                isReady = 1;
            },
            beforeSubmit: function() {
                $('#dmodal #statusMsg').html('<i class="fa fa-spinner fa-spin"></i> In progress');
                isReady = 0;
            }
        });
      }
    });

    // Cancel button
    $('#dmodal .btnCancel').on('click', function() {
      $('#dmodal').modal('hide');
    });
  }

  // Page loaded
  $(document).ready(function(){
      if (ETO.model === false) {
          ETO.init({ config: [], lang: ['user'] }, 'settings');
      }
    // Select
    $('.pageFilters .select2').select2();

    // Toggle filters
    // $('.pageFilters').toggle();
    $('.pageTitle .btnFilters').on('click', function() {
      $('.pageFilters').toggle();
    });

    // Reset form
    $('.btnReset').on('click', function(e) {
      // $('.pageFilters form').trigger('reset');
      $('.pageFilters form input[type="text"]').val('').trigger('change');

      $('.pageFilters form select.select2').each(function( index ) {
        $(this).val([]).trigger('change');
        // $(this).val(null).trigger('change');
        // $(this).select2('val', 0);
      });

      // $('#dtable').DataTable().search('').draw();
      $('#dtable').DataTable().search('');
      filterTable(false, true);
      e.preventDefault();
    });

    // Filter table
    $('.pageFilters .btnSearch').on('click', function(e) {
      filterTable(false, true);
      e.preventDefault();
    });

    $('.pageFilters form').submit(function(e) {
      filterTable();
      e.preventDefault();
    });

    // Display labels
    $('.pageFilters form').find('input, select').on('change', function(e) {
      // console.log($(this).val());
      if( $(this).val() ) {
        $(this).parent('.form-group').find('label').show();
      }
      else {
        $(this).parent('.form-group').find('label').hide();
      }
      // filterTable();
      e.preventDefault();
    });

    var tableOptionsn = {
        processing: true,
        serverSide: true,
        ajax: {
            headers : {
                'X-CSRF-TOKEN': EasyTaxiOffice.csrfToken
            },
            url: EasyTaxiOffice.appPath +'/etov2?apiType=backend',
            method: 'POST',
            dataType: 'json',
            cache: false,
            data: {
                task: 'payment',
                action: 'list'
            },
            dataSrc: 'list'
        },
        columns: [
         @if (auth()->user()->hasPermission(['admin.payments.edit', 'admin.payments.destroy']))
         {
            title: '',
            data: null,
            defaultContent: '',
            orderable: false,
            className: 'actionColumn'
        },
        @endif
        {
            title: 'ID',
            data: 'id',
            width: '50px',
            visible: false
        }, {
            title: 'Image',
            data: 'image',
            width: '150px'
        }, {
            title: 'Name',
            data: 'name',
            width: '150px'
        }, {
            title: 'Description',
            data: 'description',
            width: '250px'
        }, {
            title: 'Payment Charge',
            data: 'price',
            width: '80px'
        }, {
            title: 'Services',
            data: 'service_ids',
            width: '200px',
            class: 'column-service_ids'
        }, {
            title: 'Default',
            data: 'default',
            width: '80px'
        }, {
            title: 'Ordering',
            data: 'ordering',
            width: '90px'
        }, {
            title: 'Active',
            data: 'published',
            width: '80px'
        }, {
            title: 'Display',
            data: 'is_backend',
            width: '80px'
        }],
        columnDefs: [{
            targets: 0,
            data: null,
            render: function(data, type, row) {
                var h = '';
                h += '<div class="btn-group" role="group" aria-label="..." style="width:70px;">';
                if(ETO.hasPermission('admin.payments.edit')) {
                    h += '<button type="button" onclick="updateRecord(' + row.id + ', \'' + row.method + '\'); return false;" class="btn btn-default btn-sm btnEdit" title="Edit"><i class="fa fa-pencil-square-o"></i></button>';
                }
                if(ETO.hasPermission('admin.payments.destroy')) {
                    h += '<button type="button" onclick="deleteRecord(' + row.id + ', \'' + row.method + '\'); return false;" class="btn btn-default btn-sm btnDelete" title="Delete"><i class="fa fa-trash"></i></button>';
                }
                h += '</div>';
                return h;
            }
        }],
        paging: true,
        pagingType: 'full_numbers',
        dom: 'rt<"row"<"col-xs-12 col-md-5 dataTablesFooterLeft"li><"col-xs-12 col-md-7 dataTablesFooterRight"p>><"clear">',
        scrollX: true,
        searching: true,
        ordering: true,
        lengthChange: true,
        info: true,
        autoWidth: false,
        stateSave: true,
        stateDuration: 0,
        order: [
            [7, 'asc']
        ],
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50],
        language: {
            lengthMenu: '_MENU_',
            paginate: {
                first: '<i class="fa fa-angle-double-left"></i>',
                previous: '<i class="fa fa-angle-left"></i>',
                next: '<i class="fa fa-angle-right"></i>',
                last: '<i class="fa fa-angle-double-right"></i>'
            }
        },
        drawCallback: function(settings) {
            var pagination = $(this).closest('.dataTables_wrapper').find('.dataTables_paginate');
            pagination.toggle(this.api().page.info().pages > 1);
        },
        infoCallback: function( settings, start, end, max, total, pre ) {
            return '<i class="ion-ios-information-outline" title="'+ pre +'"></i>';
        }
    };

      if (!ETO.hasPermission(['admin.payments.edit', 'admin.payments.destroy'], true)) {
          delete tableOptionsn.columnDefs;
      }

    // Table
    $('#dtable').DataTable(tableOptionsn)
    .on('draw.dt', function() {
      // $('.dataTables_length select').select2();

      // Edit button
      $('#dtable').find('button.btnEdit').hover(
        function() {
          $(this).removeClass('btn-default').addClass('btn-success');
        },
        function() {
          $(this).removeClass('btn-success').addClass('btn-default');
        }
      );

      // Delete button
      $('#dtable').find('button.btnDelete').hover(
        function() {
          $(this).removeClass('btn-default').addClass('btn-danger');
        },
        function() {
          $(this).removeClass('btn-danger').addClass('btn-default');
        }
      );
    });

  });
  </script>
@endsection
