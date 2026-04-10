<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WorkLocation;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class WorkLocationController extends Controller
{
    public function index()
    {
        $locations = WorkLocation::orderBy('is_active', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $activeLocation = WorkLocation::where('is_active', true)->first();

        return view('admin.locations.index', compact('locations', 'activeLocation'));
    }

    public function create()
    {
        return view('admin.locations.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'address' => 'nullable|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'required|integer|min:10|max:1000',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // If this location is set as active, deactivate others
        if ($request->is_active) {
            WorkLocation::where('is_active', true)->update(['is_active' => false]);
        }

        $location = WorkLocation::create([
            'name' => $request->name,
            'address' => $request->address,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'radius' => $request->radius,
            'is_active' => $request->has('is_active'),
        ]);

        // Log activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'create_location',
            'description' => "Menambahkan lokasi kerja: {$location->name}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'new_data' => json_encode($location->toArray()),
        ]);

        return redirect()->route('admin.locations.index')
            ->with('success', 'Lokasi kerja berhasil ditambahkan!');
    }

    public function show(WorkLocation $location)
    {
        return view('admin.locations.show', compact('location'));
    }

    public function edit(WorkLocation $location)
    {
        return view('admin.locations.edit', compact('location'));
    }

    public function update(Request $request, WorkLocation $location)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'address' => 'nullable|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'required|integer|min:10|max:1000',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $oldData = $location->toArray();

        // If this location is set as active, deactivate others
        if ($request->is_active && !$location->is_active) {
            WorkLocation::where('is_active', true)->update(['is_active' => false]);
        }

        $location->update([
            'name' => $request->name,
            'address' => $request->address,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'radius' => $request->radius,
            'is_active' => $request->has('is_active'),
        ]);

        // Log activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'update_location',
            'description' => "Mengupdate lokasi kerja: {$location->name}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'old_data' => json_encode($oldData),
            'new_data' => json_encode($location->toArray()),
        ]);

        return redirect()->route('admin.locations.index')
            ->with('success', 'Lokasi kerja berhasil diupdate!');
    }

    public function destroy(WorkLocation $location)
    {
        $locationName = $location->name;
        $location->delete();

        // Log activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'delete_location',
            'description' => "Menghapus lokasi kerja: {$locationName}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->route('admin.locations.index')
            ->with('success', 'Lokasi kerja berhasil dihapus!');
    }

    public function toggleStatus(WorkLocation $location)
    {
        if (!$location->is_active) {
            // Deactivate all other locations
            WorkLocation::where('is_active', true)->update(['is_active' => false]);
        }

        $location->update(['is_active' => !$location->is_active]);

        $status = $location->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->back()->with('success', "Lokasi kerja berhasil {$status}!");
    }

    public function validateLocation(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $activeLocation = WorkLocation::where('is_active', true)->first();

        if (!$activeLocation) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada lokasi kerja yang aktif'
            ]);
        }

        $isValid = $activeLocation->isWithinRadius(
            $request->latitude,
            $request->longitude
        );

        $distance = $activeLocation->calculateDistance(
            $request->latitude,
            $request->longitude,
            $activeLocation->latitude,
            $activeLocation->longitude
        );

        return response()->json([
            'success' => $isValid,
            'message' => $isValid ? 'Anda berada dalam radius yang diizinkan' : 'Anda berada di luar radius yang diizinkan',
            'distance' => round($distance),
            'max_distance' => $activeLocation->radius,
            'location' => $activeLocation->name
        ]);
    }
}
