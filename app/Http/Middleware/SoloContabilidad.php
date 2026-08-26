<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SoloContabilidad
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!auth()->user()->esContabilidad()) {
            return redirect()->back()
                ->with('error', 'Esta seccion es exclusiva del area de Contabilidad.');
        }

        return $next($request);
    }
}
