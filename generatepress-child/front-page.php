<?php
/**
 * Front page template for Mountain Experience.
 *
 * @package MountainExperience
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$home_id = get_queried_object_id();

if ( ! $home_id ) {
	$home_id = (int) get_option( 'page_on_front' );
}

/**
 * Return image URL from an ACF image field.
 *
 * @param string $field_name ACF field name.
 * @param int    $post_id    Post ID.
 * @param string $size       Image size.
 * @return string
 */
function me_home_get_image_url( $field_name, $post_id, $size = 'full' ) {
	$image = function_exists( 'get_field' ) ? get_field( $field_name, $post_id ) : '';

	if ( empty( $image ) ) {
		return '';
	}

	if ( is_array( $image ) ) {
		if ( ! empty( $image['sizes'][ $size ] ) ) {
			return $image['sizes'][ $size ];
		}

		if ( ! empty( $image['url'] ) ) {
			return $image['url'];
		}
	}

	if ( is_numeric( $image ) ) {
		return wp_get_attachment_image_url( (int) $image, $size );
	}

	if ( is_string( $image ) ) {
		return $image;
	}

	return '';
}

$hero_image_url            = me_home_get_image_url( 'homepage_hero_image', $home_id, 'full' );
$hiking_image_url          = me_home_get_image_url( 'homepage_hiking_image', $home_id, 'large' );
$via_ferrata_image_url     = me_home_get_image_url( 'homepage_via_ferrata_image', $home_id, 'large' );
$climbing_image_url        = me_home_get_image_url( 'homepage_climbing_image', $home_id, 'large' );
$ski_mount_image_url       = me_home_get_image_url( 'homepage_ski_mountaineering_image', $home_id, 'large' );
$experiences_cta_image_url = me_home_get_image_url( 'homepage_experiences_cta_image', $home_id, 'full' );

$featured_query = new WP_Query(
	array(
		'post_type'      => 'experience',
		'post_status'    => 'publish',
		'posts_per_page' => 3,
		'meta_query'     => array(
			array(
				'key'     => 'featured_on_homepage',
				'value'   => '1',
				'compare' => '=',
			),
		),
		'lang'           => function_exists( 'pll_current_language' ) ? pll_current_language( 'slug' ) : '',
	)
);
?>

