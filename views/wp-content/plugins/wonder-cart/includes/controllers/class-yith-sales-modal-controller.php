<?php
/**
 * Modal Controller
 *
 * @class   YITH_Sales
 * @package YITH/Sales/Controllers
 * @since   1.0.0
 * @author  YITH
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main Class
 */
class YITH_Sales_Modal_Controller {

	/**
	 * Campaigns
	 *
	 * @var array
	 */
	protected $campaigns = array();

	/**
	 * Store the valid campaigns to show in cart page
	 *
	 * @var array
	 */
	protected $valid_campaigns_in_cart = array();
	/**
	 * Store the valid campaigns to show in checkout page
	 *
	 * @var array
	 */
	protected $valid_campaigns_in_checkout = array();

	/**
	 * Construct function of controller
	 *
	 * @param array $campaigns Campaigns ordered by priority.
	 */
	public function __construct( $campaigns ) {
		$this->campaigns = $this->process_campaigns( $campaigns );

		add_action( 'woocommerce_add_to_cart', array( $this, 'init_valid_rules' ), 25 );
		add_action( 'woocommerce_cart_loaded_from_session', array( $this, 'init_valid_rules' ), 110 );
		add_action( 'woocommerce_after_calculate_totals', array( $this, 'update_gift_products' ), 20 );
		add_action( 'woocommerce_checkout_order_processed', array( $this, 'clear_modal_cookies' ) );
		add_action( 'wp_footer', array( $this, 'show_modal_campaigns' ), 20 );
		add_action( 'wp_ajax_yith_sales_action', array( $this, 'ajax_actions' ) );
		add_action( 'wp_ajax_nopriv_yith_sales_action', array( $this, 'ajax_actions' ) );
		add_filter( 'woocommerce_cart_item_quantity', array( $this, 'remove_quantity' ), 1000, 3 );
	}

	/**
	 * Return the list of campaign as one level
	 *
	 * @param array $grouped_campaings Campaigns grouped.
	 *
	 * @return array
	 * @since  1.0.0
	 * @author YITH
	 */
	public function process_campaigns( $grouped_campaings ) {
		$ordered_campaigns = array();
		foreach ( $grouped_campaings as $group ) {
			$ordered_campaigns = array_merge( $ordered_campaigns, $group );
		}

		return $ordered_campaigns;
	}

	/**
	 * Init the gift args , used to validate the campaign
	 *
	 * @return array
	 */
	public function get_gift_args() {
		$cart_items_to_process = $this->get_valid_cart_keys();

		$gift_args = array(
			'cart_total'   => yith_sales_get_cart_subtotal(),
			'past_expense' => is_user_logged_in() ? wc_get_customer_total_spent( get_current_user_id() ) : - 1,
			'products'     => array(),
			'categories'   => array(),
		);

		foreach ( $cart_items_to_process as $cart_item_key ) {
			$cart_item               = WC()->cart->get_cart_item( $cart_item_key );
			$gift_args['products'][] = $cart_item['product_id'];
			$cat_ids                 = yith_sales_get_product_term_ids( $cart_item['data'] );
			$gift_args['categories'] = array_unique( array_merge( $gift_args['categories'], $cat_ids ) );
		}

		return $gift_args;
	}

	/**
	 * Initialize the valid campaigns
	 *
	 * @return void
	 */
	public function init_valid_rules() {
		$gift_args                  = $this->get_gift_args();
		$valid_last_deals_campaigns = array();
		foreach ( $this->campaigns as $campaign ) {
			if ( 'gift-on-cart' === $campaign->get_type() && $campaign->is_valid( $gift_args ) && ! yith_sales_is_modal_visited( $campaign, 'campaigns' ) ) {
				$total_added_product = $this->count_gift_products_in_cart( $campaign->get_id() );
				$max_to_add          = $campaign->total_products_to_add();
				if ( ( $max_to_add - $total_added_product ) > 0 ) {
					if ( ! $campaign->can_add_to_cart_automatically() ) {
						$this->valid_campaigns_in_cart[] = $campaign->get_id();
					} else {
						$campaign->add_to_cart();
						yith_sales_set_modal_as_visited( $campaign, 'campaigns', 1 );
					}
				}
			}

			if ( 'last-deal' === $campaign->get_type() && $campaign->is_valid() && ! yith_sales_is_modal_visited( $campaign, 'campaigns' ) ) {
				$valid_last_deals_campaigns[] = $campaign;
			}
		}

		if ( empty( $valid_last_deals_campaigns ) ) {
			return;
		}

		foreach ( $valid_last_deals_campaigns as $campaign ) {
			$show_on = $campaign->get_show_on();
			if ( in_array( $show_on, array( 'cart', 'both' ), true ) ) {
				$this->valid_campaigns_in_cart[] = $campaign->get_id();
			}
			if ( in_array( $show_on, array( 'checkout', 'both' ), true ) ) {
				$this->valid_campaigns_in_checkout[] = $campaign->get_id();
			}
		}
	}

