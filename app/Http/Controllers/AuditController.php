<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OwenIt\Auditing\Models\Audit;

class AuditController extends Controller
{
    public function index()
    {
        // CAMBIO: Usamos paginate() en lugar de take()->get()
        return response()->json(
            Audit::with('user')
                ->latest()
                ->paginate(15)
        );
    }
}
