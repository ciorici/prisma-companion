<?php
/**
 * Prisma Companion - Register new widgets.
 *
 * @package     Prisma Companion
 * @author      Prisma Core Team
 * @since       1.0.0
 */

/**
 * Do not allow direct script access.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return list of available widgets.
 *
 * @since 1.0.0
 */
function prisma_companion_get_widgets() {

	$widgets = array(
		'prisma-companion-custom-list-widget' => 'Prisma_Companion_Custom_List_Widget',
		'prisma-companion-posts-list-widget'  => 'Prisma_Companion_Posts_List_Widget',
	);

	return apply_filters( 'prisma_companion_widgets', $widgets );
}

/**
 * Register widgets.
 *
 * @since 1.0.0
 */
function prisma_companion_register_widgets() {

	// Get available widgets.
	$widgets = prisma_companion_get_widgets();

	if ( empty( $widgets ) ) {
		return;
	}

	// Path to widgets folder.
	$path = PRISMA_COMPANION_PLUGIN_DIR . 'includes/widgets';

	// Register widgets.
	foreach ( $widgets as $key => $value ) {

		// Include class and register widget.
		$widget_path = $path . '/class-' . $key . '.php';

		if ( file_exists( $widget_path ) ) {
			require_once $widget_path;
			register_widget( $value );
		}
	}
}
add_action( 'widgets_init', 'prisma_companion_register_widgets' );

/**
 * Enqueue admin styles.
 *
 * @since 1.0.0
 */
function prisma_companion_widgets_enqueue( $page ) {

	$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

	wp_enqueue_style(
		'prisma-companion-admin-widgets-css',
		PRISMA_COMPANION_PLUGIN_URL . 'assets/css/admin-widgets' . $suffix . '.css',
		PRISMA_COMPANION_VERSION,
		true
	);

	wp_enqueue_script(
		'prisma-companion-admin-widgets-js',
		PRISMA_COMPANION_PLUGIN_URL . 'assets/js/admin-widgets.min.js',
		array( 'jquery' ),
		PRISMA_COMPANION_VERSION,
		true
	);
}
add_action( 'admin_print_footer_scripts-widgets.php', 'prisma_companion_widgets_enqueue' );

/**
 * Enqueue front styles.
 *
 * @since 1.0.0
 */
function prisma_companion_enqueue_widget_assets() {

	$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

	$widgets = prisma_companion_get_widgets();

	if ( is_array( $widgets ) ) {
		foreach ( $widgets as $id_slug => $class ) {
			if ( is_active_widget( false, false, $id_slug, true ) ) {

				wp_enqueue_style(
					'prisma-companion-widget-styles',
					PRISMA_COMPANION_PLUGIN_URL . 'assets/css/widgets' . $suffix . '.css',
					false,
					PRISMA_COMPANION_VERSION,
					'all'
				);
			}
		}
	}
}
add_action( 'wp_enqueue_scripts', 'prisma_companion_enqueue_widget_assets' );



/**
 * Print repeatable template.
 *
 * @since  1.0.0
 * @return void
 */
function prisma_companion_print_widget_templates() {
	?>
	<script type="text/template" id="tmpl-prisma-companion-repeatable-item">
		<div class="pc-repeatable-item open">

			<div class="pc-repeatable-item-title">
				<?php echo esc_attr_x( 'New Item', 'Widget', 'prisma-companion' ); ?>

				<div class="pc-repeatable-indicator">
					<span class="accordion-section-title" aria-hidden="true"></span>
				</div>

			</div>

			<div class="pc-repeatable-item-content">

				<p>
					<label for="{{data.id}}-{{data.index}}-icon">
						<?php echo esc_attr_x( 'Icon', 'Widget', 'prisma-companion' ); ?>
					</label>

					<textarea class="widefat" id="{{data.id}}-{{data.index}}-icon" name="{{data.name}}[{{data.index}}][icon]" rows="3"></textarea>
				</p>
				
				<p>
					<label for="{{data.id}}-{{data.index}}-description">
						<?php echo esc_attr_x( 'Item Description', 'Widget', 'prisma-companion' ); ?>
					</label>
					<textarea class="widefat" id="{{data.id}}-{{data.index}}-description" name="{{data.name}}[{{data.index}}][description]" rows="3"></textarea>
					<em class="description pc-description">
						<?php
						echo wp_kses_post(
							sprintf(
								_x( 'HTML tags and %1$sdynamic strings%2$s allowed.', 'Widget', 'prisma-companion' ),
								'<a href="https://github.com/ciorici/prisma-core/docs/prisma-dynamic-strings/" rel="nofollow noreferrer" target="_blank">',
								'</a>'
							)
						);
						?>
					</em>
				</p>

				<p>
					<input type="checkbox" id="{{data.name}}[{{data.index}}][separator]" name="{{data.name}}[{{data.index}}][separator]" />
					<label for="{{data.name}}[{{data.index}}][separator]"><?php _ex( 'Add bottom separator', 'Widget', 'prisma-companion' ); ?></label>
				</p>

				<button type="button" class="remove-repeatable-item button-link button-link-delete"><?php _ex( 'Remove', 'Widget', 'prisma-companion' ); ?></button>
			</div>
		</div>
	</script>
	<?php
}
add_action( 'admin_print_footer_scripts-widgets.php', 'prisma_companion_print_widget_templates' );
