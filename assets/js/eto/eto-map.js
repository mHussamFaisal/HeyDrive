

ETO.Map = function() {
    var etoFn = {};

    etoFn.config = {
        config: ['page', 'icons', 'routes', 'config_site'],
        lang: ['map', 'user'],
        mapInterval: 20000,
        location: {
            lat: 51.509865,
            lng: -0.118092,
            formatted_address: 'London, UK',
        },
        zoom: 10,
        typeMap: 'roadmap',
        ajaxReady: true, // XHR is avaleble
    };

    etoFn.map = {};
    etoFn.settings = {};
    etoFn.markers = []; // markers rof map,
    etoFn.drivers = {}; // driver list
    etoFn.infoWindow = {};
    etoFn.mapUrl = '';
    etoFn.trafficLayer = new google.maps.TrafficLayer();
    etoFn.styles = {
        default: null,
        hide: [
            {
                featureType: 'poi.business',
                stylers: [{visibility: 'off'}]
            },
            {
                featureType: 'transit',
                elementType: 'labels.icon',
                stylers: [{visibility: 'off'}]
            }
        ]
    };

    etoFn.init = function(config) {
        ETO.extendConfig(this, config, 'map');

        // etoFn.settings = {user: {eto_map: ETO.current_user.settings.eto_map}, subscription: ETO.config.subscription};
        etoFn.settings = {user: {eto_map: ETO.current_user.settings.eto_map}, subscription: {eto: {interval: ETO.settings('eto.interval')}}};

        etoFn.initMap();

        var selected = {
            id: 0,
            text: '',
            title: '',
            selected: true,
        },
        configDriver = ETO.Form.select2AjaxUser('get-config/driver-search', selected, '', 'Find driver'),
        defaultConfig = $.extend(true, {}, ETO.Form.config.plugins.select2),
        selec2tConfig = $.extend(true, defaultConfig, configDriver);

        selec2tConfig.width = '150';
        selec2tConfig.allowClear = true;

        $('select.eto-driver-map-search').select2(selec2tConfig);

        $('body').on('change', 'select.eto-driver-map-search', function() {
            $('.driver-event[data-eto-driver-id="'+$(this).val()+'"]').click();
        })
        .on('click', '.driver-event', function() {
            var driverId = $(this).attr('data-eto-driver-id'),
                driver = {};

            for (var i in ETO.Map.drivers) {
                if (ETO.Map.drivers[i].id == driverId) {
                    driver = ETO.Map.drivers[i];
                }
            }

            if(driver.name) {
                if (driver.lat != null) {
                    etoFn.map.setCenter(new google.maps.LatLng(driver.lat, driver.lng));
                } else {
                    var parameters = {
                        name: driver.name,
                        filters: {
                            'filter-driver-name': [driver.id],
                        }
                    };

                    if (ETO.Dispatch.config.layout == 'bootstrap') {
                        var tabHeader = $('.dispatch-bookings').find('.nav-tabs').find('.active'),
                            tabBody = $('.dispatch-bookings').find('.tab-content').find('.active');

                        tabHeader.removeClass('active');
                        tabBody.removeClass('active');

                        ETO.Booking.createTable('driver-id-' + driver.id, parameters, 'active');
                    } else if (ETO.Dispatch.config.layout == 'goldenlayout') {
                        ETO.Dispatch.newTabSet('datatable-bookings', 'driver-id', driver.id, driver.name, parameters);
                    }
                }
            }
        })
        .on('change', '.eto-modal-map-settings input:not(#search)', function(e) {
            var values = ETO.parseSettings($(this));

            if (Object.keys(values).length > 0) {
                ETO.saveSettings(values);
            }
        })
        .on('change', '.eto-modal-map-settings input#show_inactive_drivers', function(e) {
            etoFn.getMarkers();

            if ($(this).attr('checked') == 'checked') {
                etoFn.settings.user.eto_map.view.show_inactive_drivers = true;
            }
            else {
                etoFn.settings.user.eto_map.view.show_inactive_drivers = false;
            }
        })
        .on('change', '.eto-modal-map-settings input[name="type"]', function() {
            var typeId = $(this).closest('.form-group').find(':checked').val();

            etoFn.map.setMapTypeId(typeId);
            etoFn.settings.user.eto_map.view.type = typeId;
        })
        .on('change', '.eto-modal-map-settings input#traffic', function() {
            // https://developers.google.com/maps/documentation/javascript/examples/layer-traffic
            if ($(this).attr('checked') == "checked") {
                etoFn.trafficLayer.setMap(etoFn.map);
                etoFn.settings.user.eto_map.view.traffic = true;
            }
            else {
                etoFn.trafficLayer.setMap(null);
                etoFn.settings.user.eto_map.view.traffic = false;
            }
        })
        .on('change', '.eto-modal-map-settings input#poi_style', function() {
            if ($(this).attr('checked') == "checked") {
                etoFn.map.setOptions({styles: etoFn.styles['default']});
                etoFn.settings.user.eto_map.view.poi_style = true;
            }
            else {
                etoFn.map.setOptions({styles: etoFn.styles['hide']});
                etoFn.settings.user.eto_map.view.poi_style = false;
            }
        })
        .on('change', '.eto-modal-map-settings input#mapRefresh', function() {
            etoFn.settings.user.eto_map.interval.refresh = $(this).val();
        })
        .on('change', '.eto-modal-map-settings input#driverRefresh', function() {
            // ETO.settings('eto.interval.driver_refresh', 20) = $(this).val();
            etoFn.settings.subscription.eto.interval.driver_refresh = $(this).val();
        });

        $('#search').on('change', function () {
            // if(etoFn.mapUrl.trim() == etoFn.map.mapUrl.trim()) {
                var geocoder = new google.maps.Geocoder();
                geocoder.geocode({
                    address: $(this).val()
                }, function (results, status) {
                    if (status == google.maps.GeocoderStatus.OK) {
                        etoFn.map.setCenter(results[0].geometry.location);
                        etoFn.map.setZoom(14);

                        etoFn.settings.user.eto_map.view.search_lat = results[0].geometry.location.lat();
                        etoFn.settings.user.eto_map.view.search_lng = results[0].geometry.location.lng();
                        etoFn.settings.user.eto_map.view.search = results[0].formatted_address;
                        $('#search_lat').val(results[0].geometry.location.lat());
                        $('#search_lng').val(results[0].geometry.location.lng());
                        etoFn.saveSearchSettings();
                    }
                });
            // }
            etoFn.mapUrl = etoFn.map.mapUrl;
        });

        etoFn.initSettings();

        // window.onresize = function() {
        //     var currCenter = etoFn.map.getCenter();
        //     google.maps.event.trigger(etoFn.map, 'resize');
        //     etoFn.map.setCenter(currCenter);
        // };
    };

    etoFn.saveSearchSettings = function() {
        var inputs = $('.eto-modal-map-settings').find('input.eto-search, input.eto-search_lat, input.eto-search_lng'),
            values = ETO.parseSettings(inputs);

        if (Object.keys(values).length > 0) {
            ETO.saveSettings(values);
        }
    };

    etoFn.initSettings = function() {
        ETO.configFormUpdate($('.eto-modal-map-settings').find('input'), etoFn.settings);

        etoFn.getMarkers();

        if ($('.eto-modal-map-settings input#show_inactive_drivers').attr('checked') == 'checked') {
            etoFn.settings.user.eto_map.view.show_inactive_drivers = true;
        }
        else {
            etoFn.settings.user.eto_map.view.show_inactive_drivers = false;
        }

        var typeId = $('.eto-modal-map-settings input[name="type"]:checked').val();

        etoFn.map.setMapTypeId(typeId);
        etoFn.settings.user.eto_map.view.type = typeId;

        if ($('.eto-modal-map-settings input#traffic').attr('checked') == "checked") {
            etoFn.trafficLayer.setMap(etoFn.map);
            etoFn.settings.user.eto_map.view.traffic = true;
        }
        else {
            etoFn.trafficLayer.setMap(null);
            etoFn.settings.user.eto_map.view.traffic = false;
        }

        if ($('.eto-modal-map-settings input#poi_style').attr('checked') == "checked") {
            etoFn.map.setOptions({styles: etoFn.styles['default']});
            etoFn.settings.user.eto_map.view.poi_style = true;
        }
        else {
            etoFn.map.setOptions({styles: etoFn.styles['hide']});
            etoFn.settings.user.eto_map.view.poi_style = false;
        }
    };

    etoFn.driverList = function() {
        if (this.drivers.length > 0 ){
            $('.empty-drivers').addClass('hidden');
        }
        else {
            $('.empty-drivers').removeClass('hidden');
        }

        this.drivers.forEach(function(user) {
            if ($('#driver-button-id-'+user.id).length > 0) {$('#driver-button-id-'+user.id).remove();}
            $('.dispatch-drivers-list span.'+user.availability).append('<div class="driver-event marker-driver-label '+user.availability+'" onclick="" id="driver-button-id-'+user.id+'" data-eto-driver-id="'+user.id+'">'+user.name+'</div>');
        });
    };

    etoFn.bindInfoWindow = function(marker, html) {
        google.maps.event.addListener(marker, 'click', function() {
            etoFn.infoWindow.setContent(html);
            etoFn.infoWindow.open(this.map, marker);
        });
    };

    etoFn.getMarkers = function() {
        var bounds = etoFn.map.getBounds();

        if (etoFn.config.ajaxReady === false || null === bounds || typeof bounds == 'undefined') {
            return;
        }

        var ne = bounds.getNorthEast(),
            sw = bounds.getSouthWest();

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': ETO.config.csrfToken
            },
            url: ETO.config.routes.mapDrivers,
            type: 'GET',
            data: {
                bounds: [
                    ne.lat(),
                    ne.lng(),
                    sw.lat(),
                    sw.lng()
                ]
            },
            dataType: 'json',
            async: true,
            cache: false,
            success: function(response) {
                etoFn.drivers = response;
                var exists = [],
                    availability = {available: 0, unavailable: 0, busy: 0, away: 0};

                etoFn.driverList();
                etoFn.drivers.forEach(function(user) {
                    availability[user.availability]++;
                    exists.push(user.id);

                    if(ETO.config.driverApp	=== 1) {
                        var marker,
                            icon,
                            prevPosition,
                            markerLabel = typeof user.unique_id != 'undefined' ? user.unique_id.toString() : '';

                        if (markerLabel.length === 0) {
                            markerLabel = user.displayName;
                        }

                        if (etoFn.markers.hasOwnProperty(user.id)) {
                            marker = etoFn.markers[user.id];
                            prevPosition = marker.getPosition();

                            marker.setPosition(
                                new google.maps.LatLng(
                                    user.lat,
                                    user.lng
                                )
                            );

                            if (user.heading === null) {
                                user.heading = google.maps.geometry.spherical.computeHeading(prevPosition, marker.getPosition());
                            }

                            icon = marker.getIcon();
                            icon.rotation = parseFloat(user.heading);
                            marker.setIcon(icon);

                            marker.labelContent = '<span class="marker-driver-label ' + user.availability + '">' + markerLabel + '</span>';
                            marker.labelClass = "marker-label " + user.availability;
                        } else {
                            icon = {
                                url: ETO.config.icons.carBlack,
                                scaledSize: new google.maps.Size(50, 30), // scaled size
                                size: new google.maps.Size(50, 30),
                                origin: new google.maps.Point(0, 0),
                            };

                            marker = new MarkerWithLabel({
                                animation: google.maps.Animation.DROP,
                                position: new google.maps.LatLng(user.lat, user.lng),
                                userId: user.id,
                                title: user.name,
                                map: etoFn.map,
                                labelContent: '<span class="marker-driver-label ' + user.availability + '">' + markerLabel + '</span>',
                                labelClass: "marker-label " + user.availability, // the CSS class for the label
                                labelInBackground: false,
                                icon: icon,
                            });

                            marker.addListener('click', function () {
                                if (typeof ETO.Dispatch != 'undefined') {
                                    var parameters = {
                                        name: user.name,
                                        filters: {
                                            'filter-driver-name': [user.id],
                                        }
                                    };
                                    if (ETO.Dispatch.config.layout == 'bootstrap') {
                                        var tabHeader = $('.dispatch-bookings').find('.nav-tabs').find('.active'),
                                            tabBody = $('.dispatch-bookings').find('.tab-content').find('.active');

                                        tabHeader.removeClass('active');
                                        tabBody.removeClass('active');

                                        ETO.Booking.createTable('driver-id-' + user.id, parameters, 'active');
                                    } else if (ETO.Dispatch.config.layout == 'goldenlayout') {
                                        ETO.Dispatch.newTabSet('datatable-bookings', 'driver-id', user.id, user.name, parameters);
                                    }
                                }
                            });

                            etoFn.markers[user.id] = marker;
                        }
                    }
                });

                // Clear out the old markers.
                etoFn.markers.forEach(function(marker) {
                    if ( $.inArray(marker.userId, exists) < 0 ) {
                        marker.setMap(null);
                        etoFn.markers.splice(etoFn.markers.indexOf(marker), 1);
                    }
                });

                if (etoFn.drivers.length === 0 ){
                    $('.dispatch-drivers-list .available').html('');
                    $('.dispatch-drivers-list .unavailable').html('');
                    $('.dispatch-drivers-list .busy').html('');
                    $('.dispatch-drivers-list .away').html('');
                }
                else if (availability.unavailable === 0 ){
                    $('.dispatch-drivers-list .unavailable').html('');
                }
            },
            error: function() {
                console.log('AJAX error: getMarkers()');
            },
            beforeSend: function() {
                etoFn.config.ajaxReady = false;
            },
            complete: function() {
                etoFn.config.ajaxReady = true;
            }
        });
    };

    etoFn.initMap = function() {
        var input = document.getElementById('search'),
            searchBox = new google.maps.places.SearchBox(input);

        etoFn.map = new google.maps.Map(document.getElementById('map'), {
            mapTypeControl: false,
            center: {
                lat: parseFloat(etoFn.settings.user.eto_map.view.search_lat),
                lng: parseFloat(etoFn.settings.user.eto_map.view.search_lng)
            },
            zoom: parseInt(etoFn.settings.user.eto_map.view.zoom),
            mapTypeId: etoFn.settings.user.eto_map.view.type.toString(),
            zoomControlOptions: {
                position: google.maps.ControlPosition.RIGHT_TOP
            },
            fullscreenControlOptions: {
                position: google.maps.ControlPosition.TOP_RIGHT
            },
            streetViewControlOptions: {
                position: google.maps.ControlPosition.RIGHT_TOP
            },
        });

        etoFn.infoWindow = new google.maps.InfoWindow;

        $('.eto-search').attr('placeholder', ETO.lang.map.search);

        etoFn.map.controls[google.maps.ControlPosition.TOP_RIGHT].push(document.getElementById('eto-btn-map-settings'));

        // Map loaded
        google.maps.event.addListenerOnce(etoFn.map, 'idle', function() {
            $('#search').val(etoFn.settings.user.eto_map.view.search);
            $('#search').show(100);

            setInterval(function(){
                etoFn.getMarkers();
            }, etoFn.settings.user.eto_map.interval.refresh * 1000);
        });

        google.maps.event.addListener(etoFn.map, 'zoom_changed', function() {
            var pixelSizeAtZoom0 = 5; //the size of the icon at zoom level 0
            var maxPixelSize = 50; //restricts the maximum size of the icon, otherwise the browser will choke at higher zoom levels trying to scale an image to millions of pixels

            var zoom = etoFn.map.getZoom();
            var relativePixelSize = Math.pow(zoom, 2) / pixelSizeAtZoom0; // use 2 to the power of current zoom to calculate relative pixel size.  Base of exponent is 2 because relative size should double every time you zoom in

            if (relativePixelSize > maxPixelSize) //restrict the maximum size of the icon
                relativePixelSize = maxPixelSize;

            //change the size of the icon
            for (var i in etoFn.markers) {
                var icon = etoFn.markers[i].getIcon();
                // icon.scaledSize = new google.maps.Size(relativePixelSize, relativePixelSize * 0.6);
                // icon.anchor = new google.maps.Point(relativePixelSize / 2, (relativePixelSize * 1.1) / 2);
                etoFn.markers[i].setIcon(icon);
            }
        });

        // Bias the SearchBox results towards current map's viewport.
        etoFn.map.addListener('bounds_changed', function() {
            searchBox.setBounds(etoFn.map.getBounds());
        });

        etoFn.map.addListener('idle', function() {
            etoFn.getMarkers();
        });

        var markers = [];
        // Listen for the event fired when the user selects a prediction and retrieve
        // more details for that place.
        searchBox.addListener('places_changed', function() {
            alert('oyhelo');
            $('#search').change();
            var places = searchBox.getPlaces();
            if ( places.length == 0 ) {return;}

            // Clear out the old markers.
            markers.forEach(function(marker) {marker.setMap(null);});
            markers = [];

            // For each place, get the icon, name and location.
            var bounds = new google.maps.LatLngBounds();
            places.forEach(function(place) {
                if ( !place.geometry ) {
                    console.log('Returned place contains no geometry');
                    return;
                }

                // Create a marker for each place.
                markers.push(new google.maps.Marker({
                    map: etoFn.map,
                    // icon: {
                    //     url: place.icon,
                    //     size: new google.maps.Size(71, 71),
                    //     origin: new google.maps.Point(0, 0),
                    //     anchor: new google.maps.Point(17, 34),
                    //     scaledSize: new google.maps.Size(25, 25)
                    // },
                    title: place.name,
                    position: place.geometry.location
                }));

                if ( place.geometry.viewport ) {
                    // Only geocodes have viewport.
                    bounds.union(place.geometry.viewport);
                }
                else {
                    bounds.extend(place.geometry.location);
                }
            });
            etoFn.map.fitBounds(bounds);
        });
    };

    return etoFn;
}();
