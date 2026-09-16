

if (typeof parseBoolean !== 'function') {
    window.parseBoolean = function(string) {
        var bool;

        bool = (function() {
            switch (false) {
                case null !== string:
                    return false;
                case typeof string != 'undefined':
                    return false;
                case parseInt(string) !== 1:
                    return true;
                case parseInt(string) !== 0:
                    return false;
                case string.toString().toLowerCase() != 'true':
                    return true;
                case string.toString().toLowerCase() != 'false':
                    return false;
            }
        })();

        if (typeof bool === "boolean") {
            return bool;
        }

        return 0;
    };
}
if (typeof generatePassword !== 'function') {
    window.generatePassword = function(len) {
        var length = (len)?(len):(8);
        var string = "abcdefghijklmnopqrstuvwxyz"; // to upper
        var numeric = '0123456789';
        var punctuation = '!@#$%^&*()_+~`|}{[]\:;?><,./-=';
        var password = "";
        var character = "";

        while( password.length<length ) {
            entity1 = Math.ceil(string.length * Math.random()*Math.random());
            entity2 = Math.ceil(numeric.length * Math.random()*Math.random());
            entity3 = Math.ceil(punctuation.length * Math.random()*Math.random());
            hold = string.charAt( entity1 );
            hold = (password.length%2==0)?(hold.toUpperCase()):(hold);
            character += hold;
            character += numeric.charAt( entity2 );
            character += punctuation.charAt( entity3 );
            password = character;
        }
        password=password.split('').sort(function(){return 0.5-Math.random()}).join('');
        return password.substr(0,len);
    };
}

var ETO = window.ETO || {};

ETO.config = {
    csrfToken: '',
    appPath: '/',
    timestamp: '',
    timezone: '',
    serviceType: {},
    currency_symbol: '',
    currency_code: '',
    date_format: '',
    time_format: '',
    date_start_of_week: '',
    system: {},
    subscription: {},
};

ETO.requires = [];
ETO.fields = {};

// language object
ETO.lang = {};

// loged user data object
ETO.current_user = {};

// name model
ETO.model = false;

// extends for Datatable library
ETO.datatable = {
    language: function() {
        return {
            lengthMenu: '_MENU_',
            paginate: {
                first: '<i class="fa fa-angle-double-left"></i>',
                previous: '<i class="fa fa-angle-left"></i>',
                next: '<i class="fa fa-angle-right"></i>',
                last: '<i class="fa fa-angle-double-right"></i>'
            },
        };
    },
    lengthMenu: [5, 10, 25, 50],
    domWithSearch: '<"row topContainer"<"col-xs-12 col-sm-12 col-md-12 dataTablesHeaderLeft">'
        +'<"col-xs-12 col-sm-6 col-md-5 dataTablesHeaderRight">><"dataTablesBody"rt>'
        +'<"row bottomContainer"' +
        +'<"col-xs-6 col-sm-6 col-md-5 dataTablesFooterLeft"liB>'
        + '<"col-xs-6 col-sm-6 col-md-7 pull-right dataTablesFooterRight"p>'
        + '>',
    dom: '<"row topContainer"<"col-xs-12 col-sm-12 col-md-12 dataTablesHeaderLeft">'
        +'<"col-xs-12 col-sm-6 col-md-5 dataTablesHeaderRight">><"dataTablesBody"rt>'
        +'<"row bottomContainer"'
        +'<"col-xs-6 col-sm-6 col-md-5 dataTablesFooterLeft"liB>'
        + '<"col-xs-6 col-sm-6 col-md-7 pull-right dataTablesFooterRight"p>'
        + '>',
    domForTranslations: '<"row topContainer"<"col-xs-12 col-sm-12 col-md-12 dataTablesHeaderLeft">'
        +'<"col-xs-12 col-sm-6 col-md-5 dataTablesHeaderRight">><"dataTablesBody"rt>'
        +'<"row bottomContainer"<"col-xs-6 col-sm-6 col-md-7 pull-right dataTablesFooterRight"p>'
        +'<"col-xs-6 col-sm-6 col-md-5 dataTablesFooterLeft"liB>>',

    buttons: function() {
        return [{
            extend: 'colvis',
            collectionLayout: 'fixed three-column',
            text: '<i class="fa fa-eye"></i>',
            titleAttr: ETO.lang.user.button.column_visibility,
            postfixButtons: ['colvisRestore'],
            className: 'btn-datatable btn-sm',
        }, {
            text: '<div class="eto-refresh-localStorage"><i class="fa fa-undo"></i></div>',
            titleAttr: ETO.lang.user.button.reset,
            className: 'btn-datatable btn-sm'
        }, {
            extend: 'reload',
            text: '<i class="fa fa-refresh"></i>',
            titleAttr: ETO.lang.user.button.reload,
            className: 'btn-datatable btn-sm'
        }];
    },
    buttonsForTranslations: function() {
        var data = this.buttons();
        data.push({
            text: '<i class="fa fa-trash-o"></i>',
            titleAttr: ETO.lang.user.button.clear_cache,
            className: 'btn-datatable btn-sm eto-clear-cache'
        });
        data.push({
            text: '<i class="fa fa-trash-o"></i>+',
            titleAttr: ETO.lang.user.button.clear_translations,
            className: 'btn-datatable btn-sm eto-remove-translations'
        });
        $.each(data, function(key, val) {
            if (val.extend && val.extend == 'colvis') {
                val.collectionLayout = 'fixed';
                data[key] = val;
            }
        });
        return data;
    },
    tooltip: {
        placement: 'auto',
        container: 'body',
        selector: '',
        html: true,
        trigger: 'hover',
        delay: {
            'show': 500,
            'hide': 100
        }
    },
    popover: {
        placement: 'auto right',
        container: 'body',
        trigger: 'focus hover',
        html: true
    },
    // errorMassages: 'alert',
    errorMassages: 'throw',
    // errorMassages: 'none',
};

