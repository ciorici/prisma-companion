<?php
/**
 * Prisma Companion: Posts List widget.
 *
 * @package Prisma Companion
 * @author  Prisma Core Team
 * @since   1.0.0
 */
class Prisma_Companion_Posts_List_Widget extends WP_Widget {

	/**
	 * Holds widget settings defaults, populated in constructor.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $defaults;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Widget defaults.
		$this->defaults = array(
			'title'         => '',
			'number'        => 5,
			'show_category' => false,
			'show_thumb'    => true,
			'show_date'     => true,
			'orderby'       => 'date',
		);

		// Widget Slug.
		$widget_slug = 'prisma-companion-posts-list-widget';

		// Widget basics.
		$widget_ops = array(
			'classname'   => $widget_slug,
			'description' => _x( 'Displays a configurable list of your site’s posts.', 'Widget', 'prisma-companion' ),
		);

		// Widget controls.
		$control_ops = array(
			'id_base' => $widget_slug,
		);

		// Load widget.
		parent::__construct( $widget_slug, _x( '[Prisma] Posts List', 'Widget', 'prisma-companion' ), $widget_ops, $control_ops );

		// Hook into dynamic styles.
		add_filter( 'prisma_core_dynamic_styles', array( $this, 'dynamic_styles' ) );

	}

	/**
	 * Outputs the HTML for this widget.
	 *
	 * @since 1.0.0
	 * @param array $args An array of standard parameters for widgets in this theme.
	 * @param array $instance An array of settings for this widget instance.
	 */
	public function widget( $args, $instance ) {

		// Merge with defaults.
		$instance = wp_parse_args( (array) $instance, $this->defaults );

		echo wp_kses_post( $args['before_widget'] );

		do_action( 'prisma_core_before_posts_list_widget', $instance );

		// Title.
		if ( ! empty( $instance['title'] ) ) {
			echo wp_kses_post( $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'] );
		}

		$query_args = array(
			'posts_per_page'      => $instance['number'],
			'post_type'           => 'post',
			'status'              => 'publish',
			'orderby'             => $instance['orderby'],
			'order'               => 'DESC',
			'ignore_sticky_posts' => true,
		);

		$query_args = apply_filters( 'prisma_companion_widget_posts_list_query_args', $query_args, $args, $instance );

		$posts = new WP_Query( $query_args );

		if ( $posts->have_posts() ) :

			while ( $posts->have_posts() ) :
				$posts->the_post();

				echo '<div class="pc-posts-list-widget">';

				if ( $instance['show_thumb'] ) {

					$post_thumbnail = prisma_core_get_post_thumbnail( get_the_ID(), array( 75, 75 ), true );
					$post_thumbnail = apply_filters( 'prisma_companion_opsts_list_widget_thumbnail', $post_thumbnail, get_the_ID() );

					if ( ! empty( $post_thumbnail ) ) {
						echo '<div class="pc-posts-list-widget-thumb"><a href="' . esc_url( get_permalink() ) . '">' . $post_thumbnail . '</a></div>';
					}
				}

				echo '<div class="pc-posts-list-widget-details">';

				echo '<div class="pc-posts-list-widget-title">';

				echo '<a href="' . esc_url( get_permalink() ) . '" title="' . esc_attr( get_the_title() ) . '">' . wp_trim_words( wp_kses_post( get_the_title() ), 10, '&hellip;' ) . '</a>';

				echo '</div>';

				$post_meta = '';

				if ( $instance['show_date'] ) {

					$date_icon = '<i class="pc-icon pc-clock"></i>';

					if ( version_compare( PRISMA_CORE_THEME_VERSION, '1.2.0', '>=' ) ) {
						$date_icon = prisma_core()->icons->get_svg( 'clock' );
					}

					$post_meta .= '<span class="pc-posts-list-widget-date pc-flex-center">' . $date_icon . get_the_time( get_option( 'date_format' ) ) . '</span>';
				}

				if ( $instance['show_category'] ) {
					$post_meta .= '<span class="pc-posts-list-widget-categories">' . prisma_core_entry_meta_category( ', ', true, true ) . '</span>';
				}

				$post_meta = apply_filters( 'prisma_companion_posts_list_widget_meta', $post_meta, get_the_ID() );

				if ( ! empty( $post_meta ) ) {
					echo '<div class="pc-posts-list-widget-meta">' . wp_kses( $post_meta, prisma_core_get_allowed_html_tags() ) . '</div>';
				}

				echo '</div>';

				echo '</div>';
			endwhile;

			wp_reset_postdata();
		endif;

		do_action( 'prisma_core_after_posts_list_widget', $instance );

		echo wp_kses_post( $args['after_widget'] );
	}

	/**
	 * Deals with the settings when they are saved by the admin. Here is
	 * where any validation should be dealt with.
	 *
	 * @since 1.0.0
	 * @param array $new_instance An array of new settings as submitted by the admin.
	 * @param array $old_instance An array of the previous settings.
	 * @return array The validated and (if necessary) amended settings
	 */
	public function update( $new_instance, $old_instance ) {

		$instance = array();

		$instance['title']         = ! empty( $new_instance['title'] ) ? wp_strip_all_tags( $new_instance['title'] ) : '';
		$instance['number']        = ! empty( $new_instance['number'] ) ? intval( $new_instance['number'] ) : 3;
		$instance['show_category'] = ! empty( $new_instance['show_category'] ) ? true : false;
		$instance['show_thumb']    = ! empty( $new_instance['show_thumb'] ) ? true : false;
		$instance['show_date']     = ! empty( $new_instance['show_date'] ) ? true : false;
		$instance['orderby']       = ! empty( $new_instance['orderby'] ) ? sanitize_text_field( $new_instance['orderby'] ) : 'date';

		return $instance;
	}

	/**
	 * Displays the form for this widget on the Widgets page of the WP Admin area.
	 *
	 * @since 1.0.0
	 * @param array $instance An array of the current settings for this widget.
	 * @return void
	 */
	public function form( $instance ) {

		// Merge with defaults.
		$instance = wp_parse_args( (array) $instance, $this->defaults );

		?>
		<div class="pc-posts-list-widget pc-widget">
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
					<?php echo esc_html_x( 'Title:', 'Widget', 'prisma-companion' ); ?>
				</label>
				<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" class="widefat"/>
			</p>

			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>">
					<?php echo esc_html_x( 'Number of posts to show:', 'Widget', 'prisma-companion' ); ?>
				</label>
				<input class="tiny-text" id="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'number' ) ); ?>" type="number" step="1" min="1" value="5" size="3" />
			</p>

			<p>
				<input class="checkbox" type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'show_thumb' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_thumb' ) ); ?>" <?php checked( $instance['show_thumb'], true ); ?>>
				<label for="<?php echo esc_attr( $this->get_field_id( 'show_thumb' ) ); ?>"><?php echo esc_html_x( 'Display thumbnail', 'prisma-companion' ); ?></label>
				<br/>
				<input class="checkbox" type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'show_date' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_date' ) ); ?>" <?php checked( $instance['show_date'], true ); ?>>
				<label for="<?php echo esc_attr( $this->get_field_id( 'show_date' ) ); ?>"><?php echo esc_html_x( 'Display post date', 'prisma-companion' ); ?></label>
				<br/>
				<input class="checkbox" type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'show_category' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_category' ) ); ?>" <?php checked( $instance['show_category'], true ); ?>>
				<label for="<?php echo esc_attr( $this->get_field_id( 'show_category' ) ); ?>"><?php echo esc_html_x( 'Display post categories', 'prisma-companion' ); ?></label>
			</p>

			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'orderby' ) ); ?>"><?php echo esc_html_x( 'Sort by:', 'prisma-companion' ); ?></label>
				<select id="<?php echo $this->get_field_id( 'orderby' ); ?>" name="<?php echo $this->get_field_name( 'orderby' ); ?>">
					<option value="date" <?php selected( $instance['orderby'], 'date' ); ?>><?php echo esc_html_x( 'Date (Latest posts)', 'Widget', 'prisma-companion' ); ?></option>
					<option value="modified" <?php selected( $instance['orderby'], 'modified' ); ?>><?php echo esc_html_x( 'Modified (Recently updated)', 'Widget', 'prisma-companion' ); ?></option>
					<option value="comment_count" <?php selected( $instance['orderby'], 'comment_count' ); ?>><?php echo esc_html_x( 'Comment count (Most popular)', 'Widget', 'prisma-companion' ); ?></option>
					<option value="menu_order" <?php selected( $instance['orderby'], 'menu_order' ); ?>><?php echo esc_html_x( 'Menu Order (Custom order)', 'Widget', 'prisma-companion' ); ?></option>
				</select>
			</p>

			<?php
			if ( function_exists( 'prisma_core_help_link' ) ) {
				prisma_core_help_link( array( 'link' => 'https://github.com/ciorici/prisma-core/docs/posts-list-widget/' ) );
			}
			?>

		</div>
		<?php
	}

	/**
	 * Hook into Prisma Core dynamic styles.
	 *
	 * @param  string $css Generated CSS code.
	 * @return string Modified CSS code.
	 */
	function dynamic_styles( $css ) {
		$css .= '#main .pc-posts-list-widget-meta {
			color: ' . prisma_core_hex2rgba( prisma_core_option( 'content_text_color' ), 0.75 ) . ';
		}';

		return $css;
	}
}
