/**
 * Prayer History Block — Editor script.
 *
 * @package Intercessor
 * @since   1.0.0
 */
( function ( blocks, blockEditor, components, i18n, element ) {
	'use strict';

	var el                = element.createElement;
	var __                = i18n.__;
	var registerBlockType = blocks.registerBlockType;
	var useBlockProps     = blockEditor.useBlockProps;
	var InspectorControls = blockEditor.InspectorControls;
	var PanelBody         = components.PanelBody;
	var ToggleControl     = components.ToggleControl;
	var TextControl       = components.TextControl;

	registerBlockType( 'intercessor/prayer-history', {
		edit: function ( props ) {
			var attributes    = props.attributes;
			var setAttributes = props.setAttributes;
			var blockProps    = useBlockProps( { className: 'intercessor-prayer-history-editor' } );

			return [
				el( InspectorControls, { key: 'controls' },
					el( PanelBody, { title: __( 'History Settings', 'intercessor' ), initialOpen: true },
						el( TextControl, {
							label:    __( 'Prayer Request ID', 'intercessor' ),
							help:     __( 'Leave 0 to read from the URL query parameter ?prayer_request=ID.', 'intercessor' ),
							type:     'number',
							value:    attributes.requestId,
							onChange: function ( val ) {
								setAttributes( { requestId: parseInt( val, 10 ) || 0 } );
							},
						} ),
						el( ToggleControl, {
							label:    __( 'Show Moderator Notes', 'intercessor' ),
							checked:  attributes.showNotes,
							onChange: function ( val ) { setAttributes( { showNotes: val } ); },
						} ),
						el( ToggleControl, {
							label:    __( 'Show Moderator Name (admins only)', 'intercessor' ),
							checked:  attributes.showModerator,
							onChange: function ( val ) { setAttributes( { showModerator: val } ); },
						} )
					)
				),
				el( 'div', blockProps,
					el( 'div', { className: 'intercessor-editor-preview' },
						el( 'p', { className: 'intercessor-editor-label' },
							__( '🕐 Prayer History', 'intercessor' )
						),
						attributes.requestId > 0
							? el( 'p', { className: 'intercessor-editor-hint' },
								__( 'Showing history for request ID:', 'intercessor' ) + ' ' + attributes.requestId
							)
							: el( 'p', { className: 'intercessor-editor-hint' },
								__( 'Will read the prayer request ID from the URL (?prayer_request=ID).', 'intercessor' )
							),
						el( 'ol', { className: 'intercessor-editor-mock-timeline' },
							el( 'li', null,
								el( 'strong', null, __( 'Pending', 'intercessor' ) ),
								' → ',
								el( 'strong', null, __( 'Approved', 'intercessor' ) ),
								el( 'span', { className: 'intercessor-editor-mock-meta' }, ' — Jan 1, 2025' )
							)
						)
					)
				)
			];
		},

		save: function () {
			return null;
		},
	} );

} )(
	window.wp.blocks,
	window.wp.blockEditor,
	window.wp.components,
	window.wp.i18n,
	window.wp.element
);
