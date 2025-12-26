<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // Obtener categorías (menú + panel)
    public function index()
    {
        return response()->json(
            Category::orderBy('name')->get()
        );
    }

    // Crear nueva categoría (panel)
     public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100'
        ]);

        return Category::create([
            'name' => $request->name
        ]);
    }
    // Actualizar categoría (panel)
    public function update(Request $request, Category $category)
{
    $request->validate([
        'name' => 'required|string|max:100'
    ]);

    $category->name = $request->name;
    $category->save();

    return response()->json($category);
}

    public function destroy(Category $category)
    {
        $category->delete();
        return response()->json(['message' => 'Categoría eliminada']);
    }
    
}
