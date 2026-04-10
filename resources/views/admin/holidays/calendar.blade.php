@extends('layouts.app')

@section('title', 'Kalender Hari Libur')
@section('page-title', 'Kalender Hari Libur')

@section('content')
<div class="container-fluid fade-in-up">
    <div class="row">
        <div class="col-12">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-alt text-primary"></i> Kalender Hari Libur {{ $year }}
                    </h5>
                    <a href="{{ route('admin.holidays.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
                <div id="calendar" style="height: 600px;"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/id.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const calendarEl = document.getElementById('calendar');
        const calendar = new FullCalendar.Calendar(calendarEl, {
            locale: 'id',
            initialView: 'dayGridMonth',
            initialDate: '{{ $year }}-{{ $month }}-01',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,listWeek'
            },
            events: @json($calendarData),
            eventClick: function(info) {
                alert('Libur: ' + info.event.title + '\n' + (info.event.extendedProps.description || 'Tidak ada deskripsi'));
            },
            height: 'auto',
            buttonText: {
                today: 'Hari Ini',
                month: 'Bulan',
                week: 'Minggu',
                list: 'Daftar'
            }
        });
        calendar.render();
    });
</script>
@endpush
