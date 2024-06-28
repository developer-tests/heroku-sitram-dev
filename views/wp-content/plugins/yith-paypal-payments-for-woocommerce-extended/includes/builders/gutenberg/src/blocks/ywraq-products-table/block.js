import {__} from '@wordpress/i18n';

export const attributes = {
	thumbnails: {
		type: 'boolean',
		default: true
	},
	productName: {
		type: 'boolean',
		default: true
	},
	productSku: {
		type: 'boolean',
		default: true
	},
	productNameLabel: {
		type: 'text',
		default: 'Product'
	},
	unitPrice: {
		type: 'boolean',
		default: true
	},
	unitPriceLabel: {
		type: 'text',
		default: 'Price'
	},
	quantity: {
		type: 'boolean',
		default: true
	},
	quantityLabel: {
		type: 'text',
		default: 'Qty'
	},
	productSubtotal: {
		type: 'boolean',
		default: true
	},
	productSubtotalLabel: {
		type: 'text',
		default: 'Total'
	},
	textAlign: {
		type: "string",
		default: "left"
	},
	fontSize: {
		type: 'string',
		default: 'small',
	},
	titlesFontSize: {
		type: 'number',
		default: 13,
	},
	titlesFontColor: {
		type: 'string',
		default: '#2e2e2e',
	},
	titlesTextTransform: {
		type: 'string',
		default: 'none',
	},
	titlesBorderColor: {
		type: 'string',
		default: '#cccccc'
	},
	itemsFontSize: {
		type: 'number',
		default: 13,
	},
	itemsFontColor: {
		type: 'string',
		default: '#000000',
	},
	itemsTextTransform: {
		type: 'string',
		default: 'none',
	},
	itemsBorderColor: {
		type: 'string',
		default: '#ffffff'
	},
};


export const supports = {
	"anchor": true,
	spacing: {
		padding: true, // Enable padding UI control.
	},

}