<?php

/**
 * Fired during plugin deactivation
 *
 * @link       http://www.weblineindia.com
 * @since      1.0.0
 *
 * @package    Cr_Featured_Images
 * @subpackage Cr_Featured_Images/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Cr_Featured_Images
 * @subpackage Cr_Featured_Images/includes
 * @author     Weblineindia <info@weblineindia.com>
 */
class Cr_Featured_Images_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		delete_option( 'general_settings' );
    	//delete_option( 'new_product_settings' );
    	//delete_option( 'sale_product_settings' );
    	//delete_option( 'sold_product_settings' );
	}

}
