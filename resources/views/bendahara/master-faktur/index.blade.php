<x-app-layout>
    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-4 flex items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900">Membuat Harga</h1>
                    <p class="text-sm text-slate-500">Kelola master data jenis faktur dan harga</p>
                </div>
                <button
                    type="button"
                    data-modal-open="create-modal"
                    class="inline-flex items-center rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800"
                >
                    + Tambah Harga
                </button>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <form method="GET" action="{{ route('bendahara.master-faktur.index') }}" class="mb-4 flex flex-wrap gap-3">
                    <select
                        name="jenis_faktur"
                        class="w-full rounded-md border-slate-300 text-sm focus:border-blue-600 focus:ring-blue-600 sm:w-56"
                    >
                        <option value="">Filter Jenis Faktur</option>
                        @foreach ($jenisFakturOptions as $jenis)
                            <option value="{{ $jenis }}" @selected(($filters['jenis_faktur'] ?? null) === $jenis)>
                                {{ $jenis }}
                            </option>
                        @endforeach
                    </select>

                    <input
                        type="text"
                        name="search"
                        value="{{ $filters['search'] ?? '' }}"
                        placeholder="Cari nama faktur..."
                        class="w-full rounded-md border-slate-300 text-sm focus:border-blue-600 focus:ring-blue-600 sm:w-72"
                    />

                    <button type="submit" class="rounded-md border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                        Cari
                    </button>
                </form>

                <div class="overflow-x-auto rounded-lg border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                <th class="px-4 py-3">Tanggal Pembuatan</th>
                                <th class="px-4 py-3">Jenis Faktur</th>
                                <th class="px-4 py-3">Nama Faktur</th>
                                <th class="px-4 py-3">Nominal</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($masterFakturs as $faktur)
                                <tr class="text-sm text-slate-700">
                                    <td class="px-4 py-3">{{ $faktur->created_at->format('Y-m-d') }}</td>
                                    <td class="px-4 py-3">{{ $faktur->jenis_faktur }}</td>
                                    <td class="px-4 py-3">{{ $faktur->nama_faktur }}</td>
                                    <td class="px-4 py-3">Rp {{ number_format($faktur->nominal, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="inline-flex items-center gap-2">
                                            <button
                                                type="button"
                                                data-modal-open="edit-modal-{{ $faktur->id }}"
                                                class="rounded-md border border-slate-200 px-2 py-1 text-xs font-medium text-slate-700 hover:bg-slate-50"
                                            >
                                                Edit
                                            </button>
                                            <button
                                                type="button"
                                                data-modal-open="delete-modal-{{ $faktur->id }}"
                                                class="rounded-md border border-rose-200 px-2 py-1 text-xs font-medium text-rose-600 hover:bg-rose-50"
                                            >
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500">Belum ada data faktur.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="create-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 px-4">
        <div class="w-full max-w-xl rounded-xl bg-white p-5 shadow-xl">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-xl font-semibold text-slate-900">Tambah Harga Tagihan</h2>
                <button type="button" data-modal-close="create-modal" class="text-slate-500 hover:text-slate-700">&times;</button>
            </div>
            <form method="POST" action="{{ route('bendahara.master-faktur.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="create_jenis_faktur" class="mb-1 block text-sm font-medium text-slate-700">Jenis Faktur</label>
                    <select id="create_jenis_faktur" name="jenis_faktur" class="block w-full rounded-md border-slate-300 focus:border-blue-600 focus:ring-blue-600" required>
                        <option value="">Pilih jenis faktur</option>
                        @foreach ($jenisFakturOptions as $jenis)
                            <option value="{{ $jenis }}">{{ $jenis }}</option>
                        @endforeach
                    </select>
                    @error('jenis_faktur')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="create_nama_faktur" class="mb-1 block text-sm font-medium text-slate-700">Nama Faktur</label>
                    <input id="create_nama_faktur" name="nama_faktur" value="{{ old('nama_faktur') }}" class="block w-full rounded-md border-slate-300 focus:border-blue-600 focus:ring-blue-600" placeholder="Contoh: SPP Bulanan Kelas X" required />
                    @error('nama_faktur')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="create_nominal" class="mb-1 block text-sm font-medium text-slate-700">Nominal (Rp)</label>
                    <input id="create_nominal" name="nominal" type="number" min="0" value="{{ old('nominal') }}" class="block w-full rounded-md border-slate-300 focus:border-blue-600 focus:ring-blue-600" required />
                    @error('nominal')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="create_deskripsi" class="mb-1 block text-sm font-medium text-slate-700">Deskripsi</label>
                    <textarea id="create_deskripsi" name="deskripsi" rows="3" class="block w-full rounded-md border-slate-300 focus:border-blue-600 focus:ring-blue-600">{{ old('deskripsi') }}</textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" data-modal-close="create-modal" class="rounded-md border border-slate-300 px-4 py-2 text-sm text-slate-700">Batal</button>
                    <button type="submit" class="rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">Tambah</button>
                </div>
            </form>
        </div>
    </div>

    @foreach ($masterFakturs as $faktur)
        <div id="edit-modal-{{ $faktur->id }}" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 px-4">
            <div class="w-full max-w-xl rounded-xl bg-white p-5 shadow-xl">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-slate-900">Edit Harga Tagihan</h2>
                    <button type="button" data-modal-close="edit-modal-{{ $faktur->id }}" class="text-slate-500 hover:text-slate-700">&times;</button>
                </div>
                <form method="POST" action="{{ route('bendahara.master-faktur.update', $faktur) }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Jenis Faktur</label>
                        <select name="jenis_faktur" class="block w-full rounded-md border-slate-300 focus:border-blue-600 focus:ring-blue-600" required>
                            @foreach ($jenisFakturOptions as $jenis)
                                <option value="{{ $jenis }}" @selected($faktur->jenis_faktur === $jenis)>{{ $jenis }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Nama Faktur</label>
                        <input name="nama_faktur" value="{{ $faktur->nama_faktur }}" class="block w-full rounded-md border-slate-300 focus:border-blue-600 focus:ring-blue-600" required />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Nominal (Rp)</label>
                        <input name="nominal" type="number" min="0" value="{{ $faktur->nominal }}" class="block w-full rounded-md border-slate-300 focus:border-blue-600 focus:ring-blue-600" required />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Deskripsi</label>
                        <textarea name="deskripsi" rows="3" class="block w-full rounded-md border-slate-300 focus:border-blue-600 focus:ring-blue-600">{{ $faktur->deskripsi }}</textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" data-modal-close="edit-modal-{{ $faktur->id }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm text-slate-700">Batal</button>
                        <button type="submit" class="rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        <div id="delete-modal-{{ $faktur->id }}" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 px-4">
            <div class="w-full max-w-xl rounded-xl bg-white p-5 shadow-xl">
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="text-2xl font-semibold text-slate-900">Hapus Harga Tagihan</h2>
                    <button type="button" data-modal-close="delete-modal-{{ $faktur->id }}" class="text-slate-500 hover:text-slate-700">&times;</button>
                </div>
                <p class="mb-5 text-sm text-slate-600">
                    Apakah Anda yakin ingin menghapus harga tagihan "{{ $faktur->nama_faktur }}"?
                    Tindakan ini tidak dapat dibatalkan.
                </p>
                <div class="flex justify-end gap-2">
                    <button type="button" data-modal-close="delete-modal-{{ $faktur->id }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm text-slate-700">
                        Batal
                    </button>
                    <form method="POST" action="{{ route('bendahara.master-faktur.destroy', $faktur) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded-md bg-rose-500 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-600">
                            Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

    <script>
        document.querySelectorAll('[data-modal-open]').forEach((button) => {
            button.addEventListener('click', () => {
                const modalId = button.getAttribute('data-modal-open');
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                }
            });
        });

        document.querySelectorAll('[data-modal-close]').forEach((button) => {
            button.addEventListener('click', () => {
                const modalId = button.getAttribute('data-modal-close');
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }
            });
        });
    </script>
</x-app-layout>
