<?php
/**
 * Main Controller
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
class YITH_Sales_Controller {

	use YITH_Sales_Trait_Singleton;

	/**
	 * Campaigns
	 *
	 * @var array
	 */
	protected $campaigns = array();

	/**
	 * Controller instances
	 *
	 * @var array
	 */
	protected $controllers = array();


	/**
	 * Constructor
	 */
	private function __construct() {
		// Plugin framework implementation.
		add_action( 'init', array( $this, 'init' ), 50 );

		if ( yith_sales_is_using_block_template_in_archive_product() ) {
			add_filter( 'render_block_woocommerce/product-image', array( $this, 'show_block_badge' ), 10, 2 );
		}
		add_action( 'woocommerce_before_shop_loop_item_title', array( $this, 'show_badge' ), 100 );
		add_action( 'woocommerce_product_thumbnails', array( $this, 'show_badge' ), 100 );

		// Add to cart form in the single product page.
		add_action( 'template_redirect', array( $this, 'remove_add_to_cart' ), 30 );

		// Adjust pricing on cart.
		add_filter( 'woocommerce_cart_item_price', array( $this, 'adjust_cart_item_price' ), 10, 3 );
		add_filter( 'woocommerce_cart_item_subtotal', array( $this, 'woocommerce_cart_item_subtotal' ), 10, 3 );
		add_filter( 'woocommerce_cart_item_subtotal', array( $this, 'format_cart_item_subtotal' ), 100, 3 );

		add_action( 'woocommerce_after_cart_item_quantity_update', array( $this, 'update_quantity_on_cart' ), 1, 2 );
		add_action( 'woocommerce_remove_cart_item', array( $this, 'remove_item_from_cart' ), 100 );
		add_action( 'woocommerce_cart_item_restored', array( $this, 'restore_item_on_cart' ), 100 );

		// Add info on cart for campaign.
		add_filter( 'woocommerce_add_cart_item', array( $this, 'check_campaign_on_cart_item' ), 10, 2 );
		add_filter( 'woocommerce_store_api_add_to_cart_data', array( $this, 'store_api_check_campaign_on_cart_item' ) );

		add_action( 'woocommerce_before_calculate_totals', array( $this, 'calculate_discounts' ), 200 );
		add_action( 'woocommerce_cart_loaded_from_session', array( $this, 'calculate_discounts' ), 200 );

		// Custom add to cart.
		add_action( 'wp_loaded', array( $this, 'add_to_cart' ), 20 );

		// Modal & Banner Promotions.
		add_action( 'wp_footer', array( $this, 'add_modal_promotion_template' ), 20 );
		add_action( 'wp_body_open', array( $this, 'add_banner_promotion_template' ) );
	}


	/**
	 * Get the campaigns and load the controllers
	 *
	 * @return void
	 * @author YITH
	 * @since  1.0.0
	 */
	public function init() {
		$this->campaigns   = $this->sort_campaigns( $this->get_campaigns() );
		$grouped_campaigns = yith_sales_grouped_campaigns( $this->campaigns );
		$controller_list   = array();
		if ( $grouped_campaigns ) {
			foreach ( $grouped_campaigns as $type => $group ) {
				$controller = yith_sales_get_controller_by_type( $type );
				if ( class_exists( $controller ) ) {
					$controller_list[ $controller ][ $type ] = $group;
				}
			}
		}

		foreach ( $controller_list as $controller => $campaigns ) {
			$this->controllers[ $controller ] = new $controller( $campaigns );
		}

	}


	/**
	 * Remove add to cart if the product is valid for a quantity discount
	 *
	 * @return void
	 * @since  1.0.0
	 * @author YITH
	 */
	public function remove_add_to_cart() {
		if ( is_product() ) {
			global $product;

			if ( is_string( $product ) ) {
				$product_obj = get_page_by_path( $product, OBJECT, 'product' );
				$product     = wc_get_product( $product_obj->ID );
			}

			foreach ( $this->campaigns as $campaign ) {
				if ( method_exists( $campaign, 'is_valid_for' ) && $campaign->is_valid_for( $product ) && method_exists( $campaign, 'remove_add_to_cart' ) && $campaign->remove_add_to_cart() ) {
					remove_action( 'woocommerce_simple_add_to_cart', 'woocommerce_simple_add_to_cart', 30 );
					remove_action( 'woocommerce_variable_add_to_cart', 'woocommerce_variable_add_to_cart', 30 );
					break;
				}
			}
		}

	}

	/**
	 * Return a valid campaign for a product
	 *
	 * @param WC_Product $product Product.
	 *
	 * @return Abstract_YITH_Sales_Campaign
	 * @since  1.0.0
	 * @author YITH
	 */
	public function get_valid_campaign_by_product( $product ) {
		if ( ! $product ) {
			return false;
		}

		$valid_campaign = false;

		foreach ( $this->campaigns as $campaign ) {
			if ( $campaign->is_valid_for( $product ) ) {
				$valid_campaign = $campaign;
				break;
			}
		}

		return $valid_campaign;
	}

	/**
	 * Show a badge on current product.
	 *
	 * @return void
	 * @author YITH
	 * @since  1.0.0
	 */
	public function show_badge() {
		global $product;
		echo $this->get_product_badge( $product );
	}

	/**
	 * Get the badges for the product
	 *
	 * @param WC_Product $product The product.
	 *
	 * @return string
	 */
	public function get_product_badge( $product ) {
		$campaigns  = $this->get_campaigns_for_controller( $product );
		$badge_list = '';
		if ( $campaigns ) {
			$badge_position = get_option( 'yith_sales_badge_position', 'top_right' );
			$index          = 1;
			foreach ( $campaigns as $campaign ) {
				if ( $campaign && method_exists( $campaign, 'show_badge_for_product' ) && $campaign->show_badge_for_product( $product ) ) {
					$badge_text = method_exists( $campaign, 'get_badge_text_by_product' ) ? $campaign->get_badge_text_by_product( $product ) : $campaign->get_badge_text();
					$args       = array(
						'badgeText' => wp_kses_post( $badge_text ),
					);
					$badge_list .= '<div class="yith-sales-badge ' . esc_attr( $badge_position ) . '" style="background-color:' . esc_attr( $campaign->get_badge_background_color() ) . '; color:' . esc_attr( $campaign->get_badge_text_color() ) . '" data-info="' . yith_sales_get_json( $args ) . '" data-index="' . ( $index ++ ) . '"></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
			}
		}

		return $badge_list;
	}

	/**
	 * Get the active campaigns grouped by type
	 *
	 * @return array
	 * @author YITH
	 * @since  1.0.0
	 */
	public function get_campaigns() {
		if ( $this->campaigns ) {
			return $this->campaigns;
		}

		return yith_sales_get_campaigns();
	}

	public function get_campaigns_for_controller( $product ) {
		$campaigns = array();
		foreach ( $this->controllers as $key => $controller ) {
			if ( ! is_null( $controller ) && is_callable( array( $controller, 'get_valid_campaign_by_product' ) ) ) {
				$campaign = $controller->get_valid_campaign_by_product( $product );
				if ( $campaign ) {
					$campaigns[] = $campaign;
				}
			}
		}

		return $campaigns;
	}

	/**
	 * Get the specific controller instance if exist
	 *
	 * @param string $controller The controller class.
	 *
	 * @return false|mixed
	 *
	 * @author YITH
	 * @since  1.0.0
	 */
	public function get_controller( $controller ) {
		if ( isset( $this->controllers[ $controller ] ) && ! is_null( $this->controllers[ $controller ] ) ) {
			return $this->controllers[ $controller ];
		}

		return false;
	}


	/**
	 * Add info to cart item if there is a campaign valid for this product
	 *
	 * @param array  $cart_item The cart item added to cart.
	 * @param string $cart_item_key Cart item key.
	 *
	 * @return array
	 *
	 * @author YITH
	 * @since  1.0.0
	 */
	public function check_campaign_on_cart_item( $cart_item, $cart_item_key ) {
		$request = $_REQUEST; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $campaign_ids = array();
        if( isset( $request['campaign_ids'])) {
            $campaign_ids = $request['campaign_ids'];
        }elseif( isset( $request['campaign_id'])){
            $campaign_ids = array($request['campaign_id'] );
        }

		if ( count( $campaign_ids ) === 0 || isset( $cart_item_data['yith_sales_main_cart_item_key'] ) ) {
			return $cart_item;
		}
		$product_id = $cart_item['product_id'];
		if ( isset( $request['add-to-cart'] ) || ( isset( $request['wc-ajax'] ) && 'add_to_cart' === sanitize_text_field( wp_unslash( $request['wc-ajax'] ) ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			foreach ( $campaign_ids as $campaign_id ) {
				$campaign = yith_sales_get_campaign( $campaign_id );

				$is_for_product = method_exists( $campaign, 'can_be_applied' ) ? $campaign->can_be_applied( $product_id ) : true;
				$is_valid       = $campaign->is_active() && $is_for_product;
				if ( $is_valid ) {
					$cart_item = $campaign->get_cart_item_data( $cart_item, $product_id, $cart_item['variation_id'], $cart_item['quantity'] );
				}
			}
		}

		return $cart_item;
	}

	public function store_api_check_campaign_on_cart_item( $cart_data ) {
		$product = wc_get_product( $cart_data['id'] );

		return apply_filters( 'yith_sales_store_api_check_campaign_on_cart_item', $cart_data, $product );
	}

	/**
	 * Apply the price rules in the cart.
	 *
	 * @author YITH
	 * @since  1.0.0
	 */
	public function calculate_discounts() {

		if ( count( $this->campaigns ) > 0 ) {
			$sorted_cart = yith_sales_clone_cart();
			uasort( $sorted_cart, 'yith_sales_sort_cart_by_price' );
			foreach ( $sorted_cart as $cart_item_key => $cart_item ) {
				if ( isset( $cart_item['yith_sales']['campaigns'] ) ) {

					foreach ( $cart_item['yith_sales']['campaigns'] as $index => $campaign_id ) {
						$campaign = yith_sales_get_campaign( $campaign_id );
						if ( ! $campaign ) {
							continue;
						}

						$is_active = $campaign->is_active();
						$applied   = false;
						if ( $is_active && is_callable( array( $campaign, 'apply_rule_in_cart' ) ) ) {
							$applied = $campaign->apply_rule_in_cart( $cart_item, $cart_item_key );
						}

						if ( ( ! $is_active || ! $applied ) && ( isset( WC()->cart->cart_contents[ $cart_item_key ]['yith_sales_discounts']['applied_discounts'] ) || isset( WC()->cart->cart_contents[ $cart_item_key ]['yith_sales_discounts'] ) ) ) {
							$discounts = WC()->cart->cart_contents[ $cart_item_key ]['yith_sales_discounts']['applied_discounts'];
							if ( $discounts ) {
								foreach ( $discounts as $discount ) {
									if ( $discount['id'] === $campaign->get_id() ) {
										unset( WC()->cart->cart_contents[ $cart_item_key ]['yith_sales_discounts'] );
									}
								}
							}

							unset( WC()->cart->cart_contents[ $cart_item_key ]['yith_sales']['campaigns'] [ $index ] );
							if ( empty( WC()->cart->cart_contents[ $cart_item_key ]['yith_sales']['campaigns'] ) ) {
								unset( WC()->cart->cart_contents[ $cart_item_key ]['yith_sales']['campaigns'] );
							}
						}
					}
				}
			}
		} else {

			$this->reset_discounts();
		}

		$this->maybe_remove_coupon();
	}

	/**
	 * Reset the discounts if no campaigns are active
	 *
	 * @return void
	 * @author YITH
	 * @since  1.0.0
	 */
	public function reset_discounts() {
		foreach ( WC()->cart->get_cart_contents() as $cart_item_key => $cart_item ) {
			unset( WC()->cart->cart_contents[ $cart_item_key ] ['yith_sales'] );
			unset( WC()->cart->cart_contents[ $cart_item_key ] ['yith_sales_discounts'] );
			unset( WC()->cart->cart_contents[ $cart_item_key ] ['yith_sales_gift_product'] );
			unset( WC()->cart->cart_contents[ $cart_item_key ] ['yith_sales_main_cart_item_key'] );
		}
	}

	/**
	 * Check if is needed remove from cart our coupon
	 *
	 * @return void
	 * @author YITH
	 * @since  1.0.0
	 */
	public function maybe_remove_coupon() {

		$coupon_to_remove = false;

		$grouped_campaigns = yith_sales_grouped_campaigns( $this->campaigns );
		$cart_campaigns    = isset( $grouped_campaigns['cart-discount'] ) ?? false;

		if ( ! $cart_campaigns ) {
			foreach ( WC()->cart->applied_coupons as $coupon_code ) {
				$coupon = new WC_Coupon( $coupon_code );
				if ( $coupon->get_meta( 'yith_sales_coupon' ) ) {
					$coupon_to_remove = $coupon_code;
					break;
				}
			}
			if ( $coupon_to_remove ) {
				WC()->cart->remove_coupon( $coupon_to_remove );
				$coupon = new WC_Coupon( $coupon_to_remove );
				$coupon->delete( true );
			}
		}
	}

	/**
	 * Add in the subtotal column info about the campaign applied in the cart item
	 *
	 * @param string $subtotal The old subtotal.
	 * @param array  $cart_item The cart item.
	 * @param string $cart_item_key The cart item key.
	 *
	 * @return string
	 * @author YITH
	 * @since  1.0.0
	 */
	public function format_cart_item_subtotal( $subtotal, $cart_item, $cart_item_key ) {
		// Manage the fbt items.
		if ( isset( $cart_item['yith_sales_discounts']['applied_discounts'] ) ) {
			$subtotal = $this->format_items( $cart_item, $cart_item_key, $subtotal );
		}

		return $subtotal;
	}

	/**
	 * Format the items with the info about campaign
	 *
	 * @param array  $cart_item The cart item.
	 * @param string $cart_item_key The cart key.
	 * @param string $subtotal The subtotal.
	 *
	 * @return string
	 * @author YITH
	 * @since  1.0.0
	 */
	public function format_items( $cart_item, $cart_item_key, $subtotal ) {
		$product   = wc_get_product( $cart_item['data'] );
		$price     = $cart_item['data']->get_price( 'edit' );
		$old_price = $product->get_regular_price();
		if ( $price !== $old_price ) {
			$old_subtotal = yith_sales_get_product_subtotal( $cart_item['data'], $old_price, $cart_item['quantity'] );
			$subtotal     = wc_format_sale_price( $old_subtotal, $subtotal );

			if ( isset( $cart_item['yith_sales_discounts']['id'] ) ) {
				$campaign = yith_sales_get_campaign( $cart_item['yith_sales_discounts']['id'] );
				if ( $campaign ) {
					$subtotal .= sprintf( '<div class="yith-sales-discount-applied"><span >%s</span></div>', $campaign->get_campaign_name() );
				}
			}
		}

		return $subtotal;
	}

	/**
	 * Get the product row subtotal using the price not filtered
	 *
	 * Gets the tax etc to avoid rounding issues.
	 *
	 * When on the checkout (review order), this will get the subtotal based on the customer's tax rate rather than the base rate.
	 *
	 * @param string $price Price to filter.
	 * @param array  $cart_item Cart item.
	 * @param string $cart_key Cart item key.
	 *
	 * @return string Formatted price.
	 *
	 * @author YITH
	 * @since  1.0.0
	 */
	public function woocommerce_cart_item_subtotal( $price, $cart_item, $cart_key ) {
		if ( ! isset( $cart_item['yith_sales'] ) ) {
			return $price;
		}
		$campaigns = ! empty( $cart_item['yith_sales']['campaigns'] ) ? $cart_item['yith_sales']['campaigns'] : array();
		foreach ( $campaigns as $campaign ) {
			$campaign = yith_sales_get_campaign( $campaign );
			if ( $campaign && $campaign->get_type() === 'category-discount' ) {
				return $price;
			}
		}
		$product  = $cart_item['data'];
		$quantity = $cart_item['quantity'];

		return yith_sales_get_product_subtotal( $product, $product->get_price(), $quantity );
	}

	/**
	 * Get the product row price per item.
	 *
	 * @param string $price Price to filter.
	 * @param array  $cart_item Cart item.
	 * @param string $cart_key Cart item key.
	 *
	 * @return string Formatted price.
	 *
	 * @author YITH
	 * @since  1.0.0
	 */
	public function adjust_cart_item_price( $price, $cart_item, $cart_key ) {

		if ( ! isset( $cart_item['yith_sales']['campaigns'] ) ) {
			return $price;
		}

		$campaigns = $cart_item['yith_sales']['campaigns'];
		foreach ( $campaigns as $campaign ) {
			$campaign = yith_sales_get_campaign( $campaign );
			if ( $campaign && $campaign->get_type() === 'category-discount' ) {
				return $price;
			}
		}

		$product = $cart_item['data'];

		return yith_sales_get_product_price( $product );
	}

	/**
	 * Sort the campaigns so the rule more specific will be applied before
	 *
	 * @param array $campaigns List of campaigns.
	 *
	 * @return array
	 * @since  1.0.0
	 * @author YITH
	 */
	public function sort_campaigns( $campaigns ) {
		$ordered_keys      = yith_sales()->get_order_of_campaigns();
		$ordered_campaigns = array();
		foreach ( $ordered_keys as $key ) {
			foreach ( $campaigns as $campaign ) {

				if ( $campaign->get_type() === $key ) {
					$ordered_campaigns[] = $campaign;
				}
			}
		}

		return $ordered_campaigns;
	}

	/**
	 * Sort campaign by data modified
	 *
	 * @param int $campaign_id_a The first campaign.
	 * @param int $campaign_id_b The second campaign.
	 *
	 * @return int
	 * @author YITH
	 * @since  1.0.0
	 */
	public function sort_campaigns_by_modified_date( $campaign_id_a, $campaign_id_b ) {
		$campaign_a = yith_sales_get_campaign( $campaign_id_a );
		$campaign_b = yith_sales_get_campaign( $campaign_id_b );
		$modified_a = strtotime( $campaign_a->get_data_modified() );
		$modified_b = strtotime( $campaign_b->get_data_modified() );
		if ( $modified_a === $modified_b ) {
			return 0;
		}

		return $modified_a > $modified_b ? - 1 : 1;
	}

	/**
	 * Custom add to cart.
	 *
	 * @return void
	 * @author YITH
	 * @since  1.0.0
	 */
	public function add_to_cart() {
		$request = json_decode( file_get_contents( 'php://input' ), ARRAY_A );

		if ( ! isset( $request['yith-sales-add-to-cart'] ) || ! is_numeric( wp_unslash( $request['yith-sales-add-to-cart'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			return;
		}

		if ( ! isset( $request['campaign_id'] ) || ! is_numeric( wp_unslash( $request['campaign_id'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			return;
		}

		wc_nocache_headers();

		$data        = false;
		$campaign_id = absint( wp_unslash( $request['campaign_id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$campaign    = yith_sales_get_campaign( $campaign_id );
		if ( $campaign ) {
			$data = method_exists( $campaign, 'add_to_cart' ) ? $campaign->add_to_cart( $request ) : false;
		}
		if ( ! $data ) {
			wp_send_json_error( new WP_Error( 'add_to_cart', 'Error during the add to cart' ) );
		} else {
			wp_send_json( $data );
		}

	}

	/**
	 * Add in the footer the template to show modal promotions
	 *
	 * @return void
	 * @author YITH
	 * @since  1.0.0
	 */
	public function add_modal_promotion_template() {
		$campaign_ids = array();
		if ( $this->can_show_modal_promotion() ) {
			$total_to_show = $this->get_max_modal_promotion_in_a_page();

			foreach ( $this->campaigns as $campaign ) {
				if ( count( $campaign_ids ) === $total_to_show ) {
					break;
				}
				if ( $campaign->show_promotion() && 'popup' === $campaign->get_promotion_style() && ! yith_sales_is_modal_visited( $campaign ) ) {
					$campaign_ids[] = $campaign->get_id();
				}
			}

			if ( ! empty( $campaign_ids ) ) { ?>
                <div class="yith-sales-modal-promotions"
                     data-options="<?php echo yith_sales_get_json( array( 'campaignIDS' => $campaign_ids ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
                </div>
				<?php
			}
		}
	}

	/**
	 * Check if the modal promotion can be show in mobile devices
	 *
	 * @return bool
	 */
	public function show_modal_promotion_in_mobile() {
		return 'yes' !== get_option( 'yith_sales_hide_modal_on_mobile', 'yes' );
	}

	/**
	 * Check if the modal promotion can be show in current page
	 *
	 * @return bool
	 */
	public function show_modal_in_page() {
		global $post;
		$hide_in_pages      = get_option( 'yith_sales_hide_modal_on_pages', 'no' );
		$pages              = get_option( 'yith_sales_page_where_hide_modal', array() );
		$hide_product_pages = get_option( 'yith_sales_hide_on_pages', 'yes' );

		$current_post_id = $post->ID;

		if ( ( 'yes' === $hide_in_pages && ( in_array( $current_post_id, array_map( 'intval', $pages ), true ) ) ) || ( 'yes' === $hide_product_pages && is_product() ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Check if is possible to show the modal promotion.
	 *
	 * @return bool
	 * @author YITH
	 * @since  1.0.0
	 */
	public function can_show_modal_promotion() {
		if ( wp_is_mobile() ) {
			return $this->show_modal_promotion_in_mobile();
		} else {
			return $this->show_modal_in_page();
		}
	}

	/**
	 * Return the max modal promotions that can be show in a page
	 *
	 * @return int
	 * @author YITH
	 * @since  1.0.0
	 */
	public function get_max_modal_promotion_in_a_page() {
		return intval( get_option( 'yith_sales_max_notification_per_page', 5 ) );
	}

	/**
	 * Add the top banner template
	 *
	 * @return void
	 * @author YITH
	 * @since  1.0.0
	 */
	public function add_banner_promotion_template() {
		if ( $this->can_show_banner_promotion() ) {

			$campaign_ids = array();
			$campaigns    = $this->campaigns;

			foreach ( $campaigns as $campaign ) {
				if ( $campaign->show_promotion() && 'banner' === $campaign->get_promotion_style() ) {
					$campaign_ids[] = $campaign->get_id();
				}
			}

			if ( ! empty( $campaign_ids ) ) {
				$how_show = get_option( 'yith_sales_more_banner_in_page', 'random' );
				if ( 'latest' === $how_show ) {
					usort( $campaign_ids, array( $this, 'sort_campaigns_by_modified_date' ) );
					$campaign_id = $campaign_ids[0];
				} else {
					$random_index = wp_rand( 0, count( $campaign_ids ) - 1 );
					$campaign_id  = $campaign_ids[ $random_index ];
				}
				?>
                <div class="yith-sales-banner-promotion"
                     data-options="<?php echo yith_sales_get_json( array( 'campaignID' => $campaign_id ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"></div>
				<?php
			}
		}
	}

	/**
	 * Is possible show the banner promotion ?
	 *
	 * @return bool
	 * @author YITH
	 * @since  1.0.0
	 */
	public function can_show_banner_promotion() {
		if ( wp_is_mobile() ) {
			return $this->show_banner_promotion_in_mobile();
		} else {
			return $this->show_banner_in_page();
		}
	}

	/**
	 * Check if the tob banner promotions can be show in mobile devices
	 *
	 * @return bool
	 * @author YITH
	 * @since  1.0.0
	 */
	public function show_banner_promotion_in_mobile() {
		return 'yes' !== get_option( 'yith_sales_hide_top_banner_on_mobile', 'yes' );
	}

	/**
	 * Check if the banner promotion can be show in current page
	 *
	 * @return bool
	 * @author YITH
	 * @since  1.0.0
	 */
	public function show_banner_in_page() {
		global $post;
		$hide_in_pages      = get_option( 'yith_sales_hide_top_banner_on_pages', 'no' );
		$pages              = get_option( 'yith_sales_page_where_hide_top_banner', array() );
		$hide_product_pages = get_option( 'yith_sales_hide_top_banner_on_product_pages', 'yes' );
		$current_post_id    = $post->ID;

		if ( ( 'yes' === $hide_in_pages && in_array( $current_post_id, array_map( 'intval', $pages ), true ) ) || ( 'yes' === $hide_product_pages && is_product() ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Check if campaigns need to do some actions when the quantity of a cart item is updated.
	 *
	 * @param string $cart_item_key Cart item key.
	 * @param int    $quantity Quantity of cart item.
	 * @param bool   $calculate_totals Check if is necessary calculate the discount.
	 *
	 * @return void
	 * @author YITH
	 * @since  1.0.0
	 */
	public function update_quantity_on_cart( $cart_item_key, $quantity, $calculate_totals = false ) {

		$cart_item = WC()->cart->get_cart_item( $cart_item_key );
		if ( isset( $cart_item['yith_sales']['campaigns'] ) ) {
			$campaigns = $cart_item['yith_sales']['campaigns'];
			foreach ( $campaigns as $index => $campaign_id ) {
				$campaign = yith_sales_get_campaign( $campaign_id );
				if ( $campaign ) {
					$is_active = $campaign->is_active();
					if ( ! $is_active ) {
						$discounts = WC()->cart->cart_contents[ $cart_item_key ]['yith_sales_discounts']['applied_discounts'] ?? false;
						if ( ! $discounts ) {
							continue;
						}
						foreach ( $discounts as $discount ) {
							if ( $discount['id'] === $campaign->get_id() ) {
								unset( WC()->cart->cart_contents[ $cart_item_key ]['yith_sales_discounts'] );
								unset( WC()->cart->cart_contents[ $cart_item_key ]['yith_sales']['campaigns'] [ $index ] );

								if ( empty( WC()->cart->cart_contents[ $cart_item_key ]['yith_sales']['campaigns'] ) ) {
									unset( WC()->cart->cart_contents[ $cart_item_key ]['yith_sales']['campaigns'] );
								}
							}
						}
					}

					if ( $is_active && method_exists( $campaign, 'check_cart_on_update' ) ) {

						$campaign->check_cart_on_update( $cart_item_key, $cart_item, $quantity );
					}
				}
			}

			if ( $calculate_totals ) {
				WC()->cart->calculate_totals();
			}
		}

		if ( isset( $cart_item['yith_sales_bogo_main_product'] ) ) { // This is for the main product of mixed bogo campaign.
			$campaign = yith_sales_get_campaign( $cart_item['yith_sales_bogo_main_product'] );
			if ( ! $campaign || ! $campaign->is_active() ) {
				unset( WC()->cart->cart_contents[ $cart_item_key ]['yith_sales_bogo_main_product'] );
			} elseif ( $campaign && method_exists( $campaign, 'check_cart_on_update' ) ) {
				$keys_to_check = $campaign->check_cart_on_update( $cart_item_key, $cart_item, $quantity );
				if ( count( $keys_to_check ) > 0 ) {
					$this->apply_a_price_campaign_on_cart_update( $keys_to_check );
				}
				WC()->cart->calculate_totals();
			}
		}

		if ( isset( $cart_item['yith_sales_main_product'] ) ) { // check for fbt campaign, whe the main product is removed.
			$campaign = yith_sales_get_campaign( $cart_item['yith_sales_main_product'] );
			if ( ! $campaign || ! $campaign->is_active() ) {
				unset( WC()->cart->cart_contents[ $cart_item_key ]['yith_sales_main_product'] );
			} elseif ( $campaign && method_exists( $campaign, 'check_cart_on_update' ) ) {
				$keys_to_check = $campaign->check_cart_on_update( $cart_item_key, $cart_item, $quantity );
				if ( count( $keys_to_check ) > 0 ) {
					$this->apply_a_price_campaign_on_cart_update( $keys_to_check );
				}
				WC()->cart->calculate_totals();
			}
		}
	}

	/**
	 * If a  product is removed from cart, and this product was a main product, check if is possible add in the children a price campaign
	 *
	 * @param array $cart_item_keys The keys to check.
	 *
	 * @return void
	 * @author YITH
	 * @since  1.0.0
	 */
	public function apply_a_price_campaign_on_cart_update( $cart_item_keys ) {
		$price_controller = $this->get_controller( 'YITH_Sales_Price_Controller' );

		if ( $price_controller ) {
			foreach ( $cart_item_keys as $cart_item_key ) {
				$cart_item = WC()->cart->get_cart_item( $cart_item_key );
				if ( $cart_item && ! isset( $cart_item['yith_sales'] ) ) {
					$campaign_to_apply = $price_controller->get_valid_campaign_by_product( $cart_item['data'] );
					if ( $campaign_to_apply ) {
						WC()->cart->cart_contents[ $cart_item_key ]['yith_sales']['campaigns'][] = $campaign_to_apply->get_id();
					}
				}
			}
		}
	}

	/**
	 * Remove the discount on this item or in the related items ( for example for fbt products )
	 *
	 * @param string $cart_item_key The cart item key.
	 *
	 * @return void
	 * @author YITH
	 * @since  1.0.0
	 */
	public function remove_item_from_cart( $cart_item_key ) {
		$this->update_quantity_on_cart( $cart_item_key, 0 );

		$modal_controller = $this->get_controller( 'YITH_Sales_Modal_Controller' );
		if ( $modal_controller ) {
			$modal_controller->check_cart( $cart_item_key );
			WC()->cart->calculate_totals();
		}
	}

	/**
	 * Restore the discount on this item or in the related items ( for example for fbt products )
	 *
	 * @param string $cart_item_key The cart item key.
	 *
	 * @return void
	 * @author YITH
	 * @since  1.0.0
	 */
	public function restore_item_on_cart( $cart_item_key ) {
		$cart_item = WC()->cart->get_cart_item( $cart_item_key );
		$quantity  = $cart_item['quantity'];
		$this->update_quantity_on_cart( $cart_item_key, $quantity, true );
	}

	/**
	 * Add the WonderCart badge after Product Image block
	 *
	 * @param string $block_content The block content html
	 * @param array  $block The block args.
	 *
	 * @return string
	 */
	public function show_block_badge( $block_content, $block ) {
		global $product;

		if ( $product ) {
			$block_content .= $this->get_product_badge( $product );
		}

		return $block_content;
	}
}
