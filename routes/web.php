<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AttendanceReportController;
use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\Admin\HolidayController;
use App\Http\Controllers\Admin\WorkLocationController;
use App\Http\Controllers\Admin\WorkScheduleController;
use App\Http\Controllers\Operator\DashboardController as OperatorDashboardController;
use App\Http\Controllers\Operator\LeaveApprovalController;
use App\Http\Controllers\Employee\AttendanceController;
use App\Http\Controllers\Employee\LeaveController;
use App\Http\Controllers\Employee\StatisticsController;
use App\Http\Controllers\Operator\AttendanceValidationController;
use App\Http\Controllers\Operator\EmployeeController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Dashboard route with role-based redirection
Route::get('/dashboard', function () {
    if (!Auth::check()) {
        return redirect()->route('login');
    }

    $user = Auth::user();

    if ($user->role === 'admin') {
        return redirect()->route('admin.dashboard');
    } elseif ($user->role === 'operator') {
        return redirect()->route('operator.dashboard');
    } else {
        return redirect()->route('employee.attendance.index');
    }
})->name('dashboard');

// Authenticated routes with Breeze
Route::middleware('auth')->group(function () {
    // Profile routes (from Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin routes
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::resource('/users', UserController::class);
        Route::get('/attendance-reports', [AttendanceReportController::class, 'index'])->name('reports.attendance');
        Route::get('/attendance-reports/export', [AttendanceReportController::class, 'export'])->name('reports.export');
    });

    // Operator routes
    Route::middleware(['role:operator,admin'])->prefix('operator')->name('operator.')->group(function () {
        Route::get('/dashboard', [OperatorDashboardController::class, 'index'])->name('dashboard');
        Route::get('/leaves', [LeaveApprovalController::class, 'index'])->name('leaves.index');
        Route::post('/leaves/{leave}/approve', [LeaveApprovalController::class, 'approve'])->name('leaves.approve');
        Route::post('/leaves/{leave}/reject', [LeaveApprovalController::class, 'reject'])->name('leaves.reject');
        Route::get('/leaves/pending-count', [LeaveApprovalController::class, 'pendingCount'])->name('leaves.pending-count');
    });

    // Employee routes
    Route::middleware(['role:employee,operator,admin'])->prefix('employee')->name('employee.')->group(function () {
        Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn'])->name('attendance.check-in');
        Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut'])->name('attendance.check-out');
        Route::get('/attendance/history', [AttendanceController::class, 'history'])->name('attendance.history');
    });
});

// Auth routes (Breeze)
require __DIR__ . '/auth.php';









Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::resource('/users', UserController::class);
    Route::put('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::get('/users/export', [UserController::class, 'export'])->name('users.export');
    Route::get('/attendance-reports', [AttendanceReportController::class, 'index'])->name('reports.attendance');
    Route::get('/attendance-reports/export', [AttendanceReportController::class, 'export'])->name('reports.export');
});


// Authenticated routes with Breeze
Route::middleware('auth')->group(function () {
    // Profile routes (from Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin routes
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::resource('/users', UserController::class);
        Route::get('/attendance-reports', [AttendanceReportController::class, 'index'])->name('reports.attendance');
        Route::get('/attendance-reports/export', [AttendanceReportController::class, 'export'])->name('reports.export');
    });

    // Operator routes
    Route::middleware(['role:operator,admin'])->prefix('operator')->name('operator.')->group(function () {
        Route::get('/dashboard', [OperatorDashboardController::class, 'index'])->name('dashboard');
        Route::get('/leaves', [LeaveApprovalController::class, 'index'])->name('leaves.index');
        Route::post('/leaves/{leave}/approve', [LeaveApprovalController::class, 'approve'])->name('leaves.approve');
        Route::post('/leaves/{leave}/reject', [LeaveApprovalController::class, 'reject'])->name('leaves.reject');
    });

    // Employee routes
    Route::middleware(['role:employee,operator,admin'])->prefix('employee')->name('employee.')->group(function () {
        Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn'])->name('attendance.check-in');
        Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut'])->name('attendance.check-out');
        Route::get('/attendance/history', [AttendanceController::class, 'history'])->name('attendance.history');
    });
});


