/**
 * Prayer Form Block — Editor (src/blocks/prayer-form/index.js)
 *
 * Compiled by @wordpress/scripts into assets/js/blocks/prayer-form/index.js
 *
 * Run:  npm run build
 * Dev:  npm run start
 */
import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import {
    PanelBody,
    ToggleControl,
    TextControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';

registerBlockType( metadata.name, {
    edit( { attributes, setAttributes } ) {
        const blockProps = useBlockProps( {
            className: 'intercessor-prayer-form-editor',
        } );

        const {
            showAnonymousOption,
            submitLabel,
            successMessage,
        } = attributes;

        return (
            <>
                <InspectorControls>
                    <PanelBody title={ __( 'Form Settings', 'intercessor' ) }>
                        <ToggleControl
                            label={ __( 'Show Anonymous Option', 'intercessor' ) }
                            help={ __(
                                'Allow submitters to mark their request as anonymous.',
                                'intercessor'
                            ) }
                            checked={ showAnonymousOption }
                            onChange={ ( val ) =>
                                setAttributes( { showAnonymousOption: val } )
                            }
                        />
                        <TextControl
                            label={ __( 'Submit Button Label', 'intercessor' ) }
                            value={ submitLabel }
                            placeholder={ __( 'Submit Prayer Request', 'intercessor' ) }
                            onChange={ ( val ) =>
                                setAttributes( { submitLabel: val } )
                            }
                        />
                        <TextControl
                            label={ __( 'Success Message', 'intercessor' ) }
                            value={ successMessage }
                            placeholder={ __(
                                'Thank you. Your prayer request has been received.',
                                'intercessor'
                            ) }
                            onChange={ ( val ) =>
                                setAttributes( { successMessage: val } )
                            }
                        />
                    </PanelBody>
                </InspectorControls>

                <div { ...blockProps }>
                    {/* Editor preview — the live form is PHP-rendered on the front end */}
                    <div className="intercessor-editor-preview">
                        <p className="intercessor-editor-label">
                            { __( '🙏 Prayer Form', 'intercessor' ) }
                        </p>
                        <p className="intercessor-editor-hint">
                            { __(
                                'The prayer request form will be rendered here on the front end.',
                                'intercessor'
                            ) }
                        </p>
                        { showAnonymousOption && (
                            <p className="intercessor-editor-hint">
                                { __(
                                    '✓ Anonymous option enabled',
                                    'intercessor'
                                ) }
                            </p>
                        ) }
                    </div>
                </div>
            </>
        );
    },

    // The block uses a PHP render callback — no save output.
    save() {
        return null;
    },
} );
