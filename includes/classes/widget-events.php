<?php
/**
 * Events Widget
 */

class TBF_Widget_Events extends WP_Widget {

	public function __construct() {

		$widget_options = array(
			'description' => __( 'A customizable list of events.', 'themebright-framework' ),
			'classname'   => 'tbf-widget tbf-widget--events'
		);

		parent::__construct( 'tbf-events', __( 'Events', 'themebright-framework' ), $widget_options );

	}

	public function widget( $args, $instance ) {

		$title          = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Events', 'themebright-framework' ) : $instance['title'] );
		$number         = isset( $instance['number'] )         ? absint( $instance['number'] ) : 5;
		$show_thumbnail = isset( $instance['show_thumbnail'] ) ? $instance['show_thumbnail']   : false;
		$show_excerpt   = isset( $instance['show_excerpt'] )   ? $instance['show_excerpt']     : false;
		$show_date      = isset( $instance['show_date'] )      ? $instance['show_date']        : true;
		$show_time      = isset( $instance['show_time'] )      ? $instance['show_time']        : true;
		$show_venue     = isset( $instance['show_venue'] )     ? $instance['show_venue']       : true;
		$show_address   = isset( $instance['show_address'] )   ? $instance['show_address']     : true;
		$show_map       = isset( $instance['show_map'] )       ? $instance['show_map']         : false;

		$theme_support = get_theme_support( 'tbf' );
		$theme_support = $theme_support[0]['widgets']['events']['fields'];

		$query_args = array(
			'post_type'      => 'ctc_event',
			'post_status'    => 'publish',
			'posts_per_page' => $number,
			'order'          => 'ASC',
			'orderby'        => 'meta_value',
			'meta_key'       => '_ctc_event_start_date',
			'meta_query'     => array(
				array(
					'key'     => '_ctc_event_end_date',
					'value'   => date_i18n( 'Y-m-d' ),
					'compare' => '>=',
					'type'    => 'DATE'
				)
			)
		);

		$events = new WP_Query( $query_args );

		$override = locate_template( 'widgets/widget-events.php' );

		if ( $override ) :

			include( $override );

		else :

			echo $args['before_widget'];

				if ( $title ) {
					echo $args['before_title'] . $title . $args['after_title'];
				}

				if ( $events->have_posts() ) : ?>

					<ul class="tbf-widget__entries tbf-widget--events__entries">
						<?php while ( $events->have_posts() ) : $events->the_post(); ?>
							<li class="tbf-widget__entry">
								<?php if ( in_array( 'thumbnail', $theme_support ) && $show_thumbnail && has_post_thumbnail() ) : ?>
									<div class="tbf-widget__entry-thumbnail tbf-widget--events__entry-thumbnail">
										<?php the_post_thumbnail( 'large' ); ?>
									</div>
								<?php endif; ?>

								<?php if ( in_array( 'title', $theme_support ) ) : ?>
									<?php the_title( sprintf( '<h4 class="tbf-widget__entry-title tbf-widget--events__entry-title"><a href="%s">', esc_url( get_permalink() ) ), '</a></h4>' ); ?>
								<?php endif; ?>

								<div class="tbf-widget__entry-body tbf-widget--events__entry-body">
									<?php if ( in_array( 'excerpt', $theme_support ) && $show_excerpt && get_the_excerpt() ) : ?>
										<div class="tbf-widget__excerpt tbf-widget--events__excerpt"><?php the_excerpt(); ?></div>
									<?php endif; ?>

									<?php $date = tbf_event_date(); if ( in_array( 'date', $theme_support ) && $show_date && $date ) : ?>
										<div class="tbf-widget__date tbf-widget--events__date">
											<span class="tbf-widget__start-date tbf-widget--events__start-date"><?php echo $date['start']; ?></span>

											<?php if ( array_key_exists( 'end', $date ) ) : ?>
												<span class="tbf-widget__to-sep tbf-widget--events__to-sep">&ndash;</span>
												<span class="tbf-widget__end-date tbf-widget--events__end-date"><?php echo $date['end']; ?></span>
											<?php endif; ?>
										</div>
									<?php endif; ?>

									<?php $time = tbf_event_time(); if ( in_array( 'time', $theme_support ) && $show_time && ( ( $time && ! tbf_event_hide_time_range() ) || tbf_event_time_description() ) ) : ?>
										<?php if ( $time && ! tbf_event_hide_time_range() ) : ?>
											<div class="tbf-widget__time tbf-widget--events__time">
												<span class="tbf-widget__start-time tbf-widget--events__start-time"><?php echo $time['start']; ?></span>

												<?php if ( array_key_exists( 'end', $time ) ) : ?>
													<span class="tbf-widget__to-sep tbf-widget--events__to-sep">&ndash;</span>
													<span class="tbf-widget__end-time tbf-widget--events__end-time"><?php echo $time['end']; ?></span>
												<?php endif; ?>
											</div>
										<?php endif; ?>

										<?php if ( tbf_event_time_description() ) : ?>
											<div class="tbf-widget__time-description tbf-widget--events__time-description"><?php echo tbf_event_time_description(); ?></div>
										<?php endif; ?>
									<?php endif; ?>

									<?php if ( in_array( 'venue', $theme_support ) && $show_venue && tbf_event_venue() ) : ?>
										<div class="tbf-widget__venue tbf-widget--events__venue"><?php echo tbf_event_venue(); ?></div>
									<?php endif; ?>

									<?php if ( in_array( 'address', $theme_support ) && $show_address && tbf_event_address() ) : ?>
										<div class="tbf-widget__address tbf-widget--events__address"><?php echo tbf_event_address(); ?></div>
									<?php endif; ?>

									<?php if ( in_array( 'map', $theme_support ) && $show_map && tbf_event_map() ) : ?>
										<div class="tbf-widget__map tbf-widget--events__map"><?php echo tbf_event_map(); ?></div>
									<?php endif; ?>
								</div>
							</li>
						<?php endwhile; ?>
					</ul>

				<?php else : ?>

					<p class="tbf-widget__no-entries-found tbf-widget--events__no-entries-found"><?php _e( 'No events found.', 'themebright' ); ?></p>

				<?php endif;

			echo $args['after_widget'];

			wp_reset_postdata();

		endif;

	}

