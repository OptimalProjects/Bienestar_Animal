<?php

namespace App\Exports;

use App\Models\Animal\Animal;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class AnimalesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
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
        $query = Animal::with(['historialClinico'])
            ->whereBetween('created_at', [$this->fechaInicio, $this->fechaFin]);

        // Aplicar filtros adicionales (maneja sinónimos: canino/perro, felino/gato)
        if (!empty($this->filters['especie'])) {
            $especie = strtolower($this->filters['especie']);
            $especieSinonimos = [
                'canino' => ['canino', 'perro'],
                'perro' => ['canino', 'perro'],
                'felino' => ['felino', 'gato'],
                'gato' => ['felino', 'gato'],
                'equino' => ['equino', 'caballo'],
                'caballo' => ['equino', 'caballo'],
            ];

            if (isset($especieSinonimos[$especie])) {
                $query->whereIn('especie', $especieSinonimos[$especie]);
            } else {
                $query->where('especie', $especie);
            }
        }

        if (!empty($this->filters['estado'])) {
            $query->where('estado', $this->filters['estado']);
        }

        if (!empty($this->filters['sexo'])) {
            $query->where('sexo', $this->filters['sexo']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Codigo Unico',
            'Numero Chip',
            'Nombre',
            'Especie',
            'Raza',
            'Sexo',
            'Color',
            'Tamanio',
            'Edad Aproximada',
            'Peso (kg)',
            'Estado',
            'Estado Salud',
            'Esterilizado',
            'Fecha Rescate',
            'Ubicacion Rescate',
            'Senias Particulares',
            'Observaciones',
            'Fecha Registro',
        ];
    }

    public function map($animal): array
    {
        return [
            $animal->codigo_unico ?? 'N/A',
            $animal->numero_chip ?? 'Sin chip',
            $animal->nombre ?? 'Sin nombre',
            ucfirst($animal->especie ?? 'N/A'),
            $animal->raza ?? 'Mestizo',
            $this->formatSexo($animal->sexo),
            $animal->color ?? 'N/A',
            $this->formatTamanio($animal->tamanio),
            $this->formatEdad($animal->edad_aproximada),
            $animal->peso_actual ?? 'N/A',
            $this->formatEstado($animal->estado),
            $this->formatEstadoSalud($animal->estado_salud),
            $animal->esterilizado ? 'Si' : 'No',
            $animal->fecha_rescate ? Carbon::parse($animal->fecha_rescate)->format('d/m/Y') : 'N/A',
            $animal->ubicacion_rescate ?? 'N/A',
            $animal->senias_particulares ?? '',
            $animal->observaciones ?? '',
            $animal->created_at ? Carbon::parse($animal->created_at)->format('d/m/Y H:i') : 'N/A',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFAB00'],
                ],
            ],
        ];
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

    private function formatTamanio($tamanio): string
    {
        $tamanios = [
            'pequenio' => 'Pequenio',
            'mediano' => 'Mediano',
            'grande' => 'Grande',
            'muy_grande' => 'Muy Grande',
        ];
        return $tamanios[$tamanio] ?? ucfirst($tamanio ?? 'N/A');
    }

    private function formatEdad($meses): string
    {
        if (!$meses) return 'Desconocida';
        $anios = floor($meses / 12);
        $mesesRestantes = $meses % 12;
        $partes = [];
        if ($anios > 0) $partes[] = "{$anios} anio(s)";
        if ($mesesRestantes > 0) $partes[] = "{$mesesRestantes} mes(es)";
        return implode(' y ', $partes) ?: 'Desconocida';
    }

    private function formatEstado($estado): string
    {
        $estados = [
            'en_calle' => 'En Calle',
            'en_refugio' => 'En Refugio',
            'en_adopcion' => 'En Adopcion',
            'adoptado' => 'Adoptado',
            'en_tratamiento' => 'En Tratamiento',
            'fallecido' => 'Fallecido',
        ];
        return $estados[$estado] ?? ucfirst(str_replace('_', ' ', $estado ?? 'N/A'));
    }

    private function formatEstadoSalud($estado): string
    {
        $estados = [
            'critico' => 'Critico',
            'grave' => 'Grave',
            'estable' => 'Estable',
            'bueno' => 'Bueno',
            'excelente' => 'Excelente',
        ];
        return $estados[$estado] ?? ucfirst($estado ?? 'N/A');
    }
}
