<?php

namespace App\Exports;

use App\Models\Adopcion\Adopcion;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class AdopcionesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
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
        $query = Adopcion::with(['animal', 'adoptante'])
            ->whereBetween('created_at', [$this->fechaInicio, $this->fechaFin]);

        // Aplicar filtros adicionales
        if (!empty($this->filters['estado'])) {
            $query->where('estado', $this->filters['estado']);
        }

        if (!empty($this->filters['especie'])) {
            $query->whereHas('animal', function ($q) {
                $q->where('especie', $this->filters['especie']);
            });
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'ID Adopcion',
            'Fecha Solicitud',
            'Estado',
            'Animal - Codigo',
            'Animal - Nombre',
            'Animal - Especie',
            'Animal - Raza',
            'Animal - Sexo',
            'Adoptante - Nombre',
            'Adoptante - Documento',
            'Adoptante - Telefono',
            'Adoptante - Email',
            'Adoptante - Direccion',
            'Tipo Vivienda',
            'Tiene Patio',
            'Otras Mascotas',
            'Experiencia Previa',
            'Fecha Aprobacion',
            'Fecha Entrega',
            'Observaciones',
        ];
    }

    public function map($adopcion): array
    {
        $animal = $adopcion->animal;
        $adoptante = $adopcion->adoptante;

        return [
            $adopcion->id,
            $adopcion->created_at ? Carbon::parse($adopcion->created_at)->format('d/m/Y H:i') : 'N/A',
            $this->formatEstado($adopcion->estado),
            $animal?->codigo_unico ?? 'N/A',
            $animal?->nombre ?? 'Sin nombre',
            ucfirst($animal?->especie ?? 'N/A'),
            $animal?->raza ?? 'N/A',
            $this->formatSexo($animal?->sexo),
            $adoptante?->nombre_completo ?? $adoptante?->nombre ?? 'N/A',
            ($adoptante?->tipo_documento ?? '') . ' ' . ($adoptante?->numero_documento ?? 'N/A'),
            $adoptante?->telefono ?? 'N/A',
            $adoptante?->email ?? 'N/A',
            $adoptante?->direccion ?? 'N/A',
            $this->formatTipoVivienda($adopcion->tipo_vivienda),
            $adopcion->tiene_patio ? 'Si' : 'No',
            $adopcion->tiene_otras_mascotas ? 'Si' : 'No',
            $adopcion->experiencia_mascotas ? 'Si' : 'No',
            $adopcion->fecha_aprobacion ? Carbon::parse($adopcion->fecha_aprobacion)->format('d/m/Y') : 'N/A',
            $adopcion->fecha_entrega ? Carbon::parse($adopcion->fecha_entrega)->format('d/m/Y') : 'N/A',
            $adopcion->observaciones ?? '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '068460'],
                ],
            ],
        ];
    }

    private function formatEstado($estado): string
    {
        $estados = [
            'solicitada' => 'Solicitada',
            'pendiente' => 'Pendiente',
            'en_evaluacion' => 'En Evaluacion',
            'aprobada' => 'Aprobada',
            'rechazada' => 'Rechazada',
            'completada' => 'Completada',
            'revocada' => 'Revocada',
            'devuelta' => 'Devuelta',
        ];
        return $estados[$estado] ?? ucfirst($estado ?? 'N/A');
    }

    private function formatSexo($sexo): string
    {
        $sexos = [
            'macho' => 'Macho',
            'hembra' => 'Hembra',
            'desconocido' => 'Desconocido',
        ];
        return $sexos[$sexo] ?? ucfirst($sexo ?? 'N/A');
    }

    private function formatTipoVivienda($tipo): string
    {
        $tipos = [
            'casa' => 'Casa',
            'apartamento' => 'Apartamento',
            'finca' => 'Finca',
            'otro' => 'Otro',
        ];
        return $tipos[$tipo] ?? ucfirst($tipo ?? 'N/A');
    }
}
