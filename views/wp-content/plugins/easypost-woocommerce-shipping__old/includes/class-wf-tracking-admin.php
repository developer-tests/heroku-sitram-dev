<?php

class WF_Tracking_Admin_EasyPost {

	const SHIPPING_METHOD_DISPLAY = 'Tracking';
	const TRACKING_TITLE_DISPLAY  = 'EasyPost Shipment Tracking';

	const TRACK_SHIPMENT_KEY   = 'wf_easypost_shipment'; // Note: If this key is getting changed, do the same change in JS code below.
	const SHIPMENT_SOURCE_KEY  = 'wf_easypost_shipment_source';
	const SHIPMENT_RESULT_KEY  = 'wf_easypost_shipment_result';
	const TRACKING_MESSAGE_KEY = 'wfeasyposttrackingmsg';
	const TRACKING_METABOX_KEY = 'WF_Tracking_Metabox_EasyPost';

	private function wf_init() {
		if ( ! class_exists( 'EasypostWfTrackingFactory' ) ) {
			include_once 'track/class-wf-tracking-factory.php';
		}
		if ( ! class_exists( 'EasypostWfTrackingUtil' ) ) {
			include_once 'track/class-wf-tracking-util.php';
		}

		// Sorted tracking data.
		$this->tracking_data = EasypostWfTrackingUtil::load_tracking_data( true );
	}

	public function __construct() {
		$this->wf_init();

		if ( is_admin() ) {
			add_action( 'add_meta_boxes', array( $this, 'wf_add_tracking_metabox' ), 15 );
			add_action( 'admin_notices', array( $this, 'wf_admin_notice' ), 15 );

			if ( isset( $_GET[ self::TRACK_SHIPMENT_KEY ] ) ) {
				add_action( 'init', array( $this, 'wf_display_admin_track_shipment' ), 15 );
			}
		}

		// Shipment Tracking - Customer Order Details Page.
		add_action( 'woocommerce_view_order', array( $this, 'wf_display_tracking_info_for_customer' ), 6 );
		add_action( 'woocommerce_view_order', array( $this, 'wf_display_tracking_api_info_for_customer' ), 20 );
		add_action( 'woocommerce_email_order_meta', array( $this, 'wf_add_tracking_info_to_email' ), 20 );
	}

	public function wf_add_tracking_info_to_email( $order, $sent_to_admin = false, $plain_text = false ) {
				$order_id      = ( WC()->version < '2.7.0' ) ? $order->id : $order->get_id();
		$shipment_result_array = get_post_meta( $order_id, self::SHIPMENT_RESULT_KEY, true );

		if ( ! empty( $shipment_result_array ) ) {
			echo '<h3>' . esc_attr( 'Shipping Detail', 'wf-easypost' ) . '</h3>';
			$shipment_source_data = $this->get_shipment_source_data( $order_id );
			$order_notice         = EasypostWfTrackingUtil::get_shipment_display_message( $shipment_result_array, $shipment_source_data );
			echo '<p>' . wp_kses_post( $order_notice ) . '</p></br>';
		}
	}

	public function wf_display_tracking_info_for_customer( $order_id ) {

		$shipment_result_array = get_post_meta( $order_id, self::SHIPMENT_RESULT_KEY, true );

		if ( ! empty( $shipment_result_array ) ) {
			// Note: There is a bug in wc_add_notice which gives inconstancy while displaying messages.
			// Uncomment after it gets resolved.
			// $this->display_notice_message( $order_notice );
			$shipment_source_data = $this->get_shipment_source_data( $order_id );
			$order_notice         = EasypostWfTrackingUtil::get_shipment_display_message( $shipment_result_array, $shipment_source_data );
			echo wp_kses_post( $order_notice );
		}
	}

	public function wf_display_tracking_api_info_for_customer( $order_id ) {
		$turn_off_api = get_option( EasypostWfTrackingUtil::TRACKING_SETTINGS_TAB_KEY . EasypostWfTrackingUtil::TRACKING_TURN_OFF_API_KEY );
		if ( 'yes' === $turn_off_api ) {
			return;
		}

		$shipment_result_array = get_post_meta( $order_id, self::SHIPMENT_RESULT_KEY, true );

		if ( ! empty( $shipment_result_array ) ) {
			if ( ! empty( $shipment_result_array['tracking_info_api'] ) ) {
				$this->display_api_message_table( $shipment_result_array['tracking_info_api'] );
			}
		}
	}

