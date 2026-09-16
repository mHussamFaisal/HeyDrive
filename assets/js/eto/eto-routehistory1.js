

ETO.Routehistory1 = function() {
    var etoFn = {};

    etoFn.config = {
        init: ['google', 'icons'],
        lang: ['user', 'booking']
    };

    etoFn.hasModal = false;
    etoFn.panelContainer = {};
    etoFn.map = {};
    etoFn.flightPath = {};
    etoFn.mapPoly = {};
    etoFn.mapPositionMarker = {};
    etoFn.mapPath = {};
    etoFn.mapContainer = {};
    etoFn.bookingId = 0;
    etoFn.lastCoordinateTimestamp = 0;
    etoFn.wayPoints = [];
    etoFn.tracking = {};
    etoFn.startTracking = '';
    etoFn.lastStatus = '';
    etoFn.statuses = {};
    etoFn.isRequest = false;

    etoFn.init = function(config) {
        if(typeof config != 'undefined') {
            var typeConfig = typeof config.type != 'undefined' ? config.type : 'tracking';

            ETO.extendConfig(this, config, typeConfig);

            if (typeof etoFn.config.type == 'undefined') {
                if (typeof ETO.current_user != 'undefined' && typeof ETO.current_user.role != 'undefined') {
                    etoFn.config.type = ETO.current_user.role;
                } else {
                    etoFn.config.type = 'customer';
                }
            }
        }

        etoFn.hasModal = $('.eto-modal-booking-tracking').length > 0;
        etoFn.panelContainer = etoFn.hasModal ? $('.eto-modal-booking-tracking') : $('.eto-tracking-panel');
        etoFn.mapContainer = etoFn.panelContainer.find('.eto-booking-tracking-map')[0];

        $('body')
            .on('click', '.eto-btn-booking-tracking', function (e) {
                etoFn.initTraking($(this));
            })
            .on('click', '.status-label.after-map', function() {
                var lat = $(this).data('eto-lat'),
                    lng = $(this).data('eto-lng');

                if (typeof lat != 'undefined' && lat != null) {
                    etoFn.map.setCenter(new google.maps.LatLng(parseFloat(lat), parseFloat(lng)));
                    etoFn.map.setZoom(15);
                } else {
                    ETO.swalWithBootstrapButtons({
                        title: ETO.lang.booking.tracking.no_status_coordinates,
                        type: 'warning',
                    })
                }
            });

        etoFn.panelContainer.on('hidden.bs.modal', function () {
            etoFn.lastCoordinateTimestamp = 0;
            etoFn.statuses = {};
            etoFn.initMap();
            $('.eto-statuses-list').remove();
        });

        if(typeof config != 'undefined' && etoFn.config.type == 'customer') {
            $(".eto-tracking-panel").show();
            etoFn.initTraking($('.eto-btn-booking-tracking'));
        }

        if(typeof config != 'undefined' && etoFn.config.type == 'passenger') {
            etoFn.initTraking($('.eto-btn-booking-tracking'));
        }
    };

    etoFn.initTraking = function(el) {
        if (!etoFn.hasModal && etoFn.panelContainer.is( ":hidden" ) && Object.keys(etoFn.tracking).length < 0) {
            return;
        }

        if (etoFn.isRequest === false) {
            etoFn.initMap();
            $('.eto-no-tracking').closest('small').remove();
            $('.eto-no-tracking').remove();
            $('.eto-statuses-list').remove();
            var title = typeof el.data('originalTitle') != "undefined" ? el.data('originalTitle') : el.data('title');

            etoFn.bookingId = el.data('etoId');

            if (etoFn.hasModal) {
                if (typeof title != 'undefined' && title.toString().length > 0) {
                    etoFn.panelContainer.find('.modal-title').html(title);
                }
                etoFn.panelContainer.modal('show');
            }

            etoFn.isRequest = true;
            ETO.ajax('booking-tracking/' + etoFn.bookingId, {
                data: {
                    type: etoFn.config.type
                },
                async: true,
                success: function (booking) {
                    etoFn.mapPath = new google.maps.MVCArray();

                    etoFn.setStatuses(booking);
                    etoFn.setCoordinates(booking);

                    if (typeof etoFn.mapPath.length != 'undefined' && etoFn.mapPath.length === 0) {
                        if ((typeof ETO.current_user == 'undefined' || ETO.current_user.role != 'supplier') && etoFn.config.type != 'passenger') {
                            var modal = $(etoFn.mapContainer).closest('.modal');
                            var message = '<span style="padding-top:10px; display:inline-block;" class="eto-no-tracking clearfix">No tracking available</span>';

                            if (modal.length > 0) {
                                modal.find('.modal-title').append('<small style="display:inline-block; margin-left:14px;">' + message + '</small>');
                            } else {
                                $(etoFn.mapContainer).after(message);
                            }
                        }
                    }

                    etoFn.drawRoute(booking);
                },
                complete: function () {
                    etoFn.isRequest = false;
                }
            });
        }
    };

    etoFn.setCoordinates = function(booking)
    {
        if (typeof booking.coordinates != 'undefined'
            && (booking.coordinates.length > 0 || Object.keys(booking.coordinates).length > 0)
            && (booking.new === true || booking.new == 'end')
        ) {
            var LatLng = {};
            $.each(booking.coordinates, function (driverId, coordinates) {
                $.each(coordinates, function (key, point) {
                    LatLng = etoFn.getLatLng(point.lat, point.lng);
                    if (typeof point.lat != "undefined") {
                        etoFn.setLatLng(LatLng);
                    }
                });

                if (booking.new != 'end') {
                    etoFn.lastCoordinateTimestamp = booking.lastCoordinateTime[driverId];
                } else {
                    clearInterval(etoFn.tracking);
                }
            });

            etoFn.setCoordinatesToMap(LatLng);
        }
    };

    etoFn.initMap = function()
    {
        clearInterval(etoFn.tracking);
        etoFn.lastStatus = '';
        etoFn.map = new google.maps.Map(etoFn.mapContainer, {
            zoom: parseInt(ETO.config.google.booking_map_zoom),
            mapTypeId: 'roadmap',
            // draggable: parseInt(ETO.config.google.booking_map_draggable),
            // zoomControl: parseInt(ETO.config.google.booking_map_zoomcontrol),
            // scrollwheel: parseInt(ETO.config.google.booking_map_scrollwheel),
        });

        etoFn.mapPoly = new google.maps.Polyline({
            map: etoFn.map,
            geodesic: true,
            strokeColor: '#0822ff',
            strokeOpacity: 1.0,
            strokeWeight: 3
        });

        etoFn.mapPositionMarker = new google.maps.Marker({
            map: etoFn.map,
            icon: ETO.config.appPath +'/assets/images/icons/pin-blue.png'
        });
    };

    etoFn.drawRoute = function(booking)
    {
        var directionsDisplay = new google.maps.DirectionsRenderer({
                polylineOptions: {
                    geodesic: true,
                    strokeColor: "#000",
                    strokeOpacity: 0.4,
                },
                suppressMarkers: true,
            }),
            directionsService = new google.maps.DirectionsService(),
            request = {
                origin: booking.origin,
                destination: booking.destination,
                provideRouteAlternatives: true,
                unitSystem: google.maps.UnitSystem.IMPERIAL,
                travelMode: google.maps.TravelMode.DRIVING
            },
            tempWaypoints = [];

        if (ETO.config.google.google_region_code) {
            request.region = ETO.config.google.google_region_code;
        } else {
            request.region = 'gb';
        }

        if (ETO.config.google.quote_avoid_highways > 0) {
            request.avoidHighways = true;
        }

        if (ETO.config.google.quote_avoid_tolls > 0) {
            request.avoidTolls = true;
        }

        if (ETO.config.google.quote_avoid_ferries > 0) {
            request.avoidFerries = true;
        }

        $.each(booking.waypoints, function(key, value) {
            tempWaypoints.push({
                location: value,
            });
        });

        if (tempWaypoints.length > 0) {
            request.waypoints = tempWaypoints;
            request.optimizeWaypoints = true;
        }

        etoFn.mapPoly.setPath([]);
        etoFn.setCenter(booking.origin);
        directionsDisplay.setMap(etoFn.map);
        directionsService.route(request, function(response, status) {
            if (status == google.maps.DirectionsStatus.OK) {
                google.maps.event.trigger(etoFn.mapContainer, 'resize');
                directionsDisplay.setDirections(response);
                etoFn.setDriverTracking();
                if (typeof ETO.current_user == 'undefined'
                    || (typeof ETO.current_user == 'object' && ETO.current_user.role != 'driver')
                ) {
                    etoFn.updateTracking();
                }

                // console.log(response);

                var labels = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                var labelIndex = 0;
                var routeDuration = 0;
                var routeDistance = 0;

                function secondsToTime(s) {
                    var duration = s * 1000;
                    var days = Math.floor(duration / (1000 * 60 * 60 * 24));
                    var hours = Math.floor((duration % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    var minutes = Math.floor((duration % (1000 * 60 * 60)) / (1000 * 60));
                    var seconds = Math.floor((duration % (1000 * 60)) / 1000);
                    var text = '';

                    if (days > 0) {
                        text += (text ? ' ' : '') + days +' day';
                    }
                    if (hours > 0) {
                        text += (text ? ' ' : '') + hours +' h';
                    }
                    if (minutes > 0) {
                        text += (text ? ' ' : '') + minutes +' min';
                    }
                    // if (seconds > 0) {
                    //   text += (text ? ' ' : '') + seconds +' s';
                    // }

                    // console.log(days, hours, minutes, seconds);
                    return text;
                }

                function createMarker(leg, type) {
                    if (type != 'start') {
                        routeDuration += leg.duration.value;
                        routeDistance += leg.distance.value;
                    }

                    var duration = routeDuration / 60;
                    var distance = Math.round((routeDistance / 1000) * 100) / 100;
                    var position = type == 'start' ? leg.start_location : leg.end_location;
                    var text = secondsToTime(routeDuration);
                    var label = labels[labelIndex++ % labels.length];

                    var marker = new google.maps.Marker({
                        position: position,
                        map: etoFn.map,
                        label: {
                            text: label,
                            color: '#fff'
                        }
                    });

                    if (type != 'start' && text) {
                        var infowindow = new google.maps.InfoWindow({
                            content: text
                        });

                        infowindow.open(etoFn.map, marker);

                        marker.addListener('click', function() {
                            if (!infowindow.getMap()) {
                                infowindow.open(etoFn.map, marker);
                            } else {
                                infowindow.close(etoFn.map, marker);
                            }
                        });
                    }
                }

                if (response.routes[0].legs.length > 0) {
                    createMarker(response.routes[0].legs[0], 'start');

                    for (var i = 0; i < response.routes[0].legs.length; i++) {
                        createMarker(response.routes[0].legs[i], 'end');
                    }
                }
            }
            else {
                window.alert('Directions request failed due to ' + status);
            }
        });
    };

    etoFn.setDriverTracking = function()
    {
        etoFn.mapPoly.setPath(etoFn.mapPath);
    };

    etoFn.updateTracking = function()
    {
        if (etoFn.lastCoordinateTimestamp != '0') {
            etoFn.tracking = setInterval(function() {
                ETO.ajax('booking-tracking/' + etoFn.bookingId + '/last/' + etoFn.lastCoordinateTimestamp, {
                    data: {
                        type: etoFn.config.type
                    },
                    async: false,
                    success: function (result) {
                        etoFn.setStatuses(result);

                        if (result.new === true || result.new == 'end') {
                            $.each(result.coordinates, function (driverId, coordinates) {
                                $.each(coordinates, function (key, point) {
                                    if (typeof point.lat != "undefined") {
                                        var LatLng = etoFn.getLatLng(point.lat, point.lng);
                                        etoFn.setLatLng(LatLng);
                                        etoFn.setCoordinatesToMap(LatLng);
                                    }
                                });

                                if (result.new != 'end') {
                                    etoFn.lastCoordinateTimestamp = result.lastCoordinateTime[driverId];
                                } else {
                                    clearInterval(etoFn.tracking);
                                }
                            });
                        }
                    },
                    error: function () {
                        return false;
                    }
                });
            }, 10000);
        }
    };

    etoFn.animateTracking = function()
    {
        for (var index = 0; index < etoFn.mapPath.length; index++) {
            setTimeout(function(offset) {
                etoFn.setCoordinatesToMap(etoFn.mapPath.getAt(offset));
            }, index * 30, index);
        }
    };

    etoFn.setCenter = function(address)
    {
        if (typeof address == 'object' ) {
            etoFn.map.setCenter(address);
        } else {
            var geocoder = new google.maps.Geocoder();

            geocoder.geocode({
                'address': address
            }, function (results, status) {
                if (status === google.maps.GeocoderStatus.OK) {
                    etoFn.map.setCenter(results[0].geometry.location);
                    etoFn.map.setZoom(15);
                }
            });
        }
    };

    etoFn.setCoordinatesToMap = function(LatLng)
    {
        etoFn.mapPoly.getPath().push(LatLng);
        etoFn.mapPositionMarker.setPosition(LatLng);
        etoFn.map.panTo(LatLng);
    };

    etoFn.setStatuses = function(booking) {
        if (booking.statuses.length > 0 || Object.keys(booking.statuses).length > 0) {
            $.each(booking.statuses, function(time, status) {
                if (typeof etoFn.statuses[time] == 'undefined') {
                    etoFn.statuses[time] = status;

                    if (status.lat !== null && status.status != etoFn.lastStatus) {
                        etoFn.setNewStatus(status.status, etoFn.getLatLng(status.lat, status.lng));
                    }

                    if ($('.eto-statuses-list').length === 0) {
                        $(etoFn.mapContainer).after('<div class="eto-statuses-list clearfix"></div>');
                    }

                    if (typeof ETO.config.bookingStatusColor[status.status] != 'undefined') {
                        var dateFormated = ETO.convertDate(ETO.config.date_format, time, true),
                            timeformated = ETO.convertTime(ETO.config.time_format, time, true),
                            color = ETO.config.bookingStatusColor[status.status].color,
                            statusName = ETO.config.bookingStatusColor[status.status].name,
                            driverName = typeof booking.drivers != "undefined"
                                    && typeof booking.drivers[status.user_id] != "undefined"
                                ? ' - ' + booking.drivers[status.user_id]
                                : '',
                            statusHtml = '<span class="label status-label status-label-' + status.status + ' after-map"' +
                                ' data-eto-status="'+status.status+'"' +
                                ' data-eto-lat="'+status.lat+'"' +
                                ' data-eto-lng="'+status.lng+'"' +
                                ' title="'
                                + statusName + ' ' + dateFormated + ' ' + timeformated + driverName
                                + '" style="background: ' + color + ';">' + statusName + ' ' + timeformated + '</span>';

                        $('.eto-statuses-list').append(statusHtml);
                    }
                }
            });
        }
    };

    etoFn.setNewStatus = function(status, LatLng)
    {
        if (typeof ETO.config.bookingStatusColor[status] != 'undefined') {
            etoFn.lastStatus = status;

            var color = ETO.config.bookingStatusColor[status].color,
                marker = new MarkerWithLabel({
                    animation: google.maps.Animation.DROP,
                    position: LatLng,
                    labelContent: '<span class="label status-label status-label-' + status + '" style="background: ' + color + '; ">' + ETO.config.bookingStatusColor[status].name + '</span>',
                    labelClass: "marker-label",
                    labelInBackground: false,
                    icon: {
                        path: google.maps.SymbolPath.CIRCLE,
                        scale: 4,
                        fillColor: color,
                        fillOpacity: 1.0,
                        strokeWeight: 5,
                    },
                });

            marker.setMap(etoFn.map);
        }
    };

    etoFn.setLatLng = function (LatLng)
    {
        etoFn.mapPath.push(LatLng);
    };

    etoFn.getLatLng = function (lat, lng)
    {
        lat = parseFloat(lat);
        lng = parseFloat(lng);

        if (!isNaN(lat) && !isNaN(lng)) {
            return new google.maps.LatLng({
                lat: lat,
                lng: lng
            });
        }
        return null;
    };

    return etoFn;
}();
