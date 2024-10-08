<?php
/**
 * Helper class for CMB2
 *
 * @package    Openkaarten_Geodata_Plugin
 * @subpackage Openkaarten_Geodata_Plugin/Admin
 * @author     Acato <eyal@acato.nl>
 */

namespace Openkaarten_Geodata_Plugin\Admin;

use CMB2_Field;

/**
 * Helper class for CMB2
 */
class Cmb2 {

	/**
	 * The object ID.
	 *
	 * @var int
	 */
	private static $object_id;

	/**
	 * The singleton instance of this class.
	 *
	 * @access private
	 * @var    \Openkaarten_Geodata_Plugin\Admin\Cmb2|null $instance The singleton instance of this class.
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance of this class.
	 *
	 * @return Cmb2 The singleton instance of this class.
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new Cmb2();
		}

		return self::$instance;
	}

	/**
	 * Initialize the class and set its properties.
	 *
	 * @return void
	 */
	private function __construct() {
		add_action( 'cmb2_init', array( 'Openkaarten_Geodata_Plugin\Admin\Cmb2', 'action_cmb2_init' ) );
		add_action( 'cmb2_render_geomap', array( 'Openkaarten_Geodata_Plugin\Admin\Cmb2', 'cmb2_render_geomap_field_type' ), 10, 5 );
	}

