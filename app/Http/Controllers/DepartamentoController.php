<?php

namespace App\Http\Controllers;

use App\Models\Departamento;
use App\Models\Empleado;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartamentoController extends Controller
{
    public function index(): View
    {
        $departamentos = Departamento::withCount('empleados')
            ->orderBy('nombre')
            ->paginate(15);

        return view('departamentos.index', compact('departamentos'));
    }

    public function create(): View
    {
        return view('departamentos.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $v = $request->validate(
            [
                'nombre' => 'required|string|max:100|unique:departamentos,nombre',
                'descripcion' => 'nullable|string|max:255',
            ],
            [
                'nombre.unique' => 'Ya existe un departamento con ese nombre.',
            ]
        );

        $v['activo'] = $request->boolean('activo', true);

        Departamento::create($v);

        return redirect()
            ->route('departamentos.index')
            ->with(
                'success',
                "Departamento \"{$v['nombre']}\" creado exitosamente."
            );
    }

    public function edit(Departamento $departamento): View
    {
        $totalEmpleados = Empleado::where(
            'departamento',
            $departamento->nombre
        )->count();

        return view(
            'departamentos.edit',
            compact('departamento', 'totalEmpleados')
        );
    }

    public function update(
        Request $request,
        Departamento $departamento
    ): RedirectResponse {
        $v = $request->validate(
            [
                'nombre' => "required|string|max:100|unique:departamentos,nombre,{$departamento->id}",
                'descripcion' => 'nullable|string|max:255',
            ],
            [
                'nombre.unique' => 'Ya existe un departamento con ese nombre.',
            ]
        );

        $nombreAnterior = $departamento->nombre;

        $v['activo'] = $request->boolean('activo', true);

        $departamento->update($v);

        if ($nombreAnterior !== $v['nombre']) {
            Empleado::where(
                'departamento',
                $nombreAnterior
            )->update([
                'departamento' => $v['nombre'],
            ]);
        }

        return redirect()
            ->route('departamentos.index')
            ->with(
                'success',
                "Departamento \"{$v['nombre']}\" actualizado exitosamente."
            );
    }

    public function destroy(
        Departamento $departamento
    ): RedirectResponse {
        $total = Empleado::where(
            'departamento',
            $departamento->nombre
        )->count();

        if ($total > 0) {
            return back()->with(
                'error',
                "No se puede eliminar \"{$departamento->nombre}\" porque tiene {$total} empleado(s) asignados."
            );
        }

        $nombre = $departamento->nombre;

        $departamento->delete();

        return redirect()
            ->route('departamentos.index')
            ->with(
                'success',
                "Departamento \"{$nombre}\" eliminado exitosamente."
            );
    }
}