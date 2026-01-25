<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\Assignment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use PhpParser\Node\Expr\Assign;

class DeviceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Device::with(['category', 'employee.unit', 'purchase']);

        //Fltrar por statdo (status)
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        //Filtrar 'Sin Asignar'
        if ($request->boolean('unassigned')) {
            $query->whereNull('employee_id');
        }

        //Filtrar por unidad
        if ($request->has('unit')) {
            $query->whereHas('employee.unit', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->unit . '%');
            });
        }

        //Filtrar por categoria
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('inventory_code', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhereHas('employee', fn($e) => $e->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->has('specs')) {
            foreach ($request->input('specs') as $key => $value) {
                $query->where("specs->{$key}", $value);
            }
        }

        return $query->latest()->paginate(20);
    }

    /**
     * Store a newly created resource in storage.
     */
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

    /**
     * Display the specified resource.
     */
    public function show(Device $device)
    {
        return $device->load(['category', 'employee', 'assignments', 'purchase']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Device $device)
    {
        // Validaciones básicas (puedes agregar más si necesitas)
        $request->validate([
            'inventory_code' => 'required|string|unique:devices,inventory_code,' . $device->id,
            'serial_number' => 'required|string',
        ]);

        return DB::transaction(function () use ($request, $device) {
            $data = $request->all();

            // 1. Manejo inteligente del Status
            // Si el usuario cambia el empleado pero NO el status, asumimos "assigned" o "available" automáticamente.
            // Si el usuario MANDA el status explícitamente, respetamos su decisión (prioridad).
            if ($request->has('employee_id') && !$request->has('status')) {
                $data['status'] = $request->employee_id ? 'assigned' : 'available';
            }

            // 2. Actualizar Specs (JSON)
            // Si vienen specs nuevas, las actualizamos.
            if ($request->has('specs')) {
                $data['specs'] = $request->specs; // Laravel casteará esto a JSON automáticamente por el modelo
            }

            $device->update($data);

            return response()->json($device);
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
