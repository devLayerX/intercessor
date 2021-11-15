<?php
/**
 * Intercessor Admin Welcome.
 *
 * @package     Intercessor
 * @subpackage  Admin/Welcome
 * @copyright   Copyright (c) 2020, Victor Aigbeghian
 * @license     http://opensource.org/licenses/GPL-3.0.php GNU Public License
 * @since       1.0.0
 */
 
namespace Intercessor\Admin;

use function admin_url;
use function add_query_arg;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Intercessor Admin Welcome Class
 *
 * A general class for generating the Welcome screen, About and Credits pages.
 *
 * @since 0.9.5
 */
class Welcome {

	/**
     * The capability users should have to view the page
	 * @var string
	 */
	public $minimum_capability = 'manage_options';

	/**
	 * Get things started
	 *
	 * @since 0.9.5
	 */
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'setup_admin_menus' ] );
		add_action( 'admin_head', [ $this, 'setup_admin_head' ] );
		add_action( 'admin_init', [ $this, 'welcome' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'intercessor_welcome_style' ] );
	}

	/**
	 * Register the Dashboard Pages which are later hidden but these pages
	 * are used to render the Welcome and Credits pages.
	 *
	 * @access public
	 * @since  0.9.5
	 * @return void
	 */
	public function setup_admin_menus() {
		global $intercessor_welcome_screen;

		list( $display_version ) = explode( '-', INTERCESSOR_VERSION );

		// About Page.
		$intercessor_welcome_screen = \add_dashboard_page(
		/* translators: %s: Intercessor version */
			sprintf( esc_html__( 'Welcome to Intercessor %s', 'intercessor' ), $display_version ),
			esc_html__( 'Welcome to Intercessor', 'intercessor' ),
			$this->minimum_capability,
			'intercessor-about',
			[ $this, 'about_screen' ]
		);

		// Changelog Page
		add_dashboard_page(
			esc_html__( 'Intercessor Changelog', 'intercessor' ),
			esc_html__( 'Intercessor Changelog', 'intercessor' ),
			$this->minimum_capability,
			'intercessor-changelog',
			[ $this, 'changelog_screen' ]
		);

		// Getting Started Page
		add_dashboard_page(
		/* translators: %s: Intercessor version */
			sprintf( esc_html__( 'Getting Started Guide', 'intercessor' ), $display_version ),
			esc_html__( 'Getting started with Intercessor', 'intercessor' ),
			$this->minimum_capability,
			'intercessor-getting-started',
			[ $this, 'getting_started_screen' ]
		);

		// Credits Page
		add_dashboard_page(
		/* translators: %s: Intercessor version */
			sprintf( esc_html__( 'Intercessor %s - Credits', 'intercessor' ), $display_version ),
			esc_html__( 'The people behind Intercessor', 'intercessor' ),
			$this->minimum_capability,
			'intercessor-credits',
			[ $this, 'credits_screen' ]
		);
	}

	/**
	 * Hide Individual Dashboard Pages
	 *
	 * @access public
	 * @since  0.9.5
	 * @return void
	 */
	public function setup_admin_head() {
		remove_submenu_page( 'index.php', 'intercessor-about' );
		remove_submenu_page( 'index.php', 'intercessor-changelog' );
		remove_submenu_page( 'index.php', 'intercessor-getting-started' );
		remove_submenu_page( 'index.php', 'intercessor-credits' );
	}

	/**
	 * Navigation tabs
	 *
	 * @access public
	 * @since  0.9.5
	 * @return void
	 */
	public function tabs() {
		$selected = isset( $_GET['page'] ) ? $_GET['page'] : 'intercessor-about';
		?>
        <h2 class="nav-tab-wrapper">
            <a class="nav-tab <?php echo $selected == 'intercessor-about' ? 'nav-tab-active' : ''; ?>"
               href="<?php echo esc_url( admin_url( add_query_arg( [ 'page' => 'intercessor-about' ], 'index.php' ) ) ); ?>">
				<?php esc_html_e( 'About Intercessor', 'intercessor' ); ?>
            </a>
            <a class="nav-tab <?php echo $selected == 'intercessor-getting-started' ? 'nav-tab-active' : ''; ?>"
               href="<?php echo esc_url( admin_url( add_query_arg( [ 'page' => 'intercessor-getting-started' ], 'index.php' ) ) ); ?>">
				<?php esc_html_e( 'Getting Started', 'intercessor' ); ?>
            </a>
            <a class="nav-tab <?php echo $selected == 'intercessor-credits' ? 'nav-tab-active' : ''; ?>"
               href="<?php echo esc_url( admin_url( add_query_arg( [ 'page' => 'intercessor-credits' ], 'index.php' ) ) ); ?>">
				<?php esc_html_e( 'Credits', 'intercessor' ); ?>
            </a>
            <a class="nav-tab <?php echo $selected == 'intercessor-changelog' ? 'nav-tab-active' : ''; ?>"
               href="<?php echo esc_url( admin_url( add_query_arg( [ 'page' => 'intercessor-changelog' ], 'index.php' ) ) ); ?>">
				<?php esc_html_e( 'Changelog', 'intercessor' ); ?>
            </a>
        </h2>
		<?php
	}

	/**
	 * Render About Screen
	 *
	 * @access public
	 * @since  0.9.5
	 * @return void
	 */
	public function about_screen() {
		list( $display_version ) = explode( '-', INTERCESSOR_VERSION );
		?>
        <div class="wrap about-wrap">

			<?php
				$this->get_welcome_header();
			?>

            <p class="about-text">
				<?php
				printf(
				/* translators: %s: http://docs.intercessorwp.com/docs */
					__( 'Thank you for activating or updating to the latest version of Intercessor! On first time activation, Intercessor creates three pages: prayer request form, listing and history pages. These are automatically filled with their respective shortcodes. You could check out the <a href="%s" target="_blank">plugin documentation</a> and getting started guide below as required.', 'intercessor' ),
					esc_url( 'http://docs.intercessorwp.com/docs' )
				);
				?>
			</p>

            <div class="intercessor-badge">
				<?php
				printf(
				/* translators: %s: Intercessor version */
					esc_html__( 'Version %s', 'intercessor' ),
					$display_version
				);
				?>
			</div>

			<?php $this->tabs(); ?>
			
			<div class="intercessor-scriptures clearfix">
				<p><?php esc_html_e( 'And I sought for a man among them, that should make up the hedge, and stand in the gap before me for the land... Ezekiel 22 vs 30 KJV', 'intercessor' ); ?></p>
				
				<p><?php esc_html_e( 'Therefore I exhort first of all that supplications, prayers, intercessions, and giving of thanks be made for all men, 1 Timothy 2 vs 1 NKJV', 'intercessor' ) ; ?></p>
			</div>	
		
            <div class="feature-section clearfix introduction">

                <div class="video feature-item">
                    <img src="<?php echo INTERCESSOR_URL . 'assets/images/praying.jpg' ?>"
                         alt="<?php esc_attr_e( 'Intercessor', 'intercessor' ); ?>">
                </div>

                <div class="content feature-item last-item">

                    <h3><?php esc_html_e( 'Intercessor - Intercessory Prayer Requests', 'intercessor' ); ?></h3>

                    <p><?php esc_html_e( 'Intercessor is designed by an experienced intercessor to assist your prayer ministry in fulfilling the task of interceding and praying for one another or for users who submit their prayer requests on your website. You can specify the default status of submitted prayer requests in the settings options, as well as create, view the details of, edit or delete a prayer request from the admin dashboard. Active prayer request can also be prayed for on the details view screen.', 'intercessor' ); ?></p>
                    <a href="https://intercessorwp.com" target="_blank" class="button-secondary">
						<?php esc_html_e( 'Learn More', 'intercessor' ); ?>
                        <span class="dashicons dashicons-external"></span>
                    </a>

                </div>

            </div>
            <!-- /.intro-section -->

            <div class="feature-section clearfix">

                <div class="content feature-item">

                    <h3><?php esc_html_e( 'Getting to Know Intercessor', 'intercessor' ); ?></h3>

                    <p><?php esc_html_e( 'Before you get started with Intercessor we suggest you take a look at the online documentation. There you will find the getting started guide which will help you get up and running quickly. If you have a question, issue or bug with the Core plugin please submit an issue on the Intercessor website. We also welcome your feedback and feature requests. Welcome to Intercessor. We hope you much success with your cause.', 'intercessor' ); ?></p>

                    <h4>Find Out More:</h4>
                    <ul class="ul-disc">
                        <li><a href="https://intercessorwp.com/" target="_blank"><?php esc_html_e( 'Visit the Intercessor Website', 'intercessor' ); ?></a></li>
                        <li><a href="https://intercessorwp.com/features/" target="_blank"><?php esc_html_e( 'View the Intercessor Features', 'intercessor' ); ?></a></li>
                        <li><a href="https://intercessorwp.com/documentation/" target="_blank"><?php esc_html_e( 'Read the Documentation', 'intercessor' ); ?></a></li>
                    </ul>

                </div>

                <div class="content  feature-item last-item">
                    <img src="<?php echo INTERCESSOR_URL . '/assets/images/admin/intercessor-form-mockup.png' ?>"
                         alt="<?php esc_attr_e( 'An Intercessor request form', 'intercessor' ); ?>">
                </div>

            </div>
            <!-- /.feature-section -->


        </div>
		<?php
	}

	/**
	 * Render Changelog Screen
	 *
	 * @access public
	 * @since  0.9.5
	 * @return void
	 */
	public function changelog_screen() {
		list( $display_version ) = explode( '-', INTERCESSOR_VERSION );
		?>
        <div class="wrap about-wrap">

			<?php $this->get_welcome_header() ?>
			
            <p class="about-text"><?php
				printf(
				/* translators: %s: Intercessor version */
					esc_html__( 'Thank you for updating to the latest version! Intercessor %s is ready to make your online store faster, safer, and better!', 'intercessor' ),
					$display_version
				);
				?></p>
            <div class="intercessor-badge"><?php
				printf(
				/* translators: %s: Intercessor version */
					esc_html__( 'Version %s', 'intercessor' ),
					$display_version
				);
				?></div>

			<?php $this->tabs(); ?>

            <div class="changelog">
                <h3><?php esc_html_e( 'Full Changelog', 'intercessor' ); ?></h3>

                <div class="feature-section">
					<?php echo $this->parse_readme(); ?>
                </div>
            </div>

            <div class="return-to-dashboard">
                <a href="<?php echo esc_url(
					admin_url(
						add_query_arg(
							[
								'page' => 'intercessor-settings',
							],
							'admin.php'
						)
					)
						); ?>">
					<?php esc_html_e( 'Intercessor Settings', 'intercessor' ); ?>
				</a>
            </div>
        </div>
		<?php
	}

	/**
	 * Render Getting Started Screen
	 *
	 * @access public
	 * @since  0.9.5
	 * @return void
	 */
	public function getting_started_screen() {
		list( $display_version ) = explode( '-', INTERCESSOR_VERSION );
		?>
        <div class="wrap about-wrap get-started">

			<?php $this->get_welcome_header() ?>

            <p class="about-text"><?php esc_html_e( 'Welcome to the getting started guide.', 'intercessor' ); ?></p>

            <div class="intercessor-badge"><?php
				printf(
				/* translators: %s: Intercessor version */
					esc_html__( 'Version %s', 'intercessor' ),
					$display_version
				);
				?></div>

			<?php $this->tabs(); ?>

            <p class="about-text"><?php printf( esc_html__( 'Getting started with Intercessor is easy! We put together this quick start guide to help first time users of the plugin. Our goal is to get you up and running in no time. Let\'s begin!', 'intercessor' ), $display_version ); ?></p>

            <div class="feature-section clearfix">

                <div class="content feature-item">
                    <h3><?php esc_html_e( 'STEP 1: Create a New Form', 'intercessor' ); ?></h3>

                    <p><?php esc_html_e( 'Intercessor is driven by it\'s powerful donation form building features. However, it is much more than just a "donation form". From the "Add Form" page you\'ll be able to choose how and where you want to receive your donations. You will also be able to set the preferred donation amounts.', 'intercessor' ); ?></p>

                    <p><?php esc_html_e( 'All of these features begin by simply going to the menu and choosing "Donations > Add Form".', 'intercessor' ); ?></p>
                </div>

                <div class="content feature-item last-item">
                    <img src="<?php echo INTERCESSOR_URL; ?>assets/images/admin/getting-started-add-new-form.png">
                </div>

            </div>
            <!-- /.feature-section -->

            <div class="feature-section clearfix">

                <div class="content feature-item multi-level-gif">
                    <img src="<?php echo INTERCESSOR_URL; ?>assets/images/admin/getting-started-new-form-multi-level.gif">
                </div>

                <div class="content feature-item last-item">
                    <h3><?php esc_html_e( 'STEP 2: Customize Your Donation Forms', 'intercessor' ); ?></h3>

                    <p><?php esc_html_e( 'Each donation form you create can be customized to receive either a pre-determined set donation amount or have multiple suggested levels of giving. Choosing "Multi-level Donation" opens up the donation levels view where you can add as many levels as you\'d like with your own custom names and suggested amounts. As well, you can allow donors to give a custom amount and even set up donation goals.', 'intercessor' ); ?></p>
                </div>

            </div>
            <!-- /.feature-section -->

            <div class="feature-section clearfix">

                <div class="content feature-item add-content">
                    <h3><?php esc_html_e( 'STEP 3: Add Additional Content', 'intercessor' ); ?></h3>

                    <p><?php esc_html_e( 'Every donation form you create with Intercessor can be used on its own stand-alone page, or it can be inserted into any other page or post throughout your site via a shortcode or widget.', 'intercessor' ); ?></p>

                    <p><?php esc_html_e( 'You can choose these different modes by going to the "Form Content" section. From there, you can choose to add content before or after the donation form on a page, or if you choose "None" perhaps you want to instead use the shortcode. You can find the shortcode in the top right column directly under the Publish/Save button. This feature gives you the most amount of flexibility with controlling your content on your website all within the same page.', 'intercessor' ); ?></p>
                </div>

                <div class="content feature-item last-item">
                    <img src="<?php echo INTERCESSOR_URL; ?>assets/images/admin/getting-started-add-content.png">
                </div>

            </div>
            <!-- /.feature-section -->

            <div class="feature-section clearfix">

                <div class="content feature-item display-options">
                    <img src="<?php echo INTERCESSOR_URL; ?>assets/images/admin/getting-started-display-options.png">
                </div>

                <div class="content feature-item last-item">
                    <h3><?php esc_html_e( 'STEP 4: Configure Your Display Options', 'intercessor' ); ?></h3>

                    <p><?php esc_html_e( 'Lastly, you can present the form in a number of different ways that each create their own unique donor experience. The "Modal" display mode opens the credit card fieldset within a popup window. The "Reveal" mode will slide into place the additional fields. If you\'re looking for a simple button, then "Button" more is the way to go. This allows you to create a customizable "Donate Now" button which will open the donation form upon clicking. There\'s tons of possibilities here, give it a try!', 'intercessor' ); ?></p>
                </div>


            </div>
            <!-- /.feature-section -->
        </div>
		<?php
	}

	/**
	 * Render Credits Screen
	 *
	 * @access public
	 * @since  0.9.5
	 * @return void
	 */
	public function credits_screen() {
		list( $display_version ) = explode( '-', INTERCESSOR_VERSION );
		?>
        <div class="wrap about-wrap">

			<?php $this->get_welcome_header() ?>

            <p class="about-text"><?php esc_html_e( 'Thanks to all those who have contributed code directly or indirectly.', 'intercessor' ); ?></p>


            <div class="intercessor-badge"><?php
				printf(
				/* translators: %s: Intercessor version */
					esc_html__( 'Version %s', 'intercessor' ),
					$display_version
				);
				?></div>

			<?php $this->tabs(); ?>

            <p class="about-description"><?php
				printf(
				/* translators: %s: https://github.com/VictorAigbeghian/intercessor */
					__( 'Intercessor is created by a dedicated team of developers. If you are interested in contributing please visit the <a href="%s" target="_blank">GitHub Repo</a>.', 'intercessor' ),
					esc_url( 'https://github.com/VictorAigbeghian/intercessor' )
				);
				?></p>

			<?php echo $this->contributors(); ?>
        </div>
		<?php
	}


	/**
	 * Parse the IPR readme.txt file
	 *
	 * @since 0.9.5
	 * @return string $readme HTML formatted readme file
	 */
	public function parse_readme() {
		$file = file_exists( INTERCESSOR_DIR . 'readme.txt' ) ? INTERCESSOR_DIR . 'readme.txt' : null;

		if ( ! $file ) {
			$readme = '<p>' . esc_html__( 'No valid changlog was found.', 'intercessor' ) . '</p>';
		} else {
			$readme = file_get_contents( $file );
			$readme = nl2br( esc_html( $readme ) );
			$readme = explode( '== Changelog ==', $readme );
			$readme = end( $readme );

			$readme = preg_replace( '/`(.*?)`/', '<code>\\1</code>', $readme );
			$readme = preg_replace( '/[\040]\*\*(.*?)\*\*/', ' <strong>\\1</strong>', $readme );
			$readme = preg_replace( '/[\040]\*(.*?)\*/', ' <em>\\1</em>', $readme );
			$readme = preg_replace( '/= (.*?) =/', '<h4>\\1</h4>', $readme );
			$readme = preg_replace( '/\[(.*?)\]\((.*?)\)/', '<a href="\\2">\\1</a>', $readme );
		}

		return $readme;
	}

	/**
	 * Enqueue Welcome Screen Style
	 *
	 * @access public
	 * @since  0.9.5
	 *
	 * @param $screen
	 *
	 * @return void
	 */
	public function intercessor_welcome_style( $screen ) {		
		// Access the global variable with the saved screen.
		global $intercessor_welcome_screen;
		
		// Add style to the welcome page only.
		if ( $screen !== $intercessor_welcome_screen ) {
			return;
		}
		
		wp_register_style( 'intercessor-welcome', INTERCESSOR_URL . 'assets/css/intercessor-setup.css', [], INTERCESSOR_VERSION, 'all' );
		wp_enqueue_style( 'intercessor-welcome' );
	}


	/**
	 * Render Contributors List
	 *
	 * @since 0.9.5
	 * @uses  \Intercessor\Admin\Admin_Setup::get_contributors()
	 * @return string $contributor_list HTML formatted list of all the contributors for IPR
	 */
	public function contributors() {
		$contributors = $this->get_contributors();

		if ( empty( $contributors ) ) {
			return '';
		}

		$contributor_list = '<ul class="wp-people-group">';

		foreach ( $contributors as $contributor ) {
			$contributor_list .= '<li class="contributor">';
			$contributor_list .= sprintf(
				'<a href="%1$s" target="_blank"><img src="%2$s" width="64" height="64" class="gravatar" alt="%3$s" /></a>',
				esc_url( 'https://github.com/' . $contributor->login ),
				esc_url( $contributor->avatar_url ),
				esc_attr( $contributor->login )
			);
			$contributor_list .= sprintf(
				'<a class="web" target="_blank" href="%1$s">%2$s</a>',
				esc_url( 'https://github.com/' . $contributor->login ),
				esc_html( $contributor->login )
			);
			$contributor_list .= '</li>';
		}

		$contributor_list .= '</ul>';

		return $contributor_list;
	}

	/**
	 * Retreive list of contributors from GitHub.
	 *
	 * @access public
	 * @since  0.9.5
	 * @return array $contributors List of contributors
	 */
	public function get_contributors() {
		$contributors = IPR_Cache::get( 'intercessor_contributors', true );

		if ( false !== $contributors ) {
			return $contributors;
		}

		$response = wp_remote_get( 'https://api.github.com/repos/aictoraigbeghian/intercessor/contributors', [ 'sslverify' => false ] );

		if ( is_wp_error( $response ) || 200 != wp_remote_retrieve_response_code( $response ) ) {
			return [];
		}

		$contributors = json_decode( wp_remote_retrieve_body( $response ) );

		if ( ! is_array( $contributors ) ) {
			return [];
		}

		IPR_Cache::set( 'intercessor_contributors', $contributors, HOUR_IN_SECONDS, true );

		return $contributors;
	}

	/**
	 * The header section for the welcome screen.
	 *
	 * @since 0.9.5
	 */
	public function get_welcome_header() {
		// Logo for welcome page
		$logo_url = INTERCESSOR_URL . 'assets/images/prayers.png';
		?>
        <h1 class="welcome-h1"><?php echo \get_admin_page_title(); ?></h1>
		<style type="text/css" media="screen">
            /*<![CDATA[*/
            .intercessor-badge {
                background: url('<?php echo $logo_url; ?>') no-repeat;
            }

            /*]]>*/
        </style>
	<?php 
	}

	/**
	 * Redirects user to the Welcome page on first activation of Intercessor 
	 *
	 * Also redirects whenever Intercessor  is upgraded to a new version.
	 *
	 * @access public
	 * @since  0.9.5
	 * @return void
	 */
	public function welcome() {

		// Bail if no activation redirect
		if ( ! \get_transient( '_intercessor_redirect_activation' ) ) {
			return;
		}

		// Delete the redirect transient
		\delete_transient( '_intercessor_redirect_activation' );

		// Bail if activating from network, or bulk
		if ( \is_network_admin() || isset( $_GET['activate-multi'] ) ) {
			return;
		}

		$upgrade = \get_option( 'intercessor_version_upgraded_from' );

		if ( ! $upgrade ) {
			// First time instal.
			\wp_safe_redirect( admin_url( 'index.php?page=intercessor-about' ) );
			exit;
		} elseif ( intercessor_get_option( 'disable_welcome_screen' ) ) {
			 // Welcome is disabled in settings.

		} else {
			// Welcome is NOT disabled in settings
			\wp_safe_redirect( admin_url( 'index.php?page=intercessor-about' ) );
			exit;
		}

		// Dequeue the welcome style.
		\wp_dequeue_style( 'intercessor-welcome' );
	}
}