<?php
/**
 * ZIOR Block Elements
 *
 * Plugin Name: ZIOR Block Elements
 * Description: Custom block elements with ACF support.
 * Version: 0.1.0
 * Author:      Rey Calantaol
 * Author URI:  https://github.com/reygcalantaol
 * License:     GPLv2 or later
 * License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain: zior-blocks
 * Requires at least: 4.9
 * Tested up to: 6.1
 * Requires PHP: 7.4
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU
 * General Public License version 2, as published by the Free Software Foundation. You may NOT assume
 * that you can use any other version of the GPL.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */
class ZIOR_Blocks {

	/**
	 * @var ZIOR_Blocks
	 */
	protected static $instance;
	protected $loader;
	
	/**
	 * @var string
	 */
	protected $version = '0.1.0';

	private function __construct() {
	}

	/**
	 * Get instance.
	 *
	 * @return static
	 * @since
	 * @access static
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
			self::$instance->init();
		}

		return self::$instance;
	}

	public function init() {
		$this->setup_constants();
		$this->includes();
		$this->admin_init();
	}

	public function includes() {
		require_once ZR_BLOCKS_PLUGIN_DIR . 'includes/blocks/blocks.php';
		require_once ZR_BLOCKS_PLUGIN_DIR . 'includes/shortcodes/shortcodes.php';
		require_once ZR_BLOCKS_PLUGIN_DIR . 'includes/routes.php';
	}

	public function admin_init() {
		$this->loader = new ZIOR_Blocks_Loader();
		$this->loader->init();
		add_action( 'enqueue_block_editor_assets', [ $this->loader, 'enqueue_scripts' ], 1 );
	}

	/**
	 * Setup plugin constants
	 *
	 */
	private function setup_constants() {
		// Plugin version.
		if ( ! defined( 'ZR_BLOCKS_VERSION' ) ) {
			define( 'ZR_BLOCKS_VERSION', $this->version );
		}
		// Plugin Folder Path.
		if ( ! defined( 'ZR_BLOCKS_PLUGIN_DIR')) {
			define( 'ZR_BLOCKS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		}

		// Plugin Folder URL.
		if ( ! defined( 'ZR_BLOCKS_PLUGIN_URL' ) ) {
			define( 'ZR_BLOCKS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}

		// Plugin Root File.
		if ( ! defined( 'ZR_BLOCKS_PLUGIN_FILE' ) )
		{
			define( 'ZR_BLOCKS_PLUGIN_FILE', __FILE__ );
		}
	}
}

/**
 * Start the blocks loader.
 *
 * @return ZIOR_Blocks
 */
function ZIOR_Blocks_Initialize() {
	return ZIOR_Blocks::instance();
}

ZIOR_Blocks_Initialize();