	public function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		$instance['title']          = strip_tags( $new_instance['title'] );
		$instance['number']         = (int) $new_instance['number'];
		$instance['show_thumbnail'] = isset( $new_instance['show_thumbnail'] ) ? (bool) $new_instance['show_thumbnail'] : false;
		$instance['show_excerpt']   = isset( $new_instance['show_excerpt'] )   ? (bool) $new_instance['show_excerpt']   : false;
		$instance['show_date']      = isset( $new_instance['show_date'] )      ? (bool) $new_instance['show_date']      : false;
		$instance['show_time']      = isset( $new_instance['show_time'] )      ? (bool) $new_instance['show_time']      : false;
		$instance['show_venue']     = isset( $new_instance['show_venue'] )     ? (bool) $new_instance['show_venue']     : false;
		$instance['show_address']   = isset( $new_instance['show_address'] )   ? (bool) $new_instance['show_address']   : false;
		$instance['show_map']       = isset( $new_instance['show_map'] )       ? (bool) $new_instance['show_map']       : false;

		return $instance;

	}

	public function form( $instance ) {

		$title          = isset( $instance['title'] )          ? esc_attr( $instance['title'] )     : '';
		$number         = isset( $instance['number'] )         ? absint( $instance['number'] )      : 5;
		$show_thumbnail = isset( $instance['show_thumbnail'] ) ? (bool) $instance['show_thumbnail'] : false;
		$show_excerpt   = isset( $instance['show_excerpt'] )   ? (bool) $instance['show_excerpt']   : false;
		$show_date      = isset( $instance['show_date'] )      ? (bool) $instance['show_date']      : true;
		$show_time      = isset( $instance['show_time'] )      ? (bool) $instance['show_time']      : true;
		$show_venue     = isset( $instance['show_venue'] )     ? (bool) $instance['show_venue']     : true;
		$show_address   = isset( $instance['show_address'] )   ? (bool) $instance['show_address']   : true;
		$show_map       = isset( $instance['show_map'] )       ? (bool) $instance['show_map']       : false;

		$theme_support = get_theme_support( 'tbf' );
		$theme_support = $theme_support[0]['widgets']['events']['fields'];

	?>

		<?php if ( in_array( 'title', $theme_support ) ) : ?>
			<p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'themebright-framework' ); ?></label>
				<input id="<?php echo $this->get_field_id( 'title' ); ?>" class="widefat" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
			</p>
		<?php endif; ?>

		<?php if ( in_array( 'number', $theme_support ) ) : ?>
			<p>
				<label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of events to show:', 'themebright-framework' ); ?></label>
				<input id="<?php echo $this->get_field_id( 'number' ); ?>" class="widefat" name="<?php echo $this->get_field_name( 'number' ); ?>" type="number" value="<?php echo $number; ?>" min="1" />
			</p>
		<?php endif; ?>

		<?php if ( in_array( 'thumbnail', $theme_support ) ) : ?>
			<p>
				<input class="checkbox" type="checkbox" <?php checked( $show_thumbnail ); ?> id="<?php echo $this->get_field_id( 'show_thumbnail' ); ?>" name="<?php echo $this->get_field_name( 'show_thumbnail' ); ?>" />
				<label for="<?php echo $this->get_field_id( 'show_thumbnail' ); ?>"><?php _e( 'Show thumbnail', 'themebright-framework' ); ?></label>
			</p>
		<?php endif; ?>

		<?php if ( in_array( 'excerpt', $theme_support ) ) : ?>
			<p>
				<input class="checkbox" type="checkbox" <?php checked( $show_excerpt ); ?> id="<?php echo $this->get_field_id( 'show_excerpt' ); ?>" name="<?php echo $this->get_field_name( 'show_excerpt' ); ?>" />
				<label for="<?php echo $this->get_field_id( 'show_excerpt' ); ?>"><?php _e( 'Show excerpt', 'themebright-framework' ); ?></label>
			</p>
		<?php endif; ?>

		<?php if ( in_array( 'date', $theme_support ) ) : ?>
			<p>
				<input class="checkbox" type="checkbox" <?php checked( $show_date ); ?> id="<?php echo $this->get_field_id( 'show_date' ); ?>" name="<?php echo $this->get_field_name( 'show_date' ); ?>" />
				<label for="<?php echo $this->get_field_id( 'show_date' ); ?>"><?php _e( 'Show date', 'themebright-framework' ); ?></label>
			</p>
		<?php endif; ?>

		<?php if ( in_array( 'time', $theme_support ) ) : ?>
			<p>
				<input class="checkbox" type="checkbox" <?php checked( $show_time ); ?> id="<?php echo $this->get_field_id( 'show_time' ); ?>" name="<?php echo $this->get_field_name( 'show_time' ); ?>" />
				<label for="<?php echo $this->get_field_id( 'show_time' ); ?>"><?php _e( 'Show time', 'themebright-framework' ); ?></label>
			</p>
		<?php endif; ?>

		<?php if ( in_array( 'venue', $theme_support ) ) : ?>
			<p>
				<input class="checkbox" type="checkbox" <?php checked( $show_venue ); ?> id="<?php echo $this->get_field_id( 'show_venue' ); ?>" name="<?php echo $this->get_field_name( 'show_venue' ); ?>" />
				<label for="<?php echo $this->get_field_id( 'show_venue' ); ?>"><?php _e( 'Show venue', 'themebright-framework' ); ?></label>
			</p>
		<?php endif; ?>

		<?php if ( in_array( 'address', $theme_support ) ) : ?>
			<p>
				<input class="checkbox" type="checkbox" <?php checked( $show_address ); ?> id="<?php echo $this->get_field_id( 'show_address' ); ?>" name="<?php echo $this->get_field_name( 'show_address' ); ?>" />
				<label for="<?php echo $this->get_field_id( 'show_address' ); ?>"><?php _e( 'Show address', 'themebright-framework' ); ?></label>
			</p>
		<?php endif; ?>

		<?php if ( in_array( 'map', $theme_support ) ) : ?>
			<p>
				<input class="checkbox" type="checkbox" <?php checked( $show_map ); ?> id="<?php echo $this->get_field_id( 'show_map' ); ?>" name="<?php echo $this->get_field_name( 'show_map' ); ?>" />
				<label for="<?php echo $this->get_field_id( 'show_map' ); ?>"><?php _e( 'Show map', 'themebright-framework' ); ?></label>
			</p>
		<?php endif; ?>

	<?php

	}
}
