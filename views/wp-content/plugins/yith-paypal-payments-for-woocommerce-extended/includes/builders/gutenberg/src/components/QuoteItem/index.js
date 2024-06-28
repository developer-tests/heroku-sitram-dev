import Dinero from 'dinero.js';
import {useBlockProps} from '@wordpress/block-editor';
import {__} from '@wordpress/i18n';

function QuoteItem(props) {
  const {item, show} = props;
  const {
    thumbnails,
    productName,
    productSku,
    unitPrice,
    quantity,
    productSubtotal,
  } = show;
  const {
    itemsFontColor,
    itemsBorderColor,
    itemsFontSize,
    itemsTextTransform,
  } = show;
  const currencySymbol = wc.wcSettings.CURRENCY.symbol;

  const itemStyle = {
    color: itemsFontColor,
    borderColor: itemsBorderColor,
    fontSize: itemsFontSize,
    textTransform: itemsTextTransform,
  };

  const itemsBlockProps = useBlockProps({style: itemStyle});
  const itemsBlockPropsNum = useBlockProps({
    style: {textAlign: 'right', ...itemStyle},
  });

  const adjustPrice = price => parseInt(price * 100);

  const getImage = () => {
    if (item.thumbnail) {
      return <td width="1" className="thumbnail" {...itemsBlockProps}><img
          src={item.thumbnail}/></td>;
    } else {
      return <td width="1" className="thumbnail" {...itemsBlockProps}></td>;
    }
  };

  const getName = () => {
    if (!productSku || item.sku == '' ) {
      return <td className="name" {...itemsBlockProps}>{item.name}</td>;
    } else {
      return <td
          className="name" {...itemsBlockProps}>{item.name}<br/><small>{__(
          'SKU:', 'yith-woocommerce-request-a-quote')} {item.sku}</small></td>;
    }
  };

  const getUnitPrice = () => {
    let price = adjustPrice(item.line_total + item.line_total_tax);
    price = Dinero({amount: parseInt(price), currency: item.currency_code});

    return <td {...itemsBlockPropsNum}>{price.toFormat(
        currencySymbol + '0,0.00')}</td>;
  };

  const getQuantity = () => {
    return <td {...itemsBlockPropsNum}>{item.quantity}</td>;
  };

  const getSubtotal = () => {
    const total = adjustPrice(item.line_total + item.line_total_tax);
    const price = Dinero({amount: total, currency: item.currency_code});
    return <td {...itemsBlockPropsNum}>{price.toFormat(
        currencySymbol + '0,0.00')}</td>;
  };

  return (
      <tr>
        {thumbnails && getImage()}
        {productName && getName()}
        {quantity && getQuantity()}
        {unitPrice && getUnitPrice()}
        {productSubtotal && getSubtotal()}
      </tr>
  );
}

export default QuoteItem;