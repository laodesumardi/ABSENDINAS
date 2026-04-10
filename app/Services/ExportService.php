<?php

namespace App\Services;

use Illuminate\Http\Response;
use Carbon\Carbon;

class ExportService
{
    /**
     * Export data to CSV
     */
    public function exportToCSV($data, $headers, $filename)
    {
        $handle = fopen('php://temp', 'w');

        // Add UTF-8 BOM for Excel compatibility
        fwrite($handle, "\xEF\xBB\xBF");

        // Add headers
        fputcsv($handle, $headers);

        // Add data
        foreach ($data as $row) {
            fputcsv($handle, $row);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
        ]);
    }

    /**
     * Format date for export
     */
    public function formatDate($date, $format = 'd/m/Y')
    {
        if (!$date) return '-';
        return Carbon::parse($date)->format($format);
    }

    /**
     * Format datetime for export
     */
    public function formatDateTime($date, $format = 'd/m/Y H:i:s')
    {
        if (!$date) return '-';
        return Carbon::parse($date)->format($format);
    }

    /**
     * Format status text
     */
    public function formatStatus($status, $type = 'attendance')
    {
        $statuses = [
            'attendance' => [
                'present' => 'Hadir',
                'late' => 'Terlambat',
                'absent' => 'Tidak Hadir',
                'half_day' => 'Setengah Hari',
            ],
            'leave' => [
                'pending' => 'Menunggu',
                'approved' => 'Disetujui',
                'rejected' => 'Ditolak',
                'cancelled' => 'Dibatalkan',
            ],
            'user' => [
                'active' => 'Aktif',
                'inactive' => 'Nonaktif',
            ]
        ];

        return $statuses[$type][$status] ?? $status;
    }
}
