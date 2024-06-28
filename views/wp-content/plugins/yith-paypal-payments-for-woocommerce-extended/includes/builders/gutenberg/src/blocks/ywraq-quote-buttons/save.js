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
		accept,
		textAlign,
		fontSize,
		buttonColor,
		buttonBackgroundColor,
		buttonTextTransform,
		buttonBorderRadius
	} = attributes;

	let {
		acceptLabel,
		rejectLabel,
	} = attributes;

	const wrapperClass = `ywraq-wrapper-buttons has-text-align-${textAlign}`;
	const blockProps = useBlockProps.save({
		style: {textAlign:textAlign, color:buttonColor, textTransform: buttonTextTransform, backgroundColor:buttonBackgroundColor, fontSize:fontSize, borderRadius:buttonBorderRadius},
	});

	acceptLabel = `<a href="{{ywraq_accept_quote_url}}" style="color:${buttonColor}">${acceptLabel}</a>`;
	rejectLabel = `<a href="{{ywraq_reject_quote_url}}" style="color:${buttonColor}">${rejectLabel}</a>`;
	return (
		<>

			<div className={wrapperClass} >
			{accept && (
				<RichText.Content
					tagName={'p'}
					value={acceptLabel}
					className='accept-quote'
					{...blockProps}
				/>)}

			{!accept && (
				<RichText.Content
					tagName={'p'}
					value={rejectLabel}
					className='reject-quote'
					{...blockProps}
				/>)}
			</div>

		</>

	);
}
