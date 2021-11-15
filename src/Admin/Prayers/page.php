<?php
/**
 * Prayer Requests
 *
 * @package     Intercessor
 * @subpackage  Admin/Prayers
 * @copyright   Copyright (c) 2018, Victor Aigbeghian
 * @license     http://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since       0.9.5
 */

// Exit if accessed directly.
use Intercessor\Admin\Prayers\Table;

defined( 'ABSPATH' ) || exit;

//require_once INTERCESSOR_DIR . 'src/Admin/Prayers/Requests_Table.php';

/**
 * Renders the Prayer Pages Admin Page
 *
 * @since 0.9.5
 * @return void
 */
function intercessor_prayers_page() {
	if ( isset( $_GET['intercessor-action'] ) && 'edit_prayer' === $_GET['intercessor-action'] ) {
		require_once INTERCESSOR_DIR . 'src/Admin/Prayers/edit-prayer.php';
	} elseif ( isset( $_GET['intercessor-action'] ) && 'add_prayer' === $_GET['intercessor-action'] ) {
		require_once INTERCESSOR_DIR . 'src/Admin/Prayers/add-prayer.php';
	} elseif ( isset( $_GET['intercessor-action'] ) && 'view_request_details' === $_GET['intercessor-action'] ) {
	    wp_enqueue_script( 'intercessor-prayers' );
		require_once INTERCESSOR_DIR . 'src/Admin/Prayers/view-details.php';
	} else {
		$prayers_table = new Table();
		$prayers_table->prepare_items();
		?>
	<div class="wrap">
		<h1 class="wp-heading-inline">
			<?php esc_html_e( 'Intercessory Prayer Requests ', 'intercessor' ); ?>
			<a href="<?php echo esc_url( add_query_arg( array( 'intercessor-action' => 'add_prayer' ) ) ); ?>" class="add-new-h2">
				<?php esc_html_e( 'Add New', 'intercessor' ); ?>
			</a>
		</h1>

		<?php
		/**
		 * Fires at the top of the prayer request list screen page.
		 *
		 * @since  0.9.5
		 */
		do_action( 'intercessor_prayers_page_top' ); 
		?>

		<hr class="wp-header-end">

		<form id="intercessor-prayers-advanced-filter" method="get" action="<?php echo esc_url( admin_url( 'admin.php?page=intercessor-prayers' ) ); ?>">

			<input type="hidden" name="page" value="intercessor-prayers" />
			<input type="hidden" name="prayers" value="prayers" />
			<?php $prayers_table->views(); ?>
			<?php $prayers_table->advanced_filters(); ?>
		</form>		

		<form id="intercessor-prayers-filter" method="get" action="<?php echo esc_url( admin_url( 'admin.php?page=intercessor-prayers' ) ); ?>">
			<input type="hidden" name="page" value="intercessor-prayers" />
			<input type="hidden" name="prayers" value="prayers" />
			<?php $prayers_table->display(); ?>
		</form>

		<?php
		/**
		 * Fires at the bottom of the prayer request list screen page.
		 *
		 * @since  0.9.5
		 */
		do_action( 'intercessor_prayers_page_bottom' );
		?>
	</div>
<?php
	}
}

/**
 * Add Screen options
 *
 * @since  0.9.5
 * @return void
 */
function intercessor_add_prayers_screen_options() {
	$screen = intercessor_get_admin_current_screen();

	if ( 'intercessor-prayers' !== $screen ) {
		return;
	}	

	$option = 'per_page';
	$args   = array(
		'label'   => esc_html__( 'Number of prayer requests per page', 'prayer-house' ),
		'option'  => 'prayers_per_page',
		'default' => 20,
	);

	add_screen_option( $option, $args );

	if ( empty( $_REQUEST['action'] )
		|| ( ! empty( $_REQUEST['action'] ) && 'view_prayer' !== $_REQUEST['action'] )
	) {
		new Table();
	}

	/**
	 * Fires in the iPrayers screen options area.
	 *
	 * @param string $screen The current screen.
	 */
	do_action( 'intercessor_admin_screen_options', $screen );
}

