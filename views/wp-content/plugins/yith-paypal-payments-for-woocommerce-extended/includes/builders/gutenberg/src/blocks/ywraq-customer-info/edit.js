import {
	RichText,
	AlignmentControl,
	BlockControls,
	useBlockProps
} from '@wordpress/block-editor';
import classnames from "classnames";


function EditorCustomerInfo(props) {
	const {attributes, setAttributes, style} = props;
	const {title, textAlign} = attributes;
	const blockProps = useBlockProps( {
		className: classnames( {
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

			<div className="ywpi-customer-info">
				<RichText
					tagName={'p'}
					value={title}
					className='pdf-customer-info'
					onChange={(value) => setAttributes({title: value})}
					textAlign={ textAlign }
					{...blockProps}
				/>
			</div>
		</>
	);
}

export default EditorCustomerInfo;