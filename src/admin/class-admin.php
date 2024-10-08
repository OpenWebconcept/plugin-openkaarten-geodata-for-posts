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
		add_action( 'admin_enqueue_scripts', [ 'Openkaarten_Geodata_Plugin\Admin\Admin', 'admin_enqueue_scripts' ] );

		// Add the CMB2 fields to the selected post types.
		add_filter( 'openkaarten_base_post_types', ['Openkaarten_Geodata_Plugin\Admin\Settings', 'openkaarten_geodata_post_types' ], 5, 0 );

		// Call save function for all selected post types.
		$openkaarten_geodata_post_types = Settings::openkaarten_geodata_post_types();
		if ( ! empty( $openkaarten_geodata_post_types ) ) {
			foreach ( $openkaarten_geodata_post_types as $post_type ) {
				// Exclude default post type from save function, because it's already triggered by the CMB2 plugin.
				if ( 'owc_ok_location' === $post_type ) {
					continue;
				}

				add_action( 'save_post_' . $post_type, [ $this, 'save_geometry_object' ], 20, 1 );
			}
		}
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
			( ! is_plugin_active( 'cmb2/init.php' ) )
			&& is_plugin_active( 'plugin-openkaarten-geodata-for-posts/plugin-openkaarten-geodata-for-posts.php' )
		) {
			set_transient( 'ok_geo_transient', __( 'The plugin OpenKaarten Geodata for Posts requires CMB2 plugin to be installed and activated. The plugin has been deactivated.', 'openkaarten-geodata' ), 100 );
			deactivate_plugins( 'plugin-openkaarten-geodata-for-posts/plugin-openkaarten-geodata-for-posts.php' );
		} else {
			delete_transient( 'ok_geo_transient' );
		}
	}

	/**
	 * Enqueue scripts and styles
	 *
	 * @return void
	 */
	public static function admin_enqueue_scripts() {

		// Include custom.js file.
		wp_enqueue_script(
			'owc_ok_custom',
			plugin_dir_url( __FILE__ ) . 'js/custom.js',
			[ 'jquery', 'cmb2-scripts' ],
			filemtime( plugin_dir_path( __FILE__ ) . 'js/custom.js' ),
			true
		);

		wp_enqueue_script(
			'cmb2-conditional-logic',
			plugin_dir_url( __FILE__ ) . 'js/cmb2-conditional-logic.js',
			[ 'jquery', 'cmb2-scripts' ],
			filemtime( plugin_dir_path( __FILE__ ) . 'js/cmb2-conditional-logic.js' ),
			true
		);

		wp_enqueue_style(
			'owc_ok-font-awesome',
			'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css',
			[],
			OWC_OPENKAARTEN_BASE_VERSION
		);

		wp_enqueue_style(
			'owc_ok-openstreetmap',
			self::mix( '/styles/openstreetmap.css' ),
			[],
			OWC_OPENKAARTEN_BASE_VERSION
		);

		wp_enqueue_script(
			'owc_ok-openstreetmap',
			self::mix( '/scripts/openstreetmap.js' ),
			[],
			OWC_OPENKAARTEN_BASE_VERSION,
			true
		);
	}

	/**
	 * Just a little helper to get filenames from the mix-manifest file.
	 *
	 * @param string $path to file.
	 *
	 * @return string|null
	 */
	private static function mix( string $path ): ?string {
		static $manifest;
		if ( empty( $manifest ) ) {
			$manifest = OWC_OPENKAARTEN_GEODATA_ABSPATH . '/build/mix-manifest.json';

			if ( ! self::has_resource( $manifest ) ) {
				return OWC_OPENKAARTEN_GEODATA_ASSETS_URL . $path;
			}

			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- We need to read the file.
			$manifest = json_decode( file_get_contents( $manifest ), true );
		}

		// We need to set the `/` in front of the `$path` due to how the mix-manifest.json file is saved.
		if ( ! str_starts_with( $path, '/' ) ) {
			$path = '/' . $path;
		}

		return ! empty( $manifest[ $path ] ) ? untrailingslashit( OWC_OPENKAARTEN_GEODATA_ASSETS_URL ) . $manifest[ $path ] : null;
	}

	/**
	 * Checks if file exists and if the file is populated, so we don't enqueue empty files.
	 *
	 * @param string $path ABSPATH to file.
	 *
	 * @return bool|mixed
	 */
	private static function has_resource( $path ) {

		static $resources = null;

		if ( isset( $resources[ $path ] ) ) {
			return $resources[ $path ];
		}

		// Check if resource exists and has content.
		// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		$resources[ $path ] = @file_exists( $path ) && 0 < (int) @filesize( $path );

		return $resources[ $path ];
	}

	/**
	 * Save the location geometry object.
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return void
	 */
	public static function save_geometry_object( $post_id ) {
		if ( wp_is_post_autosave( $post_id ) ) {
			return;
		}

		// Check nonce.
		if ( ! isset( $_POST['nonce_CMB2phplocation_geometry_metabox'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce_CMB2phplocation_geometry_metabox'] ) ), 'nonce_CMB2phplocation_geometry_metabox' ) ) {
			return;
		}

		// Retrieve the latitude and longitude by address.
		if ( isset( $_POST['location_geometry_geodata_type'] ) ) {

			switch ( sanitize_text_field( wp_unslash( $_POST['location_geometry_geodata_type'] ) ) ) {
				case 'address':
					$address = sanitize_text_field( wp_unslash( $_POST['field_geo_address'] ) );
					$zipcode = sanitize_text_field( wp_unslash( $_POST['field_geo_zipcode'] ) );
					$city    = sanitize_text_field( wp_unslash( $_POST['field_geo_city'] ) );
					$country = sanitize_text_field( wp_unslash( $_POST['field_geo_country'] ) );

					// Update post meta data.
					update_post_meta( $post_id, 'field_geo_address', wp_slash( $address ) );
					update_post_meta( $post_id, 'field_geo_zipcode', wp_slash( $zipcode ) );
					update_post_meta( $post_id, 'field_geo_city', wp_slash( $city ) );
					update_post_meta( $post_id, 'field_geo_country', wp_slash( $country ) );

					$address .= ' ' . $zipcode . ' ' . $city . ' ' . $country;

					$lat_long = self::convert_address_to_latlong( sanitize_text_field( wp_unslash( $address ) ) );
					if ( ! empty( $lat_long['latitude'] ) && ! empty( $lat_long['longitude'] ) ) {
						$latitude  = sanitize_text_field( wp_unslash( $lat_long['latitude'] ) );
						$longitude = sanitize_text_field( wp_unslash( $lat_long['longitude'] ) );
					}

					$geometry_coordinates = [ (float) $longitude, (float) $latitude ];

					$geometry  = [
						'type'        => 'Point',
						'coordinates' => $geometry_coordinates,
					];
					break;
				case 'marker':
					// Check if there is a location_geometry_coordinates input.
					if ( ! isset( $_POST['location_geometry_coordinates'] ) ) {
						return;
					}

					// Check if the input has one or multiple markers in it.
					$marker_data = json_decode( stripslashes( $_POST['location_geometry_coordinates'] ), true );

					if ( ! $marker_data ) {
						return;
					}

					// Remove duplicates from the array where lat and lng are the same.
					$marker_data = array_map( 'unserialize', array_unique( array_map( 'serialize', $marker_data ) ) );

					// Make the geometry object based on the amount of markers.
					if ( 1 === count( $marker_data ) ) {
						$marker_data = $marker_data[0];
						$geometry  = [
							'type'        => 'Point',
							'coordinates' => [ (float) $marker_data['lng'], (float) $marker_data['lat'] ],
						];
					} else {
						$geometry_coordinates = [];
						foreach ( $marker_data as $marker ) {
							$geometry_coordinates[] = [ (float) $marker['lng'], (float) $marker['lat'] ];
						}

						$geometry  = [
							'type'        => 'MultiPoint',
							'coordinates' => $geometry_coordinates,
						];
					}

					// Delete the address fields.
					delete_post_meta( $post_id, 'field_geo_address' );
					delete_post_meta( $post_id, 'field_geo_zipcode' );
					delete_post_meta( $post_id, 'field_geo_city' );
					delete_post_meta( $post_id, 'field_geo_country' );

					break;
			}
		}

		$component = [
			'type'       => 'Feature',
			'properties' => [],
			'geometry'   => $geometry,
		];
		$component = wp_json_encode( $component );

		// Check if post meta exists and update or add the post meta.
		if ( metadata_exists( 'post', $post_id, 'geometry' ) ) {
			update_post_meta( $post_id, 'geometry', wp_slash( $component ) );
		} else {
			add_post_meta( $post_id, 'geometry', wp_slash( $component ), true );
		}
	}

	/**
	 * Get latitude and longitude from an address with OpenStreetMap.
	 *
	 * @param string $address The address.
	 *
	 * @return array|null
	 */
	public static function convert_address_to_latlong( $address ) {

		if ( ! $address ) {
			return null;
		}

		$address     = str_replace( ' ', '+', $address );
		$osm_url     = 'https://nominatim.openstreetmap.org/search?q=' . $address . '&format=json&addressdetails=1';
		$osm_address = wp_remote_get( $osm_url );

		if ( ! $osm_address ) {
			return null;
		}

		$osm_address = json_decode( $osm_address['body'] );

		if ( ! $osm_address[0]->lat || ! $osm_address[0]->lon ) {
			return null;
		}

		$latitude  = $osm_address[0]->lat;
		$longitude = $osm_address[0]->lon;

		return [
			'latitude'  => $latitude,
			'longitude' => $longitude,
		];
	}
}
