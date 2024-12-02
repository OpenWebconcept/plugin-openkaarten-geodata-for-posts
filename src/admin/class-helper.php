<?php
/**
 * Helper class with several functions.
 *
 * @package    Openkaarten_Geodata_Plugin
 * @subpackage Openkaarten_Geodata_Plugin/Admin
 * @author     Eyal Beker <eyal@acato.nl>
 */

namespace Openkaarten_Geodata_Plugin\Admin;

use CMB2_Boxes;

/**
 * Helper class with several functions.
 */
class Helper {

	/**
	 * The singleton instance of this class.
	 *
	 * @access private
	 * @var    Helper|null $instance The singleton instance of this class.
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance of this class.
	 *
	 * @return Helper The singleton instance of this class.
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new Helper();
		}

		return self::$instance;
	}

	/**
	 * Get all CMB2 fields for the REST API.
	 *
	 * @return array
	 */
	public static function get_cmb2_fields_for_rest_api() {
		return [
			'ID',
			'post_title',
			'post_content',
			'post_excerpt',
			'post_date',
			'post_name',
			'post_status',
			'comments',
			'connected',
			'portal_url',
			'date_modified',
			'date_modified_gmt',
			'downloads',
			'expired',
			'highlighted',
			'image',
			'links',
			'notes',
			'synonyms',
			'taxonomies',
			'escape_element',
			'seopress',
			'yoast',
		];
	}
}
