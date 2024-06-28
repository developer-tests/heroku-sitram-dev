<?php
/**
 * Buy X Get Y Controller
 *
 * @class   YITH_Sales
 * @package YITH/Sales/Controllers
 * @since   1.0.0
 * @author  YITH
 */

defined( 'ABSPATH' ) || exit;

/**
 * The class
 */
class YITH_Sales_Buy_X_Get_Y_Controller {

	/**
	 * Campaigns
	 *
	 * @var YITH_Sales_Buy_X_Get_Y_Campaign[]
	 */
	protected $campaigns = array();

	/**
	 * Campaigns
	 *
	 * @var array
	 */
	protected $valid_campaign_by_product = array();

	/**
	 * The constructor
	 *
	 * @param Abstract_YITH_Sales_Campaign[] $campaigns The campaigns.
	 */
	public function __construct( $campaigns ) {
		$this->campaigns = $campaigns['buy-x-get-y'] ?? array();

		add_action( 'woocommerce_before_add_to_cart_quantity', array( $this, 'display_campaign_title' ), 20 );
		add_action( 'wp_footer', array( $this, 'add_notice_after_add_to_cart' ), 99 );
		add_action( 'woocommerce_add_to_cart', array( $this, 'save_latest_item_added' ), 20 );
		add_action( 'woocommerce_add_to_cart', array( $this, 'check_automatic_add_to_cart' ), 25 );
		add_action( 'wp_ajax_yith_sales_action', array( $this, 'ajax_actions' ) );
		add_action( 'wp_ajax_nopriv_yith_sales_action', array( $this, 'ajax_actions' ) );
		add_action( 'woocommerce_before_calculate_totals', array( $this, 'init_totals' ), 150 );
		add_action( 'woocommerce_cart_loaded_from_session', array( $this, 'init_totals' ), 150 );
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
	 * Add the product in cart
	 *
	 * @return void
	 * @throws Exception The exception.
	 */
	public function add_buy_x_get_y() {
		if ( ! isset( $_REQUEST['yith_sales_data'] ) ) {// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			return;
		}

		$added_to_cart  = array();
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

		$rule = $campaign->get_x_y_promo_rule();
		foreach ( $products as $product ) {
			$variations = array();
			if ( ! empty( $product['variations'] ) ) {
				foreach ( $product['variations'] as $variation ) {
					$variations[ $variation['key'] ] = $variation['option'];
				}
			}

			$cart_item_key = yith_sales_check_product_on_cart( $product['product_id'], $product['variation_id'], $variations );
			if ( $cart_item_key ) {
				$cart_item = WC()->cart->cart_contents[ $cart_item_key ];
				$old_qty   = $cart_item['quantity'];

				if ( isset( $cart_item['yith_sales'] ) ) {
					WC()->cart->cart_contents[ $cart_item_key ]['yith_sales']['campaigns'][] = $campaign_id;
				} else {
					WC()->cart->cart_contents[ $cart_item_key ]['yith_sales']['campaigns'] = array( $campaign_id );
				}
				WC()->cart->set_quantity( $cart_item_key, $old_qty + $rule['itemsToGet'] );
			} else {
				$cart_item_key = WC()->cart->add_to_cart( $product['product_id'], $rule['itemsToGet'], $product['variation_id'], $variations, $cart_item_data );
			}
			if ( $cart_item_key ) {
				$added_to_cart[ $product['product_id'] ] = $rule['itemsToGet'];
			}
		}
		if ( ! empty( $added_to_cart ) ) {
			wc_add_to_cart_message( $added_to_cart, false );
			WC_AJAX::get_refreshed_fragments();
		}
	}

	/**
	 * Find if exist a valid campaign
	 *
	 * @return void
	 */
	public function find_valid_x_y_campaign_details() {
		$valid_campaign_details = $this->get_valid_campaign_details();
		$data                   = array(
			'campaignID' => false,
			'productID'  => false,
		);
		if ( $valid_campaign_details['valid_campaign'] ) {
			$campaign            = $valid_campaign_details['valid_campaign'];
			$data['campaignID']  = $campaign->get_id();
			$data['productID']   = $valid_campaign_details['product_id'];
			$data['cartItemKey'] = $valid_campaign_details['cart_item_key'];

		}

		wp_send_json_success( $data );
	}


	/**
	 * Show the campaign title on product page.
	 *
	 * @return void
	 * @since  1.0.0
	 * @author YITH
	 */
	public function display_campaign_title() {
		global $product;
		$campaign = $this->get_valid_campaign_by_product( $product );

		if ( is_callable( array( $campaign, 'show_title_on_single_page' ) ) ) {
			$campaign->show_title_on_single_page();
		}
	}

	/**
	 * Return a valid campaign for a product
	 *
	 * @param WC_Product $product Product.
	 *
	 * @return YITH_Sales_Buy_X_Get_Y_Campaign|bool
	 * @since  1.0.0
	 * @author YITH
	 */
	public function get_valid_campaign_by_product( $product ) {
		if ( ! $product ) {
			return false;
		}
		$product_id     = $product->get_id();
		$valid_campaign = false;
		foreach ( $this->campaigns as $campaign ) {
			if ( $campaign->is_valid_for( $product ) ) {
				$this->valid_campaign_by_product[ $product_id ] = $campaign;
				$valid_campaign                                 = $campaign;
				break;
			}
		}

		return $valid_campaign;
	}

	/**
	 * Add the template for the buy x get y campaigns
	 *
	 * @return void
	 */
	public function add_notice_after_add_to_cart() {
		$valid_campaign_details = $this->get_valid_campaign_details();
		$valid_campaign         = $valid_campaign_details['valid_campaign'];

		$options = array(
			'campaignID' => false,
			'productID'  => false,
		);
		if ( false !== $valid_campaign ) {
			$options = array(
				'campaignID'  => $valid_campaign->get_id(),
				'productID'   => $valid_campaign_details['product_id'],
				'cartItemKey' => $valid_campaign_details['cart_item_key'],
			);
		}
		// phpcs:disable Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed
		// phpcs:disable Generic.WhiteSpace.ScopeIndent.Incorrect
		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
		// phpcs:disable WordPress.WhiteSpace.PrecisionAlignment.Found
		?>
        <div class="yith-sales-x-y-modal"
             data-options="
             <?php
		     echo yith_sales_get_json( $options );
				?>
             "
        >
        </div>
		<?php
		// phpcs:enable
	}

	/**
	 * Add in a transient the latest product added in the cart
	 *
	 * @param string $cart_item_key The cart item key.
	 *
	 * @return void
	 */
	public function save_latest_item_added( $cart_item_key ) {
		set_transient( 'yith_sales_latest_cart_item_key', $cart_item_key );
	}

	/**
	 * Check if is possible add the product automatically
	 *
	 * @param string $cart_item_key The cart key.
	 *
	 * @return void
	 */
	public function check_automatic_add_to_cart( $cart_item_key ) {
		$valid_campaign = $this->get_valid_campaign_details();

		$campaign = $valid_campaign['valid_campaign'];

		if ( $campaign && $campaign->can_add_to_cart() ) {

			remove_action( 'woocommerce_add_to_cart', array( $this, 'check_automatic_add_to_cart' ), 25 );
			$campaign->add_to_cart();
			add_action( 'woocommerce_add_to_cart', array( $this, 'check_automatic_add_to_cart' ), 25 );
		} else {
			set_transient( 'yith_sales_latest_cart_item_key', $cart_item_key );
		}
	}


	/**
	 * Return the details of the current valid campaign
	 *
	 * @param string|bool $cart_item_key The cart item key.
	 *
	 * @return array
	 */
	public function get_valid_campaign_details( $cart_item_key = false ) {
		$cart_item_key_to_check = false !== $cart_item_key ? $cart_item_key : get_transient( 'yith_sales_latest_cart_item_key' );
		$valid_campaign         = false;
		$product_id             = false;
		if ( $cart_item_key_to_check ) {
			$cart_item = WC()->cart->get_cart_item( $cart_item_key_to_check );
			if ( $cart_item ) {
				foreach ( $this->campaigns as $campaign ) {

					if ( $campaign->check_is_valid_by_cart_item( $cart_item ) && ! yith_sales_is_modal_visited( $campaign, 'buy-x-get-y' ) ) {
						$valid_campaign = $campaign;
						$product_id     = $cart_item['data']->get_id();
						break;
					}
				}
			}
		}

		delete_transient( 'yith_sales_latest_cart_item_key' );

		return array(
			'valid_campaign' => $valid_campaign,
			'product_id'     => $product_id,
			'cart_item_key'  => $cart_item_key_to_check,
		);
	}

	/**
	 * Add discount in the cart
	 *
	 * @return void
	 */
	public function init_totals() {
		$sorted_cart = yith_sales_clone_cart();
		uasort( $sorted_cart, 'yith_sales_sort_cart_by_price' );
		YITH_Sales_Buy_X_Get_Y_Counter::reset();
		foreach ( $sorted_cart as $cart_key => $cart_item ) {
			WC()->cart->cart_contents[ $cart_key ]['yith_sales_av_quantity'] = $cart_item['quantity'];
		}
		foreach ( $this->campaigns as $campaign ) {
			foreach ( $sorted_cart as $cart_key => $cart_item ) {
				if ( $campaign->is_valid_for( $cart_item['data'] ) ) { // Check if is a trigger product.
					$trigger_product  = $campaign->get_trigger_product();
					$product_to_offer = $campaign->get_products_to_offer();
					$rule             = $campaign->get_x_y_promo_rule();
					if ( $campaign->check_is_trigger_valid_by_cart_item( $cart_item, 'same' === $product_to_offer['type'] ) ) {
						$rcq          = $campaign->get_total_valid_product( $cart_item, true );
						$rmq          = $campaign->get_total_valid_product( $cart_item );
						$total_target = $this->get_total_target( $rcq + $rmq, $rule, 'same' === $product_to_offer['type'] );

						if ( 'same' === $product_to_offer['type'] ) { // The campaign is valid for the same cart item.
							WC()->cart->cart_contents[ $cart_key ][ $campaign->get_id() ]['total_target'] = $total_target;
							WC()->cart->cart_contents[ $cart_key ]['yith_sales']['campaigns']             = array( $campaign->get_id() );
						} else {
							foreach ( $sorted_cart as $cart_offer_key => $cart_offer_item ) {
								if ( $cart_offer_key !== $cart_key && $campaign->is_valid_for( $cart_offer_item['data'], 'products_offer' ) ) {
									WC()->cart->cart_contents[ $cart_offer_key ][ $campaign->get_id() ]['total_target'] = $total_target;
									WC()->cart->cart_contents[ $cart_offer_key ]['yith_sales']['campaigns']             = array( $campaign->get_id() );
								}
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Return how many products are available for the rule
	 *
	 * @param int   $total The total.
	 * @param array $rule  The rule configuration.
	 * @param bool  $same  The rule is for the same product.
	 *
	 * @return int|mixed
	 */
	public function get_total_target( $total, $rule, $same = false ) {
		if ( ! $same ) {
			return $this->get_global_total_target( $total, $rule );
		} else {
			$total_to_buy = $rule['itemsToBuy'] + 1;
			$total_to_add = 0;

			while ( $total >= $total_to_buy ) {
				if ( $total >= ( $rule['itemsToBuy'] + $rule['itemsToGet'] ) ) {
					$total        -= $rule['itemsToBuy'] + $rule['itemsToGet'];
					$total_to_add += $rule['itemsToGet'];
				} else {

					$total_to_add += $total - $rule['itemsToBuy'];
					$total         = 0;
				}
			}

			return $total_to_add;
		}
	}

	/**
	 * Get the global target
	 *
	 * @param int   $total The total.
	 * @param array $rule The rule configuration.
	 *
	 * @return int|mixed
	 */
	public function get_global_total_target( $total, $rule ) {
		$repetitions = floor( $total / $rule['itemsToBuy'] );
		$tt          = 0;
		for ( $x = 1; $x <= $repetitions; $x ++ ) {
			if ( $total - $rule['itemsToBuy'] >= 0 ) {
				$total -= $rule['itemsToBuy'];
				$tt    += $rule['itemsToGet'];
			}
		}

		return $tt;
	}
}
