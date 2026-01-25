<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role; // <--- Importante: Importar el modelo Role
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{

    public function index(Request $request)
    {
        $query = User::with('role');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return response()->json($query->get());
    }

    // Función auxiliar para obtener el ID de Developer dinámicamente
    private function getDeveloperRoleId()
    {
        $role = Role::where('name', 'like', '%developer%')
            ->orWhere('name', 'like', '%dev%')
            ->first();

        return $role ? $role->id : null;
    }

    public function store(Request $request)
    {
        $devRoleId = $this->getDeveloperRoleId();
        $currentUserRole = Auth::user()->role;

        if ($devRoleId && $request->role_id == $devRoleId) {

            if (!$currentUserRole || !str_contains(strtolower($currentUserRole->name), 'developer')) {
                return response()->json([
                    'message' => 'Acceso Denegado: Solo un Developer puede crear usuarios con este rol.'
                ], 403);
            }
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role_id' => 'required|exists:roles,id',
            'is_active' => 'boolean'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json($user, 201);
    }

    public function update(Request $request, User $user)
    {
        $devRoleId = $this->getDeveloperRoleId();
        $currentUser = Auth::user();

        $userRoleName = strtolower($user->role->name ?? '');
        $currentUserRoleName = strtolower($currentUser->role->name ?? '');
        $isCurrentDev = str_contains($currentUserRoleName, 'developer');
        $isTargetDev = str_contains($userRoleName, 'developer');

        if ($isTargetDev && !$isCurrentDev) {
            return response()->json(['message' => 'Acceso Denegado: No tienes permisos para modificar a un Developer.'], 403);
        }

        if ($devRoleId && $request->role_id == $devRoleId && !$isCurrentDev) {
            return response()->json(['message' => 'Acceso Denegado: Solo un Developer puede asignar este rol.'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'role_id' => 'required|exists:roles,id',
            'is_active' => 'boolean',
            'password' => 'nullable|string|min:8',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role_id' => $request->role_id,
            'is_active' => $request->is_active,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);
        return response()->json($user);
    }

    public function destroy(User $user)
    {
        $currentUserRoleName = strtolower(Auth::user()->role->name ?? '');
        $targetRoleName = strtolower($user->role->name ?? '');

        if (str_contains($targetRoleName, 'developer') && !str_contains($currentUserRoleName, 'developer')) {
            return response()->json(['message' => 'Acceso Denegado: Solo un Developer puede eliminar a otro Developer.'], 403);
        }

        if (Auth::id() === $user->id) {
            return response()->json(['message' => 'No puedes eliminar tu propia cuenta.'], 403);
        }

        $user->delete();
        return response()->json(null, 204);
    }
}
