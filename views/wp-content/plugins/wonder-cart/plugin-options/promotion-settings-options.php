<?php

return array(
	'title'       => __( 'Promotion options', 'wonder-cart' ),
	'description' => __( 'Global options related to campaign’s promotions', 'wonder-cart' ),
	'sections'    => array(
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