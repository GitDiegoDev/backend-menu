<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'delivery_type' => 'required|string',
            'address' => 'nullable|string',
            'total' => 'required|numeric',
        ]);

        $order = Order::create([
            'delivery_type' => $data['delivery_type'],
            'address' => $data['address'] ?? null,
            'total' => $data['total'],
        ]);

        return response()->json([
            'success' => true,
            'order_id' => $order->id
        ], 201);
    }
}
