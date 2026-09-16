

var _token = $('meta[name="csrf-token"]').attr('content');

function etoLang(id) {
    var showTranslation = 0;
    var translation = '';

    if (showTranslation) {
        translation += '*';
    }

    if (typeof ETOLangOverride !== 'undefined' && ETOLangOverride[id]) {
        translation += ETOLangOverride[id];
    } else if (typeof ETOLang !== 'undefined' && ETOLang[id]) {
        translation += ETOLang[id];
    } else {
        translation += id;
    }

    if (showTranslation) {
        translation += '*';
    }

    return translation;
}

function printPartOfPage(elementId) {
    var printHeader = '<html><head><title>' + etoLang('print_Heading') + '</title><style type="text/css">table td {padding:3px 3px;}</style></head><body>';
    var printFooter = '</body></html>';
    var printContent = document.getElementById(elementId).innerHTML;
    var windowUrl = 'about:blank';
    var windowName = 'Print' + new Date().getTime();
    var printWindow = window.open(windowUrl, windowName, 'left=50000,top=50000,width=0,height=0');

    printWindow.document.write(printHeader + printContent + printFooter);
    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
    printWindow.close();
}

function hoursToTime(h) {
    var distance = h * 3600 * 1000;
    var days = Math.floor(distance / (1000 * 60 * 60 * 24));
    var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    var seconds = Math.floor((distance % (1000 * 60)) / 1000);
    var text = '';

    if (days > 0) {
      text += (text ? ' ' : '') + days +' day(s)';
    }
    if (hours > 0) {
      text += (text ? ' ' : '') + hours +'';
      // text += (text ? ' ' : '') + hours +' hours';
    }
    // if (minutes > 0) {
    //   text += (text ? ' ' : '') + minutes +' minutes';
    // }
    // if (seconds > 0) {
    //   text += (text ? ' ' : '') + seconds +' seconds';
    // }

    // console.log(days, hours, minutes, seconds);
    return text;
}

var Base64 = {
    _keyStr: "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",
    encode: function(e) {
        var t = "";
        var n, r, i, s, o, u, a;
        var f = 0;
        e = Base64._utf8_encode(e);
        while (f < e.length) {
            n = e.charCodeAt(f++);
            r = e.charCodeAt(f++);
            i = e.charCodeAt(f++);
            s = n >> 2;
            o = (n & 3) << 4 | r >> 4;
            u = (r & 15) << 2 | i >> 6;
            a = i & 63;
            if (isNaN(r)) {
                u = a = 64
            } else if (isNaN(i)) {
                a = 64
            }
            t = t + this._keyStr.charAt(s) + this._keyStr.charAt(o) + this._keyStr.charAt(u) + this._keyStr.charAt(a)
        }
        return t
    },
    decode: function(e) {
        var t = "";
        var n, r, i;
        var s, o, u, a;
        var f = 0;
        e = e.replace(/[^A-Za-z0-9+/=]/g, "");
        while (f < e.length) {
            s = this._keyStr.indexOf(e.charAt(f++));
            o = this._keyStr.indexOf(e.charAt(f++));
            u = this._keyStr.indexOf(e.charAt(f++));
            a = this._keyStr.indexOf(e.charAt(f++));
            n = s << 2 | o >> 4;
            r = (o & 15) << 4 | u >> 2;
            i = (u & 3) << 6 | a;
            t = t + String.fromCharCode(n);
            if (u != 64) {
                t = t + String.fromCharCode(r)
            }
            if (a != 64) {
                t = t + String.fromCharCode(i)
            }
        }
        t = Base64._utf8_decode(t);
        return t
    },
    _utf8_encode: function(e) {
        e = e.replace(/rn/g, "n");
        var t = "";
        for (var n = 0; n < e.length; n++) {
            var r = e.charCodeAt(n);
            if (r < 128) {
                t += String.fromCharCode(r)
            } else if (r > 127 && r < 2048) {
                t += String.fromCharCode(r >> 6 | 192);
                t += String.fromCharCode(r & 63 | 128)
            } else {
                t += String.fromCharCode(r >> 12 | 224);
                t += String.fromCharCode(r >> 6 & 63 | 128);
                t += String.fromCharCode(r & 63 | 128)
            }
        }
        return t
    },
    _utf8_decode: function(e) {
        var t = "";
        var n = 0;
        var r = c1 = c2 = 0;
        while (n < e.length) {
            r = e.charCodeAt(n);
            if (r < 128) {
                t += String.fromCharCode(r);
                n++
            } else if (r > 191 && r < 224) {
                c2 = e.charCodeAt(n + 1);
                t += String.fromCharCode((r & 31) << 6 | c2 & 63);
                n += 2
            } else {
                c2 = e.charCodeAt(n + 1);
                c3 = e.charCodeAt(n + 2);
                t += String.fromCharCode((r & 15) << 12 | (c2 & 63) << 6 | c3 & 63);
                n += 3
            }
        }
        return t
    }
}

var $isSubmitReady = 1;

function etoApp(data) {
    moment.tz.setDefault(EasyTaxiOffice.timezone);

    var etoData = {
        apiURL: EasyTaxiOffice.appPath + '/etov2?apiType=frontend',
        isPageLoaded: 0,
        urlParams: {},
        customerURL: '',
        layout: 'complete',
        mainContainer: 'etoCompleteContainer',
        messageContainer: 'etoMessageContainer',
        appPath: EasyTaxiOffice.appPath,
        debug: 0,
        siteId: 0,
        bookingId: 0,
        userId: 0,
        userName: '',
        userHomeAddress: '',
        userDepartments: [],
        isCompany: 0,
        isAccountPayment: 0,
        message: [],
        errorMessage: [],
        clicked: 0,
        noVehicleMessage: 0,
        submitOK: 1,
        displayMessage: 0,
        displayHighlight: 0,
        singleVehicle: 1,
        enabledTypeahead: 1,
        dynamicQuote: 1,
        manualQuote: 0,
        quoteStatus: 0,
        currentStep: 1,
        maxStep: 1,
        waypointsCount: 0,
        waypointsMax: 3,
        serviceParams: {
          type: 'standard',
          availability: 0,
          hide_location: 0,
          duration: 0,
          duration_min: 1,
          duration_max: 10
        },
        request: {
            config: [],
            language: [],
            category: [],
            location: [],
            vehicle: [],
            payment: [],
            services: []
        },
        booking: etoReset()
    };

    etoInit(data);

    function etoScrollToTop(top) {
        var offset = (top ? top : 0);

        if ('parentIFrame' in window && etoData.request.config.booking_scroll_to_top_enable) {
            if (etoData.request.config.booking_scroll_to_top_offset) {
              offset += etoData.request.config.booking_scroll_to_top_offset;
            }
            window.parentIFrame.scrollToOffset(0, offset);
        }
        else {
            $('html, body').animate({scrollTop: offset}, 500);
        }
    }

    function getRoundedDate(minutes, date) {
        var ms = 1000 * 60 * minutes; // convert minutes to ms
        var date = date ? date : new Date();
        var rounded = new Date(Math.ceil(date.getTime() / ms) * ms);
        return rounded;
    }

    function etoReset() {

        return {
            scheduledRouteId: 0,
            serviceId: 0,
            serviceDuration: 0,
            preferred: {
                passengers: 0,
                luggage: 0,
                handLuggage: 0,
            },
            routeReturn: 1,
            route1: {
                category: {
                    start: null,
                    end: null,
                    type: {
                        start: null,
                        end: null
                    }
                },
                location: {
                    start: null,
                    end: null
                },
                waypoints: [],
                waypointsComplete: [],
                waypointsPlaceId: [],
                address: {
                    start: null,
                    end: null
                },
                addressComplete: {
                    start: null,
                    end: null
                },
                placeId: {
                    start: null,
                    end: null
                },
                coordinate: {
                    start: {
                        lat: null,
                        lon: null
                    },
                    end: {
                        lat: null,
                        lon: null
                    }
                },
                distance: 0,
                duration: 0,
                distance_base_start: 0,
                duration_base_start: 0,
                distance_base_end: 0,
                duration_base_end: 0,
                date: null,
                isAirport: 0,
                isAirport2: 0,
                excludedRouteAllowed: 0,
                mapOpened: 0,
                flightNumber: null,
                flightLandingTime: null,
                departureCity: null,
                departureFlightNumber: null,
                departureFlightTime: null,
                departureFlightCity: null,
                waitingTime: 0,
                meetAndGreet: null,
                meetingPoint: null,
                requirements: null,
                items: [],
                vehicle: [],
                vehicleLimits: {
                    passengers: 0,
                    luggage: 0,
                    handLuggage: 0,
                    childSeats: 0,
                    babySeats: 0,
                    infantSeats: 0,
                    wheelchair: 0
                },
                passengers: 1,
                luggage: 0,
                handLuggage: 0,
                childSeats: 0,
                babySeats: 0,
                infantSeats: 0,
                wheelchair: 0,
                extraChargesList: [],
                extraChargesPrice: 0,
                totalPrice: 0,
                totalPriceWithDiscount: 0,
                totalDiscount: 0,
                accountDiscount: 0,
                vehicleButtons: null
            },
            route2: {
                category: {
                    start: null,
                    end: null,
                    type: {
                        start: null,
                        end: null
                    }
                },
                location: {
                    start: null,
                    end: null
                },
                waypoints: [],
                waypointsComplete: [],
                waypointsPlaceId: [],
                address: {
                    start: null,
                    end: null
                },
                addressComplete: {
                    start: null,
                    end: null
                },
                placeId: {
                    start: null,
                    end: null
                },
                coordinate: {
                    start: {
                        lat: null,
                        lon: null
                    },
                    end: {
                        lat: null,
                        lon: null
                    }
                },
                distance: 0,
                duration: 0,
                distance_base_start: 0,
                duration_base_start: 0,
                distance_base_end: 0,
                duration_base_end: 0,
                date: null,
                isAirport: 0,
                isAirport2: 0,
                excludedRouteAllowed: 0,
                mapOpened: 0,
                flightNumber: null,
                flightLandingTime: null,
                departureCity: null,
                departureFlightNumber: null,
                departureFlightTime: null,
                departureFlightCity: null,
                waitingTime: 0,
                meetAndGreet: null,
                meetingPoint: null,
                requirements: null,
                items: [],
                vehicle: [],
                vehicleLimits: {
                    passengers: 0,
                    luggage: 0,
                    handLuggage: 0,
                    childSeats: 0,
                    babySeats: 0,
                    infantSeats: 0,
                    wheelchair: 0
                },
                passengers: 1,
                luggage: 0,
                handLuggage: 0,
                childSeats: 0,
                babySeats: 0,
                infantSeats: 0,
                wheelchair: 0,
                extraChargesList: [],
                extraChargesPrice: 0,
                totalPrice: 0,
                totalPriceWithDiscount: 0,
                totalDiscount: 0,
                accountDiscount: 0,
                vehicleButtons: null
            },
            contactDepartment: null,
            contactTitle: null,
            contactName: null,
            contactEmail: null,
            contactMobile: null,
            leadPassenger: null,
            leadPassengerTitle: null,
            leadPassengerName: null,
            leadPassengerEmail: null,
            leadPassengerMobile: null,
            payment: null,
            paymentButtons: null,
            totalPrice: 0,
            totalPriceWithDiscount: 0,
            totalDiscount: 0,
            totalDeposit: 0,
            discountId: 0,
            discountCode: ''
        };
    }

    function etoStep(backStep) {

        /*
        console.log('backStep: ' + backStep);
        console.log('currentStep: ' + etoData.currentStep);
        console.log('maxStep: ' + etoData.maxStep);
        */

        $('#'+ etoData.mainContainer +' #' + etoData.messageContainer).html('');

        if (backStep && backStep < etoData.currentStep) {
            etoData.currentStep = backStep;
        }

        if (etoData.currentStep > etoData.maxStep) {
            etoData.currentStep = etoData.maxStep;
        }

        $('#'+ etoData.mainContainer +' #etoStep1Container, ' +
          '#'+ etoData.mainContainer +' #etoStep2Container, ' +
          '#'+ etoData.mainContainer +' #etoStep3Container').hide();

        $('.eto-main-container').removeClass('v2-current-step1 v2-current-step2 v2-current-step3');

        $('.etoMapBoxMainContainer').hide();

        $('#'+ etoData.mainContainer +' #etoStep1Button, ' +
          '#'+ etoData.mainContainer +' #etoStep2Button, ' +
          '#'+ etoData.mainContainer +' #etoStep3Button').find('.v2-steps-edit').show();

        if (etoData.currentStep == 3) {
            $('#etoBookingUserContainer').show();
        } else {
            $('#etoBookingUserContainer').hide();
        }

        if (etoData.currentStep == 3) {
            $('.eto-main-container').addClass('v2-current-step3');
            $('.etoMapBoxMainContainer').show();
        }
        else if (etoData.currentStep == 2) {
            $('.eto-main-container').addClass('v2-current-step2');
            $('#'+ etoData.mainContainer +' #etoStep3Button').find('.v2-steps-edit').hide();
            $('.etoMapBoxMainContainer').show();
        }
        else if (etoData.currentStep == 1) {
            $('.eto-main-container').addClass('v2-current-step1');
            $('#'+ etoData.mainContainer +' #etoStep2Button').find('.v2-steps-edit').hide();
            $('#'+ etoData.mainContainer +' #etoStep3Button').find('.v2-steps-edit').hide();
        }

        if (etoData.currentStep > 0) {
            $('#'+ etoData.mainContainer +' #etoStep' + etoData.currentStep + 'Container').show();
            $('#'+ etoData.mainContainer +' #etoStep' + etoData.currentStep + 'Button').find('.v2-steps-edit').hide();
        }


        var sBtn = $('#'+ etoData.mainContainer +' #etoStep1Button');
        sBtn.append(sBtn.find('.v2-steps-name'));

        var sBtn = $('#'+ etoData.mainContainer +' #etoStep2Button');
        sBtn.append(sBtn.find('.v2-steps-name'));

        var sBtn = $('#'+ etoData.mainContainer +' #etoStep3Button');
        sBtn.append(sBtn.find('.v2-steps-name'));

        var sBtn = $('#'+ etoData.mainContainer +' #etoStep' + etoData.currentStep + 'Button');
        sBtn.append(sBtn.find('.v2-steps-title'));


        $('#'+ etoData.mainContainer +' #etoStepButtonsContainer .v2-steps-step-active').removeClass('v2-steps-step-active');
        $('#'+ etoData.mainContainer +' #etoStep' + etoData.currentStep + 'Button').closest('.v2-steps-step').addClass('v2-steps-step-active');

        // AdWords tracking code
        if (etoData.currentStepTemp != etoData.currentStep) {
            etoData.currentStepTemp = etoData.currentStep;

            // gTag start - Steps
            switch (etoData.currentStep) {
              case 1:

                var search = '';

                if (etoData.booking.route1.address.start && etoData.booking.route1.address.end) {
                    search = etoData.booking.route1.address.start +' - '+ etoData.booking.route1.address.end;
                }

                if (etoData.request.config.google_analytics_tracking_id) {
                    gtag('event', 'location', {
                      'event_label': 'Booking location'+ (search ? ' ('+ search +')' : ''),
                      'event_category': 'booking',
                    });
                }

                if (etoData.request.config.google_adwords_conversion_id &&
                    etoData.request.config.google_adwords_conversions &&
                    etoData.request.config.google_adwords_conversions.booking_location) {
                  gtag('event', 'conversion', {
                    'send_to': etoData.request.config.google_adwords_conversion_id +'/'+ etoData.request.config.google_adwords_conversions.booking_location,
                    'search': search,
                  });
                }

              break;
              case 2:

                if (etoData.request.config.google_analytics_tracking_id) {
                    gtag('event', 'vehicles', {
                      'event_label': 'Booking vehicles',
                      'event_category': 'booking',
                    });
                }

                if (etoData.request.config.google_adwords_conversion_id &&
                    etoData.request.config.google_adwords_conversions &&
                    etoData.request.config.google_adwords_conversions.booking_vehicles) {
                  gtag('event', 'conversion', {
                    'send_to': etoData.request.config.google_adwords_conversion_id +'/'+ etoData.request.config.google_adwords_conversions.booking_vehicles,
                  });
                }

              break;
              case 3:

                if (etoData.request.config.google_analytics_tracking_id) {
                    gtag('event', 'details', {
                      'event_label': 'Booking details',
                      'event_category': 'booking',
                    });
                }

                if (etoData.request.config.google_adwords_conversion_id &&
                    etoData.request.config.google_adwords_conversions &&
                    etoData.request.config.google_adwords_conversions.booking_details) {
                  gtag('event', 'conversion', {
                    'send_to': etoData.request.config.google_adwords_conversion_id +'/'+ etoData.request.config.google_adwords_conversions.booking_details,
                  });
                }

              break;
            }
            // gTag end - Steps
        }

    }

    function etoCopyReturn() {

        // Category - start
        $('#'+ etoData.mainContainer +' #etoRoute2CategoryStart').val(etoData.booking.route1.category.end);
        $('#'+ etoData.mainContainer +' #etoRoute2CategoryEnd').val(etoData.booking.route1.category.start);
        // Category - end


        // Location - start
        etoCreate('location', 'Route2LocationStart', '', {
            'type': etoData.booking.route1.category.type.end,
            'value': etoData.booking.route1.category.end
        });
        etoCreate('location', 'Route2LocationEnd', '', {
            'type': etoData.booking.route1.category.type.start,
            'value': etoData.booking.route1.category.start
        });

        $('#'+ etoData.mainContainer +' #etoRoute2LocationStart').val(etoData.booking.route1.location.end);
        $('#'+ etoData.mainContainer +' #etoRoute2LocationEnd').val(etoData.booking.route1.location.start);
        // Location - end


        // Waypoints - start
        $('#'+ etoData.mainContainer +' #etoRoute2WaypointsLoader').html('');

//my changes here

        // etoData.booking.route2.waypoints = etoData.booking.route1.waypoints;

        // if( $.isArray(etoData.booking.route2.waypoints) )
        // {
        // 	etoData.booking.route2.waypoints.reverse();
        // }

// end my changes

        etoData.booking.route2.waypoints = [];

        $.each(etoData.booking.route1.waypoints, function(key, value) {
            etoData.booking.route2.waypoints.push(value);
        });

        if ($.isArray(etoData.booking.route2.waypoints)) {
            etoData.booking.route2.waypoints.reverse();
        }

        $.each(etoData.booking.route2.waypoints, function(key, value) {
            var fieldId = etoWaypoints('Route2Waypoints');
            $('#'+ etoData.mainContainer +' #' + fieldId).val(value);

            if (etoData.enabledTypeahead == 1) {
                $('#'+ etoData.mainContainer +' #' + fieldId + 'Typeahead').typeahead('val', value);
            }
        });
        // Waypoints - end

//herer changes aply
        
        // Waypoints address - start
        // var waypointCount = 1;
        // var html = '';

        // $.each(etoData.booking.route1.waypoints, function(key, value) {
        // 	html += '<div class="etoOuterContainer"><label class="etoLabel">Waypoint '+ waypointCount +'</label><div class="etoInnerContainer">'+ value +'</div><div class="clear"></div></div>';
        // 	html += etoCreate('input', 'Route2WaypointsComplete[]', 'Waypoint Address');
        // 	waypointCount += 1;
        // });

        $('#'+ etoData.mainContainer +' #etoRoute2WaypointsCompleteLoader').html(html);

        if( $.isArray(etoData.booking.route1.waypointsComplete) )
        {
        	etoData.booking.route1.waypointsComplete.reverse();
        }

        $('#'+ etoData.mainContainer +' input[name*=etoRoute2WaypointsComplete]').each(function(key, value) {
        	$(this).val(etoData.booking.route1.waypointsComplete[key]);
        });
        // Waypoints address - end
       


        // Vehicle - start
        $('#'+ etoData.mainContainer +' #etoRoute2VehicleContainer select.etoVehicleSelect').each(function(key, value) {
            // Reset
            if (parseInt($(value).val()) > 0) {
                $(value).val(0);
            }

            // Select
            $.each(etoData.booking.route1.vehicle, function(key2, value2) {
                if (parseInt($(value).attr('vehicle_id')) == parseInt(value2.id)) {
                    $(value).val(value2.amount);
                    return;
                }
            });
        });
        // Vehicle - end


        // Capacity - start
        etoCreate('amount', 'Route2Passengers', etoLang('ROUTE_PASSENGERS'), etoData.booking.route1.vehicleLimits.passengers);
        etoCreate('amount', 'Route2Luggage', etoLang('ROUTE_LUGGAGE'), etoData.booking.route1.vehicleLimits.luggage);
        etoCreate('amount', 'Route2HandLuggage', etoLang('ROUTE_HAND_LUGGAGE'), etoData.booking.route1.vehicleLimits.handLuggage);
        etoCreate('amount', 'Route2ChildSeats', etoLang('ROUTE_CHILD_SEATS'), etoData.booking.route1.vehicleLimits.childSeats, etoLang('ROUTE_CHILD_SEATS_INFO'));
        etoCreate('amount', 'Route2BabySeats', etoLang('ROUTE_BABY_SEATS'), etoData.booking.route1.vehicleLimits.babySeats, etoLang('ROUTE_BABY_SEATS_INFO'));
        etoCreate('amount', 'Route2InfantSeats', etoLang('ROUTE_INFANT_SEATS'), etoData.booking.route1.vehicleLimits.infantSeats, etoLang('ROUTE_INFANT_SEATS_INFO'));
        etoCreate('amount', 'Route2Wheelchair', etoLang('ROUTE_WHEELCHAIR'), etoData.booking.route1.vehicleLimits.wheelchair, etoLang('ROUTE_WHEELCHAIR_INFO'));

        if( etoData.booking.route2.vehicleLimits.childSeats > 0 ||
            etoData.booking.route2.vehicleLimits.babySeats > 0 ||
            etoData.booking.route2.vehicleLimits.infantSeats > 0 ) {
            $('#etoRoute2ChildSeatsToggleMain').show();
        }
        else {
            $('#etoRoute2ChildSeatsToggleMain').hide();
        }

        if (etoData.request.config.booking_show_preferred) {
            etoData.booking.route2.passengers = etoData.booking.preferred.passengers;
            etoData.booking.route2.luggage = etoData.booking.preferred.luggage;
            etoData.booking.route2.handLuggage = etoData.booking.preferred.handLuggage;
        }

        $('#'+ etoData.mainContainer +' #etoRoute2Passengers').val(etoData.booking.route1.passengers);
        $('#'+ etoData.mainContainer +' #etoRoute2Luggage').val(etoData.booking.route1.luggage);
        $('#'+ etoData.mainContainer +' #etoRoute2HandLuggage').val(etoData.booking.route1.handLuggage);
        $('#'+ etoData.mainContainer +' #etoRoute2ChildSeats').val(etoData.booking.route1.childSeats);
        $('#'+ etoData.mainContainer +' #etoRoute2BabySeats').val(etoData.booking.route1.babySeats);
        $('#'+ etoData.mainContainer +' #etoRoute2InfantSeats').val(etoData.booking.route1.infantSeats);
        $('#'+ etoData.mainContainer +' #etoRoute2Wheelchair').val(etoData.booking.route1.wheelchair);
        // Capacity - end


        // Meet and greet - start
        // $('#'+ etoData.mainContainer +' #etoRoute2MeetAndGreet').val(0);
        $('#'+ etoData.mainContainer +' #etoRoute2MeetAndGreet').attr('checked', false);
        // Meet and greet - end


        // Show return options - end
        //$('#'+ etoData.mainContainer +' .etoHideContainer').hide();
        // Show return options - end

    }

    function etoInit(data) {
        $('.language-switcher').addClass('hidden');

        etoData.booking = etoReset();

        if (data) {
            $.each(data, function(key, value) {
                etoData[key] = value;
            });
        }

        if (etoData.debug) {
            console.log('initV1');
        }

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': _token
            },
            url: etoData.apiURL,
            type: 'POST',
            data: {
                task: 'initV1'
            },
            dataType: 'json',
            // async: false,
            // cache: false,
            success: function(response) {

                if (response.message.length > 0) {
                    $.each(response.message, function(key, value) {
                        etoData.message.push(value);
                    });
                }
                if (response.config) {
                    etoData.request.config = response.config;

                    if (etoData.request.config.booking_time_picker_by_minute) {
                        etoData.request.config.booking_time_picker_steps = 1;
                    }
                    else {
                        etoData.request.config.booking_time_picker_steps = parseInt(etoData.request.config.booking_time_picker_steps);
                    }
                }
                if (etoData.request.config.debug) {
                    etoData.debug = etoData.request.config.debug;
                }

                var urlParams = etoData.urlParams;

                if( urlParams.finishType && urlParams.bID ) {
                    etoFinish(urlParams.finishType, urlParams.bID, urlParams.tID, urlParams.tMSG);
                    return false;
                }

                if (response.user) {
                    $user = response.user;
                    //console.log($user);

                    if ($user.isCompany) {
                        etoData.isCompany = $user.isCompany;
                    }
                    if ($user.isAccountPayment) {
                        etoData.isAccountPayment = $user.isAccountPayment;
                    }
                    if ($user.id) {
                        etoData.userId = $user.id;
                    }
                    if ($user.name) {
                        etoData.userName = $user.name;
                    }
                    if ($user.homeAddress) {
                        etoData.userHomeAddress = $user.homeAddress;
                    }
                    if ($user.departments) {
                        etoData.userDepartments = $user.departments;
                        $('.eto-v2-departments-container').html(etoCreate('departments', 'ContactDepartment', etoLang('bookingField_Department')));
                    }
                    if ($user.title != '') {
                        etoData.booking.contactTitle = $user.title;
                    }
                    if ($user.name != '') {
                        etoData.booking.contactName = $user.name;
                    }
                    if ($user.email != '') {
                        etoData.booking.contactEmail = $user.email;
                    }
                    if ($user.mobileNumber != '') {
                        etoData.booking.contactMobile = $user.mobileNumber;
                    }
                }

                // if (response.language) {
                //   etoData.request.language = response.language;
                // }

                if (response.category) {
                    etoData.request.category = response.category;
                }
                //
                // if (response.location) {
                //   etoData.request.location = response.location;
                // }

                if (response.vehicle) {
                    etoData.request.vehicle = response.vehicle;
                }

                if (response.payment) {
                    etoData.request.payment = response.payment;
                }

                if (response.services) {
                    etoData.request.services = response.services;
                }

                if (etoData.debug) {
                    console.log(response);
                }

                if (etoData.layout == 'minimal') {
                    var html = '<div class="etoMinimalContainer">';
                } else {
                    var html = '<div class="etoCompleteContainer">';
                }

                if (etoData.layout != 'minimal') {

                    html += '<div class="v2-steps-main">';
                      html += '<div id="etoStepButtonsContainer" class="v2-steps clearfix">';
                        html += '<div id="etoStep1Button" class="v2-steps-step v2-steps-step1 v2-steps-step-active" id="etoStep1Button">';
                          html += '<span class="v2-steps-icon">';
                            html += '<i class="fa fa-caret-right"></i>';
                          html += '</span>';
                          html += '<span class="v2-steps-title">'+ etoLang('STEP1_BUTTON_TITLE') +'</span>';
                          html += '<span class="v2-steps-name">'+ etoLang('STEP1_BUTTON') +'</span>';
                          // html += '<span class="v2-steps-edit" title="'+ etoLang('bookingButton_Edit') +'">';
                          //   html += '<i class="fa fa-pencil"></i>';
                          // html += '</span>';
                        html += '</div>';
                        html += '<div id="etoStep2Button" class="v2-steps-step v2-steps-step2" id="etoStep2Button">';
                          html += '<span class="v2-steps-icon">';
                            html += '<i class="fa fa-caret-right"></i>';
                          html += '</span>';
                          html += '<span class="v2-steps-title">'+ etoLang('STEP2_BUTTON_TITLE') +'</span>';
                          html += '<span class="v2-steps-name">'+ etoLang('STEP2_BUTTON') +'</span>';
                          // html += '<span class="v2-steps-edit" title="'+ etoLang('bookingButton_Edit') +'">';
                          //   html += '<i class="fa fa-pencil"></i>';
                          // html += '</span>';
                        html += '</div>';
                        html += '<div id="etoStep3Button" class="v2-steps-step v2-steps-step3" id="etoStep3Button">';
                          html += '<span class="v2-steps-icon">';
                            html += '<i class="fa fa-caret-right"></i>';
                          html += '</span>';
                          html += '<span class="v2-steps-title">'+ etoLang('STEP3_BUTTON_TITLE') +'</span>';
                          html += '<span class="v2-steps-name">'+ etoLang('STEP3_BUTTON') +'</span>';
                          // html += '<span class="v2-steps-edit" title="'+ etoLang('bookingButton_Edit') +'">';
                          //   html += '<i class="fa fa-pencil"></i>';
                          // html += '</span>';
                        html += '</div>';
                        html += '<div class="v2-steps-lang"></div>';
                      html += '</div>';

                      if(etoData.request.config.booking_display_book_by_phone && etoData.request.config.company_telephone) {
                          html += '<div class="v2-book-by-phone">\
                              <a class="clearfix" href="tel:'+ etoData.request.config.company_telephone +'" title="'+ etoData.request.config.company_telephone +'" onclick="$(this).find(\'.v2-book-by-phone-name\').toggle(); $(this).find(\'.v2-book-by-phone-number\').toggle();">\
                                <i class="fa fa-phone"></i>\
                                <span class="v2-book-by-phone-name">'+ etoLang('bookingBookByPhone') +'</span>\
                                <span class="v2-book-by-phone-number" style="display:none;">'+ etoData.request.config.company_telephone +'</span>\
                              </a>\
                            </div>';
                      }
                    html += '</div>';

                    html += '<div id="etoMessageContainer"></div>';

                    if (etoData.request.config.login_enable == 1) {
                        html += '<div id="etoBookingUserContainer">';

                        if (etoLang('bookingHeading_Step3') != '' &&
                            etoLang('bookingHeading_Step3') != 'bookingHeading_Step3') {
                            html += '<h3 class="etoStep3Header">' + etoLang('bookingHeading_Step3') + '</h3>';
                        }

                        html += '<div id="etoBookingUserModal" class="modal fade" role="dialog" aria-labelledby="etoBookingUserModalTitle" aria-hidden="true">' +
                            '<div class="modal-dialog">' +
                            '<div class="modal-content">' +
                            '<div class="modal-header">' +
                            '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>' +
                            '<h4 class="modal-title hide" id="etoBookingUserModalTitle"></h4><br />' +
                            '</div>' +
                            '<div class="modal-body" style="padding:2px;"></div>' +
                            '</div>' +
                            '</div>' +
                            '</div>';

                        html += '<div id="etoBookingLoginFormContainer">' +
                            '<div id="etoBookingUserMessageContainer"></div>' +

                            '<div class="eto-v2-section eto-v2-section-checkout-type" id="etoBookingCheckoutType">'+
                              '<div class="eto-v2-section-label">' + etoLang('bookingHeading_CheckoutType') + '</div>'+
                              '<div class="clearfix eto-v2-checkout">' +
                                '<div class="radio eto-v2-checkout-guest">' +
                                  '<label><input type="radio" name="checkoutType" value="0" checked="checked"><span class="cr"><i class="cr-icon ion-record"></i></span><span class="cr-val">' + etoLang('bookingHeading_CheckoutTypeGuest') + '</span></label>' +
                                '</div>' +
                                // '<div class="eto-v2-checkout-separator"></div>'+
                                '<div class="radio eto-v2-checkout-login">' +
                                  '<label><input type="radio" name="checkoutType" value="2"><span class="cr"><i class="cr-icon ion-record"></i></span><span class="cr-val">' + etoLang('bookingHeading_CheckoutTypeLogin') + '</span></label>' +
                                '</div>' +
                                // '<div class="eto-v2-checkout-separator"></div>'+
                                '<div class="radio eto-v2-checkout-register">' +
                                  '<label><input type="radio" name="checkoutType" value="1"><span class="cr"><i class="cr-icon ion-record"></i></span><span class="cr-val">' + etoLang('bookingHeading_CheckoutTypeRegister') + '</span></label>' +
                                '</div>' +
                              '</div>' +
                            '</div>'+

                            '<form role="form" id="etoBookingLoginForm">' +
                              '<div class="eto-v2-section eto-v2-section-box eto-v2-section-login-form">' +
                                '<div class="eto-v2-section-label">' + etoLang('bookingHeading_Login') + '</div>' +
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
                                '<div class="row eto-v2-section-login-form-buttons">' +
                                  '<div class="col-xs-6 col-sm-6 col-md-6">' +
                                    '<input type="submit" id="loginButton" class="btn btn-md btn-primary" value="' + etoLang('userButton_Login') + '" tabindex="3">' +
                                  '</div>' +
                                  '<div class="col-xs-6 col-sm-6 col-md-6">' +
                                    '<a href="' + etoData.customerURL + '#password" target="_blank" id="passwordButton" class="btn btn-link pull-right" tabindex="5">' + etoLang('userButton_LostPassword') + '</a>' +
                                  '</div>' +
                                '</div>' +
                              '</div>'+
                            '</form>' +

                            '<form role="form" id="etoBookingRegisterForm">' +
                              '<div class="eto-v2-section eto-v2-section-box eto-v2-section-register-form">' +
                                '<div class="eto-v2-section-label">' + etoLang('bookingHeading_Register') + '</div>' +
                                '<div class="eto-v2-user-profile-switch">' +
                                  '<div class="radio" style="float:left; margin:0px 20px 10px 0;">' +
                                    '<label><input type="radio" name="profileType" value="private" checked="checked"><span class="cr"><i class="cr-icon ion-record"></i></span>' + etoLang('userField_ProfileTypePrivate') + '</label>' +
                                  '</div>' +
                                  '<div class="radio" style="float:left; margin:0px 0px 10px 0;">' +
                                    '<label><input type="radio" name="profileType" value="company"><span class="cr"><i class="cr-icon ion-record"></i></span>' + etoLang('userField_ProfileTypeCompany') + '</label>' +
                                  '</div>' +
                                  '<div class="clearfix"></div>' +
                                '</div>' +

                                '<div class="row">' +
                                  '<div class="col-xs-12 col-sm-6">' +
                                    '<div class="form-group" title="' + etoLang('userField_FirstName') + '">' +
                                      '<div class="input-group">' +
                                        // '<span class="input-group-addon"><span class="ion-ios-person-outline"></span></span>' +
                                        '<input type="text" name="firstName" id="firstName" placeholder="' + etoLang('userField_FirstName') + '" class="form-control" tabindex="1">' +
                                      '</div>' +
                                    '</div>' +
                                  '</div>' +
                                  '<div class="col-xs-12 col-sm-6">' +
                                    '<div class="form-group" title="' + etoLang('userField_LastName') + '">' +
                                      '<div class="input-group">' +
                                        // '<span class="input-group-addon"><span class="ion-ios-person-outline"></span></span>' +
                                        '<input type="text" name="lastName" id="lastName" placeholder="' + etoLang('userField_LastName') + '" class="form-control" tabindex="2">' +
                                      '</div>' +
                                    '</div>' +
                                  '</div>' +
                                '</div>' +

                                '<div class="form-group" title="' + etoLang('userField_Email') + '">' +
                                  '<div class="input-group">' +
                                    // '<span class="input-group-addon"><span class="ion-ios-email-outline"></span></span>' +
                                    '<input type="email" name="email" id="email" placeholder="' + etoLang('userField_Email') + '" class="form-control" tabindex="3">' +
                                  '</div>' +
                                '</div>' +

                                '<div class="company-container">' +
                                  '<div class="form-group" title="' + etoLang('userField_CompanyName') + '">' +
                                    '<div class="input-group">' +
                                      // '<span class="input-group-addon"><span class="ion-ios-home-outline"></span></span>' +
                                      '<input type="text" name="companyName" id="companyName" placeholder="' + etoLang('userField_CompanyName') + '" class="form-control" tabindex="4">' +
                                    '</div>' +
                                  '</div>' +
                                  '<div class="row">' +
                                    '<div class="col-xs-12 col-sm-6">' +
                                      '<div class="form-group" title="' + etoLang('userField_CompanyNumber') + '">' +
                                        '<div class="input-group">' +
                                          // '<span class="input-group-addon"><span class="ion-ios-home-outline"></span></span>' +
                                          '<input type="text" name="companyNumber" id="companyNumber" placeholder="' + etoLang('userField_CompanyNumber') + '" class="form-control" tabindex="5">' +
                                        '</div>' +
                                      '</div>' +
                                    '</div>' +
                                    '<div class="col-xs-12 col-sm-6">' +
                                      '<div class="form-group" title="' + etoLang('userField_CompanyTaxNumber') + '">' +
                                        '<div class="input-group">' +
                                          // '<span class="input-group-addon"><span class="ion-ios-home-outline"></span></span>' +
                                          '<input type="text" name="companyTaxNumber" id="companyTaxNumber" placeholder="' + etoLang('userField_CompanyTaxNumber') + '" class="form-control" tabindex="6">' +
                                        '</div>' +
                                      '</div>' +
                                    '</div>' +
                                  '</div>' +
                                '</div>' +

                                '<div class="row">' +
                                  '<div class="col-xs-12 col-sm-6">' +
                                    '<div class="form-group" title="' + etoLang('userField_Password') + '">' +
                                      '<div class="input-group">' +
                                        // '<span class="input-group-addon"><span class="ion-ios-locked-outline"></span></span>' +
                                        '<input type="password" name="password" id="password" placeholder="' + etoLang('userField_Password') + '" class="form-control" tabindex="4">' +
                                      '</div>' +
                                    '</div>' +
                                  '</div>' +
                                  '<div class="col-xs-12 col-sm-6">' +
                                    '<div class="form-group" title="' + etoLang('userField_ConfirmPassword') + '">' +
                                      '<div class="input-group">' +
                                        // '<span class="input-group-addon"><span class="ion-ios-locked-outline"></span></span>' +
                                        '<input type="password" name="passwordConfirmation" id="passwordConfirmation" placeholder="' + etoLang('userField_ConfirmPassword') + '" class="form-control" tabindex="5">' +
                                      '</div>' +
                                    '</div>' +
                                  '</div>' +
                                '</div>' +

                                (parseInt(etoData.request.config.terms_enable) == 1 ? ('<div class="form-group form-group-terms">' +
                                  '<div class="checkbox">' +
                                    '<label>' +
                                    '<input type="checkbox" name="terms" id="terms" value="terms" tabindex="6" /><span class="cr"><i class="cr-icon ion-ios-checkmark-empty"></i></span> ' +
                                    '' + etoLang('userField_Agree') + ' <a href="' + etoData.request.config.url_terms + '" target="_blank">' + etoLang('userField_TermsAndConditions') + '</a>' +
                                    '</label>' +
                                  '</div>' +
                                '</div>') : '') +

                                '<input type="submit" id="registerButton" value="' + etoLang('userButton_Register') + '" class="btn btn-primary btn-block" tabindex="7">' +

                              '</div>'+
                            '</form>' +
                            '</div>' +

                            '<div id="etoBookingLogoutFormContainer">' +
                              '<div class="eto-v2-section eto-v2-section-box eto-v2-section-logout-form">' +
                                // html += '<div class="etoBookingUserName">' + etoLang('panel_Hello') + ' <span id="userNameContainer"></span>!</div>'+
                                '<div class="eto-v2-section-label">' + etoLang('panel_Hello') + ' <span id="userNameContainer"></span>!</div>' +
                                '<a href="' + etoData.customerURL + '#booking/list" target="_blank" id="accountButton" class="btn btn-md btn-primary">' + etoLang('bookingButton_CustomerAccount') + '</a> ' +
                                '<a href="' + etoData.customerURL + '#logout" target="_blank" id="logoutButton" class="btn btn-md btn-primary">' + etoLang('panel_Logout') + '</a>' +
                              '</div>'+
                            '</div>' +

                          '</div>';
                    }

                    html += '<form method="post" action="#" id="etoForm" autocomplete="off">';

                    // This option will disable autocomplete option in Google Chrome
                    html += '<input type="text" name="randomusernameremembered" id="randomusernameremembered" value="" style="width:0;height:0;visibility:hidden;position:absolute;left:0;top:0;margin:0;padding:0;border:0;background:none;">';
                    html += '<input type="password" name="randompasswordremembered" id="randompasswordremembered" value="" style="width:0;height:0;visibility:hidden;position:absolute;left:0;top:0;margin:0;padding:0;border:0;background:none;">';
                    // End

                    html += '<div id="etoStep1Container">';
                      if (etoLang('bookingHeading_Step1') != '' &&
                          etoLang('bookingHeading_Step1') != 'bookingHeading_Step1') {
                          html += '<h3 class="etoStep1Header">' + etoLang('bookingHeading_Step1') + '</h3>';
                      }

                      html += etoCreate('services', 'Services', etoLang('bookingField_Services'));

                      html += '<div class="etoRoutesContainer">';

                        html += '<div id="etoRoute1Container">';
                          html += etoCreate('category', 'Route1CategoryStart', etoLang('bookingField_From'), {
                              placeholder: (ETOBookingType == 'from-airport') ? etoLang('bookingField_SelectAirportPlaceholder') : etoLang('bookingField_FromPlaceholder')
                          });
                          html += etoCreate('loader', 'Route1LocationStart', '');
                          html += etoCreate('loader', 'Route1Waypoints', '');
                          html += '<div id="etoRoute1WaypointsPosition1"></div>';
                          html += etoCreate('category', 'Route1CategoryEnd', etoLang('bookingField_To'), {
                              placeholder: (ETOBookingType == 'to-airport') ? etoLang('bookingField_SelectAirportPlaceholder') : etoLang('bookingField_ToPlaceholder')
                          });
                          html += etoCreate('loader', 'Route1LocationEnd', '');
                          html += etoCreate('services_duration', 'ServicesDuration', etoLang('bookingField_ServicesDuration'));
                          html += etoCreate('input', 'Route1Date', etoLang('ROUTE_DATE'), {
                              fieldClass: 'eto-v2-field-no-label',
                          });
                          html += '<div id="etoRoute1WaypointsPosition2"></div>';

                          if (etoData.request.config.booking_show_preferred) {
                              html += etoCreate('preferred');
                          }

                          html += '<div class="etoWaypointsAddButtonContainer">';
                            html += '<a href="#" id="etoRoute1WaypointsButton" class="etoWaypointsAddButton" onclick="return false;" title="' + etoLang('ROUTE_WAYPOINTS') + '"><span class="ion-plus"></span> ' + etoLang('ROUTE_WAYPOINTS') + '</a>';
                            html += '<a href="#" id="etoRoute1SwapLocationsButton" class="etoSwapLocationsButton" onclick="return false;" title="' + etoLang('ROUTE_SWAP_LOCATIONS') + '"><span class="ion-arrow-swap"></span></a>';
                            html += etoCreate('return', 'RouteReturn', etoLang('ROUTE_RETURN'));
                            html += etoCreate('clear');
                          html += '</div>';
                        html += '</div>';

                        html += '<div id="etoRoute2Container">';
                          html += etoCreate('category', 'Route2CategoryStart', etoLang('bookingField_From'), {
                              placeholder: (ETOBookingType == 'to-airport') ? etoLang('bookingField_SelectAirportPlaceholder') : etoLang('bookingField_FromPlaceholder')
                          });
                          html += etoCreate('loader', 'Route2LocationStart', '');
                          html += etoCreate('loader', 'Route2Waypoints', '');
                          html += '<div id="etoRoute2WaypointsPosition1"></div>';
                          html += etoCreate('category', 'Route2CategoryEnd', etoLang('bookingField_To'), {
                              placeholder: (ETOBookingType == 'from-airport') ? etoLang('bookingField_SelectAirportPlaceholder') : etoLang('bookingField_ToPlaceholder')
                          });
                          html += etoCreate('loader', 'Route2LocationEnd', '');
                          html += etoCreate('input', 'Route2Date', etoLang('ROUTE_DATE'), {
                              fieldClass: 'eto-v2-field-no-label',
                          });
                          html += '<div id="etoRoute2WaypointsPosition2"></div>';

                          html += '<div class="etoWaypointsAddButtonContainer">';
                            html += '<a href="#" id="etoRoute2WaypointsButton" class="etoWaypointsAddButton" onclick="return false;" title="' + etoLang('ROUTE_WAYPOINTS') + '"><span class="ion-plus"></span> ' + etoLang('ROUTE_WAYPOINTS') + '</a>';
                            html += '<a href="#" id="etoRoute2SwapLocationsButton" class="etoSwapLocationsButton" onclick="return false;" title="' + etoLang('ROUTE_SWAP_LOCATIONS') + '"><span class="ion-arrow-swap"></span></a>';
                            html += etoCreate('clear');
                          html += '</div>';
                        html += '</div>';

                        html += '<div id="etoButtonsContainer">';
                          html += '<div class="etoButtonsInnerContainer">';
                            html += etoCreate('button', 'QuoteStep1Button', etoLang('BUTTON_COMPLETE_QUOTE_STEP1'), {
                                'cls': 'btn-primary btn-block',
                                'icon': 'fa fa-arrow-right'
                            });
                            html += etoCreate('button', 'ResetButton', etoLang('BUTTON_COMPLETE_RESET'), {
                                'cls': 'btn-link'
                            });
                            html += etoCreate('clear');
                          html += '</div>';
                        html += '</div>';
                        html += etoCreate('clear');

                      html += '</div>';

                    html += '</div>';
                    html += '<div id="etoStep2Container">';

                    if (etoLang('bookingHeading_Step2') != '' &&
                        etoLang('bookingHeading_Step2') != 'bookingHeading_Step2') {
                        html += '<h3 class="etoStep2Header">' + etoLang('bookingHeading_Step2') + '</h3>';
                    }

                    // html += etoCreate('button', 'QuoteStep2ButtonHelper1', etoLang('BUTTON_COMPLETE_QUOTE_STEP2'), {
                    //   'cls': 'btn-default'
                    // });

                    html += '<ul class="nav nav-tabs etoVehicleTabs" role="tablist">';
                    html += '<li role="presentation" class="active"><a href="#etoTabRoute1" aria-controls="etoTabRoute1" role="tab" data-toggle="tab">' + etoLang('ROUTE_RETURN_NO') + '</a></li>';
                    html += '<li role="presentation"><a href="#etoTabRoute2" aria-controls="etoTabRoute2" role="tab" data-toggle="tab">' + etoLang('ROUTE_RETURN_YES') + '</a></li>';
                    html += '</ul>';

                    html += '<div class="etoRouteReturnSectionContainer">';
                    html += '<div id="etoRoute1Container">';

                    // One-way - start
                    html += '<fieldset class="etoVehicleTabContent etoVehicleTabContentActive">';
                    html += '<legend>' + etoLang('ROUTE_RETURN_NO') + '</legend>';

                    html += etoCreate('vehicle_single', 'Route1Vehicle', etoLang('ROUTE_VEHICLE'));
                    html += etoCreate('clear');
                    html += etoCreate('meet_and_greet', 'Route1MeetAndGreet', etoLang('ROUTE_MEET_AND_GREET'));

                    html += '<div class="etoRoute1MapMaster">';
                      html += '<div class="etoRoute1MapParent">';
                        html += '<a href="#" onclick="return false;" class="etoRoute1MapBtnShow"><i class="fa fa-map-marker" aria-hidden="true"></i> ' + etoLang('bookingButton_ShowMap') + '</a>';
                        html += '<a href="#" onclick="return false;" class="etoRoute1MapBtnHide" style="display:none;"><i class="fa fa-map-marker" aria-hidden="true"></i> ' + etoLang('bookingButton_HideMap') + '</a>';
                        html += etoCreate('clear');
                        // html += '<div class="etoRoute1MapChild" style="height:0; overflow:hidden;">';
                        // html += etoCreate('map', 'Route1Map');
                        // html += '</div>';
                      html += '</div>';
                    html += '</div>';

                    html += '</fieldset>';
                    // One-way - end

                    html += '</div>';
                    html += '<div id="etoRoute2Container">';

                    // Return - start
                    html += '<fieldset class="etoVehicleTabContent">';
                    html += '<legend>' + etoLang('ROUTE_RETURN_YES') + '</legend>';

                    html += etoCreate('vehicle_single', 'Route2Vehicle', etoLang('ROUTE_VEHICLE'));
                    html += etoCreate('clear');
                    html += etoCreate('meet_and_greet', 'Route2MeetAndGreet', etoLang('ROUTE_MEET_AND_GREET'));

                    html += '<div class="etoRoute2MapMaster">';
                      html += '<div class="etoRoute2MapParent">';
                        html += '<a href="#" onclick="return false;" class="etoRoute2MapBtnShow"><i class="fa fa-map-marker" aria-hidden="true"></i> ' + etoLang('bookingButton_ShowMap') + '</a>';
                        html += '<a href="#" onclick="return false;" class="etoRoute2MapBtnHide" style="display:none;"><i class="fa fa-map-marker" aria-hidden="true"></i> ' + etoLang('bookingButton_HideMap') + '</a>';
                        html += etoCreate('clear');
                        // html += '<div class="etoRoute2MapChild" style="height:0; overflow:hidden;">';
                        // html += etoCreate('map', 'Route2Map');
                        // html += '</div>';
                      html += '</div>';
                    html += '</div>';

                    html += '</fieldset>';
                    // Return - end

                    html += '</div>';
                    html += '</div>';

                    // html += '<div class="tabs-below">';
                    // html += '<ul class="nav nav-tabs etoVehicleTabs" role="tablist">';
                    //   html += '<li role="presentation" class="active"><a href="#etoTabRoute1" aria-controls="etoTabRoute1" role="tab" data-toggle="tab">' + etoLang('ROUTE_RETURN_NO') + '</a></li>';
                    //   html += '<li role="presentation"><a href="#etoTabRoute2" aria-controls="etoTabRoute2" role="tab" data-toggle="tab">' + etoLang('ROUTE_RETURN_YES') + '</a></li>';
                    // html += '</ul>';
                    // html += '</div>';

                    html += '<div id="etoButtonsContainer">';
                    html += '<div class="etoButtonsInnerContainer">';

                    html += '<div id="etoVehicleCheckoutTotal"></div>';

                    html += etoCreate('button', 'QuoteStep2Button', etoLang('BUTTON_COMPLETE_QUOTE_STEP2'), {
                        'cls': 'btn-primary btn-lg'
                    });
                    html += etoCreate('clear');
                    html += '</div>';
                    html += '</div>';

                    if(etoLang('STEP2_INFO1') && etoLang('STEP2_INFO1') != 'STEP2_INFO1') {
                        var info1 = etoLang('STEP2_INFO1');
                        info1 = info1.replace(/\{phone\}/g, etoData.request.config.company_telephone);
                        info1 = info1.replace(/\{email\}/g, etoData.request.config.company_email);

                        html += '<div class="etoStep2Info1">' + info1 + '</div>';
                    }

                    if(etoLang('STEP2_INFO2') && etoLang('STEP2_INFO2') != 'STEP2_INFO2') {
                        html += '<div class="etoStep2Info2">' + etoLang('STEP2_INFO2') + '</div>';
                    }

                    html += '</div>';
                    html += '<div id="etoStep3Container">';

                    if (etoLang('bookingHeading_Step3') != '' &&
                        etoLang('bookingHeading_Step3') != 'bookingHeading_Step3' &&
                        etoData.request.config.login_enable != 1) {
                        html += '<h3 class="etoStep3Header">' + etoLang('bookingHeading_Step3') + '</h3>';
                    }

                    html += '<div id="etoBookingStep3Container">';

                      html += '<div class="clearfix eto-v2-container eto-v2-container-1">';

                        html += '<div class="eto-v2-section eto-v2-section-box eto-v2-section-contact-details" id="etoContactSectionContainer">';
                          html += '<div class="eto-v2-section-label">' + etoLang('STEP3_SECTION1') + '</div>';
                          html += etoCreate('name_title', 'ContactTitle', etoLang('CONTACT_TITLE'));
                          html += etoCreate('input', 'ContactName', etoLang('CONTACT_NAME'));
                          html += etoCreate('input', 'ContactMobile', etoLang('CONTACT_MOBILE'), {
                              // placeholder: etoLang('userField_MobileNumberPlaceholder')
                          });
                          html += etoCreate('input', 'ContactEmail', etoLang('CONTACT_EMAIL'));

                          html += '<div class="eto-v2-departments-container">';
                              html += etoCreate('departments', 'ContactDepartment', etoLang('bookingField_Department'));
                          html += '</div>';

                          if(etoData.request.config.booking_show_second_passenger) {
                              html += etoCreate('lead_passenger', 'LeadPassenger', '');
                          }

                          html += '<div id="etoLeadPassengerSectionContainer">';
                            html += '<div class="eto-v2-section-label">' + etoLang('STEP3_SECTION7') + '</div>';
                            html += etoCreate('name_title', 'LeadPassengerTitle', etoLang('LEAD_PASSENGER_TITLE'));
                            html += etoCreate('input', 'LeadPassengerName', etoLang('LEAD_PASSENGER_NAME'));
                            html += etoCreate('input', 'LeadPassengerMobile', etoLang('LEAD_PASSENGER_MOBILE'), {
                                // placeholder: etoLang('userField_MobileNumberPlaceholder')
                            });
                            html += etoCreate('input', 'LeadPassengerEmail', etoLang('LEAD_PASSENGER_EMAIL'));
                          html += '</div>';
                        html += '</div>';

                        // One-way - start
                        html += '<div id="etoRoute1Container">';
                          html += '<div class="eto-v2-header-label eto-v2-return-active">' + etoLang('ROUTE_RETURN_NO') + '</div>';

                          html += '<div class="eto-v2-section eto-v2-section-box eto-v2-section-options-items">';
                            html += '<div class="etoVehicleOtherOptionsContainer etoVehicleOtherOptionsContainerR1 clearfix">';
                              html += etoCreate('loader', 'Route1Passengers', '');
                              html += etoCreate('loader', 'Route1Luggage', '');
                              html += etoCreate('loader', 'Route1HandLuggage', '');
                              html += etoCreate('loader', 'Route1Wheelchair', '');
                              // html += etoCreate('clear');
                            html += '</div>';

                            var more_options_checked = '';
                            var more_options_style = 'style="display:none;"';
                            if (etoData.request.config.booking_show_more_options == 1) {
                                more_options_checked = 'checked="checked"';
                                more_options_style = '';
                            }

                            html += '<div class="checkbox etoMoreOptionToggle">' +
                                      '<label for="etoRoute1MoreOptionToggle">' +
                                        '<input type="checkbox" id="etoRoute1MoreOptionToggle" onclick="$(\'.etoRoute1MoreOptionToggleContainer\').toggle();" '+ more_options_checked +'>' +
                                        '<span class="cr"><i class="cr-icon ion-ios-checkmark-empty"></i></span> ' + etoLang('bookingField_MoreOption') +
                                      '</label>' +
                                    '</div>';
                            html += '<div class="etoMoreOptionContainer etoRoute1MoreOptionToggleContainer" '+ more_options_style +'>';

                              html += '<div class="etoChildSeatsContainer" id="etoRoute1ChildSeatsToggleMain">';
                                html += '<div class="checkbox etoChildSeatsToggle">' +
                                          '<label for="etoRoute1ChildSeatsToggle">' +
                                            '<input type="checkbox" id="etoRoute1ChildSeatsToggle" onclick="$(\'.etoRoute1ChildSeatsContainer\').toggle();">' +
                                            '<span class="cr"><i class="cr-icon ion-ios-checkmark-empty"></i></span> ' + etoLang('bookingField_ChildSeatsNeeded') +
                                          '</label>' +
                                        '</div>';
                                html += '<div class="etoVehicleChildSeatsOptionsContainer etoRoute1ChildSeatsContainer clearfix" style="display:none;">';
                                  html += etoCreate('loader', 'Route1BabySeats', '');
                                  html += etoCreate('loader', 'Route1ChildSeats', '');
                                  html += etoCreate('loader', 'Route1InfantSeats', '');
                                  // html += etoCreate('clear');
                                html += '</div>';
                              html += '</div>';

                              html += etoCreate('clear');
                              html += etoCreate('items', 'Route1Items', ''); // etoLang('ROUTE_ITEMS')

                            html += '</div>';
                          html += '</div>';

                          html += '<div class="eto-v2-section eto-v2-section-box eto-v2-section-journey-details">';
                            html += etoCreate('loader', 'Route1JourneyDetails', '');
                            // html += '<div class="etoJourneyTitle">' + etoLang('TITLE_JOURNEY_FROM') + '</div>';
                            html += etoCreate('loader', 'Route1JourneyFrom', '');
                            html += etoCreate('input', 'Route1AddressStartComplete', etoLang('ROUTE_ADDRESS_START'), {
                                placeholder: parseInt(etoData.request.config.booking_required_address_complete_from) == 1 ? '' : '('+ etoLang('bookingOptional') +')'
                            });
                            html += etoCreate('input', 'Route1FlightNumber', etoLang('ROUTE_FLIGHT_NUMBER'));
                            html += '<div class="etoFlightLandingTimeSection">';
                                html += etoCreate('time', 'Route1FlightLandingTime', etoLang('ROUTE_FLIGHT_LANDING_TIME'));
                            html += '</div>';
                            html += etoCreate('input', 'Route1DepartureCity', etoLang('ROUTE_DEPARTURE_CITY'));
                            html += etoCreate('label', 'Route1MeetingPoint', etoLang('ROUTE_MEETING_POINT')); //, null, etoLang('ROUTE_MEETING_POINT_INFO')
                            html += '<div class="etoRoute1WaitingTimeSection">';
                                html += etoCreate('waiting_time', 'Route1WaitingTime', etoLang('ROUTE_WAITING_TIME'));
                            html += '</div>';
                            // html += etoCreate('checkbox', 'Route1MeetAndGreet', etoLang('ROUTE_MEET_AND_GREET'));
                            html += etoCreate('loader', 'Route1WaypointsComplete', '');
                            // html += '<div class="etoJourneyTitle">' + etoLang('TITLE_JOURNEY_TO') + '</div>';
                            html += etoCreate('loader', 'Route1JourneyTo', '');
                            html += etoCreate('input', 'Route1AddressEndComplete', etoLang('ROUTE_ADDRESS_END'), {
                                placeholder: parseInt(etoData.request.config.booking_required_address_complete_to) == 1 ? '' : '('+ etoLang('bookingOptional') +')'
                            });
                            html += etoCreate('input', 'Route1DepartureFlightNumber', etoLang('ROUTE_DEPARTURE_FLIGHT_NUMBER'));
                            html += '<div class="etoDepartureFlightTimeSection">';
                                html += etoCreate('time', 'Route1DepartureFlightTime', etoLang('ROUTE_DEPARTURE_FLIGHT_TIME'));
                            html += '</div>';
                            html += etoCreate('input', 'Route1DepartureFlightCity', etoLang('ROUTE_DEPARTURE_FLIGHT_CITY'));
                            html += etoCreate('charges', 'Route1ExtraCharges', etoLang('ROUTE_EXTRA_CHARGES'));
                            html += etoCreate('price', 'Route1TotalPrice', etoLang('ROUTE_TOTAL_PRICE'));
                          html += '</div>';

                          html += '<div class="eto-v2-section eto-v2-section-box eto-v2-section-comments '+ (etoData.request.config.booking_show_requirements ? '' : 'hidden') +'">';
                            html += etoCreate('textarea', 'Route1Requirements', etoLang('ROUTE_REQUIREMENTS'), {
                                placeholder: '('+ etoLang('bookingOptional') +')'
                            }, etoLang('ROUTE_REQUIREMENTS_INFO'));
                            html += etoCreate('clear');
                            if(etoLang('STEP3_INFO1') && etoLang('STEP3_INFO1') != 'STEP3_INFO1') {
                                html += '<div class="etoLagguageInfo">' + etoLang('STEP3_INFO1') + '</div>';
                            }
                          html += '</div>';

                        html += '</div>';
                        // One-way - end

                      html += '</div>';
                      html += '<div class="clearfix eto-v2-container eto-v2-container-2">';

                        if (etoData.request.config.booking_member_benefits_enable == 1 && etoData.request.config.booking_member_benefits != '') {
                          var benefitsHtml = '';
                          var benefitList = etoData.request.config.booking_member_benefits.split('\n');

                          $.each(benefitList, function(benefitKey, benefitValue) {
                              benefitValue = $.trim(benefitValue);
                              if(benefitValue) {
                                  benefitsHtml += '<li>'+ benefitValue +'</li>';
                              }
                          });

                          if(benefitsHtml) {
                              benefitsHtml = '<ul>'+ benefitsHtml +'</ul>';
                          }

                          html += '<div class="eto-v2-section eto-v2-section-benefits">';
                            html += '<div class="eto-v2-section-label">'+ etoLang('bookingMemberBenefits') +':</div>';
                            html += '<div class="eto-v2-benefits-list clearfix">'+ benefitsHtml +'</div>';
                          html += '</div>';
                        }

                        // Return - start
                        html += '<div id="etoRoute2Container">';
                          html += '<div class="eto-v2-header-label">' + etoLang('ROUTE_RETURN_YES') + '</div>';

                          html += '<div class="eto-v2-section eto-v2-section-box eto-v2-section-options-items">';
                            html += '<div class="etoVehicleOtherOptionsContainer etoVehicleOtherOptionsContainerR2 clearfix">';
                              html += etoCreate('loader', 'Route2Passengers', '');
                              html += etoCreate('loader', 'Route2Luggage', '');
                              html += etoCreate('loader', 'Route2HandLuggage', '');
                              html += etoCreate('loader', 'Route2Wheelchair', '');
                              // html += etoCreate('clear');
                            html += '</div>';

                            var more_options_checked = '';
                            var more_options_style = 'style="display:none;"';
                            if (etoData.request.config.booking_show_more_options == 1) {
                                more_options_checked = 'checked="checked"';
                                more_options_style = '';
                            }

                            html += '<div class="checkbox etoMoreOptionToggle">' +
                                      '<label for="etoRoute2MoreOptionToggle">' +
                                        '<input type="checkbox" id="etoRoute2MoreOptionToggle" onclick="$(\'.etoRoute2MoreOptionToggleContainer\').toggle();" '+ more_options_checked +'>' +
                                        '<span class="cr"><i class="cr-icon ion-ios-checkmark-empty"></i></span> ' + etoLang('bookingField_MoreOption') +
                                      '</label>' +
                                    '</div>';
                            html += '<div class="etoMoreOptionContainer etoRoute2MoreOptionToggleContainer" '+ more_options_style +'>';

                              html += '<div class="etoChildSeatsContainer" id="etoRoute2ChildSeatsToggleMain">';
                                html += '<div class="checkbox etoChildSeatsToggle">' +
                                          '<label for="etoRoute2ChildSeatsToggle">' +
                                            '<input type="checkbox" id="etoRoute2ChildSeatsToggle" onclick="$(\'.etoRoute2ChildSeatsContainer\').toggle();">' +
                                            '<span class="cr"><i class="cr-icon ion-ios-checkmark-empty"></i></span> ' + etoLang('bookingField_ChildSeatsNeeded') +
                                          '</label>' +
                                        '</div>';
                                html += '<div class="etoVehicleChildSeatsOptionsContainer etoRoute2ChildSeatsContainer clearfix" style="display:none;">';
                                  html += etoCreate('loader', 'Route2BabySeats', '');
                                  html += etoCreate('loader', 'Route2ChildSeats', '');
                                  html += etoCreate('loader', 'Route2InfantSeats', '');
                                  // html += etoCreate('clear');
                                html += '</div>';
                              html += '</div>';

                              html += etoCreate('clear');
                              html += etoCreate('items', 'Route2Items', ''); // etoLang('ROUTE_ITEMS')

                            html += '</div>';
                          html += '</div>';

                          html += '<div class="eto-v2-section eto-v2-section-box eto-v2-section-journey-details">';
                            html += etoCreate('loader', 'Route2JourneyDetails', '');
                            // html += '<div class="etoJourneyTitle">' + etoLang('TITLE_JOURNEY_FROM') + '</div>';
                            html += etoCreate('loader', 'Route2JourneyFrom', '');
                            html += etoCreate('input', 'Route2AddressStartComplete', etoLang('ROUTE_ADDRESS_START'), {
                                placeholder: parseInt(etoData.request.config.booking_required_address_complete_from) == 1 ? '' : '('+ etoLang('bookingOptional') +')'
                            });
                            html += etoCreate('input', 'Route2FlightNumber', etoLang('ROUTE_FLIGHT_NUMBER'));
                            html += '<div class="etoFlightLandingTimeSection">';
                                html += etoCreate('time', 'Route2FlightLandingTime', etoLang('ROUTE_FLIGHT_LANDING_TIME'));
                            html += '</div>';
                            html += etoCreate('input', 'Route2DepartureCity', etoLang('ROUTE_DEPARTURE_CITY'));
                            html += etoCreate('label', 'Route2MeetingPoint', etoLang('ROUTE_MEETING_POINT')); //, null, etoLang('ROUTE_MEETING_POINT_INFO')
                            html += '<div class="etoRoute2WaitingTimeSection">';
                                html += etoCreate('waiting_time', 'Route2WaitingTime', etoLang('ROUTE_WAITING_TIME'));
                            html += '</div>';
                            // html += etoCreate('checkbox', 'Route2MeetAndGreet', etoLang('ROUTE_MEET_AND_GREET'));
                            html += etoCreate('loader', 'Route2WaypointsComplete', '');
                            // html += '<div class="etoJourneyTitle">' + etoLang('TITLE_JOURNEY_TO') + '</div>';
                            html += etoCreate('loader', 'Route2JourneyTo', '');
                            html += etoCreate('input', 'Route2AddressEndComplete', etoLang('ROUTE_ADDRESS_END'), {
                                placeholder: parseInt(etoData.request.config.booking_required_address_complete_to) == 1 ? '' : '('+ etoLang('bookingOptional') +')'
                            });
                            html += etoCreate('input', 'Route2DepartureFlightNumber', etoLang('ROUTE_DEPARTURE_FLIGHT_NUMBER'));
                            html += '<div class="etoDepartureFlightTimeSection">';
                                html += etoCreate('time', 'Route2DepartureFlightTime', etoLang('ROUTE_DEPARTURE_FLIGHT_TIME'));
                            html += '</div>';
                            html += etoCreate('input', 'Route2DepartureFlightCity', etoLang('ROUTE_DEPARTURE_FLIGHT_CITY'));
                            html += etoCreate('charges', 'Route2ExtraCharges', etoLang('ROUTE_EXTRA_CHARGES'));
                            html += etoCreate('price', 'Route2TotalPrice', etoLang('ROUTE_TOTAL_PRICE'));
                          html += '</div>';

                          html += '<div class="eto-v2-section eto-v2-section-box eto-v2-section-comments '+ (etoData.request.config.booking_show_requirements ? '' : 'hidden') +'">';
                            html += etoCreate('textarea', 'Route2Requirements', etoLang('ROUTE_REQUIREMENTS'), {
                                placeholder: '('+ etoLang('bookingOptional') +')'
                            }, etoLang('ROUTE_REQUIREMENTS_INFO'));

                            html += etoCreate('clear');
                            if(etoLang('STEP3_INFO1') && etoLang('STEP3_INFO1') != 'STEP3_INFO1') {
                                html += '<div class="etoLagguageInfo">' + etoLang('STEP3_INFO1') + '</div>';
                            }
                          html += '</div>';

                        html += '</div>';
                        // Return - end

                      html += '</div>';
                      html += '<div class="clearfix eto-v2-container eto-v2-container-3">';

                        html += '<div class="eto-v2-section eto-v2-section-payment">';
                          html += '<div class="eto-v2-section-label">' + etoLang('STEP3_SECTION6') + '</div>';

                          var terms = etoLang('TERMS');
                          terms = terms.replace(/\{terms-conditions\}/g, etoData.request.config.url_terms);
                          html += etoCreate('checkbox', 'Terms', terms);

                          html += '<div class="etoDiscountCodeMaster">';
                            html += etoCreate('input', 'DiscountCode', etoLang('DISCOUNT_CODE'));
                          html += '</div>';
                          html += etoCreate('loader', 'DiscountCodeInfo', '');
                          html += etoCreate('clear');

                          html += etoCreate('price', 'TotalPrice', etoLang('TOTAL_PRICE'));
                          html += etoCreate('payment_single', 'Payment', etoLang('PAYMENT_TYPE'));

                          if (etoLang('bookingStep3BottomInfo') != 'bookingStep3BottomInfo') {
                              html += etoLang('bookingStep3BottomInfo');
                          }

                          html += '<div id="etoButtonsContainer">';
                            html += '<div class="etoButtonsInnerContainer">';
                              html += etoCreate('button', 'QuoteStep3Button', etoLang('BUTTON_COMPLETE_QUOTE_STEP3'));
                              html += etoCreate('button', 'SubmitButton', etoLang('BUTTON_COMPLETE_SUBMIT'));
                              html += etoCreate('clear');
                            html += '</div>';
                          html += '</div>';
                        html += '</div>';

                      html += '</div>';
                      html += etoCreate('clear');


                    html += '</div>';
                    html += '</div>';


                    // Map - start
                    html += '<div class="etoMapBoxMainContainer">';
                      html += '<div class="etoRouteReturnSectionContainer etoMapBox">';
                        html += '<div class="etoRoute1SectionContainer" id="etoRoute1Container">';

                          // One-way - start
                          html += '<div class="etoVehicleTabContent etoVehicleTabContentActive">';
                            html += '<div class="etoRoute1MapMaster">';
                              html += '<div class="etoRoute1MapParent">';
                                html += '<div class="etoRoute1MapChild" style="height:0; overflow:hidden;">';
                                  html += etoCreate('map', 'Route1Map');
                                html += '</div>';
                              html += '</div>';
                            html += '</div>';
                          html += '</div>';
                          // One-way - end

                        html += '</div>';
                        html += '<div class="etoRoute2SectionContainer" id="etoRoute2Container">';

                          // Return - start
                          html += '<div class="etoVehicleTabContent">';
                            html += '<div class="etoRoute2MapMaster">';
                              html += '<div class="etoRoute2MapParent">';
                                html += '<div class="etoRoute2MapChild" style="height:0; overflow:hidden;">';
                                  html += etoCreate('map', 'Route2Map');
                                html += '</div>';
                              html += '</div>';
                            html += '</div>';
                          html += '</div>';
                          // Return - end

                        html += '</div>';
                      html += '</div>';
                    html += '</div>';
                    // Map - end

                    html += '</form>';

                } else {

                    // html += '<div id="etoMessageContainer"></div>';

                    html += '<form method="post" action="#" id="etoForm">';
                    html += '<div id="etoStep1Container">';

                      if (etoData.request.config.booking_display_widget_header == 1 &&
                          etoLang('bookingHeading_Step1Mini') != '' &&
                          etoLang('bookingHeading_Step1Mini') != 'bookingHeading_Step1Mini') {
                          html += '<h3 class="etoStep1HeaderMini">' + etoLang('bookingHeading_Step1Mini') + '</h3>';
                      }

                      html += etoCreate('services', 'Services', etoLang('bookingField_Services'));

                      html += '<div class="etoRoutesContainer">';
                        html += '<div id="etoRoute1Container">';
                            html += etoCreate('category', 'Route1CategoryStart', etoLang('bookingField_From'), {
                                placeholder: (ETOBookingType == 'from-airport') ? etoLang('bookingField_SelectAirportPlaceholder') : etoLang('bookingField_FromPlaceholder')
                            });
                            html += etoCreate('loader', 'Route1LocationStart', '');
                            html += etoCreate('loader', 'Route1Waypoints', '');
                            html += '<div id="etoRoute1WaypointsPosition1"></div>';
                            html += etoCreate('category', 'Route1CategoryEnd', etoLang('bookingField_To'), {
                                placeholder: (ETOBookingType == 'to-airport') ? etoLang('bookingField_SelectAirportPlaceholder') : etoLang('bookingField_ToPlaceholder')
                            });
                            html += etoCreate('loader', 'Route1LocationEnd', '');
                            html += etoCreate('services_duration', 'ServicesDuration', etoLang('bookingField_ServicesDuration'));
                            html += etoCreate('input', 'Route1Date', etoLang('ROUTE_DATE'), {
                                fieldClass: 'eto-v2-field-no-label',
                            });
                            html += '<div id="etoRoute1WaypointsPosition2"></div>';

                            if (etoData.request.config.booking_show_preferred) {
                                html += etoCreate('preferred');
                            }

                            html += '<div class="etoWaypointsAddButtonContainer">';
                            html += '<a href="#" id="etoRoute1WaypointsButton" class="etoWaypointsAddButton" onclick="return false;" title="' + etoLang('ROUTE_WAYPOINTS') + '"><span class="ion-plus"></span> ' + etoLang('ROUTE_WAYPOINTS') + '</a>';
                              html += '<a href="#" id="etoRoute1SwapLocationsButton" class="etoSwapLocationsButton" onclick="return false;" title="' + etoLang('ROUTE_SWAP_LOCATIONS') + '"><span class="ion-arrow-swap"></span></a>';
                              html += etoCreate('return', 'RouteReturn', etoLang('ROUTE_RETURN'));
                              html += etoCreate('clear');
                            html += '</div>';
                        html += '</div>';

                        html += '<div id="etoRoute2Container">';
                          html += etoCreate('category', 'Route2CategoryStart', etoLang('bookingField_From'), {
                              placeholder: (ETOBookingType == 'to-airport') ? etoLang('bookingField_SelectAirportPlaceholder') : etoLang('bookingField_FromPlaceholder')
                          });
                          html += etoCreate('loader', 'Route2LocationStart', '');
                          html += etoCreate('loader', 'Route2Waypoints', '');
                          html += '<div id="etoRoute2WaypointsPosition1"></div>';
                          html += etoCreate('category', 'Route2CategoryEnd', etoLang('bookingField_To'), {
                              placeholder: (ETOBookingType == 'from-airport') ? etoLang('bookingField_SelectAirportPlaceholder') : etoLang('bookingField_ToPlaceholder')
                          });
                          html += etoCreate('loader', 'Route2LocationEnd', '');
                          html += etoCreate('input', 'Route2Date', etoLang('ROUTE_DATE'), {
                              fieldClass: 'eto-v2-field-no-label',
                          });
                          html += '<div id="etoRoute2WaypointsPosition2"></div>';

                          html += '<div class="etoWaypointsAddButtonContainer">';
                              html += '<a href="#" id="etoRoute2WaypointsButton" class="etoWaypointsAddButton" onclick="return false;" title="' + etoLang('ROUTE_WAYPOINTS') + '"><span class="ion-plus"></span> ' + etoLang('ROUTE_WAYPOINTS') + '</a>';
                              html += '<a href="#" id="etoRoute2SwapLocationsButton" class="etoSwapLocationsButton" onclick="return false;" title="' + etoLang('ROUTE_SWAP_LOCATIONS') + '"><span class="ion-arrow-swap"></span></a>';
                              html += etoCreate('clear');
                          html += '</div>';
                        html += '</div>';

                        html += '<div id="etoButtonsContainer">';
                          html += '<div class="etoButtonsInnerContainer">';
                            html += etoCreate('button', 'SubmitButton', etoLang('BUTTON_MINIMAL_SUBMIT'), {
                                'cls': 'btn-primary btn-block',
                                'icon': 'fa fa-arrow-right'
                            });
                            html += etoCreate('button', 'ResetButton', etoLang('BUTTON_MINIMAL_RESET'), {
                                'cls': 'btn-link'
                            });
                            html += etoCreate('clear');
                          html += '</div>';
                        html += '</div>';
                        html += etoCreate('clear');

                      html += '</div>';

                    html += '</div>';
                    html += '</form>';
                }

                html += '</div>';

                $('#' + etoData.mainContainer).html(html);

                // Widget update
                if (etoData.layout == 'minimal') {
                    // Move language switcher
                    // $('#etoStep1Container .etoRoutesContainer').before($('.language-switcher-booking-widget'));

                    if(etoData.request.config.locale_switcher_style == 'dropdown' && $('.etoStep1HeaderMini').length > 0) {
                        $('.etoStep1HeaderMini').append($('.language-switcher'));
                    }
                    else {
                        $('#etoStep1Container').before($('.language-switcher-booking-widget'));
                    }
                }
                else {
                    if(etoData.request.config.locale_switcher_style == 'dropdown') {
                        $('.v2-steps-lang').append($('.language-switcher'));
                    }
                }

                if (etoData.request.config.booking_service_display_mode != 'tabs') {
                    $('#etoStep1Container .etoRoutesContainer #etoRoute1Container').prepend($("#etoServicesContainer"));
                }

                function widgetLayout() {
                    if ($('#etoForm').width() >= 920) {
                        $('#etoForm').addClass('eto-v2-form-horizontal');
                        $('#etoForm').removeClass('eto-v2-form-vertical');

                        $('#etoRoute1DateContainer').after($("#etoButtonsContainer"));

                        if( $("#etoRoute1WaypointsPosition2 #etoRoute1WaypointsLoader").length <= 0 ) {
                            $("#etoRoute1WaypointsPosition2").append($('#etoRoute1WaypointsLoader'));
                        }
                        if( $("#etoRoute2WaypointsPosition2 #etoRoute2WaypointsLoader").length <= 0 ) {
                            $("#etoRoute2WaypointsPosition2").append($('#etoRoute2WaypointsLoader'));
                        }
                    }
                    else {
                        $('#etoForm').removeClass('eto-v2-form-horizontal');
                        $('#etoForm').addClass('eto-v2-form-vertical');

                        $('.etoRoutesContainer').append($("#etoButtonsContainer"));

                        if( $("#etoRoute1WaypointsPosition1 #etoRoute1WaypointsLoader").length <= 0 ) {
                            $("#etoRoute1WaypointsPosition1").append($('#etoRoute1WaypointsLoader'));
                        }
                        if( $("#etoRoute2WaypointsPosition1 #etoRoute2WaypointsLoader").length <= 0 ) {
                            $("#etoRoute2WaypointsPosition1").append($('#etoRoute2WaypointsLoader'));
                        }
                    }
                }

                if (etoData.layout == 'minimal') {
                    $(window).resize(function() {
                        widgetLayout();
                    });

                    $(document).ready(function() {
                      // setTimeout(function() {
                        widgetLayout();
                      // }, 0);
                    });
                }

                if (etoData.request.config.booking_display_return_journey == 1) {
                  $('#etoRouteReturnContainer').removeClass('hidden');
                }
                else {
                  $('#etoRouteReturnContainer').addClass('hidden');
                }

                if (etoData.request.config.booking_display_via == 1) {
                  $('.etoWaypointsAddButton').removeClass('hidden');
                }
                else {
                  $('.etoWaypointsAddButton').addClass('hidden');
                }

                if (etoData.request.config.booking_display_swap == 1) {
                  $('.etoSwapLocationsButton').removeClass('hidden');
                }
                else {
                  $('.etoSwapLocationsButton').addClass('hidden');
                }


                if (etoData.layout != 'minimal') {
                    // Toggle journey details
                    $('.etoMapStyle2Button').toggle(
                        function() {
                            $(this).closest('.etoMapStyle2').find('.etoMapStyle2Directions').hide('fast');
                            $(this).html('<i class="fa fa-info"></i>');
                        },
                        function() {
                            $(this).closest('.etoMapStyle2').find('.etoMapStyle2Directions').show('fast');
                            $(this).html('<i class="fa fa-times"></i>');
                        }
                    );

                    // Map buttons - oneway
                    $('.etoRoute1MapBtnShow').click(function(event) {
                        etoData.booking.route1.mapOpened = 1;
                        embedMap(
                            'etoRoute1Map',
                            etoData.booking.route1.address.start,
                            etoData.booking.route1.address.end,
                            etoData.booking.route1.waypoints
                        );
                        // $('.etoRoute1MapChild').show();
                        $('.etoRoute1MapChild').css({
                            height: 'auto'
                        });
                        $('.etoRoute1MapBtnShow').hide();
                        $('.etoRoute1MapBtnHide').show();

                        if (etoData.isPageLoaded == 1) {
                          etoScrollToTop($('.etoMapBox').offset().top);
                        }
                        event.preventDefault();
                    });

                    $('.etoRoute1MapBtnHide').click(function(event) {
                        etoData.booking.route1.mapOpened = 0;
                        // $('.etoRoute1MapChild').hide();
                        $('.etoRoute1MapChild').css({
                            height: '0'
                        });
                        $('.etoRoute1MapBtnShow').show();
                        $('.etoRoute1MapBtnHide').hide();
                        event.preventDefault();
                    });

                    // Map buttons - return
                    $('.etoRoute2MapBtnShow').click(function(event) {
                        etoData.booking.route2.mapOpened = 1;
                        embedMap(
                            'etoRoute2Map',
                            etoData.booking.route2.address.start,
                            etoData.booking.route2.address.end,
                            etoData.booking.route2.waypoints
                        );
                        // $('.etoRoute2MapChild').show();
                        $('.etoRoute2MapChild').css({
                            height: 'auto'
                        });
                        $('.etoRoute2MapBtnShow').hide();
                        $('.etoRoute2MapBtnHide').show();

                        if (etoData.isPageLoaded == 1) {
                          etoScrollToTop($('.etoMapBox').offset().top);
                        }
                        event.preventDefault();
                    });

                    $('.etoRoute2MapBtnHide').click(function(event) {
                        etoData.booking.route2.mapOpened = 0;
                        // $('.etoRoute2MapChild').hide();
                        $('.etoRoute2MapChild').css({
                            height: '0'
                        });
                        $('.etoRoute2MapBtnShow').show();
                        $('.etoRoute2MapBtnHide').hide();
                        event.preventDefault();
                    });

                    // Mobile tabs
                    $('.etoVehicleTabs a').click(function(event) {
                        var hrefVal = $(this).attr('href');
                        $('.etoVehicleTabs li.active').removeClass('active');
                        $('.etoVehicleTabs li a[href="' + hrefVal + '"]').parent('li').addClass('active');

                        $('.etoVehicleTabContentActive').removeClass('etoVehicleTabContentActive');

                        if (hrefVal == '#etoTabRoute2') {
                            $('#etoRoute2Container .etoVehicleTabContent').addClass('etoVehicleTabContentActive');
                        } else {
                            $('#etoRoute1Container .etoVehicleTabContent').addClass('etoVehicleTabContentActive');
                        }

                        // Update map
                        if (etoData.booking.route1.mapOpened) {
                            embedMap(
                                'etoRoute1Map',
                                etoData.booking.route1.address.start,
                                etoData.booking.route1.address.end,
                                etoData.booking.route1.waypoints
                            );
                        }

                        if (etoData.booking.routeReturn == 2) {
                            if (etoData.booking.route2.mapOpened) {
                                embedMap(
                                    'etoRoute2Map',
                                    etoData.booking.route2.address.start,
                                    etoData.booking.route2.address.end,
                                    etoData.booking.route2.waypoints
                                );
                            }
                        }
                        event.preventDefault();
                    });

                    // Open map by default
                    if (parseInt(etoData.request.config.booking_map_open) == 1) {
                        $('.etoRoute1MapBtnShow').click();
                        $('.etoRoute2MapBtnShow').click();
                    }

                    // User - start
                    if (etoData.request.config.login_enable == 1) {
                        $('#etoBookingLoginFormContainer').hide();
                        $('#etoBookingLogoutFormContainer').hide();
                        $('#etoBookingLoginForm').hide();
                        $('#etoBookingRegisterForm').hide();
                        // $('#etoBookingStep3Container').hide();

                        if (etoData.userId > 0) {
                            $('#etoBookingLogoutFormContainer #userNameContainer').html(etoData.userName);
                            $('#etoBookingLogoutFormContainer').show();
                            $('#etoBookingStep3Container').show();
                        } else {
                            $('#etoBookingLoginFormContainer').show();
                        }


                        // Prefill user date if logged in
                        if (etoData.userId > 0 && etoData.request.config.booking_account_autocompletion) {
                            if ($('#etoContactTitle').val() == '') {
                                $('#etoContactTitle').val(etoData.booking.contactTitle);
                            }
                            if ($('#etoContactName').val() == '') {
                                $('#etoContactName').val(etoData.booking.contactName);
                            }
                            if ($('#etoContactEmail').val() == '') {
                                $('#etoContactEmail').val(etoData.booking.contactEmail);
                            }
                            if ($('#etoContactMobile').val() == '') {
                                $('#etoContactMobile').val(etoData.booking.contactMobile);
                            }
                        }


                        // Checkout type
                        $('#etoBookingCheckoutType input[name=checkoutType]').change(function() {
                            $('#etoBookingLoginForm').hide();
                            $('#etoBookingRegisterForm').hide();
                            $('#etoBookingStep3Container').hide();
                            $('.etoMapBoxMainContainer').hide();

                            var checkoutType = $(this).val();
                            if (checkoutType == 2) {
                                $('#etoBookingLoginForm').show();
                            } else if (checkoutType == 1) {
                                $('#etoBookingRegisterForm').show();
                            } else {
                                $('#etoBookingStep3Container').show();
                                $('.etoMapBoxMainContainer').show();
                            }
                        });

                        if (etoData.request.config.booking_allow_guest_checkout == 0) {
                            $('#etoBookingCheckoutType .eto-v2-checkout-guest').hide();
                            if (etoData.userId <= 0) {
                                $('#etoBookingCheckoutType input[name=checkoutType][value=2]').attr('checked', true).change();
                            }
                        }

                        // Login form
                        var $form = $('#etoBookingLoginForm');
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

                            data.fv.disableSubmitButtons(false);
                            $('#etoBookingLoginFormContainer #loginButton').removeAttr('disabled').removeClass('disabled');
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
                                $.ajax({
                                    headers: {
                                        'X-CSRF-TOKEN': _token
                                    },
                                    url: etoData.apiURL,
                                    type: 'POST',
                                    data: 'task=user&action=login&' + $('#etoBookingLoginForm').serialize(),
                                    dataType: 'json',
                                    // cache: false,
                                    success: function(response) {
                                        $('#etoBookingLoginFormContainer #loginButton').removeAttr('disabled').removeClass('disabled');

                                        if (response.message) {
                                            var message = response.message;
                                            var msg = '';

                                            if (message.error && message.error.length > 0) {
                                                $.each(message.error, function(key, value) {
                                                    msg += '<div class="alert alert-danger alert-dismissible" role="alert">' +
                                                        '<button type="button" class="close" data-dismiss="alert" aria-label="' + etoLang('button_Close') + '">' +
                                                        '<span aria-hidden="true">&times;</span>' +
                                                        '</button>' + value +
                                                        '</div>';
                                                });
                                            }

                                            if (message.warning && message.warning.length > 0) {
                                                $.each(message.warning, function(key, value) {
                                                    msg += '<div class="alert alert-warning alert-dismissible" role="alert">' +
                                                        '<button type="button" class="close" data-dismiss="alert" aria-label="' + etoLang('button_Close') + '">' +
                                                        '<span aria-hidden="true">&times;</span>' +
                                                        '</button>' + value +
                                                        '</div>';
                                                });
                                            }

                                            if (message.success && message.success.length > 0) {
                                                $.each(message.success, function(key, value) {
                                                    msg += '<div class="alert alert-success alert-dismissible" role="alert">' +
                                                        '<button type="button" class="close" data-dismiss="alert" aria-label="' + etoLang('button_Close') + '">' +
                                                        '<span aria-hidden="true">&times;</span>' +
                                                        '</button>' + value +
                                                        '</div>';
                                                });
                                            }

                                            if (msg != '') {
                                                $('#etoBookingUserMessageContainer').html(msg);
                                            }
                                        }

                                        if (response.success) {
                                            $.ajax({
                                                headers: {
                                                    'X-CSRF-TOKEN': _token
                                                },
                                                url: etoData.apiURL,
                                                type: 'POST',
                                                data: 'task=user&action=get',
                                                dataType: 'json',
                                                // cache: false,
                                                success: function(response2) {
                                                    $('#etoMessageContainer, #etoBookingUserMessageContainer').html('');

                                                    if (response2.message) {
                                                        var message = response2.message;
                                                        var msg = '';

                                                        if (message.error && message.error.length > 0) {
                                                            $.each(message.error, function(key, value) {
                                                                msg += '<div class="alert alert-danger alert-dismissible" role="alert">' +
                                                                    '<button type="button" class="close" data-dismiss="alert" aria-label="' + etoLang('button_Close') + '">' +
                                                                    '<span aria-hidden="true">&times;</span>' +
                                                                    '</button>' + value +
                                                                    '</div>';
                                                            });
                                                        }

                                                        if (message.warning && message.warning.length > 0) {
                                                            $.each(message.warning, function(key, value) {
                                                                msg += '<div class="alert alert-warning alert-dismissible" role="alert">' +
                                                                    '<button type="button" class="close" data-dismiss="alert" aria-label="' + etoLang('button_Close') + '">' +
                                                                    '<span aria-hidden="true">&times;</span>' +
                                                                    '</button>' + value +
                                                                    '</div>';
                                                            });
                                                        }

                                                        if (message.success && message.success.length > 0) {
                                                            $.each(message.success, function(key, value) {
                                                                msg += '<div class="alert alert-success alert-dismissible" role="alert">' +
                                                                    '<button type="button" class="close" data-dismiss="alert" aria-label="' + etoLang('button_Close') + '">' +
                                                                    '<span aria-hidden="true">&times;</span>' +
                                                                    '</button>' + value +
                                                                    '</div>';
                                                            });
                                                        }

                                                        if (msg != '') {
                                                            $('#etoBookingUserMessageContainer').html(msg);
                                                        }
                                                    }

                                                    $('#etoBookingLoginFormContainer').hide();
                                                    $('#etoBookingLogoutFormContainer').show();
                                                    $('#etoBookingStep3Container').show();

                                                    $('#etoBookingLogoutFormContainer #userNameContainer').html(response2.user.name);

                                                    if (etoData.request.config.booking_account_autocompletion) {
                                                        if ($('#etoContactTitle').val() == '') {
                                                            $('#etoContactTitle').val(response2.user.title);
                                                        }
                                                        if ($('#etoContactName').val() == '') {
                                                            $('#etoContactName').val(response2.user.name);
                                                        }
                                                        if ($('#etoContactEmail').val() == '') {
                                                            $('#etoContactEmail').val(response2.user.email);
                                                        }
                                                        if ($('#etoContactMobile').val() == '') {
                                                            $('#etoContactMobile').val(response2.user.mobileNumber);
                                                        }
                                                    }

                                                    etoData.userId = response2.user.id;
                                                    etoData.userDepartments = response2.user.departments;
                                                    $('.eto-v2-departments-container').html(etoCreate('departments', 'ContactDepartment', etoLang('bookingField_Department')));
                                                    etoData.isCompany = response2.user.isCompany;
                                                    etoData.isAccountPayment = response2.user.isAccountPayment;
                                                    etoError();

                                                    if (etoData.request.config.booking_account_discount) {
                                                        etoCheck(1);
                                                    }

                                                    if (etoData.debug) {
                                                        console.log(response2);
                                                    }
                                                },
                                                error: function(response2) {
                                                    etoData.message.push('AJAX error: Init');
                                                }
                                            });

                                        }

                                        etoScrollToTop();

                                        if (etoData.debug) {
                                            console.log(response);
                                        }
                                    },
                                    error: function(response) {
                                        etoData.message.push('AJAX error: Login');
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

                        // Register form
                        var $form = $('#etoBookingRegisterForm');
                        var $isValid = 0;
                        var $isReady = 1;

                        function initFormValidation(enabled) {
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
                                        enabled: enabled,
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
                                        enabled: false,
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
                                        enabled: false,
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
                                                min: etoData.request.config.password_length_min,
                                                max: etoData.request.config.password_length_max,
                                                message: etoLang('userMsg_PasswordLength')
                                                    .replace(/\{passwordLengthMin\}/g, etoData.request.config.password_length_min)
                                                    .replace(/\{passwordLengthMax\}/g, etoData.request.config.password_length_max)
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
                                        enabled: parseInt(etoData.request.config.terms_enable) == 1 ? true : false,
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
                            $('#etoMessageContainer, #etoBookingUserMessageContainer').html('');

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
                            if ($('input[name="profileType"]:checked').val() == 'company') {
                                $('.company-container').show();
                            } else {
                                $('.company-container').hide();
                            }
                        }

                        profileType();

                        $('input[name="profileType"]').change(function() {
                            profileType();
                        });

                        var baseURL = etoData.request.config.url_customer;

                        $form.submit(function(event) {
                            if ($isValid && $isReady) {
                                $.ajax({
                                    headers: {
                                        'X-CSRF-TOKEN': _token
                                    },
                                    url: etoData.apiURL,
                                    type: 'POST',
                                    data: 'task=user&action=register&baseURL=' + baseURL + '&' + $('#etoBookingRegisterForm').serialize(),
                                    dataType: 'json',
                                    // cache: false,
                                    success: function(response) {
                                        if (response.success) {
                                            $('#etoBookingCheckoutType input[name="checkoutType"][value="2"]').attr('checked', true).change();
                                        }

                                        if (response.message) {
                                            var message = response.message;
                                            var msg = '';

                                            if (message.error && message.error.length > 0) {
                                                $.each(message.error, function(key, value) {
                                                    msg += '<div class="alert alert-danger alert-dismissible" role="alert">' +
                                                        '<button type="button" class="close" data-dismiss="alert" aria-label="' + etoLang('button_Close') + '">' +
                                                        '<span aria-hidden="true">&times;</span>' +
                                                        '</button>' + value +
                                                        '</div>';
                                                });
                                            }

                                            if (message.warning && message.warning.length > 0) {
                                                $.each(message.warning, function(key, value) {
                                                    msg += '<div class="alert alert-warning alert-dismissible" role="alert">' +
                                                        '<button type="button" class="close" data-dismiss="alert" aria-label="' + etoLang('button_Close') + '">' +
                                                        '<span aria-hidden="true">&times;</span>' +
                                                        '</button>' + value +
                                                        '</div>';
                                                });
                                            }

                                            if (message.success && message.success.length > 0) {
                                                $.each(message.success, function(key, value) {
                                                    msg += '<div class="alert alert-success alert-dismissible" role="alert">' +
                                                        '<button type="button" class="close" data-dismiss="alert" aria-label="' + etoLang('button_Close') + '">' +
                                                        '<span aria-hidden="true">&times;</span>' +
                                                        '</button>' + value +
                                                        '</div>';
                                                });
                                            }

                                            if (msg != '') {
                                                $('#etoBookingUserMessageContainer').html(msg);
                                            }
                                        }

                                        etoScrollToTop();

                                        if (etoData.debug) {
                                            console.log(response);
                                        }
                                    },
                                    error: function(response) {
                                        etoData.message.push('AJAX error: Register');
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
                        // Register form

                        $('#etoBookingUserModal').modal({
                            show: false
                        })
                        .on('show.bs.modal', function (e) {
                            // setTimeout(function() {
                                etoScrollToTop();
                            // }, 500);
                        });

                        $('#etoBookingLoginFormContainer #passwordButton').click(function(event) {
                            var iframe = '<script data-cfasync="false" src="'+ EasyTaxiOffice.appPath +'/assets/plugins/iframe-resizer/iframeResizer.min.js"></script>';
                            iframe += '<script>$(\'iframe\').iFrameResize({heightCalculationMethod: \'lowestElement\', log: false, targetOrigin: \'*\', checkOrigin: false});</script>';
                            iframe += '<iframe src="' + etoData.customerURL + '?tmpl=component&no_redirect=1#password" width="100%" height="500" frameborder="0" allowtransparency="false"></iframe>';

                            $('#etoBookingUserModal .modal-title').html(etoLang('userButton_LostPassword'));
                            $('#etoBookingUserModal .modal-body').html(iframe);
                            $('#etoBookingUserModal').modal('show');
                            event.preventDefault();
                        });

                        $('#etoBookingLogoutFormContainer #accountButton').click(function(event) {
                            // heightCalculationMethod: \'lowestElement\',
                            var iframe = '<script data-cfasync="false" src="'+ EasyTaxiOffice.appPath +'/assets/plugins/iframe-resizer/iframeResizer.min.js"></script>';
                            iframe += '<script>$(\'iframe\').iFrameResize({log: false, targetOrigin: \'*\', checkOrigin: false});</script>';
                            iframe += '<iframe src="' + etoData.customerURL + '?tmpl=component&no_redirect=1#booking/list" width="100%" height="500" frameborder="0" allowtransparency="false"></iframe>';

                            $('#etoBookingUserModal .modal-title').html(etoLang('userButton_Register'));
                            $('#etoBookingUserModal .modal-body').html(iframe);
                            $('#etoBookingUserModal').modal('show');
                            event.preventDefault();
                        });

                        $('#etoBookingLogoutFormContainer #logoutButton').click(function(event) {
                            var $isReady = 1;

                            if ($isReady) {
                                $.ajax({
                                    headers: {
                                        'X-CSRF-TOKEN': _token
                                    },
                                    url: etoData.apiURL,
                                    type: 'POST',
                                    data: 'task=user&action=logout',
                                    dataType: 'json',
                                    // cache: false,
                                    success: function(response) {
                                        $('#etoBookingLoginFormContainer #loginButton').removeAttr('disabled').removeClass('disabled');

                                        $('#etoMessageContainer, #etoBookingUserMessageContainer').html('');

                                        if (response.success) {
                                            $('#etoBookingCheckoutType input[name="checkoutType"][value="0"]').attr('checked', true).change();
                                        }

                                        if (response.message) {
                                            var message = response.message;
                                            var msg = '';

                                            if (message.error && message.error.length > 0) {
                                                $.each(message.error, function(key, value) {
                                                    msg += '<div class="alert alert-danger alert-dismissible" role="alert">' +
                                                        '<button type="button" class="close" data-dismiss="alert" aria-label="' + etoLang('button_Close') + '">' +
                                                        '<span aria-hidden="true">&times;</span>' +
                                                        '</button>' + value +
                                                        '</div>';
                                                });
                                            }

                                            if (message.warning && message.warning.length > 0) {
                                                $.each(message.warning, function(key, value) {
                                                    msg += '<div class="alert alert-warning alert-dismissible" role="alert">' +
                                                        '<button type="button" class="close" data-dismiss="alert" aria-label="' + etoLang('button_Close') + '">' +
                                                        '<span aria-hidden="true">&times;</span>' +
                                                        '</button>' + value +
                                                        '</div>';
                                                });
                                            }

                                            if (message.success && message.success.length > 0) {
                                                $.each(message.success, function(key, value) {
                                                    msg += '<div class="alert alert-success alert-dismissible" role="alert">' +
                                                        '<button type="button" class="close" data-dismiss="alert" aria-label="' + etoLang('button_Close') + '">' +
                                                        '<span aria-hidden="true">&times;</span>' +
                                                        '</button>' + value +
                                                        '</div>';
                                                });
                                            }

                                            if (msg != '') {
                                                $('#etoBookingUserMessageContainer').html(msg);
                                            }
                                        }

                                        if (response.success) {
                                            $('#etoBookingLoginFormContainer').show();
                                            $('#etoBookingLogoutFormContainer').hide();
                                            // $('#etoBookingCheckoutType input[name="checkoutType"][value="0"]').attr('checked', true).change();

                                            if (etoData.request.config.booking_allow_guest_checkout == 0) {
                                                $('#etoBookingCheckoutType input[name=checkoutType][value=2]').attr('checked', true).change();
                                            }

                                            etoData.userId = 0;
                                            etoData.userDepartments = [];
                                            $('.eto-v2-departments-container').html(etoCreate('departments', 'ContactDepartment', etoLang('bookingField_Department')));
                                            etoData.isCompany = 0;
                                            etoData.isAccountPayment = 0;
                                            etoError();

                                            if (etoData.request.config.booking_account_discount) {
                                                etoCheck(1);
                                            }
                                        }

                                        etoScrollToTop();

                                        if (etoData.debug) {
                                            console.log(response);
                                        }
                                    },
                                    error: function(response) {
                                        etoData.message.push('AJAX error: Logout');
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

                    } else {
                        $('#etoBookingLoginFormContainer').hide();
                        $('#etoBookingLogoutFormContainer').hide();
                    }
                    // User - end

                }

                // Suggestions - start
                if (etoData.enabledTypeahead == 1) {
                    etoTypeahead('Route1CategoryStart');
                    etoTypeahead('Route1CategoryEnd');
                    etoTypeahead('Route2CategoryStart');
                    etoTypeahead('Route2CategoryEnd');
                }
                // Suggestions - end


                // Flight details start
                if (etoData.request.config.services_flightstats_enabled) {
                    var searchURL = EasyTaxiOffice.appPath + '/searchAirports?keyword=%QUERY&'+ Math.random();
                    var searchAirports = new Bloodhound({
                        name: 'searchAirports',
                        initialize: false,
                        datumTokenizer: function(data) {
                            return Bloodhound.tokenizers.whitespace(data.name);
                        },
                        queryTokenizer: Bloodhound.tokenizers.whitespace,
                        remote: {
                            url: searchURL,
                            wildcard: '%QUERY',
                            filter: function(response) {
                                return response;
                            }
                        }
                    });
                    searchAirports.initialize(true);

                    $('#etoRoute1DepartureCity, #etoRoute1DepartureFlightCity, #etoRoute2DepartureCity, #etoRoute2DepartureFlightCity').typeahead({
                        hint: true,
                        highlight: true,
                        minLength: 3
                    }, {
                        name: 'searchAirports',
                        display: 'name',
                        source: searchAirports.ttAdapter(),
                        limit: 100,
                        templates: {
                            suggestion: function(data) {
                                var code = data.fs ? data.fs : data.iata;
                                return '<div class="clearfix"><span class="tt-s-name pull-left">'+ data.name +' ('+ code +')</span></div>';
                            }
                        }
                    });
                }
                // Flight details end


                // start
                $('.address-auto-complete-input').each(function(index) {
                  var autoCompleteInput = document.getElementsByClassName('address-auto-complete-input')[index];
                  var autoCompleteOptions = {
                       bounds: new google.maps.LatLngBounds(
                           new google.maps.LatLng(49.00, -13.00),
                           new google.maps.LatLng(60.00, 3.00)
                       ),
                       componentRestrictions: {
                           country: 'uk'
                       },
                       
                    //   var autoCompleteOptions = {
                    //   bounds: new google.maps.LatLngBounds(
                    //       new google.maps.LatLng(49.00, -13.00),
                    //       new google.maps.LatLng(60.00, 3.00)
                    //   ),
                    //   componentRestrictions: {
                    //       country: 'uk'
                    //   },
                       
                      types: ['geocode']
                  };

                  var autoComplete = new google.maps.places.Autocomplete(autoCompleteInput, autoCompleteOptions);

                  google.maps.event.addListener(autoComplete, 'place_changed', function() {
                      etoCheck();
                  });
                });
                // end


                // Intl phone number
                // https://github.com/jackocnr/intl-tel-input
                if (etoData.layout != 'minimal') {
                    var isMobile = /Android.+Mobile|webOS|iPhone|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

                    $('#etoContactMobile').intlTelInput({
                        // dropdownContainer: isMobile ? $('#etoContactMobile').closest('.etoInnerContainer') : '',
                        dropdownContainer: isMobile ? document.getElementById("etoContactMobileContainer") : '',
                        utilsScript: etoData.appPath +'/assets/plugins/jquery-intl-tel-input/js/utils.js?1515077554',
                        preferredCountries: ['gb'],
                        initialCountry: 'auto',
                        geoIpLookup: function(callback) {
                          $.get('https://ipinfo.io', function() {}, 'jsonp').always(function(resp) {
                            var countryCode = (resp && resp.country) ? resp.country : "";
                            callback(countryCode);
                          });
                        },
                    })
                    .on('change', function() {
                        $(this).intlTelInput('setNumber', $(this).val().replace(/^(00)/g, '+'));
                    });

                    $('#etoLeadPassengerMobile').intlTelInput({
                        // dropdownContainer: isMobile ? $('#etoLeadPassengerMobile').closest('.etoInnerContainer') : '',
                        dropdownContainer: isMobile ? document.getElementById("etoLeadPassengerMobileContainer") : '',
                        utilsScript: etoData.appPath +'/assets/plugins/jquery-intl-tel-input/js/utils.js?1515077554',
                        preferredCountries: ['gb'],
                        initialCountry: 'auto',
                        geoIpLookup: function(callback) {
                          $.get('https://ipinfo.io', function() {}, 'jsonp').always(function(resp) {
                            var countryCode = (resp && resp.country) ? resp.country : "";
                            callback(countryCode);
                          });
                        },
                    })
                    .on('change', function() {
                        $(this).intlTelInput('setNumber', $(this).val().replace(/^(00)/g, '+'));
                    });
                }


                // Date & Time picker - start
                etoDateTimePicker('Route1Date');
                etoDateTimePicker('Route2Date');
                // Date & Time picker - end

                // Default time - start
                var minDateTime = moment(getRoundedDate(etoData.request.config.booking_time_picker_steps));
                // var minDateTime = moment();
                // var currentMinute = minDateTime.get('minute');
                // var newMinute = 0;
                // if (currentMinute >= 45) {
                //     newMinute = 0;
                //     minDateTime.add(1, 'hours');
                // } else if (currentMinute >= 30) {
                //     newMinute = 45;
                // } else if (currentMinute >= 15) {
                //     newMinute = 30;
                // } else if (currentMinute >= 0) {
                //     newMinute = 15;
                // } else {
                //     newMinute = 0;
                // }
                // minDateTime.set('minute', newMinute);
                // minDateTime.set('second', 0);

                if (etoData.request.config.min_booking_time_limit) {
                    minDateTime.add(parseInt(etoData.request.config.min_booking_time_limit), 'hours');
                }
                else {
                    if (!etoData.request.config.booking_time_picker_by_minute) {
                        minDateTime.add(1, 'h');
                    }
                }

                var ghostDate = minDateTime;
                var formatedDate = ghostDate.format('YYYY-MM-DD HH:mm');

                $('#'+ etoData.mainContainer +' #etoRoute1DateGhostDate').data('DateTimePicker').date(ghostDate);
                $('#'+ etoData.mainContainer +' #etoRoute1DateGhostTime').combodate('setValue', ghostDate);
                $('#'+ etoData.mainContainer +' #etoRoute1Date').val(formatedDate);

                $('#'+ etoData.mainContainer +' #etoRoute2DateGhostDate').data('DateTimePicker').date(ghostDate);
                $('#'+ etoData.mainContainer +' #etoRoute2DateGhostTime').combodate('setValue', ghostDate);
                $('#'+ etoData.mainContainer +' #etoRoute2Date').val(formatedDate);

                // if (etoData.request.config.booking_date_picker_style == 1) {
                //   $('#'+ etoData.mainContainer +' #etoRoute1DateGhostDate').combodate('setValue', ghostDate);
                // }
                // $('#'+ etoData.mainContainer +' #etoRoute1DateGhostTimeBox').data('DateTimePicker').date(ghostDate);
                //
                // if (etoData.request.config.booking_date_picker_style == 1) {
                //   $('#'+ etoData.mainContainer +' #etoRoute2DateGhostDate').combodate('setValue', ghostDate);
                // }
                // $('#'+ etoData.mainContainer +' #etoRoute2DateGhostTimeBox').data('DateTimePicker').date(ghostDate);
                // Default time - end

                // Steps - start
                $('#'+ etoData.mainContainer +' #etoStep1Button').click(function() {
                    etoStep(1);
                });

                $('#'+ etoData.mainContainer +' #etoStep2Button').click(function() {
                    etoStep(2);
                });

                $('#'+ etoData.mainContainer +' #etoStep3Button').click(function() {
                    etoStep(3);
                });
                // Steps - end


                // Reset form
                $('#'+ etoData.mainContainer +' #etoResetButton').click(function() {
                    etoData.manualQuote = 0;
                    etoInit();
                });

                // Checkout type
                $('#'+ etoData.mainContainer +' #etoBookingCheckoutType input[type=radio]').change(function() {
                   $('#etoMessageContainer, #etoBookingUserMessageContainer').html('');
                });

                // Booking form
                $('#'+ etoData.mainContainer +' #etoForm input[type=text]').focus(function() {
                    $('.input-group.focus').removeClass('focus');
                    $(this).parents('.input-group').addClass('focus');
                })
                .blur(function() {
                    $(this).parents('.input-group').removeClass('focus');
                });


                // Placeholder
                $('#'+ etoData.mainContainer +' #etoForm').find('input:not([type="submit"]), textarea, select').each(function() {
                    etoUpdateFormPlaceholder(this);
                })
                .bind('change keyup', function() {
                    etoUpdateFormPlaceholder(this);
                });
                // ! Placeholder

                $('body').on('click', '.eto-v2-summary-header-edit', function() {
                    etoData.maxStep = 1;
                    etoStep();
                });

                $('#'+ etoData.mainContainer +' #etoForm select,' +
                  '#'+ etoData.mainContainer +' #etoForm textarea,' +
                  '#'+ etoData.mainContainer +' #etoForm input[type=text],' +
                  '#'+ etoData.mainContainer +' #etoForm input[type=radio],' +
                  '#'+ etoData.mainContainer +' #etoForm input[type=checkbox]').change(function() {
                    var fieldName = $(this).attr('name');
                    etoData.quoteStatus = 0;

                    //console.log('fieldName: ' +fieldName);

                    if (fieldName == 'etoRoute1CategoryStart') {
                        etoCheck();
                        etoCreate('location', 'Route1LocationStart', '', {
                            'type': etoData.booking.route1.category.type.start,
                            'value': etoData.booking.route1.category.start
                        });
                        etoCheck();
                    } else if (fieldName == 'etoRoute1CategoryEnd') {
                        etoCheck();
                        etoCreate('location', 'Route1LocationEnd', '', {
                            'type': etoData.booking.route1.category.type.end,
                            'value': etoData.booking.route1.category.end
                        });
                        etoCheck();
                    } else if (fieldName == 'etoRoute1Date') {
                        etoCheck();
                    } else if (fieldName && (fieldName.indexOf('etoRoute1Vehicle') >= 0)) {

                        if ($('#'+ etoData.mainContainer +' #etoForm').hasClass('etoJourneyTypeScheduled')) {
                            var scheduled = 1;
                        }
                        else {
                            var scheduled = 0;
                        }

                        // Single vehicle - start
                        if (etoData.singleVehicle > 0) {
                            var vehicleId = $(this).attr('vehicle_id');

                            $('#'+ etoData.mainContainer +' #etoRoute1VehicleContainer select.etoVehicleSelect').each(function(key, value) {
                                if (parseInt($(value).val()) > 0) {
                                    $(value).val(0);
                                }
                            });

                            $('#'+ etoData.mainContainer +' #etoRoute1VehicleContainer select[name="etoRoute1Vehicle[' + vehicleId + ']"]').val(1);
                        }
                        // Single vehicle - end

                        etoCheck();

                        etoData.booking.route1.passengers = $('#'+ etoData.mainContainer +' #etoRoute1Passengers option:selected').val();
                        etoData.booking.route1.luggage = $('#'+ etoData.mainContainer +' #etoRoute1Luggage option:selected').val();
                        etoData.booking.route1.handLuggage = $('#'+ etoData.mainContainer +' #etoRoute1HandLuggage option:selected').val();
                        etoData.booking.route1.childSeats = $('#'+ etoData.mainContainer +' #etoRoute1ChildSeats option:selected').val();
                        etoData.booking.route1.babySeats = $('#'+ etoData.mainContainer +' #etoRoute1BabySeats option:selected').val();
                        etoData.booking.route1.infantSeats = $('#'+ etoData.mainContainer +' #etoRoute1InfantSeats option:selected').val();
                        etoData.booking.route1.wheelchair = $('#'+ etoData.mainContainer +' #etoRoute1Wheelchair option:selected').val();

                        etoCreate('amount', 'Route1Passengers', etoLang('ROUTE_PASSENGERS'), etoData.booking.route1.vehicleLimits.passengers);
                        etoCreate('amount', 'Route1Luggage', etoLang('ROUTE_LUGGAGE'), etoData.booking.route1.vehicleLimits.luggage);
                        etoCreate('amount', 'Route1HandLuggage', etoLang('ROUTE_HAND_LUGGAGE'), etoData.booking.route1.vehicleLimits.handLuggage);
                        etoCreate('amount', 'Route1ChildSeats', etoLang('ROUTE_CHILD_SEATS'), etoData.booking.route1.vehicleLimits.childSeats, etoLang('ROUTE_CHILD_SEATS_INFO'));
                        etoCreate('amount', 'Route1BabySeats', etoLang('ROUTE_BABY_SEATS'), etoData.booking.route1.vehicleLimits.babySeats, etoLang('ROUTE_BABY_SEATS_INFO'));
                        etoCreate('amount', 'Route1InfantSeats', etoLang('ROUTE_INFANT_SEATS'), etoData.booking.route1.vehicleLimits.infantSeats, etoLang('ROUTE_INFANT_SEATS_INFO'));
                        etoCreate('amount', 'Route1Wheelchair', etoLang('ROUTE_WHEELCHAIR'), etoData.booking.route1.vehicleLimits.wheelchair, etoLang('ROUTE_WHEELCHAIR_INFO'));

                        if( etoData.booking.route1.vehicleLimits.childSeats > 0 ||
                            etoData.booking.route1.vehicleLimits.babySeats > 0 ||
                            etoData.booking.route1.vehicleLimits.infantSeats > 0 ) {
                            $('#etoRoute1ChildSeatsToggleMain').show();
                        }
                        else {
                            $('#etoRoute1ChildSeatsToggleMain').hide();
                        }

                        if (etoData.request.config.booking_show_preferred) {
                            etoData.booking.route1.passengers = etoData.booking.preferred.passengers;
                            etoData.booking.route1.luggage = etoData.booking.preferred.luggage;
                            etoData.booking.route1.handLuggage = etoData.booking.preferred.handLuggage;
                        }

                        if (etoData.booking.route1.passengers <= etoData.booking.route1.vehicleLimits.passengers) {
                            $('#'+ etoData.mainContainer +' #etoRoute1Passengers').val(etoData.booking.route1.passengers);
                        }
                        if (etoData.booking.route1.luggage <= etoData.booking.route1.vehicleLimits.luggage) {
                            $('#'+ etoData.mainContainer +' #etoRoute1Luggage').val(etoData.booking.route1.luggage);
                        }
                        if (etoData.booking.route1.handLuggage <= etoData.booking.route1.vehicleLimits.handLuggage) {
                            $('#'+ etoData.mainContainer +' #etoRoute1HandLuggage').val(etoData.booking.route1.handLuggage);
                        }
                        if (etoData.booking.route1.childSeats <= etoData.booking.route1.vehicleLimits.childSeats) {
                            $('#'+ etoData.mainContainer +' #etoRoute1ChildSeats').val(etoData.booking.route1.childSeats);
                        }
                        if (etoData.booking.route1.babySeats <= etoData.booking.route1.vehicleLimits.babySeats) {
                            $('#'+ etoData.mainContainer +' #etoRoute1BabySeats').val(etoData.booking.route1.babySeats);
                        }
                        if (etoData.booking.route1.infantSeats <= etoData.booking.route1.vehicleLimits.infantSeats) {
                            $('#'+ etoData.mainContainer +' #etoRoute1InfantSeats').val(etoData.booking.route1.infantSeats);
                        }
                        if (etoData.booking.route1.wheelchair <= etoData.booking.route1.vehicleLimits.wheelchair) {
                            $('#'+ etoData.mainContainer +' #etoRoute1Wheelchair').val(etoData.booking.route1.wheelchair);
                        }

                        //etoData.currentStep += 1;
                        //etoData.displayMessage = 0;
                        //etoData.displayHighlight = 0;
                        //etoData.quoteStatus = 1;
                        //etoCheck(2);

                        $('#etoPassengersGhost').remove();

                        if (scheduled) {
                            var vehicleId = $(this).attr('vehicle_id');

                            var ghost = '';
                            $('#etoRoute1Passengers option').each(function() {
                                if (parseInt($(this).attr('value')) > 0) {
                                    ghost += '<option value="'+ $(this).attr('value') +'">'+ $(this).text() +'</option>';
                                }
                            });
                            if (!ghost) {
                                ghost += '<option value="0">0</option>';
                            }
                            $('#etoRoute1VehicleContainer .etoVehicleContainer' + vehicleId +' .etoVehicleColumn3').append('<select id="etoPassengersGhost">'+ ghost +'</select>');

                            $('#etoPassengersGhost').off('change').on('change', function(e) {
                                $('#etoRoute1Passengers').val($(this).val()).change();
                                e.preventDefault();
                            });

                            $('#etoRoute1Passengers').val(1);
                            $('#etoPassengersGhost').val(1);

                            etoCheck();
                        }
                        else {
                            etoCheck();
                        }

                    } else if (fieldName == 'etoRoute1MeetAndGreet') {
                        if (etoData.request.config.charge_meet_and_greet > 0) {
                            etoCheck(1);
                        } else {
                            etoCheck(0);
                        }
                    } else if (fieldName == 'etoRoute1WaitingTime') {
                        if (etoData.request.config.charge_waiting_time > 0) {
                            etoCheck(1);
                        } else {
                            etoCheck(0);
                        }
                    } else if (fieldName && (fieldName.indexOf('etoRoute1Items') >= 0)) {
                        if ($(this).is('[doquote]')) {
                            etoCheck(1);
                        } else {
                            etoCheck(0);
                        }
                    } else if (fieldName == 'etoRouteReturn') {
                        $('#'+ etoData.mainContainer +' #etoRoute2CategoryStart').val(etoData.booking.route1.category.end);
                        etoCreate('location', 'Route2LocationStart', '', {
                            'type': etoData.booking.route1.category.type.end,
                            'value': etoData.booking.route1.category.end
                        });
                        $('#'+ etoData.mainContainer +' #etoRoute2LocationStart').val(etoData.booking.route1.location.end);

                        $('#'+ etoData.mainContainer +' #etoRoute2CategoryEnd').val(etoData.booking.route1.category.start);
                        etoCreate('location', 'Route2LocationEnd', '', {
                            'type': etoData.booking.route1.category.type.start,
                            'value': etoData.booking.route1.category.start
                        });
                        $('#'+ etoData.mainContainer +' #etoRoute2LocationEnd').val(etoData.booking.route1.location.start);

                        var ghostDate = moment(String($('#'+ etoData.mainContainer +' #etoRoute1Date').val()), 'YYYY-MM-DD HH:mm').add(2, 'hours');
                        var formatedDate = ghostDate.format('YYYY-MM-DD HH:mm');

                        $('#'+ etoData.mainContainer +' #etoRoute2DateGhostDate').data('DateTimePicker').date(ghostDate);
                        $('#'+ etoData.mainContainer +' #etoRoute2DateGhostTime').combodate('setValue', ghostDate);
                        $('#'+ etoData.mainContainer +' #etoRoute2Date').val(formatedDate);

                        // if (etoData.request.config.booking_date_picker_style == 1) {
                        //   $('#'+ etoData.mainContainer +' #etoRoute2DateGhostDate').combodate('setValue', ghostDate);
                        // }

                        if (etoData.enabledTypeahead == 1) {
                            if (etoData.booking.route1.category.type.start == 'address') {
                                var getVal = etoData.booking.route1.location.start;
                            } else {
                                var getVal = $('#'+ etoData.mainContainer +' #etoRoute1LocationStart option[value="' + etoData.booking.route1.location.start + '"]').text();
                            }
                            $('#'+ etoData.mainContainer +' #etoRoute2CategoryEndTypeahead').typeahead('val', getVal);

                            if (etoData.booking.route1.category.type.end == 'address') {
                                var getVal = etoData.booking.route1.location.end;
                            } else {
                                var getVal = $('#'+ etoData.mainContainer +' #etoRoute1LocationEnd option[value="' + etoData.booking.route1.location.end + '"]').text();
                            }
                            $('#'+ etoData.mainContainer +' #etoRoute2CategoryStartTypeahead').typeahead('val', getVal);
                        }

                        if (etoData.booking.route1.waypoints) {
                            $('#'+ etoData.mainContainer +' #etoRoute2WaypointsLoader').html('');

                            etoData.booking.route2.waypoints = [];

                            $.each(etoData.booking.route1.waypoints, function(key, value) {
                                etoData.booking.route2.waypoints.push(value);
                            });

                            if ($.isArray(etoData.booking.route2.waypoints)) {
                                etoData.booking.route2.waypoints.reverse();
                            }

                            $.each(etoData.booking.route2.waypoints, function(key, value) {
                                var fieldId = etoWaypoints('Route2Waypoints');
                                $('#'+ etoData.mainContainer +' #' + fieldId).val(value);

                                if (etoData.enabledTypeahead == 1) {
                                    $('#'+ etoData.mainContainer +' #' + fieldId + 'Typeahead').typeahead('val', value);
                                }
                            });
                        }


                        // PlaceId - start
                        $('#'+ etoData.mainContainer +' #etoRoute2CategoryStartPlaceId').val(etoData.booking.route1.placeId.end);
                        $('#'+ etoData.mainContainer +' #etoRoute2CategoryEndPlaceId').val(etoData.booking.route1.placeId.start);

                        if (etoData.booking.route1.waypointsPlaceId) {
                            etoData.booking.route2.waypointsPlaceId = [];

                            $.each(etoData.booking.route1.waypointsPlaceId, function(key, value) {
                                etoData.booking.route2.waypointsPlaceId.push(value);
                            });

                            if ($.isArray(etoData.booking.route2.waypointsPlaceId)) {
                                etoData.booking.route2.waypointsPlaceId.reverse();
                            }

                            $('#'+ etoData.mainContainer +' input[name*=etoRoute2WaypointsPlaceId]').each(function(key, val) {
                                fieldValue = '';
                                if (etoData.booking.route2.waypointsPlaceId[key]) {
                                    fieldValue = etoData.booking.route2.waypointsPlaceId[key];
                                }
                                $(this).val(fieldValue);
                            });
                        }
                        // PlaceId - end


                        etoCheck();
                    } else if (fieldName == 'etoRoute2CategoryStart') {
                        etoCheck();
                        etoCreate('location', 'Route2LocationStart', '', {
                            'type': etoData.booking.route2.category.type.start,
                            'value': etoData.booking.route2.category.start
                        });
                        etoCheck();
                    } else if (fieldName == 'etoRoute2CategoryEnd') {
                        etoCheck();
                        etoCreate('location', 'Route2LocationEnd', '', {
                            'type': etoData.booking.route2.category.type.end,
                            'value': etoData.booking.route2.category.end
                        });
                        etoCheck();
                    } else if (fieldName == 'etoRoute2Date') {
                        etoCheck();
                    } else if (fieldName && (fieldName.indexOf('etoRoute2Vehicle') >= 0)) {
                        // Single vehicle - start
                        if (etoData.singleVehicle > 0) {
                            var vehicleId = $(this).attr('vehicle_id');

                            $('#'+ etoData.mainContainer +' #etoRoute2VehicleContainer select.etoVehicleSelect').each(function(key, value) {
                                if (parseInt($(value).val()) > 0) {
                                    $(value).val(0);
                                }
                            });

                            $('#'+ etoData.mainContainer +' #etoRoute2VehicleContainer select[name="etoRoute2Vehicle[' + vehicleId + ']"]').val(1);
                        }
                        // Single vehicle - end

                        etoCheck();

                        etoData.booking.route2.passengers = $('#'+ etoData.mainContainer +' #etoRoute2Passengers option:selected').val();
                        etoData.booking.route2.luggage = $('#'+ etoData.mainContainer +' #etoRoute2Luggage option:selected').val();
                        etoData.booking.route2.handLuggage = $('#'+ etoData.mainContainer +' #etoRoute2HandLuggage option:selected').val();
                        etoData.booking.route2.childSeats = $('#'+ etoData.mainContainer +' #etoRoute2ChildSeats option:selected').val();
                        etoData.booking.route2.babySeats = $('#'+ etoData.mainContainer +' #etoRoute2BabySeats option:selected').val();
                        etoData.booking.route2.infantSeats = $('#'+ etoData.mainContainer +' #etoRoute2InfantSeats option:selected').val();
                        etoData.booking.route2.wheelchair = $('#'+ etoData.mainContainer +' #etoRoute2Wheelchair option:selected').val();

                        etoCreate('amount', 'Route2Passengers', etoLang('ROUTE_PASSENGERS'), etoData.booking.route2.vehicleLimits.passengers);
                        etoCreate('amount', 'Route2Luggage', etoLang('ROUTE_LUGGAGE'), etoData.booking.route2.vehicleLimits.luggage);
                        etoCreate('amount', 'Route2HandLuggage', etoLang('ROUTE_HAND_LUGGAGE'), etoData.booking.route2.vehicleLimits.handLuggage);
                        etoCreate('amount', 'Route2ChildSeats', etoLang('ROUTE_CHILD_SEATS'), etoData.booking.route2.vehicleLimits.childSeats, etoLang('ROUTE_CHILD_SEATS_INFO'));
                        etoCreate('amount', 'Route2BabySeats', etoLang('ROUTE_BABY_SEATS'), etoData.booking.route2.vehicleLimits.babySeats, etoLang('ROUTE_BABY_SEATS_INFO'));
                        etoCreate('amount', 'Route2InfantSeats', etoLang('ROUTE_INFANT_SEATS'), etoData.booking.route2.vehicleLimits.infantSeats, etoLang('ROUTE_INFANT_SEATS_INFO'));
                        etoCreate('amount', 'Route2Wheelchair', etoLang('ROUTE_WHEELCHAIR'), etoData.booking.route2.vehicleLimits.wheelchair, etoLang('ROUTE_WHEELCHAIR_INFO'));

                        if( etoData.booking.route2.vehicleLimits.childSeats > 0 ||
                            etoData.booking.route2.vehicleLimits.babySeats > 0 ||
                            etoData.booking.route2.vehicleLimits.infantSeats > 0 ) {
                            $('#etoRoute2ChildSeatsToggleMain').show();
                        }
                        else {
                            $('#etoRoute2ChildSeatsToggleMain').hide();
                        }

                        if (etoData.request.config.booking_show_preferred) {
                            etoData.booking.route2.passengers = etoData.booking.preferred.passengers;
                            etoData.booking.route2.luggage = etoData.booking.preferred.luggage;
                            etoData.booking.route2.handLuggage = etoData.booking.preferred.handLuggage;
                        }

                        if (etoData.booking.route2.passengers <= etoData.booking.route2.vehicleLimits.passengers) {
                            $('#'+ etoData.mainContainer +' #etoRoute2Passengers').val(etoData.booking.route2.passengers);
                        }
                        if (etoData.booking.route2.luggage <= etoData.booking.route2.vehicleLimits.luggage) {
                            $('#'+ etoData.mainContainer +' #etoRoute2Luggage').val(etoData.booking.route2.luggage);
                        }
                        if (etoData.booking.route2.handLuggage <= etoData.booking.route2.vehicleLimits.handLuggage) {
                            $('#'+ etoData.mainContainer +' #etoRoute2HandLuggage').val(etoData.booking.route2.handLuggage);
                        }
                        if (etoData.booking.route2.childSeats <= etoData.booking.route2.vehicleLimits.childSeats) {
                            $('#'+ etoData.mainContainer +' #etoRoute2ChildSeats').val(etoData.booking.route2.childSeats);
                        }
                        if (etoData.booking.route2.babySeats <= etoData.booking.route2.vehicleLimits.babySeats) {
                            $('#'+ etoData.mainContainer +' #etoRoute2BabySeats').val(etoData.booking.route2.babySeats);
                        }
                        if (etoData.booking.route2.infantSeats <= etoData.booking.route2.vehicleLimits.infantSeats) {
                            $('#'+ etoData.mainContainer +' #etoRoute2InfantSeats').val(etoData.booking.route2.infantSeats);
                        }
                        if (etoData.booking.route2.wheelchair <= etoData.booking.route2.vehicleLimits.wheelchair) {
                            $('#'+ etoData.mainContainer +' #etoRoute2Wheelchair').val(etoData.booking.route2.wheelchair);
                        }

                        etoCheck();
                    } else if (fieldName == 'etoRoute2MeetAndGreet') {
                        if (etoData.request.config.charge_meet_and_greet > 0) {
                            etoCheck(1);
                        } else {
                            etoCheck(0);
                        }
                    } else if (fieldName == 'etoRoute2WaitingTime') {
                        if (etoData.request.config.charge_waiting_time > 0) {
                            etoCheck(1);
                        } else {
                            etoCheck(0);
                        }
                    } else if (fieldName && (fieldName.indexOf('etoRoute2Items') >= 0)) {
                        if ($(this).is('[doquote]')) {
                            etoCheck(1);
                        } else {
                            etoCheck(0);
                        }
                    } else if (fieldName == 'etoPayment') {
                        etoData.displayMessage = 1;
                        etoData.displayHighlight = 1;
                        etoData.quoteStatus = 1;
                        if ($isSubmitReady == 1) {
                            etoCheck(2);
                            etoSubmit();
                            etoScrollToTop();
                        }
                    } else if (fieldName == 'etoDiscountCode') {
                        //if( etoData.booking.discountCode != '' ) {
                        etoCheck(1);
                        //}
                        //else {
                        //	etoCheck();
                        //}
                    } else if (fieldName == 'etoServices') {

                        if ($(this).prop('tagName').toLowerCase() == 'input') {
                            etoData.booking.serviceId = parseInt($(this, ':checked').val());
                        }
                        else {
                            etoData.booking.serviceId = parseInt($(this).val());
                        }
                        // console.log($(this).prop('tagName').toLowerCase(), etoData.booking.serviceId);

                        etoService();
                        etoCheck();
                    } else if (fieldName == 'etoServicesDuration') {
                        etoCheck();
                    } else {
                        etoCheck();
                    }
                });


                // SwapLocations - start
                function etoSwapLocations(route) {
                    var cStart = $('#'+ etoData.mainContainer +' #etoRoute'+ route +'CategoryStart').val();
                    var cEnd = $('#'+ etoData.mainContainer +' #etoRoute'+ route +'CategoryEnd').val();

                    var pStart = $('#'+ etoData.mainContainer +' #etoRoute'+ route +'CategoryStartPlaceId').val();
                    var pEnd = $('#'+ etoData.mainContainer +' #etoRoute'+ route +'CategoryEndPlaceId').val();

                    var lStart = $('#'+ etoData.mainContainer +' #etoRoute'+ route +'LocationStart').val();
                    var lEnd = $('#'+ etoData.mainContainer +' #etoRoute'+ route +'LocationEnd').val();

                    var lTStart = $('#'+ etoData.mainContainer +' #etoRoute'+ route +'CategoryStartTypeahead').typeahead('val');
                    var lTEnd = $('#'+ etoData.mainContainer +' #etoRoute'+ route +'CategoryEndTypeahead').typeahead('val');

                    if (route == 1) {
                        var waypoints = etoData.booking.route1.waypoints;
                        var waypointsPlaceId = etoData.booking.route1.waypointsPlaceId;
                    }
                    else {
                        var waypoints = etoData.booking.route2.waypoints;
                        var waypointsPlaceId = etoData.booking.route2.waypointsPlaceId;
                    }

                    $('#'+ etoData.mainContainer +' #etoRoute'+ route +'CategoryStart').val(cEnd).change();
                    $('#'+ etoData.mainContainer +' #etoRoute'+ route +'CategoryEnd').val(cStart).change();

                    $('#'+ etoData.mainContainer +' #etoRoute'+ route +'CategoryStartPlaceId').val(pEnd);
                    $('#'+ etoData.mainContainer +' #etoRoute'+ route +'CategoryEndPlaceId').val(pStart);

                    $('#'+ etoData.mainContainer +' #etoRoute'+ route +'LocationStart').val(lEnd);
                    $('#'+ etoData.mainContainer +' #etoRoute'+ route +'LocationEnd').val(lStart);

                    $('#'+ etoData.mainContainer +' #etoRoute'+ route +'CategoryStartTypeahead').typeahead('val', lTEnd);
                    $('#'+ etoData.mainContainer +' #etoRoute'+ route +'CategoryEndTypeahead').typeahead('val', lTStart);

                    if (waypoints && $.isArray(waypoints)) {
                        $('#'+ etoData.mainContainer +' #etoRoute'+ route +'WaypointsLoader').html('');

                        waypoints.reverse();

                        $.each(waypoints, function(key, value) {
                            var fieldId = etoWaypoints('Route'+ route +'Waypoints');
                            $('#'+ etoData.mainContainer +' #' + fieldId).val(value);

                            if (etoData.enabledTypeahead == 1) {
                                $('#'+ etoData.mainContainer +' #' + fieldId + 'Typeahead').typeahead('val', value);
                            }
                        });
                    }

                    if (waypointsPlaceId && $.isArray(waypointsPlaceId)) {
                        waypointsPlaceId.reverse();

                        $('#'+ etoData.mainContainer +' input[name*=etoRoute'+ route +'WaypointsPlaceId]').each(function(key, val) {
                            fieldValue = '';
                            if (waypointsPlaceId[key]) {
                                fieldValue = waypointsPlaceId[key];
                            }
                            $(this).val(fieldValue);
                        });
                    }

                    etoCheck();
                }

                $('#'+ etoData.mainContainer +' #etoRoute1SwapLocationsButton').click(function() {
                    etoSwapLocations(1);
                });

                $('#'+ etoData.mainContainer +' #etoRoute2SwapLocationsButton').click(function() {
                    etoSwapLocations(2);
                });
                // SwapLocations - end


                // Waypoints - start
                $('#'+ etoData.mainContainer +' #etoRoute1WaypointsButton').click(function() {
                    etoWaypoints('Route1Waypoints');
                    etoData.displayMessage = 0;
                    etoData.displayHighlight = 0;
                    etoCheck();
                });

                $('#'+ etoData.mainContainer +' #etoRoute2WaypointsButton').click(function() {
                    etoWaypoints('Route2Waypoints');
                    etoData.displayMessage = 0;
                    etoData.displayHighlight = 0;
                    etoCheck();
                });
                // Waypoints - end


                // Sortable - start
                // $('#'+ etoData.mainContainer +' #etoRoute1WaypointsLoader').sortable({
                //   stop: function(event, ui) {
                //     etoCheck();
                //   }
                // });
                // $('#'+ etoData.mainContainer +' #etoRoute2WaypointsLoader').sortable({
                //   stop: function(event, ui) {
                //     etoCheck();
                //   }
                // });
                // Sortable - end


                if (etoData.layout != 'minimal') {
                    // Prices - start
                    $('#'+ etoData.mainContainer +' #etoRoute1TotalPriceDisplay').html('<span class="etoEmptyPrice">' + etoLang('ROUTE_TOTAL_PRICE_EMPTY') + '</span>');
                    $('#'+ etoData.mainContainer +' #etoRoute1ExtraChargesContainer').hide();

                    $('#'+ etoData.mainContainer +' #etoRoute2TotalPriceDisplay').html('<span class="etoEmptyPrice">' + etoLang('ROUTE_TOTAL_PRICE_EMPTY') + '</span>');
                    $('#'+ etoData.mainContainer +' #etoRoute2ExtraChargesContainer').hide();

                    $('#'+ etoData.mainContainer +' #etoTotalPriceDisplay').html('<span class="etoEmptyPrice">' + etoLang('TOTAL_PRICE_EMPTY') + '</span>');
                    //   $('#'+ etoData.mainContainer +' #etoTotalPriceContainer').hide();
                    // Prices - end
                }

                $('#'+ etoData.mainContainer +' #etoQuoteStep1Button').click(function() {
                    etoData.manualQuote = 0;
                    etoData.currentStep += 1;
                    etoData.displayMessage = 1;
                    etoData.noVehicleMessage = 1;
                    etoData.displayHighlight = 1;
                    etoData.quoteStatus = 1;
                    etoCheck(2);
                    etoScrollToTop();
                });

                $('#'+ etoData.mainContainer +' #etoQuoteStep2Button').click(function() {
                    etoData.clicked = 1;
                    etoData.currentStep += 1;
                    etoData.displayMessage = 1;
                    etoData.displayHighlight = 0;
                    etoData.quoteStatus = 1;

                    if (etoData.request.config.booking_show_preferred) {
                        $('#'+ etoData.mainContainer +' #etoRoute1Passengers').val(etoData.booking.preferred.passengers);
                        $('#'+ etoData.mainContainer +' #etoRoute1Luggage').val(etoData.booking.preferred.luggage);
                        $('#'+ etoData.mainContainer +' #etoRoute1HandLuggage').val(etoData.booking.preferred.handLuggage);

                        if (etoData.booking.route1.vehicleLimits.wheelchair <= 0) {
                            $('.etoVehicleOtherOptionsContainerR1').addClass('hidden');
                        }
                        else {
                            $('.etoVehicleOtherOptionsContainerR1').removeClass('hidden');
                            $('#'+ etoData.mainContainer).find('#etoRoute1PassengersLoader, #etoRoute1LuggageLoader, #etoRoute1HandLuggageLoader').hide();
                        }

                        $('#'+ etoData.mainContainer +' #etoRoute2Passengers').val(etoData.booking.preferred.passengers);
                        $('#'+ etoData.mainContainer +' #etoRoute2Luggage').val(etoData.booking.preferred.luggage);
                        $('#'+ etoData.mainContainer +' #etoRoute2HandLuggage').val(etoData.booking.preferred.handLuggage);

                        if (etoData.booking.route2.vehicleLimits.wheelchair <= 0) {
                            $('.etoVehicleOtherOptionsContainerR2').addClass('hidden');
                        }
                        else {
                            $('.etoVehicleOtherOptionsContainerR2').removeClass('hidden');
                            $('#'+ etoData.mainContainer).find('#etoRoute2PassengersLoader, #etoRoute2LuggageLoader, #etoRoute2HandLuggageLoader').hide();
                        }
                    }

                    etoCheck(2);
                    etoScrollToTop();
                });

                $('#'+ etoData.mainContainer +' #etoQuoteStep2ButtonHelper1, #'+ etoData.mainContainer +' #etoQuoteStep2ButtonHelper2').click(function() {
                    $('#'+ etoData.mainContainer +' #etoQuoteStep2Button').click();
                });

                $('#'+ etoData.mainContainer +' #etoQuoteStep3Button').click(function() {
                    etoData.displayMessage = 1;
                    etoData.displayHighlight = 1;
                    etoCheck(2);
                    etoScrollToTop();
                });

                $('#'+ etoData.mainContainer +' #etoSubmitButton').click(function() {
                    etoData.displayMessage = 1;
                    etoData.displayHighlight = 1;
                    if ($isSubmitReady == 1) {
                        etoCheck(2);
                        etoSubmit();
                        etoScrollToTop();
                    }
                });

                $('body').on('click', '#'+ etoData.mainContainer +' .etoGoBackButton', function() {
                    etoData.currentStep = 1;
                    etoData.displayMessage = 1;
                    etoData.displayHighlight = 1;
                    etoCheck();
                    etoScrollToTop();
                });

                // Tooltip - start
                $('#'+ etoData.mainContainer +' #etoForm [title]').tooltip({
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
                // Tooltip - end

                etoLoad();
                etoData.isPageLoaded = 1;
            },
            error: function(response) {
                etoData.message.push('AJAX error: Init');
            }
        });
    }

    function etoUpdateFormPlaceholder(that) {
        var fieldInput = $(that),
            field = fieldInput.closest('.eto-v2-field'),
            fieldLabel = field.find('.eto-v2-field-label');
        if( fieldInput.val() || fieldInput.prop('tagName').toLowerCase() == 'select' ) {
            field.addClass('eto-v2-field-has-value');
            fieldLabel.show();
        }
        else {
            field.removeClass('eto-v2-field-has-value');
            fieldLabel.hide();
        }
    }

    function embedMap(id, start, end, waypoints, status) {
        if (status == 'NOT_FOUND') {
            return false;
        }

        if (id == 'etoRoute1Map') {
            var mainObj = etoData.booking.route1;
        } else if (id == 'etoRoute2Map') {
            var mainObj = etoData.booking.route2;
        } else {
            var mainObj = null;
        }

        if (mainObj) {
            var f = mainObj.address.start;
            if (mainObj.placeId.start) {
                f = {
                    'placeId': mainObj.placeId.start
                };
            }

            var t = mainObj.address.end;
            if (mainObj.placeId.end) {
                t = {
                    'placeId': mainObj.placeId.end
                };
            }

            var v = [];
            $.each(mainObj.waypoints, function(key, value) {
                var temp = value;
                if (mainObj.waypointsPlaceId[key]) {
                    temp = {
                        'placeId': mainObj.waypointsPlaceId[key]
                    };
                }
                v.push(temp);
            });

            start = f;
            end = t;
            waypoints = v;
            // console.log(id, f, t, v);
        }

        if (id && start && end) {

            if (0) {
                // embed
                var mapParams = '';
                if (etoData.request.config.google_maps_embed_api_key) {
                    mapParams += 'key=' + etoData.request.config.google_maps_embed_api_key;
                }
                mapParams += '&origin=' + start;
                mapParams += '&destination=' + end;

                if (waypoints) {
                    var waypointsList = '';
                    $.each(waypoints, function(key, value) {
                        if (waypointsList) {
                            waypointsList += '|';
                        }
                        waypointsList += value;
                    });
                    if (waypointsList) {
                        mapParams += '&waypoints=' + waypointsList;
                    }
                }

                var avoidList = '';
                if (etoData.request.config.quote_avoid_highways > 0) {
                    if (avoidList) {
                        avoidList += '|';
                    }
                    avoidList += 'highways';
                }
                if (etoData.request.config.quote_avoid_tolls > 0) {
                    if (avoidList) {
                        avoidList += '|';
                    }
                    avoidList += 'tolls';
                }
                if (etoData.request.config.quote_avoid_ferries > 0) {
                    if (avoidList) {
                        avoidList += '|';
                    }
                    avoidList += 'ferries';
                }
                if (avoidList) {
                    mapParams += '&avoid=' + avoidList;
                }

                // mapParams += '&zoom=10';
                mapParams += '&region=gb';
                mapParams += '&mode=driving';
                mapParams += '&units=metric';

                var mapURL = 'https://www.google.com/maps/embed/v1/directions?' + mapParams;
                // console.log(mapURL);
                $('#'+ etoData.mainContainer +' #' + id).html('<iframe width="100%" height="180" frameborder="0" style="border:0" src="' + mapURL + '" allowfullscreen></iframe>');
            } else {
                // js
                var directionsDisplay = new google.maps.DirectionsRenderer();
                var directionsService = new google.maps.DirectionsService();
                var map = new google.maps.Map(document.getElementById(id), {
                    zoom: parseInt(etoData.request.config.booking_map_zoom),
                    draggable: parseInt(etoData.request.config.booking_map_draggable),
                    zoomControl: parseInt(etoData.request.config.booking_map_zoomcontrol),
                    scrollwheel: parseInt(etoData.request.config.booking_map_scrollwheel),
                    center: new google.maps.LatLng(51.5073509, -0.1277583)
                });
                directionsDisplay.setMap(map);

                var request = {
                    origin: start,
                    destination: end,
                    // optimizeWaypoints: true,
                    // drivingOptions: {
                    //     departureTime: new Date(moment().add(15, 'days'))
                    // },
                    provideRouteAlternatives: true,
                    unitSystem: google.maps.UnitSystem.IMPERIAL,
                    travelMode: google.maps.TravelMode.DRIVING
                };

                if (etoData.request.config.google_region_code) {
                    request.region = etoData.request.config.google_region_code;
                }
                else {
                    request.region = 'gb';
                }

                if (etoData.request.config.quote_avoid_highways > 0) {
                    request.avoidHighways = true;
                }

                if (etoData.request.config.quote_avoid_tolls > 0) {
                    request.avoidTolls = true;
                }

                if (etoData.request.config.quote_avoid_ferries > 0) {
                    request.avoidFerries = true;
                }

                var tempWaypoints = [];
                $.each(waypoints, function(key, value) {
                    tempWaypoints.push({
                        location: value,
                        stopover: true
                    });
                });

                if (tempWaypoints.length > 0) {
                    request.waypoints = tempWaypoints;
                    request.optimizeWaypoints = true;
                }

                directionsService.route(request, function(response, status) {
                    if (status == google.maps.DirectionsStatus.OK) {
                        var routeIndex = 0;
                        var routeMinDistance = 0;
                        var routeMinDuration = 0;

                        // Find shortes - start
                        if (response.routes.length > 0 && etoData.request.config.quote_enable_shortest_route > 0) {
                            for (var i = 0; i < response.routes.length; i++) {
                                var route = response.routes[i];
                                var distance = 0;
                                var duration = 0;
                                for (var j = 0; j < route.legs.length; j++) {
                                    distance += route.legs[j].distance.value;
                                    duration += route.legs[j].duration.value;
                                }
                                // Distance
                                if ((routeMinDistance >= distance || routeMinDistance == 0) && etoData.request.config.quote_enable_shortest_route == 1) {
                                    routeMinDistance = distance;
                                    routeIndex = i;
                                }
                                // Duration
                                if ((routeMinDuration >= duration || routeMinDuration == 0) && etoData.request.config.quote_enable_shortest_route == 2) {
                                    routeMinDuration = duration;
                                    routeIndex = i;
                                }
                            }
                        }
                        // Find shortes - end

                        var route = response.routes[routeIndex];
                        google.maps.event.trigger(document.getElementById(id), 'resize');

                        response.routes = [];
                        response.routes[0] = route;
                        directionsDisplay.setDirections(response);
                    }
                });
            }
        }
    }

    function etoTypeahead(name) {
        // if( etoData.debug ){ console.log('Typeahead'); }
        var url = etoData.apiURL;
        url += url.indexOf('?') < 0 ? '?' : '&';

        var pacontainer = '';

        // Force airport suggestions only
        var forceSelectionType = 'none';

        if (ETOBookingType == 'from-airport' && (name == 'Route1CategoryStart' || name == 'Route2CategoryEnd')) {
            etoData.request.config.autocomplete_force_selection = 1;
            forceSelectionType = 'airport';
        }
        else if (ETOBookingType == 'to-airport' && (name == 'Route1CategoryEnd' || name == 'Route2CategoryStart')) {
            etoData.request.config.autocomplete_force_selection = 1;
            forceSelectionType = 'airport';
        }

        if (!ETOBookingType) {
            forceSelectionType = '';
        }

        if (etoData.serviceParams.type == 'scheduled') {
            var searchURL = url + 'task=scheduled_locations&search=%QUERY&'+ Math.random();
        }
        else {
            var searchURL = url + 'task=locations&search=%QUERY&pacontainer=' + pacontainer; // +'&'+ Math.random()

            if (name == 'Route1CategoryStart' || name == 'Route2CategoryStart') {
                searchURL += '&searchType=from';
            }
            else if (name == 'Route1CategoryEnd' || name == 'Route2CategoryEnd') {
                searchURL += '&searchType=to';
            }

            if (name == 'Route2CategoryStart' || name == 'Route2CategoryEnd') {
                searchURL += '&searchReturn=yes';
            }
        }

        // https://github.com/corejavascript/typeahead.js
        var locations = new Bloodhound({
            name: 'locations',
            initialize: false,
            datumTokenizer: function(data) {
                return Bloodhound.tokenizers.whitespace(data.name);
            },
            queryTokenizer: Bloodhound.tokenizers.whitespace,
            remote: {
                url: searchURL,
                wildcard: '%QUERY',
                replace: function() {
                    var search = $('#'+ etoData.mainContainer +' #eto'+ name +'Typeahead').val();

                    if (etoData.serviceParams.type == 'scheduled') {
                        var searchURL = url + 'task=scheduled_locations&search='+ encodeURIComponent(search) +'&'+ Math.random();

                        if (name == 'Route1CategoryStart') {
                            searchURL += '&searchType=from&searchValue='+ $('#'+ etoData.mainContainer +' #etoRoute1CategoryEndTypeahead').val();
                        }
                        else if (name == 'Route1CategoryEnd') {
                            searchURL += '&searchType=to&searchValue='+ $('#'+ etoData.mainContainer +' #etoRoute1CategoryStartTypeahead').val();
                            console.log('checking_part',searchURL)
                        }
                    }
                    else {
                        var searchURL = url + 'task=locations&search='+ encodeURIComponent(search) +'&pacontainer='+ encodeURIComponent(pacontainer);
                        if (forceSelectionType) {
                            searchURL += '&forceSelectionType=' + forceSelectionType;
                        }

                        if (name == 'Route1CategoryStart' || name == 'Route2CategoryStart') {
                            searchURL += '&searchType=from';
                        }
                        else if (name == 'Route1CategoryEnd' || name == 'Route2CategoryEnd') {
                            searchURL += '&searchType=to';
                        }

                        if (name == 'Route2CategoryStart' || name == 'Route2CategoryEnd') {
                            searchURL += '&searchReturn=yes';
                        }
                    }

                    return searchURL;
                },
                filter: function(response) {
                    // console.log(response);
                    locations.showGoogleLogo = response.showGoogleLogo;
                    $.each(response.locations, function(key, value) {
                        if (locations.valueCache.indexOf(value.name) === -1) {
                            locations.valueCache.push(value.name);
                        }
                    });
                    return response.locations;
                }
            }
        });

        locations.showGoogleLogo = 0;
        locations.valueCache = [];
        locations.initialize(true);

        var typeaheadInput = $('#'+ etoData.mainContainer +' #eto'+ name +'Typeahead').typeahead({
            hint: true,
            highlight: true,
            minLength: 0
        }, {
            name: 'locations',
            display: 'name',
            source: locations.ttAdapter(),
            limit: 100,
            templates: {
                header: '<div class="tt-header">' + etoLang('bookingField_TypeAddress') + '</div>',
                footer: '<img class="powered-by-google-locations" src="' + etoData.appPath + '/assets/images/icons/powered-by-google-on-white.png" alt="powered-by-google" />',
                suggestion: function(data) {
                    return '<div class="clearfix ' + (data.cat_type ? 'tt-c-'+data.cat_type : '') + ' ' + (data.cat_featured ? 'tt-c-featured' : '') + '"><span class="tt-s-name pull-left">' + data.name + '</span><span class="tt-s-category pull-right">' + data.cat_icon +'</span></div>';
                },
                notFound: '<div class="tt-empty">' + etoLang('bookingMsg_NoAddressFound') + '</div>'
            }
        });

        // Icons start
        if (name.indexOf('Waypoints') >= 0) {
            var container = '#'+ etoData.mainContainer +' #eto'+ name +'SubContainer';
            var title = $(typeaheadInput).attr('placeholder');
            var btn = '<span class="eto-icon-default ion-ios-location-outline" title="' + title + '"></span>';
            btn += '<span class="etoWaypointsRemoveButton ion-close" id="eto'+ name +'RemoveButton" title="' + etoLang('TITLE_REMOVE_WAYPOINTS') + '"></span>';
        } else {
            var container = '#'+ etoData.mainContainer +' #eto'+ name +'Container';
            var title = $(typeaheadInput).attr('placeholder');
            var btn = '<span class="eto-icon-default ion-ios-location-outline" title="' + title + '"></span>'; // ion-ios-search
        }

        $(container + ' .twitter-typeahead input[type="text"]').wrapAll(
            '<span class="input-group"><span class="typeahead-wrap" />'
        );

        var geoBtn = '<span class="eto-icon-geolocation ion-android-locate" title="' + etoLang('TITLE_GEOLOCATION') + '"></span>';

        if (typeof navigator.geolocation == 'undefined') {
            geoBtn = '';
        }

        $(container + ' .input-group').append(
            '<span class="input-group-addon">' +
            geoBtn +
            '<span class="eto-icon-clear ion-ios-close-empty" style="display:none;" title="' + etoLang('bookingField_ClearBtn') + '"></span>' + btn +
            '</span>'
        );

        if (etoData.request.config.booking_display_geolocation == 1) {
            $('.eto-icon-geolocation').removeClass('hidden');
        }
        else {
            $('.eto-icon-geolocation').addClass('hidden');
        }

        $(container + ' .eto-icon-clear').on('click', function(e, data) {
            $(container + ' .eto-icon-default').show();
            $(container + ' .eto-icon-geolocation').show();
            $(container + ' .eto-icon-clear').hide();
            $(typeaheadInput).typeahead('val', '').focus();
            $(container).find('#eto'+ name +'PlaceId').val('');
            pacontainer = '';
        });

        $(container + ' .eto-icon-geolocation').on('click', function(e, data) {
            e.preventDefault();
            var bThat = this;

            // https://developers.google.com/web/fundamentals/native-hardware/user-location/

            // Check for Geolocation API permissions
            // if (typeof navigator.permissions != 'undefined') {
            //   navigator.permissions.query({name:'geolocation'})
            //     .then(function(permissionStatus) {
            //       console.log('geolocation permission state is ', permissionStatus.state);
            //
            //       permissionStatus.onchange = function() {
            //         console.log('geolocation permission state has changed to ', this.state);
            //       };
            //     });
            // }

            // If the browser supports the Geolocation API
            if (typeof navigator.geolocation == 'undefined') {
                // etoData.message.push(etoLang('GEOLOCATION_UNDEFINED'));
                alert(etoLang('GEOLOCATION_UNDEFINED'));
                return;
            }

            $(bThat).addClass('fa-spin');

            navigator.geolocation.getCurrentPosition(function(position) {
                var geocoder = new google.maps.Geocoder();
                geocoder.geocode({
                    'location': new google.maps.LatLng(position.coords.latitude, position.coords.longitude)
                },
                function(results, status) {
                    $(bThat).removeClass('fa-spin');

                    if (status == google.maps.GeocoderStatus.OK) {
                        $(typeaheadInput).focus().typeahead('val', results[0].formatted_address);
                    }
                    else {
                        // etoData.message.push(etoLang('GEOLOCATION_UNABLE'));
                        alert(etoLang('GEOLOCATION_UNABLE'));
                    }
                });
            },
            function(positionError) {
                $(bThat).removeClass('fa-spin');
                // etoData.message.push(etoLang('GEOLOCATION_ERROR') + ': ' + positionError.message);
                alert(etoLang('GEOLOCATION_ERROR') + "\nError: " + positionError.message);
            },
            {
                enableHighAccuracy: true,
                timeout: 3 * 1000 // seconds
            });
        });

        $(container + ' .eto-icon-default').on('click', function(e, data) {
            $(typeaheadInput).focus();
        });

        $(typeaheadInput).on('keydown change typeahead:render typeahead:change typeahead:selected typeahead:autocompleted', function(e, data) {
            if ($(typeaheadInput).typeahead('val') != '') {
                $(container + ' .eto-icon-default').hide();
                $(container + ' .eto-icon-geolocation').hide();
                $(container + ' .eto-icon-clear').show();
            } else {
                $(container + ' .eto-icon-default').show();
                $(container + ' .eto-icon-geolocation').show();
                $(container + ' .eto-icon-clear').hide();
            }
        })
        .on('typeahead:open', function(e, data) {
            var oldVal = $(typeaheadInput).typeahead('val');
            $(typeaheadInput).typeahead('val', Math.random()).typeahead('val', oldVal);
        })
        .on('keydown', function(e, data) {
            $(container).find('#eto'+ name +'PlaceId').val('');
            pacontainer = '';
        })
        .on('typeahead:open typeahead:change typeahead:asynccancel typeahead:asyncreceive', function(e, data) {
            var force = etoData.request.config.autocomplete_force_selection;
            if (etoData.serviceParams.type == 'scheduled') {
                force = 1;
            }

            if (force == 1) {
                if ($(typeaheadInput).typeahead('val') == '') {
                    $('.tt-empty').closest('.tt-menu').hide();
                }
                else {
                    $('.tt-empty').closest('.tt-menu').show();
                }

                $('.tt-header').hide();
            }
            else {
                $('.tt-empty').closest('.tt-menu').hide();
                $('.tt-header').show();
            }
        })
        .on('typeahead:selected typeahead:autocompleted', function(e, data) {
            //console.log(data);
            if ($('#'+ etoData.mainContainer +' #etoForm').hasClass('etoJourneyTypeScheduled')) {
                etoScheduled();
            }

            // Postcode anywhere - start
            if (data.pa_container) {
                pacontainer = data.pa_container;
                $(typeaheadInput).focus().typeahead('val', data.pa_text);
                // console.log('PA Selected:'+ pacontainer);
                return false;
            }
            pacontainer = '';
            // Postcode anywhere - end

            // Update place id - start
            var place_id = '';
            if (data.place_id) {
                place_id = data.place_id;
            }
            $('#'+ etoData.mainContainer +' #eto'+ name +'PlaceId').val(place_id);
            // Update place id - end

            if (name.indexOf('Waypoints') >= 0) {
                $('#'+ etoData.mainContainer +' #eto' + name).val(data.name);
            } else {
                $('#'+ etoData.mainContainer +' #eto' + name).val(data.cat_id);
                locName = name.replace('Category', 'Location');

                $('#'+ etoData.mainContainer +' #eto'+ name +' option[field_type="address"]').attr('selected', 'selected');
                $('#'+ etoData.mainContainer +' #eto' + name).change();

                var value = data.name;
                if (data.address) {
                    value = data.address;
                }

                $('#'+ etoData.mainContainer +' #eto' + locName).val(value);
            }
            etoCheck();

            $(typeaheadInput).blur();

            if ( navigator && navigator.userAgent && navigator.userAgent.match(/iPhone/i) ) {
                etoScrollToTop($(this).offset().top - 10);
            }
        })
        .on('typeahead:asyncrequest', function() {
            $(container).find('.input-group-addon .typeahead-icon-loading').remove();
            $(container).find('.input-group-addon').prepend('<span class="ion-load-c fa fa-spin typeahead-icon-loading" title="Loading..."></span>');
        })
        .on('typeahead:asynccancel typeahead:asyncreceive', function() {
            $(container).find('.input-group-addon .typeahead-icon-loading').remove();
        })
        .on('typeahead:asyncreceive', function() {
            if (locations.showGoogleLogo > 0) {
                $('.powered-by-google-locations').show();
            } else {
                $('.powered-by-google-locations').hide();
            }
        })
        .on('typeahead:beforeclose', function(e) {
            // keep menu open if input element is still focused https://github.com/twitter/typeahead.js/issues/796
            if ($(e.target).is(':focus') && pacontainer) {
                return false;
            }
        })
        .on('blur', function() {
            // console.log( locations.valueCache );
            //console.log($(this).val());
            if ($.inArray($(this).val(), locations.valueCache) === -1) {
                if (etoData.debug) {
                    console.log('Error : element not in list!');
                }

                if (name.indexOf('Category') >= 0) {
                    $('#'+ etoData.mainContainer +' #eto'+ name +' option[field_type="address"]').attr('selected', 'selected');
                    $('#'+ etoData.mainContainer +' #eto' + name).change();
                }

                locName = name.replace('Category', 'Location');
                $('#'+ etoData.mainContainer +' #eto' + locName).val($(this).val());

                var force = etoData.request.config.autocomplete_force_selection;
                if (etoData.serviceParams.type == 'scheduled') {
                    force = 1;
                }

                if (force == 1) {
                    $(container + ' .eto-icon-default').show();
                    $(container + ' .eto-icon-geolocation').show();
                    $(container + ' .eto-icon-clear').hide();
                    $(typeaheadInput).typeahead('val', '');
                    $(container).find('#eto'+ name +'PlaceId').val('');
                    pacontainer = '';

                    if (name.indexOf('Waypoints') >= 0) {
                        $('#'+ etoData.mainContainer +' #eto' + name).val('');
                    } else {
                        $('#'+ etoData.mainContainer +' #eto' + name).val('');
                        locName = name.replace('Category', 'Location');
                        $('#'+ etoData.mainContainer +' #eto' + locName + 'Loader').html('');
                    }
                }

                etoData.displayMessage = 0;
                etoData.displayHighlight = 0;
                etoCheck();
            }
        });
    }

    function etoWaypoints(fieldName) {

        var html = '';
        var fieldCount = $('#'+ etoData.mainContainer +' #eto' + fieldName + 'Loader .etoWaypointsSubContainer').length;

        if (fieldCount >= etoData.waypointsMax) {
            return false;
        }

        var waypointsCount = etoData.waypointsCount += 1;

        html += '<div class="etoOuterContainer etoWaypointsSubContainer" id="eto' + fieldName + waypointsCount + 'SubContainer' + '">';
        html += '<label class="etoLabel" for="eto' + fieldName + waypointsCount + 'Typeahead" id="eto' + fieldName + waypointsCount + 'TypeaheadLabel">' + etoLang('bookingField_Via') + '</label>';
        html += '<div class="etoInnerContainer">';

        if (etoData.enabledTypeahead == 1) {
            html += '<input name="eto' + fieldName + waypointsCount + 'Typeahead" id="eto' + fieldName + waypointsCount + 'Typeahead" class="form-control typeahead" type="text" placeholder="' + etoLang('bookingField_ViaPlaceholder') + '" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">';
        }

        html += '<input type="hidden" name="eto' + fieldName + 'PlaceId[]" id="eto' + fieldName + waypointsCount + 'PlaceId">';
        html += '<textarea name="eto' + fieldName + '[]" id="eto' + fieldName + waypointsCount + '" class="form-control etoWaypointTextarea"></textarea>';
        html += '<div class="clear"></div>';

        html += '</div>';
        html += '<div class="clear"></div>';
        html += '</div>';

        $('#'+ etoData.mainContainer +' #eto' + fieldName + 'Loader').append(html);

        // Auto complete
        if (etoData.enabledTypeahead == 1) {
            etoTypeahead(fieldName + waypointsCount);
        } else {
            //var autoCompleteInput = $('#'+ etoData.mainContainer +' #eto'+ fieldName + waypointsCount);
            var autoCompleteInput = document.getElementById('eto' + fieldName + waypointsCount);

            var autoCompleteOptions = {
                bounds: new google.maps.LatLngBounds(
                    new google.maps.LatLng(49.00, -13.00),
                    new google.maps.LatLng(60.00, 3.00)
                ),
                componentRestrictions: {
                    country: 'uk'
                },
                types: ['geocode']
            };

            var autoComplete = new google.maps.places.Autocomplete(autoCompleteInput, autoCompleteOptions);

            google.maps.event.addListener(autoComplete, 'place_changed', function() {
                etoCheck();
            });

            $('#'+ etoData.mainContainer +' #eto' + fieldName + waypointsCount).focus().blur().change(function() {
                etoData.quoteStatus = 0;
                etoCheck();
            });
        }

        // Geolocation
        $('#'+ etoData.mainContainer +' #eto' + fieldName + waypointsCount + 'GeolocationButton').click(function() {
            etoGeolocation(fieldName + waypointsCount);
            etoCheck();
        });

        // Remove
        $('#'+ etoData.mainContainer +' #eto' + fieldName + waypointsCount + 'RemoveButton').click(function() {
            if (etoData.enabledTypeahead == 0) {
                google.maps.event.clearListeners(autoComplete);
            }
            $('#'+ etoData.mainContainer +' #eto' + fieldName + waypointsCount + 'SubContainer').find('[data-original-title]').tooltip('destroy');
            $('#'+ etoData.mainContainer +' #eto' + fieldName + waypointsCount + 'SubContainer').remove();
            etoCheck();
        });

        // Tooltip - start
        $('#'+ etoData.mainContainer +' #etoForm [title]').tooltip({
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
        // Tooltip - end

        return 'eto' + fieldName + waypointsCount;
    }

    function etoLoad() {

        if (etoData.debug) {
            console.log('load');
        }

        if (etoData.layout != 'minimal') {
            var minDateTime = moment(getRoundedDate(etoData.request.config.booking_time_picker_steps));
            // var minDateTime = moment();
            // var currentMinute = minDateTime.get('minute');
            // var newMinute = 0;
            // if (currentMinute >= 45) {
            //     newMinute = 0;
            //     minDateTime.add(1, 'hours');
            // } else if (currentMinute >= 30) {
            //     newMinute = 45;
            // } else if (currentMinute >= 15) {
            //     newMinute = 30;
            // } else if (currentMinute >= 0) {
            //     newMinute = 15;
            // } else {
            //     newMinute = 0;
            // }
            // minDateTime.set('minute', newMinute);
            // minDateTime.set('second', 0);

            if (etoData.request.config.min_booking_time_limit) {
                minDateTime.add(parseInt(etoData.request.config.min_booking_time_limit), 'hours');
            }

            var formatedDate = minDateTime.format('YYYY-MM-DD HH:mm');

            var defaultVehicle = [];
            $.each(etoData.request.vehicle, function(key, value) {
                if (parseInt(value['default']) > 0) {
                    defaultVehicle.push({
                        id: parseInt(value.id),
                        amount: parseInt(value['default'])
                    });
                }
            });
        }

        var urlParams = etoData.urlParams;

        if (urlParams.r1cs) {
            etoData.booking.route1.category.start = urlParams.r1cs;
        } else if (etoData.booking.route1.category.start == null) {
            etoData.booking.route1.category.start = $('#'+ etoData.mainContainer +' #etoRoute1CategoryStart option:first').val();
        }

        if (urlParams.r1ce) {
            etoData.booking.route1.category.end = urlParams.r1ce;
        } else if (etoData.booking.route1.category.end == null) {
            etoData.booking.route1.category.end = $('#'+ etoData.mainContainer +' #etoRoute1CategoryEnd option:first').val();
        }

        $('#'+ etoData.mainContainer +' #etoRoute1CategoryStart').val(etoData.booking.route1.category.start);
        $('#'+ etoData.mainContainer +' #etoRoute1CategoryEnd').val(etoData.booking.route1.category.end);

        if (urlParams.r1wp) {
            $.each(urlParams.r1wp, function(key, value) {
                var fieldId = etoWaypoints('Route1Waypoints');
                $('#'+ etoData.mainContainer +' #' + fieldId).val(value);

                if (etoData.enabledTypeahead == 1) {
                    $('#'+ etoData.mainContainer +' #' + fieldId + 'Typeahead').typeahead('val', value);
                }
            });
        }


        // Place id - start
        if (urlParams.r1ps) {
            etoData.booking.route1.placeId.start = urlParams.r1ps;
            $('#'+ etoData.mainContainer +' #etoRoute1CategoryStartPlaceId').val(etoData.booking.route1.placeId.start);
        }

        if (urlParams.r1pe) {
            etoData.booking.route1.placeId.end = urlParams.r1pe;
            $('#'+ etoData.mainContainer +' #etoRoute1CategoryEndPlaceId').val(etoData.booking.route1.placeId.end);
        }

        if (urlParams.r1pwp) {
            etoData.booking.route1.waypointsPlaceId = [];
            $('#'+ etoData.mainContainer +' input[name*=etoRoute1WaypointsPlaceId]').each(function(key, val) {
                fieldValue = '';
                if (urlParams.r1pwp[key]) {
                    fieldValue = urlParams.r1pwp[key];
                }
                $(this).val(fieldValue);
                etoData.booking.route1.waypointsPlaceId.push(fieldValue);
            });
        }
        // Place id - end


        if (urlParams.r1d) {
            etoData.booking.route1.date = urlParams.r1d;
        }

        if (etoData.booking.route1.date != '') {
            var ghostDate = moment(String(etoData.booking.route1.date), 'YYYY-MM-DD HH:mm');
            var formatedDate = ghostDate.format('YYYY-MM-DD HH:mm');

            $('#'+ etoData.mainContainer +' #etoRoute1DateGhostDate').data('DateTimePicker').date(ghostDate);
            $('#'+ etoData.mainContainer +' #etoRoute1DateGhostTime').combodate('setValue', ghostDate);
            $('#'+ etoData.mainContainer +' #etoRoute1Date').val(formatedDate);

            if (etoData.request.config.booking_time_picker_by_minute) {
                var minutes = Math.abs(ghostDate.diff(moment(), 'minutes'));
                var rounded = Math.ceil(minutes / 5) * 5;
                if (rounded < 5) { rounded = 5; }
                $('#'+ etoData.mainContainer +' #etoRoute1DateGhostTimeDropdownMinutes').val(rounded).change();
                // console.log(minutes, rounded, ghostDate);
            }

            // if (etoData.request.config.booking_date_picker_style == 1) {
            //   $('#'+ etoData.mainContainer +' #etoRoute1DateGhostDate').combodate('setValue', ghostDate);
            // }
            // etoData.booking.route1.date = formatedDate;
        }

        if (etoData.layout != 'minimal') {
            etoData.booking.route1.vehicle = defaultVehicle;
            $(etoData.booking.route1.vehicle).each(function(key, value) {
                if (value.amount > 0) {
                    $('#'+ etoData.mainContainer +' select[name*=etoRoute1Vehicle]').filter('[vehicle_id=' + value.id + ']').val(value.amount);
                }
            });

            $('#'+ etoData.mainContainer +' #etoRoute1FlightNumber').val(etoData.booking.route1.flightNumber);
            $('#'+ etoData.mainContainer +' #etoRoute1FlightLandingTime').val(etoData.booking.route1.flightLandingTime);
            $('#'+ etoData.mainContainer +' #etoRoute1DepartureCity').val(etoData.booking.route1.departureCity);

            $('#'+ etoData.mainContainer +' #etoRoute1DepartureFlightNumber').val(etoData.booking.route1.departureFlightNumber);
            $('#'+ etoData.mainContainer +' #etoRoute1DepartureFlightTime').val(etoData.booking.route1.departureFlightTime);
            $('#'+ etoData.mainContainer +' #etoRoute1DepartureFlightCity').val(etoData.booking.route1.departureFlightCity);

            $('#'+ etoData.mainContainer +' #etoRoute1WaitingTime').val(etoData.booking.route1.waitingTime);
            if (etoData.booking.route1.meetAndGreet > 0) {
                $('#'+ etoData.mainContainer +' #etoRoute1MeetAndGreet').attr('checked', true);
                // $('#'+ etoData.mainContainer +' #etoRoute1MeetAndGreet').val(etoData.booking.route1.meetAndGreet);
            } else {
                $('#'+ etoData.mainContainer +' #etoRoute1MeetAndGreet').attr('checked', false);
            }
            $('#'+ etoData.mainContainer +' #etoRoute1Requirements').val(etoData.booking.route1.requirements);

            // Items
            $(etoData.booking.route1.items).each(function(key, value) {
                if (value) {
                    $('#'+ etoData.mainContainer +' #etoRoute1Items_' + value.id).attr('checked', true);
                    $('#'+ etoData.mainContainer +' #etoRoute1Items_' + value.id + '_amount').val(parseInt(value.amount));
                    $('#'+ etoData.mainContainer +' #etoRoute1Items_' + value.id + '_custom').val(String(value.custom));
                    $('#'+ etoData.mainContainer +' #etoRoute1Items_' + value.id + '_type').val(String(value.type));
                }
            });
        }

        if (urlParams.r2cs) {
            etoData.booking.route2.category.start = urlParams.r2cs;
        } else if (etoData.booking.route2.category.start == null) {
            etoData.booking.route2.category.start = $('#'+ etoData.mainContainer +' #etoRoute2CategoryStart option:first').val();
        }

        if (urlParams.r2ce) {
            etoData.booking.route2.category.end = urlParams.r2ce;
        } else if (etoData.booking.route2.category.end == null) {
            etoData.booking.route2.category.end = $('#'+ etoData.mainContainer +' #etoRoute2CategoryEnd option:first').val();
        }

        $('#'+ etoData.mainContainer +' #etoRoute2CategoryStart').val(etoData.booking.route2.category.start);
        $('#'+ etoData.mainContainer +' #etoRoute2CategoryEnd').val(etoData.booking.route2.category.end);

        if (urlParams.r2wp) {
            $.each(urlParams.r2wp, function(key, value) {
                var fieldId = etoWaypoints('Route2Waypoints');
                $('#'+ etoData.mainContainer +' #' + fieldId).val(value);

                if (etoData.enabledTypeahead == 1) {
                    $('#'+ etoData.mainContainer +' #' + fieldId + 'Typeahead').typeahead('val', value);
                }
            });
        }


        // Place id - start
        if (urlParams.r2ps) {
            etoData.booking.route2.placeId.start = urlParams.r2ps;
            $('#'+ etoData.mainContainer +' #etoRoute2CategoryStartPlaceId').val(etoData.booking.route2.placeId.start);
        }

        if (urlParams.r2pe) {
            etoData.booking.route2.placeId.end = urlParams.r2pe;
            $('#'+ etoData.mainContainer +' #etoRoute2CategoryEndPlaceId').val(etoData.booking.route2.placeId.end);
        }

        if (urlParams.r2pwp) {
            etoData.booking.route2.waypointsPlaceId = [];
            $('#'+ etoData.mainContainer +' input[name*=etoRoute2WaypointsPlaceId]').each(function(key, val) {
                fieldValue = '';
                if (urlParams.r2pwp[key]) {
                    fieldValue = urlParams.r2pwp[key];
                }
                $(this).val(fieldValue);
                etoData.booking.route2.waypointsPlaceId.push(fieldValue);
            });
        }
        // Place id - end


        if (urlParams.r2d) {
            etoData.booking.route2.date = urlParams.r2d;
        }

        if (etoData.booking.route2.date != '') {
            var ghostDate = moment(String(etoData.booking.route2.date), 'YYYY-MM-DD HH:mm');
            var formatedDate = ghostDate.format('YYYY-MM-DD HH:mm');

            $('#'+ etoData.mainContainer +' #etoRoute2DateGhostDate').data('DateTimePicker').date(ghostDate);
            $('#'+ etoData.mainContainer +' #etoRoute2DateGhostTime').combodate('setValue', ghostDate);
            $('#'+ etoData.mainContainer +' #etoRoute2Date').val(formatedDate);

            if (etoData.request.config.booking_time_picker_by_minute) {
                var minutes = Math.abs(ghostDate.diff(moment(), 'minutes'));
                var rounded = Math.ceil(minutes / 5) * 5;
                if (rounded < 5) { rounded = 5; }
                $('#'+ etoData.mainContainer +' #etoRoute2DateGhostTimeDropdownMinutes').val(rounded).change();
                // console.log(minutes, rounded, ghostDate);
            }

            // if (etoData.request.config.booking_date_picker_style == 1) {
            //   $('#'+ etoData.mainContainer +' #etoRoute2DateGhostDate').combodate('setValue', ghostDate);
            // }
            // etoData.booking.route2.date = formatedDate;
        }

        if (etoData.layout != 'minimal') {
            etoData.booking.route2.vehicle = defaultVehicle;
            $(etoData.booking.route2.vehicle).each(function(key, value) {
                if (value.amount > 0) {
                    $('#'+ etoData.mainContainer +' select[name*=etoRoute2Vehicle]').filter('[vehicle_id=' + value.id + ']').val(value.amount);
                }
            });

            $('#'+ etoData.mainContainer +' #etoRoute2FlightNumber').val(etoData.booking.route2.flightNumber);
            $('#'+ etoData.mainContainer +' #etoRoute2FlightLandingTime').val(etoData.booking.route2.flightLandingTime);
            $('#'+ etoData.mainContainer +' #etoRoute2DepartureCity').val(etoData.booking.route2.departureCity);

            $('#'+ etoData.mainContainer +' #etoRoute2DepartureFlightNumber').val(etoData.booking.route2.departureFlightNumber);
            $('#'+ etoData.mainContainer +' #etoRoute2DepartureFlightTime').val(etoData.booking.route2.departureFlightTime);
            $('#'+ etoData.mainContainer +' #etoRoute2DepartureFlightCity').val(etoData.booking.route2.departureFlightCity);

            $('#'+ etoData.mainContainer +' #etoRoute2WaitingTime').val(etoData.booking.route2.waitingTime);
            if (etoData.booking.route2.meetAndGreet > 0) {
                $('#'+ etoData.mainContainer +' #etoRoute2MeetAndGreet').attr('checked', true);
                // $('#'+ etoData.mainContainer +' #etoRoute2MeetAndGreet').val(etoData.booking.route2.meetAndGreet);
            } else {
                $('#'+ etoData.mainContainer +' #etoRoute2MeetAndGreet').attr('checked', false);
            }
            $('#'+ etoData.mainContainer +' #etoRoute2Requirements').val(etoData.booking.route2.requirements);

            // Items
            $(etoData.booking.route2.items).each(function(key, value) {
                if (value) {
                    $('#'+ etoData.mainContainer +' #etoRoute2Items_' + value.id).attr('checked', true);
                    $('#'+ etoData.mainContainer +' #etoRoute2Items_' + value.id + '_amount').val(parseInt(value.amount));
                    $('#'+ etoData.mainContainer +' #etoRoute2Items_' + value.id + '_custom').val(String(value.custom));
                    $('#'+ etoData.mainContainer +' #etoRoute2Items_' + value.id + '_type').val(String(value.type));
                }
            });
        }


        if (urlParams.s) {
            etoData.booking.serviceId = parseInt(urlParams.s);
        } else {
            if (etoData.request.services) {
                var selectedVal = 0;
                $.each(etoData.request.services, function(key, value) {
                    if (key == 0) {
                        selectedVal = value.id;
                    }
                    if (value.selected == 1) {
                        selectedVal = value.id;
                    }
                });
                etoData.booking.serviceId = selectedVal;
            }
        }

        if ($('#'+ etoData.mainContainer +' input[name=etoServices]').length > 0 &&
            $('#'+ etoData.mainContainer +' input[name=etoServices]').prop('tagName').toLowerCase() == 'input') {
            $('#'+ etoData.mainContainer +' input[name=etoServices]').filter('[value='+ etoData.booking.serviceId +']').attr('checked', true);
        }
        else {
            $('#'+ etoData.mainContainer +' #etoServices').val(etoData.booking.serviceId);
        }
        // console.log('services load:', $('#'+ etoData.mainContainer +' input[name=etoServices]').prop('tagName').toLowerCase());

        etoService();


        if (urlParams.sd) {
            etoData.booking.serviceDuration = parseInt(urlParams.sd);
        }
        $('#'+ etoData.mainContainer +' #etoServicesDuration').val(etoData.booking.serviceDuration);

        if (etoData.request.config.booking_show_preferred) {
            if (urlParams.maxp) { etoData.booking.preferred.passengers = parseInt(urlParams.maxp); }
            $('#'+ etoData.mainContainer +' #etoPreferredPassengers').val(etoData.booking.preferred.passengers);

            if (urlParams.maxl) { etoData.booking.preferred.luggage = parseInt(urlParams.maxl); }
            $('#'+ etoData.mainContainer +' #etoPreferredLuggage').val(etoData.booking.preferred.luggage);

            if (urlParams.maxh) { etoData.booking.preferred.handLuggage = parseInt(urlParams.maxh); }
            $('#'+ etoData.mainContainer +' #etoPreferredHandLuggage').val(etoData.booking.preferred.handLuggage);
        }

        if (urlParams.r) {
            etoData.booking.routeReturn = urlParams.r;
        }
        $('#'+ etoData.mainContainer +' input[name=etoRouteReturn]').filter('[value=' + etoData.booking.routeReturn + ']').attr('checked', true);


        if (etoData.layout != 'minimal') {
            if (etoData.booking.contactDepartment == null) {
                etoData.booking.contactDepartment = $('#'+ etoData.mainContainer +' #etoContactDepartment option:first').val();
            }
            $('#'+ etoData.mainContainer +' #etoContactDepartment').val(etoData.booking.contactDepartment);

            if (etoData.booking.contactTitle == null) {
                etoData.booking.contactTitle = $('#'+ etoData.mainContainer +' #etoContactTitle option:first').val();
            }
            $('#'+ etoData.mainContainer +' #etoContactTitle').val(etoData.booking.contactTitle);
            $('#'+ etoData.mainContainer +' #etoContactName').val(etoData.booking.contactName);
            $('#'+ etoData.mainContainer +' #etoContactEmail').val(etoData.booking.contactEmail);
            $('#'+ etoData.mainContainer +' #etoContactMobile').val(etoData.booking.contactMobile);

            if (etoData.booking.leadPassengerTitle == null) {
                etoData.booking.leadPassengerTitle = $('#'+ etoData.mainContainer +' #etoLeadPassengerTitle option:first').val();
            }
            $('#'+ etoData.mainContainer +' #etoLeadPassengerTitle').val(etoData.booking.leadPassengerTitle);
            $('#'+ etoData.mainContainer +' #etoLeadPassengerName').val(etoData.booking.leadPassengerName);
            $('#'+ etoData.mainContainer +' #etoLeadPassengerEmail').val(etoData.booking.leadPassengerEmail);
            $('#'+ etoData.mainContainer +' #etoLeadPassengerMobile').val(etoData.booking.leadPassengerMobile);

            $('#'+ etoData.mainContainer +' input[name=etoPayment]').filter('[value=' + etoData.booking.payment + ']').attr('checked', true);
        }

        etoCheck();

        etoCreate('location', 'Route1LocationStart', '', {
            'type': etoData.booking.route1.category.type.start,
            'value': etoData.booking.route1.category.start
        });
        etoCreate('location', 'Route1LocationEnd', '', {
            'type': etoData.booking.route1.category.type.end,
            'value': etoData.booking.route1.category.end
        });

        if (urlParams.r1ls) {
            etoData.booking.route1.location.start = urlParams.r1ls;
        } else if (etoData.booking.route1.location.start == null && String($('#'+ etoData.mainContainer +' #etoRoute1LocationStart').prop('tagName')).toLowerCase() == 'select') {
            etoData.booking.route1.location.start = $('#'+ etoData.mainContainer +' #etoRoute1LocationStart option:first').val();
        }

        // Customer home address
        if (!etoData.booking.route1.location.start && etoData.userHomeAddress) {
            etoData.booking.route1.location.start = etoData.userHomeAddress;
        }

        if (etoData.request.config.booking_force_home_address && etoData.userHomeAddress && etoData.booking.route1.location.start == etoData.userHomeAddress) {
            $('#'+ etoData.mainContainer +' #etoRoute1CategoryStartTypeahead').attr('disabled','disabled');
            $('#'+ etoData.mainContainer +' #etoRoute1CategoryStartContainer .eto-icon-clear').addClass('hidden');
        }

        if (urlParams.r1le) {
            etoData.booking.route1.location.end = urlParams.r1le;
        } else if (etoData.booking.route1.location.end == null && String($('#'+ etoData.mainContainer +' #etoRoute1LocationEnd').prop('tagName')).toLowerCase() == 'select') {
            etoData.booking.route1.location.end = $('#'+ etoData.mainContainer +' #etoRoute1LocationEnd option:first').val();
        }

        $('#'+ etoData.mainContainer +' #etoRoute1LocationStart').val(etoData.booking.route1.location.start);
        $('#'+ etoData.mainContainer +' #etoRoute1LocationEnd').val(etoData.booking.route1.location.end);


        if (etoData.enabledTypeahead == 1) {
            if (etoData.booking.route1.category.type.start == 'address') {
                var getVal = etoData.booking.route1.location.start;
            } else {
                var getVal = $('#'+ etoData.mainContainer +' #etoRoute1LocationStart option[value="' + etoData.booking.route1.location.start + '"]').text();
            }
            $('#'+ etoData.mainContainer +' #etoRoute1CategoryStartTypeahead').typeahead('val', getVal);

            if (etoData.booking.route1.category.type.end == 'address') {
                var getVal = etoData.booking.route1.location.end;
            } else {
                var getVal = $('#'+ etoData.mainContainer +' #etoRoute1LocationEnd option[value="' + etoData.booking.route1.location.end + '"]').text();
            }
            $('#'+ etoData.mainContainer +' #etoRoute1CategoryEndTypeahead').typeahead('val', getVal);
        }


        if (etoData.layout != 'minimal') {
            etoCreate('amount', 'Route1Passengers', etoLang('ROUTE_PASSENGERS'), etoData.booking.route1.vehicleLimits.passengers);
            etoCreate('amount', 'Route1Luggage', etoLang('ROUTE_LUGGAGE'), etoData.booking.route1.vehicleLimits.luggage);
            etoCreate('amount', 'Route1HandLuggage', etoLang('ROUTE_HAND_LUGGAGE'), etoData.booking.route1.vehicleLimits.handLuggage);
            etoCreate('amount', 'Route1ChildSeats', etoLang('ROUTE_CHILD_SEATS'), etoData.booking.route1.vehicleLimits.childSeats, etoLang('ROUTE_CHILD_SEATS_INFO'));
            etoCreate('amount', 'Route1BabySeats', etoLang('ROUTE_BABY_SEATS'), etoData.booking.route1.vehicleLimits.babySeats, etoLang('ROUTE_BABY_SEATS_INFO'));
            etoCreate('amount', 'Route1InfantSeats', etoLang('ROUTE_INFANT_SEATS'), etoData.booking.route1.vehicleLimits.infantSeats, etoLang('ROUTE_INFANT_SEATS_INFO'));
            etoCreate('amount', 'Route1Wheelchair', etoLang('ROUTE_WHEELCHAIR'), etoData.booking.route1.vehicleLimits.wheelchair, etoLang('ROUTE_WHEELCHAIR_INFO'));

            if( etoData.booking.route1.vehicleLimits.childSeats > 0 ||
                etoData.booking.route1.vehicleLimits.babySeats > 0 ||
                etoData.booking.route1.vehicleLimits.infantSeats > 0 ) {
                $('#etoRoute1ChildSeatsToggleMain').show();
            }
            else {
                $('#etoRoute1ChildSeatsToggleMain').hide();
            }

            etoData.booking.route1.passengers = 0;
            etoData.booking.route1.luggage = 0;
            etoData.booking.route1.handLuggage = 0;
            etoData.booking.route1.childSeats = 0;
            etoData.booking.route1.babySeats = 0;
            etoData.booking.route1.infantSeats = 0;
            etoData.booking.route1.wheelchair = 0;

            if (etoData.request.config.booking_show_preferred) {
                etoData.booking.route1.passengers = etoData.booking.preferred.passengers;
                etoData.booking.route1.luggage = etoData.booking.preferred.luggage;
                etoData.booking.route1.handLuggage = etoData.booking.preferred.handLuggage;
            }

            $('#'+ etoData.mainContainer +' #etoRoute1Passengers').val(etoData.booking.route1.passengers);
            $('#'+ etoData.mainContainer +' #etoRoute1Luggage').val(etoData.booking.route1.luggage);
            $('#'+ etoData.mainContainer +' #etoRoute1HandLuggage').val(etoData.booking.route1.handLuggage);
            $('#'+ etoData.mainContainer +' #etoRoute1ChildSeats').val(etoData.booking.route1.childSeats);
            $('#'+ etoData.mainContainer +' #etoRoute1BabySeats').val(etoData.booking.route1.babySeats);
            $('#'+ etoData.mainContainer +' #etoRoute1InfantSeats').val(etoData.booking.route1.infantSeats);
            $('#'+ etoData.mainContainer +' #etoRoute1Wheelchair').val(etoData.booking.route1.wheelchair);
        }

        etoCreate('location', 'Route2LocationStart', '', {
            'type': etoData.booking.route2.category.type.start,
            'value': etoData.booking.route2.category.start
        });
        etoCreate('location', 'Route2LocationEnd', '', {
            'type': etoData.booking.route2.category.type.end,
            'value': etoData.booking.route2.category.end
        });

        if (urlParams.r2ls) {
            etoData.booking.route2.location.start = urlParams.r2ls;
        } else if (etoData.booking.route2.location.start == null && String($('#'+ etoData.mainContainer +' #etoRoute2LocationStart').prop('tagName')).toLowerCase() == 'select') {
            etoData.booking.route2.location.start = $('#'+ etoData.mainContainer +' #etoRoute2LocationStart option:first').val();
        }

        if (urlParams.r2le) {
            etoData.booking.route2.location.end = urlParams.r2le;
        } else if (etoData.booking.route2.location.end == null && String($('#'+ etoData.mainContainer +' #etoRoute2LocationEnd').prop('tagName')).toLowerCase() == 'select') {
            etoData.booking.route2.location.end = $('#'+ etoData.mainContainer +' #etoRoute2LocationEnd option:first').val();
        }

        $('#'+ etoData.mainContainer +' #etoRoute2LocationStart').val(etoData.booking.route2.location.start);
        $('#'+ etoData.mainContainer +' #etoRoute2LocationEnd').val(etoData.booking.route2.location.end);


        if (etoData.enabledTypeahead == 1) {
            if (etoData.booking.route2.category.type.start == 'address') {
                var getVal = etoData.booking.route2.location.start;
            } else {
                var getVal = $('#'+ etoData.mainContainer +' #etoRoute2LocationStart option[value="' + etoData.booking.route2.location.start + '"]').text();
            }
            $('#'+ etoData.mainContainer +' #etoRoute2CategoryStartTypeahead').typeahead('val', getVal);

            if (etoData.booking.route2.category.type.end == 'address') {
                var getVal = etoData.booking.route2.location.end;
            } else {
                var getVal = $('#'+ etoData.mainContainer +' #etoRoute2LocationEnd option[value="' + etoData.booking.route2.location.end + '"]').text();
            }
            $('#'+ etoData.mainContainer +' #etoRoute2CategoryEndTypeahead').typeahead('val', getVal);
        }


        if (etoData.layout != 'minimal') {
            etoCreate('amount', 'Route2Passengers', etoLang('ROUTE_PASSENGERS'), etoData.booking.route2.vehicleLimits.passengers);
            etoCreate('amount', 'Route2Luggage', etoLang('ROUTE_LUGGAGE'), etoData.booking.route2.vehicleLimits.luggage);
            etoCreate('amount', 'Route2HandLuggage', etoLang('ROUTE_HAND_LUGGAGE'), etoData.booking.route2.vehicleLimits.handLuggage);
            etoCreate('amount', 'Route2ChildSeats', etoLang('ROUTE_CHILD_SEATS'), etoData.booking.route2.vehicleLimits.childSeats, etoLang('ROUTE_CHILD_SEATS_INFO'));
            etoCreate('amount', 'Route2BabySeats', etoLang('ROUTE_BABY_SEATS'), etoData.booking.route2.vehicleLimits.babySeats, etoLang('ROUTE_BABY_SEATS_INFO'));
            etoCreate('amount', 'Route2InfantSeats', etoLang('ROUTE_INFANT_SEATS'), etoData.booking.route2.vehicleLimits.infantSeats, etoLang('ROUTE_INFANT_SEATS_INFO'));
            etoCreate('amount', 'Route2Wheelchair', etoLang('ROUTE_WHEELCHAIR'), etoData.booking.route2.vehicleLimits.wheelchair, etoLang('ROUTE_WHEELCHAIR_INFO'));

            if( etoData.booking.route2.vehicleLimits.childSeats > 0 ||
                etoData.booking.route2.vehicleLimits.babySeats > 0 ||
                etoData.booking.route2.vehicleLimits.infantSeats > 0 ) {
                $('#etoRoute2ChildSeatsToggleMain').show();
            }
            else {
                $('#etoRoute2ChildSeatsToggleMain').hide();
            }

            etoData.booking.route2.passengers = 0;
            etoData.booking.route2.luggage = 0;
            etoData.booking.route2.handLuggage = 0;
            etoData.booking.route2.childSeats = 0;
            etoData.booking.route2.babySeats = 0;
            etoData.booking.route2.infantSeats = 0;
            etoData.booking.route2.wheelchair = 0;

            if (etoData.request.config.booking_show_preferred) {
                etoData.booking.route2.passengers = etoData.booking.preferred.passengers;
                etoData.booking.route2.luggage = etoData.booking.preferred.luggage;
                etoData.booking.route2.handLuggage = etoData.booking.preferred.handLuggage;
            }

            $('#'+ etoData.mainContainer +' #etoRoute2Passengers').val(etoData.booking.route2.passengers);
            $('#'+ etoData.mainContainer +' #etoRoute2Luggage').val(etoData.booking.route2.luggage);
            $('#'+ etoData.mainContainer +' #etoRoute2HandLuggage').val(etoData.booking.route2.handLuggage);
            $('#'+ etoData.mainContainer +' #etoRoute2ChildSeats').val(etoData.booking.route2.childSeats);
            $('#'+ etoData.mainContainer +' #etoRoute2BabySeats').val(etoData.booking.route2.babySeats);
            $('#'+ etoData.mainContainer +' #etoRoute2InfantSeats').val(etoData.booking.route2.infantSeats);
            $('#'+ etoData.mainContainer +' #etoRoute2Wheelchair').val(etoData.booking.route2.wheelchair);
        }

        if ($('#'+ etoData.mainContainer +' #etoForm').hasClass('etoJourneyTypeScheduled')) {
            etoScheduled();
        }

        // Display errors
        if (urlParams.s || urlParams.r || urlParams.r1cs || urlParams.r1ce || urlParams.r1ls || urlParams.r1le || urlParams.r1d ||
            urlParams.r2cs || urlParams.r2ce || urlParams.r2ls || urlParams.r2le || urlParams.r2d) {
            etoData.displayMessage = 1;
            etoData.noVehicleMessage = 1;
            etoData.displayHighlight = 1;
            etoData.currentStep = 2;
            etoData.maxStep = 2;
        }

        etoCheck(1);

        $('.language-switcher').removeClass('hidden');
    }

    function etoCheckDepartureFlightTime(route) {
        if (parseInt(etoData.request.config.booking_departure_flight_time_check_enable) &&
            parseInt(etoData.request.config.booking_departure_flight_time_enable) == 1) {
            if (route == 2) {
                var pickupDateTime = etoData.booking.route2.date;
                var flightTime = etoData.booking.route2.departureFlightTime;
            }
            else {
                var pickupDateTime = etoData.booking.route1.date;
                var flightTime = etoData.booking.route1.departureFlightTime;
            }

            var minutesBefore = parseInt(etoData.request.config.booking_departure_flight_time_check_value);

            if (pickupDateTime && flightTime) {
                var mPickupDateTime = moment(String(pickupDateTime), 'YYYY-MM-DD HH:mm');
                var mFlightTime = moment(String(flightTime), 'HH:mm');
                var mFlightDateTime = moment(mPickupDateTime).set({
                    'hour': mFlightTime.get('hour'),
                    'minute': mFlightTime.get('minute')
                });

                if (mPickupDateTime.isAfter(moment(mFlightDateTime).add('minutes', minutesBefore), 'minutes')) {
                    mFlightDateTime.add('day', 1);
                }

                var diffMinutes = mFlightDateTime.diff(mPickupDateTime, 'minutes');
                var diffMinutesAbs = Math.abs(diffMinutes);

                if (diffMinutes < minutesBefore) {
                    var setMinutes = minutesBefore - diffMinutesAbs;
                    var ghostDate = moment(mPickupDateTime).subtract(setMinutes, 'minutes');
                    var formatedDate = ghostDate.format('YYYY-MM-DD HH:mm');

                    $('#'+ etoData.mainContainer +' #etoRoute'+ route +'DateGhostDate').data('DateTimePicker').date(ghostDate);
                    $('#'+ etoData.mainContainer +' #etoRoute'+ route +'DateGhostTime').combodate('setValue', ghostDate);
                    $('#'+ etoData.mainContainer +' #etoRoute'+ route +'Date').val(formatedDate);

                    alert(etoLang('bookingDepartureFlightTimeWarning'));
                }
            }
        }
    }

    function etoCheck(quote) {

        if (etoData.debug) {
            console.log('check ' + quote);
        }

        var route1IsAirport = etoData.booking.route1.isAirport;
        var route1IsAirport2 = etoData.booking.route1.isAirport2;
        var route2IsAirport = etoData.booking.route2.isAirport;
        var route2IsAirport2 = etoData.booking.route2.isAirport2;
        var route1ExcludedRouteAllowed = etoData.booking.route1.excludedRouteAllowed;
        var route2ExcludedRouteAllowed = etoData.booking.route2.excludedRouteAllowed;
        var route1MapOpened = etoData.booking.route1.mapOpened;
        var route2MapOpened = etoData.booking.route2.mapOpened;
        var route1VehicleButtons = etoData.booking.route1.vehicleButtons;
        var route2VehicleButtons = etoData.booking.route2.vehicleButtons;

        // Reset booking - start
        etoData.booking = etoReset();
        // Reset booking - end

        etoData.booking.route1.isAirport = route1IsAirport;
        etoData.booking.route1.isAirport2 = route1IsAirport2;
        etoData.booking.route2.isAirport = route2IsAirport;
        etoData.booking.route2.isAirport2 = route2IsAirport2;
        etoData.booking.route1.excludedRouteAllowed = route1ExcludedRouteAllowed;
        etoData.booking.route2.excludedRouteAllowed = route2ExcludedRouteAllowed;
        etoData.booking.route1.mapOpened = route1MapOpened;
        etoData.booking.route2.mapOpened = route2MapOpened;
        etoData.booking.route1.vehicleButtons = route1VehicleButtons;
        etoData.booking.route2.vehicleButtons = route2VehicleButtons;

        if ($('#'+ etoData.mainContainer +' #etoForm').hasClass('etoJourneyTypeScheduled')) {
            var scheduled = 1;
        }
        else {
            var scheduled = 0;
        }

        if (etoData.userDepartments && etoData.userDepartments.length > 0) {
            $('#etoContactDepartmentContainer').removeClass('hidden');
        }
        else {
            $('#etoContactDepartmentContainer').addClass('hidden');
        }

        if (parseInt(etoData.request.config.booking_waiting_time_enable) == 0 || etoData.manualQuote == 1) {
            $('.etoRoute1WaitingTimeSection').hide();
            $('.etoRoute2WaitingTimeSection').hide();
        } else {
            $('.etoRoute1WaitingTimeSection').show();
            $('.etoRoute2WaitingTimeSection').show();
        }

        if (parseInt(etoData.request.config.booking_flight_landing_time_enable) == 1) {
            $('.etoFlightLandingTimeSection').show();
        } else {
            $('.etoFlightLandingTimeSection').hide();
        }

        if (parseInt(etoData.request.config.booking_departure_flight_time_enable) == 1) {
            $('.etoDepartureFlightTimeSection').show();
        } else {
            $('.etoDepartureFlightTimeSection').hide();
        }

        if (parseInt(etoData.request.config.booking_map_enable) == 0 &&
            parseInt(etoData.request.config.booking_directions_enable) == 0) {
            $('.etoRoute1MapMaster').hide();
            $('.etoRoute2MapMaster').hide();
        } else {
            if (parseInt(etoData.request.config.booking_map_enable) == 0) {
                $('#etoRoute1Map').hide();
                $('#etoRoute2Map').hide();
            }

            if (parseInt(etoData.request.config.booking_directions_enable) == 0) {
                $('#etoRoute1MapDirections').hide();
                $('#etoRoute2MapDirections').hide();
            }
        }

        if (parseInt(etoData.request.config.booking_map_enable) == 1 &&
            parseInt(etoData.request.config.booking_directions_enable) == 1) {
            $('#'+ etoData.mainContainer +' #etoRoute1MapContainer .etoInnerContainer .row > div.col-xs-12').addClass('col-md-6');
            $('#'+ etoData.mainContainer +' #etoRoute2MapContainer .etoInnerContainer .row > div.col-xs-12').addClass('col-md-6');
        } else {
            $('#'+ etoData.mainContainer +' #etoRoute1MapContainer .etoInnerContainer .row > div.col-xs-12').removeClass('col-md-6');
            $('#'+ etoData.mainContainer +' #etoRoute2MapContainer .etoInnerContainer .row > div.col-xs-12').removeClass('col-md-6');
        }


        if ($('#'+ etoData.mainContainer +' input[name=etoServices]').length > 0 &&
            $('#'+ etoData.mainContainer +' input[name=etoServices]').prop('tagName').toLowerCase() == 'input') {
            etoData.booking.serviceId = parseInt($('#'+ etoData.mainContainer +' input[name=etoServices]:checked').val());
        }
        else {
            etoData.booking.serviceId = parseInt($('#'+ etoData.mainContainer +' #etoServices option:selected').val());
        }

        etoData.booking.serviceDuration = parseInt($('#'+ etoData.mainContainer +' #etoServicesDuration option:selected').val());

        if (etoData.request.config.booking_show_preferred) {
            etoData.booking.preferred.passengers = parseInt($('#'+ etoData.mainContainer +' #etoPreferredPassengers option:selected').val());
            etoData.booking.preferred.luggage = parseInt($('#'+ etoData.mainContainer +' #etoPreferredLuggage option:selected').val());
            etoData.booking.preferred.handLuggage = parseInt($('#'+ etoData.mainContainer +' #etoPreferredHandLuggage option:selected').val());
        }

        // Return - start
        etoData.booking.routeReturn = parseInt($('#'+ etoData.mainContainer +' input[name=etoRouteReturn]:checked').val()) ? 2 : 1;

        if (etoData.booking.routeReturn == 2) {
            $('#'+ etoData.mainContainer +' #etoForm').removeClass('etoJourneyTypeOneWay');
            $('#'+ etoData.mainContainer +' #etoForm').addClass('etoJourneyTypeReturn');
            $('#'+ etoData.mainContainer +' .etoRouteReturnSectionContainer').addClass('etoRouteReturnSectionContainerActive');
            // $('#'+ etoData.mainContainer +' .etoRoute1SectionContainer').addClass('col-md-6');
            $('#'+ etoData.mainContainer +' #etoRoute2Container').show();
            $('#'+ etoData.mainContainer +' .eto-v2-return-active').show();
            $('#'+ etoData.mainContainer +' #etoRouteReturnBtn1').removeClass('active');
            $('#'+ etoData.mainContainer +' #etoRouteReturnBtn2').addClass('active');
        } else {
            $('#'+ etoData.mainContainer +' #etoForm').addClass('etoJourneyTypeOneWay');
            $('#'+ etoData.mainContainer +' #etoForm').removeClass('etoJourneyTypeReturn');
            $('#'+ etoData.mainContainer +' .etoRouteReturnSectionContainer').removeClass('etoRouteReturnSectionContainerActive');
            // $('#'+ etoData.mainContainer +' .etoRoute1SectionContainer').removeClass('col-md-6');
            $('#'+ etoData.mainContainer +' #etoRoute2Container').hide();
            $('#'+ etoData.mainContainer +' .eto-v2-return-active').hide();
            $('#'+ etoData.mainContainer +' #etoRouteReturnBtn1').addClass('active');
            $('#'+ etoData.mainContainer +' #etoRouteReturnBtn2').removeClass('active');

            $('.etoVehicleTabs li a[href="#etoTabRoute1"]').click();
        }
        // Return - end


        // Route 1

        // Category - start
        etoData.booking.route1.category.start = parseInt($('#'+ etoData.mainContainer +' #etoRoute1CategoryStart option:selected').val());
        etoData.booking.route1.category.end = parseInt($('#'+ etoData.mainContainer +' #etoRoute1CategoryEnd option:selected').val());
        etoData.booking.route1.category.type.start = String($('#'+ etoData.mainContainer +' #etoRoute1CategoryStart option:selected').attr('field_type'));
        etoData.booking.route1.category.type.end = String($('#'+ etoData.mainContainer +' #etoRoute1CategoryEnd option:selected').attr('field_type'));
        // Category - end


        // Locations - start
        if (String($('#'+ etoData.mainContainer +' #etoRoute1LocationStart').prop('tagName')).toLowerCase() == 'select') {
            etoData.booking.route1.location.start = $('#'+ etoData.mainContainer +' #etoRoute1LocationStart option:selected').val();
        } else {
            etoData.booking.route1.location.start = $('#'+ etoData.mainContainer +' #etoRoute1LocationStart').val();
        }

        if (String($('#'+ etoData.mainContainer +' #etoRoute1LocationEnd').prop('tagName')).toLowerCase() == 'select') {
            etoData.booking.route1.location.end = $('#'+ etoData.mainContainer +' #etoRoute1LocationEnd option:selected').val();
        } else {
            etoData.booking.route1.location.end = $('#'+ etoData.mainContainer +' #etoRoute1LocationEnd').val();
        }
        // Locations - end


        // Address - start
        if (etoData.booking.route1.category.start > 0) {
            if (etoData.booking.route1.category.type.start == 'address') {
                etoData.booking.route1.address.start = etoData.booking.route1.location.start;
            } else {
                if (etoData.request.location) {
                    $.each(etoData.request.location, function(key, value) {
                        if (value.id == etoData.booking.route1.location.start) {
                            if (value.name == value.address) {
                                etoData.booking.route1.address.start = value.address;
                            } else {
                                etoData.booking.route1.address.start = value.name + ', ' + value.address;
                            }
                            return false;
                        }
                    });
                }
            }
        }

        if (etoData.booking.route1.category.end > 0) {
            if (etoData.booking.route1.category.type.end == 'address') {
                etoData.booking.route1.address.end = etoData.booking.route1.location.end;
            } else {
                if (etoData.request.location) {
                    $.each(etoData.request.location, function(key, value) {
                        if (value.id == etoData.booking.route1.location.end) {
                            if (value.name == value.address) {
                                etoData.booking.route1.address.end = value.address;
                            } else {
                                etoData.booking.route1.address.end = value.name + ', ' + value.address;
                            }
                            return false;
                        }
                    });
                }
            }
        }
        // Address - end


        // Place id - start
        etoData.booking.route1.placeId.start = String($('#'+ etoData.mainContainer +' #etoRoute1CategoryStartPlaceId').val());
        etoData.booking.route1.placeId.end = String($('#'+ etoData.mainContainer +' #etoRoute1CategoryEndPlaceId').val());
        etoData.booking.route1.waypointsPlaceId = [];
        $('#'+ etoData.mainContainer +' input[name*=etoRoute1WaypointsPlaceId]').each(function() {
            fieldValue = String($(this).val());
            etoData.booking.route1.waypointsPlaceId.push(fieldValue);
        });
        // Place id - end


        // Waypoints - start
        etoData.booking.route1.waypoints = [];
        $('#'+ etoData.mainContainer +' textarea[name*=etoRoute1Waypoints]').each(function() {
            fieldValue = String($(this).val());
            etoData.booking.route1.waypoints.push(fieldValue);
        });
        // Waypoints - end


        // Date - start
        etoData.booking.route1.date = $('#'+ etoData.mainContainer +' #etoRoute1Date').val();
        // Date - end


        if (etoData.layout != 'minimal') {
            $('#'+ etoData.mainContainer +' #etoRoute1VehicleContainer .etoVehicleInnerContainerSelected').removeClass('etoVehicleInnerContainerSelected');
            $('#'+ etoData.mainContainer +' #etoRoute1VehicleContainer button').html('<i class="fa"></i>');

            // Vehicles - start
            $('#'+ etoData.mainContainer +' select[name*=etoRoute1Vehicle]').each(function(key, value) {
                var amount = parseInt($(this).val());
                if (amount > 0) {
                    etoData.booking.route1.vehicle.push({
                        'id': parseInt($(this).attr('vehicle_id')),
                        'amount': amount
                    });

                    $(this).parents('.etoVehicleInnerContainer').addClass('etoVehicleInnerContainerSelected');
                }
            });

            $('#'+ etoData.mainContainer +' #etoRoute1VehicleContainer .etoVehicleInnerContainerSelected button').html('<i class="ion-ios-checkmark-empty"></i>');

            $.each(etoData.booking.route1.vehicle, function(key1, value1) {
                $.each(etoData.request.vehicle, function(key2, value2) {
                    if (value1.id == value2.id) {
                        etoData.booking.route1.vehicleLimits.passengers += parseInt(value2.passengers) * value1.amount;
                        etoData.booking.route1.vehicleLimits.luggage += parseInt(value2.luggage) * value1.amount;
                        etoData.booking.route1.vehicleLimits.handLuggage += parseInt(value2.hand_luggage) * value1.amount;
                        etoData.booking.route1.vehicleLimits.childSeats += parseInt(value2.child_seats) * value1.amount;
                        etoData.booking.route1.vehicleLimits.babySeats += parseInt(value2.baby_seats) * value1.amount;
                        etoData.booking.route1.vehicleLimits.infantSeats += parseInt(value2.infant_seats) * value1.amount;
                        etoData.booking.route1.vehicleLimits.wheelchair += parseInt(value2.wheelchair) * value1.amount;
                        return false;
                    }
                });
            });
            // Vehicles - end


            // Capacity - start
            etoData.booking.route1.passengers = $('#'+ etoData.mainContainer +' #etoRoute1Passengers option:selected').val();
            etoData.booking.route1.luggage = $('#'+ etoData.mainContainer +' #etoRoute1Luggage option:selected').val();
            etoData.booking.route1.handLuggage = $('#'+ etoData.mainContainer +' #etoRoute1HandLuggage option:selected').val();
            etoData.booking.route1.childSeats = $('#'+ etoData.mainContainer +' #etoRoute1ChildSeats option:selected').val();
            etoData.booking.route1.babySeats = $('#'+ etoData.mainContainer +' #etoRoute1BabySeats option:selected').val();
            etoData.booking.route1.infantSeats = $('#'+ etoData.mainContainer +' #etoRoute1InfantSeats option:selected').val();
            etoData.booking.route1.wheelchair = $('#'+ etoData.mainContainer +' #etoRoute1Wheelchair option:selected').val();
            // Capacity - end


            // Details - start
            etoData.booking.route1.flightNumber = $('#'+ etoData.mainContainer +' #etoRoute1FlightNumber').val();
            etoData.booking.route1.flightLandingTime = $('#'+ etoData.mainContainer +' #etoRoute1FlightLandingTime').val();
            etoData.booking.route1.departureCity = $('#'+ etoData.mainContainer +' #etoRoute1DepartureCity').val();

            etoData.booking.route1.departureFlightNumber = $('#'+ etoData.mainContainer +' #etoRoute1DepartureFlightNumber').val();
            etoData.booking.route1.departureFlightTime = $('#'+ etoData.mainContainer +' #etoRoute1DepartureFlightTime').val();
            etoData.booking.route1.departureFlightCity = $('#'+ etoData.mainContainer +' #etoRoute1DepartureFlightCity').val();

            etoCheckDepartureFlightTime(1);

            etoData.booking.route1.waitingTime = $('#'+ etoData.mainContainer +' #etoRoute1WaitingTime').val();
            // etoData.booking.route1.meetAndGreet = $('#'+ etoData.mainContainer +' #etoRoute1MeetAndGreet').val();
            etoData.booking.route1.meetAndGreet = ($('#'+ etoData.mainContainer +' #etoRoute1MeetAndGreet:checked').val()) ? 1 : 0;
            etoData.booking.route1.requirements = $('#'+ etoData.mainContainer +' #etoRoute1Requirements').val();

            // Items
            var items = [];
            $.each($("input[name*='etoRoute1Items']:checked"), function() {
                var itemid = String($(this).val());
                if (itemid) {
                    var amount = $("#etoRoute1Items_" + itemid + "_amount option:selected").val();
                    if ($("#etoRoute1Items_" + itemid + "_custom").length && $("#etoRoute1Items_" + itemid + "_custom").prop('tagName').toLowerCase() == 'select') {
                        var custom = $("#etoRoute1Items_" + itemid + "_custom option:selected").val();
                    }
                    else {
                        var custom = $("#etoRoute1Items_" + itemid + "_custom").val();
                    }
                    var type = $("#etoRoute1Items_" + itemid + "_type").val();

                    items.push({
                        'id': itemid,
                        'amount': amount ? parseInt(amount) : 0,
                        'custom': custom != undefined ? String(custom) : '',
                        'type': type != undefined ? String(type) : ''
                    });
                }
            });
            etoData.booking.route1.items = items;


            $('#'+ etoData.mainContainer +' #etoRoute1FlightNumberContainer, ' +
              '#'+ etoData.mainContainer +' #etoRoute1FlightLandingTimeContainer, ' +
              '#'+ etoData.mainContainer +' #etoRoute1DepartureCityContainer, ' +
              '#'+ etoData.mainContainer +' #etoRoute1DepartureFlightNumberContainer, ' +
              '#'+ etoData.mainContainer +' #etoRoute1DepartureFlightTimeContainer, ' +
              '#'+ etoData.mainContainer +' #etoRoute1DepartureFlightCityContainer, ' +
              '#'+ etoData.mainContainer +' #etoRoute1WaitingTimeContainer, ' +
              // '#'+ etoData.mainContainer +' #etoRoute1MeetingPointContainer, ' +
              '#'+ etoData.mainContainer +' #etoRoute1MeetAndGreetContainer, ' +
              '#'+ etoData.mainContainer +' #etoRoute1AddressStartCompleteContainer, ' +
              '#'+ etoData.mainContainer +' #etoRoute1AddressEndCompleteContainer').hide();

            if (
                etoData.booking.route1.isAirport == 1 ||
                etoData.booking.route1.category.type.start == 'airport'
            ) {
                $('#'+ etoData.mainContainer +' #etoRoute1FlightNumberContainer, ' +
                  '#'+ etoData.mainContainer +' #etoRoute1FlightLandingTimeContainer, ' +
                  '#'+ etoData.mainContainer +' #etoRoute1DepartureCityContainer, ' +
                  '#'+ etoData.mainContainer +' #etoRoute1WaitingTimeContainer, ' +
                  // '#'+ etoData.mainContainer +' #etoRoute1MeetingPointContainer, ' +
                  '#'+ etoData.mainContainer +' #etoRoute1MeetAndGreetContainer').show();
            } else {
                $('#'+ etoData.mainContainer +' #etoRoute1FlightNumber').val('');
                $('#'+ etoData.mainContainer +' #etoRoute1FlightLandingTime').val('');
                $('#'+ etoData.mainContainer +' #etoRoute1DepartureCity').val('');
                $('#'+ etoData.mainContainer +' #etoRoute1WaitingTime').val('');
                // $('#'+ etoData.mainContainer +' #etoRoute1MeetAndGreet').val(0);
                $('#'+ etoData.mainContainer +' #etoRoute1MeetAndGreet').attr('checked', false);
            }

            if (
                etoData.booking.route1.isAirport2 == 1 ||
                etoData.booking.route1.category.type.end == 'airport'
            ) {
                $('#'+ etoData.mainContainer +' #etoRoute1DepartureFlightNumberContainer, ' +
                  '#'+ etoData.mainContainer +' #etoRoute1DepartureFlightTimeContainer, ' +
                  '#'+ etoData.mainContainer +' #etoRoute1DepartureFlightCityContainer').show();
            } else {
                $('#'+ etoData.mainContainer +' #etoRoute1DepartureFlightNumber').val('');
                $('#'+ etoData.mainContainer +' #etoRoute1DepartureFlightTime').val('');
                $('#'+ etoData.mainContainer +' #etoRoute1DepartureFlightCity').val('');
            }

            if (parseInt(etoData.request.config.booking_meet_and_greet_compulsory) > 0) {
                $('#'+ etoData.mainContainer +' #etoRoute1MeetAndGreetContainer').hide();
                if (etoData.booking.route1.isAirport == 1 || etoData.booking.route1.category.type.start == 'airport') {
                    $('#'+ etoData.mainContainer +' #etoRoute1MeetAndGreet').attr('checked', true);
                } else {
                    $('#'+ etoData.mainContainer +' #etoRoute1MeetAndGreet').attr('checked', false);
                }
            }


            if (parseInt(etoData.request.config.booking_meet_and_greet_enable) == 0) {
                $('#'+ etoData.mainContainer +' #etoRoute1MeetAndGreetContainer').hide();
                $('#'+ etoData.mainContainer +' #etoRoute1MeetAndGreet').attr('checked', false);
            }

            // if (etoData.booking.route1.meetAndGreet > 0) {
            //   $('#'+ etoData.mainContainer +' #etoRoute1MeetingPointContainer').show();
            // } else {
            //   $('#'+ etoData.mainContainer +' #etoRoute1MeetingPointContainer').hide();
            // }

            if (
                etoData.booking.route1.isAirport == 0 && (
                    etoData.booking.route1.category.type.start == 'address' ||
                    etoData.booking.route1.category.type.start == 'postcode'
                )
            ) {
                $('#'+ etoData.mainContainer +' #etoRoute1AddressStartCompleteContainer').show();
            } else {
                $('#'+ etoData.mainContainer +' #etoRoute1AddressStartComplete').val('');
            }

            if (
                etoData.booking.route1.isAirport2 == 0 && (
                    etoData.booking.route1.category.type.end == 'address' ||
                    etoData.booking.route1.category.type.end == 'postcode'
                )
            ) {
                $('#'+ etoData.mainContainer +' #etoRoute1AddressEndCompleteContainer').show();
            } else {
                $('#'+ etoData.mainContainer +' #etoRoute1AddressEndComplete').val('');
            }
            // Details - end


            if (etoData.serviceParams.hide_location) {
                $('#'+ etoData.mainContainer +' #etoRoute1AddressEndCompleteContainer').hide();
            } else {
                //  $('#'+ etoData.mainContainer +' #etoRoute1AddressEndCompleteContainer').show();
            }


            // Journey Details - start
            var dateHtml = moment(String(etoData.booking.route1.date), 'YYYY-MM-DD HH:mm').format('DD/MM/YYYY');
            var timeHtml = moment(String(etoData.booking.route1.date), 'YYYY-MM-DD HH:mm').format('HH:mm');
            timeHtml += '<span class="etoAmPmTime">(' + moment(String(etoData.booking.route1.date), 'YYYY-MM-DD HH:mm').format('h:mm a') + ')</span>';
            var vehicleHtml = '';

            $.each(etoData.booking.route1.vehicle, function(key1, value1) {
                $.each(etoData.request.vehicle, function(key2, value2) {
                    if (value1.id == value2.id) {
                        if (vehicleHtml) {
                            vehicleHtml += ', ';
                        }
                        vehicleHtml += value2.name;
                        return false;
                    }
                });
            });


            var serviceName = '';
            var serviceDuration = '';

            if (etoData.booking.serviceId) {
                if (etoData.request.services && etoData.request.services.length > 0) {
                    $.each(etoData.request.services, function(key, value) {
                        if (value.id == etoData.booking.serviceId) {
                            serviceName = value.name;
                        }
                    });
                }

                if (serviceName) {
                    serviceName = '<span class="etoJourneyLine etoJourneyLineServices"><label>' + etoLang('bookingField_Services') + '</label>' + serviceName + '</span> ';
                }
            }

            if (etoData.booking.serviceDuration) {
                serviceDuration = etoData.booking.serviceDuration;

                if (serviceDuration) {
                    serviceDuration = '<span class="etoJourneyLine etoJourneyLineServicesDuration"><label>' + etoLang('bookingField_ServicesDuration') + '</label>' + serviceDuration + 'h</span> ';
                }
            }

            var html = '<div class="etoJourneyLineContainer">\
                ' + serviceName + serviceDuration + '\
                <span class="etoJourneyLine etoJourneyLineDate"><label>' + etoLang('bookingField_RequiredOn') + '</label>' + dateHtml + '</span> \
                <span class="etoJourneyLine etoJourneyLineTime"><label>' + etoLang('bookingField_PickupTime') + '</label>' + timeHtml + '</span> \
                <span class="etoJourneyLine etoJourneyLineVehicle"><label>' + etoLang('bookingField_Vehicle') + '</label>' + vehicleHtml + '</span>\
            </div>';
            $('#'+ etoData.mainContainer +' #etoRoute1JourneyDetailsLoader').html(html);


            // Journey from
            if (
                etoData.booking.route1.isAirport == 1 ||
                etoData.booking.route1.category.type.start == 'airport'
            ) {
                var html = '<div class="etoOuterContainer"><label class="etoLabel">' + etoLang('TITLE_PICKUP_FROM') + ' ' + etoLang('TITLE_AIPORT') + '</label><div class="etoInnerContainer"><span class="form-control1">' + etoData.booking.route1.address.start + '</span></div><div class="clear"></div></div>';
            } else if (etoData.booking.route1.category.type.start == 'seaport') {
                var html = '<div class="etoOuterContainer"><label class="etoLabel">' + etoLang('TITLE_PICKUP_FROM') + ' ' + etoLang('TITLE_CRUISE_PORT') + '</label><div class="etoInnerContainer"><span class="form-control1">' + etoData.booking.route1.address.start + '</span></div><div class="clear"></div></div>';
            } else {
                var html = '<div class="etoOuterContainer"><label class="etoLabel">' + etoLang('TITLE_PICKUP_FROM') + '</label><div class="etoInnerContainer"><span class="form-control1">' + etoData.booking.route1.address.start + '</span></div><div class="clear"></div></div>';
            }

            $('#'+ etoData.mainContainer +' #etoRoute1JourneyFromLoader').html(html);


            // Waypoints address - start
            var waypointCount = 1;
            var html = '';

            etoData.booking.route1.waypointsComplete = [];

            $('#'+ etoData.mainContainer +' input[name*=etoRoute1WaypointsComplete]').each(function() {
                fieldValue = String($(this).val());
                etoData.booking.route1.waypointsComplete.push(fieldValue);
            });

            $.each(etoData.booking.route1.waypoints, function(key, value) {
                html += '<div class="etoOuterContainer"><label class="etoLabel">' + etoLang('bookingField_Waypoint') + ' ' + waypointCount + '</label><div class="etoInnerContainer">' + value + '</div><div class="clear"></div></div>';
                html += etoCreate('input', 'Route1WaypointsComplete[]', etoLang('bookingField_WaypointAddress'), {
                    placeholder: parseInt(etoData.request.config.booking_required_address_complete_via) == 1 ? '' : '('+ etoLang('bookingOptional') +')'
                });
                waypointCount += 1;
            });

            $('#'+ etoData.mainContainer +' #etoRoute1WaypointsCompleteLoader').html(html);

            $('#'+ etoData.mainContainer +' input[name*=etoRoute1WaypointsComplete]').each(function(key, value) {
                $(this).val(etoData.booking.route1.waypointsComplete[key]);
                etoUpdateFormPlaceholder(this);
            })
            .bind('change keyup', function() {
                etoUpdateFormPlaceholder(this);
            });
            // Waypoints address - end


            // Journey to
            if (
                etoData.booking.route1.isAirport2 == 1 ||
                etoData.booking.route1.category.type.end == 'airport'
            ) {
                var html = '<div class="etoOuterContainer"><label class="etoLabel">' + etoLang('TITLE_DROPOFF_TO') + ' ' + etoLang('TITLE_AIPORT') + '</label><div class="etoInnerContainer"><span class="form-control1">' + etoData.booking.route1.address.end + '</span></div><div class="clear"></div></div>';
            } else if (etoData.booking.route1.category.type.end == 'seaport') {
                var html = '<div class="etoOuterContainer"><label class="etoLabel">' + etoLang('TITLE_DROPOFF_TO') + ' ' + etoLang('TITLE_CRUISE_PORT') + '</label><div class="etoInnerContainer"><span class="form-control1">' + etoData.booking.route1.address.end + '</span></div><div class="clear"></div></div>';
            } else {
                var html = '<div class="etoOuterContainer"><label class="etoLabel">' + etoLang('TITLE_DROPOFF_TO') + '</label><div class="etoInnerContainer"><span class="form-control1">' + etoData.booking.route1.address.end + '</span></div><div class="clear"></div></div>';
            }

            $('#'+ etoData.mainContainer +' #etoRoute1JourneyToLoader').html(html);

            // Read - start
            etoData.booking.route1.waypointsComplete = [];

            $('#'+ etoData.mainContainer +' input[name*=etoRoute1WaypointsComplete]').each(function() {
                fieldValue = String($(this).val());
                etoData.booking.route1.waypointsComplete.push(fieldValue);
            });

            etoData.booking.route1.addressComplete.start = $('#'+ etoData.mainContainer +' #etoRoute1AddressStartComplete').val();
            etoData.booking.route1.addressComplete.end = $('#'+ etoData.mainContainer +' #etoRoute1AddressEndComplete').val();
            // Read - end

            // Journey Details - end
        }


        // Hide location
        if (etoData.serviceParams.hide_location) {
            etoData.booking.route1.category.end = etoData.booking.route1.category.start;
            etoData.booking.route1.category.type.end = etoData.booking.route1.category.type.start;
            etoData.booking.route1.location.end = etoData.booking.route1.location.start;
            etoData.booking.route1.addressComplete.end = etoData.booking.route1.addressComplete.start;
            etoData.booking.route1.address.end = etoData.booking.route1.address.start;

            // $('#' + etoData.mainContainer +' #etoRoute1CategoryEnd').val(etoData.booking.route1.category.end);
            $('#'+ etoData.mainContainer +' #etoRoute1LocationEnd').val(etoData.booking.route1.location.end);
            $('#'+ etoData.mainContainer +' #etoRoute1CategoryEndTypeahead').typeahead('val', etoData.booking.route1.location.end);
            $('#'+ etoData.mainContainer +' #etoRoute1AddressEndComplete').val(etoData.booking.route1.addressComplete.end);
            // console.log( etoData.booking.route1.location.end, etoData.booking );
        }


        // Copy return - start
        /*if( etoData.singleVehicle > 0 && etoData.booking.routeReturn == 2 )
        {
        	etoCopyReturn();
        }*/
        // Copy return - end


        // Show return options - end
        //$('#'+ etoData.mainContainer +' .etoHideContainer').hide();
        // Show return options - end


        // Route 2

        // Category - start
        etoData.booking.route2.category.start = parseInt($('#'+ etoData.mainContainer +' #etoRoute2CategoryStart option:selected').val());
        etoData.booking.route2.category.end = parseInt($('#'+ etoData.mainContainer +' #etoRoute2CategoryEnd option:selected').val());
        etoData.booking.route2.category.type.start = String($('#'+ etoData.mainContainer +' #etoRoute2CategoryStart option:selected').attr('field_type'));
        etoData.booking.route2.category.type.end = String($('#'+ etoData.mainContainer +' #etoRoute2CategoryEnd option:selected').attr('field_type'));
        // Category - end


        // Locations - start
        if (String($('#'+ etoData.mainContainer +' #etoRoute2LocationStart').prop('tagName')).toLowerCase() == 'select') {
            etoData.booking.route2.location.start = $('#'+ etoData.mainContainer +' #etoRoute2LocationStart option:selected').val();
        } else {
            etoData.booking.route2.location.start = $('#'+ etoData.mainContainer +' #etoRoute2LocationStart').val();
        }

        if (String($('#'+ etoData.mainContainer +' #etoRoute2LocationEnd').prop('tagName')).toLowerCase() == 'select') {
            etoData.booking.route2.location.end = $('#'+ etoData.mainContainer +' #etoRoute2LocationEnd option:selected').val();
        } else {
            etoData.booking.route2.location.end = $('#'+ etoData.mainContainer +' #etoRoute2LocationEnd').val();
        }
        // Locations - end


        // Address - start
        if (etoData.booking.route2.category.start > 0) {
            if (etoData.booking.route2.category.type.start == 'address') {
                etoData.booking.route2.address.start = etoData.booking.route2.location.start;
            } else {
                if (etoData.request.location) {
                    $.each(etoData.request.location, function(key, value) {
                        if (value.id == etoData.booking.route2.location.start) {
                            if (value.name == value.address) {
                                etoData.booking.route2.address.start = value.address;
                            } else {
                                etoData.booking.route2.address.start = value.name + ', ' + value.address;
                            }
                            return false;
                        }
                    });
                }
            }
        }

        if (etoData.booking.route2.category.end > 0) {
            if (etoData.booking.route2.category.type.end == 'address') {
                etoData.booking.route2.address.end = etoData.booking.route2.location.end;
            } else {
                if (etoData.request.location) {
                    $.each(etoData.request.location, function(key, value) {
                        if (value.id == etoData.booking.route2.location.end) {
                            if (value.name == value.address) {
                                etoData.booking.route2.address.end = value.address;
                            } else {
                                etoData.booking.route2.address.end = value.name + ', ' + value.address;
                            }
                            return false;
                        }
                    });
                }
            }
        }
        // Address - end


        // Place id - start
        etoData.booking.route2.placeId.start = String($('#'+ etoData.mainContainer +' #etoRoute2CategoryStartPlaceId').val());
        etoData.booking.route2.placeId.end = String($('#'+ etoData.mainContainer +' #etoRoute2CategoryEndPlaceId').val());
        etoData.booking.route2.waypointsPlaceId = [];
        $('#'+ etoData.mainContainer +' input[name*=etoRoute2WaypointsPlaceId]').each(function() {
            fieldValue = String($(this).val());
            etoData.booking.route2.waypointsPlaceId.push(fieldValue);
        });
        // Place id - end


        // Waypoints - start
        etoData.booking.route2.waypoints = [];
        $('#'+ etoData.mainContainer +' textarea[name*=etoRoute2Waypoints]').each(function() {
            fieldValue = String($(this).val());
            etoData.booking.route2.waypoints.push(fieldValue);
        });
        // Waypoints - end


        // Date - start
        etoData.booking.route2.date = $('#'+ etoData.mainContainer +' #etoRoute2Date').val();
        // Date - end


        if (etoData.layout != 'minimal') {
            // Vehicles - start
            $('#'+ etoData.mainContainer +' #etoRoute2VehicleContainer .etoVehicleInnerContainerSelected').removeClass('etoVehicleInnerContainerSelected');
            $('#'+ etoData.mainContainer +' #etoRoute2VehicleContainer button').html('<i class="fa"></i>');

            $('#'+ etoData.mainContainer +' select[name*=etoRoute2Vehicle]').each(function(key, value) {
                var amount = parseInt($(this).val());
                if (amount > 0) {
                    etoData.booking.route2.vehicle.push({
                        'id': parseInt($(this).attr('vehicle_id')),
                        'amount': amount
                    });

                    $(this).parents('.etoVehicleInnerContainer').addClass('etoVehicleInnerContainerSelected');
                }
            });

            $('#'+ etoData.mainContainer +' #etoRoute2VehicleContainer .etoVehicleInnerContainerSelected button').html('<i class="ion-ios-checkmark-empty"></i>');

            $.each(etoData.booking.route2.vehicle, function(key1, value1) {
                $.each(etoData.request.vehicle, function(key2, value2) {
                    if (value1.id == value2.id) {
                        etoData.booking.route2.vehicleLimits.passengers += parseInt(value2.passengers) * value1.amount;
                        etoData.booking.route2.vehicleLimits.luggage += parseInt(value2.luggage) * value1.amount;
                        etoData.booking.route2.vehicleLimits.handLuggage += parseInt(value2.hand_luggage) * value1.amount;
                        etoData.booking.route2.vehicleLimits.childSeats += parseInt(value2.child_seats) * value1.amount;
                        etoData.booking.route2.vehicleLimits.babySeats += parseInt(value2.baby_seats) * value1.amount;
                        etoData.booking.route2.vehicleLimits.infantSeats += parseInt(value2.infant_seats) * value1.amount;
                        etoData.booking.route2.vehicleLimits.wheelchair += parseInt(value2.wheelchair) * value1.amount;
                        return false;
                    }
                });
            });
            // Vehicles - end


            // Capacity - start
            etoData.booking.route2.passengers = $('#'+ etoData.mainContainer +' #etoRoute2Passengers option:selected').val();
            etoData.booking.route2.luggage = $('#'+ etoData.mainContainer +' #etoRoute2Luggage option:selected').val();
            etoData.booking.route2.handLuggage = $('#'+ etoData.mainContainer +' #etoRoute2HandLuggage option:selected').val();
            etoData.booking.route2.childSeats = $('#'+ etoData.mainContainer +' #etoRoute2ChildSeats option:selected').val();
            etoData.booking.route2.babySeats = $('#'+ etoData.mainContainer +' #etoRoute2BabySeats option:selected').val();
            etoData.booking.route2.infantSeats = $('#'+ etoData.mainContainer +' #etoRoute2InfantSeats option:selected').val();
            etoData.booking.route2.wheelchair = $('#'+ etoData.mainContainer +' #etoRoute2Wheelchair option:selected').val();
            // Capacity - end


            // Details - start
            etoData.booking.route2.flightNumber = $('#'+ etoData.mainContainer +' #etoRoute2FlightNumber').val();
            etoData.booking.route2.flightLandingTime = $('#'+ etoData.mainContainer +' #etoRoute2FlightLandingTime').val();
            etoData.booking.route2.departureCity = $('#'+ etoData.mainContainer +' #etoRoute2DepartureCity').val();

            etoData.booking.route2.departureFlightNumber = $('#'+ etoData.mainContainer +' #etoRoute2DepartureFlightNumber').val();
            etoData.booking.route2.departureFlightTime = $('#'+ etoData.mainContainer +' #etoRoute2DepartureFlightTime').val();
            etoData.booking.route2.departureFlightCity = $('#'+ etoData.mainContainer +' #etoRoute2DepartureFlightCity').val();

            etoCheckDepartureFlightTime(2);

            etoData.booking.route2.waitingTime = $('#'+ etoData.mainContainer +' #etoRoute2WaitingTime').val();
            // etoData.booking.route2.meetAndGreet = $('#'+ etoData.mainContainer +' #etoRoute2MeetAndGreet').val();
            etoData.booking.route2.meetAndGreet = ($('#'+ etoData.mainContainer +' #etoRoute2MeetAndGreet:checked').val()) ? 1 : 0;
            etoData.booking.route2.requirements = $('#'+ etoData.mainContainer +' #etoRoute2Requirements').val();

            // Items
            var items = [];
            $.each($("input[name*='etoRoute2Items']:checked"), function() {
                var itemid = String($(this).val());
                if (itemid) {
                    var amount = $("#etoRoute2Items_" + itemid + "_amount option:selected").val();
                    if ($("#etoRoute2Items_" + itemid + "_custom").length && $("#etoRoute2Items_" + itemid + "_custom").prop('tagName').toLowerCase() == 'select') {
                        var custom = $("#etoRoute2Items_" + itemid + "_custom option:selected").val();
                    }
                    else {
                        var custom = $("#etoRoute2Items_" + itemid + "_custom").val();
                    }
                    var type = $("#etoRoute2Items_" + itemid + "_type").val();

                    items.push({
                        'id': itemid,
                        'amount': amount ? parseInt(amount) : 0,
                        'custom': custom != undefined ? String(custom) : '',
                        'type': type != undefined ? String(type) : ''
                    });
                }
            });
            etoData.booking.route2.items = items;


            $('#'+ etoData.mainContainer +' #etoRoute2FlightNumberContainer, ' +
              '#'+ etoData.mainContainer +' #etoRoute2FlightLandingTimeContainer, ' +
              '#'+ etoData.mainContainer +' #etoRoute2DepartureCityContainer, ' +
              '#'+ etoData.mainContainer +' #etoRoute2DepartureFlightNumberContainer, ' +
              '#'+ etoData.mainContainer +' #etoRoute2DepartureFlightTimeContainer, ' +
              '#'+ etoData.mainContainer +' #etoRoute2DepartureFlightCityContainer, ' +
              '#'+ etoData.mainContainer +' #etoRoute2WaitingTimeContainer, ' +
              // '#'+ etoData.mainContainer +' #etoRoute2MeetingPointContainer, ' +
              '#'+ etoData.mainContainer +' #etoRoute2MeetAndGreetContainer, ' +
              '#'+ etoData.mainContainer +' #etoRoute2AddressStartCompleteContainer, ' +
              '#'+ etoData.mainContainer +' #etoRoute2AddressEndCompleteContainer').hide();

            if (
                etoData.booking.route2.isAirport == 1 ||
                etoData.booking.route2.category.type.start == 'airport'
            ) {
                $('#'+ etoData.mainContainer +' #etoRoute2FlightNumberContainer, ' +
                  '#'+ etoData.mainContainer +' #etoRoute2FlightLandingTimeContainer, ' +
                  '#'+ etoData.mainContainer +' #etoRoute2DepartureCityContainer, ' +
                  '#'+ etoData.mainContainer +' #etoRoute2WaitingTimeContainer, ' +
                  // '#'+ etoData.mainContainer +' #etoRoute2MeetingPointContainer, ' +
                  '#'+ etoData.mainContainer +' #etoRoute2MeetAndGreetContainer').show();
            } else {
                $('#'+ etoData.mainContainer +' #etoRoute2FlightNumber').val('');
                $('#'+ etoData.mainContainer +' #etoRoute2FlightLandingTime').val('');
                $('#'+ etoData.mainContainer +' #etoRoute2DepartureCity').val('');
                $('#'+ etoData.mainContainer +' #etoRoute2WaitingTime').val('');
                // $('#'+ etoData.mainContainer +' #etoRoute2MeetAndGreet').val(0);
                $('#'+ etoData.mainContainer +' #etoRoute2MeetAndGreet').attr('checked', false);
            }

            if (
                etoData.booking.route2.isAirport2 == 1 ||
                etoData.booking.route2.category.type.end == 'airport'
            ) {
                $('#'+ etoData.mainContainer +' #etoRoute2DepartureFlightNumberContainer, ' +
                  '#'+ etoData.mainContainer +' #etoRoute2DepartureFlightTimeContainer, ' +
                  '#'+ etoData.mainContainer +' #etoRoute2DepartureFlightCityContainer').show();
            } else {
                $('#'+ etoData.mainContainer +' #etoRoute2DepartureFlightNumber').val('');
                $('#'+ etoData.mainContainer +' #etoRoute2DepartureFlightTime').val('');
                $('#'+ etoData.mainContainer +' #etoRoute2DepartureFlightCity').val('');
            }

            if (parseInt(etoData.request.config.booking_meet_and_greet_compulsory) > 0) {
                $('#'+ etoData.mainContainer +' #etoRoute2MeetAndGreetContainer').hide();
                if (etoData.booking.route2.isAirport == 1 || etoData.booking.route2.category.type.start == 'airport') {
                    $('#'+ etoData.mainContainer +' #etoRoute2MeetAndGreet').attr('checked', true);
                } else {
                    $('#'+ etoData.mainContainer +' #etoRoute2MeetAndGreet').attr('checked', false);
                }
            }


            if (parseInt(etoData.request.config.booking_meet_and_greet_enable) == 0) {
                $('#'+ etoData.mainContainer +' #etoRoute2MeetAndGreetContainer').hide();
                $('#'+ etoData.mainContainer +' #etoRoute2MeetAndGreet').attr('checked', false);
            }

            // if (etoData.booking.route2.meetAndGreet > 0) {
            //   $('#'+ etoData.mainContainer +' #etoRoute2MeetingPointContainer').show();
            // } else {
            //   $('#'+ etoData.mainContainer +' #etoRoute2MeetingPointContainer').hide();
            // }

            if (
                etoData.booking.route2.isAirport == 0 && (
                    etoData.booking.route2.category.type.start == 'address' ||
                    etoData.booking.route2.category.type.start == 'postcode'
                )
            ) {
                $('#'+ etoData.mainContainer +' #etoRoute2AddressStartCompleteContainer').show();
            } else {
                $('#'+ etoData.mainContainer +' #etoRoute2AddressStartComplete').val('');
            }

            if (
                etoData.booking.route2.isAirport2 == 0 && (
                    etoData.booking.route2.category.type.end == 'address' ||
                    etoData.booking.route2.category.type.end == 'postcode'
                )
            ) {
                $('#'+ etoData.mainContainer +' #etoRoute2AddressEndCompleteContainer').show();
            } else {
                $('#'+ etoData.mainContainer +' #etoRoute2AddressEndComplete').val('');
            }
            // Details - end


            // Journey Details - start
            var dateHtml = moment(String(etoData.booking.route2.date), 'YYYY-MM-DD HH:mm').format('DD/MM/YYYY');
            var timeHtml = moment(String(etoData.booking.route2.date), 'YYYY-MM-DD HH:mm').format('HH:mm');
            timeHtml += '<span class="etoAmPmTime">(' + moment(String(etoData.booking.route2.date), 'YYYY-MM-DD HH:mm').format('h:mm a') + ')</span>';
            var vehicleHtml = '';

            $.each(etoData.booking.route2.vehicle, function(key1, value1) {
                $.each(etoData.request.vehicle, function(key2, value2) {
                    if (value1.id == value2.id) {
                        if (vehicleHtml) {
                            vehicleHtml += ', ';
                        }
                        vehicleHtml += value2.name;
                        return false;
                    }
                });
            });

            var serviceName = '';
            var serviceDuration = '';

            if (etoData.booking.serviceId) {
                if (etoData.request.services && etoData.request.services.length > 0) {
                    $.each(etoData.request.services, function(key, value) {
                        if (value.id == etoData.booking.serviceId) {
                            serviceName = value.name;
                        }
                    });
                }

                if (serviceName) {
                    serviceName = '<span class="etoJourneyLine etoJourneyLineServices"><label>' + etoLang('bookingField_Services') + '</label>' + serviceName + '</span> ';
                }
            }

            if (etoData.booking.serviceDuration) {
                serviceDuration = etoData.booking.serviceDuration;

                if (serviceDuration) {
                    serviceDuration = '<span class="etoJourneyLine etoJourneyLineServicesDuration"><label>' + etoLang('bookingField_ServicesDuration') + '</label>' + serviceDuration + 'h</span> ';
                }
            }

            var html = '<div class="etoJourneyLineContainer">\
                    ' + serviceName + serviceDuration + '\
                    <span class="etoJourneyLine etoJourneyLineDate"><label>' + etoLang('bookingField_RequiredOn') + '</label>' + dateHtml + '</span> \
                    <span class="etoJourneyLine etoJourneyLineTime"><label>' + etoLang('bookingField_PickupTime') + '</label>' + timeHtml + '</span> \
                    <span class="etoJourneyLine etoJourneyLineVehicle"><label>' + etoLang('bookingField_Vehicle') + '</label>' + vehicleHtml + '</span></div>';
            $('#'+ etoData.mainContainer +' #etoRoute2JourneyDetailsLoader').html(html);


            // Journey from
            if (
                etoData.booking.route2.isAirport == 1 ||
                etoData.booking.route2.category.type.start == 'airport'
            ) {
                var html = '<div class="etoOuterContainer"><label class="etoLabel">' + etoLang('TITLE_PICKUP_FROM') + ' ' + etoLang('TITLE_AIPORT') + '</label><div class="etoInnerContainer"><span class="form-control1">' + etoData.booking.route2.address.start + '</span></div><div class="clear"></div></div>';
            } else if (etoData.booking.route2.category.type.start == 'seaport') {
                var html = '<div class="etoOuterContainer"><label class="etoLabel">' + etoLang('TITLE_PICKUP_FROM') + ' ' + etoLang('TITLE_CRUISE_PORT') + '</label><div class="etoInnerContainer"><span class="form-control1">' + etoData.booking.route2.address.start + '</span></div><div class="clear"></div></div>';
            } else {
                var html = '<div class="etoOuterContainer"><label class="etoLabel">' + etoLang('TITLE_PICKUP_FROM') + '</label><div class="etoInnerContainer"><span class="form-control1">' + etoData.booking.route2.address.start + '</span></div><div class="clear"></div></div>';
            }

            $('#'+ etoData.mainContainer +' #etoRoute2JourneyFromLoader').html(html);


            // Waypoints address - start
            var waypointCount = 1;
            var html = '';

            etoData.booking.route2.waypointsComplete = [];

            $('#'+ etoData.mainContainer +' input[name*=etoRoute2WaypointsComplete]').each(function() {
                fieldValue = String($(this).val());
                etoData.booking.route2.waypointsComplete.push(fieldValue);
            });

            $.each(etoData.booking.route2.waypoints, function(key, value) {
                html += '<div class="etoOuterContainer"><label class="etoLabel">' + etoLang('bookingField_Waypoint') + ' ' + waypointCount + '</label><div class="etoInnerContainer">' + value + '</div><div class="clear"></div></div>';
                html += etoCreate('input', 'Route2WaypointsComplete[]', etoLang('bookingField_WaypointAddress'), {
                    placeholder: parseInt(etoData.request.config.booking_required_address_complete_via) == 1 ? '' : '('+ etoLang('bookingOptional') +')'
                });
                waypointCount += 1;
            });

            $('#'+ etoData.mainContainer +' #etoRoute2WaypointsCompleteLoader').html(html);

            $('#'+ etoData.mainContainer +' input[name*=etoRoute2WaypointsComplete]').each(function(key, value) {
                $(this).val(etoData.booking.route2.waypointsComplete[key]);
                etoUpdateFormPlaceholder(this);
            })
            .bind('change keyup', function() {
                etoUpdateFormPlaceholder(this);
            });
            // Waypoints address - end


            // Journey to
            if (
                etoData.booking.route2.isAirport2 == 1 ||
                etoData.booking.route2.category.type.end == 'airport'
            ) {
                var html = '<div class="etoOuterContainer"><label class="etoLabel">' + etoLang('TITLE_DROPOFF_TO') + ' ' + etoLang('TITLE_AIPORT') + '</label><div class="etoInnerContainer"><span class="form-control1">' + etoData.booking.route2.address.end + '</span></div><div class="clear"></div></div>';
            } else if (etoData.booking.route2.category.type.end == 'seaport') {
                var html = '<div class="etoOuterContainer"><label class="etoLabel">' + etoLang('TITLE_DROPOFF_TO') + ' ' + etoLang('TITLE_CRUISE_PORT') + '</label><div class="etoInnerContainer"><span class="form-control1">' + etoData.booking.route2.address.end + '</span></div><div class="clear"></div></div>';
            } else {
                var html = '<div class="etoOuterContainer"><label class="etoLabel">' + etoLang('TITLE_DROPOFF_TO') + '</label><div class="etoInnerContainer"><span class="form-control1">' + etoData.booking.route2.address.end + '</span></div><div class="clear"></div></div>';
            }

            $('#'+ etoData.mainContainer +' #etoRoute2JourneyToLoader').html(html);

            // Read - start
            etoData.booking.route2.waypointsComplete = [];

            $('#'+ etoData.mainContainer +' input[name*=etoRoute2WaypointsComplete]').each(function() {
                fieldValue = String($(this).val());
                etoData.booking.route2.waypointsComplete.push(fieldValue);
            });

            etoData.booking.route2.addressComplete.start = $('#'+ etoData.mainContainer +' #etoRoute2AddressStartComplete').val();
            etoData.booking.route2.addressComplete.end = $('#'+ etoData.mainContainer +' #etoRoute2AddressEndComplete').val();
            // Read - end

            // Journey Details - end
        }

        if (etoData.layout != 'minimal') {
            // Other - start
            etoData.booking.contactDepartment = $('#'+ etoData.mainContainer +' #etoContactDepartment').val();
            etoData.booking.contactTitle = $('#'+ etoData.mainContainer +' #etoContactTitle').val();
            etoData.booking.contactName = $('#'+ etoData.mainContainer +' #etoContactName').val();
            etoData.booking.contactEmail = $('#'+ etoData.mainContainer +' #etoContactEmail').val();

            if($('#'+ etoData.mainContainer +' #etoContactMobile').intlTelInput('getNumber')) {
                etoData.booking.contactMobile = $('#'+ etoData.mainContainer +' #etoContactMobile').intlTelInput('getNumber');
            }
            else {
                etoData.booking.contactMobile = $('#'+ etoData.mainContainer +' #etoContactMobile').val();
            }

            etoData.booking.leadPassenger = parseInt($('#'+ etoData.mainContainer +' input[name=etoLeadPassenger]:checked').val());
            etoData.booking.leadPassengerTitle = $('#'+ etoData.mainContainer +' #etoLeadPassengerTitle').val();
            etoData.booking.leadPassengerName = $('#'+ etoData.mainContainer +' #etoLeadPassengerName').val();
            etoData.booking.leadPassengerEmail = $('#'+ etoData.mainContainer +' #etoLeadPassengerEmail').val();

            if($('#'+ etoData.mainContainer +' #etoLeadPassengerMobile').intlTelInput('getNumber')) {
                etoData.booking.leadPassengerMobile = $('#'+ etoData.mainContainer +' #etoLeadPassengerMobile').intlTelInput('getNumber');
            }
            else {
                etoData.booking.leadPassengerMobile = $('#'+ etoData.mainContainer +' #etoLeadPassengerMobile').val();
            }

            etoData.booking.payment = parseInt($('#'+ etoData.mainContainer +' input[name=etoPayment]:checked').val());

            if ($("select[name='totalDeposit[" + etoData.booking.payment + "]'] option:selected").length > 0) {
                etoData.booking.totalDeposit = parseFloat($("select[name='totalDeposit[" + etoData.booking.payment + "]'] option:selected").val());
            } else {
                etoData.booking.totalDeposit = 0;
            }

            etoData.booking.discountCode = $('#'+ etoData.mainContainer +' #etoDiscountCode').val();
            // Other - end

            // Lead passenger - start
            if (etoData.booking.leadPassenger <= 0) {
                // $('#'+ etoData.mainContainer +' #etoContactSectionContainer').addClass('col-md-6');
                $('#'+ etoData.mainContainer +' #etoLeadPassengerSectionContainer').show();
            } else {
                // $('#'+ etoData.mainContainer +' #etoContactSectionContainer').removeClass('col-md-6');
                $('#'+ etoData.mainContainer +' #etoLeadPassengerSectionContainer').hide();
                $('#'+ etoData.mainContainer +' #etoLeadPassengerTitle option:first').val();
                $('#'+ etoData.mainContainer +' #etoLeadPassengerName').val('');
                $('#'+ etoData.mainContainer +' #etoLeadPassengerEmail').val('');
                $('#'+ etoData.mainContainer +' #etoLeadPassengerMobile').val('');
            }
            // Lead passenger - end


            // Disable payment buttons - start
            if (parseInt(etoData.request.config.booking_terms_disable_button) == 1) {
                var terms = ($('#'+ etoData.mainContainer +' #etoTerms:checked').val()) ? 1 : 0;
                if (terms <= 0) {
                    $('#'+ etoData.mainContainer +' button.etoPaymentButton').attr('disabled', true);
                } else {
                    $('#'+ etoData.mainContainer +' button.etoPaymentButton').attr('disabled', false);
                }
            }
            // Disable payment buttons - end


            // Manual Quote - start
            $('#etoManualPayButtonContainer').remove();

            if (etoData.manualQuote == 1) {
                quote = 0;

                $('#etoRoute1VehicleContainer .etoVehicleTotalPrice, ' +
                    '.etoRoute1MapParent, ' +
                    '#etoRoute1ExtraChargesContainer, ' +
                    '#etoRoute1TotalPriceContainer, ' +
                    '#etoRoute2VehicleContainer .etoVehicleTotalPrice, ' +
                    '.etoRoute2MapParent, ' +
                    '#etoRoute2ExtraChargesContainer, ' +
                    '#etoRoute2TotalPriceContainer, ' +
                    '#etoTotalPriceContainer, ' +
                    '.legendBookContainer, ' +
                    '#etoVehicleCheckoutTotal, ' +
                    '#etoPaymentContainer .eto-v2-payment-method').hide();

                $('#etoQuoteStep2Button').html(etoLang('bookingButton_Next'));
                $('#etoQuoteStep2ButtonHelper1').html(etoLang('bookingButton_Next'));
                $('#etoQuoteStep2ButtonHelper2').html(etoLang('bookingButton_Next'));

                var htmlBtn = '<div id="etoManualPayButtonContainer" class="etoPaymentContainer" style="margin:0 auto; float:none;">' +
                    '<input type="radio" name="etoPayment" id="etoPayment0" value="0">' +
                    '<label for="etoPayment0" style="display:inline-block; width:auto;">' +
                    '<button id="etoManualPayButton" class="btn btn-primary etoPaymentButton" onclick="$(\'input#etoPayment0\').attr(\'checked\', true).change(); return false;">' + etoLang('bookingButton_RequestQuote') + '</button>' +
                    '</label>' +
                    '</div>';
                $('#etoPaymentContainer .etoInnerContainer').append(htmlBtn);

                $('#etoManualPayButton').click(function(e) {
                    etoData.displayMessage = 1;
                    etoData.displayHighlight = 1;
                    etoData.quoteStatus = 1;
                    if ($isSubmitReady == 1) {
                        etoCheck(2);
                        etoSubmit();
                        etoScrollToTop();
                    }
                    e.preventDefault();
                });
            } else {
                $('#etoRoute1VehicleContainer .etoVehicleTotalPrice, ' +
                    '.etoRoute1MapParent, ' +
                    '#etoRoute1ExtraChargesContainer, ' +
                    '#etoRoute1TotalPriceContainer, ' +
                    '#etoRoute2VehicleContainer .etoVehicleTotalPrice, ' +
                    '.etoRoute2MapParent, ' +
                    '#etoRoute2ExtraChargesContainer, ' +
                    '#etoRoute2TotalPriceContainer, ' +
                    '#etoTotalPriceContainer, ' +
                    '.legendBookContainer, ' +
                    '#etoVehicleCheckoutTotal, ' +
                    '#etoPaymentContainer .eto-v2-payment-method').show();

                $('#etoQuoteStep2Button').html(etoLang('bookingButton_BookNow'));
                $('#etoQuoteStep2ButtonHelper1').html(etoLang('bookingButton_BookNow'));
                $('#etoQuoteStep2ButtonHelper2').html(etoLang('bookingButton_BookNow'));
            }
            // Manual Quote - end


            // Quote - start
            if (etoData.dynamicQuote == 0) {
                // Extra charges show / hide
                $('#'+ etoData.mainContainer +' #etoRoute1ExtraChargesContainer').hide();
                $('#'+ etoData.mainContainer +' #etoRoute2ExtraChargesContainer').hide();

                // Price show / hide
                $('#'+ etoData.mainContainer +' #etoRoute1TotalPriceContainer').hide();
                $('#'+ etoData.mainContainer +' #etoRoute2TotalPriceContainer').hide();
                // $('#'+ etoData.mainContainer +' #etoTotalPriceContainer').hide();
            }

            if (etoData.dynamicQuote == 1 && (quote == 1 || quote == 2)) {
                quote = 1;
            } else if (etoData.dynamicQuote == 0 && quote == 2) {
                quote = 1;
            } else {
                quote = 0;
            }

            if (quote == 1 && (
                  (etoData.booking.route1.address.start && etoData.booking.route1.address.end) ||
                  (etoData.booking.route2.address.start && etoData.booking.route2.address.end)
              )) {

                if (etoData.debug) {
                    console.log('quote');
                }

                var quoteData = {
                    scheduledRouteId: etoData.booking.scheduledRouteId,
                    serviceId: etoData.booking.serviceId,
                    serviceDuration: etoData.booking.serviceDuration,
                    preferred: {
                        passengers: etoData.booking.preferred.passengers,
                        luggage: etoData.booking.preferred.luggage,
                        handLuggage: etoData.booking.preferred.handLuggage,
                    },
                    routeReturn: etoData.booking.routeReturn,
                    payment: etoData.booking.payment,
                    discountCode: etoData.booking.discountCode,
                    route1: {
                        address: {
                            start: etoData.booking.route1.address.start,
                            end: etoData.booking.route1.address.end,
                            waypoints: etoData.booking.route1.waypoints,
                            startPlaceId: etoData.booking.route1.placeId.start,
                            endPlaceId: etoData.booking.route1.placeId.end,
                            waypointsPlaceId: etoData.booking.route1.waypointsPlaceId
                        },
                        date: etoData.booking.route1.date,
                        passengers: etoData.booking.route1.passengers,
                        childSeats: etoData.booking.route1.childSeats,
                        babySeats: etoData.booking.route1.babySeats,
                        infantSeats: etoData.booking.route1.infantSeats,
                        wheelchair: etoData.booking.route1.wheelchair,
                        waitingTime: etoData.booking.route1.waitingTime,
                        meetAndGreet: etoData.booking.route1.meetAndGreet,
                        vehicle: etoData.booking.route1.vehicle,
                        items: etoData.booking.route1.items
                    },
                    route2: {
                        address: {
                            start: etoData.booking.route2.address.start,
                            end: etoData.booking.route2.address.end,
                            waypoints: etoData.booking.route2.waypoints,
                            startPlaceId: etoData.booking.route2.placeId.start,
                            endPlaceId: etoData.booking.route2.placeId.end,
                            waypointsPlaceId: etoData.booking.route2.waypointsPlaceId
                        },
                        date: etoData.booking.route2.date,
                        passengers: etoData.booking.route2.passengers,
                        childSeats: etoData.booking.route2.childSeats,
                        babySeats: etoData.booking.route2.babySeats,
                        infantSeats: etoData.booking.route2.infantSeats,
                        wheelchair: etoData.booking.route2.wheelchair,
                        waitingTime: etoData.booking.route2.waitingTime,
                        meetAndGreet: etoData.booking.route2.meetAndGreet,
                        vehicle: etoData.booking.route2.vehicle,
                        items: etoData.booking.route2.items
                    }
                };

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': _token
                    },
                    url: etoData.apiURL,
                    type: 'POST',
                    data: {
                        task: 'quote',
                        booking: quoteData
                    },
                    dataType: 'json',
                    async: false,
                    // cache: false,
                    success: function(response) {
                        // Manual Quote - start
                        $('#etoManualQuoteButtonContainer').remove();
                        if (response.manualQuote == 1) {
                            if (response.manualQuoteMessage) {
                                $('#etoStep2Container > .etoVehicleTabs').before('<div id="etoManualQuoteButtonContainer" class="alert alert-info">'+ response.manualQuoteMessage +'</div>');
                            }
                            etoData.manualQuote = 1;
                            etoCheck(0);
                        }
                        // Manual Quote - end

                        if (response.message.length > 0) {
                            $.each(response.message, function(key, value) {
                                etoData.message.push(value);
                            });
                        }

                        if (response.message_r1s3 && response.message_r1s3.length > 0) {
                            $.each(response.message_r1s3, function(key, value) {
                                etoData.errorMessage.push(['s3_r1', value, 'server']);
                            });
                        }
                        else if (response.message_r1s2 && response.message_r1s2.length > 0) {
                            $.each(response.message_r1s2, function(key, value) {
                                etoData.errorMessage.push(['s2_r1', value, 'server']);
                            });
                        }
                        else if (response.message_r1 && response.message_r1.length > 0) {
                            $.each(response.message_r1, function(key, value) {
                                etoData.errorMessage.push(['s1_r1', value, 'server']);
                            });
                        }


                        if (response.message_r2s3 && response.message_r2s3.length > 0) {
                            $.each(response.message_r2s3, function(key, value) {
                                etoData.errorMessage.push(['s3_r2', value, 'server']);
                            });
                        }
                        else if (response.message_r2s2 && response.message_r2s2.length > 0) {
                            $.each(response.message_r2s2, function(key, value) {
                                etoData.errorMessage.push(['s2_r2', value, 'server']);
                            });
                        }
                        else if (response.message_r2 && response.message_r2.length > 0) {
                            $.each(response.message_r2, function(key, value) {
                                etoData.errorMessage.push(['s1_r2', value, 'server']);
                            });
                        }

                        if (response.booking) {
                            if (etoData.booking.route1) {
                                etoData.booking.scheduledRouteId = response.booking.route1.scheduledRouteId;

                                etoData.booking.route1.extraChargesList = response.booking.route1.extraChargesList;
                                etoData.booking.route1.extraChargesPrice = response.booking.route1.extraChargesPrice;
                                etoData.booking.route1.totalPrice = response.booking.route1.totalPrice;
                                etoData.booking.route1.totalPriceWithDiscount = response.booking.route1.totalPriceWithDiscount;
                                etoData.booking.route1.totalDiscount = response.booking.route1.totalDiscount;
                                etoData.booking.route1.accountDiscount = response.booking.route1.accountDiscount;
                                etoData.booking.route1.distance = response.booking.route1.distance;
                                etoData.booking.route1.duration = response.booking.route1.duration;
                                etoData.booking.route1.distance_base_start = response.booking.route1.distance_base_start;
                                etoData.booking.route1.duration_base_start = response.booking.route1.duration_base_start;
                                etoData.booking.route1.distance_base_end = response.booking.route1.distance_base_end;
                                etoData.booking.route1.duration_base_end = response.booking.route1.duration_base_end;
                                etoData.booking.route1.address.start = response.booking.route1.address.start;
                                etoData.booking.route1.address.end = response.booking.route1.address.end;
                                etoData.booking.route1.coordinate.start.lat = response.booking.route1.coordinate.start.lat;
                                etoData.booking.route1.coordinate.start.lon = response.booking.route1.coordinate.start.lon;
                                etoData.booking.route1.coordinate.end.lat = response.booking.route1.coordinate.end.lat;
                                etoData.booking.route1.coordinate.end.lon = response.booking.route1.coordinate.end.lon;
                                etoData.booking.route1.vehicleButtons = response.booking.route1.vehicleButtons;
                                etoData.booking.route1.stepsText = response.booking.route1.stepsText;
                                etoData.booking.route1.isAirport = parseInt(response.booking.route1.isAirport);
                                etoData.booking.route1.isAirport2 = parseInt(response.booking.route1.isAirport2);
                                etoData.booking.route1.excludedRouteAllowed = parseInt(response.booking.route1.excludedRouteAllowed);
                                etoData.booking.route1.meetingPoint = response.booking.route1.meetingPoint;

                                if (etoData.booking.route1.isAirport == 1) {
                                    $('#'+ etoData.mainContainer +' #etoRoute1FlightNumberContainer, ' +
                                      '#'+ etoData.mainContainer +' #etoRoute1FlightLandingTimeContainer, ' +
                                      '#'+ etoData.mainContainer +' #etoRoute1DepartureCityContainer, ' +
                                      '#'+ etoData.mainContainer +' #etoRoute1WaitingTimeContainer, ' +
                                      // '#'+ etoData.mainContainer +' #etoRoute1MeetingPointContainer, ' +
                                      '#'+ etoData.mainContainer +' #etoRoute1MeetAndGreetContainer').show();
                                }

                                if (etoData.booking.route1.isAirport2 == 1) {
                                    $('#'+ etoData.mainContainer +' #etoRoute1DepartureFlightNumberContainer, ' +
                                      '#'+ etoData.mainContainer +' #etoRoute1DepartureFlightTimeContainer, ' +
                                      '#'+ etoData.mainContainer +' #etoRoute1DepartureFlightCityContainer').show();
                                }

                                if (parseInt(etoData.request.config.booking_meet_and_greet_compulsory) > 0) {
                                    $('#'+ etoData.mainContainer +' #etoRoute1MeetAndGreetContainer').hide();
                                    if (etoData.booking.route1.isAirport == 1 || etoData.booking.route1.category.type.start == 'airport') {
                                        $('#'+ etoData.mainContainer +' #etoRoute1MeetAndGreet').attr('checked', true);
                                    } else {
                                        $('#'+ etoData.mainContainer +' #etoRoute1MeetAndGreet').attr('checked', false);
                                    }
                                }

                                if (parseInt(etoData.request.config.booking_meet_and_greet_enable) == 0) {
                                    $('#'+ etoData.mainContainer +' #etoRoute1MeetAndGreetContainer').hide();
                                    $('#'+ etoData.mainContainer +' #etoRoute1MeetAndGreet').attr('checked', false);
                                }

                                if (etoData.booking.route1.meetingPoint) {
                                    $('#'+ etoData.mainContainer +' #etoRoute1MeetingPointContainer .etoInnerContainer').html(etoData.booking.route1.meetingPoint);
                                    $('#'+ etoData.mainContainer +' #etoRoute1MeetingPointContainer').show();
                                } else {
                                    $('#'+ etoData.mainContainer +' #etoRoute1MeetingPointContainer').hide();
                                }

                                // Map
                                if (etoData.booking.route1.mapOpened) {
                                    embedMap(
                                        'etoRoute1Map',
                                        etoData.booking.route1.address.start,
                                        etoData.booking.route1.address.end,
                                        etoData.booking.route1.waypoints,
                                        response.booking.route1.status
                                    );
                                }

                                // Directions
                                if (etoData.booking.route1.stepsText) {
                                    $('#'+ etoData.mainContainer +' #etoRoute1MapDirections').html(etoData.booking.route1.stepsText);

                                    // $('#'+ etoData.mainContainer +' #etoRoute1MapDirections').show();
                                    // $('#'+ etoData.mainContainer +' #etoRoute1MapContainer .etoMapStyle2Button').show();
                                }
                                else {
                                    // $('#'+ etoData.mainContainer +' #etoRoute1MapDirections').hide();
                                    // $('#'+ etoData.mainContainer +' #etoRoute1MapContainer .etoMapStyle2Button').hide();
                                }
                            }


                            if (etoData.booking.route2) {
                                etoData.booking.route2.extraChargesList = response.booking.route2.extraChargesList;
                                etoData.booking.route2.extraChargesPrice = response.booking.route2.extraChargesPrice;
                                etoData.booking.route2.totalPrice = response.booking.route2.totalPrice;
                                etoData.booking.route2.totalPriceWithDiscount = response.booking.route2.totalPriceWithDiscount;
                                etoData.booking.route2.totalDiscount = response.booking.route2.totalDiscount;
                                etoData.booking.route2.accountDiscount = response.booking.route2.accountDiscount;
                                etoData.booking.route2.distance = response.booking.route2.distance;
                                etoData.booking.route2.duration = response.booking.route2.duration;
                                etoData.booking.route2.distance_base_start = response.booking.route2.distance_base_start;
                                etoData.booking.route2.duration_base_start = response.booking.route2.duration_base_start;
                                etoData.booking.route2.distance_base_end = response.booking.route2.distance_base_end;
                                etoData.booking.route2.duration_base_end = response.booking.route2.duration_base_end;
                                etoData.booking.route2.address.start = response.booking.route2.address.start;
                                etoData.booking.route2.address.end = response.booking.route2.address.end;
                                etoData.booking.route2.coordinate.start.lat = response.booking.route2.coordinate.start.lat;
                                etoData.booking.route2.coordinate.start.lon = response.booking.route2.coordinate.start.lon;
                                etoData.booking.route2.coordinate.end.lat = response.booking.route2.coordinate.end.lat;
                                etoData.booking.route2.coordinate.end.lon = response.booking.route2.coordinate.end.lon;
                                etoData.booking.route2.vehicleButtons = response.booking.route2.vehicleButtons;
                                etoData.booking.route2.stepsText = response.booking.route2.stepsText;
                                etoData.booking.route2.isAirport = parseInt(response.booking.route2.isAirport);
                                etoData.booking.route2.isAirport2 = parseInt(response.booking.route2.isAirport2);
                                etoData.booking.route2.excludedRouteAllowed = parseInt(response.booking.route2.excludedRouteAllowed);
                                etoData.booking.route2.meetingPoint = response.booking.route2.meetingPoint;

                                if (etoData.booking.route2.isAirport == 1) {
                                    $('#'+ etoData.mainContainer +' #etoRoute2FlightNumberContainer, ' +
                                      '#'+ etoData.mainContainer +' #etoRoute2FlightLandingTimeContainer, ' +
                                      '#'+ etoData.mainContainer +' #etoRoute2DepartureCityContainer, ' +
                                      '#'+ etoData.mainContainer +' #etoRoute2WaitingTimeContainer, ' +
                                      // '#'+ etoData.mainContainer +' #etoRoute2MeetingPointContainer, ' +
                                      '#'+ etoData.mainContainer +' #etoRoute2MeetAndGreetContainer').show();
                                }

                                if (etoData.booking.route2.isAirport2 == 1) {
                                    $('#'+ etoData.mainContainer +' #etoRoute2DepartureFlightNumberContainer, ' +
                                      '#'+ etoData.mainContainer +' #etoRoute2DepartureFlightTimeContainer, ' +
                                      '#'+ etoData.mainContainer +' #etoRoute2DepartureFlightCityContainer').show();
                                }

                                if (parseInt(etoData.request.config.booking_meet_and_greet_compulsory) > 0) {
                                    $('#'+ etoData.mainContainer +' #etoRoute2MeetAndGreetContainer').hide();
                                    if (etoData.booking.route2.isAirport == 1 || etoData.booking.route2.category.type.start == 'airport') {
                                        $('#'+ etoData.mainContainer +' #etoRoute2MeetAndGreet').attr('checked', true);
                                    } else {
                                        $('#'+ etoData.mainContainer +' #etoRoute2MeetAndGreet').attr('checked', false);
                                    }
                                }

                                if (parseInt(etoData.request.config.booking_meet_and_greet_enable) == 0) {
                                    $('#'+ etoData.mainContainer +' #etoRoute2MeetAndGreetContainer').hide();
                                    $('#'+ etoData.mainContainer +' #etoRoute2MeetAndGreet').attr('checked', false);
                                }

                                if (etoData.booking.route2.meetingPoint) {
                                    $('#'+ etoData.mainContainer +' #etoRoute2MeetingPointContainer .etoInnerContainer').html(etoData.booking.route2.meetingPoint);
                                    $('#'+ etoData.mainContainer +' #etoRoute2MeetingPointContainer').show();
                                } else {
                                    $('#'+ etoData.mainContainer +' #etoRoute2MeetingPointContainer').hide();
                                }

                                // Map
                                if (etoData.booking.route2.mapOpened) {
                                    embedMap(
                                        'etoRoute2Map',
                                        etoData.booking.route2.address.start,
                                        etoData.booking.route2.address.end,
                                        etoData.booking.route2.waypoints,
                                        response.booking.route2.status
                                    );
                                }

                                // Directions
                                if (etoData.booking.route2.stepsText) {
                                    $('#'+ etoData.mainContainer +' #etoRoute2MapDirections').html(etoData.booking.route2.stepsText);
                                    $('#'+ etoData.mainContainer +' #etoRoute2MapDirections').show();
                                    $('#'+ etoData.mainContainer +' #etoRoute2MapContainer .etoMapStyle2Button').show();
                                }
                                else {
                                    $('#'+ etoData.mainContainer +' #etoRoute2MapDirections').hide();
                                    $('#'+ etoData.mainContainer +' #etoRoute2MapContainer .etoMapStyle2Button').hide();
                                }
                            }

                            etoData.booking.paymentButtons = response.booking.paymentButtons;
                            etoData.booking.totalPrice = response.booking.totalPrice;
                            etoData.booking.totalPriceWithDiscount = response.booking.totalPriceWithDiscount;
                            etoData.booking.totalDiscount = response.booking.totalDiscount;
                            etoData.booking.discountId = response.booking.discountId;
                            etoData.booking.discountCode = response.booking.discountCode;

                            var msg = '';

                            if (response.booking.discountMessage) {
                                if (response.booking.discountStatus) {
                                    var type = 'success';
                                } else {
                                    var type = 'warning';
                                }
                                msg += '<p class="alert alert-' + type + '">' + response.booking.discountMessage;

                                if (typeof  response.booking.discountExcludedInfo != 'undefined' && response.booking.discountExcludedInfo !== false) {
                                    msg += '<span class="eto-field-btn eto-field-btn-help" \
                                        title="'+response.booking.discountExcludedInfo+'">\
                                    <i class="fa fa-info-circle"></i></span>';
                                }
                                msg += '</p>';
                            }

                            if (response.booking.discountReturnMessage) {
                                msg += '<p class="alert alert-success">' + response.booking.discountReturnMessage + '</p>';
                            }

                            if (response.booking.discountAccountMessage) {
                                msg += '<p class="alert alert-success">' + response.booking.discountAccountMessage + '</p>';
                            }

                            $('#'+ etoData.mainContainer +' #etoDiscountCodeInfoLoader').html(msg);
                        }

                        if (etoData.debug) {
                            console.log(response);
                        }
                    },
                    error: function(response) {
                        etoData.message.push('AJAX error: Quote '+ JSON.stringify(response));
                    }
                });

                // Extra charges - R1
                var listHtml = "";
                if (etoData.booking.route1.extraChargesList) {
                    $.each(etoData.booking.route1.extraChargesList, function(key, value) {
                        listHtml += '<li>' + String(value.name + (value.amount > 1 ? ' (x'+ value.amount +')' : '') + (value.total > 0 ? ' '+ etoData.request.config.currency_symbol + value.total + etoData.request.config.currency_code : '')) + '</li>';
                    });
                }
                if (listHtml) {
                    listHtml = '<ul>' + listHtml + '</ul>';
                }
                $('#'+ etoData.mainContainer +' #etoRoute1ExtraCharges').val(JSON.stringify(etoData.booking.route1.extraChargesList));
                $('#'+ etoData.mainContainer +' #etoRoute1ExtraChargesDisplay').html(listHtml);
                if (listHtml) {
                    $('#'+ etoData.mainContainer +' #etoRoute1ExtraChargesContainer').show();
                } else {
                    $('#'+ etoData.mainContainer +' #etoRoute1ExtraChargesContainer').hide();
                }

                // Extra charges - R2
                var listHtml = "";
                if (etoData.booking.route2.extraChargesList) {
                    $.each(etoData.booking.route2.extraChargesList, function(key, value) {
                        listHtml += '<li>' + String(value.name + (value.amount > 1 ? ' (x'+ value.amount +')' : '') + (value.total > 0 ? ' '+ etoData.request.config.currency_symbol + value.total + etoData.request.config.currency_code : '')) + '</li>';
                    });
                }
                if (listHtml) {
                    listHtml = '<ul>' + listHtml + '</ul>';
                }
                $('#'+ etoData.mainContainer +' #etoRoute2ExtraCharges').val(JSON.stringify(etoData.booking.route2.extraChargesList));
                $('#'+ etoData.mainContainer +' #etoRoute2ExtraChargesDisplay').html(listHtml);
                if (listHtml) {
                    $('#'+ etoData.mainContainer +' #etoRoute2ExtraChargesContainer').show();
                } else {
                    $('#'+ etoData.mainContainer +' #etoRoute2ExtraChargesContainer').hide();
                }

                if(etoData.request.config.booking_display_book_button == 0) {
                    if(!$('#etoForm').hasClass('etoJourneyTypeReturn')) {
                        $('#etoStep2Container #etoButtonsContainer').hide();
                    }
                    else {
                        $('#etoStep2Container #etoButtonsContainer').show();
                    }
                }

                var r1VehicleInactive = 0;
                var r1VehicleHidden = 0;
                var r2VehicleInactive = 0;
                var r2VehicleHidden = 0;

                // Single vehicle price - start
                if (etoData.booking.route1.vehicleButtons) {
                    var vehicleAmount = 0;
                    var vehicleID = 0;
                    var hideSelect = 0;
                    var vehicleInactive = 0;
                    var vehicleHidden = 0;

                    $.each(etoData.booking.route1.vehicleButtons, function(key, value) {
                        var vc = $('#'+ etoData.mainContainer +' #etoRoute1VehicleContainer .etoVehicleContainer' + value.id);

                        vc.off('click');

                        if (!scheduled) {
                            if (value.linkType != '') {
                                vc.find('select#etoRoute1Vehicle'+ value.id).val(0);
                                vc.find('.etoVehicleInnerContainerSelected').removeClass('etoVehicleInnerContainerSelected');
                            }
                            else {
                                vc.on('click', function(e) {
                                    $(this).find('select#etoRoute1Vehicle'+ value.id).val(1).change();

                                    if(etoData.request.config.booking_display_book_button == 0) {
                                        if(!$('#etoForm').hasClass('etoJourneyTypeReturn')) {
                                            $('#etoQuoteStep2Button').click();
                                            $('#etoStep2Container #etoButtonsContainer').hide();
                                        }
                                        else {
                                            $('#etoStep2Container #etoButtonsContainer').show();
                                        }
                                    }

                                    e.preventDefault();
                                    return false;
                                });
                            }
                        }

                        if (value.hidden) {
                            vc.find('select#etoRoute1Vehicle'+ value.id).val(0);
                            vc.find('.etoVehicleInnerContainerSelected').removeClass('etoVehicleInnerContainerSelected');
                            vc.hide();
                        }
                        else {
                            vc.show();
                        }

                        var msgTop = '';
                        var msgBottom = '';
                        var msgBottomV2 = etoLang('BTN_RESERVE');
                        var msgTotalPrice = 0;

                        if (value.linkType == 'scheduled_sold' || value.linkType == 'scheduled_available') {
                            var cls = '';

                            if (value.linkType == 'scheduled_sold') {
                              hideSelect = 1;
                              cls = 'class="text-danger"';
                            }

                            msgTop = '<span '+ cls +'>' + value.linkUrl + '</span>';

                            etoData.displayMessage = 0;
                            etoData.displayHighlight = 0;

                            if (value.hidden == 0) {
                                vehicleAmount += 1;
                                vehicleID = value.id;
                            }
                        }
                        else if (value.linkType == 'enquire') {
                            msgTop = etoData.request.config.booking_hide_vehicle_not_available_message == 0 ? '<span class="eto-vehicle-not-available-msg">' + etoLang('bookingVehicle_NotAvailable') + '</span>' : '';
                            msgBottom = '<a target="_blank" href="' + value.linkUrl + '" class="btn btn-primary btn-sm" style="text-transform:uppercase;">' + etoLang('bookingVehicle_LinkEnquire') + '</a>';
                            msgBottomV2 = '<a target="_blank" href="' + value.linkUrl + '">' + etoLang('bookingVehicle_LinkEnquire') + '</a>';
                        }
                        else if (value.linkType == 'availability') {
                            msgTop = '<span>' + etoLang('bookingVehicle_Booked') + '</span>';
                            msgBottom = '<a href="' + value.linkUrl + '" class="btn btn-primary btn-sm popupAvailability" style="text-transform:uppercase;">' + etoLang('bookingVehicle_LinkAvailability') + '</a>';
                            msgBottomV2 = '<a href="' + value.linkUrl + '" class="popupAvailability">' + etoLang('bookingVehicle_LinkAvailability') + '</a>';
                        }
                        else {
                            msgTotalPrice = value.total;

                            if (scheduled) {
                                msgTop = etoData.request.config.currency_symbol + value.ticketPrice + etoData.request.config.currency_code;
                            }
                            else {
                                msgTop = etoData.request.config.currency_symbol + value.total + etoData.request.config.currency_code;
                            }

                            if (vc.find('select#etoRoute1Vehicle' + value.id).val() > 0) {
                                msgBottom = '<button class="btn btn-primary etoVehicleSelectButton"><i class="ion-ios-checkmark-empty"></i></button>';
                            } else {
                                msgBottom = '<button class="btn btn-primary etoVehicleSelectButton"><i class="fa"></i></button>';
                            }

                            if (value.hidden == 0) {
                                vehicleAmount += 1;
                                vehicleID = value.id;
                            }
                        }

                        if (value.hidden || value.linkType == 'enquire' || value.linkType == 'availability') {
                            vehicleInactive++;
                        }
                        if (value.hidden) {
                            vehicleHidden++;
                        }

                        $('#'+ etoData.mainContainer +' #etoRoute1Vehicle' + value.id + 'TotalPrice').attr('total_price', msgTotalPrice).html(msgTop);
                        $('#'+ etoData.mainContainer +' #etoRoute1Vehicle' + value.id + 'MsgBottom').html(msgBottom);
                        $('#'+ etoData.mainContainer +' #etoRoute1VehicleContainer .etoVehicleContainer' + value.id + ' .eto-v2-vehicle-reserve > span').html(msgBottomV2);
                    });

                    // Auto select vehicle if only one is avaiable
                    if (vehicleAmount == 1 && vehicleID > 0 && $('#'+ etoData.mainContainer +' #etoRoute1VehicleContainer .etoVehicleContainer' + vehicleID +' select.etoVehicleSelect').val() <= 0) {
                        if (scheduled) {
                            $('#'+ etoData.mainContainer +' #etoRoute1VehicleContainer .etoVehicleContainer' + vehicleID +' select.etoVehicleSelect').val(1).change();
                        }
                        else {
                            $('#'+ etoData.mainContainer +' #etoRoute1VehicleContainer .etoVehicleContainer' + vehicleID).click();
                        }
                    }

                    if (hideSelect) {
                        $('#etoPassengersGhost').hide();
                    }
                    else {
                        $('#etoPassengersGhost').show();
                    }

                    if (vehicleInactive >= etoData.booking.route1.vehicleButtons.length) {
                        r1VehicleInactive = 1;
                    }
                    if (vehicleHidden >= etoData.booking.route1.vehicleButtons.length) {
                        r1VehicleHidden = 1;
                    }
                }

                if (etoData.booking.route2.vehicleButtons) {
                    var vehicleAmount = 0;
                    var vehicleID = 0;
                    var vehicleInactive = 0;
                    var vehicleHidden = 0;

                    $.each(etoData.booking.route2.vehicleButtons, function(key, value) {
                        var vc = $('#'+ etoData.mainContainer +' #etoRoute2VehicleContainer .etoVehicleContainer' + value.id);

                        vc.off('click');

                        if (value.linkType != '') {
                            vc.find('select#etoRoute2Vehicle' + value.id).val(0);
                            vc.find('.etoVehicleInnerContainerSelected').removeClass('etoVehicleInnerContainerSelected');
                        } else {
                            vc.on('click', function(e) {
                                $(this).find('select#etoRoute2Vehicle'+ value.id).val(1).change();
                                e.preventDefault();
                                return false;
                            });
                        }

                        if (value.hidden) {
                            vc.find('select#etoRoute2Vehicle'+ value.id).val(0);
                            vc.find('.etoVehicleInnerContainerSelected').removeClass('etoVehicleInnerContainerSelected');
                            vc.hide();
                        }
                        else {
                            vc.show();
                        }

                        var msgTop = '';
                        var msgBottom = '';
                        var msgBottomV2 = etoLang('BTN_RESERVE');
                        var msgTotalPrice = 0;

                        if (value.linkType == 'enquire') {
                            msgTop = etoData.request.config.booking_hide_vehicle_not_available_message == 0 ? '<span class="eto-vehicle-not-available-msg">' + etoLang('bookingVehicle_NotAvailable') + '</span>' : '';
                            msgBottom = '<a target="_blank" href="' + value.linkUrl + '" class="btn btn-primary btn-sm" style="text-transform:uppercase;">' + etoLang('bookingVehicle_LinkEnquire') + '</a>';
                            msgBottomV2 = '<a target="_blank" href="' + value.linkUrl + '">' + etoLang('bookingVehicle_LinkEnquire') + '</a>';
                        }
                        else if (value.linkType == 'availability') {
                            msgTop = '<span>' + etoLang('bookingVehicle_Booked') + '</span>';
                            msgBottom = '<a href="' + value.linkUrl + '" class="btn btn-primary btn-sm popupAvailability" style="text-transform:uppercase;">' + etoLang('bookingVehicle_LinkAvailability') + '</a>';
                            msgBottomV2 = '<a href="' + value.linkUrl + '" class="popupAvailability">' + etoLang('bookingVehicle_LinkAvailability') + '</a>';
                        }
                        else {
                            msgTotalPrice = value.total;

                            if (scheduled) {
                                msgTop = etoData.request.config.currency_symbol + value.ticketPrice + etoData.request.config.currency_code;
                            }
                            else {
                                msgTop = etoData.request.config.currency_symbol + value.total + etoData.request.config.currency_code;
                            }

                            if (vc.find('select#etoRoute2Vehicle' + value.id).val() > 0) {
                                msgBottom = '<button class="btn btn-primary etoVehicleSelectButton"><i class="ion-ios-checkmark-empty"></i></button>';
                            } else {
                                msgBottom = '<button class="btn btn-primary etoVehicleSelectButton"><i class="fa"></i></button>';
                            }

                            if (value.hidden == 0) {
                                vehicleAmount += 1;
                                vehicleID = value.id;
                            }
                        }

                        if (value.hidden || value.linkType == 'enquire' || value.linkType == 'availability') {
                            vehicleInactive++;
                        }
                        if (value.hidden) {
                            vehicleHidden++;
                        }

                        $('#'+ etoData.mainContainer +' #etoRoute2Vehicle' + value.id + 'TotalPrice').attr('total_price', msgTotalPrice).html(msgTop);
                        $('#'+ etoData.mainContainer +' #etoRoute2Vehicle' + value.id + 'MsgBottom').html(msgBottom);
                        $('#'+ etoData.mainContainer +' #etoRoute2VehicleContainer .etoVehicleContainer' + value.id + ' .eto-v2-vehicle-reserve > span').html(msgBottomV2);
                    });

                    // Auto select vehicle if only one is avaiable
                    if (vehicleAmount == 1 && vehicleID > 0 && $('#'+ etoData.mainContainer +' #etoRoute2VehicleContainer .etoVehicleContainer' + vehicleID +' select.etoVehicleSelect').val() <= 0) {
                        $('#'+ etoData.mainContainer +' #etoRoute2VehicleContainer .etoVehicleContainer' + vehicleID).click();
                    }

                    if (vehicleInactive >= etoData.booking.route2.vehicleButtons.length) {
                        r2VehicleInactive = 1;
                    }
                    if (vehicleHidden >= etoData.booking.route2.vehicleButtons.length) {
                        r2VehicleHidden = 1;
                    }
                }
                // Single vehicle price - end

                if (r1VehicleInactive == 1 && r2VehicleInactive == 1) {
                    $('#etoStep2Container #etoButtonsContainer').addClass('hidden');
                }
                else {
                    $('#etoStep2Container #etoButtonsContainer').removeClass('hidden');
                }

                $('#etoRoute1VehicleContainer .etoNoVehiclesMessage').remove();
                if (r1VehicleHidden == 1) {
                    $('#etoRoute1VehicleContainer .etoInnerContainer').prepend('<div class="etoNoVehiclesMessage"><div>'+ etoLang('bookingNoVehiclesAvailable') +'</div><a href="#" class="etoGoBackButton btn btn-primary">'+ etoLang('bookingButton_Back') +'<a></div>');
                }

                $('#etoRoute2VehicleContainer .etoNoVehiclesMessage').remove();
                if (r2VehicleHidden == 1) {
                    $('#etoRoute2VehicleContainer .etoInnerContainer').prepend('<div class="etoNoVehiclesMessage"><div>'+ etoLang('bookingNoVehiclesAvailable') +'</div><a href="#" class="etoGoBackButton btn btn-primary">'+ etoLang('bookingButton_Back') +'<a></div>');
                }

                // Add modal window
                if ($('#'+ etoData.mainContainer +' #etoAvailabilityModal').length <= 0) {
                    $('#' + etoData.mainContainer).append(
                        '<div id="etoAvailabilityModal" class="modal fade" role="dialog" aria-labelledby="etoAvailabilityModalTitle" aria-hidden="true">' +
                        '<div class="modal-dialog">' +
                        '<div class="modal-content">' +
                        '<div class="modal-header">' +
                        '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>' +
                        // '<h4 class="modal-title"></h4>' +
                        '</div>' +
                        '<div class="modal-body">' +
                        '<iframe src="" frameborder="0" height="600" width="100%"></iframe>' +
                        '</div>' +
                        '</div>' +
                        '</div>' +
                        '</div>'
                    );
                }

                $('.popupAvailability').click(function(e) {
                    var frametarget = $(this).attr('href');
                    var targetmodal = $(this).attr('target');

                    if (targetmodal == undefined) {
                        targetmodal = '#etoAvailabilityModal';
                    } else {
                        targetmodal = '#' + targetmodal;
                    }

                    if ($(this).attr('title') != undefined) {
                        $(targetmodal + ' .modal-header .modal-title').html($(this).attr('title'));
                        // $(targetmodal +' .modal-header').show();
                    } else {
                        $(targetmodal + ' .modal-header .modal-title').html('');
                        // $(targetmodal +' .modal-header').hide();
                    }

                    $(targetmodal + ' iframe').attr('src', frametarget);

                    $(targetmodal).modal({
                        show: true
                    });

                    e.preventDefault();
                    return false;
                });


                // Price display
                var text = '';
                if (etoData.booking.route1.totalPrice > 0) {
                    text = etoData.request.config.currency_symbol + etoData.booking.route1.totalPrice + etoData.request.config.currency_code;
                    if (etoData.booking.route1.accountDiscount > 0) {
                        text += ' <span style="color:#ABABAB;font-weight:normal;">( <s>' + etoData.request.config.currency_symbol + ((etoData.booking.route1.totalPrice + etoData.booking.route1.accountDiscount).toFixed(2)) + etoData.request.config.currency_code + '</s> )</span>';
                    }
                } else {
                    text = '<span class="etoEmptyPrice">' + etoData.request.config.currency_symbol + '0' + etoData.request.config.currency_code + '</span>';
                }
                $('#'+ etoData.mainContainer +' #etoRoute1TotalPriceDisplay').html(text);


                text = '';
                if (etoData.booking.route2.totalPrice > 0) {
                    text = etoData.request.config.currency_symbol + etoData.booking.route2.totalPrice + etoData.request.config.currency_code;
                    if (etoData.booking.route2.accountDiscount > 0) {
                        text += ' <span style="color:#ABABAB;font-weight:normal;">( <s>' + etoData.request.config.currency_symbol + ((etoData.booking.route2.totalPrice + etoData.booking.route2.accountDiscount).toFixed(2)) + etoData.request.config.currency_code + '</s> )</span>';
                    }
                } else {
                    text = '<span class="etoEmptyPrice">' + etoData.request.config.currency_symbol + '0' + etoData.request.config.currency_code + '</span>';
                }
                $('#'+ etoData.mainContainer +' #etoRoute2TotalPriceDisplay').html(text);


                text = '';
                if (etoData.booking.totalPrice != etoData.booking.totalPriceWithDiscount) {
                    text += '<span class="etoPaymentInfoDiscount">' + etoData.request.config.currency_symbol + etoData.booking.totalPrice + etoData.request.config.currency_code + '</span>';
                }
                text += etoData.request.config.currency_symbol + etoData.booking.totalPriceWithDiscount + etoData.request.config.currency_code;
                $('#'+ etoData.mainContainer +' #etoTotalPriceDisplay').html(text);


                if (etoData.booking.paymentButtons) {
                    $.each(etoData.booking.paymentButtons, function(key, value) {
                        var tHtml = '';

                        // Price
                        tHtml += '<span class="etoPaymentInfoPrice1"></span>';
                        tHtml += '<div class="etoPaymentInfoPrice2">';
                        if (value.totalOriginal != value.total) {
                            tHtml += '<span class="etoPaymentInfoDiscount">' + etoData.request.config.currency_symbol + value.totalOriginal + etoData.request.config.currency_code + '</span>';
                        }
                        tHtml += etoData.request.config.currency_symbol + value.total + etoData.request.config.currency_code;
                        tHtml += '</div>';

                        $('#'+ etoData.mainContainer +' #etoPayment' + value.id + 'TotalPrice').html(tHtml);

                        // Deposit
                        var onchange = 'if( $(this).find(\'option:selected\').attr(\'payment_charge\') > 0 ) {' +
                            '$(\'#etoPayment' + value.id + 'TotalPrice .etoPaymentInfoPrice1\').html(\'( +' + etoData.request.config.currency_symbol + '\'+ $(this).find(\'option:selected\').attr(\'payment_charge\') +\'' + etoData.request.config.currency_code + ' )\').show();' +
                            '} else {' +
                            '$(\'#etoPayment' + value.id + 'TotalPrice .etoPaymentInfoPrice1\').html(\'\').hide();' +
                            '};';
                        var selectDeposit = '';
                        var selectFullAmount = '';

                        var dHtml = '<select class="form-control" name="totalDeposit[' + value.id + ']" onchange="' + onchange + '">';
                        if (value.depositSelected == 'deposit') {
                            selectDeposit = 'selected="selected"';
                        } else {
                            selectFullAmount = 'selected="selected"';
                        }

                        if (value.deposit > 0) {
                            dHtml += '<option value="' + value.deposit + '" ' + selectDeposit + ' payment_charge="' + value.depositCharge + '">';
                            dHtml += etoLang('bookingPayDeposit') + ' ' + etoData.request.config.currency_symbol + value.deposit + etoData.request.config.currency_code;
                            dHtml += '</option>';
                        } else {
                            selectDeposit = '';
                            selectFullAmount = 'selected="selected"';
                        }

                        dHtml += '<option value="0" ' + selectFullAmount + ' payment_charge="' + value.totalCharge + '">';
                        dHtml += etoLang('bookingPayFullAmount') + ' ' + etoData.request.config.currency_symbol + value.total + etoData.request.config.currency_code;
                        dHtml += '</option>';
                        dHtml += '</select>';

                        $('.etoPayment' + value.id + 'Deposit').html(dHtml);

                        // Set payment charge
                        var paymentCharge = 0;

                        if (selectDeposit != '') {
                            if (value.depositCharge > 0) {
                                paymentCharge = value.depositCharge;
                            }
                        } else {
                            if (value.totalCharge > 0) {
                                paymentCharge = value.totalCharge;
                            }
                        }

                        if (paymentCharge) {
                            $('#etoPayment' + value.id + 'TotalPrice .etoPaymentInfoPrice1').html('( +' + etoData.request.config.currency_symbol + paymentCharge + etoData.request.config.currency_code + ' )').show();
                        } else {
                            $('#etoPayment' + value.id + 'TotalPrice .etoPaymentInfoPrice1').html('').hide();
                        }

                        // Hide dropdown if no deposit
                        if (value.deposit > 0) {
                            $('.etoPayment' + value.id + 'Deposit').show();
                        } else {
                            $('.etoPayment' + value.id + 'Deposit').hide();
                        }

                        // Hide method
                        if (value.hidden > 0) {
                            $('.etoPayment' + value.id + 'MainContainer').hide();
                        } else {
                            $('.etoPayment' + value.id + 'MainContainer').show();
                        }
                    });
                }

                // Price update
                $('#'+ etoData.mainContainer +' #etoRoute1TotalPrice').val(etoData.booking.route1.totalPrice);
                $('#'+ etoData.mainContainer +' #etoRoute2TotalPrice').val(etoData.booking.route2.totalPrice);
                $('#'+ etoData.mainContainer +' #etoTotalPrice').val(etoData.booking.totalPrice);

                // Price show / hide
                if (etoData.booking.route1.totalPrice > 0) {
                    $('#'+ etoData.mainContainer +' #etoRoute1TotalPriceContainer').show();
                }

                if (etoData.booking.route2.totalPrice > 0) {
                    $('#'+ etoData.mainContainer +' #etoRoute2TotalPriceContainer').show();
                }

                // if (etoData.booking.totalPrice > 0) {
                //$('#'+ etoData.mainContainer +' #etoTotalPriceContainer').show();
                //   $('#'+ etoData.mainContainer +' #etoTotalPriceContainer').hide();
                // }

            }

            // console.log(etoData.booking);

            // Quote - end
        }

        // Vehicle checkout total - start
        if (etoData.layout != 'minimal') {
            var vehicleCheckoutTotal = 0;
            if ($('#'+ etoData.mainContainer +' #etoRoute1VehicleContainer .etoVehicleInnerContainerSelected .etoVehicleTotalPrice').attr('total_price')) {
                var priceTotal = parseFloat($('#'+ etoData.mainContainer +' #etoRoute1VehicleContainer .etoVehicleInnerContainerSelected .etoVehicleTotalPrice').attr('total_price'));
                vehicleCheckoutTotal += isNaN(priceTotal) ? 0 : priceTotal;
            }
            if (etoData.booking.routeReturn == 2) {
                if ($('#'+ etoData.mainContainer +' #etoRoute2VehicleContainer .etoVehicleInnerContainerSelected .etoVehicleTotalPrice').attr('total_price')) {
                    var priceTotal = parseFloat($('#'+ etoData.mainContainer +' #etoRoute2VehicleContainer .etoVehicleInnerContainerSelected .etoVehicleTotalPrice').attr('total_price'));
                    vehicleCheckoutTotal += isNaN(priceTotal) ? 0 : priceTotal;
                }
            }
            vehicleCheckoutTotal = vehicleCheckoutTotal.toFixed(2);
            $('#'+ etoData.mainContainer +' #etoVehicleCheckoutTotal').html('<span>' + etoLang('bookingField_Total') + ': ' + etoData.request.config.currency_symbol + vehicleCheckoutTotal + etoData.request.config.currency_code + '</span>');
        }
        // Vehicle checkout total - end

        // Show quote/submit button
        $('#'+ etoData.mainContainer +' #etoQuoteButtonStep3Container').hide();
        $('#'+ etoData.mainContainer +' #etoSubmitButtonContainer').show();

        if (etoData.layout != 'minimal' && (!etoError() || etoData.dynamicQuote == 0 && etoData.quoteStatus == 0)) {
            $('#'+ etoData.mainContainer +' #etoQuoteButtonStep3Container').show();
            $('#'+ etoData.mainContainer +' #etoSubmitButtonContainer').hide();
        }

        // Terms
        if (parseInt(etoData.request.config.terms_enable) == 1) {
            $('#'+ etoData.mainContainer +' #etoTermsContainer').show();
        } else {
            $('#'+ etoData.mainContainer +' #etoTermsContainer').hide();
        }
    }

    function etoSubmit() {

        if (etoData.debug) {
            console.log('submit');
        }

        if (etoData.layout == 'minimal') {

            etoError();

            if (etoData.submitOK == 0) {
                return false;
            }

            if (etoData.booking.routeReturn >= 2) {
                var urlParams = {
                    's': etoData.booking.serviceId,
                    'sd': etoData.booking.serviceDuration,
                    'r': etoData.booking.routeReturn,
                    'r1cs': etoData.booking.route1.category.start,
                    'r1ls': etoData.booking.route1.location.start,
                    'r1ce': etoData.booking.route1.category.end,
                    'r1le': etoData.booking.route1.location.end,
                    'r1wp': etoData.booking.route1.waypoints,
                    'r1d': etoData.booking.route1.date,
                    'r1ps': etoData.booking.route1.placeId.start,
                    'r1pe': etoData.booking.route1.placeId.end,
                    'r1pwp': etoData.booking.route1.waypointsPlaceId,
                    'r2cs': etoData.booking.route2.category.start,
                    'r2ls': etoData.booking.route2.location.start,
                    'r2ce': etoData.booking.route2.category.end,
                    'r2le': etoData.booking.route2.location.end,
                    'r2wp': etoData.booking.route2.waypoints,
                    'r2d': etoData.booking.route2.date,
                    'r2ps': etoData.booking.route2.placeId.start,
                    'r2pe': etoData.booking.route2.placeId.end,
                    'r2pwp': etoData.booking.route2.waypointsPlaceId
                };
            }
            else {
                var urlParams = {
                    's': etoData.booking.serviceId,
                    'sd': etoData.booking.serviceDuration,
                    'r': etoData.booking.routeReturn,
                    'r1cs': etoData.booking.route1.category.start,
                    'r1ls': etoData.booking.route1.location.start,
                    'r1ce': etoData.booking.route1.category.end,
                    'r1le': etoData.booking.route1.location.end,
                    'r1wp': etoData.booking.route1.waypoints,
                    'r1d': etoData.booking.route1.date,
                    'r1ps': etoData.booking.route1.placeId.start,
                    'r1pe': etoData.booking.route1.placeId.end,
                    'r1pwp': etoData.booking.route1.waypointsPlaceId
                };
            }

            if (etoData.request.config.booking_show_preferred) {
                urlParams.maxp = etoData.booking.preferred.passengers;
                urlParams.maxl = etoData.booking.preferred.luggage;
                urlParams.maxh = etoData.booking.preferred.handLuggage;
            }

            if (etoData.request.config.site_key) {
                urlParams.site_key = etoData.request.config.site_key;
            }

            var urlParams = JSON.stringify(urlParams);
            urlParams = JSON.parse(urlParams);
            for (var i in urlParams) {
                if (urlParams[i] == '' ||
                    urlParams[i] === null ||
                    urlParams[i] === undefined) {
                    delete urlParams[i];
                }
            }

            var url = EasyTaxiOffice.appPath + '/booking' +'?'+ $.param(urlParams);
            url = encodeURI(url);
            url = url.replace(/'/g, '%27');

            var a = document.createElement('a');
            a.href = url;
            a.target = '_top';
            document.body.appendChild(a);
            a.click();

        }
        else {

            if (etoData.submitOK == 0 || $isSubmitReady == 0) {
                return false;
            }

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': _token
                },
                url: etoData.apiURL,
                type: 'POST',
                data: {
                    task: 'submit',
                    booking: JSON.stringify(etoData.booking),
                    manualQuote: etoData.manualQuote
                },
                dataType: 'json',
                success: function(response) {
                    if (response.message.length > 0) {
                        $.each(response.message, function(key, value) {
                            etoData.message.push(value);
                        });
                    }

                    if (response.finishType && response.bID) {
                        if (etoData.debug) {
                            console.log('saved');
                        }

                        var url = EasyTaxiOffice.appPath + '/booking' + '?finishType='+ response.finishType + '&bID='+ response.bID;
                        if( response.tID ) {
                            url += '&tID='+ response.tID;
                        }

                        if (etoData.request.config.site_key) {
                            url += '&site_key='+ etoData.request.config.site_key;
                        }

                        url = encodeURI(url);
                        url = url.replace(/'/g, '%27');

                        var a = document.createElement('a');
                        a.href = url;
                        a.target = '_top';
                        document.body.appendChild(a);
                        a.click();
                    }

                    if (etoData.debug) {
                        console.log(response);
                    }
                },
                error: function(response) {
                    etoData.message.push('AJAX error: Submit');
                },
                beforeSend: function() {
                    $isSubmitReady = 0;
                    $('.eto-main-container').LoadingOverlay('show');
                },
                complete: function() {
                    $isSubmitReady = 1;
                    setTimeout(function() {
                        $('.eto-main-container').LoadingOverlay('hide');
                    }, 3000);
                }
            });

        }

        return false;
    }

    function etoError() {

        if (etoData.debug) {
            console.log('error');
        }

        // Errors
        var emailFilter = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        var mobileFilter = /^-?\d+$/;
        var error = '';

        var minBookingTimeLimit = 0;

        if (etoData.request.config.min_booking_time_limit) {
            minBookingTimeLimit = parseInt(etoData.request.config.min_booking_time_limit);
        }

        if ($('#'+ etoData.mainContainer +' #etoForm').hasClass('etoJourneyTypeScheduled')) {
            var scheduled = 1;
        }
        else {
            var scheduled = 0;
        }

        $('.eto-v2-payment-method').last().after($('.eto-v2-payment-method-account'));

        // Account payment - start
        if (parseInt(etoData.userId) > 0 && etoData.isCompany > 0 && etoData.isAccountPayment > 0) {
            $('#'+ etoData.mainContainer +' .eto-v2-payment-method-account').removeClass('hide');
        } else {
            $('#'+ etoData.mainContainer +' .eto-v2-payment-method-account').addClass('hide');
        }
        // Account payment - end


        // Cash payment - start
        if (parseInt(etoData.request.config.booking_hide_cash_payment_if_airport) == 1) {
            if (
                etoData.booking.route1.isAirport == 1 ||
                etoData.booking.route1.category.type.start == 'airport' ||
                etoData.booking.route2.isAirport == 1 ||
                etoData.booking.route2.category.type.start == 'airport'
            ) {
                $('#'+ etoData.mainContainer +' .eto-v2-payment-method-cash').addClass('hide');
            } else {
                $('#'+ etoData.mainContainer +' .eto-v2-payment-method-cash').removeClass('hide');
            }
        }
        // Cash payment - end


        $('.eto-v2-payment-method-first-box').removeClass('eto-v2-payment-method-first-box');
        $('.eto-v2-payment-method').not('.hide').first().addClass('eto-v2-payment-method-first-box');


        // Reset tooltips and hightlight - start
        $('#'+ etoData.mainContainer +' #etoForm').find('.etoErrorContainer').removeClass('etoErrorContainer');
        $('#'+ etoData.mainContainer +' #etoForm').find('.etoError').removeClass('etoError');
        $('#'+ etoData.mainContainer +' #etoForm').find('select, textarea, input[type=text], input[type=radio], input[type=checkbox]').removeAttr('title');
        // Reset tooltips and hightlight - end


        // if( !etoData.booking.serviceId ) {
        //   error = etoLang('ERROR_SERVICES_EMPTY');
        //   etoData.errorMessage.push(['s1_g', error]);
        //   $('#'+ etoData.mainContainer +' #etoServices').addClass('etoError').attr('title', error);
        // }

        if (!etoData.booking.serviceDuration && etoData.serviceParams.duration) {
            error = etoLang('ERROR_SERVICES_DURATION_EMPTY');
            etoData.errorMessage.push(['s1_g', error]);
            $('#'+ etoData.mainContainer +' #etoServicesDuration').addClass('etoError').attr('title', error);
        }

        if (!etoData.booking.routeReturn) {
            error = etoLang('ERROR_RETURN_EMPTY');
            etoData.errorMessage.push(['s1_g', error]);
            $('#'+ etoData.mainContainer +' input[name*=etoRouteReturn]').addClass('etoError').attr('title', error);
        }

        // Route 1
        if (!etoData.booking.route1.category.start) {
            error = etoLang('ERROR_ROUTE_CATEGORY_START_EMPTY');
            etoData.errorMessage.push(['s1_r1', error]);
            $('#'+ etoData.mainContainer +' #etoRoute1CategoryStart').addClass('etoError').attr('title', error);
            if (etoData.enabledTypeahead == 1) {
                $('#'+ etoData.mainContainer +' #etoRoute1CategoryStartTypeahead').addClass('etoError').attr('title', error);
            }
        } else if (!etoData.booking.route1.location.start) {
            error = etoLang('ERROR_ROUTE_LOCATION_START_EMPTY');
            etoData.errorMessage.push(['s1_r1', error]);
            $('#'+ etoData.mainContainer +' #etoRoute1LocationStart').addClass('etoError').attr('title', error);
            if (etoData.enabledTypeahead == 1) {
                $('#'+ etoData.mainContainer +' #etoRoute1CategoryStartTypeahead').addClass('etoError').attr('title', error);
            }
        }

        if (!etoData.booking.route1.category.end) {
            error = etoLang('ERROR_ROUTE_CATEGORY_END_EMPTY');
            etoData.errorMessage.push(['s1_r1', error]);
            $('#'+ etoData.mainContainer +' #etoRoute1CategoryEnd').addClass('etoError').attr('title', error);
            if (etoData.enabledTypeahead == 1) {
                $('#'+ etoData.mainContainer +' #etoRoute1CategoryEndTypeahead').addClass('etoError').attr('title', error);
            }
        } else if (!etoData.booking.route1.location.end) {
            error = etoLang('ERROR_ROUTE_LOCATION_END_EMPTY');
            etoData.errorMessage.push(['s1_r1', error]);
            $('#'+ etoData.mainContainer +' #etoRoute1LocationEnd').addClass('etoError').attr('title', error);
            if (etoData.enabledTypeahead == 1) {
                $('#'+ etoData.mainContainer +' #etoRoute1CategoryEndTypeahead').addClass('etoError').attr('title', error);
            }
        }


        if (!scheduled) {
            var waypointsCount = 0;

            $('#'+ etoData.mainContainer +' textarea[name*=etoRoute1Waypoints]').each(function() {
                if (!$(this).val()) {
                    waypointsCount += 1;
                    error = etoLang('ERROR_ROUTE_WAYPOINT_EMPTY');
                    $(this).addClass('etoError').attr('title', error);
                    if (etoData.enabledTypeahead == 1) {
                        $('#'+ etoData.mainContainer +' #' + $(this).attr('id') + 'Typeahead').addClass('etoError').attr('title', error);
                    }
                }
            });

            if (waypointsCount > 0) {
                error = etoLang('ERROR_ROUTE_WAYPOINT_EMPTY');
                etoData.errorMessage.push(['s1_r1', error]);
            }


            // Waypoint address - start
            if (parseInt(etoData.request.config.booking_required_address_complete_via) == 1) {
                var waypointsCount = 0;

                $('#'+ etoData.mainContainer +' input[name*=etoRoute1WaypointsComplete]').each(function() {
                    if (!$(this).val()) {
                        waypointsCount += 1;
                        error = etoLang('ERROR_ROUTE_WAYPOINT_COMPLETE_EMPTY');
                        $(this).addClass('etoError').attr('title', error);
                    }
                });

                if (waypointsCount > 0) {
                    error = etoLang('ERROR_ROUTE_WAYPOINT_COMPLETE_EMPTY');
                    etoData.errorMessage.push(['s3_r1', error]);
                }
            }
            // Waypoint address - end
        }


        if (etoData.layout != 'minimal') {

            if (!etoData.booking.route1.vehicle || etoData.booking.route1.vehicle.length <= 0) {
                error = etoLang('ERROR_ROUTE_VEHICLE_EMPTY');
                etoData.errorMessage.push(['s2_r1', error]);
                $('#'+ etoData.mainContainer +' select[name*=etoRoute1Vehicle]').addClass('etoError').attr('title', error);
            } else {

                if (scheduled || parseInt(etoData.request.config.booking_required_passengers) == 1) {
                    if (!etoData.booking.route1.passengers) {
                        error = etoLang('ERROR_ROUTE_PASSENGERS_EMPTY');
                        etoData.errorMessage.push(['s3_r1', error]);
                        $('#'+ etoData.mainContainer +' #etoRoute1Passengers').addClass('etoError').attr('title', error);
                    } else if (etoData.booking.route1.passengers <= 0) {
                        error = etoLang('ERROR_ROUTE_PASSENGERS_INCORRECT');
                        etoData.errorMessage.push(['s3_r1', error]);
                        $('#'+ etoData.mainContainer +' #etoRoute1Passengers').addClass('etoError').attr('title', error);
                    }
                }

                if (!scheduled) {
                    if (!etoData.booking.route1.luggage && parseInt(etoData.request.config.booking_required_luggage) == 1) {
                        error = etoLang('ERROR_ROUTE_LUGGAGE_EMPTY');
                        etoData.errorMessage.push(['s3_r1', error]);
                        $('#'+ etoData.mainContainer +' #etoRoute1Luggage').addClass('etoError').attr('title', error);
                    }

                    if (!etoData.booking.route1.handLuggage && parseInt(etoData.request.config.booking_required_hand_luggage) == 1) {
                        error = etoLang('ERROR_ROUTE_HANDLUGGAGE_EMPTY');
                        etoData.errorMessage.push(['s3_r1', error]);
                        $('#'+ etoData.mainContainer +' #etoRoute1HandLuggage').addClass('etoError').attr('title', error);
                    }

                    if (!etoData.booking.route1.childSeats && parseInt(etoData.request.config.booking_required_child_seats) == 1) {
                        error = etoLang('ERROR_ROUTE_CHILDSEATS_EMPTY');
                        etoData.errorMessage.push(['s3_r1', error]);
                        $('#'+ etoData.mainContainer +' #etoRoute1ChildSeats').addClass('etoError').attr('title', error);
                    }

                    if (!etoData.booking.route1.babySeats && parseInt(etoData.request.config.booking_required_baby_seats) == 1) {
                        error = etoLang('ERROR_ROUTE_BABYSEATS_EMPTY');
                        etoData.errorMessage.push(['s3_r1', error]);
                        $('#'+ etoData.mainContainer +' #etoRoute1BabySeats').addClass('etoError').attr('title', error);
                    }

                    if (!etoData.booking.route1.infantSeats && parseInt(etoData.request.config.booking_required_infant_seats) == 1) {
                        error = etoLang('ERROR_ROUTE_INFANTSEATS_EMPTY');
                        etoData.errorMessage.push(['s3_r1', error]);
                        $('#'+ etoData.mainContainer +' #etoRoute1InfantSeats').addClass('etoError').attr('title', error);
                    }

                    if (!etoData.booking.route1.wheelchair && parseInt(etoData.request.config.booking_required_wheelchair) == 1) {
                        error = etoLang('ERROR_ROUTE_WHEELCHAIR_EMPTY');
                        etoData.errorMessage.push(['s3_r1', error]);
                        $('#'+ etoData.mainContainer +' #etoRoute1Wheelchair').addClass('etoError').attr('title', error);
                    }
                }
            }


            if (!etoData.booking.route1.date) {
                error = etoLang('ERROR_ROUTE_DATE_EMPTY');
                etoData.errorMessage.push(['s1_r1', error]);
                $('#'+ etoData.mainContainer +' #etoRoute1Date').addClass('etoError').attr('title', error);
                $('#'+ etoData.mainContainer +' #etoRoute1DateGhostDate').addClass('etoError').attr('title', error);
                $('#'+ etoData.mainContainer +' #etoRoute1DateGhostTime').addClass('etoError').attr('title', error);
            } else if (!moment(String(etoData.booking.route1.date), 'YYYY-MM-DD HH:mm')) {
                error = etoLang('ERROR_ROUTE_DATE_INCORRECT');
                etoData.errorMessage.push(['s1_r1', error]);
                $('#'+ etoData.mainContainer +' #etoRoute1Date').addClass('etoError').attr('title', error);
                $('#'+ etoData.mainContainer +' #etoRoute1DateGhostDate').addClass('etoError').attr('title', error);
                $('#'+ etoData.mainContainer +' #etoRoute1DateGhostTime').addClass('etoError').attr('title', error);
            } else if (moment(String(etoData.booking.route1.date), 'YYYY-MM-DD HH:mm') <= moment()) {
                error = etoLang('ERROR_ROUTE_DATE_PASSED');
                etoData.errorMessage.push(['s1_r1', error]);
                $('#'+ etoData.mainContainer +' #etoRoute1Date').addClass('etoError').attr('title', error);
                $('#'+ etoData.mainContainer +' #etoRoute1DateGhostDate').addClass('etoError').attr('title', error);
                $('#'+ etoData.mainContainer +' #etoRoute1DateGhostTime').addClass('etoError').attr('title', error);
            } else if (moment(String(etoData.booking.route1.date), 'YYYY-MM-DD HH:mm') <= moment().add(minBookingTimeLimit, 'hours') && etoData.booking.route1.excludedRouteAllowed == 0) {
                error = etoLang('ERROR_ROUTE_DATE_LIMIT');
                // error = error.replace(/\{number\}/g, minBookingTimeLimit);
                error = error.replace(/\{number\}/g, hoursToTime(minBookingTimeLimit));
                etoData.errorMessage.push(['s1_r1', error]);
                $('#'+ etoData.mainContainer +' #etoRoute1Date').addClass('etoError').attr('title', error);
                $('#'+ etoData.mainContainer +' #etoRoute1DateGhostDate').addClass('etoError').attr('title', error);
                $('#'+ etoData.mainContainer +' #etoRoute1DateGhostTime').addClass('etoError').attr('title', error);
            }
        }

        if (!scheduled && (etoData.booking.route1.isAirport == 1 || etoData.booking.route1.category.type.start == 'airport')) {
            if (!etoData.booking.route1.flightNumber && parseInt(etoData.request.config.booking_required_flight_number) == 1) {
                error = etoLang('ERROR_ROUTE_FLIGHT_NUMBER_EMPTY');
                etoData.errorMessage.push(['s3_r1', error]);
                $('#'+ etoData.mainContainer +' #etoRoute1FlightNumber').addClass('etoError').attr('title', error);
            }

            if (!etoData.booking.route1.flightLandingTime &&
                parseInt(etoData.request.config.booking_required_flight_landing_time) == 1 &&
                parseInt(etoData.request.config.booking_flight_landing_time_enable) == 1) {
                error = etoLang('ERROR_ROUTE_FLIGHT_LANDING_TIME_EMPTY');
                etoData.errorMessage.push(['s3_r1', error]);
                $('#'+ etoData.mainContainer +' #etoRoute1FlightLandingTime').addClass('etoError').attr('title', error);
            }

            if (!etoData.booking.route1.departureCity && parseInt(etoData.request.config.booking_required_departure_city) == 1) {
                error = etoLang('ERROR_ROUTE_DEPARTURE_CITY_EMPTY');
                etoData.errorMessage.push(['s3_r1', error]);
                $('#'+ etoData.mainContainer +' #etoRoute1DepartureCity').addClass('etoError').attr('title', error);
            }

            if (!etoData.booking.route1.waitingTime &&
                parseInt(etoData.request.config.booking_required_waiting_time) == 1 &&
                parseInt(etoData.request.config.booking_waiting_time_enable) == 1 &&
                etoData.manualQuote == 0) {
                error = etoLang('ERROR_ROUTE_WAITING_TIME_EMPTY');
                etoData.errorMessage.push(['s3_r1', error]);
                $('#'+ etoData.mainContainer +' #etoRoute1WaitingTime').addClass('etoError').attr('title', error);
            }

            /*
            if( !etoData.booking.route1.meetAndGreet ) {
            	error = etoLang('ERROR_ROUTE_MEET_AND_GREET_EMPTY');
            	etoData.errorMessage.push(['s2_r1', error]);
            	$('#'+ etoData.mainContainer +' #etoRoute1MeetAndGreet').addClass('etoError').attr('title', error);
            }
            */
        }

        if (!scheduled && (etoData.booking.route1.isAirport2 == 1 || etoData.booking.route1.category.type.end == 'airport')) {
            if (!etoData.booking.route1.departureFlightNumber && parseInt(etoData.request.config.booking_required_departure_flight_number) == 1) {
                error = etoLang('ERROR_ROUTE_DEPARTURE_FLIGHT_NUMBER_EMPTY');
                etoData.errorMessage.push(['s3_r1', error]);
                $('#'+ etoData.mainContainer +' #etoRoute1DepartureFlightNumber').addClass('etoError').attr('title', error);
            }

            if (!etoData.booking.route1.departureFlightTime &&
                parseInt(etoData.request.config.booking_required_departure_flight_time) == 1 &&
                parseInt(etoData.request.config.booking_departure_flight_time_enable) == 1) {
                error = etoLang('ERROR_ROUTE_DEPARTURE_FLIGHT_TIME_EMPTY');
                etoData.errorMessage.push(['s3_r1', error]);
                $('#'+ etoData.mainContainer +' #etoRoute1DepartureFlightTime').addClass('etoError').attr('title', error);
            }

            if (!etoData.booking.route1.departureFlightCity && parseInt(etoData.request.config.booking_required_departure_flight_city) == 1) {
                error = etoLang('ERROR_ROUTE_DEPARTURE_FLIGHT_CITY_EMPTY');
                etoData.errorMessage.push(['s3_r1', error]);
                $('#'+ etoData.mainContainer +' #etoRoute1DepartureFlightCity').addClass('etoError').attr('title', error);
            }
        }

        if (!scheduled && parseInt(etoData.request.config.booking_required_address_complete_from) == 1) {
            if ( etoData.booking.route1.isAirport == 0 && (
                    etoData.booking.route1.category.type.start == 'address' ||
                    etoData.booking.route1.category.type.start == 'postcode'
                )
            ) {
                if (!etoData.booking.route1.addressComplete.start) {
                    error = etoLang('ERROR_ROUTE_ADDRESS_START_COMPLETE_EMPTY');
                    etoData.errorMessage.push(['s3_r1', error]);
                    $('#'+ etoData.mainContainer +' #etoRoute1AddressStartComplete').addClass('etoError').attr('title', error);
                }
            }
        }

        if (!scheduled && parseInt(etoData.request.config.booking_required_address_complete_to) == 1) {
            if ( etoData.booking.route1.isAirport2 == 0 && (
                    etoData.booking.route1.category.type.end == 'address' ||
                    etoData.booking.route1.category.type.end == 'postcode'
                )
            ) {
                if (!etoData.booking.route1.addressComplete.end) {
                    error = etoLang('ERROR_ROUTE_ADDRESS_END_COMPLETE_EMPTY');
                    etoData.errorMessage.push(['s3_r1', error]);
                    $('#'+ etoData.mainContainer +' #etoRoute1AddressEndComplete').addClass('etoError').attr('title', error);
                }
            }
        }


        // Route 2
        if (etoData.booking.routeReturn == 2) {

            if (!etoData.booking.route2.category.start) {
                error = etoLang('ERROR_ROUTE_CATEGORY_START_EMPTY');
                etoData.errorMessage.push(['s1_r2', error]);
                $('#'+ etoData.mainContainer +' #etoRoute2CategoryStart').addClass('etoError').attr('title', error);
                if (etoData.enabledTypeahead == 1) {
                    $('#'+ etoData.mainContainer +' #etoRoute2CategoryStartTypeahead').addClass('etoError').attr('title', error);
                }
            } else if (!etoData.booking.route2.location.start) {
                error = etoLang('ERROR_ROUTE_LOCATION_START_EMPTY');
                etoData.errorMessage.push(['s1_r2', error]);
                $('#'+ etoData.mainContainer +' #etoRoute2LocationStart').addClass('etoError').attr('title', error);
                if (etoData.enabledTypeahead == 1) {
                    $('#'+ etoData.mainContainer +' #etoRoute2CategoryStartTypeahead').addClass('etoError').attr('title', error);
                }
            }

            if (!etoData.booking.route2.category.end) {
                error = etoLang('ERROR_ROUTE_CATEGORY_END_EMPTY');
                etoData.errorMessage.push(['s1_r2', error]);
                $('#'+ etoData.mainContainer +' #etoRoute2CategoryEnd').addClass('etoError').attr('title', error);
                if (etoData.enabledTypeahead == 1) {
                    $('#'+ etoData.mainContainer +' #etoRoute2CategoryEndTypeahead').addClass('etoError').attr('title', error);
                }
            } else if (!etoData.booking.route2.location.end) {
                error = etoLang('ERROR_ROUTE_LOCATION_END_EMPTY');
                etoData.errorMessage.push(['s1_r2', error]);
                $('#'+ etoData.mainContainer +' #etoRoute2LocationEnd').addClass('etoError').attr('title', error);
                if (etoData.enabledTypeahead == 1) {
                    $('#'+ etoData.mainContainer +' #etoRoute2CategoryEndTypeahead').addClass('etoError').attr('title', error);
                }
            }

            var waypointsCount = 0;

            $('#'+ etoData.mainContainer +' textarea[name*=etoRoute2Waypoints]').each(function() {
                if (!$(this).val()) {
                    waypointsCount += 1;
                    error = etoLang('ERROR_ROUTE_WAYPOINT_EMPTY');
                    $(this).addClass('etoError').attr('title', error);
                    if (etoData.enabledTypeahead == 1) {
                        $('#'+ etoData.mainContainer +' #' + $(this).attr('id') + 'Typeahead').addClass('etoError').attr('title', error);
                    }
                }
            });

            if (waypointsCount > 0) {
                error = etoLang('ERROR_ROUTE_WAYPOINT_EMPTY');
                etoData.errorMessage.push(['s1_r2', error]);
            }

            // Waypoint address - start
            if (parseInt(etoData.request.config.booking_required_address_complete_via) == 1) {
                var waypointsCount = 0;

                $('#'+ etoData.mainContainer +' input[name*=etoRoute2WaypointsComplete]').each(function() {
                    if (!$(this).val()) {
                        waypointsCount += 1;
                        error = etoLang('ERROR_ROUTE_WAYPOINT_COMPLETE_EMPTY');
                        $(this).addClass('etoError').attr('title', error);
                    }
                });

                if (waypointsCount > 0) {
                    error = etoLang('ERROR_ROUTE_WAYPOINT_COMPLETE_EMPTY');
                    etoData.errorMessage.push(['s3_r2', error]);
                }
            }
            // Waypoint address - end

            if (etoData.layout != 'minimal') {

                if (!etoData.booking.route2.vehicle || etoData.booking.route2.vehicle.length <= 0) {
                    error = etoLang('ERROR_ROUTE_VEHICLE_EMPTY');
                    etoData.errorMessage.push(['s2_r2', error]);
                    $('#'+ etoData.mainContainer +' select[name*=etoRoute2Vehicle]').addClass('etoError').attr('title', error);
                } else {

                    if (parseInt(etoData.request.config.booking_required_passengers) == 1) {
                        if (!etoData.booking.route2.passengers) {
                            error = etoLang('ERROR_ROUTE_PASSENGERS_EMPTY');
                            etoData.errorMessage.push(['s3_r2', error]);
                            $('#'+ etoData.mainContainer +' #etoRoute2Passengers').addClass('etoError').attr('title', error);
                        } else if (etoData.booking.route2.passengers <= 0) {
                            error = etoLang('ERROR_ROUTE_PASSENGERS_INCORRECT');
                            etoData.errorMessage.push(['s3_r2', error]);
                            $('#'+ etoData.mainContainer +' #etoRoute2Passengers').addClass('etoError').attr('title', error);
                        }
                    }

                    if (!etoData.booking.route2.luggage && parseInt(etoData.request.config.booking_required_luggage) == 1) {
                        error = etoLang('ERROR_ROUTE_LUGGAGE_EMPTY');
                        etoData.errorMessage.push(['s3_r2', error]);
                        $('#'+ etoData.mainContainer +' #etoRoute2Luggage').addClass('etoError').attr('title', error);
                    }

                    if (!etoData.booking.route2.handLuggage && parseInt(etoData.request.config.booking_required_hand_luggage) == 1) {
                        error = etoLang('ERROR_ROUTE_HANDLUGGAGE_EMPTY');
                        etoData.errorMessage.push(['s3_r2', error]);
                        $('#'+ etoData.mainContainer +' #etoRoute2HandLuggage').addClass('etoError').attr('title', error);
                    }

                    if (!etoData.booking.route2.childSeats && parseInt(etoData.request.config.booking_required_child_seats) == 1) {
                        error = etoLang('ERROR_ROUTE_CHILDSEATS_EMPTY');
                        etoData.errorMessage.push(['s3_r2', error]);
                        $('#'+ etoData.mainContainer +' #etoRoute2ChildSeats').addClass('etoError').attr('title', error);
                    }

                    if (!etoData.booking.route2.babySeats && parseInt(etoData.request.config.booking_required_baby_seats) == 1) {
                        error = etoLang('ERROR_ROUTE_BABYSEATS_EMPTY');
                        etoData.errorMessage.push(['s3_r2', error]);
                        $('#'+ etoData.mainContainer +' #etoRoute2BabySeats').addClass('etoError').attr('title', error);
                    }

                    if (!etoData.booking.route2.infantSeats && parseInt(etoData.request.config.booking_required_infant_seats) == 1) {
                        error = etoLang('ERROR_ROUTE_INFANTSEATS_EMPTY');
                        etoData.errorMessage.push(['s3_r2', error]);
                        $('#'+ etoData.mainContainer +' #etoRoute2InfantSeats').addClass('etoError').attr('title', error);
                    }

                    if (!etoData.booking.route2.wheelchair && parseInt(etoData.request.config.booking_required_wheelchair) == 1) {
                        error = etoLang('ERROR_ROUTE_WHEELCHAIR_EMPTY');
                        etoData.errorMessage.push(['s3_r2', error]);
                        $('#'+ etoData.mainContainer +' #etoRoute2Wheelchair').addClass('etoError').attr('title', error);
                    }
                }


                if (!etoData.booking.route2.date) {
                    error = etoLang('ERROR_ROUTE_DATE_EMPTY');
                    etoData.errorMessage.push(['s1_r2', error]);
                    $('#'+ etoData.mainContainer +' #etoRoute2Date').addClass('etoError').attr('title', error);
                    $('#'+ etoData.mainContainer +' #etoRoute2DateGhostDate').addClass('etoError').attr('title', error);
                    $('#'+ etoData.mainContainer +' #etoRoute2DateGhostTime').addClass('etoError').attr('title', error);
                } else if (!moment(String(etoData.booking.route2.date), 'YYYY-MM-DD HH:mm')) {
                    error = etoLang('ERROR_ROUTE_DATE_INCORRECT');
                    etoData.errorMessage.push(['s1_r2', error]);
                    $('#'+ etoData.mainContainer +' #etoRoute2Date').addClass('etoError').attr('title', error);
                    $('#'+ etoData.mainContainer +' #etoRoute2DateGhostDate').addClass('etoError').attr('title', error);
                    $('#'+ etoData.mainContainer +' #etoRoute2DateGhostTime').addClass('etoError').attr('title', error);
                } else if (moment(String(etoData.booking.route2.date), 'YYYY-MM-DD HH:mm') <= moment()) {
                    error = etoLang('ERROR_ROUTE_DATE_PASSED');
                    etoData.errorMessage.push(['s1_r2', error]);
                    $('#'+ etoData.mainContainer +' #etoRoute2Date').addClass('etoError').attr('title', error);
                    $('#'+ etoData.mainContainer +' #etoRoute2DateGhostDate').addClass('etoError').attr('title', error);
                    $('#'+ etoData.mainContainer +' #etoRoute2DateGhostTime').addClass('etoError').attr('title', error);
                } else if (moment(String(etoData.booking.route2.date), 'YYYY-MM-DD HH:mm') <= moment().add(minBookingTimeLimit, 'hours') && etoData.booking.route2.excludedRouteAllowed == 0) {
                    error = etoLang('ERROR_ROUTE_DATE_LIMIT');
                    // error = error.replace(/\{number\}/g, minBookingTimeLimit);
                    error = error.replace(/\{number\}/g, hoursToTime(minBookingTimeLimit));
                    etoData.errorMessage.push(['s1_r2', error]);
                    $('#'+ etoData.mainContainer +' #etoRoute2Date').addClass('etoError').attr('title', error);
                    $('#'+ etoData.mainContainer +' #etoRoute2DateGhostDate').addClass('etoError').attr('title', error);
                    $('#'+ etoData.mainContainer +' #etoRoute2DateGhostTime').addClass('etoError').attr('title', error);
                } else if (moment(String(etoData.booking.route2.date), 'YYYY-MM-DD HH:mm') <=
                    moment(String(etoData.booking.route1.date), 'YYYY-MM-DD HH:mm')) {
                    error = etoLang('ERROR_ROUTE_DATE_RETURN');
                    etoData.errorMessage.push(['s1_r2', error]);
                    $('#'+ etoData.mainContainer +' #etoRoute2Date').addClass('etoError').attr('title', error);
                    $('#'+ etoData.mainContainer +' #etoRoute2DateGhostDate').addClass('etoError').attr('title', error);
                    $('#'+ etoData.mainContainer +' #etoRoute2DateGhostTime').addClass('etoError').attr('title', error);
                }
            }

            if (etoData.booking.route2.isAirport == 1 || etoData.booking.route2.category.type.start == 'airport') {
                if (!etoData.booking.route2.flightNumber && parseInt(etoData.request.config.booking_required_flight_number) == 1) {
                    error = etoLang('ERROR_ROUTE_FLIGHT_NUMBER_EMPTY');
                    etoData.errorMessage.push(['s3_r2', error]);
                    $('#'+ etoData.mainContainer +' #etoRoute2FlightNumber').addClass('etoError').attr('title', error);
                }

                if (!etoData.booking.route2.flightLandingTime &&
                    parseInt(etoData.request.config.booking_required_flight_landing_time) == 1 &&
                    parseInt(etoData.request.config.booking_flight_landing_time_enable) == 1) {
                    error = etoLang('ERROR_ROUTE_FLIGHT_LANDING_TIME_EMPTY');
                    etoData.errorMessage.push(['s3_r2', error]);
                    $('#'+ etoData.mainContainer +' #etoRoute2FlightLandingTime').addClass('etoError').attr('title', error);
                }

                if (!etoData.booking.route2.departureCity && parseInt(etoData.request.config.booking_required_departure_city) == 1) {
                    error = etoLang('ERROR_ROUTE_DEPARTURE_CITY_EMPTY');
                    etoData.errorMessage.push(['s3_r2', error]);
                    $('#'+ etoData.mainContainer +' #etoRoute2DepartureCity').addClass('etoError').attr('title', error);
                }

                if (!etoData.booking.route2.waitingTime &&
                    parseInt(etoData.request.config.booking_required_waiting_time) == 1 &&
                    parseInt(etoData.request.config.booking_waiting_time_enable) == 1 &&
                    etoData.manualQuote == 0) {
                    error = etoLang('ERROR_ROUTE_WAITING_TIME_EMPTY');
                    etoData.errorMessage.push(['s3_r2', error]);
                    $('#'+ etoData.mainContainer +' #etoRoute2WaitingTime').addClass('etoError').attr('title', error);
                }

                /*
                if( !etoData.booking.route2.meetAndGreet ) {
                	error = etoLang('ERROR_ROUTE_MEET_AND_GREET_EMPTY');
                	etoData.errorMessage.push(['s2_r2', error]);
                	$('#'+ etoData.mainContainer +' #etoRoute2MeetAndGreet').addClass('etoError').attr('title', error);
                }
                */
            }

            if (etoData.booking.route2.isAirport2 == 1 || etoData.booking.route2.category.type.end == 'airport') {
                if (!etoData.booking.route2.departureFlightNumber && parseInt(etoData.request.config.booking_required_departure_flight_number) == 1) {
                    error = etoLang('ERROR_ROUTE_DEPARTURE_FLIGHT_NUMBER_EMPTY');
                    etoData.errorMessage.push(['s3_r2', error]);
                    $('#'+ etoData.mainContainer +' #etoRoute2DepartureFlightNumber').addClass('etoError').attr('title', error);
                }

                if (!etoData.booking.route2.departureFlightTime &&
                    parseInt(etoData.request.config.booking_required_departure_flight_time) == 1 &&
                    parseInt(etoData.request.config.booking_departure_flight_time_enable) == 1) {
                    error = etoLang('ERROR_ROUTE_DEPARTURE_FLIGHT_TIME_EMPTY');
                    etoData.errorMessage.push(['s3_r2', error]);
                    $('#'+ etoData.mainContainer +' #etoRoute2DepartureFlightTime').addClass('etoError').attr('title', error);
                }

                if (!etoData.booking.route2.departureFlightCity && parseInt(etoData.request.config.booking_required_departure_flight_city) == 1) {
                    error = etoLang('ERROR_ROUTE_DEPARTURE_FLIGHT_CITY_EMPTY');
                    etoData.errorMessage.push(['s3_r2', error]);
                    $('#'+ etoData.mainContainer +' #etoRoute2DepartureFlightCity').addClass('etoError').attr('title', error);
                }
            }

            if (parseInt(etoData.request.config.booking_required_address_complete_from) == 1) {
                if (
                    etoData.booking.route2.isAirport == 0 && (
                        etoData.booking.route2.category.type.start == 'address' ||
                        etoData.booking.route2.category.type.start == 'postcode'
                    )
                ) {
                    if (!etoData.booking.route2.addressComplete.start) {
                        error = etoLang('ERROR_ROUTE_ADDRESS_START_COMPLETE_EMPTY');
                        etoData.errorMessage.push(['s3_r2', error]);
                        $('#'+ etoData.mainContainer +' #etoRoute2AddressStartComplete').addClass('etoError').attr('title', error);
                    }
                }
            }

            if (parseInt(etoData.request.config.booking_required_address_complete_to) == 1) {
                if (
                    etoData.booking.route2.isAirport2 == 0 && (
                        etoData.booking.route2.category.type.end == 'address' ||
                        etoData.booking.route2.category.type.end == 'postcode'
                    )
                ) {
                    if (!etoData.booking.route2.addressComplete.end) {
                        error = etoLang('ERROR_ROUTE_ADDRESS_END_COMPLETE_EMPTY');
                        etoData.errorMessage.push(['s3_r2', error]);
                        $('#'+ etoData.mainContainer +' #etoRoute2AddressEndComplete').addClass('etoError').attr('title', error);
                    }
                }
            }
        }

        // Other
        if (!etoData.booking.contactTitle) {
            error = etoLang('ERROR_CONTACT_TITLE_EMPTY');
            etoData.errorMessage.push(['s3_g', error]);
            $('#'+ etoData.mainContainer +' #etoContactTitle').addClass('etoError').attr('title', error);
        }

        if (!etoData.booking.contactName) {
            error = etoLang('ERROR_CONTACT_NAME_EMPTY');
            etoData.errorMessage.push(['s3_g', error]);
            $('#'+ etoData.mainContainer +' #etoContactName').addClass('etoError').attr('title', error);
        }

        if (!etoData.booking.contactEmail) {
            error = etoLang('ERROR_CONTACT_EMAIL_EMPTY');
            etoData.errorMessage.push(['s3_g', error]);
            $('#'+ etoData.mainContainer +' #etoContactEmail').addClass('etoError').attr('title', error);
        } else if (!emailFilter.test(etoData.booking.contactEmail)) {
            error = etoLang('ERROR_CONTACT_EMAIL_INCORRECT');
            etoData.errorMessage.push(['s3_g', error]);
            $('#'+ etoData.mainContainer +' #etoContactEmail').addClass('etoError').attr('title', error);
        }

        if (parseInt(etoData.request.config.booking_required_contact_mobile) == 1 && !etoData.booking.contactMobile) {
            error = etoLang('ERROR_CONTACT_MOBILE_EMPTY');
            etoData.errorMessage.push(['s3_g', error]);
            $('#'+ etoData.mainContainer +' #etoContactMobile').addClass('etoError').attr('title', error);
        }
        else if (etoData.booking.contactMobile && !$('#etoContactMobile').intlTelInput('isValidNumber')) {
            var errorPhoneCode = $('#etoContactMobile').intlTelInput('getValidationError');
            var errorPhoneMsg = '';

            switch (errorPhoneCode) {
                case 1:
                    errorPhoneMsg = 'Invalid country code';
                break;
                case 2:
                    errorPhoneMsg = 'Too short';
                break;
                case 3:
                    errorPhoneMsg = 'Too long';
                break;
                case 4:
                    errorPhoneMsg = 'Invalid number';
                break;
            }

            if(errorPhoneMsg) {
                errorPhoneMsg = ' ('+ errorPhoneMsg +')';
            }

            error = etoLang('ERROR_CONTACT_MOBILE_INCORRECT') + errorPhoneMsg;
            etoData.errorMessage.push(['s3_g', error]);
            $('#'+ etoData.mainContainer +' #etoContactMobile').addClass('etoError').attr('title', error);
        }
        // else if (etoData.booking.contactMobile && !mobileFilter.test(etoData.booking.contactMobile)) {
        //   error = etoLang('ERROR_CONTACT_MOBILE_INCORRECT');
        //   etoData.errorMessage.push(['s3_g', error]);
        //   $('#'+ etoData.mainContainer +' #etoContactMobile').addClass('etoError').attr('title', error);
        // }

        if (!scheduled && etoData.booking.leadPassenger <= 0) {
            if (!etoData.booking.leadPassengerTitle) {
                error = etoLang('ERROR_LEAD_PASSENGER_TITLE_EMPTY');
                etoData.errorMessage.push(['s3_g', error]);
                $('#'+ etoData.mainContainer +' #etoLeadPassengerTitle').addClass('etoError').attr('title', error);
            }

            if (!etoData.booking.leadPassengerName) {
                error = etoLang('ERROR_LEAD_PASSENGER_NAME_EMPTY');
                etoData.errorMessage.push(['s3_g', error]);
                $('#'+ etoData.mainContainer +' #etoLeadPassengerName').addClass('etoError').attr('title', error);
            }

            if (!etoData.booking.leadPassengerEmail) {
                error = etoLang('ERROR_LEAD_PASSENGER_EMAIL_EMPTY');
                etoData.errorMessage.push(['s3_g', error]);
                $('#'+ etoData.mainContainer +' #etoLeadPassengerEmail').addClass('etoError').attr('title', error);
            } else if (!emailFilter.test(etoData.booking.leadPassengerEmail)) {
                error = etoLang('ERROR_LEAD_PASSENGER_EMAIL_INCORRECT');
                etoData.errorMessage.push(['s3_g', error]);
                $('#'+ etoData.mainContainer +' #etoLeadPassengerEmail').addClass('etoError').attr('title', error);
            }

            if (!etoData.booking.leadPassengerMobile) {
                error = etoLang('ERROR_LEAD_PASSENGER_MOBILE_EMPTY');
                etoData.errorMessage.push(['s3_g', error]);
                $('#'+ etoData.mainContainer +' #etoLeadPassengerMobile').addClass('etoError').attr('title', error);
            }
            else if (etoData.booking.leadPassengerMobile && !$('#etoLeadPassengerMobile').intlTelInput('isValidNumber')) {
                var errorPhoneCode = $('#etoLeadPassengerMobile').intlTelInput('getValidationError');
                var errorPhoneMsg = '';

                switch (errorPhoneCode) {
                    case 1:
                        errorPhoneMsg = 'Invalid country code';
                    break;
                    case 2:
                        errorPhoneMsg = 'Too short';
                    break;
                    case 3:
                        errorPhoneMsg = 'Too long';
                    break;
                    case 4:
                        errorPhoneMsg = 'Invalid number';
                    break;
                }

                if(errorPhoneMsg) {
                    errorPhoneMsg = ' ('+ errorPhoneMsg +')';
                }

                error = etoLang('ERROR_LEAD_PASSENGER_MOBILE_INCORRECT') + errorPhoneMsg;
                etoData.errorMessage.push(['s3_g', error]);
                $('#'+ etoData.mainContainer +' #etoLeadPassengerMobile').addClass('etoError').attr('title', error);
            }
            // else if (!mobileFilter.test(etoData.booking.leadPassengerMobile)) {
            //     error = etoLang('ERROR_LEAD_PASSENGER_MOBILE_INCORRECT');
            //     etoData.errorMessage.push(['s3_g', error]);
            //     $('#'+ etoData.mainContainer +' #etoLeadPassengerMobile').addClass('etoError').attr('title', error);
            // }
        }

        if (!etoData.booking.payment && etoData.manualQuote == 0) {
            error = etoLang('ERROR_PAYMENT_EMPTY');
            etoData.errorMessage.push(['s3_g', error]);
            $('#'+ etoData.mainContainer +' input[name*=etoPayment]').addClass('etoError').attr('title', error);
        }

        var terms = ($('#'+ etoData.mainContainer +' #etoTerms:checked').val()) ? 1 : 0;
        if (terms <= 0 && parseInt(etoData.request.config.terms_enable) == 1) {
            error = etoLang('ERROR_TERMS_EMPTY');
            etoData.errorMessage.push(['s3_g', error]);
            $('#'+ etoData.mainContainer +' #etoTerms').addClass('etoError').attr('title', error);
        }

        // Show top message
        //if( $('#'+ etoData.mainContainer +' #etoForm').find('.etoError').length > 0 ) {
        //error = etoLang('ERROR_EMPTY_FIELDS');
        // etoData.message.push(error);
        //}

        $('#'+ etoData.mainContainer +' #etoForm').find('.etoError').parents('.etoOuterContainer').addClass('etoErrorContainer');

        // Highlight - start
        if (etoData.displayHighlight == 0) {
            $('#'+ etoData.mainContainer +' #etoForm').find('.etoError').removeClass('etoError');
            $('#'+ etoData.mainContainer +' #etoForm').find('.etoErrorContainer').removeClass('etoErrorContainer');
        }
        // Highlight - end

        // Tooltip - start
        $('#'+ etoData.mainContainer +' #etoForm [title]').tooltip({
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
        // Tooltip - end

        var message = '';
        var s1_r1_message = '';
        var s1_r2_message = '';
        var s1_g_message = '';
        var s2_r1_message = '';
        var s2_r2_message = '';
        var s2_g_message = '';
        var s3_r1_message = '';
        var s3_r2_message = '';
        var s3_g_message = '';
        var g_message = '';

        var s1_r1_message_server = '';
        var s1_r2_message_server = '';
        var s1_g_message_server = '';
        var s2_r1_message_server = '';
        var s2_r2_message_server = '';
        var s2_g_message_server = '';
        var s3_r1_message_server = '';
        var s3_r2_message_server = '';
        var s3_g_message_server = '';
        var g_message_server = '';

        $.each(etoData.errorMessage, function(key, value) {
            var server = (value[2] && value[2] == 'server') ? 1 : 0;

            switch (value[0]) {
                case 's1_r1':
                    if (server) {
                        s1_r1_message_server += '<li>' + value[1] + '</li>';
                    }
                    else {
                        s1_r1_message += '<li>' + value[1] + '</li>';
                    }
                break;
                case 's1_r2':
                    if (server) {
                        s1_r2_message_server += '<li>' + value[1] + '</li>';
                    }
                    else {
                        s1_r2_message += '<li>' + value[1] + '</li>';
                    }
                break;
                case 's1_g':
                    if (server) {
                        s1_g_message_server += '<li>' + value[1] + '</li>';
                    }
                    else {
                        s1_g_message += '<li>' + value[1] + '</li>';
                    }
                break;
                case 's2_r1':
                    if (server) {
                        s2_r1_message_server += '<li>' + value[1] + '</li>';
                    }
                    else {
                        s2_r1_message += '<li>' + value[1] + '</li>';
                    }
                break;
                case 's2_r2':
                    if (server) {
                        s2_r2_message_server += '<li>' + value[1] + '</li>';
                    }
                    else {
                        s2_r2_message += '<li>' + value[1] + '</li>';
                    }
                break;
                case 's2_g':
                    if (server) {
                        s2_g_message_server += '<li>' + value[1] + '</li>';
                    }
                    else {
                        s2_g_message += '<li>' + value[1] + '</li>';
                    }
                break;
                case 's3_r1':
                    if (server) {
                        s3_r1_message_server += '<li>' + value[1] + '</li>';
                    }
                    else {
                        s3_r1_message += '<li>' + value[1] + '</li>';
                    }
                break;
                case 's3_r2':
                    if (server) {
                        s3_r2_message_server += '<li>' + value[1] + '</li>';
                    }
                    else {
                        s3_r2_message += '<li>' + value[1] + '</li>';
                    }
                break;
                case 's3_g':
                    if (server) {
                        s3_g_message_server += '<li>' + value[1] + '</li>';
                    }
                    else {
                        s3_g_message += '<li>' + value[1] + '</li>';
                    }
                break;
            }
        });

        var s1_r1_message = s1_r1_message ? s1_r1_message : s1_r1_message + s1_r1_message_server;
        var s1_r2_message = s1_r2_message ? s1_r2_message : s1_r2_message + s1_r2_message_server;
        var s1_g_message = s1_g_message ? s1_g_message : s1_g_message + s1_g_message_server;
        var s2_r1_message = s2_r1_message ? s2_r1_message : s2_r1_message + s2_r1_message_server;
        var s2_r2_message = s2_r2_message ? s2_r2_message : s2_r2_message + s2_r2_message_server;
        var s2_g_message = s2_g_message ? s2_g_message : s2_g_message + s2_g_message_server;
        var s3_r1_message = s3_r1_message ? s3_r1_message : s3_r1_message + s3_r1_message_server;
        var s3_r2_message = s3_r2_message ? s3_r2_message : s3_r2_message + s3_r2_message_server;
        var s3_g_message = s3_g_message ? s3_g_message : s3_g_message + s3_g_message_server;
        var g_message = g_message ? g_message : g_message + g_message_server;

        $.each(etoData.message, function(key, value) {
            g_message += '<li>' + value + '</li>';
        });

        if (s1_r1_message || s1_r2_message || s1_g_message) {
            etoData.maxStep = 1;
            etoStep();
            if (etoData.currentStep == 1) {
                if (s1_g_message) {
                    message += '<ul>' + s1_g_message + '</ul>';
                }

                if (s1_r1_message) {
                    if (etoData.booking.routeReturn == 2) {
                        message += 'One-way:<br />';
                    }
                    message += '<ul>' + s1_r1_message + '</ul>';
                }

                if (s1_r2_message) {
                    message += 'Return:<br /><ul>' + s1_r2_message + '</ul>';
                }
            }
        } else if (s2_r1_message || s2_r2_message || s2_g_message) {
            etoData.maxStep = 2;
            etoStep();
            if (etoData.currentStep == 2) {
                if (s2_g_message) {
                    message += '<ul>' + s2_g_message + '</ul>';
                }

                if (s2_r1_message) {
                    if (etoData.booking.routeReturn == 2) {
                        message += '' + etoLang('bookingField_OneWay') + ':<br />';
                    }
                    message += '<ul>' + s2_r1_message + '</ul>';
                }

                if (s2_r2_message) {
                    message += '' + etoLang('bookingField_Return') + ':<br /><ul>' + s2_r2_message + '</ul>';
                }

                if (etoData.noVehicleMessage == 1) {
                    etoData.displayMessage = 0;
                }
                etoData.noVehicleMessage = 0;
            }
        } else if (s3_r1_message || s3_r2_message || s3_g_message) {
            etoData.maxStep = 3;
            etoStep();
            if (etoData.currentStep == 3) {
                if (s3_g_message) {
                    message += '<ul>' + s3_g_message + '</ul>';
                }

                if (s3_r1_message) {
                    if (etoData.booking.routeReturn == 2) {
                        message += '' + etoLang('bookingField_OneWay') + ':<br />';
                    }
                    message += '<ul>' + s3_r1_message + '</ul>';
                }

                if (s3_r2_message) {
                    message += '' + etoLang('bookingField_Return') + ':<br /><ul>' + s3_r2_message + '</ul>';
                }

                if (etoData.clicked == 1) {
                    etoData.displayMessage = 0;
                }
                etoData.clicked = 0;
            }
        } else {
            etoData.maxStep = 3;
            etoStep();
        }

        if (g_message) {
            message += '<ul>' + g_message + '</ul>';
        }

        // Show tooltip error
        // $('#'+ etoData.mainContainer +' #etoForm #etoStep'+ etoData.currentStep +'Container .etoError[title]').tooltip('show');

        if (message) {
            etoData.submitOK = 0;
            $('#'+ etoData.mainContainer +' #etoQuoteButtonStep3Container').show();
            if (etoData.layout != 'minimal') {
                $('#'+ etoData.mainContainer +' #etoSubmitButtonContainer').hide();
            }
        } else {
            etoData.submitOK = 1;
            $('#'+ etoData.mainContainer +' #etoQuoteButtonStep3Container').hide();
            $('#'+ etoData.mainContainer +' #etoSubmitButtonContainer').show();
        }


        // Display messages - start
        if (etoData.displayMessage == 0) {
            message = '';
        }

        if (message != '') {

            message = '<div class="alert alert-danger alert-dismissible" role="alert">' +
                '<button type="button" class="close" data-dismiss="alert" aria-label="' + etoLang('button_Close') + '">' +
                '<span aria-hidden="true">&times;</span>' +
                '</button>' + message +
                '</div>';
        }
        // Display messages - end


        $('#'+ etoData.mainContainer +' #' + etoData.messageContainer).html(message);

        etoData.message = [];
        etoData.errorMessage = [];

        if (etoData.submitOK == 1) {
            return true;
        } else {
            return false;
        }
    }

    function etoFinish(finishType, bID, tID, tMSG) {

        if (etoData.debug) {
            console.log('finish');
        }

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': _token
            },
            url: etoData.apiURL,
            type: 'POST',
            data: {
                task: 'finish',
                finishType: finishType,
                bID: bID,
                tID: tID,
                tMSG: tMSG
            },
            dataType: 'json',
            // async: false,
            // cache: false,
            success: function(response) {

                if (response.message.length > 0) {
                    $.each(response.message, function(key, value) {
                        etoData.message.push(value);
                    });

                    $.removeCookie('eto_redirect_booking_url', {path: EasyTaxiOffice.cookiePath, secure: EasyTaxiOffice.cookieSecure, same_site: EasyTaxiOffice.cookieSameSite});
                }

                if (response.html) {
                    var html = '<div id="etoFinishContainer">' + response.html + '</div>';
                    $('#' + etoData.mainContainer).html(html);

                    $('body').click('.eto-v2-new-booking-link', function() {
                        $.removeCookie('eto_redirect_booking_url', {path: EasyTaxiOffice.cookiePath, secure: EasyTaxiOffice.cookieSecure, same_site: EasyTaxiOffice.cookieSameSite});
                    });

                    if (response.redirect > 0) {
                        setTimeout(function() {
                            $('#'+ etoData.mainContainer +' #paymentForm [type="submit"]').trigger('click');
                        }, 3000);
                    }
                }

                // gTag start - Steps
                if (finishType == 'payment') {

                    if (etoData.request.config.google_analytics_tracking_id) {
                        gtag('event', 'payment', {
                          'event_category': 'booking',
                          'event_label': 'Booking payment ('+ response.payment_method +' '+ response.payment_value_f +')',
                          'value': response.payment_value,
                        });
                    }

                    if (etoData.request.config.google_adwords_conversion_id &&
                        etoData.request.config.google_adwords_conversions &&
                        etoData.request.config.google_adwords_conversions.booking_payment) {
                      gtag('event', 'conversion', {
                        'send_to': etoData.request.config.google_adwords_conversion_id +'/'+ etoData.request.config.google_adwords_conversions.booking_payment,
                        'value': response.payment_value,
                        'currency': response.payment_currency,
                        'payment_method': response.payment_method,
                      });
                    }

                }
                else {

                    if (etoData.request.config.google_analytics_tracking_id) {
                        gtag('event', 'completed', {
                          'event_category': 'booking',
                          'event_label': 'Booking completed ('+ response.payment_method +' '+ response.payment_value_f +')',
                          'value': response.payment_value,
                        });
                    }

                    if (etoData.request.config.google_adwords_conversion_id &&
                        etoData.request.config.google_adwords_conversions &&
                        etoData.request.config.google_adwords_conversions.booking_completed) {
                      gtag('event', 'conversion', {
                        'send_to': etoData.request.config.google_adwords_conversion_id +'/'+ etoData.request.config.google_adwords_conversions.booking_completed,
                        'value': response.payment_value,
                        'currency': response.payment_currency,
                        'payment_method': response.payment_method,
                      });
                    }

                }
                // gTag end - Steps


                if (etoData.debug) {
                    console.log(response);
                }
            },
            error: function(response) {
                etoData.message.push('AJAX error: Finish');
            }
        });

        // $.removeCookie('eto_redirect_booking_url', {path: EasyTaxiOffice.cookiePath, secure: EasyTaxiOffice.cookieSecure, same_site: EasyTaxiOffice.cookieSameSite});
    }

    function etoGeolocation(fieldName) {

        // if( etoData.debug ){ console.log('geolocation'); }

        // If the browser supports the Geolocation API
        if (typeof navigator.geolocation == 'undefined') {
            etoData.message.push(etoLang('GEOLOCATION_UNDEFINED'));
            return;
        }

        event.preventDefault();

        navigator.geolocation.getCurrentPosition(function(position) {
            var geocoder = new google.maps.Geocoder();
            geocoder.geocode({
                'location': new google.maps.LatLng(position.coords.latitude, position.coords.longitude)
            },
            function(results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    $('#'+ etoData.mainContainer +' #eto' + fieldName).val(results[0].formatted_address);

                    if (etoData.enabledTypeahead == 1) {
                        $('#'+ etoData.mainContainer +' #eto' + fieldName + 'Typeahead').typeahead('val', results[0].formatted_address);
                    }

                    // Route 1 reverse start
                    etoCheck();
                    if (etoData.singleVehicle > 0 && etoData.booking.routeReturn == 2) {
                        if (fieldName == 'Route1LocationEnd') {
                            $('#'+ etoData.mainContainer +' #etoRoute2LocationStart').val(results[0].formatted_address);
                        }
                        if (fieldName == 'Route1LocationStart') {
                            $('#'+ etoData.mainContainer +' #etoRoute2LocationEnd').val(results[0].formatted_address);
                        }
                    }
                    // Route 1 reverse end

                    etoData.quoteStatus = 0;
                    etoCheck();
                } else {
                    etoData.message.push(etoLang('GEOLOCATION_UNABLE'));
                }
            });
        },
        function(positionError) {
            etoData.message.push(etoLang('GEOLOCATION_ERROR') + ': ' + positionError.message);
        },
        {
            enableHighAccuracy: true,
            timeout: 10 * 1000 // 10 seconds
        });
    }

    function etoService() {
        if (etoData.debug) {
            console.log('etoService');
        }

        var selectedVal = 0;
        var selectedParams = {
            type: 'standard',
            availability: 0,
            hide_location: 0,
            duration: 0,
            duration_min: 1,
            duration_max: 10
        };

        if (etoData.request.services) {
            $.each(etoData.request.services, function(key, value) {
                if (value.id == etoData.booking.serviceId) {
                    selectedVal = value.id;
                    selectedParams.type = value.type;
                    selectedParams.availability = value.availability;
                    selectedParams.hide_location = value.hide_location;
                    selectedParams.duration = value.duration;
                    selectedParams.duration_min = value.duration_min;
                    selectedParams.duration_max = value.duration_max;
                }
            });
        }

        etoData.booking.serviceId = selectedVal;

        if (selectedParams) {
            etoData.serviceParams.type = selectedParams.type;
            etoData.serviceParams.availability = selectedParams.availability;
            etoData.serviceParams.hide_location = selectedParams.hide_location;
            etoData.serviceParams.duration = selectedParams.duration;
            etoData.serviceParams.duration_min = selectedParams.duration_min;
            etoData.serviceParams.duration_max = selectedParams.duration_max;
        }

        var serviceHide = 0;
        if (etoData.request.config.booking_service_dropdown) {
            serviceHide = 1;
        }

        if (etoData.request.services && etoData.request.services.length > serviceHide) {
            $('#'+ etoData.mainContainer +' #etoServicesContainer').show();
        } else {
            $('#'+ etoData.mainContainer +' #etoServicesContainer').hide();
        }

        // Reset default
        $('#'+ etoData.mainContainer +' #etoRoute1MapDirections').show();
        $('#'+ etoData.mainContainer +' #etoRoute1MapContainer .etoMapStyle2Button').show();
        $('#'+ etoData.mainContainer +' #etoRoute1CategoryEndContainer').show();
        $('#'+ etoData.mainContainer +' #etoRoute1LocationEndLoader').show();
        $('#'+ etoData.mainContainer +' #etoRoute1AddressEndCompleteContainer').show();
        $('#'+ etoData.mainContainer +' .etoWaypointsAddButtonContainer').show();

        var placeholderStart = (ETOBookingType == 'from-airport') ? etoLang('bookingField_SelectAirportPlaceholder') : etoLang('bookingField_FromPlaceholder');
        var placeholderEnd = (ETOBookingType == 'to-airport') ? etoLang('bookingField_SelectAirportPlaceholder') : etoLang('bookingField_ToPlaceholder');

        // Scheduled
        if (etoData.serviceParams.type == 'scheduled') {
            $('#'+ etoData.mainContainer +' .etoWaypointsAddButtonContainer').hide();
            $('#'+ etoData.mainContainer +' input[name=etoRouteReturn]').attr('checked', false);
            $('#'+ etoData.mainContainer +' #etoRoute1WaypointsLoader').html('');

            $('#'+ etoData.mainContainer +' form#etoForm').addClass('etoJourneyTypeScheduled');
            $('#'+ etoData.mainContainer +' input[name=etoLeadPassenger]').attr('checked', false);

            $('.eto-icon-geolocation').addClass('hidden');

            placeholderStart = etoLang('bookingField_From');
            placeholderEnd = etoLang('bookingField_To');

            etoScheduled();
        }
        else {
            etoData.booking.scheduledRouteId = 0;

            $('#'+ etoData.mainContainer +' #etoRoute1DateGhostDate').data('DateTimePicker').enabledDates([]);

            var dropdown = $('#'+ etoData.mainContainer +' #etoRoute1DateGhostTimeDropdown');
            var dSelected = dropdown.val();
            var dOptions = '';
            for (i = 0; i <= 23; i++) {
                for (j = 0; j < 60; j += 5) {
                    var option_val = ((i < 10) ? '0'+ i : i) +':'+ ((j < 10) ? '0'+ j : j);
                    dOptions += '<option value="'+ option_val +'">'+ option_val +'</option>';
                }
            }
            dropdown.html(dOptions).val(dSelected).change();

            $('#'+ etoData.mainContainer +' form#etoForm').removeClass('etoJourneyTypeScheduled');

            if (etoData.request.config.booking_display_geolocation == 1) {
                $('.eto-icon-geolocation').removeClass('hidden');
            }
            else {
                $('.eto-icon-geolocation').addClass('hidden');
            }
        }

        if (etoData.serviceParams.type == 'scheduled') {
            $('#'+ etoData.mainContainer +' #etoRoute1DateContainer .eto-v2-time-picker-combined').show();
            $('#'+ etoData.mainContainer +' #etoRoute1DateContainer .combodate').hide();
        }
        else {
            $('#'+ etoData.mainContainer +' #etoRoute1DateContainer .eto-v2-time-picker-combined').hide();
            $('#'+ etoData.mainContainer +' #etoRoute1DateContainer .combodate').show();
        }

        $('#'+ etoData.mainContainer +' #etoRoute1CategoryStartTypeahead').attr('placeholder', placeholderStart);
        $('#'+ etoData.mainContainer +' #etoRoute1CategoryEndTypeahead').attr('placeholder', placeholderEnd);


        // Hide location
        if (etoData.serviceParams.hide_location) {
            $('#'+ etoData.mainContainer +' #etoRoute1MapDirections').hide();
            $('#'+ etoData.mainContainer +' #etoRoute1MapContainer .etoMapStyle2Button').hide();
            $('#'+ etoData.mainContainer +' #etoRoute1CategoryEndContainer').hide();
            $('#'+ etoData.mainContainer +' #etoRoute1LocationEndLoader').hide();
            $('#'+ etoData.mainContainer +' #etoRoute1AddressEndCompleteContainer').hide();
            $('#'+ etoData.mainContainer +' .etoWaypointsAddButtonContainer').hide();
            $('#'+ etoData.mainContainer +' input[name=etoRouteReturn]').attr('checked', false);
            $('#'+ etoData.mainContainer +' #etoRoute1WaypointsLoader').html('');
        }

        // Duration
        var options = '<option value="0">' + etoLang('bookingField_ServicesDurationSelect') + '</option>';

        if (etoData.serviceParams.duration) {
            for (var i = etoData.serviceParams.duration_min; i <= etoData.serviceParams.duration_max; i++) {
                options += '<option value="' + i + '">' + i + 'h</option>';
            }

            if (etoData.booking.serviceDuration < etoData.serviceParams.duration_min ||
                etoData.booking.serviceDuration > etoData.serviceParams.duration_max) {
                etoData.booking.serviceDuration = 0;
            }

            $('#'+ etoData.mainContainer +' #etoServicesDurationContainer').show();
        } else {
            etoData.booking.serviceDuration = 0;
            $('#'+ etoData.mainContainer +' #etoServicesDurationContainer').hide();
        }

        $('#'+ etoData.mainContainer +' #etoServicesDuration').html(options).val(etoData.booking.serviceDuration);
    }

    function etoScheduled() {
        var currentDate = moment($('#'+ etoData.mainContainer +' #etoRoute1DateGhostDate').data('DateTimePicker').viewDate()).format('YYYY-MM-DD HH:mm');
        var searchFrom = $('#'+ etoData.mainContainer +' #etoRoute1CategoryStartTypeahead').val();
        console.log('form',searchFrom)
        var searchTo = $('#'+ etoData.mainContainer +' #etoRoute1CategoryEndTypeahead').val();

        if (!searchFrom || !searchTo) {
            return false;
        }

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': _token
            },
            url: etoData.apiURL,
            type: 'POST',
            data: {
                task: 'scheduled_availability',
                currentDate: currentDate,
                searchFrom: searchFrom,
                searchTo: searchTo,
            },
            dataType: 'json',
            success: function(response) {
                // Set dates
                var dates = [];

                $.each(response.availability, function(k, v) {
                    dates.push(moment(v[0]));
                });

                if (!dates.length) {
                    dates.push($('#'+ etoData.mainContainer +' #etoRoute1DateGhostDate').data('DateTimePicker').minDate());
                }

                $('#'+ etoData.mainContainer +' #etoRoute1DateGhostDate').data('DateTimePicker').enabledDates(dates);

                // Set times
                var dropdown = $('#'+ etoData.mainContainer +' #etoRoute1DateGhostTimeDropdown');
                var date = $('#'+ etoData.mainContainer +' #etoRoute1DateGhostDate').val();
                var times = [];

                $.each(response.availability, function(k, v) {
                    if (v[0] == date) {
                        times = v[1];
                        return false;
                    }
                });

                var dSelected = dropdown.val();
                var dFirst = '';
                var dExists = 0;
                var dOptions = '';
                $.each(times, function(k, v) {
                    if (k == 0) { dFirst = v; }
                    if (v == dSelected) { dExists = 1; }
                    dOptions += '<option value="'+ v +'">'+ v +'</option>';
                });
                if (!dOptions) {
                    dOptions = '<option value="">' + etoLang('SELECT') + '</option>';
                }
                if (!dSelected || !dExists) {
                    dSelected = dFirst;
                }
                dropdown.html(dOptions).val(dSelected).change();
            },
            error: function(response) {
                etoData.message.push('AJAX error: Init');
            }
        });
    }

    function etoDateTimePicker(name) {
        // if( etoData.debug ){ console.log('date picker'); }

        var minDateTime = moment(getRoundedDate(etoData.request.config.booking_time_picker_steps));
        // var minDateTime = moment();
        // var currentMinute = minDateTime.get('minute');
        // var newMinute = 0;
        // if (currentMinute >= 45) {
        //     newMinute = 0;
        //     minDateTime.add(1, 'hours');
        // } else if (currentMinute >= 30) {
        //     newMinute = 45;
        // } else if (currentMinute >= 15) {
        //     newMinute = 30;
        // } else if (currentMinute >= 0) {
        //     newMinute = 15;
        // } else {
        //     newMinute = 0;
        // }
        // minDateTime.set('minute', newMinute);
        // minDateTime.set('second', 0);

        var toggleDate = 0;
        var language = etoData.request.config.language;
        // var languageFirstPart = etoData.request.config.language.split('-')[0];
        // console.log(language);
        // https://eonasdan.github.io/bootstrap-datetimepicker/#no-icon-input-field-only
        // http://vitalets.github.io/combodate/

        if ($('#'+ etoData.mainContainer +' #eto'+ name +'Ghost').length <= 0) {
            $('#'+ etoData.mainContainer +' #eto' + name).before(
                '<div class="etoGhostDateTime">' +
                  '<div class="etoGhostDateBox input-group" id="eto'+ name +'GhostDateBox">' +
                    '<span class="form-control" id="eto'+ name +'GhostDateInfo"></span>' +
                    '<input type="text" class="form-control" name="eto'+ name +'GhostDate" id="eto'+ name +'GhostDate" value="" placeholder="' + etoLang('bookingField_DatePlaceholder') + '" />' +
                    '<span class="input-group-addon">' +
                      '<span class="ion-ios-calendar-outline" title="' + etoLang('bookingField_DatePlaceholder') + '"></span>' +
                    '</span>' +
                  '</div>' +
                  '<div class="etoGhostTimeBox input-group" id="eto'+ name +'GhostTimeBox">' +
                    '<span class="form-control" id="eto'+ name +'GhostTimeInfo"></span>' +
                    '<input type="text" class="form-control" name="eto'+ name +'GhostTime" id="eto'+ name +'GhostTime" value="" placeholder="' + etoLang('bookingField_TimePlaceholder') + '" />' +
                    '<span class="input-group-addon">' +
                      '<span class="ion-ios-clock-outline" title="' + etoLang('bookingField_TimePlaceholder') + '"></span>' +
                    '</span>' +
                  '</div>' +
                '</div>' +
                '<div id="eto'+ name +'GhostDateWidget" class="etoGhostWidget" style="display:none;">' +
                '</div>'
            );
        }

        // Date widget
        if (etoData.request.config.booking_date_picker_style == 1) {
            $('#'+ etoData.mainContainer +' #eto'+ name +'GhostDate').combodate({
                firstItem: 'none',
                value: moment(minDateTime).format('YYYY-MM-DD'),
                customClass: 'form-control',
                template: 'DDMMMYYYY',
                format: 'YYYY-MM-DD',
                minuteStep: etoData.request.config.booking_time_picker_steps,
                roundTime: true,
                yearDescending: false,
                minYear: moment().year(),
                maxYear: moment().year() + 1,
                smartDays: true
            })
            .bind('change', function(e) {
                // console.log('combodate', name);
                var date = $('#'+ etoData.mainContainer +' #eto'+ name +'GhostDate').val();
                var time = $('#'+ etoData.mainContainer +' #eto'+ name +'GhostTime').val();
                var fDateTime = '';
                // var fDate = etoLang('bookingField_DatePlaceholder');
                var fDate = '';
                if (date && time) {
                    fDateTime = date + ' ' + time;
                }
                if (date) {
                    fDate = moment(date, 'YYYY-MM-DD').format('DD/MM/YYYY');
                }
                $('#'+ etoData.mainContainer +' #eto' + name).val(fDateTime);
                $('#'+ etoData.mainContainer +' #eto'+ name +'GhostDateInfo').html(fDate);

                var otherDate = moment($('#'+ etoData.mainContainer +' #eto'+ name +'GhostDate').data('DateTimePicker').date()).format('YYYY-MM-DD');
                // console.log(otherDate,date);
                if (otherDate != date) {
                    // console.log('combodate');
                    $('#'+ etoData.mainContainer +' #eto'+ name +'GhostDate').data('DateTimePicker').date(moment(date, 'YYYY-MM-DD'));
                }
                etoCheck();
            })
            .focus(function() {
                $(this).blur();
            });
        }

        $('#'+ etoData.mainContainer +' #eto'+ name +'GhostDate').datetimepicker({
            widgetParent: '#eto'+ name +'GhostDateWidget',
            inline: true,
            locale: language,
            format: 'YYYY-MM-DD',
            allowInputToggle: false,
            collapse: false,
            keepOpen: true,
            focusOnShow: false,
            toolbarPlacement: 'bottom',
            showTodayButton: true,
            showClose: false,
            // showClear: true,
            minDate: moment(minDateTime).format('YYYY-MM-DD'),
            maxDate: moment(minDateTime).add(1, 'years').format('YYYY-MM-DD'),
            tooltips: {
                today: etoLang('bookingField_Today'),
                clear: etoLang('bookingField_Clear'),
                close: etoLang('bookingField_Close'),
                selectMonth: etoLang('bookingField_SelectMonth'),
                prevMonth: etoLang('bookingField_PrevMonth'),
                nextMonth: etoLang('bookingField_NextMonth'),
                selectYear: etoLang('bookingField_SelectYear'),
                prevYear: etoLang('bookingField_PrevYear'),
                nextYear: etoLang('bookingField_NextYear'),
                selectDecade: etoLang('bookingField_SelectDecade'),
                prevDecade: etoLang('bookingField_PrevDecade'),
                nextDecade: etoLang('bookingField_NextDecade'),
                prevCentury: etoLang('bookingField_PrevCentury'),
                nextCentury: etoLang('bookingField_NextCentury')
            },
            icons: {
                close: 'ion-ios-checkmark-outline'
            }
        })
        .bind('dp.show', function(e) {
            $('#'+ etoData.mainContainer +' #eto'+ name +'GhostDateWidget a[data-action="today"]')
            .append('<span class="text">' + etoLang('bookingField_ButtonToday') + '</span>')
            .click(function(e) {
                $('#'+ etoData.mainContainer +' #eto'+ name +'GhostDate').data('DateTimePicker').date(minDateTime);
            });
        })
        .bind('dp.update', function(e) {
            // console.log('dp.update', e);
            if ($('#'+ etoData.mainContainer +' #etoForm').hasClass('etoJourneyTypeScheduled')) {
                etoScheduled();
            }
        })
        .bind('dp.change', function(e) {
            // console.log('dp.change', e);
            if ($('#'+ etoData.mainContainer +' #etoForm').hasClass('etoJourneyTypeScheduled')) {
                etoScheduled();
            }

            var date = $('#'+ etoData.mainContainer +' #eto'+ name +'GhostDate').val();
            var time = $('#'+ etoData.mainContainer +' #eto'+ name +'GhostTime').val();
            var fDateTime = '';
            var fDate = etoLang('bookingField_DatePlaceholder');
            if (date && time) {
                fDateTime = date + ' ' + time;
            }
            if (date) {
                fDate = moment(date, 'YYYY-MM-DD').format('DD/MM/YYYY');
            }
            $('#'+ etoData.mainContainer +' #eto' + name).val(fDateTime);
            $('#'+ etoData.mainContainer +' #eto'+ name +'GhostDateInfo').html(fDate);

            if (etoData.request.config.booking_date_picker_style == 1) {
                var otherDate = $('#'+ etoData.mainContainer +' #eto'+ name +'GhostDate').combodate('getValue');
                if (otherDate != date) {
                    $('#'+ etoData.mainContainer +' #eto'+ name +'GhostDate').combodate('setValue', moment(date, 'YYYY-MM-DD'));
                }
            }

            toggleDate = 0;
            $('#'+ etoData.mainContainer +' #eto'+ name +'GhostDateWidget').hide();
        })
        .focus(function() {
            $(this).blur();
        });

        $('.datepicker-months .picker-switch').on('click', function(e) {
            e.stopPropagation();
        });

        $('#'+ etoData.mainContainer +' #eto'+ name +'GhostDate').data('DateTimePicker').date(minDateTime);

        $('#'+ etoData.mainContainer +' #eto'+ name +'GhostDateWidget a[data-action="today"]')
        .append('<span class="text">' + etoLang('bookingField_ButtonToday') + '</span>')
        .click(function(e) {
            $('#'+ etoData.mainContainer +' #eto'+ name +'GhostDate').data('DateTimePicker').date(minDateTime);
        });

        if (etoData.request.config.booking_date_picker_style == 1) {
            $('#'+ etoData.mainContainer +' .etoGhostDateTime').addClass('etoGhostDateStyle1');
            $('#'+ etoData.mainContainer +' #eto'+ name +'GhostDateInfo').hide();
            var dateSwitcher = $('#'+ etoData.mainContainer +' #eto'+ name +'GhostDateBox .input-group-addon');
        } else {
            var dateSwitcher = $('#'+ etoData.mainContainer +' #eto'+ name +'GhostDateBox');
        }

        dateSwitcher.on('click', function(e) {
            // console.log('switch click', e);
            if (toggleDate == 1) {
                toggleDate = 0;
                $('#'+ etoData.mainContainer +' #eto'+ name +'GhostDateWidget').hide();
            } else {
                toggleDate = 1;
                $('#'+ etoData.mainContainer +' #eto'+ name +'GhostDateWidget').show();
                $('#'+ etoData.mainContainer +' #eto'+ name +'GhostDate').data('DateTimePicker').show();
            }
            e.preventDefault();
        });


        // Time widget
        $('#'+ etoData.mainContainer +' #eto'+ name +'GhostTime').combodate({
            firstItem: 'none',
            value: moment(minDateTime).format('HH:mm'),
            customClass: 'form-control',
            template: 'HHmm',
            format: 'HH:mm',
            minuteStep: etoData.request.config.booking_time_picker_steps,
            roundTime: true,
            yearDescending: false,
            minYear: moment().year(),
            maxYear: moment().year() + 5,
            smartDays: true
        })
        .bind('change', function(e) {
            var date = $('#'+ etoData.mainContainer +' #eto'+ name +'GhostDate').val();
            var time = $('#'+ etoData.mainContainer +' #eto'+ name +'GhostTime').val();
            var fDateTime = '';
            // var fTime = etoLang('bookingField_TimePlaceholder');
            var fTime = '';
            if (date && time) {
                fDateTime = date + ' ' + time;
            }
            if (time) {
                // fTime = moment(time, 'HH:mm').format('HH:mm') + '<span class="etoAmPmTime">(' + moment(time, 'HH:mm').format('h:mm a') + ')</span>';
                fTime = '<span class="etoAmPmTime">' + moment(time, 'HH:mm').format('h:mm a') + '</span>';
            }
            $('#'+ etoData.mainContainer +' #eto' + name).val(fDateTime);

            if (etoData.request.config.booking_time_picker_style == 1) {
                $('#'+ etoData.mainContainer +' #eto'+ name +'GhostTimeInfo').html(fTime);
            }

            $('#'+ etoData.mainContainer +' #eto' + name +'GhostTimeDropdown').val(time);

            etoCheck();
        })
        .focus(function() {
            $(this).blur();
        });

        if (etoData.request.config.booking_time_picker_style == 1) {
            $('#'+ etoData.mainContainer +' .etoGhostDateTime').addClass('etoGhostTimeStyle1');
            var cHour = 0;
            $('#'+ etoData.mainContainer +' #eto'+ name +'GhostTimeBox .combodate').find('select.hour option').each(function() {
                var val = $(this).val();
                var text = $(this).text();
                if (val) {
                    // text = moment(val, 'H').format('HH') + '&nbsp;&nbsp;&nbsp;&nbsp;(' + moment(val, 'H').format('h a') + ')';
                    text = moment(val, 'H').format('HH') + ' ' + moment(val, 'H').format('a') + '';
                }
                $(this).html(text);
            });
        }

        $('#'+ etoData.mainContainer +' #eto'+ name +'GhostTimeInfo').hide();


        // Add dropdown
        var dropdown = '<div class="clearfix eto-v2-time-picker-combined">';
        dropdown += '<select name="eto'+ name +'GhostTimeDropdown" id="eto'+ name +'GhostTimeDropdown" class="form-control">';
        // dropdown += '<option value="">' + etoLang('SELECT') + '</option>';
        for (i = 0; i <= 23; i++) {
            for (j = 0; j < 60; j += 5) {
                var option_val = ((i < 10) ? '0'+ i : i) +':'+ ((j < 10) ? '0'+ j : j);
                dropdown += '<option value="'+ option_val +'">'+ option_val +'</option>';
            }
        }
        dropdown += '</select>';
        dropdown += '</div>';

        $('#'+ etoData.mainContainer +' #eto'+ name +'GhostTime').after(dropdown);

        $('#'+ etoData.mainContainer +' #eto'+ name +'GhostTimeDropdown').change(function() {
            $('#'+ etoData.mainContainer +' #eto'+ name +'GhostTime').combodate('setValue', moment($(this).val(), 'HH:mm'));
        });

        $('#'+ etoData.mainContainer +' #eto'+ name +'Container .eto-v2-time-picker-combined').hide();
        $('#'+ etoData.mainContainer +' #eto'+ name +'Container .combodate').show();

        // Pickup time in minutes
        if (etoData.request.config.booking_time_picker_by_minute) {
            $('#'+ etoData.mainContainer +' #eto'+ name +'Container .eto-v2-time-picker-minutes').remove();
            var optionTrans = etoLang('bookingTimePickerMinutes');
            var dropdown = '<div class="clearfix eto-v2-time-picker-minutes">';
            dropdown += '<select name="eto'+ name +'GhostTimeDropdownMinutes" id="eto'+ name +'GhostTimeDropdownMinutes" class="form-control">';
            for (i = 5; i <= 60; i += 5) {
                dropdown += '<option value="'+ i +'">'+ optionTrans.replace(/\{time\}/g, i) +'</option>';
            }
            dropdown += '</select>';
            dropdown += '</div>';

            $('#'+ etoData.mainContainer +' #eto'+ name +'GhostTime').after(dropdown);

            $('#'+ etoData.mainContainer +' #eto'+ name +'GhostTimeDropdownMinutes').change(function() {
                var ghostDate = moment().add(parseInt($(this).val()), 'minutes');
                var formatedDate = ghostDate.format('YYYY-MM-DD HH:mm');
                $('#'+ etoData.mainContainer +' #eto'+ name +'GhostDate').data('DateTimePicker').date(ghostDate);
                $('#'+ etoData.mainContainer +' #eto'+ name +'GhostTime').combodate('setValue', ghostDate);
                $('#'+ etoData.mainContainer +' #eto'+ name +'').val(formatedDate);
            });

            $('#'+ etoData.mainContainer +' #eto'+ name +'Container .etoGhostDateTime').addClass('eto-v2-time-picker-minutes-active');
        }
        else {
            $('#'+ etoData.mainContainer +' #eto'+ name +'Container .etoGhostDateTime').removeClass('eto-v2-time-picker-minutes-active');
        }

        // fieldName.indexOf('etoRoute2Vehicle') to check?
    }

    var inputIndex = 1;

    function etoCreate(type, name, label, options, info, help) {
        var html = '';

        switch (type) {
            case 'preferred':
                var maxP = 0;
                var maxL = 0;
                var maxH = 0;

                if (etoData.request.vehicle) {
                    $.each(etoData.request.vehicle, function(key, value) {
                        if (maxP < parseInt(value.passengers)) {
                            maxP = parseInt(value.passengers);
                        }
                        if (maxL < parseInt(value.luggage)) {
                            maxL = parseInt(value.luggage);
                        }
                        if (maxH < parseInt(value.hand_luggage)) {
                            maxH = parseInt(value.hand_luggage);
                        }
                    });
                }

                html += '<div class="clearfix eto-v2-preferred-box">';
                    html += '<div class="eto-v2-preferred-passengers" title="'+ etoLang('ROUTE_PASSENGERS') +'">';
                      // html += '<img src="' + etoData.appPath + '/assets/images/icons/passengers.png" alt="">';
                      html += '<i class="ion-ios-person-outline"></i>';
                      html += '<select name="etoPreferredPassengers" id="etoPreferredPassengers" class="form-control">';
                      if (maxP == 0) {
                          html += '<option value="0">0</option>';
                      }
                      for (var i = 1; i <= maxP; i++) {
                          var selectedOption = (i == 1) ? 'selected="selected"' : '';
                          html += '<option value="' + i + '" '+ selectedOption +'>' + i + '</option>';
                      }
                      html += '</select>';
                    html += '</div>';

                    html += '<div class="eto-v2-preferred-luggage" title="'+ etoLang('ROUTE_LUGGAGE') +'">';
                      // html += '<img src="' + etoData.appPath + '/assets/images/icons/luggage.png" alt="">';
                      html += '<i class="ion-ios-briefcase-outline"></i>';
                      html += '<select name="etoPreferredLuggage" id="etoPreferredLuggage" class="form-control">';
                      html += '<option value="0">0</option>';
                      for (var i = 1; i <= maxL; i++) {
                          html += '<option value="' + i + '">' + i + '</option>';
                      }
                      html += '</select>';
                    html += '</div>';

                    html += '<div class="eto-v2-preferred-hand_luggage" title="'+ etoLang('ROUTE_HAND_LUGGAGE') +'">';
                      // html += '<img src="' + etoData.appPath + '/assets/images/icons/hand_luggage.png" alt="">';
                      html += '<i class="ion-bag"></i>';
                      html += '<select name="etoPreferredHandLuggage" id="etoPreferredHandLuggage" class="form-control">';
                      html += '<option value="0">0</option>';
                      for (var i = 1; i <= maxH; i++) {
                          html += '<option value="' + i + '">' + i + '</option>';
                      }
                      html += '</select>';
                    html += '</div>';
                html += '</div>';

            break;
            case 'category':

                var placeholder = '';
                if (options && options.placeholder) {
                    placeholder = options.placeholder;
                }

                html = '<div class="etoOuterContainer" id="eto'+ name +'Container">' +
                    '<label class="etoLabel" for="eto'+ name +'" id="eto'+ name +'Label">' + label + '</label>' +
                    '<div class="etoInnerContainer">';

                if (etoData.enabledTypeahead == 1) {
                    html += '<input name="eto'+ name +'Typeahead" id="eto'+ name +'Typeahead" class="form-control typeahead" type="text" placeholder="' + placeholder + '" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">';
                }

                html += '<input type="hidden" name="eto'+ name +'PlaceId" id="eto'+ name +'PlaceId">';

                html += '<select name="eto'+ name +'" id="eto'+ name +'" class="form-control">';
                if (etoData.enabledTypeahead == 1) {
                    html += '<option value="">' + etoLang('SELECT') + '</option>';
                }

                var hasAddressType = 0;

                if (etoData.request.category) {
                    $.each(etoData.request.category, function(key, value) {
                        var selected = '';
                        if (value.type == 'address') {
                            hasAddressType = 1;
                            selected = 'selected="selected"';
                        }
                        html += '<option value="' + value.id + '" field_type="' + value.type + '" '+ selected +'>' + value.name + '</option>';
                    });
                }

                if (hasAddressType == 0) {
                    html += '<option value="1000000" field_type="address" selected="selected">Address</option>';
                }

                html += '</select>' +
                    '<div class="clear"></div>' +
                    '</div>' +
                    '<div class="clear"></div>' +
                    '</div>';

            break;
            case 'location':

                if (options.value > 0) {

                    html = '<div class="etoOuterContainer" id="eto'+ name +'Container">' +
                        '<label class="etoLabel" for="eto'+ name +'" id="eto'+ name +'Label">' + label + '</label>' +
                        '<div class="etoInnerContainer">';

                    if (options.type == 'address') { // Address

                        html += '<textarea name="eto'+ name +'" id="eto'+ name +'" autocomplete="off" autocorrect="off" autocapitalize="off" class="form-control"></textarea>';

                    } else { // Dropdown

                        html += '<select name="eto'+ name +'" id="eto'+ name +'">';

                        if (etoData.request.location.length <= 0) {
                            html += '<option value="">' + etoLang('SELECT') + '</option>';
                        }

                        if (etoData.request.location) {
                            $.each(etoData.request.location, function(key, value) {
                                if (value.category_id == options.value) {
                                    html += '<option value="' + value.id + '">' + value.name + '</option>';
                                }
                            });
                        }

                        html += '</select>';
                    }

                    html += '<div class="clear"></div>' +
                        '</div>' +
                        '<div class="clear"></div>' +
                        '</div>';

                    $('#'+ etoData.mainContainer +' #eto'+ name +'Loader').html(html);

                    if (options.type == 'address') { // Address

                        // Geolocation
                        $('#'+ etoData.mainContainer +' #eto'+ name +'GeolocationButton').click(function() {
                            etoGeolocation(name);
                        });

                        // Auto complete
                        if (etoData.enabledTypeahead == 0) {
                            var autoCompleteInput = $('#'+ etoData.mainContainer +' #eto'+ name);
                            

                            // var autoCompleteInput = $('#'+ etoData.mainContainer +' #eto'+ name);
                            var autoCompleteInput = document.getElementById('eto' + name);

                            var autoCompleteOptions = {
                                bounds: new google.maps.LatLngBounds(
                                    new google.maps.LatLng(49.00, -13.00),
                                    new google.maps.LatLng(60.00, 3.00)
                                ),
                                componentRestrictions: {
                                    country: 'uk'
                                },
                                types: ['geocode']
                            };

                            var autoComplete = new google.maps.places.Autocomplete(autoCompleteInput, autoCompleteOptions);

                            google.maps.event.addListener(autoComplete, 'place_changed', function() {
                                etoCheck();
                            });
                        }
                    }

                    $('#'+ etoData.mainContainer +' #eto' + name).focus().blur().change(function() {
                        etoData.quoteStatus = 0;
                        etoCheck();
                    });

                    $('#'+ etoData.mainContainer +' #eto'+ name +'Container').show();
                } else {
                    $('#'+ etoData.mainContainer +' #eto'+ name +'Container').hide();
                }

                break;
            case 'loader':
                html = '<div id="eto'+ name +'Loader"></div>';
                break;
            case 'return':

                html += '<div class="etoOuterContainer" id="eto'+ name +'Container">' +
                    '<label class="etoLabel" for="eto'+ name +'" id="eto'+ name +'Label">' + label + '</label>' +
                    '<div class="etoInnerContainer">' +
                    '<div class="checkbox">' +
                    '<label for="eto'+ name +'2"><input type="checkbox" name="eto'+ name +'" id="eto'+ name +'2" value="2"><span class="cr"><i class="cr-icon ion-ios-checkmark-empty"></i></span><span class="cr-label">' + etoLang('bookingField_ReturnEnable') + '</span></label>' +
                    '</div>' +
                    /*
                    '<input type="radio" name="eto'+ name +'" id="eto'+ name +'1" value="1" checked="checked">' +
                    // '<label for="eto'+ name +'1" id="eto'+ name +'Label1" class="btn btn-sm">' + etoLang('ROUTE_RETURN_NO') + '</label>' +
                    '<button class="btn btn-primary btn-block" id="eto'+ name +'Btn1" onclick="$(\'#eto'+ name +'1\').attr(\'checked\', true).change(); return false;">' + etoLang('ROUTE_RETURN_NO') + '</button>' +

                    '<input type="radio" name="eto'+ name +'" id="eto'+ name +'2" value="2">' +
                    // '<label for="eto'+ name +'2" id="eto'+ name +'Label2" class="btn btn-sm">' + etoLang('ROUTE_RETURN_YES') + '</label>'+
                    '<button class="btn btn-primary btn-block" id="eto'+ name +'Btn2" onclick="$(\'#eto'+ name +'2\').attr(\'checked\', true).change(); return false;">' + etoLang('ROUTE_RETURN_YES') + '</button>' +
                    */
                    '<div class="clear"></div>' +
                    '</div>' +
                    '<div class="clear"></div>' +
                    '</div>';

                break;
            case 'amount':

                if (options > 0) {
                    html = '<div class="etoOuterContainer" id="eto'+ name +'Container">' +
                        '<div class="etoInnerContainer eto-v2-field eto-v2-field-has-value">' +
                        '<span class="eto-v2-field-label">' + label + '</span>' +
                        '<select name="eto'+ name +'" id="eto'+ name +'" class="form-control">';
                    // html += '<option value="">'+ etoLang('SELECT') +'</option>';
                    for (var i = 0; i <= options; i++) {
                        html += '<option value="' + i + '">' + i + '</option>';
                    }
                    html += '</select>';
                    if (info) {
                        html += '<small>' + info + '</small>';
                    }
                    html += '<div class="clear"></div>' +
                        '</div>' +
                        '<div class="clear"></div>' +
                        '</div>';

                    $('#'+ etoData.mainContainer +' #eto'+ name +'Loader').html(html);

                    $('#'+ etoData.mainContainer +' #eto' + name).change(function() {
                        etoData.quoteStatus = 0;

                        if ($('#'+ etoData.mainContainer +' #etoForm').hasClass('etoJourneyTypeScheduled')) {
                            var scheduled = 1;
                        }
                        else {
                            var scheduled = 0;
                        }

                        if (scheduled && name == 'Route1Passengers') {
                            $('#etoPassengersGhost').val($(this).val());
                            etoCheck(1);
                        } else if (name == 'Route1ChildSeats' && etoData.request.config.charge_child_seat > 0) {
                            etoCheck(1);
                        } else if (name == 'Route1BabySeats' && etoData.request.config.charge_baby_seat > 0) {
                            etoCheck(1);
                        } else if (name == 'Route1InfantSeats' && etoData.request.config.charge_infant_seats > 0) {
                            etoCheck(1);
                        } else if (name == 'Route1Wheelchair' && etoData.request.config.charge_wheelchair > 0) {
                            etoCheck(1);
                        } else if (name == 'Route2ChildSeats' && etoData.request.config.charge_child_seat > 0) {
                            etoCheck(1);
                        } else if (name == 'Route2BabySeats' && etoData.request.config.charge_baby_seat > 0) {
                            etoCheck(1);
                        } else if (name == 'Route2InfantSeats' && etoData.request.config.charge_infant_seats > 0) {
                            etoCheck(1);
                        } else if (name == 'Route2Wheelchair' && etoData.request.config.charge_wheelchair > 0) {
                            etoCheck(1);
                        } else {
                            etoCheck(0);
                        }


                        // Allow only on child seat
                        if (etoData.request.config.booking_allow_one_type_of_child_seat) {
                            if (name == 'Route1ChildSeats' || name == 'Route1BabySeats' || name == 'Route1InfantSeats') {
                                if (name != 'Route1ChildSeats') {
                                    $('#'+ etoData.mainContainer +' #etoRoute1ChildSeats').val(0);
                                }
                                if (name != 'Route1BabySeats') {
                                    $('#'+ etoData.mainContainer +' #etoRoute1BabySeats').val(0);
                                }
                                if (name != 'Route1InfantSeats') {
                                    $('#'+ etoData.mainContainer +' #etoRoute1InfantSeats').val(0);
                                }
                            } else if (name == 'Route2ChildSeats' || name == 'Route2BabySeats' || name == 'Route2InfantSeats') {
                                if (name != 'Route2ChildSeats') {
                                    $('#'+ etoData.mainContainer +' #etoRoute2ChildSeats').val(0);
                                }
                                if (name != 'Route2BabySeats') {
                                    $('#'+ etoData.mainContainer +' #etoRoute2BabySeats').val(0);
                                }
                                if (name != 'Route2InfantSeats') {
                                    $('#'+ etoData.mainContainer +' #etoRoute2InfantSeats').val(0);
                                }
                            }
                        }


                    });

                    $('#'+ etoData.mainContainer +' #eto'+ name +'Container').show();
                } else {
                    $('#'+ etoData.mainContainer +' #eto'+ name +'Container').hide();
                }

                break;
            case 'input':

                var placeholder = '';
                var fieldClass = '';
                var flightLabel = '';

                if (options) {
                    if (options.placeholder) {
                        placeholder = options.placeholder;
                    }
                    if (options.fieldClass) {
                        fieldClass = options.fieldClass;
                    }
                }

                if ( name == 'Route1WaypointsComplete[]' || name == 'Route2WaypointsComplete[]' ) {
                    var id_attr = name.replace('[]', inputIndex);
                    inputIndex += 1;
                }
                else {
                    var id_attr = name;
                }

                if(name != 'ContactMobile' && name != 'LeadPassengerMobile') {
                    placeholder = $.trim(label +' '+ placeholder);
                }

                if(name == 'Route1FlightNumber' || name == 'Route2FlightNumber') {
                    flightLabel = '<span class="eto-v2-field-label-flight">'+ etoLang('bookingFlightMsg') +'</span>';
                    placeholder = etoLang('bookingFlightExample');
                }

                if(name == 'Route1DepartureFlightNumber' || name == 'Route2DepartureFlightNumber') {
                    flightLabel = '<span class="eto-v2-field-label-flight">'+ etoLang('bookingDepartureFlightMsg') +'</span>';
                    placeholder = etoLang('bookingFlightExample');
                }

                html = '<div class="etoOuterContainer" id="eto'+ name +'Container">' +
                    flightLabel+
                    '<div class="etoInnerContainer eto-v2-field '+ fieldClass +' clearfix">' +
                    '<span class="eto-v2-field-label">' + label + '</span>' +
                    '<input type="text" name="eto'+ name +'" id="eto' + id_attr + '" value=""  placeholder="' + placeholder + '" class="form-control" />';
                if (info) {
                    html += '<small>' + info + '</small>';
                }
                html += '<div class="clear"></div>' +
                    '</div>' +
                    '<div class="clear"></div>' +
                    '</div>';

                break;
            case 'textarea':

                var placeholder = '';
                if (options && options.placeholder) {
                    placeholder = options.placeholder;
                }

                html = '<div class="etoOuterContainer" id="eto'+ name +'Container">' +
                    '<div class="etoInnerContainer eto-v2-field">' +
                    '<span class="eto-v2-field-label">' + label + '</span>' +
                    '<textarea name="eto'+ name +'" id="eto'+ name +'" placeholder="' + $.trim(label +' '+ placeholder) + '" class="form-control"></textarea>';
                if (info) {
                    html += '<small>' + info + '</small>';
                }
                html += '<div class="clear"></div>' +
                    '</div>' +
                    '<div class="clear"></div>' +
                    '</div>';

                break;
            case 'charges':

                html = '<div class="etoOuterContainer" id="eto'+ name +'Container">' +
                    '<label class="etoLabel" for="eto'+ name +'" id="eto'+ name +'Label">' + label + '</label>' +
                    '<div class="etoInnerContainer">' +
                    '<div id="eto'+ name +'Display" class="form-control1"></div>' +
                    '<textarea name="eto'+ name +'" id="eto'+ name +'" style="display:none;"></textarea>';
                if (info) {
                    html += '<small>' + info + '</small>';
                }
                html += '<div class="clear"></div>' +
                    '</div>' +
                    '<div class="clear"></div>' +
                    '</div>';

                break;
            case 'price':

                html = '<div class="etoOuterContainer" id="eto'+ name +'Container">' +
                    '<label class="etoLabel" for="eto'+ name +'" id="eto'+ name +'Label">' + label + '</label>' +
                    '<div class="etoInnerContainer">' +
                    '<div id="eto'+ name +'Display">0</div>' +
                    '<input type="hidden" name="eto'+ name +'" id="eto'+ name +'" value="0" />';
                if (info) {
                    html += '<small>' + info + '</small>';
                }
                html += '<div class="clear"></div>' +
                    '</div>' +
                    '<div class="clear"></div>' +
                    '</div>';

                break;
            case 'button':

                var cls = 'btn-primary';
                var icon = '';

                if (options && options.cls) {
                    cls = options.cls;
                }
                if (options && options.icon) {
                    icon = '<i class="'+ options.icon +'"></i>';
                }

                html += '<div class="etoOuterContainer" id="eto'+ name +'Container">' +
                    '<label class="etoLabel" for="eto'+ name +'" id="eto'+ name +'Label"></label>' +
                    '<div class="etoInnerContainer">' +
                    '<button id="eto'+ name +'" class="eto-v2-button eto-v2-button-'+ name +' btn ' + cls + '" onclick="return false;"><span class="eto-v2-button-label">'+ label + '</span>'+ icon +'</button>' +
                    '<div class="clear"></div>' +
                    '</div>' +
                    '<div class="clear"></div>' +
                    '</div>';

                break;
            case 'clear':

                html += '<div class="clear"></div>';

                break;
            case 'vehicle_single':

                if (etoData.request.config.booking_vehicle_display_mode == 'box') {
                    html = '<div class="eto-v2-vehicles clearfix" id="eto'+ name +'Container">';
                    if (etoData.request.vehicle) {
                        $.each(etoData.request.vehicle, function(key, value) {
                            // var onclick = 'onclick="$(\'#eto'+ name +'Container .etoVehicleContainer' + value.id + '.etoVehicleAllowed select#eto'+ name +'' + value.id + '\').val(1).change(); return false;"';
                            var onclick = '';

                            var details = '';
                            if (parseInt(value.passengers) > 0 && etoData.request.config.enable_passengers > 0) {
                                details += '<div class="eto-v2-vehicle-details-item eto-v2-vehicle-details-item-1">\
                                  <span class="eto-v2-vehicle-details-item-icon"><i class="fa fa-male"></i></span>\
                                  <span class="eto-v2-vehicle-details-item-value">'+ value.passengers +'</span>\
                                  <span class="eto-v2-vehicle-details-item-name">'+ etoLang('VEHICLE_PASSENGERS') +'</span>\
                                </div>';
                            }
                            if (parseInt(value.luggage) > 0 && etoData.request.config.enable_luggage > 0) {
                                details += '<div class="eto-v2-vehicle-details-item eto-v2-vehicle-details-item-2">\
                                  <span class="eto-v2-vehicle-details-item-icon"><i class="fa fa-suitcase"></i></span>\
                                  <span class="eto-v2-vehicle-details-item-value">'+ value.luggage +'</span>\
                                  <span class="eto-v2-vehicle-details-item-name">'+ etoLang('VEHICLE_LUGGAGE') +'</span>\
                                </div>';
                            }
                            if (parseInt(value.hand_luggage) > 0 && etoData.request.config.enable_hand_luggage > 0) {
                                details += '<div class="eto-v2-vehicle-details-item eto-v2-vehicle-details-item-3">\
                                  <span class="eto-v2-vehicle-details-item-icon"><i class="fa fa-briefcase"></i></span>\
                                  <span class="eto-v2-vehicle-details-item-value">' + value.hand_luggage + '</span>\
                                  <span class="eto-v2-vehicle-details-item-name">'+ etoLang('VEHICLE_HAND_LUGGAGE') +'</span>\
                                </div>';
                            }
                            if (parseInt(value.child_seats) > 0 && etoData.request.config.enable_child_seats > 0) {
                                details += '<div class="eto-v2-vehicle-details-item eto-v2-vehicle-details-item-4">\
                                  <span class="eto-v2-vehicle-details-item-icon"><i class="fa fa-child"></i></span>\
                                  <span class="eto-v2-vehicle-details-item-value">' + value.child_seats + '</span>\
                                  <span class="eto-v2-vehicle-details-item-name">'+ etoLang('VEHICLE_CHILD_SEATS') +'</span>\
                                </div>';
                            }
                            if (parseInt(value.baby_seats) > 0 && etoData.request.config.enable_baby_seats > 0) {
                                details += '<div class="eto-v2-vehicle-details-item eto-v2-vehicle-details-item-5">\
                                  <span class="eto-v2-vehicle-details-item-icon"><i class="fa fa-child"></i></span>\
                                  <span class="eto-v2-vehicle-details-item-value">' + value.baby_seats + '</span>\
                                  <span class="eto-v2-vehicle-details-item-name">'+ etoLang('VEHICLE_BABY_SEATS') +'</span>\
                                </div>';
                            }
                            if (parseInt(value.infant_seats) > 0 && etoData.request.config.enable_infant_seats > 0) {
                                details += '<div class="eto-v2-vehicle-details-item eto-v2-vehicle-details-item-6">\
                                  <span class="eto-v2-vehicle-details-item-icon"><i class="fa fa-child"></i></span>\
                                  <span class="eto-v2-vehicle-details-item-value">' + value.infant_seats + '</span>\
                                  <span class="eto-v2-vehicle-details-item-name">'+ etoLang('VEHICLE_INFANT_SEATS') +'</span>\
                                </div>';
                            }
                            if (parseInt(value.wheelchair) > 0 && etoData.request.config.enable_wheelchair > 0) {
                                details += '<div class="eto-v2-vehicle-details-item eto-v2-vehicle-details-item-7">\
                                  <span class="eto-v2-vehicle-details-item-icon"><i class="fa fa-wheelchair"></i></span>\
                                  <span class="eto-v2-vehicle-details-item-value">' + value.wheelchair + '</span>\
                                  <span class="eto-v2-vehicle-details-item-name">'+ etoLang('VEHICLE_WHEELCHAIR') +'</span>\
                                </div>';
                            }

                            var selectOptions = '';
                            for (var i = 0; i <= parseInt(value.max_amount); i++) {
                                selectOptions += '<option value="' + i + '">' + i + '</option>';
                            }

                            var image = '';
                            if (value.image_path) {
                                // image = '<div class="eto-v2-vehicle-img"><img src="'+ value.image_path +'" alt="" /></div>';
                                image = '<div class="eto-v2-vehicle-img eto-v2-vehicle-img-bg" style="background-image:url(\''+ value.image_path +'\');"></div>';
                            }

                            html += '<div class="eto-v2-vehicle eto-v2-vehicle-'+ value.id +' etoVehicleContainer' + value.id + ' clearfix" '+ onclick +'>\
                              <label class="etoVehicleInnerContainer clearfix" for="eto'+ name +'' + value.id + '">\
                                <div class="eto-v2-vehicle-top">\
                                  <div class="eto-v2-vehicle-name">'+ value.name +'</div>\
                                  <div class="eto-v2-vehicle-desc">'+ value.description +'</div>\
                                  '+ image +'\
                                </div>\
                                <div class="eto-v2-vehicle-bottom clearfix">\
                                  <div class="eto-v2-vehicle-details clearfix">'+ details +'</div>\
                                  <div class="eto-v2-vehicle-price clearfix">\
                                    <div class="etoVehicleTotalPrice" id="eto'+ name +'' + value.id + 'TotalPrice">\
                                        ' + etoData.request.config.currency_symbol + '0' + etoData.request.config.currency_code + '\
                                    </div>\
                                    <div class="eto-v2-vehicle-reserve">\
                                      <span>'+ etoLang('BTN_RESERVE') +'</span>\
                                      <i class="fa fa-chevron-right"></i>\
                                    </div>\
                                    <div class="eto-v2-vehicle-disable-info" id="eto'+ name +'' + value.id + 'MsgBottom">\
                                        <button class="btn btn-primary etoVehicleSelectButton"><i class="fa"></i></button>\
                                    </div>\
                                    <select name="eto'+ name +'[' + value.id + ']" id="eto'+ name +'' + value.id + '" vehicle_id="' + value.id + '" class="etoVehicleSelect">\
                                        '+ selectOptions +'\
                                    </select>\
                                  </div>\
                                </div>\
                              </label>\
                            </div>';
                        });
                    }
                    html += '</div>';
                }
                else {

                  html = '<div class="etoOuterContainer" id="eto'+ name +'Container">' +
                      '<label class="etoLabel" for="eto'+ name +'" id="eto'+ name +'Label">' + label + '</label>' +
                      '<div class="etoInnerContainer">';
                  if (etoData.request.vehicle) {
                      $.each(etoData.request.vehicle, function(key, value) {

                          // var onclick = 'onclick="$(\'#eto'+ name +'Container .etoVehicleContainer' + value.id + '.etoVehicleAllowed select#eto'+ name +'' + value.id + '\').val(1).change(); return false;"';
                          var onclick = '';

                          var details = '';
                          if (parseInt(value.passengers) > 0 && etoData.request.config.enable_passengers > 0) {
                              details += '<div class="etoVehicleDetails1"><img src="' + etoData.appPath + '/assets/images/icons/passengers.png" alt="" title="' + etoLang('VEHICLE_PASSENGERS') + '" /><span>x' + value.passengers + '</span></div>';
                          }
                          if (parseInt(value.luggage) > 0 && etoData.request.config.enable_luggage > 0) {
                              details += '<div class="etoVehicleDetails2"><img src="' + etoData.appPath + '/assets/images/icons/luggage.png" alt="" title="' + etoLang('VEHICLE_LUGGAGE') + '" /><span>x' + value.luggage + '</span></div>';
                          }
                          if (parseInt(value.hand_luggage) > 0 && etoData.request.config.enable_hand_luggage > 0) {
                              details += '<div class="etoVehicleDetails3"><img src="' + etoData.appPath + '/assets/images/icons/hand_luggage.png" alt="" title="' + etoLang('VEHICLE_HAND_LUGGAGE') + '" /><span>x' + value.hand_luggage + '</span></div>';
                          }
                          if (parseInt(value.child_seats) > 0 && etoData.request.config.enable_child_seats > 0) {
                              details += '<div class="etoVehicleDetails4"><img src="' + etoData.appPath + '/assets/images/icons/child_seats.png" alt="" title="' + etoLang('VEHICLE_CHILD_SEATS') + '" /><span>x' + value.child_seats + '</span></div>';
                          }
                          if (parseInt(value.baby_seats) > 0 && etoData.request.config.enable_baby_seats > 0) {
                              details += '<div class="etoVehicleDetails5"><img src="' + etoData.appPath + '/assets/images/icons/baby_seats.png" alt="" title="' + etoLang('VEHICLE_BABY_SEATS') + '" /><span>x' + value.baby_seats + '</span></div>';
                          }
                          if (parseInt(value.infant_seats) > 0 && etoData.request.config.enable_infant_seats > 0) {
                              details += '<div class="etoVehicleDetails6"><img src="' + etoData.appPath + '/assets/images/icons/infant_seats.png" alt="" title="' + etoLang('VEHICLE_INFANT_SEATS') + '" /><span>x' + value.infant_seats + '</span></div>';
                          }
                          if (parseInt(value.wheelchair) > 0 && etoData.request.config.enable_wheelchair > 0) {
                              details += '<div class="etoVehicleDetails7"><img src="' + etoData.appPath + '/assets/images/icons/wheelchair.png" alt="" title="' + etoLang('VEHICLE_WHEELCHAIR') + '" /><span>x' + value.wheelchair + '</span></div>';
                          }

                          var selectOptions = '';
                          for (var i = 0; i <= parseInt(value.max_amount); i++) {
                              selectOptions += '<option value="' + i + '">' + i + '</option>';
                          }

                          var image = '';
                          if (value.image_path) {
                              // image = '<div class="etoVehicleImage"><img src="'+ value.image_path +'" alt="" /></div>';
                              image = '<div class="etoVehicleImage etoVehicleImageBG" style="background-image:url(\''+ value.image_path +'\');"></div>';
                          }

                          html += '<div class="etoVehicleContainer etoVehicleContainer' + value.id + '" ' + onclick + '>\
                              <label for="eto'+ name +'' + value.id + '" title="' + value.description + '">\
                                  <div class="box etoVehicleInnerContainer">\
                                      <div class="etoVehicleColumn1">'+ image +'</div>\
                                      <div class="etoVehicleColumn2">\
                                          <div class="etoVehicleName">' + value.name + '</div>\
                                          <div class="etoVehicleDetails">\
                                              ' + details + '\
                                              <div class="clear"></div>\
                                          </div>\
                                      </div>\
                                      <div class="etoVehicleColumn3">\
                                          <div class="etoVehicleTotalPrice" id="eto'+ name +'' + value.id + 'TotalPrice">\
                                              ' + etoData.request.config.currency_symbol + '0' + etoData.request.config.currency_code + '\
                                          </div>\
                                          <div class="etoVehicleSelectDisableInfo" id="eto'+ name +'' + value.id + 'MsgBottom">\
                                              <button class="btn btn-primary etoVehicleSelectButton"><i class="fa"></i></button>\
                                          </div>\
                                          <select name="eto'+ name +'[' + value.id + ']" id="eto'+ name +'' + value.id + '" vehicle_id="' + value.id + '" class="etoVehicleSelect">\
                                              ' + selectOptions + '\
                                          </select>\
                                      </div>\
                                      <div class="clear"></div>\
                                  </div>\
                              </label>\
                          </div>';

                      });
                  }
                  html += '<div class="clear"></div>' +
                      '</div>' +
                      '<div class="clear"></div>' +
                      '</div>';

                }

                break;
            case 'payment_single':

                var ccLogos = '';

                html += '<div class="etoOuterContainer" id="eto'+ name +'Container">' +
                    '<div class="etoInnerContainer">';
                if (etoData.request.payment) {
                    $.each(etoData.request.payment, function(key, value) {
                        var checked = '';
                        if (value['default'] == 1) {
                            checked = 'checked="checked"';
                        }
                        var methodCls = value.method.charAt(0).toUpperCase() + value.method.slice(1);
                        var cols = 'eto-v2-payment-method eto-v2-payment-method-'+ value.id +' eto-v2-payment-method-'+ value.method;

                        html += '<div class="' + cols + '">';
                        html += '<div class="etoPaymentContainer etoPayment' + value.id + 'MainContainer etoPayment' + methodCls + 'Container">' +
                            '<div class="etoPaymentDeposit etoPayment' + value.id + 'Deposit"></div>' +
                            '<input type="radio" name="eto'+ name +'" id="eto'+ name +'' + value.id + '" value="' + value.id + '" ' + checked + ' />' +
                            '<label for="eto'+ name +'' + value.id + '">';
                        // html += '<span class="etoPaymentHeader">' + etoLang('bookingButton_BookNow') + '</span>';

                        var logo = '';
                        if (value.image_path) {
                            logo += '<img class=\'etoPaymentImage\' src=\'' + value.image_path + '\' />';
                        }

                        var text = '<span class="etoPaymentTotalPrice" id="etoPayment' + value.id + 'TotalPrice">' + etoData.request.config.currency_symbol + '0' + etoData.request.config.currency_code + '</span>';
                        value.description = '<div class="etoPaymentButtonText">' + text + '<span class="eto-v2-payment-method-name">' + value.description + '</span></div><div class="etoPaymentButtonLogoTable"><div class="etoPaymentButtonLogo">' + logo + '</div></div>';
                        html += '<button class="btn btn-primary etoPaymentButton" onclick="$(\'input#eto'+ name +'' + value.id + '\').attr(\'checked\', true).change(); return false;" >' + value.description + '</button>';

                        // if (value.image) {
                        // ccLogos += logo;
                        // html += logo;
                        // }

                        html += '</label>' +
                            '<div class="clear"></div>' +
                            '</div>';

                        html += '</div>';
                    });
                }

                if (ccLogos) {
                    html += '<div class="etoPaymentLogosContainer">' + ccLogos + '</div>';
                }

                html += '<div class="clear"></div>' +
                    '</div>' +
                    '<div class="clear"></div>' +
                    '</div>';

            break;
            case 'meet_and_greet':

                html = '<div class="etoOuterContainer" id="eto'+ name +'Container">' +
                    // '<label class="etoLabel" for="eto'+ name +'" id="eto'+ name +'Label">' + label + '</label>' +
                    '<div class="etoInnerContainer">';

                var chargeAmount = '';

                if (etoData.request.config.charge_meet_and_greet > 0) {
                    chargeAmount = ' (' + etoData.request.config.currency_symbol + etoData.request.config.charge_meet_and_greet + etoData.request.config.currency_code + ')';
                }

                // html += '<select name="eto'+ name +'" id="eto'+ name +'" class="form-control">';
                // html += '<option value="0">' + etoLang('MEET_AND_GREET_OPTION_NO') + '</option>';
                // html += '<option value="1">' + etoLang('MEET_AND_GREET_OPTION_YES') + chargeAmount + '</option>';
                // html += '</select>';

                html += '<div class="checkbox">' +
                    '<label for="eto'+ name +'"><input type="checkbox" name="eto'+ name +'" id="eto'+ name +'" value="1"><span class="cr"><i class="cr-icon ion-ios-checkmark-empty"></i></span>' + label + chargeAmount + '</label>' +
                    '</div>';

                if (info) {
                    html += '<small>' + info + '</small>';
                }
                html += '<div class="clear"></div>' +
                    '</div>' +
                    '<div class="clear"></div>' +
                    '</div>';

                break;
            case 'checkbox':

                html = '<div class="etoOuterContainer" id="eto'+ name +'Container">' +
                    '<label class="etoLabel" for="eto'+ name +'" id="eto'+ name +'Label"></label>' +
                    '<div class="etoInnerContainer">' +
                    '<div class="checkbox"><label for="eto'+ name +'"><input type="checkbox" name="eto'+ name +'" id="eto'+ name +'" value="1" /><span class="cr"><i class="cr-icon ion-ios-checkmark-empty"></i></span>' + label + '</label></div>';
                // html += '' + label + '</label>';
                if (info) {
                    html += '<small>' + info + '</small>';
                }
                html += '<div class="clear"></div>' +
                    '</div>' +
                    '<div class="clear"></div>' +
                    '</div>';

                break;
            case 'lead_passenger':

                html = '<div class="etoOuterContainer" id="eto'+ name +'Container">' +
                    '<label class="etoLabel" for="eto'+ name +'" id="eto'+ name +'Label"></label>' +
                    '<div class="etoInnerContainer">' +
                    '<div class="checkbox">' +
                    '<label for="eto'+ name +'0"><input type="checkbox" name="eto'+ name +'" id="eto'+ name +'0" value="0"><span class="cr"><i class="cr-icon ion-ios-checkmark-empty"></i></span>' + etoLang('LEAD_PASSENGER_NO') + '</label>' +
                    '</div>';
                // '<input type="radio" name="eto'+ name +'" id="eto'+ name +'1" value="1" checked="checked" />' +
                // '<label for="eto'+ name +'1">' + etoLang('LEAD_PASSENGER_YES') + '</label>' +
                // '<div class="clear"></div>' +
                // '<input type="radio" name="eto'+ name +'" id="eto'+ name +'0" value="0" />' +
                // '<label for="eto'+ name +'0">' + etoLang('LEAD_PASSENGER_NO') + '</label>';
                if (info) {
                    html += '<small>' + info + '</small>';
                }
                html += '<div class="clear"></div>' +
                    '</div>' +
                    '<div class="clear"></div>' +
                    '</div>';

            break;
            case 'name_title':

                var optionsV2 = '';
                if (etoLang('bookingField_Mr') != ' ') {
                    optionsV2 += '<option value="' + etoLang('bookingField_Mr') + '">' + etoLang('bookingField_Mr') + '</option>';
                }
                if (etoLang('bookingField_Mrs') != ' ') {
                    optionsV2 += '<option value="' + etoLang('bookingField_Mrs') + '">' + etoLang('bookingField_Mrs') + '</option>';
                }
                if (etoLang('bookingField_Miss') != ' ') {
                    optionsV2 += '<option value="' + etoLang('bookingField_Miss') + '">' + etoLang('bookingField_Miss') + '</option>';
                }
                if (etoLang('bookingField_Ms') != ' ') {
                    optionsV2 += '<option value="' + etoLang('bookingField_Ms') + '">' + etoLang('bookingField_Ms') + '</option>';
                }
                if (etoLang('bookingField_Dr') != ' ') {
                    optionsV2 += '<option value="' + etoLang('bookingField_Dr') + '">' + etoLang('bookingField_Dr') + '</option>';
                }
                if (etoLang('bookingField_Sir') != ' ') {
                    optionsV2 += '<option value="' + etoLang('bookingField_Sir') + '">' + etoLang('bookingField_Sir') + '</option>';
                }

                html = '<div class="etoOuterContainer" id="eto'+ name +'Container">' +
                    '<div class="etoInnerContainer eto-v2-field eto-v2-field-has-value">' +
                    '<span class="eto-v2-field-label">' + label + '</span>' +
                    '<select name="eto'+ name +'" id="eto'+ name +'" class="form-control">';
                html += optionsV2;
                html += '</select>';
                if (info) {
                    html += '<small>' + info + '</small>';
                }
                html += '<div class="clear"></div>' +
                    '</div>' +
                    '<div class="clear"></div>' +
                    '</div>';

            break;
            case 'departments':

                html = '<div class="etoOuterContainer" id="eto'+ name +'Container">' +
                    '<div class="etoInnerContainer eto-v2-field eto-v2-field-has-value">' +
                    '<span class="eto-v2-field-label">' + label + '</span>' +
                    '<select name="eto'+ name +'" id="eto'+ name +'" class="form-control" style="width: 100%;">';
                if (etoData.userDepartments && etoData.userDepartments.length > 0) {
                    html += '<option value="">' + etoLang('SELECT') + '</option>';
                    $.each(etoData.userDepartments, function(key, value) {
                        html += '<option value="' + value + '">' + value + '</option>';
                    });
                } else {
                    html += '<option value="">' + etoLang('SELECT') + '</option>';
                }
                html += '</select>' +
                    '<div class="clear"></div>' +
                    '</div>' +
                    '<div class="clear"></div>' +
                    '</div>';

            break;
            case 'map':

                html = '<div class="etoOuterContainer" id="eto'+ name +'Container">' +
                    '<label class="etoLabel" for="eto'+ name +'" id="eto'+ name +'Label"></label>' +
                    '<div class="etoInnerContainer">';

                if (etoData.request.config.booking_summary_display_mode == 'separated' || parseInt(etoData.request.config.booking_map_enable) == 0) {
                    html += '<div class="eto-v2-trip '+ (parseInt(etoData.request.config.booking_map_enable) == 0 || parseInt(etoData.request.config.booking_directions_enable) == 0 ? 'eto-v2-trip-fullwidth' : '') +'">';
                      html += '<div class="eto-v2-trip-summary">';
                        html += '<div id="eto'+ name +'Directions"></div>';
                      html += '</div>';
                      html += '<div class="eto-v2-trip-map">';
                        html += '<div id="eto'+ name +'"></div>';
                      html += '</div>';
                    html += '</div>';

                }
                else {
                    html += '<div class="etoMapStyle2">';
                      html += '<div id="eto'+ name +'" class="etoMapStyle2Map"></div>';
                      html += '<button type="button" class="etoMapStyle2Button"><i class="fa fa-times"></i></button>';
                      html += '<div id="eto'+ name +'Directions" class="etoMapStyle2Directions"></div>';
                    html += '</div>';
                }

                html += '<div class="clear"></div>' +
                    '</div>' +
                    '<div class="clear"></div>' +
                    '</div>';

                break;
            case 'label':

                html = '<div class="etoOuterContainer" id="eto'+ name +'Container">' +
                    '<label class="etoLabel" for="eto'+ name +'" id="eto'+ name +'Label">' + label + '</label>' +
                    '<div class="etoInnerContainer">' + info + '</div>' +
                    '<div class="clear"></div>' +
                    '</div>';

                break;
            case 'waiting_time':

                html = '<div class="etoOuterContainer" id="eto'+ name +'Container">' +
                    '<div class="etoInnerContainer eto-v2-field eto-v2-field-has-value">' +
                    '<span class="eto-v2-field-label">' + label + '</span>' +
                    '<select name="eto'+ name +'" id="eto'+ name +'" class="form-control">';
                html += '<option value="">' + etoLang('SELECT') + '</option>';
                for (var i = 10; i <= 120; i += 5) {
                    html += '<option value="' + i + '">' + i + ' min</option>';
                }
                html += '</select>';
                if (info) {
                    html += '<small>' + info + '</small>';
                }
                html += '<div class="clear"></div>' +
                    '</div>' +
                    '<div class="clear"></div>' +
                    '</div>';

                break;
            case 'time':

                html = '<div class="etoOuterContainer" id="eto'+ name +'Container">' +
                    '<label class="etoLabel" for="eto'+ name +'" id="eto'+ name +'Label">' + label + '</label>' +
                    '<div class="etoInnerContainer">' +
                    '<select name="eto'+ name +'" id="eto'+ name +'" class="form-control">';
                html += '<option value="">' + etoLang('SELECT') + '</option>';
                for (i = 0; i <= 23; i++) {
                    for (j = 0; j < 60; j += 5) {
                        var option_val = ((i < 10) ? '0'+ i : i) +':'+ ((j < 10) ? '0'+ j : j);
                        html += '<option value="'+ option_val +'">'+ option_val +'</option>';
                    }
                }
                html += '</select>';
                if (info) {
                    html += '<small>' + info + '</small>';
                }
                html += '<div class="clear"></div>' +
                    '</div>' +
                    '<div class="clear"></div>' +
                    '</div>';

                break;
            case 'services':

                if (etoData.request.config.booking_service_display_mode == 'tabs') {
                    html = '<div class="etoOuterContainer eto-v2-services-tabs clearfix" id="eto'+ name +'Container">';
                      if (etoData.request.services && etoData.request.services.length > 0) {
                          $.each(etoData.request.services, function(key, value) {
                              html += '<div class="radio">\
                                <label for="eto'+ name +'-'+ key +'">\
                                  <input type="radio" name="eto'+ name +'" id="eto'+ name +'-'+ key +'" value="' + value.id + '">\
                                  <span class="cr"><i class="cr-icon ion-record"></i></span>\
                                  <span class="cr-val">' + value.name + '</span>\
                                </label>\
                              </div>';
                          });
                      } else {
                          html += '<div class="radio">\
                            <label for="eto'+ name +'">\
                              <input type="radio" name="eto'+ name +'" id="eto'+ name +'" value="0">\
                              <span class="cr"><i class="cr-icon ion-record"></i></span>\
                              <span class="cr-val">' + etoLang('bookingField_ServicesSelect') + '</span>\
                            </label>\
                          </div>';
                      }
                    html += '</div>';
                }
                else {
                    html = '<div class="etoOuterContainer" id="eto'+ name +'Container">' +
                        '<label class="etoLabel" for="eto'+ name +'" id="eto'+ name +'Label">' + label + '</label>' +
                        '<div class="etoInnerContainer">' +
                        '<div class="input-group">' +
                        '<select name="eto'+ name +'" id="eto'+ name +'" class="form-control" style="width: 100%;">';
                    if (etoData.request.services && etoData.request.services.length > 0) {
                        // html += '<option value="0">' + etoLang('bookingField_ServicesSelect') + '</option>';
                        $.each(etoData.request.services, function(key, value) {
                            html += '<option value="' + value.id + '">' + value.name + '</option>';
                        });
                    } else {
                        html += '<option value="0">' + etoLang('bookingField_ServicesSelect') + '</option>';
                    }
                    html += '</select>' +
                        '<span class="input-group-addon"><span class="ion-ios-search"></span></span>' +
                        '</div>' +
                        '</div>' +
                        '<div class="clear"></div>' +
                        '</div>';
                }

                break;
            case 'services_duration':

                html = '<div class="etoOuterContainer" id="eto'+ name +'Container">' +
                    '<label class="etoLabel" for="eto'+ name +'" id="eto'+ name +'Label">' + label + '</label>' +
                    '<div class="etoInnerContainer">' +
                    '<div class="input-group">' +
                    '<select name="eto'+ name +'" id="eto'+ name +'" class="form-control" style="width: 100%;">';
                html += '<option value="0">' + etoLang('bookingField_ServicesDurationSelect') + '</option>';
                for (var i = etoData.serviceParams.duration_min; i <= etoData.serviceParams.duration_max; i++) {
                    html += '<option value="' + i + '">' + i + 'h</option>';
                }
                html += '</select>' +
                    '<span class="input-group-addon"><span class="ion-ios-stopwatch-outline"></span></span>' +
                    '</div>' +
                    '<div class="clear"></div>' +
                    '</div>' +
                    '<div class="clear"></div>' +
                    '</div>';

                break;
            case 'items':

                if (etoData.request.config.booking_items && etoData.request.config.booking_items.length > 0) {

                    html = '<div class="etoOuterContainer" id="eto'+ name +'Container">' +
                        '<label class="etoLabel" for="eto'+ name +'" id="eto'+ name +'Label">' + label + '</label>' +
                        '<div class="etoInnerContainer">';

                    $.each(etoData.request.config.booking_items, function(key, value) {
                        if (value.name && value.amount > 0) {
                            var fieldlabel = value.name;
                            var doquote = "";
                            if (parseFloat(value.value) > 0) {
                                fieldlabel += ' (' + etoData.request.config.currency_symbol + value.value + etoData.request.config.currency_code + ')';
                                doquote = "doquote";
                            }

                            var styleCheckbox = '';
                            if (key + 1 < etoData.request.config.booking_items.length) {
                                styleCheckbox = 'margin-bottom:5px;';
                            }

                            var onclick = '';
                            var select = '';

                            if( value.type == 'input' || value.type == 'address' ) {
                                // Scheduled service only
                                onclick = 'onclick="$(\'#eto'+ name +'_' + value.id + '_input_container\').toggle();"';

                                var placeholder = value.type == 'address' ? 'placeholder="'+ etoLang('userField_Address') +'"' : '';

                                select = '<span id="eto'+ name +'_' + value.id + '_input_container" style="display:none;">';
                                    select += '<input name="eto'+ name +'[][\'custom\']" id="eto'+ name +'_' + value.id + '_custom" class="form-control address-auto-complete-input" autocomplete="off" ' + doquote + ' '+ placeholder +' style="width:auto; height:24px; padding:2px 4px; line-height:14px; font-size:14px; margin-left:10px; display:inline-block;" />';
                                select += '</span>';
                            }
                            else if( value.type == 'custom' ) {
                                if( value.custom && value.custom.length > 0 ) {
                                    select = '<span id="eto'+ name +'_' + value.id + '_custom_container">';
                                          select += '<select name="eto'+ name +'[][\'custom\']" id="eto'+ name +'_' + value.id + '_custom" class="form-control" ' + doquote + ' style="width:auto; height:24px; padding:2px 4px; line-height:14px; font-size:14px; margin-left:10px; display:inline-block;">';
                                          $.each(value.custom, function(kC, vC) {
                                              select += '<option value="' + vC + '">' + vC + '</option>';
                                          });
                                          select += '</select>';
                                      select += '</span>';
                                }
                            }
                            else {
                                if (value.amount > 1) {
                                    onclick = 'onclick="$(\'#eto'+ name +'_' + value.id + '_amount_container\').toggle();"';
                                }

                                select = '<span id="eto'+ name +'_' + value.id + '_amount_container" style="display:none;">';
                                      select += '<select name="eto'+ name +'[][\'amount\']" id="eto'+ name +'_' + value.id + '_amount" class="form-control" ' + doquote + ' style="width:auto; height:24px; padding:2px 4px; line-height:14px; font-size:14px; margin-left:10px; display:inline-block;">';
                                      for (var i = 1; i <= value.amount; i++) {
                                          select += '<option value="' + i + '">' + i + '</option>';
                                      }
                                      select += '</select>';
                                  select += '</span>';
                            }

                            html += '<input type="hidden" name="eto'+ name +'[][\'type\']" id="eto'+ name +'_' + value.id + '_type" value="' + value.type + '" />';

                            html += '<div class="checkbox item_field_type item_field_type_'+ value.type +'" style="overflow:hidden; margin-top:0; ' + styleCheckbox + '">\
                                        <label for="eto'+ name +'_' + value.id + '">\
                                            <input type="checkbox" name="eto'+ name +'[][\'active\']" id="eto'+ name +'_' + value.id + '" value="' + value.id + '" ' + onclick + ' ' + doquote + ' />\
                                            <span class="cr"><i class="cr-icon ion-ios-checkmark-empty"></i></span>' + fieldlabel + '\
                                        </label>\
                                        ' + select + '\
                                    </div>';
                        }
                    });

                    if (info) {
                        html += '<small>' + info + '</small>';
                    }
                    html += '<div class="clear"></div>' +
                        '</div>' +
                        '<div class="clear"></div>' +
                        '</div>';

                }

                break;
        }

        return html;
    }

}
