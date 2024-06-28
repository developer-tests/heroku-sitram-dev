import {__} from '@wordpress/i18n';

export const attributes = {
	title: {
		type: 'text',
		default: '{{billing_first_name}} {{billing_last_name}}<br>{{billing_address_1}}<br>{{billing_postcode}} {{billing_city}}<br>' +
			'{{billing_country}}<br>SSN: {{billing_vat_ssn}}<br>VAT: {{billing_vat_number}}<br>{{billing_phone}}<br>{{billing_email}}'
	},

	"textAlign": {
		"type": "string",
		"default": "right"
	},

	fontSize: {
		type: 'string',
		default: 'small',
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
		"__experimentalFontStyle": true,
		"__experimentalFontWeight": true,
		"__experimentalLetterSpacing": true,
		"__experimentalDefaultControls": {
			"fontSize": true,
			"fontAppearance": true,
			"textTransform": true
		}
	}
}


export const placeholders = '{{'+ywraq_pdf_template.customer_info_placeholders.join('}}, {{') + '}}';
