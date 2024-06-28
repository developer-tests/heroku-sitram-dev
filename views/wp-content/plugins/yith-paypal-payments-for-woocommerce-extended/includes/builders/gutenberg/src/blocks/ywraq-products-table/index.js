import {registerBlockType} from '@wordpress/blocks';
import {__} from '@wordpress/i18n';
import{ attributes, supports } from './block';
import {yith_icon} from "../../common";
import edit from './edit';
import save from './save';

const blockConfig = {
	title: __( 'Product Table', 'yith-woocommerce-request-a-quote' ),
	description: __( 'Add the product table inside the template.', 'yith-woocommerce-request-a-quote' ),
	icon: yith_icon,
	category: 'yith-blocks',
	attributes,
	supports,
	edit,
	save,
};

registerBlockType(
	'yith/ywraq-products-table',
	{
		...blockConfig,
	}
);
