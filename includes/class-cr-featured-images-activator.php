<?php

/**
 * Fired during plugin activation
 *
 * @link       http://www.weblineindia.com
 * @since      1.0.0
 *
 * @package    Cr_Featured_Images
 * @subpackage Cr_Featured_Images/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Cr_Featured_Images
 * @subpackage Cr_Featured_Images/includes
 * @author     Weblineindia <info@weblineindia.com>
 */
class Cr_Featured_Images_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		update_option(CRFI_OPTION_NAME, CRFI_VERSION);
	}

}