	/**
	 * Retun how many products are gift products by campaign id
	 *
	 * @param int $campaign_id The campaign id.
	 *
	 * @return int
	 */
	public function count_gift_products_in_cart( $campaign_id ) {

		$total = 0;

		foreach ( WC()->cart->cart_contents as $cart_item_key => $cart_item ) {
			if ( isset( $cart_item['yith_sales_gift_product'] ) && intval( $cart_item['yith_sales_gift_product'] ) === intval( $campaign_id ) ) {
				$total ++;
			}
		}

		return $total;
	}

	/**
	 * Get all cart items without the gift campaign applied
	 *
	 * @return array
	 */
	public function get_valid_cart_keys() {

		$cart_keys = array();
		foreach ( WC()->cart->cart_contents as $cart_item_key => $cart_item ) {
			if ( ! isset( $cart_item['yith_sales_gift_product'] ) ) {
				$cart_keys[] = $cart_item_key;
			}
		}

		return $cart_keys;
	}

	/**
	 * When the cart totals are update, check if the gift products are valid
	 *
	 * @return void
	 */
	public function update_gift_products() {
		remove_action( 'woocommerce_after_calculate_totals', array( $this, 'update_gift_products' ), 20 );

		$gift_args              = $this->get_gift_args();
		$force_calculate_totals = false;
		foreach ( WC()->cart->cart_contents as $cart_item_key => $cart_item ) {
			if ( isset( $cart_item['yith_sales_gift_product'] ) ) {
				$campaign_id = $cart_item['yith_sales_gift_product'];
				$campaign    = yith_sales_get_campaign( $campaign_id );

				if ( $campaign && ! $campaign->is_valid( $gift_args ) ) {
					unset( WC()->cart->cart_contents[ $cart_item_key ]['yith_sales'] );
					unset( WC()->cart->cart_contents[ $cart_item_key ]['yith_sales_discounts'] );
					unset( WC()->cart->cart_contents[ $cart_item_key ]['yith_sales_gift_product'] );
					$force_calculate_totals = true;
				}
			}
		}

		if ( $force_calculate_totals ) {
			WC()->cart->calculate_totals();
		}
		add_action( 'woocommerce_after_calculate_totals', array( $this, 'update_gift_products' ), 20 );
	}

	/**
	 * Add in cart page and in checkout page the template
	 *
	 * @return void
	 */
	public function show_modal_campaigns() {
		// phpcs:disable Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed
		// phpcs:disable WordPress.WhiteSpace.PrecisionAlignment.Found
		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
		if ( is_cart() && count( $this->valid_campaigns_in_cart ) > 0 ) {
			?>
            <div id="yith-sales-modal-campaigns"
                 data-options="
                 <?php
					echo yith_sales_get_json(
                        array(
							'campaignIDS' =>
								$this->valid_campaigns_in_cart,
                        )
                    );
					?>
                 ">
            </div>
			<?php
		}
		if ( is_checkout() && count( $this->valid_campaigns_in_checkout ) > 0 ) {
			?>
            <div id="yith-sales-modal-campaigns"
                 data-options="
                 <?php
					echo yith_sales_get_json(
                        array(
							'campaignIDS'    => $this->valid_campaigns_in_checkout,
							'reloadCheckout' => true,
                        )
                    );
					?>
                 ">
            </div>
			<?php
			// phpcs:enable
		}
	}

