<?php

namespace Database\Seeders;

use App\Models\User\Permiso;
use App\Models\User\Rol;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PermisoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * La tabla permisos usa columnas: recurso, accion, descripcion
     */
    public function run(): void
    {
        $permisos = [
            // Animales
            ['recurso' => 'animales', 'accion' => 'ver', 'descripcion' => 'Ver Animales'],
            ['recurso' => 'animales', 'accion' => 'crear', 'descripcion' => 'Crear Animales'],
            ['recurso' => 'animales', 'accion' => 'editar', 'descripcion' => 'Editar Animales'],
            ['recurso' => 'animales', 'accion' => 'eliminar', 'descripcion' => 'Eliminar Animales'],

            // Veterinaria
            ['recurso' => 'consultas', 'accion' => 'ver', 'descripcion' => 'Ver Consultas'],
            ['recurso' => 'consultas', 'accion' => 'crear', 'descripcion' => 'Crear Consultas'],
            ['recurso' => 'vacunas', 'accion' => 'aplicar', 'descripcion' => 'Aplicar Vacunas'],
            ['recurso' => 'cirugias', 'accion' => 'registrar', 'descripcion' => 'Registrar Cirugias'],
            ['recurso' => 'historial', 'accion' => 'ver', 'descripcion' => 'Ver Historial Clinico'],

            // Adopciones
            ['recurso' => 'adopciones', 'accion' => 'ver', 'descripcion' => 'Ver Adopciones'],
            ['recurso' => 'adopciones', 'accion' => 'crear', 'descripcion' => 'Crear Solicitudes'],
            ['recurso' => 'adopciones', 'accion' => 'evaluar', 'descripcion' => 'Evaluar Solicitudes'],
            ['recurso' => 'adopciones', 'accion' => 'aprobar', 'descripcion' => 'Aprobar Adopciones'],
            ['recurso' => 'visitas', 'accion' => 'programar', 'descripcion' => 'Programar Visitas'],

            // Denuncias
            ['recurso' => 'denuncias', 'accion' => 'ver', 'descripcion' => 'Ver Denuncias'],
            ['recurso' => 'denuncias', 'accion' => 'asignar', 'descripcion' => 'Asignar Denuncias'],
            ['recurso' => 'denuncias', 'accion' => 'resolver', 'descripcion' => 'Resolver Denuncias'],
            ['recurso' => 'rescates', 'accion' => 'registrar', 'descripcion' => 'Registrar Rescates'],

            // Administracion
            ['recurso' => 'usuarios', 'accion' => 'ver', 'descripcion' => 'Ver Usuarios'],
            ['recurso' => 'usuarios', 'accion' => 'crear', 'descripcion' => 'Crear Usuarios'],
            ['recurso' => 'usuarios', 'accion' => 'editar', 'descripcion' => 'Editar Usuarios'],
            ['recurso' => 'roles', 'accion' => 'gestionar', 'descripcion' => 'Gestionar Roles'],
            ['recurso' => 'reportes', 'accion' => 'ver', 'descripcion' => 'Ver Reportes'],
            ['recurso' => 'auditoria', 'accion' => 'ver', 'descripcion' => 'Ver Auditoria'],
            ['recurso' => 'inventario', 'accion' => 'gestionar', 'descripcion' => 'Gestionar Inventario'],
        ];

        foreach ($permisos as $permiso) {
            Permiso::updateOrCreate(
                ['recurso' => $permiso['recurso'], 'accion' => $permiso['accion']],
                array_merge($permiso, [
                    'id' => (string) Str::uuid(),
                ])
            );
        }

        // Asignar permisos a roles
        $this->asignarPermisosARoles();
    }

    protected function asignarPermisosARoles(): void
    {
        // Admin - todos los permisos
        $admin = Rol::where('codigo', 'ADMIN')->first();
        if ($admin) {
            $this->syncPermisosConUuid($admin, Permiso::pluck('id')->toArray());
        }

        // Director - todos los permisos excepto crear usuarios y gestionar roles
        $director = Rol::where('codigo', 'DIRECTOR')->first();
        if ($director) {
            $permisos = Permiso::where(function ($q) {
                $q->where('recurso', '!=', 'usuarios')->orWhere('accion', '!=', 'crear');
            })->where(function ($q) {
                $q->where('recurso', '!=', 'roles')->orWhere('accion', '!=', 'gestionar');
            })->pluck('id')->toArray();
            $this->syncPermisosConUuid($director, $permisos);
        }

        // Coordinador - permisos operativos
        $coordinador = Rol::where('codigo', 'COORDINADOR')->first();
        if ($coordinador) {
            $permisos = Permiso::where(function ($q) {
                $q->whereIn('recurso', ['animales', 'consultas', 'historial', 'adopciones', 'denuncias', 'reportes']);
            })->whereIn('accion', ['ver', 'crear', 'editar', 'evaluar', 'asignar'])->pluck('id')->toArray();
            $this->syncPermisosConUuid($coordinador, $permisos);
        }

        // Veterinario - permisos de veterinaria
        $veterinario = Rol::where('codigo', 'VETERINARIO')->first();
        if ($veterinario) {
            $permisos = Permiso::whereIn('recurso', ['animales', 'consultas', 'vacunas', 'cirugias', 'historial'])
                ->pluck('id')->toArray();
            $this->syncPermisosConUuid($veterinario, $permisos);
        }

        // Auxiliar Veterinario
        $auxiliar = Rol::where('codigo', 'AUXILIAR_VET')->first();
        if ($auxiliar) {
            $permisos = Permiso::where(function ($q) {
                $q->where('recurso', 'animales')->where('accion', 'ver');
            })->orWhere(function ($q) {
                $q->where('recurso', 'consultas')->where('accion', 'ver');
            })->orWhere(function ($q) {
                $q->whereIn('recurso', ['vacunas', 'historial']);
            })->pluck('id')->toArray();
            $this->syncPermisosConUuid($auxiliar, $permisos);
        }

        // Operador de Rescate
        $operador = Rol::where('codigo', 'OPERADOR')->first();
        if ($operador) {
            $permisos = Permiso::where(function ($q) {
                $q->where('recurso', 'animales')->whereIn('accion', ['ver', 'crear']);
            })->orWhere(function ($q) {
                $q->where('recurso', 'denuncias')->whereIn('accion', ['ver', 'resolver']);
            })->orWhere(function ($q) {
                $q->where('recurso', 'rescates');
            })->pluck('id')->toArray();
            $this->syncPermisosConUuid($operador, $permisos);
        }

        // Evaluador de Adopciones
        $evaluador = Rol::where('codigo', 'EVALUADOR')->first();
        if ($evaluador) {
            $permisos = Permiso::where(function ($q) {
                $q->where('recurso', 'animales')->where('accion', 'ver');
            })->orWhere(function ($q) {
                $q->where('recurso', 'adopciones');
            })->orWhere(function ($q) {
                $q->where('recurso', 'visitas');
            })->pluck('id')->toArray();
            $this->syncPermisosConUuid($evaluador, $permisos);
        }
    }

    /**
     * Sincroniza permisos generando UUIDs manualmente para la tabla pivot.
     */
    protected function syncPermisosConUuid(Rol $rol, array $permisoIds): void
    {
        // Eliminar permisos existentes
        $rol->permisos()->detach();

        // Insertar nuevos con UUID
        $pivotData = [];
        foreach ($permisoIds as $permisoId) {
            $pivotData[$permisoId] = [
                'id' => (string) Str::uuid(),
                'fecha_asignacion' => now(),
            ];
        }

        $rol->permisos()->attach($pivotData);
    }
}
