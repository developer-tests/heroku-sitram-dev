<?php
/**
 * Logs tab options
 *
 * @package YITH\Booking\Options
 */

defined( 'YITH_WCBK' ) || exit();

$tab_options = array(
	'tools-logs' => array(
		'logs-tab' => array(
			'type'           => 'custom_tab',
			'action'         => 'yith_wcbk_print_logs_tab',
			'show_container' => true,
			'title'          => _x( 'Logs', 'Tab title in plugin settings panel', 'yith-booking-for-woocommerce' ),
		),
	),
);

/**
 * APPLY_FILTERS: yith_wcbk_panel_logs_options
 *
 * Filters the options of the Logs panel page.
 *
 * @param array $options The options.
 *
 * @return array
 */
return apply_filters( 'yith_wcbk_panel_logs_options', $tab_options );