	/**
	 * Execute the ajax actions for the upsell campaign
	 *
	 * @return void
	 */
	public function ajax_actions() {
		check_ajax_referer( 'yith-sales-action', 'security' );
		$action = isset( $_REQUEST['yith_sales_action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['yith_sales_action'] ) ) : false;
		if ( $action && method_exists( $this, $action ) ) {
			$this->$action();
		}
	}

	/**
	 * Add the last deal on cart.
	 *
	 * @return void
	 * @throws Exception The exception.
	 */
	public function add_last_deal() {
		if ( ! empty( $_REQUEST['yith_sales_data'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$added_to_cart  = array();
			$quantity       = 1;
			$data           = wp_unslash( $_REQUEST['yith_sales_data'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$campaign_id    = $data['campaign_id'];
			$campaign       = yith_sales_get_campaign( $campaign_id );
			$cart_item_data = array(
				'yith_sales' => array(
					'campaigns' => array(
						$campaign_id,
					),
				),
			);
			$products       = $data['products'];
			foreach ( $products as $product ) {
				$variations = array();
				if ( ! empty( $product['variations'] ) ) {
					foreach ( $product['variations'] as $variation ) {
						$variations[ $variation['key'] ] = $variation['option'];
					}
				}

				// check if on cart exists the same product, if yes remove the current cart item and add the quantity to the new one.
				$cart_item_exists = yith_sales_check_product_on_cart( $product['product_id'], $product['variation_id'], $variations );
				if ( $cart_item_exists ) {
					$quantity += WC()->cart->cart_contents[ $cart_item_exists ]['quantity'];
					WC()->cart->remove_cart_item( $cart_item_exists );
				}

				$cart_item_key = WC()->cart->add_to_cart( $product['product_id'], $quantity, $product['variation_id'], $variations, $cart_item_data );

				if ( $cart_item_key ) {
					$added_to_cart[ $product['product_id'] ] = 1;
				}
			}
			if ( count( $added_to_cart ) > 0 ) {
				wc_add_to_cart_message( $added_to_cart, false );
			}

			if ( 'yes' === $campaign->get_redirect_to_checkout() ) {
				$data = array(
					'redirect_to' => wc_get_checkout_url(),
				);
				wp_send_json( $data );
			} else {
				WC_AJAX::get_refreshed_fragments();
			}
		}
	}

	/**
	 * Add the gift products in the cart
	 *
	 * @return void
	 * @throws Exception The exception.
	 */
	public function add_gift_on_cart() {
		if ( ! isset( $_REQUEST['yith_sales_data'] ) ) {// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			return;
		}

		$added_to_cart       = array();
		$data                = wp_unslash( $_REQUEST['yith_sales_data'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$campaign_id         = $data['campaign_id'];
		$campaign            = yith_sales_get_campaign( $campaign_id );
		$cart_item_data      = array(
			'yith_sales'              => array(
				'campaigns' => array(
					$campaign_id,
				),
			),
			'yith_sales_gift_product' => $campaign_id,
		);
		$products            = $data['products'];
		$total_added_product = $this->count_gift_products_in_cart( $campaign_id );
		$max_to_add          = $campaign->total_products_to_add();
		foreach ( $products as $product ) {
			if ( $total_added_product >= $max_to_add ) {
				break;
			}
			$variations = array();
			if ( ! empty( $product['variations'] ) ) {
				foreach ( $product['variations'] as $variation ) {
					$variations[ $variation['key'] ] = $variation['option'];
				}
			}

			$cart_item_key = WC()->cart->add_to_cart( $product['product_id'], 1, $product['variation_id'], $variations, $cart_item_data );

			if ( $cart_item_key ) {
				$added_to_cart[ $product['product_id'] ] = 1;
			}
			$total_added_product ++;
		}
		if ( ! empty( $added_to_cart ) ) {
			wc_add_to_cart_message( $added_to_cart, false );
			WC_AJAX::get_refreshed_fragments();
		}

	}

	/**
	 * Check the cart when a cart item is removed
	 *
	 * @param int $cart_item_key_removed Cart item removed.
	 *
	 * @return void
	 * @throws Exception The exception.
	 */
	public function check_cart( $cart_item_key_removed ) {

		foreach ( WC()->cart->cart_contents as $cart_item_key => $cart_item ) {
			if ( isset( $cart_item['yith_sales']['campaigns'] ) ) {
				foreach ( $cart_item['yith_sales']['campaigns'] as $campaign_id ) {
					$campaign = yith_sales_get_campaign( $campaign_id );
					if ( $campaign && $campaign->get_type() === 'gift-on-cart' ) {
						yith_sales_delete_single_campaign_from_modal_cookie( $campaign, 'campaigns' );
					}
					if ( $campaign && $campaign->get_type() === 'last-deal' && ! $campaign->check_if_is_valid( $cart_item_key_removed, $cart_item_key ) ) {
						WC()->cart->remove_cart_item( $cart_item_key );
						WC()->cart->add_to_cart( $cart_item['product_id'], $cart_item['quantity'], $cart_item['variation_id'], $cart_item['variation'] );
					}
				}
			}
		}
	}


	/**
	 * Clear campaign modal cookie
	 *
	 * @return void
	 */
	public function clear_modal_cookies() {
		yith_sales_clear_modal_cookie( 'campaigns' );
	}

	/**
	 * Remove the quantity input from cart
	 *
	 * @param string $product_quantity HTML code to show product quantity.
	 * @param string $cart_item_key    Cart item key.
	 * @param array  $cart_item        Cart item.
	 *
	 * @return string
	 * @since  1.0.0
	 * @author YITH
	 */
	public function remove_quantity( $product_quantity, $cart_item_key, $cart_item ) {
		if ( isset( $cart_item['yith_sales']['campaigns'] ) ) {
			foreach ( $cart_item['yith_sales']['campaigns'] as $campaign_id ) {
				$campaign = yith_sales_get_campaign( $campaign_id );
				if ( $campaign && method_exists( $campaign, 'remove_quantity' ) ) {
					$product_quantity = $campaign->remove_quantity( $product_quantity, $cart_item_key, $cart_item );
				}
			}
		}

		return $product_quantity;
	}
}