ETO.setConfig = function(params){
    if(typeof params.config != 'undefined') {
        ETO.config = $.extend(true, ETO.config, params.config);
    }
    if(typeof params.lang != 'undefined') {
        ETO.lang =  params.lang;
    }
    if (typeof params.fields != 'undefined') {
        ETO.fields = params.fields;
    }
    if(typeof params.current_user != 'undefined') {
        ETO.current_user = params.current_user;
    }
};

ETO.updateTooltip = function(container, params){
    if (typeof container == 'undefined') {
        container = $('[data-toggle="tooltip"]:not(.eto-field-btn-help), [title]:not(.eto-field-btn-help)');
        container.tooltip('hide');
    }

    if(ETO.isMobile) { return; }

    setTimeout(function() {
        params = typeof params == 'undefined' ? {} : params;
        container.tooltip($.extend(true,{
            placement: 'auto',
            container: 'body',
            selector: '',
            html: true,
            trigger: 'hover',
            delay: {
                show: 500,
                hide: 100
            },
            template: '<div class="tooltip eto-bs-tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>',
            title: function(e) {
                if (typeof $(this).attr('data-title') != 'undefined') {
                    return $(this).attr('data-title');
                }
                return $(this).attr('title');
            }
        }, params));
    }, 0);
};

ETO.updatePopover = function(container, params){
    if (typeof container == 'undefined') {
        container = $('[data-toggle="popover"]');
        container.webuiPopover('destroy');
    }

    if(ETO.isMobile) { return; }

    // WebuiPopovers.hideAll();

    params = typeof params == 'undefined' ? {} : params;
    container.webuiPopover($.extend(true,{
        trigger: ETO.isMobile ? 'click' : 'hover',
        delay: {
            hide: 100
        },
        hideOthers: true,
        hideEmpty: true,
        template: '<div class="eto-bs-popover">'+
            '<div class="arrow"></div>'+
            '<div class="webui-popover-inner">'+
            '<h3 class="webui-popover-title"></h3>'+
            '<div class="webui-popover-content"><i class="icon-refresh"></i> <p>&nbsp;</p></div>'+
            '</div>'+
            '</div>',
    }, params));
};

ETO.updateTooltipPopover = function(container, popover, tooltip) {
    ETO.updateTooltip(container, tooltip);
    ETO.updatePopover(container, popover);
};

ETO.removeSetPopoverTooltip = function(container, data, type) {
    var paramsPopover = {};
    if (typeof type == 'undefined') { type = 'popover'}

    container.removeAttr('data-toggle');
    container.removeAttr('data-title');
    container.removeAttr('data-content');
    container.tooltip('destroy');
    container.webuiPopover('destroy');

    if (typeof data != 'undefined' && data !== false) {
        if (type == 'popover') {
            if (typeof data.tooltip != 'undefined' && data.tooltip.length > 0) {
                paramsPopover.title = data.title;
                paramsPopover.content = data.tooltip;
                container.attr('data-toggle', 'popover')
            }
            else if (typeof data.title != 'undefined' && data.title.length > 0) {
                paramsPopover.content = data.title;
            }
        }
        else {
            container.attr('data-toggle', type);
            container.attr('data-title', data.title);
        }
    }

    if (typeof paramsPopover.content != 'undefined' && paramsPopover.content.length > 0) {
        ETO.updatePopover(container, paramsPopover);
    }
    else if (type == 'tooltip') {
        ETO.updateTooltip(container);
    }
};

