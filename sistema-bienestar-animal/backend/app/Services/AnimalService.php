<?php

namespace App\Services;

use App\Repositories\Contracts\AnimalRepositoryInterface;
use App\Models\Animal\Animal;
use App\Models\Animal\HistorialClinico;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AnimalService
{
    public function __construct(
        protected AnimalRepositoryInterface $animalRepository
    ) {}

    /**
     * Listar animales con paginacion y filtros.
     */
    public function listar(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->animalRepository->paginateWithFilters($perPage, $filters);
    }

    /**
     * Obtener catalogo de adopcion (publico).
     */
    public function getCatalogoAdopcion(): Collection
    {
        return $this->animalRepository->getDisponiblesAdopcion();
    }

    /**
     * Obtener animal por ID con historial clinico.
     */
    public function obtenerConHistorial(string $id)
    {
        return $this->animalRepository->getWithHistorialClinico($id);
    }

    /**
     * Registrar nuevo animal.
     */
    public function registrar(array $data, ?string $usuarioId = null): Animal
    {
        return DB::transaction(function () use ($data, $usuarioId) {
            // Procesar foto si existe
            if (isset($data['foto_principal']) && $data['foto_principal']) {
                $data['foto_principal'] = $this->guardarFoto($data['foto_principal']);
            }

            // Procesar galeria si existe
            if (isset($data['galeria_fotos']) && is_array($data['galeria_fotos'])) {
                $data['galeria_fotos'] = $this->guardarGaleria($data['galeria_fotos']);
            }

            // Agregar usuario creador
            if ($usuarioId) {
                $data['created_by'] = $usuarioId;
            }

            // Crear animal
            $animal = $this->animalRepository->create($data);

            // Crear historial clinico vacio
            HistorialClinico::create([
                'animal_id' => $animal->id,
                'estado_general' => $data['estado_salud'] ?? 'pendiente_evaluacion',
            ]);

            return $animal->fresh(['historialClinico']);
        });
    }

    /**
     * Actualizar animal.
     */
    public function actualizar(string $id, array $data, ?string $usuarioId = null): Animal
    {
        return DB::transaction(function () use ($id, $data, $usuarioId) {
            $animal = $this->animalRepository->findByIdOrFail($id);

            // Procesar foto si existe nueva
            if (isset($data['foto_principal']) && $data['foto_principal']) {
                // Eliminar foto anterior
                if ($animal->foto_principal) {
                    Storage::disk('public')->delete($animal->foto_principal);
                }
                $data['foto_principal'] = $this->guardarFoto($data['foto_principal']);
            }

            // Agregar usuario actualizador
            if ($usuarioId) {
                $data['updated_by'] = $usuarioId;
            }

            return $this->animalRepository->update($id, $data);
        });
    }

    /**
     * Eliminar animal (soft delete).
     */
    public function eliminar(string $id): bool
    {
        return $this->animalRepository->delete($id);
    }

    /**
     * Cambiar estado del animal.
     */
    public function cambiarEstado(string $id, string $nuevoEstado, ?string $usuarioId = null): Animal
    {
        $estadosValidos = ['en_refugio', 'en_tratamiento', 'en_adopcion', 'adoptado', 'fallecido', 'liberado'];

        if (!in_array($nuevoEstado, $estadosValidos)) {
            throw new \InvalidArgumentException("Estado no valido: {$nuevoEstado}");
        }

        $data = ['estado' => $nuevoEstado];
        if ($usuarioId) {
            $data['updated_by'] = $usuarioId;
        }

        return $this->animalRepository->update($id, $data);
    }

    /**
     * Buscar animal por chip.
     */
    public function buscarPorChip(string $chip)
    {
        return $this->animalRepository->findByChip($chip);
    }

    /**
     * Obtener estadisticas.
     */
    public function getEstadisticas(): array
    {
        return $this->animalRepository->getEstadisticas();
    }

    /**
     * Guardar foto en storage.
     */
    protected function guardarFoto($foto): string
    {
        $path = $foto->store('animales/fotos', 'public');
        return $path;
    }

    /**
     * Guardar galeria de fotos.
     */
    protected function guardarGaleria(array $fotos): array
    {
        $paths = [];
        foreach ($fotos as $foto) {
            $paths[] = $foto->store('animales/galeria', 'public');
        }
        return $paths;
    }
}
