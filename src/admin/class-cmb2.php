<?php
/**
 * Helper class for CMB2
 *
 * @package    Openkaarten_Geodata_Plugin
 * @subpackage Openkaarten_Geodata_Plugin/Admin
 * @author     Acato <eyal@acato.nl>
 */

namespace Openkaarten_Geodata_Plugin\Admin;

use Openkaarten_Base_Functions\Openkaarten_Base_Functions;

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

		$openkaarten_geodata_post_types = Settings::openkaarten_geodata_post_types();
		Openkaarten_Base_Functions::cmb2_location_geometry_fields( $post_id, $openkaarten_geodata_post_types );
		self::$object_id = (int) $post_id;
	}
}
