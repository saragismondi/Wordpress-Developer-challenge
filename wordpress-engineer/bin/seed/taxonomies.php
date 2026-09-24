<?php
/**
 * Seed step 1: sections (categories) and topics (tags).
 *
 * Run with: wp eval-file bin/seed/taxonomies.php
 * Idempotent: existing terms are reused, never duplicated.
 *
 * @package AgroNews_Seed
 */

require_once __DIR__ . '/data.php';

$created_categories = 0;
$created_tags       = 0;

foreach ( agronews_seed_categories() as $slug => $category ) {
	$existing = get_term_by( 'slug', $slug, 'category' );

	if ( $existing instanceof WP_Term ) {
		continue;
	}

	$result = wp_insert_term(
		$category['name'],
		'category',
		array(
			'slug'        => $slug,
			'description' => $category['description'],
		)
	);

	if ( is_wp_error( $result ) ) {
		WP_CLI::warning( sprintf( 'Category %s: %s', $slug, $result->get_error_message() ) );

		continue;
	}

	++$created_categories;
}

foreach ( agronews_seed_tags() as $slug => $name ) {
	if ( get_term_by( 'slug', $slug, 'post_tag' ) instanceof WP_Term ) {
		continue;
	}

	$result = wp_insert_term( $name, 'post_tag', array( 'slug' => $slug ) );

	if ( is_wp_error( $result ) ) {
		WP_CLI::warning( sprintf( 'Tag %s: %s', $slug, $result->get_error_message() ) );

		continue;
	}

	++$created_tags;
}

/*
 * "Uncategorized" only gets in the way of a portal organized by section. It
 * can only be removed once it stops being the default category.
 */
$default_category = get_term_by( 'slug', 'uncategorized', 'category' );
$grains           = get_term_by( 'slug', 'granos', 'category' );

if ( $default_category instanceof WP_Term && $grains instanceof WP_Term ) {
	update_option( 'default_category', $grains->term_id );

	if ( 0 === (int) $default_category->count ) {
		wp_delete_term( $default_category->term_id, 'category' );
	}
}

WP_CLI::success(
	sprintf(
		'Taxonomies ready: %d categories created (%d total), %d tags created (%d total).',
		$created_categories,
		(int) wp_count_terms( array( 'taxonomy' => 'category', 'hide_empty' => false ) ),
		$created_tags,
		(int) wp_count_terms( array( 'taxonomy' => 'post_tag', 'hide_empty' => false ) )
	)
);
