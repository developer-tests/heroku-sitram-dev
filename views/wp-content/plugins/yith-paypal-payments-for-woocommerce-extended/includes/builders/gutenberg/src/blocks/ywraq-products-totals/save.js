/**
 * WordPress dependencies
 */
import {useBlockProps} from '@wordpress/block-editor';

export default function save(props) {
  const {attributes} = props;
  const {backgroundColor, fontSize, color} = attributes;
  const {subtotalLabelColor, subtotalLabelFontSize} = attributes;
  const {totalColor, totalFontSize} = attributes;

  const blockProps = useBlockProps.save(
      {
        className: 'quote-totals-wrapper',
        style: {
          backgroundColor,
          color
        },
      },
  );
  return (
      <>
        <style>{`.quote-totals td{ font-size:${fontSize} } .quote-totals td.subtotal-label{ color: ${subtotalLabelColor}; font-size:${subtotalLabelFontSize};} .quote-totals .total-row td{ color: ${totalColor}; font-size:${totalFontSize}; font-weight:bold }`}</style>
        <div {...blockProps}>
        <table className='quote-totals' autosize="1"  style="page-break-inside:avoid">
        <tbody>
        {'##table_totals'}
        </tbody>
        </table>
        </div>
      </>

  );
}
