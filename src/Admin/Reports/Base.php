<?php
/**
 * Intercessor Reports Graph.
 *
 * @package     Intercessor
 * @subpackage  Reports/Graph
 * @copyright   Copyright (c) 2019, Victor Aigbeghian
 * @license     http://opensource.org/licenses/gpl-0.9.5.php GNU Public License
 * @since       0.9.5
 */

namespace Intercessor\Admin\Reports;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Class Graph
 *
 * @package Intercessor\Admin\Reports
 *
 * @since 0.9.5
 */
class Base {

	/**
	 * Unique ID for the graph
	 *
	 * @var string
	 * @since 0.9.5
	 */
	public $id = '';

	/**
	 * Data to graph
	 *
	 * @var array
	 * @since 0.9.5
	 */
	public $data;

	/**
	 * Graph options
	 *
	 * @var array
	 * @since 0.9.5
	 */
	public $options = [];

	/**
	 * Get things started
	 *
	 * @param array $_data Array of data.
	 *
	 * @since 0.9.5
	 */
	public function __construct( array $_data = [] ) {

		$this->data = $_data;

		// Generate unique ID.
		$this->id = md5( wp_rand() );

		// Setup default options.
		$this->options = [
			'y_mode'          => null,
			'x_mode'          => null,
			'y_decimals'      => 0,
			'x_decimals'      => 0,
			'y_position'      => 'right',
			'time_format'     => '%d/%b',
			'ticksize_unit'   => 'day',
			'ticksize_num'    => 1,
			'multiple_y_axes' => false,
			'bgcolor'         => '#f9f9f9',
			'bordercolor'     => '#ccc',
			'color'           => '#bbb',
			'borderwidth'     => 2,
			'bars'            => false,
			'lines'           => true,
			'points'          => true,
			'currency'        => true,
		];

	}

	/**
	 * Set an option
	 *
	 * @param string $key   The option key to set.
	 * @param string $value The value to assign to the key.
	 *
     * @access public
	 * @since 0.9.5
	 */
	public function set( string $key, string $value ) {
        $this->options[ $key ] = $value;
	}

	/**
	 * Get graph data
	 *
	 * @since 0.9.5
	 */
	public function get_data() {
		return apply_filters( 'intercessor_get_graph_data', $this->data, $this );
	}

	/**
	 * Get an option.
	 *
	 * @param string $key The option key to get.
	 *
	 * @return bool|mixed
	 * @since 0.9.5
	 */
	public function get( string $key ) {
		return $this->options[ $key ] ?? false;
	}

	/**
	 * Load the graphing library script
	 *
	 * @since 0.9.5
	 */
	public function load_scripts() {
		// Use minified libraries if SCRIPT_DEBUG is turned off.
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
		wp_enqueue_script( 'jquery-flot', INTERCESSOR_URL . 'assets/js/jquery.flot' . $suffix . '.js' );
		wp_enqueue_script( 'jquery-flot-resize', INTERCESSOR_URL . 'assets/js/jquery.flot.resize' . $suffix . '.js' );
		wp_enqueue_style( 'intercessor-reports' );
	}

