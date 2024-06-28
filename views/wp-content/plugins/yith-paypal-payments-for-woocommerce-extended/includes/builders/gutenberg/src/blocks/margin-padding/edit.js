const {createHigherOrderComponent} = wp.compose;
const {Fragment} = wp.element;
import {InspectorControls} from '@wordpress/block-editor';
import {
  PanelBody,
  __experimentalBoxControl as BoxControl,
  __experimentalUseCustomUnits as useCustomUnits,
} from '@wordpress/components';

const {addFilter} = wp.hooks;
const {__} = wp.i18n;
import {enableMarginControlOnBlocks} from './add-attributes';

/**
 * Create HOC to add margin control to inspector controls of block.
 */
const withMarginControl = createHigherOrderComponent((BlockEdit) => {
  return (props) => {
    // Do nothing if it's another block than our defined ones.
    if (!enableMarginControlOnBlocks.includes(props.name)) {
      return (
          <BlockEdit {...props} />
      );
    }

    const units = useCustomUnits({
      availableUnits: [
        'px',
      ],
    });
    const {attributes, setAttributes} = props;
    const {margin, paddingBlock} = attributes;

    let style = '';
    if (paddingBlock) {
      style = `#block-${props.clientId} { padding-top: ${paddingBlock.top};padding-bottom: ${paddingBlock.bottom};padding-left: ${paddingBlock.left};padding-right: ${paddingBlock.right};}`;
    }
    if (margin) {
      style += ` #block-${props.clientId} { margin-top: ${margin.top};margin-bottom: ${margin.bottom};margin-left: ${margin.left};margin-right: ${margin.right};}`;
    }

    return (
        <Fragment>
          <style>{style}</style>
          <BlockEdit {...props}/>

          <InspectorControls>
            <PanelBody
                title={__('Dimensions', 'yith-woocommerce-request-a-quote')}
                initialOpen={true}
            >
              <BoxControl
                  values={margin}
                  label={__('Margin', 'yith-woocommerce-request-a-quote')}
                  units={units}
                  allowReset={false}
                  onChange={(nextValues) => setAttributes(
                      {margin: nextValues})}
                  splitOnAxis={false}
              />
              <BoxControl
                  values={paddingBlock}
                  label={__('Padding', 'yith-woocommerce-request-a-quote')}
                  units={units}
                  allowReset={false}
                  onChange={(nextValues) => setAttributes(
                      {paddingBlock: nextValues})}
                  splitOnAxis={false}
              />
            </PanelBody>
          </InspectorControls>
        </Fragment>
    );
  };
}, 'withMarginControl');

addFilter('editor.BlockEdit', 'ywpar-extend-blocks/with-margin-control',
    withMarginControl);