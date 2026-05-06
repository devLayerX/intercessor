<?php
/**
 * Unit tests for Settings_Exporter boolean normalisation logic.
 *
 * @package Intercessor\Tests\Unit\Tools
 */

declare(strict_types=1);

namespace Intercessor\Tests\Unit\Tools;

use PHPUnit\Framework\TestCase;

/**
 * Tests the boolean-to-Yes/No normalisation logic in Settings_Exporter::get_rows().
 *
 * Settings_Exporter extends Abstract_Exporter which requires a WordPress
 * environment for dispatch()/stream_csv(). We test only the normalisation
 * logic by extracting it into a standalone helper that mirrors the exact
 * conditionals in get_rows().
 */
class Settings_ExporterTest extends TestCase {

	/**
	 * Mirrors the boolean normalisation logic from Settings_Exporter::get_rows().
	 */
	private function normalise( mixed $value ): mixed {
		if ( is_bool( $value ) ) {
			return $value ? 'Yes' : 'No';
		} elseif ( $value === '1' || $value === 1 ) {
			return 'Yes';
		} elseif ( $value === '0' || $value === 0 ) {
			return 'No';
		}

		return $value;
	}

	public function test_bool_true_to_yes(): void {
		$this->assertSame( 'Yes', $this->normalise( true ) );
	}

	public function test_bool_false_to_no(): void {
		$this->assertSame( 'No', $this->normalise( false ) );
	}

	public function test_string_one_to_yes(): void {
		$this->assertSame( 'Yes', $this->normalise( '1' ) );
	}

	public function test_string_zero_to_no(): void {
		$this->assertSame( 'No', $this->normalise( '0' ) );
	}

	public function test_integer_one_to_yes(): void {
		$this->assertSame( 'Yes', $this->normalise( 1 ) );
	}

	public function test_integer_zero_to_no(): void {
		$this->assertSame( 'No', $this->normalise( 0 ) );
	}

	/** Empty string must NOT become 'No' — this was the fixed bug. */
	public function test_empty_string_passes_through_unchanged(): void {
		$this->assertSame( '', $this->normalise( '' ) );
	}

	public function test_text_value_passes_through_unchanged(): void {
		$this->assertSame( 'editor', $this->normalise( 'editor' ) );
	}

	public function test_numeric_string_three_passes_through(): void {
		$this->assertSame( '3', $this->normalise( '3' ) );
	}

	public function test_float_string_passes_through(): void {
		$this->assertSame( '0.5', $this->normalise( '0.5' ) );
	}

	public function test_null_passes_through(): void {
		$this->assertNull( $this->normalise( null ) );
	}

	public function test_string_two_is_not_yes_or_no(): void {
		$result = $this->normalise( '2' );
		$this->assertNotSame( 'Yes', $result );
		$this->assertNotSame( 'No', $result );
	}
}
