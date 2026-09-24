<?php
/**
 * Smoke tests for the four home blocks.
 *
 * They assert that each template part renders without a fatal error and with
 * the expected number of story cards.
 *
 * @package AgroNews_Home
 */

/**
 * Covers the theme template parts under template-parts/.
 */
class Test_AgroNews_Home_Blocks extends WP_UnitTestCase {

	/**
	 * Categories created for the fixtures.
	 *
	 * @var int[]
	 */
	private $categories = array();

	/**
	 * Creates the content the blocks query.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();

		delete_option( AGRONEWS_HOME_OPTION );

		$this->categories = array(
			'featured' => self::factory()->category->create( array( 'name' => 'Destacadas' ) ),
			'grains'   => self::factory()->category->create( array( 'name' => 'Granos' ) ),
			'cattle'   => self::factory()->category->create( array( 'name' => 'Ganadería' ) ),
			'weather'  => self::factory()->category->create( array( 'name' => 'Clima' ) ),
		);

		// Six stories in the featured category: the block must show five.
		self::factory()->post->create_many( 6, array( 'post_category' => array( $this->categories['featured'] ) ) );

		// Six in each section category: the block must show four of each.
		foreach ( array( 'grains', 'cattle', 'weather' ) as $section ) {
			self::factory()->post->create_many( 6, array( 'post_category' => array( $this->categories[ $section ] ) ) );
		}

		update_option(
			AGRONEWS_HOME_OPTION,
			agronews_home_sanitize_settings(
				array(
					'featured_category'  => $this->categories['featured'],
					'section_categories' => array(
						$this->categories['grains'],
						$this->categories['cattle'],
						$this->categories['weather'],
					),
					'quotes'             => array( 'soy' => '512.35' ),
				)
			)
		);
	}

	/**
	 * Renders a template part and returns its markup.
	 *
	 * @param string $slug Template part name, e.g. featured.
	 * @return string Rendered markup.
	 */
	private function render_block( $slug ) {
		ob_start();
		get_template_part( 'template-parts/block', $slug );

		return (string) ob_get_clean();
	}

	/**
	 * Counts the story cards in a chunk of markup.
	 *
	 * @param string $html Rendered markup.
	 * @return int Number of cards.
	 */
	private function count_cards( $html ) {
		return substr_count( $html, 'class="an-card__title"' );
	}

	/**
	 * The featured block renders five cards from the configured category.
	 *
	 * @return void
	 */
	public function test_featured_block_renders_five_stories() {
		$html = $this->render_block( 'featured' );

		$this->assertStringContainsString( 'an-block--featured', $html );
		$this->assertSame( 5, $this->count_cards( $html ) );
		$this->assertStringContainsString( 'an-card--lead', $html, 'The first story is rendered as the lead.' );
	}

	/**
	 * The latest block renders the ten most recent stories.
	 *
	 * @return void
	 */
	public function test_latest_block_renders_ten_stories() {
		$html = $this->render_block( 'latest' );

		$this->assertStringContainsString( 'an-block--latest', $html );
		$this->assertSame( 10, $this->count_cards( $html ) );
	}

	/**
	 * The sections block renders three sections with four stories each.
	 *
	 * @return void
	 */
	public function test_sections_block_renders_three_sections_of_four_stories() {
		$html = $this->render_block( 'sections' );

		$this->assertStringContainsString( 'an-block--sections', $html );
		$this->assertSame( 3, substr_count( $html, 'class="an-section__title"' ) );
		$this->assertSame( 12, $this->count_cards( $html ) );
		$this->assertStringContainsString( 'Granos', $html );
		$this->assertStringContainsString( 'Ganadería', $html );
	}

	/**
	 * The most read block renders six stories ordered by the an_views meta.
	 *
	 * @return void
	 */
	public function test_most_read_block_renders_six_stories_ordered_by_views() {
		$expected_top = '';

		foreach ( range( 1, 8 ) as $position ) {
			$views = $position * 100;
			$title = 'Ranked story ' . $position;

			self::factory()->post->create(
				array(
					'post_title'    => $title,
					'post_category' => array( $this->categories['grains'] ),
					'meta_input'    => array( AGRONEWS_VIEWS_META => $views ),
				)
			);

			$expected_top = $title;
		}

		$html = $this->render_block( 'most-read' );

		$this->assertStringContainsString( 'an-block--most-read', $html );
		$this->assertSame( 6, $this->count_cards( $html ) );
		$this->assertStringContainsString( $expected_top, $html, 'The most viewed story is in the block.' );

		$first_title_position = strpos( $html, $expected_top );
		$runner_up_position   = strpos( $html, 'Ranked story 7' );

		$this->assertNotFalse( $first_title_position );
		$this->assertNotFalse( $runner_up_position );
		$this->assertLessThan( $runner_up_position, $first_title_position, 'Stories are ranked by view count.' );
	}
}
