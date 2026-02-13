<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OwenIt\Auditing\Models\Audit;
use App\Models\Employee;
use App\Models\Role;
use App\Models\Unit;
use App\Models\Device;
use App\Models\User;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $query = Audit::with('user')->latest();

        // Filtros opcionales
        if ($request->has('search') && $request->search) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('module') && $request->module) {
            $query->where('auditable_type', 'like', '%' . $request->module);
        }

        if ($request->has('event') && $request->event) {
            $query->where('event', $request->event);
        }

        // Filtro por rango de fechas con ajuste de zona horaria (UTC-6)
        if ($request->has('date_from') && $request->date_from) {
            $query->whereRaw("DATE(DATE_SUB(created_at, INTERVAL 6 HOUR)) >= ?", [$request->date_from]);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereRaw("DATE(DATE_SUB(created_at, INTERVAL 6 HOUR)) <= ?", [$request->date_to]);
        }

        $audits = $query->paginate(15);

        // Resolver nombres de relaciones y del modelo auditado
        $audits->getCollection()->transform(function ($audit) {
            $audit->old_values = $this->resolveRelationNames($audit->old_values);
            $audit->new_values = $this->resolveRelationNames($audit->new_values);
            
            // Agregar el nombre del registro auditado
            $audit->auditable_name = $this->getAuditableName($audit);
            
            return $audit;
        });

        return response()->json($audits);
    }

    /**
     * Resuelve los IDs de relaciones a nombres legibles
     */
    private function resolveRelationNames(array $values): array
    {
        $resolved = $values;

        // Resolver employee_id -> nombre del empleado
        if (isset($values['employee_id']) && $values['employee_id']) {
            $employee = Employee::find($values['employee_id']);
            $resolved['employee_id'] = $employee ? $employee->name : $values['employee_id'];
        }

        // Resolver role_id -> nombre del rol
        if (isset($values['role_id']) && $values['role_id']) {
            $role = Role::find($values['role_id']);
            $resolved['role_id'] = $role ? $role->name : $values['role_id'];
        }

        // Resolver unit_id -> nombre de la unidad
        if (isset($values['unit_id']) && $values['unit_id']) {
            $unit = Unit::find($values['unit_id']);
            $resolved['unit_id'] = $unit ? $unit->name : $values['unit_id'];
        }

        // Resolver device_id -> código de inventario del dispositivo
        if (isset($values['device_id']) && $values['device_id']) {
            $device = Device::find($values['device_id']);
            $resolved['device_id'] = $device ? $device->inventory_code : $values['device_id'];
        }

        return $resolved;
    }

    /**
     * Obtiene el nombre legible del modelo auditado
     */
    private function getAuditableName($audit): ?string
    {
        $type = class_basename($audit->auditable_type);
        $id = $audit->auditable_id;

        switch ($type) {
            case 'User':
                $model = User::find($id);
                return $model ? $model->name : null;
            
            case 'Employee':
                $model = Employee::find($id);
                return $model ? $model->name : null;
            
            case 'Device':
                $model = Device::find($id);
                return $model ? $model->inventory_code : null;
            
            case 'Role':
                $model = Role::find($id);
                return $model ? $model->name : null;
            
            case 'Unit':
                $model = Unit::find($id);
                return $model ? $model->name : null;
            
            default:
                // Intentar obtener el nombre genéricamente
                $modelClass = $audit->auditable_type;
                if (class_exists($modelClass)) {
                    $model = $modelClass::find($id);
                    return $model->name ?? $model->inventory_code ?? null;
                }
                return null;
        }
    }
}