// Admin routes
Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::resource('/users', UserController::class);
    Route::put('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::get('/users/export', [UserController::class, 'export'])->name('users.export');

    // Attendance Reports
    Route::get('/attendance-reports', [AttendanceReportController::class, 'index'])->name('reports.attendance');
    Route::get('/attendance-reports/export', [AttendanceReportController::class, 'export'])->name('reports.export');
    Route::get('/attendance-reports/print', [AttendanceReportController::class, 'print'])->name('reports.print');
    Route::get('/attendance-reports/pdf', [AttendanceReportController::class, 'exportPdf'])->name('reports.pdf');
});



// Admin routes
Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // User Management
    Route::resource('/users', UserController::class);
    Route::put('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::get('/users/export', [UserController::class, 'export'])->name('users.export');

    // Attendance Reports
    Route::get('/attendance-reports', [AttendanceReportController::class, 'index'])->name('reports.attendance');
    Route::get('/attendance-reports/export', [AttendanceReportController::class, 'export'])->name('reports.export');
    Route::get('/attendance-reports/print', [AttendanceReportController::class, 'print'])->name('reports.print');

    // Work Locations
    Route::resource('/locations', WorkLocationController::class);
    Route::put('/locations/{location}/toggle-status', [WorkLocationController::class, 'toggleStatus'])->name('locations.toggle-status');
    Route::post('/locations/validate', [WorkLocationController::class, 'validateLocation'])->name('locations.validate');
});


// Admin routes
Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // User Management
    Route::resource('/users', UserController::class);
    Route::put('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::get('/users/export', [UserController::class, 'export'])->name('users.export');

    // Attendance Reports
    Route::get('/attendance-reports', [AttendanceReportController::class, 'index'])->name('reports.attendance');
    Route::get('/attendance-reports/export', [AttendanceReportController::class, 'export'])->name('reports.export');
    Route::get('/attendance-reports/print', [AttendanceReportController::class, 'print'])->name('reports.print');

    // Work Locations
    Route::resource('/locations', WorkLocationController::class);
    Route::put('/locations/{location}/toggle-status', [WorkLocationController::class, 'toggleStatus'])->name('locations.toggle-status');
    Route::post('/locations/validate', [WorkLocationController::class, 'validateLocation'])->name('locations.validate');

    // Work Schedules
    Route::get('/schedules', [WorkScheduleController::class, 'index'])->name('schedules.index');
    Route::get('/schedules/{day}/edit', [WorkScheduleController::class, 'edit'])->name('schedules.edit');
    Route::put('/schedules/{day}', [WorkScheduleController::class, 'update'])->name('schedules.update');
    Route::post('/schedules/reset', [WorkScheduleController::class, 'reset'])->name('schedules.reset');
    Route::get('/schedules/api/data', [WorkScheduleController::class, 'getScheduleApi'])->name('schedules.api');
});


Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // ... existing routes ...

    // Holidays
    Route::resource('/holidays', HolidayController::class);
    Route::delete('/holidays/bulk-delete', [HolidayController::class, 'bulkDelete'])->name('holidays.bulk-delete');
    Route::get('/holidays/calendar/view', [HolidayController::class, 'calendar'])->name('holidays.calendar');
    Route::post('/holidays/import', [HolidayController::class, 'import'])->name('holidays.import');
});

// Admin routes
Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // ... existing routes ...

    // Activity Logs
    Route::get('/activity', [ActivityLogController::class, 'index'])->name('activity.index');
    Route::get('/activity/export', [ActivityLogController::class, 'export'])->name('activity.export');
    Route::get('/activity/clear-old', [ActivityLogController::class, 'clearOld'])->name('activity.clear-old');
    Route::get('/activity/clear-all', [ActivityLogController::class, 'clearAll'])->name('activity.clear-all');
    Route::get('/activity/{log}', [ActivityLogController::class, 'show'])->name('activity.show');
    Route::delete('/activity/{log}', [ActivityLogController::class, 'destroy'])->name('activity.destroy');
});


