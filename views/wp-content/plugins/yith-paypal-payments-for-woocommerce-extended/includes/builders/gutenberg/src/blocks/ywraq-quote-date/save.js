/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import {RichText, useBlockProps} from '@wordpress/block-editor';


export default function save(props) {
	const {attributes} = props;
	const {
		labelCreatedDate,
		labelExpirationDate,
		labelCurrentDate,
		textAlign,
		dateType
	} = attributes;

	const className = classnames({
		[`has-text-align-${textAlign}`]: textAlign,
	});

	const blockProps = useBlockProps.save({className});
	return (
		<>
			{ dateType === 'created' && (
				<div {...blockProps}><RichText.Content
					tagName={'span'}
					value={labelCreatedDate}
					className='pdf_quote_date_item'
				/> {'{{created_date}}'} </div>)}

			{ dateType === 'expiring' && (
				<div {...blockProps}><RichText.Content
					tagName={'span'}
					value={labelExpirationDate}
					className='pdf_quote_date_item'
				/> {'{{expired_date}}'} </div>)}

			{ dateType === 'current' && (
				<div {...blockProps}><RichText.Content
					tagName={'span'}
					value={labelCurrentDate}
					className='pdf_quote_date_item'

				/> {'{{current_date}}'} </div>)}
		</>

	);
}
