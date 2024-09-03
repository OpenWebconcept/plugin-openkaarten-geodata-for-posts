<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the public-facing side of the site and
 * the admin area.
 *
 * @link       https://www.openwebconcept.nl
 *
 * @package    Openkaarten_Geodata_Plugin
 */

namespace Openkaarten_Geodata_Plugin;

use Openkaarten_Geodata_Plugin\Admin\Cmb2;
use Openkaarten_Geodata_Plugin\Admin\Settings;
use Openkaarten_Geodata_Plugin\Admin\Admin;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and public-facing site hooks.
 *
 * @package    Openkaarten_Geodata_Plugin
 * @author     Acato <eyal@acato.nl>
 */
class Plugin {
	/**
	 * Define the core functionality of the plugin.
	 */
	public function __construct() {
		/**
		 * Enable internationalization.
		 */
		I18n::get_instance();

		/**
		 * Register admin specific functionality.
		 */
		Admin::get_instance();
		Cmb2::get_instance();
		Settings::get_instance();

		/**
		 * Register REST API specific functionality.
		 */
		Rest_Api\Openkaarten_Geodata_Controller::get_instance();
	}
}
