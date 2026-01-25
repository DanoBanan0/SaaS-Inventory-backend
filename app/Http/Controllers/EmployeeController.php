<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::with('unit')->withCount('devices');

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->has('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        return $query->orderBy('name')->paginate(20);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'unit_id' => 'required|exists:units,id',
            'email' => 'nullable|email|max:255|unique:employees,email', 
            'job_title' => 'nullable|string|max:255',
            'status' => 'boolean'
        ]);

        $employee = Employee::create($request->all());

        return response()->json($employee, 201);
    }

    public function show(Employee $employee)
    {
        return $employee->load(['unit', 'devices']);
    }

    public function update(Request $request, Employee $employee)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'unit_id' => 'required|exists:units,id',
            'email' => 'nullable|email|max:255|unique:employees,email,' . $employee->id,
            'job_title' => 'nullable|string|max:255',
            'status' => 'boolean'
        ]);

        $employee->update($request->all());

        return response()->json($employee);
    }

    public function destroy(Employee $employee)
    {
        if ($employee->devices()->where('status', 'assigned')->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar: El empleado tiene equipos asignados. Recíbelos primero.'
            ], 422);
        }

        $employee->delete();

        return response()->json(null, 204);
    }
}
