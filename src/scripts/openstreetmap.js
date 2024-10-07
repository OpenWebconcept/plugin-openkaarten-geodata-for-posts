import L from "leaflet";

// Retrieve the locations and map configuration from the global window object.
const {centerLat, centerLong, defaultZoom, setMarker } = window.leaflet_vars;

// Set the map configuration.
const config = {
  "centerX": centerLong,
  "centerY": centerLat,
  "minimumZoom": 4,
  "maximumZoom": 16,
  "defaultZoom": defaultZoom,
  "enableZoomControl": true,
  "enableBoxZoomControl": true
}

// Create the map with the specified configuration.
window.onload = function() {
  const map = new L.Map('map', {
    center: [config.centerY, config.centerX],
    zoom: config.defaultZoom,
    minZoom: config.minimumZoom,
    maxZoom: config.maximumZoom,
    boxZoom: config.enableBoxZoomControl
  });

  // Add the OpenStreetMap tile layer to the map.
  L.tileLayer('https://{s}.tile.osm.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://osm.org/copyright">OpenStreetMap</a> contributors'
  }).addTo(map);

  // Add a marker to the map if the location is set.
  if (setMarker) {
    addMarker( map, config.centerY, config.centerX );
  }

  map.on( 'click', function (e) {
    var coord = e.latlng;
    var lat = coord.lat;
    var lng = coord.lng;

    // Add a draggable marker to the map.
    addMarker( map, lat, lng );
    map.panTo( new L.LatLng( lat, lng ) );

    // Update the form fields with the new coordinates.
    updateGeoFields( lat, lng );
  } );
};

function addMarker( map, lat, lng ) {
  // Remove all existing markers from the map.
  map.eachLayer( function (layer) {
    if (layer instanceof L.Marker) {
      map.removeLayer( layer );
    }
  } );

  // Create a custom marker icon with the location color and icon.
  let customIconHtml = "<div style='background-color:" + location.color + ";' class='marker-pin'></div>";
  if (location.icon) {
    customIconHtml += "<span class='marker-icon'><img src='" + location.icon + "'  alt='marker icon' /></span>";
  }

  var customIcon = L.divIcon( {
    className: 'leaflet-custom-icon',
    html: customIconHtml,
    iconSize: [30, 42],
    iconAnchor: [15, 42]
  } );

  let iconOptions = {
    icon: customIcon,
    draggable: 'true'
  }

  var marker = L.marker( [lat, lng], iconOptions );
  marker.on( 'dragend', function (event) {
    var marker = event.target;
    var position = marker.getLatLng();
    marker.setLatLng( new L.LatLng( position.lat, position.lng ), {draggable: 'true'} );
    map.panTo( new L.LatLng( position.lat, position.lng ) );

    // Update the form fields with the new coordinates.
    updateGeoFields( position.lat, position.lng );
  } );
  map.addLayer( marker );
}

function updateGeoFields(lat, lng) {
  if ( document.getElementById('field_geo_latitude') !== null ) {
    document.getElementById( 'field_geo_latitude' ).value = lat;
  }
  if ( document.getElementById('field_geo_longitude') !== null ) {
    document.getElementById( 'field_geo_longitude' ).value = lng;
  }
}