	/**
	 * Build the graph and return it as a string
	 *
	 * @var array
	 * @since 0.9.5
	 * @return string
	 */
	public function build_graph() {

		$yaxis_count = 1;

		$this->load_scripts();

		ob_start();

		?>
		<script type="text/javascript">
            var intercessor_vars;
            jQuery( document ).ready( function($) {
                $.plot(
                    $("#intercessor-graph-<?php echo $this->id; ?>"),
                    [
						<?php foreach( $this->get_data() as $label => $data ) : ?>
                        {
                            label: "<?php echo esc_attr( $label ); ?>",
                            id: "<?php echo sanitize_key( $label ); ?>",
                            // data format is: [ point on x, value on y ]
                            data: [<?php foreach( $data as $point ) { echo '[' . implode( ',', $point ) . '],'; } ?>],
                            points: {
                                show: <?php echo $this->options['points'] ? 'true' : 'false'; ?>,
                            },
                            bars: {
                                show: <?php echo $this->options['bars'] ? 'true' : 'false'; ?>,
                                barWidth: 12,
                                align: 'center'
                            },
                            lines: {
                                show: <?php echo $this->options['lines'] ? 'true' : 'false'; ?>
                            },
							<?php if ( $this->options['multiple_y_axes'] ) : ?>
                            yaxis: <?php echo $yaxis_count; ?>
							<?php endif; ?>
                        },
						<?php $yaxis_count++; endforeach; ?>
                    ],
                    {
                        // Options.
                        grid: {
                            show: true,
                            aboveData: false,
                            color: "<?php echo $this->options[ 'color' ]; ?>",
                            backgroundColor: "<?php echo $this->options[ 'bgcolor' ]; ?>",
                            borderColor: "<?php echo $this->options[ 'bordercolor' ]; ?>",
                            borderWidth: <?php echo absint( $this->options[ 'borderwidth' ] ); ?>,
                            clickable: false,
                            hoverable: true
                        },
                        xaxis: {
                            mode: "<?php echo $this->options['x_mode']; ?>",
                            timeFormat: "<?php echo $this->options['x_mode'] == 'time' ? $this->options['time_format'] : ''; ?>",
                            tickSize: "<?php echo $this->options['x_mode'] == 'time' ? '' : 1; ?>",
							<?php if ( $this->options['x_mode'] != 'time' ) : ?>
                            tickDecimals: <?php echo $this->options['x_decimals']; ?>
							<?php endif; ?>
                        },
                        yaxis: {
                            position: 'right',
                            min: 0,
                            mode: "<?php echo $this->options['y_mode']; ?>",
                            timeFormat: "<?php echo $this->options['y_mode'] == 'time' ? $this->options['time_format'] : ''; ?>",
							<?php if ( $this->options['y_mode'] != 'time' ) : ?>
                            tickDecimals: <?php echo $this->options['y_decimals']; ?>
							<?php endif; ?>
                        }
                    }

                );

                function intercessor_flot_tooltip(x, y, contents) {
                    $('<div id="intercessor-flot-tooltip">' + contents + '</div>').css( {
                        position: 'absolute',
                        display: 'none',
                        top: y + 5,
                        left: x + 5,
                        border: '1px solid #fdd',
                        padding: '2px',
                        'background-color': '#fee',
                        opacity: 0.80,
                        zIndex: 3,
                    }).appendTo("body").fadeIn(200);
                }

                var previousPoint = null;
                $("#intercessor-graph-<?php echo $this->id; ?>").bind("plothover", function (event, pos, item) {
                    $("#x").text(pos.x.toFixed(2));
                    $("#y").text(pos.y.toFixed(2));
                    if (item) {
                        if (previousPoint != item.dataIndex) {
                            previousPoint = item.dataIndex;
                            $("#intercessor-flot-tooltip").remove();
                            var x = item.datapoint[0].toFixed(2),
                                y = item.datapoint[1].toFixed(2);

							<?php if ( $this->get( 'currency' ) ) : ?>
                            if ( intercessor_vars.currency_pos == 'before' ) {
                                intercessor_flot_tooltip( item.pageX, item.pageY, item.series.label + ' ' + intercessor_vars.currency_sign + y );
                            } else {
                                intercessor_flot_tooltip( item.pageX, item.pageY, item.series.label + ' ' + y + intercessor_vars.currency_sign );
                            }
							<?php else : ?>
                            intercessor_flot_tooltip( item.pageX, item.pageY, item.series.label + ' ' + y );
							<?php endif; ?>
                        }
                    } else {
                        $("#intercessor-flot-tooltip").remove();
                        previousPoint = null;
                    }
                });

                $( '#intercessor-graphs-date-options' ).change( function() {
                    var $this = $(this);
                    if ( $this.val() == 'other' ) {
                        $( '#intercessor-date-range-options' ).css('display', 'inline-block');
                    } else {
                        $( '#intercessor-date-range-options' ).hide();
                    }
                });

            });
		</script>
		<?php echo $this->graph_controls(); ?>
		<div id="intercessor-graph-<?php echo $this->id; ?>" class="intercessor-graph" style="height: 300px; width:100%;"></div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Output the final graph
	 *
	 * @since 0.9.5
	 */
	public function display() {
		/**
		 * Fires before the graph is displayed.
		 *
		 * @param object $this This object.
		 * 
		 * @since 1.1.0
		 */
		do_action( 'intercessor_before_graph', $this );
		
		// Display the graph.
		echo $this->build_graph();

		/**
		 * Fires after the graph is displayed.
		 *
		 * @param object $this This object.
		 * 
		 * @since 1.1.0
		 */
		do_action( 'intercessor_after_graph', $this );
	}

	/**
	 * Show report graph date filters
	 *
	 * @return void
	 * @since 0.9.5
	 */
	function graph_controls() {
		$date_options = apply_filters(
			'intercessor_report_date_options',
			[
				'today' 	    => esc_html__( 'Today', 'intercessor' ),
				'yesterday'     => esc_html__( 'Yesterday', 'intercessor' ),
				'this_week' 	=> esc_html__( 'This Week', 'intercessor' ),
				'last_week' 	=> esc_html__( 'Last Week', 'intercessor' ),
				'this_month' 	=> esc_html__( 'This Month', 'intercessor' ),
				'last_month' 	=> esc_html__( 'Last Month', 'intercessor' ),
				'this_quarter'	=> esc_html__( 'This Quarter', 'intercessor' ),
				'last_quarter'	=> esc_html__( 'Last Quarter', 'intercessor' ),
				'this_year'		=> esc_html__( 'This Year', 'intercessor' ),
				'last_year'		=> esc_html__( 'Last Year', 'intercessor' ),
				'other'			=> esc_html__( 'Custom', 'intercessor' ),
			]
		);

		// Set up variables.
		$dates        = intercessor_get_report_dates();
		$display      = $dates['range'] === 'other' ? 'style="display:inline-block;"' : 'style="display:none;"';
		$current_time = current_time( 'timestamp' );

		?>
		<form id="intercessor-graphs-filter" method="get">
			<div class="tablenav top">

				<?php if ( is_admin() ) : ?>
					<?php $tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'prayers'; ?>
					<?php $page = isset( $_GET['page'] ) ? $_GET['page'] : 'intercessor'; ?>
					<input type="hidden" name="page" value="<?php echo esc_attr( $page ); ?>"/>
				<?php else: ?>
					<?php $tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'graphs'; ?>
					<input type="hidden" name="page_id" value="<?php echo esc_attr( get_the_ID() ); ?>"/>
				<?php endif; ?>

				<input type="hidden" name="tab" value="<?php echo esc_attr( $tab ); ?>"/>

				<?php if ( isset( $_GET['prayer_id'] ) ) : ?>
					<input type="hidden" name="prayer_id" value="<?php echo absint( $_GET['prayer_id'] ); ?>"/>
					<input type="hidden" name="action" value="view_prayer"/>
				<?php endif; ?>

				<select id="intercessor-graphs-date-options" name="range">
					<?php
					foreach ( $date_options as $key => $option ) {
						echo '<option value="' . esc_attr( $key ) . '" ' . selected( $key, $dates['range'] ) . '>' . esc_html( $option ) . '</option>';
					}
					?>
				</select>

				<div id="intercessor-date-range-options" <?php echo $display; ?>>
					<span><?php _e( 'From', 'intercessor' ); ?>&nbsp;</span>
					<select id="intercessor-graphs-month-start" name="m_start">
						<?php for ( $i = 1; $i <= 12; $i++ ) : ?>
							<option value="<?php echo absint( $i ); ?>" <?php selected( $i, $dates['m_start'] ); ?>><?php echo intercessor_month_num_to_name( $i ); ?></option>
						<?php endfor; ?>
					</select>
					<select id="intercessor-graphs-year" name="year_start">
						<?php for ( $i = 2019; $i <= date( 'Y', $current_time ); $i++ ) : ?>
							<option value="<?php echo absint( $i ); ?>" <?php selected( $i, $dates['year'] ); ?>><?php echo $i; ?></option>
						<?php endfor; ?>
					</select>
					<span><?php _e( 'To', 'intercessor' ); ?>&nbsp;</span>
					<select id="intercessor-graphs-month-start" name="m_end">
						<?php for ( $i = 1; $i <= 12; $i++ ) : ?>
							<option value="<?php echo absint( $i ); ?>" <?php selected( $i, $dates['m_end'] ); ?>><?php echo intercessor_month_num_to_name( $i ); ?></option>
						<?php endfor; ?>
					</select>
					<select id="intercessor-graphs-year" name="year_end">
						<?php for ( $i = 2019; $i <= date( 'Y', $current_time ); $i++ ) : ?>
							<option value="<?php echo absint( $i ); ?>" <?php selected( $i, $dates['year_end'] ); ?>><?php echo $i; ?></option>
						<?php endfor; ?>
					</select>
				</div>

				<input type="submit" class="button" value="<?php _e( 'Filter', 'intercessor' ); ?>"/>
			</div>
		</form>
		<?php
	}

}
