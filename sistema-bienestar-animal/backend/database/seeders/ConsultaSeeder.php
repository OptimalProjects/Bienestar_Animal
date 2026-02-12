<?php

namespace Database\Seeders;

use App\Models\Animal\Animal;
use App\Models\Veterinaria\Consulta;
use App\Models\Veterinaria\TipoVacuna;
use App\Models\Veterinaria\Vacuna;
use App\Models\Veterinaria\Veterinario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ConsultaSeeder extends Seeder
{
    public function run(): void
    {
        $veterinarios = Veterinario::where('activo', true)->get();

        if ($veterinarios->isEmpty()) {
            $this->command->warn('No hay veterinarios registrados. Saltando ConsultaSeeder.');
            return;
        }

        $animales = Animal::with('historialClinico')
            ->whereHas('historialClinico')
            ->get();

        if ($animales->isEmpty()) {
            $this->command->warn('No hay animales con historial clinico. Saltando ConsultaSeeder.');
            return;
        }

        $consultasData = [
            // --- CONSULTAS DE HOY (5) ---
            ['tipo_consulta' => 'inicial', 'motivo' => 'Revision de rutina al ingreso al refugio', 'diagnostico' => 'Animal en buen estado general, signos vitales normales.', 'recomendaciones' => 'Desparasitacion y vacunacion segun esquema.', 'days_ago' => 0],
            ['tipo_consulta' => 'emergencia', 'motivo' => 'Vomito y diarrea desde anoche', 'diagnostico' => 'Gastroenteritis viral. Deshidratacion leve.', 'recomendaciones' => 'Suero oral, dieta blanda por 3 dias. Metronidazol 250mg c/12h.', 'days_ago' => 0],
            ['tipo_consulta' => 'control', 'motivo' => 'Control post-cirugia de esterilizacion', 'diagnostico' => 'Herida quirurgica en buen proceso de cicatrizacion.', 'recomendaciones' => 'Continuar antibiotico por 3 dias. Retirar puntos en 5 dias.', 'days_ago' => 0],
            ['tipo_consulta' => 'seguimiento', 'motivo' => 'Seguimiento de tratamiento dermatologico', 'diagnostico' => 'Mejoria notable en lesiones cutaneas.', 'recomendaciones' => 'Continuar bano medicado semanal.', 'days_ago' => 0],
            ['tipo_consulta' => 'inicial', 'motivo' => 'Evaluacion pre-adopcion', 'diagnostico' => 'Animal apto para adopcion, estado de salud optimo.', 'recomendaciones' => 'Vacunas al dia. Listo para nuevo hogar.', 'days_ago' => 0],

            // --- CONSULTAS DE ESTA SEMANA (5) ---
            ['tipo_consulta' => 'emergencia', 'motivo' => 'Atropellamiento, fractura en pata trasera', 'diagnostico' => 'Fractura cerrada de tibia derecha. Requiere cirugia.', 'recomendaciones' => 'Programar cirugia. Analgesicos y reposo absoluto.', 'days_ago' => 1],
            ['tipo_consulta' => 'control', 'motivo' => 'Control de peso y condicion corporal', 'diagnostico' => 'Sobrepeso moderado. Condicion corporal 7/9.', 'recomendaciones' => 'Dieta reducida en calorias. Aumentar actividad fisica.', 'days_ago' => 2],
            ['tipo_consulta' => 'inicial', 'motivo' => 'Cachorro rescatado de la calle', 'diagnostico' => 'Desnutricion leve, parasitos intestinales.', 'recomendaciones' => 'Desparasitacion inmediata. Alimentacion especial para cachorros.', 'days_ago' => 3],
            ['tipo_consulta' => 'seguimiento', 'motivo' => 'Control de parvovirus en tratamiento', 'diagnostico' => 'Mejoria significativa. Hidratacion normal.', 'recomendaciones' => 'Continuar tratamiento 3 dias mas. Dieta blanda.', 'days_ago' => 2],
            ['tipo_consulta' => 'control', 'motivo' => 'Revision de ojos con secrecion', 'diagnostico' => 'Conjuntivitis bacteriana bilateral.', 'recomendaciones' => 'Gotas oftalmicas c/8h por 7 dias. Limpiar con solucion salina.', 'days_ago' => 4],

            // --- CONSULTAS DEL MES ACTUAL (8) ---
            ['tipo_consulta' => 'inicial', 'motivo' => 'Ingreso de animal rescatado de situacion de maltrato', 'diagnostico' => 'Multiples heridas superficiales. Desnutricion moderada.', 'recomendaciones' => 'Curacion diaria de heridas. Alimentacion rica en proteinas.', 'days_ago' => 7],
            ['tipo_consulta' => 'emergencia', 'motivo' => 'Convulsiones repentinas', 'diagnostico' => 'Posible epilepsia idiopatica. Requiere examenes.', 'recomendaciones' => 'Examenes de sangre. Monitoreo de episodios.', 'days_ago' => 8],
            ['tipo_consulta' => 'control', 'motivo' => 'Cojera en pata delantera izquierda', 'diagnostico' => 'Esguince leve sin fractura aparente.', 'recomendaciones' => 'Reposo, antiinflamatorio por 5 dias, compresas frias.', 'days_ago' => 10],
            ['tipo_consulta' => 'seguimiento', 'motivo' => 'Control de tratamiento respiratorio', 'diagnostico' => 'Rinitis cronica en mejoria con tratamiento.', 'recomendaciones' => 'Continuar nebulizaciones. Control en 2 semanas.', 'days_ago' => 9],
            ['tipo_consulta' => 'inicial', 'motivo' => 'Revision general de gato rescatado', 'diagnostico' => 'Otitis externa bilateral. Acaros en oidos.', 'recomendaciones' => 'Limpieza otica diaria. Gotas oticas acaricidas.', 'days_ago' => 11],
            ['tipo_consulta' => 'control', 'motivo' => 'Chequeo anual de salud', 'diagnostico' => 'Animal senior en buen estado para su edad.', 'recomendaciones' => 'Suplemento articular. Dieta senior. Examenes de sangre.', 'days_ago' => 6],
            ['tipo_consulta' => 'emergencia', 'motivo' => 'Mordedura de otro animal', 'diagnostico' => 'Herida punzante en cuello. Riesgo de infeccion.', 'recomendaciones' => 'Limpieza profunda. Antibiotico amplio espectro 7 dias.', 'days_ago' => 5],
            ['tipo_consulta' => 'seguimiento', 'motivo' => 'Control post-desparasitacion', 'diagnostico' => 'Desparasitacion exitosa. No se observan parasitos.', 'recomendaciones' => 'Proxima desparasitacion en 3 meses.', 'days_ago' => 12],

            // --- CONSULTAS DE MESES ANTERIORES (12) ---
            ['tipo_consulta' => 'inicial', 'motivo' => 'Revision de ingreso por abandono', 'diagnostico' => 'Animal en estado general aceptable. Estres por abandono.', 'recomendaciones' => 'Ambiente tranquilo. Socializacion progresiva.', 'days_ago' => 20],
            ['tipo_consulta' => 'control', 'motivo' => 'Revision post-esterilizacion', 'diagnostico' => 'Recuperacion normal. Sin signos de infeccion.', 'recomendaciones' => 'Alta medica. Puede iniciar proceso de adopcion.', 'days_ago' => 25],
            ['tipo_consulta' => 'emergencia', 'motivo' => 'Intoxicacion por alimento en mal estado', 'diagnostico' => 'Intoxicacion alimentaria leve.', 'recomendaciones' => 'Ayuno 12 horas. Hidratacion oral. Dieta blanda.', 'days_ago' => 30],
            ['tipo_consulta' => 'seguimiento', 'motivo' => 'Control de herida en recuperacion', 'diagnostico' => 'Cicatrizacion completa. Sin complicaciones.', 'recomendaciones' => 'Alta de tratamiento. Animal apto para adopcion.', 'days_ago' => 35],
            ['tipo_consulta' => 'inicial', 'motivo' => 'Evaluacion de perro callejero capturado', 'diagnostico' => 'Sarna demodecica localizada. Parasitos intestinales.', 'recomendaciones' => 'Ivermectina. Desparasitacion. Bano medicado semanal.', 'days_ago' => 40],
            ['tipo_consulta' => 'control', 'motivo' => 'Mal aliento y encias enrojecidas', 'diagnostico' => 'Enfermedad periodontal grado 2.', 'recomendaciones' => 'Programar limpieza dental bajo anestesia.', 'days_ago' => 45],
            ['tipo_consulta' => 'emergencia', 'motivo' => 'Dificultad respiratoria aguda', 'diagnostico' => 'Posible rinotraqueitis felina.', 'recomendaciones' => 'Antibiotico, mucolytico, nebulizaciones.', 'days_ago' => 50],
            ['tipo_consulta' => 'seguimiento', 'motivo' => 'Control de sarna en tratamiento', 'diagnostico' => 'Mejoria del 70%. Nuevo pelo creciendo en zonas afectadas.', 'recomendaciones' => 'Continuar tratamiento 2 semanas mas.', 'days_ago' => 55],
            ['tipo_consulta' => 'inicial', 'motivo' => 'Ingreso de camada de gatitos abandonados', 'diagnostico' => 'Cachorros en buen estado. Peso adecuado para su edad.', 'recomendaciones' => 'Alimentacion con formula especial cada 4 horas.', 'days_ago' => 60],
            ['tipo_consulta' => 'control', 'motivo' => 'Revision de oido con infeccion', 'diagnostico' => 'Otitis media en resolucion.', 'recomendaciones' => 'Continuar gotas oticas. Control en 1 semana.', 'days_ago' => 70],
            ['tipo_consulta' => 'seguimiento', 'motivo' => 'Control de peso post-dieta', 'diagnostico' => 'Peso dentro de rango normal. Condicion corporal 5/9.', 'recomendaciones' => 'Mantener dieta actual. Proximo control en 3 meses.', 'days_ago' => 80],
            ['tipo_consulta' => 'inicial', 'motivo' => 'Perro encontrado con collar de cadena', 'diagnostico' => 'Herida por collar incrustado en cuello. Infeccion local.', 'recomendaciones' => 'Retiro de cadena. Curacion diaria. Antibioticos.', 'days_ago' => 90],
        ];

        foreach ($consultasData as $index => $data) {
            $animal = $animales[$index % $animales->count()];
            $vet = $veterinarios[$index % $veterinarios->count()];
            $daysAgo = $data['days_ago'];

            Consulta::create([
                'id' => (string) Str::uuid(),
                'historial_clinico_id' => $animal->historialClinico->id,
                'veterinario_id' => $vet->id,
                'fecha_consulta' => now()->subDays($daysAgo)->setTime(rand(7, 16), rand(0, 59)),
                'tipo_consulta' => $data['tipo_consulta'],
                'motivo' => $data['motivo'],
                'temperatura' => round(37.5 + (rand(0, 25) / 10), 2),
                'frecuencia_cardiaca' => rand(60, 140),
                'frecuencia_respiratoria' => rand(15, 35),
                'peso' => $animal->peso_actual ? round($animal->peso_actual + (rand(-10, 10) / 10), 2) : null,
                'diagnostico' => $data['diagnostico'],
                'recomendaciones' => $data['recomendaciones'],
                'proxima_cita' => rand(0, 1) ? now()->addDays(rand(7, 30))->format('Y-m-d') : null,
            ]);
        }

        $this->crearVacunas($animales, $veterinarios);
    }

    protected function crearVacunas($animales, $veterinarios): void
    {
        $tiposVacuna = TipoVacuna::all();

        if ($tiposVacuna->isEmpty()) {
            return;
        }

        foreach ($animales->take(15) as $index => $animal) {
            if (!$animal->historialClinico) {
                continue;
            }

            $vet = $veterinarios[$index % $veterinarios->count()];

            // Vacuna antirrabica
            $especieVacuna = $animal->especie === 'gato' ? 'gato' : 'perro';
            $vacunaRabia = $tiposVacuna->first(function ($tipo) use ($especieVacuna) {
                return str_contains(strtolower($tipo->nombre), 'rabia')
                    && in_array($tipo->especie_aplicable, [$especieVacuna, 'ambos']);
            });

            if ($vacunaRabia) {
                Vacuna::create([
                    'id' => (string) Str::uuid(),
                    'historial_clinico_id' => $animal->historialClinico->id,
                    'tipo_vacuna_id' => $vacunaRabia->id,
                    'veterinario_id' => $vet->id,
                    'fecha_aplicacion' => now()->subDays(rand(30, 180))->format('Y-m-d'),
                    'lote_vacuna' => 'LOT-' . rand(10000, 99999),
                    'fabricante' => collect(['Zoetis', 'MSD Animal Health', 'Boehringer Ingelheim'])->random(),
                    'nombre_vacuna' => 'Vacuna Antirrabica ' . ucfirst($especieVacuna),
                    'fecha_vencimiento' => now()->addYears(2)->format('Y-m-d'),
                    'dosis' => 1.00,
                    'via_administracion' => 'subcutanea',
                    'sitio_aplicacion' => 'Muslo derecho',
                    'numero_dosis' => '1',
                    'estado' => 'aplicada',
                    'observaciones' => 'Aplicacion sin reacciones adversas.',
                ]);
            }

            // Vacuna polivalente para perros
            if ($animal->especie === 'perro') {
                $vacunaPoli = $tiposVacuna->first(function ($tipo) {
                    return str_contains(strtolower($tipo->nombre), 'polivalente')
                        || str_contains(strtolower($tipo->nombre), 'parvovirus');
                });
                if ($vacunaPoli) {
                    Vacuna::create([
                        'id' => (string) Str::uuid(),
                        'historial_clinico_id' => $animal->historialClinico->id,
                        'tipo_vacuna_id' => $vacunaPoli->id,
                        'veterinario_id' => $vet->id,
                        'fecha_aplicacion' => now()->subDays(rand(60, 200))->format('Y-m-d'),
                        'lote_vacuna' => 'LOT-' . rand(10000, 99999),
                        'fabricante' => collect(['Zoetis', 'MSD Animal Health', 'Virbac'])->random(),
                        'nombre_vacuna' => $vacunaPoli->nombre,
                        'fecha_vencimiento' => now()->addYear()->format('Y-m-d'),
                        'dosis' => 1.00,
                        'via_administracion' => 'subcutanea',
                        'sitio_aplicacion' => 'Escapular derecha',
                        'numero_dosis' => '1',
                        'estado' => 'aplicada',
                        'observaciones' => 'Primera dosis del esquema de vacunacion.',
                    ]);
                }
            }

            // Triple felina para gatos
            if ($animal->especie === 'gato') {
                $vacunaTriple = $tiposVacuna->first(function ($tipo) {
                    return str_contains(strtolower($tipo->nombre), 'triple felina');
                });
                if ($vacunaTriple) {
                    Vacuna::create([
                        'id' => (string) Str::uuid(),
                        'historial_clinico_id' => $animal->historialClinico->id,
                        'tipo_vacuna_id' => $vacunaTriple->id,
                        'veterinario_id' => $vet->id,
                        'fecha_aplicacion' => now()->subDays(rand(30, 150))->format('Y-m-d'),
                        'lote_vacuna' => 'LOT-' . rand(10000, 99999),
                        'fabricante' => collect(['Zoetis', 'MSD Animal Health', 'Merial'])->random(),
                        'nombre_vacuna' => 'Triple Felina (PIF)',
                        'fecha_vencimiento' => now()->addMonths(18)->format('Y-m-d'),
                        'dosis' => 1.00,
                        'via_administracion' => 'subcutanea',
                        'sitio_aplicacion' => 'Escapular izquierda',
                        'numero_dosis' => '1',
                        'estado' => 'aplicada',
                        'observaciones' => 'Vacuna aplicada sin complicaciones.',
                    ]);
                }
            }
        }
    }
}
