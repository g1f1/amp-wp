<?php

namespace AmpProject\AmpWP\Tests\Transformer;

use AmpProject\AmpWP\Dom\Options;
use AmpProject\AmpWP\Tests\Helpers\ErrorComparison;
use AmpProject\AmpWP\Tests\Helpers\MarkupComparison;
use AmpProject\AmpWP\Transformer\DetermineHeroImages;
use AmpProject\Dom\Document;
use AmpProject\Optimizer\Error;
use AmpProject\Optimizer\ErrorCollection;
use WP_UnitTestCase;

/** @coversDefaultClass \AmpProject\AmpWP\Transformer\DetermineHeroImages */
final class DetermineHeroImagesTest extends WP_UnitTestCase {

	use ErrorComparison;
	use MarkupComparison;

	/**
	 * Provide the data to test the transform() method.
	 *
	 * @return array[] Associative array of data arrays.
	 */
	public function data_transform() {
		$input = static function ( $body ) {
			return '<!DOCTYPE html><html ⚡><head>'
				. '<meta charset="utf-8">'
				. '</head><body>'
				. $body
				. '</body></html>';
		};

		$output = static function ( $body ) {
			return '<!DOCTYPE html><html ⚡><head>'
				. '<meta charset="utf-8">'
				. '</head><body>'
				. $body
				. '</body></html>';
		};

		return [
			'detects custom header'                      => [
				$input(
					'<div class="wp-custom-header">'
					. '<img width="789" height="539" src="https://example.com/custom-header.jpg">'
					. '</div>'
				),
				$output(
					'<div class="wp-custom-header">'
					. '<img width="789" height="539" src="https://example.com/custom-header.jpg" data-hero-candidate>'
					. '</div>'
				),
			],

			'detects custom header as amp-img'           => [
				$input(
					'<div class="wp-custom-header">'
					. '<amp-img width="789" height="539" src="https://example.com/custom-header.jpg"></amp-img>'
					. '</div>'
				),
				$output(
					'<div class="wp-custom-header">'
					. '<amp-img width="789" height="539" src="https://example.com/custom-header.jpg" data-hero-candidate></amp-img>'
					. '</div>'
				),
			],

			'detects site icon'                          => [
				$input(
					'<div class="site-logo faux-heading">'
					. '<a href="https://amp.lndo.site/" class="custom-logo-link" rel="home">'
					. '<img width="789" height="539" src="https://example.com/site-icon.jpg" class="custom-logo" alt="Theme Unit Test" loading="lazy" srcset="https://example.com/site-icon_789.jpg 789w, https://example.com/site-icon_300.jpg 300w, https://example.com/site-icon_768.jpg 768w" sizes="(max-width: 789px) 100vw, 789px">'
					. '</a>'
					. '</div>'
				),
				$output(
					'<div class="site-logo faux-heading">'
					. '<a href="https://amp.lndo.site/" class="custom-logo-link" rel="home">'
					. '<img width="789" height="539" src="https://example.com/site-icon.jpg" class="custom-logo" alt="Theme Unit Test" loading="lazy" srcset="https://example.com/site-icon_789.jpg 789w, https://example.com/site-icon_300.jpg 300w, https://example.com/site-icon_768.jpg 768w" sizes="(max-width: 789px) 100vw, 789px" data-hero-candidate>'
					. '</a>'
					. '</div>'
				),
			],

			'detects site icon as amp-img'               => [
				$input(
					'<div class="site-logo faux-heading">'
					. '<a href="https://amp.lndo.site/" class="custom-logo-link" rel="home">'
					. '<amp-img width="789" height="539" src="https://example.com/site-icon.jpg" class="custom-logo" alt="Theme Unit Test" loading="lazy" srcset="https://example.com/site-icon_789.jpg 789w, https://example.com/site-icon_300.jpg 300w, https://example.com/site-icon_768.jpg 768w" sizes="(max-width: 789px) 100vw, 789px"></amp-img>'
					. '</a>'
					. '</div>'
				),
				$output(
					'<div class="site-logo faux-heading">'
					. '<a href="https://amp.lndo.site/" class="custom-logo-link" rel="home">'
					. '<amp-img width="789" height="539" src="https://example.com/site-icon.jpg" class="custom-logo" alt="Theme Unit Test" loading="lazy" srcset="https://example.com/site-icon_789.jpg 789w, https://example.com/site-icon_300.jpg 300w, https://example.com/site-icon_768.jpg 768w" sizes="(max-width: 789px) 100vw, 789px" data-hero-candidate></amp-img>'
					. '</a>'
					. '</div>'
				),
			],

			'detects featured image'                     => [
				$input(
					'<figure class="featured-media">'
					. '<div class="featured-media-inner section-inner">'
					. '<img width="640" height="480" src="https://example.com/featured-image.jpg" class="attachment-post-thumbnail size-post-thumbnail wp-post-image" alt="" loading="lazy" srcset="https://example.com/featured-image_640.jpg 640w, https://example.com/featured-image_300.jpg 300w" sizes="(max-width: 640px) 100vw, 640px">'
					. '</div>'
					. '</figure>'
				),
				$output(
					'<figure class="featured-media">'
					. '<div class="featured-media-inner section-inner">'
					. '<img width="640" height="480" src="https://example.com/featured-image.jpg" class="attachment-post-thumbnail size-post-thumbnail wp-post-image" alt="" loading="lazy" srcset="https://example.com/featured-image_640.jpg 640w, https://example.com/featured-image_300.jpg 300w" sizes="(max-width: 640px) 100vw, 640px" data-hero-candidate>'
					. '</div>'
					. '</figure>'
				),
			],

			'detects featured image as amp-img'          => [
				$input(
					'<figure class="featured-media">'
					. '<div class="featured-media-inner section-inner">'
					. '<amp-img width="640" height="480" src="https://example.com/featured-image.jpg" class="attachment-post-thumbnail size-post-thumbnail wp-post-image" alt="" loading="lazy" srcset="https://example.com/featured-image_640.jpg 640w, https://example.com/featured-image_300.jpg 300w" sizes="(max-width: 640px) 100vw, 640px"></amp-img>'
					. '</div>'
					. '</figure>'
				),
				$output(
					'<figure class="featured-media">'
					. '<div class="featured-media-inner section-inner">'
					. '<amp-img width="640" height="480" src="https://example.com/featured-image.jpg" class="attachment-post-thumbnail size-post-thumbnail wp-post-image" alt="" loading="lazy" srcset="https://example.com/featured-image_640.jpg 640w, https://example.com/featured-image_300.jpg 300w" sizes="(max-width: 640px) 100vw, 640px" data-hero-candidate></amp-img>'
					. '</div>'
					. '</figure>'
				),
			],

			'detects first content cover block'          => [
				$input(
					'<div class="entry-content">'
					. '<div class="wp-block-cover has-dark-gray-background-color has-background-dim has-custom-content-position is-position-center-left" style="min-height:100vh"><img loading="lazy" width="2000" height="1199" class="wp-block-cover__image-background wp-image-2266" alt="" src="https://example.com/cover-block-1.jpg" style="object-position:100% 98%" data-object-fit="cover" data-object-position="100% 98%"><div class="wp-block-cover__inner-container"><p class="has-text-align-left has-large-font-size">Cover Image with bottom-right positioning, full height, end left-aligned text.</p></div></div>'
					. '<div class="wp-block-cover has-dark-gray-background-color has-background-dim has-custom-content-position is-position-center-left" style="min-height:100vh"><img loading="lazy" width="2000" height="1199" class="wp-block-cover__image-background wp-image-2266" alt="" src="https://example.com/cover-block-1.jpg" style="object-position:100% 98%" data-object-fit="cover" data-object-position="100% 98%"><div class="wp-block-cover__inner-container"><p class="has-text-align-left has-large-font-size">Cover Image with bottom-right positioning, full height, end left-aligned text.</p></div></div>'
					. '</div>'
					. '<div class="entry-content">'
					. '<div class="wp-block-cover has-dark-gray-background-color has-background-dim has-custom-content-position is-position-center-left" style="min-height:100vh"><img loading="lazy" width="2000" height="1199" class="wp-block-cover__image-background wp-image-2266" alt="" src="https://example.com/cover-block-1.jpg" style="object-position:100% 98%" data-object-fit="cover" data-object-position="100% 98%"><div class="wp-block-cover__inner-container"><p class="has-text-align-left has-large-font-size">Cover Image with bottom-right positioning, full height, end left-aligned text.</p></div></div>'
					. '</div>'
				),
				$output(
					'<div class="entry-content">'
					. '<div class="wp-block-cover has-dark-gray-background-color has-background-dim has-custom-content-position is-position-center-left" style="min-height:100vh"><img loading="lazy" width="2000" height="1199" class="wp-block-cover__image-background wp-image-2266" data-hero-candidate alt="" src="https://example.com/cover-block-1.jpg" style="object-position:100% 98%" data-object-fit="cover" data-object-position="100% 98%"><div class="wp-block-cover__inner-container"><p class="has-text-align-left has-large-font-size">Cover Image with bottom-right positioning, full height, end left-aligned text.</p></div></div>'
					. '<div class="wp-block-cover has-dark-gray-background-color has-background-dim has-custom-content-position is-position-center-left" style="min-height:100vh"><img loading="lazy" width="2000" height="1199" class="wp-block-cover__image-background wp-image-2266" alt="" src="https://example.com/cover-block-1.jpg" style="object-position:100% 98%" data-object-fit="cover" data-object-position="100% 98%"><div class="wp-block-cover__inner-container"><p class="has-text-align-left has-large-font-size">Cover Image with bottom-right positioning, full height, end left-aligned text.</p></div></div>'
					. '</div>'
					. '<div class="entry-content">'
					. '<div class="wp-block-cover has-dark-gray-background-color has-background-dim has-custom-content-position is-position-center-left" style="min-height:100vh"><img loading="lazy" width="2000" height="1199" class="wp-block-cover__image-background wp-image-2266" alt="" src="https://example.com/cover-block-1.jpg" style="object-position:100% 98%" data-object-fit="cover" data-object-position="100% 98%"><div class="wp-block-cover__inner-container"><p class="has-text-align-left has-large-font-size">Cover Image with bottom-right positioning, full height, end left-aligned text.</p></div></div>'
					. '</div>'
				),
			],

			'detects first content image block'          => [
				$input(
					'<div class="entry-content">'
					. '<figure class="wp-block-image size-large"><img loading="lazy" width="1024" height="768" src="https://example.com/image-block-1.jpg" alt="" class="wp-image-2135"></figure>'
					. '</div>'
					. '<div class="entry-content">'
					. '<figure class="wp-block-image size-large"><img loading="lazy" width="1024" height="768" src="https://example.com/image-block-2.jpg" alt="" class="wp-image-2135"></figure>'
					. '</div>'
				),
				$output(
					'<div class="entry-content">'
					. '<figure class="wp-block-image size-large"><img loading="lazy" width="1024" height="768" src="https://example.com/image-block-1.jpg" alt="" class="wp-image-2135" data-hero-candidate></figure>'
					. '</div>'
					. '<div class="entry-content">'
					. '<figure class="wp-block-image size-large"><img loading="lazy" width="1024" height="768" src="https://example.com/image-block-2.jpg" alt="" class="wp-image-2135"></figure>'
					. '</div>'
				),
			],

			'detects first cover block in initial group' => [
				$input(
					'<div class="entry-content">'
					. '<div class="wp-block-group"><div class="wp-block-group__inner-container">'
					. '<div class="wp-block-cover has-dark-gray-background-color has-background-dim has-custom-content-position is-position-center-left" style="min-height:100vh"><img loading="lazy" width="2000" height="1199" class="wp-block-cover__image-background wp-image-2266" alt="" src="https://example.com/cover-block-1.jpg" style="object-position:100% 98%" data-object-fit="cover" data-object-position="100% 98%"><div class="wp-block-cover__inner-container"><p class="has-text-align-left has-large-font-size">Cover Image with bottom-right positioning, full height, end left-aligned text.</p></div></div>'
					. '</div></div>'
					. '</div>'
				),
				$output(
					'<div class="entry-content">'
					. '<div class="wp-block-group"><div class="wp-block-group__inner-container">'
					. '<div class="wp-block-cover has-dark-gray-background-color has-background-dim has-custom-content-position is-position-center-left" style="min-height:100vh"><img loading="lazy" width="2000" height="1199" class="wp-block-cover__image-background wp-image-2266" data-hero-candidate alt="" src="https://example.com/cover-block-1.jpg" style="object-position:100% 98%" data-object-fit="cover" data-object-position="100% 98%"><div class="wp-block-cover__inner-container"><p class="has-text-align-left has-large-font-size">Cover Image with bottom-right positioning, full height, end left-aligned text.</p></div></div>'
					. '</div></div>'
					. '</div>'
				),
			],

			'ignores non-initial cover blocks'           => [
				$input(
					'<div class="entry-content">'
					. '<p>Another block at beginning!</p>'
					. '<div class="wp-block-cover has-dark-gray-background-color has-background-dim has-custom-content-position is-position-center-left" style="min-height:100vh"><img loading="lazy" width="2000" height="1199" class="wp-block-cover__image-background wp-image-2266" alt="" src="https://example.com/cover-block-1.jpg" style="object-position:100% 98%" data-object-fit="cover" data-object-position="100% 98%"><div class="wp-block-cover__inner-container"><p class="has-text-align-left has-large-font-size">Cover Image with bottom-right positioning, full height, end left-aligned text.</p></div></div>'
					. '</div>'
					. '<div class="entry-content">'
					. '<div class="wp-block-cover has-dark-gray-background-color has-background-dim has-custom-content-position is-position-center-left" style="min-height:100vh"><img loading="lazy" width="2000" height="1199" class="wp-block-cover__image-background wp-image-2266" alt="" src="https://example.com/cover-block-1.jpg" style="object-position:100% 98%" data-object-fit="cover" data-object-position="100% 98%"><div class="wp-block-cover__inner-container"><p class="has-text-align-left has-large-font-size">Cover Image with bottom-right positioning, full height, end left-aligned text.</p></div></div>'
					. '</div>'
				),
				$output(
					'<div class="entry-content">'
					. '<p>Another block at beginning!</p>'
					. '<div class="wp-block-cover has-dark-gray-background-color has-background-dim has-custom-content-position is-position-center-left" style="min-height:100vh"><img loading="lazy" width="2000" height="1199" class="wp-block-cover__image-background wp-image-2266" alt="" src="https://example.com/cover-block-1.jpg" style="object-position:100% 98%" data-object-fit="cover" data-object-position="100% 98%"><div class="wp-block-cover__inner-container"><p class="has-text-align-left has-large-font-size">Cover Image with bottom-right positioning, full height, end left-aligned text.</p></div></div>'
					. '</div>'
					. '<div class="entry-content">'
					. '<div class="wp-block-cover has-dark-gray-background-color has-background-dim has-custom-content-position is-position-center-left" style="min-height:100vh"><img loading="lazy" width="2000" height="1199" class="wp-block-cover__image-background wp-image-2266" alt="" src="https://example.com/cover-block-1.jpg" style="object-position:100% 98%" data-object-fit="cover" data-object-position="100% 98%"><div class="wp-block-cover__inner-container"><p class="has-text-align-left has-large-font-size">Cover Image with bottom-right positioning, full height, end left-aligned text.</p></div></div>'
					. '</div>'
				),
			],

			'site icon and custom header are prioritized over cover blocks' => [
				$input(
					'<div class="entry-content">'
					. '<div class="wp-block-cover has-dark-gray-background-color has-background-dim has-custom-content-position is-position-center-left" style="min-height:100vh"><img loading="lazy" width="2000" height="1199" class="wp-block-cover__image-background wp-image-2266" alt="" src="https://example.com/cover-block-1.jpg" style="object-position:100% 98%" data-object-fit="cover" data-object-position="100% 98%"><div class="wp-block-cover__inner-container"><p class="has-text-align-left has-large-font-size">Cover Image with bottom-right positioning, full height, end left-aligned text.</p></div></div>'
					. '</div>'
					. '<a href="https://amp.lndo.site/" class="custom-logo-link" rel="home">'
					. '<amp-img width="789" height="539" src="https://example.com/site-icon.jpg" class="custom-logo" alt="Theme Unit Test" loading="lazy" srcset="https://example.com/site-icon_789.jpg 789w, https://example.com/site-icon_300.jpg 300w, https://example.com/site-icon_768.jpg 768w" sizes="(max-width: 789px) 100vw, 789px"></amp-img>'
					. '</a>'
					. '<div class="wp-custom-header"><amp-img width="640" height="480" src="https://example.com/custom-header.jpg"></amp-img></div>'
				),
				$output(
					'<div class="entry-content">'
					. '<div class="wp-block-cover has-dark-gray-background-color has-background-dim has-custom-content-position is-position-center-left" style="min-height:100vh"><img loading="lazy" width="2000" height="1199" class="wp-block-cover__image-background wp-image-2266" alt="" src="https://example.com/cover-block-1.jpg" style="object-position:100% 98%" data-object-fit="cover" data-object-position="100% 98%"><div class="wp-block-cover__inner-container"><p class="has-text-align-left has-large-font-size">Cover Image with bottom-right positioning, full height, end left-aligned text.</p></div></div>'
					. '</div>'
					. '<a href="https://amp.lndo.site/" class="custom-logo-link" rel="home">'
					. '<amp-img width="789" height="539" src="https://example.com/site-icon.jpg" class="custom-logo" alt="Theme Unit Test" loading="lazy" srcset="https://example.com/site-icon_789.jpg 789w, https://example.com/site-icon_300.jpg 300w, https://example.com/site-icon_768.jpg 768w" sizes="(max-width: 789px) 100vw, 789px" data-hero-candidate></amp-img>'
					. '</a>'
					. '<div class="wp-custom-header"><amp-img width="640" height="480" src="https://example.com/custom-header.jpg" data-hero-candidate></amp-img></div>'
				),
			],

			'featured image and custom header prioritized over cover blocks' => [
				$input(
					'<div class="entry-content">'
					. '<div class="wp-block-cover has-dark-gray-background-color has-background-dim has-custom-content-position is-position-center-left" style="min-height:100vh"><img loading="lazy" width="2000" height="1199" class="wp-block-cover__image-background wp-image-2266" alt="" src="https://example.com/cover-block-1.jpg" style="object-position:100% 98%" data-object-fit="cover" data-object-position="100% 98%"><div class="wp-block-cover__inner-container"><p class="has-text-align-left has-large-font-size">Cover Image with bottom-right positioning, full height, end left-aligned text.</p></div></div>'
					. '</div>'
					. '<amp-img width="640" height="480" class="attachment-post-thumbnail size-post-thumbnail wp-post-image" src="https://example.com/featured-image.jpg"></amp-img>'
					. '<div class="wp-custom-header"><amp-img width="640" height="480" src="https://example.com/custom-header.jpg"></amp-img></div>'
				),
				$output(
					'<div class="entry-content">'
					. '<div class="wp-block-cover has-dark-gray-background-color has-background-dim has-custom-content-position is-position-center-left" style="min-height:100vh"><img loading="lazy" width="2000" height="1199" class="wp-block-cover__image-background wp-image-2266" alt="" src="https://example.com/cover-block-1.jpg" style="object-position:100% 98%" data-object-fit="cover" data-object-position="100% 98%"><div class="wp-block-cover__inner-container"><p class="has-text-align-left has-large-font-size">Cover Image with bottom-right positioning, full height, end left-aligned text.</p></div></div>'
					. '</div>'
					. '<amp-img width="640" height="480" class="attachment-post-thumbnail size-post-thumbnail wp-post-image" src="https://example.com/featured-image.jpg" data-hero-candidate></amp-img>'
					. '<div class="wp-custom-header"><amp-img width="640" height="480" src="https://example.com/custom-header.jpg" data-hero-candidate></amp-img></div>'
				),
			],
		];
	}

