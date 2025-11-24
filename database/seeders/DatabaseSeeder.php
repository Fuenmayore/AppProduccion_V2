<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    { {
            // Primero los roles
            $this->call(RolesAndPermissionsSeeder::class);
            $this->call(ProductionCatalogSeeder::class);

            // Crear usuario y asignarle rol
            $user = \App\Models\User::factory()->create([
                'name' => 'Super Administrador',
                'email' => 'admin@admin.com',
                'password' => bcrypt('password'),
            ]);

            // ¡ASIGNAR EL ROL AQUÍ!
            $user->assignRole('Super Admin');
            $user->assignRole('Coordinador'); // Para que aparezca en la lista de coordinadores
        }

        // 2. Llamar al Seeder del Catálogo (Líneas y Turnos) que creamos antes
        $this->call([
            ProductionCatalogSeeder::class,
        ]);
    }
}
