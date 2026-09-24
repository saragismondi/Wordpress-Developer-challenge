<?php
/**
 * Data providers for the home page blocks.
 *
 * The templates under template-parts/ stay presentational: every query lives
 * here, which is also what makes the blocks testable from PHPUnit.
 *
 * @package AgroNews
 */

if ( ! defined( 'AGRONEWS_VIEWS_META' ) ) {
	// Per post view counter written by the seeder and by the analytics job.
	define( 'AGRONEWS_VIEWS_META', 'an_views' );
}

/**
 * Returns how many posts each home block shows.
 *
 * @return array<string,int> Block slug => number of posts.
 */
function agronews_get_block_sizes() {
	$sizes = array(
		'featured'  => 5,
		'latest'    => 10,
		'section'   => 4,
		'most-read' => 6,
	);

	/**
	 * Filters the number of posts rendered by each home block.
	 *
	 * @param array<string,int> $sizes Block slug => number of posts.
	 */
	return (array) apply_filters( 'agronews_block_sizes', $sizes );
}

/**
 * Returns the number of posts a block renders.
 *
 * @param string $block Block slug.
 * @return int Number of posts, 0 when the block is unknown.
 */
function agronews_get_block_size( $block ) {
	$sizes = agronews_get_block_sizes();

	return isset( $sizes[ $block ] ) ? (int) $sizes[ $block ] : 0;
}

/**
 * Reads a block setting from the AgroNews Home plugin.
 *
 * The theme renders with or without the plugin: when it is not active every
 * block falls back to "no category filter".
 *
 * @param string $key           Setting key.
 * @param mixed  $default_value Value returned when the plugin is missing.
 * @return mixed Setting value.
 */
function agronews_get_setting( $key, $default_value = '' ) {
	if ( function_exists( 'agronews_home_get_setting' ) ) {
		return agronews_home_get_setting( $key, $default_value );
	}

	return $default_value;
}

/**
 * Returns the category assigned to the "Featured" block.
 *
 * @return int Term ID, 0 when unset.
 */
function agronews_get_featured_category_id() {
	return (int) agronews_get_setting( 'featured_category', 0 );
}

/**
 * Returns the three categories assigned to the "By section" block.
 *
 * Missing or deleted terms are dropped, so the block never queries a term
 * that no longer exists.
 *
 * @return int[] List of term IDs, at most three.
 */
function agronews_get_section_category_ids() {
	$configured = (array) agronews_get_setting( 'section_categories', array() );
	$ids        = array();

	foreach ( $configured as $term_id ) {
		$term_id = (int) $term_id;

		if ( $term_id > 0 && get_term( $term_id, 'category' ) instanceof WP_Term ) {
			$ids[] = $term_id;
		}
	}

	return array_slice( array_values( array_unique( $ids ) ), 0, 3 );
}

/**
 * Builds the WP_Query arguments for a home block.
 *
 * @param string $block       Block slug: featured, latest, section or most-read.
 * @param int    $category_id Category to restrict the block to. Only used by
 *                            the "section" block, which is rendered once per
 *                            configured category.
 * @return array<string,mixed> Query arguments.
 */
function agronews_get_block_query_args( $block, $category_id = 0 ) {
	$args = array(
		'post_type'              => 'post',
		'post_status'            => 'publish',
		'posts_per_page'         => agronews_get_block_size( $block ),
		'ignore_sticky_posts'    => true,
		'update_post_term_cache' => false,
		'update_post_meta_cache' => false,
	);

	switch ( $block ) {
		case 'featured':
			$featured = agronews_get_featured_category_id();

			if ( $featured > 0 ) {
				$args['cat'] = $featured;
			}
			break;

		case 'section':
			$args['cat'] = (int) $category_id;
			break;

		case 'most-read':
			/*
			 * Ordering by a meta value is the price of the "most read" ranking.
			 * The counter is written on every view, so a join is unavoidable
			 * without a dedicated table.
			 */
			$args['meta_key'] = AGRONEWS_VIEWS_META; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Ranking by view count is the point of this block.
			$args['orderby']  = 'meta_value_num';
			$args['order']    = 'DESC';
			break;
	}

	/**
	 * Filters the query arguments of a home block.
	 *
	 * @param array<string,mixed> $args        Query arguments.
	 * @param string              $block       Block slug.
	 * @param int                 $category_id Category the block is restricted to.
	 */
	return (array) apply_filters( 'agronews_block_query_args', $args, $block, $category_id );
}

/**
 * Runs the query for a home block.
 *
 * @param string              $block       Block slug.
 * @param int                 $category_id Category the block is restricted to.
 * @param array<string,mixed> $overrides   Query arguments to override, so a block
 *                                         can split its list across several calls.
 * @return WP_Query Query object, already executed.
 */
function agronews_get_block_query( $block, $category_id = 0, $overrides = array() ) {
	$args = array_merge( agronews_get_block_query_args( $block, $category_id ), (array) $overrides );

	return new WP_Query( $args );
}

/**
 * Runs the "related stories" query of a single post.
 *
 * Related means "same primary category", resolved with a single query that
 * excludes the post being read.
 *
 * @param int $post_id Post to find related stories for.
 * @param int $number  How many stories to return. Default 4.
 * @return WP_Query Query object, already executed.
 */
function agronews_get_related_query( $post_id, $number = 4 ) {
	$post_id    = (int) $post_id;
	$categories = wp_get_post_categories( $post_id );

	$args = array(
		'post_type'              => 'post',
		'post_status'            => 'publish',
		'posts_per_page'         => absint( $number ),
		'post__not_in'           => array( $post_id ),
		'ignore_sticky_posts'    => true,
		'orderby'                => 'rand',
		'update_post_term_cache' => false,
		'update_post_meta_cache' => false,
	);

	if ( ! empty( $categories ) ) {
		$args['cat'] = (int) $categories[0];
	}

	return new WP_Query( $args );
}

/**
 * Renders the "Topics of the day" block: the most used tags of the site.
 *
 * @param array<string,mixed> $args {
 *     Optional. Rendering arguments.
 *
 *     @type int    $number How many tags to show. Default 12.
 *     @type string $title  Heading text. Default "Topics of the day".
 * }
 * @return void
 */
function agronews_render_topics( $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'number' => 12,
			'title'  => __( 'Topics of the day', 'agronews' ),
		)
	);

	$tags = get_terms(
		array(
			'taxonomy'   => 'post_tag',
			'orderby'    => 'count',
			'order'      => 'DESC',
			'number'     => absint( $args['number'] ),
			'hide_empty' => true,
		)
	);

	if ( is_wp_error( $tags ) || empty( $tags ) ) {
		return;
	}
	?>
	<nav class="an-topics" aria-label="<?php echo esc_attr( $args['title'] ); ?>">
		<h2 class="an-topics__title"><?php echo esc_html( $args['title'] ); ?></h2>
		<ul class="an-topics__list">
			<?php foreach ( $tags as $tag ) : ?>
				<li class="an-topics__item">
					<a class="an-topics__link" href="<?php echo esc_url( get_term_link( $tag ) ); ?>">
						<?php echo esc_html( $tag->name ); ?>
						<span class="an-topics__count"><?php echo esc_html( number_format_i18n( $tag->count ) ); ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</nav>
	<?php
}
