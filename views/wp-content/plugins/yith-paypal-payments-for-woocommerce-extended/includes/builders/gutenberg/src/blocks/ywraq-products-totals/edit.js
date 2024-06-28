import {__} from '@wordpress/i18n';

import QuoteTotals from '../../components/QuoteTotals';
import {
  InspectorControls,
  useBlockProps,
  __experimentalPanelColorGradientSettings as PanelColorGradientSettings,
} from '@wordpress/block-editor';
import {
  FontSizePicker,
} from '@wordpress/components';
import {fontSizes, previewQuote} from '../../packages/constants';

function EditorProductTotals(props) {
  const {attributes, setAttributes} = props;
  const {backgroundColor, fontSize, color} = attributes;
  const {subtotalLabelColor, subtotalLabelFontSize} = attributes;
  const {totalColor, totalFontSize} = attributes;

  const tableProps = useBlockProps({
    className: 'quote-totals-wrapper',
    style: {
      backgroundColor,
      fontSize,
      color
    },
  });

  return (
      <>
        <InspectorControls>
          <PanelColorGradientSettings
              title={__('Table settings', 'yith-woocommerce-request-a-quote')}
              initialOpen={true}
              settings={[
                {
                  label: __('Color',
                      'yith-woocommerce-request-a-quote'),
                  onColorChange: (color) => setAttributes(
                      {color}),
                  colorValue: color,
                },
                ,
                {
                  label: __('Background color',
                      'yith-woocommerce-request-a-quote'),
                  onColorChange: (color) => setAttributes(
                      {backgroundColor: color}),
                  colorValue: backgroundColor,
                },

              ]}
          >
            <FontSizePicker
                fontSizes={fontSizes}
                value={fontSize || 14}
                fallbackFontSize={14}
                withSlider={false}
                onChange={(newFontSize) => {
                  setAttributes({fontSize: newFontSize});
                }}
            />
          </PanelColorGradientSettings>
          <PanelColorGradientSettings
              title={__('Subtotal labels', 'yith-woocommerce-request-a-quote')}
              initialOpen={true}
              settings={[
                {
                  label: __('Color', 'yith-woocommerce-request-a-quote'),
                  onColorChange: (color) => setAttributes(
                      {subtotalLabelColor: color}),
                  colorValue: subtotalLabelColor,
                },
              ]}
          >
            <FontSizePicker
                fontSizes={fontSizes}
                value={subtotalLabelFontSize || 16}
                fallbackFontSize={16}
                withSlider={false}
                onChange={(newFontSize) => {
                  setAttributes({subtotalLabelFontSize: newFontSize});
                }}
            />
          </PanelColorGradientSettings>
          <PanelColorGradientSettings
              title={__('Total settings', 'yith-woocommerce-request-a-quote')}
              initialOpen={true}
              settings={[
                {
                  label: __('Color', 'yith-woocommerce-request-a-quote'),
                  onColorChange: (color) => setAttributes({totalColor: color}),
                  colorValue: totalColor,
                },
              ]}
          >
            <FontSizePicker
                fontSizes={fontSizes}
                value={totalFontSize || 18}
                fallbackFontSize={18}
                withSlider={false}
                onChange={(newFontSize) => {
                  setAttributes({totalFontSize: newFontSize});
                }}
            />
          </PanelColorGradientSettings>
        </InspectorControls>
        <div {...tableProps}>
        <table width="100%" className='quote-totals'>
          <tbody>
          <QuoteTotals items={previewQuote} innerStyle={attributes}/>
          </tbody>
        </table>
        </div>
      </>
  );
}

export default EditorProductTotals;