<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class AuditController extends Controller
{
    public function index()
    {
        return Activity::with(['causer', 'subject'])->latest()->paginate(20);
    }
}
