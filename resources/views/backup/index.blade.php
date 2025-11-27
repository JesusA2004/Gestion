{{-- resources/views/backup/index.blade.php --}}

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Respaldo, Restauración e Importación de Datos
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Mensajes de estado --}}
            @if(session('status'))
                <div class="mb-4 p-3 rounded bg-green-100 text-green-800 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 p-3 rounded bg-red-100 text-red-800 text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                {{-- DESCARGAR RESPALDO SQL --}}
                <div class="bg-white shadow sm:rounded-lg p-6 border border-gray-100">
                    <h3 class="font-semibold text-lg mb-3">
                        Descargar respaldo (.sql)
                    </h3>

                    <p class="text-sm text-gray-600 mb-4">
                        Descarga el archivo SQL de respaldo actual de la base de datos de empleados.
                    </p>

                    <a href="{{ route('backup.download') }}"
                       class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Descargar respaldo
                    </a>
                </div>

                {{-- RESTAURAR DESDE ARCHIVO SQL --}}
                <div class="bg-white shadow sm:rounded-lg p-6 border border-gray-100">
                    <h3 class="font-semibold text-lg mb-3">
                        Restaurar base de datos (.sql)
                    </h3>

                    <p class="text-sm text-gray-600 mb-4">
                        Sube un archivo <strong>.sql</strong> para restaurar completamente la base de datos.
                        Esta acción puede sobrescribir información existente.
                    </p>

                    <form action="{{ route('backup.restore') }}"
                          method="POST"
                          enctype="multipart/form-data"
                          class="space-y-4">
                        @csrf

                        <div>
                            <input type="file"
                                   name="archivo"
                                   accept=".sql,.txt"
                                   required
                                   class="block w-full text-sm text-gray-700 border border-gray-300 rounded-md p-2 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            Restaurar base de datos
                        </button>
                    </form>
                </div>

                {{-- IMPORTAR DATOS DESDE EXCEL BD TRABAJADORES --}}
                <div class="bg-white shadow sm:rounded-lg p-6 border border-gray-100">
                    <h3 class="font-semibold text-lg mb-3">
                        Importar datos desde Excel (BD trabajadores)
                    </h3>

                    <p class="text-sm text-gray-600 mb-4">
                        Sube el archivo de <strong>BASE DE DATOS GENERAL TRABAJADORES</strong>
                        (hoja <strong>BD</strong>) en formato <strong>.xlsx, .xls o .xlsm</strong>.
                        El sistema validará el formato y poblará las tablas de patrones, sucursales,
                        departamentos, supervisores y empleados.
                    </p>

                    <form action="{{ route('backup.importExcel') }}"
                          method="POST"
                          enctype="multipart/form-data"
                          class="space-y-4"
                          x-data="{ loading: false, progress: 0, timerId: null }"
                          x-on:submit="
                              loading = true;
                              progress = 5;
                              timerId = setInterval(() => {
                                  if (progress < 90) {
                                      progress += 5;
                                  }
                              }, 800);
                          ">
                        @csrf

                        <div>
                            <input type="file"
                                   name="archivo"
                                   accept=".xlsx,.xls,.xlsm"
                                   required
                                   class="block w-full text-sm text-gray-700 border border-gray-300 rounded-md p-2 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-60"
                                :disabled="loading">
                            <span x-show="!loading">Importar desde Excel</span>
                            <span x-show="loading" class="flex items-center gap-2">
                                Importando...
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                                     viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                          d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                </svg>
                            </span>
                        </button>

                        {{-- Barra de progreso visible solo mientras loading = true --}}
                        <div class="mt-4 space-y-1" x-show="loading">
                            <div class="flex items-center justify-between text-xs text-gray-600">
                                <span>Procesando archivo e importando empleados...</span>
                                <span x-text="progress + '%'"></span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                                <div class="h-2 bg-green-500 rounded-full transition-all duration-300"
                                     :style="`width: ${progress}%`"></div>
                            </div>
                            <p class="text-[11px] text-gray-500">
                                No cierres ni recargues la página hasta que el proceso termine.
                            </p>
                        </div>
                    </form>

                    <p class="mt-3 text-xs text-gray-500">
                        El archivo debe contener, al menos, las columnas:
                        NUMERO DE TRABAJADOR NOI, NOMBRE, PATRON, PLAZA, IMSS, NSS, RFC, CURP,
                        Fecha de Alta IMSS, SDI, FECHA DE BAJA, SUPERVISOR y Departamento.
                        Si falta alguna de estas columnas, se mostrará un mensaje indicando que el formato no es el adecuado.
                    </p>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
