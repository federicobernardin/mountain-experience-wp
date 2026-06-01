<?php
/**
 * Search & Filter Pro results template for Experience cards.
 *
 * @package MountainExperience
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( $query->have_posts() ) :
	?>
	<div class="me-results">
		<?php
		while ( $query->have_posts() ) :
			$query->the_post();

			$post_id = get_the_ID();
			$facts   = array(
				array(
					'label' => __( 'Dislivello', 'mountain-experience' ),
					'value' => me_format_field_value( me_get_experience_field( 'elevation_gain', $post_id ), 'm' ),
				),
				array(
					'label' => __( 'Durata', 'mountain-experience' ),
					'value' => me_format_duration( me_get_experience_field( 'duration', $post_id ) ),
				),
				array(
					'label' => __( 'Lunghezza', 'mountain-experience' ),
					'value' => me_format_field_value( me_get_experience_field( 'length', $post_id ), 'km' ),
				),
				array(
					'label' => __( 'Quota max', 'mountain-experience' ),
					'value' => me_format_field_value( me_get_experience_field( 'max_altitude', $post_id ), 'm' ),
				),
			);
			?>

			<article <?php post_class( 'me-card' ); ?>>
				<a class="me-card__image" href="<?php the_permalink(); ?>" aria-label="<?php the_title_attribute(); ?>">
					<span class="me-card__badge-wrap">
						<?php
						$activity_terms = get_the_terms( $post_id, 'activity_type' );

						if ( ! empty( $activity_terms ) && ! is_wp_error( $activity_terms ) ) {
							foreach ( $activity_terms as $term ) {
								printf( '<span class="me-card__badge">%s</span>', esc_html( $term->name ) );
							}
						}
						?>
					</span>

					<?php
					if ( has_post_thumbnail() ) {
						the_post_thumbnail( 'large' );
					} else {
						echo '<span class="me-card__image-placeholder" aria-hidden="true"></span>';
					}
					?>
				</a>

				<div class="me-card__body">
					<h2 class="me-card__title">
						<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
					</h2>

					<?php if ( has_excerpt() ) : ?>
						<p class="me-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>
					<?php endif; ?>

					<dl class="me-card__facts" aria-label="<?php esc_attr_e( 'Experience technical data', 'mountain-experience' ); ?>">
						<?php foreach ( $facts as $fact ) : ?>
							<?php if ( $fact['value'] ) : ?>
								<div class="me-card__fact">
									<dt><?php echo esc_html( $fact['label'] ); ?></dt>
									<dd><?php echo esc_html( $fact['value'] ); ?></dd>
								</div>
							<?php endif; ?>
						<?php endforeach; ?>
					</dl>

					<a class="me-card__link" href="<?php the_permalink(); ?>">
						<?php esc_html_e( 'View experience', 'mountain-experience' ); ?>
					</a>
				</div>
			</article>

			<?php
		endwhile;
		?>
	</div>

	<?php if ( $query->max_num_pages > 1 ) : ?>
		<nav class="me-pagination" aria-label="<?php esc_attr_e( 'Results pagination', 'mountain-experience' ); ?>">
			<?php
			echo wp_kses_post(
				paginate_links(
					array(
						'total'   => $query->max_num_pages,
						'current' => max( 1, get_query_var( 'paged' ) ),
					)
				)
			);
			?>
		</nav>
	<?php endif; ?>

	<?php
else :
	?>
	<div class="me-no-results">
		<p><?php esc_html_e( 'Nessuna experience trovata con i filtri selezionati.', 'mountain-experience' ); ?></p>
	</div>
	<?php
endif;

wp_reset_postdata();
