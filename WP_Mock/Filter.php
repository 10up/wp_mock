<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Eric
 * Date: 3/26/13
 * Time: 9:21 AM
 * To change this template use File | Settings | File Templates.
 */

namespace WP_Mock;


class Filter {
	/**
	 * @var string Filter name
	 */
	protected $filter;

	/**
	 * @var array Individual processor collection
	 */
	protected $processors;

	public function __construct( $filter ) {
		$this->filter = $filter;
	}

	public function with() {
		$args = func_get_args();

		$processors = $this->processors;
		for( $i = 0; $i < count( $args ) - 1; $i++ ) {
			$arg = $args[ $i ];

			if ( ! isset( $processors[ $arg ] ) ) {
				$processors[ $arg ] = array();
			}

			$processors = $processors[ $arg ];
		}

		$responder = new Filter_Responder( $args );
		$processors[ func_get_arg( func_num_args() - 1 ) ] = $responder;

		return $responder;
	}

	/**
	 * Apply the stored filter.
	 *
	 * @return mixed
	 */
	public function apply() {
		$args = func_get_args();

		$processors = $this->processors;
		for( $i = 0; $i < count( $args ) - 1; $i++ ) {
			$arg = $args[ $i ];

			if ( ! isset( $processors[ $arg ] ) ) {
				return func_get_arg( 0 );
			}

			$processors = $processors[ $arg ];
		}

		return $processors[ func_get_arg( func_num_args() - 1 ) ];
	}
}

class Filter_Responder {
	/**
	 * @var mixed
	 */
	protected $value;

	public function reply( $value ) {
		$this->value = $value;
	}

	public function send() {
		return $this->value;
	}
}