<?php
/**
 * The Openkaarten_Geodata_Controller_OpenPub class.
 *
 * @package    Openkaarten_Geodata_Plugin
 * @subpackage Openkaarten_Geodata_Plugin/Rest_Api
 * @author     Acato <eyal@acato.nl>
 */

namespace Openkaarten_Geodata_Plugin\Rest_Api;

use geoPHP\Exception\IOException;
use geoPHP\geoPHP;
use Openkaarten_Geodata_Plugin\Admin\Helper;

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
		// Retrieve the list of registered collection query parameters.
		$registered = $this->get_collection_params();
		$args       = [];

		// Ensure our per_page parameter overrides any provided posts_per_page filter.
		if ( isset( $registered['per_page'] ) ) {
			$args['posts_per_page'] = $request['per_page'];
		}

		$args = $this->prepare_tax_query( $args, $request );

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

		$response = rest_ensure_response( $response );

		return $response;
	}

	/**
	 * Prepares the 'tax_query' for a collection of posts.
	 *
	 * @param array            $args WP_Query arguments.
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return array Updated query arguments.
	 * @since 5.7.0
	 */
	public function prepare_tax_query( array $args, \WP_REST_Request $request ) {
		$relation = $request['tax_relation'];

		if ( $relation ) {
			// phpcs:ignore WordPress.DB.SlowDBQuery -- This is a valid query.
			$args['tax_query'] = [ 'relation' => $relation ];
		}

		$taxonomies = wp_list_filter(
			get_object_taxonomies( $this->post_type, 'objects' ),
			[ 'show_in_rest' => true ]
		);

		foreach ( $taxonomies as $taxonomy ) {
			$base = ! empty( $taxonomy->rest_base ) ? $taxonomy->rest_base : $taxonomy->name;

			$tax_include = $request[ $base ];
			$tax_exclude = $request[ $base . '_exclude' ];

			if ( $tax_include ) {
				$terms            = [];
				$include_children = false;
				$operator         = 'IN';

				if ( rest_is_array( $tax_include ) ) {
					$terms = $tax_include;
				} elseif ( rest_is_object( $tax_include ) ) {
					$terms            = empty( $tax_include['terms'] ) ? [] : $tax_include['terms'];
					$include_children = ! empty( $tax_include['include_children'] );

					if ( isset( $tax_include['operator'] ) && 'AND' === $tax_include['operator'] ) {
						$operator = 'AND';
					}
				}

				if ( $terms ) {
					$args['tax_query'][] = [
						'taxonomy'         => $taxonomy->name,
						'field'            => 'term_id',
						'terms'            => $terms,
						'include_children' => $include_children,
						'operator'         => $operator,
					];
				}
			}

			if ( $tax_exclude ) {
				$terms            = [];
				$include_children = false;

				if ( rest_is_array( $tax_exclude ) ) {
					$terms = $tax_exclude;
				} elseif ( rest_is_object( $tax_exclude ) ) {
					$terms            = empty( $tax_exclude['terms'] ) ? [] : $tax_exclude['terms'];
					$include_children = ! empty( $tax_exclude['include_children'] );
				}

				if ( $terms ) {
					$args['tax_query'][] = [
						'taxonomy'         => $taxonomy->name,
						'field'            => 'term_id',
						'terms'            => $terms,
						'include_children' => $include_children,
						'operator'         => 'NOT IN',
					];
				}
			}
		}

		return $args;
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

		// Get all properties for the item and add them to the output.
		$item_properties = [];
		foreach ( $item as $key => $value ) {
			$item_properties[ $key ] = $value;
		}

		// Get all taxonomies for the item and add them to the output.
		$taxonomies = get_object_taxonomies( $item->post_type );

		// Collect all taxonomies for the item.
		$item_properties['taxonomies'] = [];
		foreach ( $taxonomies as $taxonomy ) {
			$taxonomy_terms = wp_get_post_terms( $item->ID, $taxonomy );

			if ( ! empty( $taxonomy_terms ) ) {
				$item_properties['taxonomies'][ $taxonomy ] = [];
				foreach ( $taxonomy_terms as $term ) {
					$taxonomy_term['id']                          = $term->term_id;
					$taxonomy_term['name']                        = $term->name;
					$taxonomy_term['slug']                        = $term->slug;
					$item_properties['taxonomies'][ $taxonomy ][] = $taxonomy_term;
				}
			}
		}

		// Get the geometry for the item and add it to the output.
		$geometry = get_post_meta( $item->ID, 'geometry', true );

		if ( $geometry ) {
			$geometry                = geoPHP::load( $geometry );
			$item_output['geometry'] = $geometry->out( 'json' );
		}

		// Get the image for the item and add it to the output.
		$item_properties['image'] = $this->get_image_url( $item->ID );

		// Get custom cmb2 fields.
		$item_downloads = get_post_meta( $item->ID, '_owc_openpub_downloads_group', true );

		// Add downloads to item, with the correct field names.
		if ( ! empty( $item_downloads ) ) {
			foreach ( $item_downloads as $key => $value ) {
				$item_properties['downloads'][ $key ]['title'] = $value['openpub_downloads_title'];
				$item_properties['downloads'][ $key ]['url']   = $value['openpub_downloads_url'];
			}
		} else {
			$item_properties['downloads'] = [];
		}

		// Add expiration date to item and make it the right date format.
		$item_expired = get_post_meta( $item->ID, '_owc_openpub_expirationdate', true );

		if ( $item_expired ) {
			$item_properties['expired']['on'] = gmdate( 'Y-m-d H:i', $item_expired );
		} else {
			$item_properties['expired'] = false;
		}

		// Get the highlighted status for the item and add it to the output as a boolean.
		$item_highlighted = get_post_meta( $item->ID, '_owc_openpub_highlighted_item', true );

		if ( 'on' === $item_highlighted ) {
			$item_properties['highlighted'] = true;
		} else {
			$item_properties['highlighted'] = false;
		}

		// Get the links for the item and add them to the output with the correct field names.
		$item_links = get_post_meta( $item->ID, '_owc_openpub_links_group', true );

		if ( ! empty( $item_links ) ) {
			foreach ( $item_links as $key => $value ) {
				$item_properties['links'][ $key ]['title'] = $value['openpub_links_title'];
				$item_properties['links'][ $key ]['url']   = $value['openpub_links_url'];
			}
		} else {
			$item_properties['links'] = [];
		}

		// Get the notes and synonyms for the item and add them to the output.
		$item_properties['notes']    = get_post_meta( $item->ID, '_owc_openpub_notes', true );
		$item_properties['synonyms'] = get_post_meta( $item->ID, '_owc_openpub_tags', true );

		// Loop through all the allowed fields and only add them to the output.
		$allowed_fields = Helper::get_cmb2_fields_for_rest_api();

		if ( ! empty( $allowed_fields ) ) {
			foreach ( $allowed_fields as $field ) {
				foreach ( $item_properties as $key => $value ) {
					if ( $field === $key ) {
						$item_output['properties'][ $field ] = $value;
					}
				}
			}
		}

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