// initialize default data for view
ETO.init = function(getData, page) {
    ETO.model = page;

    // var url = 'get-config';

    // if (page == 'customer') {
    //     url = page + '/' + url;
    // } else {
    //     url += '/' + page;
    // }

    // ETO.ajax(url, {
    //     data: {
    //         getData: getData,
    //     },
    //     async: false,
    //     success: function(data) {
    //         // ETO.config = $.extend({
    //         //     csrfToken: ETO.config.csrfToken,
    //         //     appPath: ETO.config.appPath,
    //         //     timestamp: ETO.config.timestamp,
    //         //     timezone: ETO.config.timezone,
    //         //     serviceType: ETO.config.serviceType,
    //         //     currency_symbol:  ETO.config.currency_symbol,
    //         //     currency_code:  ETO.config.currency_code,
    //         //     date_format:  ETO.config.date_format,
    //         //     time_format:  ETO.config.time_format,
    //         //     date_start_of_week:  ETO.config.date_start_of_week,
    //         //     // system:  ETO.config.system,
    //         //     subscription:  ETO.config.subscription,
    //         // }, (typeof data.config === 'undefined')? {} : data.config );
    //
    //         ETO.current_user = data.user;
    //         // ETO.lang = data.lang;
    //         ETO.model = page;
    //         // if (typeof data.fields != 'undefined') {
    //         //     ETO.fields = data.fields;
    //         // }
    //     },
    //     error: function(data, status) {
    //         console.log(data, status);
    //     }
    // }, 'POST');

    // $('body').on('click', '.eto-refresh-localStorage', function(e) {
    //     if (confirm('Are you sure you would like to reset current columns visibility and sorting settings?') == true) {
    //         $.each(ETO.findLocalItems('DataTables_(.*)/' + ETO.model), function (i, obj) {
    //             localStorage.removeItem(obj.key);
    //         });
    //
    //         if (ETO.model.localeCompare('dispatch') === 0) {
    //             $.each(ETO.findLocalItems('ETO_admin_dispatch_(.*$)'), function (i, obj) {
    //                 localStorage.removeItem(obj.key);
    //             });
    //         }
    //
    //         window.location.reload();
    //         // dtStateSave(null, true);
    //     }
    // });

    ETO.extend_jQuery();
};

ETO.extendConfig = function(model, config, name) {
    if (typeof config != 'undefined') {
        model.config = $.extend(true, model.config, config);
    }

    if (ETO.model === false || model.config.forceReload === true) {
        ETO.init({
            config: model.config.init,
            lang:  model.config.lang
        }, name);
    }
};

ETO.extend_jQuery = function() {
    (function($){
        $.fn.replaceClass = function (pFromClass, pToClass) {
            return this.removeClass(pFromClass).addClass(pToClass);
        };

        $.fn.toogleClass = function (classToToogle) {
            if (this.hasClass(classToToogle)) {
                return this.removeClass(classToToogle)
            }
            else {
                return this.addClass(classToToogle)
            }
        };

        $.fn.toogleCheckbox = function (classToToogle) {
            if (this.attr('checked') == 'checked') {
                return this.attr('checked', false)
            }
            else {
                return this.attr('checked', true)
            }
        };

        $.fn.focusTextToEnd = function(){
            this.focus();

            if (typeof this[0] != 'undefined') {
                if (this[0].tagName == 'SELECT') {
                    return this;
                }
            }

            var $thisVal = this.val();
            this.val('').val($thisVal);
            return this;
        };

        $.fn.upperCaseFirst = function(){
            return this.selector.charAt(0).toUpperCase() + this.selector.slice(1);
        };

        $.fn.lowerCaseFirst = function(){
            return this.selector.charAt(0).toLowerCase() + this.selector.slice(1);
        };

        $.fn.formObject = function() {
            var formData = {};
            this.find('[name]').each(function() {
                formData[this.name] = this.value;
            });
            return formData;
        };

        $.fn.serializeObject = function(boolCheckbox){
            var self = this,
                json = {},
                push_counters = {},
                patterns = {
                    "validate": /^[a-zA-Z][a-zA-Z0-9_]*(?:\[(?:\d*|[a-zA-Z0-9_]+)\])*$/,
                    "key":      /[a-zA-Z0-9_]+|(?=\[\])/g,
                    "push":     /^$/,
                    "fixed":    /^\d+$/,
                    "named":    /^[a-zA-Z0-9_]+$/
                };

            this.build = function(base, key, value){
                base[key] = value;
                return base;
            };

            this.push_counter = function(key){
                if(push_counters[key] === undefined){
                    push_counters[key] = 0;
                }
                return push_counters[key]++;
            };

            $.each($(this).serializeArray(), function(){
                // skip invalid keys
                if(!patterns.validate.test(this.name)){
                    return;
                }

                var k,
                    keys = this.name.match(patterns.key),
                    merge = this.value,
                    reverse_key = this.name;

                if(boolCheckbox === true && $('[name="'+reverse_key+'"]').attr('type') == 'checkbox') {
                    merge = $('[name="'+reverse_key+'"]').attr('checked') == 'checked';

                }

                while((k = keys.pop()) !== undefined){
                    // adjust reverse_key
                    reverse_key = reverse_key.replace(new RegExp("\\[" + k + "\\]$"), '');

                    if (k.match(patterns.push)){ // push
                        merge = self.build([], self.push_counter(reverse_key), merge);
                    } else if (k.match(patterns.fixed)){ // fixed
                        merge = self.build([], k, merge);
                    } else if (k.match(patterns.named)){ // named
                        merge = self.build({}, k, merge);
                    }
                }

                json = $.extend(true, json, merge);
            });

            return json;
        };
    })(jQuery);

    String.prototype.truncate = function(n, l, useWordBoundary)
    {
        if (this.length <= n) { return this; }
        if (typeof l == "undefined") { l = n; }
        var subString = this.substr(0, l);
        return (useWordBoundary
            ? subString.substr(0, subString.lastIndexOf(' '))
            : subString) + "&hellip;";
    };
};

