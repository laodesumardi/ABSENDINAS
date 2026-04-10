<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Absensi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print {
                display: none;
            }
            body {
                padding: 20px;
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
            <h2>SISTEM ABSENSI</h2>
            <h4>Laporan Kehadiran Pegawai</h4>
            <p>Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
            @if($selectedUser)
                <p>Pegawai: {{ $selectedUser->name }} ({{ $selectedUser->employee_id ?? '-' }})</p>
            @else
                <p>Semua Pegawai</p>
            @endif
        </div>

        <!-- Info -->
        <div class="info-box">
            <div class="row">
                <div class="col-md-3">
                    <strong>Total Kehadiran:</strong> {{ $attendances->count() }}
                </div>
                <div class="col-md-3">
                    <strong>Tanggal Cetak:</strong> {{ now()->format('d/m/Y H:i:s') }}
                </div>
                <div class="col-md-3">
                    <strong>Status:</strong> {{ request('status') ?: 'Semua' }}
                </div>
            </div>
        </div>

        <!-- Table -->
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Nama Pegawai</th>
                    <th>Check In</th>
                    <th>Check Out</th>
                    <th>Status</th>
                    <th>Terlambat</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendances as $index => $attendance)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($attendance->attendance_date)->format('d/m/Y') }}</td>
                    <td>{{ $attendance->user->name }}</td>
                    <td>{{ $attendance->check_in_time }}</td>
                    <td>{{ $attendance->check_out_time ?? '-' }}</td>
                    <td>
                        @php
                            $statusText = [
                                'present' => 'Hadir',
                                'late' => 'Terlambat',
                                'absent' => 'Tidak Hadir',
                                'half_day' => 'Setengah Hari'
                            ][$attendance->status] ?? $attendance->status;
                        @endphp
                        {{ $statusText }}
                    </td>
                    <td>{{ $attendance->late_minutes > 0 ? $attendance->late_minutes . ' menit' : '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center">Tidak ada data</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Footer -->
        <div class="footer">
            <p>Dicetak oleh: {{ auth()->user()->name }} | {{ now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>

    <div class="no-print text-center mt-4">
        <button onclick="window.print()" class="btn btn-primary">Print</button>
        <button onclick="window.close()" class="btn btn-secondary">Tutup</button>
    </div>
</body>
</html>
