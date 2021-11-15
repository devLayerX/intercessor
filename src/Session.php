<?php
/**
 * IPR Session
 *
 * This is a wrapper class for \WP_Session / PHP $_SESSION and handles the storage of request items, prayer sessions, etc
 *
 * @package     IPR
 * @subpackage  Classes/Session
 * @copyright   Copyright (c) 2019, Victor Aigbeghian
 * @license     http://opensource.org/licenses/GPL-3.0.php GNU Public License
 * @since       0.9.5
 */

namespace Intercessor;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Session Class
 *
 * @since 0.9.5
 */
class Session {

	/**
	 * Holds session data
	 *
	 * @var array
	 * @access private
	 * @since 0.9.5
	 */
	private $session;

	/**
	 * Whether to use PHP $_SESSION or \WP_Session
	 *
	 * @var bool
	 * @access private
	 * @since 0.9.5
	 */
	private $use_php_sessions = false;

	/**
	 * Session index prefix
	 *
	 * @var string
	 * @access private
	 * @since 0.9.5
	 */
	private $prefix = '';

	/**
	 * Session expiration time
	 *
	 * @var int
	 * @access private
	 * @since 0.9.5
	 */
	private $session_exp = false;

	/**
	 * Get things started
	 *
	 * Defines our \WP_Session constants, includes the necessary libraries and
	 * retrieves the \WP Session instance
	 *
	 * @since 0.9.5
	 */
	public function __construct() {
		// Settings object.
		$this->session_exp = \intercessor_get_option( 'session_lifetime' );

		// Use PHP sessions.
		$this->use_php_sessions = $this->use_php_sessions();

		if ( $this->use_php_sessions ) {
			if ( is_multisite() ) {
				$this->prefix = '_' . get_current_blog_id();
			}

			// Use PHP SESSION (must be enabled via the IPR_USE_PHP_SESSIONS constant).
			add_action( 'init', [ $this, 'maybe_start_session' ], -2 );
		} else {
			if ( ! $this->should_start_session() ) {
				return;
			}

			// Use \WP_Session (default).
			if ( ! defined( '\WP_SESSION_COOKIE' ) ) {
				define( '\WP_SESSION_COOKIE', 'intercessor_wp_session' );
			}

			if ( ! class_exists( '\Recursive_ArrayAccess' ) ) {
				require_once INTERCESSOR_DIR . 'src/libraries/class-recursive-arrayaccess.php';
			}

			if ( ! class_exists( '\WP_Session' ) ) {
				require_once INTERCESSOR_DIR . 'src/libraries/class-wp-session.php';
				require_once INTERCESSOR_DIR . 'src/libraries/wp-session.php';
			}

			add_filter( 'wp_session_expiration_variant', [ $this, 'set_expiration_variant_time' ], 99999 );
			add_filter( 'wp_session_expiration', [ $this, 'set_expiration_time' ], 99999 );

		}

		$hook = ( empty( $this->session ) && ! $this->use_php_sessions )
			? 'plugins_loaded'
			: 'init';

		add_action( $hook, [ $this, 'init' ], -1 );

		add_action( 'intercessor_pre_process_prayer', [ $this, 'set_session_cookies' ] );
	}

	/**
	 * Setup the \WP_Session instance
	 *
	 * @access public
	 * @since 0.9.5
	 * @return string
	 */
	public function init() {

		if ( $this->use_php_sessions ) {
			$this->session = isset( $_SESSION[ 'ipr' . $this->prefix ] ) && is_array( $_SESSION[ 'ipr' . $this->prefix ] ) ? $_SESSION[ 'ipr' . $this->prefix ] : [];
		} else {
			$this->session = \WP_Session::get_instance();
		}

		return $this->session;
	}


	/**
	 * Retrieve session ID
	 *
	 * @access public
	 * @since 0.9.5
	 * @return array Session ID
	 */
	public function get_id() {
		return $this->session->session_id;
	}


