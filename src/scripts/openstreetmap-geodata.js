import L from "leaflet";

// Run as soon as the DOM is ready. The script is enqueued in the footer, so the
// DOM is already parsed by the time this executes. We deliberately do NOT rely on
// `window.onload`: in the block editor that can fire before the CMB2 meta-box area
// has its final layout, which caused Leaflet to cache a wrong (often zero) container
// size and only render the top-left tile until a manual window resize.
if ( document.readyState === 'loading' ) {
  document.addEventListener( 'DOMContentLoaded', initializeGeodataMap );
} else {
  initializeGeodataMap();
}

function initializeGeodataMap() {
  // Check if there is a div with the ID 'map-geodata'.
  if ( ! document.getElementById( 'map-geodata' ) ) {
    return;
  }

  // Check if leafletvars is defined.
  if ( 'undefined' === typeof window.leaflet_vars ) {
    console.error( 'leaflet_vars is not defined' );
    return;
  }

  // Retrieve the locations and map configuration from the global window object.
  const { centerLat, centerLong, defaultZoom, setMarker, markers } = window.leaflet_vars;

  let markersArray = markers;

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

  const map = new L.Map('map-geodata', {
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
    // Add a marker for every location in the markers array.
    markersArray.forEach( function( location ) {
      addMarker( map, location[1], location[0] );
    } );
  }

  // Make sure Leaflet (re)calculates its container size whenever the map becomes
  // visible or its dimensions change. This single mechanism covers all cases that
  // previously left the map grey until a manual window resize:
  //   - the initial layout settling in the (Gutenberg) meta-box area;
  //   - the CMB2 conditional row becoming visible (address -> marker(s));
  //   - window resizes and panel collapse/expand.
  ensureMapSize( map, document.getElementById( 'map-geodata' ) );

  map.on( 'click', function (e) {
    var coord = e.latlng;
    var lat = coord.lat;
    var lng = coord.lng;

    // Add a draggable marker to the map.
    addMarker( map, lat, lng );
    map.panTo( new L.LatLng( lat, lng ) );
  } );
}

/**
 * Keep the Leaflet map sized to its container.
 *
 * @param {L.Map}       map     The Leaflet map instance.
 * @param {HTMLElement} element The map container element.
 */
function ensureMapSize( map, element ) {
  // Recalculate once the first render is done, on the next tick so the browser
  // has applied layout.
  map.whenReady( function () {
    setTimeout( function () {
      map.invalidateSize();
    }, 0 );
  } );

  // A ResizeObserver fires when the container gains or changes size, which is
  // exactly when Leaflet needs to recalculate. This handles the container going
  // from hidden/0px to visible without relying on fragile event ordering.
  if ( 'ResizeObserver' in window && element ) {
    let lastWidth = 0;
    const observer = new ResizeObserver( function () {
      // Only act when the container is actually visible and the width changed.
      if ( element.offsetWidth > 0 && element.offsetWidth !== lastWidth ) {
        lastWidth = element.offsetWidth;
        map.invalidateSize();
      }
    } );
    observer.observe( element );
  }

  // Fallback for browsers without ResizeObserver: keep listening for the CMB2
  // conditional-logic "show" event on the surrounding row.
  if ( typeof jQuery !== 'undefined' ) {
    jQuery( function( $ ) {
      var $row = $( '#map-geodata' ).closest( '.cmb-row' );
      $row.on( 'CMB2show', function() {
        setTimeout( function() {
          map.invalidateSize();
        }, 0 );
      } );
    } );
  }
}

/**
 * Add a draggable marker to the map.
 *
 * @param {L.Map}  map   The Leaflet map instance.
 * @param {number} lat   Latitude.
 * @param {number} lng   Longitude.
 * @param {string} color Optional marker pin colour. Falls back to the CSS default.
 * @param {string} icon  Optional marker icon URL.
 */
function addMarker( map, lat, lng, color, icon ) {
  // Create a custom marker icon. Only set an inline background colour when one is
  // explicitly provided; otherwise let the .marker-pin CSS default apply.
  let customIconHtml = "<div" + ( color ? " style='background-color:" + color + ";'" : "" ) + " class='marker-pin'></div>";
  if ( icon ) {
    customIconHtml += "<span class='marker-icon'><img src='" + icon + "'  alt='marker icon' /></span>";
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
  } );

  // Add an on right click to the marker to remove it.
  marker.on( 'contextmenu', function (event) {
    map.removeLayer( event.target );
    updateMarkers( map );
  }, this );

  map.addLayer( marker );

  // Update markers after adding marker.
  updateMarkers( map );
}

function updateMarkers( map ) {
  // Retrieve all markers from the map.
  let markers = map._layers;

  // Filter out the markers from the map.
  markers = Object.values( markers ).filter( function( marker ) {
    return marker instanceof L.Marker;
  } );

  // Create an array with the marker coordinates.
  let markerData = markers.map( function( marker ) {

    if ( marker[0] && marker[1] ) {
      return {
        lat: marker[1],
        lng: marker[0]
      }
    }

    return {
      lat: marker.getLatLng().lat,
      lng: marker.getLatLng().lng
    }
  } );

  // Pass the marker data to PHP.
  updateGeoFields( markerData );
}

function updateGeoFields( markerData ) {
  // Update hidden CMB2 fields with the marker data.
  jQuery( '#location_geometry_coordinates' ).val( JSON.stringify( markerData ) );
}
