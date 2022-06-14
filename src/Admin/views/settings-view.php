<?php
/**
 * Intercessor Settings Display
 *
 * @package     Intercessor
 * @subpackage  Admin/Settings
 * @copyright   Copyright (c) 2022, Victor Aigbeghian
 * @license     https://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since       1.1.0
 */

// Bail if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Display admin settings view.
 *
 * @param array  $settings_tabs The Settings tabs.
 * @param string $active_tab    Active tab.
 * @param array  $sections      Settings sections.
 * @param array  $section       Single settings section.
 * @param bool   $override      Whether to override the section.
 *
 * @since 1.1.0
 * @return void
 */
function intercessor_settings_display( $settings_tabs, $active_tab, $sections, $section, $override ) {

	ob_start();
	?>
	<div class="wrap <?php echo 'wrap-' . esc_attr( $active_tab ); ?>">
		<h2><?php esc_html_e( 'Intercessor Settings', 'intercessor' ); ?></h2>
		<h2 class="nav-tab-wrapper intercessor-settings-nav">
			<?php
			foreach ( $settings_tabs as $tab_id => $tab_name ) {
				$tab_url = add_query_arg(
					[
						'settings-updated' => false,
						'tab'              => $tab_id,
					]
				);

				// Remove the section from the tabs.
				$tab_url = \remove_query_arg( 'section', $tab_url );

				$active = $active_tab === $tab_id ? ' nav-tab-active' : '';

				echo '<a href="' . esc_url( $tab_url ) . '" class="nav-tab' . $active . '">';
				echo esc_html( $tab_name );
				echo '</a>';

				flush_rewrite_rules( true );
			}
			?>
		</h2>
		<?php

		$number_of_sections = count( $sections );
		$number             = 0;

		if ( $number_of_sections > 1 ) {
			echo '<div><ul class="subsubsub intercessor-sub-nav">';
			foreach ( $sections as $section_id => $section_name ) {
				echo '<li>';
				$number++;
				$tab_url = add_query_arg(
					array(
						'settings-updated' => false,
						'tab'              => $active_tab,
						'section'          => $section_id,
					)
				);

				$class = '';
				if ( $section === $section_id ) {
					$class = 'current';
				}
				echo '<a class="' . esc_attr( $class ) . '" href="' . esc_url( $tab_url ) . '">' . esc_attr( $section_name ) . '</a>';

				if ( $number !== $number_of_sections ) {
					echo ' | ';
				}
				echo '</li>';
			}
			echo '</ul></div>';
		}
		?>
		<div id="tab_container">
			<form method="post" action="options.php">
				<table class="form-table">
					<?php

					settings_fields( 'intercessor_settings' );

					if ( 'main' === $section ) {
						do_action( 'intercessor_top', $active_tab );
					}

					do_action( 'intercessor_settings_tab_top_' . $active_tab . '_' . $section );

					do_settings_sections( 'intercessor_settings_' . $active_tab . '_' . $section );

					do_action( 'intercessor_settings_tab_bottom_' . $active_tab . '_' . $section  );

					if ( 'main' === $section ) {
						do_action( 'intercessor_settings_tab_bottom', $active_tab );
					}

					// If the main section was empty and we overrode the view
					// with the next subsection, prepare the section for saving.
					if ( true === $override ) {
						?>
						<input type="hidden" name="intercessor_section_override" value="<?php echo $section; ?>" />
						<?php
					}
					?>
				</table>
				<?php submit_button(); ?>
			</form>
		</div><!-- #tab_container-->
	</div><!-- .wrap -->
	<?php
	echo ob_get_clean();
}

