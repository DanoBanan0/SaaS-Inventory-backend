<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        $query = Unit::withCount('employees');

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        return $query->orderBy('name')->paginate(20);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:units,name|max:255',
        ]);

        $unit = Unit::create($request->all());

        return response()->json($unit, 201);
    }

    public function show(Unit $unit)
    {
        return $unit->load('employees');
    }

    public function update(Request $request, Unit $unit)
    {
        $request->validate([
            'name' => 'required|string|unique:units,name,' . $unit->id . '|max:255',
        ]);

        $unit->update($request->all());

        return response()->json($unit);
    }

    public function destroy(Unit $unit)
    {
        if ($unit->employees()->exists()) {
            return response()->json(['message' => 'No se puede eliminar una unidad con empleados activos.'], 422);
        }

        $unit->delete();

        return response()->json(null, 204);
    }
}
