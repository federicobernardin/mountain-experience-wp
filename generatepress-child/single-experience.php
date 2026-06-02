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

	$post_id          = get_the_ID();
	$hero_image_url   = get_the_post_thumbnail_url( $post_id, 'full' );
	$excerpt          = has_excerpt() ? get_the_excerpt() : '';
	$gpx_url          = me_get_file_url( me_get_experience_field( 'gpx_file', $post_id ) );
	$difficulty       = '';
	$difficulty_terms = get_the_terms( $post_id, 'difficulty' );

	if ( ! empty( $difficulty_terms ) && ! is_wp_error( $difficulty_terms ) ) {
		$difficulty_term = reset( $difficulty_terms );
		$difficulty      = $difficulty_term->name;
	}

	$technical_data = array(
		array(
			'label' => me_translate( 'Dislivello' ),
			'value' => me_format_field_value( me_get_experience_field( 'elevation_gain', $post_id ), 'm' ),
		),
		array(
			'label' => me_translate( 'Durata' ),
			'value' => me_format_duration( me_get_experience_field( 'duration', $post_id ) ),
		),
		array(
			'label' => me_translate( 'Lunghezza' ),
			'value' => me_format_field_value( me_get_experience_field( 'length', $post_id ), 'km' ),
		),
		array(
			'label' => me_translate( 'Quota massima' ),
			'value' => me_format_field_value( me_get_experience_field( 'max_altitude', $post_id ), 'm' ),
		),
		array(
			'label' => me_translate( 'Partenza' ),
			'value' => me_format_field_value( me_get_experience_field( 'starting_point', $post_id ) ),
		),
	);

	$summary_data = array(
		array(
			'label' => me_translate( 'Difficulty' ),
			'value' => $difficulty,
		),
		array(
			'label' => me_translate( 'Elevation gain' ),
			'value' => me_format_field_value( me_get_experience_field( 'elevation_gain', $post_id ), 'm' ),
		),
		array(
			'label' => me_translate( 'Duration' ),
			'value' => me_format_duration( me_get_experience_field( 'duration', $post_id ) ),
		),
		array(
			'label' => me_translate( 'Length' ),
			'value' => me_format_field_value( me_get_experience_field( 'length', $post_id ), 'km' ),
		),
		array(
			'label' => me_translate( 'Max altitude' ),
			'value' => me_format_field_value( me_get_experience_field( 'max_altitude', $post_id ), 'm' ),
		),
	);
	?>

	<main id="primary" <?php post_class( 'me-experience' ); ?>>
		<section class="me-experience__hero" aria-label="<?php echo esc_attr( me_translate( 'Experience overview' ) ); ?>">
			<?php if ( $hero_image_url ) : ?>
				<div
					class="me-experience__hero-image"
					style="background-image: url('<?php echo esc_url( $hero_image_url ); ?>');"
					aria-hidden="true"
				></div>
			<?php endif; ?>

			<div class="me-experience__hero-inner">
				<div class="me-experience__kicker" aria-label="<?php echo esc_attr( me_translate( 'Experience categories' ) ); ?>">
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

		<section class="me-summary-bar" aria-label="<?php echo esc_attr( me_translate( 'Experience summary' ) ); ?>">
			<div class="me-summary-bar__inner">
				<?php foreach ( $summary_data as $item ) : ?>
					<?php if ( $item['value'] ) : ?>
						<div class="me-summary-item">
							<span class="me-summary-item__label"><?php echo esc_html( $item['label'] ); ?></span>
							<strong class="me-summary-item__value"><?php echo esc_html( $item['value'] ); ?></strong>
						</div>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		</section>

		<div class="me-experience__layout">
			<article class="me-experience__content">
				<?php the_content(); ?>
			</article>

			<aside class="me-experience__meta" aria-label="<?php echo esc_attr( me_translate( 'Experience technical data' ) ); ?>">
				<section class="me-panel">
					<header class="me-panel__header">
						<h2 class="me-panel__title"><?php echo esc_html( me_translate( 'Dati tecnici' ) ); ?></h2>
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
							<?php echo esc_html( me_translate( 'Scarica traccia GPX' ) ); ?>
						</a>
					<?php endif; ?>
				</section>

				<section class="me-panel">
					<header class="me-panel__header">
						<h2 class="me-panel__title"><?php echo esc_html( me_translate( 'Caratteristiche' ) ); ?></h2>
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