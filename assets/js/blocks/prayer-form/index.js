/**
 * Prayer Form Block — Editor script.
 *
 * Registers the intercessor/prayer-form block with the WordPress block editor.
 * Uses wp.* globals so no build step is required. The front-end form is
 * rendered by PrayerFormBlock::render() (PHP); this script provides the
 * editor UI controls.
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

	registerBlockType( 'intercessor/prayer-form', {
		edit: function ( props ) {
			var attributes  = props.attributes;
			var setAttributes = props.setAttributes;
			var blockProps  = useBlockProps( { className: 'intercessor-prayer-form-editor' } );

			return [
				el( InspectorControls, { key: 'controls' },
					el( PanelBody, { title: __( 'Form Settings', 'intercessor' ), initialOpen: true },
						el( ToggleControl, {
							label:    __( 'Show Anonymous Option', 'intercessor' ),
							help:     __( 'Allow submitters to mark their request as anonymous.', 'intercessor' ),
							checked:  attributes.showAnonymousOption,
							onChange: function ( val ) { setAttributes( { showAnonymousOption: val } ); },
						} ),
						el( TextControl, {
							label:       __( 'Submit Button Label', 'intercessor' ),
							value:       attributes.submitLabel,
							placeholder: __( 'Submit Prayer Request', 'intercessor' ),
							onChange:    function ( val ) { setAttributes( { submitLabel: val } ); },
						} ),
						el( TextControl, {
							label:       __( 'Success Message', 'intercessor' ),
							value:       attributes.successMessage,
							placeholder: __( 'Thank you. Your prayer request has been received.', 'intercessor' ),
							onChange:    function ( val ) { setAttributes( { successMessage: val } ); },
						} )
					)
				),
				el( 'div', blockProps,
					el( 'div', { className: 'intercessor-editor-preview' },
						el( 'p', { className: 'intercessor-editor-label' },
							__( '🙏 Prayer Form', 'intercessor' )
						),
						el( 'p', { className: 'intercessor-editor-hint' },
							__( 'The prayer request form will be rendered here on the front end.', 'intercessor' )
						),
						attributes.showAnonymousOption && el( 'p', { className: 'intercessor-editor-hint' },
							__( '✓ Anonymous option enabled', 'intercessor' )
						)
					)
				)
			];
		},

		save: function () {
			return null; // PHP render callback handles all output.
		},
	} );

} )(
	window.wp.blocks,
	window.wp.blockEditor,
	window.wp.components,
	window.wp.i18n,
	window.wp.element
);
