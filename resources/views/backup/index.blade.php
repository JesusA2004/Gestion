{{-- resources/views/backup/index.blade.php --}}

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Respaldo y Restauración de Base de Datos
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

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- DESCARGAR RESPALDO --}}
                <div class="bg-white shadow sm:rounded-lg p-6 border border-gray-100">
                    <h3 class="font-semibold text-lg mb-3">
                        Descargar respaldo
                    </h3>

                    <p class="text-sm text-gray-600 mb-4">
                        Descarga el archivo SQL de respaldo actual de la base de datos.
                    </p>

                    <a href="{{ route('backup.download') }}"
                       class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Descargar respaldo
                    </a>
                </div>

                {{-- RESTAURAR DESDE ARCHIVO --}}
                <div class="bg-white shadow sm:rounded-lg p-6 border border-gray-100">
                    <h3 class="font-semibold text-lg mb-3">
                        Restaurar base de datos
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

            </div>
        </div>
    </div>
</x-app-layout>
