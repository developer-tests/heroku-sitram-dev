import {registerBlockType} from '@wordpress/blocks';
import {__} from '@wordpress/i18n';
import{ attributes } from './block';
import {yith_icon} from "../../common";
import edit from './edit';
import save from './save';


const blockConfig = {
	title: __( 'Accept | Reject buttons', 'yith-woocommerce-request-a-quote' ),
	description: __( 'Add buttons to accept or reject the quote.', 'yith-woocommerce-request-a-quote' ),
	icon: yith_icon,
	category: 'yith-blocks',
	attributes,
	edit,
	save,
};

registerBlockType(
	'yith/ywraq-quote-buttons',
	{
		...blockConfig,
	}
);
