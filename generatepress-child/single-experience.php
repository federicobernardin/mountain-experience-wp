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
	$content_raw      = get_the_content();

	$gallery_shortcode = '';

	if ( preg_match( '/\[foogallery[^\]]*\]/', $content_raw, $matches ) ) {
		$gallery_shortcode = $matches[0];
	} elseif ( preg_match( '/\[gallery[^\]]*\]/', $content_raw, $matches ) ) {
		$gallery_shortcode = $matches[0];
	}

	$content_without_gallery = $gallery_shortcode ? str_replace( $gallery_shortcode, '', $content_raw ) : $content_raw;
	$content_has_gallery     = ! empty( $gallery_shortcode );

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

	$support_points = function_exists( 'get_field' ) ? get_field( 'support_points', $post_id ) : array();

if ( empty( $support_points ) || ! is_array( $support_points ) ) {
	$support_points = array();
}
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

		<nav class="me-experience-nav" aria-label="<?php echo esc_attr( me_translate( 'Experience navigation' ) ); ?>">
			<div class="me-experience-nav__inner">
				<a href="#description"><?php echo esc_html( me_translate( 'Descrizione' ) ); ?></a>

				<?php if ( $gpx_url ) : ?>
					<a href="#route-map"><?php echo esc_html( me_translate( 'Mappa' ) ); ?></a>
					<a href="#route-profile"><?php echo esc_html( me_translate( 'Profilo altimetrico' ) ); ?></a>
				<?php endif; ?>

				<?php if ( $content_has_gallery ) : ?>
					<a href="#experience-gallery"><?php echo esc_html( me_translate( 'Gallery' ) ); ?></a>
				<?php endif; ?>
			</div>
		</nav>

		<div id="description" class="me-experience__layout">
			<article class="me-experience__content">
				<?php echo apply_filters( 'the_content', $content_without_gallery ); ?>
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

				<section class="me-panel me-panel--taxonomy">
					<header class="me-panel__header me-panel__header--with-info">
						<h2 class="me-panel__title"><?php echo esc_html( me_translate( 'Caratteristiche' ) ); ?></h2>

						<div class="me-info-popover">
							<button
								type="button"
								class="me-info-popover__button"
								aria-label="<?php echo esc_attr( me_translate( 'Legenda caratteristiche' ) ); ?>"
								aria-describedby="me-features-legend"
							>
								i
							</button>

							<div id="me-features-legend" class="me-info-popover__content" role="tooltip">
								<strong><?php echo esc_html( me_translate( 'Legenda' ) ); ?></strong>

								<p>
									<?php echo esc_html( me_translate( 'Difficoltà: T = Turistico, E = Escursionistico, EE = Escursionisti esperti, EEA = Escursionisti esperti con attrezzatura.' ) ); ?>
								</p>

								<p>
									<?php echo esc_html( me_translate( 'Esposizione: indica l’orientamento prevalente del percorso.' ) ); ?>
								</p>

								<p>
									<?php echo esc_html( me_translate( 'Materiale: indica l’attrezzatura consigliata o necessaria.' ) ); ?>
								</p>
							</div>
						</div>
					</header>

					<div class="me-taxonomy-list">
						<?php me_the_taxonomy_pills( $post_id, array( 'difficulty', 'exposure', 'equipment' ) ); ?>
					</div>
				</section>

			<?php if ( ! empty( $support_points ) ) : ?>
	<section class="me-panel">
		<header class="me-panel__header">
			<h2 class="me-panel__title"><?php echo esc_html( me_translate( 'Punti di appoggio' ) ); ?></h2>
		</header>

		<div class="me-taxonomy-list">
			<?php foreach ( $support_points as $support_point ) : ?>
				<?php
				$support_point_id = $support_point instanceof WP_Post ? $support_point->ID : absint( $support_point );

				if ( ! $support_point_id ) {
					continue;
				}

				$support_point_title = get_the_title( $support_point_id );

				if ( ! $support_point_title ) {
					continue;
				}
				?>

				<span class="me-taxonomy-pill">
					<?php echo esc_html( $support_point_title ); ?>
				</span>
			<?php endforeach; ?>
		</div>
	</section>
