{{-- resources/views/reportes/index.blade.php --}}
<x-app-layout>
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/reportes.css') }}">
    @endpush

    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <h1 class="text-2xl md:text-3xl lg:text-4xl font-bold text-slate-900 tracking-tight">
                    Reportes de guardias
                </h1>
                <p class="mt-1 text-xs md:text-sm text-slate-500 max-w-xl">
                    Explora guardias por estado, supervisor y situación ante IMSS. Todo en tiempo real y listo para exportar a Excel.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <span class="badge-soft">
                    <span class="badge-dot"></span>
                    Vista analítica en tiempo real
                </span>
            </div>
        </div>
    </x-slot>

    <div class="report-page-wrapper py-8">
        <div class="max-w-7xl mx-auto w-full px-3 sm:px-4 lg:px-8 space-y-6">

            {{-- ======================= FILTROS ======================= --}}
            <section class="grid grid-cols-1 lg:grid-cols-3 gap-4 report-animate-up-1">
                {{-- Filtros de contexto --}}
                <div class="report-card lg:col-span-2">
                    <div class="report-card-header">
                        <div>
                            <h2 class="card-title">Contexto del reporte</h2>
                            <p class="card-subtitle">
                                Ajusta los filtros para enfocar el análisis por empresa, plaza, supervisor o empleado.
                            </p>
                        </div>

                        <button
                            type="button"
                            class="btn-secondary"
                            id="btn-reset-filters">
                            Limpiar filtros
                        </button>
                    </div>

                    <div class="space-y-4">
                        {{-- Primera fila: patrón, plaza --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-4">
                            <div>
                                <label class="filter-label">Patrón</label>
                                <select
                                    class="filter-input"
                                    name="patron_id"
                                    data-report-filter>
                                    <option value="">Todos</option>
                                    @foreach($patrones as $patron)
                                        <option
                                            value="{{ $patron->id }}"
                                            @selected(($filters['patron_id'] ?? '') == $patron->id)
                                        >
                                            {{ $patron->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="filter-label">Plaza</label>
                                <select
                                    class="filter-input"
                                    name="sucursal_id"
                                    data-report-filter>
                                    <option value="">Todas</option>
                                    @foreach($sucursales as $sucursal)
                                        <option
                                            value="{{ $sucursal->id }}"
                                            @selected(($filters['sucursal_id'] ?? '') == $sucursal->id)
                                        >
                                            {{ $sucursal->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Segunda fila: supervisor, empleado --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-4">
                            <div>
                                <label class="filter-label">Supervisor</label>
                                <select
                                    class="filter-input"
                                    name="supervisor_id"
                                    data-report-filter>
                                    <option value="">Todos</option>
                                    @foreach($supervisores as $supervisor)
                                        <option
                                            value="{{ $supervisor->id }}"
                                            @selected(($filters['supervisor_id'] ?? '') == $supervisor->id)
                                        >
                                            {{ $supervisor->nombres }}
                                            {{ $supervisor->apellidoPaterno }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="filter-label">Empleado</label>
                                <select
                                    class="filter-input"
                                    name="empleado_id"
                                    data-report-filter>
                                    <option value="">Todos</option>
                                    @foreach($empleadosList as $empleado)
                                        <option
                                            value="{{ $empleado->id }}"
                                            @selected(($filters['empleado_id'] ?? '') == $empleado->id)
                                        >
                                            {{ $empleado->nombres }}
                                            {{ $empleado->apellidoPaterno }}
                                            {{ $empleado->apellidoMaterno }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Tercera fila: búsqueda rápida --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-4">
                            <div class="md:col-span-2">
                                <label class="filter-label">Búsqueda rápida</label>
                                <div class="relative">
                                    <input
                                        type="text"
                                        name="q"
                                        value="{{ $filters['q'] ?? '' }}"
                                        placeholder="Nombre, apellidos o número de trabajador"
                                        class="filter-input pr-9"
                                        data-report-filter>
                                    <span class="filter-icon">
                                        <svg class="h-4 w-4 md:h-5 md:w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z"/>
                                        </svg>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Filtros de periodo / IMSS + resumen --}}
                <div class="report-card">
                    <div class="report-card-header">
                        <div>
                            <h2 class="card-title">Periodo e IMSS</h2>
                            <p class="card-subtitle">
                                Acota el rango de fechas y filtra por situación ante IMSS.
                            </p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="filter-label">Desde</label>
                                <input
                                    type="date"
                                    name="fecha_desde"
                                    value="{{ $filters['fecha_desde'] ?? '' }}"
                                    class="filter-input"
                                    data-report-filter>
                            </div>
                            <div>
                                <label class="filter-label">Hasta</label>
                                <input
                                    type="date"
                                    name="fecha_hasta"
                                    value="{{ $filters['fecha_hasta'] ?? '' }}"
                                    class="filter-input"
                                    data-report-filter>
                            </div>
                        </div>

                        <div>
                            <label class="filter-label mb-1">IMSS</label>
                            <div class="flex flex-wrap gap-2">
                                <button type="button"
                                    class="pill-toggle pill-toggle-active"
                                    data-imss-value=""
                                    data-report-imss>
                                    Todos
                                </button>
                                <button type="button"
                                    class="pill-toggle"
                                    data-imss-value="alta"
                                    data-report-imss>
                                    Solo ALTA
                                </button>
                                <button type="button"
                                    class="pill-toggle"
                                    data-imss-value="sin_imss"
                                    data-report-imss>
                                    Solo SIN IMSS
                                </button>
                            </div>
                            <input type="hidden" name="imss_estado" value="{{ $filters['imss_estado'] ?? '' }}" data-report-filter>
                        </div>

                        <div class="divider-light"></div>

                        {{-- Métricas compactas (sin la tarjeta "Plantilla filtrada 1") --}}
                        <div class="space-y-2 text-xs">
                            <div class="metric-inline">
                                <span class="metric-inline-label">Guardias filtradas</span>
                                <span class="metric-inline-value" id="metric-total-guardias">
                                    {{ $metrics['total_guardias'] }}
                                </span>
                            </div>
                            <div class="metric-inline">
                                <span class="metric-inline-label">Plazas involucradas</span>
                                <span class="metric-inline-value" id="metric-total-plazas">
                                    {{ $metrics['total_plazas'] }}
                                </span>
                            </div>
                            <div class="metric-inline">
                                <span class="metric-inline-label">Supervisores involucrados</span>
                                <span class="metric-inline-value" id="metric-total-supervisores">
                                    {{ $metrics['total_supervisores'] }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- ======================= TABS DE REPORTE ======================= --}}
            <section class="report-card report-animate-up-2">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
                    <div class="flex flex-wrap gap-2">
                        <button
                            type="button"
                            class="report-tab report-tab-active"
                            data-report-type="guardias_estado">
                            Guardias por estado
                        </button>
                        <button
                            type="button"
                            class="report-tab"
                            data-report-type="guardias_supervisor">
                            Guardias por supervisor
                        </button>
                        <button
                            type="button"
                            class="report-tab"
                            data-report-type="guardias_imss">
                            Guardias con IMSS / sin IMSS
                        </button>
                    </div>

                    <button
                        type="button"
                        class="btn-primary"
                        id="btn-export-excel">
                        Exportar a Excel
                    </button>
                </div>

                {{-- Gráfica + tabla --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    {{-- Gráfica --}}
                    <div class="lg:col-span-2">
                        <div class="chart-card">
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <h3 class="chart-title" id="chart-title">
                                        Distribución por estado
                                    </h3>
                                    <p class="chart-subtitle" id="chart-subtitle">
                                        Total de guardias agrupadas por estado.
                                    </p>
                                </div>
                            </div>

                            <div class="chart-wrapper">
                                <canvas id="reportChart"></canvas>
                                <div id="chart-empty" class="chart-empty-state">
                                    No hay datos para graficar con los filtros actuales.
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tabla dinámica según el tipo de reporte --}}
                    <div class="table-card">
                        <h4 class="table-title" id="table-title">
                            Detalle por estado
                        </h4>
                        <div class="table-scroll">
                            <table class="table-report" id="report-table">
                                <thead id="table-head">
                                    {{-- Se llena desde JS según el tipo de reporte --}}
                                </thead>
                                <tbody id="table-body">
                                    {{-- Se llena desde JS según el tipo de reporte --}}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            window.ReportesConfig = {
                dataUrl: '{{ route('reportes.index') }}',
                exportUrl: '{{ route('reportes.export') }}',
                initialData: {
                    metrics: @json($metrics),
                    reportes: @json($reportes),
                },
            };
        </script>
        <script src="{{ asset('js/reportes.js') }}"></script>
    @endpush
</x-app-layout>
