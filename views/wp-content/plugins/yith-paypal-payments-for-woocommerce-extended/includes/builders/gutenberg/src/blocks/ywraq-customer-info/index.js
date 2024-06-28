import {registerBlockType} from '@wordpress/blocks';
import {__} from '@wordpress/i18n';
import{ attributes, supports, placeholders } from './block';
import {yith_icon} from "../../common";
import edit from './edit';
import save from './save';


const blockConfig = {
	title: __( 'Customer info', 'yith-woocommerce-request-a-quote' ),
	description: __( 'Add the information about the customer who requested the quote. Placeholders available:', 'yith-woocommerce-request-a-quote' ) + ' ' + placeholders,
	icon: yith_icon,
	category: 'yith-blocks',
	attributes,
	supports,
	edit,
	save,
};

registerBlockType(
	'yith/ywraq-customer-info',
	{
		...blockConfig,
	}
);
