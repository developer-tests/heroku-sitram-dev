import {__} from '@wordpress/i18n';
import {
	RichText,
	AlignmentControl,
	BlockControls,
	useBlockProps
} from '@wordpress/block-editor';
import classnames from "classnames";


function EditorQuoteNumber(props) {
	const {attributes, setAttributes, style} = props;
	const {title, textAlign} = attributes;
	const blockProps = useBlockProps( {
		className: classnames( 'ywraq-quote-number', {
			[ `has-text-align-${ textAlign }` ]: textAlign,
		} ),
		style,
	} );


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
			<div {...blockProps}>
				<RichText
					tagName={'p'}
					value={title}
					className='pdf_quote_number'
					onChange={(value) => setAttributes({title: value})}
					textAlign={ textAlign }
				/>
			</div>
		</>
	);
}

export default EditorQuoteNumber;