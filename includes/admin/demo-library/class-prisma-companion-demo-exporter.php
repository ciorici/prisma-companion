<?php
/**
 * Prisma Companion Demo Library. Install a copy of a Prisma Core demo to your website.
 *
 * @package Prisma Companion
 * @author  Prisma Core Team
 * @since   1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Prisma Companion Demo Exporter Class.
 *
 * @since 1.0.0
 * @package Prisma Companion
 */
final class Prisma_Companion_Demo_Exporter {

	/**
	 * Singleton instance of the class.
	 *
	 * @since 1.0.0
	 * @var object
	 */
	private static $instance;

	/**
	 * Demo ID.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $demo_id;

	/**
	 * Main Prisma Companion Demo Exporter Instance.
	 *
	 * @since 1.0.0
	 * @return Prisma_Companion_Demo_Exporter
	 */
	public static function instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Prisma_Companion_Demo_Exporter ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Add export listeners.
		add_action( 'init', array( $this, 'export' ) );
	}


	/**
	 * Export.
	 *
	 * @since 1.0.0
	 */
	public function export() {

		// Check if user has permission for this.
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			return;
		}

		// Export Customizer.
		if ( isset( $_REQUEST['prisma-companion-customizer-export'] ) ) { // phpcs:ignore

			if ( ! class_exists( 'Prisma_Companion_Customizer_Import_Export' ) ) {

				$class_customizer_import = plugin_dir_path( __FILE__ ) . 'importers/class-customizer-import-export.php';

				if ( file_exists( $class_customizer_import ) ) {
					require_once $class_customizer_import;

					Prisma_Companion_Customizer_Import_Export::export();
				}
			}
		}

		// Export Widgets.
		if ( isset( $_REQUEST['prisma-companion-widgets-export'] ) ) { // phpcs:ignore

			if ( ! class_exists( 'Prisma_Companion_Widgets_Import_Export' ) ) {

				$class_widgets_import = plugin_dir_path( __FILE__ ) . 'importers/class-widgets-import-export.php';

				if ( file_exists( $class_widgets_import ) ) {
					require_once $class_widgets_import;

					Prisma_Companion_Widgets_Import_Export::export();
				}
			}
		}

		// Export Options.
		if ( isset( $_REQUEST['prisma-companion-options-export'] ) ) { // phpcs:ignore

			if ( ! class_exists( 'Prisma_Companion_Options_Import_Export' ) ) {

				$class_options_import = plugin_dir_path( __FILE__ ) . 'importers/class-options-import-export.php';

				if ( file_exists( $class_options_import ) ) {
					require_once $class_options_import;

					Prisma_Companion_Options_Import_Export::export();
				}
			}
		}
	}
}

/**
 * The function which returns the one Prisma_Companion_Demo_Exporter instance.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $prisma_companion_demo_exporter = prisma_companion_demo_exporter(); ?>
 *
 * @since 1.0.0
 * @return object
 */
function prisma_companion_demo_exporter() {
	return Prisma_Companion_Demo_Exporter::instance();
}

prisma_companion_demo_exporter();
