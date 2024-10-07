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
	}

	/**
	 * This function is used to create the settings page
	 *
	 * @return  void
	 */
	public static function add_admin_menu() {
		add_options_page(
			__( 'OpenKaarten Geodata Settings', 'openkaarten-geodata' ),
			__( 'OpenKaarten Geodata Settings', 'openkaarten-geodata' ),
			'manage_options',
			'openkaarten-geodata-settings',
			['Openkaarten_Geodata_Plugin\Admin\Settings', 'settings_page' ]
		);
	}

	/**
	 * This function is used to create the settings group
	 *
	 * @return  void
	 */
	public static function register_plugin_settings() {
		register_setting( 'openkaarten-geodata-settings-group', 'openkaarten_geodata_post_types' );
	}

	/**
	 * This function add the html for the settings page
	 *
	 * @return  void
	 */
	public static function settings_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'OpenKaarten Geodata Settings', 'openkaarten-geodata' ); ?></h1>
			<form method="post" action="options.php">
				<?php settings_fields( 'openkaarten-geodata-settings-group' ); ?>
				<?php do_settings_sections( 'openkaarten-geodata-settings-group' ); ?>

				<p><label for="openkaarten_geodata_post_types"><strong><?php esc_html_e( 'Post types', 'openkaarten-geodata' ); ?></strong></label><br />
					<?php esc_html_e( 'Select one or multiple post types to use the OpenKaarten module in.', 'openkaarten-geodata' ); ?><br />
					<?php
					// Check if the OpenKaarten Base plugin is installed. If so, show the default post type.
					if ( is_plugin_active( 'plugin-openkaarten-base/openkaarten-base.php' ) ) {
						esc_html_e( 'The OpenKaarten Locations post type is automatically included, since this is the base for the OpenKaarten plugin.', 'openkaarten-geodata' );
					}
					?>
				</p>

				<?php
				$openkaarten_selected_post_types = self::openkaarten_geodata_post_types();
				if ( ! is_array( $openkaarten_selected_post_types ) ) {
					$openkaarten_selected_post_types = [ $openkaarten_selected_post_types ];
				}
				?>

				<select multiple name="openkaarten_geodata_post_types[]" size="10" style="width: 250px;" id="openkaarten_geodata_post_types">
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
	public static function openkaarten_geodata_post_types() {
		$openkaarten_default_post_types  = [ 'owc_ok_location' ];
		$openkaarten_selected_post_types = get_option( 'openkaarten_geodata_post_types' );
		if ( ! is_array( $openkaarten_selected_post_types ) || empty( $openkaarten_selected_post_types ) ) {
			return $openkaarten_default_post_types;
		}

		return array_merge( $openkaarten_default_post_types, $openkaarten_selected_post_types );
	}
}
