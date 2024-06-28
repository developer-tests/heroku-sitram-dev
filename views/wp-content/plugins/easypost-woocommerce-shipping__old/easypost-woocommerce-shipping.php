<?php
/*
	Plugin Name: ELEX EasyPost Shipping Plugin (UPS, FedEx, Canada Post & USPS)
	Plugin URI: https://elextensions.com/plugin/easypost-shipping-method-plugin-for-woocommerce/
	Description: Obtain real-time shipping rates and print shipping labels with postage for UPS, FedEx, Canada Post & USPS shipping carriers using EasyPost shipping API.
	Version: 3.0.2
	WC requires at least: 2.6.0
	WC tested up to: 7.1
	Author: ELEXtensions
	Author URI: https://elextensions.com/
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
// define the woocommerce_cart_shipping_method_full_label callback 
// Required functions
if ( ! function_exists( 'wf_is_woocommerce_active' ) ) {
	require_once( 'wf-includes/wf-functions.php' );
}

// Woocommerce active check
if ( ! wf_is_woocommerce_active() ) {
	add_action( 'admin_notices', 'xa_premium_woocommerce_inactive_notice' );
	return;
}
function xa_premium_woocommerce_inactive_notice() {     ?>
	<div id="message" class="error">
		<p>
			<?php printf( esc_attr( '<b>WooCommerce</b> plugin must be active for <b>EasyPost WooCommerce Extension</b> to work. ', 'wf-easypost' ) ); ?>
		</p>
	</div>
	<?php
}

if ( ! function_exists( 'wf_get_settings_url' ) ) {
	function wf_get_settings_url() {
		return version_compare( WC()->version, '2.1', '>=' ) ? 'wc-settings' : 'woocommerce_settings';
	}
}
if ( ! defined( 'WF_USPS_EASYPOST_ACCESS_KEY' ) ) {
	define( 'WF_USPS_EASYPOST_ACCESS_KEY', 'A5DgeMLnX5ATAeZu3dKixg' ); // This is test key , You can change it with live key when going production
}

if ( ! defined( 'WF_EASYPOST_ID' ) ) {
	define( 'WF_EASYPOST_ID', 'wf_easypost_id' );
}
if ( ! defined( 'WF_EASYPOST_ADV_DEBUG_MODE' ) ) {
	define( 'WF_EASYPOST_ADV_DEBUG_MODE', 'on' ); // Turn "off" to disable advanced logs.
}

if ( ! defined( 'ELEX_EASYPOST_RETURN_ADDON_STATUS' ) ) {  //Add-on for woocommerce return label.
	if ( in_array( 'elex-easypost-for-woocommerce-return-labels-addon/elex-easypost-for-woocommerce-return-labels-addon.php', get_option( 'active_plugins' ) ) ) {
		define( 'ELEX_EASYPOST_RETURN_ADDON_STATUS', true );
	} else {
		define( 'ELEX_EASYPOST_RETURN_ADDON_STATUS', false );
	}
}
if ( ! defined( 'ELEX_EASYPOST_AUTO_LABEL_GENERATE_STATUS_CHECK' ) ) {  //Add-on for woocommerce auto generate label.
	if ( in_array( 'elex-easypost-for-woocommerce-auto-label-generate-email-add-on/elex-easypost-for-woocommerce-auto-label-generate-email-add-on.php', get_option( 'active_plugins' ) ) ) {
		define( 'ELEX_EASYPOST_AUTO_LABEL_GENERATE_STATUS_CHECK', true );
		if ( ! defined( 'ELEX_EASYPOST_AUTO_LABEL_GENERATE_STATUS_CHECK_PATH' ) ) {
			define( 'ELEX_EASYPOST_AUTO_LABEL_GENERATE_STATUS_CHECK_PATH', ABSPATH . PLUGINDIR . '/elex-easypost-for-woocommerce-auto-label-generate-email-add-on/' );
		}
	} else {
		define( 'ELEX_EASYPOST_AUTO_LABEL_GENERATE_STATUS_CHECK', false );
	}
}
if ( ! defined( 'ELEX_EASYPOST_MULTIPLE_WAREHOUSE_STATUS_CHECK' ) ) {  //Add-on for woocommerce multiple warehouse.
	if ( in_array( 'elex-multiple-warehouse-addon-for-easypost/elex-multiple-warehouse-addon-for-easypost.php', get_option( 'active_plugins' ) ) ) {
		define( 'ELEX_EASYPOST_MULTIPLE_WAREHOUSE_STATUS_CHECK', true );
		if ( ! defined( 'ELEX_EASYPOST_MULTIPLE_WAREHOUSE_STATUS_CHECK_PATH' ) ) {
			define( 'ELEX_EASYPOST_MULTIPLE_WAREHOUSE_STATUS_CHECK_PATH', ABSPATH . PLUGINDIR . '/elex-multiple-warehouse-addon-for-easypost/' );
		}
	} else {
		define( 'ELEX_EASYPOST_MULTIPLE_WAREHOUSE_STATUS_CHECK', false );
	}
}
//Return label add-on path
if ( defined( 'ELEX_EASYPOST_RETURN_ADDON_STATUS' ) ) {
	if ( ! defined( 'ELEX_EASYPOST_RETURN_LABEL_ADDON_PATH' ) ) {
		define( 'ELEX_EASYPOST_RETURN_LABEL_ADDON_PATH', ABSPATH . PLUGINDIR . '/elex-easypost-for-woocommerce-return-labels-addon/' );
	}
}

function wf_easyshop_activation_check() { 
	//check if basic version is there
	if ( is_plugin_active( 'woo-easypost-shipping-method/easypost-woocommerce-shipping.php' ) ) {
		deactivate_plugins( basename( __FILE__ ) );
		wp_die( esc_attr( 'Oops! You tried installing the premium version without deactivating and deleting the basic version. Kindly deactivate and delete EasyPost(Basic) Woocommerce Extension and then try again', 'wf-easypost' ), '', array( 'back_link' => 1 ) );
	}

	if ( ! function_exists( 'curl_init' ) ) {
		deactivate_plugins( basename( __FILE__ ) );
		wp_die( esc_attr( 'EasyPost needs the CURL PHP extension.', 'wf-easypost' ) );
	}
	if ( ! function_exists( 'json_decode' ) ) {
		deactivate_plugins( basename( __FILE__ ) );
		wp_die( esc_attr( 'EasyPost needs the JSON PHP extension.', 'wf-easypost' ) );
	}
}
register_activation_hook( __FILE__, 'wf_easyshop_activation_check' );

/**
 * WC_USPS class
 */
