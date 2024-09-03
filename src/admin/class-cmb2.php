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

		$openkaarten_post_types = get_option( 'openkaarten_post_types' );

		$cmb = new_cmb2_box(
			[
				'id'           => $prefix . 'metabox',
				'title'        => __( 'Location geometry object', 'openkaarten-base' ),
				'object_types' => $openkaarten_post_types,
				'context'      => 'normal',
				'priority'     => 'low',
				'show_names'   => true,
				'cmb_styles'   => true,
				'show_in_rest' => true,
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
					'readonly' => 'readonly',
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
}
