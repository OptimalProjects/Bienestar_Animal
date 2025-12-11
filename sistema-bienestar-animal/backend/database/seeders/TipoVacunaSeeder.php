<?php

namespace Database\Seeders;

use App\Models\Veterinaria\TipoVacuna;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TipoVacunaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tiposVacuna = [
            // Vacunas para perros
            [
                'codigo' => 'VAC-CAN-RAB',
                'nombre' => 'Rabia Canina',
                'descripcion' => 'Vacuna antirabica obligatoria para caninos',
                'especie_aplicable' => 'perro',
                'edad_minima' => 3,
                'intervalo_dosis' => 365,
                'numero_dosis' => 1,
                'es_obligatoria' => true,
            ],
            [
                'codigo' => 'VAC-CAN-PAR',
                'nombre' => 'Parvovirus Canino',
                'descripcion' => 'Vacuna contra el parvovirus canino',
                'especie_aplicable' => 'perro',
                'edad_minima' => 2,
                'intervalo_dosis' => 365,
                'numero_dosis' => 3,
                'es_obligatoria' => true,
            ],
            [
                'codigo' => 'VAC-CAN-MOQ',
                'nombre' => 'Moquillo Canino',
                'descripcion' => 'Vacuna contra el distemper canino',
                'especie_aplicable' => 'perro',
                'edad_minima' => 2,
                'intervalo_dosis' => 365,
                'numero_dosis' => 3,
                'es_obligatoria' => true,
            ],
            [
                'codigo' => 'VAC-CAN-HEP',
                'nombre' => 'Hepatitis Canina',
                'descripcion' => 'Vacuna contra la hepatitis infecciosa canina',
                'especie_aplicable' => 'perro',
                'edad_minima' => 2,
                'intervalo_dosis' => 365,
                'numero_dosis' => 2,
                'es_obligatoria' => false,
            ],
            [
                'codigo' => 'VAC-CAN-LEP',
                'nombre' => 'Leptospirosis',
                'descripcion' => 'Vacuna contra la leptospirosis',
                'especie_aplicable' => 'perro',
                'edad_minima' => 3,
                'intervalo_dosis' => 180,
                'numero_dosis' => 2,
                'es_obligatoria' => false,
            ],
            [
                'codigo' => 'VAC-CAN-POL',
                'nombre' => 'Polivalente Canina (Puppy)',
                'descripcion' => 'Vacuna polivalente para cachorros',
                'especie_aplicable' => 'perro',
                'edad_minima' => 1,
                'intervalo_dosis' => 21,
                'numero_dosis' => 3,
                'es_obligatoria' => true,
            ],

            // Vacunas para gatos
            [
                'codigo' => 'VAC-FEL-RAB',
                'nombre' => 'Rabia Felina',
                'descripcion' => 'Vacuna antirabica obligatoria para felinos',
                'especie_aplicable' => 'gato',
                'edad_minima' => 3,
                'intervalo_dosis' => 365,
                'numero_dosis' => 1,
                'es_obligatoria' => true,
            ],
            [
                'codigo' => 'VAC-FEL-TRI',
                'nombre' => 'Triple Felina',
                'descripcion' => 'Vacuna contra panleucopenia, calicivirus y rinotraqueitis',
                'especie_aplicable' => 'gato',
                'edad_minima' => 2,
                'intervalo_dosis' => 365,
                'numero_dosis' => 2,
                'es_obligatoria' => true,
            ],
            [
                'codigo' => 'VAC-FEL-LEU',
                'nombre' => 'Leucemia Felina',
                'descripcion' => 'Vacuna contra el virus de leucemia felina',
                'especie_aplicable' => 'gato',
                'edad_minima' => 2,
                'intervalo_dosis' => 365,
                'numero_dosis' => 2,
                'es_obligatoria' => false,
            ],
            [
                'codigo' => 'VAC-FEL-PAN',
                'nombre' => 'Panleucopenia Felina',
                'descripcion' => 'Vacuna contra la panleucopenia felina',
                'especie_aplicable' => 'gato',
                'edad_minima' => 2,
                'intervalo_dosis' => 365,
                'numero_dosis' => 2,
                'es_obligatoria' => true,
            ],
        ];

        foreach ($tiposVacuna as $tipo) {
            TipoVacuna::updateOrCreate(
                ['codigo' => $tipo['codigo']],
                array_merge($tipo, [
                    'id' => (string) Str::uuid(),
                    'activo' => true,
                ])
            );
        }
    }
}
