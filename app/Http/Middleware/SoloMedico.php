<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SoloMedico
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login.medico');
        }

        if (!auth()->user()->isMedico() || !auth()->user()->medico) {
            abort(403);
        }

        return $next($request);
    }
}
