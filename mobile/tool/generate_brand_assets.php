<?php

declare(strict_types=1);

$root = dirname(__DIR__);

function rgb(string $hex): array
{
    $hex = ltrim($hex, '#');
    return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
}

function color(GdImage $image, string $hex, int $alpha = 0): int
{
    [$r, $g, $b] = rgb($hex);
    return imagecolorallocatealpha($image, $r, $g, $b, $alpha);
}

function fillTransparent(GdImage $image): void
{
    imagealphablending($image, false);
    imagesavealpha($image, true);
    imagefill($image, 0, 0, imagecolorallocatealpha($image, 0, 0, 0, 127));
    imagealphablending($image, true);
}

function drawBrandMark(GdImage $canvas, int $size, int $scale): void
{
    $w = imagesx($canvas);
    $h = imagesy($canvas);
    $cream = color($canvas, '#f8fff9');
    $gold = color($canvas, '#f2c15d');
    $shadow = color($canvas, '#0a4f43', 55);

    imagesetthickness($canvas, max(3, (int) round($size * $scale * .045)));
    imagearc($canvas, (int) ($w * .50), (int) ($h * .62), (int) ($w * .46), (int) ($h * .38), 190, 350, $shadow);
    imagearc($canvas, (int) ($w * .50), (int) ($h * .60), (int) ($w * .46), (int) ($h * .38), 190, 350, $cream);

    imagesetthickness($canvas, max(2, (int) round($size * $scale * .028)));
    imagearc($canvas, (int) ($w * .47), (int) ($h * .55), (int) ($w * .34), (int) ($h * .22), 195, 340, $gold);
    imagearc($canvas, (int) ($w * .50), (int) ($h * .67), (int) ($w * .30), (int) ($h * .17), 190, 330, $gold);
    imageline($canvas, (int) ($w * .39), (int) ($h * .43), (int) ($w * .62), (int) ($h * .72), $gold);

    imagesetthickness($canvas, max(3, (int) round($size * $scale * .038)));
    for ($i = 0; $i < 5; $i++) {
        $x = .28 + ($i * .085);
        imageline($canvas, (int) ($w * $x), (int) ($h * .39), (int) ($w * ($x + .055)), (int) ($h * .22), $cream);
        imagefilledellipse($canvas, (int) ($w * ($x + .055)), (int) ($h * .22), (int) ($w * .045), (int) ($h * .045), $cream);
    }
}

function drawIcon(int $size): GdImage
{
    $scale = 4;
    $canvas = imagecreatetruecolor($size * $scale, $size * $scale);
    imagealphablending($canvas, true);
    imagesavealpha($canvas, true);
    $w = imagesx($canvas);
    $h = imagesy($canvas);
    $top = rgb('#18ad86');
    $bottom = rgb('#0d7c66');

    for ($y = 0; $y < $h; $y++) {
        $t = $y / max(1, $h - 1);
        $r = (int) round($top[0] * (1 - $t) + $bottom[0] * $t);
        $g = (int) round($top[1] * (1 - $t) + $bottom[1] * $t);
        $b = (int) round($top[2] * (1 - $t) + $bottom[2] * $t);
        imageline($canvas, 0, $y, $w, $y, imagecolorallocate($canvas, $r, $g, $b));
    }

    drawBrandMark($canvas, $size, $scale);

    $output = imagecreatetruecolor($size, $size);
    imagealphablending($output, true);
    imagesavealpha($output, true);
    imagecopyresampled($output, $canvas, 0, 0, 0, 0, $size, $size, $w, $h);

    return $output;
}

function drawLaunchMark(int $size): GdImage
{
    $scale = 4;
    $canvas = imagecreatetruecolor($size * $scale, $size * $scale);
    fillTransparent($canvas);
    drawBrandMark($canvas, $size, $scale);

    $output = imagecreatetruecolor($size, $size);
    fillTransparent($output);
    imagecopyresampled($output, $canvas, 0, 0, 0, 0, $size, $size, imagesx($canvas), imagesy($canvas));

    return $output;
}

function drawGradient(int $width, int $height): GdImage
{
    $image = imagecreatetruecolor($width, $height);
    $top = rgb('#18ad86');
    $bottom = rgb('#0d7c66');

    for ($y = 0; $y < $height; $y++) {
        $t = $y / max(1, $height - 1);
        $r = (int) round($top[0] * (1 - $t) + $bottom[0] * $t);
        $g = (int) round($top[1] * (1 - $t) + $bottom[1] * $t);
        $b = (int) round($top[2] * (1 - $t) + $bottom[2] * $t);
        imageline($image, 0, $y, $width, $y, imagecolorallocate($image, $r, $g, $b));
    }

    return $image;
}

