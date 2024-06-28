<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Abstract YITH Sales Promotion Campaign
 *
 * @author  YITH
 * @package YITH\Sales\Abstracts
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Abstract_YITH_Sales_Promotion_Campaign' ) ) {

	/**
	 * The class manage the campaign with the promotion feature.
	 */
	class Abstract_YITH_Sales_Promotion_Campaign extends Abstract_YITH_Sales_Campaign {

		/**
		 * Store the promotion data.
		 *
		 * @var string[]
		 */
		protected $promotion_data = array(
			'promote_campaign' => 'no',
			'promotion_style'  => 'popup',
		);

		/**
		 * Initialize category discount campaign.
		 *
		 * @param Abstract_YITH_Sales_Promotion_Campaign|int $campaign Campaign instance or ID.
		 */
		public function __construct( $campaign = 0 ) {

			$this->extra_data = array_merge( $this->extra_data, $this->promotion_data );
			parent::__construct( $campaign );
		}

		/**
		 * Get the promote campaign option
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 *
		 * @since 1.0.0
		 */
		public function get_promote_campaign( $context = 'view' ) {
			return $this->get_prop( 'promote_campaign', $context );
		}

		/**
		 * Get the promote campaign style option
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 *
		 * @since 1.0.0
		 */
		public function get_promotion_style( $context = 'view' ) {
			return $this->get_prop( 'promotion_style', $context );
		}

		/**
		 * Set the promote campaign option
		 *
		 * @param string $promote_campaign The value get from db.
		 *
		 * @since 1.0.0
		 */
		public function set_promote_campaign( $promote_campaign ) {
			$this->set_prop( 'promote_campaign', $promote_campaign );
		}

		/**
		 * Set the promotion campaign style option
		 *
		 * @param string $promotion_style The option from db.
		 *
		 * @since 1.0.0
		 */
		public function set_promotion_style( $promotion_style ) {
			$this->set_prop( 'promotion_style', $promotion_style );
		}

		/**
		 * Check if the campaign need to show the promotion
		 *
		 * @return bool
		 */
		public function show_promotion() {
			return $this->get_promote_campaign() === 'yes';
		}
	}
}
