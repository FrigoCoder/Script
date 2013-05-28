<?php

main();

function main () {
    file_put_contents(".server.txt", print_r($_SERVER, true), FILE_APPEND);
    $since = isset($_GET["since"]) ? $_GET["since"] : "2013-05-21 10:00:00";
    $days = getDaysSince($since);
    $image = createImage($days);
    outputImage($image);
    imagedestroy($image);
}

function getDaysSince ($sinceString) {
    $now = time();
    $since = strtotime($sinceString);
    $interval = $now - $since;
    $days = floor($interval / (60 * 60 * 24));
    return $days;
}

function createImage ($daysInt) {
    $days = strval($daysInt);
    $image = loadImage("$days[0].png");
    for( $i = 1; $i < strlen($days); $i++ ){
        $image = mergeImages($image, loadImage("padding.png"));
        $image = mergeImages($image, loadImage("$days[$i].png"));
    }
    return $image;
}

function mergeImages ($left, $right) {
    $leftxs = imagesx($left);
    $leftys = imagesy($left);

    $rightxs = imagesx($right);
    $rightys = imagesy($right);

    $image = newImage($leftxs + $rightxs, max($leftys, $rightys));
 
    imagecopy($image, $left, 0, 0, 0, 0, $leftxs, $leftys);
    imagecopy($image, $right, $leftxs, 0, 0, 0, $rightxs, $rightys);

    imagedestroy($left);
    imagedestroy($right);

    return $image;
}

function loadImage ($filename) {
    return imagecreatefrompng($filename);
}

function newImage ($xs, $ys) {
    $image = imagecreatetruecolor($xs, $ys);
    $color = imagecolorallocatealpha($image, 0, 0, 0, 127);
    imagefill($image, 0, 0, $color);
    return $image;
}

function outputImage ($image) {
    header('Content-type: image/png');
    imagesavealpha($image, true);
    imagepng($image);
}

?>
