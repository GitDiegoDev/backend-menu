<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Promo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PromoController extends Controller
{
    public function index(Request $request)
    {
        // Si querés todas las promos (incluir inactivas) comentar la siguiente línea
        // $promos = Promo::orderBy('created_at', 'desc')->get();

        // Por defecto: traer todas para el panel; si querés filtrar solo activas usá ?active=1
        if ($request->has('active')) {
            $promos = Promo::where('active', (bool) $request->query('active'))->orderBy('created_at', 'desc')->get();
        } else {
            $promos = Promo::orderBy('created_at', 'desc')->get();
        }

        return response()->json($promos);
    }

    public function show(Promo $promo)
    {
        return response()->json($promo);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'day_of_week' => 'nullable|string',
            'description' => 'nullable|string',
            'active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $promo = Promo::create([
            'title' => $request->title,
            'description' => $request->description ?? '',
            'price' => $request->price,
            'active' => $request->has('active') ? (bool)$request->active : true,
            'day_of_week' => $request->day_of_week ?? 'todos',
        ]);

        return response()->json($promo, 201);
    }

    public function update(Request $request, Promo $promo)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'day_of_week' => 'nullable|string',
            'description' => 'nullable|string',
            'active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $promo->update([
            'title' => $request->title,
            'description' => $request->description ?? $promo->description,
            'price' => $request->price,
            'active' => $request->has('active') ? (bool)$request->active : $promo->active,
            'day_of_week' => $request->day_of_week ?? $promo->day_of_week,
        ]);

        return response()->json($promo);
    }

    public function destroy(Promo $promo)
    {
        $promo->delete();
        return response()->json(['message' => 'Promoción eliminada']);
    }
}
