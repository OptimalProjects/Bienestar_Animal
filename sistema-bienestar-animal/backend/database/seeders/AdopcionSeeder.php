<?php

namespace Database\Seeders;

use App\Models\Adopcion\Adoptante;
use App\Models\Adopcion\Adopcion;
use App\Models\Adopcion\VisitaDomiciliaria;
use App\Models\Animal\Animal;
use App\Models\User\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdopcionSeeder extends Seeder
{
    public function run(): void
    {
        // Crear adoptantes
        $adoptantesData = [
            [
                'nombres' => 'Carolina', 'apellidos' => 'Mejia Escobar',
                'tipo_documento' => 'CC', 'numero_documento' => '1143567890',
                'fecha_nacimiento' => '1985-03-15', 'telefono' => '3201234567',
                'email' => 'carolina.mejia@email.com',
                'direccion' => 'Carrera 42 #5B-25, Barrio San Fernando',
                'tipo_vivienda' => 'casa', 'tiene_patio' => true,
                'experiencia_animales' => 'Tuve un gato adulto por 8 anos. Experiencia con mascotas.',
                'num_personas_hogar' => 3, 'estado' => 'activo',
            ],
            [
                'nombres' => 'Andres Felipe', 'apellidos' => 'Rios Cardona',
                'tipo_documento' => 'CC', 'numero_documento' => '1087654321',
                'fecha_nacimiento' => '1990-07-22', 'telefono' => '3112345678',
                'email' => 'andres.rios@email.com',
                'direccion' => 'Calle 13 #100-45 Apto 502',
                'tipo_vivienda' => 'apartamento', 'tiene_patio' => false,
                'experiencia_animales' => 'Trabajo desde casa. Primera mascota.',
                'num_personas_hogar' => 2, 'estado' => 'activo',
            ],
            [
                'nombres' => 'Lucia Fernanda', 'apellidos' => 'Vargas Trujillo',
                'tipo_documento' => 'CC', 'numero_documento' => '31876543',
                'fecha_nacimiento' => '1978-11-08', 'telefono' => '3153456789',
                'email' => 'lucia.vargas@email.com',
                'direccion' => 'Carrera 56 #12-78, Barrio Granada',
                'tipo_vivienda' => 'casa', 'tiene_patio' => true,
                'experiencia_animales' => 'Tengo dos perros adultos y mucho amor para dar.',
                'num_personas_hogar' => 4, 'estado' => 'activo',
            ],
            [
                'nombres' => 'Diego Alejandro', 'apellidos' => 'Munoz Parra',
                'tipo_documento' => 'CC', 'numero_documento' => '1098234567',
                'fecha_nacimiento' => '1995-01-20', 'telefono' => '3164567890',
                'email' => 'diego.munoz@email.com',
                'direccion' => 'Calle 5 #39-15, Barrio El Penon',
                'tipo_vivienda' => 'casa', 'tiene_patio' => true,
                'experiencia_animales' => 'Creci con mascotas. Tengo jardin amplio.',
                'num_personas_hogar' => 2, 'estado' => 'activo',
            ],
            [
                'nombres' => 'Sandra Patricia', 'apellidos' => 'Caicedo Lopez',
                'tipo_documento' => 'CC', 'numero_documento' => '66789012',
                'fecha_nacimiento' => '1982-09-12', 'telefono' => '3175678901',
                'email' => 'sandra.caicedo@email.com',
                'direccion' => 'Carrera 66 #9-40, Barrio Tequendama',
                'tipo_vivienda' => 'apartamento', 'tiene_patio' => false,
                'experiencia_animales' => 'Quiero adoptar un gato. Vivo sola en apartamento.',
                'num_personas_hogar' => 1, 'estado' => 'activo',
            ],
            [
                'nombres' => 'Jorge Eduardo', 'apellidos' => 'Henao Gutierrez',
                'tipo_documento' => 'CC', 'numero_documento' => '16345678',
                'fecha_nacimiento' => '1975-05-30', 'telefono' => '3186789012',
                'email' => 'jorge.henao@email.com',
                'direccion' => 'Finca La Esperanza, via Pance',
                'tipo_vivienda' => 'finca', 'tiene_patio' => true,
                'experiencia_animales' => 'Tengo finca con espacio para animales grandes.',
                'num_personas_hogar' => 5, 'estado' => 'activo',
            ],
            [
                'nombres' => 'Valentina', 'apellidos' => 'Ospina Restrepo',
                'tipo_documento' => 'CC', 'numero_documento' => '1107890123',
                'fecha_nacimiento' => '1998-12-05', 'telefono' => '3197890123',
                'email' => 'valentina.ospina@email.com',
                'direccion' => 'Calle 44 #3-56, San Antonio',
                'tipo_vivienda' => 'apartamento', 'tiene_patio' => false,
                'experiencia_animales' => null,
                'num_personas_hogar' => 1, 'estado' => 'activo',
            ],
            [
                'nombres' => 'Carlos Alberto', 'apellidos' => 'Ramirez Duque',
                'tipo_documento' => 'CC', 'numero_documento' => '14567890',
                'fecha_nacimiento' => '1970-04-18', 'telefono' => '3108901234',
                'email' => 'carlos.ramirez.d@email.com',
                'direccion' => 'Carrera 25 #10-30, Barrio Obrero',
                'tipo_vivienda' => 'casa', 'tiene_patio' => true,
                'experiencia_animales' => 'Siempre he tenido perros. Jubilado con mucho tiempo.',
                'num_personas_hogar' => 2, 'estado' => 'activo',
            ],
        ];

        $adoptantes = [];
        foreach ($adoptantesData as $data) {
            $adoptantes[] = Adoptante::create(array_merge($data, [
                'id' => (string) Str::uuid(),
            ]));
        }

        // Obtener animales por estado
        $animalesAdopcion = Animal::where('estado', 'en_adopcion')->get();
        $animalesAdoptados = Animal::where('estado', 'adoptado')->get();

        // Obtener evaluador
        $evaluador = Usuario::whereHas('roles', function ($q) {
            $q->where('codigo', 'EVALUADOR');
        })->first();

        $evaluadorId = $evaluador?->id;

        // ===================================================
        // ADOPCIONES COMPLETADAS - Diciembre 2025 (para tendencias)
        // ===================================================
        $this->crearAdopcion($adoptantes[0], $animalesAdoptados->get(0) ?? $animalesAdopcion->get(0), [
            'estado' => 'completada',
            'fecha_solicitud' => now()->subMonths(3)->subDays(10),
            'fecha_aprobacion' => now()->subMonths(3)->subDays(5),
            'fecha_entrega' => now()->subMonths(3),
            'evaluador_id' => $evaluadorId,
            'observaciones' => 'Adoptante cumple todos los requisitos. Casa amplia con patio.',
            'contrato_firmado' => true,
        ]);

        $this->crearAdopcion($adoptantes[1], $animalesAdoptados->get(1) ?? $animalesAdopcion->get(1), [
            'estado' => 'completada',
            'fecha_solicitud' => now()->subMonths(3)->subDays(15),
            'fecha_aprobacion' => now()->subMonths(3)->subDays(8),
            'fecha_entrega' => now()->subMonths(3)->subDays(3),
            'evaluador_id' => $evaluadorId,
            'observaciones' => 'Apartamento apto para gatos. Adoptante responsable.',
            'contrato_firmado' => true,
        ]);

        $this->crearAdopcion($adoptantes[5], $animalesAdoptados->get(2) ?? $animalesAdopcion->get(2), [
            'estado' => 'completada',
            'fecha_solicitud' => now()->subMonths(2)->subDays(20),
            'fecha_aprobacion' => now()->subMonths(2)->subDays(12),
            'fecha_entrega' => now()->subMonths(2)->subDays(8),
            'evaluador_id' => $evaluadorId,
            'observaciones' => 'Finca con espacio ideal para perro grande.',
            'contrato_firmado' => true,
        ]);

        // ===================================================
        // ADOPCIONES COMPLETADAS - Enero 2026 (para tendencias)
        // ===================================================
        $this->crearAdopcion($adoptantes[2], $animalesAdoptados->get(3) ?? $animalesAdopcion->get(3), [
            'estado' => 'completada',
            'fecha_solicitud' => now()->subMonth()->subDays(20),
            'fecha_aprobacion' => now()->subMonth()->subDays(12),
            'fecha_entrega' => now()->subMonth()->subDays(7),
            'evaluador_id' => $evaluadorId,
            'observaciones' => 'Familia con experiencia. Perros existentes sociables.',
            'contrato_firmado' => true,
        ]);

        $this->crearAdopcion($adoptantes[3], $animalesAdoptados->get(4) ?? $animalesAdopcion->get(4), [
            'estado' => 'completada',
            'fecha_solicitud' => now()->subMonth()->subDays(18),
            'fecha_aprobacion' => now()->subMonth()->subDays(10),
            'fecha_entrega' => now()->subMonth()->subDays(5),
            'evaluador_id' => $evaluadorId,
            'observaciones' => 'Hogar adecuado con jardin. Pareja joven comprometida.',
            'contrato_firmado' => true,
        ]);

        $this->crearAdopcion($adoptantes[7], $animalesAdoptados->get(5) ?? $animalesAdopcion->get(5), [
            'estado' => 'completada',
            'fecha_solicitud' => now()->subMonth()->subDays(25),
            'fecha_aprobacion' => now()->subMonth()->subDays(15),
            'fecha_entrega' => now()->subMonth()->subDays(10),
            'evaluador_id' => $evaluadorId,
            'observaciones' => 'Jubilado con mucho tiempo y experiencia. Hogar ideal para perro senior.',
            'contrato_firmado' => true,
        ]);

        $this->crearAdopcion($adoptantes[4], $animalesAdoptados->get(6) ?? $animalesAdopcion->get(6), [
            'estado' => 'completada',
            'fecha_solicitud' => now()->subMonth()->subDays(15),
            'fecha_aprobacion' => now()->subMonth()->subDays(8),
            'fecha_entrega' => now()->subMonth()->subDays(3),
            'evaluador_id' => $evaluadorId,
            'observaciones' => 'Apartamento apto para gato. Adoptante comprometida.',
            'contrato_firmado' => true,
        ]);

        // ===================================================
        // ADOPCIONES COMPLETADAS - Febrero 2026 (mes actual)
        // ===================================================
        $this->crearAdopcion($adoptantes[6], $animalesAdoptados->get(7) ?? $animalesAdopcion->get(7), [
            'estado' => 'completada',
            'fecha_solicitud' => now()->subDays(15),
            'fecha_aprobacion' => now()->subDays(8),
            'fecha_entrega' => now()->subDays(3),
            'evaluador_id' => $evaluadorId,
            'observaciones' => 'Primera mascota. Adoptante muy entusiasmada y responsable.',
            'contrato_firmado' => true,
        ]);

        $this->crearAdopcion($adoptantes[0], $animalesAdopcion->get(8), [
            'estado' => 'completada',
            'fecha_solicitud' => now()->subDays(12),
            'fecha_aprobacion' => now()->subDays(6),
            'fecha_entrega' => now()->subDays(2),
            'evaluador_id' => $evaluadorId,
            'observaciones' => 'Segunda adopcion de esta familia. Excelente historial.',
            'contrato_firmado' => true,
        ]);

        $this->crearAdopcion($adoptantes[3], $animalesAdopcion->get(9), [
            'estado' => 'completada',
            'fecha_solicitud' => now()->subDays(10),
            'fecha_aprobacion' => now()->subDays(5),
            'fecha_entrega' => now()->subDays(1),
            'evaluador_id' => $evaluadorId,
            'observaciones' => 'Adopcion de gatito. Hogar verificado previamente.',
            'contrato_firmado' => true,
        ]);

        // ===================================================
        // ADOPCIONES APROBADAS (pendientes de entrega)
        // ===================================================
        $adopcionAprobada = $this->crearAdopcion($adoptantes[1], $animalesAdopcion->get(0), [
            'estado' => 'aprobada',
            'fecha_solicitud' => now()->subDays(14),
            'fecha_aprobacion' => now()->subDays(3),
            'evaluador_id' => $evaluadorId,
            'observaciones' => 'Aprobada. Pendiente coordinar entrega.',
            'contrato_firmado' => true,
        ]);

        // Visita pre-adopcion para la aprobada
        if ($adopcionAprobada && $evaluador) {
            VisitaDomiciliaria::create([
                'id' => (string) Str::uuid(),
                'adopcion_id' => $adopcionAprobada->id,
                'fecha_programada' => now()->subDays(7)->format('Y-m-d'),
                'fecha_realizada' => now()->subDays(7),
                'visitador_id' => $evaluador->id,
                'tipo_visita' => 'pre_adopcion',
                'observaciones' => 'Vivienda adecuada. Adoptante demuestra interes genuino.',
                'resultado' => 'satisfactoria',
                'recomendaciones' => 'Aprobar adopcion.',
            ]);
        }

        $this->crearAdopcion($adoptantes[5], $animalesAdopcion->get(1), [
            'estado' => 'aprobada',
            'fecha_solicitud' => now()->subDays(10),
            'fecha_aprobacion' => now()->subDays(2),
            'evaluador_id' => $evaluadorId,
            'observaciones' => 'Finca con espacio amplio. Ideal para perro grande.',
            'contrato_firmado' => false,
        ]);

        // ===================================================
        // ADOPCIONES EN EVALUACION
        // ===================================================
        $this->crearAdopcion($adoptantes[2], $animalesAdopcion->get(2), [
            'estado' => 'en_evaluacion',
            'fecha_solicitud' => now()->subDays(5),
            'evaluador_id' => $evaluadorId,
            'observaciones' => 'Pendiente visita domiciliaria para verificar condiciones.',
        ]);

        $this->crearAdopcion($adoptantes[4], $animalesAdopcion->get(3), [
            'estado' => 'en_evaluacion',
            'fecha_solicitud' => now()->subDays(3),
            'evaluador_id' => $evaluadorId,
            'observaciones' => 'Verificar si apartamento permite mascotas.',
        ]);

        $this->crearAdopcion($adoptantes[7], $animalesAdopcion->get(4), [
            'estado' => 'en_evaluacion',
            'fecha_solicitud' => now()->subDays(4),
            'evaluador_id' => $evaluadorId,
            'observaciones' => 'Evaluar compatibilidad con perro existente.',
        ]);

        // ===================================================
        // ADOPCIONES SOLICITADAS (recientes)
        // ===================================================
        $this->crearAdopcion($adoptantes[6], $animalesAdopcion->get(5), [
            'estado' => 'solicitada',
            'fecha_solicitud' => now()->subDays(1),
        ]);

        $this->crearAdopcion($adoptantes[3], $animalesAdopcion->get(6), [
            'estado' => 'solicitada',
            'fecha_solicitud' => now(),
        ]);

        // ===================================================
        // ADOPCIONES RECHAZADAS
        // ===================================================
        $this->crearAdopcion($adoptantes[6], $animalesAdopcion->get(7), [
            'estado' => 'rechazada',
            'fecha_solicitud' => now()->subMonth()->subDays(10),
            'evaluador_id' => $evaluadorId,
            'motivo_rechazo' => 'Vivienda no apta. Apartamento muy pequeno sin balcon ni espacio para perro grande.',
        ]);

        $this->crearAdopcion($adoptantes[7], $animalesAdopcion->get(8), [
            'estado' => 'rechazada',
            'fecha_solicitud' => now()->subDays(20),
            'evaluador_id' => $evaluadorId,
            'motivo_rechazo' => 'Antecedentes de devolucion previa. Se recomienda esperar 6 meses.',
        ]);
    }

    private function crearAdopcion(Adoptante $adoptante, ?Animal $animal, array $extra = []): ?Adopcion
    {
        if (!$animal) {
            return null;
        }

        return Adopcion::create(array_merge([
            'id' => (string) Str::uuid(),
            'animal_id' => $animal->id,
            'adoptante_id' => $adoptante->id,
            'fecha_solicitud' => $extra['fecha_solicitud'] ?? now(),
        ], $extra));
    }
}
