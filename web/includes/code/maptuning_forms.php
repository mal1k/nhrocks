<?php

/*==================================================================*\
######################################################################
#                                                                    #
# Copyright 2018 Arca Solutions, Inc. All Rights Reserved.           #
#                                                                    #
# This file may not be redistributed in whole or part.               #
# eDirectory is licensed on a per-domain basis.                      #
#                                                                    #
# ---------------- eDirectory IS NOT FREE SOFTWARE ----------------- #
#                                                                    #
# http://www.edirectory.com | http://www.edirectory.com/license.html #
######################################################################
\*==================================================================*/

# ----------------------------------------------------------------------------------------------------
# * FILE: /includes/code/maptuning_forms.php
# ----------------------------------------------------------------------------------------------------

$keyStr = '';

$mapZoom = 15;
if(!empty($map_zoom)){
    $mapZoom = $map_zoom;
} elseif (!empty($contact_mapzoom)) {
    $mapZoom = $contact_mapzoom;
}

/* key for demodirectory.com */
if (DEMO_LIVE_MODE) {
    $googleMapsKey = GOOGLE_MAPS_APP_DEMO;
} else {
    setting_get('google_api_key', $google_maps_key);
    $googleMapsKey = $google_maps_key;
}

if ($googleMapsKey) {
    $keyStr = "&key=$googleMapsKey";
}

$protocol = 'http';

if (SSL_ENABLED === 'on') {
    if (string_strpos($_SERVER['PHP_SELF'], SITEMGR_ALIAS) !== false && FORCE_SITEMGR_SSL === 'on') {
        $protocol = 'https';
    } elseif (string_strpos($_SERVER['PHP_SELF'], MEMBERS_ALIAS) !== false && FORCE_MEMBERS_SSL === 'on') {
        $protocol = 'https';
    }
}
?>

<script src="<?= $protocol ?>://maps.google.com/maps/api/js?sensor=false<?= $keyStr ?>" type="text/javascript"></script>

<?php
/* ModStores Hooks */
if (!HookFire("load_map_ml", [
    "map_zoom" => &$map_zoom
])) { ?>
    <script type="text/javascript">
        function setCoordinates(coord, termId) {
            termId = termId || '';
            var new_lat;
            var new_long;
            var aux_latlong;

            document.getElementById('myLatitudeLongitude' + termId).value = coord;
            aux_latlong = document.getElementById('myLatitudeLongitude' + termId).value;
            aux_latlong = aux_latlong.replace("(", "").replace(")", "").replace(" ", "").split(',');
            new_lat = aux_latlong[0];
            new_long = aux_latlong[1];

            var num_lat = Number(new_lat);
            var num_long = Number(new_long);

            document.getElementById('latitude' + termId).value = num_lat.toFixed(6);
            document.getElementById('longitude' + termId).value = num_long.toFixed(6);
        }

        function initialize(map_zoom, address, latitude, longitude, use_lat_long, termId) {
            var geocoder = new google.maps.Geocoder();

            termId = termId || '';

            var myOptions = {
                zoom: map_zoom,
                scrollwheel: false,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            };

            var map = new google.maps.Map(document.getElementById("map" + termId), myOptions);

            var marker = new google.maps.Marker({
                map: map,
                draggable: true
            });

            if (use_lat_long && latitude && longitude) {
                var latlng = new google.maps.LatLng(latitude, longitude);
                marker.setPosition(latlng);
                map.setCenter(latlng);
            } else if (geocoder) {
                geocoder.geocode({'address': address}, function (results, status) {
                    if (status == google.maps.GeocoderStatus.OK) {
                        map.setCenter(results[0].geometry.location);
                        marker.setPosition(results[0].geometry.location);
                        setCoordinates(results[0].geometry.location, termId);
                    }
                });
            }

            document.getElementById("map_zoom" + termId).value = map.getZoom();

            google.maps.event.addListener(marker, 'dragend', function (event) {
                setCoordinates(event.latLng, termId);
            });

            google.maps.event.addListener(map, 'zoom_changed', function () {
                document.getElementById("map_zoom" + termId).value = map.getZoom();
            });
        }

        function loadMap(form, use_lat_long) {
            var latitude = document.getElementById('latitude').value;
            var longitude = document.getElementById('longitude').value;
            var map = $("#tableMapTuning");
            var locations = [];

            var address = document.getElementById('address');
            var zip = document.getElementById('zip_code');
            var locationName = document.getElementById('location_name');
            var location_1 = document.getElementById('location_1');
            var location_3 = document.getElementById('location_3');
            var location_4 = document.getElementById('location_4');
            var location_5 = document.getElementById('location_5');
            var neighborhood = document.getElementById('neighborhood');
            var city = document.getElementById('city');
            var state = document.getElementById('state');
            var country = document.getElementById('country');

            map.hide();

            if (use_lat_long && latitude && longitude) {
                if (!isFinite(latitude) || !isFinite(longitude) || latitude < -90 || latitude > 90 || longitude < -180 || longitude > 180) {
                    if (!isFinite(latitude) || latitude < -90 || latitude > 90) {
                        return false;
                    }
                    if (!isFinite(longitude) || longitude < -180 || longitude > 180) {
                        return false;
                    }
                }

                map.show();

                return initialize(<?=$mapZoom?>, '', latitude, longitude, use_lat_long);
            }

            if (address && address.value) locations.push(address.value);
            if (zip && zip.value) locations.push(zip.value);
            if (locationName && locationName.value) locations.push(locationName.value);

            if (location_1 && location_1.selectedIndex > 0) {
                locations.push(location_1.options[location_1.selectedIndex].text);
            } else if(country) {
                locations.push(country.value)
            }

            if (location_3 && location_3.selectedIndex > 0) {
                locations.push(location_3.options[location_3.selectedIndex].text)
            } else if(state) {
                locations.push(state.value)
            }

            if (location_4 && location_4.selectedIndex > 0) {
                locations.push(location_4.options[location_4.selectedIndex].text);
            } else if(city) {
                locations.push(city.value)
            }

            if (location_5 && location_5.selectedIndex > 0) {
                locations.push(location_5.options[location_5.selectedIndex].text);
            } else if(neighborhood) {
                locations.push(neighborhood.value)
            }

            if (document.getElementById('new_location4_field') && document.getElementById('new_location4_field').value) {
                locations.push(document.getElementById('new_location4_field').value);
            }

            if (locations.length == 0) {
                latitude = longitude = '';
                document.getElementById('latitude').value = latitude;
                document.getElementById('longitude').value = longitude;
            }

            if (locations.length > 0) {
                map.show();

                return initialize(<?=($map_zoom ?: 15)?>, locations.join(', '), latitude, longitude, use_lat_long);
            }
        }

        function loadTermsMap(termId) {
            var latitude = document.getElementById('latitude' + termId).value;
            var longitude = document.getElementById('longitude' + termId).value;
            var token = document.getElementById('token' + termId).value;
            var valid_coord = true;
            var loaded = document.getElementById("myLatitudeLongitude" + termId).value;

            if (latitude && longitude) {
                if (!isFinite(latitude) || latitude < -90 || latitude > 90) {
                    valid_coord = false;
                }
                if (!isFinite(longitude) || longitude < -180 || longitude > 180) {
                    valid_coord = false;
                }
            }

            if (valid_coord && !loaded) {
                $("#tableMapTuning" + termId).show();

                return initialize(15, token, latitude, longitude, true, termId);
            }

            if (!latitude && !longitude) {
                $("#tableMapTuning" + termId).hide();
            }
        }
    </script>
<? } ?>
