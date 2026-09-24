<?php
/**
 * Seed step 3: the stories, with a generated featured image each.
 *
 * Run with: wp eval-file bin/seed/posts.php
 *
 * Idempotent: every story carries an an_seed_uid meta with its index, and the
 * script only creates the indexes that are missing. Re-running after an
 * interrupted seed resumes where it stopped.
 *
 * Images are drawn with GD at run time (800x450, a different colour and
 * number per story): nothing is downloaded.
 *
 * @package AgroNews_Seed
 */

require_once __DIR__ . '/data.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

global $wpdb;

$target = (int) getenv( 'AN_SEED_POSTS' );

if ( $target <= 0 ) {
	$target = 2000;
}

mt_srand( AGRONEWS_SEED_RANDOM_SEED );

// Bulk import settings: no revisions, no per post term recount, GD over
// Imagick (faster and consistent across machines for flat colour images).
add_filter( 'wp_revisions_to_keep', '__return_zero' );
add_filter(
	'wp_image_editors',
	static function () {
		return array( 'WP_Image_Editor_GD' );
	}
);

wp_defer_term_counting( true );

$categories = array();

foreach ( array_keys( agronews_seed_categories() ) as $slug ) {
	$term = get_term_by( 'slug', $slug, 'category' );

	if ( $term instanceof WP_Term ) {
		$categories[ $slug ] = (int) $term->term_id;
	}
}

$tags = array();

foreach ( array_keys( agronews_seed_tags() ) as $slug ) {
	$term = get_term_by( 'slug', $slug, 'post_tag' );

	if ( $term instanceof WP_Term ) {
		$tags[] = (int) $term->term_id;
	}
}

if ( empty( $categories ) || empty( $tags ) ) {
	WP_CLI::error( 'Run bin/seed/taxonomies.php first: no categories or tags found.' );
}

$authors = array();

foreach ( agronews_seed_users() as $definition ) {
	$user = get_user_by( 'login', $definition['user_login'] );

	if ( $user instanceof WP_User ) {
		$authors[] = (int) $user->ID;
	}
}

if ( empty( $authors ) ) {
	WP_CLI::error( 'Run bin/seed/users.php first: no authors found.' );
}

$category_slugs = array_keys( $categories );
$category_names = wp_list_pluck( agronews_seed_categories(), 'name' );
$vocabulary     = agronews_seed_vocabulary();
$headlines      = agronews_seed_headlines();

/**
 * Builds a lorem-style sentence.
 *
 * @param string[] $vocabulary Words to draw from.
 * @param int      $words      Number of words.
 * @return string Sentence, capitalized and closed with a period.
 */
function agronews_seed_sentence( array $vocabulary, $words ) {
	$picked = array();

	for ( $i = 0; $i < $words; $i++ ) {
		$picked[] = $vocabulary[ mt_rand( 0, count( $vocabulary ) - 1 ) ];
	}

	$sentence = implode( ' ', $picked );

	return ucfirst( $sentence ) . '.';
}

/**
 * Builds the body of a story.
 *
 * @param string[] $vocabulary Words to draw from.
 * @return string Post content, as classic paragraphs.
 */
function agronews_seed_content( array $vocabulary ) {
	$paragraphs = array();
	$total      = mt_rand( 4, 7 );

	for ( $p = 0; $p < $total; $p++ ) {
		$sentences = array();
		$count     = mt_rand( 3, 6 );

		for ( $s = 0; $s < $count; $s++ ) {
			$sentences[] = agronews_seed_sentence( $vocabulary, mt_rand( 8, 18 ) );
		}

		$paragraphs[] = implode( ' ', $sentences );
	}

	return implode( "\n\n", $paragraphs );
}

/**
 * Converts an HSL colour to RGB.
 *
 * @param float $hue        Hue, 0-360.
 * @param float $saturation Saturation, 0-1.
 * @param float $lightness  Lightness, 0-1.
 * @return int[] Red, green and blue, 0-255.
 */
