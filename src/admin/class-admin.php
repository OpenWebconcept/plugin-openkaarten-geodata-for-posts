<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.openwebconcept.nl
 *
 * @package    Openkaarten_Geodata_Plugin
 * @subpackage Openkaarten_Geodata_Plugin/Admin
 */

namespace Openkaarten_Geodata_Plugin\Admin;

use Openkaarten_Base_Functions\Openkaarten_Base_Functions;

/**
 * The admin-specific functionality of the plugin.
 *
 * @package    Openkaarten_Geodata_Plugin
 * @subpackage Openkaarten_Geodata_Plugin/Admin
 * @author     Acato <eyal@acato.nl>
 */
class Admin {

	/**
	 * The singleton instance of this class.
	 *
	 * @access private
	 * @var    Admin|null $instance The singleton instance of this class.
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance of this class.
	 *
	 * @return Admin The singleton instance of this class.
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new Admin();
		}

		return self::$instance;
	}
	/**
	 * Initialize the class and set its properties.
	 *
	 * @return void
	 */
	private function __construct() {
		add_action( 'admin_notices', array( 'Openkaarten_Geodata_Plugin\Admin\Admin', 'admin_notices' ) );
		add_action( 'admin_init', array( 'Openkaarten_Geodata_Plugin\Admin\Admin', 'check_plugin_dependency' ) );
		add_action( 'admin_enqueue_scripts', array( 'Openkaarten_Geodata_Plugin\Admin\Admin', 'admin_enqueue_scripts' ) );

		// Add the CMB2 fields to the selected post types.
		add_filter( 'openkaarten_base_post_types', array( 'Openkaarten_Geodata_Plugin\Admin\Settings', 'openkaarten_geodata_post_types' ), 5, 0 );

		// Call save function for all selected post types.
		$openkaarten_geodata_post_types = Settings::openkaarten_geodata_post_types();
		if ( ! empty( $openkaarten_geodata_post_types ) ) {
			foreach ( $openkaarten_geodata_post_types as $post_type ) {
				// Exclude default post type from save function, because it's already triggered by the CMB2 plugin.
				if ( 'owc_ok_location' === $post_type ) {
					continue;
				}

				add_action( 'save_post_' . $post_type, array( 'Openkaarten_Base_Functions\Openkaarten_Base_Functions', 'save_geometry_object' ), 20, 1 );
			}
		}
	}

	/**
	 * Show admin notices
	 *
	 * @return void
	 */
	public static function admin_notices() {
		$error_message = get_transient( 'ok_geo_transient' );

		if ( $error_message ) {
			echo "<div class='error'><p>" . esc_html( $error_message ) . '</p></div>';
		}
	}

	/**
	 * Check if CMB2 plugin is installed and activated
	 *
	 * @return void
	 */
	public static function check_plugin_dependency() {
		if (
			( ! is_plugin_active( 'cmb2/init.php' ) )
			&& is_plugin_active( 'plugin-openkaarten-geodata-for-posts/plugin-openkaarten-geodata-for-posts.php' )
		) {
			set_transient( 'ok_geo_transient', __( 'The plugin OpenKaarten Geodata for Posts requires CMB2 plugin to be installed and activated. The plugin has been deactivated.', 'openkaarten-geodata' ), 100 );
			deactivate_plugins( 'plugin-openkaarten-geodata-for-posts/plugin-openkaarten-geodata-for-posts.php' );
		} else {
			delete_transient( 'ok_geo_transient' );
		}
	}

	/**
	 * Enqueue scripts and styles
	 *
	 * @return void
	 */
	public static function admin_enqueue_scripts() {

		// Include custom.js file.
		wp_enqueue_script(
			'owc_ok_geodata_custom',
			plugin_dir_url( __FILE__ ) . 'js/custom.js',
			array( 'jquery', 'cmb2-scripts' ),
			filemtime( plugin_dir_path( __FILE__ ) . 'js/custom.js' ),
			true
		);
	}
}
