<x-app-layout>
    {{-- Halaman utama Faktur untuk role TU. --}}
    <div class="py-4 sm:py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-4">
                <h1 class="text-2xl font-semibold text-slate-900">Faktur</h1>
                <p class="text-sm text-slate-500">Buat dan kelola faktur untuk siswa</p>
            </div>

            {{-- Baris filter + tombol tambah faktur --}}
            <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <form method="GET" action="{{ route('tu.faktur.index') }}" class="flex flex-1 flex-col gap-2 md:flex-row md:items-center">
                    <input
                        type="month"
                        name="bulan"
                        value="{{ $filters['bulan'] ?? '' }}"
                        placeholder="Filter Tahun dan Bulan"
                        class="block w-full rounded-md border-slate-300 text-sm focus:border-blue-600 focus:ring-blue-600 md:w-52"
                    />
                    <select
                        name="kelas"
                        class="block w-full rounded-md border-slate-300 text-sm focus:border-blue-600 focus:ring-blue-600 md:w-52"
                        onchange="this.form.submit()"
                    >
                        <option value="">Filter kelas</option>
                        @foreach (($kelasOptions ?? []) as $kelas)
                            <option value="{{ $kelas }}" @selected(($filters['kelas'] ?? '') === $kelas)>{{ $kelas }}</option>
                        @endforeach
                    </select>
                    <input
                        type="text"
                        name="search"
                        value="{{ $filters['search'] ?? '' }}"
                        placeholder="Cari nama faktur..."
                        class="block w-full rounded-md border-slate-300 text-sm focus:border-blue-600 focus:ring-blue-600 md:w-72"
                    />
                    <button type="submit" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                        Filter
                    </button>
                </form>

                <button
                    type="button"
                    data-modal-open="create-faktur-modal"
                    class="inline-flex items-center rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800"
                >
                    + Tambah Faktur
                </button>
            </div>

            {{-- Tabel daftar faktur hasil filter. --}}
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="overflow-x-auto rounded-lg border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                <th class="px-4 py-3">Nama Faktur</th>
                                <th class="px-4 py-3">Tipe Target</th>
                                <th class="px-4 py-3">Nama Target</th>
                                <th class="px-4 py-3">Nominal</th>
                                <th class="px-4 py-3">Tanggal Faktur</th>
                                <th class="px-4 py-3">Jatuh Tempo</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white text-sm text-slate-700">
                            @forelse ($fakturs as $faktur)
                                @php
                                    $fakturId = (string) $faktur->id;
                                    $targetLabel = match ($faktur->target_type) {
                                        'angkatan' => 'Per Angkatan',
                                        'kelas' => 'Per Kelas',
                                        'siswa' => 'Per Siswa',
                                        'semua_siswa' => 'Semua Siswa',
                                        default => ucfirst((string) $faktur->target_type),
                                    };
                                    $targetValueText = $faktur->target_value ?: 'Semua Siswa';
                                    if ($faktur->target_type === 'kelas' && str_contains($targetValueText, '|')) {
                                        [$thn, $kls] = explode('|', $targetValueText);
                                        $targetValueText = "Angkatan {$thn} - Kelas {$kls}";
                                    }
                                    
                                    $rawStatus = strtolower($faktur->status);
                                    if (in_array($rawStatus, ['pending', 'berlangsung'])) {
                                        $displayStatus = 'Pending';
                                        $statusClass = 'bg-slate-100 text-slate-700';
                                    } elseif ($rawStatus === 'selesai') {
                                        if ($faktur->last_exported_at) {
                                            $displayStatus = 'Aman';
                                            $statusClass = 'bg-emerald-100 text-emerald-800';
                                        } else {
                                            $displayStatus = 'Selesai';
                                            $statusClass = 'bg-amber-100 text-amber-800';
                                        }
                                    } elseif ($rawStatus === 'diarsipkan') {
                                        $displayStatus = 'Arsip';
                                        $statusClass = 'bg-emerald-100 text-emerald-800';
                                    } else {
                                        $displayStatus = ucfirst($rawStatus);
                                        $statusClass = 'bg-slate-100 text-slate-700';
                                    }
                                @endphp
                                <tr>
                                    <td class="whitespace-nowrap px-4 py-3">{{ $faktur->masterFaktur?->nama_faktur ?? '-' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <span class="inline-flex whitespace-nowrap rounded-full border border-slate-200 px-2 py-1 text-xs font-semibold text-slate-700">
                                            {{ $targetLabel }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">{{ $targetValueText }}</td>
                                    <td class="whitespace-nowrap px-4 py-3">Rp {{ number_format((int) ($faktur->masterFaktur?->nominal ?? 0), 0, ',', '.') }}</td>
                                    <td class="whitespace-nowrap px-4 py-3">{{ $faktur->tersedia_pada?->toDateString() }}</td>
                                    <td class="whitespace-nowrap px-4 py-3">{{ $faktur->jatuh_tempo?->toDateString() }}</td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <span class="inline-flex whitespace-nowrap rounded-full px-2 py-1 text-xs font-semibold {{ $statusClass }}">
                                            {{ $displayStatus }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="inline-flex items-center gap-2">
                                            <button type="button" data-modal-open="edit-faktur-modal-{{ $fakturId }}" class="rounded-md border border-slate-200 px-2 py-1 text-xs font-medium text-slate-700 hover:bg-slate-50">Edit</button>
                                            <button type="button" data-modal-open="delete-faktur-modal-{{ $fakturId }}" class="rounded-md border border-rose-200 px-2 py-1 text-xs font-medium text-rose-600 hover:bg-rose-50">Delete</button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-8 text-center text-sm text-slate-500">Belum ada data faktur.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    {{-- Pagination --}}
                    {{-- @if ($fakturs instanceof \Illuminate\Contracts\Pagination\Paginator || $fakturs instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator) --}}
                    @if (method_exists($fakturs, 'links'))
                        <div class="mt-4">
                            {{ $fakturs->onEachSide(1)->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Modal create faktur --}}
    <div id="create-faktur-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 px-4">
        <div class="w-full max-w-2xl rounded-xl bg-white p-5 shadow-xl">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-xl font-semibold text-slate-900">Tambah Faktur</h2>
                <button type="button" data-modal-close="create-faktur-modal" class="text-slate-500 hover:text-slate-700">&times;</button>
            </div>
            <form method="POST" action="{{ route('tu.faktur.store') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-slate-700">Nama Faktur (Master)</label>
                        <select name="master_faktur_id" class="block w-full rounded-md border-slate-300 focus:border-blue-600 focus:ring-blue-600" required>
                            <option value="">Pilih master faktur</option>
                            @foreach ($masterFakturs as $masterFaktur)
                                <option value="{{ $masterFaktur->id }}">{{ $masterFaktur->nama_faktur }} - Rp {{ number_format((int) $masterFaktur->nominal, 0, ',', '.') }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Tipe Target</label>
                        <select id="create-target-type" name="target_type" class="block w-full rounded-md border-slate-300 focus:border-blue-600 focus:ring-blue-600" required>
                            @foreach ($targetOptions as $targetValue => $targetLabel)
                                <option value="{{ $targetValue }}">{{ $targetLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Nama Target</label>
                        <div id="create-target-value-container"></div>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Tanggal Faktur</label>
                        <input type="date" name="tersedia_pada" class="block w-full rounded-md border-slate-300 focus:border-blue-600 focus:ring-blue-600" required />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Jatuh Tempo</label>
                        <input type="date" name="jatuh_tempo" class="block w-full rounded-md border-slate-300 focus:border-blue-600 focus:ring-blue-600" required />
                    </div>

                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" data-modal-close="create-faktur-modal" class="rounded-md border border-slate-300 px-4 py-2 text-sm text-slate-700">Batal</button>
                    <button type="submit" class="rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal edit/delete per baris faktur. --}}
    @foreach ($fakturs as $faktur)
        @php
            $fakturId = (string) $faktur->id;
        @endphp
        <div id="edit-faktur-modal-{{ $fakturId }}" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 px-4">
            <div class="w-full max-w-2xl rounded-xl bg-white p-5 shadow-xl">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-slate-900">Edit Faktur</h2>
                    <button type="button" data-modal-close="edit-faktur-modal-{{ $fakturId }}" class="text-slate-500 hover:text-slate-700">&times;</button>
                </div>
                <form method="POST" action="{{ route('tu.faktur.update', $faktur) }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-slate-700">Nama Faktur (Master)</label>
                            <select name="master_faktur_id" class="block w-full rounded-md border-slate-300 focus:border-blue-600 focus:ring-blue-600" required>
                                @foreach ($masterFakturs as $masterFaktur)
                                    <option value="{{ $masterFaktur->id }}" @selected($faktur->master_faktur_id === $masterFaktur->id)>{{ $masterFaktur->nama_faktur }} - Rp {{ number_format((int) $masterFaktur->nominal, 0, ',', '.') }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Tipe Target</label>
                            <select
                                id="edit-target-type-{{ $fakturId }}"
                                data-edit-target-type
                                data-target-id="{{ $fakturId }}"
                                name="target_type"
                                class="block w-full rounded-md border-slate-300 focus:border-blue-600 focus:ring-blue-600"
                                required
                            >
                                @foreach ($targetOptions as $targetValue => $targetLabel)
                                    <option value="{{ $targetValue }}" @selected($faktur->target_type === $targetValue)>{{ $targetLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Nama Target</label>
                            <div id="edit-target-value-container-{{ $fakturId }}" data-current-value="{{ $faktur->target_value ?? '' }}"></div>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Tanggal Faktur</label>
                            <input type="date" name="tersedia_pada" value="{{ $faktur->tersedia_pada?->toDateString() }}" class="block w-full rounded-md border-slate-300 focus:border-blue-600 focus:ring-blue-600" required />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Jatuh Tempo</label>
                            <input type="date" name="jatuh_tempo" value="{{ $faktur->jatuh_tempo?->toDateString() }}" class="block w-full rounded-md border-slate-300 focus:border-blue-600 focus:ring-blue-600" required />
                        </div>

                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" data-modal-close="edit-faktur-modal-{{ $fakturId }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm text-slate-700">Batal</button>
                        <button type="submit" class="rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        <div id="delete-faktur-modal-{{ $fakturId }}" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 px-4">
            <div class="w-full max-w-lg rounded-xl bg-white p-5 shadow-xl">
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-slate-900">Hapus Faktur</h2>
                    <button type="button" data-modal-close="delete-faktur-modal-{{ $fakturId }}" class="text-slate-500 hover:text-slate-700">&times;</button>
                </div>
                <p class="mb-5 text-sm text-slate-600">Yakin ingin menghapus faktur "{{ $faktur->masterFaktur?->nama_faktur ?? '-' }}"?</p>
                <div class="flex justify-end gap-2">
                    <button type="button" data-modal-close="delete-faktur-modal-{{ $fakturId }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm text-slate-700">Batal</button>
                    <form method="POST" action="{{ route('tu.faktur.destroy', $faktur) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded-md bg-rose-500 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-600">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

    <script>
        // Opsi dinamis untuk field "Nama Target" berdasarkan tipe target.
        const angkatanOptions = @json($angkatanOptions ?? []);
        const kelasOptions = @json($kelasOptions ?? []);
        const siswaTargetOptions = @json($siswaTargetOptions ?? []);

        // Render field target_value secara dinamis:
        // - angkatan/kelas => dropdown
        // - semua_siswa => readonly fixed value
        // - siswa => input search + datalist
        function renderTargetValueField(containerId, targetType, fieldName, currentValue = '') {
            const container = document.getElementById(containerId);
            if (!container) return;

            const safeCurrentValue = currentValue ?? '';

            if (targetType === 'angkatan') {
                const optionsHtml = angkatanOptions.map((item) => {
                    const value = String(item);
                    const selected = value === String(safeCurrentValue) ? 'selected' : '';
                    return `<option value="${value}" ${selected}>${value}</option>`;
                }).join('');

                container.innerHTML = `
                    <select name="${fieldName}" class="block w-full rounded-md border-slate-300 focus:border-blue-600 focus:ring-blue-600" required>
                        <option value="">Pilih angkatan</option>
                        ${optionsHtml}
                    </select>
                `;
                return;
            }

            if (targetType === 'kelas') {
                const optionsHtml = angkatanOptions.flatMap((angkatan) => 
                    kelasOptions.map((kelas) => {
                        const value = `${angkatan}|${kelas}`;
                        const label = `Angkatan ${angkatan} - Kelas ${kelas}`;
                    const selected = value === String(safeCurrentValue) ? 'selected' : '';
                        return `<option value="${value}" ${selected}>${label}</option>`;
                    })
                ).join('');

                container.innerHTML = `
                    <select name="${fieldName}" class="block w-full rounded-md border-slate-300 focus:border-blue-600 focus:ring-blue-600" required>
                        <option value="">Pilih kelas</option>
                        ${optionsHtml}
                    </select>
                `;
                return;
            }

            if (targetType === 'semua_siswa') {
                container.innerHTML = `
                    <input type="text" value="Semua Siswa" class="block w-full rounded-md border-slate-300 bg-slate-50 text-slate-600 focus:border-blue-600 focus:ring-blue-600" readonly />
                    <input type="hidden" name="${fieldName}" value="Semua Siswa" />
                `;
                return;
            }

            const datalistId = `${containerId}-datalist`;
            const siswaOptionsHtml = siswaTargetOptions.map((item) => {
                const nama = String(item.nama_siswa ?? '');
                const nisn = String(item.nisn ?? '');
                const value = `${nama} - ${nisn}`.trim();
                return `<option value="${value}"></option>`;
            }).join('');

            container.innerHTML = `
                <input
                    name="${fieldName}"
                    list="${datalistId}"
                    value="${String(safeCurrentValue).replace(/"/g, '&quot;')}"
                    class="block w-full rounded-md border-slate-300 focus:border-blue-600 focus:ring-blue-600"
                    placeholder="Cari nama siswa atau NISN"
                    required
                />
                <datalist id="${datalistId}">
                    ${siswaOptionsHtml}
                </datalist>
            `;
        }

        // Generic open modal handler.
        document.querySelectorAll('[data-modal-open]').forEach((button) => {
            button.addEventListener('click', () => {
                const modal = document.getElementById(button.getAttribute('data-modal-open'));
                if (modal) {
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                }
            });
        });

        // Generic close modal handler.
        document.querySelectorAll('[data-modal-close]').forEach((button) => {
            button.addEventListener('click', () => {
                const modal = document.getElementById(button.getAttribute('data-modal-close'));
                if (modal) {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }
            });
        });

        // Sinkronisasi field target untuk modal create.
        const createTargetType = document.getElementById('create-target-type');
        const syncCreateTargetField = () => {
            renderTargetValueField('create-target-value-container', createTargetType?.value ?? 'angkatan', 'target_value', '');
        };
        createTargetType?.addEventListener('change', syncCreateTargetField);
        syncCreateTargetField();

        // Sinkronisasi field target untuk modal edit per item.
        document.querySelectorAll('[data-edit-target-type]').forEach((select) => {
            const fakturId = select.getAttribute('data-target-id');
            if (!fakturId) return;
            const containerId = `edit-target-value-container-${fakturId}`;
            const initialValue = document.getElementById(containerId)?.getAttribute('data-current-value') ?? '';

            const syncEditField = () => {
                renderTargetValueField(containerId, select.value, 'target_value', initialValue);
            };

            select.addEventListener('change', () => {
                renderTargetValueField(containerId, select.value, 'target_value', '');
            });
            syncEditField();
        });
    </script>
</x-app-layout>
