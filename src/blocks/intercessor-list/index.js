( function( wp ) {
	var registerBlockType = wp.blocks.registerBlockType;
	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var TextControl = wp.components && wp.components.TextControl;
	var Button = wp.components && wp.components.Button;

	registerBlockType( 'intercessor/intercessor-list', {
		edit: function( props ) {
			var attrs = props.attributes;
			var setAttributes = props.setAttributes;
			var className = props.className || '';
			var items = attrs.items || [];

			function addItem() {
				var nextId = items.length ? ( items[ items.length - 1 ].id + 1 ) : 1;
				setAttributes( { items: items.concat( [ { id: nextId, text: 'New prayer' } ] ) } );
			}
			function updateItemText( index, text ) {
				var next = items.map( function( it, i ) {
					return i === index ? { id: it.id, text: text } : it;
				} );
				setAttributes( { items: next } );
			}
			function removeItem( index ) {
				var next = items.filter( function( _, i ) {
					return i !== index;
				} );
				setAttributes( { items: next } );
			}

			return el(
				'div',
				{ className: className + ' intercessor-list-editor' },
				items.map( function( it, i ) {
					return el(
						'div',
						{ key: it.id, className: 'intercessor-list-item' },
						el( TextControl, {
							value: it.text,
							onChange: function( value ) {
								updateItemText( i, value );
							}
						} ),
						el( Button, { isDestructive: true, onClick: function() { removeItem( i ); } }, 'Remove' )
					);
				} ),
				el( Button, { isPrimary: true, onClick: addItem }, 'Add item' )
			);
		},
		save: function( props ) {
			var attrs = props.attributes;
			var className = props.className || '';
			var items = attrs.items || [];

			return el(
				'div',
				{ className: className + ' intercessor-list' },
				el( 'ul', null,
					items.map( function( it ) {
						return el( 'li', { key: it.id }, it.text );
					} )
				)
			);
		}
	} );
} )( window.wp );
