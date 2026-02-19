<?php
/**
 * Plugin Name: Prisma Companion
 * Plugin URI:  https://github.com/ciorici/prisma-core
 * Description: Additional features for Prisma Core WordPress Theme.
 * Author:      Prisma Core Team
 * Author URI:  https://github.com/ciorici/prisma-core
 * Version:     1.0.0
 * Text Domain: prisma-companion
 * Domain Path: languages
 *
 * Prisma Companion is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Prisma Companion is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Social Snap. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    Prisma Companion
 * @author     Prisma Core Team
 * @since      1.0.0
 * @license    GPL-3.0+
 * @copyright  Copyright (c) 2018, Sinatra Team. 2025, Prisma Core Team
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Don't allow multiple versions to be active.
if ( ! class_exists( 'Prisma_Companion' ) ) {

	/**
	 * Main Prisma Companion class.
	 *
	 * @since 1.0.0
	 * @package Prisma Companion
	 */
	final class Prisma_Companion {

		/**
		 * Singleton instance of the class.
		 *
		 * @since 1.0.0
		 * @var object
		 */
		private static $instance;

		/**
		 * Plugin version for enqueueing, etc.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		public $version = '';

		/**
		 * Main Prisma Companion Instance.
		 *
		 * Insures that only one instance of Prisma Companion exists in memory at any one
		 * time. Also prevents needing to define globals all over the place.
		 *
		 * @since 1.0.0
		 * @return Prisma_Companion
		 */
		public static function instance() {

			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Prisma_Companion ) ) {

				self::$instance = new Prisma_Companion();
				self::$instance->constants();
				self::$instance->load_textdomain();
				self::$instance->includes();
				self::$instance->objects();

				add_action( 'plugins_loaded', array( self::$instance, 'objects' ), 10 );
			}

			return self::$instance;
		}

		/**
		 * Setup plugin constants.
		 *
		 * @since 1.0.0
		 */
		private function constants() {

			// Plugin version — read from the plugin header so there's a single source of truth.
			if ( ! defined( 'PRISMA_COMPANION_VERSION' ) ) {
				if ( ! function_exists( 'get_plugin_data' ) ) {
					require_once ABSPATH . 'wp-admin/includes/plugin.php';
				}
				$plugin_data   = get_plugin_data( __FILE__, false, false );
				$this->version = $plugin_data['Version'];
				define( 'PRISMA_COMPANION_VERSION', $this->version );
			}

			// Plugin Folder Path.
			if ( ! defined( 'PRISMA_COMPANION_PLUGIN_DIR' ) ) {
				define( 'PRISMA_COMPANION_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
			}

			// Plugin Folder URL.
			if ( ! defined( 'PRISMA_COMPANION_PLUGIN_URL' ) ) {
				define( 'PRISMA_COMPANION_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
			}

			// Plugin Root File.
			if ( ! defined( 'PRISMA_COMPANION_PLUGIN_FILE' ) ) {
				define( 'PRISMA_COMPANION_PLUGIN_FILE', __FILE__ );
			}
		}

		/**
		 * Loads the plugin language files.
		 *
		 * @since 1.0.0
		 */
		public function load_textdomain() {

			load_plugin_textdomain( 'prisma-companion', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}

		/**
		 * Include files.
		 *
		 * @since 1.0.0
		 */
		private function includes() {

			// Global includes.
			require_once PRISMA_COMPANION_PLUGIN_DIR . 'includes/widgets/widgets.php';

			require_once PRISMA_COMPANION_PLUGIN_DIR . 'includes/admin/class-prisma-companion-admin.php';

			if ( defined( 'WP_CLI' ) && WP_CLI ) {
				require_once PRISMA_COMPANION_PLUGIN_DIR . 'includes/cli/class-prisma-companion-cli.php';
			}
		}

		/**
		 * Setup objects to be used throughout the plugin.
		 *
		 * @since 1.0.0
		 */
		public function objects() {

			// Hook now that all of the Prisma Companion stuff is loaded.
			do_action( 'prisma_companion_loaded' );
		}
	}

	/**
	 * The function which returns the one Prisma_Companion instance.
	 *
	 * Use this function like you would a global variable, except without needing
	 * to declare the global.
	 *
	 * Example: <?php $prisma_companion = prisma_companion(); ?>
	 *
	 * @since 1.0.0
	 * @return object
	 */
	function prisma_companion() {
		return Prisma_Companion::instance();
	}

	$theme = wp_get_theme();

	if ( 'Prisma Core' === $theme->name || 'prisma-core' === $theme->template ) {
		prisma_companion();
	} else {
		add_action( 'admin_notices', 'prisma_companion_theme_notice' );
	}

	/**
	 * Display notice.
	 *
	 * @since 1.0.0
	 */
	function prisma_companion_theme_notice() {
		echo '<div class="notice notice-warning"><p>' . __( 'Please activate Prisma Core theme before activating Prisma Companion.', 'prisma-companion' ) . '</p></div>';
	}
}