// extend for ajax object
ETO.ajax = function(url, options, type, is_ajax) {
    var fullUrl = '';

    type = typeof type == 'undefined' ? 'POST' : type;

    if (url.search('http://') === 0 || url.search('https://') === 0) {
        fullUrl = url;
    } else {
        fullUrl = ETO.config.appPath + '/' + url;
    }

    var dataAjax = $.extend(true, {
        headers: {
            'X-CSRF-TOKEN': ETO.config.csrfToken
        },
        url: fullUrl,
        dataType: 'json',
        type: type,
        async: false,
        cache: false,
        beforeSend: function(){
            // Handle the beforeSend event
        },
        error: function( response ) {
            ETO.swalWithBootstrapButtons({
                type: 'error',
                title: response.statusText + ' ('+response.status+')',
            });
        }
    }, options);

    if (typeof is_ajax == 'undefined' || is_ajax === true) {
        $.ajax(dataAjax);
    }
    else {
        return dataAjax
    }
};

ETO.ajaxWithFileUpload = function (formContainer, url, params, optionsAjax)
{
    var formData = new FormData(),
        enctype = 'text/plain',
        response = {};

    formContainer.find('input, select, textarea').each(function (k,v) {
        var name = $(this).attr('name'),
            value = $(this).val();

        // console.log( $(this).attr('type'), $(this).attr('name'));

        if (typeof $(this).attr('type') != "undefined" && ['checkbox', 'radio'].indexOf($(this).attr('type')) != -1) {
            value = $(this).attr('checked') == 'checked' ? 1 : 0;
        }
        else if (this.tagName == 'SELECT') {
            if ($(this).data('select2')) {
                value = $(this).select2('val');
            } else {
                value = $(this).find(':selected').val();
            }
        }

        if (typeof $(this).attr('type') != "undefined" && ['file'].indexOf($(this).attr('type')) != -1 && value.toString().length > 0) {
            formData.append(name, $('input[name="'+name+'"][type=file]')[0].files[0]);
            formData.append("upload_file", true);
            enctype = 'multipart/form-data';
        } else {
            formData.append(name, value);
        }
    });

    if (typeof params == 'object') {
        $.each(params, function (k,v) {
            formData.append(k,v);
        })
    }

    optionsAjax = typeof optionsAjax == 'object' ? optionsAjax : {};

    ETO.ajax(url, $.extend(true, {
        data: formData,
        async: false,
        cache: false,
        contentType: false,
        enctype: enctype,
        processData: false,
        // success: function (data) {
            // response = data;
        // }
    }, optionsAjax));

    // return response;
};

ETO.createNewObject = function(object, a, b, c, d, e, f, g, h) {
    if (typeof a != 'undefined' && typeof ETO[object].config[a] == 'undefined') {
        ETO[object].config[a] = {};
    }
    if (typeof b != 'undefined' && typeof ETO[object].config[a] != 'undefined' && typeof ETO[object].config[a][b] == 'undefined') {
        ETO[object].config[a][b] = {};
    }
    if (typeof c != 'undefined' && typeof ETO[object].config[a][b] != 'undefined' && typeof ETO[object].config[a][b][c] == 'undefined') {
        ETO[object].config[a][b][c] = {};
    }
    if (typeof d != 'undefined' && typeof ETO[object].config[a][b][c] != 'undefined' && typeof ETO[object].config[a][b][c][d] == 'undefined') {
        ETO[object].config[a][b][c][d] = {};
    }
    if (typeof e != 'undefined' && typeof ETO[object].config[a][b][c][d] != 'undefined' && typeof ETO[object].config[a][b][c][d][e] == 'undefined') {
        ETO[object].config[a][b][c][d][e] = {};
    }
    if (typeof f != 'undefined' && typeof ETO[object].config[a][b][c][d][e] != 'undefined' && typeof ETO[object].config[a][b][c][d][e][f] == 'undefined') {
        ETO[object].config[a][b][c][d][e][f] = {};
    }
    if (typeof g != 'undefined' && typeof ETO[object].config[a][b][c][d][e][f] != 'undefined' && typeof ETO[object].config[a][b][c][d][e][f][g] == 'undefined') {
        ETO[object].config[a][b][c][d][e][f][g] = {};
    }
    if (typeof h != 'undefined' && typeof ETO[object].config[a][b][c][d][e][f][g] != 'undefined' && typeof ETO[object].config[a][b][c][d][e][f][g][h] == 'undefined') {
        ETO[object].config[a][b][c][d][e][f][g][h] = {};
    }
};

