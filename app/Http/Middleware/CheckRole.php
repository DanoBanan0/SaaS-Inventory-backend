<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    protected array $readOnlyRoles = ['auditoría', 'auditoria', 'auditor'];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->role) {
            return response()->json(['message' => 'Acceso denegado.'], 403);
        }

        $roleName = strtolower($user->role->name);

        foreach ($this->readOnlyRoles as $role) {
            if (str_contains($roleName, $role)) {
                return response()->json([
                    'message' => 'Tu rol de auditoría solo permite consultar información.'
                ], 403);
            }
        }

        return $next($request);
    }
}
