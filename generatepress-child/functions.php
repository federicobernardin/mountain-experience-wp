<?php
/**
 * Mountain Experience child theme functions.
 *
 * @package MountainExperience
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* =========================================================
   Assets
   ========================================================= */

add_action( 'wp_enqueue_scripts', 'me_child_enqueue_styles' );
/**
 * Load GeneratePress parent styles and child theme styles.
 */
function me_child_enqueue_styles() {
	wp_enqueue_style(
		'generatepress-parent',
		get_template_directory_uri() . '/style.css',
		array(),
		wp_get_theme( 'generatepress' )->get( 'Version' )
	);

	wp_enqueue_style(
		'me-child',
		get_stylesheet_uri(),
		array( 'generatepress-parent' ),
		wp_get_theme()->get( 'Version' )
	);
}

add_action( 'wp_enqueue_scripts', 'me_child_enqueue_scripts' );
/**
 * Load child theme scripts.
 */
function me_child_enqueue_scripts() {
	wp_enqueue_script(
		'me-filters-accordion',
		get_stylesheet_directory_uri() . '/assets/js/filters-accordion.js',
		array(),
		wp_get_theme()->get( 'Version' ),
		true
	);

	if ( is_singular( 'experience' ) ) {
		wp_enqueue_style(
			'leaflet',
			'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
			array(),
			'1.9.4'
		);

		wp_enqueue_script(
			'leaflet',
			'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
			array(),
			'1.9.4',
			true
		);

		wp_enqueue_script(
			'chart-js',
			'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js',
			array(),
			'4.4.1',
			true
		);

		wp_enqueue_script(
			'me-experience-map',
			get_stylesheet_directory_uri() . '/assets/js/experience-map.js',
			array( 'leaflet', 'chart-js' ),
			wp_get_theme()->get( 'Version' ),
			true
		);
	}
}

/* =========================================================
   Custom Post Type and taxonomies
   ========================================================= */

add_action( 'init', 'me_register_experience_content_types' );
/**
 * Register the Experience CPT and its taxonomies.
 */
function me_register_experience_content_types() {
	register_post_type(
		'experience',
		array(
			'labels'       => array(
				'name'               => __( 'Experiences', 'mountain-experience' ),
				'singular_name'      => __( 'Experience', 'mountain-experience' ),
				'add_new_item'       => __( 'Add New Experience', 'mountain-experience' ),
				'edit_item'          => __( 'Edit Experience', 'mountain-experience' ),
				'new_item'           => __( 'New Experience', 'mountain-experience' ),
				'view_item'          => __( 'View Experience', 'mountain-experience' ),
				'search_items'       => __( 'Search Experiences', 'mountain-experience' ),
				'not_found'          => __( 'No experiences found', 'mountain-experience' ),
				'not_found_in_trash' => __( 'No experiences found in Trash', 'mountain-experience' ),
			),
			'public'       => true,
			'has_archive'  => false,
			'menu_icon'    => 'dashicons-location-alt',
			'rewrite'      => array( 'slug' => 'experiences' ),
			'show_in_rest' => true,
			'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ),
		)
	);

	$taxonomies = array(
		'activity_type' => array(
			'singular' => __( 'Activity Type', 'mountain-experience' ),
			'plural'   => __( 'Activity Types', 'mountain-experience' ),
			'slug'     => 'activity-type',
		),
		'difficulty'    => array(
			'singular' => __( 'Difficulty', 'mountain-experience' ),
			'plural'   => __( 'Difficulties', 'mountain-experience' ),
			'slug'     => 'difficulty',
		),
		'exposure'      => array(
			'singular' => __( 'Exposure', 'mountain-experience' ),
			'plural'   => __( 'Exposures', 'mountain-experience' ),
			'slug'     => 'exposure',
		),
		'equipment'     => array(
			'singular' => __( 'Equipment', 'mountain-experience' ),
			'plural'   => __( 'Equipment', 'mountain-experience' ),
			'slug'     => 'equipment',
		),
	);

	foreach ( $taxonomies as $taxonomy => $labels ) {
		register_taxonomy(
			$taxonomy,
			'experience',
			array(
				'labels'            => array(
					'name'          => $labels['plural'],
					'singular_name' => $labels['singular'],
					'search_items'  => sprintf(
						/* translators: %s: taxonomy plural label. */
						__( 'Search %s', 'mountain-experience' ),
						$labels['plural']
					),
					'all_items'     => sprintf(
						/* translators: %s: taxonomy plural label. */
						__( 'All %s', 'mountain-experience' ),
						$labels['plural']
					),
					'edit_item'     => sprintf(
						/* translators: %s: taxonomy singular label. */
						__( 'Edit %s', 'mountain-experience' ),
						$labels['singular']
					),
					'add_new_item'  => sprintf(
						/* translators: %s: taxonomy singular label. */
						__( 'Add New %s', 'mountain-experience' ),
						$labels['singular']
					),
				),
				'hierarchical'      => true,
				'public'            => true,
				'rewrite'           => array( 'slug' => $labels['slug'] ),
				'show_admin_column' => true,
				'show_in_rest'      => true,
			)
		);
	}
}

