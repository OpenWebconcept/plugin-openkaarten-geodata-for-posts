<?php
/**
 * The Openkaarten_Geodata_Controller_OpenPub class.
 *
 * @package    Openkaarten_Geodata_Plugin
 * @subpackage Openkaarten_Geodata_Plugin/Rest_Api
 * @author     Acato <eyal@acato.nl>
 */

namespace Openkaarten_Geodata_Plugin\Rest_Api;

use geoPHP\geoPHP;
use Openkaarten_Geodata_Plugin\Admin\Helper;
use OWC\OpenPub\Base\Foundation\Plugin;

/**
 * The Openkaarten_Controller class.
 */
class Openkaarten_Geodata_Controller_OpenPub extends \WP_REST_Posts_Controller {

	/**
	 * The singleton instance of this class.
	 *
	 * @access private
	 * @var    Openkaarten_Geodata_Controller_OpenPub|null $instance The singleton instance of this class.
	 */
	private static $instance = null;

	/**
	 * The OpenPub plugin instance.
	 *
	 * @access private
	 * @var    Plugin|null $open_pub_plugin The OpenPub plugin instance.
	 */
	private static $open_pub_plugin = null;

	/**
	 * Get the singleton instance of this class.
	 *
	 * @return Openkaarten_Geodata_Controller_OpenPub The singleton instance of this class.
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new Openkaarten_Geodata_Controller_OpenPub();
		}

		return self::$instance;
	}

	/**
	 * Initialize the class and set its properties.
	 *
	 * @return void
	 */
	private function __construct() {
		// Check if OpenPub plugin is installed. If not, return.
		if ( ! is_plugin_active( 'plugin-openpub-base/openpub-base.php' ) ) {
			return;
		}

		parent::__construct( 'openpub-item' );

		add_action( 'init', [ $this, 'init' ] );
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	/**
	 * Initialize the controller.
	 *
	 * @return void
	 */
	public function init() {
		parent::__construct( 'openpub-item' );

		$this->namespace = 'owc/openkaarten/v1';
		$this->rest_base = 'openpub-item';
	}

	/**
	 * Register the routes for the objects of the controller.
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			$this->rest_base,
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_items' ],
				'permission_callback' => [ $this, 'get_items_permissions_check' ],
				'args'                => $this->get_collection_params(),
			]
		);
	}

	/**
	 * Check if a given request has permission to read items.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return bool Whether the request has permission to read items.
	 */
	public function get_items_permissions_check( $request ) {
		return true;
	}

	/**
	 * Retrieves a collection of items.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_items( $request ) {
		// Include OpenPub API config file and get the taxonomies for the plugin.
		$openpub_plugin_dir_path = plugin_dir_path( __DIR__ ) . '../../plugin-openpub-base/';
		$taxonomies              = require $openpub_plugin_dir_path . 'config/taxonomies.php';

		// Set the taxonomies for the OpenPub plugin.
		$open_pub_plugin = new Plugin( 'openpub' );
		$open_pub_plugin->config->set( 'taxonomies', $taxonomies );
		self::$open_pub_plugin = $open_pub_plugin;

		// Retrieve the list of registered collection query parameters.
		$registered = $this->get_collection_params();
		$args       = [];

		// Ensure our per_page parameter overrides any provided posts_per_page filter.
		if ( isset( $registered['per_page'] ) ) {
			$args['posts_per_page'] = $request['per_page'];
		}

		$args['post_type'] = 'openpub-item';

		$query_args = $this->prepare_items_query( $args, $request );

		$posts_query  = new \WP_Query();
		$query_result = $posts_query->query( $query_args );

		$posts = [];
		foreach ( $query_result as $post ) {
			$posts[] = $this->prepare_item_for_response( $post, $request );
		}

		$response = [
			'type'     => 'FeatureCollection',
			'features' => $posts,
		];

		return rest_ensure_response( $response );
	}

	/**
	 * Prepares the query for the collection of items.
	 *
	 * @param \WP_POST         $item The post object.
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return array|string The item data.
	 */
	public function prepare_item_for_response( $item, $request ) {
		$item_output = [ 'type' => 'Feature' ];

		// Get the geometry for the item and add it to the output.
		$geometry = get_post_meta( $item->ID, 'geometry', true );

		// Check if the geometry is a valid geometry by using the geoPHP library.
		if ( $geometry ) {
			$geometry                = geoPHP::load( $geometry );
			$item_output['geometry'] = json_decode( $geometry->out( 'json' ) );
		} else {
			$item_output['geometry'] = null;
		}

		// Loop through all the allowed fields and only add them to the output.
		$base_properties = Helper::get_base_fields_for_rest_api( $item );
		$cmb2_properties = Helper::get_cmb2_fields_for_rest_api( $item, self::$open_pub_plugin );
		$item_properties = array_merge( $base_properties, $cmb2_properties );

		if ( ! empty( $item_properties ) ) {
			foreach ( $item_properties as $prop_key => $property ) {
				$item_output['properties'][ $prop_key ] = $property;
			}
		}

		// Override image field with the image URL.
		$item_output['properties']['image'] = $this->get_image_url( $item->ID );

		return $item_output;
	}

	/**
	 * Get the image URL for the item.
	 *
	 * @param int $item_id The post object ID.
	 *
	 * @return string The image URL.
	 */
	public function get_image_url( $item_id ) {
		// Get the image for the item, based on the large size.
		$image = get_the_post_thumbnail_url( $item_id, 'large' );

		if ( ! $image ) {
			return null;
		}

		return $image;
	}
}
