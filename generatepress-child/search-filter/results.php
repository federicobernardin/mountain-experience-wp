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

			$post_id        = get_the_ID();
			$activity_terms = get_the_terms( $post_id, 'activity_type' );

			$overlay_facts = array(
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
			);
			?>

			<article <?php post_class( 'me-card' ); ?>>

				<a class="me-card__media" href="<?php the_permalink(); ?>" aria-label="<?php the_title_attribute(); ?>">

					<div class="me-card__image">
						<?php if ( has_post_thumbnail() ) : ?>
							<?php the_post_thumbnail( 'large' ); ?>
						<?php else : ?>
							<div class="me-card__placeholder">
								<span>Mountain Experience</span>
							</div>
						<?php endif; ?>
					</div>

					<?php if ( ! empty( $activity_terms ) && ! is_wp_error( $activity_terms ) ) : ?>
						<div class="me-card__badges" aria-label="<?php esc_attr_e( 'Activity type', 'mountain-experience' ); ?>">
							<?php foreach ( $activity_terms as $term ) : ?>
								<span class="me-card__badge me-card__badge--activity"><?php echo esc_html( $term->name ); ?></span>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<div class="me-card__image-facts" aria-label="<?php esc_attr_e( 'Experience technical data', 'mountain-experience' ); ?>">
						<?php foreach ( $overlay_facts as $fact ) : ?>
							<?php if ( ! empty( $fact['value'] ) ) : ?>
								<span class="me-card__image-fact">
									<span class="me-card__image-fact-label"><?php echo esc_html( $fact['label'] ); ?></span>
									<strong class="me-card__image-fact-value"><?php echo esc_html( $fact['value'] ); ?></strong>
								</span>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>

				</a>

				<div class="me-card__content">

					<h2 class="me-card__title">
						<a href="<?php the_permalink(); ?>">
							<?php the_title(); ?>
						</a>
					</h2>

					<?php if ( has_excerpt() ) : ?>
						<p class="me-card__excerpt">
							<?php echo esc_html( wp_trim_words( get_the_excerpt(), 30 ) ); ?>
						</p>
					<?php endif; ?>

					<a class="me-card__cta" href="<?php the_permalink(); ?>">
						<?php esc_html_e( 'Read experience', 'mountain-experience' ); ?>
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