<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and starts the plugin.
 *
 * @link              https://www.openwebconcept.nl
 * @package           Openkaarten_Geodata_Plugin
 *
 * @wordpress-plugin
 * Plugin Name:       OpenKaarten Geodata for Posts
 * Plugin URI:        https://www.openwebconcept.nl
 * Description:       The OpenKaarten Add-on to add geodata fields to posts.
 * Version:           0.1.0
 * Author:            Acato
 * Author URI:        https://www.acato.nl
 * License:           EUPL-1.2
 * License URI:       https://opensource.org/licenses/EUPL-1.2
 * Text Domain:       openkaarten-geodata
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'OWC_OPENKAARTEN_GEODATA_VERSION', '0.1.0' );

if ( ! defined( 'OWC_OPENKAARTEN_GEODATA_ABSPATH' ) ) {
	define( 'OWC_OPENKAARTEN_GEODATA_ABSPATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'OWC_OPENKAARTEN_GEODATA_ASSETS_URL' ) ) {
	define( 'OWC_OPENKAARTEN_GEODATA_ASSETS_URL', esc_url( trailingslashit( plugins_url( '', __FILE__ ) ) . 'build' ) );
}

// Load Composer autoloader if available.
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

require_once plugin_dir_path( __FILE__ ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'class-autoloader.php';
spl_autoload_register( array( '\Openkaarten_Geodata_Plugin\Autoloader', 'autoload' ) );
/**
 * Begins execution of the plugin.
 */
new \Openkaarten_Geodata_Plugin\Plugin();