<?php endif; ?>
			</aside>
		</div>

		<?php if ( $gpx_url ) : ?>
			<section id="route-map" class="me-route-section" aria-label="<?php echo esc_attr( me_translate( 'Mappa del percorso' ) ); ?>">
				<div class="me-route-section__inner">
					<div class="me-route-section__header">
						<p class="me-route-section__kicker">
							<?php echo esc_html( me_translate( 'Route map' ) ); ?>
						</p>

						<h2 class="me-route-section__title">
							<?php echo esc_html( me_translate( 'Mappa del percorso' ) ); ?>
						</h2>
					</div>

					<div
						class="me-route-map"
						data-gpx-url="<?php echo esc_url( $gpx_url ); ?>"
						data-distance-label="<?php echo esc_attr( me_translate( 'Distanza' ) ); ?>"
						data-elevation-label="<?php echo esc_attr( me_translate( 'Quota' ) ); ?>"
						data-error-label="<?php echo esc_attr( me_translate( 'Impossibile caricare la traccia GPX.' ) ); ?>"
					>
						<div class="me-route-map__canvas"></div>

						<div id="route-profile" class="me-route-profile">
							<div class="me-route-profile__header">
								<h3><?php echo esc_html( me_translate( 'Profilo altimetrico' ) ); ?></h3>
							</div>

							<div class="me-route-profile__chart-wrap">
								<canvas class="me-route-profile__chart"></canvas>
							</div>
						</div>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<?php if ( $content_has_gallery ) : ?>
			<section id="experience-gallery" class="me-gallery-section" aria-label="<?php echo esc_attr( me_translate( 'Gallery' ) ); ?>">
				<div class="me-gallery-section__inner">
					<div class="me-gallery-section__header">
						<p class="me-gallery-section__kicker">
							<?php echo esc_html( me_translate( 'Gallery' ) ); ?>
						</p>

						<h2 class="me-gallery-section__title">
							<?php echo esc_html( me_translate( 'Gallery fotografica' ) ); ?>
						</h2>
					</div>

					<?php echo do_shortcode( $gallery_shortcode ); ?>
				</div>
			</section>
		<?php endif; ?>

		<?php
		$related_experiences = function_exists( 'get_field' ) ? get_field( 'related_experiences', $post_id ) : array();

		if ( ! empty( $related_experiences ) ) :
			?>
			<section class="me-related-section" aria-label="<?php echo esc_attr( me_translate( 'Esperienze collegate' ) ); ?>">
				<div class="me-related-section__inner">
					<div class="me-related-section__header">
						<p class="me-related-section__kicker">
							<?php echo esc_html( me_translate( 'Related experiences' ) ); ?>
						</p>

						<h2 class="me-related-section__title">
							<?php echo esc_html( me_translate( 'Esperienze collegate' ) ); ?>
						</h2>

						<p class="me-related-section__intro">
							<?php echo esc_html( me_translate( 'Altri itinerari vicini o simili che potrebbero interessarti.' ) ); ?>
						</p>
					</div>

					<div class="me-related-grid">
						<?php foreach ( $related_experiences as $related_post ) : ?>
							<?php
							$related_id        = is_object( $related_post ) ? $related_post->ID : absint( $related_post );
							$related_title     = get_the_title( $related_id );
							$related_url       = get_permalink( $related_id );
							$related_image_url = get_the_post_thumbnail_url( $related_id, 'large' );
							$related_excerpt   = has_excerpt( $related_id ) ? get_the_excerpt( $related_id ) : wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', $related_id ) ), 22 );

							$related_elevation = me_format_field_value( me_get_experience_field( 'elevation_gain', $related_id ), 'm' );
							$related_duration  = me_format_duration( me_get_experience_field( 'duration', $related_id ) );
							$related_length    = me_format_field_value( me_get_experience_field( 'length', $related_id ), 'km' );

							$related_activity_terms = get_the_terms( $related_id, 'activity_type' );
							$related_activity       = '';

							if ( ! empty( $related_activity_terms ) && ! is_wp_error( $related_activity_terms ) ) {
								$related_activity = $related_activity_terms[0]->name;
							}
							?>

							<article class="me-related-card">
								<a class="me-related-card__link" href="<?php echo esc_url( $related_url ); ?>">
									<div class="me-related-card__media">
										<?php if ( $related_image_url ) : ?>
											<img src="<?php echo esc_url( $related_image_url ); ?>" alt="<?php echo esc_attr( $related_title ); ?>">
										<?php endif; ?>

										<?php if ( $related_activity ) : ?>
											<span class="me-related-card__tag"><?php echo esc_html( $related_activity ); ?></span>
										<?php endif; ?>

										<div class="me-related-card__stats">
											<?php if ( $related_elevation ) : ?>
												<span>
													<small><?php echo esc_html( me_translate( 'Dislivello' ) ); ?></small>
													<strong><?php echo esc_html( $related_elevation ); ?></strong>
												</span>
											<?php endif; ?>

											<?php if ( $related_duration ) : ?>
												<span>
													<small><?php echo esc_html( me_translate( 'Durata' ) ); ?></small>
													<strong><?php echo esc_html( $related_duration ); ?></strong>
												</span>
											<?php endif; ?>

											<?php if ( $related_length ) : ?>
												<span>
													<small><?php echo esc_html( me_translate( 'Lunghezza' ) ); ?></small>
													<strong><?php echo esc_html( $related_length ); ?></strong>
												</span>
											<?php endif; ?>
										</div>
									</div>

									<div class="me-related-card__body">
										<h3><?php echo esc_html( $related_title ); ?></h3>

										<?php if ( $related_excerpt ) : ?>
											<p><?php echo esc_html( $related_excerpt ); ?></p>
										<?php endif; ?>

										<span class="me-related-card__cta">
											<?php echo esc_html( me_translate( 'Leggi l’esperienza' ) ); ?>
										</span>
									</div>
								</a>
							</article>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
		<?php endif; ?>
	</main>

	<?php
endwhile;

get_footer();