/* =========================================================
   ACF helpers
   ========================================================= */

/**
 * Return an ACF field value when ACF is active, with post meta fallback.
 *
 * @param string $field   Field name.
 * @param int    $post_id Post ID.
 * @return mixed
 */
function me_get_experience_field( $field, $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();

	if ( function_exists( 'get_field' ) ) {
		$value = get_field( $field, $post_id );
	} else {
		$value = get_post_meta( $post_id, $field, true );
	}

	return $value;
}

/**
 * Format a scalar ACF value with an optional unit.
 *
 * @param mixed  $value Field value.
 * @param string $unit  Optional unit.
 * @return string
 */
function me_format_field_value( $value, $unit = '' ) {
	if ( is_array( $value ) || '' === $value || null === $value ) {
		return '';
	}

	$value = trim( wp_strip_all_tags( (string) $value ) );

	if ( '' === $value ) {
		return '';
	}

	return trim( $value . ( $unit ? ' ' . $unit : '' ) );
}

/**
 * Format a duration value in minutes.
 *
 * @param mixed $minutes Duration in minutes.
 * @return string
 */
function me_format_duration( $minutes ) {
	if ( '' === $minutes || null === $minutes || is_array( $minutes ) ) {
		return '';
	}

	if ( ! is_numeric( $minutes ) || $minutes <= 0 ) {
		return '';
	}

	$minutes = absint( $minutes );

	if ( $minutes <= 0 ) {
		return '';
	}

	$hours             = floor( $minutes / 60 );
	$remaining_minutes = $minutes % 60;

	if ( $hours && $remaining_minutes ) {
		return sprintf(
			'%1$dh %2$dm',
			$hours,
			$remaining_minutes
		);
	}

	if ( $hours ) {
		return sprintf( '%dh', $hours );
	}

	return sprintf( '%dm', $remaining_minutes );
}

/**
 * Return a URL from an ACF file field, supporting URL, ID, and array formats.
 *
 * @param mixed $file Field value.
 * @return string
 */
function me_get_file_url( $file ) {
	if ( empty( $file ) ) {
		return '';
	}

	if ( is_numeric( $file ) ) {
		return wp_get_attachment_url( (int) $file );
	}

	if ( is_array( $file ) && ! empty( $file['url'] ) ) {
		return $file['url'];
	}

	if ( is_string( $file ) ) {
		return $file;
	}

	return '';
}

/* =========================================================
   Template helpers
   ========================================================= */

/**
 * Render taxonomy terms as small pills.
 *
 * @param int      $post_id    Post ID.
 * @param string[] $taxonomies Taxonomy names.
 * @return void
 */
function me_the_taxonomy_pills( $post_id, $taxonomies ) {
	foreach ( $taxonomies as $taxonomy ) {
		$terms = get_the_terms( $post_id, $taxonomy );

		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			continue;
		}

		foreach ( $terms as $term ) {
			printf(
				'<span class="me-taxonomy-pill">%s</span>',
				esc_html( $term->name )
			);
		}
	}
}

/* =========================================================
   Polylang helpers and strings
   ========================================================= */

/**
 * Translate a theme string with Polylang fallback.
 *
 * @param string $string String to translate.
 * @return string
 */
function me_translate( $string ) {
	if ( function_exists( 'pll__' ) ) {
		return pll__( $string );
	}

	return __( $string, 'mountain-experience' );
}

add_action( 'init', 'me_register_polylang_strings' );
/**
 * Register theme strings for Polylang translations.
 */
