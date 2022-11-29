<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Helper methods for running the blocks.
 */
class ZIOR_Blocks_Loader {
	public function init() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		add_action( 'init', [ $this, 'register' ] );
		add_filter( 'block_categories_all', [ $this, 'register_block_category' ] );
		add_action( 'wp_loaded', [ $this, 'register_block_attrs' ], 100 );
	}

	/**
	 * Register server side blocks for the editor.
	 */
	public function register() {
		spl_autoload_register( function ( $class ) {
			$allowed_class = [
				'abstract_block',
				'zior_blocks_acf_button',
			];
		
			if ( ! in_array( strtolower( $class ), $allowed_class ) ) {
				return;
			}
			include strtolower( $class ) . '.php';
		});

		( new ZIOR_Blocks_ACF_Button() )->register();
	}

	/**
	 * Get the js variables required for the block editor.
	 *
	 * @return array
	 */
	public function get_js_vars() {
		return [
			'ajax'          => admin_url( 'admin-ajax.php' ),
			'blocks'        => apply_filters( 'zior_blocks', [] ),
			'enabledBlocks' => $this->enabled_blocks(),
		];
	}

	public function register_block_category( $categories ) {
		$categories = array_merge( $categories, [
			[
				'slug'  => 'zior',
				'title' => 'ZIOR',
			],
		] );

		return $categories;
	}

	public function enqueue_scripts() {
		wp_enqueue_style( 'zr-blocks', ZR_BLOCKS_PLUGIN_URL . 'build/zr-blocks.min.css', array(), ZR_BLOCKS_VERSION );
		wp_enqueue_script( 'zr-blocks', ZR_BLOCKS_PLUGIN_URL . 'build/zr-blocks.min.js', [
			'wp-blocks',
			'wp-i18n',
			'wp-element',
			'wp-components',
			'wp-editor',
		], ZR_BLOCKS_VERSION, true );

		wp_localize_script( 'zr-blocks', 'zrBlocks', $this->get_js_vars() );
	}

	public function register_block_attrs() {
		$registered_blocks = WP_Block_Type_Registry::get_instance()->get_all_registered();

		foreach ( $registered_blocks as $name => $block ) {
			//TODO
		}
	}

	public function enabled_blocks() {
		$blocks = [
			'core/paragraph',
			'core/heading',
			'core/image',
		];

		return apply_filters( 'zior_acf_enabled_blocks', $blocks );
	}
}
