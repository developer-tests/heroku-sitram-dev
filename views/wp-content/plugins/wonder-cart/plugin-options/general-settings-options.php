<?php

return array(
	'title'       => __( 'Global style options', 'wonder-cart' ),
	'description' => __( 'Customize and apply global style options to all your campaigns', 'wonder-cart' ),
	'sections'    => array(
		'form'          => array(
			'title'       => __( 'Forms', 'wonder-cart' ),
			'description' => __( 'Set colors and style for forms shown in campaigns', 'wonder-cart' ),
			'options'     => array(
				'form_colors' => array(
					'id'     => 'yith_sales_form_colors',
					'name'   => __( 'Colors', 'wonder-cart' ),
					'type'   => 'inline-fields',
					'fields' => array(
						array(
							'id'      => 'background_color',
							'name'    => __( 'Background', 'wonder-cart' ),
							'default' => '#ffffff',
							'style'   => array(
								'size' => 'lg',
							),
							'type'    => 'colorpicker',
						),
						array(
							'id'      => 'border_color',
							'name'    => __( 'Borders', 'wonder-cart' ),
							'default' => '#E0E0E0',
							'style'   => array(
								'size' => 'lg',
							),
							'type'    => 'colorpicker',
						),
						array(
							'id'      => 'text_color',
							'name'    => __( 'Text', 'wonder-cart' ),
							'default' => '#000',
							'style'   => array(
								'size' => 'lg',
							),
							'type'    => 'colorpicker',
						),
						array(
							'id'      => 'check_color',
							'name'    => __( 'Check', 'wonder-cart' ),
							'default' => '#A1C746',
							'style'   => array(
								'size' => 'lg',
							),
							'type'    => 'colorpicker',
						),
					),
				),
				'form_border' => array(
					'id'     => 'yith_sales_form_border',
					'name'   => __( 'Border radius', 'wonder-cart' ),
					'type'   => 'inline-fields',
					'fields' => array(
						array(
							'id'      => 'border_radius',
							'name'    => '',
							'default' => 5,
							'style'   => array(
								'size' => 'lg',
							),
							'type'    => 'number',
							'min'     => 0,
							'step'    => 1,
						),
						array(
							'id'   => 'border_radius_suffix',
							'type' => 'html',
							'html' => 'px',
						),
					),
				),
			),
		),
		'badges'        => array(
			'title'       => __( 'Badges', 'wonder-cart' ),
			'description' => __( 'Set colors and default position of badge shown on products images in campaigns like BOGO, 3x2, etc.', 'wonder-cart' ),
			'options'     => array(
				'badge_colors'   => array(
					'id'     => 'yith_sales_badge_colors',
					'name'   => __( 'Colors', 'wonder-cart' ),
					'type'   => 'inline-fields',
					'fields' => array(
						array(
							'id'      => 'background_color',
							'name'    => __( 'Background', 'wonder-cart' ),
							'default' => '#000',
							'style'   => array(
								'size' => 'lg',
							),
							'type'    => 'colorpicker',
						),
						array(
							'id'      => 'text_color',
							'name'    => __( 'Text', 'wonder-cart' ),
							'default' => '#fff',
							'style'   => array(
								'size' => 'lg',
							),
							'type'    => 'colorpicker',
						),
					),
				),
				'badge_position' => array(
					'id'      => 'yith_sales_badge_position',
					'name'    => __( 'Position', 'wonder-cart' ),
					'type'    => 'select',
					'options' => array(
						array(
							'value' => 'top_left',
							'label' => __( 'Top left', 'wonder-cart' ),
						),
						array(
							'value' => 'top_right',
							'label' => __( 'Top right', 'wonder-cart' ),
						),
						array(
							'value' => 'bottom_left',
							'label' => __( 'Bottom left', 'wonder-cart' ),
						),
						array(
							'value' => 'bottom_right',
							'label' => __( 'Bottom right', 'wonder-cart' ),
						),
					),
					'default' => 'top_right',
				),
			),
		),
		'campaign-labels'        => array(
			'title'       => __( 'Campaigns label in Cart & Checkout', 'wonder-cart' ),
			'description' => __( 'Set colors for the label of a campaign displayed in the cart or during checkout.', 'wonder-cart' ),
			'options'     => array(
				'campaign_label_colors'   => array(
					'id'     => 'yith_sales_campaign_label_colors',
					'name'   => __( 'Colors', 'wonder-cart' ),
					'type'   => 'inline-fields',
					'fields' => array(
						array(
							'id'      => 'background_color',
							'name'    => __( 'Background', 'wonder-cart' ),
							'default' => '#000',
							'style'   => array(
								'size' => 'lg',
							),
							'type'    => 'colorpicker',
						),
						array(
							'id'      => 'text_color',
							'name'    => __( 'Text', 'wonder-cart' ),
							'default' => '#fff',
							'style'   => array(
								'size' => 'lg',
							),
							'type'    => 'colorpicker',
						),
					),
				),

			),
		),
		'modal_window'  => array(
			'title'       => __( 'Modal windows', 'wonder-cart' ),
			'description' => __( 'Set colors and style for promotions show in modal windows', 'wonder-cart' ),
			'options'     => array(
				'modal_colors' => array(
					'id'     => 'yith_sales_modal_colors',
					'name'   => __( 'Colors', 'wonder-cart' ),
					'type'   => 'inline-fields',
					'fields' => array(
						array(
							'id'      => 'overlay_color',
							'name'    => __( 'Overlay', 'wonder-cart' ),
							'default' => 'rgba(34, 59, 80, 0.7)',
							'style'   => array(
								'size' => 'lg',
							),
							'type'    => 'colorpicker',
						),
						array(
							'id'      => 'background_color',
							'name'    => __( 'Background', 'wonder-cart' ),
							'default' => '#ffffff',
							'style'   => array(
								'size' => 'lg',
							),
							'type'    => 'colorpicker',
						),
						array(
							'id'      => 'close_icon_color',
							'name'    => __( 'Close Icon', 'wonder-cart' ),
							'default' => '#BFBFBF',
							'style'   => array(
								'size' => 'lg',
							),
							'type'    => 'colorpicker',
						),
					),
				),
				'modal_border' => array(
					'id'     => 'yith_sales_modal_border',
					'name'   => __( 'Border radius', 'wonder-cart' ),
					'type'   => 'inline-fields',
					'fields' => array(
						array(
							'id'      => 'border_radius',
							'name'    => '',
							'default' => 15,
							'style'   => array(
								'size' => 'lg',
							),
							'type'    => 'number',
							'min'     => 0,
							'step'    => 1,
						),
						array(
							'id'   => 'border_radius_suffix',
							'type' => 'html',
							'html' => 'px',
						),
					),
				),
			),
		),
		'gift_product'  => array(
			'title'       => __( 'Gift product', 'wonder-cart' ),
			'description' => __( 'Set the icon and the background shown in the Gift Product modal window', 'wonder-cart' ),
			'options'     => array(
				'gift_settings' => array(
					'id'     => 'yith_sales_gift_settings',
					'type'   => 'inline-fields',
					'name'   => '',
					'fields' => array(
						array(
							'id'      => 'gift_icon',
							'name'    => __( 'Icon', 'wonder-cart' ),
							'type'    => 'media',
							'style'   => array(
								'allowedTypes' => array( 'image' ),
								'showTabs'     => 'no',
							),
							'default' => get_option( 'yith_sales_default_gift_icon' ),
						),
						array(
							'id'      => 'gift_bg',
							'name'    => __( 'Background', 'wonder-cart' ),
							'type'    => 'media',
							'style'   => array(
								'allowedTypes' => array( 'image' ),
								'showTabs'     => 'no',
							),
							'default' => get_option( 'yith_sales_default_gift_bg_image' ),
						),
					),
				),
			),
		),
		'free_shipping' => array(
			'title'       => __( 'Free shipping', 'wonder-cart' ),
			'options'     => array(
				'free_shipping_settings' => array(
					'id'      => 'yith_sales_free_shipping_icon',
					'name'    => __( 'Icon', 'wonder-cart' ),
					'type'    => 'media',
					'style'   => array(
						'allowedTypes' => array( 'image' ),
						'showTabs'     => 'no',
					),
					'default' => get_option( 'yith_sales_default_free_shipping_icon' ),
					'description' => __( 'Set the banner icon shown in cart when a free shipping campaign is active', 'wonder-cart' ),
				),
				'exclude_other_shipping' => array(
					'id' => 'yith_sales_hide_other_shipping',
					'name'=> __('Hide other shipping methods', 'wonder-cart'),
					'type' => 'onoff',
					'default' => 'off',
					'description' => __('Hide other shipping methods if a free shipping campaign is active', 'wonder-cart'),
				),
			),
		),
		'extra'         => array(
			'title'       => __( 'Extra', 'wonder-cart' ),
			'description' => __( 'Set colors for prices shown in campaigns', 'wonder-cart' ),
			'options'     => array(
				'pricing_colors' => array(
					'id'     => 'yith_sales_price_colors',
					'name'   => __( 'Pricing Colors', 'wonder-cart' ),
					'type'   => 'inline-fields',
					'fields' => array(
						array(
							'id'      => 'regular_color',
							'name'    => __( 'Regular', 'wonder-cart' ),
							'default' => '#949494',
							'style'   => array(
								'size' => 'lg',
							),
							'type'    => 'colorpicker',
						),
						array(
							'id'      => 'sale_color',
							'name'    => __( 'Sale', 'wonder-cart' ),
							'default' => '#000',
							'style'   => array(
								'size' => 'lg',
							),
							'type'    => 'colorpicker',
						),
						array(
							'id'      => 'saving_color',
							'name'    => __( 'Saving', 'wonder-cart' ),
							'default' => '#D10000',
							'style'   => array(
								'size' => 'lg',
							),
							'type'    => 'colorpicker',
						),
						array(
							'id'      => 'free_color',
							'name'    => __( 'Free', 'wonder-cart' ),
							'default' => '#B6A000',
							'style'   => array(
								'size' => 'lg',
							),
							'type'    => 'colorpicker',
						),
					),
				),
			),
		),
		'modal'         => array(
			'title'       => __( 'Modal windows notifications', 'wonder-cart' ),
			'description' => __( 'Choose where to hide the promotional modal windows', 'wonder-cart' ),
			'options'     => array(
				'hide-on-mobile'        => array(
					'id'          => 'yith_sales_hide_modal_on_mobile',
					'name'        => __( 'Hide on mobile devices', 'wonder-cart' ),
					'description' => __( 'Enable to hide all promotional modal windows in mobile devices', 'wonder-cart' ),
					'type'        => 'onoff',
					'default'     => 'yes',
					'style'       => array(
						'color' => 'primary',
					),
				),
				'hide-on-pages'         => array(
					'id'          => 'yith_sales_hide_modal_on_pages',
					'name'        => __( 'Hide on specific pages', 'wonder-cart' ),
					'description' => __( 'Enable to prevent modal window opening in specific pages', 'wonder-cart' ),
					'type'        => 'onoff',
					'default'     => 'no',
				),
				'choose-pages'          => array(
					'id'          => 'yith_sales_page_where_hide_modal',
					'name'        => __( 'Choose pages', 'wonder-cart' ),
					'placeholder' => __( 'Search for pages...', 'wonder-cart' ),
					'type'        => 'multiselect',
					'default'     => array(),
					'options'     => yith_sales_get_pages_options(),
					'style'       => array(
						'size'  => 'md',
						'width' => '400px',
					),
					'deps'        => array(
						'id'    => 'yith_sales_hide_modal_on_pages',
						'value' => 'yes',
						'type'  => 'show',
					),
				),
				'hide-on-product-pages' => array(
					'id'          => 'yith_sales_hide_on_pages',
					'name'        => __( 'Hide on product pages', 'wonder-cart' ),
					'description' => __( 'Enable to prevent modal window opening in product pages', 'wonder-cart' ),
					'type'        => 'onoff',
					'default'     => 'yes',
				),
			),
		),
		'notifications' => array(
			'title'       => __( 'Notifications display rules', 'wonder-cart' ),
			'description' => __( 'Set rules to choose how many promotional notifications to show', 'wonder-cart' ),
			'options'     => array(
				'max-notification-per-page'  => array(
					'id'          => 'yith_sales_max_notification_per_page',
					'name'        => __( 'Max notifications for page', 'wonder-cart' ),
					'description' => __( 'Set the maximum number of promotional notifications to show on the same page', 'wonder-cart' ),
					'type'        => 'number',
					'min'         => 1,
					'step'        => 1,
					'default'     => '5',
				),
				'notification-initial-delay' => array(
					'id'          => 'yith_sales_notification_initial_delay',
					'name'        => __( 'Initial delay', 'wonder-cart' ),
					'description' => __( 'Set how many seconds to wait before to show the first notification to the user', 'wonder-cart' ),
					'type'        => 'inline-fields',
					'fields'      => array(
						array(
							'id'      => 'delay',
							'name'    => '',
							'default' => 3,
							'style'   => array(
								'size' => 'lg',
							),
							'type'    => 'number',
							'min'     => 0,
							'step'    => 1,
						),
						array(
							'id'   => 'suffix',
							'type' => 'html',
							'html' => __( 'seconds', 'wonder-cart' ),
						),
					),
				),

				'hide-notification-after-time'  => array(
					'id'          => 'yith_sales_hide_notification_after_time',
					'name'        => __( 'Hide notifications after a specific time', 'wonder-cart' ),
					'description' => __( 'Enable to automatically hide notifications after a specific time, when the user doesn’t click on closing icon', 'wonder-cart' ),
					'type'        => 'onoff',
					'default'     => 'yes',
				),
				'hide-notification-after'       => array(
					'id'     => 'yith_sales_hide_notification_after',
					'name'   => __( 'Hide notifications after', 'wonder-cart' ),
					'type'   => 'inline-fields',
					'fields' => array(
						array(
							'id'      => 'delay',
							'name'    => '',
							'default' => 15,
							'style'   => array(
								'size' => 'lg',
							),
							'type'    => 'number',
							'min'     => 0,
							'step'    => 1,
						),
						array(
							'id'   => 'suffix',
							'type' => 'html',
							'html' => __( 'seconds', 'wonder-cart' ),
						),
					),
					'deps'   => array(
						'id'    => 'yith_sales_hide_notification_after_time',
						'value' => 'yes',
						'type'  => 'show',
					),
				),
				'notification-time-between'     => array(
					'id'          => 'yith_sales_notification_time_between',
					'name'        => __( 'Time between notifications', 'wonder-cart' ),
					'description' => __( 'Set the time interval between multiple notifications', 'wonder-cart' ),
					'type'        => 'inline-fields',
					'fields'      => array(
						array(
							'id'      => 'time_between',
							'name'    => '',
							'default' => 3,
							'style'   => array(
								'size' => 'lg',
							),
							'type'    => 'number',
							'min'     => 0,
							'step'    => 1,
						),
						array(
							'id'   => 'suffix',
							'type' => 'html',
							'html' => __( 'seconds', 'wonder-cart' ),
						),
					),
					'deps'        => array(
						'id'    => 'yith_sales_hide_notification_after_time',
						'value' => 'yes',
						'type'  => 'show',
					),
				),
				'notification-wait-after-close' => array(
					'id'          => 'yith_sales_notification_wait_after_close',
					'name'        => __( 'If the user closes a notifications, wait', 'wonder-cart' ),
					'description' => __( 'Set the time to wait after a notifications has been closed by an user, before showing another notification.', 'wonder-cart' ),
					'type'        => 'inline-fields',
					'fields'      => array(
						array(
							'id'      => 'delay',
							'title'   => '',
							'default' => 3,
							'style'   => array(
								'size' => 'lg',
							),
							'type'    => 'number',
							'min'     => 0,
							'step'    => 1,
						),
						array(
							'id'   => 'suffix',
							'type' => 'html',
							'html' => __( 'seconds before showing another one', 'wonder-cart' ),
						),
					),
				),
			),
		),
		'top-banner'    => array(
			'title'       => __( 'Top banner display options', 'wonder-cart' ),
			'description' => __( 'Choose where to hide the promotional top banner', 'wonder-cart' ),
			'options'     => array(
				'hide-top-banner-on-mobile'          => array(
					'id'          => 'yith_sales_hide_top_banner_on_mobile',
					'name'        => __( 'Hide on mobile devices', 'wonder-cart' ),
					'description' => __( 'Enable to hide the promotional top banner in mobile devices.', 'wonder-cart' ),
					'type'        => 'onoff',
					'default'     => 'yes',
				),
				'hide-top-banner-on-pages'           => array(
					'id'          => 'yith_sales_hide_top_banner_on_pages',
					'name'        => __( 'Hide on specific pages', 'wonder-cart' ),
					'description' => __( 'Enable to hide the promotional top banner in specific pages.', 'wonder-cart' ),
					'type'        => 'onoff',
					'default'     => 'no',
				),
				'top-banner-choose-pages-where-hide' => array(
					'id'          => 'yith_sales_page_where_hide_top_banner',
					'name'        => __( 'Choose pages', 'wonder-cart' ),
					'placeholder' => __( 'Search for pages...', 'wonder-cart' ),
					'type'        => 'multiselect',
					'default'     => array(),
					'options'     => yith_sales_get_pages_options(),
					'style'       => array(
						'size'  => 'md',
						'width' => '400px',
					),
					'deps'        => array(
						'id'    => 'yith_sales_hide_top_banner_on_pages',
						'value' => 'yes',
						'type'  => 'show',
					),
				),
				'hide-top-banner-on-product-pages'   => array(
					'id'          => 'yith_sales_hide_top_banner_on_product_pages',
					'name'        => __( 'Hide on product pages', 'wonder-cart' ),
					'description' => __( 'Enable to hide the promotional top banner in product pages', 'wonder-cart' ),
					'type'        => 'onoff',
					'default'     => 'yes',
				),
				'more-banner-in-page'                => array(
					'id'          => 'yith_sales_more_banner_in_page',
					'name'        => __( 'More banners in page', 'wonder-cart' ),
					'description' => __( 'Select the banner to show, in case more banner are available', 'wonder-cart' ),
					'type'        => 'radioGroup',
					'options'     => array(
						array(
							'value' => 'random',
							'label' => __( 'Show a random banner', 'wonder-cart' ),
						),
						array(
							'value' => 'latest',
							'label' => __( 'Show the banner of latest campaign added', 'wonder-cart' ),
						),
					),
					'default'     => 'random',
				),
			),
		),
	),
);