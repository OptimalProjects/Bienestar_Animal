<?php

namespace App\Exports;

use App\Models\Veterinaria\Vacuna;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class VacunasExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $fechaInicio;
    protected $fechaFin;
    protected $filters;

    public function __construct($fechaInicio = null, $fechaFin = null, $filters = [])
    {
        $this->fechaInicio = $fechaInicio ? Carbon::parse($fechaInicio)->startOfDay() : now()->subMonth()->startOfDay();
        $this->fechaFin = $fechaFin ? Carbon::parse($fechaFin)->endOfDay() : now()->endOfDay();
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Vacuna::with(['historialClinico.animal', 'tipoVacuna', 'veterinario'])
            ->whereBetween('fecha_aplicacion', [$this->fechaInicio, $this->fechaFin]);

        // Aplicar filtros adicionales
        if (!empty($this->filters['tipo_vacuna_id'])) {
            $query->where('tipo_vacuna_id', $this->filters['tipo_vacuna_id']);
        }

        if (!empty($this->filters['estado'])) {
            $query->where('estado', $this->filters['estado']);
        }

        return $query->orderBy('fecha_aplicacion', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Fecha Aplicacion',
            'Nombre Vacuna',
            'Tipo Vacuna',
            'Animal - Codigo',
            'Animal - Especie',
            'Animal - Nombre',
            'Veterinario',
            'Lote',
            'Fabricante',
            'Dosis',
            'Via Administracion',
            'Sitio Aplicacion',
            'Numero de Dosis',
            'Fecha Proxima Dosis',
            'Fecha Vencimiento',
            'Estado',
            'Reacciones Adversas',
            'Observaciones',
        ];
    }

    public function map($vacuna): array
    {
        $animal = $vacuna->historialClinico?->animal;

        return [
            $vacuna->id,
            $vacuna->fecha_aplicacion ? Carbon::parse($vacuna->fecha_aplicacion)->format('d/m/Y') : 'N/A',
            $vacuna->nombre_vacuna ?? $vacuna->tipoVacuna?->nombre ?? 'N/A',
            $vacuna->tipoVacuna?->nombre ?? 'N/A',
            $animal?->codigo_unico ?? 'N/A',
            ucfirst($animal?->especie ?? 'N/A'),
            $animal?->nombre ?? 'Sin nombre',
            $vacuna->veterinario?->nombre_completo ?? 'N/A',
            $vacuna->lote_vacuna ?? 'N/A',
            $vacuna->fabricante ?? 'N/A',
            $vacuna->dosis ?? 'N/A',
            $this->formatViaAdministracion($vacuna->via_administracion),
            $vacuna->sitio_aplicacion ?? 'N/A',
            $vacuna->numero_dosis ?? 'N/A',
            $vacuna->fecha_proxima_dosis ? Carbon::parse($vacuna->fecha_proxima_dosis)->format('d/m/Y') : 'N/A',
            $vacuna->fecha_vencimiento ? Carbon::parse($vacuna->fecha_vencimiento)->format('d/m/Y') : 'N/A',
            $this->formatEstado($vacuna->estado),
            $vacuna->reacciones_adversas ?? 'Ninguna',
            $vacuna->observaciones ?? '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '004884'],
                ],
            ],
        ];
    }

    private function formatEstado($estado): string
    {
        $estados = [
            'aplicada' => 'Aplicada',
            'programada' => 'Programada',
            'vencida' => 'Vencida',
            'cancelada' => 'Cancelada',
        ];
        return $estados[$estado] ?? ucfirst($estado ?? 'N/A');
    }

    private function formatViaAdministracion($via): string
    {
        $vias = [
            'subcutanea' => 'Subcutanea',
            'intramuscular' => 'Intramuscular',
            'oral' => 'Oral',
            'intranasal' => 'Intranasal',
            'intravenosa' => 'Intravenosa',
        ];
        return $vias[$via] ?? ucfirst($via ?? 'N/A');
    }
}
