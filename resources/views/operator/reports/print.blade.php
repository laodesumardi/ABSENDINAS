<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Absensi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print {
                display: none;
            }
            body {
                padding: 20px;
                font-size: 12px;
            }
            .page-break {
                page-break-before: always;
            }
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #3a0ca3;
            padding-bottom: 20px;
        }
        .header h2 {
            color: #3a0ca3;
            margin-bottom: 5px;
        }
        .info-box {
            margin-bottom: 20px;
            padding: 10px;
            background: #f8f9fc;
            border-radius: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #3a0ca3;
            color: white;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h2>LAPORAN REKAP ABSENSI</h2>
            <h4>Sistem Absensi Karyawan</h4>
            <p>Periode: {{ Carbon\Carbon::parse($startDate)->format('d F Y') }} - {{ Carbon\Carbon::parse($endDate)->format('d F Y') }}</p>
            @if($department)
                <p>Departemen: {{ $department }}</p>
            @else
                <p>Semua Departemen</p>
            @endif
        </div>

        <!-- Info -->
        <div class="info-box">
            <div class="row">
                <div class="col-md-4">
                    <strong>Tanggal Cetak:</strong> {{ now()->format('d/m/Y H:i:s') }}
                </div>
                <div class="col-md-4">
                    <strong>Total Pegawai:</strong> {{ count($reportData) }}
                </div>
                <div class="col-md-4">
                    <strong>Dicetak Oleh:</strong> {{ auth()->user()->name }}
                </div>
            </div>
        </div>

        <!-- Table -->
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>ID Pegawai</th>
                    <th>Nama</th>
                    <th>Departemen</th>
                    <th>Hari Kerja</th>
                    <th>Hadir</th>
                    <th>Terlambat</th>
                    <th>Setengah Hari</th>
                    <th>Tidak Hadir</th>
                    <th>Kehadiran (%)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reportData as $index => $data)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $data['employee_id'] ?? '-' }}</td>
                    <td>{{ $data['name'] }}</td>
                    <td>{{ $data['department'] ?? '-' }}</td>
                    <td>{{ $data['working_days'] }}</td>
                    <td>{{ $data['present'] }}</td>
                    <td>{{ $data['late'] }}</td>
                    <td>{{ $data['half_day'] }}</td>
                    <td>{{ $data['absent'] }}</td>
                    <td>{{ $data['attendance_rate'] }}%</td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center">Tidak ada data</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Footer -->
        <div class="footer">
            <p>Laporan ini dibuat secara otomatis oleh sistem</p>
        </div>
    </div>

    <div class="no-print text-center mt-4">
        <button onclick="window.print()" class="btn btn-primary">Print</button>
        <button onclick="window.close()" class="btn btn-secondary">Tutup</button>
    </div>
</body>
</html>
