<?php
/*
 * Vimeo Thumbnail Enhancer by Hal Gatewood
 * License: The just-use-it license. Have fun!
 *
 * Dependances:
 * curl
 * GD Library
 * coffee
 *
 * Parameters:
 * id = Vimeo ID
 * size = large, medium, small
 * refresh = skips the cache to grab a fresh one
 *
 * Usage:
 * http://example.com/vimeo-thumb.php?size=large&id=29115778
 * http://example.com/vimeo-thumb.php?size=medium&id=29115778
 * http://example.com/vimeo-thumb.php?size=small&id=29115778
 * http://example.com/vimeo-thumb.php?size=large&id=29115778&refresh
 *
 */



// PARAMETERS
$size = $_REQUEST['size'];
if($size != "small" AND $size != "medium" AND $size != "large" ) $size = "large";

$vimeo_id 				= (int) trim($_REQUEST['id']);
$filename  				= $vimeo_id . "-" . $size;
$play_btn_file_name 	= "play-" . $size;


// IF NOT ID GO THROUGH AN ERROR
if( ! $vimeo_id ) 
{
	header("Status: 404 Not Found");
	die("Vimeo ID not found");
}


// CHECK IF VIMEO VIDEO
$handle = curl_init("http://vimeo.com/api/v2/video/" . $vimeo_id . ".json");
curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);
$response = (array) reset( json_decode( curl_exec($handle) ) );


// CHECK FOR 404 OR NO RESPONSE
$httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
if($httpCode == 404 OR !$response) 
{
	header("Status: 404 Not Found");
	die("No Vimeo video found or Vimeo timed out. Try again soon."); 
}

curl_close($handle);

// IF EXISTS, GO
if(file_exists("i/" . $filename . ".jpg") AND !isset($_GET['refresh']))
{
	header("Location: i/" . $filename . ".jpg");
	die;
}

// SET CURRENT VIDEO THUMBNAIL
$vimeo_thumbnail = $response['thumbnail_' . $size];


// CREATE IMAGE FROM YOUTUBE THUMB
$image = imagecreatefromjpeg( $vimeo_thumbnail );

$image_width 	= imagesx($image);
$image_height 	= imagesy($image);


// ICON SIZE
$icon = imagecreatefrompng( $play_btn_file_name . ".png" );
imagealphablending($icon, true);

$icon_width 	= imagesx($icon);
$icon_height 	= imagesy($icon);


// CENTER PLAY ICON
$left = round($image_width / 2) - round($icon_width / 2);
$top = round($image_height / 2) - round($icon_height / 2);


// CONVERT TO PNG SO WE CAN GET THAT PLAY BUTTON ON THERE
imagecopy( $image, $icon, $left, $top, 0, 0, $icon_width, $icon_height);
imagepng( $image, "i/" . $filename .".png", 9);


// MASHUP FINAL IMAGE AS A JPEG
$input = imagecreatefrompng("i/" . $filename .".png");
$output = imagecreatetruecolor($image_width, $image_height);
$white = imagecolorallocate($output,  255, 255, 255);
imagefilledrectangle($output, 0, 0, $image_width, $image_height, $white);
imagecopy($output, $input, 0, 0, 0, 0, $image_width, $image_height);

// OUTPUT TO 'i' FOLDER
imagejpeg($output, "i/" . $filename . ".jpg", 100);

// UNLINK PNG VERSION
@unlink("i/" . $filename .".png");

// REDIRECT TO NEW IMAGE
header("Location: i/" . $filename . ".jpg");
die;

?>