	/**
	 * Retrieve a session variable
	 *
	 * @param string $key Session key.
	 *
	 * @access public
	 * @since 0.9.5
	 * @return mixed Session variable
	 */
	public function get( $key ) {

		$key    = \sanitize_key( $key );
		$return = false;

		if ( isset( $this->session[ $key ] ) && ! empty( $this->session[ $key ] ) ) {

			preg_match( '/[oO]\s*:\s*\d+\s*:\s*"\s*(?!(?i)(stdClass))/', $this->session[ $key ], $matches );
			if ( ! empty( $matches ) ) {
				$this->set( $key, null );
				return false;
			}

			if ( is_numeric( $this->session[ $key ] ) ) {
				$return = $this->session[ $key ];
			} else {

				$maybe_json = json_decode( $this->session[ $key ] );

				// Since json_last_error is PHP 5.3+, we have to rely on a `null` value for failing to parse JSON.
				if ( is_null( $maybe_json ) ) {
					$is_serialized = is_serialized( $this->session[ $key ] );
					if ( $is_serialized ) {
						$value = @unserialize( $this->session[ $key ] );
						$this->set( $key, (array) $value );
						$return = $value;
					} else {
						$return = $this->session[ $key ];
					}
				} else {
					$return = json_decode( $this->session[ $key ], true );
				}

			}
		}

		return $return;
	}

	/**
	 * Set a session variable
	 *
	 * @param string           $key   Session key.
	 * @param int|string|array $value Session variable.
	 *
	 * @since 0.9.5
	 * @return mixed Session variable.
	 */
	public function set( $key, $value ) {

		$key = \sanitize_key( $key );

		if ( is_array( $value ) ) {
			$this->session[ $key ] = \wp_json_encode( $value );
		} else {
			$this->session[ $key ] = esc_attr( $value );
		}

		if ( $this->use_php_sessions ) {
			$_SESSION[ 'ipr' . $this->prefix ] = $this->session;
		}

		return $this->session[ $key ];
	}

	/**
	 * Set a cookie to identify whether the request is empty or not
	 *
	 * This is for hosts and caching plugins to identify if caching should be disabled
	 *
	 * @access public
	 * @since 0.9.5
	 * @return void
	 */
	public function set_session_cookies() {
		if ( ! headers_sent() ) {
			$lifetime = current_time( 'timestamp' ) + $this->set_expiration_time();
			@setcookie( session_name(), session_id(), $lifetime, COOKIEPATH, COOKIE_DOMAIN, false );
			@setcookie( session_name() . '_expiration', $lifetime, $lifetime, COOKIEPATH, COOKIE_DOMAIN, false );
		}
	}

	/**
	 * Set the cookie expiration variant time to value defined in IPR settings
	 *
	 * @access public
	 * @since 0.9.5
	 * @return int
	 */
	public function set_expiration_variant_time() {
		return ( ! empty( $this->session_exp ) ? ( intval( $this->session_exp ) - 3600 ) : 30 * 60 * 23 );
	}

	/**
	 * Set the cookie expiration time to 24 hours, if not already defined.
	 *
	 * @access public
	 * @since 0.9.5
	 * @return int Cookie expiration time
	 */
	public function set_expiration_time() {
		return ( ! empty( $this->session_exp ) ? intval( $this->session_exp ) : 30 * 60 * 24 );
	}

