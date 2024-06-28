import {__} from '@wordpress/i18n';
import Dinero from 'dinero.js';
import {useBlockProps} from '@wordpress/block-editor';

function QuoteTotals(props) {

  const {items, innerStyle} = props;
  const {subtotalLabelColor, subtotalLabelFontSize} = innerStyle;
  const {totalColor, totalFontSize} = innerStyle;
  const currencySymbol = wc.wcSettings.CURRENCY.symbol;
  const currencyCode = wc.wcSettings.CURRENCY.code;
  let totalTax = 0;
  let total = 0;

  const calculateTotals = function (){

    items.map( item => {
      total += item.line_total;
      totalTax += item.line_total_tax;
    });

    total = parseInt( total * 100 );
    totalTax = parseInt( totalTax  * 100 );
  }

  const labelProps = useBlockProps({
    className: 'subtotal-label',
    style: {
      fontSize: subtotalLabelFontSize,
      color: subtotalLabelColor,
    },
  });

  const totalProps = useBlockProps({
    className: 'total',
    style: {
      fontSize: totalFontSize,
      color: totalColor,
      fontWeight: 'bold',
    },
  });

  const totalItemProps = useBlockProps({
    style: {
      fontSize: totalFontSize,
      color: totalColor,
      textAlign: 'right',
      fontWeight: 'bold',
    },
  });


  const getTotal = total => {
    const priceObj = Dinero(
        {amount: total, currency: currencyCode});
    return <td className="unit-price number" {...props}>{priceObj.toFormat(
        currencySymbol + '0,0.00')}</td>;
  };

  calculateTotals();

  return (
      <>
        <tr className="subtotal-row"><td className="subtotal-label" {...labelProps}>{'Subtotal'}</td>{getTotal(total, [])}</tr>
        <tr className="subtotal-row"><td className="subtotal-label" {...labelProps}>{'Taxes'}</td>{getTotal(totalTax, [])}</tr>
        <tr className="subtotal-row"><td className="subtotal-label" {...totalProps}>{'Total'}</td>{getTotal(total+totalTax, totalItemProps)}</tr>
      </>
  );
}

export default QuoteTotals;