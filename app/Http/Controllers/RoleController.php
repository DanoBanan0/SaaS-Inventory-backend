<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        // Retornamos roles con el conteo de usuarios asignados
        return response()->json(Role::withCount('users')->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name|max:255',
        ]);

        $role = Role::create($request->all());

        return response()->json($role, 201);
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
        ]);

        $role->update($request->all());

        return response()->json($role);
    }

    public function destroy(Role $role)
    {
        // Regla de Oro: No borrar roles que tienen usuarios activos
        if ($role->users()->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar: Hay ' . $role->users()->count() . ' usuarios con este rol.'
            ], 422);
        }

        $role->delete();

        return response()->json(null, 204);
    }
}
