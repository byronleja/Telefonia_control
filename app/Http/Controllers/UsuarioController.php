<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UsuarioController extends Controller
{
    public function index(): View
    {
        return view('usuarios.index', [
            'usuarios' => User::orderBy('name')->paginate(15)
        ]);
    }

    public function create(): View
    {
        return view('usuarios.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $v = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email',
            'rol' => 'required|in:admin,visualizador,contabilidad',
            'password' => [
                'required',
                'confirmed',
                Password::min(8)->letters()->numbers()
            ],
        ], [
            'password.required' => 'La contraseña es obligatoria.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        User::create([
            'name' => $v['name'],
            'email' => $v['email'],
            'rol' => $v['rol'],
            'password' => Hash::make($v['password']),
        ]);

        return redirect()
            ->route('usuarios.index')
            ->with('success', "Usuario {$v['name']} creado exitosamente.");
    }

    public function edit(User $usuario): View
    {
        return view('usuarios.edit', compact('usuario'));
    }

    public function update(Request $request, User $usuario): RedirectResponse
    {
        $v = $request->validate([
            'name' => 'required|string|max:150',
            'email' => "required|email|unique:users,email,{$usuario->id}",
            'rol' => 'required|in:admin,visualizador,contabilidad',
        ]);

        $usuario->update($v);

        if ($request->filled('password')) {
            $request->validate([
                'password' => [
                    'confirmed',
                    Password::min(8)->letters()->numbers()
                ],
            ], [
                'password.confirmed' => 'Las contraseñas no coinciden.',
            ]);

            $usuario->update([
                'password' => Hash::make($request->password)
            ]);
        }

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuario actualizado exitosamente.');
    }

    public function destroy(User $usuario): RedirectResponse
    {
        if ($usuario->id === auth()->id()) {
            return back()->with(
                'error',
                'No puedes eliminar tu propio usuario.'
            );
        }

        $usuario->delete();

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuario eliminado exitosamente.');
    }
}