<main id="primary" class="me-home">

	<section class="me-home-hero">
		<div
			class="me-home-hero__image"
			<?php if ( $hero_image_url ) : ?>
				style="background-image: url('<?php echo esc_url( $hero_image_url ); ?>');"
			<?php endif; ?>
			aria-hidden="true"
		></div>

		<div class="me-home-hero__overlay"></div>

		<div class="me-home-hero__content">
			<p class="me-home-hero__kicker">
				<?php echo esc_html( me_translate( 'Mountain Experience' ) ); ?>
			</p>

			<h1 class="me-home-hero__title">
				<?php echo esc_html( me_translate( 'Esperienze, itinerari e racconti di montagna' ) ); ?>
			</h1>

			<p class="me-home-hero__text">
				<?php echo esc_html( me_translate( 'Un archivio personale di escursioni, ferrate, arrampicate e scialpinistiche, filtrabile per difficoltà, dislivello, durata e caratteristiche del percorso.' ) ); ?>
			</p>

			<div class="me-home-hero__actions">
				<a class="me-home-button me-home-button--primary" href="<?php echo esc_url( home_url( function_exists( 'pll_current_language' ) && 'en' === pll_current_language( 'slug' ) ? '/en/experiences/' : '/esperienze/' ) ); ?>">
					<?php echo esc_html( me_translate( 'Explore experiences' ) ); ?>
				</a>
			</div>
		</div>
	</section>

	<section class="me-home-section me-home-featured">
		<div class="me-home-section__header">
			<p class="me-home-section__kicker">
				<?php echo esc_html( me_translate( 'Selected routes' ) ); ?>
			</p>

			<h2 class="me-home-section__title">
				<?php echo esc_html( me_translate( 'Esperienze in evidenza' ) ); ?>
			</h2>

			<p class="me-home-section__intro">
				<?php echo esc_html( me_translate( 'Alcune attività selezionate tra le esperienze pubblicate, con dati tecnici e racconto dell’itinerario.' ) ); ?>
			</p>
		</div>

		<?php if ( $featured_query->have_posts() ) : ?>
			<div class="me-home-cards">
				<?php
				while ( $featured_query->have_posts() ) :
					$featured_query->the_post();

					$post_id        = get_the_ID();
					$activity_terms = get_the_terms( $post_id, 'activity_type' );

					$image_facts = array(
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
					);
					?>

					<article <?php post_class( 'me-card' ); ?>>

						<a class="me-card__media" href="<?php the_permalink(); ?>" aria-label="<?php the_title_attribute(); ?>">

							<div class="me-card__image">
								<?php if ( has_post_thumbnail() ) : ?>
									<?php the_post_thumbnail( 'large' ); ?>
								<?php else : ?>
									<div class="me-card__placeholder">
										<span><?php echo esc_html( me_translate( 'Mountain Experience' ) ); ?></span>
									</div>
								<?php endif; ?>
							</div>

							<?php if ( ! empty( $activity_terms ) && ! is_wp_error( $activity_terms ) ) : ?>
								<div class="me-card__badges" aria-label="<?php echo esc_attr( me_translate( 'Activity type' ) ); ?>">
									<?php foreach ( $activity_terms as $term ) : ?>
										<span class="me-card__badge"><?php echo esc_html( $term->name ); ?></span>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>

							<div class="me-card__image-facts" aria-label="<?php echo esc_attr( me_translate( 'Experience technical data' ) ); ?>">
								<?php foreach ( $image_facts as $fact ) : ?>
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

							<h3 class="me-card__title">
								<a href="<?php the_permalink(); ?>">
									<?php the_title(); ?>
								</a>
							</h3>

							<?php if ( has_excerpt() ) : ?>
								<p class="me-card__excerpt">
									<?php echo esc_html( wp_trim_words( get_the_excerpt(), 30 ) ); ?>
								</p>
							<?php endif; ?>

							<a class="me-card__cta" href="<?php the_permalink(); ?>">
								<?php echo esc_html( me_translate( 'Read experience' ) ); ?>
							</a>

						</div>

					</article>

					<?php
				endwhile;
				wp_reset_postdata();
				?>
			</div>
		<?php else : ?>
			<div class="me-home-empty">
				<p><?php echo esc_html( me_translate( 'Nessuna experience trovata con i filtri selezionati.' ) ); ?></p>
			</div>
		<?php endif; ?>
	</section>

	<section class="me-home-section me-home-activities">
		<div class="me-home-section__header">
			<p class="me-home-section__kicker">
				<?php echo esc_html( me_translate( 'Choose your activity' ) ); ?>
			</p>

			<h2 class="me-home-section__title">
				<?php echo esc_html( me_translate( 'Scegli il tipo di esperienza' ) ); ?>
			</h2>
		</div>

		<div class="me-home-activity-grid">

			<a
				class="me-home-activity"
				href="<?php echo esc_url( home_url( function_exists( 'pll_current_language' ) && 'en' === pll_current_language( 'slug' ) ? '/en/experiences/' : '/experiences/' ) ); ?>"
				<?php if ( $hiking_image_url ) : ?>
					style="background-image: linear-gradient(180deg, rgba(24, 34, 29, 0.10), rgba(24, 34, 29, 0.76)), url('<?php echo esc_url( $hiking_image_url ); ?>');"
				<?php endif; ?>
			>
				<span class="me-home-activity__label"><?php echo esc_html( me_translate( 'Hiking' ) ); ?></span>
				<span class="me-home-activity__text"><?php echo esc_html( me_translate( 'Escursioni e trekking' ) ); ?></span>
			</a>

			<a
				class="me-home-activity"
				href="<?php echo esc_url( home_url( function_exists( 'pll_current_language' ) && 'en' === pll_current_language( 'slug' ) ? '/en/experiences/' : '/experiences/' ) ); ?>"
				<?php if ( $via_ferrata_image_url ) : ?>
					style="background-image: linear-gradient(180deg, rgba(24, 34, 29, 0.10), rgba(24, 34, 29, 0.76)), url('<?php echo esc_url( $via_ferrata_image_url ); ?>');"
				<?php endif; ?>
			>
				<span class="me-home-activity__label"><?php echo esc_html( me_translate( 'Via Ferrata' ) ); ?></span>
				<span class="me-home-activity__text"><?php echo esc_html( me_translate( 'Percorsi attrezzati' ) ); ?></span>
			</a>

			<a
				class="me-home-activity"
				href="<?php echo esc_url( home_url( function_exists( 'pll_current_language' ) && 'en' === pll_current_language( 'slug' ) ? '/en/experiences/' : '/experiences/' ) ); ?>"
				<?php if ( $climbing_image_url ) : ?>
					style="background-image: linear-gradient(180deg, rgba(24, 34, 29, 0.10), rgba(24, 34, 29, 0.76)), url('<?php echo esc_url( $climbing_image_url ); ?>');"
				<?php endif; ?>
			>
				<span class="me-home-activity__label"><?php echo esc_html( me_translate( 'Climbing' ) ); ?></span>
				<span class="me-home-activity__text"><?php echo esc_html( me_translate( 'Arrampicate e vie' ) ); ?></span>
			</a>

			<a
				class="me-home-activity"
				href="<?php echo esc_url( home_url( function_exists( 'pll_current_language' ) && 'en' === pll_current_language( 'slug' ) ? '/en/experiences/' : '/experiences/' ) ); ?>"
				<?php if ( $ski_mount_image_url ) : ?>
					style="background-image: linear-gradient(180deg, rgba(24, 34, 29, 0.10), rgba(24, 34, 29, 0.76)), url('<?php echo esc_url( $ski_mount_image_url ); ?>');"
				<?php endif; ?>
			>
				<span class="me-home-activity__label"><?php echo esc_html( me_translate( 'Ski Mountaineering' ) ); ?></span>
				<span class="me-home-activity__text"><?php echo esc_html( me_translate( 'Scialpinismo' ) ); ?></span>
			</a>

		</div>
	</section>

	<section
		class="me-home-cta"
		<?php if ( $experiences_cta_image_url ) : ?>
			style="background-image: linear-gradient(90deg, rgba(24, 34, 29, 0.68), rgba(24, 34, 29, 0.28)), url('<?php echo esc_url( $experiences_cta_image_url ); ?>');"
		<?php endif; ?>
	>
		<div class="me-home-cta__inner">
			<h2><?php echo esc_html( me_translate( 'Trova l’esperienza più adatta a te' ) ); ?></h2>

			<p>
				<?php echo esc_html( me_translate( 'Usa i filtri per cercare attività in base a dislivello, difficoltà, esposizione, durata, lunghezza e materiale necessario.' ) ); ?>
			</p>

			<a class="me-home-button me-home-button--primary" href="<?php echo esc_url( home_url( function_exists( 'pll_current_language' ) && 'en' === pll_current_language( 'slug' ) ? '/en/experiences/' : '/esperienze/' ) ); ?>">
				<?php echo esc_html( me_translate( 'Open experiences archive' ) ); ?>
			</a>
		</div>
	</section>

</main>

<?php
get_footer();