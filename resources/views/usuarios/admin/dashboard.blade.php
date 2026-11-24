@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-dashboard.css') }}">
@endpush

@section('content')
    <div class="container py-4 py-lg-5 admin-dashboard">
        <div class="admin-hero mb-4">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div>
                    <div class="admin-eyebrow">PANEL DE ADMINISTRACIÓN</div>
                    <h1 class="fw-bold mb-2">Resumen de la clínica</h1>
                    <p class="mb-0">Información de profesionales, especialidades y turnos en un solo lugar.</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-light" href="{{ route('admin.medicos.index') }}">Profesionales</a>
                    <a class="btn btn-outline-light" href="{{ route('admin.especialidades.index') }}">Especialidades</a>
                </div>
            </div>
        </div>

        <div class="admin-panel mb-4">
            <form action="{{ route('admin.dashboard') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-4 col-lg-3">
                    <label for="mes" class="form-label fw-semibold">Mes a consultar</label>
                    <input type="month" id="mes" name="mes" class="form-control" value="{{ $mes }}"
                        required>
                </div>
                <div class="col-md-5 col-lg-5">
                    <label for="medico" class="form-label fw-semibold">Profesional</label>
                    <select id="medico" name="medico" class="form-select">
                        <option value="">Todos los profesionales</option>
                        @foreach ($medicos as $medico)
                            <option value="{{ $medico->id }}" @selected((string) $medicoId === (string) $medico->id)>
                                {{ $medico->user->name ?? 'Medico #' . $medico->id }}{{ $medico->trashed() ? ' (inactivo)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 col-lg-4 d-flex gap-2">
                    <button type="submit" class="btn admin-btn-primary flex-grow-1">Aplicar filtros</button>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">Limpiar</a>
                </div>
            </form>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-xl-3">
                <div class="admin-kpi">
                    <div class="admin-kpi-label">Turnos del mes</div>
                    <div class="admin-kpi-value">{{ number_format($total, 0, ',', '.') }}</div>
                    <div class="admin-kpi-note">Reservas registradas</div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="admin-kpi">
                    <div class="admin-kpi-label">Finalizados</div>
                    <div class="admin-kpi-value">{{ number_format($finalizados, 0, ',', '.') }}</div>
                    <div class="admin-kpi-note">Atenciones completadas</div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="admin-kpi">
                    <div class="admin-kpi-label">Cancelaciones</div>
                    <div class="admin-kpi-value">{{ number_format($tasaCancelacion, 1, ',', '.') }}%</div>
                    <div class="admin-kpi-note">{{ $cancelados }} de {{ $total }} turnos del mes</div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="admin-kpi">
                    <div class="admin-kpi-label">Pacientes únicos</div>
                    <div class="admin-kpi-value">{{ number_format($pacientesUnicos, 0, ',', '.') }}</div>
                    <div class="admin-kpi-note">Con turnos en el mes</div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="admin-mini-stat"><span>Profesionales activos</span><strong>{{ $medicosActivos }}</strong></div>
            </div>
            <div class="col-md-4">
                <div class="admin-mini-stat"><span>Especialidades
                        activas</span><strong>{{ $especialidadesActivas }}</strong></div>
            </div>
            <div class="col-md-4">
                <div class="admin-mini-stat"><span>Pacientes registrados</span><strong>{{ $pacientesRegistrados }}</strong>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-lg-8">
                <div class="admin-panel h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                        <div>
                            <h2 class="admin-section-title">Turnos por día</h2>
                            <p class="admin-hint mb-0">Evolución durante el mes seleccionado</p>
                        </div>
                        <span class="admin-small-tag">{{ $mes }}</span>
                    </div>
                    <div class="admin-chart"><canvas id="graficoDias" aria-label="Turnos por día"></canvas></div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="admin-panel h-100">
                    <h2 class="admin-section-title">Estados de turnos</h2>
                    <p class="admin-hint">Distribución del mes seleccionado</p>
                    <div class="admin-donut"><canvas id="graficoEstados" aria-label="Estados de turnos"></canvas></div>
                    <div class="admin-legend-note">Pendientes: {{ $pendientes }} · Ausentes: {{ $ausentes }}</div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-lg-6">
                <div class="admin-panel h-100">
                    <h2 class="admin-section-title mb-3">Especialidades más solicitadas</h2>
                    @forelse($porEspecialidad as $item)
                        <div class="admin-rank-row">
                            <span class="text-truncate">{{ $item->nombre }}</span>
                            <strong>{{ $item->total }} turnos</strong>
                        </div>
                    @empty
                        <p class="admin-hint mb-0">Todavía no hay turnos en este período.</p>
                    @endforelse
                </div>
            </div>
            <div class="col-lg-6">
                <div class="admin-panel h-100">
                    <h2 class="admin-section-title mb-3">Actividad por profesional</h2>
                    @forelse($porMedico as $item)
                        <div class="admin-rank-row">
                            <span class="text-truncate">{{ $item->name }}</span>
                            <strong>{{ $item->total }} turnos</strong>
                        </div>
                    @empty
                        <p class="admin-hint mb-0">Todavía no hay turnos en este período.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="admin-panel mb-4">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                <div>
                    <h2 class="admin-section-title">Calendario de turnos</h2>
                    <p class="admin-hint mb-0">Consulta de agenda. Seleccioná un turno para ver sus datos.</p>
                </div>
                <div class="admin-calendar-legend">
                    <span><i class="admin-dot" style="background:#55b4a8"></i>Pendiente</span>
                    <span><i class="admin-dot" style="background:#267a62"></i>Finalizado</span>
                    <span><i class="admin-dot" style="background:#9aa6b2"></i>Cancelado</span>
                    <span><i class="admin-dot" style="background:#c48e47"></i>Ausente</span>
                </div>
            </div>
            <div id="calendarError" class="alert alert-danger d-none" role="alert"></div>
            <div id="adminCalendar"></div>
            <div id="adminEventDetail" class="admin-event-detail d-none mt-3" aria-live="polite"></div>
        </div>

        <div class="admin-panel">
            <h2 class="admin-section-title mb-3">Próximos turnos pendientes</h2>
            <div class="table-responsive">
                <table class="table align-middle admin-table mb-0">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Profesional</th>
                            <th>Especialidad</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($proximos as $turno)
                            <tr>
                                <td>{{ $turno->fecha->format('d/m/Y') }}</td>
                                <td>{{ substr((string) $turno->getRawOriginal('hora_inicio'), 0, 5) }}</td>
                                <td>{{ $turno->medico?->user?->name ?? 'Sin profesional' }}</td>
                                <td>{{ $turno->especialidad?->nombre ?? '-' }}</td>
                                <td><span class="admin-small-tag">{{ ucfirst($turno->estado) }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No hay próximos turnos pendientes.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <p class="admin-footnote mt-3 mb-0">Los indicadores mensuales corresponden al período filtrado; el calendario
            permite navegar otros meses. Los turnos cancelados se incluyen en el total del mes y en la tasa de cancelación.
            Los datos son de la base del proyecto.</p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.6.1/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.22/index.global.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fechas = @json($dias);
            const cantidades = @json($cantidadDia);
            const estados = @json($estadosGrafico);
            const seleccionado = @json($mes);
            const medico = @json($medicoId);
            const urlCalendario = @json(route('admin.dashboard.calendario'));

            if (window.Chart) {
                new Chart(document.getElementById('graficoDias'), {
                    type: 'line',
                    data: {
                        labels: fechas,
                        datasets: [{
                            label: 'Turnos',
                            data: cantidades,
                            borderColor: '#369c8e',
                            backgroundColor: 'rgba(54, 156, 142, .12)',
                            fill: true,
                            tension: .3,
                            pointRadius: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
                new Chart(document.getElementById('graficoEstados'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Pendientes', 'Finalizados', 'Cancelados', 'Ausentes'],
                        datasets: [{
                            data: estados,
                            backgroundColor: ['#55b4a8', '#267a62', '#9aa6b2', '#c48e47'],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '72%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    boxWidth: 10,
                                    usePointStyle: true
                                }
                            }
                        }
                    }
                });
            }

            if (window.FullCalendar) {
                const aviso = document.getElementById('calendarError');
                const detalle = document.getElementById('adminEventDetail');
                const calendar = new FullCalendar.Calendar(document.getElementById('adminCalendar'), {
                    initialView: 'dayGridMonth',
                    initialDate: seleccionado + '-01',
                    locale: 'es',
                    height: 'auto',
                    displayEventTime: false,
                    dayMaxEvents: 3,
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,listWeek'
                    },
                    buttonText: {
                        today: 'Hoy',
                        month: 'Mes',
                        list: 'Lista'
                    },
                    events: function(info, successCallback, failureCallback) {
                        const params = new URLSearchParams({
                            start: info.startStr,
                            end: info.endStr
                        });
                        if (medico) params.set('medico', medico);
                        aviso.classList.add('d-none');
                        fetch(urlCalendario + '?' + params.toString(), {
                                headers: {
                                    'Accept': 'application/json'
                                }
                            })
                            .then(async response => {
                                if (!response.ok) throw new Error(
                                    'No se pudieron cargar los turnos (' + response
                                    .status + ').');
                                return response.json();
                            })
                            .then(successCallback)
                            .catch(error => {
                                aviso.textContent = error.message;
                                aviso.classList.remove('d-none');
                                failureCallback(error);
                            });
                    },
                    eventClick: function(info) {
                        const datos = info.event.extendedProps;
                        detalle.textContent = 'Profesional: ' + datos.medico + '  |  Especialidad: ' +
                            datos.especialidad + '  |  Hora: ' + datos.hora + '  |  Estado: ' + datos
                            .estado;
                        detalle.classList.remove('d-none');
                    }
                });
                calendar.render();
            }
        });
    </script>
@endsection
