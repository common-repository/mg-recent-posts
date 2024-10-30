<?php

class mg_Widget_Recent_Posts extends WP_Widget {

	function __construct() {
		$widget_ops = array(
			'classname' => 'widget_recent_entries', 
			'description' => __('Your site&#8217;s most recent Posts, by category', 'mg_recent_posts')
		);
		parent::__construct(
			'mg-recent-posts', // id base
			__('mg Recent Posts', 'mg_recent_posts'), // Widget title
			$widget_ops
		);
		
		$this->alt_option_name = 'mg_widget_recent_entries';

		add_action('save_post', array($this, 'flush_widget_cache') );
		add_action('deleted_post', array($this, 'flush_widget_cache') );
		add_action('switch_theme', array($this, 'flush_widget_cache') );
	}
	
	function form($instance) {
		$title     = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$number    = isset( $instance['number'] ) ? absint( $instance['number'] ) : 5;
		$show_date = isset( $instance['show_date'] ) ? (bool) $instance['show_date'] : false;
		$category = isset($instance['category']) ? (int)$instance['category'] : 0;
		//if ($category > 0 && !mg_qt_Query::category_exists($category) )
			//$category = 0;
?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></p>

		<p><label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of posts to show:' ); ?></label>
		<input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo $number; ?>" size="3" /></p>

		<p><input class="checkbox" type="checkbox" <?php checked( $show_date ); ?> id="<?php echo $this->get_field_id( 'show_date' ); ?>" name="<?php echo $this->get_field_name( 'show_date' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'show_date' ); ?>"><?php _e( 'Display post date?' ); ?></label></p>
		
		<p>
			<label for="<?php echo $this->get_field_id('category'); ?>"><?php _e('Category:', 'mg_Recent_Posts'); ?></label> 
				<?php
					wp_dropdown_categories(array(
						'name' => $this->get_field_name('category'),
						'selected' => $category,
						'show_option_all' => __('Select a category', 'mg_recent_posts'),
						'hierarchical' => 1,
						//'show_count' => 1,
						'orderby' => 'name',
						'class' => 'widefat'
					));
				?>
		</p>
<?php
	}
	
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['number'] = (int) $new_instance['number'];
		$instance['show_date'] = isset( $new_instance['show_date'] ) ? (bool) $new_instance['show_date'] : false;
		$instance['category'] = (int) $new_instance['category'];
		
		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset($alloptions['mg_widget_recent_entries']) )
			delete_option('mg_widget_recent_entries');

		return $instance;
	}

	function widget($args, $instance) {
		$cache = array();
		
		if ( ! $this->is_preview() ) {
			$cache = wp_cache_get( 'mg_widget_recent_posts', 'widget' );
		}

		if ( ! is_array( $cache ) ) {
			$cache = array();
		}

		if ( ! isset( $args['widget_id'] ) ) {
			$args['widget_id'] = $this->id;
		}

		if ( isset( $cache[ $args['widget_id'] ] ) ) {
			echo $cache[ $args['widget_id'] ];
			return;
		}

		ob_start();
		extract($args);

		$title = ( ! empty( $instance['title'] ) ) ? $instance['title'] : __( 'Recent Posts', 'mg Recent Posts' );

		/** This filter is documented in wp-includes/default-widgets.php */
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		$number = ( ! empty( $instance['number'] ) ) ? absint( $instance['number'] ) : 5;
		if ( ! $number )
			$number = 5;
		
		$show_date = isset( $instance['show_date'] ) ? $instance['show_date'] : false;
		
		$category = isset($instance['category']) ? (int)$instance['category'] : 0;
		if ($category < 0)
			$category = 0;

		/**
		 * Filter the arguments for the Recent Posts widget.
		 *
		 * @since 3.4.0
		 *
		 * @see WP_Query::get_posts()
		 *
		 * @param array $args An array of arguments used to retrieve the recent posts.
		 */
		$r = new WP_Query( apply_filters( 'mg_recent_posts_query_args', array(
			'posts_per_page'      => $number,
			'no_found_rows'       => true,
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true,
			'cat' => $category
		) ) );

		if ($r->have_posts()) :
?>
		<?php echo $before_widget; ?>
		<?php if ( $title ) echo $before_title . $title . $after_title; ?>
		<ul>
		<?php while ( $r->have_posts() ) : $r->the_post(); ?>
			<li>
				<a href="<?php the_permalink(); ?>"><?php get_the_title() ? the_title() : the_ID(); ?></a>
			<?php if ( $show_date ) : ?>
				<span class="post-date"><?php echo get_the_date(); ?></span>
			<?php endif; ?>
			</li>
		<?php endwhile; ?>
		</ul>
		<?php echo $after_widget; ?>
<?php
		// Reset the global $the_post as this query will have stomped on it
		wp_reset_postdata();

		endif;

		if ( ! $this->is_preview() ) {
			$cache[ $args['widget_id'] ] = ob_get_flush();
			wp_cache_set( 'mg_widget_recent_posts', $cache, 'widget' );
		} else {
			ob_end_flush();
		}
	}

	function flush_widget_cache() {
		wp_cache_delete('mg_widget_recent_posts', 'widget');
	}

}