	public function display_api_message_table( $tracking_info_api_array ) {

		echo '<h3>' . esc_attr( self::TRACKING_TITLE_DISPLAY, 'wf-easypost' ) . '</h3>';
		echo '<table class="shop_table wooforce_tracking_details">
			<thead>
				<tr>
					<th class="product-name">' . esc_attr( 'Shipment ID', 'wf-easypost' ) . '<br/>(' . esc_attr( 'Follow link for detailed status.', 'wf-easypost' ) . ')</th>
					<th class="product-total">' . esc_attr( 'Status', 'wf-easypost' ) . '</th>
				</tr>
			</thead>
			<tfoot>';

		foreach ( $tracking_info_api_array as $tracking_info_api ) {
			echo '<tr>';
			echo '<th scope="row"><a href="' . esc_attr( $tracking_info_api['tracking_link'] ) . '" target="_blank">' . esc_attr( $tracking_info_api['tracking_id'] ) . '</a></th>';
			if ( '' === $tracking_info_api['api_tracking_status'] ) {
				$message = esc_attr( 'Unable to update real time status at this point of time. Please follow the link on shipment id to check status.', 'wf-easypost' );
			} else {
				$message = $tracking_info_api['api_tracking_status'];
			}
			echo '<td><span>' . esc_attr( $message, 'wf-easypost' ) . '</span></td>';
			echo '</tr>';
		}
		echo '</tfoot>
		</table>';
	}

	public function display_notice_message( $message, $type = 'notice' ) {
		if ( version_compare( WOOCOMMERCE_VERSION, '2.1', '>=' ) ) {
			wc_add_notice( $message, $type );
		} else {
			global $woocommerce;
			$woocommerce->add_message( $message );
		}
	}

	public function wf_admin_notice() {
		global $pagenow;
		global $post;

		if ( ! isset( $_GET[ self::TRACKING_MESSAGE_KEY ] ) && empty( $_GET[ self::TRACKING_MESSAGE_KEY ] ) ) {
			return;
		}

		$wftrackingmsg = sanitize_text_field( $_GET[ self::TRACKING_MESSAGE_KEY ] );

		switch ( $wftrackingmsg ) {
			case '0':
				echo '<div class="error"><p>' . esc_attr( self::SHIPPING_METHOD_DISPLAY ) . ': ' . esc_attr( 'Sorry, Unable to proceed.', 'wf-easypost' ) . '</p></div>';
				break;
			case '4':
				echo '<div class="error"><p>' . esc_attr( self::SHIPPING_METHOD_DISPLAY ) . ': ' . esc_attr( 'Unable to track the shipment. Please cross check shipment id or try after some time.', 'wf-easypost' ) . '</p></div>';
				break;
			case '5':
				$wftrackingmsg = get_post_meta( $post->ID, self::TRACKING_MESSAGE_KEY, true );
				if ( '' !== trim( $wftrackingmsg ) ) {
					echo '<div class="updated"><p>' . wp_kses_post( $wftrackingmsg, 'wf-easypost' ) . '</p></div>';
				}
				break;
			case '6':
				echo '<div class="updated"><p>' . esc_attr( 'Tracking is unset.', 'wf-easypost' ) . '</p></div>';
				break;
			case '7':
				echo '<div class="updated"><p>' . esc_attr( 'Tracking Data is reset to default.', 'wf-easypost' ) . '</p></div>';
				break;
			default:
				break;
		}
	}

	public function wf_add_tracking_metabox() {

		global $post;
		if ( ! $post ) {
			return;
		}

		if ( ! in_array( $post->post_type, array( 'shop_order' ) ) ) {
			return;
		}

		$order = $this->wf_load_order( $post->ID );
		if ( ! $order ) {
			return;
		}

		// Shipping method is available.
		add_meta_box( self::TRACKING_METABOX_KEY, esc_attr( self::TRACKING_TITLE_DISPLAY, 'wf-easypost' ), array( $this, 'wf_tracking_metabox_content' ), 'shop_order', 'side', 'default' );
	}