	/**
	 * Register the CMB2 metaboxes
	 *
	 * @return void
	 */
	public static function action_cmb2_init() {
		// Get the post ID, both on edit page and after submit.
		$post_id = '';

		// phpcs:ignore WordPress.Security.NonceVerification -- No nonce verification needed.
		if ( ! empty( $_GET['post'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification -- No nonce verification needed.
			$post_id = sanitize_text_field( wp_unslash( $_GET['post'] ) );
			// phpcs:ignore WordPress.Security.NonceVerification -- No nonce verification needed.
		} elseif ( ! empty( $_POST['post_ID'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification -- No nonce verification needed.
			$post_id = sanitize_text_field( wp_unslash( $_POST['post_ID'] ) );
		}

		self::cmb2_location_geometry_fields();
		self::$object_id = (int) $post_id;
	}

	/**
	 * Register the CMB2 metaboxes for the geometry fields for the Location post type.
	 *
	 * @return void
	 */
	public static function cmb2_location_geometry_fields() {
		$prefix = 'location_geometry_';

		$openkaarten_geodata_post_types = get_option( 'openkaarten_geodata_post_types' );

		$cmb = new_cmb2_box(
			array(
				'id'           => $prefix . 'metabox',
				'title'        => __( 'Geodata', 'openkaarten-geodata' ),
				'object_types' => $openkaarten_geodata_post_types,
				'context'      => 'normal',
				'priority'     => 'low',
				'show_names'   => true,
				'cmb_styles'   => true,
				'show_in_rest' => true,
			)
		);

		// Add field to select whether to insert geodata based on a map marker or on an address.
		$cmb->add_field(
			array(
				'id'           => $prefix . 'geodata_type',
				'name'         => __( 'Geodata type', 'openkaarten-geodata' ),
				'type'         => 'radio',
				'options'      => array(
					'marker'  => __( 'Marker(s)', 'openkaarten-geodata' ),
					'address' => __( 'Address', 'openkaarten-geodata' ),
				),
				'default'      => 'marker',
				'show_in_rest' => true,
			)
		);

		$cmb->add_field(
			array(
				'id'           => $prefix . 'coordinates',
				'name'         => __( 'Coordinates', 'openkaarten-geodata' ),
				'desc'         => __( 'Click on the map to add a new marker. Drag the marker after adding to change the location of the marker. Right click on a marker to remove the marker from the map.', 'openkaarten-geodata' ),
				'type'         => 'geomap',
				'show_in_rest' => true,
				'save_field'   => false,
				'attributes'   => array(
					'data-conditional-id'    => $prefix . 'geodata_type',
					'data-conditional-value' => 'marker',
				),
			)
		);

		// Add address and latitude and longitude fields.
		$address_fields = array(
			'address'   => __( 'Address + number', 'openkaarten-geodata' ),
			'zipcode'   => __( 'Zipcode', 'openkaarten-geodata' ),
			'city'      => __( 'City', 'openkaarten-geodata' ),
			'country'   => __( 'Country', 'openkaarten-geodata' ),
			'latitude'  => __( 'Latitude', 'openkaarten-geodata' ),
			'longitude' => __( 'Longitude', 'openkaarten-geodata' ),
		);

		foreach ( $address_fields as $field_key => $field ) {
			// Check if this field has a value and set it as readonly and disabled if it has.
			$field_value = get_post_meta( self::$object_id, 'field_geo_' . $field_key, true );
			$attributes  = array(
				'data-conditional-id'    => $prefix . 'geodata_type',
				'data-conditional-value' => 'address',
			);

			if ( 'latitude' === $field_key || 'longitude' === $field_key ) {
				$attributes = array_merge(
					$attributes,
					array(
						'readonly' => 'readonly',
					)
				);
			}

			$cmb->add_field(
				array(
					'name'         => $field,
					'id'           => 'field_geo_' . $field_key,
					'type'         => 'text',
					/* translators: %s: The field name. */
					'description'  => sprintf( __( 'The %s of the location.', 'openkaarten-geodata' ), strtolower( $field ) ),
					'show_in_rest' => true,
					'attributes'   => $attributes,
					'save_field'   => false,
				)
			);
		}
	}

	/**
	 * Render the geomap field type.
	 *
	 * @param CMB2_Field $field The CMB2 field object.
	 * @param mixed      $escaped_value The value of the field.
	 * @param int        $object_id The object ID.
	 *
	 * @return void
	 */
	public static function cmb2_render_geomap_field_type( $field, $escaped_value, $object_id ) {
		// Get latitude and longitude of centre of the Netherlands as starting point.
		$center_lat  = 52.1326;
		$center_long = 5.2913;

		// Retrieve the current values of the latitude and longitude of the markers from the geometry object.
		$set_marker = false;

		$markers  = array();
		$geometry = get_post_meta( $object_id, 'geometry', true );
		if ( ! empty( $geometry ) ) {
			$geometry = json_decode( $geometry, true );
			if ( ! empty( $geometry['geometry']['coordinates'] ) ) {
				if ( ! is_array( $geometry['geometry']['coordinates'][0] ) ) {
					$geometry['geometry']['coordinates'] = array( $geometry['geometry']['coordinates'] );
				}

				// Calculate center lat/long and bounds.
				$center_lat  = 0;
				$center_long = 0;
				foreach ( $geometry['geometry']['coordinates'] as $marker ) {
					$center_lat  += $marker[1];
					$center_long += $marker[0];
				}
				$center_lat  = $center_lat / count( $geometry['geometry']['coordinates'] );
				$center_long = $center_long / count( $geometry['geometry']['coordinates'] );

				// Set the marker to true.
				$set_marker = true;

				// Add the marker to the markers array.
				$markers = $geometry['geometry']['coordinates'];
			}
		}

		// Enqueue the OpenStreetMap script.
		wp_localize_script(
			'owc_ok_geodata-openstreetmap',
			'leaflet_vars',
			array(
				'centerLat'   => esc_attr( $center_lat ),
				'centerLong'  => esc_attr( $center_long ),
				'defaultZoom' => 10,
				'fitBounds'   => false,
				'allowClick'  => true,
				'setMarker'   => $set_marker,
				'markers'     => $markers,
			)
		);

		// Add the map and the hidden input field. This hidden input field is needed for the CMB2 Conditional Logic to work, but doesn't store any data itself.
		echo '<div id="map" class="map"></div>
		<p class="cmb2-metabox-description">' . esc_attr( $field->args['desc'] ) . '</p>
		<input type="hidden" id="' . esc_attr( $field->args['id'] ) . '" name="' . esc_attr( $field->args['_name'] ) . '" data-conditional-id="location_geometry_geodata_type" data-conditional-value="marker">';
	}
}
