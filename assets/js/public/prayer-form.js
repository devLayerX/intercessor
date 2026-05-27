( function () {
	'use strict';

	const wrap   = document.querySelector( '[data-intercessor-form]' );
	if ( ! wrap ) return;

	const form   = wrap.querySelector( '.intercessor-form' );
	const msgs   = wrap.querySelector( '.intercessor-form-messages' );
	const config = window.intercessorForm      || {};
	const rc     = window.intercessorRecaptcha || {};
	const labels = config.messages || {};

	// ── Registration fieldset toggle ──────────────────────────────────────
	// Declared at outer scope so the submit handler's success branch can also
	// collapse the panel after a successful submission.
	const createChk = form ? form.querySelector( '#intercessor-create-account' ) : null;
	const regFields = form ? form.querySelector( '#intercessor-register-fields' ) : null;

	if ( createChk && regFields ) {
		const usernameInput  = regFields.querySelector( '[name="username"]' );
		const pwInput        = regFields.querySelector( '[name="account_password"]' );
		const pwConfirmInput = regFields.querySelector( '[name="account_password_confirm"]' );

		function toggleRegFields( open ) {
			regFields.hidden = ! open;
			regFields.setAttribute( 'aria-hidden', open ? 'false' : 'true' );
			createChk.setAttribute( 'aria-expanded', open ? 'true' : 'false' );

			[ usernameInput, pwInput, pwConfirmInput ].forEach( function ( el ) {
				if ( el ) el.required = open;
			} );
		}

		toggleRegFields( false );

		createChk.addEventListener( 'change', function () {
			toggleRegFields( this.checked );
			if ( this.checked && usernameInput ) usernameInput.focus();
		} );
	}

	function showMessage( text, type ) {
		msgs.innerHTML = '<div class="intercessor-alert intercessor-alert--' + type + '">' +
			text.replace( /</g, '&lt;' ) + '</div>';
	}

	/**
	 * Obtain a reCAPTCHA v3 token asynchronously.
	 * Returns a Promise that resolves to the token string.
	 */
	function getV3Token() {
		return new Promise( function ( resolve ) {
			if ( typeof grecaptcha === 'undefined' || typeof grecaptcha.execute === 'undefined' ) {
				resolve( '' );
				return;
			}
			grecaptcha.ready( function () {
				grecaptcha.execute( rc.siteKey || '', { action: rc.action || 'submit' } )
					.then( resolve )
					.catch( function () { resolve( '' ); } );
			} );
		} );
	}

	form.addEventListener( 'submit', async function ( e ) {
		e.preventDefault();

		const btn = form.querySelector( '.intercessor-submit' );
		btn.disabled = true;
		msgs.innerHTML = '';

		const data = new FormData( form );
		data.append( 'action', config.action || 'intercessor_submit_request' );
		data.append( 'nonce',  config.nonce  || '' );

		// Inject reCAPTCHA v3 token before submitting.
		if ( config.recaptchaActive && config.recaptchaV === 'v3' ) {
			const token = await getV3Token();
			data.set( 'g-recaptcha-response', token );
		}

		try {
			const res  = await fetch( config.ajaxUrl || '/wp-admin/admin-ajax.php', {
				method : 'POST',
				body   : data,
			} );
			const json = await res.json();

			if ( json.success ) {
				form.reset();
				// Collapse the registration panel after reset.
				if ( createChk && regFields ) {
					createChk.checked = false;
					createChk.dispatchEvent( new Event( 'change' ) );
				}
				// Reset reCAPTCHA v2 widget so it can be used again.
				if ( config.recaptchaActive && config.recaptchaV === 'v2' &&
					 typeof grecaptcha !== 'undefined' ) {
					grecaptcha.reset();
				}
				showMessage( json.data.message, 'success' );
			} else {
				showMessage(
					( json.data && json.data.message ) || labels.error || 'An error occurred.',
					'error'
				);
			}
		} catch ( err ) {
			showMessage( labels.networkError || 'Network error. Please try again.', 'error' );
		} finally {
			btn.disabled = false;
		}
	} );
} () );
