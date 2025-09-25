<?php
/**
 * The Openpub_Controller class.
 *
 * @package    Openkaarten_Geodata_Plugin
 * @subpackage Openkaarten_Geodata_Plugin/Rest_Api
 * @author     Acato <eyal@acato.nl>
 */

namespace Openkaarten_Geodata_Plugin\Rest_Api;

use geoPHP\Exception\IOException;
use geoPHP\geoPHP;
use Openkaarten_Geodata_Plugin\Admin\Helper;
use OWC\OpenPub\Base\Foundation\Plugin;

/**
 * The Openpub_Controller class.
 */
class Openpub_Controller extends \WP_REST_Posts_Controller {

	/**
	 * The singleton instance of this class.
	 *
	 * @access private
	 * @var    Openpub_Controller|null $instance The singleton instance of this class.
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
	 * @return Openpub_Controller The singleton instance of this class.
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new Openpub_Controller();
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
		if ( ! is_plugin_active( 'openpub-base/openpub-base.php' ) ) {
			return;
		}

		// Note: Below we make sure that we don't call the constructor before the OpenPub plugin registered its post types.
		add_action( 'init', [ $this, 'init' ], 11 );

		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
		add_filter( 'rest_pre_serve_request', [ $this, 'rest_change_output_format' ], 10, 4 );
	}

	/**
	 * Initialize the controller.
	 *
	 * @return void
	 */
	public function init() {
		parent::__construct( 'openpub-item' );

		$this->namespace = 'owc/openkaarten/v1';
		$this->rest_base = 'openpub-items';
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

		register_rest_route(
			$this->namespace,
			$this->rest_base . '/(?P<output_format>[a-zA-Z0-9-]+)',
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
		// Check if output format exists in the processor types. If not, return an error.
		$output_format = $request['output_format'] ?? 'geojson';
		if ( ! empty( $output_format ) ) {
			$processor_types        = geoPHP::getAdapterMap();
			$processor_types_string = implode( ', ', array_keys( $processor_types ) );
			if ( ! isset( $processor_types[ $output_format ] ) ) {
				// translators: %s: List of valid output formats.
				return new \WP_Error( 'rest_post_invalid_output_format', sprintf( __( 'Invalid output format. Use one of the following output formats: %s', 'openkaarten-geodata' ), $processor_types_string ), [ 'status' => 404 ] );
			}
		}

		// Include OpenPub API config file and get the taxonomies for the plugin.
		$openpub_plugin_dir_path = plugin_dir_path( __DIR__ ) . '../../openpub-base/';
		$taxonomies_config_file  = $openpub_plugin_dir_path . 'config/taxonomies.php';

		// Check if file exists.
		if ( file_exists( $taxonomies_config_file ) ) {
			$taxonomies = require $taxonomies_config_file;
		} else {
			$taxonomies = [];
		}

		$connections_config_file = $openpub_plugin_dir_path . 'config/p2p_connections.php';

		// Check if file exists.
		if ( file_exists( $connections_config_file ) ) {
			$connections = require $connections_config_file;
		} else {
			$connections = [];
		}

		// Set the taxonomies for the OpenPub plugin.
		$open_pub_plugin = new Plugin( 'openpub' );
		$open_pub_plugin->config->set( 'taxonomies', $taxonomies );
		$open_pub_plugin->config->set( 'p2p_connections', $connections );
		self::$open_pub_plugin = $open_pub_plugin;

		// Retrieve the list of registered collection query parameters.
		$registered = $this->get_collection_params();
		$args       = [];

		// Ensure our per_page parameter overrides any provided posts_per_page filter.
		if ( isset( $registered['per_page'] ) ) {
			$args['posts_per_page'] = $request['per_page'];
		}

		// Set the post type to openpub-item and prepare the query.
		$args['post_type'] = 'openpub-item';

		// Check if OpenPub item has geometry data and geometry is not empty.
		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- We need to check for the existence of the geometry field.
		$args['meta_query'] = [
			[
				'key'     => 'geometry',
				'compare' => 'EXISTS',
			],
			[
				'key'     => 'geometry',
				'compare' => '!=',
				'value'   => '',
			],
		];

		$query_args = $this->prepare_items_query( $args, $request );

		$posts_query  = new \WP_Query();
		$query_result = $posts_query->query( $query_args );

		// Retrieve the posts and prepare them for the response.
		$posts = [];
		foreach ( $query_result as $post ) {
			$posts[] = $this->prepare_item_for_response( $post, $request );
		}

		// Create response in GeoJSON format.
		$response = [
			'type'     => 'FeatureCollection',
			'features' => $posts,
		];

		$response = wp_json_encode( $response );

		// Parse the response to the output format.
		try {
			$geom   = geoPHP::load( $response );
			$output = $geom->out( $output_format );

		} catch ( IOException $e ) {
			$output = [];
		}

		return rest_ensure_response( $output );
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
		// Get the geometry for the item and add it to the output.
		$geometry    = get_post_meta( $item->ID, 'geometry', true );
		$item_output = json_decode( $geometry, true );

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

	/**
	 * Changes the output format of the REST API response.
	 *
	 * @param bool              $served Whether the request has already been served.
	 * @param \WP_REST_Response $result The response object.
	 * @param \WP_REST_Request  $request The request object.
	 * @param \WP_REST_Server   $server The server instance.
	 *
	 * @return bool Whether the request has already been served.
	 */
	public static function rest_change_output_format( $served, $result, $request, $server ) {
		// Bail if the result is not an instance of WP_REST_Response.
		if ( ! $result instanceof \WP_REST_Response ) {
			return $served;
		}

		$request_route = $request->get_route();

		// Check if the request route is the OpenPub items route.
		if ( false === strpos( $request_route, 'owc/openkaarten/v1/openpub-items' ) ) {
			return $served;
		}

		$output_format = $request['output_format'] ?? 'geojson';

		// Check if output format exists in the processor types. If not, return an error.
		$processor_types = geoPHP::getAdapterMap();
		if ( ! isset( $processor_types[ $output_format ] ) ) {
			return false;
		}

		// Different output for json and geojson, otherwise it outputs with backslashes.
		if ( in_array( $output_format, [ 'json', 'geojson' ], true ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- The output is escaped in the WP_REST_Response class.
			echo $result->get_data();
			exit();
		}

		// Change the output headers for specific output formats.
		$server->send_header( 'Content-Type', 'application/' . $output_format );
		$server->send_header( 'Content-Disposition', 'attachment; filename=' . $request['id'] . '.' . $output_format );

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- The output is escaped in the WP_REST_Response class.
		echo $result->get_data();
		exit();
	}
}
