<?php

namespace App\Exports;

use App\Models\Veterinaria\Cirugia;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class CirugiasExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
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
        $query = Cirugia::with(['historialClinico.animal', 'veterinario'])
            ->whereBetween('fecha_realizacion', [$this->fechaInicio, $this->fechaFin]);

        // Aplicar filtros adicionales
        if (!empty($this->filters['tipo_cirugia'])) {
            $query->where('tipo_cirugia', $this->filters['tipo_cirugia']);
        }

        if (!empty($this->filters['estado'])) {
            $query->where('estado', $this->filters['estado']);
        }

        return $query->orderBy('fecha_realizacion', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Fecha Realizacion',
            'Tipo Cirugia',
            'Animal - Codigo',
            'Animal - Especie',
            'Animal - Raza',
            'Veterinario',
            'Diagnostico Preoperatorio',
            'Procedimiento',
            'Anestesia Utilizada',
            'Duracion (min)',
            'Complicaciones',
            'Estado',
            'Observaciones',
            'Creado',
        ];
    }

    public function map($cirugia): array
    {
        $animal = $cirugia->historialClinico?->animal;

        return [
            $cirugia->id,
            $cirugia->fecha_realizacion ? Carbon::parse($cirugia->fecha_realizacion)->format('d/m/Y H:i') : 'N/A',
            $this->formatTipoCirugia($cirugia->tipo_cirugia),
            $animal?->codigo_unico ?? 'N/A',
            ucfirst($animal?->especie ?? 'N/A'),
            $animal?->raza ?? 'N/A',
            $cirugia->veterinario?->nombre_completo ?? 'N/A',
            $cirugia->diagnostico_preoperatorio ?? 'N/A',
            $cirugia->procedimiento_realizado ?? 'N/A',
            $cirugia->anestesia_utilizada ?? 'N/A',
            $cirugia->duracion_minutos ?? 'N/A',
            $cirugia->complicaciones ?? 'Ninguna',
            $this->formatEstado($cirugia->estado),
            $cirugia->observaciones ?? '',
            $cirugia->created_at ? Carbon::parse($cirugia->created_at)->format('d/m/Y H:i') : 'N/A',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '3366CC'],
                ],
            ],
        ];
    }

    private function formatTipoCirugia($tipo): string
    {
        $tipos = [
            'esterilizacion' => 'Esterilizacion',
            'castracion' => 'Castracion',
            'ortopedica' => 'Ortopedica',
            'tumor' => 'Extirpacion de Tumor',
            'emergencia' => 'Emergencia',
            'otra' => 'Otra',
        ];
        return $tipos[$tipo] ?? ucfirst($tipo ?? 'N/A');
    }

    private function formatEstado($estado): string
    {
        $estados = [
            'programada' => 'Programada',
            'en_curso' => 'En Curso',
            'completada' => 'Completada',
            'cancelada' => 'Cancelada',
            'complicaciones' => 'Con Complicaciones',
        ];
        return $estados[$estado] ?? ucfirst($estado ?? 'N/A');
    }
}
