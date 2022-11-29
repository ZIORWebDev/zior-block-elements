const { assign } = lodash;

const { createHigherOrderComponent } = wp.compose;
const { Fragment } = wp.element;
const { InspectorControls, useBlockProps } = wp.blockEditor;
const { Panel, PanelBody, SelectControl, ToggleControl } = wp.components;
const { addFilter, applyFilters } = wp.hooks;
const { __ } = wp.i18n;

const enabledACFControlsOnTheseBlocks = zrBlocks.enabledBlocks;

const hasACFEnabledBlocks = (name) => {
	return applyFilters("zrBlocks.EnabledBlocks", enabledACFControlsOnTheseBlocks).includes(name);
}

const acfFields = [];
wp.apiFetch({ path: "/zior-blocks/v1/acf-fields" }).then(fields =>
	fields.map(function(field) {
		acfFields.push({ label: field.label, value: field.value })
	})
);

/**
 * Add restriction control attributes to block.
 *
 * @param {object} settings Current block settings.
 * @param {string} name Name of block.
 *
 * @returns {object} Modified block settings.
 */
const addACFFieldControlAttributes = (settings, name) => {
	if (!hasACFEnabledBlocks(name)) {
		return settings;
	}

	settings.attributes = assign(settings.attributes, {
		zr_replace_with_acf_field: {
			type: "string",
			default: "zr_replace_with_acf_field"
		},
		zr_replace_with_acf_field_value: {
			type: "boolean",
			default: false
		}
	});
	return settings;
};

addFilter(
	"blocks.registerBlockType",
	"zr-blocks/attribute/acf-fields",
	addACFFieldControlAttributes
);

const withACFFieldControls = createHigherOrderComponent(BlockEdit => {
	return props => {
		if(
			( props.name === "core/legacy-widget" && !hasACFEnabledBlocks(props.attributes.idBase) ) ||
			!hasACFEnabledBlocks(props.name)
		){
			return <BlockEdit {...props} />;
		}

		let controls = [
			"zr_replace_with_acf_field",
			"zr_replace_with_acf_field_value"
		];

		const {
			zr_replace_with_acf_field,
			zr_replace_with_acf_field_value
		} = (props.name == "core/legacy-widget" && props.attributes.instance && props.attributes.instance.hasOwnProperty( "raw" )) ? props.attributes.instance.raw : props.attributes;

		if ( props.name == "core/legacy-widget" && props.attributes.instance && !props.attributes.instance.hasOwnProperty( "raw" ) ) {
			props.attributes.instance.raw = {};
			controls.forEach( control => props.attributes.instance.raw[ control ] = props.attributes[ control ] );
		}

		return (
			<Fragment>
				<BlockEdit {...props} />
				<InspectorControls>
					<Panel className="zr-acf-field-controls">
					<PanelBody
						title={__("ZIOR - ACF Fields")}	
						initialOpen={true}
					>
						<ToggleControl
							label={__("Replace content with ACF value")}
							help={
								!zr_replace_with_acf_field_value
								? __(
									"ACF content disabled",
									"zior-blocks"
								)
								: __(
									"The content of this block will be replaced by ACF field value",
									"zior-blocks"
								)
							}
							checked={zr_replace_with_acf_field_value}
							onChange={selected => {
								props.setAttributes({
									zr_replace_with_acf_field_value: selected
								});

								if ( props.name == "core/legacy-widget" ) {
									props.attributes.instance.raw["zr_replace_with_acf_field_value"] = selected;
								}
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
											<option value={subfield.value}>{subfield.label}</option>
										))}
									</optgroup>
									)
								})}
								onChange={selectedField => {
									props.setAttributes({
										zr_replace_with_acf_field: selectedField
									});

									if ( props.name == "core/legacy-widget" ) {
										props.attributes.instance.raw["zr_replace_with_acf_field"] = selectedField;
									}
								}}>
								</SelectControl>
						)}
					</PanelBody>
					</Panel>
				</InspectorControls>
			</Fragment>
		);
	};
}, "withACFFieldControls");

addFilter(
	"editor.BlockEdit",
	"zr-blocks/with-acf-field-controls",
	withACFFieldControls
);