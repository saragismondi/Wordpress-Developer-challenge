<?php
/**
 * Seed step 4: pages, menu and plugin settings.
 *
 * Run with: wp eval-file bin/seed/options.php
 * Idempotent: pages and menu items are looked up before being created, and
 * the plugin settings are rewritten to the same deterministic values.
 *
 * @package AgroNews_Seed
 */

require_once __DIR__ . '/data.php';

/**
 * Returns a page by slug, creating it when missing.
 *
 * @param string $slug    Page slug.
 * @param string $title   Page title.
 * @param string $content Page content.
 * @return int Page ID, or 0 on failure.
 */
function agronews_seed_ensure_page( $slug, $title, $content = '' ) {
	$page = get_page_by_path( $slug );

	if ( $page instanceof WP_Post ) {
		return (int) $page->ID;
	}

	$page_id = wp_insert_post(
		array(
			'post_type'      => 'page',
			'post_name'      => $slug,
			'post_title'     => $title,
			'post_content'   => $content,
			'post_status'    => 'publish',
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
		),
		true
	);

	if ( is_wp_error( $page_id ) ) {
		WP_CLI::warning( sprintf( 'Page %s: %s', $slug, $page_id->get_error_message() ) );

		return 0;
	}

	return (int) $page_id;
}

/*
 * The portal home is rendered by front-page.php. A static front page is what
 * frees "Blog" to act as the posts page.
 */
$home_id = agronews_seed_ensure_page( 'home', 'Home', 'Portada de AgroNews.' );
$blog_id = agronews_seed_ensure_page( 'blog', 'Blog', '' );

if ( $home_id > 0 && $blog_id > 0 ) {
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $home_id );
	update_option( 'page_for_posts', $blog_id );
	update_option( 'posts_per_page', 12 );
}

// Settings of the AgroNews Home plugin: real categories and valid quotes.
$featured = get_term_by( 'slug', 'mercados', 'category' );
$sections = array( 'granos', 'ganaderia', 'clima' );
$section_ids = array();

foreach ( $sections as $slug ) {
	$term          = get_term_by( 'slug', $slug, 'category' );
	$section_ids[] = $term instanceof WP_Term ? (int) $term->term_id : 0;
}

$quotes = array();

foreach ( agronews_seed_quotes() as $key => $value ) {
	$quotes[ $key ] = (string) $value;
}

if ( function_exists( 'agronews_home_sanitize_settings' ) ) {
	update_option(
		AGRONEWS_HOME_OPTION,
		agronews_home_sanitize_settings(
			array(
				'featured_category'  => $featured instanceof WP_Term ? (int) $featured->term_id : 0,
				'section_categories' => $section_ids,
				'quotes'             => $quotes,
			)
		)
	);
} else {
	WP_CLI::warning( 'AgroNews Home is not active: skipping its settings.' );
}

// Primary menu: the sections of the portal plus the blog.
$menu_name = 'Primary';
$menu      = wp_get_nav_menu_object( $menu_name );

if ( ! $menu ) {
	$menu_id = wp_create_nav_menu( $menu_name );

	if ( is_wp_error( $menu_id ) ) {
		WP_CLI::warning( sprintf( 'Menu: %s', $menu_id->get_error_message() ) );
		$menu_id = 0;
	}
} else {
	$menu_id = (int) $menu->term_id;
}

if ( $menu_id > 0 ) {
	$items    = wp_get_nav_menu_items( $menu_id );
	$existing = array();

	if ( is_array( $items ) ) {
		foreach ( $items as $item ) {
			$existing[ $item->object . ':' . $item->object_id ] = true;
		}
	}

	$position = count( $existing );

	foreach ( array( 'granos', 'ganaderia', 'lecheria', 'clima', 'mercados', 'maquinaria' ) as $slug ) {
		$term = get_term_by( 'slug', $slug, 'category' );

		if ( ! $term instanceof WP_Term || isset( $existing[ 'category:' . $term->term_id ] ) ) {
			continue;
		}

		++$position;

		wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-object'    => 'category',
				'menu-item-object-id' => (int) $term->term_id,
				'menu-item-type'      => 'taxonomy',
				'menu-item-status'    => 'publish',
				'menu-item-position'  => $position,
			)
		);
	}

	if ( $blog_id > 0 && ! isset( $existing[ 'page:' . $blog_id ] ) ) {
		wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $blog_id,
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
				'menu-item-position'  => $position + 1,
			)
		);
	}

	$locations = get_theme_mod( 'nav_menu_locations' );
	$locations = is_array( $locations ) ? $locations : array();

	$locations['menu-1'] = $menu_id;

	set_theme_mod( 'nav_menu_locations', $locations );
}

WP_CLI::success( 'Pages, menu and plugin settings ready.' );
