<?php
// File: public/create-storage-link.php
// Akses: https://absen.simpegsmpbursel.com/create-storage-link.php

echo "<h2>Membuat Storage Link</h2>";

$target = __DIR__ . '/../storage/app/public';
$link = __DIR__ . '/storage';

echo "<p>Target: $target</p>";
echo "<p>Link: $link</p>";

if (file_exists($link)) {
    echo "<p style='color: green;'>✅ Symlink/folder sudah ada!</p>";
    echo "<p>Path: " . realpath($link) . "</p>";
} else {
    // Coba buat symlink dulu
    if (function_exists('symlink')) {
        if (@symlink($target, $link)) {
            echo "<p style='color: green;'>✅ Symlink berhasil dibuat!</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Gagal membuat symlink, mencoba copy folder...</p>";
            copyDirectory($target, $link);
        }
    } else {
        echo "<p style='color: orange;'>⚠️ Fungsi symlink tidak tersedia, mencoba copy folder...</p>";
        copyDirectory($target, $link);
    }
}

function copyDirectory($source, $destination)
{
    if (!is_dir($source)) {
        echo "<p style='color: red;'>❌ Source folder tidak ditemukan: $source</p>";
        return false;
    }

    if (!is_dir($destination)) {
        mkdir($destination, 0777, true);
    }

    $files = scandir($source);
    $count = 0;

    foreach ($files as $file) {
        if ($file == '.' || $file == '..') {
            continue;
        }

        $sourcePath = $source . DIRECTORY_SEPARATOR . $file;
        $destPath = $destination . DIRECTORY_SEPARATOR . $file;

        if (is_dir($sourcePath)) {
            copyDirectory($sourcePath, $destPath);
        } else {
            copy($sourcePath, $destPath);
            $count++;
        }
    }

    echo "<p style='color: green;'>✅ $count file berhasil dicopy!</p>";
    return true;
}

echo "<p><a href='/'>Kembali ke halaman utama</a></p>";
