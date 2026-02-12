<?php

namespace Database\Seeders;

use App\Models\Denuncia\Denunciante;
use App\Models\Denuncia\Denuncia;
use App\Models\User\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DenunciaSeeder extends Seeder
{
    public function run(): void
    {
        // Crear denunciantes
        $denunciantesData = [
            ['nombres' => 'Maria Fernanda', 'apellidos' => 'Gonzalez Ruiz', 'telefono' => '3151234567', 'email' => 'maria.gonzalez@email.com', 'direccion' => 'Calle 15 #45-32, Barrio San Fernando'],
            ['nombres' => 'Juan Carlos', 'apellidos' => 'Perez Duarte', 'telefono' => '3162345678', 'email' => 'juanc.perez@email.com', 'direccion' => 'Carrera 100 #13-25, Ciudad Jardin'],
            ['nombres' => 'Ana Lucia', 'apellidos' => 'Martinez Ospina', 'telefono' => '3173456789', 'email' => null, 'direccion' => null],
            ['nombres' => 'Roberto', 'apellidos' => 'Valencia Henao', 'telefono' => '3184567890', 'email' => 'roberto.valencia@email.com', 'direccion' => 'Carrera 25 #10-15, Barrio Obrero'],
            ['nombres' => 'Claudia Patricia', 'apellidos' => 'Munoz Salazar', 'telefono' => '3195678901', 'email' => 'claudia.munoz@email.com', 'direccion' => 'Calle 70 #1A-25, Tequendama'],
        ];

        $denunciantes = [];
        foreach ($denunciantesData as $data) {
            $denunciantes[] = Denunciante::create(array_merge($data, [
                'id' => (string) Str::uuid(),
            ]));
        }

        // Obtener operadores para asignar como responsables
        $operadores = Usuario::whereHas('roles', function ($q) {
            $q->where('codigo', 'OPERADOR');
        })->get();

        $denunciasData = [
            // --- RECIBIDAS (sin asignar) ---
            [
                'tipo_denuncia' => 'maltrato',
                'descripcion' => 'Vecino tiene un perro amarrado todo el dia en el sol sin agua ni comida. El animal se ve muy delgado y tiene heridas en el cuello por la cadena.',
                'ubicacion' => 'Calle 23 #67-45, Barrio Alameda, Comuna 19',
                'latitud' => 3.4516,
                'longitud' => -76.5320,
                'canal_recepcion' => 'web',
                'prioridad' => 'urgente',
                'estado' => 'recibida',
                'es_anonima' => false,
                'days_ago' => 1,
            ],
            [
                'tipo_denuncia' => 'abandono',
                'descripcion' => 'Hay una camada de gatitos abandonados en una caja en el parque. Llevan varios dias ahi.',
                'ubicacion' => 'Parque de la Flora, entrada principal, Comuna 3',
                'latitud' => 3.4412,
                'longitud' => -76.5198,
                'canal_recepcion' => 'whatsapp',
                'prioridad' => 'alta',
                'estado' => 'recibida',
                'es_anonima' => false,
                'days_ago' => 0,
            ],
            [
                'tipo_denuncia' => 'animal_herido',
                'descripcion' => 'Perro atropellado en la via. No puede caminar y esta sangrando.',
                'ubicacion' => 'Carrera 80 con Calle 5, sector Melendez',
                'latitud' => 3.3789,
                'longitud' => -76.5456,
                'canal_recepcion' => 'telefono',
                'prioridad' => 'urgente',
                'estado' => 'recibida',
                'es_anonima' => true,
                'days_ago' => 0,
            ],

            // --- EN REVISION ---
            [
                'tipo_denuncia' => 'maltrato',
                'descripcion' => 'Persona pateando a un perro callejero en plena calle. Varios testigos.',
                'ubicacion' => 'Calle 70 #1A-25, Barrio Tequendama, Comuna 19',
                'latitud' => 3.4123,
                'longitud' => -76.5567,
                'canal_recepcion' => 'presencial',
                'prioridad' => 'alta',
                'estado' => 'en_revision',
                'es_anonima' => false,
                'days_ago' => 3,
            ],
            [
                'tipo_denuncia' => 'animal_peligroso',
                'descripcion' => 'Perro de raza grande suelto en zona residencial, ha intentado morder a ninos.',
                'ubicacion' => 'Manzana F, Barrio Alfonso Lopez, Comuna 7',
                'latitud' => 3.4789,
                'longitud' => -76.4987,
                'canal_recepcion' => 'web',
                'prioridad' => 'urgente',
                'estado' => 'en_revision',
                'es_anonima' => false,
                'days_ago' => 2,
            ],

            // --- ASIGNADAS ---
            [
                'tipo_denuncia' => 'abandono',
                'descripcion' => 'Familia se mudo y dejo al perro encerrado en la casa. Ya lleva 3 dias solo.',
                'ubicacion' => 'Carrera 56 #12-78, Barrio Granada, Comuna 2',
                'latitud' => 3.4567,
                'longitud' => -76.5234,
                'canal_recepcion' => 'email',
                'prioridad' => 'alta',
                'estado' => 'asignada',
                'es_anonima' => false,
                'days_ago' => 5,
                'asignar' => true,
            ],

            // --- EN ATENCION ---
            [
                'tipo_denuncia' => 'maltrato',
                'descripcion' => 'Casa con mas de 20 gatos en condiciones de hacinamiento. Mal olor y animales enfermos.',
                'ubicacion' => 'Carrera 25 #10-15, Barrio Obrero, Comuna 9',
                'latitud' => 3.4345,
                'longitud' => -76.5123,
                'canal_recepcion' => 'web',
                'prioridad' => 'alta',
                'estado' => 'en_atencion',
                'es_anonima' => false,
                'days_ago' => 10,
                'asignar' => true,
            ],
            [
                'tipo_denuncia' => 'animal_herido',
                'descripcion' => 'Gato con una pata herida debajo de un puente. No se deja atrapar.',
                'ubicacion' => 'Puente de la Calle 5 con Rio Cali, Comuna 3',
                'latitud' => 3.4501,
                'longitud' => -76.5289,
                'canal_recepcion' => 'telefono',
                'prioridad' => 'media',
                'estado' => 'en_atencion',
                'es_anonima' => false,
                'days_ago' => 7,
                'asignar' => true,
            ],

            // --- RESUELTAS ---
            [
                'tipo_denuncia' => 'animal_herido',
                'descripcion' => 'Perro atrapado en un hueco en construccion abandonada. No puede salir solo.',
                'ubicacion' => 'Lote baldio Carrera 80 con Calle 5, Comuna 17',
                'latitud' => 3.3812,
                'longitud' => -76.5467,
                'canal_recepcion' => 'web',
                'prioridad' => 'urgente',
                'estado' => 'resuelta',
                'es_anonima' => false,
                'days_ago' => 15,
                'asignar' => true,
                'observaciones_resolucion' => 'Animal rescatado exitosamente y trasladado al refugio para atencion veterinaria.',
            ],
            [
                'tipo_denuncia' => 'abandono',
                'descripcion' => 'Perro abandonado amarrado a un poste en el parque del barrio.',
                'ubicacion' => 'Parque del Barrio Petecuy, Comuna 6',
                'latitud' => 3.4678,
                'longitud' => -76.5045,
                'canal_recepcion' => 'whatsapp',
                'prioridad' => 'media',
                'estado' => 'resuelta',
                'es_anonima' => false,
                'days_ago' => 25,
                'asignar' => true,
                'observaciones_resolucion' => 'Perro rescatado. Se encuentra en el refugio en buen estado.',
            ],
            [
                'tipo_denuncia' => 'maltrato',
                'descripcion' => 'Dueno golpeando a su perro con un palo en la calle.',
                'ubicacion' => 'Calle 44 #3-56, Barrio San Antonio, Comuna 3',
                'latitud' => 3.4456,
                'longitud' => -76.5345,
                'canal_recepcion' => 'presencial',
                'prioridad' => 'alta',
                'estado' => 'resuelta',
                'es_anonima' => true,
                'days_ago' => 40,
                'asignar' => true,
                'observaciones_resolucion' => 'Intervencion realizada. Animal decomisado y llevado a refugio. Se inicio proceso legal.',
            ],

            // --- CERRADAS ---
            [
                'tipo_denuncia' => 'otro',
                'descripcion' => 'Gatos callejeros haciendo ruido por la noche en el barrio.',
                'ubicacion' => 'Barrio El Vallado, Comuna 15',
                'latitud' => 3.4234,
                'longitud' => -76.4901,
                'canal_recepcion' => 'telefono',
                'prioridad' => 'baja',
                'estado' => 'cerrada',
                'es_anonima' => true,
                'days_ago' => 50,
                'asignar' => true,
                'observaciones_resolucion' => 'Situacion normal de gatos callejeros. Se programo campana de esterilizacion en la zona.',
            ],
            [
                'tipo_denuncia' => 'abandono',
                'descripcion' => 'Perro callejero viviendo en lote baldio del barrio.',
                'ubicacion' => 'Lote en Calle 48 #25-00, Comuna 8',
                'latitud' => 3.4590,
                'longitud' => -76.5156,
                'canal_recepcion' => 'web',
                'prioridad' => 'baja',
                'estado' => 'cerrada',
                'es_anonima' => false,
                'days_ago' => 60,
                'asignar' => true,
                'observaciones_resolucion' => 'Perro rescatado y en proceso de adopcion.',
            ],

            // --- DESESTIMADA ---
            [
                'tipo_denuncia' => 'otro',
                'descripcion' => 'Solicitud de recoger perro propio que se escapo.',
                'ubicacion' => 'Barrio Calipso, Comuna 13',
                'latitud' => 3.4345,
                'longitud' => -76.4978,
                'canal_recepcion' => 'telefono',
                'prioridad' => 'baja',
                'estado' => 'desestimada',
                'es_anonima' => false,
                'days_ago' => 30,
                'observaciones_resolucion' => 'No corresponde a denuncia de bienestar animal. Se oriento al ciudadano.',
            ],
        ];

        foreach ($denunciasData as $index => $data) {
            $denunciante = $denunciantes[$index % count($denunciantes)];
            $daysAgo = $data['days_ago'];
            $asignar = $data['asignar'] ?? false;
            $observacionesResolucion = $data['observaciones_resolucion'] ?? null;

            unset($data['days_ago'], $data['asignar'], $data['observaciones_resolucion']);

            $fechaDenuncia = now()->subDays($daysAgo);

            $responsableId = null;
            $fechaAsignacion = null;
            $fechaResolucion = null;

            if ($asignar && $operadores->isNotEmpty()) {
                $responsableId = $operadores->random()->id;
                $fechaAsignacion = $fechaDenuncia->copy()->addHours(rand(1, 24));
            }

            if (in_array($data['estado'], ['resuelta', 'cerrada'])) {
                $fechaResolucion = $fechaDenuncia->copy()->addDays(rand(1, 5));
            }

            Denuncia::create(array_merge($data, [
                'id' => (string) Str::uuid(),
                'denunciante_id' => $data['es_anonima'] ? null : $denunciante->id,
                'fecha_denuncia' => $fechaDenuncia,
                'responsable_id' => $responsableId,
                'fecha_asignacion' => $fechaAsignacion,
                'fecha_resolucion' => $fechaResolucion,
                'observaciones_resolucion' => $observacionesResolucion,
            ]));
        }
    }
}