ETO.hasProperty = function(data, keys) {
    var isObject = true;
    for (var i in keys) {
        isObject = data.hasOwnProperty(keys[i]);

        if (isObject === true) { data = data[keys[i]]; }
        else { break; }
    }
    return isObject;
};

ETO.getBrowserName = function() {
    var string = '';

    if ( navigator.userAgent.indexOf("Edge") > -1 && navigator.appVersion.indexOf('Edge') > -1 ) {
        string = 'Edge';
    }
    else if ( navigator.userAgent.indexOf("Opera") !== -1 || navigator.userAgent.indexOf('OPR') !== -1 ) {
        string = 'Opera';
    }
    else if ( navigator.userAgent.indexOf("Chrome") !== -1 ) {
        string = 'Chrome';
    }
    else if ( navigator.userAgent.indexOf("Safari") !== -1) {
        string = 'Safari';
    }
    else if ( navigator.userAgent.indexOf("Firefox") !== -1 )  {
        string = 'Firefox';
    }
    else if ( ( navigator.userAgent.indexOf("MSIE") !== -1 ) || (!!document.documentMode === true ) ) {
        string = 'IE';  //IF IE > 10
    }
    else  {
        string = 'unknown';
    }

    return string;
};

ETO.toast = swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000
});

ETO.swalWithBootstrapButtons = swal.mixin({
    confirmButtonClass: 'btn btn-success',
    cancelButtonClass: 'btn btn-danger',
    buttonsStyling: false,
    reverseButtons: true,
    showCloseButton: true,
    focusConfirm: false,
});

ETO.isMobile = ('ontouchstart' in document.documentElement) && (/Mobi/.test(navigator.userAgent));

ETO.findLocalItems = function(query)
{
    var i, results = [];
    for (i in localStorage) {
        if (localStorage.hasOwnProperty(i)) {
            if (i.match(query) || (!query && typeof i === 'string')) {
                value = JSON.parse(localStorage.getItem(i));
                results.push({key:i,val:value});
            }
        }
    }
    return results;
};

ETO.uid = function()
{
    var dt = new Date().getTime();
    var uid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
        var r = (dt + Math.random()*16)%16 | 0;
        dt = Math.floor(dt/16);
        return (c=='x' ? r :(r&0x3|0x8)).toString(16);
    });
    return uid;
};

ETO.arrayRemove = function (arr, value) {
    for( var i = 0; i < arr.length; i++){
        if ( arr[i] === value) {
            arr.splice(i, 1);
            i--;
        }
    }

    return arr;
};

ETO.convertDate = function(format, date, isTimestamp)
{
    switch (format) {
        case "jS F Y":
            format = "Do MMMM YYYY";
            break;
        case "j F Y":
            format = "DD MMMM YYYY";
            break;
        case "jS M Y":
            format = "Do MMM YYYY";
            break;
        case "j M Y":
            format = "DD MMMM YYYY";
            break;
        case "Y/m/d":
            format = "YYYY/MM/DD";
            break;
        case "m/d/Y":
            format = "MM/DD/YYYY";
            break;
        case "d/m/Y":
            format = "DD/MM/YYYY";
            break;
        case "Y-m-d":
            format = "YYYY-MM-DD";
            break;
        case "d-m-Y":
            format = "DD-MM-YYYY";
            break;
        case "m-d-Y":
            format = "MM-DD-YYYY";
            break;
        case "Y.m.d":
            format = "YYYY.MM.DD";
            break;
        case "d.m.Y":
            format = "DD.MM.YYYY";
            break;
        case "m.d.Y":
            format = "MM.DD.YYYY";
            break;
        default:
            format = "MM.DD.YYYY";
    }

    if(typeof date != 'undefined') {
        return ETO.parseDateTime(format, date, isTimestamp);
    }

    return format;
};

ETO.convertTime = function(format, date, isTimestamp)
{
    switch (format) {
        case "H:i":
            format = "HH:mm";
            break;
        case "g:i a":
            format = "h:mm a";
            break;
        case "g:i A":
            format = "h:mm A";
            break;
        default:
            format = "HH:mm";
    }

    if(typeof date != 'undefined') {
        return ETO.parseDateTime(format, date, isTimestamp);
    }

    return format;
};

ETO.parseDateTime = function(format, date, isTimestamp)
{
    if(isTimestamp === true) {
        date = parseInt(date) * 1000;
    }

    return moment(date).tz(ETO.config.timezone).format(format);
};

ETO.formatPrice = function(n, c, d, t)
{
    c = isNaN(c = Math.abs(c)) ? 2 : c;
    d = d == undefined ? "." : d;
    t = t == undefined ? "" : t;

    var s = n < 0 ? "-" : "",
        i = String(parseInt(n = Math.abs(Number(n) || 0).toFixed(c))),
        j = (j = i.length) > 3 ? j % 3 : 0,
        formatPrice = s + (j ? i.substr(0, j) + t : "") +
        i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) +
        (c ? d + Math.abs(n - i).toFixed(c).slice(2) : ""),
        markPrice = parseFloat(formatPrice) < 0 ? '-' : '';

    return markPrice +
        ETO.config.currency_symbol +
        formatPrice.toString().replace('-', '') +
        ETO.config.currency_code;
};

