<?php
/**
 * Prisma Companion Admin class. Prisma Core related pages in WP Dashboard.
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
 * Prisma Companion Admin Class.
 *
 * @since 1.0.0
 * @package Prisma Companion
 */
final class Prisma_Companion_Admin {

	/**
	 * Singleton instance of the class.
	 *
	 * @since 1.0.0
	 * @var object
	 */
	private static $instance;

	/**
	 * Main Prisma Companion Admin Instance.
	 *
	 * @since 1.0.0
	 * @return Prisma_Companion_Admin
	 */
	public static function instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Prisma_Companion_Admin ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @since  1.0.0
	 */
	public function __construct() {

		if ( ! is_admin() ) {
			return;
		}

		// Init Prisma Companion admin.
		add_action( 'after_setup_theme', array( $this, 'init_admin' ), 99 );

		// Fetch recommended plugins remotely.
		add_filter( 'prisma_core_recommended_plugins', array( $this, 'recommended_plugins' ) );

		// Prisma Companion Admin loaded.
		do_action( 'prisma_companion_admin_loaded' );
	}

	/**
	 * Include files.
	 *
	 * @since 1.0.0
	 */
	private function includes() {

		// Demo Library.
		require_once PRISMA_COMPANION_PLUGIN_DIR . 'includes/admin/demo-library/class-prisma-companion-demo-library.php';
	}

