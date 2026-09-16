

ETO.Dispatch = function() {
    var etoFn = {};

    etoFn.config = {
        init: ['page', 'icons', 'routes', 'config_site', 'vehicle', 'service', 'source', 'booking_status', 'payment_type'],
        lang: ['frontend.js', 'map', 'user', 'booking'],
        minWidth: 768,
        layout: '', // TYPE LAYOUT
        view: {},
        stateTimestamp: 1,
        bookingsTabs: {
            next24: {
                page: 'next24',
                name: 'Next 24h',
            },
            latest: {
                page: 'latest',
                name: 'Latest'
            },
            completed: {
                page: 'completed',
                name: 'Completed'
            },
            canceled: {
                page: 'canceled',
                name: 'Canceled'
            },
            all: {
                page: 'all',
                name: 'All'
            },
            // trash: {
            //     page: 'trash',
            //     name: 'Trash'
            // },
            empty: {
                page: 'empty',
                name: 'empty'
            },
        },
    };

    etoFn.init = function(config) {
        ETO.extendConfig(this, config, 'dispatch');

        etoFn.config.settingsGoldenLayout =  {
            settings: {
                selectionEnabled: true,
            },
            dimensions: {
                borderWidth: 1,
            },
            content: [{
                headContainerEl: false,
                type: 'column',
                content:[{
                    type: 'row',
                    content:[{
                        type: 'component',
                        header: { show: false},
                        componentName: 'Add booking',
                        componentState: { newClass: ''  },
                        isClosable: false,
                        id: 'add-booking-container'
                    },{
                        type: 'component',
                        header: { show: false},
                        componentName: 'Map',
                        componentState: { newClass: 'map-container' },
                        isClosable: false,
                        id: 'map-container'
                    }]
                },{
                    type: 'stack', // stack give tobs in container
                    content: etoFn.newTabDefault(),
                    id: 'datatable-bookings',
                }]
            }]
        };

        $('body').on('click', '.actionColumn > .btn-group .pull-left', function() {
            var el = $(this).parent(),
                buttons = el.find('.dropdown-menu').html();

            swal({
                html: buttons,
                showCloseButton: true,
                showCancelButton: false, // There won't be any cancel button
                showConfirmButton: false, // There won't be any confirm button
                focusConfirm: false,
            });
        })
        .on('click', '.btnDelete', function() {
            var id = $(this).attr('data-eto-id'),
                table = $('table [data-eto-id="'+id+'"]').closest('table'),
                bookingChildren = $(this).attr('data-eto-booking-children');

            var html = 'You won\'t be able to revert this!';

            if (bookingChildren > 0) {
                html += '<div style="margin-bottom:20px;" class="eto-booking-delete-parent"><span style="font-weight:bold; color:red;">Important!</span> Please note that deleting main booking will also cause deleting all sub-bookings associated with it.</div>';
            }

            ETO.swalWithBootstrapButtons({
                title: 'Are you sure?',
                html: html,
                type: 'warning',
                showCancelButton: true,
            })
            .then(function(result){
                if (result.value) {
                    ETO.Booking.deleteRecord(id, bookingChildren, table);
                    ETO.toast({
                        type: 'success',
                        title: 'Deleted'
                    });
                }
            });
        })

        .on('click', 'a.sidebar-toggle[data-toggle="offcanvas"]', function() {
            setTimeout(function(){
                ETO.Booking.updateTableHeight();
            }, 300);
        })
        .on('click', '.action-button', function() {
            var button = $(this),
                formId = ETO.uid(),
                data = button.data();

            if ($(this).hasClass('eto-wrapper-booking-edit') === true) {
                $('.swal2-content').LoadingOverlay('show');

                setTimeout(function() {
                    $('.eto-modal-booking-edit').modal('show');
                    $('.eto-modal-booking-edit .eto-form-booking').attr('id', formId);
                    ETO.Booking.Form.config.bookingId[formId] = button.attr('data-eto-id');
                    $('.eto-modal-booking-edit').attr('data-eto-id', button.attr('data-eto-id'));

                    ETO.Booking.Form.initializeForm($('.eto-modal-booking-edit .eto-form-booking'));

                    $('.swal2-content').LoadingOverlay('hide');
                    $('.swal2-close').trigger('click');
                }, 0);
            }
            else if ($(this).hasClass('btnMark') === true) {
                $('.swal2-close').trigger('click');

                ETO.ajax('booking2/markBooking/' + data.etoId, {
                    data: {is_read: data.etoIsRead == 1 ? 0 : 1},
                    async: false,
                    success: function(data) {},
                    complete: function() {}
                });

                var table = $('table [data-eto-id="'+ data.etoId+'"]').closest('table');

                table.each(function(key,el){
                    var id = $(el).attr('id'),
                        dTable = new $.fn.dataTable.Api( "#"+id );

                    dTable.ajax.reload(null, false);
                });
            }
            else if (!$(this).hasClass('btnDelete')
                && !$(this).hasClass('btnRemoveFromTrash')
                && !$(this).hasClass('btnRestoreFromTrash')
                && !$(this).hasClass('eto-notifications')
                && !$(this).hasClass('eto-btn-booking-tracking')
            ) {
                $('.swal2-close').trigger('click');
                ETO.Booking.modalIframe(this);
            }
            else if ($(this).hasClass('eto-btn-booking-tracking') || $(this).hasClass('eto-notifications')) {
                $('.swal2-close').trigger('click');
            }
        });

        $('.eto-modal-booking-edit').on('hidden.bs.modal', function() {
            if ($(this).find('.eto-form-booking').attr('id') != '') {
                var formId = $(this).find('.eto-form-booking').attr('id');

                delete ETO.config.driver;
                delete ETO.config.customer;
                delete ETO.Form.config.form.values[formId];
                delete ETO.Booking.Form.config.driverId[formId];
                delete ETO.Booking.Form.config.values[formId];
                delete ETO.Booking.Form.config.bookingId[formId];
                delete ETO.Booking.Form.config.refNumber[formId];
                delete ETO.Booking.Form.config.oldData[formId];
            }
            $(this).find('.eto-form-booking').html('');
            $(this).find('.eto-form-booking').attr('id', '');

            var modal = $(this),
                buttonId = modal.attr('data-eto-id'),
                table = $('table [data-eto-id="'+buttonId+'"]').closest('table');

            modal.attr('data-eto-id', false);

            table.each(function(key,el){
                var id = $(el).attr('id'),
                    dTable = new $.fn.dataTable.Api( "#"+id );
                dTable.ajax.reload(null, false);
            });
        });

        $('#dispatch').on( 'resize', function(){
            if (typeof ETO.Booking != 'undefined') {
                if (typeof ETO.Booking.updateTableHeight != 'undefined') {
                    ETO.Booking.updateTableHeight();
                }
            }
        });

        etoFn.generateLayoutTabs();

        $(window).resize(function() {
            ETO.Booking.updateTableHeight();
            if (etoFn.config.layout == "goldenlayout") {
                etoFn.config.view.updateSize();
            }
        });
    };

    etoFn.refreshDatatableAfterSaveBooking = function() {
        var table = $('table');
        table.each(function(key,el){
            var id = $(el).attr('id'),
                dTable = new $.fn.dataTable.Api( "#"+id );
            dTable.ajax.reload(null, false);
        });
    };

    etoFn.newTabSet = function(idContainer, prefix, id, nameTab, parameters) {
        if(typeof id == 'undefined') {
            return false;
        }

        var nameBox = prefix + '-' + id,
            isTab = etoFn.config.view.root.getItemsById( nameBox ).length > 0 ? true : false,
            tabs = etoFn.config.view.root.getItemsById( idContainer ),
            newItemConfig = etoFn.newTabConfig(nameBox, nameTab, true, false, {id: parseInt(id), params: parameters});

        if (isTab) { return false; }

        tabs[0].addChild( newItemConfig );

        ETO.Booking.createTable(nameBox, parameters, 'active');
    };

    // generate object with default bookings tabs to display
    etoFn.newTabDefault = function() {
        var struct = [];

        for (var nameBox in etoFn.config.bookingsTabs) {
            var isClosable = nameBox == 'empty' ? true : false;
            struct.push(etoFn.newTabConfig(nameBox, etoFn.config.bookingsTabs[nameBox].name, isClosable, true));
        }

        return struct;
    };

    etoFn.newTabConfig = function(nameBox, nameTab, isClosable, isDefault, params) {
        var name = (isDefault === true) ? nameTab : 'New';

        if (typeof params == 'undefined') {
            params = {};
        }

        params.newClass = nameBox;
        params.nameTab = nameTab;
        isClosable = (isClosable === false) ? isClosable : true;

        return {
            type: 'component',
            componentName: name,
            componentState: params,
            isClosable: isClosable,
            id: nameBox,
        };
    };

    // create default structure leyaut on view
    etoFn.generateLayoutTabs = function() {
        if ($(window).width() <= etoFn.config.minWidth) {
            etoFn.config.layout = "bootstrap";
            etoFn.bootstrapLayout();
        }
        else {
            etoFn.config.layout = "goldenlayout";
            etoFn.goldenlayout();
        }
    };

    etoFn.goldenlayout = function() {
        etoFn.generateLayoutTabsConfig();

        if (ETO.hasPermission(['admin.bookings.create'])) {
            etoFn.config.view.registerComponent('Add booking', function (container, componentState) {
                container.getElement().html('<div class="col-xs-12 col-md-12 dispatch-create eto-form eto-form-booking" id="' + ETO.uid() + '"></div>');

                container.on('resize', function () {
                    // Add class to booking when the screen it small
                    var dbc = $('.dispatch-create .etoAdminContainer');
                    dbc.removeClass('dispatch-create-viewport-sm');
                    dbc.removeClass('dispatch-create-viewport-xs');

                    if ($('#dispatch .dispatch-create').innerWidth() <= 780) {
                        $('.dispatch-create .etoAdminContainer').addClass('dispatch-create-viewport-sm');
                    }

                    if ($('#dispatch .dispatch-create').innerWidth() <= 500) {
                        $('.dispatch-create .etoAdminContainer').addClass('dispatch-create-viewport-xs');
                    }
                });
            });
        } else {
            if(typeof etoFn.config.view.config.content[0].content[0].content[1] != 'undefined') {
                etoFn.config.view.config.content[0].content[0].content = [etoFn.config.view.config.content[0].content[0].content[1]];
                delete etoFn.config.view.config.content[0].content[0].content[1];
            }
        }

        etoFn.registerBookingTab('New', true);
        etoFn.config.view.registerComponent('Map', function( container, componentState ) {
            container.getElement().html('\
                <div class="dispatch-drivers-map">\
                    <div style="display:none;">\
                        <button id="eto-btn-map-settings" type="button" class="controls btn btn-sm eto-btn-map-settings" data-toggle="modal" data-target=".eto-modal-map-settings">\
                            <i class="fa fa-cogs"></i>\
                        </button>\
                    </div>\
                    <div id="map-find-driver">\
                        <select class="eto-driver-map-search"></select>\
                    </div>\
                    <div id="map"></div>\
                    <div class="dispatch-drivers-list clearfix">\
                        <div class="splitter clearfix"><hr></div>\
                        <span class="available clearfix"></span>\
                        <span class="busy clearfix"></span>\
                        <span class="away clearfix"></span>\
                        <span class="unavailable clearfix"></span>\
                        <div class="empty-drivers">All drivers are offline at the moment.</div>\
                    </div>\
                </div>');
        });

        // generate all dafoult tabs with bookings
        for (var nameBox in etoFn.config.bookingsTabs) {
            var $page = etoFn.config.bookingsTabs[nameBox];

            etoFn.registerBookingTab($page.name);
        }

        etoFn.config.view.init();
        // etoFn.config.view.root.contentItems[0].contentItems[1].config.activeItemIndex = 0;

        if (typeof ETO.Booking != 'undefined') {
            if (typeof ETO.Booking.updateTableHeight != 'undefined') {
                ETO.Booking.updateTableHeight();
            }
        }

        etoFn.config.view.on('stateChanged', function() {
            var state = JSON.stringify( etoFn.config.view.toConfig() );
            localStorage.setItem('ETO_admin_dispatch_'+ etoFn.config.stateTimestamp, state);

            // Add class to booking when the screen it small
            var dbc = $('.dispatch-create .etoAdminContainer');
            dbc.removeClass('dispatch-create-viewport-sm');
            dbc.removeClass('dispatch-create-viewport-xs');

            if ($('#dispatch .dispatch-create').innerWidth() <= 780) {
                $('.dispatch-create .etoAdminContainer').addClass('dispatch-create-viewport-sm');
            }

            if ($('#dispatch .dispatch-create').innerWidth() <= 500) {
                $('.dispatch-create .etoAdminContainer').addClass('dispatch-create-viewport-xs');
            }
        });
        etoFn.config.view.on('itemDestroyed', function(){
            ETO.updateTooltip();
        });

        etoFn.resizeDriverList();
    };

    etoFn.bootstrapLayout = function() {
        var mainBox = $('#dispatch');

        mainBox.html('<div class="row dispatch-top">\
            <div class="col-xs-12 col-md-6 dispatch-create eto-form eto-form-booking bf-style-2" id="'+ETO.uid()+'"></div>\
            <div class="col-xs-12 col-md-6 dispatch-drivers">\
            <div class="dispatch-drivers-map">\
            <div style="display:none;">\
             <button id="eto-btn-map-settings" type="button" class="controls btn btn-sm eto-btn-map-settings" data-toggle="modal" data-target=".eto-modal-map-settings">\
             <i class="fa fa-cogs"></i>\
             </button>\
            </div>\
             <div id="map-find-driver">\
                <select class="eto-driver-map-search"></select>\
             </div>\
             <div id="map"></div>\
            </div>\
            <div class="dispatch-drivers-list clearfix">\
            <div class="splitter clearfix" style="background-color: #333;"><hr></div>\
            <span class="available clearfix"></span>\
            <span class="busy clearfix"></span>\
            <span class="away clearfix"></span>\
            <span class="unavailable clearfix"></span>\
            </div>\
            </div>\
            </div>\
            <div class="row dispatch-bottom">\
            <div class="col-xs-12 dispatch-bookings" >\
            <div class="nav-tabs-custom">\
            <ul class="nav nav-tabs"></ul>\
            <div class="tab-content"></div>\
            </div>\
            </div>\
            </div>');

        for (var nameBox in etoFn.config.bookingsTabs) {
            var active = '';
            if (nameBox === 'next24' ) {
                active = 'active';
            }
            ETO.Booking.createTable(nameBox, etoFn.config.bookingsTabs[nameBox], active);
        }

        etoFn.resizeDriverList();
    };

    etoFn.generateLayoutTabsConfig = function() {
        var savedState = localStorage.getItem('ETO_admin_dispatch_'+ etoFn.config.stateTimestamp);

        if (savedState !== null) {
            savedState = JSON.parse(savedState);

            var empty = false,
                tabs = savedState.content[0].content[1].content;

            for (var i in tabs) {
                if (typeof tabs[i] != 'undefined' &&  typeof tabs[i].componentState != 'undefined' &&  tabs[i].componentState.newClass == 'empty') {
                    empty = true;
                    break;
                }
            }

            if (!empty) {
                savedState.content[0].content[1].content.push(etoFn.newTabConfig('empty','empty', true, false));
            }
            etoFn.config.view = new GoldenLayout( savedState, '#dispatch' );
        }
        else {
            etoFn.config.view = new GoldenLayout( etoFn.config.settingsGoldenLayout, '#dispatch' );
        }
    };

    etoFn.closeTab = function( container, componentState, tabName ) {
        if (componentState.newClass == tabName) {
            container.on( 'open', function() {
                container.close();
            });
        }
    };

    etoFn.registerBookingTab = function(name, setTitle) {
        etoFn.config.view.registerComponent( name, function( container, componentState ){
            var content = container.getElement();
            if (setTitle === true) {
                container.setTitle( componentState.nameTab );
            }
            etoFn.onActiveTab( container, componentState, content );
            etoFn.closeTab( container, componentState, 'empty' );
        });
    };

    etoFn.onActiveTab = function( container, componentState, content ) {
        content.addClass(componentState.newClass);
        container.on( 'resize', function(){
            if (typeof ETO.Booking != 'undefined') {
                if (typeof ETO.Booking.updateTableHeight != 'undefined') {
                    ETO.Booking.updateTableHeight();
                }
            }
        });
        container.on( 'show', function(){
            if (typeof ETO.Booking != 'undefined') {
                if (typeof ETO.Booking.createTable != 'undefined') {
                    var params = typeof componentState.params != 'undefined' ? componentState.params : etoFn.config.bookingsTabs[componentState.newClass];

                    ETO.Booking.createTable(componentState.newClass, params);
                    ETO.Booking.updateTableHeight();
                }
            }
        });
        container.on( 'hide', function(){
            if (typeof ETO.Booking != 'undefined') {
                if (typeof ETO.Booking.tableList != 'undefined') {
                    ETO.Booking.config.tableList = etoFn.arrayRemove(ETO.Booking.config.tableList, componentState.newClass);
                    content.html('');
                }
            }
        });
    };

    etoFn.arrayRemove = function(arr, value) {
        return arr.filter(function(ele){
            return ele != value;
        });
    };

    etoFn.resizeDriverList = function () {
        var heightDriverList = localStorage.getItem('ETO_map_driver_list_height'+ ETO.config.timestamp),
            resizeConf = {
                handleSelector: ".splitter",
                resizeWidth: false,
                resizeHeightFrom: 'top',
                onDragEnd: function (e, $el, opt) {
                    localStorage.setItem('ETO_map_driver_list_height'+ ETO.config.timestamp, $el.height());
                }
            };

        if ( heightDriverList !== null ) {
            $(".dispatch-drivers-list").height( heightDriverList );
        }

        $(".dispatch-drivers-list").resizable(resizeConf);
    };

    return etoFn;
}();
