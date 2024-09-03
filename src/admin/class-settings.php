<?php
/**
 * Helper class for Settings
 *
 * @package    Openkaarten_Geodata_Plugin
 * @subpackage Openkaarten_Geodata_Plugin/Admin
 * @author     Acato <eyal@acato.nl>
 */

namespace Openkaarten_Geodata_Plugin\Admin;

/**
 * Helper class for Settings
 */
class Settings {

	/**
	 * The singleton instance of this class.
	 *
	 * @access private
	 * @var    Settings|null $instance The singleton instance of this class.
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance of this class.
	 *
	 * @return Settings The singleton instance of this class.
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new Settings();
		}

		return self::$instance;
	}

	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct() {
		add_action( 'admin_menu', ['Openkaarten_Geodata_Plugin\Admin\Settings', 'add_admin_menu' ] );
		add_action( 'admin_init', ['Openkaarten_Geodata_Plugin\Admin\Settings', 'register_plugin_settings' ], 10 );

		// Add the CMB2 fields to the selected post types.
		add_filter( 'openkaarten_base_post_types', ['Openkaarten_Geodata_Plugin\Admin\Settings', 'openkaarten_post_types' ], 5, 0 );

		// Call save function for all selected post types.
		$openkaarten_post_types = self::openkaarten_post_types();
		if ( ! empty( $openkaarten_post_types ) ) {
			foreach ( $openkaarten_post_types as $post_type ) {
				// Exclude default post type from save function, because it's already triggered by the CMB2 plugin.
				if ( 'owc_ok_location' === $post_type ) {
					continue;
				}

				add_action( 'save_post_' . $post_type, [ 'Openkaarten_Base_Plugin\Admin\Locations', 'save_address_object' ], 15, 1 );
				add_action( 'save_post_' . $post_type, [ 'Openkaarten_Base_Plugin\Admin\Locations', 'save_geometry_object' ], 20, 1 );
			}
		}
	}

	/**
	 * This function is used to create the settings page
	 *
	 * @return  void
	 */
	public static function add_admin_menu() {
		add_options_page(
			__( 'OpenKaarten Settings', 'openkaarten-geodata' ),
			__( 'OpenKaarten Settings', 'openkaarten-geodata' ),
			'manage_options',
			'openkaarten-settings',
			['Openkaarten_Geodata_Plugin\Admin\Settings', 'settings_page' ]
		);
	}

	/**
	 * This function is used to create the settings group
	 *
	 * @return  void
	 */
	public static function register_plugin_settings() {
		register_setting( 'openkaarten-settings-group', 'openkaarten_post_types' );
	}

	/**
	 * This function add the html for the settings page
	 *
	 * @return  void
	 */
	public static function settings_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'OpenKaarten Settings', 'openkaarten-geodata' ); ?></h1>
			<form method="post" action="options.php">
				<?php settings_fields( 'openkaarten-settings-group' ); ?>
				<?php do_settings_sections( 'openkaarten-settings-group' ); ?>

				<p><label for="openkaarten_post_types"><strong><?php esc_html_e( 'Post types', 'openkaarten-geodata' ); ?></strong></label><br />
					<?php esc_html_e( 'Select one or multiple post types to use the OpenKaarten module in.', 'openkaarten-base' ); ?><br />
					<?php esc_html_e( 'The Locations post type is automatically included, since this is the base for the OpenKaarten plugin.', 'openkaarten-geodata' ); ?>
				</p>

				<?php
				$openkaarten_selected_post_types = self::openkaarten_post_types();
				if ( ! is_array( $openkaarten_selected_post_types ) ) {
					$openkaarten_selected_post_types = [ $openkaarten_selected_post_types ];
				}
				?>

				<select multiple name="openkaarten_post_types[]" size="10" style="width: 250px;" id="openkaarten_post_types">
					<?php
					$post_types = get_post_types( [ 'public' => true ], 'objects' );
					foreach ( $post_types as $post_type ) {
						if ( 'owc_ok_location' === $post_type->name ) {
							continue;
						}
						$selected = in_array( $post_type->name, $openkaarten_selected_post_types, true ) ? ' selected' : '';
						echo '<option value="' . esc_attr( $post_type->name ) . '" ' . esc_attr( $selected ) . '>' . esc_html( $post_type->label ) . '</option>';
					}
					?>
				</select>
				<p><small><?php esc_html_e( 'Hold the CTRL or CMD key when clicking a post type for selecting multiple post types.', 'openkaarten-geodata' ); ?></small></p>

				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Get the selected post types for the OpenKaarten module.
	 *
	 * @return array
	 */
	public static function openkaarten_post_types() {
		$openkaarten_default_post_types  = [ 'owc_ok_location' ];
		$openkaarten_selected_post_types = get_option( 'openkaarten_post_types' );
		if ( ! is_array( $openkaarten_selected_post_types ) || empty( $openkaarten_selected_post_types ) ) {
			return $openkaarten_default_post_types;
		}

		return array_merge( $openkaarten_default_post_types, $openkaarten_selected_post_types );
	}
}
