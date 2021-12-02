<?php
/**
 * Intercessor Shortcodes
 *
 * @package     Intercessor
 * @subpackage  Classes/Shortcodes
 * @copyright   Copyright (c) 2019, Victor Aigbeghian
 * @license     http://opensource.org/licenses/GPL-3.0php GNU Public License
 * @since       0.9.5
 */

namespace Intercessor;

use function intercessor_get_option;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
/**
 * Intercessor Shortcodes class.
 *
 * @since 0.9.5
 */
class Shortcodes {

	/**
	 * Instance of this class.
	 *
	 * @since    0.9.5
	 *
	 * @var object Instance of this class.
	 */
	protected static $instance = null;

	/**
	 * Form action.
	 *
	 * @access protected
	 * @var string
	 */
	protected $action = '';

	/**
	 * Frontend Message.
	 *
	 * @access public
	 * @var string
	 */
	public $frontend_message = '';

	/**
	 * Initialize the class.
	 *
	 * @since    0.9.5
	 */
	public function __construct() {
		add_shortcode( 'intercessor_form', [ $this, 'output_prayer_form' ] );
		add_shortcode( 'intercessor_prayers', [ $this, 'output_prayer_requests' ] );
		add_shortcode( 'intercessor_history', [ $this, 'output_prayer_history' ] );
		add_shortcode( 'intercessor_login', [ $this, 'output_intercessor_login' ] );
		add_shortcode( 'intercessor_register', [ $this, 'output_register_form' ] );

	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     0.9.5
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Output prayer request form.
	 *
	 * @param array $atts Shortcodes attributes.
	 *
	 * @since 0.9.5
	 * @return string
	 */
	public function output_prayer_form( $atts ) {
		$page_id  = intercessor_get_option( 'form_page' );
		$form_url = esc_url( get_permalink( $page_id ) );
		
		$atts = shortcode_atts(
			[
				'redirect' => $form_url,
				'button'   => '',
			],
			$atts,
			'intercessor_form'
		);

        $form = Form::get_instance();

        return $form->output( $atts );
	}

	/**
	 * Prayer Listing Shortcode
	 *
	 * @since 0.9.5
	 *
	 * @param array $atts    Shortcode arguments.
	 * @param null  $content Content.
	 *
	 * @return string
	 */
	public function output_prayer_requests( $atts, $content = null ) {

		$number   = intercessor_get_option( 'prayer_number', 10 );
		$current  = \intercessor_get_current_page_number();
		$display  = intercessor_get_option( 'prayer_display_period', '90' );

		// Arguments for query.
		$atts = shortcode_atts(
			[
				'ids'        => '',
				'number'     => $number,
				'order'      => 'DESC',
				'orderby'    => 'id',
				'pagination' => 'true',
				'status'     => 'active',
				'offset'     => $number * ( $current - 1 ),
				'display'    => $display,
			],
			$atts,
			'intercessor_prayers'
		);

		if ( 'random' === $atts['orderby'] ) {
			$atts['pagination'] = false;
		}

		switch ( $atts['orderby'] ) {
			case 'title':
				$atts['orderby'] = 'title';
				break;

			case 'date':
				$atts['orderby'] = 'date_created';
				break;

			case 'random':
				$atts['orderby'] = 'rand';
				break;

			case 'prayer_in':
				$atts['orderby'] = 'id__in';
				break;

			default:
				$atts['orderby'] = 'id';
				break;
		}

		if ( ! empty( $atts['ids'] ) ) {
			$atts['id__in'] = explode( ',', $atts['ids'] );
		}

		// Parse args.
		$args = wp_parse_args( $atts );

		// Get prayers.
		$prayers = intercessor_get_items( 'prayer', $args );

		/**
		 * Fires before the prayer requests list
		 *
		 * @since 0.9.5
		 */
		do_action( 'intercessor_prayers_list_before', $atts );

		if ( ! empty( $content ) ) {
			echo do_shortcode( $content );
		}

		$main_title = intercessor_get_option( 'prayer_list_title' );
		$sub_title  = intercessor_get_option( 'prayer_list_message' );

		if ( ! isset( $sub_title ) ) {
			$subtitle = esc_html__( 'Pray for the request below and click on the prayed for button', 'intercessor' );
		} else {
			$subtitle = $sub_title;
		}

		$prayers_list = '<div class="intercessor">';

		if ( intercessor_get_option( 'format_header' ) ) {
			$prayers_list .= '<h3>' . $main_title . '</h3>';
		}

		// Prayers pagination.
		$pagination = false;
		if ( filter_var( $atts['pagination'], FILTER_VALIDATE_BOOLEAN ) ) {

			$big          = 999999;
			$search_for   = [ $big, '#038;' ];
			$replace_with = [ '%#%', '&' ];
			$prayer_count = \intercessor_count_prayers( $atts['status'] );
			$total        = absint( ceil( $prayer_count / $number ) );
			$pagination   = paginate_links(
				apply_filters(
					'intercessor_request_pagination_args',
					[
						'base'    => str_replace( $search_for, $replace_with, get_pagenum_link( $big ) ),
						'format'  => '?paged=%#%',
						'current' => $current,
						'total'   => $total,
					],
					$atts,
					$prayers,
					$args
				)
			);

			// Check if top pagination is activated in settings.
			$top_pagination = intercessor_get_option( 'top_pagination', 0 );

			if ( ! empty( $pagination ) && '1' === $top_pagination ) {
				$prayers_list .= '<div class="intercessor-pagination">' . $pagination . '</div>';
			}
		}

		if ( ! empty( $prayers ) ) {

			if ( intercessor_get_option( 'format_header' ) ) {
				$prayers_list .= '<h5>' . $subtitle . '</h5>';
			}

			foreach ( $prayers as $prayer ) {
				// Generate data to display prayer requests.
				$prayer_id     = absint( $prayer->id );
				$message       = stripslashes( $prayer->message );
				$title         = stripslashes( $prayer->title );
				$prayer_number = \intercessor_get_prayer_number( $prayer_id );
				$name          = intercessor_get_prayer_name( $prayer_id );
				$date          = esc_attr( $prayer->date_created );
				$gmt_offset    = get_option( 'gmt_offset' );
				$prayer_date   = \intercessor_time_ago( $date, $gmt_offset );
				$counts        = \intercessor_get_prayed_for_counts( $prayer_id );
				$answered      = \intercessor_is_answered_prayer( $prayer_id );
				$praises       = intercessor_get_item_meta( 'prayer', $prayer_id, 'praise_report', false );

				$received = sprintf(
					/* translators: %s: date prayer was activated */
					esc_html__( 'Received %s', 'intercessor' ),
					$prayer_date
				);

				$submitted = sprintf(
					/* translators: %s: name of requester who submitted prayer request. */
					__( 'Submitted by: %s', 'intercessor' ),
					$name
				);
				
				// Setup praise report label.
				if ( ! empty( $praises ) ) {
					$answered_msg = esc_html( $praises );
				} else {
					$answered_msg = esc_html__( 'This prayer request has been answered! Thanks for praying.', 'intercessor' );
				}

				ob_start();

				/**
				 * Fires before the prayer list shortcode
				 *
				 * @param array $atts Attributes array.
				 *
				 * @since 0.9.5
				 */
				do_action( 'intercessor_prayer_shortcode_item', $atts );

				$prayers_list .= '<div class="intercessor-prayers-list">';
				$prayers_list .= '<div class="prayers" id="' . $prayer_id . '">';
				$prayers_list .= '<h4 class="prayer-title">' . esc_attr( $prayer_number ) . '  ' . esc_attr( $title ) . '</h4>';
				$prayers_list .= '<div class="intercessor-requester">';
				$prayers_list .= '<div id="requester_name">' . $submitted . '</div>';
				$prayers_list .= '</div>';
				$prayers_list .= '<div class="prayer-list-counter" id="ipr_counter' . $prayer_id . '">';
				$prayers_list .= '<form id="intercessor_update_counts" action="" name="" method="post">';
				$prayers_list .= '<div id="intercessor_praying"></div>';
				$prayers_list .= '<input name="intercessor_prayed_count" value="' . $counts . '" type="hidden"/>';
				$prayers_list .= \intercessor_get_prayer_button( $prayer_id );
				$prayers_list .= '</form>';

				// Add prayer counter if activated in plugin settings.
				if ( '1' === intercessor_get_option( 'enable_prayer_count' ) ) {
					$prayers_list .= '<div class="prayed-for">';
					$prayers_list .= esc_html__( 'Prayed for ', 'intercessor' );
					$prayers_list .= '<span id="prayed_counts_' . $prayer_id . '">' . $counts . '</span>';
					$prayers_list .= esc_html__( ' times', 'intercessor' );
					$prayers_list .= '</div>';
				}

				$prayers_list .= '</div>';
				$prayers_list .= '<div class="prayer-message">' . stripslashes( $message ) . '</div>';
				$prayers_list .= '<div class="intercessor-received-date">' . $received . '</div>';

				if ( $answered ) {
					$prayers_list .= '<div class="intercessor-answered">' . $answered_msg . '</div>';
				}

				/**
				 * Fires at the bottom of the prayer list table
				 *
				 * @param $atts
				 *
				 * @since 0.9.5
				 */
				do_action( 'intercessor_reqeust_list_bottom', $atts );

				$prayers_list .= '</div>';

				$prayers_list .= '</div>';

			}

			// Prayers pagination.
			if ( filter_var( $atts['pagination'], FILTER_VALIDATE_BOOLEAN ) ) {

				if ( ! empty( $pagination ) ) {
					$prayers_list .= '<div class="intercessor-pagination">' . $pagination . '</div>';
				}
			}

			ob_end_clean();

		} else {
			$prayers_list .= '<div class="intercessor_prayer_list">' . __( 'No prayer request found', 'intercessor' ) . '</div>';

		}

		$prayers_list .= '</div>';

		return $prayers_list;

	}

	/**
	 * Display Prayer History For a User.
	 *
	 * @param array $atts    Shortcode attributes.
	 * @param bool  $content Shortcode content.
	 *
	 * @since  0.9.5
	 * @return string
	 */
	public function output_prayer_history( $atts, $content = false ) {

		$prayer_history_args = shortcode_atts(
			[
				'id'        => true,
				'requester' => false,
				'date'      => true,
				'status'    => false,
				'number'    => true,
				'details'   => true,
			],
			$atts,
			'intercessor_history'
		);


		ob_start();

		// User is logged in, session exist or a valid email access token exist.
		if ( is_user_logged_in() ) {

			$redirect = intercessor_get_history_page_uri();

			if ( ! empty( $content ) ) {
				echo do_shortcode( $content );
			}

			$history = new Prayer_History();
			return $history->get_history( $redirect );

		} else {
			// User must login or register to view prayer history.
			echo '<h2>' . esc_html__( 'You must be logged in to view your prayer request history. Please login using your account or create an account using the same email you used to submit prayer request.', 'intercessor' ) . '</h2>';
			echo do_shortcode( '[intercessor_register]' );
		}

		/**
		 * Filter to modify prayer request history HTMl
		 *
		 * @param string          HTML content.
		 * @param array  $atts    Arguments array.
		 * @param string $content Content passed between enclose content.
		 *
		 * @since 0.9.5
		 *
		 * @return string HTML content
		 */
		return apply_filters( 'intercessor_history_shortcode_html', ob_get_clean(), $atts, $content );
	}

	/**
	 * Login Shortcode.
	 *
	 * @param array  $atts    Shortcode attributes.
	 * @param string $content Shortcode content.
	 *
	 * @since  0.9.5
	 * @uses   intercessor_login_form()
	 * @return string
	 */
	public function output_intercessor_login( $atts, $content = null ) {
		$redirect = '';

		extract(
			shortcode_atts(
				[
					'redirect' => $redirect,
				],
				$atts,
				'intercessor_login'
			)
		);

		// Redirect to history page.
		if ( empty( $redirect ) ) {
			$prayer_history = intercessor_get_option( 'history_page', 0 );

			if ( ! empty( $prayer_history ) ) {
				$redirect = esc_url( get_permalink( $prayer_history ) );
			}
		}

		if ( empty( $redirect ) ) {
			$redirect = esc_url( home_url() );
		}

		if ( ! empty( $content ) ) {
			echo do_shortcode( $content );
		}

		return \intercessor_login_form( $redirect );
	}

	/**
	 * Register Form Shortcode
	 *
	 * Shows a registration form allowing users to register for the site.
	 *
	 * @param  array  $atts    Short-code attributes.
	 * @param  string $content Short-code content.
	 *
	 * @since  0.9.5
	 * @uses   \intercessor_register_form()
	 * @return string
	 */
	public function output_register_form( $atts, $content = null ) {
		$redirect       = home_url();
		$prayer_history = intercessor_get_option( 'history_page' );

		if ( ! empty( $prayer_history ) ) {
			$redirect = esc_url( get_permalink( $prayer_history ) );
		}

		extract(
			shortcode_atts(
				[
					'redirect' => $redirect,
				],
				$atts,
				'intercessor_register'
			)
		);

		if ( ! empty( $content ) ) {
			echo do_shortcode( $content );
		}

		return \intercessor_register_form( $redirect );
	}

}
