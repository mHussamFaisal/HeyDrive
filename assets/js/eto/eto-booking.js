

ETO.Booking = function() {
    var etoFn = {};

    etoFn.config = {
        init: ['page', 'icons', 'routes', 'config_site', 'vehicle', 'service', 'source', 'booking_status', 'payment_type'],
        lang: ['user', 'booking'],
        tableList:[],
        minHeight: 100,
        initializing: true,
    };

    etoFn.init = function(config) {
        ETO.extendConfig(this, config, 'booking');

        if ( etoFn.config.initializing === true) {
            // what to do with datatable errors
            $.fn.dataTable.ext.errMode = ETO.datatable.errorMassages;

            $('.eto-modal-booking-edit').modal({
                show: false
            });

            // Popup
            $('#modal-popup').modal({
                show: false,
            })
            .on('hidden.bs.modal', function(e) {
                $(this).find('iframe').attr('src', 'about:blank').contents().find('body').append('');

                $(this).removeClass(
                  'modal-booking-delete '+
                  'modal-booking-add '+
                  'modal-booking-report '+
                  'modal-booking-view '+
                  'modal-booking-edit '+
                  'modal-booking-copy '+
                  'modal-booking-invoice '+
                  'modal-booking-sms '+
                  'modal-booking-feedback '+
                  'modal-booking-meeting-board'
                );
            });

            $('body').on('click', '.closeTab', function() {
                //there are multiple elements which has .closeTab icon so close the tab whose close icon is clicked
                var tabContentId = $(this).parent().find('a').attr('href');
                $(this).parent().remove(); //remove li of tab
                $('.nav-tabs a:last').tab('show'); // Select first tab
                $(tabContentId).remove(); //remove respective tab content
            })

            .on('click', '.payment-status', function(e) {
                e.preventDefault();
                etoFn.modalIframe(this);
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
                            ETO.Booking.deleteRecord(id, bookingChildren, table, 'destroy');
                            ETO.toast({
                                type: 'success',
                                title: 'Deleted'
                            });
                        }
                    });
            })

            .on('click', '.btnRemoveFromTrash', function(e) {
                e.preventDefault();
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
                            ETO.Booking.deleteRecord(id, bookingChildren, table, 'removeFromTrash');
                            ETO.toast({
                                type: 'success',
                                title: 'Deleted'
                            });
                        }
                    });
            })

            .on('click', '.btnRestoreFromTrash', function(e) {
                var id = $(this).attr('data-eto-id'),
                    table = $('table [data-eto-id="'+id+'"]').closest('table'),
                    bookingChildren = $(this).attr('data-eto-booking-children');

                ETO.Booking.deleteRecord(id, bookingChildren, table, 'restoreFromTrash');
                swal.clickCancel();

            });

            // Table height
            etoFn.updateTableHeight();
        }
    };

    etoFn.columns = function(param, page) {
        var setCols = [],
            roles = {
                admin: 'all',
                driver: [],
                customer: []
            },
            cols = {
                actions: {
                    title: ETO.trans('booking.actions'),
                    data: null,
                    defaultContent: '',
                    orderable: false,
                    className: 'actionColumn'
                },
                additional_info: {
                    title: ETO.trans('booking.info'),
                    data: 'additional_info',
                    width: 'auto',
                    orderable: false
                },
                date: {
                    title: ETO.trans('booking.date'),
                    data: 'date',
                    width: '130px'
                },
                ref_number: {
                    title: ETO.trans('booking.ref_number'),
                    data: 'ref_number',
                    width: '175px'
                },
                status_btn: {
                    title: ETO.trans('booking.status'),
                    data: 'status_btn',
                    width: '107px'
                }
            };

        if (!ETO.hasRole('admin.fleet_operator')) {
            $.extend(true, cols, {
                payment_status: {
                    title: ETO.trans('booking.payments'),
                    data: 'payment_status',
                    width: '120px',
                    sortable: false
                }
            });
        }

        if (parseInt(ETO.settings('booking.allow_fleet_operator', 0)) === 1 && !ETO.hasRole('admin.fleet_operator')) {
            $.extend(true, cols, {
                fleet_btn: {
                    title: ETO.trans('booking.fleet_name'),
                    data: 'fleet_btn',
                    width: '122px'
                }
            });
        }

        if (parseInt(ETO.settings('booking.allow_fleet_operator', 0)) === 1) {
            $.extend(true, cols, {
                fleet_commission_btn: {
                    title: ETO.trans('booking.fleet_commission') +' <i class="ion-ios-information-outline" style="color:#a0a0a0; font-size:16px;" title="'+ ETO.trans('booking.fleet_commission_info') +'"></i>',
                    data: 'fleet_commission_btn',
                    width: '77px'
                }
            });
        }

        $.extend(true, cols, {
            driver_btn: {
                title: ETO.trans('booking.driver_name'),
                data: 'driver_btn',
                width: '122px'
            },
            vehicle_btn: {
                title: ETO.trans('booking.vehicle_name'),
                data: 'vehicle_btn',
                width: '122px'
            }
        });

        if (!ETO.hasRole('admin.fleet_operator')) {
            $.extend(true, cols, {
                total: {
                    title: ETO.trans('booking.total'),
                    data: 'total',
                    width: '77px',
                    sortable: false
                }
            });
        }

        $.extend(true, cols, {
            commission_btn: {
                title: ETO.trans('booking.commission') + ' <i class="ion-ios-information-outline" style="color:#a0a0a0; font-size:16px;" data-toggle="popover" data-title="" data-content="' + ETO.trans('booking.commission_info') + '"></i>',
                data: 'commission_btn',
                width: '77px'
            },
            cash_btn: {
                title: ETO.trans('booking.cash') + ' <i class="ion-ios-information-outline" style="color:#a0a0a0; font-size:16px;" data-toggle="popover" data-title="" data-content="' + ETO.trans('booking.cash_info') + '"></i>',
                data: 'cash_btn',
                width: '77px'
            }
        });

        if (!ETO.hasRole('admin.fleet_operator')) {
            $.extend(true, cols, {
                service_id: {
                    title: ETO.trans('booking.service_type'),
                    data: 'service_id',
                    width: '200px',
                    class: 'column-service_id'
                },
                service_duration: {
                    title: ETO.trans('booking.service_duration'),
                    data: 'service_duration',
                    width: '60px',
                    class: 'column-service_duration'
                },
                scheduled_route_id: {
                    title: ETO.trans('booking.scheduled_route_id'),
                    data: 'scheduled_route_id',
                    width: '200px',
                    class: 'column-scheduled_route_id'
                }
            });
        }

        $.extend(true, cols, {
            contact_name: {
                title: ETO.trans('booking.contact_name'),
                data: 'contact_name',
                width: '148px'
            },
            flight_number: {
                title: ETO.trans('booking.flight_number'),
                data: 'flight_number',
                width: '122px'
            },
            flight_landing_time: {
                title: ETO.trans('booking.flight_landing_time'),
                data: 'flight_landing_time',
                width: '122px'
            },
            departure_city: {
                title: ETO.trans('booking.departure_city'),
                data: 'departure_city',
                width: '122px'
            },
            departure_flight_number: {
                title: ETO.trans('booking.departure_flight_number'),
                data: 'departure_flight_number',
                width: '122px'
            },
            departure_flight_time: {
                title: ETO.trans('booking.departure_flight_time'),
                data: 'departure_flight_time',
                width: '122px'
            },
            departure_flight_city: {
                title: ETO.trans('booking.departure_flight_city'),
                data: 'departure_flight_city',
                width: '122px'
            },
            contact_mobile: {
                title: ETO.trans('booking.contact_mobile'),
                data: 'contact_mobile',
                width: '140px'
            },
            from: {
                title: ETO.trans('booking.from'),
                data: 'from',
                width: '220px'
            },
            to: {
                title: ETO.trans('booking.to'),
                data: 'to',
                width: '220px'
            },
            waypoints: {
                title: ETO.trans('booking.via'),
                data: 'waypoints',
                width: '220px'
            },
            passengers: {
                title: ETO.trans('booking.passenger_amount'),
                data: 'passengers',
                width: '122px',
                visible: false
            }
        });

        if (!ETO.hasRole('admin.fleet_operator')) {
            $.extend(true, cols, {
                price: {
                    title: ETO.trans('booking.price'),
                    data: 'price',
                    width: '77px',
                    visible: false
                },
                discount: {
                    title: ETO.trans('booking.discount_price'),
                    data: 'discount',
                    width: '120px',
                    visible: false
                },
                discount_code: {
                    title: ETO.trans('booking.discount_code'),
                    data: 'discount_code',
                    width: '120px',
                    visible: false
                }
            });
        }

        $.extend(true, cols, {
            waiting_time: {
                title: ETO.trans('booking.waiting_time'),
                data: 'waiting_time',
                width: '122px'
            },
            vehicle: {
                title: ETO.trans('booking.vehicle'),
                data: 'vehicle',
                width: '155px'
            },
            contact_email: {
                title: ETO.trans('booking.contact_email'),
                data: 'contact_email',
                width: '134px'
            },
            meet_and_greet: {
                title: ETO.trans('booking.meet_and_greet'),
                data: 'meet_and_greet',
                width: '130px'
            }
        });

        if (!ETO.hasRole('admin.fleet_operator')) {
            $.extend(true, cols, {
                source: {
                    title: ETO.trans('booking.source'),
                    data: 'source',
                    width: '150px'
                }
            });
        }

        $.extend(true, cols, {
            user_name: {
                title: ETO.trans('booking.user_name'),
                data: 'user_name',
                width: '122px',
                sortable: false
            },
            department: {
                title: ETO.trans('booking.departments'),
                data: 'department',
                width: '122px'
            },
            lead_passenger_name: {
                title: ETO.trans('booking.lead_passenger_name'),
                data: 'lead_passenger_name',
                width: '148px'
            },
            lead_passenger_email: {
                title: ETO.trans('booking.lead_passenger_email'),
                data: 'lead_passenger_email',
                width: '148px'
            },
            lead_passenger_mobile: {
                title: ETO.trans('booking.lead_passenger_mobile'),
                data: 'lead_passenger_mobile',
                width: '161px'
            },
            custom: {
                title: function() {
                    return ETO.settings('eto_booking.custom_field.name', ETO.trans('booking.customPlaceholder')).toString();
                }(),
                class: 'eto-custom-column',
                data: 'custom',
                width: '122px',
                visible: false
            },
            created_date: {
                title: ETO.trans('booking.created_at'),
                data: 'created_date',
                width: '140px'
            },
            modified_date: {
                title: ETO.trans('booking.updated_at'),
                data: 'modified_date',
                width: '140px'
            }
        });

        if (!ETO.hasRole('admin.fleet_operator')) {
            $.extend(true, cols, {
                id: {
                    title: ETO.trans('booking.id'),
                    data: 'id',
                    width: '122px',
                    visible: false
                },
                user_id: {
                    title: ETO.trans('booking.user_id'),
                    data: 'user_id',
                    width: '122px',
                    visible: false
                }
            });
        }

        if (parseInt(ETO.settings('booking.allow_fleet_operator', 0)) === 1 && !ETO.hasRole('admin.fleet_operator')) {
            $.extend(true, cols, {
                fleet_id: {
                   title: ETO.trans('booking.fleet_id'),
                   data: 'fleet_id',
                   width: '122px',
                   visible: false
                }
            });
        }

        if (!ETO.hasRole('admin.fleet_operator')) {
            $.extend(true, cols, {
                driver_id: {
                    title: ETO.trans('booking.driver_id'),
                    data: 'driver_id',
                    width: '122px',
                    visible: false
                },
                vehicle_id: {
                    title: ETO.trans('booking.vehicle_id'),
                    data: 'vehicle_id',
                    width: '122px',
                    visible: false
                }
            });
        }

        $.extend(true, cols, {
            is_read_formatted: {
                title: ETO.trans('booking.is_read'),
                data: 'is_read_formatted',
                width: '122px',
                visible: false
            }
        });

        if ((page == 'trash' & !ETO.hasPermission('admin.bookings.destroy') && !ETO.hasPermission('admin.bookings.restore'))
            || (!ETO.hasPermission('admin.bookings.edit')
                && !ETO.hasPermission('admin.bookings.show')
                && !ETO.hasPermission('admin.bookings.tracking')
                && !ETO.hasPermission('admin.bookings.transactions')
                && !ETO.hasPermission('admin.bookings.invoice')
                && !ETO.hasPermission('admin.bookings.create')
                && !ETO.hasPermission('admin.bookings.sms')
                && !ETO.hasPermission('admin.feedback.show')
                && !ETO.hasPermission('admin.bookings.meeting_board')
                && !ETO.hasPermission('admin.bookings.mark_as_read')
                && !ETO.hasPermission('admin.bookings.notifications')
                && !ETO.hasPermission('admin.activity.index')
                && !ETO.hasPermission('admin.bookings.trash')
            )
        ) {
            delete cols.actions;
        }

        if ((typeof param != 'undefined' && typeof roles[param] != 'undefined' && roles[param].localeCompare('all') === 0)
            || typeof param == 'undefined'
        ) {
            $.each(cols, function(key, value){
                setCols.push(value);
            });
        }
        else if (typeof param == 'string') {
            $.each(roles[param], function(key, value){
                setCols.push(cols[value]);
            });
        }
        else if (typeof param == 'object') {
            $.each(cols, function(key, value){
                if ($.inArray(key, param) !== -1) {
                    setCols.push(value);
                }
            });
        }

        if (page == 'trash') {
            setCols.push({
                title: ETO.trans('booking.deleted_at'),
                data: 'deleted_at',
                width: '122px',
                visible: false
            });
        }

        return setCols;
    };

    etoFn.actions = function(row,  buttonsToGet) {
        var actions = '',
            html = '',
            isReadTrans = row.is_read == '0' ? 'markAsRead' : 'markAsUnread',
            buttons = {
            edit: '<button type="button" data-eto-id="'+ row.id +'" class="btn btn-link action-button eto-wrapper-booking-edit" data-title="' + ETO.trans('booking.button.edit') + ' #'+ row.ref_number +'">\
                <span style="display:inline-block; width:20px; text-align:center;">\
                    <i class="fa fa-pencil-square-o"></i>\
                </span>\
                ' + ETO.trans('booking.button.edit') + '\
              </button>',
            activity: '<button type="button" data-eto-id="'+ row.id +'" data-eto-url="' + ETO.config.appPath + '/activity?subject=booking&subject_id='+ row.id +'" class="btn btn-link action-button eto-wrapper-booking-activity" data-title="' + ' #'+ row.ref_number +' ' + ETO.trans('booking.button.activity') + '">\
                <span style="display:inline-block; width:20px; text-align:center;">\
                    <i class="fa fa-shield"></i>\
                </span>\
                ' + ETO.trans('booking.button.activity') + '\
              </button>',
            tracking: '<button type="button" data-eto-id="'+ row.id +'" class="btn btn-link action-button eto-btn-booking-tracking" data-title="' + ETO.trans('booking.button.tracking') + ' #'+ row.ref_number +'">\
                <span style="display:inline-block; width:20px; text-align:center;">\
                    <i class="fa fa-map-marker"></i>\
                </span>\
                ' + ETO.trans('booking.button.tracking') + '\
              </button>',
            transactions: '<button type="button" data-eto-id="'+ row.id +'" data-eto-url="' + row.url_transactions + '" class="btn btn-link action-button btnTransactions" data-title="' + ETO.trans('booking.button.transactions') + ' #' + row.ref_number + '">\
                <span style="display:inline-block; width:20px; text-align:center;">\
                <i class="fa fa-credit-card"></i>\
                </span>\
                ' + ETO.trans('booking.button.transactions') + '\
              </button>',
            invoice: ' <button type="button" data-eto-id="'+ row.id +'" data-eto-url="' + row.url_invoice + '" class="btn btn-link action-button btnInvoice" data-title="' + ETO.trans('booking.button.invoice') + ' #' + row.ref_number + '">\
                <span style="display:inline-block; width:20px; text-align:center;">\
                    <i class="fa fa-file-pdf-o"></i>\
                </span>\
                ' + ETO.trans('booking.button.invoice') + '\
              </button>',
            copy: '<button type="button" data-eto-id="'+ row.id +'" data-eto-url="' + row.url_copy + '" class="btn btn-link action-button btnCopy" data-title="' + ETO.trans('booking.button.copy') + ' #' + row.ref_number + '">\
                <span style="display:inline-block; width:20px; text-align:center;">\
                    <i class="fa fa-files-o"></i>\
                </span>\
                ' + ETO.trans('booking.button.copy') + '\
              </button>',
            sms: ' <button type="button" data-eto-id="'+ row.id +'" data-eto-url="' + row.url_sms + '" class="btn btn-link action-button btnSMS" data-title="' + ETO.trans('booking.button.sms') + ' #' + row.ref_number + '">\
                <span style="display:inline-block; width:20px; text-align:center;">\
                    <i class="fa fa-commenting"></i>\
                </span>\
                ' + ETO.trans('booking.button.sms') + '\
              </button>',
            feedback: '<button type="button" data-eto-id="'+ row.id +'" data-eto-url="' + row.url_feedback + '" class="btn btn-link action-button btnFeedback"  data-title="' + ETO.trans('booking.button.feedback') + ' #' + row.ref_number + '">\
                <span style="display:inline-block; width:20px; text-align:center;">\
                    <i class="fa fa-comments-o"></i>\
                </span>\
                ' + ETO.trans('booking.button.feedback') + '\
              </button>',
            meeting_board: '<button type="button" data-eto-id="'+ row.id +'" data-eto-url="' + row.url_meeting_board + '" class="btn btn-link action-button btnMeetingBoard" data-title="' + ETO.trans('booking.button.meeting_board') + ' #' + row.ref_number + '">\
                <span style="display:inline-block; width:20px; text-align:center;">\
                    <i class="fa fa-address-card-o"></i>\
                </span>\
                ' + ETO.trans('booking.button.meeting_board') + '\
              </button>',
            destroy: '<button type="button" data-eto-id="'+ row.id +'" data-eto-booking-children="'+ row.booking_children +'" class="btn btn-link action-button btnDelete" data-title="' + ETO.trans('booking.button.destroy') + ' #'+ row.ref_number +'">\
                <span style="display:inline-block; width:20px; text-align:center;">\
                    <i class="fa fa-trash"></i>\
                </span>\
                ' + ETO.trans('booking.button.destroy') + '\
              </button>',
            is_read: '<button type="button" data-eto-id="'+ row.id +'" data-eto-booking-children="'+ row.booking_children +'" class="btn btn-link action-button btnMark" data-eto-is-read="'+row.is_read+'" data-title="' + ETO.lang.booking.button[isReadTrans] + ' #'+ row.ref_number +'">\
                <span style="display:inline-block; width:20px; text-align:center;">\
                    <i class="fa fa-eye'+ (row.is_read == '0' ? '' : '-slash') +'"></i>\
                </span>\
                ' + ETO.lang.booking.button[isReadTrans] + '\
              </button>',
            notifications: '<button type="button" data-eto-id="'+ row.id +'" class="btn btn-link  action-button eto-notifications" data-eto-is-read="'+row.is_read+'" data-title="' + ETO.trans('booking.button.notifications') + ' #'+ row.ref_number +'">\
                <span style="display:inline-block; width:20px; text-align:center;">\
                    <i class="fa fa-bell"></i>\
                </span>\
                ' + ETO.trans('booking.button.notifications') + '\
              </button>',
            restore_from_trash: '<button type="button" data-eto-id="'+ row.id +'" data-eto-booking-children="'+ row.booking_children +'" class="btn btn-link action-button btnRestoreFromTrash" data-title="' + ETO.trans('booking.button.restore_from_trash') + ' #'+ row.ref_number +'">\
                <span style="display:inline-block; width:20px; text-align:center;">\
                    <i class="fa fa-reply"></i>\
                </span>\
                ' + ETO.trans('booking.button.restore_from_trash') + '\
              </button>',
            remove_from_trash: '<button type="button" data-eto-id="'+ row.id +'" data-eto-booking-children="'+ row.booking_children +'" class="btn btn-link action-button btnRemoveFromTrash" data-title="' + ETO.trans('booking.button.remove_from_trash') + ' #'+ row.ref_number +'">\
                <span style="display:inline-block; width:20px; text-align:center;">\
                    <i class="fa fa-trash"></i>\
                </span>\
                ' + ETO.trans('booking.button.remove_from_trash') + '\
              </button>',
        };

        for (var i in buttonsToGet) {
            if((buttonsToGet[i] == 'edit' && !ETO.hasPermission('admin.bookings.edit'))
                || (buttonsToGet[i] == 'tracking' && !ETO.hasPermission('admin.bookings.tracking'))
                || (buttonsToGet[i] == 'transactions' && !ETO.hasPermission('admin.bookings.transactions'))
                || (buttonsToGet[i] == 'invoice' && !ETO.hasPermission('admin.bookings.invoice'))
                || (buttonsToGet[i] == 'copy' && !ETO.hasPermission('admin.bookings.create'))
                || (buttonsToGet[i] == 'sms' && !ETO.hasPermission('admin.bookings.sms'))
                || (buttonsToGet[i] == 'feedback' && !ETO.hasPermission('admin.feedback.show'))
                || (buttonsToGet[i] == 'meeting_board' && !ETO.hasPermission('admin.bookings.meeting_board'))
                || (buttonsToGet[i] == 'is_read' && !ETO.hasPermission('admin.bookings.mark_as_read'))
                || (buttonsToGet[i] == 'notifications' && !ETO.hasPermission('admin.bookings.notifications'))
                || (buttonsToGet[i] == 'activity' && (!ETO.hasPermission('admin.activity.index') || parseInt(ETO.settings('booking.allow_activitylog', 0)) === 0))
                || (buttonsToGet[i] == 'destroy' && !ETO.hasPermission('admin.bookings.trash'))
                || (buttonsToGet[i] == 'remove_from_trash' && !ETO.hasPermission('admin.bookings.destroy'))
                || (buttonsToGet[i] == 'restore_from_trash' && !ETO.hasPermission('admin.bookings.restore'))
            ) {
                continue;
            }

            actions += buttons[buttonsToGet[i]];
        }

        if (actions != '') {
            html = '<div class="btn-group dropup" role="group" aria-label="..." style="width:70px;">';
        }

        if (ETO.hasPermission('admin.bookings.show')) {
            html += '<button data-eto-url="' + row.url_show + '" onclick="ETO.Booking.modalIframe(this); return false;" class="btn btn-default btn-sm btnView" data-title="' + ETO.trans('booking.button.show')
                + ' #' + row.ref_number + '"><i class="fa fa-eye"></i></button>';
        }

        if (actions != '') {
            html += '<div class="btn-group pull-left" role="group">' +
                '<button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-expanded="false">' +
                '<span class="fa fa-angle-down"></span>' +
                '</button>' +
                '<ul class="dropdown-menu hidden" role="menu">' +
                '<div class="box-menu">' +
                actions +
                '</div>' +
                '</ul>' +
                '</div>' +
                '</div>';
        }

        return html;
    };

    etoFn.updateTableHeight = function() {
        var that = this;
        setTimeout(function() {
            for (var i = 0, len = that.config.tableList.length; i < len; i++) {
                var className = that.config.tableList[i],
                    container = $('.lm_content.'+className),
                    height = container.height() -
                    container.find('.bottomContainer').height() -
                    container.find('.dataTables_scrollHead').height() - 14;

                if ( height < etoFn.config.minHeight ) {
                    height = etoFn.config.minHeight;
                }

                container.find('.dataTables_scrollBody').css({'height': height +'px'});
            }
        }, 0);
    };

    etoFn.modalIframe = function(el) {
        ETO.modalIframe(el);
    };

    etoFn.deleteRecord = function(id, bookingChildren, table, adction) {
        ETO.ajax('etov2?apiType=backend', {
            data: {
                task: 'bookings',
                action: adction,
                id: id,
                bookingChildren: bookingChildren
            },
            success: function(response) {
                if (response.success) {
                    table.each(function(key,el){
                        var id = $(el).attr('id'),
                            dTable = new $.fn.dataTable.Api( "#"+id );

                        dTable.ajax.reload(null, false);
                    });
                }
                else { alert('The booking could not be deleted!'); }
            },
        });
    };

    etoFn.filterTable = function(parameters){
        var filters = {
            'filter-driver-name': [],
            'filter-start-date': '',
            'filter-end-date': '',
            'filter-date-type': '',
            'filter-keywords': '',
            'filter-scheduled_route_id': '',
            'filter-parent_booking_id': '',
        };

        for (var key in parameters) {
            if (typeof  filters[key] != 'undefined') {
                filters[key] = parameters[key];
            }
        }

        return $.param(parameters);
    };

    /**
     *  reftesh data in all tables
     * @param datatable
     */
    etoFn.refreshTable = function(datatable) {
        if ( ETO.config.booking.refresh_type > 0 ) {
            var refreshInterval = ETO.config.booking.refresh_interval; // Seconds
            var nofitfyInterval = refreshInterval;
            var refreshTime = parseInt(moment().format('X')) + refreshInterval;
            var i = nofitfyInterval;

            setInterval(function () {
                var secs = parseInt(moment().format('X'));

                if (ETO.config.booking.refresh_counter > 0) {
                    if (secs > refreshTime - nofitfyInterval) {
                        if ($('#reload-counter').length > 0) {
                            $('#reload-counter').html(i);
                        }
                        else {
                            $('.buttons-reload').append('<span id="reload-counter">' + i + '</span>');
                        }
                        i--;
                    }
                    else {
                        $('#reload-counter').remove();
                        i = nofitfyInterval;
                    }
                }

                if (secs > refreshTime) {
                    refreshTime = secs + refreshInterval;
                    i = nofitfyInterval;
                    $('.buttons-reload i').addClass('fa-spin');
                    datatable.ajax.reload(null, false); // user paging is not reset on reload
                    // datatable.draw(); // user paging is not reset on reload
                }
            }, 1000);
        }
    };

    /**
     *  initialize new datatable
     * @param nameBox
     * @param parameters
     * @param active
     * @returns {boolean}
     */
    etoFn.createTable = function(nameBox, parameters, active) {
        if ($('#dtable_'+nameBox).length > 0) {
            if (ETO.Dispatch.config.layout == 'bootstrap') {
                $('.link-'+nameBox).click();
            }
            else if (ETO.Dispatch.config.layout == 'goldenlayout') {
                $('.lm_tab[title='+nameBox+']').click();
            }
            return false;
        }

        if (nameBox == 'empty') { return; }

        var page = '';

        if (typeof parameters !== 'undefined' && typeof parameters.page !== 'undefined') {
            page = parameters.page;
        }

        if (ETO.Dispatch.config.layout == 'bootstrap') {
            var tabHeader = $('.dispatch-bookings').find('.nav-tabs'),
                tabBody = $('.dispatch-bookings').find('.tab-content'),
                closeButton = '<button href="javascript:;" class="closeTab btn btn-link">X</button>';

            if (typeof parameters.page !== 'undefined') {closeButton = '';}
            tabHeader.append('<li class="' + active + ' link-button"><a href="#' + nameBox + '" class="link-' + nameBox + '" data-toggle="tab" aria-expanded="false">' + parameters.name + '</a>' + closeButton + '</li>');
            tabBody.append('<div class="tab-pane ' + active + '" id="' + nameBox + '"><table class="table table-hover" id="dtable_' + nameBox + '" style="width:100%;"></table></div>');
        }
        else if (ETO.Dispatch.config.layout == 'goldenlayout') {
            $('.'+nameBox).append('<table class="table table-hover" id="dtable_'+nameBox+'" style="width:100%;"></table>');
        }

        var pageName = 'all';
        if (parseInt(ETO.settings('booking.allow_fleet_operator', 0)) === 1 && !ETO.hasRole('admin.fleet_operator')) {
            var defaultOrder = [42, 'desc'];
        }
        else {
            var defaultOrder = [40, 'desc'];
        }

        switch (nameBox) {
            case 'next24':
                pageName = 'next24';
                defaultOrder = [2, 'asc'];
            break;
            case 'latest':
                pageName = 'latest';
            break;
            case 'requested':
                pageName = 'requested';
            break;
            case 'completed':
                pageName = 'completed';
            break;
            case 'canceled':
                pageName = 'canceled';
            break;
        }

        // State start
        $('body').on('click', '.eto-refresh-localStorage-'+ nameBox, function(e) {
            if (confirm('Are you sure you would like to reset current columns visibility and sorting settings?') == true) {
                $.each(ETO.findLocalItems('DataTables_(.*)/' + ETO.model), function (i, obj) {
                    localStorage.removeItem(obj.key);
                });

                if (ETO.model.localeCompare('dispatch') === 0) {
                    $.each(ETO.findLocalItems('ETO_admin_dispatch_(.*$)'), function (i, obj) {
                        localStorage.removeItem(obj.key);
                    });
                }

                dtStateSave(null, true);
                // window.location.reload();
            }
        });

        var dtPageKey = pageName;
        var dtStorageKey = 'ETO_admin_dispatch_'+ dtPageKey +'_1';
        if (ETO.config.eto_booking && ETO.config.eto_booking.admin_dispatch_state && ETO.config.eto_booking.admin_dispatch_state[dtPageKey]) {
            var dtCurrentState = dtFixJson(ETO.config.eto_booking.admin_dispatch_state[dtPageKey]);
        } else {
            var dtCurrentState = null;
        }
        var dtIsFirstLoad = true;
        var dtDelayTimer = null;

        function dtFixJson(data) {
            if (data) {
                data = JSON.stringify(data);
                data = data.replace(/\"true\"/g, 'true');
                data = data.replace(/\"false\"/g, 'false');
                data = JSON.parse(data);
            }
            return data;
        }

        function dtStateSave(data, clear) {
            if (dtIsFirstLoad) {
                dtIsFirstLoad = false;
                return false;
            }

            window.localStorage.setItem(dtStorageKey, JSON.stringify(data));
            var changed = false;

            if (typeof data != 'undefined' && data != null && typeof dtCurrentState != 'undefined' && dtCurrentState != null) {
                if (JSON.stringify(data.ColReorder) != JSON.stringify(dtCurrentState.ColReorder)) {
                    changed = true;
                }
                else if (JSON.stringify(data.columns) != JSON.stringify(dtCurrentState.columns)) {
                    changed = true;
                }
                else if (JSON.stringify(data.order) != JSON.stringify(dtCurrentState.order)) {
                    changed = true;
                }
            } else {
                changed = true;
            }

            if (changed || clear == true) {
                dtCurrentState = dtFixJson(data);
                var dtDelayTimerWait = clear == true ? 0 : 2000;
                clearTimeout(dtDelayTimer);
                dtDelayTimer = setTimeout(function() {
                    $.ajax({
                        headers : {
                            'X-CSRF-TOKEN': ETO.config.csrfToken
                        },
                        url: ETO.config.appPath +'/admin/saveDtState',
                        type: 'POST',
                        dataType: 'json',
                        cache: false,
                        data: {
                            type: 'admin_dispatch_state',
                            state: data,
                            page: dtPageKey
                        },
                        success: function(response) {
                            if (response.status == false) {
                                console.log('Dispatch listing table state could not be saved!');
                            } else {
                                if (clear == true) {
                                    window.location.reload();
                                }
                            }
                        }
                    });
                }, dtDelayTimerWait);
            }
        }

        function dtStateLoad(settings, callback) {
            var state = JSON.parse(window.localStorage.getItem(dtStorageKey));
            if (typeof dtCurrentState != 'undefined' && dtCurrentState != null) {
                state = dtFixJson(dtCurrentState);
            }
            callback(state);
        }
        // State end

        var table = $('#dtable_'+ nameBox),
            datatableOptions = {
                processing: true,
                serverSide: true,
                deferLoading: 0,
                ajax: {
                    headers : {
                        'X-CSRF-TOKEN': ETO.config.csrfToken
                    },
                    url: ETO.config.appPath +'/etov2?apiType=backend',
                    type: 'POST',
                    dataType: 'json',
                    cache: false,
                    // async: false,
                    data: {
                        task: 'bookings',
                        action: 'list',
                        page: page
                    },
                    dataSrc: 'bookings',
                },
                columns: etoFn.columns(ETO.current_user.role, page),
                columnDefs: [{
                    targets: 0,
                    data: null,
                    render: function(data, type, row) {
                        var buttons = ['edit', 'tracking', 'transactions', 'invoice', 'copy', 'sms', 'feedback', 'meeting_board', 'is_read', 'notifications', 'activity', 'destroy'];

                        if(page == 'trash') {
                            buttons = ['remove_from_trash','restore_from_trash'];
                        }
                        return etoFn.actions(row, buttons);
                    }
                }],
                colReorder: true,
                paging: true,
                pagingType: 'full_numbers',
                dom: ETO.datatable.dom,
                // buttons: ETO.datatable.buttons(),
                buttons: [{
                    extend: 'colvis',
                    collectionLayout: 'fixed three-column',
                    text: '<i class="fa fa-eye"></i>',
                    titleAttr: ETO.lang.user.button.column_visibility,
                    postfixButtons: ['colvisRestore'],
                    className: 'btn-datatable btn-sm',
                }, {
                    text: '<div class="eto-refresh-localStorage-'+ nameBox +'"><i class="fa fa-undo"></i></div>',
                    titleAttr: ETO.lang.user.button.reset,
                    className: 'btn-datatable btn-sm'
                }, {
                    extend: 'reload',
                    text: '<i class="fa fa-refresh"></i>',
                    titleAttr: ETO.lang.user.button.reload,
                    className: 'btn-datatable btn-sm'
                }],
                scrollX: true,
                searching: true,
                ordering: true,
                lengthChange: true,
                info: true,
                autoWidth: false,
                stateSave: true,
                stateSaveCallback: function(settings, data) {
                    dtStateSave(data);
                },
                stateLoadCallback: function(settings, callback) {
                    if (typeof callback == 'undefined') { return null; }
                    dtStateLoad(settings, callback);
                },
                stateDuration: 0,
                order: [defaultOrder],
                pageLength: 10,
                lengthMenu: ETO.datatable.lengthMenu,
                language: ETO.datatable.language(ETO.lang.user),
                drawCallback: function(settings) {
                    if ($(this).find('tr').length > 0) {
                        var pagination = $(this).closest('.dataTables_wrapper').find('.dataTables_paginate');
                        if (typeof this.api().page.info() != 'undefined') {
                            pagination.toggle(this.api().page.info().pages > 1);
                        }
                    }
                },
                infoCallback: function( settings, start, end, max, total, pre ) {
                    return '<i class="ion-ios-information-outline" data-toggle="popover" data-title="" data-content="'+ pre +'"></i>';
                },
            };
        var uriParams = ETO.getUrlParams(window.location.search);

        if ((uriParams.page == 'trash' & !ETO.hasPermission('admin.bookings.destroy') && !ETO.hasPermission('admin.bookings.restore'))
            || (
                !ETO.hasPermission('admin.bookings.edit')
                && !ETO.hasPermission('admin.bookings.show')
                && !ETO.hasPermission('admin.bookings.tracking')
                && !ETO.hasPermission('admin.bookings.transactions')
                && !ETO.hasPermission('admin.bookings.invoice')
                && !ETO.hasPermission('admin.bookings.create')
                && !ETO.hasPermission('admin.bookings.sms')
                && !ETO.hasPermission('admin.feedback.show')
                && !ETO.hasPermission('admin.bookings.meeting_board')
                && !ETO.hasPermission('admin.bookings.mark_as_read')
                && !ETO.hasPermission('admin.bookings.notifications')
                && !ETO.hasPermission('admin.activity.index')
                && !ETO.hasPermission('admin.bookings.trash')
            )
        ) {
            delete datatableOptions.columnDefs;
        }

        var datatable = table.DataTable(datatableOptions)
        .on('preDraw', function() {
            ETO.updateTooltip();
        })
        .on('draw.dt', function() {
            $('.buttons-reload i').removeClass('fa-spin');

            // Tooltip
            ETO.updateTooltip(table.find('[data-toggle="tooltip"]'));

            // Popover
            ETO.updatePopover(table.find('[data-toggle="popover"]'));

            // Address
            table.find('.eto-address-more').readmore('destroy').readmore({
                collapsedHeight: 40,
                moreLink: '<a href="#" class="eto-address-more-link">'+ ETO.trans('booking.buttons.more') +'</a>',
                lessLink: '<a href="#" class="eto-address-more-link">'+ ETO.trans('booking.buttons.less') +'</a>'
            });

            // TO REMOVE ON UPGRADE ALL BOOKING FILES
            $('.payment-status').attr('onclick', false);

            ETO.updateTooltip($('.dataTable .label-info'));
        })
        .on('stateSaveParams.dt', function(e, settings, data) {
            data.search.search = '';
        });

        $('body').on('click', '.dataTable .inline-editing', function(e) {
            e.preventDefault();

            $(this).editable({
                // mode: 'inline',
                placement: 'bottom',
                ajaxOptions: {
                    headers: {
                        'X-CSRF-TOKEN': ETO.config.csrfToken
                    },
                    type: 'GET',
                    dataType: 'json',
                    async: false,
                },
                sourceOptions: {
                    cache: true,
                },
                source: function() {
                    if (typeof etoBookingRequestData !== 'undefined') {
                        if ($(this).hasClass('inline-editing-status')) {
                            return etoBookingRequestData.statusList;
                        }
                        else if ($(this).hasClass('inline-editing-fleet')) {
                            return etoBookingRequestData.fleetList;
                        }
                        else if ($(this).hasClass('inline-editing-driver')) {
                            return etoBookingRequestData.driverList;
                        }
                        else if ($(this).hasClass('inline-editing-vehicle')) {
                            var driverId = parseInt($(this).attr('data-driver_id'));
                            var vehicles = [];
                            $.each(etoBookingRequestData.vehicleList, function(k, v) {
                                if (v.driver_id == driverId) {
                                    vehicles.push(v);
                                }
                            });
                            return vehicles;
                        }
                    }
                    return null;
                },
                // sourceCache: true,
                display: function(value, sourceData, response) {
                    var new_value = '';

                    if ( response && response.new_value ) {
                        new_value = response.new_value;
                    }
                    else if ( sourceData && sourceData.new_value ) {
                        new_value = sourceData.new_value;
                    }

                    if ( new_value && new_value !== '<i class="fa fa-info-circle"></i>' ) {
                        $(this).html(new_value +' <i class="fa fa-edit"></i>');
                    }
                },
                success: function(response, newValue) {
                    datatable.draw();
                }
            })
            .editable('show');
        });

        if (typeof parameters !== 'undefined' && typeof parameters.filters !== 'undefined') {
            var values = etoFn.filterTable(parameters.filters);
            datatable.search(values).draw();
            console.log('this is testing',values);
        }
        else {
            datatable.draw();
        }
        etoFn.config.tableList.push(nameBox);
        etoFn.refreshTable(datatable);
        etoFn.updateTableHeight();

        ETO.updatePopover();
    };

    etoFn.getCustomerList = function() {
        ETO.ajax('get-config/customer-list', {
            data: {},
            success: function(data) { ETO.config.customerList = data;}
        });

        return ETO.config.customerList;
    };

    etoFn.getCustomer = function(id) {
        ETO.ajax('get-config/customer/' + id, {
            data: {},
            success: function(data) { ETO.config.customer = data[id]; }
        });

        return ETO.config.customer;
    };

    etoFn.getDriver = function(id) {
        ETO.ajax('get-config/driver/' + id, {
            data: {},
            success: function(data) { ETO.config.driver = data; }
        });

        return ETO.config.driver;
    };

    etoFn.getUser = function(id, type) {
        if (typeof type != 'undefined') {
            ETO.ajax('get-config/' + type + "/" + id, {
                data: {},
                success: function(data) {
                    if (type == 'customer') {
                        data = data[id];
                    }
                    ETO.config[type] = data;
                }
            });

            return ETO.config[type];
        }
    };

    etoFn.getVehicle = function(id) {
        ETO.ajax('get-config/driver-vehicle/' + id, {
            data: {},
            success: function(data) { ETO.config.vehicle = data; }
        });

        return ETO.config.vehicle;
    };

    etoFn.driverVehicles = function(id) {
        if(typeof ETO.config['driver-vehicles'] == 'undefined') {
            ETO.config['driver-vehicles'] = [];
        }
        var vehicles = false;
        ETO.ajax('get-config/driver-vehicles', {
            data: {driver_id: id},
            success: function(data) {
                vehicles = data;
                if(typeof ETO.config.driver != 'undefined' && ETO.config.driver.id === parseInt(id)) {
                    ETO.config['driver-vehicles'][id] = data;
                }
            }
        });
        return vehicles;
    };

    return etoFn;
}();
