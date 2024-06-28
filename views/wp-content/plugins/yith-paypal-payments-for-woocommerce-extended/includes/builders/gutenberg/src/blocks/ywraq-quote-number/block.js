import {__} from '@wordpress/i18n';

export const attributes = {
	title: {
		type: 'text',
		default: 'Quote #{{quote_number}}'
	},
	"textAlign": {
		"type": "string"
	}
};

export const supports = {

	color: {
		// Disable text color support.
		text: true
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