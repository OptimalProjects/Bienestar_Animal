<?php

namespace Database\Seeders;

use App\Models\Adopciones\Adoptante;
use App\Models\Adopciones\SolicitudAdopcion;
use App\Models\Adopciones\VisitaSeguimiento;
use App\Models\Animal\Animal;
use App\Models\User\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdopcionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear adoptantes
        $adoptantes = [
            [
                'nombre_completo' => 'Carolina Mejia Escobar',
                'tipo_identificacion' => 'cedula',
                'numero_identificacion' => '1143567890',
                'fecha_nacimiento' => '1985-03-15',
                'telefono' => '3201234567',
                'email' => 'carolina.mejia@email.com',
                'direccion' => 'Carrera 42 #5B-25, Barrio San Fernando',
                'comuna' => 'Comuna 19',
                'barrio' => 'San Fernando',
                'tipo_vivienda' => 'casa',
                'tiene_patio' => true,
                'permite_mascotas' => true,
                'otras_mascotas' => 'Un gato adulto',
                'ocupacion' => 'Ingeniera de sistemas',
                'ingresos_mensuales' => 4500000,
                'personas_hogar' => 3,
                'menores_hogar' => 1,
            ],
            [
                'nombre_completo' => 'Andres Felipe Rios',
                'tipo_identificacion' => 'cedula',
                'numero_identificacion' => '1087654321',
                'fecha_nacimiento' => '1990-07-22',
                'telefono' => '3112345678',
                'email' => 'andres.rios@email.com',
                'direccion' => 'Calle 13 #100-45 Apto 502',
                'comuna' => 'Comuna 22',
                'barrio' => 'Ciudad Jardin',
                'tipo_vivienda' => 'apartamento',
                'tiene_patio' => false,
                'permite_mascotas' => true,
                'otras_mascotas' => null,
                'ocupacion' => 'Contador publico',
                'ingresos_mensuales' => 3800000,
                'personas_hogar' => 2,
                'menores_hogar' => 0,
            ],
            [
                'nombre_completo' => 'Lucia Fernanda Vargas',
                'tipo_identificacion' => 'cedula',
                'numero_identificacion' => '31876543',
                'fecha_nacimiento' => '1978-11-08',
                'telefono' => '3153456789',
                'email' => 'lucia.vargas@email.com',
                'direccion' => 'Carrera 56 #12-78, Barrio Granada',
                'comuna' => 'Comuna 2',
                'barrio' => 'Granada',
                'tipo_vivienda' => 'casa',
                'tiene_patio' => true,
                'permite_mascotas' => true,
                'otras_mascotas' => 'Dos perros adultos',
                'ocupacion' => 'Profesora universitaria',
                'ingresos_mensuales' => 5200000,
                'personas_hogar' => 4,
                'menores_hogar' => 2,
            ],
        ];

        $adoptantesCreados = [];
        foreach ($adoptantes as $data) {
            $adoptantesCreados[] = Adoptante::create(array_merge($data, [
                'id' => (string) Str::uuid(),
            ]));
        }

        // Obtener animales disponibles para adopcion
        $animalesAdopcion = Animal::where('estado', 'en_adopcion')->get();
        $animalAdoptado = Animal::where('estado', 'adoptado')->first();

        // Obtener evaluador
        $evaluador = Usuario::whereHas('rol', function ($q) {
            $q->where('codigo', 'EVALUADOR');
        })->first();

        // Crear solicitudes de adopcion
        $solicitudes = [
            // Solicitud pendiente
            [
                'adoptante' => $adoptantesCreados[0],
                'animal' => $animalesAdopcion->first(),
                'estado' => 'pendiente',
                'motivacion' => 'Siempre hemos querido tener un perro en la familia. Tenemos espacio, tiempo y mucho amor para darle a una mascota.',
            ],
            // Solicitud aprobada (adopcion completada)
            [
                'adoptante' => $adoptantesCreados[1],
                'animal' => $animalAdoptado ?? $animalesAdopcion->skip(1)->first(),
                'estado' => 'aprobada',
                'motivacion' => 'Vivo solo y me encantaria tener compañia. Tengo experiencia previa con gatos y trabajo desde casa.',
            ],
            // Solicitud en evaluacion
            [
                'adoptante' => $adoptantesCreados[2],
                'animal' => $animalesAdopcion->skip(2)->first(),
                'estado' => 'en_evaluacion',
                'motivacion' => 'Queremos darle un hogar a un perrito rescatado. Tenemos un patio grande y ya tenemos experiencia con mascotas.',
            ],
        ];

        foreach ($solicitudes as $solicitudData) {
            if (!$solicitudData['animal']) {
                continue;
            }

            $solicitud = SolicitudAdopcion::create([
                'id' => (string) Str::uuid(),
                'animal_id' => $solicitudData['animal']->id,
                'adoptante_id' => $solicitudData['adoptante']->id,
                'fecha_solicitud' => now()->subDays(rand(5, 30)),
                'estado' => $solicitudData['estado'],
                'motivacion' => $solicitudData['motivacion'],
                'evaluado_por' => $solicitudData['estado'] !== 'pendiente' ? $evaluador?->id : null,
                'fecha_evaluacion' => $solicitudData['estado'] !== 'pendiente'
                    ? now()->subDays(rand(1, 10))
                    : null,
                'observaciones_evaluacion' => $solicitudData['estado'] === 'aprobada'
                    ? 'Adoptante cumple con todos los requisitos. Vivienda adecuada y actitud responsable.'
                    : ($solicitudData['estado'] === 'en_evaluacion'
                        ? 'Pendiente visita domiciliaria para verificar condiciones.'
                        : null),
                'fecha_entrega' => $solicitudData['estado'] === 'aprobada'
                    ? now()->subDays(rand(1, 5))
                    : null,
            ]);

            // Crear visitas de seguimiento para adopciones aprobadas
            if ($solicitudData['estado'] === 'aprobada' && $evaluador) {
                // Primera visita realizada
                VisitaSeguimiento::create([
                    'id' => (string) Str::uuid(),
                    'adopcion_id' => $solicitud->id,
                    'usuario_id' => $evaluador->id,
                    'numero_visita' => 1,
                    'fecha_programada' => now()->subDays(20),
                    'fecha_realizada' => now()->subDays(20),
                    'estado' => 'realizada',
                    'estado_animal' => 'excelente',
                    'condiciones_vivienda' => 'optimas',
                    'observaciones' => 'Animal adaptado perfectamente a su nuevo hogar. Se ve feliz y saludable.',
                    'requiere_seguimiento' => true,
                ]);

                // Segunda visita programada
                VisitaSeguimiento::create([
                    'id' => (string) Str::uuid(),
                    'adopcion_id' => $solicitud->id,
                    'usuario_id' => $evaluador->id,
                    'numero_visita' => 2,
                    'fecha_programada' => now()->addDays(10),
                    'estado' => 'programada',
                ]);
            }
        }
    }
}
