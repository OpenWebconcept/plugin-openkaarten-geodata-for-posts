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

				add_action( 'save_post_' . $post_type, [ $this, 'save_geodata' ], 200, 1 );
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
		// Only include the script on the kaarten edit pages.
		$screen = get_current_screen();

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
	 * Save the address object.
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return void
	 */
	public static function save_geodata( $post_id ) {
		// Check if this is an auto-save or if the user doesn't have permission to edit
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Check if this is a revision, if so, abort
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		// Check nonce.
		// phpcs:ignore WordPress.Security.NonceVerification -- Disable nonce for now, because not working with Gutenberg.
		// phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedIf -- Disable nonce for now, because not working with Gutenberg.
		if ( ! isset( $_POST['openkaarten_cmb2_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['openkaarten_cmb2_nonce'] ) ), 'openkaarten_cmb2_nonce' ) ) {
			// return;.
		}

		// Retrieve the latitude and longitude by address.
		if ( isset( $_POST['location_geometry_geodata_type'] ) ) {

			switch ( sanitize_text_field( wp_unslash( $_POST['location_geometry_geodata_type'] ) ) ) {
				case 'address':
					$address = sanitize_text_field( wp_unslash( $_POST['field_geo_address'] ) );
					$zipcode = sanitize_text_field( wp_unslash( $_POST['field_geo_zipcode'] ) );
					$city    = sanitize_text_field( wp_unslash( $_POST['field_geo_city'] ) );
					$country = sanitize_text_field( wp_unslash( $_POST['field_geo_country'] ) );

					$address .= ' ' . $zipcode . ' ' . $city . ' ' . $country;

					$lat_long = self::convert_address_to_latlong( sanitize_text_field( wp_unslash( $address ) ) );
					if ( ! empty( $lat_long['latitude'] ) && ! empty( $lat_long['longitude'] ) ) {
						$latitude  = sanitize_text_field( wp_unslash( $lat_long['latitude'] ) );
						$longitude = sanitize_text_field( wp_unslash( $lat_long['longitude'] ) );
					}
					break;
				case 'marker':
					$latitude  = sanitize_text_field( wp_unslash( $_POST['field_geo_latitude'] ) );
					$longitude = sanitize_text_field( wp_unslash( $_POST['field_geo_longitude'] ) );
					break;
			}
		}

		if ( ! empty( $latitude ) && ! empty( $longitude ) ) {
			update_post_meta( $post_id, 'field_geo_latitude', wp_slash( $latitude ) );
			update_post_meta( $post_id, 'field_geo_longitude', wp_slash( $longitude ) );
		}
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
		if ( ! isset( $_POST['openkaarten_cmb2_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['openkaarten_cmb2_nonce'] ) ), 'openkaarten_cmb2_nonce' ) ) {
			return;
		}

		// Make the geometry object.
		$geometry = [];
		if ( isset( $_POST['field_geo_latitude'] ) && isset( $_POST['field_geo_longitude'] ) ) {
			$latitude  = sanitize_text_field( wp_unslash( $_POST['field_geo_latitude'] ) );
			$longitude = sanitize_text_field( wp_unslash( $_POST['field_geo_longitude'] ) );
			$geometry  = [
				'type'        => 'Point',
				'coordinates' => [ (float) $longitude, (float) $latitude ],
			];
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
