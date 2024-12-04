<?php
/**
 * Helper class for Settings
 *
 * @package    Openkaarten_Geodata_Plugin
 * @subpackage Openkaarten_Geodata_Plugin/Admin
 * @author     Acato <eyal@acato.nl>
 */

namespace Openkaarten_Geodata_Plugin\Admin;

/**
 * Helper class for Settings
 */
class Settings {

	/**
	 * The singleton instance of this class.
	 *
	 * @access private
	 * @var    Settings|null $instance The singleton instance of this class.
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance of this class.
	 *
	 * @return Settings The singleton instance of this class.
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new Settings();
		}

		return self::$instance;
	}

	/**
	 * Get the selected post types for the OpenKaarten module.
	 *
	 * @return array
	 */
	public static function openkaarten_geodata_post_types() {
		return [ 'owc_ok_location', 'openpub-item' ];
	}
}