	/**
	 * Admin init.
	 *
	 * @since 1.0.0
	 */
	public function init_admin() {

		if ( ! defined( 'PRISMA_CORE_THEME_VERSION' ) ) {
			add_action( 'admin_notices', array( $this, 'theme_required_notice' ) );
			return;
		}

		// Add Prisma Core admin page.
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ), 100 );
		add_action( 'admin_menu', array( $this, 'add_changelog_menu' ), 999 );

		// Change about page navigation.
		add_filter( 'prisma_core_dashboard_navigation_items', array( $this, 'update_navigation_items' ) );

		// Add changelog section.
		add_action( 'prisma_core_after_changelog', array( $this, 'changelog' ) );

		// Enqueue scripts & styles.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );

		$this->includes();
	}

	/**
	 * Add main menu.
	 *
	 * @since 1.0.0
	 */
	public function add_admin_menu() {

		// Remove from Appearance.
		remove_submenu_page( 'themes.php', 'prisma-core-dashboard' );
		remove_submenu_page( null, 'prisma-core-plugins' );

		// Add a new menu item.
		add_menu_page(
			esc_html__( 'Prisma Core', 'prisma-companion' ),
			'Prisma Core', // This menu cannot be translated because it's used for the $hook prefix.
			apply_filters( 'prisma_core_manage_cap', 'edit_theme_options' ), // phpcs:ignore
			'prisma-core-dashboard',
			array( prisma_core_dashboard(), 'render_dashboard' ),
			'dashicons-si-brand',
			apply_filters( 'prisma_core_menu_position', '999.2' ) // phpcs:ignore
		);

		// About page.
		add_submenu_page(
			'prisma-core-dashboard',
			esc_html__( 'About', 'prisma-companion' ),
			'About',
			apply_filters( 'prisma_core_manage_cap', 'edit_theme_options' ), // phpcs:ignore
			'prisma-core-dashboard',
			array( prisma_core_dashboard(), 'render_dashboard' )
		);

		// Install Plugins page.
		add_submenu_page(
			'prisma-core-dashboard',
			esc_html__( 'Plugins', 'prisma-companion' ),
			'Plugins',
			apply_filters( 'prisma_core_manage_cap', 'edit_theme_options' ), // phpcs:ignore
			'prisma-core-plugins',
			array( prisma_core_dashboard(), 'render_plugins' )
		);
	}

	/**
	 * Add changelog menu.
	 *
	 * @since 1.0.0
	 */
	public function add_changelog_menu() {

		remove_submenu_page( null, 'prisma-core-changelog' );

		// Changelog page.
		add_submenu_page(
			'prisma-core-dashboard',
			esc_html__( 'Changelog', 'prisma-companion' ),
			'Changelog',
			apply_filters( 'prisma_core_manage_cap', 'edit_theme_options' ), // phpcs:ignore
			'prisma-core-changelog',
			array( prisma_core_dashboard(), 'render_changelog' )
		);
	}

	/**
	 * Add menu items to Prisma Core Dashboard navigation.
	 *
	 * @param array $items Array of navigation items.
	 * @since 1.0.0
	 */
	public function update_navigation_items( $items ) {

		$items['dashboard']['url'] = admin_url( 'admin.php?page=prisma-core-dashboard' );
		$items['plugins']['url']   = admin_url( 'admin.php?page=prisma-core-plugins' );
		$items['changelog']['url'] = admin_url( 'admin.php?page=prisma-core-changelog' );

		return $items;
	}

	/**
	 * Enqueue admin scripts and styles.
	 *
	 * @since 1.0.0
	 */
	public function enqueue() {

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_style(
			'prisma-companion-dashicon',
			PRISMA_COMPANION_PLUGIN_URL . 'assets/css/admin-dashicon' . $suffix . '.css',
			null,
			PRISMA_COMPANION_VERSION
		);
	}

	/**
	 * Enqueue admin scripts and styles.
	 *
	 * @since 1.0.0
	 */
	public function changelog() {

		$changelog = PRISMA_COMPANION_PLUGIN_DIR . '/changelog.txt';

		if ( ! file_exists( $changelog ) ) {
			$changelog = esc_html__( 'Changelog file not found.', 'prisma-companion' );
		} elseif ( ! is_readable( $changelog ) ) {
			$changelog = esc_html__( 'Changelog file not readable.', 'prisma-companion' );
		} else {
			global $wp_filesystem;

			// Check if the the global filesystem isn't setup yet.
			if ( is_null( $wp_filesystem ) ) {
				WP_Filesystem();
			}

			$changelog = $wp_filesystem->get_contents( $changelog );
		}

		?>
		<div class="prisma-core-section-title prisma-companion-changelog">
			<h2 class="prisma-core-section-title">
				<span><?php esc_html_e( 'Prisma Companion Plugin Changelog', 'prisma-companion' ); ?></span>
				<span class="changelog-version"><?php echo esc_html( sprintf( 'v%1$s', PRISMA_COMPANION_VERSION ) ); ?></span>
			</h2>

		</div><!-- END .prisma-core-section-title -->

		<div class="prisma-core-section prisma-core-columns">

			<div class="prisma-core-column column-12">
				<div class="prisma-core-box prisma-core-changelog">
					<pre><?php echo esc_html( $changelog ); ?></pre>
				</div>
			</div>
		</div><!-- END .prisma-core-columns -->
		<?php
	}

	/**
	 * Display notice.
	 *
	 * @since 1.0.0
	 */
	public function theme_required_notice() {

		echo '<div class="notice notice-warning"><p>' . esc_html__( 'Prisma Core theme needs to be installed and activated in order to use Prisma Companion plugin.', 'prisma-companion' ) . ' <a href="' . esc_url( admin_url( 'themes.php' ) ) . '"><strong>' . esc_html__( 'Install & Activate', 'prisma-companion' ) . '</strong></a>.</p></div>';
	}

	/**
	 * Fetch plugins config array from remote server.
	 *
	 * @since 1.0.0
	 * @param array $plugins Array of recommended plugins.
	 */
	public function recommended_plugins( $plugins ) {

		$remote = get_site_transient( 'prisma_core_check_plugin_update' );

		if ( false === $remote ) {

			$response = wp_remote_get(
				'https://github.com/ciorici/prisma-core/wp-json/api/v1/plugins',
				array(
					'user-agent' => 'PrismaCore/' . PRISMA_CORE_THEME_VERSION . ';',
					'timeout'    => 10,
				)
			);

			if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
				set_site_transient( 'prisma_core_check_plugin_update', 'error', 60 * 60 * 24 * 30 );
				return $plugins;
			}

			$body   = wp_remote_retrieve_body( $response );
			$remote = json_decode( $body, true );

			if ( is_array( $remote ) && ! empty( $remote ) ) {
				$plugins = $remote;
				set_site_transient( 'prisma_core_check_plugin_update', $plugins, 60 * 60 * 24 * 3 );
			} else {
				set_site_transient( 'prisma_core_check_plugin_update', 'error', 60 * 60 * 24 * 30 );
			}
		} elseif ( 'error' === $remote ) {
			return $plugins;
		} else {
			$plugins = $remote;
		}

		return $plugins;
	}
}

/**
 * The function which returns the one Prisma_Companion_Admin instance.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $prisma_companion_admin = prisma_companion_admin(); ?>
 *
 * @since 1.0.0
 * @return object
 */
function prisma_companion_admin() {
	return Prisma_Companion_Admin::instance();
}

prisma_companion_admin();
