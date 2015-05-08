<?php
/**
* @file resizeImage.php
* @brief 重置图片大小, 生成图片base64编码
* @author txthinking@gmail.com
* @version 0.0.1
* @date 2013-03-08
 */

//header("Content-type: image/png");
$imageURL = "http://i.imgur.com/yKOh2.png";
$toWidth = 270;
$toHeight = 270;
$tmpImagePath = "/tmp/phpImage";

$request = curl_init();
$xArray = array(
    CURLOPT_URL => $imageURL,
    CURLOPT_HEADER => false,
    CURLOPT_RETURNTRANSFER => true,
);
curl_setopt_array($request, $xArray);
$data = curl_exec($request);
curl_close($request);
file_put_contents($tmpImagePath, $data);

$imageInformation = getimagesize($tmpImagePath);
$imageSrc = null;
if ($imageInformation['mime'] == "image/png"){
    $imageSrc = imagecreatefrompng($tmpImagePath);
}else if($imageInformation['mime'] == "image/gif"){
    $imageSrc = imagecreatefromgif($tmpImagePath);
}else{
    $imageSrc = imagecreatefromjpeg($tmpImagePath);
}

$imageDst = imagecreatetruecolor($toWidth, $toHeight);

imagecopyresized($imageDst, $imageSrc, 0, 0, 0, 0,$toWidth, $toHeight, $imageInformation[0], $imageInformation[1]);
imagepng($imageDst, $tmpImagePath);
imagedestroy($imageDst);
$imageData = base64_encode(file_get_contents($tmpImagePath));
echo "<img src='data:image/png;base64,$imageData'>";