	public function get_shipment_source_data( $post_id ) {
		$shipment_source_data = get_post_meta( $post_id, self::SHIPMENT_SOURCE_KEY, true );

		if ( empty( $shipment_source_data ) || ! is_array( $shipment_source_data ) ) {
			$shipment_source_data                     = array();
			$shipment_source_data['shipment_id_cs']   = '';
			$shipment_source_data['shipping_service'] = '';
			$shipment_source_data['order_date']       = '';
		}
		return $shipment_source_data;
	}

	public function wf_tracking_metabox_content() {
		global $post;
		$shipmentId = '';

		$order        = $this->wf_load_order( $post->ID );
		$tracking_url = admin_url( '/?post=' . ( $post->ID ) );

		$shipment_source_data = $this->get_shipment_source_data( $post->ID );
		?>
		<ul class="order_actions submitbox">
			<li id="actions" class="wide">
				<select name="shipping_service_easypost" id="shipping_service_easypost">
		<?php
				echo "<option value=''>" . esc_attr( 'None', 'wf-easypost' ) . '</option>';
				echo '<option value="ups" ' . selected( $shipment_source_data['shipping_service'], 'ups' ) . ' >' . esc_attr( 'UPS', 'wf-easypost' ) . '</option>';
				echo '<option value="upssurepost"' . selected( $shipment_source_data['shipping_service'], 'upssurepost' ) . ' >' . esc_attr( 'UPSSurePost', 'wf-easypost' ) . '</option>';
				echo '<option value="upsdap"' . selected( $shipment_source_data['shipping_service'], 'upsdap' ) . ' >' . esc_attr( 'UPSDAP', 'wf-easypost' ) . '</option>';
				echo '<option value="fedex"' . selected( $shipment_source_data['shipping_service'], 'fedex' ) . ' >' . esc_attr( 'FedEx', 'wf-easypost' ) . '</option>';
				echo '<option value="united-states-postal-service-usps"' . selected( $shipment_source_data['shipping_service'], 'united-states-postal-service-usps' ) . ' >' . esc_attr( 'USPS', 'wf-easypost' ) . '</option>';
				echo '<option value="canada-post"' . selected( $shipment_source_data['shipping_service'], 'canada-post' ) . ' >' . esc_attr( 'Canada Post', 'wf-easypost' ) . '</option>';
		?>
				</select><br>
				<strong><?php esc_attr( 'Enter Tracking IDs', 'wf-easypost' ); ?></strong>
				<img class="help_tip" style="float:none;" data-tip="<?php esc_attr( 'Comma separated, in case of multiple shipment ids for this order.', 'wf-easypost' ); ?>" src="<?php echo esc_attr( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" /><br>
				<textarea id="tracking_easypost_shipment_ids" class="input-text" type="text" name="tracking_easypost_shipment_ids" ><?php echo esc_attr( $shipment_source_data['shipment_id_cs'] ); ?></textarea><br>
				<strong>Shipment Date</strong>
				<img class="help_tip" style="float:none;" data-tip="<?php esc_attr( 'This field is Optional.', 'wf-easypost' ); ?>" src="<?php echo esc_attr( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" /><br>
				<input type="text" id="order_date_easypost" class="date-picker" value="<?php echo esc_attr( $shipment_source_data['order_date'] ); ?>"></p>
			</li>
			<li id="" class="wide">
				<a class="button button-primary woocommerce_shipment_easypost_tracking tips" href="<?php echo esc_attr( $tracking_url ); ?>" data-tip="<?php echo esc_attr( 'Save/Show Tracking Info', 'wf-easypost' ); ?>"><?php echo esc_attr( 'Save/Show Tracking Info', 'wf-easypost' ); ?></a>
			</li>
		</ul>
		<script>
			jQuery(document).ready(function($) {
				$( "date-picker" ).datepicker();
			});
			
			jQuery("a.woocommerce_shipment_easypost_tracking").on("click", function(e) {
				e.preventDefault();
				console.log(this.href + '&wf_easypost_shipment=' + jQuery('#tracking_easypost_shipment_ids').val().replace(/ /g,'')+'&shipping_service=easy-post&carrier='+ jQuery( "#shipping_service_easypost" ).val()+'&order_date='+ jQuery( "#order_date_easypost" ).val());
			   location.href = this.href + '&wf_easypost_shipment=' + jQuery('#tracking_easypost_shipment_ids').val().replace(/ /g,'')+'&shipping_service='+ jQuery( "#shipping_service_easypost" ).val()+'&carrier='+ jQuery( "#shipping_service_easypost" ).val()+'&order_date='+ jQuery( "#order_date_easypost" ).val();
			   return false;
			});
		</script>
		<?php
	}

