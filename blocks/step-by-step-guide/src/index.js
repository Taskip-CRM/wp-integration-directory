/**
 * Step-by-Step Guide Gutenberg Block
 */

const { registerBlockType } = wp.blocks;
const { 
    InspectorControls, 
    RichText, 
    MediaUpload, 
    MediaUploadCheck,
    ColorPalette 
} = wp.blockEditor;
const { 
    PanelBody, 
    TextControl, 
    TextareaControl,
    SelectControl,
    ToggleControl,
    Button,
    RangeControl,
    ColorPicker,
    TabPanel
} = wp.components;
const { Fragment, useState } = wp.element;
const { __ } = wp.i18n;

registerBlockType('wp-integrations-directory/step-by-step-guide', {
    title: __('Step-by-Step Guide', 'wp-integrations-directory'),
    description: __('Create interactive step-by-step integration guides', 'wp-integrations-directory'),
    category: 'widgets',
    icon: 'list-view',
    keywords: [
        __('steps', 'wp-integrations-directory'),
        __('guide', 'wp-integrations-directory'),
        __('tutorial', 'wp-integrations-directory'),
        __('integration', 'wp-integrations-directory')
    ],
    supports: {
        html: false,
        align: ['wide', 'full']
    },
    attributes: {
        title: {
            type: 'string',
            default: 'Step-by-Step Guide'
        },
        description: {
            type: 'string',
            default: ''
        },
        steps: {
            type: 'array',
            default: [{
                id: 1,
                title: 'Step 1',
                content: 'Enter your step description here.',
                image: '',
                imageAlt: '',
                code: '',
                codeLanguage: 'html',
                showCode: false
            }]
        },
        layout: {
            type: 'string',
            default: 'numbered'
        },
        showNumbers: {
            type: 'boolean',
            default: true
        },
        accentColor: {
            type: 'string',
            default: '#0073aa'
        }
    },

    edit: function(props) {
        const { attributes, setAttributes, className } = props;
        const { title, description, steps, layout, showNumbers, accentColor } = attributes;

        const addStep = () => {
            const newStep = {
                id: steps.length + 1,
                title: `Step ${steps.length + 1}`,
                content: 'Enter your step description here.',
                image: '',
                imageAlt: '',
                code: '',
                codeLanguage: 'html',
                showCode: false
            };
            setAttributes({ steps: [...steps, newStep] });
        };

        const removeStep = (index) => {
            if (steps.length > 1) {
                const newSteps = steps.filter((_, i) => i !== index);
                // Renumber the steps
                const renumberedSteps = newSteps.map((step, i) => ({
                    ...step,
                    id: i + 1,
                    title: step.title.replace(/Step \d+/, `Step ${i + 1}`)
                }));
                setAttributes({ steps: renumberedSteps });
            }
        };

        const updateStep = (index, field, value) => {
            const newSteps = [...steps];
            newSteps[index] = { ...newSteps[index], [field]: value };
            setAttributes({ steps: newSteps });
        };

        const moveStep = (index, direction) => {
            const newSteps = [...steps];
            const targetIndex = direction === 'up' ? index - 1 : index + 1;
            
            if (targetIndex >= 0 && targetIndex < steps.length) {
                [newSteps[index], newSteps[targetIndex]] = [newSteps[targetIndex], newSteps[index]];
                setAttributes({ steps: newSteps });
            }
        };

        const duplicateStep = (index) => {
            const stepToDuplicate = { ...steps[index] };
            stepToDuplicate.id = steps.length + 1;
            stepToDuplicate.title = stepToDuplicate.title.replace(/Step \d+/, `Step ${steps.length + 1}`);
            setAttributes({ steps: [...steps, stepToDuplicate] });
        };

        return (
            <Fragment>
                <InspectorControls>
                    <PanelBody title={__('Guide Settings', 'wp-integrations-directory')} initialOpen={true}>
                        <TextControl
                            label={__('Guide Title', 'wp-integrations-directory')}
                            value={title}
                            onChange={(value) => setAttributes({ title: value })}
                        />
                        
                        <TextareaControl
                            label={__('Guide Description', 'wp-integrations-directory')}
                            value={description}
                            onChange={(value) => setAttributes({ description: value })}
                            help={__('Brief description of what this guide will help users achieve', 'wp-integrations-directory')}
                        />

                        <SelectControl
                            label={__('Layout Style', 'wp-integrations-directory')}
                            value={layout}
                            options={[
                                { label: __('Numbered Steps', 'wp-integrations-directory'), value: 'numbered' },
                                { label: __('Timeline', 'wp-integrations-directory'), value: 'timeline' },
                                { label: __('Minimal', 'wp-integrations-directory'), value: 'minimal' }
                            ]}
                            onChange={(value) => setAttributes({ layout: value })}
                        />

                        <ToggleControl
                            label={__('Show Step Numbers', 'wp-integrations-directory')}
                            checked={showNumbers}
                            onChange={(value) => setAttributes({ showNumbers: value })}
                        />

                        <p><strong>{__('Accent Color', 'wp-integrations-directory')}</strong></p>
                        <ColorPicker
                            color={accentColor}
                            onChangeComplete={(value) => setAttributes({ accentColor: value.hex })}
                            disableAlpha
                        />
                    </PanelBody>

                    <PanelBody title={__('Steps Management', 'wp-integrations-directory')} initialOpen={false}>
                        <p>{__('Total Steps:', 'wp-integrations-directory')} <strong>{steps.length}</strong></p>
                        <Button
                            isPrimary
                            onClick={addStep}
                            className="add-step-button"
                        >
                            {__('Add New Step', 'wp-integrations-directory')}
                        </Button>
                    </PanelBody>
                </InspectorControls>

                <div className={`${className} wp-block-step-by-step-guide layout-${layout}`} 
                     style={{ '--accent-color': accentColor }}>
                    
                    <div className="step-guide-header editor-header">
                        <RichText
                            tagName="h2"
                            className="step-guide-title"
                            value={title}
                            onChange={(value) => setAttributes({ title: value })}
                            placeholder={__('Enter guide title...', 'wp-integrations-directory')}
                        />
                        
                        {description && (
                            <p className="step-guide-description">{description}</p>
                        )}
                    </div>

                    <div className="step-guide-content editor-content">
                        {steps.map((step, index) => (
                            <div key={step.id} className="step-item editor-step-item" data-step={index + 1}>
                                <div className="step-controls">
                                    <div className="step-control-buttons">
                                        <Button
                                            isSmall
                                            isSecondary
                                            onClick={() => moveStep(index, 'up')}
                                            disabled={index === 0}
                                            title={__('Move Up', 'wp-integrations-directory')}
                                        >
                                            ↑
                                        </Button>
                                        <Button
                                            isSmall
                                            isSecondary
                                            onClick={() => moveStep(index, 'down')}
                                            disabled={index === steps.length - 1}
                                            title={__('Move Down', 'wp-integrations-directory')}
                                        >
                                            ↓
                                        </Button>
                                        <Button
                                            isSmall
                                            isSecondary
                                            onClick={() => duplicateStep(index)}
                                            title={__('Duplicate Step', 'wp-integrations-directory')}
                                        >
                                            {__('Copy', 'wp-integrations-directory')}
                                        </Button>
                                        <Button
                                            isSmall
                                            isDestructive
                                            onClick={() => removeStep(index)}
                                            disabled={steps.length <= 1}
                                            title={__('Remove Step', 'wp-integrations-directory')}
                                        >
                                            ×
                                        </Button>
                                    </div>
                                </div>

                                <div className="step-header">
                                    {showNumbers && <span className="step-number">{index + 1}</span>}
                                    <RichText
                                        tagName="h3"
                                        className="step-title"
                                        value={step.title}
                                        onChange={(value) => updateStep(index, 'title', value)}
                                        placeholder={__('Step title...', 'wp-integrations-directory')}
                                    />
                                </div>

                                <div className="step-content">
                                    <RichText
                                        tagName="div"
                                        className="step-description"
                                        value={step.content}
                                        onChange={(value) => updateStep(index, 'content', value)}
                                        placeholder={__('Describe this step...', 'wp-integrations-directory')}
                                        multiline="p"
                                    />

                                    <div className="step-media-controls">
                                        <TabPanel
                                            className="step-media-tabs"
                                            activeClass="active-tab"
                                            tabs={[
                                                {
                                                    name: 'image',
                                                    title: __('Image', 'wp-integrations-directory'),
                                                    className: 'tab-image'
                                                },
                                                {
                                                    name: 'code',
                                                    title: __('Code', 'wp-integrations-directory'),
                                                    className: 'tab-code'
                                                }
                                            ]}
                                        >
                                            {(tab) => (
                                                <div className="tab-content">
                                                    {tab.name === 'image' && (
                                                        <div className="image-controls">
                                                            <MediaUploadCheck>
                                                                <MediaUpload
                                                                    onSelect={(media) => {
                                                                        updateStep(index, 'image', media.url);
                                                                        updateStep(index, 'imageAlt', media.alt);
                                                                    }}
                                                                    allowedTypes={['image']}
                                                                    value={step.image}
                                                                    render={({ open }) => (
                                                                        <Button
                                                                            onClick={open}
                                                                            isPrimary={!step.image}
                                                                            isSecondary={!!step.image}
                                                                        >
                                                                            {step.image 
                                                                                ? __('Change Image', 'wp-integrations-directory')
                                                                                : __('Select Image', 'wp-integrations-directory')
                                                                            }
                                                                        </Button>
                                                                    )}
                                                                />
                                                            </MediaUploadCheck>
                                                            
                                                            {step.image && (
                                                                <div className="selected-image">
                                                                    <img src={step.image} alt={step.imageAlt} style={{ maxWidth: '200px', height: 'auto' }} />
                                                                    <Button
                                                                        isDestructive
                                                                        isSmall
                                                                        onClick={() => {
                                                                            updateStep(index, 'image', '');
                                                                            updateStep(index, 'imageAlt', '');
                                                                        }}
                                                                    >
                                                                        {__('Remove', 'wp-integrations-directory')}
                                                                    </Button>
                                                                </div>
                                                            )}
                                                        </div>
                                                    )}

                                                    {tab.name === 'code' && (
                                                        <div className="code-controls">
                                                            <ToggleControl
                                                                label={__('Show Code Block', 'wp-integrations-directory')}
                                                                checked={step.showCode}
                                                                onChange={(value) => updateStep(index, 'showCode', value)}
                                                            />
                                                            
                                                            {step.showCode && (
                                                                <Fragment>
                                                                    <SelectControl
                                                                        label={__('Code Language', 'wp-integrations-directory')}
                                                                        value={step.codeLanguage}
                                                                        options={[
                                                                            { label: 'HTML', value: 'html' },
                                                                            { label: 'CSS', value: 'css' },
                                                                            { label: 'JavaScript', value: 'javascript' },
                                                                            { label: 'PHP', value: 'php' },
                                                                            { label: 'JSON', value: 'json' },
                                                                            { label: 'XML', value: 'xml' }
                                                                        ]}
                                                                        onChange={(value) => updateStep(index, 'codeLanguage', value)}
                                                                    />
                                                                    
                                                                    <TextareaControl
                                                                        label={__('Code Content', 'wp-integrations-directory')}
                                                                        value={step.code}
                                                                        onChange={(value) => updateStep(index, 'code', value)}
                                                                        placeholder={__('Enter your code here...', 'wp-integrations-directory')}
                                                                        rows={6}
                                                                    />
                                                                </Fragment>
                                                            )}
                                                        </div>
                                                    )}
                                                </div>
                                            )}
                                        </TabPanel>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>

                    <div className="step-guide-footer editor-footer">
                        <Button
                            isPrimary
                            onClick={addStep}
                            className="add-step-button-inline"
                        >
                            {__('+ Add Step', 'wp-integrations-directory')}
                        </Button>
                    </div>
                </div>
            </Fragment>
        );
    },

    save: function() {
        // Dynamic block - content rendered in PHP
        return null;
    }
});