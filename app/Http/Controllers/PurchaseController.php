<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $query = Purchase::withCount('devices');

        // 1. Filtro de búsqueda general (Proveedor o Factura)
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('provider', 'like', "%{$search}%")
                    ->orWhere('invoice_number', 'like', "%{$search}%");
            });
        }

        // 2. NUEVO: Filtro por Fecha Exacta
        if ($request->has('date') && $request->date != '') {
            $query->whereDate('purchase_date', $request->date);
        }

        return $query->orderBy('purchase_date', 'desc')->paginate(20);
    }

    public function store(Request $request)
    {
        $request->validate([
            'provider' => 'required|string|max:255',
            'invoice_number' => 'required|string|max:255|unique:purchases,invoice_number',
            'purchase_date' => 'required|date',
            'total_amount' => 'required|numeric|min:0', // <--- NUEVA REGLA
        ]);

        $purchase = Purchase::create($request->all());

        return response()->json($purchase, 201);
    }

    public function show(Purchase $purchase)
    {
        return $purchase->load('devices');
    }

    public function update(Request $request, Purchase $purchase)
    {
        $request->validate([
            'provider' => 'required|string|max:255',
            'invoice_number' => 'required|string|max:255|unique:purchases,invoice_number,' . $purchase->id,
            'purchase_date' => 'required|date',
            'total_amount' => 'required|numeric|min:0', // <--- NUEVA REGLA
        ]);

        $purchase->update($request->all());

        return response()->json($purchase);
    }

    public function destroy(Purchase $purchase)
    {
        // Regla de Oro: No borrar facturas que ya "dieron a luz" dispositivos
        if ($purchase->devices()->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar: Hay ' . $purchase->devices()->count() . ' dispositivos registrados con esta factura.'
            ], 422);
        }

        $purchase->delete();

        return response()->json(null, 204);
    }
}
