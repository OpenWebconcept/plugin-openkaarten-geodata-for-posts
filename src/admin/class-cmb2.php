<?php
/**
 * Helper class for CMB2
 *
 * @package    Openkaarten_Geodata_Plugin
 * @subpackage Openkaarten_Geodata_Plugin/Admin
 * @author     Acato <eyal@acato.nl>
 */

namespace Openkaarten_Geodata_Plugin\Admin;

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
		add_action( 'cmb2_init', [ 'Openkaarten_Geodata_Plugin\Admin\Cmb2', 'action_cmb2_init' ] );
		add_action( 'cmb2_render_geomap', [ 'Openkaarten_Geodata_Plugin\Admin\Cmb2', 'cmb2_render_geomap_field_type' ], 10, 5 );
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
			[
				'id'           => $prefix . 'metabox',
				'title'        => __( 'Geodata', 'openkaarten-base' ),
				'object_types' => $openkaarten_geodata_post_types,
				'context'      => 'normal',
				'priority'     => 'low',
				'show_names'   => true,
				'cmb_styles'   => true,
				'show_in_rest' => true,
			]
		);

		// Add field to select whether to insert geodata based on a map marker or on an address.
		$cmb->add_field(
			[
				'id'           => $prefix . 'geodata_type',
				'name'         => __( 'Geodata type', 'openkaarten-base' ),
				'type'         => 'radio',
				'options'      => [
					'marker'  => __( 'Marker', 'openkaarten-base' ),
					'address' => __( 'Address', 'openkaarten-base' ),
				],
				'default'      => 'marker',
				'show_in_rest' => true,
			]
		);

		$cmb->add_field(
			[
				'id'           => $prefix . 'coordinates',
				'name'         => __( 'Coordinates', 'openkaarten-base' ),
				'desc'         => __( 'Drag the marker after finding the right spot to set the exact coordinates', 'openkaarten-base' ),
				'type'         => 'geomap',
				'show_in_rest' => true,
				'save_field'   => false,
				'attributes' => [
					'data-conditional-id'    => $prefix . 'geodata_type',
					'data-conditional-value' => 'marker',
				],
			]
		);

		// Add address and latitude and longitude fields.
		$address_fields = [
			'address'   => __( 'Address + number', 'openkaarten-base' ),
			'zipcode'   => __( 'Zipcode', 'openkaarten-base' ),
			'city'      => __( 'City', 'openkaarten-base' ),
			'country'   => __( 'Country', 'openkaarten-base' ),
			'latitude'  => __( 'Latitude', 'openkaarten-base' ),
			'longitude' => __( 'Longitude', 'openkaarten-base' ),
		];

		foreach ( $address_fields as $field_key => $field ) {
			// Check if this field has a value and set it as readonly and disabled if it has.
			$field_value = get_post_meta( self::$object_id, 'field_geo_' . $field_key, true );
			$attributes  = [];
			if (
				! empty( $field_value )
				|| ( 'latitude' === $field_key )
				|| ( 'longitude' === $field_key )
			) {
				$attributes = [
			//		'readonly' => 'readonly',
				];
			} else {
				$attributes = [
					'data-conditional-id'    => $prefix . 'geodata_type',
					'data-conditional-value' => 'address',
				];
			}

			$cmb->add_field(
				[
					'name'         => $field,
					'id'           => 'field_geo_' . $field_key,
					'type'         => 'text',
					/* translators: %s: The field name. */
					'description'  => sprintf( __( 'The %s of the location.', 'openkaarten-base' ), strtolower( $field ) ),
					'show_in_rest' => true,
					'attributes'   => $attributes,
					'save_field'   => false,
				]
			);
		}
	}

	public static function cmb2_render_geomap_field_type( $field, $escaped_value, $object_id ) {
		// Get latitude and longitude of centre of the Netherlands as starting point.
		$center_lat  = 52.1326;
		$center_long = 5.2913;

		// Retrieve the current values of the latitude and longitude fields.
		$latitude  = get_post_meta( $object_id, 'field_geo_latitude', true );
		$longitude = get_post_meta( $object_id, 'field_geo_longitude', true );

		$set_marker = false;

		// If the latitude and longitude fields are filled, use these as the starting point.
		if ( ! empty( $latitude ) && ! empty( $longitude ) ) {
			$center_lat  = $latitude;
			$center_long = $longitude;
			$set_marker  = true;
		}

		// Enqueue the OpenStreetMap script.
		wp_localize_script(
			'owc_ok-openstreetmap',
			'leaflet_vars',
			[
				'centerLat'   => esc_attr( $center_lat ),
				'centerLong'  => esc_attr( $center_long ),
				'defaultZoom' => 10,
				'fitBounds'   => false,
				'allowClick'  => true,
				'setMarker'   => $set_marker,
			]
		);

		// Add the map and the hidden input field. This hidden input field is needed for the CMB2 Conditional Logic to work, but doesn't store any data itself.
		echo '<div id="map" class="map"></div>
		<input type="hidden" id="' . esc_attr( $field->args['id'] ) . '" name="' . esc_attr( $field->args['_name'] ) . '" data-conditional-id="location_geometry_geodata_type" data-conditional-value="marker">';
	}
}
