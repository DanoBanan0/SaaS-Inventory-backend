<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\CategoryField;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Category::with('fields')->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:categories,name',
            'fields' => 'present|array',
            'fields.*.label' => 'required|string',
            'fields.*.key' => 'required|string',
            'fields.*.type' => 'required|in:text,number,select,boolean',
        ]);

        return DB::transaction(function () use ($request) {
            $category = Category::create(['name' => $request->name]);

            $category->fields()->createMany($request->fields);

            return response()->json($category->load('fields'), 201);
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        return $category->load('fields');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function addField(Request $request, Category $category)
    {
        $request->validate([
            'label' => 'required|string',
            'type' => 'required|in:text,number,select,boolean',
            // 'required' es opcional, default false
        ]);

        // Generar key automática (ej: "Disco Duro" -> "disco_duro")
        $key = Str::slug($request->label, '_');

        // Evitar duplicados de key en la misma categoría
        if ($category->fields()->where('key', $key)->exists()) {
            return response()->json(['message' => 'Este campo ya existe en esta categoría'], 422);
        }

        $field = $category->fields()->create([
            'label' => $request->label,
            'key' => $key,
            'type' => $request->type,
            'required' => $request->boolean('required', false)
        ]);

        return response()->json($field, 201);
    }
}
