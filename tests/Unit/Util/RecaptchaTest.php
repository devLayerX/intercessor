<?php
/**
 * Unit tests for Intercessor\Util\Recaptcha configuration helpers.
 *
 * @package Intercessor\Tests\Unit\Util
 */

declare(strict_types=1);

namespace Intercessor\Tests\Unit\Util;

use Intercessor\Util\Recaptcha;
use PHPUnit\Framework\TestCase;

/**
 * Tests the pure configuration helper methods on Recaptcha.
 *
 * Methods that call $wpdb->insert or wp_remote_post (verify(), enqueue())
 * are not tested here — those require integration or mock-based tests.
 * The configuration helpers read from Settings::get() which ultimately
 * calls get_option() — stubbed in the unit bootstrap.
 */
class RecaptchaTest extends TestCase {

	protected function setUp(): void {
		global $__test_options;
		$__test_options = [];
	}

	private function set_settings( array $settings ): void {
		global $__test_options;
		$__test_options['intercessor_settings'] = $settings;
	}

	// -------------------------------------------------------------------------
	// is_configured()
	// -------------------------------------------------------------------------

	public function test_is_configured_false_when_both_keys_empty(): void {
		$this->set_settings( [ 'recaptcha_site_key' => '', 'recaptcha_secret_key' => '' ] );
		$this->assertFalse( Recaptcha::is_configured() );
	}

	public function test_is_configured_false_when_site_key_empty(): void {
		$this->set_settings( [ 'recaptcha_site_key' => '', 'recaptcha_secret_key' => 'secret' ] );
		$this->assertFalse( Recaptcha::is_configured() );
	}

	public function test_is_configured_false_when_secret_key_empty(): void {
		$this->set_settings( [ 'recaptcha_site_key' => 'sitekey', 'recaptcha_secret_key' => '' ] );
		$this->assertFalse( Recaptcha::is_configured() );
	}

	public function test_is_configured_true_when_both_keys_present(): void {
		$this->set_settings( [ 'recaptcha_site_key' => 'site', 'recaptcha_secret_key' => 'secret' ] );
		$this->assertTrue( Recaptcha::is_configured() );
	}

	// -------------------------------------------------------------------------
	// is_enabled_for_form()
	// -------------------------------------------------------------------------

	public function test_is_enabled_for_form_false_when_not_configured(): void {
		$this->set_settings( [
			'recaptcha_site_key'    => '',
			'recaptcha_secret_key'  => '',
			'recaptcha_enable_form' => '1',
		] );
		$this->assertFalse( Recaptcha::is_enabled_for_form() );
	}

	public function test_is_enabled_for_form_false_when_toggle_off(): void {
		$this->set_settings( [
			'recaptcha_site_key'    => 'site',
			'recaptcha_secret_key'  => 'secret',
			'recaptcha_enable_form' => '0',
		] );
		$this->assertFalse( Recaptcha::is_enabled_for_form() );
	}

	public function test_is_enabled_for_form_true_when_configured_and_toggled(): void {
		$this->set_settings( [
			'recaptcha_site_key'    => 'site',
			'recaptcha_secret_key'  => 'secret',
			'recaptcha_enable_form' => '1',
		] );
		$this->assertTrue( Recaptcha::is_enabled_for_form() );
	}

	// -------------------------------------------------------------------------
	// get_version()
	// -------------------------------------------------------------------------

	public function test_get_version_defaults_to_v2(): void {
		$this->set_settings( [] );
		$this->assertSame( 'v2', Recaptcha::get_version() );
	}

	public function test_get_version_returns_v3_when_set(): void {
		$this->set_settings( [ 'recaptcha_version' => 'v3' ] );
		$this->assertSame( 'v3', Recaptcha::get_version() );
	}

	public function test_get_version_falls_back_to_v2_for_invalid_value(): void {
		$this->set_settings( [ 'recaptcha_version' => 'v99' ] );
		$this->assertSame( 'v2', Recaptcha::get_version() );
	}

	// -------------------------------------------------------------------------
	// get_score_threshold()
	// -------------------------------------------------------------------------

	public function test_get_score_threshold_defaults_to_half(): void {
		$this->set_settings( [] );
		$this->assertSame( 0.5, Recaptcha::get_score_threshold() );
	}

	public function test_get_score_threshold_clamps_above_one(): void {
		$this->set_settings( [ 'recaptcha_v3_threshold' => '2.0' ] );
		$this->assertSame( 1.0, Recaptcha::get_score_threshold() );
	}

	public function test_get_score_threshold_clamps_below_zero(): void {
		$this->set_settings( [ 'recaptcha_v3_threshold' => '-0.5' ] );
		$this->assertSame( 0.0, Recaptcha::get_score_threshold() );
	}

	public function test_get_score_threshold_returns_configured_value(): void {
		$this->set_settings( [ 'recaptcha_v3_threshold' => '0.7' ] );
		$this->assertSame( 0.7, Recaptcha::get_score_threshold() );
	}

	// -------------------------------------------------------------------------
	// widget_html() / token_input_html()
	// -------------------------------------------------------------------------

	public function test_widget_html_returns_empty_when_not_configured(): void {
		$this->set_settings( [ 'recaptcha_site_key' => '', 'recaptcha_secret_key' => '' ] );
		$this->assertSame( '', Recaptcha::widget_html() );
	}

	public function test_widget_html_returns_empty_for_v3(): void {
		$this->set_settings( [
			'recaptcha_site_key'    => 'site',
			'recaptcha_secret_key'  => 'secret',
			'recaptcha_version'     => 'v3',
		] );
		$this->assertSame( '', Recaptcha::widget_html() );
	}

	public function test_widget_html_returns_div_for_v2(): void {
		$this->set_settings( [
			'recaptcha_site_key'   => 'my_site_key',
			'recaptcha_secret_key' => 'secret',
			'recaptcha_version'    => 'v2',
		] );
		$html = Recaptcha::widget_html();
		$this->assertStringContainsString( 'g-recaptcha', $html );
		$this->assertStringContainsString( 'my_site_key', $html );
	}

	public function test_token_input_html_returns_empty_for_v2(): void {
		$this->set_settings( [
			'recaptcha_site_key'   => 'site',
			'recaptcha_secret_key' => 'secret',
			'recaptcha_version'    => 'v2',
		] );
		$this->assertSame( '', Recaptcha::token_input_html() );
	}

	public function test_token_input_html_returns_input_for_v3(): void {
		$this->set_settings( [
			'recaptcha_site_key'   => 'site',
			'recaptcha_secret_key' => 'secret',
			'recaptcha_version'    => 'v3',
		] );
		$html = Recaptcha::token_input_html();
		$this->assertStringContainsString( 'g-recaptcha-response', $html );
		$this->assertStringContainsString( 'input', $html );
	}
}