if ( ! class_exists( 'USPS_Easypost_WooCommerce_Shipping' ) ) {
	class USPS_Easypost_WooCommerce_Shipping {
	
		/**
		 * Constructor
		 */
		public function __construct() {
			add_action( 'init', array( $this, 'init' ) );
			add_action( 'admin_init', array( $this, 'admin_init' ) );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
			add_action( 'woocommerce_shipping_init', array( $this, 'shipping_init' ) );
			add_filter( 'woocommerce_shipping_methods', array( $this, 'add_method' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );
			add_action( 'admin_init', array( $this, 'migrate' ) );
			
			if ( is_admin() ) {
				add_action( 'admin_footer', array( $this, 'wf_easypost_add_bulk_action_links' ), 10 );
				//to add bulk label printing.
			}
		}

		/**
		 * Localisation
		 */
		public function init() {
			if ( ! class_exists( 'Wf_Order' ) ) {
				include_once 'includes/class-wf-legacy.php';
			}
			include_once( 'includes/class-wf-shipping-easypost-admin.php' );
			include_once( 'includes/class-wf-tracking-admin.php' );
			if ( is_admin() ) {
				// WF Print Shipping Label.		
				include_once( 'includes/class-wf-admin-options.php' );
				//include api manager
				include_once( 'includes/wf_api_manager/wf-api-manager-config.php' );
			}
			load_plugin_textdomain( 'wf-easypost', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}
		public function admin_init() {
		  include_once( 'includes/class-wf-shipping-easypost-admin.php' );
		
		}

		/**
		 * Plugin page links
		 */
		public function plugin_action_links( $links ) {
			$plugin_links = array(
				'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=shipping&section=wf_easypost_id&subtab=general' ) . '">' . esc_attr( 'Settings', 'wf-easypost' ) . '</a>',
				'<a href="https://elextensions.com/documentation/#elex-easypost-shipping" target="_blank">' . esc_attr( 'Documentation', 'wf-easypost' ) . '</a>',
				'<a href="https://elextensions.com/support/" target="_blank">' . esc_attr( 'Support', 'wf-easypost' ) . '</a>',
			);
			return array_merge( $plugin_links, $links );
		}

		/**
		 * Load gateway class
		 */
		public function shipping_init() {
		   include_once( 'includes/class-wf-shipping-easypost.php' );
			$est_delivery = new WF_Easypost();
			add_filter( 'woocommerce_cart_shipping_method_full_label', array( $est_delivery, 'wf_easypost_add_delivery_time' ), 10, 2 );
			add_action( 'woocommerce_checkout_update_order_meta', array( $est_delivery, 'wf_easypost_insurance_field' ) );
			
		}

		/*
		* Function to add bulk label print option to order bulk actions
		*
		* @ since  1.7.2
		* @ access public
		*/
		public function wf_easypost_add_bulk_action_links() { 
			global $post_type;
			if ( 'shop_order' === $post_type ) {
				?>
				<script type="text/javascript">
					jQuery(document).ready(function() {
						jQuery('<option>').val('create_shipment_easypost').text('<?php echo esc_attr( 'Create EasyPost Shipment', 'wf-easypost' ); ?>').appendTo("select[name='action']");

						jQuery('<option>').val('create_shipment_easypost').text('<?php echo esc_attr( 'Create EasyPost Shipment', 'wf-easypost' ); ?>').appendTo("select[name='action2']");
					});
				</script>
<?php
			}
		}

		/**
		 * Add method to WC
		 */
		public function add_method( $methods ) {
			$methods[] = 'WF_Easypost';
			return $methods;
		}
		/**
		 * Enqueue scripts
		 */
		public function scripts() {
				global $woocommerce;
				$woocommerce_version = function_exists( 'WC' ) ? WC()->version : $woocommerce->version;
				wp_enqueue_script( 'jquery' );
				wp_enqueue_script( 'jquery-ui-sortable' );
				wp_enqueue_style( 'wf-common-style', plugins_url( '/resources/css/wf_common_style.css', __FILE__ ), array(), $woocommerce_version );
				wp_enqueue_script( 'elex-wf-common-script', plugins_url( '/resources/js/wf_common.js', __FILE__ ), array( 'jquery' ), $woocommerce_version );
				wp_enqueue_script( 'elex-wf-easypost-script', plugins_url( '/resources/js/wf_easypost.js', __FILE__ ), array( 'jquery' ), $woocommerce_version );
		}
		public function migrate() {
			if ( get_option( 'easypost_migrate_v3' ) ) {
				return;
			}
			global $woocommerce;
			$global_options = get_option( 'woocommerce_' . WF_EASYPOST_ID . '_settings' );
			$general_fields = array(
				'enabled'           => $global_options['enabled'],
				'debug_mode'        => $global_options['debug_mode'],
				'status_log'        => $global_options['status_log'],
				'disable_cart_rate' => $global_options['disable_cart_rate'],
				'api'               => $global_options['api'],
				'api_mode'          => $global_options['api_mode'],
				'api_key'           => $global_options['api_key'],
				'api_test_key'      => $global_options['api_test_key'],
			);
			if ( isset( $global_options['addon_bulk_printing_project_key'] ) ) {
				$bulk_printing_add_on_fields = array(
					'addon_bulk_printing_title'       => '',
					'addon_bulk_printing_project_key' => $global_options['addon_bulk_printing_project_key'],
					'addon_bulk_printing_secret_key'  => $global_options['addon_bulk_printing_secret_key'],
				);
			  $general_fields              = array_merge( $general_fields, $bulk_printing_add_on_fields );
			}
		
			$rates_field = array(
				'title' => $global_options['title'],
				'availability' => $global_options['availability'],
				'zip' => $global_options['zip'],
				'state' => $global_options['state'],
				// adding country
				'country' => $global_options['country'],
				'countries' => $global_options['countries'],
				'estimated_title' => $global_options['estimated_title'],
				'est_delivery' => $global_options['est_delivery'],
				'working_days' => $global_options['working_days'],
				'cut_off_time' => $global_options['cut_off_time'],
				'lead_time' => $global_options['lead_time'],
				'flat_rate_boxes_title' => $global_options['flat_rate_boxes_title'],
				'flat_rate_boxes_domestic' => $global_options['flat_rate_boxes_domestic'],
				'flat_rate_boxes_domestic_carrier' => $global_options['flat_rate_boxes_domestic_carrier'],
				'selected_flat_rate_boxes'  => $global_options['selected_flat_rate_boxes'],
				'flat_rate_boxes_text' => $global_options['flat_rate_boxes_text'],
				'flat_rate_boxes_express_text' => $global_options['flat_rate_boxes_express_text'],
				'flat_rate_boxes_first_class_text' => $global_options['flat_rate_boxes_first_class_text'],
				'flat_rate_boxes_fedex_one_rate_text' => $global_options['flat_rate_boxes_fedex_one_rate_text'],
				'flat_rate_fee' => $global_options['flat_rate_fee'],
				'flat_rate_boxes_international' => $global_options['flat_rate_boxes_international'],
				'flat_rate_boxes_international_carrier'   => $global_options['flat_rate_boxes_international_carrier'],
				'predefined_package_service_usps'         => $global_options['predefined_package_service_usps'],
				'flat_rate_boxes_text_international_mail' => $global_options['flat_rate_boxes_text_international_mail'],
				'flat_rate_boxes_text_international_express' => $global_options['flat_rate_boxes_text_international_express'],
				'flat_rate_boxes_text_first_class_mail_international' => $global_options['flat_rate_boxes_text_first_class_mail_international'],
				'duty_settings' => $global_options['duty_settings'],
				'ex_easypost_duty' => $global_options['ex_easypost_duty'],
				'carrier_rates_title' => $global_options['carrier_rates_title'],
				'fallback' => $global_options['fallback'],
				'show_rates' => $global_options['show_rates'],
				'handling_fee' => $global_options['handling_fee'],
				'easypost_carrier' => $global_options['easypost_carrier'],
				'services'  => $global_options['services'],
			);
		
			
			$labels_field = array(
				'label-settings' => $global_options['label-settings'],
				'printLabelType' => $global_options['printLabelType'],
				'insurance' => $global_options['insurance'],
				'elex_shipping_label_size' => $global_options['elex_shipping_label_size'],
				'elex_shipping_label_size_usps' => $global_options['elex_shipping_label_size_usps'],
				'elex_shipping_label_size_ups'  => $global_options['elex_shipping_label_size_ups'],
				'elex_shipping_label_size_fedex' => $global_options['elex_shipping_label_size_fedex'],
				'elex_shipping_label_size_canadapost'  => $global_options['elex_shipping_label_size_canadapost'],
				'signature_option' => $global_options['signature_option'],
				'branding_url' => false,
				'ioss_number'  => $global_options['ioss_number'],
				'default_shipment' => $global_options['default_shipment'],
				'easypost_default_domestic_shipment_service' => $global_options['easypost_default_domestic_shipment_service'],
				'easypost_default_international_shipment_service' => $global_options['easypost_default_international_shipment_service'],
				'origin_address' => $global_options['origin_address'],
				'name' => $global_options['name'],
				'company' => $global_options['company'],
				'street1'  => $global_options['street1'],
				'street2'  => $global_options['street2'],
				'city'  => $global_options['city'],
				'email' => $global_options['email'],
				'phone' => $global_options['phone'],
				'customs_description' => $global_options['customs_description'],
				'Custom_signer_title' => $global_options['Custom_signer_title'],
				'customs_signer' => $global_options['customs_signer'],
				'return_header' => $global_options['return_header'],
				'return_address' => $global_options['return_address'],
				'return_name' => $global_options['return_name'],
				'return_company' => $global_options['return_company'],
				'return_street1' => $global_options['return_street1'],
				'return_street2' => $global_options['return_street2'],
				'return_city' => $global_options['return_city'],
				'return_state' => $global_options['return_state'],
				'return_zip'  => $global_options['return_zip'],
				'return_country' => $global_options['return_country'],
				'return_phone' => $global_options['return_phone'],
				'return_email' => $global_options['return_email'],
				'third_party__header' => $global_options['third_party__header'],
				'third_party_billing' => $global_options['third_party_billing'],
				'third_party_apikey'  => $global_options['third_party_apikey'],
				'third_party_country' => $global_options['third_party_country'],
				'third_party_zip'   => $global_options['third_party_zip'],
				'third_party_checkout' => $global_options['third_party_checkout'],
				'third_party_fedex_header'  => '',
				'fedex_third_party_billing' => false,
				'fedex_third_party_apikey'  => '',
				'fedex_third_party_country' => 'US',
				'fedex_third_party_zip' => '',
				'fedex_third_party_checkout' => false,
			);
			
			$packing_field  = array(
				'packing_method'         => $global_options['packing_method'],
				'packing_algorithm'      => $global_options['packing_algorithm'],
				'box_max_weight'         => $global_options['box_max_weight'],
				'weight_packing_process' => $global_options['weight_packing_process'],
				'boxes'   => $global_options['boxes'],
			);
			
			$licence_field = array(
				'licence' => $global_options['licence'],
			);
			
	
	
			if ( isset( $global_options['auto_label_email_status'] ) ) {    
				$auto_add_on_fields = array( 
					//adding order status
					'auto_label_generation' => '',
					'order_status' => $global_options['order_status'],
					'auto_label_email_settings' => '',
					'auto_label_email_status' => $global_options['auto_label_email_status'],
					'auto_label_email_add_on' => $global_options['auto_label_email_add_on'],
					'auto_label_email_add_on_from' => $global_options['auto_label_email_add_on_from'],
					'auto_label_email_name' => $global_options['auto_label_email_name'],
					'auto_label_email_subject' => $global_options['auto_label_email_subject'],
					'auto_label_email_content' => $global_options['auto_label_email_content'],
					'auto_label_failure_email' => '',
					'auto_label_enable_failed_email' => $global_options['auto_label_enable_failed_email'],
					'auto_label_failed_email_subject' => $global_options['auto_label_failed_email_subject'],
					'auto_label_failed_email_content' => $global_options['auto_label_failed_email_content'],
			   
				);
				$auto_add_on_fields = wp_parse_args( get_option( 'woocommerce_' . WF_EASYPOST_ID . '_auto_generate_settings' ), $auto_add_on_fields );
				update_option( 'woocommerce_' . WF_EASYPOST_ID . '_auto_generate_settings', $auto_add_on_fields );
			}
			
			
			
			if ( isset( $global_options['enable_return_label'] ) ) {
				$return_add_on_fields = array(

					'return_shipment'  => '',
					'enable_return_label' => $global_options['enable_return_label'],
					'easypost_default_domestic_return_shipment_service' => $global_options['easypost_default_domestic_return_shipment_service'],
					'easypost_default_international_return_shipment_service'        => $global_options['easypost_default_international_return_shipment_service'],
					'return_order_status' => $global_options['return_order_status'],
					'label_generation' => '',
					'enable_auto_return_label' => $global_options['enable_auto_return_label'],
					'email_settings' => '',
					'email_status' => $global_options['email_status'],
					'email_add_on' => $global_options['email_add_on'],
					'email_add_on_from' => $global_options['email_add_on_from'],
					'email_name' => $global_options['email_add_on_from'],
					'email_subject' => $global_options['email_name'],
					'email_content' => $global_options['email_content'],
					'failure_email' => '',
					'enable_failed_email' => $global_options['enable_failed_email'],
					'failed_email_subject' => $global_options['failed_email_subject'],
					'failed_email_content' => $global_options['failed_email_content'],
					'return_shipment_address' => '',
					'return_address_addon' => $global_options['return_address_addon'],
					'return_shipment_address_manual' => '',
					'return_name_addon' => $global_options['return_name_addon'],
					'return_company_addon' => $global_options['return_company_addon'],
					'return_street1_addon' => $global_options['return_street1_addon'],
					'return_street2_addon' => $global_options['return_street2_addon'],
					'return_city_addon' => $global_options['return_city_addon'],
					'return_email_addon' => $global_options['return_email_addon'],
					'return_zip_addon' => $global_options['return_zip_addon'],
					'return_state_addon' => $global_options['return_state_addon'],
					'return_country_addon' => $global_options['return_country_addon'],
					'return_phone_addon' => $global_options['return_phone_addon'],
				
				);
				$return_add_on_fields = wp_parse_args( get_option( 'woocommerce_' . WF_EASYPOST_ID . '_return_settings' ), $return_add_on_fields );
				update_option( 'woocommerce_' . WF_EASYPOST_ID . '_return_settings', $return_add_on_fields );
			}
			
			
			
			$general_fields = wp_parse_args( get_option( 'woocommerce_' . WF_EASYPOST_ID . '_general_settings' ), $general_fields );
			update_option( 'woocommerce_' . WF_EASYPOST_ID . '_general_settings', $general_fields );
			$rates_field = wp_parse_args( get_option( 'woocommerce_' . WF_EASYPOST_ID . '_rates_settings' ), $rates_field );
			update_option( 'woocommerce_' . WF_EASYPOST_ID . '_rates_settings', $rates_field );
			$labels_field = wp_parse_args( get_option( 'woocommerce_' . WF_EASYPOST_ID . '_labels_settings' ), $labels_field );
			update_option( 'woocommerce_' . WF_EASYPOST_ID . '_labels_settings', $labels_field );
			$packing_field = wp_parse_args( get_option( 'woocommerce_' . WF_EASYPOST_ID . '_packing_settings' ), $packing_field );
			update_option( 'woocommerce_' . WF_EASYPOST_ID . '_packing_settings', $packing_field );
			$licence_field = wp_parse_args( get_option( 'woocommerce_' . WF_EASYPOST_ID . '_licence_settings' ), $licence_field );
			update_option( 'woocommerce_' . WF_EASYPOST_ID . '_licence_settings', $licence_field );
			update_option( 'easypost_migrate_v3', true );
		}
	}
	new USPS_Easypost_WooCommerce_Shipping();
}