ETO.delayCallback = function(callback, ms)
{
    var timer = 0;
    return function() {
        var context = this, args = arguments;
        clearTimeout(timer);
        timer = setTimeout(function() {
            callback.apply(context, args);
        }, ms || 0);
    };
};

ETO.indexObjectValue = function(obj, is, value)
{
    if(typeof obj == 'undefined')
        return null;
    if (typeof is == 'string') {
        return ETO.indexObjectValue(obj, is.split('.'), value);
    } else if (is.length===1 && typeof value != 'undefined') {
        return obj[is[0]] = value;
    } else if (is.length===0) {
        return obj;
    } else {
        return ETO.indexObjectValue(obj[is[0]], is.slice(1), value);
    }
};

ETO.trans = function(key, obj)
{
    var trnslation = ETO.indexObjectValue(ETO.lang, key);

    if (trnslation !== null) {
        if (typeof obj != "undefined") {
            $.each(obj, function (k,v) {
                trnslation = trnslation.replace(':'+k, v);
            })
        }
        return trnslation
    } else {
        return key;
    }
};

ETO.settings = function(key, defaultVal)
{
    var config = ETO.indexObjectValue(ETO.config, key);

    if (config !== null) {
        return config
    } else {
        return typeof defaultVal != "undefined" ? defaultVal : null;
    }
};

ETO.setToObjectByPath = function(obj, path, value)
{
    // protect against being something unexpected
    obj = typeof obj === 'object' ? obj : {};
    // split the path into and array if its not one already
    var keys = Array.isArray(path) ? path : path.split('.');
    // keep up with our current place in the object
    // starting at the root object and drilling down
    var curStep = obj;
    // loop over the path parts one at a time
    // but, dont iterate the last part,
    for (var i = 0; i < keys.length - 1; i++) {
        // get the current path part
        var key = keys[i];

        // if nothing exists for this key, make it an empty object or array
        if (!curStep[key] && !Object.prototype.hasOwnProperty.call(curStep, key)){
            // get the next key in the path, if its numeric, make this property an empty array
            // otherwise, make it an empty object
            var nextKey = keys[i+1];
            var useArray = /^\+?(0|[1-9]\d*)$/.test(nextKey);
            curStep[key] = useArray ? [] : {};
        }
        // update curStep to point to the new level
        curStep = curStep[key];
    }
    // set the final key to our value
    var finalStep = keys[keys.length - 1];
    curStep[finalStep] = value;
};

ETO.escapeHtml = function(text)
{
    if (null === text) {
        return text;
    }

    var map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
};

ETO.uuidHTML = function()
{
    var len = 30;
    var arr = 'qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM';
    var ans = '';
    for (var i = len; i > 0; i--) {
        ans +=
            arr[Math.floor(Math.random() * arr.length)];
    }
    return ans;
};

// load files js and css dynamicaly
ETO.addRequires = function(requires)
{
    $.each(requires, function(key, value) {
        if ($.inArray(value, ETO.requires) == -1) {
            ETO.requires.push(value);
        }
    });
};

ETO.loadExtensionsETO = function(array)
{
    ETO.addRequires(array);
    for (var file in  ETO.requires) {
        var name = ETO.requires[file].charAt(0).toUpperCase() + ETO.requires[file].slice(1);

        if (typeof ETO[name] == 'undefined') {
            ETO.assets('js/eto/eto-' + ETO.requires[file].replace('.', '-') + '.js');
            ETO.initExtension(name);
        }
        else {
            console.log('file ' + name + 'is loaded');
        }
    }
};

ETO.initExtension = function(name)
{
    setTimeout(function () {
        if (typeof ETO[name] != 'undefined') {
            ETO[name].init();
        } else {
            ETO.initExtension(name);
        }
    }, 100);
};

ETO.assets = function (file)
{
    var filetype = file.substring(file.lastIndexOf(".")+1);
    var url = ETO.config.appPath + '/assets/' + file;

    if (filetype=="js"){ //if filename is a external JavaScript file
        var scripts = document.scripts;

        for (var i = 0, max = scripts.length; i < max; i++) {
            if (scripts[i].href == url)
                return;
        }

        var fileref = document.createElement('script');
        fileref.setAttribute("type","text/javascript");
        fileref.setAttribute("src", url);
    } else if (filetype=="css"){ //if filename is an external CSS file
        var cssFiles = document.styleSheets;

        for (var i = 0, max = cssFiles.length; i < max; i++) {
            if (cssFiles[i].href == url)
                return;
        }

        var fileref = document.createElement("link");
        fileref.setAttribute("rel", "stylesheet");
        fileref.setAttribute("type", "text/css");
        fileref.setAttribute("href", url);
    }

    if (typeof fileref!="undefined") {
        document.getElementsByTagName("head")[0].appendChild(fileref);
    }
};

