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
		add_filter( 'pre_render_block', [ $this, 'pre_render_block' ], 10, 3 );
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
			'post_type'     => get_post_type( $post )
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

	public function enabled_blocks() {
		$blocks = [
			'core/paragraph',
			'core/heading',
			'core/button',
			'core/verse'
		];

		return apply_filters( 'zior_acf_enabled_blocks', $blocks );
	}

	public function has_acf_field_enabled( $parsed_block ) {
		$acf_key = $parsed_block['attrs']['zr_replace_with_acf_field'] ?? '';
		$acf_enabled = $parsed_block['attrs']['zr_replace_with_acf_field_value'] ?? 0;

		if ( $acf_key && $acf_enabled ) {
			return true;
		}

		return false;
	}

	public function pre_render_block_button( $parsed_block ) {
		global $post;

		$acf_key = $parsed_block['attrs']['zr_replace_with_acf_field'] ?? '';
		$field_content = get_field( $acf_key );

		$dom = new DOMDocument;
		$dom->loadHTML( $parsed_block['innerHTML'] );

		foreach ( $dom->getElementsByTagName( 'a' ) as $href ) {
			$href->setAttribute( 'href', $field_content );
		}

		return $dom->saveHTML();
	}

	public function pre_render_block_paragraph( $parsed_block ) {
		global $post;

		$acf_key = $parsed_block['attrs']['zr_replace_with_acf_field'] ?? '';
		$field_content = get_field( $acf_key );

		return wpautop( $field_content );
	}

	public function pre_render_block_heading( $parsed_block ) {
		global $post;

		$acf_key = $parsed_block['attrs']['zr_replace_with_acf_field'] ?? '';
		$field_content = get_field( $acf_key );

		$dom = new DOMDocument;
		$dom->loadHTML( $parsed_block['innerHTML'] );
		$xpath = new DOMXpath( $dom );
		$htags = $xpath->query( '//h1 | //h2 | //h3 | //h4 | //h5 | //h6' );
		foreach( $htags as $htag ) {
			$htag->nodeValue = $field_content;
		}

		return $dom->saveHTML();
	}

	public function pre_render_block_verse( $parsed_block ) {
		global $post;

		$acf_key = $parsed_block['attrs']['zr_replace_with_acf_field'] ?? '';
		$field_content = get_field( $acf_key );

		$dom = new DOMDocument;
		$dom->loadHTML( $parsed_block['innerHTML'] );
		
		foreach ( $dom->getElementsByTagName( 'pre' ) as $pre ) {
			$pre->nodeValue = $field_content;
		}

		return $dom->saveHTML();
	}

	public function pre_render_block( $pre, $parsed_block, $parent_block ) {

		if ( ! $this->has_acf_field_enabled( $parsed_block ) ) {
			return $pre;
		}

		if ( 'core/button' === $parsed_block['blockName'] ) {
			$pre = $this->pre_render_block_button( $parsed_block );
		}
		
		if ( 'core/paragraph' === $parsed_block['blockName'] ) {
			$pre = $this->pre_render_block_paragraph( $parsed_block );
		}

		if ( 'core/heading' === $parsed_block['blockName'] ) {
			$pre = $this->pre_render_block_heading( $parsed_block );
		}

		if ( 'core/verse' === $parsed_block['blockName'] ) {
			$pre = $this->pre_render_block_verse( $parsed_block );
		}

		return $pre;
	}
}
