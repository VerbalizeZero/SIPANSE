<x-app-layout>
    @php
        // Opsi angkatan khusus modal promote, dipisah dari dropdown filter agar tidak saling menimpa.
        $promoteAngkatanOptions = collect($kelasRows)->pluck('tahun_angkatan_raw', 'tahun_angkatan_display')->unique();
        // Menjaga input lama saat validasi gagal (agar pilihan user tidak hilang).
        $oldMappings = old('mappings', []);
        // Jika ada error validasi mapping, modal promote otomatis dibuka kembali.
        $openPromoteModal = $errors->has('mappings') || $errors->has('mappings.0.tahun_angkatan_raw') || $errors->has('mappings.0.kelas') || $errors->has('mappings.0.level');
    @endphp

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-4">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900">Data Kelas</h1>
                    <p class="text-sm text-slate-500">Kelola data kelas berdasarkan data siswa</p>
                </div>

                {{-- Div Search, Filter, & Promote --}}
                <div class="mt-3 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <form action="{{ route('tu.kelas.index') }}" method="GET" class="flex flex-1 flex-col gap-2 md:flex-row md:items-center">

                        {{-- SEARCHING --}}
                        <div class="relative w-full md:w-96">
                            <input 
                            type="text"
                            name="keyword"
                            id="kelas-keyword-input"
                            value="{{ $filters['keyword'] ?? '' }}"
                            placeholder="Cari berdasarkan kelas atau wali kelas..."
                            class="block w-full rounded-md border-slate-300 pr-9 text-sm focus:border-blue-600 focus:ring-blue-600"
                            />

                            {{-- Tombol clear keyword yang muncul hanya saat ada input keyword, untuk memudahkan pengguna menghapus pencarian dengan cepat. --}}
                            @if (!empty($filters['keyword']))
                                <button 
                                type="button" 
                                id="kelas-clear-keyword" 
                                class="absolute inset-y-0 right-2 my-auto h-6 w-6 rounded text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                                aria-label="Clear keyword"
                                title="Clear keyword"
                                >
                                    &times;
                                </button>    
                            @endif
                        </div>

                        {{-- FILTER ANGKATAN --}}
                        <select 
                        name="angkatan" 
                        class="block w-full rounded-md border-slate-300 text-sm focus:border-blue-600 focus:ring-blue-600 md:w-56"
                        onchange="this.form.submit()"
                        >
                            <option value="">Pilih Angkatan</option>
                            @foreach (($angkatanOptions ?? []) as $value)
                                <option value="{{ $value }}" {{ $filters['angkatan'] === $value ? 'selected' : '' }}>
                                    {{ $value === '__NULL__' ? '-' : $value }}
                                </option>
                            @endforeach
                        </select>

                        {{-- FILTER LEVEL --}}
                        <select 
                        name="level" 
                        class="block w-full rounded-md border-slate-300 text-sm focus:border-blue-600 focus:ring-blue-600 md:w-56"
                        onchange="this.form.submit()"
                        >
                            <option value="">Pilih Level</option>
                            @foreach (($levelOptions ?? []) as $value)
                                <option value="{{ $value }}" {{ $filters['level'] === $value ? 'selected' : '' }}>
                                    {{ $value }}
                                </option>
                            @endforeach
                        </select>
                    </form>

                    <button type="button" data-modal-open="promote-kelas-modal" class="inline-flex items-center rounded-md border bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">
                        Promote
                    </button>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="overflow-x-auto rounded-lg border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                <th class="px-4 py-3">Tahun Angkatan</th>
                                <th class="px-4 py-3">Level</th>
                                <th class="px-4 py-3">Kelas</th>
                                <th class="px-4 py-3">Wali Kelas</th>
                                <th class="px-4 py-3">Jumlah Siswa</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($kelasRows as $row)
                                @php
                                    $rowId = md5($row['tahun_angkatan_display'].':::'.$row['kelas']);
                                @endphp
                                <tr class="text-sm text-slate-700">
                                    <td class="px-4 py-3">{{ $row['tahun_angkatan_display'] }}</td>
                                    <td class="px-4 py-3">{{ $row['level'] }}</td>
                                    <td class="px-4 py-3">{{ $row['kelas'] }}</td>
                                    <td class="px-4 py-3">{{ $row['wali_kelas'] ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $row['total_siswa'] }} Siswa</td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="inline-flex items-center gap-2">
                                            <button type="button" data-modal-open="edit-kelas-modal-{{ $rowId }}" class="rounded-md border border-slate-200 px-2 py-1 text-xs font-medium text-slate-700 hover:bg-slate-50">Edit</button>
                                            @if ($row['data_kelas_id'])
                                                <button type="button" data-modal-open="delete-kelas-modal-{{ $rowId }}" class="rounded-md border border-rose-200 px-2 py-1 text-xs font-medium text-rose-600 hover:bg-rose-50">Delete</button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-10 text-center text-sm text-slate-500">Belum ada data kelas dari data siswa.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="promote-kelas-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 px-4">
        <div class="w-full max-w-3xl rounded-xl bg-white p-5 shadow-xl">
            <div class="mb-2 flex items-center justify-between">
                <h2 class="text-xl font-semibold text-slate-900">Promote Kelas</h2>
                <button type="button" data-modal-close="promote-kelas-modal" class="text-slate-500 hover:text-slate-700">&times;</button>
            </div>
            <p class="mb-2 text-sm leading-5 text-slate-600">Fitur ini digunakan untuk menaikkan level beberapa kelas sekaligus berdasarkan angkatan yang dipilih, sehingga proses kenaikan kelas bisa dilakukan lebih cepat dan konsisten.</p>

            <form method="POST" action="{{ route('tu.kelas.promote.execute') }}" class="space-y-4" id="promote-form">
                @csrf
                <div class="rounded-lg border border-slate-200 p-4">
                    <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                        <div class="w-full md:max-w-xs">
                            <label class="mb-1 block text-sm font-medium text-slate-700">Pilih Angkatan</label>
                            <select id="promote-angkatan-select" class="block w-full rounded-md border-slate-300 text-sm focus:border-blue-600 focus:ring-blue-600">
                                <option value="">Pilih angkatan</option>
                                @foreach ($promoteAngkatanOptions as $label => $value)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div id="promote-selection-actions" class="mb-3 hidden items-center gap-2">
                        <button type="button" id="promote-select-all" class="rounded-md border border-slate-300 px-3 py-2 text-xs font-medium text-slate-700 hover:border-green-400 hover:text-green-700 hover:bg-slate-50">Pilih semua kelas</button>
                        <button type="button" id="promote-clear-all" class="rounded-md border border-slate-300 px-3 py-2 text-xs font-medium text-slate-700 hover:border-green-400 hover:text-green-700 hover:bg-slate-50">Batalkan semua pilihan</button>
                        <span id="promote-selected-count" class="ml-auto text-xs font-medium text-slate-600">0 kelas dipilih</span>
                    </div>

                    <div id="promote-class-list" class="space-y-2"></div>
                </div>

                <div id="mapping-container"></div>

                <div id="preview-result" class="hidden rounded-lg border border-slate-200 bg-slate-50 p-3">
                    <p class="text-sm font-semibold text-slate-800" id="preview-total"></p>
                    <ul class="mt-2 list-disc pl-5 text-sm text-slate-700" id="preview-details"></ul>
                </div>

                <div id="promote-confirm" class="rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">
                    <p class="font-semibold">Konfirmasi Promote</p>
                    <p class="mt-1">Level kelas terpilih akan dinaikkan otomatis sesuai urutan: 10 -> 11 -> 12 -> Graduated.</p>
                </div>

                @error('mappings')
                    <p class="text-xs text-rose-600">{{ $message }}</p>
                @enderror

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" data-modal-close="promote-kelas-modal" class="rounded-md border border-slate-300 px-4 py-2 text-sm text-slate-700">Batal</button>
                    <button type="submit" id="promote-submit" class="rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800 disabled:cursor-not-allowed disabled:bg-slate-300" disabled>Proses Promote</button>
                </div>
            </form>
        </div>
    </div>

    <div id="promote-verify-modal" class="fixed inset-0 z-[60] hidden items-center justify-center bg-slate-900/60 px-4">
        <div class="w-full max-w-xl rounded-xl bg-white p-5 shadow-xl">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-slate-900">Verifikasi Promote</h3>
                <button type="button" data-modal-close="promote-verify-modal" class="text-slate-500 hover:text-slate-700">&times;</button>
            </div>
            <p class="text-sm text-slate-600">Pastikan mapping berikut sudah benar sebelum diproses.</p>
            <ul id="verify-mapping-list" class="mt-3 max-h-64 list-disc space-y-1 overflow-y-auto pl-5 text-sm text-slate-700"></ul>
            <div class="mt-4 flex justify-end gap-2">
                <button type="button" data-modal-close="promote-verify-modal" class="rounded-md border border-slate-300 px-4 py-2 text-sm text-slate-700">Batal</button>
                <button type="button" id="verify-submit" class="rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">Ya, Proses</button>
            </div>
        </div>
    </div>

    @foreach ($kelasRows as $row)
        @php
            $rowId = md5($row['tahun_angkatan_display'].':::'.$row['kelas']);
        @endphp
        <div id="edit-kelas-modal-{{ $rowId }}" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 px-4">
            <div class="w-full max-w-xl rounded-xl bg-white p-5 shadow-xl">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-slate-900">{{ $row['data_kelas_id'] ? 'Edit Wali Kelas' : 'Set Wali Kelas' }}</h2>
                    <button type="button" data-modal-close="edit-kelas-modal-{{ $rowId }}" class="text-slate-500 hover:text-slate-700">&times;</button>
                </div>
                <form method="POST" action="{{ $row['data_kelas_id'] ? route('tu.kelas.update', $row['data_kelas_id']) : route('tu.kelas.store') }}" class="space-y-4">
                    @csrf
                    @if ($row['data_kelas_id'])
                        @method('PUT')
                    @else
                        <input type="hidden" name="kelas_ref" value="{{ $row['tahun_angkatan_raw'].':::'.$row['kelas'] }}" />
                    @endif
                    <div class="text-sm text-slate-600">{{ $row['kelas'] }} - Angkatan {{ $row['tahun_angkatan_display'] }} ({{ $row['total_siswa'] }} siswa)</div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Pilih Level</label>
                        <select name="level" class="block w-full rounded-md border-slate-300 focus:border-blue-600 focus:ring-blue-600" required>
                            <option value="">Pilih level</option>
                            <option value="10" @selected(($row['level'] ?? '') === '10')>10</option>
                            <option value="11" @selected(($row['level'] ?? '') === '11')>11</option>
                            <option value="12" @selected(($row['level'] ?? '') === '12')>12</option>
                            <option value="Graduated" @selected(($row['level'] ?? '') === 'Graduated')>Graduated</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Wali Kelas</label>
                        <input name="wali_kelas" value="{{ $row['wali_kelas'] ?? '' }}" class="block w-full rounded-md border-slate-300 focus:border-blue-600 focus:ring-blue-600" />
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" data-modal-close="edit-kelas-modal-{{ $rowId }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm text-slate-700">Batal</button>
                        <button type="submit" class="rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        @if ($row['data_kelas_id'])
            <div id="delete-kelas-modal-{{ $rowId }}" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 px-4">
                <div class="w-full max-w-xl rounded-xl bg-white p-5 shadow-xl">
                    <div class="mb-3 flex items-center justify-between">
                        <h2 class="text-xl font-semibold text-slate-900">Hapus Level dan Wali Kelas</h2>
                        <button type="button" data-modal-close="delete-kelas-modal-{{ $rowId }}" class="text-slate-500 hover:text-slate-700">&times;</button>
                    </div>
                    <p class="mb-5 text-sm text-slate-600">Hapus data level dan wali kelas untuk kelas {{ $row['kelas'] }} dengan angkatan {{ $row['tahun_angkatan_display'] }}?</p>
                    <div class="flex justify-end gap-2">
                        <button type="button" data-modal-close="delete-kelas-modal-{{ $rowId }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm text-slate-700">Batal</button>
                        <form method="POST" action="{{ route('tu.kelas.destroy', $row['data_kelas_id']) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-md bg-rose-500 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-600">Hapus</button>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endforeach

    <script>
        // Data awal dari server untuk kebutuhan filter, restore state, dan render daftar kelas.
        const angkatanOptions = @json($promoteAngkatanOptions);
        const oldMappings = @json($oldMappings);
        const kelasRows = @json($kelasRows);
        // Urutan level valid di sistem promote.
        const promoteLevels = ['10', '11', '12', 'Graduated'];
        // Mapping otomatis level saat proses "naik kelas".
        const nextLevelMap = { '10': '11', '11': '12', '12': 'Graduated', 'Graduated': 'Graduated' };

        document.querySelectorAll('[data-modal-open]').forEach((button) => {
            button.addEventListener('click', () => {
                const modal = document.getElementById(button.getAttribute('data-modal-open'));
                if (modal) {
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                }
            });
        });

        document.querySelectorAll('[data-modal-close]').forEach((button) => {
            button.addEventListener('click', () => {
                const modal = document.getElementById(button.getAttribute('data-modal-close'));
                if (modal) {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }
            });
        });

        const promoteAngkatanSelect = document.getElementById('promote-angkatan-select');
        const promoteClassList = document.getElementById('promote-class-list');
        const promoteSelectionActions = document.getElementById('promote-selection-actions');
        const promoteSubmitButton = document.getElementById('promote-submit');
        const mappingContainer = document.getElementById('mapping-container');
        const promoteVerifyModal = document.getElementById('promote-verify-modal');
        const verifyMappingList = document.getElementById('verify-mapping-list');
        const promoteSelectedCount = document.getElementById('promote-selected-count');

        // Ambil semua baris kelas untuk 1 angkatan terpilih.
        function getRowsByAngkatan(tahunRaw) {
            return kelasRows.filter((row) => String(row.tahun_angkatan_raw) === String(tahunRaw));
        }

        // Fallback level jika data belum valid/tersimpan.
        function getCurrentLevel(row) {
            return promoteLevels.includes(row.level) ? row.level : '10';
        }

        // Ubah checkbox terpilih menjadi payload mapping yang siap di-preview/submit.
        function getSelectedRows() {
            return Array.from(document.querySelectorAll('.promote-kelas-checkbox:checked'))
                .map((checkbox) => ({
                    tahun_angkatan_raw: checkbox.dataset.tahun,
                    kelas: checkbox.dataset.kelas,
                    current_level: checkbox.dataset.level,
                    next_level: nextLevelMap[checkbox.dataset.level] ?? 'Graduated',
                    tahun_display: checkbox.dataset.tahunDisplay,
                    total_siswa: checkbox.dataset.totalSiswa,
                }));
        }

        // Sinkronisasi state tombol submit dan indikator jumlah kelas terpilih.
        function syncPromoteButtons() {
            const selected = getSelectedRows();
            const hasSelection = selected.length > 0;
            promoteSubmitButton.disabled = !hasSelection;
            if (promoteSelectedCount) {
                promoteSelectedCount.textContent = `${selected.length} kelas dipilih`;
            }
        }

        // Style visual baris yang dicentang: hijau untuk memudahkan verifikasi pilihan.
        function syncSelectedRowStyles() {
            document.querySelectorAll('.promote-kelas-checkbox').forEach((checkbox) => {
                const row = checkbox.closest('[data-selectable-row]');
                if (!row) return;
                if (checkbox.checked) {
                    row.classList.add('border-green-300', 'bg-green-50');
                    row.classList.remove('border-slate-200', 'bg-white');
                } else {
                    row.classList.remove('border-green-300', 'bg-green-50');
                    row.classList.add('border-slate-200', 'bg-white');
                }
            });
        }

        // Reset modal promote ke kondisi awal saat ditutup.
        function resetPromoteState() {
            if (promoteAngkatanSelect) {
                promoteAngkatanSelect.value = '';
            }
            promoteClassList.innerHTML = '';
            promoteSelectionActions.classList.add('hidden');
            promoteSelectionActions.classList.remove('flex');
            mappingContainer.innerHTML = '';
            promoteSubmitButton.disabled = true;
            document.getElementById('preview-result')?.classList.add('hidden');
            verifyMappingList.innerHTML = '';
            if (promoteSelectedCount) {
                promoteSelectedCount.textContent = '0 kelas dipilih';
            }
        }

        // Bangun hidden inputs agar payload submit tetap memakai format mappings[*].
        function updateMappingInputs(mappings) {
            mappingContainer.innerHTML = '';
            mappings.forEach((item, index) => {
                const tahunInput = document.createElement('input');
                tahunInput.type = 'hidden';
                tahunInput.name = `mappings[${index}][tahun_angkatan_raw]`;
                tahunInput.value = item.tahun_angkatan_raw;
                mappingContainer.appendChild(tahunInput);

                const kelasInput = document.createElement('input');
                kelasInput.type = 'hidden';
                kelasInput.name = `mappings[${index}][kelas]`;
                kelasInput.value = item.kelas;
                mappingContainer.appendChild(kelasInput);

                const levelInput = document.createElement('input');
                levelInput.type = 'hidden';
                levelInput.name = `mappings[${index}][level]`;
                levelInput.value = item.next_level;
                mappingContainer.appendChild(levelInput);
            });
        }

        // Hit endpoint preview untuk menampilkan total siswa terdampak sebelum eksekusi.
        async function renderPreview(mappings) {
            const previewBox = document.getElementById('preview-result');
            const previewTotal = document.getElementById('preview-total');
            const previewDetails = document.getElementById('preview-details');
            previewDetails.innerHTML = '';

            const response = await fetch('{{ route('tu.kelas.promote.preview') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    mappings: mappings.map((item) => ({
                        tahun_angkatan_raw: item.tahun_angkatan_raw,
                        kelas: item.kelas,
                        level: item.next_level,
                    })),
                }),
            });

            if (!response.ok) {
                previewBox.classList.remove('hidden');
                previewTotal.textContent = 'Preview gagal. Pastikan semua pilihan mapping terisi.';
                return false;
            }

            const payload = await response.json();
            const details = payload?.data?.details ?? [];
            const totalAffected = payload?.data?.total_affected ?? 0;

            previewTotal.textContent = `Total siswa terdampak: ${totalAffected}`;
            details.forEach((item) => {
                const li = document.createElement('li');
                li.textContent = `Angkatan ${item.tahun_angkatan_display} - ${item.kelas} => Level ${item.level}: ${item.affected} siswa`;
                previewDetails.appendChild(li);
            });
            previewBox.classList.remove('hidden');
            return true;
        }

        // Render daftar kelas dinamis berdasarkan angkatan terpilih.
        function renderClassRowsByAngkatan(tahunRaw) {
            promoteClassList.innerHTML = '';
            promoteSelectionActions.classList.add('hidden');
            promoteSelectionActions.classList.remove('flex');
            mappingContainer.innerHTML = '';
            promoteSubmitButton.disabled = true;
            document.getElementById('preview-result')?.classList.add('hidden');
            if (!tahunRaw) {
                return;
            }

            const rows = getRowsByAngkatan(tahunRaw);
            if (rows.length === 0) {
                const empty = document.createElement('p');
                empty.className = 'text-sm text-slate-500';
                empty.textContent = 'Tidak ada kelas untuk angkatan ini.';
                promoteClassList.appendChild(empty);
                return;
            }

            promoteSelectionActions.classList.remove('hidden');
            promoteSelectionActions.classList.add('flex');

            rows.forEach((row, idx) => {
                const levelNow = getCurrentLevel(row);
                const nextLevel = nextLevelMap[levelNow] ?? 'Graduated';
                const wrapper = document.createElement('label');
                wrapper.setAttribute('data-selectable-row', 'true');
                wrapper.className = 'flex cursor-pointer items-center justify-between rounded-lg border border-slate-200 bg-white px-3 py-2 transition hover:border-green-300';

                wrapper.innerHTML = `
                    <div class="flex items-start gap-3">
                        <input type="checkbox" class="promote-kelas-checkbox mt-1 h-4 w-4 rounded border-slate-300 text-green-600 hover:border-green-500 focus:ring-0 focus:outline-none focus-visible:ring-0"
                            data-tahun="${row.tahun_angkatan_raw}"
                            data-kelas="${row.kelas}"
                            data-level="${levelNow}"
                            data-tahun-display="${row.tahun_angkatan_display}"
                            data-total-siswa="${row.total_siswa}"
                            ${Array.isArray(oldMappings) && oldMappings.some((item) => String(item?.tahun_angkatan_raw) === String(row.tahun_angkatan_raw) && item?.kelas === row.kelas) ? 'checked' : ''}>
                        <div>
                            <p class="text-sm font-semibold text-slate-800">${row.kelas}</p>
                            <p class="text-xs text-slate-500">Angkatan ${row.tahun_angkatan_display} • ${row.total_siswa} siswa</p>
                        </div>
                    </div>
                    <div class="text-xs text-slate-600">Level ${levelNow} -> ${nextLevel}</div>
                `;

                promoteClassList.appendChild(wrapper);
            });

            document.querySelectorAll('.promote-kelas-checkbox').forEach((checkbox) => {
                checkbox.addEventListener('change', () => {
                    syncSelectedRowStyles();
                    syncPromoteButtons();
                });
            });

            syncSelectedRowStyles();
            syncPromoteButtons();
        }

        // Perubahan angkatan akan me-render ulang daftar kelas.
        promoteAngkatanSelect?.addEventListener('change', (event) => {
            renderClassRowsByAngkatan(event.target.value);
        });

        // Pilih semua checkbox kelas yang tampil pada angkatan aktif.
        document.getElementById('promote-select-all')?.addEventListener('click', () => {
            document.querySelectorAll('.promote-kelas-checkbox').forEach((checkbox) => {
                checkbox.checked = true;
            });
            syncSelectedRowStyles();
            syncPromoteButtons();
        });

        // Kosongkan semua pilihan kelas yang sedang tampil.
        document.getElementById('promote-clear-all')?.addEventListener('click', () => {
            document.querySelectorAll('.promote-kelas-checkbox').forEach((checkbox) => {
                checkbox.checked = false;
            });
            syncSelectedRowStyles();
            syncPromoteButtons();
        });

        // Submit promote memakai 2 langkah: preview dulu, lalu modal verifikasi akhir.
        document.getElementById('promote-form')?.addEventListener('submit', async (event) => {
            event.preventDefault();
            const selected = getSelectedRows();
            if (!selected.length) return;

            updateMappingInputs(selected);
            const ok = await renderPreview(selected);
            if (!ok) return;

            verifyMappingList.innerHTML = '';
            selected.forEach((item) => {
                const li = document.createElement('li');
                li.textContent = `Angkatan ${item.tahun_display} - ${item.kelas}: ${item.current_level} -> ${item.next_level}`;
                verifyMappingList.appendChild(li);
            });

            promoteVerifyModal?.classList.remove('hidden');
            promoteVerifyModal?.classList.add('flex');
        });

        // Tombol konfirmasi akhir benar-benar mengirim form ke endpoint execute.
        document.getElementById('verify-submit')?.addEventListener('click', () => {
            promoteVerifyModal?.classList.add('hidden');
            promoteVerifyModal?.classList.remove('flex');
            document.getElementById('promote-form')?.submit();
        });

        // Menutup modal promote juga menghapus state pilihan sebelumnya.
        document.querySelectorAll('[data-modal-close="promote-kelas-modal"]').forEach((button) => {
            button.addEventListener('click', () => {
                resetPromoteState();
            });
        });

        // Restore pilihan lama jika sebelumnya gagal validasi backend.
        if (Array.isArray(oldMappings) && oldMappings.length > 0) {
            const firstAngkatan = oldMappings[0]?.tahun_angkatan_raw ?? '';
            if (firstAngkatan) {
                promoteAngkatanSelect.value = firstAngkatan;
                renderClassRowsByAngkatan(firstAngkatan);
            }
        }

        // Jika server kirim error mapping, buka modal promote otomatis.
        @if ($openPromoteModal)
            const promoteModal = document.getElementById('promote-kelas-modal');
            if (promoteModal) {
                promoteModal.classList.remove('hidden');
                promoteModal.classList.add('flex');
            }
        @endif

        // Fitur tambahan: tombol clear keyword untuk memudahkan pengguna menghapus filter pencarian dengan cepat.
        const kelasFilterForm = document.querySelector('form[action="{{ route('tu.kelas.index') }}"]');
        const kelasKeywordInput = document.getElementById('kelas-keyword-input');
        const kelasClearKeyword = document.getElementById('kelas-clear-keyword');

        kelasClearKeyword?.addEventListener('click', () => {
            if (!kelasKeywordInput || !kelasFilterForm) return;
            kelasKeywordInput.value = '';
            kelasFilterForm.submit();
        });

    </script>
</x-app-layout>
