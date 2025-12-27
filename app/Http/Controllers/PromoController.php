<?php

namespace App\Http\Controllers;

use App\Models\Promo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PromoController extends Controller
{
    public function index()
    {
        try {
            $promos = Promo::orderBy('created_at', 'desc')->get();
            return response()->json([
                'success' => true,
                'data' => $promos
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar promociones'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        // Validación básica
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Error de validación'
            ], 422);
        }

        try {
            $promo = Promo::create([
                'title'       => $request->title,
                'description' => $request->description,
                'price'       => $request->price,
                'active'      => $request->active ?? true,
                'day_of_week' => $request->day_of_week ?? 'todos',
            ]);

            return response()->json([
                'success' => true,
                'data' => $promo,
                'message' => 'Promoción creada exitosamente'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear promoción'
            ], 500);
        }
    }

    public function update(Request $request, Promo $promo)
    {
        // Validación básica
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Error de validación'
            ], 422);
        }

        try {
            $promo->update([
                'title'       => $request->title,
                'description' => $request->description,
                'price'       => $request->price,
                'active'      => $request->active ?? true,
                'day_of_week' => $request->day_of_week ?? 'todos',
            ]);

            return response()->json([
                'success' => true,
                'data' => $promo,
                'message' => 'Promoción actualizada exitosamente'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar promoción'
            ], 500);
        }
    }

    public function destroy(Promo $promo)
    {
        try {
            $promo->delete();
            return response()->json([
                'success' => true,
                'message' => 'Promoción eliminada exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar promoción'
            ], 500);
        }
    }

    // Método para obtener una promoción específica (si no lo tienes)
    public function show(Promo $promo)
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $promo
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener promoción'
            ], 500);
        }
    }
}