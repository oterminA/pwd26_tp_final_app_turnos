<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class VerificarDuracionTurnoMedico
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && $user->medico && is_null($user->medico->duracion_turno)) {
            if (!$request->routeIs('medico.configurar-turno', 'medico.guardar-turno', 'logout')) {
                return redirect()->route('medico.configurar-turno');
            }
        }

        return $next($request);
    }
}