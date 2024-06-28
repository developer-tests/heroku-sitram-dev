import {__} from '@wordpress/i18n';
import {
  InspectorControls,
  RichText,
  useBlockProps,
  __experimentalPanelColorGradientSettings as PanelColorGradientSettings,
  __experimentalTextTransformControl as  TransformControl
} from '@wordpress/block-editor';
import {
  PanelBody,
  ToggleControl,
  FontSizePicker,
} from '@wordpress/components';

import QuoteItem from '../../components/QuoteItem';
import classnames from 'classnames';
import {
  fontSizes,
  previewQuote,
} from '../../packages/constants';

function EditorTable(props) {
  const {attributes, setAttributes} = props;
  let {style} = props;
  const {
    thumbnails,
    productName,
    productNameLabel,
    unitPrice,
    unitPriceLabel,
    quantity,
    quantityLabel,
    productSubtotal,
    productSubtotalLabel,
    fontSize,
    textAlign,
    productSku
  } = attributes;

  const {titlesFontColor, titlesBorderColor, titlesFontSize, titlesTextTransform} = attributes;
  const {itemsFontColor, itemsBorderColor, itemsFontSize, itemsTextTransform} = attributes;

  const blockProps = useBlockProps({
    className: classnames('ywraq_template', {
      [`has-text-align-${textAlign}`]: textAlign,
      [`has-${fontSize}-font-size`]: fontSize,
    }),
    style: {borderBottomColor: titlesBorderColor, ...style},
  });

  const theadBlockProps = useBlockProps({
    style: {
      color: titlesFontColor,
      borderColor: titlesBorderColor,
      fontSize:titlesFontSize,
      textTransform: titlesTextTransform
    },
  });
  const theadBlockPropsNumber = useBlockProps({
    style: {
      color: titlesFontColor,
      borderColor: titlesBorderColor,
      fontSize: titlesFontSize,
      textTransform: titlesTextTransform,
      textAlign: 'right',
    },
  });





  let columnsNum = 1;
  return (
      <>
        <InspectorControls>
          <PanelBody
              title={__('Product Table settings',
                  'yith-woocommerce-request-a-quote')}
              className="blocks-table-settings"
          >
            <ToggleControl
                label={__('Show product thumbnail',
                    'yith-woocommerce-request-a-quote')}
                checked={thumbnails}
                onChange={(value) => setAttributes({thumbnails: value})}
            />

            <ToggleControl
                label={__('Show product name',
                    'yith-woocommerce-request-a-quote')}
                checked={productName}
                onChange={(value) => setAttributes({productName: value})}
            />
            { productName && <ToggleControl
                label={__('Show product SKU with the product name',
                    'yith-woocommerce-request-a-quote')}
                checked={productSku}
                onChange={(value) => setAttributes({productSku: value})}
            />}

            <ToggleControl
                label={__('Show quantity', 'yith-woocommerce-request-a-quote')}
                checked={quantity}
                onChange={(value) => setAttributes({quantity: value})}
            />
            <ToggleControl
                label={__('Show price',
                    'yith-woocommerce-request-a-quote')}
                checked={unitPrice}
                onChange={(value) => setAttributes({unitPrice: value})}
            />

            <ToggleControl
                label={__('Show total',
                    'yith-woocommerce-request-a-quote')}
                checked={productSubtotal}
                onChange={(value) => setAttributes({productSubtotal: value})}
            />

          </PanelBody>

          <PanelColorGradientSettings
              title={__('Titles', 'yith-woocommerce-request-a-quote')}
              initialOpen={false}
              settings={[
                {
                  label: __('Color', 'yith-woocommerce-request-a-quote'),
                  onColorChange: (color) => setAttributes({titlesFontColor:color}),
                  colorValue: titlesFontColor,
                },
                {
                  label: __('Border color', 'yith-woocommerce-request-a-quote'),
                  onColorChange: (color) => setAttributes({titlesBorderColor:color}),
                  colorValue: titlesBorderColor,
                },
              ]}
          >
            <FontSizePicker
                fontSizes={fontSizes}
                value={titlesFontSize || 20}
                fallbackFontSize={20}
                withSlider={false}
                onChange={(newFontSize) => {
                  setAttributes({titlesFontSize: newFontSize});
                }}
            />
            <TransformControl
                value={titlesTextTransform}
                onChange={(textTransform) => {
                  setAttributes({titlesTextTransform: textTransform});
                }}
            />

          </PanelColorGradientSettings>

          <PanelColorGradientSettings
              title={__('Items', 'yith-woocommerce-request-a-quote')}
              initialOpen={false}
              settings={[
                {
                  label: __('Color', 'yith-woocommerce-request-a-quote'),
                  onColorChange: (color) => setAttributes({itemsFontColor:color}),
                  colorValue: itemsFontColor,
                },
                {
                  label: __('Border color', 'yith-woocommerce-request-a-quote'),
                  onColorChange: (color) => setAttributes({itemsBorderColor:color}),
                  colorValue: itemsBorderColor,
                },
              ]}
          >
            <FontSizePicker
                fontSizes={fontSizes}
                value={itemsFontSize || 20}
                fallbackFontSize={20}
                withSlider={false}
                onChange={(newFontSize) => {
                  setAttributes({itemsFontSize: newFontSize});
                }}
            />
            <TransformControl
                value={itemsTextTransform}
                onChange={(textTransform) => {
                  setAttributes({itemsTextTransform: textTransform});
                }}
            />

          </PanelColorGradientSettings>

        </InspectorControls>
        <div className='ywraq_template-wrapper'>
          <table {...blockProps}>
            <thead>
            <tr>
              {(thumbnails || productName) && columnsNum++ &&
                  <RichText
                      tagName={'th'}
                      value={productNameLabel}
                      className="pdf_product_table__header_item product_name"
                      onChange={(value) => setAttributes(
                          {productNameLabel: value})}
                      colSpan={(thumbnails && productName) ? 2 : 1}
                      {...theadBlockProps}
                  />

              }
              {quantity && columnsNum++ && <RichText
                  tagName={'th'}
                  value={quantityLabel}
                  className="pdf_product_table__header_item quantity-field number"
                  onChange={(value) => setAttributes({quantityLabel: value})}
                  {...theadBlockPropsNumber}
              />}
              {unitPrice && columnsNum++ && <RichText
                  tagName={'th'}
                  value={unitPriceLabel}
                  className="pdf_product_table__header_item unit_price number"
                  onChange={(value) => setAttributes({unitPriceLabel: value})}
                  {...theadBlockPropsNumber}
              />}

              {productSubtotal && columnsNum++ && <RichText
                  tagName={'th'}
                  value={productSubtotalLabel}
                  className="pdf_product_table__header_item product_subtotal number"
                  onChange={(value) => setAttributes(
                      {productSubtotalLabel: value})}
                  {...theadBlockPropsNumber}
              />}
            </tr>
            </thead>
            <tbody>
            {previewQuote.map((item, index) => (
                <QuoteItem key={index} item={item} show={attributes}/>
            ))}


            </tbody>

          </table>
        </div>
      </>
  );
}

export default EditorTable;