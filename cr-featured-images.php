<?php
/* Plugin Name: Copy Right Featured Image Posts
 * Plugin URI:  https://google.com/
 * Description: It will make your featured image posts have a unique picture inside.
 * Version:     1.0.0
 * Author:      Mohamad Farid
 * Author URI:  https://google.com/
 * License:     GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: crfi-basics-plugin
 * Domain Path: /languages
 */
//Kalau dipanggil langsung, failed
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
    $imgsize = getimagesize($source_file);
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
            //$quality = 7;
            break;
 
        case 'image/jpeg':
            $image_create = "imagecreatefromjpeg";
            $image = "imagejpeg";
            //$quality = 80;
            break;
 
        default:
            return false;
            break;
    }
     
    $dst_img = imagecreatetruecolor($max_width, $max_height);
    $src_img = $image_create($source_file);
	imagealphablending($dst_img, false);
	//$col = imagecolorallocatealpha($dst_img, 0, 0, 0, 127);
		 
    $width_new = $height * $max_width / $max_height;
    $height_new = $width * $max_height / $max_width;
    //if the new width is greater than the actual width of the image, then the height is too large and the rest cut off, or vice versa
    if($width_new > $width){
        //cut point by height
        $h_point = (($height - $height_new) / 2);
        //copy image
        imagecopyresampled($dst_img, $src_img, 0, 0, 0, $h_point, $max_width, $max_height, $width, $height_new);
    }else{
        //cut point by width
        $w_point = (($width - $width_new) / 2);
        imagecopyresampled($dst_img, $src_img, 0, 0, $w_point, 0, $max_width, $max_height, $width_new, $height);
    }
    return $dst_img; 
    /*$image($dst_img, $dst_dir, $quality);
 
    if($dst_img)imagedestroy($dst_img);
    if($src_img)imagedestroy($src_img);*/
}

function image_handler($source_image, $destination, $tn_w = 100, $tn_h = 100,$quality = 80, $wmsource = false) {
	// The getimagesize functions provides an "imagetype" string contstant, which can be passed to the image_type_to_mime_type function for the corresponding mime type
	$info = getimagesize($source_image);
	$imgtype = image_type_to_mime_type($info[2]);
	// Then the mime type can be used to call the correct function to generate an image resource from the provided image
	switch ($imgtype) {
	case 'image/jpeg':
	  $source = imagecreatefromjpeg($source_image);
	  break;
	case 'image/gif':
	  $source = imagecreatefromgif($source_image);
	  break;
	case 'image/png':
	  $source = imagecreatefrompng($source_image);
	  break;
	default:
	  die('Invalid image type.');
	}
	// Now, we can determine the dimensions of the provided image, and calculate the width/height ratio
	$src_w = imagesx($source);
	$src_h = imagesy($source);
	$src_ratio = $src_w/$src_h;
	// Now we can use the power of math to determine whether the image needs to be cropped to fit the new dimensions, and if so then whether it should be cropped vertically or horizontally. We're just going to crop from the center to keep this simple.
	if ($tn_w/$tn_h > $src_ratio) {
	$new_h = $tn_w/$src_ratio;
	$new_w = $tn_w;
	} else {
	$new_w = $tn_h*$src_ratio;
	$new_h = $tn_h;
	}
	$x_mid = $new_w/2;
	$y_mid = $new_h/2;
	// Now actually apply the crop and resize!
	$newpic = imagecreatetruecolor(round($new_w), round($new_h));
	imagecopyresampled($newpic, $source, 0, 0, 0, 0, $new_w, $new_h, $src_w, $src_h);
	$final = imagecreatetruecolor($tn_w, $tn_h);
	imagecopyresampled($final, $newpic, 0, 0, ($x_mid-($tn_w/2)), ($y_mid-($tn_h/2)), $tn_w, $tn_h, $tn_w, $tn_h);
	// If a watermark source file is specified, get the information about the watermark as well. This is the same thing we did above for the source image.
	if($wmsource) {
	$info = getimagesize($wmsource);
	$imgtype = image_type_to_mime_type($info[2]);
	switch ($imgtype) {
	  case 'image/jpeg':
		$watermark = imagecreatefromjpeg($wmsource);
		break;
	  case 'image/gif':
		$watermark = imagecreatefromgif($wmsource);
		break;
	  case 'image/png':
		$watermark = imagecreatefrompng($wmsource);
		break;
	  default:
		die('Invalid watermark type.');
	}
	// Determine the size of the watermark, because we're going to specify the placement from the top left corner of the watermark image, so the width and height of the watermark matter.
	$wm_w = imagesx($watermark);
	$wm_h = imagesy($watermark);
	// Now, figure out the values to place the watermark in the bottom right hand corner. You could set one or both of the variables to "0" to watermark the opposite corners, or do your own math to put it somewhere else.
	$wm_x = $tn_w - $wm_w;
	$wm_y = $tn_h - $wm_h;
	// Copy the watermark onto the original image
	// The last 4 arguments just mean to copy the entire watermark
	imagecopy($final, $watermark, $wm_x, $wm_y, 0, 0, $tn_w, $tn_h);
	}
	// Ok, save the output as a jpeg, to the specified destination path at the desired quality.
	// You could use imagepng or imagegif here if you wanted to output those file types instead.
	if(Imagejpeg($final,$destination,$quality)) {
	return true;
	}
	// If something went wrong
	return false;
  }
