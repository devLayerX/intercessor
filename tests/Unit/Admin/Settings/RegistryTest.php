<?php
/**
 * Unit tests for Intercessor\Admin\Settings\Registry.
 *
 * @package Intercessor\Tests\Unit\Admin\Settings
 */

declare(strict_types=1);

namespace Intercessor\Tests\Unit\Admin\Settings;

use Intercessor\Admin\Settings\Registry;
use PHPUnit\Framework\TestCase;

/**
 * Tests the settings schema Registry class.
 *
 * Registry is a pure value-object wrapper around the schema array — no WP
 * functions are called, making it ideal for thorough unit testing.
 */
class RegistryTest extends TestCase {

	/**
	 * Minimal schema fixture used across tests.
	 *
	 * @var array
	 */
	private array $schema;

	/** @var Registry */
	private Registry $registry;

	protected function setUp(): void {
		$this->schema = [
			'general' => [
				'approval' => [
					'title'  => 'Approval Rules',
					'fields' => [
						[ 'id' => 'auto_approve',  'type' => 'checkbox', 'label' => 'Auto Approve' ],
						[ 'id' => 'require_login', 'type' => 'checkbox', 'label' => 'Require Login' ],
					],
				],
			],
			'moderation' => [
				'moderation_options' => [
					'title'  => 'Moderation Options',
					'fields' => [
						[ 'id' => 'profanity_filter', 'type' => 'checkbox', 'label' => 'Enable Filter' ],
						[ 'id' => 'profanity_words',  'type' => 'textarea', 'label' => 'Words' ],
					],
				],
			],
			'recaptcha' => [
				'recaptcha_keys' => [
					'title'  => 'API Keys',
					'fields' => [
						[ 'id' => 'recaptcha_site_key',   'type' => 'text',     'label' => 'Site Key' ],
						[ 'id' => 'recaptcha_secret_key', 'type' => 'password', 'label' => 'Secret Key' ],
					],
				],
				'recaptcha_config' => [
					'title'  => 'Configuration',
					'fields' => [
						[ 'id' => 'recaptcha_version', 'type' => 'select', 'label' => 'Version' ],
					],
				],
			],
		];

		$this->registry = new Registry( $this->schema );
	}

	// -------------------------------------------------------------------------
	// all()
	// -------------------------------------------------------------------------

	public function test_all_returns_full_schema(): void {
		$this->assertSame( $this->schema, $this->registry->all() );
	}

	public function test_all_returns_empty_array_for_empty_schema(): void {
		$registry = new Registry();
		$this->assertSame( [], $registry->all() );
	}

	// -------------------------------------------------------------------------
	// get_tabs()
	// -------------------------------------------------------------------------

	public function test_get_tabs_returns_all_tab_slugs(): void {
		$this->assertSame( [ 'general', 'moderation', 'recaptcha' ], $this->registry->get_tabs() );
	}

	public function test_get_tabs_returns_empty_for_empty_schema(): void {
		$registry = new Registry();
		$this->assertSame( [], $registry->get_tabs() );
	}

	// -------------------------------------------------------------------------
	// get_sections()
	// -------------------------------------------------------------------------

	public function test_get_sections_returns_sections_for_valid_tab(): void {
		$sections = $this->registry->get_sections( 'recaptcha' );
		$this->assertArrayHasKey( 'recaptcha_keys', $sections );
		$this->assertArrayHasKey( 'recaptcha_config', $sections );
	}

	public function test_get_sections_returns_empty_for_unknown_tab(): void {
		$this->assertSame( [], $this->registry->get_sections( 'nonexistent' ) );
	}

	// -------------------------------------------------------------------------
	// get_fields()
	// -------------------------------------------------------------------------

	public function test_get_fields_returns_correct_fields(): void {
		$fields = $this->registry->get_fields( 'general', 'approval' );
		$this->assertCount( 2, $fields );
		$this->assertSame( 'auto_approve', $fields[0]['id'] );
		$this->assertSame( 'require_login', $fields[1]['id'] );
	}

	public function test_get_fields_returns_empty_for_unknown_section(): void {
		$this->assertSame( [], $this->registry->get_fields( 'general', 'nonexistent' ) );
	}

	public function test_get_fields_returns_empty_for_unknown_tab(): void {
		$this->assertSame( [], $this->registry->get_fields( 'nonexistent', 'approval' ) );
	}

	// -------------------------------------------------------------------------
	// get_section_title()
	// -------------------------------------------------------------------------

	public function test_get_section_title_returns_declared_title(): void {
		$this->assertSame( 'Approval Rules', $this->registry->get_section_title( 'general', 'approval' ) );
	}

	public function test_get_section_title_falls_back_to_ucfirst_slug(): void {
		// Unknown tab/section should return ucfirst of the section slug.
		$this->assertSame( 'Unknown', $this->registry->get_section_title( 'general', 'unknown' ) );
	}

	// -------------------------------------------------------------------------
	// get_field_types()
	// -------------------------------------------------------------------------

	public function test_get_field_types_returns_all_types_when_no_filter(): void {
		$types = $this->registry->get_field_types();
		$this->assertArrayHasKey( 'auto_approve',        $types );
		$this->assertArrayHasKey( 'profanity_filter',    $types );
		$this->assertArrayHasKey( 'recaptcha_site_key',  $types );
		$this->assertArrayHasKey( 'recaptcha_version',   $types );
		$this->assertSame( 'checkbox', $types['auto_approve'] );
		$this->assertSame( 'textarea', $types['profanity_words'] );
		$this->assertSame( 'password', $types['recaptcha_secret_key'] );
	}

	public function test_get_field_types_filters_by_tab(): void {
		$types = $this->registry->get_field_types( 'general' );
		$this->assertArrayHasKey( 'auto_approve',     $types );
		$this->assertArrayHasKey( 'require_login',    $types );
		$this->assertArrayNotHasKey( 'profanity_filter', $types );
		$this->assertArrayNotHasKey( 'recaptcha_version', $types );
	}

	public function test_get_field_types_filters_by_tab_and_section(): void {
		$types = $this->registry->get_field_types( 'recaptcha', 'recaptcha_keys' );
		$this->assertArrayHasKey( 'recaptcha_site_key',   $types );
		$this->assertArrayHasKey( 'recaptcha_secret_key', $types );
		$this->assertArrayNotHasKey( 'recaptcha_version', $types );
	}

	public function test_get_field_types_returns_empty_for_unknown_tab(): void {
		$this->assertSame( [], $this->registry->get_field_types( 'nonexistent' ) );
	}

	public function test_get_field_types_count_matches_total_fields(): void {
		$types = $this->registry->get_field_types();
		// general(2) + moderation(2) + recaptcha_keys(2) + recaptcha_config(1) = 7
		$this->assertCount( 7, $types );
	}
}
