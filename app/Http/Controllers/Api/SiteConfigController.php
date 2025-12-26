<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SiteConfig;
use Illuminate\Support\Facades\Cache;

class SiteConfigController extends Controller
{
    // GET: /api/site-config (público o privado, según necesites)
    public function index()
    {
        try {
            // Primero intenta obtener de la caché
            $config = Cache::remember('site_config', 3600, function () {
                return SiteConfig::first();
            });

            // Si no hay configuración en DB, devolver valores por defecto
            if (!$config) {
                return response()->json([
                    'open_time' => '08:00',
                    'close_time' => '22:00',
                    'open_days' => [1, 2, 3, 4, 5, 6], // Lunes a Sábado
                    'closed_dates' => [],
                    'message' => 'Usando configuración por defecto'
                ]);
            }

            return response()->json($config);

        } catch (\Exception $e) {
            \Log::error('Error en SiteConfigController@index: ' . $e->getMessage());
            
            // En caso de error, devolver valores por defecto
            return response()->json([
                'open_time' => '08:00',
                'close_time' => '22:00',
                'open_days' => [1, 2, 3, 4, 5, 6],
                'closed_dates' => [],
                'error' => 'Error cargando configuración, usando valores por defecto'
            ], 200); // 200 para que el frontend no falle
        }
    }

    // POST: /api/site-config (protegido)
    public function store(Request $request)
    {
        // Validación mejorada
        $validated = $request->validate([
            'open_time' => 'nullable|date_format:H:i',
            'close_time' => 'nullable|date_format:H:i',
            'open_days' => 'nullable|array',
            'open_days.*' => 'integer|min:0|max:6', // 0=Domingo, 6=Sábado
            'closed_dates' => 'nullable|array',
            'closed_dates.*' => 'date_format:Y-m-d',
        ]);

        try {
            // Buscar o crear configuración
            $config = SiteConfig::first();
            
            if (!$config) {
                $config = SiteConfig::create($validated);
            } else {
                $config->update($validated);
            }

            // Actualizar caché
            Cache::put('site_config', $config, 3600);

            return response()->json([
                'success' => true,
                'message' => 'Configuración guardada correctamente',
                'data' => $config
            ]);

        } catch (\Exception $e) {
            \Log::error('Error en SiteConfigController@store: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar la configuración',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}