function agronews_seed_hsl_to_rgb( $hue, $saturation, $lightness ) {
	$chroma   = ( 1 - abs( 2 * $lightness - 1 ) ) * $saturation;
	$sector   = $hue / 60;
	$second   = $chroma * ( 1 - abs( fmod( $sector, 2 ) - 1 ) );
	$modifier = $lightness - $chroma / 2;

	if ( $sector < 1 ) {
		$rgb = array( $chroma, $second, 0 );
	} elseif ( $sector < 2 ) {
		$rgb = array( $second, $chroma, 0 );
	} elseif ( $sector < 3 ) {
		$rgb = array( 0, $chroma, $second );
	} elseif ( $sector < 4 ) {
		$rgb = array( 0, $second, $chroma );
	} elseif ( $sector < 5 ) {
		$rgb = array( $second, 0, $chroma );
	} else {
		$rgb = array( $chroma, 0, $second );
	}

	return array(
		(int) round( ( $rgb[0] + $modifier ) * 255 ),
		(int) round( ( $rgb[1] + $modifier ) * 255 ),
		(int) round( ( $rgb[2] + $modifier ) * 255 ),
	);
}

/**
 * Draws a text label scaled up from the built-in bitmap font.
 *
 * GD ships no TTF in this image, so the label is rendered small and resampled
 * onto the canvas.
 *
 * @param resource|GdImage $canvas Target image.
 * @param string           $text   Text to draw.
 * @param int              $x      Left position on the canvas.
 * @param int              $y      Top position on the canvas.
 * @param int              $width  Target width.
 * @param int              $height Target height.
 * @return void
 */
function agronews_seed_draw_label( $canvas, $text, $x, $y, $width, $height ) {
	$font_width  = imagefontwidth( 5 ) * max( 1, strlen( $text ) );
	$font_height = imagefontheight( 5 );

	$layer = imagecreatetruecolor( $font_width, $font_height );
	imagealphablending( $layer, false );
	imagesavealpha( $layer, true );
	imagefill( $layer, 0, 0, imagecolorallocatealpha( $layer, 0, 0, 0, 127 ) );
	imagestring( $layer, 5, 0, 0, $text, imagecolorallocate( $layer, 255, 255, 255 ) );

	imagealphablending( $canvas, true );
	imagecopyresampled( $canvas, $layer, $x, $y, 0, 0, $width, $height, $font_width, $font_height );
	imagedestroy( $layer );
}

/**
 * Generates the featured image of a story and attaches it.
 *
 * @param int    $post_id   Story the image belongs to.
 * @param int    $index     Story index, printed on the image.
 * @param string $title     Story title, used as the alt text.
 * @param string $post_date Story date, so the file lands in the right month.
 * @return int Attachment ID, or 0 on failure.
 */
function agronews_seed_attach_image( $post_id, $index, $title, $post_date ) {
	$upload = wp_upload_dir( $post_date );

	if ( ! empty( $upload['error'] ) ) {
		return 0;
	}

	$width  = 800;
	$height = 450;

	$canvas = imagecreatetruecolor( $width, $height );

	// A different hue per story, spread with the golden angle.
	$hue  = fmod( $index * 137.508, 360 );
	$base = agronews_seed_hsl_to_rgb( $hue, 0.52, 0.38 );
	$dark = agronews_seed_hsl_to_rgb( $hue, 0.58, 0.26 );

	$base_color = imagecolorallocate( $canvas, $base[0], $base[1], $base[2] );
	$dark_color = imagecolorallocate( $canvas, $dark[0], $dark[1], $dark[2] );

	imagefilledrectangle( $canvas, 0, 0, $width, $height, $base_color );

	// Diagonal band, so the cards do not look like flat colour swatches.
	imagefilledpolygon(
		$canvas,
		array( 0, $height, $width, $height - 220, $width, $height, 0, $height ),
		$dark_color
	);

	agronews_seed_draw_label( $canvas, 'AGRONEWS', 48, 48, 320, 56 );
	agronews_seed_draw_label( $canvas, '#' . $index, 48, 300, 60 * strlen( '#' . $index ), 110 );

	$filename = wp_unique_filename( $upload['path'], sprintf( 'agronews-%05d.jpg', $index ) );
	$path     = trailingslashit( $upload['path'] ) . $filename;

	imagejpeg( $canvas, $path, 82 );
	imagedestroy( $canvas );

	if ( ! file_exists( $path ) ) {
		return 0;
	}

	$attachment_id = wp_insert_attachment(
		array(
			'post_mime_type' => 'image/jpeg',
			'post_title'     => $title,
			'post_content'   => '',
			'post_status'    => 'inherit',
			'post_date'      => $post_date,
		),
		$path,
		$post_id,
		true
	);

	if ( is_wp_error( $attachment_id ) ) {
		return 0;
	}

	wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $path ) );
	update_post_meta( $attachment_id, '_wp_attachment_image_alt', $title );
	set_post_thumbnail( $post_id, $attachment_id );

	return (int) $attachment_id;
}

