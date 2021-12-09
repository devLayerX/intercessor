<?php
/**
 * Intercessor Upgrade
 *
 * @package     Intercessor
 * @subpackage  Admin/Upgrade
 * @author      Victor Aigbeghian
 * @license     http://opensource.org/licenses/GPL-3.0.php GNU Public License
 * @copyright   Copyright (c) 2021 Victor Aigbeghian
 * @version     1.1.0
 */

namespace Intercessor\Admin;

use function add_action;
use function remove_action;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Intercessor\Admin\Upgrade' ) ) {
    /**
     * Plugin Upgrade class.
     *
     * @since 1.1.0
     */
    class Upgrade {

		/**
		 * Constructor
		 *
		 * Get things going
		 *
		 * @since 1.1.0
         * @access public
		 *
		 * @return  void
		 */
		public function __construct() {

			// Actions.
			add_action( 'admin_menu', [ $this, 'menu' ], 20 );

            // Multisite action.
			if ( is_multisite() ) {
				add_action( 'network_admin_menu', [ $this, 'network_menu' ], 20 );
			}
		}

		/**
		 *  Admin menu.
		 *
		 *  Setups menu if upgrade is needed on a single site.
		 *
		 * @since 1.1.0
         * @access public
		 *
		 *  @return  void
		 */
		public function menu() {

			// check if upgrade is available.
			if ( \intercessor_has_upgrade() ) {

				// Add admin notices.
				add_action( 'admin_notices', array( $this, 'admin_notices' ) );

				// Add page.
				$menu_page = add_submenu_page(
                    'index.php',
                    esc_html__( 'Upgrade Database', 'intercessor' ),
                    esc_html__( 'Upgrade Database', 'intercessor' ),
                    'manage_prayer_settings',
                    'intercessor-upgrade',
                    [ $this, 'admin_view' ]
                );

				// Action to load page.
				add_action( 'load-' . $menu_page, [ $this, 'load' ] );
			}
		}

		/**
		 * Network admin menu.
		 *
		 * Setups admin menu if upgrade is required on a multi-site.
		 *
		 * @since 1.1.0
		 *
		 * @return void
		 */
		public function network_menu() {

			// Upgrade needed.
			$need_upgrade = false;

			// Check if there are sites available.
			$sites = \get_sites(
               [
                   'number' => 0,
               ]
            );

            // Process multi-site.
			if ( $sites ) {

				// Remove action to avoid memory issue.
				remove_action( 'switch_blog', 'wp_switch_roles_and_user', 1 );

				foreach ( $sites as $site ) {

					// Switch site.
					\switch_to_blog( $site->blog_id );

					// Check for upgrade.
					$upgrade_site = \intercessor_has_upgrade();

					// Restore current blog to modify global vars.
					\restore_current_blog();

					// Check if upgrade was found.
					if ( $upgrade_site ) {
						$need_upgrade = true;
						break;
					}
				}

                // Add the action formerly removed.
				add_action( 'switch_blog', 'wp_switch_roles_and_user', 1, 2 );
			}

			// Bail, if no upgrade is needed.
			if ( ! $need_upgrade ) {
				return;
			}

			// Add network admin notice.
			add_action( 'network_admin_notices', [ $this, 'network_admin_notices' ] );

			// Add page.
			$menu_page = \add_submenu_page(
				'index.php',
				esc_html__( 'Upgrade Database', 'intercessor' ),
				esc_html__( 'Upgrade Database', 'intercessor' ),
                'manage_prayer_settings',
				'intercessor-upgrade-network',
				[ $this, 'network_view' ]
			);

            // Action to load page.
			add_action( "load-$menu_page", [ $this, 'network_load' ] );
		}

		/**
		 * Set up the admin page.
		 *
		 * Runs during setting up of the admin page.
		 *
		 * @since 1.1.0
         * @access public
		 *
		 * @return void
		 */
		public function load() {

			// Remove the admin notices.
			remove_action( 'admin_notices', [ $this, 'admin_notices' ] );

			// Enqueue upgrade script.
			\wp_enqueue_script( 'intercessor_upgrade' );
		}

		/**
		 * Load network admin page.
		 *
		 * Runs during the loading of the network admin page.
		 *
		 * @since 1.1.0
		 *
		 * @return void
		 */
		public function network_load() {

			// Remove network admin notices.
			remove_action( 'network_admin_notices', [ $this, 'network_notices' ] );

			// Enqueue core script.
			wp_enqueue_script( 'intercessor_upgrade' );
		}

		/**
		 * Admin notices
		 *
		 * Displays the admin upgrade prompt.
		 *
		 * @since 1.1.0
		 *
		 * @return void
		 */
		private function admin_notices() {

			// Set up view.
			$setup = array(
				'button_text' => esc_html__( 'Upgrade Database', 'intercessor' ),
				'button_url'  => admin_url( 'index.php?page=intercessor-upgrade' ),
				'confirm'     => true,
			);

			// Get view.
			\intercessor_get_view( 'upgrade-notice', $setup );
		}

		/**
		 * Network admin notices.
		 *
		 * Displays the database update notice on the multi-site.
		 *
		 * @since 1.1.0
		 *
		 * @return void
		 */
		private function network_notices() {

			// Set up views.
			$setup = array(
				'button_text' => esc_html__( 'Review sites and upgrade', 'intercessor' ),
				'button_url'  => network_admin_url( 'index.php?page=intercessor-upgrade-network' ),
				'confirm'     => false,
			);

			// Get views.
			intercessor_get_view( 'upgrade-notice', $setup );
		}

		/**
		 * Admin view
		 *
		 * Displays the HTML for the admin page.
		 *
		 * @since 1.1.0
		 *
		 * @return void
		 */
		private function admin_view() {
			intercessor_get_view( 'admin-upgrade-page' );
		}

		/**
		 * Network admin view.
		 *
		 *  Displays the HTML for the network upgrade admin page.
		 *
		 * @since 1.1.0
		 *
		 *  @return  void
		 */
		function network_view() {
			intercessor_get_view( 'network-upgrade-page' );
		}
    }
}