/**
 * Per page screen option value for the Affiliates list table
 *
 * @param  bool|int $status Status.
 * @param  string   $option Option.
 * @param  mixed    $value  Value.
 *
 * @since  0.9.5
 * @return mixed
 */
function intercessor_set_screen_option( $status, $option, $value ) {

	if ( 'prayers_per_page' === $option ) {
		return $value;
	}

	return $status;

}
add_filter( 'set-screen-option', 'intercessor_set_screen_option', 10, 3 );

/**
 * Adds the Contextual Help for the Prayer Requests Page
 *
 * @since 0.9.5
 * @return void
 */
function intercessor_prayers_contextual_help() {
	$screen = get_current_screen();

	$screen->set_help_sidebar(
		'<p><strong>' . sprintf(
			__( 'For more information:', 'intercessor' ) . '</strong></p>' .
		'<p>' . sprintf(
			__( 'Visit the <a href="%s">documentation</a> on the iPrayers website.', 'intercessor' ),
			esc_url( 'http://docs.prayerhousewp.com/' ) )
		) . '</p>' .
		'<p>' . sprintf(
			__( '<a href="%s">Post an issue</a> on <a href="%s">GitHub</a>.', 'intercessor' ),
			esc_url( 'https://github.com/victoraigbeghian/intercessor/issues' ),
			esc_url( 'https://github.com/victoraigbeghian/intercessor' )
		) . '</p>'
	);

	$screen->add_help_tab(
		array(
			'id'      => 'intercessor-prayer-general',
			'title'	  => esc_html__( 'General', 'intercessor' ),
			'content' =>
				'<p>' . esc_html__( 'Prayer requests can have pending, active, archived or private status.', 'intercessor' ) . '</p>' .
				'<p>' . esc_html__( 'Prayer request can be personal and can only be viewed by the user who submit it and the admin.', 'intercessor' ) . '</p>' 
		)
	);

	$screen->add_help_tab(
		array(
			'id'      => 'intercessor-prayer-add',
			'title'   => esc_html__( 'Adding Prayers', 'intercessor' ),
			'content' =>
				'<p>' . esc_html__( 'You can create any number of prayer requests easily from this page.', 'intercessor' ) . '</p>' .
				'<p>' . esc_html__( 'Prayer requests have several options:', 'intercessor' ) . '</p>' .
				'<ul>' .
					'<li><strong>' . esc_html__( 'First Name - ', 'intercessor' ) . '</strong>' . esc_html__( 'this is the first name of the person submitting the prayer request.', 'intercessor' ) . '</li>' .
					'<li><strong>' . esc_html__( 'Last Name - ', 'intercessor' ) . '</strong>' . esc_html__( 'this is the last name of the person submitting the prayer request.', 'intercessor' ) . '</li>' .
					'<li><strong>' . esc_html__( 'Email - ', 'intercessor' ) . '</strong>' . esc_html__( ' this is the email of the person submitting the prayer request', 'intercessor' ) . '</li>' .
					'<li><strong>' . esc_html__( 'Title - ', 'intercessor' ) . '</strong>' . esc_html__( 'this is the prayer request title..', 'intercessor' ) . '</li>' .
					'<li><strong>' . esc_html__( 'Message - ', 'intercessor' ) . '</strong>' . esc_html__( 'this is the prayer request body or message.', 'intercessor' ) . '</li>' .
					'<li><strong>' . esc_html__( 'Share -  ', 'intercessor' ) . '</strong>' . esc_html__( 'choose how this prayer request should be shared', 'intercessor' ) . '</li>' .
					'<li><strong>' . esc_html__( 'Status - ', 'intercessor' ) . '</strong>' . esc_html__( 'specify the status of this prayer.', 'intercessor' ) . '</li>' .
					'<li><strong>' . esc_html__( 'Notify - ', 'intercessor' ) . '</strong>' . esc_html__( 'if checked the requester will be notified any day you are prayed for.', 'intercessor' ) . '</li>' .				
				'</ul>',
		)
	);

	do_action( 'intercessor_prayers_contextual_help', $screen );
}
