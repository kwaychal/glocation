<!DOCTYPE html>
<html>

<head>
    <title>Displaying Text Directions With setPanel()</title>
    <script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBHLufyrwbHjBnL_kNplKx6T9nTNMNtidM&callback=initMap&libraries=&v=weekly" defer></script>
    <style>
        /* Always set the map height explicitly to define the size of the div
       * element that contains the map. */
        #map {
            height: 100%;
        }

        /* Optional: Makes the sample page fill the window. */
        html,
        body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        #floating-panel {
            position: absolute;
            top: 10px;
            left: 25%;
            z-index: 5;
            background-color: #fff;
            padding: 5px;
            border: 1px solid #999;
            text-align: center;
            font-family: "Roboto", "sans-serif";
            line-height: 30px;
            padding-left: 10px;
        }

        #right-panel {
            font-family: "Roboto", "sans-serif";
            line-height: 30px;
            padding-left: 10px;
        }

        #right-panel select,
        #right-panel input {
            font-size: 15px;
        }

        #right-panel select {
            width: 100%;
        }

        #right-panel i {
            font-size: 12px;
        }

        #right-panel {
            height: 100%;
            float: right;
            width: 390px;
            overflow: auto;
        }

        #map {
            margin-right: 400px;
        }

        #floating-panel {
            background: #fff;
            padding: 5px;
            font-size: 14px;
            font-family: Arial;
            border: 1px solid #ccc;
            box-shadow: 0 2px 2px rgba(33, 33, 33, 0.4);
            display: none;
        }

        @media print {
            #map {
                height: 500px;
                margin: 0;
            }

            #right-panel {
                float: none;
                width: auto;
            }
        }
    </style>
    <!-- jsFiddle will insert css and js -->
</head>

<body>
    <div id="floating-panel">
        <strong>Start:</strong>
        <select id="start">
            <option value="mumbai">Mumbai</option>
            <option value="mumbai">Thane</option>
            <option value="pune">Pune</option>
            <option value="karad">Karad</option>
        </select>
        <br />
        <strong>End:</strong>
        <select id="end">
            <option value="thane">Mumbai</option>
            <option value="thane">Thane</option>
            <option value="pune">Pune</option>
            <option value="karad">Karad</option>
        </select>
    </div>
    <div id="right-panel"></div>
    <div id="map"></div>
    <p id="info" class="info"></p>
</body>
<script>
    "use strict";

    var CurrentLocation;

    /**
     * Create google maps Map instance.
     * @param {number} lat
     * @param {number} lng
     * @return {Object}
     */
    const createMap = ({
        lat,
        lng
    }) => {
        return new google.maps.Map(document.getElementById('map'), {
            center: {
                lat,
                lng
            },
            zoom: 15
        });
    };


    /**
     * Create google maps Marker instance.
     * @param {Object} map
     * @param {Object} position
     * @return {Object}
     */
    const createMarker = ({
        map,
        position
    }) => {
        return new google.maps.Marker({
            map,
            icon: 'car.png',
            position
        });
    };

    /**
     * Track the user location.
     * @param {Object} onSuccess
     * @param {Object} [onError]
     * @return {number}
     */
    const trackLocation = ({
        onSuccess,
        onError = () => {}
    }) => {
        if ('geolocation' in navigator === false) {
            return onError(new Error('Geolocation is not supported by your browser.'));
        }

        return navigator.geolocation.watchPosition(onSuccess, onError, {
            enableHighAccuracy: true,
            timeout: 5000,
            maximumAge: 0
        });
    };

    /**
     * Get position error message from the given error code.
     * @param {number} code
     * @return {String}
     */
    const getPositionErrorMessage = code => {
        switch (code) {
            case 1:
                return 'Permission denied.';
            case 2:
                return 'Position unavailable.';
            case 3:
                return 'Timeout reached.';
        }
    }

    function initMap() {
        const directionsRenderer = new google.maps.DirectionsRenderer();
        const directionsService = new google.maps.DirectionsService();
        const initialPosition = {
            lat: 59.32,
            lng: 17.84
        };

        const map = new google.maps.Map(document.getElementById("map"), {
            zoom: 7,
            center: initialPosition
        });

        const marker = createMarker({
            map,
            position: initialPosition
        });

        const $info = document.getElementById('info');

        directionsRenderer.setMap(map);
        directionsRenderer.setPanel(document.getElementById("right-panel"));
        const control = document.getElementById("floating-panel");
        control.style.display = "block";
        map.controls[google.maps.ControlPosition.TOP_CENTER].push(control);

        const onChangeHandler = function() {
            calculateAndDisplayRoute(directionsService, directionsRenderer);
        };

        document.getElementById("start").addEventListener("change", onChangeHandler);
        document.getElementById("end").addEventListener("change", onChangeHandler);

        let watchId = trackLocation({
            onSuccess: ({
                coords: {
                    latitude: lat,
                    longitude: lng
                }
            }) => {
                marker.setPosition({
                    lat,
                    lng
                });
                map.panTo({
                    lat,
                    lng
                });
                $info.textContent = `Lat: ${lat.toFixed(5)} Lng: ${lng.toFixed(5)}`;
                CurrentLocation = new google.maps.LatLng(lat.toFixed(5), lng.toFixed(5));
                $info.classList.remove('error');
            },
            onError: err => {
                console.log($info);
                $info.textContent = `Error: ${err.message || getPositionErrorMessage(err.code)}`;
                $info.classList.add('error');
            }
        });
    }

    function calculateAndDisplayRoute(directionsService, directionsRenderer) {
        const start = document.getElementById("start").value;
        const end = document.getElementById("end").value;
        const $info = document.getElementById('info');

        console.log(CurrentLocation);
        directionsService.route({
                origin: CurrentLocation,
                destination: end,
                travelMode: google.maps.TravelMode.DRIVING
            },
            (response, status) => {
                if (status === "OK") {
                    directionsRenderer.setDirections(response);
                } else {
                    window.alert("Directions request failed due to " + status);
                }
            }
        );
    }
</script>

</html>