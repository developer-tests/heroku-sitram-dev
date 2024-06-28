import assign from 'lodash.assign';
import {enableMarginControlOnBlocks} from './add-attributes';

const {addFilter} = wp.hooks;
/**
 * Add margin style attribute to save element of block.
 *
 * @param {object} saveElementProps Props of save element.
 * @param {Object} blockType Block type information.
 * @param {Object} attributes Attributes of block.
 *
 * @returns {object} Modified props of save element.
 */
const addMarginExtraProps = (saveElementProps, blockType, attributes) => {
  // Do nothing if it's another block than our defined ones.
  if (!enableMarginControlOnBlocks.includes(blockType.name)) {
    return saveElementProps;
  }

  const {style} = saveElementProps;
  const {margin, paddingBlock} = attributes;

  if (margin) {
    assign(style, {
      marginTop: margin.top,
      marginBottom: margin.bottom,
      marginLeft: margin.left,
      marginRight: margin.right,
    });
  }
  if (paddingBlock) {
    assign(style, {
      paddingTop: paddingBlock.top,
      paddingBottom: paddingBlock.bottom,
      paddingLeft: paddingBlock.left,
      paddingRight: paddingBlock.right,
    });
  }
  saveElementProps.style = style;

  return saveElementProps;
};

addFilter('blocks.getSaveContent.extraProps',
    'ywpar-extend-blocks/get-save-content/extra-props', addMarginExtraProps);