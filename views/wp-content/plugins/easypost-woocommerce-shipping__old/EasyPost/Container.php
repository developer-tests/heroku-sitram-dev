<?php

namespace EasyPost;

class Container extends EasypostResource {

	/**
	 * Retrieve a container
	 *
	 * @param string $id
	 * @param string $apiKey
	 * @return mixed
	 */
	public static function retrieve( $id, $apiKey = null ) {
		return self::_retrieve( get_class(), $id, $apiKey );
	}

	/**
	 * Retrieve all containers
	 *
	 * @param mixed  $params
	 * @param string $apiKey
	 * @return mixed
	 */
	public static function all( $params = null, $apiKey = null ) {
		return self::_all( get_class(), $params, $apiKey );
	}

	/**
	 * Save a container
	 *
	 * @return $this
	 */
	public function save() {
		return self::_save( get_class() );
	}

	/**
	 * Create a container
	 *
	 * @param mixed  $params
	 * @param string $apiKey
	 * @return mixed
	 */
	public static function create( $params = null, $apiKey = null ) {
		if ( ! isset( $params['container'] ) || ! is_array( $params['container'] ) ) {
			$clone = $params;
			unset( $params );
			$params['container'] = $clone;
		}

		return self::_create( get_class(), $params, $apiKey );
	}
}
