<?php

namespace Database\Seeders;

use App\Models\Animal\Animal;
use App\Models\Animal\HistorialClinico;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AnimalSeeder extends Seeder
{
    public function run(): void
    {
        $animales = [
            // =========================================
            // PERROS EN REFUGIO (10)
            // =========================================
            ['nombre' => 'Max', 'especie' => 'perro', 'raza' => 'Golden Retriever', 'sexo' => 'macho', 'edad_aproximada' => 24, 'peso_actual' => 28.5, 'color' => 'Dorado', 'tamanio' => 'grande', 'estado' => 'en_refugio', 'estado_salud' => 'bueno', 'esterilizacion' => true, 'observaciones' => 'Perro carinoso y jugueton. Esterilizado y desparasitado.', 'days_ago' => 120],
            ['nombre' => 'Thor', 'especie' => 'perro', 'raza' => 'Pastor Aleman', 'sexo' => 'macho', 'edad_aproximada' => 18, 'peso_actual' => 35.0, 'color' => 'Negro y cafe', 'tamanio' => 'muy_grande', 'estado' => 'en_refugio', 'estado_salud' => 'bueno', 'esterilizacion' => false, 'observaciones' => 'Perro guardian, necesita espacio amplio.', 'days_ago' => 90],
            ['nombre' => 'Nina', 'especie' => 'perro', 'raza' => 'Mestizo', 'sexo' => 'hembra', 'edad_aproximada' => 6, 'peso_actual' => 5.5, 'color' => 'Cafe claro', 'tamanio' => 'pequenio', 'estado' => 'en_refugio', 'estado_salud' => 'estable', 'esterilizacion' => false, 'observaciones' => 'Cachorra recien llegada en periodo de observacion.', 'days_ago' => 15],
            ['nombre' => 'Capitan', 'especie' => 'perro', 'raza' => 'Rottweiler', 'sexo' => 'macho', 'edad_aproximada' => 48, 'peso_actual' => 40.0, 'color' => 'Negro con cafe', 'tamanio' => 'muy_grande', 'estado' => 'en_refugio', 'estado_salud' => 'bueno', 'esterilizacion' => true, 'observaciones' => 'Rescatado de situacion de abandono. Temperamento docil.', 'days_ago' => 60],
            ['nombre' => 'Manchas', 'especie' => 'perro', 'raza' => 'Dalmata', 'sexo' => 'hembra', 'edad_aproximada' => 14, 'peso_actual' => 22.0, 'color' => 'Blanco con manchas negras', 'tamanio' => 'grande', 'estado' => 'en_refugio', 'estado_salud' => 'excelente', 'esterilizacion' => true, 'observaciones' => 'Perrita muy energetica y juguetona.', 'days_ago' => 45],
            ['nombre' => 'Pepe', 'especie' => 'perro', 'raza' => 'Schnauzer', 'sexo' => 'macho', 'edad_aproximada' => 72, 'peso_actual' => 7.0, 'color' => 'Gris', 'tamanio' => 'pequenio', 'estado' => 'en_refugio', 'estado_salud' => 'estable', 'esterilizacion' => true, 'observaciones' => 'Perro senior, tranquilo. Necesita chequeos periodicos.', 'days_ago' => 100],
            ['nombre' => 'Estrella', 'especie' => 'perro', 'raza' => 'Mestizo', 'sexo' => 'hembra', 'edad_aproximada' => 10, 'peso_actual' => 12.0, 'color' => 'Blanco', 'tamanio' => 'mediano', 'estado' => 'en_refugio', 'estado_salud' => 'bueno', 'esterilizacion' => false, 'observaciones' => 'Rescatada de la calle en sector Aguablanca. Docil.', 'days_ago' => 25],
            ['nombre' => 'Draco', 'especie' => 'perro', 'raza' => 'Pitbull', 'sexo' => 'macho', 'edad_aproximada' => 30, 'peso_actual' => 30.0, 'color' => 'Atigrado', 'tamanio' => 'grande', 'estado' => 'en_refugio', 'estado_salud' => 'bueno', 'esterilizacion' => true, 'observaciones' => 'Perro rescatado, muy sociable con personas. Requiere hogar sin gatos.', 'days_ago' => 70],
            ['nombre' => 'Copito', 'especie' => 'perro', 'raza' => 'Mestizo', 'sexo' => 'macho', 'edad_aproximada' => 4, 'peso_actual' => 3.5, 'color' => 'Blanco', 'tamanio' => 'pequenio', 'estado' => 'en_refugio', 'estado_salud' => 'estable', 'esterilizacion' => false, 'observaciones' => 'Cachorro muy pequeno, encontrado abandonado en caja.', 'days_ago' => 10],
            ['nombre' => 'Sombra', 'especie' => 'perro', 'raza' => 'Border Collie', 'sexo' => 'hembra', 'edad_aproximada' => 20, 'peso_actual' => 18.0, 'color' => 'Negro con blanco', 'tamanio' => 'mediano', 'estado' => 'en_refugio', 'estado_salud' => 'bueno', 'esterilizacion' => true, 'observaciones' => 'Perra muy inteligente y activa. Necesita estimulacion mental.', 'days_ago' => 55],

            // =========================================
            // PERROS EN ADOPCION (8)
            // =========================================
            ['nombre' => 'Luna', 'especie' => 'perro', 'raza' => 'Mestizo', 'sexo' => 'hembra', 'edad_aproximada' => 12, 'peso_actual' => 15.0, 'color' => 'Negro con manchas blancas', 'tamanio' => 'mediano', 'estado' => 'en_adopcion', 'estado_salud' => 'bueno', 'esterilizacion' => true, 'observaciones' => 'Muy sociable, ideal para familias con ninos.', 'days_ago' => 80],
            ['nombre' => 'Bella', 'especie' => 'perro', 'raza' => 'Labrador', 'sexo' => 'hembra', 'edad_aproximada' => 48, 'peso_actual' => 25.0, 'color' => 'Chocolate', 'tamanio' => 'grande', 'estado' => 'en_adopcion', 'estado_salud' => 'excelente', 'esterilizacion' => true, 'observaciones' => 'Perra tranquila, ideal para personas mayores.', 'days_ago' => 95],
            ['nombre' => 'Toby', 'especie' => 'perro', 'raza' => 'French Poodle', 'sexo' => 'macho', 'edad_aproximada' => 60, 'peso_actual' => 8.0, 'color' => 'Blanco', 'tamanio' => 'pequenio', 'estado' => 'en_adopcion', 'estado_salud' => 'bueno', 'esterilizacion' => true, 'observaciones' => 'Perro senior muy dulce, busca hogar tranquilo.', 'days_ago' => 130],
            ['nombre' => 'Kiara', 'especie' => 'perro', 'raza' => 'Beagle', 'sexo' => 'hembra', 'edad_aproximada' => 16, 'peso_actual' => 12.0, 'color' => 'Tricolor', 'tamanio' => 'mediano', 'estado' => 'en_adopcion', 'estado_salud' => 'excelente', 'esterilizacion' => true, 'observaciones' => 'Perrita muy alegre y juguetona. Le encanta correr.', 'days_ago' => 40],
            ['nombre' => 'Bruno', 'especie' => 'perro', 'raza' => 'Mestizo', 'sexo' => 'macho', 'edad_aproximada' => 36, 'peso_actual' => 20.0, 'color' => 'Cafe', 'tamanio' => 'mediano', 'estado' => 'en_adopcion', 'estado_salud' => 'bueno', 'esterilizacion' => true, 'observaciones' => 'Perro rescatado de la calle. Muy leal y protector.', 'days_ago' => 65],
            ['nombre' => 'Canela', 'especie' => 'perro', 'raza' => 'Cocker Spaniel', 'sexo' => 'hembra', 'edad_aproximada' => 24, 'peso_actual' => 13.0, 'color' => 'Canela', 'tamanio' => 'mediano', 'estado' => 'en_adopcion', 'estado_salud' => 'excelente', 'esterilizacion' => true, 'observaciones' => 'Dulce y carinosa. Se lleva bien con otros perros.', 'days_ago' => 50],
            ['nombre' => 'Rex', 'especie' => 'perro', 'raza' => 'Mestizo grande', 'sexo' => 'macho', 'edad_aproximada' => 42, 'peso_actual' => 27.0, 'color' => 'Negro', 'tamanio' => 'grande', 'estado' => 'en_adopcion', 'estado_salud' => 'bueno', 'esterilizacion' => true, 'observaciones' => 'Perro guardián reformado. Excelente temperamento.', 'days_ago' => 75],
            ['nombre' => 'Abril', 'especie' => 'perro', 'raza' => 'Chihuahua', 'sexo' => 'hembra', 'edad_aproximada' => 30, 'peso_actual' => 2.5, 'color' => 'Crema', 'tamanio' => 'pequenio', 'estado' => 'en_adopcion', 'estado_salud' => 'bueno', 'esterilizacion' => true, 'observaciones' => 'Pequenita y mimada. Ideal para apartamento.', 'days_ago' => 35],

            // =========================================
            // PERROS ADOPTADOS (5)
            // =========================================
            ['nombre' => 'Duke', 'especie' => 'perro', 'raza' => 'Labrador', 'sexo' => 'macho', 'edad_aproximada' => 20, 'peso_actual' => 30.0, 'color' => 'Dorado', 'tamanio' => 'grande', 'estado' => 'adoptado', 'estado_salud' => 'excelente', 'esterilizacion' => true, 'observaciones' => 'Adoptado por familia del barrio Granada.', 'days_ago' => 150],
            ['nombre' => 'Princesa', 'especie' => 'perro', 'raza' => 'Shih Tzu', 'sexo' => 'hembra', 'edad_aproximada' => 36, 'peso_actual' => 5.5, 'color' => 'Blanco y cafe', 'tamanio' => 'pequenio', 'estado' => 'adoptado', 'estado_salud' => 'bueno', 'esterilizacion' => true, 'observaciones' => 'Adoptada por senora del barrio San Fernando.', 'days_ago' => 120],
            ['nombre' => 'Lola', 'especie' => 'perro', 'raza' => 'Mestizo', 'sexo' => 'hembra', 'edad_aproximada' => 15, 'peso_actual' => 14.0, 'color' => 'Cafe con blanco', 'tamanio' => 'mediano', 'estado' => 'adoptado', 'estado_salud' => 'excelente', 'esterilizacion' => true, 'observaciones' => 'Adoptada por familia joven con patio.', 'days_ago' => 90],
            ['nombre' => 'Apolo', 'especie' => 'perro', 'raza' => 'Bulldog', 'sexo' => 'macho', 'edad_aproximada' => 28, 'peso_actual' => 23.0, 'color' => 'Blanco con manchas cafe', 'tamanio' => 'mediano', 'estado' => 'adoptado', 'estado_salud' => 'bueno', 'esterilizacion' => true, 'observaciones' => 'Adoptado hace 2 meses. Seguimiento satisfactorio.', 'days_ago' => 110],
            ['nombre' => 'Zeus', 'especie' => 'perro', 'raza' => 'Mestizo', 'sexo' => 'macho', 'edad_aproximada' => 22, 'peso_actual' => 19.0, 'color' => 'Gris oscuro', 'tamanio' => 'mediano', 'estado' => 'adoptado', 'estado_salud' => 'bueno', 'esterilizacion' => true, 'observaciones' => 'Adoptado el mes pasado. En periodo de adaptacion.', 'days_ago' => 80],

            // =========================================
            // PERROS EN TRATAMIENTO (3)
            // =========================================
            ['nombre' => 'Rocky', 'especie' => 'perro', 'raza' => 'Pitbull', 'sexo' => 'macho', 'edad_aproximada' => 36, 'peso_actual' => 32.0, 'color' => 'Atigrado', 'tamanio' => 'grande', 'estado' => 'en_tratamiento', 'estado_salud' => 'estable', 'esterilizacion' => true, 'observaciones' => 'Rescatado de situacion de maltrato, heridas en recuperacion.', 'days_ago' => 20],
            ['nombre' => 'Firulais', 'especie' => 'perro', 'raza' => 'Mestizo', 'sexo' => 'macho', 'edad_aproximada' => 60, 'peso_actual' => 16.0, 'color' => 'Cafe oscuro', 'tamanio' => 'mediano', 'estado' => 'en_tratamiento', 'estado_salud' => 'grave', 'esterilizacion' => false, 'observaciones' => 'Atropellado en la via. Fractura en pata trasera derecha.', 'days_ago' => 8],
            ['nombre' => 'Motas', 'especie' => 'perro', 'raza' => 'Mestizo', 'sexo' => 'hembra', 'edad_aproximada' => 8, 'peso_actual' => 4.0, 'color' => 'Blanco con manchas negras', 'tamanio' => 'pequenio', 'estado' => 'en_tratamiento', 'estado_salud' => 'estable', 'esterilizacion' => false, 'observaciones' => 'Cachorra con parvovirus en tratamiento. Mejorando.', 'days_ago' => 5],

            // =========================================
            // PERROS EN CALLE (2)
            // =========================================
            ['nombre' => 'Tigre', 'especie' => 'perro', 'raza' => 'Mestizo', 'sexo' => 'macho', 'edad_aproximada' => 40, 'peso_actual' => 22.0, 'color' => 'Atigrado cafe', 'tamanio' => 'mediano', 'estado' => 'en_calle', 'estado_salud' => 'estable', 'esterilizacion' => null, 'observaciones' => 'Reportado en sector Siloe. Pendiente rescate.', 'days_ago' => 3],
            ['nombre' => 'Nala', 'especie' => 'perro', 'raza' => 'Mestizo', 'sexo' => 'hembra', 'edad_aproximada' => 18, 'peso_actual' => 11.0, 'color' => 'Negro', 'tamanio' => 'mediano', 'estado' => 'en_calle', 'estado_salud' => 'estable', 'esterilizacion' => null, 'observaciones' => 'Vista en sector Alfonso Lopez. Parece tener dueno pero deambula.', 'days_ago' => 7],

            // =========================================
            // PERRO FALLECIDO (1)
            // =========================================
            ['nombre' => 'Viejo', 'especie' => 'perro', 'raza' => 'Mestizo', 'sexo' => 'macho', 'edad_aproximada' => 168, 'peso_actual' => 10.0, 'color' => 'Gris', 'tamanio' => 'mediano', 'estado' => 'fallecido', 'estado_salud' => 'critico', 'esterilizacion' => true, 'observaciones' => 'Fallecio por edad avanzada. Estuvo en el refugio 3 anos.', 'days_ago' => 200],

            // =========================================
            // GATOS EN REFUGIO (5)
            // =========================================
            ['nombre' => 'Salem', 'especie' => 'gato', 'raza' => 'Mestizo', 'sexo' => 'macho', 'edad_aproximada' => 24, 'peso_actual' => 4.8, 'color' => 'Negro', 'tamanio' => 'pequenio', 'estado' => 'en_refugio', 'estado_salud' => 'bueno', 'esterilizacion' => true, 'observaciones' => 'Gato tranquilo y misterioso. Le gusta explorar.', 'days_ago' => 85],
            ['nombre' => 'Bigotes', 'especie' => 'gato', 'raza' => 'Mestizo', 'sexo' => 'macho', 'edad_aproximada' => 36, 'peso_actual' => 5.2, 'color' => 'Atigrado gris', 'tamanio' => 'pequenio', 'estado' => 'en_refugio', 'estado_salud' => 'bueno', 'esterilizacion' => true, 'observaciones' => 'Gato con bigotes muy largos y expresivos. Sociable.', 'days_ago' => 60],
            ['nombre' => 'Blanquita', 'especie' => 'gato', 'raza' => 'Angora', 'sexo' => 'hembra', 'edad_aproximada' => 14, 'peso_actual' => 3.5, 'color' => 'Blanco', 'tamanio' => 'pequenio', 'estado' => 'en_refugio', 'estado_salud' => 'excelente', 'esterilizacion' => true, 'observaciones' => 'Gata muy elegante y calmada. Pelo largo.', 'days_ago' => 30],
            ['nombre' => 'Pirata', 'especie' => 'gato', 'raza' => 'Mestizo', 'sexo' => 'macho', 'edad_aproximada' => 48, 'peso_actual' => 5.0, 'color' => 'Negro con parche blanco', 'tamanio' => 'pequenio', 'estado' => 'en_refugio', 'estado_salud' => 'bueno', 'esterilizacion' => true, 'senias_particulares' => 'Tiene un ojo cerrado (perdido antes del rescate)', 'observaciones' => 'A pesar de tener un solo ojo, es un gato muy activo.', 'days_ago' => 110],
            ['nombre' => 'Chispita', 'especie' => 'gato', 'raza' => 'Mestizo', 'sexo' => 'hembra', 'edad_aproximada' => 5, 'peso_actual' => 1.8, 'color' => 'Tricolor', 'tamanio' => 'pequenio', 'estado' => 'en_refugio', 'estado_salud' => 'estable', 'esterilizacion' => false, 'observaciones' => 'Gatita bebe rescatada con sus hermanos. Muy juguetona.', 'days_ago' => 12],

            // =========================================
            // GATOS EN ADOPCION (4)
            // =========================================
            ['nombre' => 'Michi', 'especie' => 'gato', 'raza' => 'Mestizo', 'sexo' => 'macho', 'edad_aproximada' => 24, 'peso_actual' => 4.5, 'color' => 'Naranja atigrado', 'tamanio' => 'pequenio', 'estado' => 'en_adopcion', 'estado_salud' => 'excelente', 'esterilizacion' => true, 'observaciones' => 'Gato muy carinoso, le gusta dormir en el sol.', 'days_ago' => 70],
            ['nombre' => 'Negrita', 'especie' => 'gato', 'raza' => 'Mestizo', 'sexo' => 'hembra', 'edad_aproximada' => 12, 'peso_actual' => 3.2, 'color' => 'Negro', 'tamanio' => 'pequenio', 'estado' => 'en_adopcion', 'estado_salud' => 'bueno', 'esterilizacion' => true, 'observaciones' => 'Gata timida pero muy dulce una vez toma confianza.', 'days_ago' => 55],
            ['nombre' => 'Pelusa', 'especie' => 'gato', 'raza' => 'Persa', 'sexo' => 'hembra', 'edad_aproximada' => 18, 'peso_actual' => 3.8, 'color' => 'Blanco', 'tamanio' => 'pequenio', 'estado' => 'en_adopcion', 'estado_salud' => 'excelente', 'esterilizacion' => true, 'observaciones' => 'Gata muy elegante, ideal para apartamento.', 'days_ago' => 40],
            ['nombre' => 'Oreo', 'especie' => 'gato', 'raza' => 'Mestizo', 'sexo' => 'macho', 'edad_aproximada' => 10, 'peso_actual' => 3.0, 'color' => 'Negro con blanco', 'tamanio' => 'pequenio', 'estado' => 'en_adopcion', 'estado_salud' => 'bueno', 'esterilizacion' => true, 'observaciones' => 'Gatito jugueton con patron de colores tipo oreo.', 'days_ago' => 25],

            // =========================================
            // GATOS ADOPTADOS (3)
            // =========================================
            ['nombre' => 'Garfield', 'especie' => 'gato', 'raza' => 'Mestizo', 'sexo' => 'macho', 'edad_aproximada' => 48, 'peso_actual' => 6.5, 'color' => 'Naranja', 'tamanio' => 'mediano', 'estado' => 'adoptado', 'estado_salud' => 'bueno', 'esterilizacion' => true, 'observaciones' => 'Gato tranquilo y gordito. Adoptado por estudiante universitario.', 'days_ago' => 140],
            ['nombre' => 'Nieve', 'especie' => 'gato', 'raza' => 'Siames', 'sexo' => 'hembra', 'edad_aproximada' => 20, 'peso_actual' => 3.5, 'color' => 'Crema con puntas oscuras', 'tamanio' => 'pequenio', 'estado' => 'adoptado', 'estado_salud' => 'excelente', 'esterilizacion' => true, 'observaciones' => 'Adoptada por pareja joven del barrio El Penon.', 'days_ago' => 100],
            ['nombre' => 'Felix', 'especie' => 'gato', 'raza' => 'Mestizo', 'sexo' => 'macho', 'edad_aproximada' => 30, 'peso_actual' => 4.2, 'color' => 'Negro con pecho blanco', 'tamanio' => 'pequenio', 'estado' => 'adoptado', 'estado_salud' => 'bueno', 'esterilizacion' => true, 'observaciones' => 'Gato muy independiente. Adoptado el mes pasado.', 'days_ago' => 75],

            // =========================================
            // GATOS EN TRATAMIENTO (2)
            // =========================================
            ['nombre' => 'Simba', 'especie' => 'gato', 'raza' => 'Persa', 'sexo' => 'macho', 'edad_aproximada' => 36, 'peso_actual' => 5.0, 'color' => 'Gris', 'tamanio' => 'pequenio', 'estado' => 'en_tratamiento', 'estado_salud' => 'estable', 'esterilizacion' => true, 'observaciones' => 'Problemas respiratorios en tratamiento.', 'days_ago' => 18],
            ['nombre' => 'Tigresa', 'especie' => 'gato', 'raza' => 'Bengal', 'sexo' => 'hembra', 'edad_aproximada' => 15, 'peso_actual' => 3.0, 'color' => 'Atigrado dorado', 'tamanio' => 'pequenio', 'estado' => 'en_tratamiento', 'estado_salud' => 'grave', 'esterilizacion' => false, 'observaciones' => 'Encontrada con heridas multiples. En cuidados intensivos.', 'days_ago' => 4],

            // =========================================
            // GATO EN CALLE (1)
            // =========================================
            ['nombre' => 'Pantera', 'especie' => 'gato', 'raza' => 'Mestizo', 'sexo' => 'macho', 'edad_aproximada' => 24, 'peso_actual' => 4.0, 'color' => 'Negro', 'tamanio' => 'pequenio', 'estado' => 'en_calle', 'estado_salud' => 'estable', 'esterilizacion' => null, 'observaciones' => 'Reportado en sector Calipso. Se alimenta de vecinos.', 'days_ago' => 5],

            // =========================================
            // GATO FALLECIDO (1)
            // =========================================
            ['nombre' => 'Tomas', 'especie' => 'gato', 'raza' => 'Mestizo', 'sexo' => 'macho', 'edad_aproximada' => 180, 'peso_actual' => 3.0, 'color' => 'Gris atigrado', 'tamanio' => 'pequenio', 'estado' => 'fallecido', 'estado_salud' => 'critico', 'esterilizacion' => true, 'observaciones' => 'Fallecio por insuficiencia renal. Gato muy querido en el refugio.', 'days_ago' => 160],

            // =========================================
            // OTROS (2)
            // =========================================
            ['nombre' => 'Coco', 'especie' => 'otro', 'raza' => 'Conejo Mini Lop', 'sexo' => 'macho', 'edad_aproximada' => 12, 'peso_actual' => 2.0, 'color' => 'Blanco y gris', 'tamanio' => 'pequenio', 'estado' => 'en_adopcion', 'estado_salud' => 'bueno', 'esterilizacion' => true, 'senias_particulares' => 'Orejas caidas caracteristicas de la raza', 'observaciones' => 'Conejo muy docil, ideal como primera mascota.', 'days_ago' => 45],
            ['nombre' => 'Pecas', 'especie' => 'otro', 'raza' => 'Hamster Sirio', 'sexo' => 'hembra', 'edad_aproximada' => 6, 'peso_actual' => 0.15, 'color' => 'Dorado', 'tamanio' => 'pequenio', 'estado' => 'en_refugio', 'estado_salud' => 'bueno', 'esterilizacion' => false, 'observaciones' => 'Encontrada en caja abandonada con otro hamster. Docil.', 'days_ago' => 14],
        ];

        foreach ($animales as $animalData) {
            $daysAgo = $animalData['days_ago'];
            unset($animalData['days_ago']);

            $animal = Animal::updateOrCreate(
                ['nombre' => $animalData['nombre'], 'especie' => $animalData['especie']],
                array_merge($animalData, [
                    'id' => (string) Str::uuid(),
                    'fecha_rescate' => now()->subDays($daysAgo)->format('Y-m-d'),
                ])
            );

            if (!$animal->historialClinico) {
                HistorialClinico::create([
                    'id' => (string) Str::uuid(),
                    'animal_id' => $animal->id,
                    'fecha_apertura' => $animal->fecha_rescate ?? now(),
                    'estado' => in_array($animal->estado, ['fallecido']) ? 'cerrado' : 'activo',
                ]);
            }
        }
    }
}
