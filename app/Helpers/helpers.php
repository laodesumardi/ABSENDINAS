<?php

if (!function_exists('getPhotoUrl')) {
    function getPhotoUrl($path)
    {
        if (!$path) {
            return null;
        }

        // Cek apakah sudah ada URL lengkap
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        // Untuk localhost
        if (app()->environment('local')) {
            return asset('storage/' . $path);
        }

        // Untuk production (hosting)
        return url('storage/' . $path);
    }
}

if (!function_exists('formatDateIndonesia')) {
    function formatDateIndonesia($date)
    {
        if (!$date) {
            return '-';
        }

        $bulan = [
            1 => 'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember'
        ];

        $tanggal = date('d', strtotime($date));
        $bulanNum = (int)date('m', strtotime($date));
        $tahun = date('Y', strtotime($date));

        return $tanggal . ' ' . $bulan[$bulanNum] . ' ' . $tahun;
    }
}
