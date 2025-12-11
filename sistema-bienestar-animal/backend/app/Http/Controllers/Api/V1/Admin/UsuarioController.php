<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\BaseController;
use App\Models\User\Usuario;
use App\Models\User\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class UsuarioController extends BaseController
{
    /**
     * Listar usuarios.
     * GET /api/v1/usuarios
     */
    public function index(Request $request)
    {
        try {
            $query = Usuario::with('rol');

            if ($request->has('rol_id')) {
                $query->where('rol_id', $request->rol_id);
            }

            if ($request->has('activo')) {
                $query->where('activo', $request->boolean('activo'));
            }

            if ($request->has('busqueda')) {
                $busqueda = $request->busqueda;
                $query->where(function ($q) use ($busqueda) {
                    $q->where('nombres', 'like', "%{$busqueda}%")
                      ->orWhere('apellidos', 'like', "%{$busqueda}%")
                      ->orWhere('email', 'like', "%{$busqueda}%")
                      ->orWhere('documento_identidad', 'like', "%{$busqueda}%");
                });
            }

            $usuarios = $query->orderBy('nombres')
                ->paginate($request->get('per_page', 15));

            return $this->successResponse($usuarios);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al listar usuarios');
        }
    }

    /**
     * Crear usuario.
     * POST /api/v1/usuarios
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tipo_documento' => 'required|in:CC,CE,TI,PP',
            'documento_identidad' => 'required|string|max:20|unique:usuarios,documento_identidad',
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'email' => 'required|email|max:100|unique:usuarios,email',
            'telefono' => 'nullable|string|max:20',
            'password' => ['required', Password::min(8)->mixedCase()->numbers()],
            'rol_id' => 'required|exists:roles,id',
            'cargo' => 'nullable|string|max:100',
            'area' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $data = $request->all();
            $data['password'] = Hash::make($request->password);
            $data['activo'] = true;
            $data['created_by'] = auth()->id();

            $usuario = Usuario::create($data);

            return $this->createdResponse(
                $usuario->fresh('rol'),
                'Usuario creado exitosamente'
            );
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al crear usuario: ' . $e->getMessage());
        }
    }

    /**
     * Obtener usuario especifico.
     * GET /api/v1/usuarios/{id}
     */
    public function show(string $id)
    {
        try {
            $usuario = Usuario::with(['rol.permisos'])->findOrFail($id);
            return $this->successResponse($usuario);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Usuario no encontrado');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al obtener usuario');
        }
    }

    /**
     * Actualizar usuario.
     * PUT /api/v1/usuarios/{id}
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'nombres' => 'nullable|string|max:100',
            'apellidos' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:100|unique:usuarios,email,' . $id,
            'telefono' => 'nullable|string|max:20',
            'rol_id' => 'nullable|exists:roles,id',
            'cargo' => 'nullable|string|max:100',
            'area' => 'nullable|string|max:100',
            'activo' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $usuario = Usuario::findOrFail($id);

            $data = $request->only([
                'nombres', 'apellidos', 'email', 'telefono',
                'rol_id', 'cargo', 'area', 'activo'
            ]);
            $data['updated_by'] = auth()->id();

            $usuario->update(array_filter($data, fn($v) => $v !== null));

            return $this->successResponse($usuario->fresh('rol'), 'Usuario actualizado exitosamente');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Usuario no encontrado');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al actualizar usuario');
        }
    }

    /**
     * Cambiar contrasena de usuario.
     * PUT /api/v1/usuarios/{id}/password
     */
    public function cambiarPassword(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'password' => ['required', Password::min(8)->mixedCase()->numbers()],
            'password_confirmation' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $usuario = Usuario::findOrFail($id);

            $usuario->update([
                'password' => Hash::make($request->password),
                'updated_by' => auth()->id(),
            ]);

            return $this->successResponse(null, 'Contrasena actualizada exitosamente');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Usuario no encontrado');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al cambiar contrasena');
        }
    }

    /**
     * Activar/Desactivar usuario.
     * PUT /api/v1/usuarios/{id}/toggle-activo
     */
    public function toggleActivo(string $id)
    {
        try {
            $usuario = Usuario::findOrFail($id);

            // No permitir desactivar el propio usuario
            if ($usuario->id === auth()->id()) {
                return $this->errorResponse('No puede desactivar su propia cuenta', null, 400);
            }

            $usuario->update([
                'activo' => !$usuario->activo,
                'updated_by' => auth()->id(),
            ]);

            $mensaje = $usuario->activo ? 'Usuario activado' : 'Usuario desactivado';

            return $this->successResponse($usuario, $mensaje);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Usuario no encontrado');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al cambiar estado');
        }
    }

    /**
     * Eliminar usuario (soft delete).
     * DELETE /api/v1/usuarios/{id}
     */
    public function destroy(string $id)
    {
        try {
            $usuario = Usuario::findOrFail($id);

            // No permitir eliminar el propio usuario
            if ($usuario->id === auth()->id()) {
                return $this->errorResponse('No puede eliminar su propia cuenta', null, 400);
            }

            $usuario->delete();

            return $this->successResponse(null, 'Usuario eliminado exitosamente');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Usuario no encontrado');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al eliminar usuario');
        }
    }

    /**
     * Listar roles disponibles.
     * GET /api/v1/usuarios/roles
     */
    public function roles()
    {
        try {
            $roles = Rol::with('permisos')->where('activo', true)->get();
            return $this->successResponse($roles);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al listar roles');
        }
    }
}
