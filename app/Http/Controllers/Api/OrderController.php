<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function store(Request $request)
{
    $data = $request->validate([
        'items' => 'required|array',
        'total' => 'required|numeric',
        'delivery_type' => 'required|string',
        'address' => 'nullable|string',
    ]);

    DB::beginTransaction();

    try {
        // 1. Crear el pedido
        $order = Order::create([
            'items' => json_encode($data['items']),
            'total' => $data['total'],
            'delivery_type' => $data['delivery_type'],
            'address' => $data['address'] ?? null,
        ]);

        // 2. Crear los items
        foreach ($data['items'] as $item) {

    DB::table('order_items')->insert([
        'order_id'     => $order->id,
        'product_id'   => $item['product_id'] ?? null,
        'product_name' => $item['name'], // CAMBIO CLAVE
        'price'        => $item['price'],
        'quantity'     => $item['quantity'] ?? 1,
        'subtotal'     => ($item['price'] * ($item['quantity'] ?? 1)),
        'created_at'   => now(),
        'updated_at'   => now(),
    ]);
}

        DB::commit();

        return response()->json([
            'success' => true,
            'order_id' => $order->id
        ]);

    } catch (\Throwable $e) {
        DB::rollBack();

        \Log::error('ERROR GUARDANDO ORDER ITEMS', [
            'error' => $e->getMessage(),
            'items' => $data['items']
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Error al guardar el pedido'
        ], 500);
    }

}
}
