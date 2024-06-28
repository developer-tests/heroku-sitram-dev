import {__} from '@wordpress/i18n';

export const attributes = {

	labelCreatedDate: {
		type: 'text',
		default: 'Request date: '
	},
	labelExpirationDate: {
		type: 'text',
		default: 'Expiring date: '
	},
	labelCurrentDate: {
		type: 'text',
		default: 'Date: '
	},
	dateType:{
		type: "string",
		default: "current"
	},
	textAlign: {
		type: "string",
		default: "left"
	},
	fontSize: {
		type: 'string',
		default: 'small',
	},
	style: {
		type: 'object',
		default: {
			color: {
				text: '#000',
			}
		}
	}
};

export const supports = {

	spacing: {
		padding: true, // Enable padding UI control.
		margin:true
	},
	color: {
		// Disable text color support.
		text: true,
		link:false
	},
	"typography": {
		"fontSize": true,
		"lineHeight": true,
		"__experimentalFontStyle": false,
		"__experimentalFontWeight": false,
		"__experimentalLetterSpacing": true,
		"__experimentalTextTransform": true,
		"__experimentalDefaultControls": {
			"fontSize": true,
			"fontAppearance": false,
			"textTransform": true
		}
	}
}
