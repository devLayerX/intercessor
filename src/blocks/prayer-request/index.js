( function( wp ) {
	// Compatibility fallbacks.
	var registerBlockType = wp.blocks.registerBlockType;
	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var RichText = ( wp.blockEditor && wp.blockEditor.RichText ) || ( wp.editor && wp.editor.RichText );

	registerBlockType( 'intercessor/prayer-request', {
		edit: function( props ) {
			var attrs = props.attributes;
			var setAttributes = props.setAttributes;
			var className = props.className || '';

			return el(
				Fragment,
				null,
				el(
					'div',
					{ className: className + ' intercessor-prayer-request-editor' },
					el( RichText, {
						tagName: 'h3',
						placeholder: 'Prayer title…',
						value: attrs.title,
						onChange: function( value ) {
							setAttributes( { title: value } );
						}
					} ),
					el( RichText, {
						tagName: 'p',
						placeholder: 'Prayer details…',
						value: attrs.content,
						onChange: function( value ) {
							setAttributes( { content: value } );
						}
					} )
				)
			);
		},
		save: function( props ) {
			var attrs = props.attributes;
			var className = props.className || '';

			return el(
				'div',
				{ className: className + ' intercessor-prayer-request' },
				el( RichText.Content, { tagName: 'h3', value: attrs.title } ),
				el( RichText.Content, { tagName: 'p', value: attrs.content } )
			);
		}
	} );
} )( window.wp );
