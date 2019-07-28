<?php
/* Plugin Name: Copy Right Featured Image Posts
 * Plugin URI:  https://github.com/opencartplugin/cr-featured-images.git
 * Description: It will make your featured image posts have a unique picture inside.
 * Version:     1.0.0
 * Author:      Mohamad Farid
 * Author URI:  https://github.com/opencartplugin
 * License:     GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: crfi-basics-plugin
 * Domain Path: /languages
 */
//if direct call, failed
if (!defined('WPINC')) {
    die;
}
if (!defined('CRFI_VERSION')) {
    define('CRFI_VERSION', '1.0.0');
}
if (!defined('CRFI_DIR')) {
    define('CRFI_DIR', plugin_dir_url( __FILE__ ));
}
define ( 'CRFI_OPTION_NAME', 'CRFI_settings' );

function activate_cr_featured_images() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-cr-featured-images-activator.php';
	Cr_Featured_Images_Activator::activate();
}

function deactivate_cr_featured_images() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-cr-featured-images-deactivator.php';
	Cr_Featured_Images_Deactivator::deactivate();
}
register_activation_hook( __FILE__, 'activate_cr_featured_images' );
register_deactivation_hook( __FILE__, 'deactivate_cr_featured_images' );

require plugin_dir_path( __FILE__ ) . 'includes/class-cr-featured-images.php';

function run_cr_featured_images() {

	$plugin = new Cr_Featured_Images();
	$plugin->run();

}
run_cr_featured_images();

function km_hook_into_options_page_after_save( $old_value, $new_value ) {

	if ( $old_value != $new_value ) {
		$args = array(
			'post_type'=> 'post',
			'orderby'    => 'ID',
			'post_status' => $new_value['poststatus_crfi'],
			'order'    => 'DESC',
			'posts_per_page' => -1 // this will retrive all the post that is published 
		);
		if ($new_value['enable_crfi'] == 'yes' && $new_value['generate_crfi'] && $new_value['image_crfi'] != '') {
			$filecrsrc = image_path_src($new_value['image_crfi']);
			if (!file_exists($filecrsrc)) {
				return;
			}
			$filecrInfo = getFileInfo($filecrsrc);
			//var_dump($filecrInfo);
			//die;
			$result = new WP_Query( $args );
			if ( $result-> have_posts() ) { 
				while ( $result->have_posts() ) {  
					$result->the_post(); 
					$postID = get_the_ID();
					$images = array();	
					if (has_post_thumbnail( $postID ) ){
						$sizes = get_intermediate_image_sizes();
						foreach ($sizes as $size) {
							# code...
							$image = wp_get_attachment_image_src( get_post_thumbnail_id( $postID ), $size );
							$filesrc = image_path_src($image[0]);
							if (file_exists($filesrc . '.crfi')) {
								rename($filesrc . '.crfi', $filesrc);
							}
							if ($size == $new_value['thumbnail_crfi']) {
								//die;
								if (file_exists($filesrc) && (file_exists($filecrsrc))) {
									copy($filesrc, $filesrc . '.crfi');
									$fileInfo = getFileInfo($filesrc);
									//var_dump($fileInfo);		
									//die;
									$filecrimage = imageResize($fileInfo['width'], $fileInfo['height'], $filecrsrc);
									
									
									$fileimage = $fileInfo['imagecreate']($filesrc);
									$dstx = 0;
									$dsty = 0;
									imagecopymerge_alpha($fileimage, $filecrimage, $dstx, $dsty, 0, 0, $fileInfo['width'], $fileInfo['height'], $new_value['opacity_crfi']);
									imagesavealpha($fileimage, true);
									$fileInfo['imagesave']($fileimage, $filesrc, $new_value['quality_crfi']);
									imagedestroy($fileimage);
								}  else {
									var_dump($filesrc);
									die;
		
								}

							}
							//print_r($size . ': ' . $image[0] . '<br/>');
							
						}
						print_r('<br/>');					
					} else {
					
					}
				}
				//die;
			}
			imagedestroy($filecrimage);
		}
		if ($new_value['enable_crfi'] == 'no') {
			$result = new WP_Query( $args );
			if ( $result-> have_posts() ) { 
				while ( $result->have_posts() ) {  
					$result->the_post(); 
					$postID = get_the_ID();
					$images = array();	
					if (has_post_thumbnail( $postID ) ){
						$sizes = get_intermediate_image_sizes();
						foreach ($sizes as $size) {
							$image = wp_get_attachment_image_src( get_post_thumbnail_id( $postID ), $size );
							$filesrc = image_path_src($image[0]);
							if (file_exists($filesrc . '.crfi')) {
								rename($filesrc . '.crfi', $filesrc);
							}
							
						}
					} else {
					
					}
				}
				//die;
			}

		}
	}

}			

