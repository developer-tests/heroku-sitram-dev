/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { RichText, useBlockProps } from '@wordpress/block-editor';


export default function save( props ) {
	const { attributes } = props;
	const {title, textAlign} = attributes;


	const className = classnames( {
		[ `has-text-align-${ textAlign }` ]: textAlign,
	} );

	const blockProps = useBlockProps.save({ className });
	return (
		<p { ...blockProps }>
			<RichText.Content value={title}/>
		</p>
	);
}