ETO.modalIframe = function(el)
{
    var dataUrl = $(el).attr('data-eto-url');

    if (typeof  dataUrl == 'undefined') { dataUrl =  $(el).attr('href') }

    var url = dataUrl + ((dataUrl.indexOf('?') < 0) ? '?' : '&') + 'tmpl=body';
    var title = $(el).attr('title') ? $(el).attr('title') : $(el).attr('data-title');
    var html = '<iframe src="'+ url +'" frameborder="0" height="400" width="100%"></iframe>';
    var modal = $('#modal-popup');

    if ( $(el).hasClass('btnView') ) {
        modal.addClass('modal-booking-view');
    } else if ( $(el).hasClass('btnViewOnReport') ) {
        modal.addClass('modal-booking-view-report');
    } else if ( $(el).hasClass('btnEdit') ) {
        modal.addClass('modal-booking-edit');
    } else if ( $(el).hasClass('btnCopy') ) {
        modal.addClass('modal-booking-copy');
    } else if ( $(el).hasClass('btnInvoice') ) {
        modal.addClass('modal-booking-invoice');
    } else if ( $(el).hasClass('btnSMS') ) {
        modal.addClass('modal-booking-sms');
    } else if ( $(el).hasClass('btnFeedback') ) {
        modal.addClass('modal-booking-feedback');
    } else if ( $(el).hasClass('btnMeetingBoard') ) {
        modal.addClass('modal-booking-meeting-board');
    }

    if (modal.find('iframe').length > 0) {
        modal.find('iframe').attr('src', url);
    } else {
        modal.find('.modal-body').html(html);
        modal.find('iframe').iFrameResize({
            heightCalculationMethod: 'lowestElement',
            log: false,
            targetOrigin: '*',
            checkOrigin: false
        });
    }

    modal.modal('show');

    return false;
};

ETO.strip = function(html) {
    var tmp = document.createElement("DIV");
    tmp.innerHTML = html;
    return tmp.textContent || tmp.innerText || "";
};

ETO.configFormUpdate = function(inputs, settings) {
    inputs.each(function(key, field) {
        var el = $(this),
            value = ETO.indexObjectValue(settings, el.data("etoRelation") + '.' + el.data("etoKey"));

        // console.log(value, settings, el.data("etoRelation"), el.data("etoKey"));

        if (el.attr("type").localeCompare('checkbox') === 0) {
            el.attr("checked",parseBoolean(value));
        } else if (el.attr("type").localeCompare('radio') === 0) {
            el.closest('.form-group').find('[value="'+value+'"]').attr("checked", true);
        } else if (el.attr("type").localeCompare('text') === 0 || el.attr("type").localeCompare('password') === 0) {
            if(typeof ETO.lang.booking == 'undefined'
                || (null !== value && value.toString().localeCompare(ETO.lang.booking.customPlaceholder) === -1) ) {
                el.val(value);
            }
        }

        if(el.hasClass('colorpicker')) {
            var status = $(this).closest('.form-group').find('.eto-color-btn-clear').data('etoStatus');

            if(null !== value && value.localeCompare(ETO.settings('origin_status_color.'+status)) !== 0) {
                $(this).closest('.form-group').find('.eto-color-btn-clear').removeClass('hidden');

                if($(this).closest('.eto-color-settings').hasClass('hidden')) {
                    // $(this).closest('.eto-color-settings').removeClass('hidden');
                    $('.eto-settings-statusColorSettings').attr('checked', true).change();
                }
            }
        }
        else if(el.hasClass('eto-settings-custom_field_name')) {
            $(this).val(ETO.settings('eto_booking.custom_field.name', ETO.trans('booking.customPlaceholder')).toString());
        }
    });
};

ETO.parseSettings = function(inputs) {
    var data = {};

    inputs.each(function(key, val){
        var relation = $(this).data('etoRelation');
        var dotKey = $(this).data('etoKey');
        if (typeof relation != 'undefined' && typeof dotKey != 'undefined') {
            if (typeof data[relation] == 'undefined') {
                data[relation] = {};
            }
            if ($(this).attr("type").localeCompare('checkbox') === 0) {
                data[relation][dotKey] = $(this).attr("checked") !== undefined && $(this).attr("checked").localeCompare('checked') !== -1;
            } else if ($(this).attr("type").localeCompare('radio') === 0) {
                var checkedEl = $(this).closest('.form-group').find(':checked');
                data[relation][dotKey] = $(this).attr("checked") !== undefined && checkedEl.attr("checked").localeCompare('checked') !== -1 ? checkedEl.val() : $(this).closest('.form-group').find('input:first');
            } else if ($(this).attr("type").localeCompare('text') === 0 || $(this).attr("type").localeCompare('password') === 0) {
                data[relation][dotKey] = $(this).val()
            }
        }
    });
    return data;
};

ETO.saveSettings = function(data) {
    ETO.ajax('set-settings', {
        data: data,
        async: true,
        success: function(response) {},
        complete: function() {}
    });
};

ETO._slicedToArray = function(arr, i) { return ETO._arrayWithHoles(arr) || ETO._iterableToArrayLimit(arr, i) || ETO._nonIterableRest(); };

