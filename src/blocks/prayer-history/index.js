/**
 * Prayer History Block — Editor (src/blocks/prayer-history/index.js)
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
            className: 'intercessor-prayer-history-editor',
        } );

        const { requestId, showNotes, showModerator } = attributes;

        return (
            <>
                <InspectorControls>
                    <PanelBody title={ __( 'History Settings', 'intercessor' ) }>
                        <TextControl
                            label={ __( 'Prayer Request ID', 'intercessor' ) }
                            help={ __(
                                'Leave 0 to read from the URL query parameter ?prayer_request=ID.',
                                'intercessor'
                            ) }
                            type="number"
                            value={ requestId }
                            onChange={ ( val ) =>
                                setAttributes( { requestId: parseInt( val, 10 ) || 0 } )
                            }
                        />
                        <ToggleControl
                            label={ __( 'Show Moderator Notes', 'intercessor' ) }
                            checked={ showNotes }
                            onChange={ ( val ) => setAttributes( { showNotes: val } ) }
                        />
                        <ToggleControl
                            label={ __( 'Show Moderator Name (admins only)', 'intercessor' ) }
                            checked={ showModerator }
                            onChange={ ( val ) =>
                                setAttributes( { showModerator: val } )
                            }
                        />
                    </PanelBody>
                </InspectorControls>

                <div { ...blockProps }>
                    <div className="intercessor-editor-preview">
                        <p className="intercessor-editor-label">
                            { __( '🕐 Prayer History', 'intercessor' ) }
                        </p>
                        { requestId > 0 ? (
                            <p className="intercessor-editor-hint">
                                { `${ __( 'Showing history for request ID:', 'intercessor' ) } ${ requestId }` }
                            </p>
                        ) : (
                            <p className="intercessor-editor-hint">
                                { __(
                                    'Will read the prayer request ID from the URL (?prayer_request=ID).',
                                    'intercessor'
                                ) }
                            </p>
                        ) }

                        {/* Mock timeline preview */}
                        <ol className="intercessor-editor-mock-timeline">
                            <li>
                                <strong>{ __( 'Pending', 'intercessor' ) }</strong>
                                { ' → ' }
                                <strong>{ __( 'Approved', 'intercessor' ) }</strong>
                                <span className="intercessor-editor-mock-meta">
                                    { ' — Jan 2, 2025' }
                                </span>
                            </li>
                        </ol>
                    </div>
                </div>
            </>
        );
    },

    save() {
        return null;
    },
} );
