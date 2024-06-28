import assign from 'lodash.assign';
import {enableWidthControlOnBlocks} from './add-attributes';
const { addFilter } = wp.hooks;
/**
 * Add margin style attribute to save element of block.
 *
 * @param {object} saveElementProps Props of save element.
 * @param {Object} blockType Block type information.
 * @param {Object} attributes Attributes of block.
 *
 * @returns {object} Modified props of save element.
 */
const addWidthExtraProps = ( saveElementProps, blockType, attributes ) => {
  // Do nothing if it's another block than our defined ones.
  if ( ! enableWidthControlOnBlocks.includes( blockType.name ) ) {
    return saveElementProps;
  }

  const {style} = saveElementProps;

  assign( style, { 'border-top-width': attributes.width+'px' } );
  assign( saveElementProps, { style: style } );
  return saveElementProps;
};

//addFilter( 'blocks.getSaveContent.extraProps', 'ywpar-extend-blocks/get-save-content/extra-props', addWidthExtraProps );