/*!
 * Intercessor Admin Upgrades JS
 *
 * @description: The Intercessor Admin Upgrades scripts.
 * @package:     Intercessor
 * @since:       1.1.0
 * @subpackage:  Assets/JS
 * @copyright:   Copyright (c) 2021, Victor Aigbeghian
 * @license:     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 */

/* globals jQuery */

jQuery( document ).ready( function( $ ) {
	const Intercessor_v110_Upgrades = {
		inProgress: false,

		init: function() {
			// Listen for toggle on the checkbox.
			$( '.intercessor-v110-upgrade-confirmation' ).on( 'change', function( e ) {
				e.preventDefault();
				const wrapperForm = $( this ).closest( '.intercessor-v110-upgrade-button' );
				const formSubmit = wrapperForm.find( 'button' );

				if ( e.target.checked ) {
					formSubmit.removeClass( 'disabled' ).prop( 'disabled', false );
				} else {
					formSubmit.addClass( 'disabled' ).prop( 'disabled', true );
				}
			} );

			$( '.intercessor-v110-migration' ).on( 'submit', function( e ) {
				e.preventDefault();

				if ( Intercessor_v110_Upgrades.inProgress ) {
					return;
				}

				Intercessor_v110_Upgrades.inProgress = true;

				const migrationForm = $( this );
				const upgradeKeyField = migrationForm.find( 'input[name="upgrade_key"]' );
				let upgradeKey = false;

				if ( upgradeKeyField.length && upgradeKeyField.val() ) {
					upgradeKey = upgradeKeyField.val();
				}

				// Disable submit button.
				migrationForm.find( 'button' )
					.removeClass( 'button-primary' )
					.addClass( 'button-secondary disabled updating-message' )
					.prop( 'disabled', true );

				// Disable checkbox.
				migrationForm.find( 'input' ).prop( 'disabled', true );

				// If this is the main migration, reveal the steps & mark the first non-complete item as in progress.
				if ( 'intercessor-v110-migration' === migrationForm.attr( 'id' ) ) {
					$( '#intercessor-migration-progress' ).removeClass( 'intercessor-hidden' );
					const firstNonCompleteUpgrade = $( '#intercessor-migration-progress li:not(.intercessor-upgrade-complete)' );
					if ( firstNonCompleteUpgrade.length && ! upgradeKey ) {
						upgradeKey = firstNonCompleteUpgrade.data( 'upgrade' );
					}
				}

				Intercessor_v110_Upgrades.processStep( upgradeKey, 1, migrationForm.find( 'input[name="_wpnonce"]' ).val() );
			} )
		},

		processStep: function( upgrade_key, step, nonce ) {
			let data = {
				action: 'intercessor_process_v110_upgrade',
				_ajax_nonce: nonce,
				upgrade_key: upgrade_key,
				step: step
			}

			Intercessor_v110_Upgrades.clearErrors();

			if ( upgrade_key ) {
				Intercessor_v110_Upgrades.markUpgradeInProgress( upgrade_key );
			}

			$.ajax( {
				type: 'POST',
				data: data,
				url: ajaxurl,
				success: function( response ) {
					if ( ! response.success ) {
						Intercessor_v110_Upgrades.showError( upgrade_key, response.data );
						return;
					}

					if ( response.data.upgrade_completed ) {
						Intercessor_v110_Upgrades.markUpgradeComplete( response.data.upgrade_processed );

						// If we just completed legacy data removal then we're all done!
						if ( 'v1100_legacy_data_removed' === response.data.upgrade_processed ) {
							Intercessor_v110_Upgrades.legacyDataRemovalComplete();

							return;
						}
					} else if( response.data.percentage ) {
						// Update percentage for the upgrade we just processed.
						Intercessor_v110_Upgrades.updateUpgradePercentage( response.data.upgrade_processed, response.data.percentage );
					}

					if ( response.data.next_upgrade && 'v1100_legacy_data_removed' === response.data.next_upgrade && 'v1100_legacy_data_removed' !== response.data.upgrade_processed ) {
						Intercessor_v110_Upgrades.inProgress = false;

						// Legacy data removal is next, which we do not start automatically.
						Intercessor_v110_Upgrades.showLegacyDataRemoval();
					} else if ( response.data.next_upgrade ) {
						// Start the next upgrade (or continuation of current) automatically.
						Intercessor_v110_Upgrades.processStep( response.data.next_upgrade, response.data.next_step, response.data.nonce );
					} else {
						Intercessor_v110_Upgrades.inProgress = false;
						Intercessor_v110_Upgrades.stopAllSpinners();
					}
				}
			} ).fail( ( data ) => {
				// @todo
			} )
		},

		clearErrors: function() {
			$( '.intercessor-v110-migration-error' ).addClass( 'intercessor-hidden' ).html( '' );
		},

		showError: function( upgradeKey, message ) {
			let container = $( '#intercessor-v110-migration' );
			if ( 'v1100_legacy_data_removed' === upgradeKey ) {
				container = $( '#intercessor-v110-remove-legacy-data' );
			}
			const errorWrapper = container.find( '.intercessor-v110-migration-error' );

			errorWrapper.html( '<p>' + message + '</p>' ).removeClass( 'intercessor-hidden' );

			// Stop processing and allow form resubmission.
			Intercessor_v110_Upgrades.inProgress = false;
			container.find( 'input' ).prop( 'disabled', false );
			container.find( 'button' )
				.prop( 'disabled', false )
				.addClass( 'button-primary' )
				.removeClass( 'button-secondary disabled updating-message' );
		},

		markUpgradeInProgress: function( upgradeKey ) {
			const upgradeRow = $( '#intercessor-v110-migration-' + upgradeKey );
			if ( ! upgradeRow.length ) {
				return;
			}

			const statusIcon = upgradeRow.find( '.dashicons' );
			if ( statusIcon.length ) {
				statusIcon.removeClass( 'dashicons-minus' ).addClass( 'dashicons-update' );
			}

			upgradeRow.find( '.intercessor-migration-percentage' ).removeClass( 'intercessor-hidden' );
		},

		updateUpgradePercentage: function( upgradeKey, newPercentage ) {
			const upgradeRow = $( '#intercessor-v110-migration-' + upgradeKey );
			if ( ! upgradeRow.length ) {
				return;
			}

			upgradeRow.find( '.intercessor-migration-percentage-value' ).text( newPercentage );
		},

		markUpgradeComplete: function( upgradeKey ) {
			const upgradeRow = $( '#intercessor-v110-migration-' + upgradeKey );
			if ( ! upgradeRow.length ) {
				return;
			}

			upgradeRow.addClass( 'intercessor-upgrade-complete' );

			const statusIcon = upgradeRow.find( '.dashicons' );
			if ( statusIcon.length ) {
				statusIcon.removeClass( 'dashicons-minus dashicons-update' ).addClass( 'dashicons-yes' );
			}

			const statusLabel = upgradeRow.find( '.intercessor-migration-status .screen-reader-text' );
			if ( statusLabel.length ) {
				statusLabel.text( intercessor_admin_upgrade_vars.migration_complete );
			}

			// Update percentage to 100%;
			upgradeRow.find( '.intercessor-migration-percentage-value' ).text( 100 );
		},

		showLegacyDataRemoval: function() {
			// Un-spin the main submit button.
			$( '#intercessor-v110-migration-button' ).removeClass( 'updating-message' );

			// Show the "migration complete" message.
			$( '#intercessor-v110-migration-complete' ).removeClass( 'intercessor-hidden' );

			const dataRemovalWrapper = $( '#intercessor-v110-remove-legacy-data' );
			if ( ! dataRemovalWrapper.length ) {
				return;
			}

			dataRemovalWrapper.removeClass( 'intercessor-hidden' );
		},

		legacyDataRemovalComplete: function() {
			const wrapper = $( '#intercessor-v110-remove-legacy-data' );
			if ( ! wrapper.length ) {
				return;
			}

			wrapper.find( 'form' ).addClass( 'intercessor-hidden' );
			wrapper.find( '#intercessor-v110-legacy-data-removal-complete' ).removeClass( 'intercessor-hidden' );
		},

		stopAllSpinners: function() {

		}
	}

	Intercessor_v110_Upgrades.init();
} );
