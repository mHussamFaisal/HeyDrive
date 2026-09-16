

ETO.Booking.Form = function() {
    var etoFn = {};
    etoFn.config = {
        init: ['page', 'icons', 'config_site', 'vehicle', 'service', 'source', 'booking_status', 'payment_type'],
        lang: ['user', 'booking'],
        bookingId: {},
        requestParams: {},
        minimumResultsForSearch: 5,
        isReturn: false,
        multiVehicleForce: false,
        refNumber: {},
        driverId: {},
        bookingDetails: {},
        bookingIncomeCharges: {},
        values: {},
        oldData: {},
        limits: {
            minQuote: {
                location: 2,
                when: 1,
                vehicle: 1,
            },
            minSave: {
                location: 2,
                when: 1,
                passenger: 1,
            },
            min: {
                location: 2,
                customer: 1,
                passenger: 1,
                driver: 1,
                item: 0,
                vehicle: 1,
            },
            max: {
                passenger: 2,
                customer: 1,
                driver: 1,
            },
            init: {
                service: 1,
                serviceDuration: 1,
                location: 2,
                when: 1,
                vehicle: 1,
                flightDetails: 1,
                baseStart: 1,
                startEnd: 1,
                endBase: 1,
                item: 0,
                customer: 1,
                passenger: 1,
                driver: 1,
                requirement: 1,
                pricing: 1,
                discountCode: 1,
                paymentType: 1,
                bookingStatus: 1,
                source: 1,
                notes: 1,
                discount: 1,
                journeyPrice: 1,
                deposit: 1,
                notifications: 1,
                passengerAmount: 1,
                luggageAmount: 1,
                handLuggageAmount: 1,
                customField: 1,
            }
        },
        defaults: {
            section: {
                // tu mozna podac podstawowe ustawienia zalezne tylko od tego modelu
            },
        },
        containers: {},
    };

    etoFn.form = {};

    etoFn.formButtonsConfig = function() {
        return {
            section: {
                swap: {
                    icon: 'ion-arrow-swap',
                    class: 'eto-section-btn-swap',
                    tooltip: ETO.trans('booking.bookingSwapButtonTooltip'),
                },
                override: {
                    icon: 'ion-arrow-return-left',
                    class: 'eto-section-btn-override eto-return-copy eto-return-hide',
                    tooltip: ETO.trans('booking.bookingField_Return'),
                },
                add: function(classes, value, icon) {
                    value = typeof value == 'undefined' || value == '' ? false : value;
                    icon = typeof icon == 'undefined' ? 'ion-plus' : icon;
                    return {
                        icon: icon,
                        class: 'eto-section-btn-add ' + classes,
                        tooltip: ETO.trans('booking.bookingAddButtonTooltip'),
                        text: value,
                    };
                },
                addLocation: function(classes, value, icon) {
                    value = typeof value == 'undefined' || value == '' ? false : value;
                    icon = typeof icon == 'undefined' ? 'ion-plus' : icon;
                    return {
                        icon: icon,
                        class: 'eto-section-btn-add ' + classes,
                        // tooltip: ETO.trans('booking.bookingAddButtonTooltip'),
                        text: value,
                    };
                },
            },
            summary: {
                delete: function(value) {
                    return {
                        icon: 'fa fa-trash-o',
                        class: 'eto-summary-btn-delete',
                        tooltip: value,
                    };
                },
            },
            fieldset: {
                close: {
                    icon: 'ion-ios-close-empty',
                    class: 'eto-fieldset-btn-close',
                    tooltip: ETO.trans('booking.bookingCloseFieldsButtonTooltip'),
                },
            },
            field: {
                geolocation: {
                    icon: 'ion-android-locate',
                    class: 'eto-field-btn-geolocation',
                    tooltip: ETO.trans('booking.bookingGeolocationButtonTooltip'),
                },
                clear: {
                    icon: 'ion-ios-close-empty',
                    class: 'eto-field-btn-clear',
                    tooltip: ETO.trans('booking.bookingField_ClearBtn'),
                },
                handler: {
                    icon: 'fa fa-arrows-v',
                    class: 'eto-fieldset-btn-handler',
                },
                createPassenger: {
                    icon: 'fa ion-man',
                    class: 'eto-fieldset-btn-passenger hidden',
                    tooltip: ETO.trans('booking.bookingCustommerPassengerPlaceholder'),
                },
                help: function(value) {
                    return {
                        icon: 'fa fa-info-circle',
                        class: 'eto-field-btn-help',
                        popover: {
                            html: value,
                        },
                    };
                },
                advance: function(value, classes) {
                    classes = typeof classes == 'undefined' ? '' : classes;
                    value = typeof value == 'undefined' || value == '' ? ETO.trans('booking.bookingAdvanceButtonTooltip') : value;
                    return {
                        icon: 'ion-ios-compose-outline',
                        class: 'eto-field-btn-advance ' + classes,
                        tooltip: value,
                    };
                },
            },
        };
    };

    etoFn.formContainersConfig = function() {
        return {
            services: {
                classParent: '.eto-position-1',
                class: '',
                title: '',
                toogle: false,
            },
            locations: {
                classParent: '.eto-position-2',
                class: '',
                title: ETO.trans('booking.bookingLocationSectionLabel'),
                toogle: false,
            },
            details: {
                classParent: '.eto-position-3',
                class: '',
                title: '',
                toogle: false,
            },
            passengers: {
                classParent: '.eto-position-4-1',
                class: '',
                title: ETO.trans('booking.bookingPassengerSection'),
                toogle: false,
            },
            passengerDetails: {
                classParent: '.eto-position-4-2',
                class: '',
                title: '',
                toogle: false,
            },
            paymentAndDriver: {
                classParent: '.eto-position-5-1',
                class: '',
                title: ETO.trans('booking.paymentAdnDriver'),
                toogle: false,
            },
            paymentAndDriverNotes: {
                classParent: '.eto-position-5-2',
                class: '',
                title: '',
                toogle: false,
            },
            advance: {
                classParent: '.eto-position-6',
                class: 'eto-btn-advance',
                title: ETO.trans('booking.advance'),
                toogle: true,
            },
            pickupDropoff: {
                classParent: '.eto-position-7',
                class: '',
                title: '',
                toogle: false,
            },
            transactions: {
                classParent: '.eto-position-7',
                class: '',
                title: '',
                toogle: false,
            },
        };
    };

    etoFn.formConfigGenerate = function() {
        var settings = {eto_booking: ETO.settings('eto_booking', {})},
            buttons = etoFn.config.formButtons;

        return {
            settings: {
                sectionOrder: false,
                resizeAction: true,
                view: {
                    advance_open: parseBoolean(ETO.settings('eto_booking.form.view.advance_open', false)),
                    amounts_view_passenger: parseBoolean(ETO.settings('eto_booking.form.view.amounts_view_passenger', false)),
                    amounts_view_suitcase: parseBoolean(ETO.settings('eto_booking.form.view.amounts_view_suitcase', false)),
                    amounts_view_carry_on: parseBoolean(ETO.settings('eto_booking.form.view.amounts_view_carry_on', false)),
                    instant_dispatch_color_system: parseBoolean(ETO.settings('eto_booking.form.view.instant_dispatch_color_system', false)),
                    waiting_time: parseBoolean(ETO.settings('eto_booking.form.view.waiting_time', false)),
                    show_inactive_drivers_form:  parseBoolean(ETO.settings('eto_booking.form.view.show_inactive_drivers_form', false)),
                },
                checked: {
                    send_notification: parseBoolean(ETO.settings('eto_booking.form.checked.send_notification', false)),
                },
                booking: {
                    custom_field_name: ETO.settings('eto_booking.custom_field.name',''),
                    custom_field_display:  parseBoolean(ETO.settings('eto_booking.custom_field.display', false)),
                    add_vehicle_button:  parseBoolean(ETO.settings('eto_booking.form.view.add_vehicle_button', false)),
                },
                user: settings,
                subscription: settings,
             },
            sections: {
                service: {
                    sectionParent: etoFn.config.containers.services,
                    sectionClass: 'eto-style-tabs eto-style-radio-horizontal',
                    groupAutoOpen: true,
                    fields: {
                        service: {
                            valueConverterType: 'id',
                            type: 'radio',
                            callback: {
                                before: {
                                    radioSwitchGenerator: {
                                        source: ETO.settings('service',{}),
                                    }
                                },
                            },
                            validate: {
                                isRequired: true,
                                errorMessage: ETO.trans('booking.ERROR_SERVICES_EMPTY'),
                            }
                        }
                    },
                },
                location: {
                    sectionParent: etoFn.config.containers.locations,
                    customHtml: {
                        before: '<div class="eto-route-switch hidden clearfix">\
                          <button class="btn btn-default btn-sm eto-btn-set-outbound eto-route-buttons hidden" type="button">\
                             <span>Outbound</span>\
                             <i class="ion-alert eto-error-route eto-error-route-1 hidden" title="Please fill in all required fields for Outbound"></i>\
                          </button>\
                          <button class="btn btn-success btn-sm eto-btn-set-return eto-route-buttons hidden" type="button">\
                            <span>Return</span>\
                            <i class="ion-alert eto-error-route eto-error-route-2 hidden" title="Please fill in all required fields for Return"></i>\
                          </button>\
                          <button class="btn btn-default btn-sm eto-btn-return eto-return-on" type="button">\
                              <span class="eto-return-on">Return Journey?</span>\
                              <span class="eto-return-off hidden" data-toggle="popover" data-title="" data-content="Remove Return Journey"><i class="fa fa-times"></i></span>\
                          </button>\
                        </div>',
                    },
                    // sectionClass: 'eto-style-route-horizontal eto-style-route-size-half eto-style-group-size-md eto-style-summary eto-style-fieldset eto-style-fieldset-popup',
                    sectionClass: 'eto-style-route-horizontal eto-style-route-size-half eto-style-summary eto-style-summary-vertical eto-style-fieldset eto-style-fieldset-size-sm',
                    groupAutoOpen: true,
                    groupOrder: true,
                    // groupAddedOpen: true,
                    // summaryPlaceholder: ETO.trans('booking.bookingLocationTitle'),
                    buttons: {
                        bottomRight: {
                            add: buttons.section.addLocation(),
                            swap: buttons.section.swap,
                            override: buttons.section.override,
                        },
                        summary: {
                            delete: buttons.summary.delete(ETO.trans('booking.TITLE_REMOVE_WAYPOINTS')),
                        },
                        // fields : {
                        //     close: buttons.fieldset.close,
                        // }
                    },
                    fields: {
                        address: {
                            placeholder: ETO.trans('booking.bookingAddressPlaceholder'),
                            isFocus: false,
                            validate: {
                                isRequired: true,
                                errorMessage: ETO.trans('booking.errorBookingAddress'),
                            },
                            buttons: {
                                delete: buttons.summary.delete(ETO.trans('booking.TITLE_REMOVE_WAYPOINTS')),
                                handler: buttons.field.handler,
                                advance: buttons.field.advance(),
                                geolocation: buttons.field.geolocation,
                                clear: buttons.field.clear,
                            },
                            callback: {
                                after: {
                                    setTypeahead: {},
                                }
                            },
                        },
                        complete: {
                            placeholder: ETO.trans('booking.full_address'),
                            isAdvance: true,
                            buttons: {
                                clear: buttons.field.clear,
                            }
                        },
                        lat: { type: 'hidden', },
                        lng: { type: 'hidden', },
                        place_id: { type: 'hidden', },
                        // type: { type: 'hidden', },
                    },
                },
                when: {
                    sectionParent: etoFn.config.containers.details,
                    sectionClass: 'eto-style-route-horizontal eto-style-route-size-half eto-style-summary eto-style-summary-vertical eto-style-fieldset eto-style-fieldset-size-sm',
                    groupAutoOpen: true,
                    // groupClass: 'eto-group-icon-revert',
                    buttons: {
                        bottomRight: {
                            override: buttons.section.override,
                        },
                    },
                    fields: {
                        formatted_date: {
                            icon: 'ion-ios-clock-outline',
                            // fieldIconRevert: true,
                            placeholder: ETO.trans('booking.date'),
                            placeholderAlt: ETO.trans('booking.bookingWhenPlaceholder'),
                            isFocus: false,
                            validate: {
                                // isRequired: true,
                                isDate: true,
                                errorMessage: ETO.trans('booking.ERROR_ROUTE_DATE_EMPTY'),
                            },
                            callback: {
                                after: {
                                    setDaterangepicker: {},
                                }
                            },
                        },
                        date: {
                            type: 'hidden'
                        }
                    },
                    summary: {
                        formatted_date: {},
                    },
                },
                serviceDuration: {
                    sectionParent: etoFn.config.containers.details,
                    sectionClass: 'eto-style-summary eto-style-summary-vertical1 eto-style-fieldset eto-style-fieldset-popup1 eto-style-fieldset-size-xs',
                    sectionHide: true,
                    groupAutoOpen: true,
                    summaryLabel: ETO.trans('booking.service_duration'),
                    fields: {
                        service_duration: {
                            placeholder: ETO.trans('booking.service_duration'),
                            placeholderAlt: '0',
                            callback: {
                                after: {
                                    durationSetTouchSpin: {},
                                }
                            },
                        }
                    },
                    summary: {
                        duration: {},
                    },
                },
                vehicle: {
                    sectionParent: etoFn.config.containers.details,
                    // sectionClass: 'eto-style-route-horizontal eto-style-group-horizontal eto-style-summary eto-style-fieldset eto-style-fieldset-popup',
                    sectionClass: 'eto-style-route-horizontal eto-style-group-horizontal eto-style-route-size-half eto-style-summary  eto-style-summary-vertical eto-style-fieldset eto-style-fieldset-size-sm',

                    groupAutoOpen: true,
                    summaryIcon: 'ion-android-car',
                    summaryTooltip: ETO.trans('booking.bookingVehiclePlaceholderOption'),
                    summaryIconRevert: true,
                    // groupClass: 'eto-group-icon-revert',
                    // summaryClass: 'eto-summary-icon-revert',
                    buttons: {
                        bottomRight: {
                            add: buttons.section.add('eto-add-item'),
                            override: buttons.section.override,
                        },
                        summary: {
                            delete: buttons.summary.delete(ETO.trans('booking.TITLE_REMOVE_WAYPOINTS')),
                        },
                    },
                    fields: {
                        vehicle_type: {
                            icon: 'ion-android-car',
                            // fieldIconRevert: true,
                            isFocus: false,
                            valueConverterType: 'id',
                            placeholder: ETO.trans('booking.vehicle'),
                            type: 'select',
                            isRequired: true,
                            selectAutoOpen: false,
                            callback: {
                                after: {
                                    setSelect2FromConfig: {
                                        source: ETO.settings('vehicle',{}),
                                        config: {
                                            minimumResultsForSearch: -1,
                                        },
                                    },
                                }
                            },
                        },
                        vehicle_type_text_selected: {
                            type: 'hidden'
                        },
                        vehicle_amount: {
                            visible: false,
                            buttons: {
                                delete: buttons.summary.delete(ETO.trans('booking.TITLE_REMOVE_WAYPOINTS')),
                            },
                            // placeholder: ETO.trans('booking.bookingVehicleAmount'),
                            placeholderAlt: '1',
                            callback: {
                                after: {
                                    durationSetTouchSpin: {value: 1, min: 1, max:99, vertical: false},
                                }
                            },
                        },
                    },
                    summary: {
                        vehicle_type: {
                            valueConverterType: 'id',
                            sourceTo: 'config',
                            visible: false,
                        },
                        vehicle_type_text_selected: {maxLength: 20,},
                        vehicle_amount: {displayFormat: 'x __vehicle_amount__', minValue: 2},
                    },
                },
                flightDetails: {
                    sectionParent: etoFn.config.containers.details,
                    sectionClass: 'eto-style-summary eto-style-fieldset eto-style-fieldset-popup',
                    summaryIcon: 'ion-plane',
                    summaryTooltip: ETO.trans('booking.bookingFlightDetailsSectionLabel'),
                    summarySeparator: ',',
                    buttons: {
                        bottomRight: {
                            override: buttons.section.override,
                        },
                        fields : {
                            close: buttons.fieldset.close,
                        }
                    },
                    fields: {
                        flight_number: {
                            placeholder: ETO.trans('booking.flight_number'),
                            buttons: {
                                clear: buttons.field.clear,
                            },
                        },
                        flight_landing_time: {
                            placeholder: ETO.trans('booking.flight_landing_time'),
                            buttons: {
                                clear: buttons.field.clear,
                            }
                        },
                        arriving_from: {
                            placeholder: ETO.trans('booking.departure_city'),
                            buttons: {
                                clear: buttons.field.clear,
                            }
                        },
                        departure_flight_number: {
                            placeholder: ETO.trans('booking.departure_flight_number'),
                            buttons: {
                                clear: buttons.field.clear,
                            },
                        },
                        departure_flight_time: {
                            placeholder: ETO.trans('booking.departure_flight_time'),
                            buttons: {
                                clear: buttons.field.clear,
                            }
                        },
                        departure_flight_city: {
                            placeholder: ETO.trans('booking.departure_flight_city'),
                            buttons: {
                                clear: buttons.field.clear,
                            }
                        },
                        waiting_time: {
                            placeholder: ETO.trans('booking.bookingWaitingTimeAfterLandingPlaceholder'),
                            callback: {
                                after: {
                                    durationSetTouchSpin: {min: 0, value: 0},
                                }
                            },
                        },
                        meet_and_greet: {
                            type: 'checkbox',
                            callback: {
                                before: {
                                    checkboxSwitchGenerator: {
                                        source: {
                                            data: {
                                                0: {id: 1, name: ETO.trans('booking.meet_and_greet'), value: 1},
                                            },
                                            selected: 0,
                                        }
                                    }
                                },
                            },
                        },
                        meeting_point: {
                            class: 'eto-field-buttons-over',
                            placeholder: ETO.trans('booking.meeting_point'),
                            type: 'textarea',
                            buttons: {
                                clear: buttons.field.clear,
                            }
                        },
                    },
                    summary: {
                        flight_number: {maxLength: 10},
                        arriving_from: {maxLength: 15},
                        departure_flight_number: {maxLength: 10},
                        departure_flight_city: {maxLength: 15},
                    },
                    tooltip: {
                        flight_number: {
                            label: 'placeholder',
                        },
                        flight_landing_time: {
                            label: 'placeholder',
                        },
                        arriving_from: {
                            label: 'placeholder',
                        },
                        departure_flight_number: {
                            label: 'placeholder',
                        },
                        departure_flight_time: {
                            label: 'placeholder',
                        },
                        departure_flight_city: {
                            label: 'placeholder',
                        },
                        waiting_time: {
                            label: 'placeholder',
                        },
                        meet_and_greet: {
                            label: ETO.trans('booking.meet_and_greet'),
                            valueConverterType: 'isChecked',
                        },
                        meeting_point: {
                            label: 'placeholder',
                        },
                    },
                },
                customer: {
                    sectionParent: etoFn.config.containers.passengers,
                    sectionClass: 'eto-style-route-horizontal eto-style-route-size-half eto-style-summary eto-style-summary-vertical eto-style-fieldset eto-style-fieldset-size-sm',
                    groupAutoOpen: true,
                    summaryIcon: 'fa fa-search',
                    fields: {
                        customer: {
                            selectAutoOpen: false,
                            valueConverterType: 'id',
                            placeholder: ETO.trans('booking.bookingCustommerSummaryLabel'),
                            placeholderOption: ETO.trans('booking.bookingCustommerSectionLabel'),
                            type: 'select',
                            callback: {
                                after: {
                                    setSelect2FromSearchUser: {},
                                }
                            },
                            buttons: {
                                createPassenger: buttons.field.createPassenger,
                                clear: buttons.field.clear,
                                advance: buttons.field.advance(),
                            }
                        },
                        customer_text_selected: {
                            type: 'hidden'
                        },
                        department: {
                            isAdvance: true,
                            valueConverterType: 'id',
                            placeholder: ETO.trans('booking.assign_department'),
                            type: 'select',
                            callback: {
                                after: {
                                    setSelect2FromConfig: {
                                        config: {
                                            minimumResultsForSearch: -1,
                                        },
                                        sourceFrom: 'department',
                                    },
                                }
                            },
                            visible: false,
                            selectAutoOpen: false,
                            buttons: {
                                clear: buttons.field.clear,
                            }
                        },
                    },
                    summary: {
                        customer: {
                            valueConverterType: 'id',
                            sourceTo: 'getCustomerData',
                            sourceToIsMethod: true,
                            visible: false,
                        },
                        userName: {},
                    },
                    tooltip: {
                        customer: {
                            valueConverterType: 'id',
                            sourceTo: 'getCustomerData',
                            sourceToIsMethod: true,
                            visible: false,
                        },
                        userName: {label: ETO.trans('booking.user_name')},
                        userEmail: {label: ETO.trans('booking.contact_email')},
                        userPhone: {label: ETO.trans('booking.contact_mobile')},
                    },
                },
                passenger: {
                    sectionParent: etoFn.config.containers.passengers,
                    sectionClass: 'eto-style-section-btn eto-style-group-horizontal eto-style-summary eto-style-fieldset eto-style-fieldset-popup eto-small-icon',
                    groupOrder: true,
                    groupAddedOpen: true,
                    sectionAutoRemove: true,
                    summaryIcon: 'fa fa-search',
                    summaryTooltip: ETO.trans('booking.bookingPassengerSectionLabel'),
                    summaryPlaceholder: ETO.trans('booking.bookingPassengerSectionLabel'),
                    summaryIconRevert: true,
                    groupClass: 'eto-group-icon-revert',
                    summaryClass: 'eto-summary-icon-revert',
                    buttons: {
                        bottomRight: {
                            add: buttons.section.add('','','ion-man'),
                        },
                        summary: {
                            delete: buttons.summary.delete(ETO.trans('booking.TITLE_REMOVE_WAYPOINTS')),
                        },
                        fields : {
                            close: buttons.fieldset.close,
                        }
                    },
                    fields: {
                        search_field: {
                            placeholder: ETO.trans('booking.passenger_finder'),
                            placeholderOption: ETO.trans('booking.passenger'),
                            type: 'select',
                            selectAutoOpen: false,
                            callback: {
                                after: {
                                    setSelect2FromSearchUser: {
                                        config: {
                                            minimumResultsForSearch: 3,
                                        },
                                    },
                                }
                            },
                            buttons: {
                                clear: buttons.field.clear,
                            },
                        },
                        name: {
                            placeholder: ETO.trans('booking.bookingField_Name'),
                            validate: {
                                isRequired: true,
                                errorMessage: ETO.trans('booking.ERROR_LEAD_PASSENGER_NAME_EMPTY'),
                            },
                            buttons: {
                                clear: buttons.field.clear,
                            }
                        },
                        email: {
                            type: 'email',
                            placeholder: ETO.trans('booking.CONTACT_EMAIL'),
                            validate: {
                                isEmail: true,
                                errorMessage: ETO.trans('booking.ERROR_CONTACT_EMAIL_INCORRECT'),
                            },
                            buttons: {
                                clear: buttons.field.clear,
                            }
                        },
                        phone: {
                            placeholder: ETO.trans('booking.CONTACT_MOBILE'),
                            placeholderAlt: '',
                            validate: {
                                isPhone: true,
                                errorMessage: ETO.trans('booking.ERROR_CONTACT_MOBILE_EMPTY'),
                            },
                            callback: {
                                after: {
                                    setIntlTelInput: {}
                                },
                            },
                            buttons: {
                                clear: buttons.field.clear,
                            }
                        },
                        comments: {
                            class: 'eto-field-buttons-over hidden',
                            placeholder: ETO.trans('booking.ROUTE1_REQUIREMENTS'),
                            type: 'textarea',
                            buttons: {
                                clear: buttons.field.clear,
                            }
                        },
                    },
                    summary: {
                        name: {},
                    },
                    tooltip: {
                        name: {
                            label: 'placeholder',
                        },
                        email: {
                            label: 'placeholder',
                        },
                        phone: {
                            label: 'placeholder',
                        },
                        comments: {
                            label: 'placeholder',
                            maxLength: 200,
                        },
                    },
                },
                paymentType: {
                    sectionParent: etoFn.config.containers.paymentAndDriver,
                    sectionClass: 'eto-style-route-horizontal eto-style-route-size-half eto-style-summary eto-style-summary-vertical eto-style-fieldset eto-style-fieldset-size-sm',
                    groupAutoOpen: true,
                    fields: {
                        payment_method: {
                            type: 'select',
                            placeholder: ETO.trans('booking.PAYMENT_TYPE'),
                            selectAutoOpen: false,
                            callback: {
                                after: {
                                    setSelect2FromConfig: {
                                        source: ETO.settings('paymentType',{}),
                                        config: {
                                            // minimumResultsForSearch: etoFn.config.minimumResultsForSearch,
                                            minimumResultsForSearch: -1,
                                        }
                                    },
                                }
                            },
                            validate: {
                                isRequired: true,
                                errorMessage: ETO.trans('booking.ERROR_SERVICES_EMPTY'),
                            },
                        },
                    },
                },
                journeyPrice: {
                    sectionParent: etoFn.config.containers.paymentAndDriver,
                    customHtml: {
                        after: '<div class="eto-quote">\
                          <button class="btn btn-default btn-sm eto-btn-get-quote" type="button">Get Quote</button>\
                        </div>',
                    },
                    sectionClass: 'eto-style-route-horizontal eto-style-route-size-half eto-style-summary eto-style-summary-vertical eto-style-fieldset eto-style-fieldset-size-xs',
                    groupAutoOpen: true,
                    buttons: {
                        bottomRight: {
                            override: buttons.section.override,
                        },
                    },
                    fields: {
                        price: {
                            class: 'eto-field-touchspin-no-buttons eto-field-placeholder-always',
                            placeholder: ETO.trans('booking.bookingItemsPricePlaceholder'),
                            placeholderAlt: '0.00',
                            callback: {
                                after: {
                                    durationSetTouchSpin: {min: 0,  step: 0.01},
                                }
                            },
                            buttons: {
                                advance: buttons.field.advance('', 'hidden'),
                            },
                        },
                        override_name: {
                            isAdvance: true,
                            class: 'eto-js-type-override_name',
                            placeholder: ETO.trans('booking.bookingItemsNamePlaceholder'),
                            buttons: {
                                clear: buttons.field.clear,
                            }
                        },
                    },
                },
                fleet: {
                    sectionParent: etoFn.config.containers.paymentAndDriverNotes,
                    sectionClass: 'eto-style-group-horizontal eto-style-summary eto-style-fieldset eto-style-fieldset-popup eto-small-icon',
                    summaryIcon: 'fa fa-search',
                    summaryTooltip: ETO.trans('booking.assign_fleet'),
                    summaryPlaceholder: ETO.trans('booking.assign_fleet'),
                    summaryIconRevert: true,
                    groupClass: 'eto-group-icon-revert',
                    summaryClass: 'eto-summary-icon-revert',
                    buttons: {
                        bottomRight: {
                            override: buttons.section.override,
                        },
                        fields : {
                            close: buttons.fieldset.close,
                        }
                    },
                    fields: {
                        fleet: {
                            valueConverterType: 'id',
                            placeholder: ETO.trans('booking.assign_fleet'),
                            type: 'select',
                            callback: {
                                after: {
                                    setSelect2FromSearchUser: {},
                                }
                            },
                            buttons: {
                                clear: buttons.field.clear,
                            }
                        },
                        commission: {
                            class: 'eto-field-touchspin-no-buttons eto-field-placeholder-always',
                            placeholder: ETO.trans('booking.fleet_commission'),
                            placeholderAlt: '0.00',
                            visible: false,
                            callback: {
                                after: {
                                    durationSetTouchSpin: {min: 0, step: 0.01},
                                }
                            },
                            buttons: {
                                help: buttons.field.help(ETO.trans('booking.fleet_commission_info') + ETO.User.Driver.getIncomeTooltip()),
                            }
                        },
                        driver_text_selected: {
                            type: 'hidden'
                        },
                    },
                    summary: {
                        fleet: {
                            valueConverterType: 'id',
                            sourceTo: 'getFleetData',
                            sourceToIsMethod: true,
                            visible: false,
                        },
                        userName: {},
                    },
                    tooltip: {
                        fleet: {
                            valueConverterType: 'id',
                            sourceTo: 'getFleetData',
                            sourceToIsMethod: true,
                            visible: false,
                        },
                        userAvatar: {},
                        userName: {label: ETO.trans('booking.fleet_name')},
                        userEmail: {label: ETO.trans('booking.contact_email')},
                        userPhone: {label: ETO.trans('booking.contact_mobile')},
                        commission: {
                            valueConverterType: 'price',
                            sourceTo: 'getItemPrice',
                            sourceToIsMethod: true,
                            visible: false,
                        },
                        formated_commission: {
                            label: ETO.trans('booking.fleet_commission'),
                            displayFormat: '__formated_price__'
                        },
                    },
                },
                driver: {
                    sectionParent: etoFn.config.containers.paymentAndDriverNotes,
                    sectionClass: 'eto-style-group-horizontal eto-style-summary eto-style-fieldset eto-style-fieldset-popup eto-small-icon',
                    summaryIcon: 'fa fa-search',
                    summaryTooltip: ETO.trans('booking.assign_driver'),
                    summaryPlaceholder: ETO.trans('booking.assign_driver'),
                    summaryIconRevert: true,
                    groupClass: 'eto-group-icon-revert',
                    summaryClass: 'eto-summary-icon-revert',
                    buttons: {
                        bottomRight: {
                            override: buttons.section.override,
                        },
                        fields : {
                            close: buttons.fieldset.close,
                        }
                    },
                    fields: {
                        driver: {
                            valueConverterType: 'id',
                            placeholder: ETO.trans('booking.assign_driver'),
                            type: 'select',
                            selectAutoOpen: false,
                            callback: {
                                after: {
                                    setSelect2FromSearchUser: {urlParam: 'unavailable'},
                                }
                            },
                            buttons: {
                                clear: buttons.field.clear,
                            }
                        },
                        vehicle: {
                            valueConverterType: 'id',
                            placeholder: ETO.trans('booking.assign_vehicle'),
                            validate: {
                                errorMessage: ETO.trans('booking.errorBookingDriver'),
                            },
                            type: 'select',
                            callback: {
                                after: {
                                    setSelect2FromConfig: {
                                        source: {
                                            data: {
                                                0: {id:0, text:ETO.trans('booking.booking_unassigned')}
                                            },
                                        },
                                        config: {
                                            minimumResultsForSearch: -1,
                                        },
                                        sourceFrom: 'vehicle',
                                    },
                                }
                            },
                            visible: false,
                            selectAutoOpen: false,
                            // buttons: {
                            //     clear: buttons.field.clear,
                            // }
                        },
                        commission: {
                            class: 'eto-field-touchspin-no-buttons eto-field-placeholder-always',
                            placeholder: ETO.trans('booking.commission'),
                            placeholderAlt: '0.00',
                            // visible: false,
                            callback: {
                                after: {
                                    durationSetTouchSpin: {min: 0, step: 0.01},
                                }
                            },
                            buttons: {
                                help: buttons.field.help(ETO.trans('booking.commission_info') + ETO.User.Driver.getIncomeTooltip()),
                            }
                        },
                        cash: {
                            class: 'eto-field-touchspin-no-buttons eto-field-placeholder-always',
                            placeholder: ETO.trans('booking.passengerCharge'),
                            placeholderAlt: '0.00',
                            // visible: false,
                            callback: {
                                after: {
                                    durationSetTouchSpin: {min: 0, step: 0.01},
                                }
                            },
                            buttons: {
                                help: buttons.field.help(ETO.trans('booking.cash_info')),
                            }
                        },
                        comments: {
                            class: 'eto-field-buttons-over',
                            placeholder: ETO.trans('booking.requirements'),
                            type: 'textarea',
                            visible: false,
                            buttons: {
                                clear: buttons.field.clear,
                            }
                        },
                        driver_text_selected: {
                            type: 'hidden'
                        },
                        vehicle_text_selected: {
                            type: 'hidden'
                        },
                    },
                    summary: {
                        driver: {
                            valueConverterType: 'id',
                            sourceTo: 'getDriverData',
                            sourceToIsMethod: true,
                            visible: false,
                        },
                        userName: {},
                    },
                    tooltip: {
                        driver: {
                            valueConverterType: 'id',
                            sourceTo: 'getDriverData',
                            sourceToIsMethod: true,
                            visible: false,
                        },
                        userAvatar: {},
                        userName: {label: ETO.trans('booking.driver_name')},
                        uniqueId: {label: ETO.trans('booking.uniqueId')},
                        vehicle_text_selected: {label: ETO.trans('booking.vehicle')},
                        userEmail: {label: ETO.trans('booking.contact_email')},
                        userPhone: {label: ETO.trans('booking.contact_mobile')},
                        commission: {
                            valueConverterType: 'price',
                            sourceTo: 'getItemPrice',
                            // priceField: 'commission',
                            sourceToIsMethod: true,
                            visible: false,
                        },
                        formated_commission: {
                            label: ETO.trans('booking.commission'),
                            displayFormat: '__formated_price__'
                        },
                        cash: {
                            valueConverterType: 'price',
                            sourceTo: 'getItemPrice',
                            sourceToIsMethod: true,
                            visible: false,
                        },
                        formated_cash: {
                            label: ETO.trans('booking.cash'),
                            displayFormat: '__formated_price__'
                        },
                        comments: {label: 'Comments', maxLength: 100},
                    },
                },
                notes: {
                    sectionParent: etoFn.config.containers.paymentAndDriverNotes,
                    sectionClass: 'eto-style-route-horizontal eto-style-summary eto-style-summary-vertical eto-style-fieldset eto-style-fieldset-size-md eto-style-fieldset-popup',
                    summaryLabel: ETO.trans('booking.bookingOperatorNotesPlaceholder'),
                    buttons: {
                        fields : {
                            close: buttons.fieldset.close,
                        }
                    },
                    fields: {
                        notes: {
                            class: 'eto-field-buttons-over',
                            placeholder: ETO.trans('booking.bookingOperatorNotesPlaceholder'),
                            type: 'textarea',
                            buttons: {
                                clear: buttons.field.clear,
                            }
                        }
                    },
                    summary: {
                        notes: {maxLength: 25},
                    },
                    tooltip: {
                        notes: {label: ETO.trans('booking.bookingOperatorNotesPlaceholder'), maxLength: 500},
                    },
                },
                requirement: {
                    sectionParent: etoFn.config.containers.paymentAndDriverNotes,
                    sectionClass: 'eto-style-summary eto-style-summary-vertical eto-style-fieldset eto-style-fieldset-size-md eto-style-fieldset-popup',
                    summaryLabel: ETO.trans('booking.bookingRequirementSectionLabel'),
                    buttons: {
                        fields : {
                            close: buttons.fieldset.close,
                        }
                    },
                    fields: {
                        value: {
                            class: 'eto-field-buttons-over',
                            placeholder: ETO.trans('booking.bookingRequirementSectionLabel'),
                            type: 'textarea',
                            buttons: {
                                help: buttons.field.help(ETO.trans('booking.bookingRequirementHelp')),
                                clear: buttons.field.clear,
                            }
                        },
                    },
                    summary: {
                        value: {
                            maxLength: 25,
                        },
                    },
                    tooltip: {
                        value: {
                            label: 'placeholder',
                            maxLength: 500,
                        },
                    },
                },
                passengerAmount: {
                    sectionParent: etoFn.config.containers.passengerDetails,
                    sectionClass: 'eto-style-route-horizontal eto-style-route-size-half eto-style-summary eto-style-summary-vertical eto-style-fieldset eto-style-fieldset-size-xs1',
                    groupAutoOpen: true,
                    buttons: {
                        bottomRight: {
                            override: buttons.section.override,
                        },
                    },
                    fields: {
                        amount: {
                            classAdd: 'eto-is-integer',
                            valueConverterType: 'id',
                            type: 'select',
                            selectAutoOpen: false,
                            icon: 'fa fa-user',
                            callback: {
                                after: {
                                    setSelect2FromConfig: {
                                        sourceAddValue: true,
                                        source: etoFn.numbers1To30(),
                                        config: {
                                            minimumResultsForSearch: etoFn.config.minimumResultsForSearch,
                                            dropdownCssClass: 'eto-select2-dropdown-xs',
                                        },
                                        createSource: true,
                                    },
                                }
                            },
                        },
                    },
                },
                luggageAmount: {
                    sectionParent: etoFn.config.containers.passengerDetails,
                    sectionClass: 'eto-style-route-horizontal eto-style-route-size-half eto-style-summary eto-style-summary-vertical eto-style-fieldset eto-style-fieldset-size-xs1',
                    groupAutoOpen: true,
                    buttons: {
                        bottomRight: {
                            override: buttons.section.override,
                        },
                    },
                    fields: {
                        amount: {
                            classAdd: 'eto-is-integer',
                            valueConverterType: 'id',
                            type: 'select',
                            icon: 'ion-ios-briefcase',
                            selectAutoOpen: false,
                            callback: {
                                after: {
                                    setSelect2FromConfig: {
                                        sourceAddValue: true,
                                        source: etoFn.numbers1To30(),
                                        config: {
                                            minimumResultsForSearch: etoFn.config.minimumResultsForSearch,
                                            dropdownCssClass: 'eto-select2-dropdown-xs',
                                        },
                                        createSource: true,
                                    },
                                }
                            },
                        },
                    },
                },
                handLuggageAmount: {
                    sectionParent: etoFn.config.containers.passengerDetails,
                    sectionClass: 'eto-style-route-horizontal eto-style-route-size-half eto-style-summary eto-style-summary-vertical eto-style-fieldset eto-style-fieldset-size-xs1',
                    groupAutoOpen: true,
                    buttons: {
                        bottomRight: {
                            override: buttons.section.override,
                        },
                    },
                    fields: {
                        amount: {
                            classAdd: 'eto-is-integer',
                            valueConverterType: 'id',
                            type: 'select',
                            icon: 'ion-ios-briefcase-outline',
                            selectAutoOpen: false,
                            callback: {
                                after: {
                                    setSelect2FromConfig: {
                                        sourceAddValue: true,
                                        source: etoFn.numbers1To30(),
                                        config: {
                                            minimumResultsForSearch: etoFn.config.minimumResultsForSearch,
                                            dropdownCssClass: 'eto-select2-dropdown-xs',
                                        },
                                        createSource: true,
                                    },
                                }
                            },
                        },
                    },
                },
                item: {
                    sectionParent: etoFn.config.containers.passengerDetails,
                    sectionClass: 'eto-style-route-horizontal eto-style-group-horizontal eto-style-summary eto-style-fieldset eto-style-fieldset-popup',
                    summaryPlaceholderAlt: 'Select',
                    groupOrder: true,
                    groupAddedOpen: true,
                    sectionAutoRemove: true,
                    buttons: {
                        bottomRight: {
                            add: buttons.section.add('eto-add-item',ETO.trans('booking.bookingAddItemButton')),
                            override: buttons.section.override,
                        },
                        summary: {
                            delete: buttons.summary.delete(ETO.trans('booking.TITLE_REMOVE_WAYPOINTS')),
                        },
                        fields : {
                            close: buttons.fieldset.close,
                        }
                    },
                    fields: {
                        item: {
                            type: 'select',
                            validate: {
                                isRequired: true,
                                errorMessage: ETO.trans('booking.errorBookingItem'),
                            },
                            callback: {
                                after: {
                                    setSelect2FromConfig: {
                                        source: ETO.fields.booking,
                                        sourceFrom: 'fields',
                                    },
                                }
                            },
                            buttons: {
                                advance: buttons.field.advance(),
                            }
                        },
                        override_name: {
                            isAdvance: true,
                            class: 'eto-js-type-override_name',
                            placeholder: ETO.trans('booking.bookingItemsNamePlaceholder'),
                            buttons: {
                                clear: buttons.field.clear,
                            }
                        },
                        price: {
                            visible: false,
                            class: 'eto-field-touchspin-no-buttons eto-field-placeholder-always',
                            placeholder: ETO.trans('booking.bookingItemsPricePlaceholder'),
                            placeholderAlt: '0.00',
                            callback: {
                                after: {
                                    durationSetTouchSpin: {min: 0, step: 0.01},
                                }
                            },
                        },
                        amount: {
                            visible: false,
                            class: 'eto-js-type-amount eto-field-placeholder-always',
                            placeholder: ETO.trans('booking.bookingVehicleAmount'),
                            placeholderAlt: '1',
                            callback: {
                                after: {
                                    durationSetTouchSpin: {min: 1, value: 1},
                                }
                            },
                        },
                        item_type: {
                            type: 'hidden'
                        },
                        item_key: {
                            type: 'hidden'
                        },
                        item_trans_key: {
                            type: 'hidden'
                        },
                    },
                    summary: {
                        item: {
                            valueConverterType: 'type',
                            sourceTo: 'customFields',
                            visible: false,
                        },
                        price: {
                            valueConverterType: 'price',
                            sourceTo: 'getItemPrice',
                            sourceToIsMethod: true,
                            visible: false,
                        },
                        type_dispaly_name: {},
                        formated_price: {displayFormat: '__formated_price__'}
                    },
                    tooltip: {
                        item: {
                            valueConverterType: 'type',
                            sourceTo: 'customFields',
                            visible: false,
                        },
                        price: {
                            valueConverterType: 'price',
                            sourceTo: 'getItemPrice',
                            sourceToIsMethod: true,
                            visible: false,
                        },
                        type_dispaly_name_tooltip: {label: ETO.trans('booking.bookingItemsToooltip')},
                        override_name: {label: 'placeholder'},
                        formated_price: {label: ETO.trans('booking.bookingItemsPricePlaceholder')}
                    },
                },
                discount : {
                    sectionParent: etoFn.config.containers.advance,
                    sectionClass: 'eto-section-advance eto-style-route-horizontal eto-style-route-size-half eto-style-summary eto-style-summary-vertical eto-style-fieldset eto-style-fieldset-size-xs',
                    groupAutoOpen: true,
                    buttons: {
                        bottomRight: {
                            override: buttons.section.override,
                        },
                    },
                    fields: {
                        price: {
                            class: 'eto-field-touchspin-no-buttons eto-field-placeholder-always',
                            placeholder: ETO.trans('booking.discount_price'),
                            placeholderAlt: '0.00',
                            callback: {
                                after: {
                                    durationSetTouchSpin: {min: 0,  step: 0.01},
                                }
                            },
                        }
                    },
                },
                deposit: {
                    sectionParent: etoFn.config.containers.advance,
                    sectionClass: 'eto-section-advance eto-style-route-horizontal eto-style-route-size-half eto-style-summary eto-style-summary-vertical eto-style-fieldset eto-style-fieldset-size-xs',
                    groupAutoOpen: true,
                    fields: {
                        price: {
                            class: 'eto-field-touchspin-no-buttons eto-field-placeholder-always',
                            placeholder: ETO.trans('booking.deposit'),
                            placeholderAlt: '0.00',
                            callback: {
                                after: {
                                    durationSetTouchSpin: {min: 0,  step: 0.01},
                                }
                            },
                        }
                    },
                },
                discountCode: {
                    sectionParent: etoFn.config.containers.advance,
                    sectionClass: 'eto-section-advance eto-style-route-horizontal eto-style-route-size-half eto-style-summary eto-style-summary-vertical eto-style-fieldset eto-style-fieldset-size-xs',
                    groupAutoOpen: true,
                    fields: {
                        code: {
                            placeholder: ETO.trans('booking.discount_code'),
                            buttons: {
                                clear: buttons.field.clear,
                            }
                        }
                    },
                },
                bookingStatus: {
                    sectionParent: etoFn.config.containers.advance,
                    sectionClass: 'eto-section-advance eto-style-route-horizontal eto-style-route-size-half eto-style-summary eto-style-summary-vertical eto-style-fieldset eto-style-fieldset-size-sm',
                    groupAutoOpen: true,
                    summaryLabel: ETO.trans('booking.status'),
                    summarySeparator: ' ',
                    fields: {
                        status: {
                            placeholder: ETO.trans('booking.status'),
                            type: 'select',
                            buttons: {
                                advance: buttons.field.advance('', 'hidden'),
                            },
                            validate: {
                                isRequired: true,
                                errorMessage: ETO.trans('booking..errorBookingStatus'),
                            },
                            callback: {
                                after: {
                                    setSelect2FromConfig: {
                                        source: ETO.settings('bookingStatus', {}),
                                        config: {
                                            minimumResultsForSearch: -1,
                                        }
                                    },
                                }
                            }
                        },
                        comments: {
                            isAdvance: true,
                            visible: false,
                            class: 'eto-field-buttons-over',
                            placeholder: ETO.trans('booking.bookingStatusNotes'),
                            type: 'textarea',
                            buttons: {
                                clear: buttons.field.clear,
                            }
                        },
                        status_text_selected: {
                            type: 'hidden'
                        },
                    },
                },
                source: {
                    sectionParent: etoFn.config.containers.advance,
                    sectionClass: 'eto-section-advance eto-style-route-horizontal eto-style-summary eto-style-summary-vertical eto-style-fieldset eto-style-fieldset-size-sm',
                    groupAutoOpen: true,
                    summaryLabel: ETO.trans('booking.source'),
                    fields: {
                        source: {
                            selectAutoOpen: false,
                            placeholder: ETO.trans('booking.source'),
                            type: 'select',
                            callback: {
                                after: {
                                    setSelect2FromConfig: {
                                        source: ETO.settings('source',{}),
                                        config: {
                                            minimumResultsForSearch: etoFn.config.minimumResultsForSearch,
                                        },
                                        createSource: true,
                                    },
                                }
                            },
                            buttons: {
                                advance: buttons.field.advance(),
                            }
                        },
                        comments: {
                            isAdvance: true,
                            visible: false,
                            class: 'eto-field-buttons-over',
                            placeholder: ETO.trans('booking.bookingSourceDetailsPlaceholder'),
                            type: 'textarea',
                            // buttons: {
                            //     clear: buttons.field.clear,
                            // }
                        }
                    },
                    summary: {
                        source: {},
                    },
                    tooltip: {
                        comments: {label: ETO.trans('booking.bookingSourceDetailsPlaceholder'), maxLength: 500},
                    },
                },
                notifications: {
                    sectionParent: etoFn.config.containers.advance,
                    sectionClass: 'eto-section-advance eto-style-route-horizontal eto-style-route-size-half eto-style-summary eto-style-summary-vertical eto-style-fieldset eto-style-fieldset-size-md eto-style-fieldset-popup',
                    summaryLabel: ETO.trans('booking.bookingNotificationsSummary'),
                    buttons: {
                        fields : {
                            close: buttons.fieldset.close,
                        }
                    },
                    fields: {
                        send_notification: {
                            type: 'checkbox',
                            callback: {
                                before: {
                                    checkboxSwitchGenerator: {
                                        source: {
                                            data: {
                                                0: {id: 1, name: ETO.trans('booking.bookingNotificationsPlaceholder'), value: 1},
                                            },
                                        }
                                    }
                                },
                            },
                        },
                        send_invoice: {
                            type: 'checkbox',
                            callback: {
                                before: {
                                    checkboxSwitchGenerator: {
                                        source: {
                                            data: {
                                                0: {id: 1, name: ETO.trans('booking.bookingNotificationsInvoicePlaceholder'), value: 1},
                                            },
                                        }
                                    }
                                },
                            },
                        },
                        locale: {
                            placeholder: ETO.trans('booking.bookingNotificationsPreferLanguagePlaceholder'),
                            class: 'eto-field-placeholder-always',
                            type: 'select',
                            selectAutoOpen: false,
                            callback: {
                                after: {
                                    setSelect2FromConfig: {
                                        source: ETO.settings('locale_list', {}),
                                        config: {
                                            minimumResultsForSearch: etoFn.config.minimumResultsForSearch,
                                        },
                                        sourceFrom: 'locale',
                                    },
                                }
                            },
                            buttons: {
                                advance: buttons.field.advance(ETO.trans('booking.bookingNotificationsButtonSetCommentPlaceholder')),
                            }
                        },
                        locale_text_selected: {
                            type: 'hidden'
                        },
                        comments: {
                            class: 'eto-field-buttons-over',
                            placeholder: 'Message',
                            placeholderAlt: ETO.trans('booking.bookingNotificationsCommentPlaceholder'),
                            type: 'textarea',
                            isAdvance: true,
                        },
                        email: {
                            placeholder: ETO.trans('booking.bookingNotificationsEmailPlaceholder'),
                            type: 'email',
                            attr: {
                                multiple: true
                            },
                            buttons: {
                                clear: buttons.field.clear,
                            },
                            validate: {
                                isEmail: true,
                                errorMessage: ETO.trans('booking.ERROR_CONTACT_EMAIL_INCORRECT'),
                            }
                        },
                    },
                    summary: {
                        email: {},
                        locale_text_selected: {},
                    },
                    tooltip: {
                        send_notification: {
                            label: ETO.trans('booking.bookingNotificationsPlaceholder'),
                            valueConverterType: 'isChecked',
                        },
                        send_invoice: {
                            label: ETO.trans('booking.bookingNotificationsInvoicePlaceholder'),
                            valueConverterType: 'isChecked',
                        },
                        locale_text_selected: {
                            label: ETO.trans('booking.bookingNotificationsPreferLanguagePlaceholder'),
                        },
                        comments: {
                            label: 'placeholder',
                            maxLength: 200,
                        },
                        email: {
                            label: 'placeholder',
                        },
                    },
                },
                baseStart: {
                    sectionParent: etoFn.config.containers.pickupDropoff,
                    sectionClass: 'eto-section-advance eto-style-summary eto-style-summary-vertical eto-style-fieldset eto-style-fieldset-size-sm eto-style-fieldset-popup',
                    summaryLabel: ETO.trans('booking.DISTANCE_BASE_START'),
                    summarySeparator: ',',
                    buttons: {
                        bottomRight: {
                            override: buttons.section.override,
                        },
                        fields : {
                            close: {
                                icon: 'ion-ios-close-empty',
                                class: 'eto-fieldset-btn-close',
                                tooltip: ETO.trans('booking.bookingCloseFieldsButtonTooltip'),
                            },
                        }
                    },
                    fields: {
                        distance: {
                            class: 'eto-field-touchspin-no-buttons',
                            placeholder: ETO.trans('booking.DISTANCE'),
                            placeholderAlt: '0.00',
                            callback: {
                                after: {
                                    durationSetTouchSpin: {min: 0, value: 0, step: 0.01},
                                }
                            },
                        },
                        duration: {
                            class: 'eto-field-touchspin-no-buttons',
                            placeholder: ETO.trans('booking.DURATION'),
                            placeholderAlt: '0',
                            callback: {
                                after: {
                                    durationSetTouchSpin: {min: 0, value: 0},
                                }
                            },
                        },
                    },
                    summary: {
                        distance: {containerOrder: true, displayFormat: '__distance__ '+ETO.trans('booking.quote_Kilometers'), minValue: 0},
                        duration: {containerOrder: true, displayFormat: '__duration__ '+ETO.trans('booking.bookingField_Minutes'), minValue: 0},
                    },
                },
                customField: {
                    sectionParent: etoFn.config.containers.paymentAndDriverNotes,
                    sectionClass: 'eto-style-route-horizontal eto-style-summary eto-style-summary-vertical eto-style-fieldset eto-style-fieldset-size-md eto-style-fieldset-popup',
                    summaryLabel: ETO.settings('eto_booking.custom_field.name', ETO.trans('booking.customPlaceholder')).toString(),
                    fields: {
                        custom: {
                            type: 'textarea',
                            placeholder: ETO.settings('eto_booking.custom_field.name', ETO.trans('booking.customPlaceholder')).toString(),
                        }
                    },
                    summary: {
                        custom: {maxLength: 25},
                    },
                    tooltip: {
                        custom: {label: ETO.settings('eto_booking.custom_field.name', ETO.trans('booking.customPlaceholder')).toString(), maxLength: 500},
                    },
                },
                startEnd: {
                    sectionParent: etoFn.config.containers.pickupDropoff,
                    sectionClass: 'eto-section-advance eto-style-summary eto-style-summary-vertical eto-style-fieldset eto-style-fieldset-size-sm eto-style-fieldset-popup',
                    summaryLabel: ETO.trans('booking.DURATION_START_END'),
                    summarySeparator: ',',
                    buttons: {
                        bottomRight: {
                            override: buttons.section.override,
                        },
                        fields : {
                            close: {
                                icon: 'ion-ios-close-empty',
                                class: 'eto-fieldset-btn-close',
                                tooltip: ETO.trans('booking.bookingCloseFieldsButtonTooltip'),
                            },
                        }
                    },
                    fields: {
                        distance: {
                            class: 'eto-field-touchspin-no-buttons',
                            placeholder: ETO.trans('booking.DISTANCE'),
                            placeholderAlt: '0',
                            callback: {
                                after: {
                                    durationSetTouchSpin: {min: 0, value: 0, step: 0.01},
                                }
                            },
                        },
                        duration: {
                            class: 'eto-field-touchspin-no-buttons',
                            placeholder: ETO.trans('booking.DURATION'),
                            placeholderAlt: '0',
                            callback: {
                                after: {
                                    durationSetTouchSpin: {min: 0, value: 0},
                                }
                            },
                        },
                    },
                    summary: {
                        distance: {containerOrder: true, displayFormat: '__distance__ '+ETO.trans('booking.quote_Kilometers'), minValue: 0, value: 0},
                        duration: {containerOrder: true, displayFormat: '__duration__ '+ETO.trans('booking.bookingField_Minutes'), minValue: 0, value: 0},
                    },
                },
                endBase: {
                    sectionParent: etoFn.config.containers.pickupDropoff,
                    sectionClass: 'eto-section-advance eto-style-summary eto-style-summary-vertical eto-style-fieldset eto-style-fieldset-size-sm eto-style-fieldset-popup',
                    summaryLabel: ETO.trans('booking.DURATION_BASE_END'),
                    summarySeparator: ',',
                    buttons: {
                        bottomRight: {
                            override: buttons.section.override,
                        },
                        fields : {
                            close: {
                                icon: 'ion-ios-close-empty',
                                class: 'eto-fieldset-btn-close',
                                tooltip: ETO.trans('booking.bookingCloseFieldsButtonTooltip'),
                            },
                        }
                    },
                    fields: {
                        distance: {
                            class: 'eto-field-touchspin-no-buttons',
                            placeholder: ETO.trans('booking.DISTANCE'),
                            placeholderAlt: '0',
                            callback: {
                                after: {
                                    durationSetTouchSpin: {min: 0, value: 0, step: 0.01},
                                }
                            },
                        },
                        duration: {
                            class: 'eto-field-touchspin-no-buttons',
                            placeholder: ETO.trans('booking.DURATION'),
                            placeholderAlt: '0',
                            callback: {
                                after: {
                                    durationSetTouchSpin: {min: 0, value: 0},
                                }
                            },
                        },
                    },
                    summary: {
                        distance: {containerOrder: true, displayFormat: '__distance__ '+ETO.trans('booking.quote_Kilometers'), minValue: 0, value: 0},
                        duration: {containerOrder: true, displayFormat: '__duration__ '+ETO.trans('booking.bookingField_Minutes'), minValue: 0, value: 0},
                    },
                },
            },
        };
    };

    etoFn.init = function(config) {
        ETO.extendConfig(this, config, 'bookingForm');

        // var eventClick = ETO.isMobile ? 'doubleTap' : 'click';

        ETO.Form.config.objectForm = 'Booking';
        etoFn.config.containers = etoFn.formContainersConfig();
        etoFn.config.formButtons = etoFn.formButtonsConfig();

        if (ETO.hasPermission(['admin.bookings.create','admin.bookings.edit'])) {
            etoFn.initializeForm();
        } else {
            if(typeof etoFn.form.settings == 'undefined') {
                etoFn.form = etoFn.formConfigGenerate(); // etoInfo - update form object

                $.each(ETO.settings('bookingStatusColor'), function (key, data) {
                    etoFn.form.settings.booking['status_color_' + key] = data.color;
                });
            }
        }

        $('body').on('change', 'input.eto-js-payment_method', function() {
            etoFn.viewDeposit($(this).closest('.eto-fields').find('input.eto-js-payment_method:checked'));
        })
        .on('click', '.eto-btn-get-transactions', function(e) {
            var bookingContainer = $(this).closest('.eto-form'),
                formId = bookingContainer.attr('id'),
                iframe = '<script data-cfasync="false" src="'+ ETO.config.appPath +'/assets/plugins/iframe-resizer/iframeResizer.min.js"></script>';

            if ($(this).hasClass('eto-viev-transactions')) {
                bookingContainer.find('iframe').remove();
                bookingContainer.find('script').remove();
                $(this).removeClass('eto-viev-transactions').html(ETO.trans('booking.showTransaction'));
            }
            else {
                iframe += '<script>$(\'iframe\').iFrameResize({heightCalculationMethod: \'lowestElement\', log: false, targetOrigin: \'*\', checkOrigin: false});</script>' +
                  '<iframe src="'+ ETO.config.appPath +'/admin/bookings/'+ etoFn.config.bookingId[formId] +'/transactions?tmpl=body" id="transactions-iframe" width="100%" height="250" scrolling="no" style="width:1px; min-width:100%; border:0;"></iframe>';

                $(this).after(iframe);
                $(this).addClass('eto-viev-transactions').html(ETO.trans('booking.hideTransaction'));
            }
        })
        .on('click', '.eto-section-btn-override', function(e) {
            var overideBtn = $(this),
                container = overideBtn.closest('.eto-section'),
                section = container.attr('data-eto-section'),
                formContainer = container.closest('.eto-form'),
                formId = formContainer.attr('id');

            if ($(this).closest('.eto-section').find('.eto-section-header').hasClass('hidden')) {
                container.find('.eto-section-header').removeClass('hidden');
                container.addClass('eto-route-override');
                overideBtn.addClass('eto-return-added');

                if (section.localeCompare('location') === 0) {
                    etoFn.serviceDuration(formContainer.find('.eto-section-service').find('input:checked'), true);
                }
                formContainer.find('.eto-fieldset.hidden').find('.eto-field').remove();

                var to = container.find('.eto-route-2').find('.eto-groups');

                if (section.localeCompare('location') === 0) {
                    to.children().each(function (i, group) {
                        to.prepend(group)
                    });

                    ETO.Form.reorderObjectValues(to);
                }

                if (section.localeCompare('location') === 0 && to.find('.eto-group:not(.hidden)').length > 1) {
                    ETO.Form.setSortable(section, 'group', to);
                }

                ETO.Form.hideFields(container.closest('.eto-form'), to.find('.eto-js-inputs').closest('.eto-group'));

                if (to.find('.eto-group.hidden').length > 0) {
                    to.find('.eto-group.hidden').removeClass('hidden');
                    etoFn.serviceDuration(formContainer.find('.eto-section-service').find('input:checked'), true);
                }

                container.find('.eto-route-1').addClass('hidden');
                container.find('.eto-route-2').removeClass('hidden');
            }
            else {
                container.find('.eto-section-header, .eto-route-2').addClass('hidden');
                container.find('.eto-route-1').removeClass('hidden');
                container.find('.eto-route-2').find('.eto-group').remove();
                container.removeClass('eto-route-override');
                overideBtn.removeClass('eto-return-added');
                if (typeof ETO.Form.config.form.values[formId].booking != 'undefined' &&
                   typeof ETO.Form.config.form.values[formId].booking[2] != 'undefined' &&
                   typeof ETO.Form.config.form.values[formId].booking[2][section] != 'undefined') {
                    delete ETO.Form.config.form.values[formId].booking[2][section];
                }
            }
        })
        .on('click', '.eto-btn-return', function(e) {
            var btnReturn = $(this),
                formContainer = btnReturn.closest('.eto-form'),
                formId = formContainer.attr('id');

            formContainer.LoadingOverlay('show');

            setTimeout(function() {
                if (formContainer.hasClass('eto-form-return-active')) {
                    etoFn.config.isReturn = false;
                    formContainer.removeClass('eto-form-return-active');
                    formContainer.find('.eto-section-headers').addClass('hidden');
                    formContainer.find('.eto-section-has-return, .eto-route-override').removeClass('eto-section-has-return').removeClass('eto-route-override');
                    formContainer.find('.eto-route-2').remove();
                    // btnReturn.addClass('btn-default').removeClass('btn-success');

                    btnReturn.find('.eto-return-off').addClass('hidden');
                    btnReturn.find('.eto-return-on').removeClass('hidden');

                    formContainer.find('.eto-route-buttons').addClass('hidden');
                    formContainer.find('.eto-route-1').removeClass('hidden');
                    formContainer.find('.eto-section .eto-route-1').each(function () {
                        ETO.Form.enableDisableButton($(this));
                    });

                    delete ETO.Form.config.form.values[formId].booking[2];
                }
                else {
                    ETO.Form.config.form.values[formId].booking[2] = $.extend(true, {}, ETO.Form.config.form.values[formId].booking[1]);
                    etoFn.config.isReturn = true;
                    formContainer.addClass('eto-form-return-active');
                    formContainer.find('.eto-section-btn-override').closest('.eto-section').each(function(key, sectionContainer) {
                        var section = $(sectionContainer).attr('data-eto-section'),
                            data = $.extend(true, {}, etoFn.form.sections[section]),
                            bookingSectionConfig = $.extend(true, {}, etoFn.config.defaults.section),
                            sectionconfig = $.extend(true, {}, ETO.Form.config.defaults.section),
                            template = '<div class="eto-route eto-route-2 clearfix" data-eto-route-id="2"><div class="eto-groups clearfix"></div></div>';

                        formContainer.find('.eto-section-'+section).append(template);
                        bookingSectionConfig = $.extend(true, sectionconfig, bookingSectionConfig);
                        data = $.extend(true, bookingSectionConfig, data);
                        etoFn.createRoute(section, data, 2, formId);
                    });

                    formContainer.addClass('eto-form-return-active');
                    formContainer.find('.eto-route-buttons').removeClass('hidden');
                    // btnReturn.replaceClass('btn-default', 'btn-success');

                    btnReturn.find('.eto-return-on').addClass('hidden');
                    btnReturn.find('.eto-return-off').removeClass('hidden');

                    formContainer.find('.eto-btn-set-return').replaceClass('btn-default', 'btn-success');
                    formContainer.find('.eto-btn-set-outbound').replaceClass('btn-success','btn-default');
                    formContainer.find('.eto-route-1 .eto-section-btn-override').each(function(key, button) {
                        $(button).closest('.eto-section').addClass('eto-section-has-return');
                        $(button).trigger('click');

                        if ($(button).hasClass('eto-return-hide')) { $(button).addClass('hidden'); }
                        else { $(button).removeClass('hidden'); }
                    });
                }

                formContainer.find('.eto-section-btn-override:not(.eto-return-hide)').toogleClass('hidden');
                formContainer.find('.eto-return-hide').addClass('hidden');
                etoFn.setPriceSummary(formId);
            }, 100);

            formContainer.LoadingOverlay('hide');
        })
        .on('click', '.eto-btn-set-outbound', function() {
            var formContainer = $(this).closest('.eto-form');
            formContainer.find('.eto-route-1').find('.eto-section-btn-override:not(.eto-return-hide)').addClass('hidden'); // :not(.eto-return-hide)
            formContainer.find('.eto-route-2').addClass('hidden');
            formContainer.find('.eto-route-1').removeClass('hidden');
            $(this).removeClass('btn-default').addClass('btn-success');
            formContainer.find('.eto-btn-set-return').addClass('btn-default').removeClass('btn-success');
            etoFn.serviceDuration(formContainer.find('.eto-section-service').find('input:checked'), true);
        })
        .on('click', '.eto-btn-set-return', function() {
            var formContainer = $(this).closest('.eto-form'),
                route2 = $(this).closest('.eto-form').find('.eto-route-2');

            formContainer.find('.eto-route-1').find('.eto-section-btn-override:not(.eto-return-hide)').removeClass('hidden'); // :not(.eto-return-hide)
            route2.removeClass('hidden');
            route2.each(function() {
                if ($(this).find('.eto-group').length > 0 || ($(this).closest('.eto-section').attr('data-eto-section') == 'item' || $(this).closest('.eto-section').attr('data-eto-section') == 'passenger')) {
                    $(this).closest('.eto-section').find('.eto-route-1').addClass('hidden');
                }
                else { $(this).addClass('hidden'); }
            });

            $(this).removeClass('btn-default').addClass('btn-success');
            formContainer.find('.eto-btn-set-outbound').addClass('btn-default').removeClass('btn-success');
            etoFn.serviceDuration(formContainer.find('.eto-section-service').find('input:checked'), true);
        })
        .on('click', '.eto-btn-get-quote', function(e) {
            var formContainer = $(this).closest('.eto-form'),
                formId = formContainer.attr('id'),
                data =  typeof ETO.Form.config.form.values[formId] != 'undefined' ? ETO.Form.config.form.values[formId] : false;

            formContainer.LoadingOverlay('show');
            formContainer.trigger('click');


            if (etoFn.config.bookingId[formId] !== false){
                data.bookingId = etoFn.config.bookingId[formId];
            }

            if (ETO.Form.setAllValuesToObject(formContainer, 'quote') === true) {
                data.action = 'quote';

                if (etoFn.config.bookingId[formId] !== false){
                    data.bookingId = etoFn.config.bookingId[formId];
                }

                // Clear HTML from variable - ModSecurity returns 403 if HTML is send to server.
                if (data.booking && data.booking[1].bookingStatus && data.booking[1].bookingStatus[0].status_text_selected) {
                    data.booking[1].bookingStatus[0].status_text_selected = $(data.booking[1].bookingStatus[0].status_text_selected).text();
                }

                ETO.ajax('set-new/booking', {
                    data: data,
                    async: true,
                    success: function(data) {
                        var html = '';
                        if (data.success === true && data.message.length === 0) {
                            var booking = $.extend(true, {}, data);

                            html += etoFn.createPriceSummaryHtml(booking.booking) + '<div class="eto-price-summary-popup-total">Total ' + ETO.formatPrice(etoFn.getTotalPriceFromObject(booking.booking, true)) + '</div>';

                            if (data.discountMessage.length > 0) {
                                html += '<div class="eto-price-summary-popup-discount-message">' + data.discountMessage;
                                if (data.discountExcludedInfo !== false) {
                                    html += '<span class="eto-field-btn eto-field-btn-help" \
                                        title="'+data.discountExcludedInfo+'">\
                                    <i class="fa fa-info-circle"></i></span>';
                                }
                                html += '</div>';
                            }

                            ETO.swalWithBootstrapButtons({
                                title: 'Do you want to use the calculated price?',
                                html: '<div class="eto-price-summary-popup">' + html + '</div>',
                                showCancelButton: true,
                                confirmButtonText: 'Yes, I do!',
                            })
                            .then(function(result) {
                                if (result.value) {
                                    // Delete existing items from new object
                                    if (data.booking) {
                                        $.each(data.booking, function(kB, vB) {
                                            if (vB.item) {
                                                var items = [];
                                                $.each(vB.item, function(kI, vI) {
                                                    if (typeof vI.exist == 'undefined' || (typeof vI.exist != 'undefined' && parseBoolean(vI.exist) !== true)) { items.push(vI); }
                                                });
                                                delete data.booking[kB].item;
                                                data.booking[kB].item = items;
                                            }
                                        });
                                    }

                                    etoFn.updateObjectAndFormSummaries(data, formContainer);
                                    ETO.toast({
                                        type: 'success',
                                        title: 'Calculated price has been applied'
                                    });
                                }
                            });
                        }
                        else {
                            $.each(data.message, function(i, v) {
                                html += v + '<br>';
                            });

                            ETO.swalWithBootstrapButtons({
                                type: 'warning',
                                title: html,
                            });
                        }
                    },
                    complete: function() {
                        formContainer.LoadingOverlay('hide');
                    }
                });
            }
            else {
                ETO.swalWithBootstrapButtons({
                    type: 'warning',
                    title: 'Please fill in all required fields',
                });
                formContainer.LoadingOverlay('hide');
            }
        })
        .on('click', '.eto-section-btn-swap', function(e) {
            var el = $(this).closest('.eto-route').find('.eto-groups');
            el.children().each(function(i,group){ el.prepend(group) });
            ETO.Form.reorderObjectValues(el);
            e.preventDefault();
            e.stopPropagation();
        })
        .on('click', '.eto-field-btn-geolocation', function(e) {
            e.preventDefault();
            var el = $(this);
            etoFn.setGeolocation(el);
            e.stopPropagation();
        })
        .on('click', '.eto-commission-auto-calculate', function(e) {
            var commission = $(this).attr('data-eto-commission');
            $(this).closest('.eto-fieldset').find('.eto-js-commission').val(commission).change().focusTextToEnd();
            e.stopPropagation();
        })
        .on('change', 'input.eto-js-service', function() {
            etoFn.serviceDuration($(this).closest('.eto-fields').find('input.eto-js-service:checked'), false);
        })
        .on('change', 'input.eto-js-formatted_date', function() {
            if ($(this).val().length === 0) {
                $(this).closest('.eto-group').find('.eto-js-date').val('').change();
            }
        })
        .on('change', 'select.eto-js-locale', function(e) {
            var value = typeof $(this).val() != 'undefined' && null !== $(this).val()
                ? parseInt($(this).val())
                : 0,
                text = $(this).find('option:selected').text();

            $(this).closest('.eto-group').find('.eto-js-locale_text_selected').val(text).change();
        })
        .on('change', 'select.eto-js-vehicle_type', function(e) {
            var value = typeof $(this).val() != 'undefined' && null !== $(this).val()
                ? parseInt($(this).val())
                : 0,
                text = $(this).find('option:selected').text();

            $(this).closest('.eto-group').find('.eto-js-vehicle_type_text_selected').val(text).change();

            if (value > 0 && etoFn.form.settings.booking.add_vehicle_button === true) {
                $(this).closest('.eto-group').find('.eto-js-vehicle_amount').closest('.eto-field').removeClass('hidden');
            }
            else {
                $(this).closest('.eto-group').find('.eto-js-vehicle_amount').val(1).closest('.eto-field').addClass('hidden');
                $(this).closest('.eto-group').find('.eto-js-vehicle_amount').change();
            }
        })
        .on('click', '.eto-section-vehicle .eto-field-buttons .eto-field-btn-clear', function(e) {
            var input = $(this).closest('.eto-group').find('.eto-js-vehicle_type'),
                value = typeof input.val() != 'undefined' && null !== input.val()
                    ? parseInt($(this).val())
                    : 0;

            if (value === 0) {
                input.find('option[value="0"]').attr('selected', true);
                input.val('0').change();

                $(this).closest('.eto-group').find('.eto-js-vehicle_type_text_selected').val(input.find('option:selected').text()).change();
            }
        })
        .on('click', '.eto-section-customer .eto-field-btn-clear', function(e) {
            var routeId = $(this).closest('.eto-route').attr('data-eto-route-id'),
                formId = $(this).closest('.eto-form').attr('id');

            delete ETO.Form.config.form.values[formId].booking[routeId].customer;
            $(this).closest('.eto-group').find('.eto-js-customer_text_selected').val('');
        })
        .on('click', '.eto-section-bookingStatus .eto-field-btn-clear', function(e) {
            var fieldset = $(this).closest('.eto-field'),
                routeId = $(this).closest('.eto-route').attr('data-eto-route-id'),
                formId = $(this).closest('.eto-form').attr('id');

            if (fieldset.find('select.eto-js-status').length > 0) {
                $(this).closest('.eto-group').find('.eto-js-status').find('option:selected').attr('selected', false);
                $(this).closest('.eto-group').find('.eto-js-status').val(etoFn.config.values[formId][1].bookingStatus[0].status).change();
                $(this).closest('.eto-group').find('.eto-js-status_text_selected').val(etoFn.config.values[formId][1].bookingStatus[0].status_text_selected).change();
                ETO.Form.config.form.values[formId].booking[routeId].status = $.extend(true, {}, etoFn.config.values[formId][1].bookingStatus);
            }
        })
        .on('click', '.eto-section-source .eto-field-btn-clear', function(e) {
            var fieldset = $(this).closest('.eto-field'),
                routeId = $(this).closest('.eto-route').attr('data-eto-route-id'),
                formId = $(this).closest('.eto-form').attr('id');

            if (fieldset.find('select.eto-js-source').length > 0) {
                $(this).closest('.eto-group').find('.eto-js-source').find('option:selected').attr('selected', false);
                $(this).closest('.eto-group').find('.eto-js-source').val(etoFn.config.values[formId][1].source[0].source).change();
                $(this).closest('.eto-group').find('.eto-js-comments').val('').change();
            }
        })

        .on('click', '.eto-btn-add-new-source', function(e) {
            $('select.eto-js-source').select2('close');
            var sourcePopup = $('#modal-popup');
            var sourcePopupContainer = sourcePopup.find('.modal-body');

            sourcePopupContainer.LoadingOverlay('show');

            ETO.ajax('booking2/manageSources', {
                data: {
                    action: 'get',
                    siteId: typeof etoFn.config.siteId != 'undefined' ? parseInt(etoFn.config.siteId) : 0
                },
                async: true,
                success: function(data) {
                    if (data.success === true) {
                        var h = '<div class="eto-manage-sources-list clearfix">';
                        $.each(data.sourceList, function(k, v) {
                            h += '<div class="eto-manage-sources-item clearfix">'+
                                '<input type="text" class="form-control input-sm eto-manage-sources-item-input" value="'+ v +'" placeholder="Enter source name...">'+
                                '<button class="btn btn-sm btn-default eto-manage-sources-item-delete" type="button" title="Delete">X</button>'+
                            '</div>';
                        });
                        h += '</div>';
                        h += '<button class="btn btn-primary eto-manage-sources-item-save" type="button">Save</button>';
                        h += '<button class="btn btn-default eto-manage-sources-item-add" type="button">Add new</button>';

                        sourcePopup.find('.modal-title').html('Sources');
                        sourcePopup.find('.modal-body').html(h);
                        sourcePopup.modal('show');
                    }
                    else {
                        ETO.swalWithBootstrapButtons({
                            type: 'error',
                            title: 'Error',
                            html: data.message
                        });
                    }
                },
                complete: function() {
                    sourcePopupContainer.LoadingOverlay('hide');
                }
            });
        })

        .on('click', '.eto-manage-sources-item-add', function() {
            $('.eto-manage-sources-list').append('<div class="eto-manage-sources-item clearfix">'+
                '<input type="text" class="form-control input-sm eto-manage-sources-item-input" value="" placeholder="Enter source name...">'+
                '<button class="btn btn-sm btn-default eto-manage-sources-item-delete" type="button" title="Delete">X</button>'+
            '</div>');
        })
        .on('click', '.eto-manage-sources-item-delete', function() {
            $(this).closest('.eto-manage-sources-item').remove();
        })
        .on('click', '.eto-manage-sources-item-save', function() {
            var sourceList = [];
            var inputList = $('.eto-manage-sources-list .eto-manage-sources-item-input');
            $.each(inputList, function(k, v) {
                sourceList.push($(this).val());
            });
            var sourcePopup = $('#modal-popup');
            var sourcePopupContainer = sourcePopup.find('.modal-body');
            sourcePopupContainer.LoadingOverlay('show');

            ETO.ajax('booking2/manageSources', {
                data: {
                    action: 'save',
                    sourceList: sourceList,
                    siteId: typeof etoFn.config.siteId != 'undefined' ? parseInt(etoFn.config.siteId) : 0
                },
                async: true,
                success: function(data) {
                    if (data.success === true) {
                        ETO.swalWithBootstrapButtons({
                            type: 'success',
                            title: data.message,
                            timer: 5000,
                        });
                    }
                    else {
                        ETO.swalWithBootstrapButtons({
                            type: 'error',
                            title: 'Error',
                            html: data.message
                        });
                    }
                },
                complete: function() {
                    sourcePopupContainer.LoadingOverlay('hide');
                }
            });
        })
        .on('select2:open', 'select.eto-js-source', function(e) {
            var container = $('body > span.select2-container').find('.select2-dropdown .select2-results');
            container.find('.eto-container-add-new-source').remove();
            container.append('<div class="eto-container-add-new-source"><button class="btn btn-sm btn-link btn-block eto-btn-add-new-source" type="button">Edit sources</button></div>');
        })

        .on('select2:select', 'select.eto-js-customer', function(e) {
            var customer = typeof e.params.data != 'undefined' ? e.params.data : {};
            etoFn.setDepartments($(this), customer);
        })
        .on('select2:select', '.eto-section-passenger select.eto-js-search_field', function(e) {
            etoFn.getPassengerDataBySelect($(this), e.params.data);
        })
        // .on(eventClick, '.eto-fieldset-btn-passenger', function() {
        .on('click', '.eto-fieldset-btn-passenger', function() {
            var container = $(this).closest('.eto-group');

            etoFn.setPassengerFromCustomerBtn(container)
        })
        .on('change', 'select.eto-js-vehicle', function() {
            var vehicleId =null !== $(this).val() &&  typeof $(this).val() != 'undefined'
                    ? parseInt($(this).val())
                    : 0,
                container = $(this).closest('.eto-group');
            container.find('.eto-view-vehicle').remove();
            if ( vehicleId > 0 ) {
                var viewButton = '<span data-eto-url="' + ETO.config.appPath + '/admin/vehicles/' + vehicleId + '?tmpl=body&noBack=1" class="eto-field-btn eto-view-vehicle" data-toggle="popover" data-content="View" data-eto-confirm="false"><i class="fa fa-eye"></i></span>';
                container.find('.eto-field-vehicle').find('.eto-field-buttons').prepend(viewButton);
            }
            else {
                container.find('.eto-field-vehicle').find('.eto-field-btn-clear').addClass('hidden');
            }
        })
        .on('change', 'select.eto-js-fleet', function() {
            var fleetId = null !== $(this).val() && typeof $(this).val() != 'undefined'
                    ? parseInt($(this).val())
                    : 0,
                group = $(this).closest('.eto-group');

            group.find('.eto-view-fleet').remove();
            $('.select2-2urn-container').attr('title', false);

            if ( fleetId > 0 ) {
                var viewButton = '<span data-eto-url="' + ETO.config.appPath + '/admin/users/' + fleetId + '?tmpl=body&noBack=1" class="eto-field-btn eto-view-fleet" data-toggle="popover" data-content="View" data-eto-confirm="false"><i class="fa fa-eye"></i></span>';
                group.find('.eto-js-comments, .eto-js-commission, .eto-js-cash, .eto-js-vehicle').closest('.eto-field').removeClass('hidden');
                group.find('.eto-field-fleet').find('.eto-field-buttons').prepend(viewButton);
                group.find('.eto-js-comments, .eto-js-commission').closest('.eto-field').removeClass('hidden');
                ETO.Form.updateSummary(group, false);

                ETO.Form.select2FleetCommission(group, fleetId);
            }
            else {
                group.find('.eto-js-comments, .eto-js-commission').val('').closest('.eto-field').addClass('hidden');
                ETO.Form.updateSummary(group, true);

                if (typeof ETO.config.fleet != "undefined") {
                    delete ETO.config.fleet;
                }
            }
        })
        .on('click', '.eto-section-fleet .eto-field-btn-clear', function(e) {
            var fieldset = $(this).closest('.eto-field'),
                group = $(this).closest('.eto-group'),
                vehicleSelect = group.find('select.eto-js-vehicle'),
                driverSelect = group.find('select.eto-js-driver'),
                routeId = group.closest('.eto-route').attr('data-eto-route-id'),
                formId = group.closest('.eto-form').attr('id');

            if (fieldset.find('select.eto-js-fleet').length > 0) {
                group.find('.eto-js-fleet_text_selected').val('');
                vehicleSelect.find('option:selected').attr('selected', false);
                driverSelect.find('option:selected').attr('selected', false);
                group.find('.eto-view-driver').remove();

                $.each(ETO.Form.config.form.values[formId].booking[routeId].fleet, function (k,v) {
                    $.each(v, function (x,z) {
                        ETO.Form.config.form.values[formId].booking[routeId].fleet[k][x] = '';
                    });
                });
            }

            ETO.Form.updateSummary(group, true);

            if (typeof ETO.config.fleet != "undefined") {
                delete ETO.config.fleet;
            }
        })
        .on('change', 'select.eto-js-driver', function() {
            var driverId = null !== $(this).val() && typeof $(this).val() != 'undefined'
                    ? parseInt($(this).val())
                    : 0,
                container = $(this).closest('.eto-group'),
                routeId = container.closest('.eto-route').attr('data-eto-route-id'),
                formId = container.closest('.eto-form').attr('id');

            $('.select2-2urn-container').attr('title', false);
            if ( driverId > 0 ) {
                var viewButton = '<span data-eto-url="' + ETO.config.appPath + '/admin/users/' + driverId + '?tmpl=body&noBack=1" class="eto-field-btn eto-view-driver" data-toggle="popover" data-content="View" data-eto-confirm="false"><i class="fa fa-eye"></i></span>';
                container.find('.eto-js-comments, .eto-js-commission, .eto-js-cash, .eto-js-vehicle').closest('.eto-field').removeClass('hidden');

                if (etoFn.config.driverId[formId][routeId] !== driverId) {
                    ETO.Form.createValuesToObject(formId, routeId, 'bookingStatus', 0);
                    ETO.Form.config.form.values[formId].booking[routeId].bookingStatus[0].status = 'assigned';
                    ETO.Form.config.form.values[formId].booking[routeId].bookingStatus[0].status_text_selected = 'Assigned';

                    var bookingStatus = container.closest('.eto-form').find('.eto-section-bookingStatus').find('.eto-route-' + routeId);
                    bookingStatus.find('.eto-summary-value').html('Assigned');
                    bookingStatus.find('.eto-js-status').val('assigned').change();

                    ETO.Form.visibilitySummaryValue(container.closest('.eto-form').find('.eto-section-bookingStatus').find('.eto-route-' + routeId).find('.eto-group'), true);
                    ETO.Form.config.form.values[formId].booking[routeId].driver[0].vehicle = null;
                    ETO.Form.config.form.values[formId].booking[routeId].driver[0].vehicle_text_selected = '';
                }

                container.find('.eto-view-driver').remove();
                container.find('.eto-field-driver').find('.eto-field-buttons').prepend(viewButton);

                var vehicleId = ETO.Form.config.form.values[formId].booking[routeId].driver[0].vehicle;

                ETO.Form.select2DriverVehicles(container, driverId, vehicleId);
                etoFn.config.driverId[formId][routeId] = driverId;
            }
            else {
                container.find('.eto-js-comments, .eto-js-commission, .eto-js-cash').val('').closest('.eto-field').addClass('hidden');
                container.find('.eto-js-vehicle').closest('.eto-field').addClass('hidden');

                if (driverId === 0) {
                    ETO.Form.config.form.values[formId].booking[routeId].driver = $.extend(true, {}, etoFn.config.values[formId][1].driver);
                }
            }

            var group = $(this).closest('.eto-group'),
                vehicle = group.find('.eto-js-vehicle'),
                selected = vehicle.find('option:selected').text(),
                text_selected = group.find('input.eto-js-vehicle_text_selected');

            if (parseInt($(this).val()) > 0) {
                text_selected.val(selected);
                ETO.Form.setValuesToObject(text_selected);
            }
        })
        .on('click', '.eto-section-driver .eto-field-btn-clear', function(e) {
            var fieldset = $(this).closest('.eto-field'),
                container = $(this).closest('.eto-group'),
                vehicleSelect = container.find('select.eto-js-vehicle'),
                driverSelect = container.find('select.eto-js-driver'),
                routeId = container.closest('.eto-route').attr('data-eto-route-id'),
                formId = container.closest('.eto-form').attr('id'),
                driverId = etoFn.config.driverId[formId][routeId];

            if (fieldset.find('select.eto-js-driver').length > 0) {
                container.find('.eto-js-driver_text_selected').val('');
                container.find('.eto-js-vehicle_text_selected').val('');
                vehicleSelect.find('option:selected').attr('selected', false);
                driverSelect.find('option:selected').attr('selected', false);
                container.find('.eto-view-driver').remove();
                delete ETO.config.driver;
                etoFn.config.driverId[formId][routeId] = false;

                $.each(ETO.Form.config.form.values[formId].booking[routeId].driver, function (k,v) {
                    $.each(v, function (x,z) {
                        ETO.Form.config.form.values[formId].booking[routeId].driver[k][x] = '';
                    });
                    ETO.Form.config.form.values[formId].booking[routeId].driver[k].vehicle = null;
                });
            }
            else if (fieldset.find('select.eto-js-vehicle').length > 0) {
                vehicleSelect.find('option:selected').attr('selected', false);
                vehicleSelect.val(0).change();
                container.find('.eto-js-vehicle_text_selected').val('');
                fieldset.find('.eto-field-btn-clear').addClass('hidden');
                container.find('.eto-view-vehicle').remove();
                delete ETO.config['driver-vehicles'][driverId];
                ETO.Form.config.form.values[formId].booking[routeId].driver[0].vehicle_text_selected = ETO.trans('booking.booking_unassigned');
            }
        })
        .on('change', 'select.eto-js-status', function() {
            var status = $(this),
                statusesList = ['canceled', 'unfinished', 'rejected'],
                selected = status.find('option:selected').val(),
                group = status.closest('.eto-group'),
                comments = group.find('.eto-js-comments');

            if (statusesList.indexOf(selected) !== -1) {
                comments.closest('.eto-field').removeClass('hidden');
                status.closest('.eto-field').find('.eto-field-btn-advance').removeClass('hidden');
            }
            else {
                comments.val('');
                comments.closest('.eto-field').addClass('hidden');
                comments.change();
                status.closest('.eto-field').find('.eto-field-btn-advance').addClass('hidden');
            }
        })
        .on('change', '.eto-js-address', function() {
            if ($(this).val() !== null && $(this).val().localeCompare('') !== 0) {
                $(this).closest('.eto-field').find('.eto-field-btn-clear').removeClass('hidden');
            }
            else {
                $(this).closest('.eto-field').find('.eto-field-btn-clear').addClass('hidden');
            }
        })
        .on('change', 'select.eto-js-item', function(e) {
            var data = ETO.Form.findInObject(ETO.fields.booking.data, $(this).val()),
                name = $(this).closest('.eto-field').attr('data-eto-field-name'),
                group = $(this).closest('.eto-group'),
                formId = group.closest('.eto-form').attr('id'),
                section = group.find('.eto-summary-link').attr('data-eto-section'),
                routeId = group.closest('.eto-route').attr('data-eto-route-id'),
                index = group.find('.eto-summary-link').attr('data-eto-index'),
                item_key_field = group.find('input.eto-js-item_key'),
                item_trans_key_field = group.find('input.eto-js-item_trans_key'),
                item_price_field = group.find('input.eto-js-price'),
                item_amount_field = group.find('input.eto-js-amount'),
                price = typeof data.price != 'undefined' ? data.price : 0,
                amount = typeof data.amount != 'undefined' && parseFloat(data.amount) > 0 ? data.amount : 1;

            etoFn.setVisibleOnNoVisibility('item', group);

            // if ($(this).val().length === 0) {
            //     group.find('.eto-js-amount').val(0).closest('.eto-field').addClass('hidden');
            //     group.find('.eto-js-price').val('0.00').closest('.eto-field').addClass('hidden');
            // }
            // else {
                group.find('.eto-js-amount').change().closest('.eto-field').removeClass('hidden');
                group.find('.eto-js-price').closest('.eto-field').removeClass('hidden');
            // }

            var sectionObject = ETO.Form.config.form.values[formId].booking[routeId][section],
                prevItem = null;

            if (sectionObject[index] && typeof sectionObject[index].prev_item != 'undefined') {
                prevItem = sectionObject[index].prev_item;
            }

            if ($(this).select2('val').localeCompare(prevItem) !== 0 ) {
                item_price_field.val(price);
                ETO.Form.setValuesToObject(item_price_field);

                item_amount_field.val(amount);
                ETO.Form.setValuesToObject(item_amount_field);
            }
            if (sectionObject[index]) {
                sectionObject[index].prev_item = $(this).val();
            }

            if (typeof data.id != 'undefined') {
                var fieldKey = data.field_key,
                    transKey = data.params.trans_key;

                item_key_field.val(fieldKey);
                ETO.Form.setValuesToObject(item_key_field);

                item_trans_key_field.val(transKey);
                ETO.Form.setValuesToObject(item_trans_key_field);
            }
            else {
                etoFn.setElementValue(formId, group, index, name, data.id);
            }
            etoFn.setPriceSummary(formId);
        })
        .on('change', '.eto-js-inputs', function(e) {
            var name = $(this).attr('data-eto-name'),
                formId = $(this).closest('.eto-form').attr('id');

            if (['price','amount'].indexOf(name)) {
                etoFn.setPriceSummary(formId);
            }
        })
        .on('keyup', '.eto-js-inputs', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                // Get all focusable elements on the page
                var group = $(this).closest('.eto-group'),
                    inputs = group.find('.eto-field:not(.hidden)').find('.eto-js-inputs:not(.eto-field-type-checkbox-input)'),
                    index = inputs.index(this) + 1,
                    nextInput = inputs.eq(index);

                if (this.tagName.localeCompare('TEXTAREA') !== 0 || e.originalEvent.shiftKey === false) {
                    if (index >= inputs.length) {
                        ETO.Form.updateSummary(group, false);
                        ETO.Form.hideFields(group.closest('.eto-form'));
                    } else {
                        nextInput.focus();
                    }
                }
            }
        })
        .on('click', '.eto-view-driver, .eto-view-vehicle', function(e) {
            ETO.Booking.modalIframe(this);
        })
        .on('change', '.eto-js-payment_method', function(e) {
            var group = $(this).closest('.eto-group');
            ETO.Form.updateSummary(group, false);
            ETO.Form.hideFields(group.closest('.eto-form'));
        })
        .on('change', '.eto-js-date', function(e) {
            var group = $(this).closest('.eto-group');
            ETO.Form.updateSummary(group, false);
            ETO.Form.hideFields(group.closest('.eto-form'));
        })
        .on('select2:close', 'select.eto-js-vehicle_type, select.eto-js-amount', function(e) {
            var group = $(this).closest('.eto-group');
            // wykonuje sie przed clear - poprawic
            ETO.Form.updateSummary(group, false);
            ETO.Form.hideFields(group.closest('.eto-form'));
        })
        .on('click', '.eto-btn-submit', function(e){
            var formContainer = $(this).closest('.eto-form'),
                buttonSubmit = $(this);

            formContainer.LoadingOverlay('show');

            if (ETO.Form.setAllValuesToObject($(this).closest('.eto-form'), 'update') === true) {
                var formId = $(this).closest('.eto-form').attr('id'),
                    data = ETO.Form.config.form.values[formId];

                // console.log(ETO.Form.config.form.values[formId]);
                data.action = 'update';

                if (etoFn.config.bookingId[formId] !== false){
                    data.bookingId = etoFn.config.bookingId[formId];
                }

                // Clear HTML from variable - ModSecurity returns 403 if HTML is send to server.
                if (data.booking && data.booking[1].bookingStatus && data.booking[1].bookingStatus[0].status_text_selected) {
                    data.booking[1].bookingStatus[0].status_text_selected = $(data.booking[1].bookingStatus[0].status_text_selected).text();
                }

                ETO.ajax('set-new/booking', {
                    data: data,
                    async: true,
                    success: function(data) {
                        if (data.success === true) {
                            ETO.swalWithBootstrapButtons({
                                type: 'success',
                                title: ETO.trans('booking.successBookingSaved'),
                                timer: 5000,
                            });

                            if (buttonSubmit.hasClass('eto-btn-submit-not-clear') === false) {
                                delete ETO.config.driver;
                                delete ETO.config.customer;
                                delete ETO.Form.config.form.values[formId];
                                delete ETO.Booking.Form.config.driverId[formId];
                                delete ETO.Booking.Form.config.values[formId];
                                delete ETO.Booking.Form.config.bookingId[formId];
                                delete ETO.Booking.Form.config.refNumber[formId];

                                if (typeof etoFn.config.oldData[formId] =='undefined') { etoFn.config.isReturn = false; }
                                else { delete etoFn.config.oldData[formId]; }

                                formContainer.html('');
                                formContainer.attr('id', ETO.uid());
                                etoFn.initializeForm(formContainer);
                            }

                            setTimeout(function() {
                                if (window.parent && window.parent.$('.modal-caller').length > 0) {
                                    // Stay in caller popup
                                }
                                else if (window.parent && window.parent.$('#modal-popup').length > 0 && buttonSubmit.hasClass('eto-btn-submit-close')) {
                                    window.parent.$('#modal-popup').modal('hide');
                                }
                            }, 1000);

                            if (formContainer.closest('.eto-modal-booking-edit').length > 0 && buttonSubmit.hasClass('eto-btn-submit-close')) {
                                formContainer.closest('.eto-modal-booking-edit').modal('hide');
                            }
                            if (ETO.model == 'dispatch') {
                                ETO.Dispatch.refreshDatatableAfterSaveBooking();
                            }
                        }
                        else {
                            var html = '';

                            $.each(data.message, function(i, v) { html += v + '<br>'; });

                            ETO.swalWithBootstrapButtons({
                                type: 'error',
                                title: 'Error',
                                html: html
                            });
                        }
                    },
                    complete: function() {
                        formContainer.LoadingOverlay('hide');
                    }
                });
            }
            else {
                ETO.swalWithBootstrapButtons({
                    type: 'warning',
                    title: 'Please enter all required fields',
                });
                formContainer.LoadingOverlay('hide');
            }
        })
        .on('click', '.eto-section-title.eto-btn-advance', function(e) {
            var icon = $(this).find('i');

            if (icon.hasClass('fa-sort-asc')) {
                icon.replaceClass('fa-sort-asc','fa-sort-desc')
            }
            else if (icon.hasClass('fa-sort-desc')) {
                icon.replaceClass('fa-sort-desc','fa-sort-asc')
            }
        })
        .on('change', '.eto-modal-form-settings input:not(#statusColorSettings):not(.colorpicker)', function(e) {
            var values = ETO.parseSettings($(this));

            if (Object.keys(values).length > 0) {
                ETO.saveSettings(values);
            }
        })
        .on('blur', '.eto-modal-form-settings input.eto-settings-status_color', function(e) {
            var values = ETO.parseSettings($(this)),
                value = $(this).val(),
                status = $(this).closest('.form-group').find('.eto-color-btn-clear').data('etoStatus');

            if(null !== value && value.localeCompare(ETO.settings('origin_status_color.'+status)) !== 0) {
                $(this).closest('.form-group').find('.eto-color-btn-clear').removeClass('hidden');
            }

            if (Object.keys(values).length > 0) {
                ETO.saveSettings(values);
            }
        })
        .on('change', '.eto-modal-form-settings input#show_inactive_drivers_form', function(e) {
            if ($(this).attr('checked') == 'checked') {
                etoFn.form.settings.view.show_inactive_drivers_form = true;
            }
            else {
                etoFn.form.settings.view.show_inactive_drivers_form = false;
            }
        })
        .on('change', '.eto-modal-form-settings input#instant_dispatch_color_system', function(e) {
            if ($(this).attr('checked') == 'checked') {
                etoFn.form.settings.view.instant_dispatch_color_system = true;
            }
            else {
                etoFn.form.settings.view.instant_dispatch_color_system = false;
            }
        })
        .on('change', '.eto-modal-form-settings input#advance_open', function(e) {
            var icon = $('.eto-section-title.eto-btn-advance').find('i');

            if ($(this).attr('checked') == 'checked') {
                etoFn.form.settings.view.advance_open = true;
                icon.replaceClass('fa-sort-asc','fa-sort-desc');
                $('.eto-section-advance').removeClass('hidden');
            }
            else {
                etoFn.form.settings.view.advance_open = false;
                icon.replaceClass('fa-sort-desc','fa-sort-asc');
                $('.eto-section-advance').addClass('hidden');
            }
        })
        .on('change', '.eto-modal-form-settings input#amounts_view_passenger', function(e) {
            $('.eto-section-passengerAmount').toogleClass('hidden');
            if ($(this).attr('checked') == 'checked') {
                etoFn.form.settings.view.amounts_view_passenger = true;
            }
            else {
                etoFn.form.settings.view.amounts_view_passenger = false;
            }
        })
        .on('change', '.eto-modal-form-settings input#amounts_view_suitcase', function(e) {
            $('.eto-section-luggageAmount').toogleClass('hidden');
            if ($(this).attr('checked') == 'checked') {
                etoFn.form.settings.view.amounts_view_suitcase = true;
            }
            else {
                etoFn.form.settings.view.amounts_view_suitcase = false;
            }
        })
        .on('change', '.eto-modal-form-settings input#amounts_view_carry_on', function(e) {
            $('.eto-section-handLuggageAmount').toogleClass('hidden');
            if ($(this).attr('checked') == 'checked') {
                etoFn.form.settings.view.amounts_view_carry_on = true;
            }
            else {
                etoFn.form.settings.view.amounts_view_carry_on = false;
            }
        })
        .on('change', '.eto-modal-form-settings input#send_notification', function(e) {
            $('.eto-js-send_notification').toogleCheckbox();

            if ($(this).attr('checked') == 'checked') {
                etoFn.form.settings.checked.send_notification = true;
            }
            else {
                etoFn.form.settings.checked.send_notification = false;
            }
        })
        .on('change', '.eto-modal-form-settings input#waiting_time', function(e) {
            $('.eto-field-waiting_time').toogleClass('hidden');
            if ($(this).attr('checked') == 'checked') {
                etoFn.form.settings.view.waiting_time = true;
            }
            else {
                etoFn.form.settings.view.waiting_time = false;
            }
        })
        .on('change', '.eto-modal-form-settings input.eto-settings-custom_field_display', function(e) {
            $('.eto-section-customField').toogleClass('hidden');
            if ($(this).attr('checked') == 'checked') {
                etoFn.form.settings.booking.custom_field_display = true;
                $(this).closest('.eto-modal-form-settings').find('.eto-settings-custom_field.name').closest('.form-group').removeClass('hidden');
            }
            else {
                etoFn.form.settings.booking.custom_field_display = false;
                $(this).closest('.eto-modal-form-settings').find('.eto-settings-custom_field.name').closest('.form-group').addClass('hidden');
            }
        })
        .on('change', '.eto-modal-form-settings input#add_vehicle_button', function(e) {
            if ($(this).attr('checked') == 'checked') {
                etoFn.form.settings.booking.add_vehicle_button = true;
                $('.eto-section-vehicle').find('.eto-add-item, .eto-field-vehicle_amount').removeClass('hidden');
            }
            else {
                etoFn.form.settings.booking.add_vehicle_button = false;
                $('.eto-section-vehicle').find('.eto-add-item, .eto-field-vehicle_amount').addClass('hidden'); // .eto-field-vehicle_amount,
            }
        })
        .on('change', '.eto-modal-form-settings input.eto-settings-custom_field_name', function(e) {
            etoFn.form.settings.booking.custom_field_name = $(this).val().trim() != '' ? $(this).val() : ETO.trans('booking.customPlaceholder');
            etoFn.form.sections.customField.fields.custom.placeholder = $(this).val().trim() != '' ? $(this).val() : ETO.trans('booking.customPlaceholder');
            etoFn.form.sections.customField.tooltip.custom.label = $(this).val().trim() != '' ? $(this).val() : ETO.trans('booking.customPlaceholder');
            ETO.config.eto_booking.custom_field.name = etoFn.form.settings.booking.custom_field_name;

            $('.eto-section-customField').find('.eto-summary-label').html(ETO.settings('eto_booking.custom_field.name', ETO.trans('booking.customPlaceholder')).toString());
            $('.dataTables_scrollHeadInner')
                .find('.eto-custom-column.sorting, .eto-custom-column.sorting_asc,.eto-custom-column.sorting_desc')
                .html(ETO.settings('eto_booking.custom_field.name', ETO.trans('booking.customPlaceholder')).toString());
            $('.eto-section-customField').find('.eto-js-custom').attr('placeholder', ETO.settings('eto_booking.custom_field.name', ETO.trans('booking.customPlaceholder')).toString());

            // if (ETO.model == 'dispatch') {
            //     ETO.Dispatch.refreshDatatableAfterSaveBooking();
            // }
        })
        .on('change', '.eto-modal-form-settings input#statusColorSettings', function(e) {
            if ($(this).attr('checked') == 'checked') {
                $('.eto-color-settings').removeClass('hidden');
            }
            else {
                $('.eto-color-settings').addClass('hidden');
            }
        })
        .on('click', '.eto-color-btn-clear', function (e) {
            var status = $(this).data('etoStatus'),
                originColor = ETO.settings('origin_status_color.'+status);

            $('.eto-settings-status_color_' + status).minicolors('value',originColor).blur();
            $(this).addClass('hidden');
        })
        .on('hidden.bs.modal', '.eto-modal-booking-edit', function() {
            if(etoFn.config.multiVehicleForce === true) {
                etoFn.form.settings.booking.add_vehicle_button = false;
                etoFn.config.multiVehicleForce = false;
                $('#add_vehicle_button').attr('disabled', false);
            }
        });

        ETO.configFormUpdate($('.eto-modal-form-settings').find('input:not(#statusColorSettings)'), etoFn.form.settings);

        $('.eto-modal-form-settings input#statusColorSettings').change();

        $('.colorpicker').each( function(){
            $(this).minicolors({
                animationSpeed: 50,
                animationEasing: 'swing',
                change: null,
                changeDelay: 0,
                control: 'hue',
                defaultValue: $(this).attr('value') || '',
                format: 'hex',
                hide: null,
                hideSpeed: 100,
                inline: false,
                keywords: '',
                letterCase: 'lowercase',
                opacity: false,
                position: 'bottom left',
                show: null,
                showSpeed: 100,
                theme: 'bootstrap',
                swatches: [
                    '#000000',
                    '#ffffff',
                    '#FF0000',
                    '#777777',
                    '#337ab7',
                    '#5cb85c',
                    '#5bc0de',
                    '#f0ad4e',
                    '#d9534f'
                ],
            });
        });
    };

    etoFn.addNewElementToSection = function(el, index){
        if (el instanceof jQuery === false) { el = $(el); }
        el.LoadingOverlay('show');
        var section = el.closest('.eto-section').attr('data-eto-section'),
            formContainer = el.closest('.eto-form'),
            formId = formContainer.attr('id'),
            data = $.extend(true, {}, etoFn.form.sections[section]),
            routeId = el.closest('.eto-route').hasClass('eto-route-1') ? 1 : 2,
            container = formContainer.find('.eto-section-'+ section +' .eto-route-'+ routeId +' .eto-groups'),
            bookingSectionConfig = $.extend(true, {}, etoFn.config.defaults.section),
            sectionConfig = $.extend(true, {}, ETO.Form.config.defaults.section);

        bookingSectionConfig = $.extend(true, sectionConfig, bookingSectionConfig);
        data = $.extend(true, bookingSectionConfig, data);
        ETO.Form.createGroup(section, container, data, true, index);

        var newIndex = formContainer.find('.eto-section-'+ section +' .eto-route-'+ routeId +' .eto-groups').children().last().find('.eto-summary-link').data('etoIndex');

        if(typeof newIndex != 'undefined' && typeof ETO.Form.config.form.values[formId].booking[routeId][section][newIndex] == 'undefined') {
            var newData = $.extend(true, {}, etoFn.config.values[formId][1][section][0]);
            ETO.Form.config.form.values[formId].booking[routeId][section][newIndex] = newData;
        }

        el.LoadingOverlay('hide');
    };

    etoFn.viewDeposit = function(paymentMethod) {
        if (paymentMethod instanceof jQuery === false) { paymentMethod = $(paymentMethod); }

        var peymentId = paymentMethod.length > 0 ? paymentMethod.val() : 0;

        if (parseInt(peymentId) > 0) {
            paymentMethod.closest('.eto-form').find('.eto-section-deposit').find('.eto-group').removeClass('hidden');
        }
        else {
            paymentMethod.closest('.eto-form').find('.eto-section-deposit').find('.eto-group').addClass('hidden');
        }
    };

    etoFn.serviceDuration = function(el, override) {
        if (el instanceof jQuery === false) { el = $(el); }
        var serviceId = el !== false ? el.val() : ETO.settings('service.selected', 0),
            formContainer = el.closest('.eto-form'),
            formId = formContainer.attr('id'),
            durationContainer = formContainer.find('.eto-section-serviceDuration'),
            durationInput = durationContainer.find('.eto-js-service_duration'),
            locationButtons = formContainer.find('.eto-section-location').find('.eto-section-buttons'),
            locations = formContainer.find('.eto-section-location');

        if ((typeof serviceId != 'undefined' && serviceId.localeCompare('') === 0) || typeof serviceId == 'undefined' || serviceId === false) { serviceId = 0; }

        var ServiceObject = ETO.Form.findInObject(ETO.settings('service.data', []), serviceId);

        ETO.config.serviceType[formId] =  ServiceObject.type;

        if (ServiceObject.duration === 0) {
            durationInput.val('').change();
            durationContainer.addClass('hidden');
            durationContainer.find('.eto-summary-placeholder').removeClass('hidden');
            durationContainer.find('.eto-summary-value').addClass('hidden').html('');
        }
        else {
            var durationInputValue = ServiceObject.min;

            if (durationInputValue.localeCompare('') === 0) { durationInputValue = 0; }

            durationContainer.removeClass('hidden');
            if (override === false) {
                durationContainer.find('.eto-js-service_duration').val(durationInputValue);
                durationContainer.find('.eto-js-service_duration').change();
            }
        }

        if (parseInt(ServiceObject.hide_location) === 0) {
            locations.find('.eto-group').removeClass('hidden');
            locations.find('.eto-summary-buttons').removeClass('hidden');
            locationButtons.find('.eto-section-btn-swap, .eto-section-btn-add').removeClass('hidden');
            locations.find('.eto-route-1, .eto-route-2').each(function () {
                ETO.Form.enableDisableButton($(this));
            });
        }
        else {
            var notHide = locations.find('[data-eto-index="0"]').closest('.eto-group');
            locations.find('.eto-group').not(notHide).addClass('hidden');
            locations.find('.eto-summary-buttons, .eto-field-buttons .eto-summary-btn-delete').addClass('hidden');
            locations.find('.eto-summary-buttons').addClass('hidden');
            locationButtons.find('.eto-section-btn-swap, .eto-section-btn-add').addClass('hidden');
        }
        // if (ETO.config.serviceType == 'scheduled') {
        //     var locationsToDelete = formContainer.find('.eto-section-location .eto-group:not(.eto-route-1, .eto-section-location .eto-group:first):not(.eto-route-2 .eto-group:first):not(.eto-route-1, .eto-section-location .eto-group:last):not(.eto-route-2 .eto-group:last)');
        //
        //     locationsToDelete.each(function() {
        //         var formId = $(this).closest('.eto-form').attr('id'),
        //             index = $(this).find('.eto-summary-link').attr('data-eto-index');
        //
        //         etoFn.destroyFieldObjectValues(formId, locationsToDelete, index);
        //
        //         $(this).remove();
        //     });
        // }
    };

    etoFn.getFormObject = function(formId) {
        var formObject = typeof ETO.config.formObject != 'undefined' ? ETO.config.formObject.values : false,
            bookingId = '',
            refNumber = '';

        if (formObject === false || typeof etoFn.config.bookingId[formId] != 'undefined') {
            function getUrlVal(str) {
                var v = window.location.search.match(new RegExp('(?:[\?\&]'+str+'=)([^&]+)'));
                return v ? v[1] : null;
            }

            var configParams = etoFn.config.requestParams;
            var requestParams = {
                phoneNumber: getUrlVal('phoneNumber'),
                customerId: getUrlVal('customerId'),
                customerName: getUrlVal('customerName'),
                customerEmail: getUrlVal('customerEmail'),
                customerPhone: getUrlVal('customerPhone'),
            };
            if (configParams.phoneNumber) {
                requestParams.phoneNumber = configParams.phoneNumber;
            }
            if (configParams.customerId) {
                requestParams.customerId = configParams.customerId;
            }
            if (configParams.customerName) {
                requestParams.customerName = configParams.customerName;
            }
            if (configParams.customerEmail) {
                requestParams.customerEmail = configParams.customerEmail;
            }
            if (configParams.customerPhone) {
                requestParams.customerPhone = configParams.customerPhone;
            }

            ETO.ajax('booking2/getDefaultFormObject/' + etoFn.config.bookingId[formId], {
                data: {
                    requestParams: requestParams
                },
                success: function (data) {
                    formObject = data.values;
                    bookingId = data.bookingId;
                    refNumber = data.refNumber;

                    etoFn.config.bookingDetails[formId] = [];
                    if (typeof data.total_details != 'undefined') {
                        etoFn.config.bookingDetails[formId] = data.total_details;
                    }
                    if (typeof data.income_charges != 'undefined') {
                        etoFn.config.bookingIncomeCharges[formId] = data.income_charges;
                    }
                }
            });
        }

        etoFn.config.values[formId] = {1: $.extend(true, {}, formObject)};
        if (ETO.Form.isInteger(bookingId) === true && bookingId != '') {
            etoFn.config.bookingId[formId] = bookingId;
            etoFn.config.oldData[formId] = $.extend(true, {}, etoFn.config.values[formId]);
        } else {
            etoFn.config.bookingId[formId] = false;
        }
        if (typeof refNumber != 'undefined' && refNumber != '') {
            etoFn.config.refNumber[formId] = refNumber;
        } else {
            etoFn.config.refNumber[formId] = false;
        }
    };

    etoFn.initializeForm = function(bookingContainer)
    {
        if (typeof bookingContainer == 'undefined') {
            bookingContainer = $('#dispatch .eto-form-booking');
            if (bookingContainer.length === 0) {
                bookingContainer = $('.eto-wrapper-booking-edit .eto-form-booking');
            }
            if (bookingContainer.length === 0) {
                bookingContainer = $('.eto-wrapper-booking-create .eto-form-booking');
            }
        }

        var formId = bookingContainer.attr('id');

        if(typeof etoFn.form.settings == 'undefined') {
            etoFn.form = etoFn.formConfigGenerate(); // etoInfo - update form object

            $.each(ETO.settings('bookingStatusColor'), function (key, data) {
                etoFn.form.settings.booking['status_color_' + key] = data.color;
            });
        }

        if (typeof formId == "undefined") {
            return false;
        }
        etoFn.getFormObject(formId);
        if (parseInt(ETO.settings('booking.allow_fleet_operator', 0)) === 0 || ETO.hasRole('admin.fleet_operator')) {
            delete etoFn.form.sections.fleet;
        }

        var defaultFields = $.extend(true, {},etoFn.form.sections), // etoInfo - a copy of the object without its instance
            settingsConfig = $.extend(true, {},ETO.Form.config.defaults.settings),
            formMode = etoFn.config.bookingId[formId] !== false ? 'eto-form-mode-edit' : 'eto-form-mode-add',
            notClear = etoFn.config.bookingId[formId] !== false ? ' eto-btn-submit-not-clear' : '',
            saveAndCloseCopy = etoFn.config.bookingId[formId] !== false
                ? '<button class="btn btn-sm btn-success eto-btn-submit eto-btn-submit-close" for="'+formId+'" type="button"><span>Save & Close</span></button>'
                : '<button class="btn btn-sm btn-success eto-btn-submit eto-btn-submit-not-clear" for="'+formId+'" type="button"><span>Save & Copy</span></button>',
            html = '<div class="eto-form-settings">\
                  <button class="btn btn-sm btn-default eto-btn-form-settings" type="button" data-toggle="modal" data-target=".eto-modal-form-settings">\
                    <i class="fa fa-cogs"></i>\
                  </button>\
                </div>\
                <div class="clearfix eto-positions">\
                <div class="eto-form-feedback hidden"></div>\
                <div class="clearfix eto-position-1"></div>\
                <div class="clearfix eto-position-2"></div>\
                <div class="clearfix eto-position-3"></div>\
                <div class="clearfix eto-position-4">\
                <div class="clearfix eto-position-4-1"></div>\
                <div class="clearfix eto-position-4-2"></div>\
                </div>\
                <div class="clearfix eto-position-5">\
                <div class="clearfix eto-position-5-1"></div>\
                <div class="clearfix eto-position-5-2"></div>\
                </div>\
                <div class="clearfix eto-position-6"></div>\
                <div class="clearfix eto-position-7"></div>\
                <div class="clearfix eto-position-8"></div>\
                </div>\
                <div class="clearfix eto-controls">\
                <div class="eto-price-summary">\
                <span class="eto-price-summary-route hidden">\
                <span class="eto-price-summary-route-1">\
                <span class="eto-price-summary-route-header hidden">Outbound</span>\
                <span class="eto-price-summary-route-items"></span>\
                <span class="eto-price-summary-route-total hidden">Subtotal: <span class="eto-js-subtotal" data-eto-value="0"></span></span>\
                </span>\
                <span class="eto-price-summary-route-2">\
                <span class="eto-price-summary-route-header hidden">Return</span>\
                <span class="eto-price-summary-route-items"></span>\
                <span class="eto-price-summary-route-total hidden">Subtotal: <span class="eto-js-subtotal" data-eto-value="0"></span></span>\
                </span>\
                </span>\
                <span class="eto-price-summary-total">Total: <span class="eto-js-total" data-eto-value="0">'+ ETO.formatPrice(0) +'</span> <span class="eto-price-summary-info hidden"><i class="fa fa-info-circle"></i></span></span>\
                </div>\
                <div class="eto-buttons">\
                </button>\
                 <div class="btn-group dropup">\
                <button class="btn btn-sm btn-success eto-btn-submit'+notClear+'" for="'+formId+'" type="button"><span>Save</span></button>\
                <button type="button" class="btn btn-sm btn-success eto-btn-submit-toogle dropdown-toggle" data-toggle="dropdown">\
                <i class="fa fa-angle-down"></i>\
                </button>\
                <ul class="dropdown-menu " role="menu">\
                <li>'+saveAndCloseCopy+'</li>\
                </ul>\
                </div> \
                </div>\
                </div>';

        ETO.Form.createValuesToObject(formId);
        if (typeof etoFn.config.values[formId] != 'undefined' && typeof ETO.Form.config.form.values[formId] != "undefined") {
            ETO.Form.config.form.values[formId].booking = $.extend(true, {}, etoFn.config.values[formId]);
            if(etoFn.form.settings.booking.add_vehicle_button === false &&
                (Object.keys(ETO.Form.config.form.values[formId].booking[1].vehicle).length > 1
                || parseInt(ETO.Form.config.form.values[formId].booking[1].vehicle[0].vehicle_amount) > 1)) {
                etoFn.form.settings.booking.add_vehicle_button = true;
                etoFn.config.multiVehicleForce = true;
                $('#add_vehicle_button').attr('disabled', true);
            }
        }

        etoFn.config.driverId[formId] = {
            1: (etoFn.config.values[formId][1] != 'undefined' && etoFn.config.values[formId][1].driver[0].driver != '') ? etoFn.config.values[formId][1].driver[0].driver : false,
            2: false,
        };

        bookingContainer.addClass(formMode);
        bookingContainer.append(html);
        if (ETO.isMobile) {
            bookingContainer.addClass('eto-form-mobile');
        }
        settingsConfig = $.extend(true, settingsConfig ,etoFn.form.settings);

        for (var i in defaultFields) {
            etoFn.createSection(defaultFields[i], i, formId);
        }

        if (settingsConfig.sectionOrder === true) {
            ETO.Form.setSortable('section', 'section', bookingContainer);
        }

        if (settingsConfig.resizeAction === true) {
            ETO.Form.formResize();
        }

        bookingContainer.addClass('eto-controls-fixed');

        if (etoFn.form.settings.view.amounts_view_passenger === false) {
            bookingContainer.find('.eto-section-passengerAmount').addClass('hidden');
        }
        if (etoFn.form.settings.view.amounts_view_suitcase === false) {
            bookingContainer.find('.eto-section-luggageAmount').addClass('hidden');
        }
        if (etoFn.form.settings.view.amountsViewCarryOn === false) {
            bookingContainer.find('.eto-section-handLuggageAmount').addClass('hidden');
        }
        if (etoFn.form.settings.view.amountsViewCarryOn === false) {
            bookingContainer.find('.eto-section-handLuggageAmount').addClass('hidden');
        }

        if (etoFn.form.settings.booking.custom_field_display  === false) {
            bookingContainer.find('.eto-section-customField').addClass('hidden');
            $('.eto-modal-form-settings').find('.eto-settings-custom_field.name').closest('.form-group').addClass('hidden');
        }

        if (ETO.settings('eto_booking.form.view.advance_open', false)) {
            bookingContainer.find('.eto-section-advance').removeClass('hidden');
        } else {
            bookingContainer.find('.eto-section-advance').addClass('hidden');
        }

        $('.eto-section-customField').find('.eto-summary-label').html(ETO.settings('eto_booking.custom_field.name', ETO.trans('booking.customPlaceholder')).toString());

        if (etoFn.config.bookingId[formId] !== false) {
            bookingContainer.find('.eto-position-8').append('<button class="btn btn-default btn-sm eto-btn-get-transactions" for="'+formId+'" type="button">'+ETO.trans('booking.showTransaction')+'</button>');
            bookingContainer.closest('.eto-modal-booking-edit').find('.modal-title').html('Edit #' + etoFn.config.refNumber[formId]);
            etoFn.serviceDuration(bookingContainer.find('input.eto-js-service:checked'), true);

            bookingContainer.find('.eto-route-switch').addClass('hidden');
        }
        else {
            bookingContainer.find('.eto-route-switch').removeClass('hidden');
        }

        ETO.Form.hideFields(bookingContainer);

        etoFn.viewDeposit(bookingContainer.find('input.eto-js-payment_method:checked'));
        etoFn.setPriceSummary(formId);

        bookingContainer.find(".eto-position-5-1").append(bookingContainer.find(".eto-quote"));

        if (etoFn.form.settings.booking.add_vehicle_button === false) {
            bookingContainer.find('.eto-section-vehicle').find('.eto-add-item, .eto-field-vehicle_amount').addClass('hidden');
        }
        // $(".eto-quote").appendTo(".eto-position-5-1");

        bookingContainer[0].scrollTop = 0;
    };

    etoFn.createSection = function(data, section, formId) {
        var bookingSectionConfig = $.extend(true, {}, etoFn.config.defaults.section),
            sectionconfig = $.extend(true, {}, ETO.Form.config.defaults.section),
            formContainer = $('#' + formId);

        bookingSectionConfig = $.extend(true, sectionconfig, bookingSectionConfig);
        data = $.extend(true, bookingSectionConfig, data);

        if (((section == 'service' || section == 'serviceDuration') && ETO.settings('service', {}).length > 1) || section != 'service') {
            var sectionClass  = data.sectionClass !== false ? ' ' + data.sectionClass : '',
                template = '<div class="eto-section eto-section-'+section+' clearfix' + sectionClass + '" data-eto-section="'+section+'" data-eto-order="'+data.groupOrder+'"><div class="eto-route eto-route-1 clearfix" data-eto-route-id="1"><div class="eto-groups clearfix"></div></div></div>',
                sectionParentContainer = data.sectionParent !== false ? $('#'+formId + ' ' + data.sectionParent.classParent) : false;

            if (sectionParentContainer !== false && sectionParentContainer.length > 0) {
                if (typeof data.sectionParent.title != 'undefined' && data.sectionParent.title.length > 0 && sectionParentContainer.find('.eto-section-title').length === 0) {
                    var toogle = '',
                        pointer = '',
                        icon = 'fa-sort-asc';

                    if (data.sectionParent.toogle) {
                        if (etoFn.form.settings.view.advance_open === true) {
                            icon = 'fa-sort-desc';
                        }
                        toogle = '<i class="fa '+icon+'"></i>';
                        pointer = ' eto-pointer';
                    }

                    var title = '<div class="eto-section-title'+pointer+' '+data.sectionParent.class+'">'+data.sectionParent.title+' '+toogle+'</div>';

                    formContainer.find(data.sectionParent.classParent).append(title);
                }
                formContainer.find(data.sectionParent.classParent).append(template);
            }
            else {
                formContainer.append(template);
            }

            etoFn.createRoute(section, data, 1, formId);
        }
    };

    etoFn.createRoute = function(section, data, routeId, formId) {
        var formContainer = $('#'+ formId);
        if (data.sectionGlobalHeader === true) {
            var routeHeaderIcon = routeId === 1 ? 'A' : 'B',
                routeHeaderlabel = routeId === 1 ? 'one_way' : 'return';

            formContainer.find('.eto-section-'+section).find('.eto-route-'+routeId).prepend('<div class="eto-section-header clearfix hidden">' +
                '<span class="eto-section-header-icon">'+routeHeaderIcon+'</span>' +
                '<span class="eto-section-header-label">' + ETO.trans('booking.'+routeHeaderlabel) + '</span>' +
                '</div>');
        }

        if (typeof data.customHtml != 'undefined' && routeId === 1) {
            if (typeof data.customHtml.before == 'string') {
                formContainer.find('.eto-section-' + section).prepend(data.customHtml.before);
            }
            if (typeof data.customHtml.after == 'string') {
                formContainer.find('.eto-section-' + section).append(data.customHtml.after);
            }
        }
        if (data.sectionHide === true) {
            formContainer.find('.eto-section-'+section).addClass('hidden');
        }

        var container = formContainer.find('.eto-section-'+section+' .eto-route-'+routeId+' .eto-groups'),
            length = typeof ETO.Form.config.form.values[formId].booking[routeId] != 'undefined' ? Object.keys(ETO.Form.config.form.values[formId].booking[routeId][section]).length : etoFn.config.values[formId][1][section].length;

        for (var i = 0; i < length; i++) {
            var fields = ETO.Form.createGroup(section, container, data),
                index = fields.closest('.eto-group').find('.eto-summary-link').attr('data-eto-index');

            ETO.Form.prepareSammaryValue(fields.closest('.eto-group'), section, index, formId);
        }

        etoFn.setButtonsToSection(data.buttons, section, routeId, formId);
        if (etoFn.config.isReturn === false || typeof etoFn.config.oldData[formId] !='undefined') {
            formContainer.find('.eto-section-btn-override').addClass('hidden');
        }
        else {
            formContainer.find('.eto-section-btn-override').removeClass('hidden');
        }
        ETO.Form.enableDisableButton(formContainer.find('.eto-section-'+section+' .eto-route-'+routeId));
    };

    etoFn.setButtonsToSection = function(buttons, elementName, route, formId) {
        var topLeftButtonsHtml = typeof buttons.topLeft != 'undefined'
            ? ETO.Form.createButtons(buttons.topLeft, 'section') : '',
            bottomLeftButtonsHtml = typeof buttons.bottomLeft != 'undefined'
                ? ETO.Form.createButtons(buttons.bottomLeft, 'section') : '',
            topRightButtonsHtml = typeof buttons.topRight != 'undefined'
                ? ETO.Form.createButtons(buttons.topRight, 'section') : '',
            bottomRightButtonsHtml = typeof buttons.bottomRight != 'undefined'
                ? ETO.Form.createButtons(buttons.bottomRight, 'section') : '',
            formContainer = $('#'+ formId),
            groups = formContainer.find('.eto-section-' + elementName + ' .eto-route-' + route).find('.eto-groups');

        if (topLeftButtonsHtml != '') {
            groups.before('<div class="eto-section-buttons eto-section-buttons-top-left">'+topLeftButtonsHtml+'</div>');
        }
        if (bottomLeftButtonsHtml != '') {
            groups.before('<div class="eto-section-buttons eto-section-buttons-top-right">'+bottomLeftButtonsHtml+'</div>');
        }
        if (bottomRightButtonsHtml != '') {
            groups.after('<div class="eto-section-buttons eto-section-buttons-bottom-right">'+bottomRightButtonsHtml+'</div>');
        }
        if (topRightButtonsHtml != '') {
            groups.after('<div class="eto-section-buttons eto-section-buttons-bottom-left">'+topRightButtonsHtml+'</div>');
        }
        ETO.updateTooltipPopover();
    };

    etoFn.createPriceSummaryHtml = function(booking, totalView, totalPriceForm) {
        var html = '',
            headerOutbound = '<div class="eto-price-summary-popup-header">Outbound</div>',
            headerReturn = '<div class="eto-price-summary-popup-header">Return</div>',
            journyPrice = typeof booking[1].journeyPrice != 'undefined' && typeof booking[1].journeyPrice[0] != 'undefined' ? booking[1].journeyPrice[0].price : 0,
            journyPriceReturn = typeof booking[2] != 'undefined' ? (typeof booking[2].journeyPrice != 'undefined'  && typeof booking[2].journeyPrice[0] != 'undefined' ?  booking[2].journeyPrice[0].price : 0) : 0,
            itemsOutbound = booking[1].item,
            quoteOutbound = typeof booking[1].quote != 'undefined' ? booking[1].quote : {},
            discountAccountMessageOutbound = typeof quoteOutbound.discountAccountMessage != 'undefined' && quoteOutbound.discountAccountMessage.length > 0 ? '<div>' + quoteOutbound.discountAccountMessage + '</div>' : '',
            itemsReturn = typeof booking[2] != 'undefined' ? booking[2].item : false,
            quoteReturn = typeof booking[2] != 'undefined' ? (typeof booking[2].quote != 'undefined' ? booking[2].quote : {}) : {},
            discountAccountMessageReturn  = typeof quoteReturn.discountAccountMessage != 'undefined' && quoteReturn.discountAccountMessage.length > 0 ? '<div>' + quoteReturn.discountAccountMessage + '</div>' : '';

        if (itemsReturn !== false) { html += headerOutbound; }

        html += '<div class="eto-price-summary-popup-item">Journey price <span>' + ETO.formatPrice( journyPrice ) + '</span></div>';
        html += etoFn.setItems(itemsOutbound, totalView);

        if (typeof booking[1].discount != 'undefined' && typeof booking[1].discount[0] != 'undefined' && parseInt(booking[1].discount[0].price) > 0 ) {
            html +='<div class="eto-price-summary-popup-item">Discount <span>' + ETO.formatPrice(booking[1].discount[0].price) + '</span></div>';
        }

        if (itemsReturn === false) {
            if (discountAccountMessageOutbound) {
                html += '<div class="eto-price-summary-popup-discount-message">' + discountAccountMessageOutbound + '</div>';
            }
        }
        else {
            html +='<div class="eto-price-summary-popup-subtotal">Subtotal <span>' + ETO.formatPrice(etoFn.getSubtotalPriceFromObject(1, booking, totalView, totalPriceForm)) + '</span></div>';
            html += discountAccountMessageOutbound;
            html +='<br>';

            html += headerReturn;
            html += '<div class="eto-price-summary-popup-item">Journey price <span>' + ETO.formatPrice( journyPriceReturn ) + '</span></div>';
            html += etoFn.setItems(itemsReturn, totalView);

            if (typeof booking[2].discount != 'undefined' && typeof booking[2].discount[0] != 'undefined' && parseInt(booking[2].discount[0].price) > 0 ) {
                html +='<div class="eto-price-summary-popup-item">Discount <span>' + ETO.formatPrice(booking[2].discount[0].price) + '</span></div>';
            }

            html +='<div class="eto-price-summary-popup-subtotal">Subtotal <span>' + ETO.formatPrice(etoFn.getSubtotalPriceFromObject(2, booking, totalView, totalPriceForm)) + '</span></div>';
            html += discountAccountMessageReturn;
            // html +='<br>';
        }

        return html;
    };

    etoFn.setItems = function(v, s) {
        var h = '';
        for (var i in v) {
            if (v[i].added === 1 || s === true) {
                var name = typeof v[i].override_name != 'undefined' && v[i].override_name != '' ? v[i].override_name : v[i].item_type;
                h += '<div class="eto-price-summary-popup-item">' + name + ' <span>' + ETO.formatPrice(parseFloat(v[i].price) * (parseFloat(v[i].amount) > 0 ? parseFloat(v[i].amount) : 1));

                if (parseFloat(v[i].amount) > 1) {
                    h += ' (' + ETO.formatPrice(parseFloat(v[i].price)) + ' x' + parseFloat(v[i].amount) + ')';
                }
                h += '</span></div>';
            }
        }
        return h;
    };

    etoFn.getTotalPriceFromObject = function(bookingObject, totalView, totalPriceForm, getDetails) {
        var totalPrice = 0,
            totalObject = {};

        for (var i in bookingObject) {
            var subtotal = etoFn.getSubtotalPriceFromObject(i, bookingObject, totalView, totalPriceForm, getDetails);

            if (ETO.Form.isNumber(totalObject[i]) !== true) {
                totalObject = $.extend(true, totalObject, subtotal );
            }
            totalPrice = totalPrice + (typeof parseFloat(subtotal) == 'number' ? parseFloat(subtotal) : (typeof subtotal[i] != 'undefined' && typeof subtotal[i].quote != 'undefined' && typeof subtotal[i].quote.subtotalWithDiscount != 'undefined' ? parseFloat(subtotal[i].quote.subtotalWithDiscount) : 0));
        }

        if ( getDetails === true ) {
            return totalObject;
        }
        return (totalPrice > 0 ? totalPrice : 0);
    };

    etoFn.getSubtotalPriceFromObject = function(i, bookingObject, totalView, totalPriceForm, getDetails) {
        if (typeof bookingObject[i] == 'undefined' ) { return false; }
        var journey = {price: 0, name: ETO.trans('booking.price'), overrideName: ''},
            items = typeof bookingObject[i].item == 'undefined' && i === 2 ? bookingObject[1].item : bookingObject[i].item,
            details = {},
            driver_income = {
                child_seats: 0,
                additional_items: 0,
                parking_charges: 0,
                payment_charges: 0,
                meet_and_greet: 0,
                discounts: 0,
            };

        details[i] = {charges: {}, quote: {subtotal: 0, discount: 0, subtotalWithDiscount: 0 }, driver_income: []};

        if (typeof bookingObject[i].journeyPrice == 'undefined' && i === 2 && typeof  bookingObject[1].journeyPrice != 'undefined' && typeof bookingObject[1].journeyPrice[0] != 'undefined') {
            journey.price = bookingObject[1].journeyPrice[0].price;
            journey.overrideName = bookingObject[1].journeyPrice[0].override_name;
        }
        else if (typeof bookingObject[i].journeyPrice != 'undefined' && typeof bookingObject[i].journeyPrice[0] != 'undefined') {
            journey.price = bookingObject[i].journeyPrice[0].price;
            journey.overrideName = bookingObject[i].journeyPrice[0].override_name;
        }

        if (typeof bookingObject[i].discount == 'undefined' && i === 2 && typeof  bookingObject[1].discount != 'undefined' && typeof  bookingObject[1].discount[0] != 'undefined' ) {
            details[i].quote.discount = bookingObject[1].discount[0].price;
        }
        else if (typeof bookingObject[i].discount != 'undefined' && typeof bookingObject[i].discount[0] != 'undefined') {
            details[i].quote.discount = bookingObject[i].discount[0].price
        }

        journey.price = typeof parseFloat(journey.price) == 'number' ? journey.price : 0;
        details[i].quote.subtotal = parseFloat(journey.price);

        details[i]['charges'][0] = {
            item: 0,
            item_key: 'journey',
            item_trans_key: '',
            item_type: journey.name,
            override_name: journey.overrideName,
            price: (+journey.price),
            amount: 1,
            total: (+journey.price),
        };
        details[i]['driver_income'][0] = driver_income;

        for (var j in items) {
            if (items[j].added === 1 || totalPriceForm === true) {
                var total = typeof items[j].price != 'undefined'
                    ? (parseFloat(items[j].price) * (typeof items[j].amount != 'undefined' && parseFloat(items[j].amount) > 0 ? parseFloat(items[j].amount) : 1))
                    : 0;
                if (typeof items[j].item_key != 'undefined') {
                    if (items[j].item_key.includes("other")) {
                        details[i]['driver_income'][0].additional_items = parseFloat(details[i]['driver_income'][0].additional_items) + total;
                    } else if (items[j].item_key == "parking") {
                        details[i]['driver_income'][0].parking_charges = parseFloat(details[i]['driver_income'][0].parking_charges) + total;
                    } else if (items[j].item_key == "meet_and_greet") {
                        details[i]['driver_income'][0].meet_and_greet = parseFloat(details[i]['driver_income'][0].meet_and_greet) + total;
                    } else if ([null,'infant_seat', 'child_seat', 'baby_seat'].indexOf(items[j].item_key) !== -1) {
                        details[i]['driver_income'][0].child_seats = parseFloat(details[i]['driver_income'][0].child_seats) + total;
                    }
                }

                details[i].quote.subtotal = parseFloat(details[i].quote.subtotal) + total;
                details[i]['charges'][(+j)+1] = items[j];
                details[i]['charges'][(+j)+1]['total'] = total;
                if (totalView === true) {
                    delete items[j].added;
                }
            }
        }

        details[i].quote.subtotalWithDiscount
            = parseFloat(details[i].quote.subtotal) - parseFloat(details[i].quote.discount);
        details[i]['driver_income'][0].discounts
            = parseFloat(details[i]['driver_income'][0].discounts) + parseFloat(details[i].quote.discount);

        if ( getDetails === true ) {
            return details;
        }

        return details[i].quote.subtotalWithDiscount;
    };

    etoFn.setPriceSummary = function(formId) {
        var data = typeof ETO.Form.config.form.values[formId] != 'undefined' ? $.extend(true, {}, ETO.Form.config.form.values[formId].booking) : false,
            total = data !== false ? etoFn.getTotalPriceFromObject(data, true, true) : 0,
            formContainer = $('#'+ formId);
        if (data === false) { return false; }
        var priceSummary = '<div class="eto-tooltip-price-summary">' + etoFn.createPriceSummaryHtml(data, true, true) + '</div>';

        formContainer.find('.eto-price-summary-total .eto-js-total').html(ETO.formatPrice(total));

        ETO.removeSetPopoverTooltip(formContainer.find('.eto-price-summary-total'), {tooltip: priceSummary.replace(/[\"]/g, '\''), title: ''});
        if (total > 0) {
            formContainer.find('.eto-price-summary-info').removeClass('hidden');
        }
        else {
            formContainer.find('.eto-price-summary-info').addClass('hidden');
        }
    };

    etoFn.updatePriceSubsummary = function(formId, section, container, action) {
        var routeId = container.attr('data-eto-route-id'),
            groups = container.find('.eto-group'),
            dataObject = $.extend(true, {}, ETO.Form.config.form.values[formId].booking);

        if (typeof dataObject[routeId][section] != 'undefined') {
            var sectionObject = dataObject[routeId][section];

            for (var index in sectionObject) {
                if (groups.find('.eto-summary-link[data-eto-index="' + index + '"]').length > 0) {
                    var summaryLink = groups.find('.eto-summary-link[data-eto-index="' + index + '"]'),
                        viewSummary = false,
                        i = 0;

                    for (var name in sectionObject[index]) {
                        var val = sectionObject[index][name];
                        if (val != '') {
                            if ((ETO.Form.isNumber(val) === true && parseFloat(val) > 0) || ETO.Form.isNumber(val) === false) {
                                i++;
                            }
                        }
                    }
                    if (i > 0) { viewSummary = true; }

                    ETO.Form.prepareSammaryValue(summaryLink.closest('.eto-group'), section, index, formId, viewSummary);
                }
                else if (groups.find('.eto-summary-link[data-eto-index="' + index + '"]').length === 0) {
                    if(typeof action == 'undefined' || ( typeof action != 'undefined' && action.toString().localeCompare('remove') !== 0 ) ) {
                        etoFn.addNewElementToSection(container.find('.eto-groups'), index);
                    }
                }
            }
        }
    };

    etoFn.updateObjectAndFormSummaries = function(data, formContainer) {
        var formId = formContainer.attr('id'),
            sections = formContainer.find('.eto-section');
        delete ETO.Form.config.form.values[formId].booking;
        ETO.Form.config.form.values[formId].booking = $.extend(true, {}, data.booking);
        etoFn.setPriceSummary(formId);

        sections.each(function() {
            var section = $(this).attr('data-eto-section'),
                routes = $(this).find('.eto-route'),
                inputs = $(this).find('.eto-js-inputs:not(.tt-input)');

            inputs.each(function() {
                var name = $(this).data('etoName'),
                    routeId = $(this).closest('.eto-route').data('etoRouteId'),
                    index = $(this).closest('.eto-group').find('.eto-summary-link').data('etoIndex'),
                    value = ETO.Form.config.form.values[formId].booking[routeId][section][index][name],
                    type = this.type;

                if(type.localeCompare('radio') === 0) {
                    $(this).parent().find(':checked').attr('checked', false);
                    $(this).parent().find('[value="'+value+'"]').attr('checked', true).change();
                }
                else {
                    $(this).val(value);
                }
            })
            .ready(function() {
                routes.each(function() {
                    etoFn.updatePriceSubsummary(formId, section, $(this));
                });
            });
        });
    };

    etoFn.getSectionObjectValues = function(formId, group) {
        var section = group.find('.eto-summary-link').attr('data-eto-section'),
            routeId = group.closest('.eto-route').attr('data-eto-route-id');

        if (ETO.hasProperty(ETO.Form.config.form.values, [formId, 'booking', routeId,section])) {
            return $.extend(true, {}, ETO.Form.config.form.values[formId].booking[routeId][section]);
        }
        return false;
    };

    etoFn.getElementValue = function(formId, group, index, name) {
        var values = etoFn.getSectionObjectValues(formId, group);

        if (ETO.hasProperty(values, [index, name])) {
            return values[index][name];
        }
        return false;
    };

    etoFn.setElementValue = function(formId, container, index, name, value) {
        var section = container.find('.eto-summary-link').attr('data-eto-section'),
            routeId = container.closest('.eto-route').attr('data-eto-route-id'),
            config = etoFn.form.sections[section].fields[name],
            confDefault = $.extend(true, {}, etoFn.config.defaults.display);

        config = $.extend(true, confDefault, config);
        ETO.Form.createValuesToObject(formId, routeId, section, index);
        var sectionObject = ETO.Form.config.form.values[formId].booking[routeId][section];

        sectionObject[index][name] = value;

        if (value == '' && config.valueConverterType == 'id') {
            container.find('.eto-js-'+ name +'_text_selected').val('').change();
            if (container.find('.eto-js-vehicle_amount').length > 0) {
                container.find('.eto-js-vehicle_amount').val('').change();
            }
        }
        else if (value != null && value.length > 0) {
            if ((section == 'item' && name == 'item') || section == 'journeyPrice' || section == 'discount') {
                etoFn.setPriceSummary(formId);
            }
        }
    };

    etoFn.checkAndRemove = function(group) {
        var sectionData = group.closest('.eto-section').data(),
            summaryLinkData = group.find('.eto-summary-link').data(),
            formId = group.closest('.eto-form').attr('id'),
            index = summaryLinkData.etoIndex,
            section = sectionData.etoSection,
            fieldValue = false;

        if (section == 'item') {
            fieldValue = etoFn.getElementValue(formId, group, index, 'item');
        }
        else if (section == 'passenger') {
            fieldValue = etoFn.getElementValue(formId, group, index, 'name');
        }

        if (fieldValue === false || fieldValue === ''){
            etoFn.destroyFieldObjectValues(formId, group, index, 'remove');
        }
    };

    etoFn.destroyFieldObjectValues = function(formId, container, index, action) {
        var sectionData = container.closest('.eto-section').data(),
            route = container.closest('.eto-route'),
            routeData = route.data(),
            section = sectionData.etoSection,
            routeId = routeData.etoRouteId,
            fieldset = container.find('.eto-fieldset:not(.hidden)'),
            groups = container.closest('.eto-groups');

        if (((section.localeCompare('item') === 0
                    && (fieldset.find('.eto-js-item').find('option:selected').length === 0 || fieldset.find('.eto-js-item').find('option:selected').val().localeCompare('') === 0)
                )
                || ['item', 'passenger'].indexOf(section) === -1)
            && action.localeCompare('clear') !== 0
        ) {
            if (ETO.hasProperty(ETO.Form.config.form.values, [formId, 'booking', routeId, section, index])) {
                index = parseInt(index);
                delete ETO.Form.config.form.values[formId].booking[routeId][section][index];
                etoFn.updatePriceSubsummary(formId, section, route, action);

                if (action.localeCompare('remove') === 0) {
                    etoFn.removeBox(action, section, container);
                }

                ETO.Form.reorderObjectValues(groups, true);
                return true;
            }
            else if (action.localeCompare('remove') === 0) {
                etoFn.removeBox(action, section, container);
            }
        }
        else if (action.localeCompare('remove') === 0) {
            etoFn.removeBox(action, section, container);
        }

        ETO.Form.hideFields(container.closest('.eto-form'));
        return action.localeCompare('remove') === 0;
    };

    etoFn.removeBox = function(action, section, container) {
        var groups = container.closest('.eto-groups');

        if (section == 'passenger') {
            container.closest('.eto-route').find('.eto-section-btn-add').removeClass('hidden');
            if (action == 'remove') {
                var formId = groups.closest('.eto-form').attr('id'),
                    index = container.find('.eto-summary-link').attr('data-eto-index'),
                    routeId = container.closest('.eto-route').attr('data-eto-route-id');

                delete ETO.Form.config.form.values[formId].booking[routeId][section][index];
            }
        }
        if ((section == 'passenger' && groups.children().length > 1) || section != 'passenger') {
            container.remove();
        }
        if (section == 'passenger') {
            ETO.Form.enableDisableButton(groups);
        }
    };

    etoFn.destroySectionObjectValues = function(formId, container) {
        var section = container.find('.eto-summary-link').attr('data-eto-section'),
            routeId = container.closest('.eto-route').attr('data-eto-route-id');

        if (ETO.hasProperty(ETO.Form.config.form.values, [formId,'booking',routeId,section])) {
            delete ETO.Form.config.form.values[formId].booking[routeId][section];
            return true;
        }
        return false;
    };

    etoFn.isValidateDate = function(section, container) {
        var routeId = container.closest('.eto-route').attr('data-eto-route-id');

        return ((section == 'when' && parseInt(routeId) === 2) || section != 'when');
    };

    etoFn.validateFormObject = function(formContainer, type) {
        var formId = formContainer.attr('id'),
            formConfiguration = etoFn.form.sections,
            formObject = typeof ETO.Form.config.form.values[formId] != 'undefined' ? ETO.Form.config.form.values[formId].booking : false,
            minName = type.localeCompare('quote') === 0 ? 'minQuote' : 'minSave';
        formContainer.find('.eto-group-feedback-error').removeClass('eto-group-feedback-error');
        formContainer.find('.eto-error-route-1,  .eto-error-route-2').addClass('hidden');

        if (formObject === false) {return false;}

        for (var section in formConfiguration) {
            for (var routeId in formObject) {
                formContainer.find('.eto-section-'+section). find('.eto-route-'+routeId).find('.eto-section-feedback').remove();

                // check if the number of sections is required
                var lengthSection = typeof formObject[routeId][section] != 'undefined' ? Object.keys(formObject[routeId][section]).length : 0,
                    lengthMin = typeof etoFn.config.limits[minName][section] != 'undefined' ? etoFn.config.limits[minName][section] : 0,
                    sectionRouteBox = formContainer.find('.eto-section-'+section).find('.eto-route-'+routeId).find('.eto-group');

                for (var group in sectionRouteBox) {
                    if (ETO.Form.isInteger(group)) {
                        var groupBox = sectionRouteBox[group],
                            summaryLink = $(groupBox).find('.eto-summary-link'),
                            index = summaryLink.attr('data-eto-index'),
                            error = 0;

                        if (lengthSection < lengthMin && etoFn.isValidateDate(section, sectionRouteBox) === true && typeof formObject[routeId][section][index] == 'undefined') {
                            error++;
                        }
                        else {
                            for (var name in formConfiguration[section].fields) {
                                var value = typeof formObject[routeId][section][index] != 'undefined' && typeof formObject[routeId][section][index][name] != 'undefined'
                                    ? formObject[routeId][section][index][name]
                                    : false,
                                    validationConf = formConfiguration[section].fields[name].validate;

                                if (typeof validationConf != 'undefined') {
                                    if (lengthSection < lengthMin && etoFn.isValidateDate(section, sectionRouteBox) === true) {
                                        error++;
                                    }
                                    else if (lengthMin > 0 && lengthSection === lengthMin && (value === false || value == '') && validationConf.isRequired === true && (validationConf.isDate === false || etoFn.isValidateDate(section, sectionRouteBox) === true)) {
                                        error++;
                                    }
                                    else if (typeof value !== false && value != '' && validationConf.isEmail === true && ETO.Form.validateEmail(value) === false) {
                                        error++;
                                    }
                                    else if (validationConf.isPhone === true && value.length > 0 ) {
                                        var field = document.createElement('INPUT');
                                        field.setAttribute('value', value);
                                        field = $(field).wrap('<div></div>');

                                        field.intlTelInput({
                                            utilsScript: ETO.config.appPath +'/assets/plugins/jquery-intl-tel-input/js/utils.js?1515077554',
                                            preferredCountries: ['gb'],
                                            initialCountry: 'auto',
                                            geoIpLookup: function(callback) {
                                                $.get('https://ipinfo.io', function() {}, 'jsonp').always(function(resp) {
                                                    var countryCode = (resp && resp.country) ? resp.country : "";
                                                    callback(countryCode);
                                                });
                                            },
                                        });

                                        if (field.intlTelInput('isValidNumber') === false) {
                                            error++;
                                        }
                                    }
                                }
                            }
                        }

                        if(error > 0) {
                            summaryLink.closest('.eto-group:not(.hidden)').addClass('eto-group-feedback-error');
                        }
                    }
                }
            }
        }

        if (formContainer.find('.eto-route-1').find('.eto-field-feedback-error, .eto-group-feedback-error').length > 0) {
            formContainer.find('.eto-error-route-1').removeClass('hidden');
        }
        if (formContainer.find('.eto-route-2').find('.eto-field-feedback-error, .eto-group-feedback-error').length > 0) {
            formContainer.find('.eto-error-route-2').removeClass('hidden');
        }

        if (formContainer.find('.eto-group-feedback-error').length > 0) {
            ETO.swalWithBootstrapButtons({
                type: 'error',
                title: ETO.trans('booking.errorBookingFeedbackMin'),
            });
            return false;
        }
        return true;
    };

    etoFn.reorderSectionObjectValues = function(formId, container, orderedContainer, oldValues, key) {
        var section = container.find('.eto-summary-link').attr('data-eto-section'),
            routeId = container.closest('.eto-route').attr('data-eto-route-id'),
            summaryLink = orderedContainer.find('.eto-summary-link'),
            index = summaryLink.attr('data-eto-index');

        if (typeof oldValues[index] !='undefined') {
            ETO.Form.createValuesToObject(formId, routeId, section, key);
            ETO.Form.config.form.values[formId].booking[routeId][section][key] = oldValues[index];
            summaryLink.attr('data-eto-index', key);
            if (['item', 'journeyPrice'].indexOf(section) !== -1) {
                etoFn.setPriceSummary(formId);
            }
        }
    };

    etoFn.setGeolocation = function(el) {
        var input = el.closest('.eto-field').find('input').last();

        // If the browser supports the Geolocation API
        if (typeof navigator.geolocation == 'undefined') {
            swal({
                type: 'error',
                text: ETO.trans('booking.GEOLOCATION_UNDEFINED'),
            });
            return;
        }

        el.closest('.eto-form').LoadingOverlay('show');
        el.addClass('fa-spin');
        navigator.geolocation.getCurrentPosition(function(position) {
            var geocoder = new google.maps.Geocoder();
            geocoder.geocode({
                'location': new google.maps.LatLng(position.coords.latitude, position.coords.longitude)
            },
            function(results, status) {
                el.removeClass('fa-spin');
                el.closest('.eto-form').LoadingOverlay('hide');
                if (status.localeCompare(google.maps.GeocoderStatus.OK) === 0) {
                    input.typeahead('val', results[0].formatted_address).focusTextToEnd();
                    input.change();
                    input.closest('.eto-fields').find('.eto-js-place_id').val(results[0].place_id).change();
                }
                else {
                    swal({
                        type: 'error',
                        text: ETO.trans('booking.GEOLOCATION_UNABLE'),
                    });
                }
            });
        },
        function(positionError) {
            el.removeClass('fa-spin');
            el.closest('.eto-form').LoadingOverlay('hide');
            swal({
                type: 'error',
                title: ETO.trans('booking.GEOLOCATION_ERROR'),
                html: positionError.message,
            });
        }, {
            enableHighAccuracy: true,
            timeout: 3 * 1000 // seconds
        });
    };

    etoFn.setVisibleOnNoVisibility = function(section, container) {
        var sourceConfig = etoFn.form.sections[section].fields.item.callback.after.setSelect2FromConfig.source.data;

        for (var i in sourceConfig) {
            var is_override = sourceConfig[i].params.is_override = 1 ? 'removeClass' : 'addClass',
                is_price = sourceConfig[i].params.is_price = 1 ? 'removeClass' : 'addClass';

            container.find('.eto-field:not(.eto-field-advance)').find('.eto-js-override_name').val('').closest('.eto-field')[is_override]('hidden');
            container.find('.eto-field:not(.eto-field-advance)').find('.eto-js-price').closest('.eto-field')[is_price]('hidden'); // .val('0.00')
        }
    };

    etoFn.getUserData = function(container, section, itemConf, setValues, userType) {
        var user = ETO.config[userType];

        if (typeof setValues[userType] != 'undefined' && setValues[userType] !== null && setValues[userType].toString().localeCompare('') !== 0 && parseInt(setValues[userType]) !== 0) {
            if (typeof user != 'undefined') {
                if (parseInt(user.id) !== parseInt(setValues[userType])) {
                    user = ETO.Booking.getUser(setValues[userType], userType);
                }

                setValues.id = setValues[userType];
                delete setValues[userType];
            }
            else {
                user = ETO.Booking.getUser(setValues[userType], userType);
            }
        }

        if (typeof user == 'undefined') {
            return setValues;
        }


        setValues.userAvatar = null !== user.avatar_path && user.avatar_path.localeCompare('') !== 0 ? '<img alt="" src="' + user.avatar_path + '" />' : '';
        setValues.userName = user.displayName;
        setValues.userEmail = user.email.localeCompare('') !== 0 ? user.email : '';
        setValues.userPhone = null !== user.profile.mobile_no && user.profile.mobile_no.localeCompare('') !== 0 ? user.profile.mobile_no : '';

        if ($.trim(setValues.userName).length === 0) {
            setValues.userName = user.name
        }

        return setValues;
    };

    etoFn.getFleetData = function(container, section, itemConf, setValues) {
        setValues = etoFn.getUserData(container, section, itemConf, setValues, 'fleet');
        return ETO.Form.parseValuesToView(container, section, itemConf, setValues, true);
    };

    etoFn.getDriverData = function(container, section, itemConf, setValues) {
        setValues = etoFn.getUserData(container, section, itemConf, setValues, 'driver');
        return ETO.Form.parseValuesToView(container, section, itemConf, setValues, true);
    };

    etoFn.getCustomerData = function(container, section, itemConf, setValues){
        setValues = etoFn.getUserData(container, section, itemConf, setValues, 'customer');
        return ETO.Form.parseValuesToView(container, section, itemConf, setValues, true);
    };

    etoFn.getPassengerDataBySelect = function(container, user){
        var passengerContainer = container.closest('.eto-group'),
            index = passengerContainer.find('.eto-summary-link').attr('data-eto-index'),
            formId = container.closest('.eto-form').attr('id'),
            summaryLink = passengerContainer.find('.eto-summary-link'),
            summaryValue = passengerContainer.find('.eto-summary-value');

        if (typeof user.passenger_name != 'undefined' && user.passenger_name != '') {
            passengerContainer.find('.eto-fields .eto-js-inputs.eto-js-name').val(user.passenger_name).change();
        }
        if (typeof user.passenger_email != 'undefined' && user.passenger_email != '') {
            passengerContainer.find('.eto-fields .eto-js-inputs.eto-js-email').val(user.passenger_email).change();
        }
        if (typeof user.passenger_phone != 'undefined' && user.passenger_phone != '') {
            passengerContainer.find('.eto-fields .eto-js-inputs.eto-js-phone').val( user.passenger_phone).change();
        }

        var htmlObject = ETO.Form.generateSummaryWithTooltip(passengerContainer, 'passenger', index, formId);

        summaryValue.html(htmlObject.summary);
        ETO.Form.visibilitySummaryValue(passengerContainer, true);
        ETO.removeSetPopoverTooltip(summaryLink, htmlObject);
    };

    etoFn.getItemPrice = function(container, section, itemConf, setValues, name){
        if (typeof itemConf[name].priceField == 'undefined') { itemConf[name].priceField = name; }

        var priceValue = typeof setValues.amount != 'undefined' ? (+ setValues[itemConf[name].priceField]) * (+ setValues.amount) : (+ setValues[itemConf[name].priceField]),
            price = ETO.formatPrice( priceValue ),
            amount = typeof setValues.amount != 'undefined' ? (setValues.amount > 1 ? ' x'+setValues.amount : '') : '',
            mathAmount = priceValue > 0 && amount.localeCompare('') !== 0
                ? ' ('+ETO.formatPrice( setValues[itemConf[name].priceField] )+amount+')' : amount;

        setValues['formated_'+itemConf[name].priceField] = price+mathAmount;
        return ETO.Form.parseValuesToView(container, section, itemConf, setValues, true);
    };

    etoFn.getItemNameDisplayed = function(container, section, itemConf, setValues){
        setValues.type_dispaly_name = setValues.override_name.localeCompare('') !== 0 ? setValues.override_name : setValues.item_type;
        return ETO.Form.parseValuesToView(container, section, itemConf, setValues, true);
    };

    etoFn.setPassengerFromCustomerBtn = function(container) {
        var section = container.closest('.eto-section').attr('data-eto-section'),
            formId = container.closest('.eto-form').attr('id'),
            itemConf = etoFn.form.sections[section],
            setValues = etoFn.getUserData(container, section, itemConf, {}, 'customer'),
            routeId = container.closest('.eto-route').attr('data-eto-route-id'),
            passengerContainer = $('#'+formId).find('.eto-section-passenger .eto-route-'+routeId+' .eto-group:first'),
            index = passengerContainer.find('.eto-summary-link').attr('data-eto-index'),
            passengerResult = {
                name: setValues.userName,
                email: setValues.userEmail,
                phone: setValues.userPhone,
                comments: '',
            },
            summaryLink = passengerContainer.find('.eto-summary-link'),
            summaryValue = passengerContainer.find('.eto-summary-value');

        ETO.Form.createValuesToObject(formId, routeId, 'passenger', index);
        ETO.Form.config.form.values[formId].booking[routeId]['passenger'][index] = passengerResult;

        var htmlObject = ETO.Form.generateSummaryWithTooltip(passengerContainer, 'passenger', index, formId);

        summaryValue.html(htmlObject.summary);

        ETO.Form.visibilitySummaryValue(passengerContainer, true);
        ETO.removeSetPopoverTooltip(summaryLink, htmlObject);
        ETO.toast({
            type: 'success',
            title:ETO.trans('booking.createdPassenger'),
        });
    };

    etoFn.numbers1To30 = function(value) {
        var data = {data: []};
        for (var i = 0; i <= 30; i++) {
            data.data.push( {id: i, name: i} );
        }
        return data;
    };

    etoFn.setDepartments = function (el, customer) {
        var container = el.closest('.eto-group'),
            routeId = container.closest('.eto-route').attr('data-eto-route-id'),
            formId = container.closest('.eto-form').attr('id');

        $('.select2-2urn-container').attr('title', false);

        if (typeof customer.departments != 'undefined' && customer.departments !== null && customer.departments.length > 0) {
            var department = ETO.Form.config.form.values[formId].booking[routeId].customer[0].department;

            container.find('.eto-js-department').closest('.eto-field').addClass('hidden');
            department = department == null || parseInt(department) === 0 ? '' : department;
            ETO.Form.select2CustomerDepartments(container, customer, department);
            setTimeout(function() {
                container.find('.eto-js-department').closest('.eto-field').removeClass('hidden');
                el.closest('.eto-field').find('.eto-field-btn-advance').removeClass('hidden');
            }, 0);
        } else {
            ETO.Form.select2CustomerDepartments(container, customer);
            el.closest('.eto-field').find('.eto-field-btn-advance').addClass('hidden');
        }

        if (el.val().length === 0 ) {
            container.find('.eto-fieldset-btn-passenger').addClass('hidden');
            el.closest('.eto-field').find('.eto-field-btn-clear').addClass('hidden');
            ETO.Form.updateSummary(container, true)
        }
        else {
            container.find('.eto-fieldset-btn-passenger').removeClass('hidden');
            el.closest('.eto-field').find('.eto-field-btn-clear').removeClass('hidden');
        }
    };

    return etoFn;
}();
