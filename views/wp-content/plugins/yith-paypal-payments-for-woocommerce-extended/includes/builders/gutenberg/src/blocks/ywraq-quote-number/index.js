import {registerBlockType} from '@wordpress/blocks';
import {__} from '@wordpress/i18n';
import{ attributes, supports } from './block';
import {yith_icon} from "../../common";

import edit from './edit';
import save from './save';

const blockConfig = {
	title: __( 'Quote number', 'yith-woocommerce-request-a-quote' ),
	description: __( 'Add the number of the quote. Use the placeholder {{quote_number}} to show the quote number.', 'yith-woocommerce-request-a-quote' ),
	icon: yith_icon,
	category: 'yith-blocks',
	attributes,
	supports,
	edit,
	save,
};

registerBlockType(
	'yith/ywraq-quote-number',
	{
		...blockConfig,
	}
);
