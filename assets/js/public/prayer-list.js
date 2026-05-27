/* global intercessorPray */
( function () {
	'use strict';

	const config = window.intercessorPray || {};
	const i18n   = config.i18n || {};

	document.querySelectorAll( '.intercessor-pray-btn' ).forEach( function ( btn ) {
		btn.addEventListener( 'click', async function () {
			if ( btn.disabled ) return;

			btn.disabled = true;
			btn.classList.add( 'intercessor-pray-btn--loading' );
			const label = btn.querySelector( '.intercessor-pray-label' );
			const count = btn.querySelector( '.intercessor-pray-count' );
			const orig  = label ? label.textContent : '';

			if ( label ) label.textContent = i18n.praying || 'Praying\u2026';

			try {
				const body = new FormData();
				body.append( 'action',     config.action || 'intercessor_record_prayer' );
				body.append( 'nonce',      config.nonce  || '' );
				body.append( 'request_id', btn.dataset.requestId || '' );

				const res  = await fetch( config.ajaxUrl || '/wp-admin/admin-ajax.php', {
					method: 'POST',
					body,
				} );
				const json = await res.json();

				if ( json.success ) {
					if ( label ) label.textContent = i18n.prayed || 'I prayed for this!';
					if ( count ) count.textContent = json.data.total;
					btn.classList.add( 'intercessor-pray-btn--prayed' );
				} else {
					if ( label ) label.textContent = orig;
					btn.disabled = false;
					alert( json.data.message || i18n.error || 'Could not record your prayer.' ); // eslint-disable-line no-alert
				}
			} catch ( err ) {
				if ( label ) label.textContent = orig;
				btn.disabled = false;
			} finally {
				btn.classList.remove( 'intercessor-pray-btn--loading' );
			}
		} );
	} );
} () );
