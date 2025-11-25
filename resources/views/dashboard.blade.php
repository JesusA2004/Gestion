{{-- resources/views/dashboard.blade.php --}}
<x-app-layout>
    @push('styles')
        <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
    @endpush

    @php
        $cards   = $dashboardData['cards'] ?? [];
        $charts  = $dashboardData['charts'] ?? [];
        $tables  = $dashboardData['tables'] ?? [];

        $totalEmpleados     = $cards['totalEmpleados']     ?? 0;
        $empleadosActivos   = $cards['empleadosActivos']   ?? 0;
        $empleadosInactivos = $cards['empleadosInactivos'] ?? 0;
        $totalPatrones      = $cards['totalPatrones']      ?? 0;
        $totalSucursales    = $cards['totalSucursales']    ?? 0;
        $totalDepartamentos = $cards['totalDepartamentos'] ?? 0;

        $empleadosEstadoChart = $charts['empleadosEstado'] ?? ['labels' => [], 'data' => []];
        $altasMensualesChart  = $charts['altasMensuales']  ?? ['labels' => [], 'data' => []];

        $topPatrones   = $tables['topPatrones']   ?? collect();
        $topSucursales = $tables['topSucursales'] ?? collect();

        $totalImss = $empleadosActivos + $empleadosInactivos;
    @endphp

    <div class="dash-shell py-6 lg:py-8">
        <div class="dash-container mx-auto px-3 sm:px-4 xl:px-10">

            {{-- HERO --}}
            <section class="dash-hero animate-hero">
                <div class="dash-hero-left">
                    <h1 class="dash-hero-title">Dashboard general</h1>
                    <p class="dash-hero-subtitle">
                        Visión ejecutiva de empleados, patrones y sucursales.
                    </p>
                    <p class="dash-hero-helper">
                        Monitorea headcount, alta IMSS y comportamiento de altas y reingresos en los últimos meses.
                    </p>
                </div>

                <div class="dash-hero-metrics">
                    <div class="dash-hero-chip">
                        <span class="dash-hero-chip-label">Empleados</span>
                        <span class="dash-hero-chip-value">{{ number_format($totalEmpleados) }}</span>
                    </div>
                    <div class="dash-hero-chip">
                        <span class="dash-hero-chip-label">Patrones</span>
                        <span class="dash-hero-chip-value">{{ number_format($totalPatrones) }}</span>
                    </div>
                    <div class="dash-hero-chip">
                        <span class="dash-hero-chip-label">Sucursales</span>
                        <span class="dash-hero-chip-value">{{ number_format($totalSucursales) }}</span>
                    </div>
                    <div class="dash-hero-chip">
                        <span class="dash-hero-chip-label">Departamentos</span>
                        <span class="dash-hero-chip-value">{{ number_format($totalDepartamentos) }}</span>
                    </div>
                </div>
            </section>

            {{-- KPI principales --}}
            <section class="dash-grid-3 mt-6 lg:mt-8">
                <article class="dash-card dash-card-kpi animate-fade-up">
                    <div class="dash-card-tag dash-tag-green">Estado IMSS</div>
                    <h2 class="dash-card-title">Activos IMSS</h2>
                    <p class="dash-card-value dash-card-value-green">
                        {{ number_format($empleadosActivos) }}
                    </p>
                    <p class="dash-card-foot">Con registro IMSS en alta.</p>
                </article>

                <article class="dash-card dash-card-kpi animate-fade-up" style="animation-delay:.05s">
                    <div class="dash-card-tag dash-tag-amber">Estado IMSS</div>
                    <h2 class="dash-card-title">Inactivos IMSS</h2>
                    <p class="dash-card-value dash-card-value-amber">
                        {{ number_format($empleadosInactivos) }}
                    </p>
                    <p class="dash-card-foot">Sin registro activo ante IMSS.</p>
                </article>

                <article class="dash-card dash-card-kpi animate-fade-up" style="animation-delay:.1s">
                    <div class="dash-card-tag dash-tag-blue">Estructura</div>
                    <h2 class="dash-card-title">Estructura organizacional</h2>
                    <p class="dash-card-value dash-card-value-blue">
                        {{ number_format($totalDepartamentos) }}
                    </p>
                    <p class="dash-card-foot">Departamentos activos en el catálogo.</p>
                </article>
            </section>

            {{-- Bloque central: gráficas --}}
            <section class="dash-grid-2 mt-6 lg:mt-8">
                {{-- Distribución IMSS (gráfica donut) --}}
                <article class="dash-card animate-fade-up">
                    <div class="dash-card-header">
                        <h3 class="dash-card-header-title">Distribución IMSS</h3>
                        <p class="dash-card-header-subtitle">Alta vs inactivos</p>
                    </div>
                    <div class="dash-chart-wrapper">
                        <canvas id="chart-imss"></canvas>
                    </div>
                    <div class="dash-chart-legend">
                        <div class="dash-chart-legend-item">
                            <span class="dash-legend-dot dash-legend-dot-blue"></span>
                            <span>Alta IMSS</span>
                            <span class="dash-legend-value">
                                {{ $empleadosActivos }} / {{ $totalImss ?: 0 }}
                            </span>
                        </div>
                        <div class="dash-chart-legend-item">
                            <span class="dash-legend-dot dash-legend-dot-pink"></span>
                            <span>Inactivos</span>
                            <span class="dash-legend-value">
                                {{ $empleadosInactivos }} / {{ $totalImss ?: 0 }}
                            </span>
                        </div>
                    </div>
                </article>

                {{-- Altas y reingresos últimos meses (línea) --}}
                <article class="dash-card animate-fade-up" style="animation-delay:.05s">
                    <div class="dash-card-header">
                        <h3 class="dash-card-header-title">Altas y reingresos últimos meses</h3>
                        <p class="dash-card-header-subtitle">Histórico de los últimos 6 meses</p>
                    </div>
                    <div class="dash-chart-wrapper">
                        <canvas id="chart-altas"></canvas>
                    </div>
                </article>
            </section>

            {{-- Top patrones / sucursales --}}
            <section class="dash-grid-2 mt-6 lg:mt-8">
                {{-- Top patrones (tabla + barras) --}}
                <article class="dash-card animate-fade-up">
                    <div class="dash-card-header">
                        <h3 class="dash-card-header-title">Top patrones</h3>
                        <p class="dash-card-header-subtitle">Por número de empleados</p>
                    </div>

                    <div class="dash-table-wrapper">
                        <table class="dash-table">
                            <thead>
                                <tr>
                                    <th>Patrón</th>
                                    <th class="text-right">Empleados</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $maxPatron = max($topPatrones->pluck('empleados_count')->toArray() ?: [0]);
                                @endphp
                                @forelse ($topPatrones as $p)
                                    @php
                                        $pct = $maxPatron > 0
                                            ? round(($p->empleados_count / $maxPatron) * 100)
                                            : 0;
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="dash-bar-label">
                                                <span>{{ $p->nombre }}</span>
                                                <span class="dash-bar-track">
                                                    <span class="dash-bar-fill" style="--bar-width: {{ $pct }}%"></span>
                                                </span>
                                            </div>
                                        </td>
                                        <td class="text-right">
                                            {{ $p->empleados_count }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="dash-table-empty">
                                            Aún no hay patrones con empleados asignados.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </article>

                {{-- Top sucursales (tabla + barras) --}}
                <article class="dash-card animate-fade-up" style="animation-delay:.05s">
                    <div class="dash-card-header">
                        <h3 class="dash-card-header-title">Top sucursales</h3>
                        <p class="dash-card-header-subtitle">Plazas con mayor headcount</p>
                    </div>

                    <div class="dash-table-wrapper">
                        <table class="dash-table">
                            <thead>
                                <tr>
                                    <th>Sucursal</th>
                                    <th class="text-right">Empleados</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $maxSuc = max($topSucursales->pluck('empleados_count')->toArray() ?: [0]);
                                @endphp
                                @forelse ($topSucursales as $s)
                                    @php
                                        $pct = $maxSuc > 0
                                            ? round(($s->empleados_count / $maxSuc) * 100)
                                            : 0;
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="dash-bar-label">
                                                <span>{{ $s->nombre }}</span>
                                                <span class="dash-bar-track">
                                                    <span class="dash-bar-fill dash-bar-fill-alt" style="--bar-width: {{ $pct }}%"></span>
                                                </span>
                                            </div>
                                        </td>
                                        <td class="text-right">
                                            {{ $s->empleados_count }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="dash-table-empty">
                                            Aún no hay sucursales con empleados asignados.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </article>
            </section>
        </div>
    </div>

    @push('scripts')
        {{-- Chart.js desde CDN --}}
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const dashboardData = @json($dashboardData);

            document.addEventListener('DOMContentLoaded', () => {
                const charts = dashboardData.charts || {};

                // === Donut IMSS ===
                const imssCfg = charts.empleadosEstado || {};
                const ctxImss = document.getElementById('chart-imss');
                if (ctxImss && imssCfg.data && imssCfg.data.length) {
                    new Chart(ctxImss, {
                        type: 'doughnut',
                        data: {
                            labels: imssCfg.labels,
                            datasets: [{
                                data: imssCfg.data,
                                backgroundColor: ['#2563eb', '#ec4899'],
                                hoverOffset: 6
                            }]
                        },
                        options: {
                            plugins: {
                                legend: { display: false }
                            },
                            cutout: '70%'
                        }
                    });
                }

                // === Línea altas / reingresos ===
                const altasCfg = charts.altasMensuales || {};
                const ctxAltas = document.getElementById('chart-altas');
                if (ctxAltas && altasCfg.data && altasCfg.data.length) {
                    new Chart(ctxAltas, {
                        type: 'line',
                        data: {
                            labels: altasCfg.labels,
                            datasets: [{
                                label: 'Altas + reingresos',
                                data: altasCfg.data,
                                tension: 0.35,
                                borderWidth: 2,
                                pointRadius: 3,
                            }]
                        },
                        options: {
                            plugins: {
                                legend: { display: false }
                            },
                            scales: {
                                x: {
                                    grid: { display: false }
                                },
                                y: {
                                    beginAtZero: true,
                                    ticks: { precision: 0 }
                                }
                            }
                        }
                    });
                }
            });
        </script>
    @endpush
</x-app-layout>
