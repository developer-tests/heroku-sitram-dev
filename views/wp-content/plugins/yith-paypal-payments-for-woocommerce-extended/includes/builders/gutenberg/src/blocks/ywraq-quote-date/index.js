import {registerBlockType} from '@wordpress/blocks';
import {__} from '@wordpress/i18n';
import{ attributes, supports } from './block';
import {yith_icon} from "../../common";
import edit from './edit';
import save from './save';


const blockConfig = {
	title: __( 'Date', 'yith-woocommerce-request-a-quote' ),
	description: __( 'Add the information about the date of the quote.', 'yith-woocommerce-request-a-quote' ),
	icon: yith_icon,
	category: 'yith-blocks',
	attributes,
	supports,
	edit,
	save,
};

registerBlockType(
	'yith/ywraq-quote-date',
	{
		...blockConfig,
	}
);
