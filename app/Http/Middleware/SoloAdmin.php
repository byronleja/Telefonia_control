<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SoloAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!auth()->user()->esAdmin()) {
            return redirect()->back()
                ->with('error', 'Solo los administradores pueden realizar esta accion.');
        }

        return $next($request);
    }
}
