<?php
/**
 * REST API Panel Options Controller
 *
 * Handles requests to /settings/{option}
 *
 * @package YITH/Sales/RestAPI
 */

defined( 'ABSPATH' ) || exit;

/**
 * Panel Options controller.
 *
 * @internal
 * @extends WC_REST_Setting_Options_Controller
 */
class YITH_REST_Settings_Controller extends \WC_REST_Setting_Options_Controller {
	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'yith_sales/v1';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'settings/(?P<group_id>[\w-]+)';

	/**
	 * Get the settings schema, conforming to JSON Schema.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function get_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'setting',
			'type'       => 'object',
			'properties' => array(
				'id'          => array(
					'description' => __( 'A unique identifier for the setting.', 'wonder-cart' ),
					'type'        => 'string',
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_title',
					),
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'name'        => array(
					'description' => __( 'A human readable label for the setting used in interfaces.', 'wonder-cart' ),
					'type'        => 'string',
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'value'       => array(
					'description' => __( 'Setting value.', 'wonder-cart' ),
					'type'        => 'mixed',
					'context'     => array( 'view', 'edit' ),
				),
				'default'     => array(
					'description' => __( 'Default value for the setting.', 'wonder-cart' ),
					'type'        => 'mixed',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'placeholder' => array(
					'description' => __( 'Placeholder text to be displayed in text inputs.', 'wonder-cart' ),
					'type'        => 'string',
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'type'        => array(
					'description' => __( 'Type of setting.', 'wonder-cart' ),
					'type'        => 'string',
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
					'context'     => array( 'view', 'edit' ),
					'enum'        => array( 'inline-fields', 'number', 'colorpicker', 'text', 'html' ),
					'readonly'    => true,
				),
				'options'     => array(
					'description' => __( 'Array of options (key value pairs) for inputs such as select, multiselect, and radio buttons.', 'wonder-cart' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'fields'      => array(
					'description' => __( 'Array of fields for nested options', 'wonder-cart' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
			),
		);

		return $this->add_additional_fields_schema( $schema );
	}


	/**
	 * Boolean for if a setting type is a valid supported setting type.
	 *
	 * @param string $type Type.
	 *
	 * @return bool
	 * @since  3.0.0
	 */
	public function is_setting_type_valid( $type ) {
		return in_array(
			$type,
			array(
				'text',         // Validates with validate_setting_text_field.
				'number',       // Validates with validate_setting_text_field.
				'colorpicker',        // Validates with validate_setting_text_field.
				'html',     // Validates with validate_setting_text_field.
				'inline-fields',     // Validates with validate_setting_inline_fields_field.
				'multiselect', // Validates with validate_setting_multiselect_field.
				'onoff', // Validates with validate_setting_onoff_field.
				'radioGroup', // Validated with validate_setting_radio_field.
				'select', // Validated with validate_setting_select_field.
				'media', // Validated with validate_setting_media_field.
			),
			true
		);
	}

	/**
	 * Get all settings in a group.
	 *
	 * @param string $group_id Group ID.
	 *
	 * @return array|WP_Error
	 * @since  3.0.0
	 */
	public function get_group_settings( $group_id ) {

		if ( empty( $group_id ) ) {
			return new WP_Error( 'rest_setting_setting_group_invalid', __( 'Invalid setting group.', 'wonder-cart' ), array( 'status' => 404 ) );
		}

		// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
		$settings = apply_filters( 'yith-sales-for-woocommerce_settings-' . $group_id, array() );

		if ( empty( $settings ) ) {
			return new WP_Error( 'rest_setting_setting_group_invalid', __( 'Invalid setting group.', 'wonder-cart' ), array( 'status' => 404 ) );
		}

		$filtered_settings = array();
		$sections          = $settings['sections'];

		foreach ( $sections as $section ) {
			$options = ! empty( $section['options'] ) ? $section['options'] : array();
			foreach ( $options as $option ) {
				$default             = $this->get_default_value( $option );
				$value               = get_option( $option['id'], $default );
				$option['value']     = $value;
				$filtered_settings[] = $option;
			}
		}

		return $filtered_settings;
	}

