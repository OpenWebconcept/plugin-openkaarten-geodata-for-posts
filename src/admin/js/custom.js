// Create a JS file and add a function which checks if the CMB2 field field_geo_address, field_geo_zipcode, field_geo_city, field_geo_country is filled in. If so, it will call the function getGeoData() which will fetch the geodata from the OpenKaarten API.
// The function getGeoData() will fetch the geodata from the OpenKaarten API and will fill in the fields field_geo_lat and field_geo_lng with the latitude and longitude of the address.

jQuery( document ).ready( function( $ ) {
  'use strict';

  // Check if the fields field_geo_address, field_geo_zipcode, field_geo_city, field_geo_country are changed.
  // Make sure the change is fired after the CMB2 fields are fully loaded.
  $('#field_geo_address, #field_geo_zipcode, #field_geo_city, #field_geo_country').on('change', function() {
    getGeoData();
  });

  function getGeoData() {
    // Get the values of the fields field_geo_address, field_geo_zipcode, field_geo_city, field_geo_country.
    var search = '';
    var address = $( '#field_geo_address' ).val();
    if (address) {
      search = address;
    }
    var zipcode = $( '#field_geo_zipcode' ).val();
    if (zipcode) {
      if (search) {
        search += ' ' + zipcode;
      } else {
        search = zipcode;
      }
    }
    var city = $( '#field_geo_city' ).val();
    if (city) {
      if (search) {
        search += ' ' + city;
      } else {
        search = city;
      }
    }
    var country = $( '#field_geo_country' ).val();
    if (country) {
      if (search) {
        search += ' ' + country;
      } else {
        search = country;
      }
    }

    // Check if the fields field_geo_address, field_geo_zipcode, field_geo_city, field_geo_country are filled in.
    if (address && zipcode && city && country) {
      // Fetch the geodata from the OpenKaarten API.
      fetch( 'https://nominatim.openstreetmap.org/search?q=' + search + '&format=json&addressdetails=1' )
        .then( response => response.json() )
        .then( data => {
          // Fill in the fields field_geo_lat and field_geo_lng with the latitude and longitude of the address.
          if (data.length > 0) {
            $( '#field_geo_latitude' ).val( data[0].lat );
            $( '#field_geo_longitude' ).val( data[0].lon );
          }
        } );
    }
  }
});
