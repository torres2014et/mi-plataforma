<?php

namespace Database\Seeders;

use App\Models\Restaurante;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class RolesAndTestUsersSeeder extends Seeder
{
    public function run(): void
    {
        // Crear roles si no existen
        foreach (['cliente', 'restaurante', 'domiciliario', 'admin'] as $rol) {
            Role::firstOrCreate(['name' => $rol, 'guard_name' => 'web']);
        }

        // Usuario de prueba: cliente
        $cliente = User::firstOrCreate(
            ['email' => 'cliente@test.com'],
            [
                'name'     => 'Cliente Test',
                'password' => Hash::make('password'),
                'telefono' => '310 000 0001',
                'direccion'=> 'Calle 1 #1-1, Ubaté',
            ]
        );
        if (! $cliente->hasRole('cliente')) {
            $cliente->assignRole('cliente');
        }

        // Usuario de prueba: restaurante (vendedor)
        $vendedor = User::firstOrCreate(
            ['email' => 'vendedor@test.com'],
            [
                'name'          => 'El Pollo Don Luis',
                'password'      => Hash::make('password'),
                'telefono'      => '310 000 0002',
                'direccion'     => 'Carrera 4 #5-6, Ubaté',
                'nombre_negocio'=> 'El Pollo Don Luis',
            ]
        );
        if (! $vendedor->hasRole('restaurante')) {
            $vendedor->assignRole('restaurante');
        }
        Restaurante::firstOrCreate(
            ['user_id' => $vendedor->id],
            [
                'nombre'   => 'El Pollo Don Luis',
                'descripcion' => 'El mejor pollo asado de Ubaté',
                'telefono' => '310 000 0002',
                'direccion'=> 'Carrera 4 #5-6, Ubaté',
                'activo'   => true,
            ]
        );

        // Usuario de prueba: domiciliario
        $domiciliario = User::firstOrCreate(
            ['email' => 'domiciliario@test.com'],
            [
                'name'           => 'Carlos Domiciliario',
                'password'       => Hash::make('password'),
                'telefono'       => '310 000 0003',
                'cedula'         => '1234567890',
                'tipo_vehiculo'  => 'Moto',
                'placa_vehiculo' => 'ABC 123',
            ]
        );
        if (! $domiciliario->hasRole('domiciliario')) {
            $domiciliario->assignRole('domiciliario');
        }

        // Usuario de prueba: admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name'     => 'Administrador',
                'password' => Hash::make('password'),
            ]
        );
        if (! $admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        $this->command->info('✓ Roles creados: cliente, restaurante, domiciliario, admin');
        $this->command->info('✓ Usuarios de prueba:');
        $this->command->table(
            ['Email', 'Contraseña', 'Rol'],
            [
                ['cliente@test.com',      'password', 'cliente'],
                ['vendedor@test.com',     'password', 'restaurante (vendedor)'],
                ['domiciliario@test.com', 'password', 'domiciliario'],
                ['admin@test.com',        'password', 'admin'],
            ]
        );
    }
}
