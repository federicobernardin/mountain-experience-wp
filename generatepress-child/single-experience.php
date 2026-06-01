<?php
/**
 * Single template for the Experience custom post type.
 *
 * @package MountainExperience
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$post_id        = get_the_ID();
	$hero_image_url = get_the_post_thumbnail_url( $post_id, 'full' );
	$excerpt        = has_excerpt() ? get_the_excerpt() : '';
	$gpx_url        = me_get_file_url( me_get_experience_field( 'gpx_file', $post_id ) );
	$technical_data = array(
		array(
			'label' => __( 'Dislivello', 'mountain-experience' ),
			'value' => me_format_field_value( me_get_experience_field( 'elevation_gain', $post_id ), 'm' ),
		),
		array(
			'label' => __( 'Durata', 'mountain-experience' ),
			'value' => me_format_field_value( me_get_experience_field( 'duration', $post_id ) ),
		),
		array(
			'label' => __( 'Lunghezza', 'mountain-experience' ),
			'value' => me_format_field_value( me_get_experience_field( 'length', $post_id ), 'km' ),
		),
		array(
			'label' => __( 'Quota massima', 'mountain-experience' ),
			'value' => me_format_field_value( me_get_experience_field( 'max_altitude', $post_id ), 'm' ),
		),
		array(
			'label' => __( 'Partenza', 'mountain-experience' ),
			'value' => me_format_field_value( me_get_experience_field( 'starting_point', $post_id ) ),
		),
	);
	?>

	<main id="primary" <?php post_class( 'me-experience' ); ?>>
		<section class="me-experience__hero" aria-label="<?php esc_attr_e( 'Experience overview', 'mountain-experience' ); ?>">
			<?php if ( $hero_image_url ) : ?>
				<div
					class="me-experience__hero-image"
					style="background-image: url('<?php echo esc_url( $hero_image_url ); ?>');"
					aria-hidden="true"
				></div>
			<?php endif; ?>

			<div class="me-experience__hero-inner">
				<div class="me-experience__kicker" aria-label="<?php esc_attr_e( 'Experience categories', 'mountain-experience' ); ?>">
					<?php
					$activity_terms = get_the_terms( $post_id, 'activity_type' );

					if ( ! empty( $activity_terms ) && ! is_wp_error( $activity_terms ) ) {
						foreach ( $activity_terms as $term ) {
							printf( '<span class="me-chip">%s</span>', esc_html( $term->name ) );
						}
					}
					?>
				</div>

				<h1 class="me-experience__title"><?php the_title(); ?></h1>

				<?php if ( $excerpt ) : ?>
					<p class="me-experience__subtitle"><?php echo esc_html( $excerpt ); ?></p>
				<?php endif; ?>
			</div>
		</section>

		<div class="me-experience__layout">
			<article class="me-experience__content">
				<?php the_content(); ?>
			</article>

			<aside class="me-experience__meta" aria-label="<?php esc_attr_e( 'Experience technical data', 'mountain-experience' ); ?>">
				<section class="me-panel">
					<header class="me-panel__header">
						<h2 class="me-panel__title"><?php esc_html_e( 'Dati tecnici', 'mountain-experience' ); ?></h2>
					</header>

					<dl class="me-data-list">
						<?php foreach ( $technical_data as $item ) : ?>
							<?php if ( $item['value'] ) : ?>
								<div class="me-data-row">
									<dt><?php echo esc_html( $item['label'] ); ?></dt>
									<dd><?php echo esc_html( $item['value'] ); ?></dd>
								</div>
							<?php endif; ?>
						<?php endforeach; ?>
					</dl>

					<?php if ( $gpx_url ) : ?>
						<a class="me-gpx-link" href="<?php echo esc_url( $gpx_url ); ?>" download>
							<?php esc_html_e( 'Scarica traccia GPX', 'mountain-experience' ); ?>
						</a>
					<?php endif; ?>
				</section>

				<section class="me-panel">
					<header class="me-panel__header">
						<h2 class="me-panel__title"><?php esc_html_e( 'Caratteristiche', 'mountain-experience' ); ?></h2>
					</header>

					<div class="me-taxonomy-list">
						<?php me_the_taxonomy_pills( $post_id, array( 'difficulty', 'exposure', 'equipment' ) ); ?>
					</div>
				</section>
			</aside>
		</div>
	</main>

	<?php
endwhile;

get_footer();
