<?php
/**
 * Theme setup: supports, menus, image sizes and widget areas.
 *
 * @package AgroNews
 */

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Hooked into after_setup_theme, which runs before init: some features (post
 * thumbnails among them) are not available any later.
 *
 * @return void
 */
function agronews_setup() {
	load_theme_textdomain( 'agronews', AGRONEWS_DIR . '/languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support( 'responsive-embeds' );

	/*
	 * Card art is 16:9. Registering the sizes the portal actually uses keeps
	 * the front page from downloading full size files.
	 */
	add_image_size( 'agronews-card', 400, 225, true );
	add_image_size( 'agronews-lead', 800, 450, true );

	register_nav_menus(
		array(
			'menu-1' => esc_html__( 'Sections', 'agronews' ),
			'footer' => esc_html__( 'Footer', 'agronews' ),
		)
	);

	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

	add_theme_support(
		'custom-logo',
		array(
			'height'      => 60,
			'width'       => 240,
			'flex-width'  => true,
			'flex-height' => true,
		)
	);
}
add_action( 'after_setup_theme', 'agronews_setup' );

/**
 * Sets the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 *
 * @return void
 */
function agronews_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'agronews_content_width', 800 );
}
add_action( 'after_setup_theme', 'agronews_content_width', 0 );

/**
 * Registers the widget areas.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 *
 * @return void
 */
function agronews_widgets_init() {
	register_sidebar(
		array(
			'name'          => esc_html__( 'Sidebar', 'agronews' ),
			'id'            => 'sidebar-1',
			'description'   => esc_html__( 'Shown next to archives and single posts.', 'agronews' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'agronews_widgets_init' );
