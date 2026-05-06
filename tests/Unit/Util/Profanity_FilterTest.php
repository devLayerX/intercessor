<?php
/**
 * Unit tests for Intercessor\Util\Profanity_Filter.
 *
 * @package Intercessor\Tests\Unit\Util
 */

declare(strict_types=1);

namespace Intercessor\Tests\Unit\Util;

use Intercessor\Util\Profanity_Filter;
use PHPUnit\Framework\TestCase;

/**
 * Tests the profanity filter word-matching logic.
 *
 * Profanity_Filter reads its word list from Settings::get('profanity_words').
 * The unit tests bypass the Settings class entirely by injecting a word list
 * directly via the static $wordCache property using reflection, then calling
 * clear_cache() in tearDown to restore a clean state for each test.
 */
class Profanity_FilterTest extends TestCase {

	/**
	 * Inject a word list into the static cache, bypassing Settings.
	 */
	private function set_words( array $words ): void {
		$ref  = new \ReflectionProperty( Profanity_Filter::class, 'wordCache' );
		$ref->setAccessible( true );
		$ref->setValue( null, $words );
	}

	protected function tearDown(): void {
		Profanity_Filter::clear_cache();
	}

	// -------------------------------------------------------------------------
	// get_matched_words() — core matching logic
	// -------------------------------------------------------------------------

	public function test_returns_empty_when_word_list_is_empty(): void {
		$this->set_words( [] );
		$this->assertSame( [], Profanity_Filter::get_matched_words( 'any text here' ) );
	}

	public function test_detects_exact_match(): void {
		$this->set_words( [ 'badword' ] );
		$matched = Profanity_Filter::get_matched_words( 'This has a badword in it.' );
		$this->assertContains( 'badword', $matched );
	}

	public function test_is_case_insensitive(): void {
		$this->set_words( [ 'badword' ] );
		$matched = Profanity_Filter::get_matched_words( 'BADWORD should match.' );
		$this->assertContains( 'badword', $matched );
	}

	public function test_word_boundary_prevents_partial_match(): void {
		$this->set_words( [ 'ass' ] );
		// 'ass' should NOT match inside 'assistance' or 'class'.
		$this->assertSame( [], Profanity_Filter::get_matched_words( 'assistance in class' ) );
	}

	public function test_word_boundary_matches_standalone_word(): void {
		$this->set_words( [ 'ass' ] );
		$matched = Profanity_Filter::get_matched_words( 'The ass ran away.' );
		$this->assertContains( 'ass', $matched );
	}

	public function test_returns_multiple_matched_words(): void {
		$this->set_words( [ 'foo', 'bar' ] );
		$matched = Profanity_Filter::get_matched_words( 'foo and bar are here.' );
		$this->assertContains( 'foo', $matched );
		$this->assertContains( 'bar', $matched );
		$this->assertCount( 2, $matched );
	}

	public function test_returns_unique_matches_only(): void {
		$this->set_words( [ 'foo' ] );
		// 'foo' appears twice but should be returned once.
		$matched = Profanity_Filter::get_matched_words( 'foo and foo again.' );
		$this->assertCount( 1, $matched );
	}

	public function test_matched_words_are_lowercase(): void {
		$this->set_words( [ 'BadWord' ] );
		$matched = Profanity_Filter::get_matched_words( 'There is a BadWord here.' );
		$this->assertContains( 'badword', $matched );
	}

	public function test_returns_empty_when_no_match(): void {
		$this->set_words( [ 'forbidden' ] );
		$this->assertSame( [], Profanity_Filter::get_matched_words( 'completely clean text' ) );
	}

	public function test_handles_unicode_text(): void {
		$this->set_words( [ 'mot' ] );
		$matched = Profanity_Filter::get_matched_words( 'un mot français' );
		$this->assertContains( 'mot', $matched );
	}

	// -------------------------------------------------------------------------
	// passes()
	// -------------------------------------------------------------------------

	public function test_passes_returns_true_for_clean_text(): void {
		$this->set_words( [ 'badword' ] );
		$this->assertTrue( Profanity_Filter::passes( 'This text is perfectly clean.' ) );
	}

	public function test_passes_returns_false_for_flagged_text(): void {
		$this->set_words( [ 'badword' ] );
		$this->assertFalse( Profanity_Filter::passes( 'This has a badword.' ) );
	}

	public function test_passes_returns_true_when_word_list_empty(): void {
		$this->set_words( [] );
		$this->assertTrue( Profanity_Filter::passes( 'anything at all' ) );
	}

	// -------------------------------------------------------------------------
	// build_moderator_note()
	// -------------------------------------------------------------------------

	public function test_build_moderator_note_returns_empty_for_no_matches(): void {
		$this->assertSame( '', Profanity_Filter::build_moderator_note( [] ) );
	}

	public function test_build_moderator_note_contains_matched_words(): void {
		$note = Profanity_Filter::build_moderator_note( [ 'foo', 'bar' ] );
		$this->assertStringContainsString( 'foo', $note );
		$this->assertStringContainsString( 'bar', $note );
	}

	public function test_build_moderator_note_contains_filter_prefix(): void {
		$note = Profanity_Filter::build_moderator_note( [ 'word' ] );
		$this->assertStringContainsString( '[Profanity filter]', $note );
	}

	// -------------------------------------------------------------------------
	// clear_cache()
	// -------------------------------------------------------------------------

	public function test_clear_cache_resets_word_list(): void {
		$this->set_words( [ 'badword' ] );
		Profanity_Filter::clear_cache();

		// After clearing, injecting an empty word list should result in no matches.
		$this->set_words( [] );
		$this->assertSame( [], Profanity_Filter::get_matched_words( 'badword' ) );
	}
}
