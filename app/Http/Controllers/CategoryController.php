<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        return Category::with('products')->get();
    }

    public function store(Request $request)
    {
        $category = Category::create([
            'name' => $request->name
        ]);

        return $category;
    }

    public function update(Request $request, Category $category)
    {
        $category->update([
            'name' => $request->name
        ]);

        return $category;
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return ['message' => 'Categoria eliminada'];
    }
}
