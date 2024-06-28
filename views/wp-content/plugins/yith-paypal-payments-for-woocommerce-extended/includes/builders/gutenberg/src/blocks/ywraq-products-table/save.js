/**
 * WordPress dependencies
 */

import {RichText, useBlockProps} from '@wordpress/block-editor';
import classnames from 'classnames';

export default function save(props) {
  const {attributes, style} = props;
  const {
    textAlign,
    fontSize,
    thumbnails,
    productName,
    productNameLabel,
    unitPrice,
    unitPriceLabel,
    quantity,
    quantityLabel,
    productSubtotal,
    productSubtotalLabel,
  } = attributes;
  const {
    titlesFontColor,
    titlesBorderColor,
    titlesFontSize,
    titlesTextTransform,
  } = attributes;
  const {
    itemsFontColor,
    itemsBorderColor,
    itemsFontSize,
    itemsTextTransform,
  } = attributes;
  const className = classnames('ywraq_template', {
    [`has-text-align-${textAlign}`]: textAlign,
    [`has-${fontSize}-font-size`]: fontSize,
  });

  const blockProps = useBlockProps.save(
      {className, style: {borderColor: titlesBorderColor, ...style}});
  const theadBlockProps = useBlockProps.save({
    style: {
      color: titlesFontColor,
      borderColor: titlesBorderColor,
      fontSize: titlesFontSize,
      textTransform: titlesTextTransform,
    },
  });
  const theadBlockPropsNumber = useBlockProps.save({
    style: {
      color: titlesFontColor,
      borderColor: titlesBorderColor,
      fontSize: titlesFontSize,
      textTransform: titlesTextTransform,
      textAlign: 'right',
    },
  });

  return (
      <div className='ywraq_template'>
        <style>{`table.ywraq_template td{ color: ${itemsFontColor};border-bottom-color: ${itemsBorderColor};font-size:${itemsFontSize}px;text-transform: ${itemsTextTransform}}`}</style>
        <table   {...blockProps}>
          <thead >
          <tr>
            {(thumbnails || productName) && <RichText.Content
                tagName={'th'}
                value={productNameLabel}
                className="pdf_product_table__header_item product_name"
                colSpan={(thumbnails && productName) ? 2 : 1}
                {...theadBlockProps}
            />}

            {quantity && <RichText.Content
                tagName={'th'}
                value={quantityLabel}
                className="pdf_product_table__header_item quantity-field number"
                {...theadBlockPropsNumber}
            />}
            {unitPrice && <RichText.Content
                tagName={'th'}
                value={unitPriceLabel}
                className="pdf_product_table__header_item unit_price number"
                {...theadBlockPropsNumber}
            />}
            {productSubtotal && <RichText.Content
                tagName={'th'}
                value={productSubtotalLabel}
                className="pdf_product_table__header_item product_subtotal number"
                {...theadBlockPropsNumber}
            />}
          </tr>
          </thead>
          <tbody>
          {'##table_content'}
          </tbody>

        </table>
      </div>
  );
}
