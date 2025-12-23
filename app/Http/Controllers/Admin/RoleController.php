<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;

class RoleController extends Controller
{
    /**
     * Listar roles y todos los permisos disponibles
     */
    public function index()
    {
        return Inertia::render('Admin/Roles/Index', [
            // Traemos roles con sus permisos asociados
            'roles' => Role::with('permissions')->get(),
            // Lista de todos los permisos del sistema para asignar
            'permissions' => Permission::all()
        ]);
    }

    /**
     * Crear un nuevo Rol (ej: "Supervisor de Calidad")
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',
        ]);

        Role::create(['name' => $request->name, 'guard_name' => 'web']);

        return Redirect::back()->with('success', 'Rol creado correctamente.');
    }

    /**
     * Sincronizar permisos a un Rol específico
     */
    public function syncPermissions(Request $request, Role $role)
    {
        $request->validate([
            'permissions' => 'array'
        ]);

        // Evitar quitarle permisos al Super Admin por accidente desde la UI
        if ($role->name === 'Super Admin') {
            return Redirect::back()->with('error', 'El Super Admin siempre tiene todos los permisos.');
        }

        // syncPermissions reemplaza todos los permisos anteriores del rol por los nuevos
        $role->syncPermissions($request->permissions);

        return Redirect::back()->with('success', 'Permisos del rol actualizados.');
    }

    /**
     * Eliminar un Rol
     */
public function destroy(Role $role)
{
    // 1. Protegemos los roles críticos del negocio
    $protectedRoles = ['Super Admin', 'Coordinador', 'Pastero'];
    
    if (in_array($role->name, $protectedRoles)) {
        return Redirect::back()->with('error', 'Este rol es vital para el sistema y no puede ser eliminado.');
    }

    // 2. Verificamos si hay usuarios usándolo (Opcional, pero recomendado)
    if ($role->users()->count() > 0) {
        return Redirect::back()->with('error', 'No puedes borrar un rol que tiene usuarios asignados. Primero cámbiales el rol.');
    }

    $role->delete();
    return Redirect::back()->with('success', 'Rol eliminado correctamente.');
}
}