function me_register_polylang_strings() {
	if ( ! function_exists( 'pll_register_string' ) ) {
		return;
	}

		$strings = array(
		// Archive / cards.
		'Dislivello',
		'Durata',
		'Lunghezza',
		'Quota max',
		'Quota massima',
		'Nessuna experience trovata con i filtri selezionati.',
		'Read experience',
		'View experience',
		'Activity type',
		'Experience technical data',
		'Results pagination',

		// Single experience.
		'Dati tecnici',
		'Caratteristiche',
		'Partenza',
		'Scarica traccia GPX',
		'Difficulty',
		'Elevation gain',
		'Duration',
		'Length',
		'Max altitude',
		'Experience overview',
		'Experience categories',
		'Experience summary',
		'Technical details',
		'Route features',
		'Starting point',

		// Homepage.
		'Download GPX track',
		'Mountain Experience',
		'Esperienze, itinerari e racconti di montagna',
		'Un archivio personale di escursioni, ferrate, arrampicate e scialpinistiche, filtrabile per difficoltà, dislivello, durata e caratteristiche del percorso.',
		'Explore experiences',
		'Selected routes',
		'Esperienze in evidenza',
		'Alcune attività selezionate tra le esperienze pubblicate, con dati tecnici e racconto dell’itinerario.',
		'Choose your activity',
		'Scegli il tipo di esperienza',
		'Hiking',
		'Escursioni e trekking',
		'Via Ferrata',
		'Percorsi attrezzati',
		'Climbing',
		'Arrampicate e vie',
		'Ski Mountaineering',
		'Scialpinismo',
		'Trova l’esperienza più adatta a te',
		'Usa i filtri per cercare attività in base a dislivello, difficoltà, esposizione, durata, lunghezza e materiale necessario.',
		'Open experiences archive',
		'Featured experiences',
		'Choose your activity type',

		// Route map.
		'Find the right experience for you',
		'Mappa del percorso',
		'Profilo altimetrico',
		'Route map',
		'Elevation profile',
		'Distanza',
		'Quota',
		'Distance',
		'Elevation',
		'Impossibile caricare la traccia GPX.',

		// Sticky experience menu and legend.
		'GPX track could not be loaded.',
		'Experience navigation',
		'Descrizione',
		'Mappa',
		'Gallery',
		'Legenda caratteristiche',
		'Legenda',
		'Difficoltà: T = Turistico, E = Escursionistico, EE = Escursionisti esperti, EEA = Escursionisti esperti con attrezzatura.',
		'Esposizione: indica l’orientamento prevalente del percorso.',
		'Materiale: indica l’attrezzatura consigliata o necessaria.',

		// Support points.
'Punti di appoggio',
'Rifugio',
'Bivacco',
'Malga',
'Punto di ristoro',
'Altro',
'Vai al sito',
	);

	foreach ( $strings as $string ) {
		pll_register_string(
			'mountain-experience-' . sanitize_title( $string ),
			$string,
			'Mountain Experience Theme'
		);
	}
}

/* =========================================================
   Search & Filter Pro + Polylang
   ========================================================= */

add_filter( 'sf_edit_query_args', 'me_search_filter_respect_polylang_language', 20 );
/**
 * Force Search & Filter Pro results to respect the current Polylang language.
 *
 * This prevents the English Experiences page from showing Italian experiences.
 *
 * @param array $query_args Search & Filter query arguments.
 * @return array
 */
function me_search_filter_respect_polylang_language( $query_args ) {
	if ( function_exists( 'pll_current_language' ) ) {
		$current_language = pll_current_language( 'slug' );

		if ( ! empty( $current_language ) ) {
			$query_args['lang']             = $current_language;
			$query_args['suppress_filters'] = false;
		}
	}

	return $query_args;
}

/* =========================================================
   Allow GPX uploads
   ========================================================= */

add_filter( 'upload_mimes', 'me_allow_gpx_uploads' );
/**
 * Allow GPX files in WordPress Media Library.
 *
 * @param array $mimes Allowed mime types.
 * @return array
 */
function me_allow_gpx_uploads( $mimes ) {
	$mimes['gpx'] = 'application/gpx+xml';

	return $mimes;
}

add_filter( 'wp_check_filetype_and_ext', 'me_fix_gpx_filetype_check', 10, 5 );
/**
 * Help WordPress validate GPX files correctly.
 *
 * @param array       $data     File type data.
 * @param string      $file     Full path to the file.
 * @param string      $filename File name.
 * @param string[]    $mimes    Allowed mime types.
 * @param string|null $real_mime Real mime type.
 * @return array
 */
