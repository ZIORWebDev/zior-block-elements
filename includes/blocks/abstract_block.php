<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

abstract class Abstract_Block {

	protected $name;
	protected $shortcode_function;

	abstract protected function get_labels();

	abstract protected function get_attributes();

	/**
	 * Handles output of the block.
	 *
	 * @param array $attributes settings sent through.
	 *
	 * @return string
	 */
	public function render_callback( $attributes ) {
		return call_user_func( apply_filters( 'zior_blocks_callback_' . $this->name, $this->shortcode_function, $this->name ), $attributes );
	}

	public function register() {
		add_filter( 'zior_blocks', function ( $blocks ) {
			$blocks[ $this->name ] = array(
				'labels'     => $this->get_labels(),	
				'attributes' => $this->get_attributes(),
			);
			return $blocks;
		} );

		register_block_type( 'zior/' . $this->name, [
			'attributes'      => $this->get_attributes(),
			'render_callback' => [ $this, 'render_callback' ],
		] );
	}
}