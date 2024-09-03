<?php
/**
 * The Openkaarten_Geodata_Controller class.
 *
 * @package    Openkaarten_Geodata_Plugin
 * @subpackage Openkaarten_Geodata_Plugin/Rest_Api
 * @author     Acato <eyal@acato.nl>
 */

namespace Openkaarten_Geodata_Plugin\Rest_Api;

use WP_Post;
use WP_REST_Request;
use WP_REST_Response;

/**
 * The Openkaarten_Controller class.
 */
class Openkaarten_Geodata_Controller extends \WP_REST_Posts_Controller {

	/**
	 * The singleton instance of this class.
	 *
	 * @access private
	 * @var    Openkaarten_Geodata_Controller|null $instance The singleton instance of this class.
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance of this class.
	 *
	 * @return Openkaarten_Geodata_Controller The singleton instance of this class.
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new Openkaarten_Geodata_Controller();
		}

		return self::$instance;
	}

	/**
	 * Initialize the class and set its properties.
	 *
	 * @return void
	 */
	private function __construct() {
		add_action( 'init', [ $this, 'init' ] );
	}

	/**
	 * Initialize the controller.
	 *
	 * @return void
	 */
	public function init() {
		// Retrieve all post types that have a geodata field.
		$openkaarten_post_types = get_option( 'openkaarten_post_types' );

		if ( ! empty( $openkaarten_post_types ) ) {
			foreach ( $openkaarten_post_types as $post_type ) {
				// Add the geodata field to the REST API response.
				add_filter( 'rest_prepare_' . $post_type, [ $this, 'add_geodata_to_rest_api' ], 10, 3 );
			}
		}

		// Create custom filter to add geodata to the REST API response in a custom REST endpoint.
		add_filter( 'ok_geo_rest_add_geodata', [ $this, 'add_geodata_to_custom_endpoint' ], 10, 2 );
	}

	/**
	 * Add the geodata to the REST API response.
	 *
	 * @param WP_REST_Response $response The response object.
	 * @param WP_Post          $post     The post object.
	 * @param WP_REST_Request  $request  The request object.
	 */
	public function add_geodata_to_rest_api( $response, $post, $request ) {
		// Add the geometry object to the REST API response.
		$geodata = get_post_meta( $post->ID, 'geometry', true );

		if ( empty( $geodata ) ) {
			return $response;
		}

		$geodata = json_decode( $geodata );

		if ( isset( $geodata->geometry ) ) {
			$response->data['geometry'] = $geodata->geometry;
		}

		return $response;
	}

	/**
	 * Add the geodata to the custom REST API response.
	 *
	 * @param array|object $data The data object.
	 * @param WP_Post      $post The WP_Post object.
	 */
	public function add_geodata_to_custom_endpoint( $data, $post ) {
		// Check if post is a valid WP_Post object.
		if ( ! $post instanceof WP_Post ) {
			return $data;
		}

		// Check if data is not empty.
		if ( empty( $data ) ) {
			return $data;
		}

		$post_id = $post->ID;

		// Check if post type is configured to have geodata.
		$openkaarten_post_types = get_option( 'openkaarten_post_types' );

		if ( ! in_array( $post->post_type, $openkaarten_post_types, true ) ) {
			return $data;
		}

		// Add the geometry object to the REST API response.
		$geodata = get_post_meta( $post_id, 'geometry', true );

		if ( empty( $geodata ) ) {
			return $data;
		}

		$geodata = json_decode( $geodata );

		// Add the geometry object to the data object.
		if ( isset( $geodata->geometry ) ) {

			// Check if the data object is an array or an object.
			if ( is_array( $data ) ) {
				$data['geometry'] = $geodata->geometry;
			} elseif ( is_object( $data ) ) {
				$data->geometry = $geodata->geometry;
			}
		}

		return $data;
	}
}
