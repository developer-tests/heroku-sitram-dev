<?php

/**
 * Canada Post
 */
class WfTrackingEasyPost extends EasypostWfTrackingAbstract {

	protected function get_api_tracking_status( $shipment_id, $api_uri ) {
		$this->api_tracking         = new ApiTracking();
		$this->api_tracking->status = '';
		$this->api_tracking->error  = '';

		$this->wf_cp_tracking_response( $shipment_id, $api_uri );
		return $this->api_tracking;
	}

	private function wf_cp_tracking_response( $shipment_id, $api_uri ) {
		$api_tracking         = new ApiTracking();
		$api_tracking->status = '';
		$api_tracking->error  = '';

		$carrier = isset( $_GET['carrier'] ) ? sanitize_text_field( $_GET['carrier'] ) : '';

		if ( ! class_exists( 'EasyPost\EasyPost' ) ) {
			require_once plugin_dir_path( dirname( __FILE__ ) ) . '../easypost.php';
		}
		try {
			$easypost_settings = get_option( 'woocommerce_WF_EASYPOST_ID_general_settings', null );

			\EasyPost\EasyPost::setApiKey( $easypost_settings['api_key'] );

			$tracker                    = \EasyPost\Tracker::create(
				array(
					'tracking_code' => $shipment_id,
					'carrier'       => trim( $carrier ),
				)
			);
			$this->api_tracking->status = $tracker->status;

			update_option( $shipment_id, array( 'tracking_url' => $tracker->public_url ) );

			return true;

		} catch ( Exception $e ) {
			$this->api_tracking->error = $e->getMessage();
			return false;
		}
	}
}
