<?php

namespace Database\Seeders;

use App\Models\Denuncias\Denunciante;
use App\Models\Denuncias\Denuncia;
use App\Models\User\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DenunciaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear denunciantes
        $denunciantes = [
            [
                'nombre_completo' => 'Maria Fernanda Gonzalez',
                'tipo_identificacion' => 'cedula',
                'numero_identificacion' => '1144567890',
                'telefono' => '3151234567',
                'email' => 'maria.gonzalez@email.com',
                'direccion' => 'Calle 15 #45-32, Barrio San Fernando',
            ],
            [
                'nombre_completo' => 'Juan Carlos Perez',
                'tipo_identificacion' => 'cedula',
                'numero_identificacion' => '16789012',
                'telefono' => '3162345678',
                'email' => 'juanc.perez@email.com',
                'direccion' => 'Carrera 100 #13-25, Barrio Ciudad Jardin',
            ],
            [
                'nombre_completo' => 'Ana Lucia Martinez',
                'tipo_identificacion' => 'cedula',
                'numero_identificacion' => '31456789',
                'telefono' => '3173456789',
                'email' => null, // Denuncia anonima parcial
                'direccion' => null,
            ],
            [
                'nombre_completo' => null, // Denuncia completamente anonima
                'tipo_identificacion' => null,
                'numero_identificacion' => null,
                'telefono' => '3184567890',
                'email' => null,
                'direccion' => null,
            ],
        ];

        $denunciantesCreados = [];
        foreach ($denunciantes as $data) {
            $denunciantesCreados[] = Denunciante::create(array_merge($data, [
                'id' => (string) Str::uuid(),
            ]));
        }

        // Obtener operadores para asignar
        $operadores = Usuario::whereHas('rol', function ($q) {
            $q->where('codigo', 'OPERADOR');
        })->get();

        // Crear denuncias
        $denuncias = [
            [
                'tipo_denuncia' => 'maltrato',
                'descripcion' => 'Vecino tiene un perro amarrado todo el dia en el sol sin agua ni comida. El animal se ve muy delgado y tiene heridas en el cuello por la cadena.',
                'direccion' => 'Calle 23 #67-45, Barrio Alameda',
                'comuna' => 'Comuna 19',
                'barrio' => 'Alameda',
                'punto_referencia' => 'Frente a la panaderia La Espiga',
                'latitud' => 3.4516,
                'longitud' => -76.5320,
                'prioridad' => 'urgente',
                'estado' => 'en_proceso',
            ],
            [
                'tipo_denuncia' => 'abandono',
                'descripcion' => 'Hay una camada de gatitos abandonados en una caja en el parque. Llevan varios dias ahi y nadie los ha recogido.',
                'direccion' => 'Parque de la Flora, entrada principal',
                'comuna' => 'Comuna 3',
                'barrio' => 'San Nicolas',
                'punto_referencia' => 'Al lado de los columpios',
                'latitud' => 3.4412,
                'longitud' => -76.5198,
                'prioridad' => 'alta',
                'estado' => 'recibida',
            ],
            [
                'tipo_denuncia' => 'tenencia_irresponsable',
                'descripcion' => 'Casa con mas de 20 gatos en condiciones de hacinamiento. Hay mal olor y los animales se ven enfermos.',
                'direccion' => 'Carrera 25 #10-15, Barrio Obrero',
                'comuna' => 'Comuna 9',
                'barrio' => 'Obrero',
                'punto_referencia' => 'Casa esquinera amarilla',
                'latitud' => 3.4567,
                'longitud' => -76.5234,
                'prioridad' => 'alta',
                'estado' => 'en_proceso',
            ],
            [
                'tipo_denuncia' => 'animal_peligro',
                'descripcion' => 'Perro atrapado en un hueco en construccion abandonada. No puede salir solo.',
                'direccion' => 'Lote baldio Carrera 80 con Calle 5',
                'comuna' => 'Comuna 17',
                'barrio' => 'Melendez',
                'punto_referencia' => 'Frente al supermercado Exito',
                'latitud' => 3.3789,
                'longitud' => -76.5456,
                'prioridad' => 'urgente',
                'estado' => 'resuelta',
            ],
            [
                'tipo_denuncia' => 'maltrato',
                'descripcion' => 'Persona pateando a un perro callejero en la calle.',
                'direccion' => 'Calle 70 #1A-25, Barrio Tequendama',
                'comuna' => 'Comuna 19',
                'barrio' => 'Tequendama',
                'punto_referencia' => 'Cerca del CAI de policia',
                'latitud' => 3.4123,
                'longitud' => -76.5567,
                'prioridad' => 'alta',
                'estado' => 'recibida',
            ],
        ];

        foreach ($denuncias as $index => $denunciaData) {
            $denunciante = $denunciantesCreados[$index % count($denunciantesCreados)];

            $denuncia = Denuncia::create(array_merge($denunciaData, [
                'id' => (string) Str::uuid(),
                'numero_ticket' => 'DEN-' . now()->format('Ymd') . '-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'denunciante_id' => $denunciante->id,
                'fecha_denuncia' => now()->subDays(rand(1, 30)),
                'asignado_a' => $denunciaData['estado'] !== 'recibida' && $operadores->isNotEmpty()
                    ? $operadores->random()->id
                    : null,
                'fecha_asignacion' => $denunciaData['estado'] !== 'recibida'
                    ? now()->subDays(rand(1, 5))
                    : null,
                'fecha_resolucion' => $denunciaData['estado'] === 'resuelta'
                    ? now()->subDays(rand(1, 3))
                    : null,
                'resolucion' => $denunciaData['estado'] === 'resuelta'
                    ? 'Animal rescatado exitosamente y trasladado al refugio para atencion veterinaria.'
                    : null,
            ]));
        }
    }
}
