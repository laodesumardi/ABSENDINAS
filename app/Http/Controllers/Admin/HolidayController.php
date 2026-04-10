<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class HolidayController extends Controller
{
    public function index(Request $request)
    {
        $query = Holiday::query();

        // Filter by year
        $year = $request->filled('year') ? $request->year : date('Y');
        $query->whereYear('holiday_date', $year);

        // Filter by status (upcoming/past)
        if ($request->filled('status')) {
            if ($request->status == 'upcoming') {
                $query->where('holiday_date', '>=', today());
            } elseif ($request->status == 'past') {
                $query->where('holiday_date', '<', today());
            }
        }

        $holidays = $query->orderBy('holiday_date', 'asc')->paginate(15);

        // Get statistics
        $totalHolidays = Holiday::whereYear('holiday_date', $year)->count();
        $nationalHolidays = Holiday::whereYear('holiday_date', $year)->where('is_national_holiday', true)->count();
        $upcomingHolidays = Holiday::where('holiday_date', '>=', today())->count();
        $pastHolidays = Holiday::where('holiday_date', '<', today())->count();

        // Get upcoming holidays for sidebar
        $upcomingList = Holiday::getUpcomingHolidays(10);

        // Get available years for filter
        $years = Holiday::selectRaw('YEAR(holiday_date) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();

        if (empty($years)) {
            $years = [date('Y'), date('Y') + 1];
        }

        return view('admin.holidays.index', compact(
            'holidays',
            'totalHolidays',
            'nationalHolidays',
            'upcomingHolidays',
            'pastHolidays',
            'upcomingList',
            'year',
            'years'
        ));
    }

    public function create()
    {
        return view('admin.holidays.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'holiday_date' => 'required|date|unique:holidays,holiday_date',
            'is_national_holiday' => 'boolean',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $holiday = Holiday::create([
            'name' => $request->name,
            'holiday_date' => $request->holiday_date,
            'is_national_holiday' => $request->has('is_national_holiday'),
            'description' => $request->description,
        ]);

        // Log activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'create_holiday',
            'description' => "Menambahkan hari libur: {$holiday->name} ({$holiday->formatted_date})",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'new_data' => json_encode($holiday->toArray()),
        ]);

        return redirect()->route('admin.holidays.index')
            ->with('success', 'Hari libur berhasil ditambahkan!');
    }

    public function show(Holiday $holiday)
    {
        return view('admin.holidays.show', compact('holiday'));
    }

    public function edit(Holiday $holiday)
    {
        return view('admin.holidays.edit', compact('holiday'));
    }

    public function update(Request $request, Holiday $holiday)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'holiday_date' => 'required|date|unique:holidays,holiday_date,' . $holiday->id,
            'is_national_holiday' => 'boolean',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $oldData = $holiday->toArray();

        $holiday->update([
            'name' => $request->name,
            'holiday_date' => $request->holiday_date,
            'is_national_holiday' => $request->has('is_national_holiday'),
            'description' => $request->description,
        ]);

        // Log activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'update_holiday',
            'description' => "Mengupdate hari libur: {$holiday->name}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'old_data' => json_encode($oldData),
            'new_data' => json_encode($holiday->toArray()),
        ]);

        return redirect()->route('admin.holidays.index')
            ->with('success', 'Hari libur berhasil diupdate!');
    }

    public function destroy(Holiday $holiday)
    {
        $holidayName = $holiday->name;
        $holidayDate = $holiday->formatted_date;

        $holiday->delete();

        // Log activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'delete_holiday',
            'description' => "Menghapus hari libur: {$holidayName} ({$holidayDate})",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->route('admin.holidays.index')
            ->with('success', 'Hari libur berhasil dihapus!');
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:holidays,id',
        ]);

        $deletedCount = Holiday::whereIn('id', $request->ids)->delete();

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'bulk_delete_holidays',
            'description' => "Menghapus {$deletedCount} hari libur secara massal",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('admin.holidays.index')
            ->with('success', "{$deletedCount} hari libur berhasil dihapus!");
    }

    public function calendar(Request $request)
    {
        $year = $request->filled('year') ? $request->year : date('Y');
        $month = $request->filled('month') ? $request->month : date('m');

        $holidays = Holiday::whereYear('holiday_date', $year)
            ->whereMonth('holiday_date', $month)
            ->get();

        $calendarData = [];
        foreach ($holidays as $holiday) {
            $calendarData[] = [
                'title' => $holiday->name,
                'start' => $holiday->holiday_date->format('Y-m-d'),
                'color' => $holiday->is_national_holiday ? '#ef476f' : '#ffd166',
                'description' => $holiday->description,
            ];
        }

        return view('admin.holidays.calendar', compact('calendarData', 'year', 'month'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2020|max:2030',
        ]);

        $year = $request->year;
        $imported = 0;

        // National holidays for Indonesia (example for 2024)
        $nationalHolidays = $this->getIndonesianNationalHolidays($year);

        foreach ($nationalHolidays as $holiday) {
            if (!Holiday::where('holiday_date', $holiday['date'])->exists()) {
                Holiday::create([
                    'name' => $holiday['name'],
                    'holiday_date' => $holiday['date'],
                    'is_national_holiday' => true,
                    'description' => 'Hari Libur Nasional ' . $year,
                ]);
                $imported++;
            }
        }

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'import_holidays',
            'description' => "Mengimpor {$imported} hari libur nasional untuk tahun {$year}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('admin.holidays.index')
            ->with('success', "Berhasil mengimpor {$imported} hari libur nasional untuk tahun {$year}");
    }

    private function getIndonesianNationalHolidays($year)
    {
        // This is a sample. You can expand this or integrate with an API
        $holidays = [
            ['name' => 'Tahun Baru Masehi', 'date' => "{$year}-01-01"],
            ['name' => 'Hari Raya Nyepi', 'date' => "{$year}-03-11"],
            ['name' => 'Hari Buruh', 'date' => "{$year}-05-01"],
            ['name' => 'Hari Kebangkitan Nasional', 'date' => "{$year}-05-20"],
            ['name' => 'Hari Lahir Pancasila', 'date' => "{$year}-06-01"],
            ['name' => 'Hari Kemerdekaan RI', 'date' => "{$year}-08-17"],
            ['name' => 'Hari Kesaktian Pancasila', 'date' => "{$year}-10-01"],
            ['name' => 'Hari Pahlawan', 'date' => "{$year}-11-10"],
            ['name' => 'Hari Natal', 'date' => "{$year}-12-25"],
        ];

        // Add Islamic holidays (dates vary by year)
        // This is simplified - in production, use proper calculation or API
        if ($year == 2024) {
            $holidays[] = ['name' => 'Idul Fitri 1445 H', 'date' => '2024-04-10'];
            $holidays[] = ['name' => 'Idul Fitri 1445 H', 'date' => '2024-04-11'];
            $holidays[] = ['name' => 'Idul Adha 1445 H', 'date' => '2024-06-17'];
            $holidays[] = ['name' => 'Tahun Baru Islam 1446 H', 'date' => '2024-07-07'];
            $holidays[] = ['name' => 'Maulid Nabi Muhammad SAW', 'date' => '2024-09-15'];
            $holidays[] = ['name' => 'Isra Miraj Nabi Muhammad SAW', 'date' => '2024-02-08'];
        } elseif ($year == 2025) {
            $holidays[] = ['name' => 'Idul Fitri 1446 H', 'date' => '2025-03-31'];
            $holidays[] = ['name' => 'Idul Fitri 1446 H', 'date' => '2025-04-01'];
            $holidays[] = ['name' => 'Idul Adha 1446 H', 'date' => '2025-06-06'];
            $holidays[] = ['name' => 'Tahun Baru Islam 1447 H', 'date' => '2025-06-26'];
            $holidays[] = ['name' => 'Maulid Nabi Muhammad SAW', 'date' => '2025-09-04'];
            $holidays[] = ['name' => 'Isra Miraj Nabi Muhammad SAW', 'date' => '2025-01-27'];
        }

        return $holidays;
    }
}
