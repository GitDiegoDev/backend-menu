<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function stats()
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        //grafico
        $salesByHour = DB::table('orders')
            ->select(
             DB::raw('HOUR(created_at) as hour'),
             DB::raw('SUM(total) as total')
         )
        ->whereDate('created_at', Carbon::today())
        ->groupBy('hour')
        ->orderBy('hour')
        ->get();

        // ================= VENTAS HOY =================
        
        $salesToday = DB::table('orders')
            ->whereDate('created_at', $today)
            ->sum('total');

        $salesYesterday = DB::table('orders')
            ->whereDate('created_at', $yesterday)
            ->sum('total');

        $salesTrend = $salesYesterday > 0
            ? round((($salesToday - $salesYesterday) / $salesYesterday) * 100)
            : 0;

            // ================= PRODUCTOS TOP =================
            $topProducts = DB::table('order_items')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->select(
              'products.name',
             DB::raw('SUM(order_items.quantity) as total')
            )
            ->groupBy('products.name')
             ->orderByDesc('total')
             ->limit(5)
             ->get();

                // ================= VENTAS SEMANALES =================
                $weeklySales = DB::table('orders')
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('SUM(total) as total')
                )
                ->where('created_at', '>=', Carbon::now()->subDays(6))
                ->groupBy('date')
                ->orderBy('date')
                ->get();

             // ================= PEDIDOS =================
        
        $ordersToday = DB::table('orders')
            ->whereDate('created_at', $today)
            ->count();

        $ordersYesterday = DB::table('orders')
            ->whereDate('created_at', $yesterday)
            ->count();

        $ordersTrend = $ordersYesterday > 0
            ? round((($ordersToday - $ordersYesterday) / $ordersYesterday) * 100)
            : 0;

        // ================= HORA PICO =================
        $peakHour = DB::table('orders')
            ->select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('COUNT(*) as total')
            )
            ->whereDate('created_at', $today)
            ->groupBy('hour')
            ->orderByDesc('total')
            ->first();

        // ================= PRODUCTO TOP =================
        $topProduct = DB::table('order_items')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->select(
                'products.name',
                DB::raw('SUM(order_items.quantity) as total')
            )
            ->groupBy('products.name')
            ->orderByDesc('total')
            ->first();

        return response()->json([
            'salesToday'   => $salesToday,
            'salesTrend'   => $salesTrend,
            'ordersToday'  => $ordersToday,
            'ordersTrend'  => $ordersTrend,
            'peakHour'     => $peakHour,
            'topProduct'   => $topProduct,
            'salesByHour'  => $salesByHour,
            'topProducts'  => $topProducts,
            'weeklySales'  => $weeklySales
        ]);
    }
}
