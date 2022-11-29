const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;

// Import block editor settings
import EditBlock from "./edit";
import icon from "./icon";

let blockName = "acf-button";

// Register the block.
registerBlockType("zr-blocks/" + blockName, {
    title: zrBlocks.blocks[blockName].labels.title,
    description: zrBlocks.blocks[blockName].labels.description,
    icon,
    category: "zr-blocks",
    keywords: zrBlocks.blocks[blockName].labels.keywords,
    attributes: zrBlocks.blocks[blockName].attributes,

    edit: EditBlock,

    save() {
        return null;
    }
}); 