	/**
	 * Starts a new session if one hasn't started yet.
	 *
	 * Checks to see if the server supports PHP sessions
	 * or if the INTERCESSOR_USE_PHP_SESSIONS constant is defined.
	 *
	 * @access public
	 * @since 0.9.5
	 * @return boolean $ret True if we are using PHP sessions, false otherwise
	 */
	public function use_php_sessions() {

		$ret = false;

		// If the database variable is already set, no need to run autodetection.
		$intercessor_use_php_sessions = (bool) \get_option( 'intercessor_use_php_sessions' );

		if ( ! $intercessor_use_php_sessions ) {

			// Attempt to detect if the server supports PHP sessions.
			if ( function_exists( 'session_start' ) && ! ini_get( 'safe_mode' ) ) {
				$this->set( 'intercessor_use_php_sessions', 1 );

				if ( $this->get( 'intercessor_use_php_sessions' ) ) {
					$ret = true;

					// Set the database option.
					update_option( 'intercessor_use_php_sessions', true );
				}
			}
		} else {
			$ret = $intercessor_use_php_sessions;
		}

		// Enable or disable PHP Sessions based on the IPR_USE_PHP_SESSIONS constant.
		if ( defined( 'IPR_USE_PHP_SESSIONS' ) && IPR_USE_PHP_SESSIONS ) {
			$ret = true;
		} elseif ( defined( 'IPR_USE_PHP_SESSIONS' ) && ! IPR_USE_PHP_SESSIONS ) {
			$ret = false;
		}

		return (bool) apply_filters( 'intercessor_use_php_sessions', $ret );
	}

	/**
	 * Determines if we should start sessions
	 *
	 * @since  0.9.5
	 * @return bool
	 */
	public function should_start_session() {
		// Set default return value to true.
		$start_session = true;

		if ( ! empty( $_SERVER['REQUEST_URI'] ) ) {
			$blacklist = $this->get_blacklist();
			$uri       = ltrim( $_SERVER['REQUEST_URI'], '/' );
			$uri       = untrailingslashit( $uri );

			if ( in_array( $uri, $blacklist, true ) ) {
				$start_session = false;
			}

			if ( false !== strpos( $uri, 'feed=' ) ) {
				$start_session = false;
			}

			// We do not want to start sessions in the admin unless we're processing an ajax request.
			if ( \is_admin() && false === strpos( $uri, 'wp-admin/admin-ajax.php' ) ) {
				$start_session = false;
			}

			// Starting sessions while saving the file editor can break the save process, so don't start.
			if ( false !== strpos( $uri, 'wp_scrape_key' ) ) {
				$start_session = false;
			}
		}

		// Filter & return.
		return (bool) apply_filters( 'intercessor_start_session', $start_session );
	}

	/**
	 * Retrieve the URI blacklist
	 *
	 * These are the URIs where we never start sessions
	 *
	 * @since  0.9.5.11
	 * @return array
	 */
	public function get_blacklist() {

		$blacklist = apply_filters(
			'intercessor_session_start_uri_blacklist',
			[
				'feed',
				'feed/rss',
				'feed/rss2',
				'feed/rdf',
				'feed/atom',
				'comments/feed',
			]
		);

		// Look to see if WordPress is in a sub folder or this is a network site that uses sub folders.
		$home   = \network_home_url();
		$site   = \get_site_url();
		$folder = str_replace( $home, '', $site );

		if ( ! empty( $folder ) ) {
			foreach ( $blacklist as $path ) {
				$blacklist[] = $folder . '/' . $path;
			}
		}

		return $blacklist;
	}

	/**
	 * Get Session Expiration
	 *
	 * Looks at the session cookies and returns the expiration date for this session if applicable
	 *
	 * @access public
	 *
	 * @return string Formatted expiration date string.
	 */
	public function get_session_expiration() {

		$expiration = false;

		if ( session_id() && isset( $_COOKIE[ session_name() . '_expiration' ] ) ) {

			$expiration = date( 'D, d M Y h:i:s', intval( $_COOKIE[ session_name() . '_expiration' ] ) );

		}

		return $expiration;

	}

	/**
	 * Starts a new session if one hasn't started yet.
	 */
	public function maybe_start_session() {

		// Bail if should not start session.
		if ( ! $this->should_start_session() ) {
			return;
		}

		// Bail if headers already sent.
		if ( headers_sent() ) {
			return;
		}

		// Start if modern PHP and session-status is not active.
		if ( defined( 'PHP_SESSION_ACTIVE' ) && ( session_status() !== PHP_SESSION_ACTIVE ) ) {
			session_start();
		}
	}

}
