<?php

namespace App\Exports;

use App\Models\Denuncia\Denuncia;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class DenunciasExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
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
        $query = Denuncia::with(['denunciante', 'animal', 'funcionarioAsignado'])
            ->whereBetween('created_at', [$this->fechaInicio, $this->fechaFin]);

        // Aplicar filtros adicionales
        if (!empty($this->filters['tipo'])) {
            $query->where('tipo_maltrato', $this->filters['tipo']);
        }

        if (!empty($this->filters['estado'])) {
            $query->where('estado', $this->filters['estado']);
        }

        if (!empty($this->filters['prioridad'])) {
            $query->where('prioridad', $this->filters['prioridad']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Numero Radicado',
            'Fecha Denuncia',
            'Tipo Maltrato',
            'Prioridad',
            'Estado',
            'Denunciante',
            'Telefono Denunciante',
            'Es Anonima',
            'Direccion Hechos',
            'Barrio',
            'Comuna',
            'Descripcion Hechos',
            'Animal Identificado',
            'Especie Animal',
            'Cantidad Animales',
            'Funcionario Asignado',
            'Fecha Asignacion',
            'Fecha Cierre',
            'Resolucion',
            'Observaciones',
        ];
    }

    public function map($denuncia): array
    {
        $denunciante = $denuncia->denunciante;
        $animal = $denuncia->animal;
        $funcionario = $denuncia->funcionarioAsignado;

        return [
            $denuncia->numero_radicado ?? $denuncia->id,
            $denuncia->created_at ? Carbon::parse($denuncia->created_at)->format('d/m/Y H:i') : 'N/A',
            $this->formatTipoMaltrato($denuncia->tipo_maltrato),
            $this->formatPrioridad($denuncia->prioridad),
            $this->formatEstado($denuncia->estado),
            $denuncia->es_anonima ? 'ANONIMO' : ($denunciante?->nombre_completo ?? 'N/A'),
            $denuncia->es_anonima ? 'N/A' : ($denunciante?->telefono ?? 'N/A'),
            $denuncia->es_anonima ? 'Si' : 'No',
            $denuncia->direccion_hechos ?? 'N/A',
            $denuncia->barrio ?? 'N/A',
            $denuncia->comuna ?? 'N/A',
            $this->truncateText($denuncia->descripcion_hechos, 200),
            $animal?->codigo_unico ?? 'No identificado',
            ucfirst($denuncia->especie_animal ?? $animal?->especie ?? 'N/A'),
            $denuncia->cantidad_animales ?? 1,
            $funcionario?->nombre_completo ?? $funcionario?->name ?? 'Sin asignar',
            $denuncia->fecha_asignacion ? Carbon::parse($denuncia->fecha_asignacion)->format('d/m/Y') : 'N/A',
            $denuncia->fecha_cierre ? Carbon::parse($denuncia->fecha_cierre)->format('d/m/Y') : 'N/A',
            $denuncia->resolucion ?? 'N/A',
            $denuncia->observaciones ?? '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'A80521'],
                ],
            ],
        ];
    }

    private function formatTipoMaltrato($tipo): string
    {
        $tipos = [
            'abandono' => 'Abandono',
            'maltrato_fisico' => 'Maltrato Fisico',
            'negligencia' => 'Negligencia',
            'hacinamiento' => 'Hacinamiento',
            'peleas' => 'Peleas de Animales',
            'venta_ilegal' => 'Venta Ilegal',
            'tenencia_ilegal' => 'Tenencia Ilegal',
            'otro' => 'Otro',
        ];
        return $tipos[$tipo] ?? ucfirst(str_replace('_', ' ', $tipo ?? 'N/A'));
    }

    private function formatPrioridad($prioridad): string
    {
        $prioridades = [
            'baja' => 'Baja',
            'media' => 'Media',
            'alta' => 'Alta',
            'urgente' => 'URGENTE',
            'critica' => 'CRITICA',
        ];
        return $prioridades[$prioridad] ?? ucfirst($prioridad ?? 'N/A');
    }

    private function formatEstado($estado): string
    {
        $estados = [
            'recibida' => 'Recibida',
            'pendiente' => 'Pendiente',
            'en_proceso' => 'En Proceso',
            'en_investigacion' => 'En Investigacion',
            'verificada' => 'Verificada',
            'resuelta' => 'Resuelta',
            'cerrada' => 'Cerrada',
            'archivada' => 'Archivada',
            'remitida' => 'Remitida a Autoridad',
        ];
        return $estados[$estado] ?? ucfirst(str_replace('_', ' ', $estado ?? 'N/A'));
    }

    private function truncateText($text, $length = 100): string
    {
        if (!$text) return '';
        if (strlen($text) <= $length) return $text;
        return substr($text, 0, $length) . '...';
    }
}
