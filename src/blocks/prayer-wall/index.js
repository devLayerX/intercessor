/**
 * Prayer Wall Block — Editor (src/blocks/prayer-wall/index.js)
 */
import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import {
    PanelBody,
    ToggleControl,
    RangeControl,
    SelectControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';

registerBlockType( metadata.name, {
    edit( { attributes, setAttributes } ) {
        const blockProps = useBlockProps( {
            className: 'intercessor-prayer-wall-editor',
        } );

        const { limit, showDate, showAuthor, status } = attributes;

        return (
            <>
                <InspectorControls>
                    <PanelBody title={ __( 'Wall Settings', 'intercessor' ) }>
                        <RangeControl
                            label={ __( 'Items per Page', 'intercessor' ) }
                            value={ limit }
                            onChange={ ( val ) => setAttributes( { limit: val } ) }
                            min={ 1 }
                            max={ 50 }
                        />
                        <SelectControl
                            label={ __( 'Show Requests with Status', 'intercessor' ) }
                            value={ status }
                            options={ [
                                { label: __( 'Approved', 'intercessor' ), value: 'approved' },
                                { label: __( 'Pending (admin only)', 'intercessor' ), value: 'pending' },
                                { label: __( 'All', 'intercessor' ), value: 'all' },
                            ] }
                            onChange={ ( val ) => setAttributes( { status: val } ) }
                        />
                        <ToggleControl
                            label={ __( 'Show Submission Date', 'intercessor' ) }
                            checked={ showDate }
                            onChange={ ( val ) => setAttributes( { showDate: val } ) }
                        />
                        <ToggleControl
                            label={ __( 'Show Requester Name', 'intercessor' ) }
                            checked={ showAuthor }
                            onChange={ ( val ) => setAttributes( { showAuthor: val } ) }
                        />
                    </PanelBody>
                </InspectorControls>

                <div { ...blockProps }>
                    <div className="intercessor-editor-preview">
                        <p className="intercessor-editor-label">
                            { __( '📋 Prayer Wall', 'intercessor' ) }
                        </p>
                        <p className="intercessor-editor-hint">
                            { __(
                                'Displays the most recent approved prayer requests.',
                                'intercessor'
                            ) }
                        </p>
                        <ul className="intercessor-editor-mock-list">
                            { [ 1, 2, 3 ].map( ( n ) => (
                                <li key={ n } className="intercessor-editor-mock-item">
                                    <span className="intercessor-editor-mock-title">
                                        { __( 'Prayer Request', 'intercessor' ) } #{ n }
                                    </span>
                                    { showDate && (
                                        <span className="intercessor-editor-mock-meta">
                                            { ' — ' + __( 'Jan 1, 2025', 'intercessor' ) }
                                        </span>
                                    ) }
                                </li>
                            ) ) }
                        </ul>
                        <p className="intercessor-editor-hint">
                            { /* translators: %d: items per page limit */ }
                            { `${ __( 'Showing up to', 'intercessor' ) } ${ limit } ${ __( 'items', 'intercessor' ) }` }
                        </p>
                    </div>
                </div>
            </>
        );
    },

    save() {
        return null;
    },
} );