ETO._nonIterableRest = function() { throw new TypeError("Invalid attempt to destructure non-iterable instance"); };

ETO._iterableToArrayLimit = function(arr, i) {
    var _arr = [];
    var _n = true;
    var _d = false;
    var _e = undefined;
    try {
        for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) {
            _arr.push(_s.value); if (i && _arr.length === i) break;
        }
    } catch (err) {
        _d = true; _e = err;
    } finally {
        try {
            if (!_n && _i["return"] != null) _i["return"]();
        } finally {
            if (_d) throw _e;
        }
    } return _arr;
};

ETO._arrayWithHoles = function(arr) { if (Array.isArray(arr)) return arr; };

ETO.getUrlParams = function(search) {
    var hashes = search.slice(search.indexOf('?') + 1).split('&');
    var params = {};
    hashes.map(function (hash) {
        var _hash$split = hash.split('='),
            _hash$split2 = ETO._slicedToArray(_hash$split, 2),
            key = _hash$split2[0],
            val = _hash$split2[1];

        params[key] = decodeURIComponent(val);
    });
    return params;
};

ETO.UrlExists = function(url) {
    var http = new XMLHttpRequest(),
        fullUrl = '';

    if (url.search('http://') === 0 || url.search('https://') === 0) {
        fullUrl = url;
    } else {
        fullUrl = ETO.config.appPath + '/' + url;
    }

    http.open('HEAD', fullUrl, false);
    http.send();

    return parseInt(http.status) !== 404;
};

ETO.hasPermission = function(value, all) {
    if (typeof value == 'string') {
        value = [value];
    }

    var userPermissions = ETO.current_user.permissions,
        has = 0,
        check = value.length;

    $.each(value, function(id, key) {
        var matches = key.match(/\.\*$/);

        $.each(userPermissions, function (k,v) {
            var reg = typeof v == 'string' ? new RegExp(v) : false;

            if(reg && key.match(reg)) {
                has++;
            }
        });

        if((matches !== null && ETO.findWildcard(userPermissions, key)) || $.inArray(key, userPermissions) >= 0) {
            has++;
        }
    });

    if (all === true) {
        return has === check;
    } else {
        return has > 0;
    }
};

ETO.hasRole = function(value, all) {
    if (typeof value == 'string') {
        value = [value];
    }

    var userRoles = ETO.current_user.roles,
        has = 0,
        check = value.length;

    $.each(value, function(id, key) {
        var matches = key.match(/\.\*$/);

        if((matches !== null &&  ETO.findWildcard(userRoles, key)) || $.inArray(key, userRoles) >= 0) {
            has++;
        }
    });

    if (all === true) {
        return has === check;
    } else {
        return has > 0;
    }
};

ETO.findWildcard = function(array, match) {
    var matches = match.match(/\.\*$/);

    if(matches === null) {
        var isWildcard = false;
        $.each(array, function (k,v) {
            if (v.match(/\.\*$/)) {
                isWildcard = true;
                return false;
            }
        });
        if (!isWildcard) {
            return false;
        }
    }

    var reg = new RegExp(match),
        result = array.filter(function(item){
            var reg2 = new RegExp(item);
            return typeof item == 'string' && (item.match(reg) || (reg2 && match.match(reg2)));
        });

    if (!result || (typeof result == 'object') && result.length === 0) {
        return false;
    }

    return result;
};

ETO.updateFormPlaceholder = function(el) {
    var container = el.closest('.eto-field'),
        type = typeof container.find('.eto-js-inputs').attr('type') != "undefined" ? container.find('.eto-js-inputs').attr('type') : 'text',
        value = type.localeCompare('checkbox') === 0 ? (container.find('.eto-js-inputs:checked').length > 0 ? 1 : 0) : container.find('.eto-js-inputs:not(.tt-hint)').val(),
        tagName = container.find('.eto-js-inputs')[0].tagName,
        isChecked = container.find('.eto-js-inputs:checked').length > 0,
        isSelected = container.find('select.eto-js-inputs').find('option:selected').length > 0;

    if (container.hasClass('eto-field-placeholder-always')
        || (value != null
            && value.length > 0
            && type.localeCompare('checkbox') !== 0)
        || (type.localeCompare('checkbox') === 0
            && isChecked === true)
        || (tagName.localeCompare('SELECT') === 0
            && isSelected === true
            && value.localeCompare('') !== 0)
    ) {
        container.find('.eto-field-btn-clear, .eto-field-placeholder').removeClass('hidden');
        container.addClass('eto-field-has-value');
    }
    else {
        container.find('.eto-field-btn-clear, .eto-field-placeholder').addClass('hidden');
        container.removeClass('eto-field-has-value');
    }
};

ETO.updateFormPlaceholderInit = function(el) {
    el.find('input:not([type="submit"]), textarea, select').each(function() {
        ETO.updateFormPlaceholder($(this));
    }).bind('change keyup', function() {
        ETO.updateFormPlaceholder($(this));
    });
};
