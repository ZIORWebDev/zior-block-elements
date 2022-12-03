const { Fragment, useState } = wp.element;
const { __ } = window.wp.i18n;
const { registerFormatType, toggleFormat } = window.wp.richText;
const { BlockControls } = window.wp.blockEditor;
const { ToolbarGroup, ToolbarButton, Popover, ToggleControl, SelectControl } = wp.components;
const { createHigherOrderComponent } = wp.compose;
const enableToolbarOnBlocks = zrBlocks.enabledBlocks;

const acfFields = [];
wp.apiFetch({ path: "/zior-blocks/v1/acf-fields" }).then(fields =>
    fields.map(function(field) {
        acfFields.push({label: field.label, value: field.value})
    })
);

const setToolbarACFFieldAttributes = (settings, name) => {
    // Do nothing if it"s another block than our defined ones.
    if (!enableToolbarOnBlocks.includes(name)) {
        return settings;
    }

    return Object.assign({}, settings, {
        attributes: Object.assign( {}, settings.attributes, {
            zr_replace_with_acf_field: {
                type: "string",
                default: ""
            },
            zr_replace_with_acf_field_value: {
                type: "boolean",
                default: false
            }
        })
    });
};
wp.hooks.addFilter(
    "blocks.registerBlockType",
    "zr-blocks/attribute/acf-fields",
    setToolbarACFFieldAttributes
);
const withToolbarButton = createHigherOrderComponent((BlockEdit) => {
    return (props) => {
        const [isInputDialogVisible, setIsInputDialogVisible] = useState(false);

        const {
            zr_replace_with_acf_field,
            zr_replace_with_acf_field_value
        } = (props.name == "core/legacy-widget" && props.attributes.instance && props.attributes.instance.hasOwnProperty( "raw" )) ? props.attributes.instance.raw : props.attributes;

        if ( props.name == "core/legacy-widget" && props.attributes.instance && !props.attributes.instance.hasOwnProperty( "raw" ) ) {
            props.attributes.instance.raw = {};
            controls.forEach( control => props.attributes.instance.raw[ control ] = props.attributes[ control ] );
        }

        const ACFInputPopover = () =>
        <Popover
            onFocusOutside={() => {
                setIsInputDialogVisible(false)
            }}
            expandOnMobile={true}
            className={"zr-acf-field-toolbar"}
        >
            <ToggleControl
                label={props.name == "core/button" ? __("Replace button link with ACF value") : __("Replace content with ACF value")}
                checked={zr_replace_with_acf_field_value}
                onChange={selected => {
                    props.setAttributes({
                        zr_replace_with_acf_field_value: selected
                    });
                }}
            />
            {zr_replace_with_acf_field_value && (
            <SelectControl
                label={__(
                    "Select ACF Field"
                )}
                className="app-zr-acf-field"
                value={zr_replace_with_acf_field}
                children={acfFields.map((field) => {
                    return (
                    <optgroup label={field.label} key={field.label}>
                        {field.value.map((subfield) => (
                            <option value={subfield.value} key={subfield.value}>{subfield.label}</option>
                        ))}
                    </optgroup>
                    )
                })}
                onChange={selectedField => {
                    props.setAttributes({
                        zr_replace_with_acf_field: selectedField
                    });
                }}>
            </SelectControl>
            )}
        </Popover>
        // If current block is not allowed
    	if (!enableToolbarOnBlocks.includes(props.name)) {
            return (
                <BlockEdit {...props} />
            );
        }

        return (
            <Fragment>  
                <BlockControls>
                    <ToolbarGroup>
                        <ToolbarButton
                            icon="paperclip"
                            title="ACF Field"
                            onClick={() => {
                                setIsInputDialogVisible(true)
                            }}
                        >
                            {isInputDialogVisible && <ACFInputPopover />}
                        </ToolbarButton>
                    </ToolbarGroup>
                </BlockControls>
                <BlockEdit {...props} />
            </Fragment>
        );
    };
}, "withToolbarButton");
wp.hooks.addFilter(
    "editor.BlockEdit",
    "zr-blocks/acf-fields",
    withToolbarButton
);

const withToolbarButtonProp = createHigherOrderComponent( ( BlockListBlock ) => {
    return ( props ) => {

        // If current block is not allowed
        if ( ! enableToolbarOnBlocks.includes( props.name ) ) {
            return (
                <BlockListBlock { ...props } />
            );
        }
        
        const { attributes } = props;
        const { zr_replace_with_acf_field } = attributes;

        return <BlockListBlock { ...props } />
    };
}, "withToolbarButtonProp" );

wp.hooks.addFilter(
    "editor.BlockListBlock",
    "zr-blocks/acf-fields",
    withToolbarButtonProp
);

const saveToolbarButtonAttribute = ( extraProps, blockType, attributes ) => {
    if ( enableToolbarOnBlocks.includes( blockType.name ) ) {
        /*
        const { paragraphAttribute } = attributes;
        if ( paragraphAttribute && "custom" === paragraphAttribute ) {
            extraProps.className = classnames( extraProps.className, "has-custom-attribute" )
        } */
    }

    return extraProps;

};
wp.hooks.addFilter(
    "blocks.getSaveContent.extraProps",
    "zr-blocks/acf-fields",
    saveToolbarButtonAttribute
);