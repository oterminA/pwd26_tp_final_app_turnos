@extends('layouts.app')
@push('styles')<link rel="stylesheet" href="{{ asset('css/admin-gestion.css') }}">@endpush
@section('content')
<div class="container py-4 py-lg-5 admin-gestion">
    <div class="gestion-header mb-4">
        <div><div class="gestion-eyebrow">ADMINISTRACIÓN / PROFESIONALES</div><h1>Editar profesional</h1><p>Actualizá la ficha, sus especialidades y el estado de la cuenta.</p></div>
        <a href="{{ route('admin.medicos.index') }}" class="btn btn-outline-secondary">Volver a profesionales</a>
    </div>
    <div class="gestion-form-section">
        <h2 class="gestion-form-title">Datos del profesional</h2>
        @include('usuarios.admin.medicos._form')
    </div>
</div>
@endsection
