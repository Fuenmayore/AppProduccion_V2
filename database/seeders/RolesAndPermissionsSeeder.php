<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // 0. Resetear caché de roles y permisos (Obligatorio)
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. DEFINICIÓN DE PERMISOS
        $permissions = [
            'gestionar-usuarios',
            'gestionar-parametros',
            'crear-reportes',
            'editar-reportes',
            'verificar-reportes',
            'eliminar-reportes',
            'ver-trazabilidad',
            'gestionar-logistica',
            'ver-reprocesos',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 2. CREACIÓN DE ROLES
        $roleAdmin       = Role::firstOrCreate(['name' => 'Super Admin']);
        $roleCoordinador = Role::firstOrCreate(['name' => 'Coordinador']);
        $rolePastero     = Role::firstOrCreate(['name' => 'Pastero']);
        $roleCalidad     = Role::firstOrCreate(['name' => 'Calidad']);

        // 3. ASIGNACIÓN DE PERMISOS
        $roleAdmin->syncPermissions(Permission::all());

        $roleCoordinador->syncPermissions([
            'gestionar-parametros',
            'crear-reportes',
            'editar-reportes',
            'verificar-reportes',
            'ver-trazabilidad',
            'gestionar-logistica',
            'ver-reprocesos',
        ]);

        $rolePastero->syncPermissions([
            'crear-reportes',
            'editar-reportes',
            'gestionar-logistica',
        ]);

        // 4. CREACIÓN DE USUARIOS INICIALES
        
        // Super Administrador
        $admin = User::firstOrCreate(
            ['email' => 'admin@empresa.com'],
            [
                'name' => 'Administrador Sistema',
                'password' => Hash::make('admin123'),
            ]
        );
        $admin->assignRole($roleAdmin);

        // Coordinador de Planta
        $coord = User::firstOrCreate(
            ['email' => 'coord@empresa.com'],
            [
                'name' => 'Juan Coordinador',
                'password' => Hash::make('coord123'),
            ]
        );
        $coord->assignRole($roleCoordinador);

        // Pastero (Operario)
        $pastero = User::firstOrCreate(
            ['email' => 'pastero@empresa.com'],
            [
                'name' => 'Pedro Pastero',
                'password' => Hash::make('pastero123'),
            ]
        );
        $pastero->assignRole($rolePastero);

        $this->command->info('Roles, permisos y usuarios de prueba creados con éxito.');
    }
}