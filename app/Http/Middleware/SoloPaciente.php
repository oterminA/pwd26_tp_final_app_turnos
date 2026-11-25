<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SoloPaciente
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login.paciente');
        }

        if (!auth()->user()->isPaciente() || !auth()->user()->paciente) {
            abort(403);
        }

        return $next($request);
    }
}
