<?php
declare(strict_types=1);

namespace Intercessor\Http;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;


/**
 * Class Request
 *
 * WordPress-aware HTTP request abstraction.
 *
 * Provides sanitized, unslashed, and typed access
 * to request data without directly using superglobals.
 *
 * @package Intercessor\Http
 */
final class Request {

    /**
     * GET data.
     *
     * @var array
     */
    private array $get;

    /**
     * POST data.
     *
     * @var array
     */
    private array $post;

    /**
     * Server data.
     *
     * @var array
     */
    private array $server;

    /**
     * Constructor.
     *
     * @since 1.0.0
     *
     * @param array $get    $_GET data.
     * @param array $post   $_POST data.
     * @param array $server $_SERVER data.
     *
     * @return void
     */
    public function __construct( array $get, array $post, array $server ) {
        $this->get    = $this->unslash( $get );
        $this->post   = $this->unslash( $post );
        $this->server = $server;
    }

    /**
     * Capture request from globals.
     *
     * @since  1.0.0
     * @return self
     */
    public static function capture(): self {
        // phpcs:disable WordPress.Security.NonceVerification.Recommended,WordPress.Security.NonceVerification.Missing
        // Nonce verification is the responsibility of callers — this class is a
        // low-level capture wrapper; enforcing nonces here would prevent legitimate
        // read-only requests (pagination, filters) from reaching their handlers.
        return new self(
            wp_unslash( $_GET ),  // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            wp_unslash( $_POST ), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            wp_unslash( $_SERVER )
        );
        // phpcs:enable WordPress.Security.NonceVerification.Recommended,WordPress.Security.NonceVerification.Missing
    }

    // ---------------------------------------------------------------------
    // Core Access
    // ---------------------------------------------------------------------

    /**
     * Retrieve input from POST or GET.
     *
     * @since 1.0.0
     *
     * @param string $key     Input key.
     * @param mixed  $default Default value.
     *
     * @return mixed
     */
    public function input( string $key, $default = null ) {
        return $this->post[ $key ]
            ?? $this->get[ $key ]
            ?? $default;
    }

    /**
     * Retrieve POST value.
     *
     * @since 1.0.0
     *
     * @param string $key     Input key.
     * @param mixed  $default Default value.
     *
     * @return mixed
     */
    public function post( string $key, $default = null ) {
        return $this->post[ $key ] ?? $default;
    }

    /**
     * Retrieve GET value.
     *
     * @since 1.0.0
     *
     * @param string $key     Input key.
     * @param mixed  $default Default value.
     *
     * @return mixed
     */
    public function get( string $key, $default = null ) {
        return $this->get[ $key ] ?? $default;
    }

    /**
     * Check if input exists.
     *
     * @since 1.0.0
     *
     * @param string $key Input key.
     *
     * @return bool
     */
    public function has( string $key ): bool {
        return isset( $this->post[ $key ] ) || isset( $this->get[ $key ] );
    }

    // ---------------------------------------------------------------------
    // Typed Accessors
    // ---------------------------------------------------------------------

    /**
     * Get sanitized string.
     *
     * @since 1.0.0
     *
     * @param string $key     Input key.
     * @param string $default Default value.
     *
     * @return string
     */
    public function get_string( string $key, string $default = '' ): string {
        return sanitize_text_field( (string) $this->input( $key, $default ) );
    }

    /**
     * Get integer value.
     *
     * @since 1.0.0
     *
     * @param string $key     Input key.
     * @param int    $default Default value.
     *
     * @return int
     */
    public function get_int( string $key, int $default = 0 ): int {
        return absint( $this->input( $key, $default ) );
    }

    /**
     * Get boolean value.
     *
     * @since 1.0.0
     *
     * @param string $key     Input key.
     * @param bool   $default Default value.
     *
     * @return bool
     */
    public function get_bool( string $key, bool $default = false ): bool {
        return (bool) $this->input( $key, $default );
    }

    /**
     * Get sanitized email.
     *
     * @since 1.0.0
     *
     * @param string $key     Input key.
     * @param string $default Default value.
     *
     * @return string
     */
    public function get_email( string $key, string $default = '' ): string {
        return sanitize_email( (string) $this->input( $key, $default ) );
    }

