const {addFilter} = wp.hooks;

// Enable spacing control on the following blocks
export const enableMarginControlOnBlocks = [
  'core/columns',
  'yith/ywraq-quote-number',
  'yith/ywraq-quote-date',
  'yith/ywraq-quote-buttons',
  'core/paragraph',
  'yith/ywraq-customer-info'
];

/**
 * Add margin control attribute to block.
 *
 * @param {object} settings Current block settings.
 * @param {string} name Name of block.
 *
 * @returns {object} Modified block settings.
 */
const addMarginControlAttribute = (settings, name) => {
  // Do nothing if it's another block than our defined ones.
  if( name === 'core/column'){
    if (!settings.attributes.border) {
      Object.assign(settings.attributes, {
      border: {
        type: 'object',
        default: {
          top: '0px',
          left: '0px',
          right: '0px',
          bottom: '0px',
        },
      },
      });
    }
    if (!settings.attributes.borderColor) {
      Object.assign(settings.attributes, {
      borderColor: {
        type: 'string',
        default: '#fff',
      },
    });
    }
  }


  if (!enableMarginControlOnBlocks.includes(name)) {
    return settings;
  }


  if (!settings.attributes.margin) {
    Object.assign(settings.attributes, {
    margin: {
      type: 'object',
      default: {
        top: '0px',
        left: '0px',
        right: '0px',
        bottom: '30px',
      },
    },
    });
  }

  if (!settings.attributes.paddingBlock) {
    Object.assign(settings.attributes, {
    paddingBlock: {
      type: 'object',
      default: {
        top: '0px',
        left: '0px',
        right: '0px',
        bottom: '0px',
      },
    },
  });
  }

  return settings;

};
addFilter('blocks.registerBlockType', 'ywpi-extend-blocks/attribute/margin', addMarginControlAttribute);
