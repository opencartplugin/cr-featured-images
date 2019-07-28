<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/opencartplugin/cr-featured-images.git
 * @since      1.0.0
 *
 * @package    Cr_Featured_Images
 * @subpackage Cr_Featured_Images/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Cr_Featured_Images
 * @subpackage Cr_Featured_Images/admin
 * @author     Weblineindia <info@weblineindia.com>
 */
class Cr_Featured_Images_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	private $general_settings_key = 'crfi_general_settings';
	// private $new_product_settings_key = 'new_product_settings';
	// private $sale_product_settings_key = 'sale_product_settings';
	// private $sold_product_settings_key = 'sold_product_settings';
	private $plugin_options_key = 'crfi-images';
	private $plugin_settings_tabs = array ();


	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		$this->load_settings ();
		$widget_ops = array (
				'classname' => 'crfi_images',
				'description' => __ ( "CRFI Featured Images", "crfi_images_widget" ) 
		);

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Cr_Featured_Images_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Cr_Featured_Images_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/cr-featured-images-admin.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name . '-wpui', plugin_dir_url( __FILE__ ) . 'css/wp-like-ui-theme.css',         array(), $this->version, 'all' );


	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Cr_Featured_Images_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Cr_Featured_Images_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/cr-featured-images-admin.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-button', 'jquery-ui-slider' ), $this->version, false );
		//wp_enqueue_script( 'watermark-admin-script', plugins_url( 'js/admin-settings.js', __FILE__ ), array( 'jquery', 'jquery-ui-core', 'jquery-ui-button', 'jquery-ui-slider' ), $this->defaults['version'] );

	}

	/**
	 * Register settings link on plugin page.woo-crfistickers-by-webline
	 *
	 * @since    1.0.0
	 */
	public function add_settings_link($links, $file)
    {   
    	$wooStickerFile = CRFI_PLUGIN_FILE;    	 
        if (basename($file) == $wooStickerFile) {
        	
            $linkSettings = '<a href="' . admin_url("options-general.php?page=crfi-images") . '">Settings</a>';
            array_unshift($links, $linkSettings);
        }
        return $links;
    }

	/**
	 * Loads settings from
	 * the database into their respective arrays.
	 * Uses
	 * array_merge to merge with default values if they're
	 * missing.
	 *
	 * @since 1.0.0
	 * @var No arguments passed
	 * @return void
	 * @author Weblineindia
	 */
	public function load_settings() {
		$this->general_settings = ( array ) get_option ( $this->general_settings_key );
		// $this->new_product_settings = ( array ) get_option ( $this->new_product_settings_key );
		// $this->sale_product_settings = ( array ) get_option ( $this->sale_product_settings_key );
		// $this->sold_product_settings = ( array ) get_option ( $this->sold_product_settings_key );
		// Merge with defaults
		$this->general_settings = array_merge ( array (
				'enable_crfi' => 'no',
				'image_crfi' => '',
				'thumbnail_crfi' => '',
				'quality_crfi' => 80,
				'opacity_crfi' => 40,
				'generate_crfi' => false,
				'poststatus_crfi' => 'publish',
		), $this->general_settings );
		
						
	}
	/**
	 * Registers the general settings via the Settings API,
	 * appends the setting to the tabs array of the object.
	 * Tab Name will defined here.
	 *
	 * @since 1.0.0
	 * @var No arguments passed
	 * @return void
	 * @author Weblineindia
	 */
	public function register_general_settings() {
		$crfiOptions = get_option( 'crfi_general_settings' );
		if (isset($crfiOptions['generate_crfi'])) {
			$crfiOptions['generate_crfi'] = false;
			update_option('crfi_general_settings', $crfiOptions);
		}
	
		$this->plugin_settings_tabs [$this->general_settings_key] = 'General';
		// register_setting($option_group, $option_name, $sanitize_callback)
		register_setting ( $this->general_settings_key, $this->general_settings_key, array( $this, 'validate_options' ) );

		add_settings_section ( 'section_general', 'General Plugin Settings', array (
				&$this,
				'section_general_desc' 
		), $this->general_settings_key );
		// add_settings_field($id, $title, $callback, $page, $section, $args);
		add_settings_field ( 'enable_crfi', 'Enable Copy Right Featured Images:', array (
				&$this,
				'enable_crfi' 
		), $this->general_settings_key, 'section_general' );
		
		add_settings_field ( 'image_crfi', 'Add your Copy Right Image:', array (
			&$this,
			'image_crfi'
		), $this->general_settings_key, 'section_general' );
		add_settings_field ( 'thumbnail_crfi', 'Image Size Name of Featured Images:', array (
			&$this,
			'thumbnail_crfi'
		), $this->general_settings_key, 'section_general' );
		add_settings_field ( 'manual_crfi', 'Manual Copyrighting:', array (
			&$this,
			'manual_crfi'
		), $this->general_settings_key, 'section_general' );
		add_settings_field ( 'quality_crfi', 'Image Quality:', array (
			&$this,
			'quality_crfi'
		), $this->general_settings_key, 'section_general' );
		
		add_settings_field ( 'opacity_crfi', 'Copyright Image Transparancy / Opacity:', array (
			&$this,
			'opacity_crfi'
		), $this->general_settings_key, 'section_general' );
		add_settings_field ( 'poststatus_crfi', 'Post Status:', array (
			&$this,
			'poststatus_crfi'
		), $this->general_settings_key, 'section_general' );
		add_settings_field ( 'generate_crfi', 'Process:', array (
			&$this,
			'generate_crfi'
		), $this->general_settings_key, 'section_general' );
		
			
	}
	
	/**
	 * Validate options.
	 * 
	 * @param array $input
	 * @return array
	 */
	public function validate_options( $input ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return $input;
		}
		//var_dump($input['crfi_general_settings']['generate_crfi']);
		//die;
		if ( isset( $_POST['save_crfi_options'] ) ) {
			$input['manual_crfi'] = isset( $_POST['crfi_general_settings']['manual_crfi'] ) ? ((bool) $_POST['crfi_general_settings']['manual_crfi'] == 1 ? true : false) : false;
			
			$input['generate_crfi'] = isset( $_POST['crfi_general_settings']['generate_crfi'] ) ? ((bool) $_POST['crfi_general_settings']['generate_crfi'] == 1 ? true : false) : false;
			
			if ( $input['enable_crfi'] == 'yes' && $input['image_crfi'] == '' ) {
				add_settings_error( 'crfi_settings_saved', 'thumbnail_crfi_not_set', 'Copyright will not be applied when no image sizes are selected.', 'updated' );
			}
				
		}
		
		return $input;
	}

	/**
	 * The following methods provide descriptions
	 * for their respective sections, used as callbacks
	 * with add_settings_section
	 *
	 * @return void
	 * @var No arguments passed
	 * @author Weblineindia
	 */
	public function section_general_desc() {		
	}
	public function section_new_product_desc() {				
	}
	public function section_sale_product_desc() {		
	}
	public function section_sold_product_desc() {		
	}
	/**
	 * General Settings :: Enable Stickers
	 *
	 * @return void
	 * @var No arguments passed
	 * @author Weblineindia
	 */
	public function generate_crfi() {
		?>
		<label for="generate_crfi">
			<input id="generate_crfi" type="checkbox" <?php checked( 0, 1, true ); ?> value="1" name="<?= $this->general_settings_key ?>[generate_crfi]">
			<strong>Generate Featured Images Copyrighted / remove Copyrighted</strong> 	
		</label>
		<?php
	}
	
	/**
	 * General Settings :: Enable Stickers
	 *
	 * @return void
	 * @var No arguments passed
	 * @author Weblineindia
	 */
	public function manual_crfi() {
		?>
		<label for="manual_crfi">
			<input id="manual_crfi" type="checkbox" <?php checked( ( ! empty( $this->general_settings['manual_crfi'] ) ? 1 : 0 ), 1, true ); ?> value="1" name="<?= $this->general_settings_key ?>[manual_crfi]">
			Enable Apply Copyright option for Media Library images. 	
		</label>
		<?php
	}

	/**
	 * General Settings :: Enable Stickers
	 *
	 * @return void
	 * @var No arguments passed
	 * @author Weblineindia
	 */
	public function quality_crfi() {
		?>
		<fieldset id="quality_crfi">
			<div>
				<input type="text" id="quality_crfi_input" maxlength="3" class="hide-if-js" name="<?php echo $this->general_settings_key; ?>[quality_crfi]" value="<?= $this->general_settings['quality_crfi'] ?>" />
				<div class="wplike-slider">
					<span class="left hide-if-no-js">0</span><span class="middle" id="quality_crfi_span" title="<?= $this->general_settings['quality_crfi'] ?>"><span class="iw-current-value" style="left: <?= $this->general_settings['quality_crfi'] ?>%;"><?= $this->general_settings['quality_crfi'] ?></span></span><span class="right hide-if-no-js">100</span>
				</div>
			</div>
		</fieldset>
		<p class="description">Set Quality of Generated Featured Images</p>
		<?php
	}
	/**
	 * General Settings :: Enable Stickers
	 *
	 * @return void
	 * @var No arguments passed
	 * @author Weblineindia
	 */
	public function opacity_crfi() {
		?>
		<fieldset id="opacity_crfi">
			<div>
				<input type="text" id="opacity_crfi_input" maxlength="3" class="hide-if-js" name="<?php echo $this->general_settings_key; ?>[opacity_crfi]" value="<?= $this->general_settings['opacity_crfi'] ?>" />
				<div class="wplike-slider">
					<span class="left hide-if-no-js">0</span><span class="middle" id="opacity_crfi_span" title="<?= $this->general_settings['opacity_crfi'] ?>"><span class="iw-current-value" style="left: <?= $this->general_settings['opacity_crfi'] ?>%;"><?= $this->general_settings['opacity_crfi'] ?></span></span><span class="right hide-if-no-js">100</span>
				</div>
			</div>
		</fieldset>
		<p class="description">Set Transparancy of Copy right Image</p>

		<?php
	}


	/**
	 * General Settings :: Enable Stickers
	 *
	 * @return void
	 * @var No arguments passed
	 * @author Weblineindia
	 */
	public function thumbnail_crfi() {
		?>
			<select id='thumbnail_crfi'
				name="<?php echo $this->general_settings_key; ?>[thumbnail_crfi]">
				<?php $sizes = get_intermediate_image_sizes();
					foreach ($sizes as $size) { ?>

				<option value=<?= $size ?>
					<?php selected( $this->general_settings['thumbnail_crfi'], $size, true );?>><?= $size ?></option>
					<?php } ?>		
			</select>
			<p class="description">Select Images Size of Featured Images in your active Theme</p>
		<?php
		} 
	
	/**
	 * General Settings :: Enable Stickers
	 *
	 * @return void
	 * @var No arguments passed
	 * @author Weblineindia
	 */
	public function poststatus_crfi() {
		?>
			<select id='poststatus_crfi'
				name="<?php echo $this->general_settings_key; ?>[poststatus_crfi]">
				<option value='publish'
					<?php selected( $this->general_settings['poststatus_crfi'], 'publish',true );?>>Published Only</option>
				<option value='any'
					<?php selected( $this->general_settings['poststatus_crfi'], 'any',true );?>>All</option>
			</select>
			<p class="description">Select Post Status will copyrighted.</p>
		<?php
		}
	

	/**
	 * General Settings :: Enable Stickers
	 *
	 * @return void
	 * @var No arguments passed
	 * @author Weblineindia
	 */
	public function enable_crfi() {
	?>
		<select id='enable_crfi'
			name="<?php echo $this->general_settings_key; ?>[enable_crfi]">
			<option value='yes'
				<?php selected( $this->general_settings['enable_crfi'], 'yes',true );?>>Yes</option>
			<option value='no'
				<?php selected( $this->general_settings['enable_crfi'], 'no',true );?>>No</option>
		</select>
		<p class="description">Select wether you want to enable Copy Right Featured Images.</p>
	<?php
	}
	/**
	 * Sale Product Settings :: Custom Stickers for Sale Products
	 *
	 * @return void
	 * @var No arguments passed
	 * @author Weblineindia
	 */
	public function image_crfi() {
	
		?>
			
		<?php
		if (get_bloginfo('version') >= 3.5)
			wp_enqueue_media();
		else {
			wp_enqueue_style('thickbox');
			wp_enqueue_script('thickbox');
		}
		if ($this->general_settings['image_crfi'] == '' )
		{
			$image_url = "";
			echo '<img class="image_crfi" width="125px" height="auto" />';
		}
		else
		{
			$image_url = $this->general_settings ['image_crfi'];
			echo '<img class="image_crfi" src="'.$image_url.'" width="125px" height="auto" />';
		}
		echo '		<br/>
					<input type="hidden" name="'.$this->general_settings_key .'[image_crfi]" id="image_crfi" value="'.$image_url.'" />
					<button class="upload_img_btn button">Upload Image</button>
					<button class="remove_img_btn button">Remove Image</button>								
				'.$this->custom_crfi_script('image_crfi'); ?>		
		<p class="description">Add your own custom Copy right Image instead of Copy right Image default.</p>
		<?php
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function add_admin_menus() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Cr_Featured_Images_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Cr_Featured_Images_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		add_options_page ( 'Copy Right Featured Images Page', 'Copy Right Featured Images Post', 'manage_options', $this->plugin_options_key, array (
				&$this,
				'plugin_options_page' 
		) );
	}

	public function plugin_options_page(){
		$tab = isset ( $_GET ['tab'] ) ? $_GET ['tab'] : $this->general_settings_key;
		?>
		<div class="wrap">
		<h2>Copy Right Featured Images</h2>
		    			<?php $this->plugin_options_tabs(); ?>
		    			<form method="post" action="options.php">
		    				<?php wp_nonce_field( 'update-options' ); ?>
		    				<?php settings_fields( $tab ); ?>
		    				<?php do_settings_sections( $tab ); ?>
							<?php submit_button('Save / Generate', 'primary', 'save_crfi_options', false); ?>

		    			</form>
		</div><?php
	}

	/**
	 * Renders our tabs in the plugin options page,
	 * walks through the object's tabs array and prints
	 * them one by one.
	 * Provides the heading for the
	 * plugin_options_page method.
	 *
	 * @return void
	 * @var No arguments passed
	 * @author Weblineindia
	 */
	public function plugin_options_tabs() {
		$current_tab = isset ( $_GET ['tab'] ) ? $_GET ['tab'] : $this->general_settings_key;
		if ( version_compare( $GLOBALS['wp_version'], '3.8.0', '<' ) ) {
			screen_icon();
		}	
		echo '<h2 class="nav-tab-wrapper">';
		foreach ( $this->plugin_settings_tabs as $tab_key => $tab_caption ) {
			$active = $current_tab == $tab_key ? 'nav-tab-active' : '';
			echo '<a class="nav-tab ' . $active . '" href="?page=' . $this->plugin_options_key . '&tab=' . $tab_key . '">' . $tab_caption . '</a>';
		}
		echo '</h2>';
	}
	
	/**
	 *   custom_sticker_script() is used to upload using wordpress upload.
	 *
	 *  @since    			1.0.0
	 *
	 *  @return             script
	 *  @var                No arguments passed
	 *  @author             Weblineindia
	 *
	 */
	public function custom_crfi_script($obj_url) {
		return '<script type="text/javascript">
	    jQuery(document).ready(function() {
			var wordpress_ver = "'.get_bloginfo("version").'", upload_button;
			jQuery(".upload_img_btn").click(function(event) {
				upload_button = jQuery(this);
				var frame;
				jQuery(this).parent().children("img").attr("src","").show();					
				if (wordpress_ver >= "3.5") {
					event.preventDefault();
					if (frame) {
						frame.open();
						return;
					}
					frame = wp.media();
					frame.on( "select", function() {					
						// Grab the selected attachment.
						var attachment = frame.state().get("selection").first();
						frame.close();
						if (upload_button.parent().prev().children().hasClass("cat_list")) {
							upload_button.parent().prev().children().val(attachment.attributes.url);
							upload_button.parent().prev().prev().children().attr("src", attachment.attributes.url);
						}
						else
						{
							jQuery("#'.$obj_url.'").val(attachment.attributes.url);
							jQuery(".'.$obj_url.'").attr("src",attachment.attributes.url);
						}
					});
					frame.open();
				}
				else {
					tb_show("", "media-upload.php?type=image&amp;TB_iframe=true");
					return false;
				}
			});
	
			jQuery(".remove_img_btn").click(function() {
				jQuery("#'.$obj_url.'").val("");
				if(jQuery(this).parent().children("img").attr("src")!="undefined")	
				{ 
					jQuery(this).parent().children("img").attr("src","").hide();
					jQuery(this).parent().siblings(".title").children("img").attr("src"," ");
					jQuery(".inline-edit-col :input[name=\''.$obj_url.'\']").val(""); 
				}	
				else
				{
					jQuery(this).parent().children("img").attr("src","").hide();
				}						
				return false;
			});
	
			if (wordpress_ver < "3.5") {
				window.send_to_editor = function(html) {
					imgurl = jQuery("img",html).attr("src");
					if (upload_button.parent().prev().children().hasClass("cat_list")) {
						upload_button.parent().prev().children().val(imgurl);
						upload_button.parent().prev().prev().children().attr("src", imgurl);
					}
					else
					{
						jQuery("#'.$obj_url.'").val(imgurl);
						jQuery(".'.$obj_url.'").attr("src",imgurl);
					}
					tb_remove();
				}
			}
	
			jQuery(".editinline").click(function(){
			    var tax_id = jQuery(this).parents("tr").attr("id").substr(4);
			    var thumb = jQuery("#tag-"+tax_id+" .thumb img").attr("src");
				if (thumb != "") {
					jQuery(".inline-edit-col :input[name=\''.$obj_url.'\']").val(thumb);
				} else {
					jQuery(".inline-edit-col :input[name=\''.$obj_url.'\']").val("");
				}
				jQuery(".inline-edit-col .title img").attr("src",thumb);
			    return true;
			});
	    });
	</script>';
	}


}
