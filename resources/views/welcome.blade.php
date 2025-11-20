<x-guest-layout>
    <div class="max-w-6xl mx-auto px-4 pt-24 pb-10">
        <div class="grid lg:grid-cols-[1.1fr,0.9fr] gap-8 items-start">
            {{-- Columna izquierda: Hero --}}
            <section class="space-y-4">
                <div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-400/40 text-emerald-300 text-xs font-medium">
                        Sistema de Gestión de Empleados
                    </span>
                </div>

                <h1 class="text-3xl sm:text-4xl font-bold tracking-tight text-slate-50">
                    Administra el ciclo laboral de tus empleados desde un solo lugar.
                </h1>

                <p class="text-sm sm:text-base text-slate-300 leading-relaxed max-w-xl">
                    Registra altas, gestiona bajas lógicas y conserva la información oficial
                    que necesitas para cumplir con IMSS, nómina y control interno de tu empresa.
                </p>

                <div class="flex flex-wrap gap-3 pt-2">
                    <a href="{{ route('login') }}"
                       class="inline-flex items-center px-6 py-2.5 rounded-lg text-sm font-semibold bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 shadow-lg shadow-blue-900/40 transition-all duration-200">
                        Iniciar sesión
                    </a>

                    <a href="{{ route('register') }}"
                        class="inline-flex items-center px-6 py-2.5 rounded-xl text-sm font-semibold bg-white/10 backdrop-blur-md hover:bg-white/20 border border-white/20 shadow-md shadow-black/20 transition">
                            Crear cuenta
                    </a>
                </div>

                <div class="mt-4 text-xs text-slate-400">
                    Controla empleados por sucursal, patrón y situación laboral, manteniendo
                    un historial claro de cada movimiento.
                </div>
            </section>

            {{-- Columna derecha: tarjeta con campos del empleado --}}
            <section class="bg-slate-900/80 border border-slate-700 rounded-2xl shadow-xl shadow-black/40 p-6 sm:p-7">
                <h2 class="text-sm font-semibold text-slate-100 mb-4 uppercase tracking-wide">
                    Información registrada por empleado
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-1.5 text-xs sm:text-sm text-slate-200">
                    <span>• ID interno</span>
                    <span>• Nombre completo</span>
                    <span>• Registro patronal</span>
                    <span>• Patrón asociado (relación a catálogo)</span>
                    <span>• Número de IMSS</span>
                    <span>• CURP</span>
                    <span>• RFC</span>
                    <span>• Fecha de ingreso</span>
                    <span>• Fecha de baja</span>
                    <span>• Periodo vacacional (días enteros)</span>
                    <span>• Estado laboral (alta / baja)</span>
                    <span>• Pago de prima vacacional</span>
                    <span>• Número de trabajador</span>
                    <span>• Fecha de alta ante el IMSS</span>
                    <span>• Sucursal (relación a catálogo)</span>
                </div>

                <p class="mt-4 text-[11px] sm:text-xs text-slate-400 leading-relaxed">
                    Los campos como <span class="font-semibold text-slate-200">patrón</span> y
                    <span class="font-semibold text-slate-200">sucursal</span> se manejan como
                    relaciones hacia sus catálogos, lo que permite reutilizar información y
                    mantener la base de datos consistente.
                </p>
            </section>
        </div>
    </div>
</x-guest-layout>
