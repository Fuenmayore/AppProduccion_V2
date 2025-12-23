<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;

class UserController extends Controller
{
    /**
     * Mostrar lista de usuarios y roles
     */
    public function index()
    {
        // Traemos usuarios con sus roles
        $users = User::with('roles')->get();
        // Traemos todos los roles disponibles para el formulario
        $roles = Role::all();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'roles' => $roles
        ]);
    }

    /**
     * Registrar un nuevo usuario y asignarle su rol
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|exists:roles,name',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Spatie asigna el rol aquí
        $user->assignRole($request->role);

        return Redirect::back()->with('success', 'Usuario creado correctamente.');
    }

    /**
     * Eliminar usuario
     */
    public function destroy(User $user)
    {
        // Evitar que el Super Admin se borre a sí mismo
        if (auth()->id() === $user->id) {
            return Redirect::back()->with('error', 'No puedes eliminar tu propio perfil.');
        }

        $user->delete();
        return Redirect::back()->with('success', 'Usuario eliminado.');
    }
}