function me_fix_gpx_filetype_check( $data, $file, $filename, $mimes, $real_mime ) {
	$file_ext = pathinfo( $filename, PATHINFO_EXTENSION );

	if ( 'gpx' === strtolower( $file_ext ) ) {
		$data['ext']  = 'gpx';
		$data['type'] = 'application/gpx+xml';
	}

	return $data;
}

/**
 * Localize FooGallery Image Viewer buttons.
 */
function me_localize_foogallery_image_viewer_buttons() {
	if ( ! is_singular( 'experience' ) ) {
		return;
	}

	$current_language = function_exists( 'pll_current_language' ) ? pll_current_language() : 'it';

	$prev_label = 'it' === $current_language ? 'Indietro' : 'Prev';
	$next_label = 'it' === $current_language ? 'Avanti' : 'Next';
	?>
	<script>
		document.addEventListener('DOMContentLoaded', function () {
			const prevLabel = <?php echo wp_json_encode( $prev_label ); ?>;
			const nextLabel = <?php echo wp_json_encode( $next_label ); ?>;

			function updateFooGalleryLabels() {
				document.querySelectorAll('.foogallery-image-viewer .fiv-prev').forEach(function (button) {
					if (button.getAttribute('data-me-localized') === '1') {
						return;
					}

					button.setAttribute('title', prevLabel);
					button.setAttribute('data-me-localized', '1');

					const span = button.querySelector('span');
					if (span) {
						span.textContent = prevLabel;
					}
				});

				document.querySelectorAll('.foogallery-image-viewer .fiv-next').forEach(function (button) {
					if (button.getAttribute('data-me-localized') === '1') {
						return;
					}

					button.setAttribute('title', nextLabel);
					button.setAttribute('data-me-localized', '1');

					const span = button.querySelector('span');
					if (span) {
						span.textContent = nextLabel;
					}
				});
			}

			updateFooGalleryLabels();

			window.setTimeout(updateFooGalleryLabels, 500);
			window.setTimeout(updateFooGalleryLabels, 1200);
		});
	</script>
	<?php
}
add_action( 'wp_footer', 'me_localize_foogallery_image_viewer_buttons', 30 );

/* =========================================================
   Support Points CPT
   ========================================================= */

/**
 * Register Support Points custom post type.
 */
function me_register_support_point_post_type() {
	if ( post_type_exists( 'support_point' ) ) {
		return;
	}

	$labels = array(
		'name'               => __( 'Punti di appoggio', 'mountain-experience' ),
		'singular_name'      => __( 'Punto di appoggio', 'mountain-experience' ),
		'menu_name'          => __( 'Punti di appoggio', 'mountain-experience' ),
		'add_new'            => __( 'Aggiungi nuovo', 'mountain-experience' ),
		'add_new_item'       => __( 'Aggiungi punto di appoggio', 'mountain-experience' ),
		'edit_item'          => __( 'Modifica punto di appoggio', 'mountain-experience' ),
		'new_item'           => __( 'Nuovo punto di appoggio', 'mountain-experience' ),
		'view_item'          => __( 'Vedi punto di appoggio', 'mountain-experience' ),
		'search_items'       => __( 'Cerca punti di appoggio', 'mountain-experience' ),
		'not_found'          => __( 'Nessun punto di appoggio trovato', 'mountain-experience' ),
		'not_found_in_trash' => __( 'Nessun punto di appoggio nel cestino', 'mountain-experience' ),
	);

	$args = array(
		'labels'              => $labels,
		'public'              => false,
		'publicly_queryable'  => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_rest'        => true,
		'menu_icon'           => 'dashicons-location-alt',
		'capability_type'     => 'post',
		'hierarchical'        => false,
		'supports'            => array( 'title' ),
		'has_archive'         => false,
		'exclude_from_search' => true,
	);

	register_post_type( 'support_point', $args );
}
add_action( 'init', 'me_register_support_point_post_type', 5 );


/* =========================================================
   Polylang - translatable support points
   ========================================================= */

/**
 * Make Support Points translatable with Polylang.
 *
 * @param array $post_types Post types handled by Polylang.
 * @param bool  $is_settings Whether the list is used in Polylang settings.
 * @return array
 */
function me_polylang_translate_support_points( $post_types, $is_settings ) {
	$post_types['support_point'] = 'support_point';

	return $post_types;
}
add_filter( 'pll_get_post_types', 'me_polylang_translate_support_points', 20, 2 );