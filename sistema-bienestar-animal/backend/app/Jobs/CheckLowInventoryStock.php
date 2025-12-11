<?php

namespace App\Jobs;

use App\Models\Admin\Insumo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckLowInventoryStock implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Verificando niveles de inventario...');

        // Obtener insumos con stock bajo (por debajo del minimo)
        $insumosStockBajo = Insumo::whereColumn('stock_actual', '<', 'stock_minimo')
            ->where('activo', true)
            ->get();

        foreach ($insumosStockBajo as $insumo) {
            Log::warning('Alerta: Stock bajo', [
                'insumo_id' => $insumo->id,
                'nombre' => $insumo->nombre,
                'stock_actual' => $insumo->stock_actual,
                'stock_minimo' => $insumo->stock_minimo,
                'diferencia' => $insumo->stock_minimo - $insumo->stock_actual,
            ]);
        }

        // Obtener insumos agotados
        $insumosAgotados = Insumo::where('stock_actual', '<=', 0)
            ->where('activo', true)
            ->get();

        foreach ($insumosAgotados as $insumo) {
            Log::error('Alerta CRITICA: Insumo agotado', [
                'insumo_id' => $insumo->id,
                'nombre' => $insumo->nombre,
            ]);
        }

        // Obtener insumos proximos a vencer (medicamentos, vacunas)
        $insumosPorVencer = Insumo::where('fecha_vencimiento', '<=', now()->addDays(30))
            ->where('fecha_vencimiento', '>', now())
            ->where('activo', true)
            ->get();

        foreach ($insumosPorVencer as $insumo) {
            $diasParaVencer = now()->diffInDays($insumo->fecha_vencimiento, false);

            Log::warning('Alerta: Insumo proximo a vencer', [
                'insumo_id' => $insumo->id,
                'nombre' => $insumo->nombre,
                'fecha_vencimiento' => $insumo->fecha_vencimiento,
                'dias_para_vencer' => $diasParaVencer,
            ]);
        }

        Log::info('Verificacion de inventario completada', [
            'stock_bajo' => $insumosStockBajo->count(),
            'agotados' => $insumosAgotados->count(),
            'por_vencer' => $insumosPorVencer->count(),
        ]);
    }
}
