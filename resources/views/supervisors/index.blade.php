{{-- resources/views/supervisors/index.blade.php --}}
<x-app-layout>
    <div class="py-8">
        <div class="w-full px-3 sm:px-4 lg:px-8">
            <div class="max-w-7xl mx-auto space-y-6">

                {{-- Encabezado + botón --}}
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="text-3xl lg:text-4xl font-bold text-gray-900 tracking-tight">
                            Supervisores
                        </h1>
                        <p class="mt-2 text-sm md:text-base text-gray-500 max-w-xl">
                            Gestión de supervisores: alta, edición y eliminación en una sola pantalla.
                        </p>
                    </div>

                    @if(auth()->user()->role === 'admin')
                        <button
                            type="button"
                            onclick="openCreateSupervisorModal()"
                            class="inline-flex items-center gap-2 rounded-2xl bg-indigo-600 px-5 py-2.5 text-sm md:text-base font-semibold text-white shadow-md shadow-indigo-500/30 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                            </svg>
                            Nuevo supervisor
                        </button>
                    @endif
                </div>

                {{-- Filtros en tiempo real --}}
                <div class="rounded-2xl bg-white/90 backdrop-blur shadow-sm border border-slate-200 px-4 py-4 sm:px-6 sm:py-5">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:items-end">
                        {{-- Buscador texto (ocupa 2/3 en desktop) --}}
                        <div class="md:col-span-2">
                            <label for="supervisor-search-text" class="block text-[11px] md:text-xs font-semibold text-slate-500 uppercase tracking-wide">
                                Buscar por nombre completo
                            </label>
                            <div class="mt-1 relative">
                                <input
                                    id="supervisor-search-text"
                                    type="text"
                                    placeholder="Escribe cualquier parte del nombre o apellidos..."
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 pr-9 text-sm md:text-base text-slate-800 shadow-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                >
                                <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400">
                                    <svg class="h-4 w-4 md:h-5 md:w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z"/>
                                    </svg>
                                </span>
                            </div>
                        </div>

                        {{-- Filtro fecha --}}
                        <div>
                            <label for="supervisor-search-date" class="block text-[11px] md:text-xs font-semibold text-slate-500 uppercase tracking-wide">
                                Filtrar por fecha de creación (YYYY-MM-DD)
                            </label>
                            <div class="mt-1">
                                <input
                                    id="supervisor-search-date"
                                    type="date"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm md:text-base text-slate-800 shadow-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                >
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tabla --}}
                <div class="overflow-hidden rounded-2xl bg-white shadow-md shadow-slate-200/60 border border-slate-100">
                    <table class="min-w-full divide-y divide-slate-200 text-[13px] md:text-sm">
                        <thead class="bg-slate-50/80">
                            <tr>
                                <th class="px-4 py-3 md:py-4 text-left text-[11px] md:text-xs font-semibold text-slate-500 uppercase tracking-wide">
                                    Supervisor
                                </th>
                                <th class="px-4 py-3 md:py-4 text-left text-[11px] md:text-xs font-semibold text-slate-500 uppercase tracking-wide">
                                    Fechas (creación / modificación)
                                </th>
                                @if(auth()->user()->role === 'admin')
                                    <th class="px-4 py-3 md:py-4 text-right text-[11px] md:text-xs font-semibold text-slate-500 uppercase tracking-wide">
                                        Acciones
                                    </th>
                                @endif
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-slate-100 bg-white"
                            data-supervisors
                        >
                            @forelse($supervisors as $supervisor)
                                <tr
                                    class="supervisor-row hover:bg-indigo-50/60 transition-colors"
                                    data-supervisor-row
                                    data-full-name="{{ strtolower(trim($supervisor->nombres . ' ' . $supervisor->apellidoPaterno . ' ' . $supervisor->apellidoMaterno)) }}"
                                    data-created-at="{{ optional($supervisor->created_at)->format('Y-m-d') }}"
                                >
                                    <td class="px-4 py-4 md:py-5 align-top">
                                        <div class="flex flex-col gap-1.5">
                                            <span class="font-semibold text-slate-900 text-sm md:text-base">
                                                {{ $supervisor->nombres }}
                                                {{ $supervisor->apellidoPaterno }}
                                                {{ $supervisor->apellidoMaterno }}
                                            </span>
                                        </div>
                                    </td>

                                    <td class="px-4 py-4 md:py-5 align-top text-sm md:text-base text-slate-700">
                                        <div class="space-y-1.5">
                                            <div>
                                                <span class="block font-semibold text-slate-800">
                                                    Creado el
                                                    {{ optional($supervisor->created_at)->format('d-m-y') }}
                                                    a las
                                                    {{ optional($supervisor->created_at)->format('H:i') }} hrs
                                                </span>
                                            </div>
                                            <div class="text-slate-600">
                                                <span class="block font-semibold text-slate-800">
                                                    Modificado el
                                                    {{ optional($supervisor->updated_at)->format('d-m-y') }}
                                                    a las
                                                    {{ optional($supervisor->updated_at)->format('H:i') }} hrs
                                                </span>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-4 py-4 md:py-5 align-top text-right">
                                        <div class="inline-flex flex-wrap justify-end gap-2 md:gap-3">
                                            @if(auth()->user()->role === 'admin')
                                                {{-- Botón editar --}}
                                                <button
                                                    type="button"
                                                    data-id="{{ $supervisor->id }}"
                                                    data-nombres="{{ $supervisor->nombres }}"
                                                    data-apellido-paterno="{{ $supervisor->apellidoPaterno }}"
                                                    data-apellido-materno="{{ $supervisor->apellidoMaterno }}"
                                                    onclick="openEditSupervisorModal(this)"
                                                    class="inline-flex items-center gap-1.5 rounded-full bg-amber-400/90 px-3.5 md:px-4 py-1.5 md:py-2 text-[11px] md:text-xs font-semibold text-white shadow-sm hover:bg-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500"
                                                >
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M16.862 4.487l1.687-1.687a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/>
                                                    </svg>
                                                    Editar
                                                </button>
                                            @endif
                                            @if(auth()->user()->role === 'admin')
                                                {{-- Botón eliminar --}}
                                                <button
                                                    type="button"
                                                    onclick="confirmDeleteSupervisor({{ $supervisor->id }})"
                                                    class="inline-flex items-center gap-1.5 rounded-full bg-rose-500/90 px-3.5 md:px-4 py-1.5 md:py-2 text-[11px] md:text-xs font-semibold text-white shadow-sm hover:bg-rose-600 focus:outline-none focus:ring-1 focus:ring-rose-500"
                                                >
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M6 7h12M10 11v6m4-6v6M9 7V5a2 2 0 012-2h2a2 2 0 012 2v2M6 7l1 11a2 2 0 002 2h6a2 2 0 002-2l1-11"/>
                                                    </svg>
                                                    Eliminar
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-8 text-center text-sm md:text-base text-slate-500">
                                        No hay supervisores registrados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="px-4 py-3 border-t border-slate-100 bg-slate-50/60">
                        {{ $supervisors->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            window.SupervisorsConfig = {
                baseUrl: '{{ url('') }}',
                csrfToken: '{{ csrf_token() }}',
            };
        </script>
        <script src="{{ asset('js/supervisors.js') }}"></script>
    @endpush
</x-app-layout>
