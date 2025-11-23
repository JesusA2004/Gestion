// public/js/reportes.js
document.addEventListener('DOMContentLoaded', function () {
    const cfg = window.ReportesConfig || {};
    const state = {
        currentTipo: 'guardias_estado',
        metrics: cfg.initialData?.metrics || {
            total_guardias: 0,
            total_plazas: 0,
            total_supervisores: 0,
        },
        reportes: cfg.initialData?.reportes || {
            guardias_estado: [],
            guardias_supervisor: [],
            guardias_imss: [],
        },
    };

    const dataUrl = cfg.dataUrl;
    const exportUrl = cfg.exportUrl;

    // ====== DOM refs ======
    const filters = document.querySelectorAll('[data-report-filter]');
    const imssButtons = document.querySelectorAll('[data-report-imss]');
    const btnReset = document.getElementById('btn-reset-filters');
    const tabs = document.querySelectorAll('.report-tab');
    const btnExport = document.getElementById('btn-export-excel');

    const metricTotal = document.getElementById('metric-total-guardias');
    const metricPlazas = document.getElementById('metric-total-plazas');
    const metricSupervisores = document.getElementById('metric-total-supervisores');

    const chartTitle = document.getElementById('chart-title');
    const chartSubtitle = document.getElementById('chart-subtitle');
    const chartEmpty = document.getElementById('chart-empty');
    const tableTitle = document.getElementById('table-title');
    const tableHead = document.getElementById('table-head');
    const tableBody = document.getElementById('table-body');
    const ctx = document.getElementById('reportChart');

    let chartInstance = null;

    // ====== Utils ======
    function getFilters() {
        const params = new URLSearchParams();
        filters.forEach(el => {
            if (!el.name) return;
            const val = el.value ?? '';
            if (val !== '') {
                params.append(el.name, val);
            }
        });
        // tipo de reporte actual
        params.set('tipo_reporte', state.currentTipo);
        return params;
    }

    function debounce(fn, delay) {
        let t;
        return function (...args) {
            clearTimeout(t);
            t = setTimeout(() => fn.apply(this, args), delay);
        };
    }

    // ====== Render: métricas ======
    function renderMetrics() {
        if (metricTotal) metricTotal.textContent = state.metrics.total_guardias ?? 0;
        if (metricPlazas) metricPlazas.textContent = state.metrics.total_plazas ?? 0;
        if (metricSupervisores) metricSupervisores.textContent = state.metrics.total_supervisores ?? 0;
    }

    // ====== Render: tabla y textos según tipo reporte ======
    function getActiveDataset() {
        const all = state.reportes || {};
        return all[state.currentTipo] || [];
    }

    function renderTableAndText() {
        const tipo = state.currentTipo;
        const data = getActiveDataset();

        // Títulos
        if (tipo === 'guardias_estado') {
            chartTitle.textContent = 'Distribución por estado';
            chartSubtitle.textContent = 'Guardias agrupadas por estado / plaza.';
            tableTitle.textContent = 'Detalle por estado';
            tableHead.innerHTML = `
                <tr>
                    <th>Estado</th>
                    <th>Guardias</th>
                </tr>`;
        } else if (tipo === 'guardias_supervisor') {
            chartTitle.textContent = 'Distribución por supervisor';
            chartSubtitle.textContent = 'Guardias agrupadas por supervisor responsable.';
            tableTitle.textContent = 'Detalle por supervisor';
            tableHead.innerHTML = `
                <tr>
                    <th>Supervisor</th>
                    <th>Guardias</th>
                </tr>`;
        } else {
            chartTitle.textContent = 'Guardias con IMSS / sin IMSS';
            chartSubtitle.textContent = 'Distribución de guardias por empresa y situación ante IMSS.';
            tableTitle.textContent = 'Detalle por empresa e IMSS';
            tableHead.innerHTML = `
                <tr>
                    <th>Empresa</th>
                    <th>Tipo</th>
                    <th>Guardias</th>
                    <th>%</th>
                </tr>`;
        }

        // Body
        let html = '';
        if (!data.length) {
            html = `
                <tr>
                    <td colspan="${tipo === 'guardias_imss' ? 4 : 2}"
                        class="px-3 py-3 text-center text-xs text-slate-500">
                        No hay registros con los filtros actuales.
                    </td>
                </tr>`;
        } else {
            if (tipo === 'guardias_imss') {
                data.forEach(row => {
                    html += `
                        <tr class="row-soft">
                            <td>${row.empresa}</td>
                            <td>${row.tipo}</td>
                            <td>${row.total}</td>
                            <td>${row.porcentaje.toFixed ? row.porcentaje.toFixed(2) : row.porcentaje}</td>
                        </tr>`;
                });
            } else {
                data.forEach(row => {
                    html += `
                        <tr class="row-soft">
                            <td>${row.label}</td>
                            <td>${row.total}</td>
                        </tr>`;
                });
            }
        }

        tableBody.innerHTML = html;
    }

    // ====== Render: Chart.js ======
    function renderChart() {
        const data = getActiveDataset();

        if (!ctx) return;

        if (!data.length) {
            if (chartEmpty) {
                chartEmpty.style.opacity = 1;
                chartEmpty.style.pointerEvents = 'auto';
            }
            if (chartInstance) {
                chartInstance.destroy();
                chartInstance = null;
            }
            return;
        }

        if (chartEmpty) {
            chartEmpty.style.opacity = 0;
            chartEmpty.style.pointerEvents = 'none';
        }

        const tipo = state.currentTipo;
        let labels = [];
        let values = [];

        if (tipo === 'guardias_imss') {
            labels = data.map(r => `${r.empresa} - ${r.tipo}`);
            values = data.map(r => r.total);
        } else {
            labels = data.map(r => r.label);
            values = data.map(r => r.total);
        }

        if (chartInstance) {
            chartInstance.destroy();
        }

        const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 200);
        gradient.addColorStop(0, 'rgba(79,70,229,0.9)');
        gradient.addColorStop(1, 'rgba(129,140,248,0.2)');

        chartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Guardias',
                    data: values,
                    borderWidth: 1.5,
                    borderRadius: 7,
                    backgroundColor: gradient,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(15,23,42,0.95)',
                        borderColor: 'rgba(148,163,184,0.6)',
                        borderWidth: 1,
                        padding: 10,
                        titleColor: '#e5e7eb',
                        bodyColor: '#e5e7eb',
                        displayColors: false,
                        callbacks: {
                            label: function (context) {
                                return ' Guardias: ' + context.parsed.y;
                            }
                        }
                    },
                },
                scales: {
                    x: {
                        ticks: {
                            color: '#6b7280',
                            font: { size: 10 },
                        },
                        grid: { display: false },
                    },
                    y: {
                        ticks: {
                            color: '#6b7280',
                            font: { size: 10 },
                            precision: 0,
                            stepSize: 1,
                        },
                        grid: {
                            color: 'rgba(209,213,219,0.7)',
                        },
                        beginAtZero: true,
                    },
                },
                animation: {
                    duration: 450,
                },
            },
        });
    }

    // ====== Fetch en tiempo real ======
    const fetchData = debounce(function () {
        if (!dataUrl) return;

        const params = getFilters();

        fetch(dataUrl + '?' + params.toString(), {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then(res => res.json())
            .then(json => {
                if (!json) return;
                state.metrics = json.metrics || state.metrics;
                state.reportes = json.reportes || state.reportes;

                renderMetrics();
                renderTableAndText();
                renderChart();
            })
            .catch(() => {
                // silencio; si algo falla, mantenemos el último estado
            });
    }, 300);

    // ====== Listeners de filtros ======
    filters.forEach(el => {
        const evt = (el.tagName === 'SELECT' || el.type === 'date' || el.type === 'checkbox')
            ? 'change'
            : 'input';

        el.addEventListener(evt, fetchData);
    });

    // IMSS pills
    imssButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const value = btn.getAttribute('data-imss-value') || '';

            imssButtons.forEach(b => b.classList.remove('pill-toggle-active'));
            btn.classList.add('pill-toggle-active');

            const hiddenImss = document.querySelector('input[name="imss_estado"][data-report-filter]');
            if (hiddenImss) {
                hiddenImss.value = value;
            }
            fetchData();
        });
    });

    // Reset filtros
    if (btnReset) {
        btnReset.addEventListener('click', () => {
            filters.forEach(el => {
                if (el.type === 'checkbox') {
                    el.checked = false;
                } else {
                    el.value = '';
                }
            });

            // reset IMSS pills
            imssButtons.forEach(b => b.classList.remove('pill-toggle-active'));
            const first = document.querySelector('[data-report-imss][data-imss-value=""]');
            if (first) first.classList.add('pill-toggle-active');

            const hiddenImss = document.querySelector('input[name="imss_estado"][data-report-filter]');
            if (hiddenImss) hiddenImss.value = '';
            fetchData();
        });
    }

    // Tabs de reporte
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const tipo = tab.getAttribute('data-report-type');
            if (!tipo || tipo === state.currentTipo) return;

            state.currentTipo = tipo;

            tabs.forEach(t => t.classList.remove('report-tab-active'));
            tab.classList.add('report-tab-active');

            // Cambia solo vista (la data ya está en memoria). No hace falta nuevo fetch.
            renderTableAndText();
            renderChart();
        });
    });

    // Exportar Excel
    if (btnExport) {
        btnExport.addEventListener('click', () => {
            if (!exportUrl) return;
            const params = getFilters();
            // Forzamos tipo_reporte por si acaso
            params.set('tipo_reporte', state.currentTipo);
            window.location.href = exportUrl + '?' + params.toString();
        });
    }

    // ====== Render inicial ======
    renderMetrics();
    renderTableAndText();
    renderChart();
});