    /**
     * Get array value.
     *
     * @since 1.0.0
     *
     * @param string $key Input key.
     *
     * @return array
     */
    public function get_array( string $key ): array {
        $value = $this->input( $key, [] );

        return is_array( $value ) ? $value : [];
    }

    /**
     * Get sanitized integer array (e.g., IDs).
     *
     * @since 1.0.0
     *
     * @param string $key Input key.
     *
     * @return array
     */
    public function get_int_array( string $key ): array {
        $values = $this->get_array( $key );

        return array_values(
            array_filter(
                array_map( 'absint', $values ),
                static fn( $v ) => $v > 0
            )
        );
    }


    /**
     * Get sanitized textarea value.
     *
     * Uses sanitize_textarea_field() which preserves newlines, unlike
     * get_string() which uses sanitize_text_field() and strips them.
     *
     * @since 1.0.0
     *
     * @param string $key     Input key.
     * @param string $default Default value.
     *
     * @return string
     */
    public function get_textarea( string $key, string $default = '' ): string {
        return sanitize_textarea_field( (string) $this->input( $key, $default ) );
    }

    /**
     * Get sanitized key value (alphanumeric, dashes, underscores only).
     *
     * Uses sanitize_key() — suitable for status values, action slugs,
     * orderby columns, and other identifier-style fields.
     *
     * @since 1.0.0
     *
     * @param string $key     Input key.
     * @param string $default Default value.
     *
     * @return string
     */
    public function get_key( string $key, string $default = '' ): string {
        return sanitize_key( (string) $this->input( $key, $default ) );
    }

    // ---------------------------------------------------------------------
    // Security
    // ---------------------------------------------------------------------

    /**
     * Verify nonce.
     *
     * @since 1.0.0
     *
     * @param string $action Nonce action.
     * @param string $field  Nonce field name.
     *
     * @return bool
     */
    public function verify_nonce( string $action, string $field = '_wpnonce' ): bool {
        $nonce = $this->post( $field );

        if ( ! $nonce ) {
            return false;
        }

        return wp_verify_nonce( $nonce, $action ) !== false;
    }

    /**
     * Require valid nonce or terminate.
     *
     * @since 1.0.0
     *
     * @param string $action Nonce action.
     * @param string $field  Nonce field name.
     *
     * @return void
     */
    public function require_nonce( string $action, string $field = '_wpnonce' ): void {
        if ( ! $this->verify_nonce( $action, $field ) ) {
            wp_die( esc_html__( 'Security check failed.', 'intercessor' ) );
        }
    }

    // ---------------------------------------------------------------------
    // Request Meta
    // ---------------------------------------------------------------------

    /**
     * Get HTTP method.
     *
     * @since  1.0.0
     * @return string
     */
    public function get_method(): string {
        return strtoupper( $this->server['REQUEST_METHOD'] ?? 'GET' );
    }

    /**
     * Check if POST request.
     *
     * @since 1.0.0
     * @return bool
     */
    public function is_post(): bool {
        return 'POST' === $this->get_method();
    }

    /**
     * Check if GET request.
     *
     * @since  1.0.0
     * @return bool
     */
    public function is_get(): bool {
        return 'GET' === $this->get_method();
    }


    /**
     * Get the sanitized remote IP address from SERVER data.
     *
     * Reads REMOTE_ADDR only — never X-Forwarded-For — to prevent
     * IP spoofing by malicious clients.
     *
     * @since  1.0.0
     * @return string IP address string, or empty string if unavailable.
     */
    public function get_remote_addr(): string {
        return sanitize_text_field( (string) ( $this->server['REMOTE_ADDR'] ?? '' ) );
    }

    /**
     * Get the sanitized HTTP User-Agent string from SERVER data.
     *
     * @since  1.0.0
     * @return string User-Agent string, or empty string if unavailable.
     */
    public function get_user_agent(): string {
        return sanitize_text_field( (string) ( $this->server['HTTP_USER_AGENT'] ?? '' ) );
    }

    // ---------------------------------------------------------------------
    // Internal Helpers
    // ---------------------------------------------------------------------

    /**
     * Recursively unslash data.
     *
     * @since 1.0.0
     *
     * @param array $data Input data.
     *
     * @return array
     */
    private function unslash( array $data ): array {
        return array_map(
            function ( $value ) {
                return is_array( $value )
                    ? $this->unslash( $value )
                    : wp_unslash( $value );
            },
            $data
        );
    }
}
