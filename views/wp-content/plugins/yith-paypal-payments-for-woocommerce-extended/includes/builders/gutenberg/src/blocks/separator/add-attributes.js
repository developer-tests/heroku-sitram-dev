import assign from 'lodash.assign';
const { addFilter } = wp.hooks;

// Enable spacing control on the following blocks
export const enableWidthControlOnBlocks = [
  //'core/separator',
];


/**
 * Add width control attribute to block.
 *
 * @param {object} settings Current block settings.
 * @param {string} name Name of block.
 *
 * @returns {object} Modified block settings.
 */
const addWidthControlAttribute = ( settings, name ) => {
  // Do nothing if it's another block than our defined ones.
  if ( ! enableWidthControlOnBlocks.includes( name ) ) {
    return settings;
  }

  // Use Lodash's assign to gracefully handle if attributes are undefined
  settings.attributes = assign( settings.attributes, {
    width:{
      type: 'number',
      default: 1,
    }
  } );

  return settings;
};

addFilter( 'blocks.registerBlockType', 'ywpar-extend-blocks/attribute/width', addWidthControlAttribute );