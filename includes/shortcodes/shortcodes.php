<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

spl_autoload_register( function ( $class ) {
	$allowed_class = [
		'zior_blocks_acf_button_sc',
	];

	if ( ! in_array( strtolower( $class ), $allowed_class ) ) {
		return;
	}

	include strtolower( $class ) . '.php';
});

( new ZIOR_Blocks_ACF_Button_SC() )->init();