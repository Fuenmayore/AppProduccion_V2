<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Resetear caché de roles
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Crear Roles del Sistema
        $roleAdmin       = Role::create(['name' => 'Super Admin']);
        $roleCoordinador = Role::create(['name' => 'Coordinador']);
        $roleOperario    = Role::create(['name' => 'Operario']);
        $roleCalidad     = Role::create(['name' => 'Calidad']);

        // 2. Crear Permisos (Opcional por ahora, pero buena práctica)
        Permission::create(['name' => 'ver-Pastificio']);
        Permission::create(['name' => 'crear-Pastificio']);
        Permission::create(['name' => 'editar-Pastificio']);
        Permission::create(['name' => 'borrar-Pastificio']);

        // 3. Asignar permisos a roles
        $roleCoordinador->givePermissionTo(['ver-Pastificio', 'crear-Pastificio', 'editar-Pastificio']);
        $roleOperario->givePermissionTo(['ver-Pastificio', 'crear-Pastificio']);
        $roleAdmin->givePermissionTo(Permission::all());
    }
}