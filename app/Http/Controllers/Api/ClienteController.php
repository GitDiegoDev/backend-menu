<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClienteController extends Controller
{
    /**
     * Listar todos los clientes ordenados por nombre.
     */
    public function index(): JsonResponse
    {
        $clientes = Cliente::orderBy('nombre')->get();
        return response()->json($clientes);
    }

    /**
     * Obtener un cliente por ID.
     */
    public function show($id): JsonResponse
    {
        $cliente = Cliente::find($id);

        if (!$cliente) {
            return response()->json([
                'success' => false,
                'message' => 'Cliente no encontrado'
            ], 404);
        }

        return response()->json($cliente);
    }

    /**
     * Actualizar sellos manualmente.
     */
    public function updateSellos(Request $request, $id): JsonResponse
    {
        $cliente = Cliente::find($id);

        if (!$cliente) {
            return response()->json([
                'success' => false,
                'message' => 'Cliente no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'sellos_actuales' => 'required|integer|min:0|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'La cantidad de sellos debe estar entre 0 y 10'
            ], 400);
        }

        $cliente->sellos_actuales = $request->sellos_actuales;
        $cliente->save();

        return response()->json([
            'success' => true,
            'cliente' => [
                'id' => $cliente->id,
                'nombre' => $cliente->nombre,
                'sellos_actuales' => (int) $cliente->sellos_actuales,
            ]
        ]);
    }
}
