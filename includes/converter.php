<?php
if (!isset($_FILES['images'])) {
    wp_die('Dosya bulunamadı.');
}

$upload_dir = wp_upload_dir();
$temp_dir = $upload_dir['basedir'] . '/izc-temp/';
$temp_url = $upload_dir['baseurl'] . '/izc-temp/';

if (!file_exists($temp_dir)) {
    mkdir($temp_dir, 0777, true);
}

require_once(ABSPATH . 'wp-admin/includes/file.php');

$converted_files = [];
$log = [];

foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
    $original_name = $_FILES['images']['name'][$key];
    $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

    $log[] = "👉 Yükleniyor: $original_name";

    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'bmp', 'tiff'];
    if (!in_array($ext, $allowed)) {
        $log[] = "❌ $original_name: Geçersiz dosya uzantısı ($ext)";
        continue;
    }

    $image_data = @file_get_contents($tmp_name);
    if (!$image_data) {
        $log[] = "❌ $original_name: Dosya okunamadı!";
        continue;
    }

    $image = @imagecreatefromstring($image_data);
    if (!$image) {
        $log[] = "❌ $original_name: Görsel oluşturulamadı!";
        continue;
    }

    $width = imagesx($image);
    $height = imagesy($image);

    $has_alpha = false;
    $image_type = exif_imagetype($tmp_name);
    $gd_info = gd_info();

    // Şeffaflık kontrolü (PNG, WebP, vs.)
    if (
        ($image_type === IMAGETYPE_PNG || $image_type === IMAGETYPE_WEBP)
        && imagecolortransparent($image) >= 0
    ) {
        $has_alpha = true;
    }

    if (!$has_alpha && function_exists('imagealphablending')) {
        // Tarama yaparak alfa varsa PNG'e geç
        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $rgba = imagecolorat($image, $x, $y);
                $alpha = ($rgba & 0x7F000000) >> 24;
                if ($alpha > 0) {
                    $has_alpha = true;
                    break 2;
                }
            }
        }
    }

    if ($has_alpha) {
        // Şeffaflık varsa PNG olarak kaydet
        $new_filename = pathinfo($original_name, PATHINFO_FILENAME) . '.png';
        $save_path = $temp_dir . $new_filename;
        imagesavealpha($image, true);
        $png = imagecreatetruecolor($width, $height);
        imagesavealpha($png, true);
        $transparent = imagecolorallocatealpha($png, 0, 0, 0, 127);
        imagefill($png, 0, 0, $transparent);
        imagecopy($png, $image, 0, 0, 0, 0, $width, $height);
        imagepng($png, $save_path);
        imagedestroy($png);
        $log[] = "✅ $original_name dönüştürüldü → $new_filename (şeffaf PNG)";
    } else {
        // Şeffaflık yoksa beyaz zeminle JPG
        $new_filename = pathinfo($original_name, PATHINFO_FILENAME) . '.jpg';
        $save_path = $temp_dir . $new_filename;
        $jpg = imagecreatetruecolor($width, $height);
        $white = imagecolorallocate($jpg, 255, 255, 255);
        imagefilledrectangle($jpg, 0, 0, $width, $height, $white);
        imagecopy($jpg, $image, 0, 0, 0, 0, $width, $height);
        imagejpeg($jpg, $save_path, 100);
        imagedestroy($jpg);
        $log[] = "✅ $original_name dönüştürüldü → $new_filename (beyaz arka planlı JPG)";
    }

    imagedestroy($image);
    $converted_files[] = $save_path;
}

// ZIP dosyasını oluştur
$zip_path = $temp_dir . 'converted_images.zip';
$zip = new ZipArchive();
if ($zip->open($zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    wp_die('❌ ZIP dosyası oluşturulamadı.');
}

// Görselleri ZIP'e ekle
foreach ($converted_files as $file) {
    $zip->addFile($file, basename($file));
}

// Log dosyasını ekle
$log_file_path = $temp_dir . 'log.txt';
file_put_contents($log_file_path, implode(PHP_EOL, $log));
$zip->addFile($log_file_path, 'log.txt');

$zip->close();

// ZIP dosyasını indir
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="converted_images.zip"');
header('Content-Length: ' . filesize($zip_path));
ob_clean();
flush();
readfile($zip_path);

// Geçici dosyaları temizle
foreach ($converted_files as $file) {
    @unlink($file);
}
@unlink($zip_path);
@unlink($log_file_path);
@rmdir($temp_dir);

exit;
