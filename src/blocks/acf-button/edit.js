const { __ } = wp.i18n;
const { serverSideRender } = wp;

const el = wp.element.createElement;
let blockName = "acf-button";

// Build the editor settings.
export default function(props) {
	return [
		el(serverSideRender, {
			block: "zr-blocks/" + blockName,
			attributes: props.attributes
		})
	];
}