$existing = $wpdb->get_col(
	$wpdb->prepare(
		"SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s",
		AGRONEWS_SEED_UID_META
	)
);

$already = array_flip( array_map( 'intval', $existing ) );
$pending = $target - count( $already );

if ( $pending <= 0 ) {
	WP_CLI::success( sprintf( 'Stories already seeded: %d of %d.', count( $already ), $target ) );

	return;
}

WP_CLI::log( sprintf( 'Creating %d stories (%d already there).', $pending, count( $already ) ) );

$now      = time();
$oldest   = $now - ( 18 * 30 * DAY_IN_SECONDS );
$progress = WP_CLI\Utils\make_progress_bar( 'Stories', $pending );
$created  = 0;

wp_suspend_cache_invalidation( true );

for ( $index = 1; $index <= $target; $index++ ) {
	if ( isset( $already[ $index ] ) ) {
		continue;
	}

	$category_slug = $category_slugs[ mt_rand( 0, count( $category_slugs ) - 1 ) ];
	$category_id   = $categories[ $category_slug ];
	$category_name = $category_names[ $category_slug ];

	$headline = sprintf(
		$headlines[ mt_rand( 0, count( $headlines ) - 1 ) ],
		$category_name,
		mt_rand( 2, 98 )
	);

	$content       = agronews_seed_content( $vocabulary );
	$timestamp     = mt_rand( $oldest, $now );
	$post_date_gmt = gmdate( 'Y-m-d H:i:s', $timestamp );
	$post_date     = get_date_from_gmt( $post_date_gmt );

	// Two to four topics per story.
	shuffle( $tags );
	$post_tags = array_slice( $tags, 0, mt_rand( 2, 4 ) );

	$post_id = wp_insert_post(
		array(
			'post_title'     => $headline,
			'post_content'   => $content,
			'post_excerpt'   => agronews_seed_sentence( $vocabulary, 24 ),
			'post_status'    => 'publish',
			'post_type'      => 'post',
			'post_author'    => $authors[ mt_rand( 0, count( $authors ) - 1 ) ],
			'post_date'      => $post_date,
			'post_date_gmt'  => $post_date_gmt,
			'post_category'  => array( $category_id ),
			'tags_input'     => $post_tags,
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
			'meta_input'     => array(
				AGRONEWS_SEED_UID_META => $index,
				'an_views'             => mt_rand( 0, 50000 ),
			),
		),
		true
	);

	if ( is_wp_error( $post_id ) ) {
		WP_CLI::warning( sprintf( 'Story %d: %s', $index, $post_id->get_error_message() ) );
		$progress->tick();

		continue;
	}

	agronews_seed_attach_image( $post_id, $index, $headline, $post_date );

	++$created;
	$progress->tick();

	// Keep the in-process object cache from growing for the whole run.
	if ( 0 === $created % 100 ) {
		wp_cache_flush();
	}
}

$progress->finish();

wp_suspend_cache_invalidation( false );
wp_defer_term_counting( false );
wp_cache_flush();

WP_CLI::success( sprintf( 'Stories ready: %d created, %d total.', $created, (int) wp_count_posts( 'post' )->publish ) );