	public function wf_display_admin_track_shipment() {
		if ( ! $this->wf_user_check() ) {
			esc_attr( "You don't have admin privileges to view this page.", 'wf-easypost' );
			exit;
		}

		$post_id          = isset( $_GET['post'] ) ? map_deep( wp_unslash( $_GET['post'] ), 'sanitize_text_field' ) : '';
		$shipment_id_cs   = isset( $_GET[ self::TRACK_SHIPMENT_KEY ] ) ? sanitize_text_field( $_GET[ self::TRACK_SHIPMENT_KEY ] ) : '';
		$shipping_service = isset( $_GET['shipping_service'] ) ? sanitize_text_field( $_GET['shipping_service'] ) : '';
		$order_date       = isset( $_GET['order_date'] ) ? sanitize_text_field( $_GET['order_date'] ) : '';

		$shipment_source_data = EasypostWfTrackingUtil::prepare_shipment_source_data( $post_id, $shipment_id_cs, $shipping_service, $order_date );
		$shipment_result      = $this->get_shipment_info( $post_id, $shipment_source_data );

		if ( null !== $shipment_result && is_object( $shipment_result ) ) {
			$shipment_result_array = EasypostWfTrackingUtil::convert_shipment_result_obj_to_array( $shipment_result, $post_id );

			update_post_meta( $post_id, self::SHIPMENT_RESULT_KEY, $shipment_result_array );
			$admin_notice = EasypostWfTrackingUtil::get_shipment_display_message( $shipment_result_array, $shipment_source_data );
		} else {
			$admin_notice = esc_attr( 'Unable to update tracking info.', 'wf-easypost' );
			update_post_meta( $post_id, self::SHIPMENT_RESULT_KEY, '' );
		}

		self::display_admin_notification_message( $post_id, $admin_notice );
	}

	public static function display_admin_notification_message( $post_id, $admin_notice ) {
		$wftrackingmsg = 5;
		update_post_meta( $post_id, self::TRACKING_MESSAGE_KEY, $admin_notice );
		wp_redirect( admin_url( '/post.php?post=' . $post_id . '&action=edit&' . self::TRACKING_MESSAGE_KEY . '=' . $wftrackingmsg ) );
		exit;
	}

	public function get_shipment_info( $post_id, $shipment_source_data ) {

		if ( empty( $post_id ) ) {
			$wftrackingmsg = 0;
			wp_redirect( admin_url( '/post.php?post=' . $post_id . '&action=edit&' . self::TRACKING_MESSAGE_KEY . '=' . $wftrackingmsg ) );
			exit;
		}

		if ( '' === $shipment_source_data['shipping_service'] ) {
			update_post_meta( $post_id, self::SHIPMENT_SOURCE_KEY, $shipment_source_data );
			update_post_meta( $post_id, self::SHIPMENT_RESULT_KEY, '' );

			$wftrackingmsg = 6;
			wp_redirect( admin_url( '/post.php?post=' . $post_id . '&action=edit&' . self::TRACKING_MESSAGE_KEY . '=' . $wftrackingmsg ) );
			exit;
		}

		update_post_meta( $post_id, self::SHIPMENT_SOURCE_KEY, $shipment_source_data );

		try {
			$shipment_result = EasypostWfTrackingUtil::get_shipment_result( $shipment_source_data );
		} catch ( Exception $e ) {
			$wftrackingmsg = 0;
			wp_redirect( admin_url( '/post.php?post=' . $post_id . '&action=edit&' . self::TRACKING_MESSAGE_KEY . '=' . $wftrackingmsg ) );
			exit;
		}

		return $shipment_result;
	}

	public function wf_load_order( $orderId ) {
		if ( ! class_exists( 'WC_Order' ) ) {
			return false;
		}
		return new WC_Order( $orderId );
	}

	public function wf_user_check() {
		if ( is_admin() ) {
			return true;
		}
		return false;
	}
}

new WF_Tracking_Admin_EasyPost();

?>
