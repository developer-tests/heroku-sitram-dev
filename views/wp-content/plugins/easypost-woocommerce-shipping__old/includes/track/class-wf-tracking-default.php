<?php

/**
 * Default
 */
class WfTrackingDefault extends EasypostWfTrackingAbstract {
	protected function get_api_tracking_status( $shipment_id, $api_uri ) {
		return new ApiTracking(); }
}
