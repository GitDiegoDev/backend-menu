<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category')->get();
        $categories = Category::all();

        return view('products.index', compact('products', 'categories', 'variants'));
    }

    

public function store(Request $request)
{
    $data = $request->json()->all();

    DB::transaction(function () use ($data, &$product) {

        $product = Product::create([
            'category_id' => $data['category_id'],
            'name'        => $data['name'],
            'description' => $data['description'] ?? '',
            'price'       => $data['price'],
            'visible'     => $data['visible'] ?? true
        ]);

        if (!empty($data['variants']) && is_array($data['variants'])) {
            foreach ($data['variants'] as $variant) {
                ProductVariant::create([
                    'product_id' => $product->id,
                    'name'       => $variant['name'],
                    'price'      => $variant['price']
                ]);
            }
        }
    });

    return response()->json($product, 201);
}
public function update(Request $request, Product $product)
{
    $data = $request->json()->all();

    DB::transaction(function () use ($data, $product) {

        $product->update([
            'category_id' => $data['category_id'],
            'name'        => $data['name'],
            'description' => $data['description'] ?? '',
            'price'       => $data['price'],
            'image'       => $data['image'] ?? '',
            'visible'     => $data['visible']
        ]);

        // Eliminar variantes anteriores
        $product->variants()->delete();

        // Crear variantes nuevas
        if (!empty($data['variants']) && is_array($data['variants'])) {
            foreach ($data['variants'] as $variant) {
                ProductVariant::create([
                    'product_id' => $product->id,
                    'name'       => $variant['name'],
                    'price'      => $variant['price']
                ]);
            }
        }
    });

    return response()->json($product);
}

}
