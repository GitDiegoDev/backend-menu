<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReclamarRecompensaRequest;
use App\Http\Requests\StorePedidoRequest;
use App\Models\Cliente;
use App\Models\PedidoFidelizacion;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class FidelizacionController extends Controller
{
    /**
     * Identificar cliente por teléfono y consultar sellos acumulados.
     *
     * @param string $telefono
     * @return JsonResponse
     */
    public function show(string $telefono): JsonResponse
    {
        $cliente = Cliente::where('telefono', $telefono)->first();

        if (!$cliente) {
            return response()->json([
                'success' => false,
                'message' => 'Cliente no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'nombre' => $cliente->nombre,
                'telefono' => $cliente->telefono,
                'sellos_actuales' => $cliente->sellos_actuales,
                'premios_disponibles' => $cliente->premios_disponibles,
                'premios_canjeados' => $cliente->premios_canjeados,
                'faltan' => max(0, 10 - $cliente->sellos_actuales),
            ]
        ]);
    }

    /**
     * Registrar compra y acumular sellos.
     *
     * @param StorePedidoRequest $request
     * @return JsonResponse
     */
    public function registrarPedido(StorePedidoRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            // Verificar si el pedido ya fue procesado
            $pedidoExistente = PedidoFidelizacion::where('pedido_uuid', $request->pedido_uuid)->first();
            if ($pedidoExistente) {
                return response()->json([
                    'success' => false,
                    'message' => 'El pedido ya ha sido procesado'
                ], 400);
            }

            // Registrar el pedido para evitar duplicados
            PedidoFidelizacion::create([
                'pedido_uuid' => $request->pedido_uuid,
                'telefono' => $request->telefono,
            ]);

            $cliente = Cliente::firstOrCreate(
                ['telefono' => $request->telefono],
                ['nombre' => $request->nombre]
            );

            // Según requerimientos, si no existe se crea con 1 sello. Si existe se incrementa en 1.
            $cliente->sellos_actuales += 1;

            if ($cliente->sellos_actuales >= 10) {
                $cliente->sellos_actuales = 0;
                $cliente->premios_disponibles += 1;
            }

            $cliente->save();

            return response()->json([
                'success' => true,
                'data' => [
                    'nombre' => $cliente->nombre,
                    'telefono' => $cliente->telefono,
                    'sellos_actuales' => $cliente->sellos_actuales,
                    'premios_disponibles' => $cliente->premios_disponibles,
                    'premios_canjeados' => $cliente->premios_canjeados,
                    'faltan' => max(0, 10 - $cliente->sellos_actuales),
                ]
            ]);
        });
    }

    /**
     * Reclamar recompensa.
     *
     * @param ReclamarRecompensaRequest $request
     * @return JsonResponse
     */
    public function reclamarRecompensa(ReclamarRecompensaRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            $cliente = Cliente::where('telefono', $request->telefono)->first();

            if (!$cliente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cliente no encontrado'
                ], 404);
            }

            if ($cliente->premios_disponibles <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay recompensas disponibles para reclamar'
                ], 400);
            }

            $cliente->premios_disponibles -= 1;
            $cliente->premios_canjeados += 1;
            $cliente->save();

            return response()->json([
                'success' => true,
                'data' => [
                    'nombre' => $cliente->nombre,
                    'telefono' => $cliente->telefono,
                    'sellos_actuales' => $cliente->sellos_actuales,
                    'premios_disponibles' => $cliente->premios_disponibles,
                    'premios_canjeados' => $cliente->premios_canjeados,
                    'faltan' => max(0, 10 - $cliente->sellos_actuales),
                ]
            ]);
        });
    }
}
