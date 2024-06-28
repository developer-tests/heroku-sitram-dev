<?php
/**
 * This file belongs to the YIT Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @package yith-woocommerce-gift-cards\plugin-options\
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

$email_settings_url = site_url() . '/wp-admin/admin.php?page=wc-settings&tab=email';

$general_options = array(

	'settings-general' => array(
		/**
		 *
		 * General settings
		 */
		array(
			'name' => esc_html__( 'General settings', 'yith-woocommerce-gift-cards' ),
			'type' => 'title',
		),
		'ywgc_code_pattern'                           => array(
			'id'        => 'ywgc_code_pattern',
			'name'      => esc_html__( 'Gift card code pattern', 'yith-woocommerce-gift-cards' ),
			'desc'      => esc_html__( 'Choose the pattern of new gift cards. If you set ***-*** your cards will have a code like: 1ME-D28.', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'text',
			'default'   => '****-****-****-****',
		),
		'ywgc_template_design'                        => array(
			'name'      => esc_html__( 'Enable the images gallery', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'id'        => 'ywgc_template_design',
			'desc'      => esc_html__( 'Allow users to pick the gift card image from those available in the gallery. Note: images that can be used by customers have to be uploaded through the Media gallery. To make the search easier, you can group images into categories (e.g. Christmas, Easter, Birthday, etc.) through this link: ', 'yith-woocommerce-gift-cards' ) . ' <a href="' . admin_url( 'edit-tags.php?taxonomy=giftcard-category&post_type=attachment' ) . '" title="' . esc_html__( 'Set your gallery categories.', 'yith-woocommerce-gift-cards' ) . '">' . esc_html__( 'Set your template categories', 'yith-woocommerce-gift-cards' ) . '</a>',
			'default'   => 'yes',
		),
		'ywgc_template_design_number_to_show'         => array(
			'id'        => 'ywgc_template_design_number_to_show',
			'name'      => esc_html__( 'How many images to show', 'yith-woocommerce-gift-cards' ),
			'desc'      => esc_html__( 'Set how many gift card images to show on the gift card page.', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'number',
			'min'       => 0,
			'step'      => 1,
			'default'   => '3',
			'deps'      => array(
				'id'    => 'ywgc_template_design',
				'value' => 'yes',
			),
		),
		'ywgc_usage_expiration'                       => array(
			'id'                => 'ywgc_usage_expiration',
			'name'              => __( 'Gift card expiration date', 'yith-woocommerce-gift-cards' ),
			'desc'              => __( 'Set a default expiration for gift cards in months. If the value is zero, your gift cards will never expire.', 'yith-woocommerce-gift-cards' ),
			'type'              => 'yith-field',
			'yith-type'         => 'number',
			'default'           => 0,
			'custom_attributes' => 'placeholder="' . __( 'write expiration date in months', 'yith-woocommerce-gift-cards' ) . '"',

			'min'               => 0,
		),

		array(
			'type' => 'sectionend',
		),

		/**
		 *
		 * E-mail options & customization
		 */

		array(
			'name' => esc_html__( 'Email options', 'yith-woocommerce-gift-cards' ),
			'type' => 'title',
		),
		'ywgc_auto_discount_button_activation'        => array(
			'name'      => esc_html__( 'Show a button in the gift card email', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'id'        => 'ywgc_auto_discount_button_activation',
			'desc'      => esc_html__( 'If enabled, the gift card dispatch email will contain a link to redirect your user to your site in one click.', 'yith-woocommerce-gift-cards' ),
			'default'   => 'yes',
		),
		'ywgc_email_button_label'                     => array(
			'id'        => 'ywgc_email_button_label',
			'name'      => esc_html__( 'Button label', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'text',
			'default'   => esc_html__( 'Apply your gift card code', 'yith-woocommerce-gift-cards' ),
			'deps'      => array(
				'id'    => 'ywgc_auto_discount_button_activation',
				'value' => 'yes',
			),
		),

		'ywgc_shop_logo_on_gift_card'                 => array(
			'name'      => __( 'Add your shop logo on gift cards', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'id'        => 'ywgc_shop_logo_on_gift_card',
			'desc'      => __( 'Set if you want the shop logo to show up on the gift card template sent to the customers. We suggest you keep it disabled if your gift card template image contains your shop logo.', 'yith-woocommerce-gift-cards' ),
			'default'   => 'no',
		),
		'ywgc_shop_logo_url'                          => array(
			'name'      => __( 'Upload your shop logo', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'media',
			'id'        => 'ywgc_shop_logo_url',
			'allow_custom_url'  => false,
			'desc'      => __( 'Upload the logo you want to show in the gift card sent to customers.', 'yith-woocommerce-gift-cards' ),
			//banner 850x300, logo, 100x60
			'deps'      => array(
				'id'    => 'ywgc_shop_logo_on_gift_card',
				'value' => 'yes',
			),
		),

		'ywgc_shop_logo_on_gift_card_after'           => array(
			'name'      => __( 'Add your shop logo after the gift card image', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'id'        => 'ywgc_shop_logo_on_gift_card_after',
			'default'   => 'no',
			'deps'      => array(
				'id'    => 'ywgc_shop_logo_on_gift_card',
				'value' => 'yes',
			),
		),
		'ywgc_shop_logo_after_alignment'              => array(
			'name'      => __( 'Logo alignment', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'radio',
			'id'        => 'ywgc_shop_logo_after_alignment',
			'options'   => array(
				'left'   => __( 'Left', 'yith-woocommerce-gift-cards' ),
				'center' => __( 'Center', 'yith-woocommerce-gift-cards' ),
				'right'  => __( 'Right', 'yith-woocommerce-gift-cards' ),
			),
			'default'   => 'left',
			'deps'      => array(
				'id'    => 'ywgc_shop_logo_on_gift_card_after',
				'value' => 'yes',
			),
		),

		'ywgc_shop_logo_on_gift_card_before'          => array(
			'name'      => __( 'Add your shop logo before the gift card image', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'id'        => 'ywgc_shop_logo_on_gift_card_before',
			'default'   => 'no',
			'deps'      => array(
				'id'    => 'ywgc_shop_logo_on_gift_card',
				'value' => 'yes',
			),
		),
		'ywgc_shop_logo_before_alignment'             => array(
			'name'      => __( 'Logo alignment', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'radio',
			'id'        => 'ywgc_shop_logo_before_alignment',
			'options'   => array(
				'left'   => __( 'Left', 'yith-woocommerce-gift-cards' ),
				'center' => __( 'Center', 'yith-woocommerce-gift-cards' ),
				'right'  => __( 'Right', 'yith-woocommerce-gift-cards' ),
			),
			'default'   => 'left',
			'deps'      => array(
				'id'    => 'ywgc_shop_logo_on_gift_card_before',
				'value' => 'yes',
			),
		),

		array(
			'type' => 'sectionend',
		),
		array(
			'name' => __( 'Recipient & Delivery', 'yith-woocommerce-gift-cards' ),
			'type' => 'title',
		),
		'ywgc_recipient_mandatory'                    => array(
			'name'      => esc_html__( 'Make recipient\'s info mandatory', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'id'        => 'ywgc_recipient_mandatory',
			'desc'      => esc_html__( 'If enabled, the recipient\'s name and email fields will be mandatory in the virtual gift cards.', 'yith-woocommerce-gift-cards' ),
			'default'   => 'yes',
		),
		'ywgc_enable_send_later'                      => array(
			'name'      => __( 'Allow the user to choose the delivery date', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'id'        => 'ywgc_enable_send_later',
			'desc'      => __( 'Allow your customers to choose a delivery date for the virtual gift card (option not available for physical gift cards delivered at home).', 'yith-woocommerce-gift-cards' ),
			'default'   => 'no',
		),
		'ywgc_delivery_hour'                          => array(
			'name'              => __( 'Choose a default delivery time for gift cards', 'yith-woocommerce-gift-cards' ),
			'type'              => 'yith-field',
			'yith-type'         => 'text',
			'id'                => 'ywgc_delivery_hour',
			'desc'              => __( 'Select the time when the gift card will be sent. It is a 24h format, where the minimum time is 00:00 and the maximum is 24:00.', 'yith-woocommerce-gift-cards' ),
			'custom_attributes' => "placeholder='00:00'",
			'default'           => '00:00',
			'deps'              => array(
				'id'    => 'ywgc_enable_send_later',
				'value' => 'yes',
			),
		),
		'ywgc_update_cron_button'                     => array(
			'type' => 'update-cron',
			'desc' => __( 'Click the button to update the Cron Job that delivers the scheduled gift cards.', 'yith-woocommerce-gift-cards' ),
			'id'   => 'ywgc_update_cron_button',
			'deps' => array(
				'id'    => 'ywgc_enable_send_later',
				'value' => 'yes',
			),
		),

		array(
			'type' => 'sectionend',
		),

		array(
			'name' => __( 'Cart & Checkout', 'yith-woocommerce-gift-cards' ),
			'type' => 'title',
		),
		'ywgc_gift_card_form_on_cart_place'           => array(
			'name'      => __( 'Apply gift card position on Cart page', 'yith-woocommerce-gift-cards' ),
			'desc'      => __( 'Choose the position where to display the apply gift card field on the Cart page.' ),
			'type'      => 'yith-field',
			'yith-type' => 'select',
			'class'     => 'wc-enhanced-select',
			'id'        => 'ywgc_gift_card_form_on_cart_place',
			'options'   => array(
				'woocommerce_before_cart'      => __( 'before cart', 'yith-woocommerce-gift-cards' ),
				'woocommerce_after_cart_table' => __( 'after cart', 'yith-woocommerce-gift-cards' ),
			),
			'default'   => 'woocommerce_before_cart',
			'deps'      => array(
				'id'    => 'ywgc_gift_card_form_on_cart',
				'value' => 'yes',
			),
		),
		'ywgc_gift_card_form_on_checkout_place'       => array(
			'name'      => __( 'Apply gift card position on Checkout page', 'yith-woocommerce-gift-cards' ),
			'desc'      => __( 'Choose the position where to display the apply gift card field on the Checkout page.' ),
			'type'      => 'yith-field',
			'yith-type' => 'select',
			'class'     => 'wc-enhanced-select',
			'id'        => 'ywgc_gift_card_form_on_checkout_place',
			'options'   => array(
				'woocommerce_before_checkout_form' => __( 'before checkout form', 'yith-woocommerce-gift-cards' ),
				'woocommerce_after_checkout_form'  => __( 'after checkout form', 'yith-woocommerce-gift-cards' ),
			),
			'default'   => 'woocommerce_before_checkout_form',
			'deps'      => array(
				'id'    => 'ywgc_gift_card_form_on_checkout',
				'value' => 'yes',
			),
		),
		array(
			'type' => 'sectionend',
		),

	),
);

return apply_filters( 'yith_ywgc_general_options_array', $general_options );