// Employee routes
Route::middleware(['role:employee,operator,admin'])->prefix('employee')->name('employee.')->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn'])->name('attendance.check-in');
    Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut'])->name('attendance.check-out');
    Route::get('/attendance/history', [AttendanceController::class, 'history'])->name('attendance.history');
    Route::get('/attendance/current-location', [AttendanceController::class, 'getCurrentLocation'])->name('attendance.current-location');
});


// Employee routes
Route::middleware(['role:employee,operator,admin'])->prefix('employee')->name('employee.')->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn'])->name('attendance.check-in');
    Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut'])->name('attendance.check-out');
    Route::get('/attendance/history', [AttendanceController::class, 'history'])->name('attendance.history');
    Route::get('/attendance/current-location', [AttendanceController::class, 'getCurrentLocation'])->name('attendance.current-location');
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'getDetail'])->name('attendance.detail');
});


// Employee routes
Route::middleware(['role:employee,operator,admin'])->prefix('employee')->name('employee.')->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn'])->name('attendance.check-in');
    Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut'])->name('attendance.check-out');
    Route::get('/attendance/history', [AttendanceController::class, 'history'])->name('attendance.history');
    Route::get('/attendance/current-location', [AttendanceController::class, 'getCurrentLocation'])->name('attendance.current-location');
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'getDetail'])->name('attendance.detail');
    Route::post('/attendance/verify-location', [AttendanceController::class, 'verifyLocation'])->name('attendance.verify-location');
});


Route::post('/attendance/verify-location', [AttendanceController::class, 'verifyLocation'])->name('attendance.verify-location');


// Employee routes
Route::middleware(['role:employee,operator,admin'])->prefix('employee')->name('employee.')->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn'])->name('attendance.check-in');
    Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut'])->name('attendance.check-out');
    Route::get('/attendance/history', [AttendanceController::class, 'history'])->name('attendance.history');
    Route::get('/attendance/current-location', [AttendanceController::class, 'getCurrentLocation'])->name('attendance.current-location');
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'getDetail'])->name('attendance.detail');
    Route::post('/attendance/verify-location', [AttendanceController::class, 'verifyLocation'])->name('attendance.verify-location');

    // Leave routes
    Route::resource('/leaves', LeaveController::class);
    Route::delete('/leaves/{leave}/cancel', [LeaveController::class, 'cancel'])->name('leaves.cancel');
});


// Employee routes
Route::middleware(['role:employee,operator,admin'])->prefix('employee')->name('employee.')->group(function () {
    // ... existing routes ...

    // Statistics
    Route::get('/statistics', [StatisticsController::class, 'index'])->name('statistics.index');
    Route::get('/statistics/export', [StatisticsController::class, 'export'])->name('statistics.export');
});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/change-password', [ProfileController::class, 'changePassword'])->name('profile.change-password');
    Route::delete('/profile/delete-photo', [ProfileController::class, 'deletePhoto'])->name('profile.delete-photo');
});


// Operator routes
Route::middleware(['role:operator,admin'])->prefix('operator')->name('operator.')->group(function () {
    Route::get('/dashboard', [OperatorDashboardController::class, 'index'])->name('dashboard');

    // Leave Management
    Route::get('/leaves', [LeaveApprovalController::class, 'index'])->name('leaves.index');
    Route::get('/leaves/export', [LeaveApprovalController::class, 'export'])->name('leaves.export');
    Route::get('/leaves/{leave}', [LeaveApprovalController::class, 'show'])->name('leaves.show');
    Route::post('/leaves/{leave}/approve', [LeaveApprovalController::class, 'approve'])->name('leaves.approve');
    Route::post('/leaves/{leave}/reject', [LeaveApprovalController::class, 'reject'])->name('leaves.reject');
    Route::post('/leaves/bulk-approve', [LeaveApprovalController::class, 'bulkApprove'])->name('leaves.bulk-approve');
    Route::get('/leaves/pending-count', [LeaveApprovalController::class, 'pendingCount'])->name('leaves.pending-count');
});