	/**
	 * Get the default value of specific option
	 *
	 * @param array $option The option.
	 *
	 * @return  mixed
	 */
	public function get_default_value( $option ) {

		$option_type = $option['type'];
		$default     = false;

		if ( 'inline-fields' === $option_type ) {
			$default = array();
			$fields  = $option['fields'];
			foreach ( $fields as $field ) {
				$default[ $field['id'] ] = ! empty( $field['default'] ) ? $field['default'] : '';
			}
		} elseif ( 'multiselect' === $option_type ) {
			$default = ! empty( $option['default'] ) ? $option['default'] : array();
		} else {
			$default = ! empty( $option['default'] ) ? $option['default'] : '';
		}

		return $default;
	}

	/**
	 * Makes sure the current user has access to READ the settings APIs.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|boolean
	 * @since  1.0.0
	 */
	public function get_items_permissions_check( $request ) {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return new WP_Error( 'yith_sales_rest_cannot_view', __( 'Sorry, you cannot list resources.', 'wonder-cart' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Bulk create, update and delete items.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return array Of WP_Error or WP_REST_Response.
	 * @since  3.0.0
	 */
	public function batch_items( $request ) {
		// Get the request params.
		$items = array_filter( $request->get_params() );
		if ( ! empty( $items['reset'] ) ) {
			$group_id = $request->get_url_params();
			$request  = new WP_REST_Request( $request->get_method() );
			$request->set_body_params( $group_id );

			return $this->reset_all( $request );
		}

		return parent::batch_items( $request );
	}

	/**
	 * Reset the options group to default values
	 *
	 * @param WP_REST_Request $request The request.
	 *
	 * @return array|WP_Error
	 */
	public function reset_all( $request ) {

		$group_id = isset( $request['group_id'] ) ? $request['group_id'] : '';
		if ( empty( $group_id ) ) {
			return new WP_Error( 'rest_setting_setting_group_invalid', __( 'Invalid setting group.', 'wonder-cart' ), array( 'status' => 404 ) );
		}

		// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
		$settings = apply_filters( 'yith-sales-for-woocommerce_settings-' . $group_id, array() );

		if ( empty( $settings ) ) {
			return new WP_Error( 'rest_setting_setting_group_invalid', __( 'Invalid setting group.', 'wonder-cart' ), array( 'status' => 404 ) );
		}

		$filtered_settings = array();
		$sections          = $settings['sections'];

		foreach ( $sections as $section_id => $section ) {
			$options                          = ! empty( $section['options'] ) ? $section['options'] : array();
			$filtered_settings[ $section_id ] = array();
			foreach ( $options as $option ) {
				$option['value']                    = $this->get_default_value( $option );
				$filtered_settings[ $section_id ][] = $option;
				update_option( $option['id'], $option['value'] );
			}
		}

		return $filtered_settings;
	}

	/**
	 * Makes sure the current user has access to UPDATE the settings APIs.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|boolean
	 * @since  1.0.0
	 */
	public function update_items_permissions_check( $request ) {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return new WP_Error( 'yith_sales_rest_cannot_view', __( 'Sorry, you cannot update resources.', 'wonder-cart' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Get the right field type
	 *
	 * @param array $setting The setting.
	 *
	 * @return string
	 */
	public function get_setting_type( $setting ) {
		return strtolower( str_replace( '-', '_', $setting['type'] ) );
	}

	/**
	 * Validate a setting
	 *
	 * @param mixed $value   The value.
	 * @param array $setting The setting.
	 *
	 * @return mixed
	 */
	public function validate_setting( $value, $setting ) {
		$field_type = $this->get_setting_type( $setting );

		if ( is_callable( array( $this, 'validate_setting_' . $field_type . '_field' ) ) ) {
			$value = $this->{'validate_setting_' . $field_type . '_field'}( $value, $setting );
		} else {
			$value = $this->validate_setting_text_field( $value, $setting );
		}

		return $value;

	}

	/**
	 * Update a single setting in a group.
	 *
	 * @param WP_REST_Request $request Request data.
	 *
	 * @return WP_Error|WP_REST_Response
	 * @since  1.0.0
	 */
	public function update_item( $request ) {

		$setting = $this->get_setting( $request['group_id'], $request['id'] );

		if ( is_wp_error( $setting ) ) {
			return $setting;
		}

		$value = $this->validate_setting( $request['value'], $setting );
		if ( is_wp_error( $value ) ) {
			return $value;
		}

		$update_data                   = array();
		$update_data[ $setting['id'] ] = $value;
		$setting['value']              = $value;
		if ( 'select' === $this->get_setting_type( $setting ) ) {
			$options        = array();
			$select_options = wp_list_pluck( $setting['options'], 'value' );
			foreach ( $select_options as $select_option ) {
				$options[ $select_option ] = '';
			}
			$setting['options'] = $options;
		}
		WC_Admin_Settings::save_fields( array( $setting ), $update_data );
		$response = $this->prepare_item_for_response( $setting, $request );

		return rest_ensure_response( $response );
	}

	/**
	 * Validate the inline-fields field
	 *
	 * @param array $value   The values.
	 * @param array $setting The setting.
	 *
	 * @return WP_Error|array
	 */
	public function validate_setting_inline_fields_field( $value, $setting ) {
		$inline_fields = $setting['fields'];

		foreach ( $inline_fields as $inline_field ) {
			if ( isset( $value[ $inline_field['id'] ] ) ) {
				$validated_value = $this->validate_setting( $value[ $inline_field['id'] ], $inline_field );
				if ( is_wp_error( $validated_value ) ) {
					return new WP_Error( 'rest_setting_value_invalid', __( 'An invalid setting value was passed.', 'wonder-cart' ), array( 'status' => 400 ) );
				}
				$value[ $inline_field['id'] ] = $validated_value;
			} else {
				return new WP_Error( 'rest_setting_value_invalid', __( 'An invalid setting value was passed.', 'wonder-cart' ), array( 'status' => 400 ) );
			}
		}

		return $value;
	}

	/**
	 * Validate the onoff field
	 *
	 * @param string $value   The value.
	 * @param array  $setting The setting.
	 *
	 * @return string|WP_Error
	 */
	public function validate_setting_onoff_field( $value, $setting ) {
		return $this->validate_setting_checkbox_field( $value, $setting );
	}

	/**
	 * Validate multiselect based settings.
	 *
	 * @param array $values  Values.
	 * @param array $setting Setting.
	 *
	 * @return array|WP_Error
	 * @since 3.0.0
	 */
	public function validate_setting_multiselect_field( $values, $setting ) {
		if ( empty( $values ) ) {
			return array();
		}

		if ( ! is_array( $values ) ) {
			return new WP_Error( 'rest_setting_value_invalid', __( 'An invalid setting value was passed.', 'wonder-cart' ), array( 'status' => 400 ) );
		}

		$final_values = array();
		$options      = wp_list_pluck( $setting['options'], 'value' );
		foreach ( $values as $value ) {
			// phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
			if ( in_array( $value, $options ) ) {
				$final_values[] = $value;
			}
		}

		return $final_values;
	}

	/**
	 * Check if the value for the radio group field is right
	 *
	 * @param mixed $value   The value.
	 * @param array $setting The setting.
	 *
	 * @return WP_Error|mixed
	 */
	public function validate_setting_radiogroup_field( $value, $setting ) {
		$options = wp_list_pluck( $setting['options'], 'value' );
		// phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
		if ( in_array( $value, $options ) ) {
			return $value;
		}

		return new WP_Error( 'rest_setting_value_invalid', __( 'An invalid setting value was passed.', 'wonder-cart' ), array( 'status' => 400 ) );
	}

	/**
	 * Check if the value for the select field is right
	 *
	 * @param mixed $value   The value.
	 * @param array $setting The setting.
	 *
	 * @return WP_Error|mixed
	 */
	public function validate_setting_select_field( $value, $setting ) {
		$options = wp_list_pluck( $setting['options'], 'value' );
		// phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
		if ( in_array( $value, $options ) ) {
			return $value;
		}

		return new WP_Error( 'rest_setting_value_invalid', __( 'An invalid setting value was passed.', 'wonder-cart' ), array( 'status' => 400 ) );

	}

	/**
	 * Check if the value for the select field is right
	 *
	 * @param mixed $value   The value.
	 * @param array $setting The setting.
	 *
	 * @return WP_Error|mixed
	 */
	public function validate_setting_media_field( $value, $setting ) {
		if ( ! is_array( $value ) ) {
			return new WP_Error( 'rest_setting_value_invalid', __( 'An invalid setting value was passed.', 'wonder-cart' ), array( 'status' => 400 ) );

		}

		return $value;
	}
}
