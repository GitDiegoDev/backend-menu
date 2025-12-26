<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // ---------------------------
    // Productos visibles (menú)
    // ---------------------------
    public function index()
    {
        $products = Product::with(['category', 'variants'])
            ->where('visible', true)
            ->orderBy('category_id')
            ->get();

        return response()->json($products);
    }

    // ---------------------------
    // Todos los productos (panel)
    // ---------------------------
    public function allProducts()
    {
        $products = Product::with(['category', 'variants'])
            ->orderBy('category_id')
            ->get();

        return response()->json($products);
    }

    // ---------------------------
    // Producto individual
    // ---------------------------
    public function show($id)
    {
        $product = Product::with(['category', 'variants'])->find($id);

        if (!$product) {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }

        return response()->json($product);
    }

    // ---------------------------
    // Crear producto
    // ---------------------------
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'visible' => 'boolean',
            'variants' => 'nullable|array',
            'variants.*.name' => 'required|string|max:255',
        ]);

        $product = Product::create([
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? '',
            'price' => $validated['price'],
            'visible' => $validated['visible'] ?? 1,
        ]);

        if (!empty($validated['variants'])) {
            foreach ($validated['variants'] as $variant) {
                ProductVariant::create([
                    'product_id' => $product->id,
                    'name' => $variant['name'],
                ]);
            }
        }

        return response()->json(
            $product->load('variants'),
            201
        );
    }

    // ---------------------------
    // Actualizar producto
    // ---------------------------
    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'visible' => 'boolean',
            'variants' => 'nullable|array',
            'variants.*.id' => 'nullable|exists:product_variants,id',
            'variants.*.name' => 'required|string|max:255',
        ]);

        // 1️⃣ Actualizar producto
        $product->update([
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? '',
            'price' => $validated['price'],
            'visible' => $validated['visible'] ?? 1,
        ]);

        // 2️⃣ Sincronizar variantes
        if (isset($validated['variants'])) {

            $incomingIds = collect($validated['variants'])
                ->pluck('id')
                ->filter()
                ->toArray();

            // Eliminar variantes quitadas
            ProductVariant::where('product_id', $product->id)
                ->whereNotIn('id', $incomingIds)
                ->delete();

            foreach ($validated['variants'] as $variant) {

                // Editar existente
                if (!empty($variant['id'])) {
                    ProductVariant::where('id', $variant['id'])
                        ->where('product_id', $product->id)
                        ->update([
                            'name' => $variant['name'],
                        ]);
                }
                // Crear nueva
                else {
                    ProductVariant::create([
                        'product_id' => $product->id,
                        'name' => $variant['name'],
                    ]);
                }
            }
        }

        return response()->json(
            $product->load('variants')
        );
    }

    // ---------------------------
    // Eliminar producto
    // ---------------------------
    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }

        // Opcional: borrar variantes en cascada si no usás FK
        ProductVariant::where('product_id', $product->id)->delete();

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Producto eliminado',
        ]);
    }
}