	/**
	 * Test the transform() method.
	 *
	 * @covers       \AmpProject\AmpWP\Transformer\DetermineHeroImages::transform()
	 * @covers       \AmpProject\AmpWP\Transformer\DetermineHeroImages::add_data_hero_candidate_attribute()
	 * @covers       \AmpProject\AmpWP\Transformer\DetermineHeroImages::get_custom_header()
	 * @covers       \AmpProject\AmpWP\Transformer\DetermineHeroImages::get_custom_logo()
	 * @covers       \AmpProject\AmpWP\Transformer\DetermineHeroImages::get_featured_image()
	 * @covers       \AmpProject\AmpWP\Transformer\DetermineHeroImages::get_initial_content_image_block()
	 * @covers       \AmpProject\AmpWP\Transformer\DetermineHeroImages::get_initial_content_cover_block()
	 * @dataProvider data_transform()
	 *
	 * @param string                  $source          String of source HTML.
	 * @param string                  $expected_html   String of expected HTML
	 *                                                 output.
	 * @param ErrorCollection|Error[] $expected_errors Set of expected errors.
	 */
	public function test_transform( $source, $expected_html, $expected_errors = [] ) {
		$document    = Document::fromHtml( $source, Options::DEFAULTS );
		$transformer = new DetermineHeroImages();
		$errors      = new ErrorCollection();

		$transformer->transform( $document, $errors );

		$this->assertSimilarMarkup( $expected_html, $document->saveHTML() );
		$this->assertSameErrors( $expected_errors, $errors );
	}
}
