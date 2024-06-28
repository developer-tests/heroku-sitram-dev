import classnames from 'classnames';

const {createHigherOrderComponent} = wp.compose;
const {Fragment} = wp.element;
const {InspectorControls} = wp.editor;
const {PanelBody, RangeControl, useBlockProps} = wp.components;
const {addFilter} = wp.hooks;
const {__} = wp.i18n;
import {enableWidthControlOnBlocks} from './add-attributes';

/**
 * Create HOC to add spacing control to inspector controls of block.
 */
const withWidthControl = createHigherOrderComponent((BlockEdit) => {
  return (props) => {
    // Do nothing if it's another block than our defined ones.
    if (!enableWidthControlOnBlocks.includes(props.name)) {
      return (
          <BlockEdit {...props} />
      );
    }

    const {width} = props.attributes;

    if (width) {
      const classes = typeof props.attributes.className !== 'undefined' ? props.attributes.className.split(' ') : [];
      let classList = [];
      for (let i = 0; i < classes.length; i++) {
        if (classes[i].includes('has-width-')) {
          continue;
        }
        classList = [...classList, classes[i]];
      }

      const className = classnames({
        [`has-width-${width}`]: width,
      });
      classList = [...classList, className];
      props.attributes.className = classList.join(' ');
    }
    return (
        <Fragment>
          <BlockEdit {...props} />
          <InspectorControls>
            <PanelBody
                title={__('Additional Settings')}
                initialOpen={true}
            >
              <RangeControl
                  label={__('Height', 'yith-woocommerce-request-a-quote')}
                  value={width}
                  onChange={(value) => {
                    props.setAttributes({
                      width: value,
                    });
                  }}
                  min={1}
                  max={5}
              />

            </PanelBody>
          </InspectorControls>
        </Fragment>
    );
  };
}, 'withWidthControl');

addFilter('editor.BlockEdit', 'ywpar-extend-blocks/with-width-control',
    withWidthControl);