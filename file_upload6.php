<?php // file_upload6.php


/**
 * Created by PhpStorm.
 * Users: jean dedalmas and Dylan Oldham
 * Date: 11/29/16
 * Time: 12:22 AM
 */

// The scripts below were primarily used for troubleshooting during development
//
/*
    print_r( $_FILES["upload"] );
    echo "<br/>";
    echo ($_FILES["upload"]["name"]);
    echo "Size: " . ($_FILES["upload"]["size"]) . "<br/>";

    echo "Move from: " . $_FILES["upload"]["tmp_name"];
    echo "<br/>Move to: " . "uploads/" . $_FILES["upload"]["name"];
*/

// Specify upload directory and naming convention
$uploadDir = 'uploads/';
$uploadPre = $uploadDir . uniqid("upload_", true);

// Specify watermark naming convention
$watermarkPre = $uploadDir . uniqid("watermark_". true);

// Specify allowed data-types for upload
$types = array('image/jpg', 'image/jpeg');

// Listen for new upload file being added to "temp" directory and copy it to "upload" directory
if (!empty($_POST))
{
    if (in_array($_FILES["upload"]["type"], $types)) 
    {
         move_uploaded_file($_FILES["upload"]["tmp_name"], $uploadPre . "_" . $_FILES["upload"]["name"]);
     } 
    else 
    {
         echo '<script type="text/javascript">alert("Image must be a .JPG or .JPEG");</script>';
     }
}
// Figure out a way to make unique ID or timestamp
$imagePath = $uploadPre . "_" . $_FILES["upload"]["name"];
$watermarkPath = $watermarkPre . "_" . $_FILES["upload"]["name"];

echo <<<END_OF_FORM
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Photo Upload Form</title>
    <link rel="stylesheet" href="styles/style.css"
</head>
<body>
<div id="header">
    <h1>Watermarking Application</h1>
</div>
    <form action="/file_upload6.php" method="POST" enctype="multipart/form-data">
        <p class="required">* Image must be a <b><i>.jpg</i></b> or <b><i>.jpeg</i></b> and more than <b><i>500px<i></b> wide</p>
            <div class="fieldSet">
                <fieldset>
                    <legend>Upload an image to have it resized and watermarked</legend>
                    <input type="file" name="upload" id="imageType">
                    <input type="submit" name="submit" value="Upload">
                </fieldset>
            </div>
    </form>
<br/>
<center>
    <img id="displayWatermark"  src="$watermarkPath" alt="Please upload an image...">
</center>
<br/>
END_OF_FORM;


// Create image resource for original JPG in "upload" directory
$src = imagecreatefromjpeg($imagePath);

// Create watermark resource from original PNG file in "watermark" directory
$watermark = imagecreatefrompng('watermark/cloud_computing.png');

// Get width and height of original image resource
list($width, $height) = getimagesize($imagePath);

// Get width and height of original watermark resource
$watermarkWidth = imagesx($watermark);
$watermarkHeight = imagesy($watermark);

// Set margin for watermark
$marginRight = 30;
$marginBottom = ($height / $width) * $marginRight;

echo "<fieldset><legend>Original</legend>";
echo "Image Width: " . $width . "<br/>";
echo "Image Height: " . $height . "<br/>";
echo "Watermark Width: " . $watermarkWidth . "<br/>";
echo "Watermark Height: " . $watermarkHeight;
echo "</fieldset><br/><br/>";

// Set new dimensions for watermark
$newWatermarkWidth = $width / 4;
$newWatermarkHeight = ($watermarkHeight / $watermarkWidth) * $newWatermarkWidth;

// Records scaled watermark top-left corner X and Y coordinates (i.e. where it will begin overlaying watermark on original)
$x = ($width - $newWatermarkWidth - $marginRight);
$y = ($height - $newWatermarkHeight - $marginBottom);

echo "<fieldset><legend>Merge Targets</legend>";
echo "X-Coordinate: " . $x . "<br/>";
echo "Y-Coordinate: " . $y . "<br/>";
echo "Margin Right: " . $marginRight . "<br/>";
echo "Margin Bottom: " . $marginBottom;
echo "</fieldset><br/><br/>";

// Define new height and width for final merged image
if($width >= 500)
{
    $newWidth = 500;
    $newHeight = ($height / $width) * $newWidth; // <- This code calculates height to preserve aspect-ratio
}
else 
{
    $newWidth = $width;
    $newHeight = $height;
}

echo "<fieldset><legend>Scaled</legend>";
echo "Image Width: " . $newWidth . "<br/>";
echo "Image Height: " . $newHeight . "<br/>";
echo "Watermark Width: " . $newWatermarkWidth . "<br/>";
echo "Watermark Height: " . $newWatermarkHeight;
echo "</fieldset><br/><br/>";


// Merge the watermark into the main image resource
imagecopyresampled($src, $watermark, $width - $newWatermarkWidth - $marginRight, $height - $newWatermarkHeight - $marginBottom, 0, 0 ,$newWatermarkWidth, $newWatermarkHeight, $watermarkWidth, $watermarkHeight);

// Create a blank canvas called $tmp, 500px wide and preserve aspect-ratio
$tmp = imagecreatetruecolor($newWidth, $newHeight);

// Scale watermarked image and copy it into the blank canvas created above
imagecopyresampled($tmp, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

// Save the new 500px wide watermarked image (populated canvas) to location specified in $watermarkPath, defined at top of the page
imagejpeg($tmp, $watermarkPath, 100);

// Destroy resources to free up memory
imagedestroy($src);
imagedestroy($tmp);
