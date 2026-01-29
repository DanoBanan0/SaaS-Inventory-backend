<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OwenIt\Auditing\Models\Audit;

class AuditController extends Controller
{
    public function index()
    {
        return response()->json(
            Audit::with('user') 
                ->latest()
                ->take(15)
                ->get()
        );
    }
}