@extends('layouts.app')

@section('content')
    <div class="container py-5">
        <section id="panel-medico" class="py-4 mb-5">
            <div class="text-center max-w-700 mx-auto mb-5">
                <h2 class="fw-bold text-dark display-6 mb-3">Panel de administrador</h2>
                <h5 class="text-muted">
                    @auth
                        <span>Hola, {{ Auth::user()->name }}!</span>
                    @endauth
                </h5>
            </div>

            {{-- @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif --}}


        </section>
    </div>
@endsection
