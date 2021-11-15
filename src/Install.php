<?php
/**
 * Intercessor Install class.
 *
 * The file that defines the core plugin installation functions and actions.
 *
 * @package     Intercessor
 * @subpackage  Classes/Loader
 * @copyright   Copyright (c) 2020, Victor Aigbeghian
 * @license     http://opensource.org/licenses/GPL-2.0.php GNU Public License
 * @since       1.0.0
 */

namespace Intercessor;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Installation class
 *
 * @since 1.0.0
 */
class Install {

    /**
     * Initialization functions.
     *
     * @since 1.0.0
     * @access public
     */
    public static function init() {
        if ( version_compare( \get_bloginfo( 'version' ), '5.1', '>=' ) ) {
            add_action( 'wp_initialize_site', [ __CLASS__, 'new_blog' ] );
        } else {
            add_action( 'wpmu_new_blog', [ __CLASS__, 'new_blog' ] );
        }

        add_action( 'admin_init', [ __CLASS__, 'after_install' ] );
        add_action( 'admin_init', [ __CLASS__, 'network_roles' ] );
    }

    /**
     * Activate the plugin to setup custom post types, etc.
     *
     * @param bool $network_wide Whether to activate network wide on multisite.
     *
     * @return void
     * @since 1.0.0
     *
     */
    public static function activate( bool $network_wide ) {
        // On multi-site(s).
        if ( \is_multisite() && ! empty( $network_wide ) ) {
            self::multisite_activation();

            // On single site.
        } else {
            self::single_activation();
        }
    }

    /**
     * Setup multisite activation.
     *
     * @since 1.0.0
     */
    public static function multisite_activation() {
        global $wpdb;

        // Get site(s) count.
        $network_id = \get_current_network_id();
        $query      = $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->blogs} WHERE site_id = %d", $network_id );
        $count      = $wpdb->get_var( $query );

        // Bail if no sites.
        if ( empty( $count ) || \is_wp_error( $count ) ) {
            return;
        }

        // Configure the steps to do.
        $per_step    = 100;
        $total_steps = ceil( $count / $per_step );
        $step        = 1;
        $offset      = 0;

        // Process the sites in the network in groups of 100.
        do {

            // Get next batch of site IDs.
            $query    = $wpdb->prepare( "SELECT blog_id FROM {$wpdb->blogs} WHERE site_id = %d LIMIT %d, %d", $network_id, $offset, $per_step );
            $site_ids = $wpdb->get_col( $query );

            // Proceed if site IDs exist.
            if ( ! empty( $site_ids ) ) {
                foreach ( $site_ids as $site_id ) {
                    self::single_activation( $site_id );
                }
            }

            // Bump the limit for the next iteration.
            $offset = ( $step * $per_step ) - 1;

            // Bump the step.
            ++$step;

        // Bail when steps are greater than or equal to total steps.
        } while ( $total_steps > $step );
    }

    /**
     * Setup single site activation.
     *
     * @param bool $site_id The site ID.
     *
     * @return void
     * @since 1.0.0
     *
     */
    public static function single_activation( bool $site_id ) {
        // Not changed.
        $changed = false;

        // Maybe switch to a site.
        if ( ! empty( $site_id ) ) {
            \switch_to_blog( $site_id );
            $changed = true;
        }

        // Retrieve current database version.
        $current_version = \intercessor_get_db_version();

        // Clear the permalinks.
        \flush_rewrite_rules( false );

        // Install default pages.
        \intercessor_setup_pages();

        // Install default options.
        $settings = new Admin\Settings();
        $settings->install();
        \intercessor_setup_default_options();

        // Maybe save the previous version, only if different than current
        if ( ! empty( $current_version ) && ( \intercessor_format_db_version( INTERCESSOR_VERSION ) !== $current_version ) ) {
            \update_option( 'intercessor_version_upgraded_from', $current_version );
        }

        // Setup Intercessor roles.
        $prayer_roles = new Roles();
		$prayer_roles->add_roles();
		$prayer_roles->add_caps();

        // Enable PHP session support if available.
        $session = new Session();
        $session->use_php_sessions();

        // Set the transient for redirection.
        set_transient( '_intercessor_redirect_activation', true, 30 );

        // Update database version.
        \intercessor_update_db_version();

        // Maybe switch back.
        if ( true === $changed ) {
            \restore_current_blog();
        }
    }

    /**
     * Install on new blog created in a multi-site.
     *
     * @since 1.0.0
     *
     * @param int|WP_Site $blog_id WP_Site object.
     * @return void
     */
    public static function new_blog( $blog_id ) {
        // Bail if plugin is not network activated.
        if ( ! \is_plugin_active_for_network( plugin_basename( INTERCESSOR_FILE ) ) ) {
            return;
        }

        // Return the blog id.
        if ( ! is_int( $blog_id ) ) {
            $blog_id = $blog_id->id;
        }

        // Activate plugin on new blog.
        \switch_to_blog( $blog_id );
        self::activate();
        \restore_current_blog();
    }

    /**
     * Runs after installation
     *
     * @since 1.0.0
     * @access public
     */
    public static function after_install() {
        // Bail if it is not an admin.
        if ( ! \is_admin() ) {
            return;
        }

        $intercessor_options = \get_transient( '_intercessor_installed' );

        /**
         * Fires after installation
         *
         * @param array $intercessor_options Array of options.
         * @since 1.0.0
         */
        do_action( 'intercessor_after_install', $intercessor_options );

        if ( false !== $intercessor_options ) {
            // Delete the transient.
            \delete_transient( '_intercessor_installed' );
        }
    }

    /**
     * Install network roles.
     *
     * @since 1.0.0
     * @access public
     */
    public static function network_roles() {
        global $wp_roles;

        // Bail if no WP roles.
        if ( ! is_object( $wp_roles ) ) {
            return;
        }

        // Check if Intercessor roles not created.
        if ( empty( $wp_roles->roles ) || ! array_key_exists( 'prayer_manager', $wp_roles->roles ) ) {
            // Create roles.
            $roles = new Roles();
            $roles->add_roles();
            $roles->add_caps();  
        }
    }
}
Install::init();
