<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\CategoryField;

class CategoryController extends Controller
{
    public function index()
    {
        return Category::with('fields')->get();
    }

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

    public function show(Category $category)
    {
        return $category->load('fields');
    }

    public function addField(Request $request, Category $category)
    {
        $request->validate([
            'label' => 'required|string',
            'type' => 'required|in:text,number,select,boolean',
        ]);

        $key = Str::slug($request->label, '_');

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
