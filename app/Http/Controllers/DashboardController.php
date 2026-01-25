<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\Employee;
use App\Models\Unit;

class DashboardController extends Controller
{
    public function index()
    {
        return response()->json([
            'total_devices' => Device::count(),
            'available_devices' => Device::where('status', 'available')->count(),
            'assigned_devices' => Device::where('status', 'assigned')->count(),
            'maintenance_devices' => Device::where('status', 'maintenance')->count(),
            'total_employees' => Employee::count(),
            'total_units' => Unit::count(),
            // Últimos 5 movimientos para mostrar un resumen rápido
            'recent_devices' => Device::with('category', 'employee')->latest()->take(5)->get()
        ]);
    }
}
