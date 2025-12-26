<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        // validar
        $data = $request->validate([
            'items' => 'required|array',
            'delivery_type' => 'required|string',
            'address' => 'nullable|string'
        ]);

        // guardar
        $order = Order::create([
            'items' => json_encode($data['items']),
            'delivery_type' => $data['delivery_type'],
            'address' => $data['address'] ?? null
        ]);

        return response()->json([
            'success' => true,
            'order_id' => $order->id
        ], 201);
    }
}
