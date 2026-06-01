<?php
/**
 * Mountain Experience child theme functions.
 *
 * @package MountainExperience
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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

add_action( 'init', 'me_register_experience_content_types' );
/**
 * Register the experience CPT and its taxonomies.
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
			'has_archive'  => true,
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
