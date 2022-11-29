<?php
class ZIOR_Blocks_ACF_Button_SC {
	public function init() {
		add_shortcode( 'zior_acf_button', [ $this, 'zior_acf_button' ] );
		add_filter( 'zior_blocks_callback_acf-button', [ $this, 'zior_blocks_callback_acf_button' ], 10, 2 );
	}
	
	public function zior_acf_button( $atts ) {
		$atts = shortcode_atts( [
			'acf_key' => '',
			'post_id' => 0
		], $atts, 'zior_acf_button' );
	
		return 'BUTTON'; // TODO
	}

	/**
	 * @param string $callback
	 * @param string $block
	 *
	 * @return string
	 */
	public function zior_blocks_callback_acf_button( $callback, $block ) {
		return [ $this, $callback ];
	}
}