add_action( 'update_option_crfi_general_settings', 'km_hook_into_options_page_after_save', 10, 2 );
// crfi_general_settings : reference to -> private $general_settings_key = 'crfi_general_settings'; (line 43 class-cr-featured-images-admin.php)

function image_path_src($imageUrl) {
    $imagepath = str_replace(get_site_url(), substr(ABSPATH, 0, strlen(ABSPATH)-1) , $imageUrl);
	
    if($imagepath) return $imagepath;

    return false;
	
}

function getFileInfo($filesrc) {
	//list($orig_w, $orig_h, $orig_type) = @getimagesize($filesrc);
    $imgsize = getimagesize($filesrc);
    $width = $imgsize[0];
    $height = $imgsize[1];
    $mime = $imgsize['mime'];
    switch($mime){
        case 'image/gif':
            $image_create = "imagecreatefromgif";
            $image = "imagegif";
            break;
 
        case 'image/png':
            $image_create = "imagecreatefrompng";
            $image = "imagepng";
            break;
 
        case 'image/jpeg':
            $image_create = "imagecreatefromjpeg";
            $image = "imagejpeg";
            break;
 
        default:
            return false;
            break;
    }

	return array('imagesave' => $image, 'imagecreate' => $image_create, 'width' => $width, 'height' => $height);
}


function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct) {
	if (!isset($pct)) {
		return false;
	}
	$pct /= 100;
	// Get image width and height
	$w = imagesx($src_im);
	$h = imagesy($src_im);
	// Turn alpha blending off
	imagealphablending($src_im, false);
	// Find the most opaque pixel in the image (the one with the smallest alpha value)
	$minalpha = 127;
	for ($x = 0; $x < $w; $x++)
		for ($y = 0; $y < $h; $y++) {
			$alpha = ( imagecolorat($src_im, $x, $y) >> 24 ) & 0xFF;
			if ($alpha < $minalpha) {
				$minalpha = $alpha;
			}
		}
	//loop through image pixels and modify alpha for each
	for ($x = 0; $x < $w; $x++) {
		for ($y = 0; $y < $h; $y++) {
			//get current alpha value (represents the TANSPARENCY!)
			$colorxy = imagecolorat($src_im, $x, $y);
			$alpha = ( $colorxy >> 24 ) & 0xFF;
			//calculate new alpha
			if ($minalpha !== 127) {
				$alpha = 127 + 127 * $pct * ( $alpha - 127 ) / ( 127 - $minalpha );
			} else {
				$alpha += 127 * $pct;
			}
			//get the color index with new alpha
			$alphacolorxy = imagecolorallocatealpha($src_im, ( $colorxy >> 16 ) & 0xFF, ( $colorxy >> 8 ) & 0xFF, $colorxy & 0xFF, $alpha);
			//set pixel with the new color + opacity
			if (!imagesetpixel($src_im, $x, $y, $alphacolorxy)) {
				return false;
			}
		}
	}

	// The image copy
	imagecopy($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h);
}

function imageResize($max_width, $max_height, $source_file){
    $fileInfo = getFileInfo($source_file);
    $dst_img = imagecreatetruecolor($max_width, $max_height);
    $src_img = $fileInfo['imagecreate']($source_file);
	imagealphablending($dst_img, false);
	//$col = imagecolorallocatealpha($dst_img, 0, 0, 0, 127);
		 
    $width_new = $fileInfo['height'] * $max_width / $max_height;
    $height_new = $fileInfo['width'] * $max_height / $max_width;
    //if the new width is greater than the actual width of the image, then the height is too large and the rest cut off, or vice versa
    if($width_new > $fileInfo['width']){
        //cut point by height
        $h_point = (($fileInfo['height'] - $height_new) / 2);
        //copy image
        imagecopyresampled($dst_img, $src_img, 0, 0, 0, $h_point, $max_width, $max_height, $fileInfo['width'], $height_new);
    }else{
        //cut point by width
        $w_point = (($fileInfo['width'] - $width_new) / 2);
        imagecopyresampled($dst_img, $src_img, 0, 0, $w_point, 0, $max_width, $max_height, $width_new, $fileInfo['height']);
    }
	return $dst_img; 
}

add_action( 'admin_print_scripts', 'admin_print_scripts', 10, 2 );

	/**
	 * Admin inline scripts.
	 * 
	 * @global $pagenow
	 */
	function admin_print_scripts() {
		global $pagenow;

		if ( $pagenow === 'upload.php' ) {
			//if ( $this->options['watermark_image']['manual_watermarking'] == 1 ) {
				?>
				<script type="text/javascript">
					jQuery( function( $ ) {
						$( document ).ready( function() {

							$( "<option>" ).val( "applywatermark" ).text( "<?php echo 'Apply watermark'; ?>" ).appendTo( "select[name='action'], select[name='action2']" );
						});
					});
				</script>
				<?php
			//}
		}
	}
