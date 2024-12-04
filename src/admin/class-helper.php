<?php
/**
 * Helper class with several functions.
 *
 * @package    Openkaarten_Geodata_Plugin
 * @subpackage Openkaarten_Geodata_Plugin/Admin
 * @author     Eyal Beker <eyal@acato.nl>
 */

namespace Openkaarten_Geodata_Plugin\Admin;

use OWC\OpenPub\Base\Foundation\Plugin;

/**
 * Helper class with several functions.
 */
class Helper {

	/**
	 * Get base fields for the REST API.
	 *
	 * @param \WP_Post $post The post object.
	 *
	 * @return array
	 */
	public static function get_base_fields_for_rest_api( $post ) {
		$data = [
			'id'          => $post->ID,
			'title'       => $post->post_title,
			//phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- This is a WordPress core function.
			'content'     => apply_filters( 'the_content', $post->post_content ),
			'excerpt'     => $post->post_excerpt,
			'date'        => $post->post_date,
			'slug'        => $post->post_name,
			'post_status' => $post->post_status,
		];

		return $data;
	}

	/**
	 * Get all CMB2 fields for the REST API.
	 *
	 * @param \WP_Post $item            The post object.
	 * @param Plugin   $open_pub_plugin The OpenPub plugin instance.
	 *
	 * @return array
	 */
	public static function get_cmb2_fields_for_rest_api( $item, $open_pub_plugin ) {
		// Include OpenPub API config file.
		$openpub_plugin_dir_path = plugin_dir_path( __DIR__ ) . '../../openpub-base/';
		$api_config_file         = $openpub_plugin_dir_path . 'config/api.php';

		// Check if file exists.
		if ( ! file_exists( $api_config_file ) ) {
			return [];
		}

		$api_config = require $api_config_file;

		if ( ! is_array( $api_config ) || ! array_key_exists( 'models', $api_config ) ) {
			return [];
		}

		$fields = [];

		foreach ( $api_config['models'] as $model ) {
			if ( ! array_key_exists( 'fields', $model ) ) {
				continue;
			}

			$fields = array_merge( $fields, $model['fields'] );
		}

		// Exclude fields that should not be shown in the REST API, because they are also not in the OpenPub API endpoint.
		$excluded_cmb2_fields = [ 'comments', 'portal_url', 'escape_element', 'seopress', 'yoast' ];

		$item_fields = [];

		foreach ( $fields as $field_key => $field ) {
			// Skip excluded fields.
			if ( in_array( $field_key, $excluded_cmb2_fields, true ) ) {
				continue;
			}

			// Create output based on OpenPub Class.
			if ( class_exists( $field ) ) {
				$openpub_field_class       = new $field( $open_pub_plugin );
				$item_fields[ $field_key ] = $openpub_field_class->create( $item );
			}
		}

		return $item_fields;
	}
}
