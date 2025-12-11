<?php

namespace Database\Seeders;

use App\Models\Animal\Animal;
use App\Models\User\Usuario;
use App\Models\Veterinaria\Consulta;
use App\Models\Veterinaria\TipoVacuna;
use App\Models\Veterinaria\Vacuna;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ConsultaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $veterinarios = Usuario::whereHas('rol', function ($q) {
            $q->where('codigo', 'VETERINARIO');
        })->get();

        if ($veterinarios->isEmpty()) {
            return;
        }

        $animales = Animal::with('historialClinico')->get();

        $consultas = [
            [
                'tipo_consulta' => 'revision_general',
                'motivo_consulta' => 'Revision de rutina al ingreso',
                'diagnostico' => 'Animal en buen estado general, signos vitales normales.',
                'tratamiento' => 'Se recomienda desparasitacion y vacunacion segun esquema.',
                'observaciones' => 'Continuar con alimentacion balanceada.',
            ],
            [
                'tipo_consulta' => 'vacunacion',
                'motivo_consulta' => 'Aplicacion de vacuna antirabica',
                'diagnostico' => 'Apto para vacunacion.',
                'tratamiento' => 'Vacuna antirabica aplicada sin complicaciones.',
                'observaciones' => 'Programar refuerzo en 12 meses.',
            ],
            [
                'tipo_consulta' => 'urgencia',
                'motivo_consulta' => 'Animal presenta vomito y diarrea',
                'diagnostico' => 'Posible gastroenteritis viral.',
                'tratamiento' => 'Suero oral, dieta blanda, metronidazol 250mg c/12h por 5 dias.',
                'observaciones' => 'Evaluar en 48 horas, si no mejora realizar examenes.',
            ],
            [
                'tipo_consulta' => 'seguimiento',
                'motivo_consulta' => 'Control post-cirugia de esterilizacion',
                'diagnostico' => 'Herida quirurgica en buen proceso de cicatrizacion.',
                'tratamiento' => 'Continuar con antibiotico por 3 dias mas.',
                'observaciones' => 'Retirar puntos en 5 dias.',
            ],
            [
                'tipo_consulta' => 'dermatologia',
                'motivo_consulta' => 'Prurito intenso y perdida de pelo',
                'diagnostico' => 'Dermatitis alergica por pulgas.',
                'tratamiento' => 'Baño medicado, pipeta antipulgas, antihistaminico.',
                'observaciones' => 'Control en 15 dias.',
            ],
            [
                'tipo_consulta' => 'revision_general',
                'motivo_consulta' => 'Evaluacion pre-adopcion',
                'diagnostico' => 'Animal apto para adopcion, estado de salud optimo.',
                'tratamiento' => 'Ninguno requerido.',
                'observaciones' => 'Vacunas al dia, esterilizado, listo para nuevo hogar.',
            ],
            [
                'tipo_consulta' => 'oftalmologia',
                'motivo_consulta' => 'Secrecion ocular verdosa',
                'diagnostico' => 'Conjuntivitis bacteriana.',
                'tratamiento' => 'Gotas oftalmicas antibioticas c/8h por 7 dias.',
                'observaciones' => 'Limpiar ojos con solucion salina antes de cada aplicacion.',
            ],
            [
                'tipo_consulta' => 'traumatologia',
                'motivo_consulta' => 'Cojera en pata delantera izquierda',
                'diagnostico' => 'Esguince leve sin fractura aparente.',
                'tratamiento' => 'Reposo, antiinflamatorio por 5 dias, compresas frias.',
                'observaciones' => 'Si no mejora en una semana, tomar radiografia.',
            ],
            [
                'tipo_consulta' => 'odontologia',
                'motivo_consulta' => 'Mal aliento y encias enrojecidas',
                'diagnostico' => 'Enfermedad periodontal grado 2.',
                'tratamiento' => 'Programar limpieza dental bajo anestesia.',
                'observaciones' => 'Iniciar cepillado dental diario.',
            ],
            [
                'tipo_consulta' => 'revision_general',
                'motivo_consulta' => 'Chequeo anual de salud',
                'diagnostico' => 'Animal senior en buen estado para su edad.',
                'tratamiento' => 'Suplemento articular, dieta senior.',
                'observaciones' => 'Realizar examenes de sangre de control.',
            ],
        ];

        $count = 0;
        foreach ($animales->take(10) as $animal) {
            if (!$animal->historialClinico) {
                continue;
            }

            $consultaData = $consultas[$count % count($consultas)];
            $veterinario = $veterinarios->random();

            Consulta::create([
                'id' => (string) Str::uuid(),
                'historial_clinico_id' => $animal->historialClinico->id,
                'veterinario_id' => $veterinario->id,
                'fecha' => now()->subDays(rand(1, 60)),
                'tipo_consulta' => $consultaData['tipo_consulta'],
                'motivo_consulta' => $consultaData['motivo_consulta'],
                'peso_actual' => $animal->peso + (rand(-10, 10) / 10),
                'temperatura' => 38.0 + (rand(0, 20) / 10),
                'frecuencia_cardiaca' => rand(60, 140),
                'frecuencia_respiratoria' => rand(15, 30),
                'diagnostico' => $consultaData['diagnostico'],
                'tratamiento' => $consultaData['tratamiento'],
                'observaciones' => $consultaData['observaciones'],
                'proxima_cita' => rand(0, 1) ? now()->addDays(rand(7, 30)) : null,
            ]);

            $count++;
        }

        // Crear algunas vacunas aplicadas
        $this->crearVacunas($animales, $veterinarios);
    }

    protected function crearVacunas($animales, $veterinarios): void
    {
        $tiposVacuna = TipoVacuna::all();

        if ($tiposVacuna->isEmpty()) {
            return;
        }

        foreach ($animales->take(8) as $animal) {
            if (!$animal->historialClinico) {
                continue;
            }

            // Vacuna antirabica
            $vacunaRabia = $tiposVacuna->where('nombre', 'like', '%Rabia%')
                ->where('especie_aplicable', $animal->especie)
                ->first();

            if ($vacunaRabia) {
                Vacuna::create([
                    'id' => (string) Str::uuid(),
                    'historial_clinico_id' => $animal->historialClinico->id,
                    'tipo_vacuna_id' => $vacunaRabia->id,
                    'veterinario_id' => $veterinarios->random()->id,
                    'fecha_aplicacion' => now()->subDays(rand(30, 180)),
                    'lote' => 'LOT-' . rand(10000, 99999),
                    'fabricante' => collect(['Zoetis', 'MSD', 'Boehringer'])->random(),
                    'fecha_vencimiento' => now()->addYears(2),
                    'dosis' => '1 ml',
                    'via_administracion' => 'subcutanea',
                    'observaciones' => 'Aplicacion sin reacciones adversas.',
                ]);
            }

            // Vacuna polivalente para perros
            if ($animal->especie === 'perro') {
                $vacunaPolivalente = $tiposVacuna->where('nombre', 'like', '%Polivalente%')->first();
                if ($vacunaPolivalente) {
                    Vacuna::create([
                        'id' => (string) Str::uuid(),
                        'historial_clinico_id' => $animal->historialClinico->id,
                        'tipo_vacuna_id' => $vacunaPolivalente->id,
                        'veterinario_id' => $veterinarios->random()->id,
                        'fecha_aplicacion' => now()->subDays(rand(60, 200)),
                        'lote' => 'LOT-' . rand(10000, 99999),
                        'fabricante' => collect(['Zoetis', 'MSD', 'Virbac'])->random(),
                        'fecha_vencimiento' => now()->addYear(),
                        'dosis' => '1 ml',
                        'via_administracion' => 'subcutanea',
                        'observaciones' => 'Primera dosis del esquema de vacunacion.',
                    ]);
                }
            }

            // Triple felina para gatos
            if ($animal->especie === 'gato') {
                $vacunaTriple = $tiposVacuna->where('nombre', 'Triple Felina')->first();
                if ($vacunaTriple) {
                    Vacuna::create([
                        'id' => (string) Str::uuid(),
                        'historial_clinico_id' => $animal->historialClinico->id,
                        'tipo_vacuna_id' => $vacunaTriple->id,
                        'veterinario_id' => $veterinarios->random()->id,
                        'fecha_aplicacion' => now()->subDays(rand(30, 150)),
                        'lote' => 'LOT-' . rand(10000, 99999),
                        'fabricante' => collect(['Zoetis', 'MSD', 'Merial'])->random(),
                        'fecha_vencimiento' => now()->addMonths(18),
                        'dosis' => '1 ml',
                        'via_administracion' => 'subcutanea',
                        'observaciones' => 'Vacuna aplicada correctamente.',
                    ]);
                }
            }
        }
    }
}
