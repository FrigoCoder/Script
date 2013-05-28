<?php

main();

function main () {
    $since = isset($_GET["since"]) ? $_GET["since"] : "2013-05-21 00:00";
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
    $image = loadImage($days[0]);
    for( $i = 1; $i < strlen($days); $i++ ){
        $image = mergeImages($image, imagecreatefrompng("padding.png"));
        $image = mergeImages($image, loadImage($days[$i]));
    }
    return $image;
}

function outputImage ($image) {
    header('Content-type: image/png');
    imagepng($image);
}

function loadImage ($number) {
    return imagecreatefrompng("$number.png");
}

function mergeImages ($left, $right) {
    $leftxs = imagesx($left);
    $leftys = imagesy($left);

    $rightxs = imagesx($right);
    $rightys = imagesy($right);

    $image = imagecreatetruecolor($leftxs + $rightxs, max($leftys, $rightys));
    imagealphablending($image, false);

    imagecopy($image, $left, 0, 0, 0, 0, $leftxs, $leftys);
    imagecopy($image, $right, $leftxs, 0, 0, 0, $rightxs, $rightys);

    return $image;
}


?>