function drawLaunchSplash(int $width, int $height): GdImage
{
    $image = drawGradient($width, $height);
    $markSize = (int) round(min($width, $height) * .82);
    $mark = drawLaunchMark($markSize);
    $x = (int) round(($width - $markSize) / 2);
    $y = (int) round(($height - $markSize) / 2);

    imagecopy($image, $mark, $x, $y, 0, 0, $markSize, $markSize);

    return $image;
}

function saveIcon(int $size, string $path): void
{
    if (! is_dir(dirname($path))) {
        mkdir(dirname($path), 0777, true);
    }
    $image = drawIcon($size);
    imagepng($image, $path);
}

function saveLaunchMark(int $size, string $path): void
{
    if (! is_dir(dirname($path))) {
        mkdir(dirname($path), 0777, true);
    }

    imagepng(drawLaunchMark($size), $path);
}

function saveGradient(int $width, int $height, string $path): void
{
    if (! is_dir(dirname($path))) {
        mkdir(dirname($path), 0777, true);
    }

    imagepng(drawGradient($width, $height), $path);
}

function saveLaunchSplash(int $width, int $height, string $path): void
{
    if (! is_dir(dirname($path))) {
        mkdir(dirname($path), 0777, true);
    }

    imagepng(drawLaunchSplash($width, $height), $path);
}

$iosIcons = [
    'Icon-App-20x20@1x.png' => 20, 'Icon-App-20x20@2x.png' => 40, 'Icon-App-20x20@3x.png' => 60,
    'Icon-App-29x29@1x.png' => 29, 'Icon-App-29x29@2x.png' => 58, 'Icon-App-29x29@3x.png' => 87,
    'Icon-App-40x40@1x.png' => 40, 'Icon-App-40x40@2x.png' => 80, 'Icon-App-40x40@3x.png' => 120,
    'Icon-App-60x60@2x.png' => 120, 'Icon-App-60x60@3x.png' => 180,
    'Icon-App-76x76@1x.png' => 76, 'Icon-App-76x76@2x.png' => 152,
    'Icon-App-83.5x83.5@2x.png' => 167, 'Icon-App-1024x1024@1x.png' => 1024,
];
foreach ($iosIcons as $file => $size) {
    saveIcon($size, "{$root}/ios/Runner/Assets.xcassets/AppIcon.appiconset/{$file}");
}

$androidIcons = [
    'mipmap-mdpi/ic_launcher.png' => 48, 'mipmap-hdpi/ic_launcher.png' => 72,
    'mipmap-xhdpi/ic_launcher.png' => 96, 'mipmap-xxhdpi/ic_launcher.png' => 144,
    'mipmap-xxxhdpi/ic_launcher.png' => 192,
    'mipmap-mdpi/launch_image.png' => 96, 'mipmap-hdpi/launch_image.png' => 144,
    'mipmap-xhdpi/launch_image.png' => 192, 'mipmap-xxhdpi/launch_image.png' => 288,
    'mipmap-xxxhdpi/launch_image.png' => 384,
];
foreach ($androidIcons as $file => $size) {
    if (str_contains($file, 'launch_image')) {
        saveLaunchMark($size, "{$root}/android/app/src/main/res/{$file}");
    } else {
        saveIcon($size, "{$root}/android/app/src/main/res/{$file}");
    }
}

$launchImages = ['LaunchImage.png' => 320, 'LaunchImage@2x.png' => 640, 'LaunchImage@3x.png' => 960];
foreach ($launchImages as $file => $size) {
    saveLaunchMark($size, "{$root}/ios/Runner/Assets.xcassets/LaunchImage.imageset/{$file}");
}

$launchGradients = [
    'LaunchGradient.png' => [390, 844],
    'LaunchGradient@2x.png' => [780, 1688],
    'LaunchGradient@3x.png' => [1170, 2532],
];
foreach ($launchGradients as $file => [$width, $height]) {
    saveGradient($width, $height, "{$root}/ios/Runner/Assets.xcassets/LaunchGradient.imageset/{$file}");
}

$launchSplashes = [
    'LaunchSplash.png' => [390, 844],
    'LaunchSplash@2x.png' => [780, 1688],
    'LaunchSplash@3x.png' => [1170, 2532],
];
foreach ($launchSplashes as $file => [$width, $height]) {
    saveLaunchSplash($width, $height, "{$root}/ios/Runner/Assets.xcassets/LaunchSplash.imageset/{$file}");
}

echo "Generated brand icons and launch images.\n";
