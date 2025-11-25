// public/js/dashboard.js
(function () {
    document.addEventListener("DOMContentLoaded", function () {
        if (typeof Chart === "undefined") {
            console.warn("Chart.js no está cargado");
            return;
        }

        const data = window.DashboardData || {};
        const charts = data.charts || {};

        /* ============ Donut estado IMSS ============ */
        const ctxEstado = document.getElementById("chart-empleados-estado");
        if (ctxEstado && charts.empleadosEstado) {
            const ds = charts.empleadosEstado;

            new Chart(ctxEstado.getContext("2d"), {
                type: "doughnut",
                data: {
                    labels: ds.labels,
                    datasets: [
                        {
                            data: ds.data,
                            borderWidth: 0,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: "bottom",
                            labels: {
                                usePointStyle: true,
                            },
                        },
                        tooltip: {
                            callbacks: {
                                label: function (ctx) {
                                    const label = ctx.label || "";
                                    const value = ctx.parsed || 0;
                                    return `${label}: ${value}`;
                                },
                            },
                        },
                    },
                    cutout: "68%",
                },
            });
        }

        /* ============ Línea altas mensuales ============ */
        const ctxAltas = document.getElementById("chart-altas-mensuales");
        if (ctxAltas && charts.altasMensuales) {
            const ds = charts.altasMensuales;

            new Chart(ctxAltas.getContext("2d"), {
                type: "line",
                data: {
                    labels: ds.labels,
                    datasets: [
                        {
                            label: "Altas / reingresos",
                            data: ds.data,
                            tension: 0.35,
                            fill: true,
                            borderWidth: 2,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false,
                        },
                        tooltip: {
                            callbacks: {
                                label: function (ctx) {
                                    return ` ${ctx.parsed.y} altas`;
                                },
                            },
                        },
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false,
                            },
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                            },
                        },
                    },
                },
            });
        }

        /* ============ Barras empleados por patrón ============ */
        const ctxPatron = document.getElementById("chart-empleados-patron");
        if (ctxPatron && charts.empleadosPorPatron) {
            const ds = charts.empleadosPorPatron;

            new Chart(ctxPatron.getContext("2d"), {
                type: "bar",
                data: {
                    labels: ds.labels,
                    datasets: [
                        {
                            label: "Empleados",
                            data: ds.data,
                            borderWidth: 1.5,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false,
                        },
                        tooltip: {
                            callbacks: {
                                label: function (ctx) {
                                    return ` ${ctx.parsed.y} empleados`;
                                },
                            },
                        },
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false,
                            },
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                            },
                        },
                    },
                },
            });
        }
    });
})();
