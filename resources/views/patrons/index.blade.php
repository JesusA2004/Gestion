{{-- resources/views/patrons/index.blade.php --}}
<x-app-layout>
    <div class="py-8">
        {{-- Contenedor centrado, más amplio para que respire bien --}}
        <div class="max-w-5xl mx-auto px-3 sm:px-6 lg:px-8 space-y-6">
            {{-- Encabezado --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight">
                        Patrones (empresas)
                    </h1>
                    <p class="mt-1 text-sm sm:text-base text-gray-500 max-w-xl">
                        Administración básica de empresas: nombre y fechas de creación / modificación.
                    </p>
                </div>
            </div>

            {{-- Mensaje de estado --}}
            @if(session('status'))
                <div class="rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Filtro en tiempo real (solo front) --}}
            <div class="rounded-2xl bg-white/80 backdrop-blur shadow-sm border border-slate-200 px-4 py-4 sm:px-6 sm:py-5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                    <div class="flex-1">
                        <label for="patron-search-text" class="block text-xs font-semibold text-slate-500 uppercase tracking-wide">
                            Buscar por nombre
                        </label>
                        <div class="mt-1 relative">
                            <input
                                id="patron-search-text"
                                type="text"
                                placeholder="Escribe parte del nombre de la empresa..."
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3 py-2 pr-9 text-sm text-slate-800 shadow-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            >
                            <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z"/>
                                </svg>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            @if(auth()->user()->role === 'admin')
                {{-- Alta rápida (se queda con POST normal) --}}
                <div class="rounded-2xl bg-white shadow-sm border border-slate-200 px-4 py-4 sm:px-6 sm:py-5">
                    <h2 class="text-sm font-semibold text-slate-800 mb-3">
                        Nuevo patrón / empresa
                    </h2>
                    <form
                        method="POST"
                        action="{{ route('patrons.store') }}"
                        class="flex flex-col sm:flex-row gap-3 sm:items-end"
                    >
                        @csrf
                        <div class="flex-1">
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide">
                                Nombre de la empresa
                            </label>
                            <input
                                type="text"
                                name="nombre"
                                value="{{ old('nombre') }}"
                                class="mt-1 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3 py-2 text-sm text-slate-800 shadow-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            >
                            @error('nombre')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:w-40">
                            @if(auth()->user()->role === 'admin')
                                <button
                                    type="submit"
                                    class="w-full rounded-xl bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                                >
                                    Guardar
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
            @endif

            {{-- Tabla --}}
            <div class="overflow-hidden rounded-2xl bg-white shadow-md shadow-slate-200/60 border border-slate-100">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50/80">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">
                                Nombre
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">
                                Fechas (creación / modificación)
                            </th>
                            @if(auth()->user()->role === 'admin')
                                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">
                                    Acciones
                                </th>
                            @endif
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-slate-100 bg-white"
                        data-patrons
                    >
                        @forelse($patrones as $patron)
                            <tr
                                class="patron-row"
                                data-patron-row
                                data-nombre="{{ strtolower($patron->nombre) }}"
                            >
                                <td class="px-4 py-3 align-top">
                                    <span class="font-semibold text-slate-900">
                                        {{ $patron->nombre }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 align-top text-xs sm:text-sm text-slate-700">
                                    <div class="space-y-1">
                                        <div>
                                            <span class="font-semibold text-slate-800">Creado:</span>
                                            <span class="ml-1">
                                                {{ optional($patron->created_at)->format('d-m-y H:i') }}
                                            </span>
                                        </div>
                                        <div>
                                            <span class="font-semibold text-slate-800">Modificado:</span>
                                            <span class="ml-1">
                                                {{ optional($patron->updated_at)->format('d-m-y H:i') }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 align-top text-right">
                                    <div class="inline-flex flex-wrap justify-end gap-2">
                                        {{-- EDITAR --}}
                                        <form
                                            method="POST"
                                            action="{{ route('patrons.update', $patron) }}"
                                            class="inline-flex gap-2 items-center"
                                        >
                                            @csrf
                                            @method('PUT')

                                            <input
                                                type="hidden"
                                                name="nombre"
                                                value="{{ $patron->nombre }}"
                                            >
                                            @if(auth()->user()->role === 'admin')
                                                <button
                                                    type="button"
                                                    class="inline-flex items-center gap-1.5 rounded-full bg-amber-400/90 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500"
                                                    data-nombre="{{ $patron->nombre }}"
                                                    onclick="openEditPatronModal(this)"
                                                >
                                                    Editar
                                                </button>
                                            @endif
                                        </form>

                                        {{-- ELIMINAR --}}
                                        <form
                                            method="POST"
                                            action="{{ route('patrons.destroy', $patron) }}"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            @if(auth()->user()->role === 'admin')
                                                <button
                                                    type="button"
                                                    class="inline-flex items-center gap-1.5 rounded-full bg-rose-500/90 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-rose-600 focus:outline-none focus:ring-1 focus:ring-rose-500"
                                                    onclick="confirmDeletePatron(this)"
                                                >
                                                    Eliminar
                                                </button>
                                            @endif
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-8 text-center text-sm text-slate-500">
                                    No hay patrones registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="px-4 py-3 border-t border-slate-100 bg-slate-50/60">
                    {{ $patrones->links() }}
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <link href="{{ asset('css/patrons.css') }}" rel="stylesheet">
    @endpush

    @push('scripts')
        <script>
            window.PatronsConfig = {
                baseUrl: '{{ url('') }}',
            };
        </script>
        <script src="{{ asset('js/patrons.js') }}"></script>
    @endpush
</x-app-layout>
