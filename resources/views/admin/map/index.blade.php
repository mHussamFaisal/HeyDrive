@extends('admin.index')

@section('title', trans('admin/map.page_title'))
@section('subtitle', /*'<i class="fa fa-map"></i> '.*/ trans('admin/map.page_title') )
@section('subclass', 'map-wrapper')

@section('subcontent')
    <div style="display:none;">
        <input type="text" value="London, UK" id="map-search" class="controls form-control" placeholder="{{ trans('admin/map.search') }}" style="display:none;">

        <div id="style-selector-control" class="map-control">
          <label for="traffic"><input type="checkbox" name="traffic" id="traffic" class="selector-control"> Traffic</label>
          <label for="poi-style"><input type="checkbox" name="poi-style" id="poi-style" class="selector-control"> Labels</label>
        </div>
    </div>

    <div id="map"></div>
@stop

@section('subfooter')
    <script src="{{ asset_url('plugins','jquery-cookie/jquery.cookie.js') }}?_dc={{ config('app.timestamp') }}"></script>
    <script src="//maps.googleapis.com/maps/api/js?v=3&key={{ config('site.google_maps_javascript_api_key') }}&libraries=places,geometry"></script>

    <script>
    var map, infoWindow;
    var markerStore = [];
    var INTERVAL = 5000;
    var ajaxReady = 1;

    function bindInfoWindow(marker, map, infoWindow, html) {
        google.maps.event.addListener(marker, 'click', function() {
            infoWindow.setContent(html);
            infoWindow.open(map, marker);
        });
    }

    function getMarkers() {
        if( ajaxReady == 0 ) {
            return;
        }

        var bounds = map.getBounds();
        var ne = bounds.getNorthEast();
        var sw = bounds.getSouthWest();

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': EasyTaxiOffice.csrfToken
            },
            url: '{{ route('admin.map.drivers') }}',
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
                var users = response;
                var exists = [];

                users.forEach(function(user) {
                    exists.push(user.id);

                    if( markerStore.hasOwnProperty(user.id) ) {
                        var marker = markerStore[user.id];

                        var prevPosition = marker.getPosition();

                        marker.setPosition(
                            new google.maps.LatLng(
                                user.lat,
                                user.lng
                            )
                        );

                        if( user.heading === null ) {
                            user.heading = google.maps.geometry.spherical.computeHeading(prevPosition, marker.getPosition());
                        }

                        var icon = marker.getIcon();
                        icon.rotation = parseFloat(user.heading);
                        marker.setIcon(icon);
                    }
                    else {
                        // http://map-icons.com/
                        var icon = {
                            // path: google.maps.SymbolPath.CIRCLE,
                            // path: 'M22-48h-44v43h16l6 5 6-5h16z',
                            // path: 'M24-8c0 4.4-3.6 8-8 8h-32c-4.4 0-8-3.6-8-8v-32c0-4.4 3.6-8 8-8h32c4.4 0 8 3.6 8 8v32z',
                            // scale: 0.6,
                            // anchor: new google.maps.Point(0, 0),

                            path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW,
                            fillColor: '#f75850',
                            fillOpacity: 0.8,
                            strokeColor: '#ac3e39',
                            strokeWeight: 1.5,
                            scale: 8,
                            anchor: new google.maps.Point(0, 2.6),
                            rotation: parseFloat(user.heading)
                        };

                        var marker = new google.maps.Marker({
                            position: new google.maps.LatLng(
                                user.lat,
                                user.lng
                            ),
                            userId: user.id,
                            title: user.name,
                            icon: icon,
                            // label: {
                            //     text: user.unique_id,
                            //     color: '#000',
                            //     fontSize: '12px',
                            //     fontWeight: 'bold',
                            //     // anchorPoint: new google.maps.Point(500, 100)
                            // },
                            // draggable: true,
                            // animation: google.maps.Animation.DROP,
                            map: map
                        });

                        markerStore[user.id] = marker;

                        var html = '';
                        // html += '(+/- '+ user.accuracy +'m)<br />';
                        html += ' <a href="'+ user.url +'" target="_blank" style="display:inline-block; margin-top:5px; font-weight:bold; color:#000;" title="{{ trans('admin/map.view_profile') }}">' + user.name + '</a>';
                        bindInfoWindow(marker, map, infoWindow, html);
                    }
                });

                // Clear out the old markers.
                markerStore.forEach(function(marker) {
                    if( $.inArray(marker.userId, exists) < 0 ) {
                        marker.setMap(null);
                        markerStore.splice(markerStore.indexOf(marker), 1);
                    }
                });
            },
            error: function() {
                console.log('AJAX error: getMarkers');
            },
            beforeSend: function() {
                ajaxReady = 0;
            },
            complete: function() {
                ajaxReady = 1;
            }
        });
    }

    function initMap() {
        map = new google.maps.Map(document.getElementById('map'), {
            center: {
                lat: 51.509865,
                lng: -0.118092
            },
            zoom: 10,
            mapTypeId: 'roadmap'
        });

        // https://developers.google.com/maps/documentation/javascript/examples/layer-traffic
        var trafficLayer = new google.maps.TrafficLayer();

        // Add controls to the map, allowing users to hide/show features.
        var styleControl = document.getElementById('style-selector-control');
        map.controls[google.maps.ControlPosition.TOP_LEFT].push(styleControl);

        document.getElementById('traffic').addEventListener('change', function() {
            if (document.getElementById('traffic').checked) {
                trafficLayer.setMap(map);
            }
            else {
                trafficLayer.setMap(null);
            }
        });

        document.getElementById('poi-style').addEventListener('change', function() {
            if(document.getElementById('poi-style').checked) {
                map.setOptions({styles: styles['default']});
            }
            else {
                map.setOptions({styles: styles['hide']});
            }
        });

        var styles = {
          default: null,
          hide: [{
            featureType: 'poi.business',
            stylers: [{visibility: 'off'}]
          }, {
            featureType: 'transit',
            elementType: 'labels.icon',
            stylers: [{visibility: 'off'}]
          }]
        };

        map.setOptions({styles: styles['hide']});

        infoWindow = new google.maps.InfoWindow;

        // Create the search box and link it to the UI element.
        var input = document.getElementById('map-search');
        var searchBox = new google.maps.places.SearchBox(input);
        map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

        // Map loaded
        google.maps.event.addListenerOnce(map, 'idle', function() {
            if( $.cookie('eto_admin_map_search') ) {
                $('#map-search').val($.cookie('eto_admin_map_search'));
            }

            $('#map-search').show(100);

            $('#map-search').change(function(e) {
                $.cookie('eto_admin_map_search', $('#map-search').val(), {path: EasyTaxiOffice.cookiePath, secure: EasyTaxiOffice.cookieSecure, same_site: EasyTaxiOffice.cookieSameSite});
            });

            var geocoder = new google.maps.Geocoder();
            geocoder.geocode( { 'address': $('#map-search').val() }, function(results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    // var marker = new google.maps.Marker({
                    //     map: map,
                    //     position: results[0].geometry.location
                    // });
                    map.setCenter(results[0].geometry.location);
                    map.setZoom(14);
                }
            });

            // setTimeout(function() {
            //     input.focus();
            //     google.maps.event.trigger(input, 'keydown', { keyCode: 13 });
            // }, 1000);

            @if( config('site.allow_driver_app') )
                setInterval(getMarkers, INTERVAL);
            @endif
        });

        // Bias the SearchBox results towards current map's viewport.
        map.addListener('bounds_changed', function() {
            searchBox.setBounds(map.getBounds());
        });

        @if( config('site.allow_driver_app') )
            map.addListener('idle', function() {
                getMarkers();
            });
        @endif

        var markers = [];
        // Listen for the event fired when the user selects a prediction and retrieve
        // more details for that place.
        searchBox.addListener('places_changed', function() {
            $.cookie('eto_admin_map_search', $('#map-search').val(), {path: EasyTaxiOffice.cookiePath, secure: EasyTaxiOffice.cookieSecure, same_site: EasyTaxiOffice.cookieSameSite});

            var places = searchBox.getPlaces();

            if( places.length == 0 ) {
                return;
            }

            // Clear out the old markers.
            markers.forEach(function(marker) {
                marker.setMap(null);
            });
            markers = [];

            // For each place, get the icon, name and location.
            var bounds = new google.maps.LatLngBounds();
            places.forEach(function(place) {
                if( !place.geometry ) {
                    console.log('Returned place contains no geometry');
                    return;
                }
                var icon = {
                    url: place.icon,
                    size: new google.maps.Size(71, 71),
                    origin: new google.maps.Point(0, 0),
                    anchor: new google.maps.Point(17, 34),
                    scaledSize: new google.maps.Size(25, 25)
                };

                // Create a marker for each place.
                markers.push(new google.maps.Marker({
                    map: map,
                    // icon: icon,
                    title: place.name,
                    position: place.geometry.location
                }));

                if( place.geometry.viewport ) {
                    // Only geocodes have viewport.
                    bounds.union(place.geometry.viewport);
                }
                else {
                    bounds.extend(place.geometry.location);
                }
            });
            map.fitBounds(bounds);
        });
    }

    function mapWH() {
        @if (request('tmpl') == 'body')
          $('#map').css({
              'height': $(window).height() + 'px',
              'width': '100%'
          });
        @else
          $('#map').css({
              // 'height': parseFloat($('.wrapper > .content-wrapper').css('min-height')) + 'px',
              'height': $('.content-wrapper').height() + 'px',
              'width': '100%'
          });
        @endif
    }

    $(document).ready(function() {
        mapWH();
        initMap();
    });

    $(window).resize(function() {
        mapWH();
    });
    </script>
@stop
