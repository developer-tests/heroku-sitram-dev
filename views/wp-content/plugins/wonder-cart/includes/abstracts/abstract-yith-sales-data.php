<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Abstract YITH Sales Data
 *
 * @author  YITH
 * @package YITH\Sales\Abstracts
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Abstract_YITH_Sales_Data' ) ) {
	/**
	 * YITH Sales generic object
	 *
	 * @since 1.0.0
	 */
	abstract class Abstract_YITH_Sales_Data {

		/**
		 * ID for this object.
		 *
		 * @since 1.0.0
		 * @var int
		 */
		protected $id;


		/**
		 * Core data for this object. Name value pairs (name + default value).
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $data = array();

		/**
		 * Extra data for this object. Name value pairs (name + default value).
		 * Used as a standard way for subclasses (like campaign types) to add
		 * additional information to an inherited class.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $extra_data = array();

		/**
		 * The default data
		 *
		 * @var array
		 */
		protected $default_data = array();


		/**
		 * The data changes
		 *
		 * @var array
		 */
		protected $changes = array();

		/**
		 * Post type.
		 *
		 * @var string
		 */
		protected $post_type = 'yith_campaign';


		/**
		 * This is false until the object is read from the DB.
		 *
		 * @since 1.0.0
		 * @var bool
		 */
		protected $object_read = false;

		/**
		 * This is the name of this object type.
		 *
		 * @var string
		 */
		protected $object_type = 'cpt_object';


		/**
		 * Allow to add extra meta in the object.
		 *
		 * @var array
		 */
		protected $meta_data = array();


		/**
		 * Default constructor.
		 *
		 * @param int|object|array $obj ID to load from the DB (optional) or already queried data.
		 */
		public function __construct( $obj = 0 ) {
			$this->data         = array_merge( $this->data, $this->extra_data );
			$this->default_data = $this->data;

		}


		/**
		 * Only store the object ID to avoid serializing the data object instance.
		 *
		 * @return array
		 */
		public function __sleep() {
			return array( 'id' );
		}

		/**
		 * Re-run the constructor with the object ID.
		 *
		 * If the object no longer exists, remove the ID.
		 */
		public function __wakeup() {
			try {
				$this->__construct( absint( $this->id ) );
			} catch ( Exception $e ) {
				$this->set_id( 0 );
				$this->set_object_read( true );
			}
		}

		/**
		 * Returns the unique ID for this object.
		 *
		 * @return int
		 * @since  2.6.0
		 */
		public function get_id() {
			return $this->id;
		}

		/**
		 * Change data to JSON format.
		 *
		 * @return string Data in JSON format.
		 * @since  2.6.0
		 */
		public function __toString() {
			return wp_json_encode( $this->get_data() );
		}

		/**
		 * Returns all data for this object.
		 *
		 * @return array
		 * @since  2.6.0
		 */
		public function get_data() {
			return array_merge( array( 'id' => $this->get_id() ), $this->data );
		}

		/**
		 * Returns array of expected data keys for this object.
		 *
		 * @return array
		 * @since   1.0.0
		 */
		public function get_data_keys() {
			return array_keys( $this->data );
		}

		/**
		 * Returns all "extra" data keys for an object (for sub objects like product types).
		 *
		 * @return array
		 * @since  3.0.0
		 */
		public function get_extra_data_keys() {
			return array_keys( $this->extra_data );
		}

		/**
		 * Return external meta
		 *
		 * @param string $key The meta key.
		 *
		 * @return mixed
		 * @author YITH
		 * @since  3.0.0
		 */
		public function get_meta( $key ) {
			$value        = false;
			$general_meta = get_post_meta( $this->get_id(), $this->get_type(), true );
			if ( is_string( $general_meta ) ) {
				$this->meta_data = json_decode( $general_meta, ARRAY_A );

				if ( isset( $this->meta_data[ $key ] ) ) {
					$value = $this->meta_data[ $key ];
				}
			}
			return $value;
		}

		/**
		 * Set ID.
		 *
		 * @param int $id ID.
		 *
		 * @since 1.0.0
		 */
		public function set_id( $id ) {
			$this->id = absint( $id );
		}


		/**
		 * Set object read property.
		 *
		 * @param boolean $read Should read?.
		 *
		 * @since 1.0.0
		 */
		public function set_object_read( $read = true ) {
			$this->object_read = (bool) $read;
		}

		/**
		 * Get object read property.
		 *
		 * @return boolean
		 * @since  1.0.0
		 */
		public function get_object_read() {
			return (bool) $this->object_read;
		}


		/**
		 * Sets a prop for a setter method.
		 *
		 * This stores changes in a special array so we can track what needs saving
		 * the the DB later.
		 *
		 * @param string $prop  Name of prop to set.
		 * @param mixed  $value Value of the prop.
		 *
		 * @since 1.0.0
		 */
		protected function set_prop( $prop, $value ) {
			if ( array_key_exists( $prop, $this->data ) ) {
				$this->data[ $prop ] = $value;
			}

		}

		/**
		 * Set all props to default values.
		 *
		 * @since 3.0.0
		 */
		public function set_defaults() {
			$this->data    = $this->default_data;
			$this->changes = array();
			$this->set_object_read( false );
		}

		/**
		 * Return data changes only.
		 *
		 * @return array
		 * @since 1.0.0
		 */
		public function get_changes() {
			return $this->changes;
		}

		/**
		 * Merge changes with data and clear.
		 *
		 * @since 1.0.0
		 */
		public function apply_changes() {
			$this->data    = array_replace_recursive( $this->data, $this->changes ); // @codingStandardsIgnoreLine
			$this->changes = array();
		}

		/**
		 * Prefix for action and filter hooks on data.
		 *
		 * @return string
		 * @since  1.0.0
		 */
		protected function get_hook_prefix() {
			return 'yith_sales_' . $this->object_type . '_get_';
		}

		/**
		 * Gets a prop for a getter method.
		 *
		 * Gets the value from either current pending changes, or the data itself.
		 * Context controls what happens to the value before it's returned.
		 *
		 * @param string $prop    Name of prop to get.
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return mixed
		 * @since  1.0.0
		 */
		protected function get_prop( $prop, $context = 'view' ) {
			$value = null;

			if ( is_array( $this->data ) && array_key_exists( $prop, $this->data ) ) {
				$value = $this->data[ $prop ];

				if ( 'view' === $context ) {
					/**
					 * APPLY_FILTERS: yith_sales_$OBJECT_TYPE_get_$PROP
					 *
					 * This filter change the value of an object property.
					 *
					 * @param mixed           $value  The value to filter.
					 * @param Abstract_YITH_Sales_Data $object The current object.
					 *
					 * @return mixed
					 */
					$value = apply_filters( $this->get_hook_prefix() . $prop, $value, $this );
				}
			}

			return $value;
		}


		/**
		 * Get the post status of this object.
		 *
		 * @return string
		 * @since 3.0.0
		 */
		public function get_post_status() {
			return get_post_status( $this->get_id() );
		}

		/**
		 * Return the data of last modified
		 *
		 * @return false|int|string
		 */
		public function get_data_modified() {
			$post_object = get_post( $this->get_id() );

			return mysql_to_rfc3339( $post_object->post_modified );
		}

		/**
		 * When invalid data is found, throw an exception unless reading from the DB.
		 *
		 * @param string $code             Error code.
		 * @param string $message          Error message.
		 * @param int    $http_status_code HTTP status code.
		 * @param array  $data             Extra error data.
		 *
		 * @throws Exception Data Exception.
		 * @since 1.0.0
		 */
		protected function error( $code, $message, $http_status_code = 400, $data = array() ) {
			throw new Exception( $code, $message, $http_status_code, $data );
		}

	}
}
