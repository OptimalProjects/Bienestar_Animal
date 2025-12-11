<?php

namespace Database\Seeders;

use App\Models\Animal\Animal;
use App\Models\Animal\HistorialClinico;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AnimalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $animales = [
            // Perros
            [
                'nombre' => 'Max',
                'especie' => 'perro',
                'raza' => 'Golden Retriever',
                'sexo' => 'macho',
                'edad_aproximada' => 24,
                'peso_actual' => 28.5,
                'color' => 'Dorado',
                'tamanio' => 'grande',
                'estado' => 'en_adopcion',
                'estado_salud' => 'excelente',
                'observaciones' => 'Perro muy carinoso y jugueton, ideal para familias con ninos. Esterilizado, vacunado y desparasitado.',
            ],
            [
                'nombre' => 'Luna',
                'especie' => 'perro',
                'raza' => 'Mestizo',
                'sexo' => 'hembra',
                'edad_aproximada' => 12,
                'peso_actual' => 15.0,
                'color' => 'Negro con manchas blancas',
                'tamanio' => 'mediano',
                'estado' => 'en_adopcion',
                'estado_salud' => 'bueno',
                'observaciones' => 'Cachorra rescatada de la calle, muy sociable. Esterilizada, vacunada y desparasitada.',
            ],
            [
                'nombre' => 'Rocky',
                'especie' => 'perro',
                'raza' => 'Pitbull',
                'sexo' => 'macho',
                'edad_aproximada' => 36,
                'peso_actual' => 32.0,
                'color' => 'Atigrado',
                'tamanio' => 'grande',
                'estado' => 'en_tratamiento',
                'estado_salud' => 'estable',
                'observaciones' => 'Rescatado de situacion de maltrato, en recuperacion. Esterilizado y desparasitado.',
            ],
            [
                'nombre' => 'Bella',
                'especie' => 'perro',
                'raza' => 'Labrador',
                'sexo' => 'hembra',
                'edad_aproximada' => 48,
                'peso_actual' => 25.0,
                'color' => 'Chocolate',
                'tamanio' => 'grande',
                'estado' => 'en_adopcion',
                'estado_salud' => 'excelente',
                'observaciones' => 'Perra tranquila, ideal para personas mayores. Esterilizada, vacunada y desparasitada.',
            ],
            [
                'nombre' => 'Thor',
                'especie' => 'perro',
                'raza' => 'Pastor Aleman',
                'sexo' => 'macho',
                'edad_aproximada' => 18,
                'peso_actual' => 35.0,
                'color' => 'Negro y cafe',
                'tamanio' => 'muy_grande',
                'estado' => 'en_refugio',
                'estado_salud' => 'bueno',
                'observaciones' => 'Perro guardian, necesita espacio amplio. Vacunado y desparasitado.',
            ],
            [
                'nombre' => 'Toby',
                'especie' => 'perro',
                'raza' => 'French Poodle',
                'sexo' => 'macho',
                'edad_aproximada' => 60,
                'peso_actual' => 8.0,
                'color' => 'Blanco',
                'tamanio' => 'pequenio',
                'estado' => 'en_adopcion',
                'estado_salud' => 'bueno',
                'observaciones' => 'Perro senior muy dulce, busca hogar tranquilo. Esterilizado, vacunado y desparasitado.',
            ],
            [
                'nombre' => 'Nina',
                'especie' => 'perro',
                'raza' => 'Mestizo pequeno',
                'sexo' => 'hembra',
                'edad_aproximada' => 6,
                'peso_actual' => 5.5,
                'color' => 'Cafe claro',
                'tamanio' => 'pequenio',
                'estado' => 'en_refugio',
                'estado_salud' => 'estable',
                'observaciones' => 'Cachorra recien llegada en periodo de observacion.',
            ],

            // Gatos
            [
                'nombre' => 'Michi',
                'especie' => 'gato',
                'raza' => 'Mestizo',
                'sexo' => 'macho',
                'edad_aproximada' => 24,
                'peso_actual' => 4.5,
                'color' => 'Naranja atigrado',
                'tamanio' => 'pequenio',
                'estado' => 'en_adopcion',
                'estado_salud' => 'excelente',
                'observaciones' => 'Gato muy carinoso, le gusta dormir en el sol. Esterilizado, vacunado y desparasitado.',
            ],
            [
                'nombre' => 'Negrita',
                'especie' => 'gato',
                'raza' => 'Mestizo',
                'sexo' => 'hembra',
                'edad_aproximada' => 12,
                'peso_actual' => 3.2,
                'color' => 'Negro',
                'tamanio' => 'pequenio',
                'estado' => 'en_adopcion',
                'estado_salud' => 'bueno',
                'observaciones' => 'Gata timida pero muy dulce una vez toma confianza. Esterilizada, vacunada y desparasitada.',
            ],
            [
                'nombre' => 'Simba',
                'especie' => 'gato',
                'raza' => 'Persa',
                'sexo' => 'macho',
                'edad_aproximada' => 36,
                'peso_actual' => 5.0,
                'color' => 'Gris',
                'tamanio' => 'pequenio',
                'estado' => 'en_tratamiento',
                'estado_salud' => 'estable',
                'observaciones' => 'Gato rescatado con problemas respiratorios, en tratamiento. Esterilizado y desparasitado.',
            ],
            [
                'nombre' => 'Pelusa',
                'especie' => 'gato',
                'raza' => 'Angora',
                'sexo' => 'hembra',
                'edad_aproximada' => 18,
                'peso_actual' => 3.8,
                'color' => 'Blanco',
                'tamanio' => 'pequenio',
                'estado' => 'en_adopcion',
                'estado_salud' => 'excelente',
                'observaciones' => 'Gata muy elegante, ideal para departamento. Esterilizada, vacunada y desparasitada.',
            ],
            [
                'nombre' => 'Garfield',
                'especie' => 'gato',
                'raza' => 'Mestizo',
                'sexo' => 'macho',
                'edad_aproximada' => 48,
                'peso_actual' => 6.5,
                'color' => 'Naranja',
                'tamanio' => 'mediano',
                'estado' => 'adoptado',
                'estado_salud' => 'bueno',
                'observaciones' => 'Gato tranquilo y gordito. Esterilizado, vacunado y desparasitado.',
            ],

            // Otros
            [
                'nombre' => 'Coco',
                'especie' => 'otro',
                'raza' => 'Conejo Mini Lop',
                'sexo' => 'macho',
                'edad_aproximada' => 12,
                'peso_actual' => 2.0,
                'color' => 'Blanco y gris',
                'tamanio' => 'pequenio',
                'estado' => 'en_adopcion',
                'estado_salud' => 'bueno',
                'senias_particulares' => 'Conejo',
                'observaciones' => 'Conejo muy docil, ideal como primera mascota. Esterilizado y desparasitado.',
            ],
        ];

        foreach ($animales as $animalData) {
            $codigo = 'AN-' . date('Y') . '-' . rand(10000, 99999);

            $animal = Animal::updateOrCreate(
                ['nombre' => $animalData['nombre'], 'especie' => $animalData['especie']],
                array_merge($animalData, [
                    'id' => (string) Str::uuid(),
                    'codigo_unico' => $codigo,
                    'fecha_rescate' => now()->subDays(rand(7, 180))->format('Y-m-d'),
                ])
            );

            // Crear historial clinico si no existe
            if (!$animal->historialClinico) {
                HistorialClinico::create([
                    'id' => (string) Str::uuid(),
                    'animal_id' => $animal->id,
                    'fecha_apertura' => $animal->fecha_rescate ?? now(),
                    'estado' => 'activo',
                ]);
            }
        }
    }
}
