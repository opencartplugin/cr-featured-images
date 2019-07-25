<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       http://www.weblineindia.com
 * @since      1.0.0
 *
 * @package    Cr_Featured_Images
 * @subpackage Cr_Featured_Images/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Cr_Featured_Images
 * @subpackage Cr_Featured_Images/includes
 * @author     Weblineindia <info@weblineindia.com>
 */
class Cr_Featured_Images_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'bw-featured-images',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
