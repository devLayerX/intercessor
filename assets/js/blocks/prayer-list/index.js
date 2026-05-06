/**
 * Prayer List Block — Editor script.
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
	var RangeControl      = components.RangeControl;
	var SelectControl     = components.SelectControl;

	registerBlockType( 'intercessor/prayer-list', {
		edit: function ( props ) {
			var attributes    = props.attributes;
			var setAttributes = props.setAttributes;
			var blockProps    = useBlockProps( { className: 'intercessor-prayer-list-editor' } );

			return [
				el( InspectorControls, { key: 'controls' },
					el( PanelBody, { title: __( 'List Settings', 'intercessor' ), initialOpen: true },
						el( RangeControl, {
							label:    __( 'Items per Page', 'intercessor' ),
							value:    attributes.limit,
							onChange: function ( val ) { setAttributes( { limit: val } ); },
							min: 1,
							max: 50,
						} ),
						el( SelectControl, {
							label:    __( 'Show Requests with Status', 'intercessor' ),
							value:    attributes.status,
							options:  [
								{ label: __( 'Approved', 'intercessor' ),         value: 'approved' },
								{ label: __( 'Pending (admin only)', 'intercessor' ), value: 'pending'  },
								{ label: __( 'All', 'intercessor' ),               value: 'all'      },
							],
							onChange: function ( val ) { setAttributes( { status: val } ); },
						} ),
						el( ToggleControl, {
							label:    __( 'Show Submission Date', 'intercessor' ),
							checked:  attributes.showDate,
							onChange: function ( val ) { setAttributes( { showDate: val } ); },
						} ),
						el( ToggleControl, {
							label:    __( 'Show Requester Name', 'intercessor' ),
							checked:  attributes.showAuthor,
							onChange: function ( val ) { setAttributes( { showAuthor: val } ); },
						} )
					)
				),
				el( 'div', blockProps,
					el( 'div', { className: 'intercessor-editor-preview' },
						el( 'p', { className: 'intercessor-editor-label' },
							__( '📋 Prayer List', 'intercessor' )
						),
						el( 'p', { className: 'intercessor-editor-hint' },
							__( 'Displays approved prayer requests with pagination.', 'intercessor' )
						),
						el( 'ul', { className: 'intercessor-editor-mock-list' },
							[ 1, 2, 3 ].map( function ( n ) {
								return el( 'li', { key: n, className: 'intercessor-editor-mock-item' },
									el( 'span', { className: 'intercessor-editor-mock-title' },
										__( 'Prayer Request', 'intercessor' ) + ' #' + n
									)
								);
							} )
						),
						el( 'p', { className: 'intercessor-editor-hint' },
							__( 'Showing up to', 'intercessor' ) + ' ' + attributes.limit + ' ' + __( 'items', 'intercessor' )
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
