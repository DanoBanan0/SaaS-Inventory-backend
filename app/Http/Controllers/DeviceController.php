<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\Assignment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DeviceController extends Controller
{
    public function index(Request $request)
    {
        $query = Device::with(['category', 'employee.unit', 'purchase']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->boolean('unassigned')) {
            $query->whereNull('employee_id');
        }

        if ($request->has('unit')) {
            $query->whereHas('employee.unit', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->unit . '%');
            });
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('inventory_code', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhereHas('employee', fn($e) => $e->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->has('date_from')) {
            $query->whereRaw("DATE(DATE_SUB(updated_at, INTERVAL 6 HOUR)) >= ?", [$request->date_from]);
        }

        if ($request->has('date_to')) {
            $query->whereRaw("DATE(DATE_SUB(updated_at, INTERVAL 6 HOUR)) <= ?", [$request->date_to]);
        }

        if ($request->has('specs')) {
            foreach ($request->input('specs') as $key => $value) {
                $query->where("specs->{$key}", $value);
            }
        }

        return response()->json($query->paginate(25));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'purchase_id' => 'required|exists:purchases,id',
            'inventory_code' => 'required|unique:devices',
            'serial_number' => 'required|unique:devices',
            'brand' => 'required',
            'model' => 'required',
            'specs' => 'nullable|array',
            'employee_id' => 'nullable|exists:employees,id',
        ]);

        return DB::transaction(function () use ($request) {
            $data = $request->all();
            if (!$request->has('status')) {
                $data['status'] = $request->employee_id ? 'assigned' : 'available';
            }
            $device = Device::create($data);

            if ($request->employee_id) {
                Assignment::create([
                    'device_id' => $device->id,
                    'employee_id' => $request->employee_id,
                    'assigned_by' => Auth::id(),
                    'assigned_at' => now(),
                    'notes' => 'Asignación inicial al crear el dispositivo.',
                ]);
            }

            return response()->json($device, 201);
        });
    }

    public function show(Device $device)
    {
        return response()->json($device->load(['category', 'employee', 'assignments.employee', 'purchase']));
    }

    public function update(Request $request, Device $device)
    {
        $request->validate([
            'inventory_code' => 'required|string|unique:devices,inventory_code,' . $device->id,
            'serial_number' => 'required|string|unique:devices,serial_number,' . $device->id,
        ]);

        return DB::transaction(function () use ($request, $device) {
            $oldEmployeeId = $device->employee_id;
            $newEmployeeId = $request->employee_id;

            if ($newEmployeeId === 'unassigned' || $newEmployeeId === '') {
                $newEmployeeId = null;
            }

            $data = $request->all();
            $data['employee_id'] = $newEmployeeId;

            if ($request->has('employee_id') && !$request->has('status')) {
                $data['status'] = $newEmployeeId ? 'assigned' : 'available';
            }

            if ($oldEmployeeId != $newEmployeeId) {
                if ($oldEmployeeId) {
                    $lastAssignment = Assignment::where('device_id', $device->id)
                        ->where('employee_id', $oldEmployeeId)
                        ->whereNull('returned_at')
                        ->latest()
                        ->first();

                    if ($lastAssignment) {
                        $lastAssignment->update(['returned_at' => now()]);
                    }
                }

                if ($newEmployeeId) {
                    Assignment::create([
                        'device_id' => $device->id,
                        'employee_id' => $newEmployeeId,
                        'assigned_by' => Auth::id(),
                        'assigned_at' => now(),
                    ]);
                }
            }

            $device->update($data);

            return response()->json($device);
        });
    }
}
