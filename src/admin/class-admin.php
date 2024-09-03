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
		add_action( 'admin_notices', ['Openkaarten_Geodata_Plugin\Admin\Admin', 'admin_notices' ] );
		add_action( 'admin_init', ['Openkaarten_Geodata_Plugin\Admin\Admin', 'check_plugin_dependency' ] );
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
			( ! is_plugin_active( 'cmb2/init.php' ) || ! is_plugin_active( 'plugin-openkaarten-base/openkaarten-base.php' ) )
			&& is_plugin_active( 'plugin-openkaarten-geodata-for-posts/plugin-openkaarten-geodata-for-posts.php' )
		) {
			set_transient( 'ok_geo_transient', __( 'The plugin OpenKaarten Geodata for Posts requires OpenKaarten Base and CMB2 plugin to be installed and activated. The plugin has been deactivated.', 'openkaarten-base' ), 100 );
			deactivate_plugins( 'plugin-openkaarten-geodata-for-posts/plugin-openkaarten-geodata-for-posts.php' );
		} else {
			delete_transient( 'ok_geo_transient' );
		}
	}
}
