import {__, _x} from '@wordpress/i18n';
import {
  RichText,
  InspectorControls,
  AlignmentControl,
  BlockControls,

  useBlockProps,
} from '@wordpress/block-editor';
import {
  PanelBody,
  RadioControl,
} from '@wordpress/components';
import classnames from 'classnames';

function EditorQuoteDate(props) {
  const {attributes, setAttributes, style} = props;
  const {
    labelCreatedDate,
    labelExpirationDate,
    labelCurrentDate,
    textAlign,
    fontSize,
    dateType,
  } = attributes;
  const blockProps = useBlockProps({
    className: classnames({
      [`has-text-align-${textAlign}`]: textAlign,
      [`has-${fontSize}-font-size`]: fontSize,
    }),
    style,
  });

  return (
      <>
        <BlockControls group="block">
          <AlignmentControl
              value={textAlign}
              onChange={(nextAlign) => {
                setAttributes({textAlign: nextAlign});
              }}
          />
        </BlockControls>
        <InspectorControls>
          <PanelBody
              title={__('Quote Date type',
                  'yith-woocommerce-request-a-quote')}
              className="blocks-quote-date-settings"
          >
            <RadioControl
                label=""
                help={_x('Choose the data information to display.',
                    'Builder pdf template: description option',
                    'yith-woocommerce-request-a-quote')}
                selected={dateType}
                options={[
                  {
                    label: _x('Quote creation date', 'Builder pdf template: option',
                        'yith-woocommerce-request-a-quote'), value: 'created',
                  },
                  {
                    label: _x('Expiring date', 'Builder pdf template: option',
                        'yith-woocommerce-request-a-quote'), value: 'expiring',
                  },
                  {
                    label: _x('Current date', 'Builder pdf template: option',
                        'yith-woocommerce-request-a-quote'), value: 'current',
                  },
                ]}
                onChange={(newAlign) => setAttributes({dateType: newAlign})}
            />
          </PanelBody>
        </InspectorControls>
        <div className="ywraq-quote-date">
          {dateType === 'created' && (
              <div {...blockProps}><RichText
                  tagName={'span'}
                  value={labelCreatedDate}
                  className="pdf_quote_date_item"
                  onChange={(value) => setAttributes({labelCreatedDate: value})}
              />{ywraq_pdf_template.today} </div>)}

          {dateType === 'expiring' && (
              <div {...blockProps}><RichText
                  tagName={'span'}
                  value={labelExpirationDate}
                  className="pdf_quote_date_item"
                  onChange={(value) => setAttributes(
                      {labelExpirationDate: value})}
              />{ywraq_pdf_template.tomorrow}</div>)}
          {dateType === 'current' && (
              <div {...blockProps}><RichText
                  tagName={'span'}
                  value={labelCurrentDate}
                  className="pdf_quote_date_item"
                  onChange={(value) => setAttributes({labelCurrentDate: value})}
              />{ywraq_pdf_template.today}</div>)}
        </div>
      </>
  );
}

export default EditorQuoteDate;