// Operator routes
Route::middleware(['role:operator,admin'])->prefix('operator')->name('operator.')->group(function () {
    Route::get('/dashboard', [OperatorDashboardController::class, 'index'])->name('dashboard');

    // Leave Management
    Route::get('/leaves', [LeaveApprovalController::class, 'index'])->name('leaves.index');
    Route::get('/leaves/export', [LeaveApprovalController::class, 'export'])->name('leaves.export');
    Route::get('/leaves/{leave}', [LeaveApprovalController::class, 'show'])->name('leaves.show');
    Route::post('/leaves/{leave}/approve', [LeaveApprovalController::class, 'approve'])->name('leaves.approve');
    Route::post('/leaves/{leave}/reject', [LeaveApprovalController::class, 'reject'])->name('leaves.reject');
    Route::post('/leaves/bulk-approve', [LeaveApprovalController::class, 'bulkApprove'])->name('leaves.bulk-approve');
    Route::get('/leaves/pending-count', [LeaveApprovalController::class, 'pendingCount'])->name('leaves.pending-count');

    // Attendance Validation
    Route::get('/attendance', [AttendanceValidationController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/create', [AttendanceValidationController::class, 'createManual'])->name('attendance.create');
    Route::post('/attendance/store-manual', [AttendanceValidationController::class, 'createManual'])->name('attendance.store-manual');
    Route::get('/attendance/export', [AttendanceValidationController::class, 'export'])->name('attendance.export');
    Route::get('/attendance/{attendance}', [AttendanceValidationController::class, 'show'])->name('attendance.show');
    Route::get('/attendance/{attendance}/edit', [AttendanceValidationController::class, 'edit'])->name('attendance.edit');
    Route::put('/attendance/{attendance}', [AttendanceValidationController::class, 'update'])->name('attendance.update');
    Route::post('/attendance/{attendance}/validate', [AttendanceValidationController::class, 'validate'])->name('attendance.validate');
    Route::post('/attendance/bulk-validate', [AttendanceValidationController::class, 'bulkValidate'])->name('attendance.bulk-validate');
});


// Operator routes
Route::middleware(['role:operator,admin'])->prefix('operator')->name('operator.')->group(function () {
    Route::get('/dashboard', [OperatorDashboardController::class, 'index'])->name('dashboard');

    // Leave Management
    Route::get('/leaves', [LeaveApprovalController::class, 'index'])->name('leaves.index');
    Route::get('/leaves/export', [LeaveApprovalController::class, 'export'])->name('leaves.export');
    Route::get('/leaves/{leave}', [LeaveApprovalController::class, 'show'])->name('leaves.show');
    Route::post('/leaves/{leave}/approve', [LeaveApprovalController::class, 'approve'])->name('leaves.approve');
    Route::post('/leaves/{leave}/reject', [LeaveApprovalController::class, 'reject'])->name('leaves.reject');
    Route::post('/leaves/bulk-approve', [LeaveApprovalController::class, 'bulkApprove'])->name('leaves.bulk-approve');
    Route::get('/leaves/pending-count', [LeaveApprovalController::class, 'pendingCount'])->name('leaves.pending-count');

    // Attendance Validation
    Route::get('/attendance', [AttendanceValidationController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/create', [AttendanceValidationController::class, 'create'])->name('attendance.create');
    Route::post('/attendance/store-manual', [AttendanceValidationController::class, 'createManual'])->name('attendance.store-manual');
    Route::get('/attendance/export', [AttendanceValidationController::class, 'export'])->name('attendance.export');
    Route::get('/attendance/{attendance}', [AttendanceValidationController::class, 'show'])->name('attendance.show');
    Route::get('/attendance/{attendance}/edit', [AttendanceValidationController::class, 'edit'])->name('attendance.edit');
    Route::put('/attendance/{attendance}', [AttendanceValidationController::class, 'update'])->name('attendance.update');
    Route::post('/attendance/{attendance}/validate', [AttendanceValidationController::class, 'validate'])->name('attendance.validate');
    Route::post('/attendance/bulk-validate', [AttendanceValidationController::class, 'bulkValidate'])->name('attendance.bulk-validate');

    // Employee Data
    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('/employees/export', [EmployeeController::class, 'export'])->name('employees.export');
    Route::get('/employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');
    Route::get('/employees/{employee}/attendance', [EmployeeController::class, 'attendance'])->name('employees.attendance');
    Route::get('/employees/{employee}/leaves', [EmployeeController::class, 'leaves'])->name('employees.leaves');
});


Route::middleware(['role:operator,admin'])->prefix('operator')->name('operator.')->group(function () {
    // ... existing routes ...

    // Additional API routes for manual attendance
    Route::get('/attendance/get-schedule', [AttendanceValidationController::class, 'getSchedule'])->name('attendance.get-schedule');
    Route::get('/attendance/calculate-late', [AttendanceValidationController::class, 'calculateLate'])->name('attendance.calculate-late');
});


// Operator routes
Route::middleware(['role:operator,admin'])->prefix('operator')->name('operator.')->group(function () {
    // ... existing routes ...

    // Attendance Reports
    Route::get('/reports/attendance', [AttendanceReportController::class, 'index'])->name('reports.attendance');
    Route::get('/reports/export', [AttendanceReportController::class, 'export'])->name('reports.export');
    Route::get('/reports/print', [AttendanceReportController::class, 'print'])->name('reports.print');
});

// Admin routes
Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // ... existing routes ...

    // Export routes
    Route::get('/export/users', [ExportController::class, 'exportUsers'])->name('export.users');
    Route::get('/export/activity-log', [ExportController::class, 'exportActivityLog'])->name('export.activity-log');
});


// Employee routes
Route::middleware(['role:employee,operator,admin'])->prefix('employee')->name('employee.')->group(function () {
    // ... other routes ...

    // Leave routes
    Route::resource('/leaves', LeaveController::class);
    Route::delete('/leaves/{leave}/cancel', [LeaveController::class, 'cancel'])->name('leaves.cancel');
});

// Admin routes
Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // ... existing routes ...

    // Attendance Reports with Edit
    Route::get('/reports/attendance', [AttendanceReportController::class, 'index'])->name('reports.attendance');
    Route::get('/reports/attendance/create', [AttendanceReportController::class, 'create'])->name('reports.create');
    Route::post('/reports/attendance', [AttendanceReportController::class, 'store'])->name('reports.store');
    Route::get('/reports/attendance/{id}/edit', [AttendanceReportController::class, 'edit'])->name('reports.edit');
    Route::put('/reports/attendance/{id}', [AttendanceReportController::class, 'update'])->name('reports.update');
    Route::delete('/reports/attendance/{id}', [AttendanceReportController::class, 'destroy'])->name('reports.destroy');
    Route::post('/reports/attendance/bulk-update', [AttendanceReportController::class, 'bulkUpdate'])->name('reports.bulk-update');
    Route::get('/reports/attendance/export', [AttendanceReportController::class, 'export'])->name('reports.export');
});

// Admin routes
Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // ... existing routes ...

    // Work Schedules
    Route::get('/schedules', [WorkScheduleController::class, 'index'])->name('schedules.index');
    Route::get('/schedules/{day}/edit', [WorkScheduleController::class, 'edit'])->name('schedules.edit');
    Route::put('/schedules/{day}', [WorkScheduleController::class, 'update'])->name('schedules.update');
    Route::post('/schedules/reset', [WorkScheduleController::class, 'reset'])->name('schedules.reset'); // POST method
});
