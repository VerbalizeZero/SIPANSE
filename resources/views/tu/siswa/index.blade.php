<x-app-layout>
    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-4">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900">Data Siswa</h1>
                    <p class="text-sm text-slate-500">Kelola data siswa dari hasil daftar ulang</p>
                </div>

                <div class="mt-3 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <form id="siswa-filter-form" action="{{ route('tu.siswa.index') }}" method="GET" class="flex flex-1 flex-col gap-2 md:flex-row md:items-center">

                            {{-- Kolom Searching --}}
                            <div class="relative w-full md:w-96">
                                <input
                                    type="text"
                                    id="siswa-keyword-input"
                                    name="keyword"
                                    placeholder="Cari NISN, Siswa, atau Orang Tua..."
                                    value="{{ $keyword ?? request('keyword') }}"
                                    class="block w-full rounded-md border-slate-300 pr-9 focus:border-blue-600 focus:ring-blue-600 sm:text-sm"
                                />

                                {{-- Tombol Silang --}}
                                @if (!empty($keyword))
                                    <button 
                                    type="button" 
                                    id="siswa-clear-keyword" 
                                    class="absolute inset-y-0 right-2 my-auto h-6 w-6 rounded text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                                    aria-label="Clear keyword"
                                    title="Clear keyword"
                                    >
                                        &times;
                                    </button>    
                                @endif
                            </div>
                        
                            {{-- Dropdown Angkatan --}}
                            <select 
                            name="angkatan"
                            id="angkatan" 
                            class="block w-full rounded-md border-slate-300 focus:border-blue-600 focus:ring-blue-600 sm:text-sm md:w-48"
                            onchange="this.form.submit()"
                            >
                                <option value="">Pilih Tahun Angkatan</option>
                                @foreach (($angkatanOptions ?? []) as $option)
                                    <option value="{{ $option }}" {{ request('angkatan') == $option ? 'selected' : '' }}>
                                        {{ $option }}
                                    </option>
                                @endforeach
                            </select>

                            {{-- Dropdown Kelas --}}
                            <select 
                            name="kelas" 
                            id="kelas" 
                            class="block w-full rounded-md border-slate-300 focus:border-blue-600 focus:ring-blue-600 sm:text-sm md:w-32"
                            onchange="this.form.submit()"
                            >
                                <option value="">Pilih Kelas</option>
                                @foreach (($kelasOptions ?? []) as $option)
                                    <option value="{{ $option }}" {{ request('kelas') == $option ? 'selected' : '' }}>
                                        {{ $option }}
                                    </option>
                                @endforeach
                            </select>
                    </form>

                    {{-- Tombol Template & Add Siswa --}}
                    <div class="flex items-center gap-2 lg:shrink-0">
                        {{-- Ambil template CSV yang kolomnya disesuaikan dengan tabel siswas. --}}
                        <a
                            href="{{ route('tu.siswa.template') }}"
                            class="inline-flex items-center rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-blue-600 hover:text-white"
                        >
                            Template Data
                        </a>
                        {{-- Buka modal upload untuk proses import massal siswa. --}}
                        <button
                            type="button"
                            data-modal-open="upload-siswa-modal"
                            class="inline-flex items-center rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800"
                        >
                            + Tambah Siswa
                        </button>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="overflow-x-auto rounded-lg border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                <th class="px-4 py-3">Tahun Angkatan</th>
                                <th class="px-4 py-3">NISN</th>
                                <th class="px-4 py-3">Nama</th>
                                <th class="px-4 py-3">Jenis Kelamin</th>
                                <th class="px-4 py-3">Kelas</th>
                                <th class="px-4 py-3">Tanggal Lahir</th>
                                <th class="px-4 py-3">Alamat</th>
                                <th class="px-4 py-3">Nama Orang Tua</th>
                                <th class="px-4 py-3">No HP Ortu</th>
                                <th class="px-4 py-3">Email Ortu</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            {{-- Data tabel berasal dari controller: SiswaImportExportController@index --}}
                            @forelse ($siswas as $siswa)
                                <tr class="text-sm text-slate-700">
                                    <td class="px-4 py-3">{{ $siswa->tahun_angkatan }}</td>
                                    <td class="px-4 py-3">{{ $siswa->nisn }}</td>
                                    <td class="px-4 py-3">{{ $siswa->nama_siswa }}</td>
                                    <td class="px-4 py-3">
                                        @if ($siswa->jenis_kelamin === 'L')
                                            Laki-laki
                                        @elseif ($siswa->jenis_kelamin === 'P')
                                            Perempuan
                                        @else
                                            {{ $siswa->jenis_kelamin ?? '-' }}
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">{{ $siswa->kelas ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $siswa->tanggal_lahir ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $siswa->alamat ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $siswa->nama_ortu ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $siswa->no_hp_ortu ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $siswa->email_ortu ?? '-' }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="inline-flex items-center gap-2">
                                            {{-- Trigger modal edit, id modal unik per siswa. --}}
                                            <button
                                                type="button"
                                                data-modal-open="edit-siswa-modal-{{ $siswa->id }}"
                                                class="rounded-md border border-slate-200 px-2 py-1 text-xs font-medium text-slate-700 hover:bg-slate-50"
                                            >
                                                Edit
                                            </button>
                                            {{-- Trigger modal delete, id modal unik per siswa. --}}
                                            <button
                                                type="button"
                                                data-modal-open="delete-siswa-modal-{{ $siswa->id }}"
                                                class="rounded-md border border-rose-200 px-2 py-1 text-xs font-medium text-rose-600 hover:bg-rose-50"
                                            >
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="px-4 py-10 text-center align-middle text-sm text-slate-500">Belum ada data siswa.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $siswas->onEachSide(1)->links() }}
                </div>
            </div>
        </div>
    </div>

    <div id="upload-siswa-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 px-4">
        <div class="w-full max-w-xl rounded-xl bg-white p-5 shadow-xl">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-xl font-semibold text-slate-900">Upload Data Siswa</h2>
                <button type="button" data-modal-close="upload-siswa-modal" class="text-slate-500 hover:text-slate-700">&times;</button>
            </div>

            {{-- Submit file CSV/TXT ke endpoint import. --}}
            <form method="POST" action="{{ route('tu.siswa.import') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div id="siswa-dropzone" class="mx-auto max-w-md rounded-lg border border-dashed border-slate-300 px-6 py-8 text-center transition">
                    <p class="mb-3 text-sm text-slate-500">Drag &amp; drop file spreadsheet di sini</p>
                    <p class="mb-3 text-xs text-slate-400">atau</p>
                    <div class="flex flex-col items-center gap-2">
                        <label for="siswa-file-input" class="inline-flex cursor-pointer items-center rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                            Choose File
                        </label>
                        <input id="siswa-file-input" type="file" name="file" accept=".csv,.txt" class="hidden" required />
                        <p id="siswa-file-name" class="text-sm text-slate-500">No file chosen</p>
                    </div>
                    <p class="mt-3 text-xs text-slate-400">Format yang didukung: .csv, .txt</p>
                    @error('file')
                        <p class="mt-2 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button" data-modal-close="upload-siswa-modal" class="rounded-md border border-slate-300 px-4 py-2 text-sm text-slate-700">
                        Batal
                    </button>
                    <button type="submit" class="rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">
                        Upload
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal edit + delete dibuat per siswa agar data awal form langsung terisi. --}}
    @foreach ($siswas as $siswa)
        <div id="edit-siswa-modal-{{ $siswa->id }}" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 px-4">
            <div class="w-full max-w-2xl rounded-xl bg-white p-5 shadow-xl">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-slate-900">Edit Data Siswa</h2>
                    <button type="button" data-modal-close="edit-siswa-modal-{{ $siswa->id }}" class="text-slate-500 hover:text-slate-700">&times;</button>
                </div>
                {{-- Route-model binding: {siswa} akan di-resolve ke model Siswa pada controller update(). --}}
                <form method="POST" action="{{ route('tu.siswa.update', $siswa) }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Tahun Angkatan</label>
                            <input name="tahun_angkatan" value="{{ $siswa->tahun_angkatan }}" class="block w-full rounded-md border-slate-300 focus:border-blue-600 focus:ring-blue-600" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">NISN</label>
                            <input name="nisn" value="{{ $siswa->nisn }}" class="block w-full rounded-md border-slate-300 focus:border-blue-600 focus:ring-blue-600" required />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Nama Siswa</label>
                            <input name="nama_siswa" value="{{ $siswa->nama_siswa }}" class="block w-full rounded-md border-slate-300 focus:border-blue-600 focus:ring-blue-600" required />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="block w-full rounded-md border-slate-300 focus:border-blue-600 focus:ring-blue-600">
                                <option value="">Pilih jenis kelamin</option>
                                <option value="L" @selected($siswa->jenis_kelamin === 'L')>Laki-laki</option>
                                <option value="P" @selected($siswa->jenis_kelamin === 'P')>Perempuan</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Kelas</label>
                            <input name="kelas" value="{{ $siswa->kelas }}" class="block w-full rounded-md border-slate-300 focus:border-blue-600 focus:ring-blue-600" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Tanggal Lahir</label>
                            <input name="tanggal_lahir" type="date" value="{{ $siswa->tanggal_lahir }}" class="block w-full rounded-md border-slate-300 focus:border-blue-600 focus:ring-blue-600" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Nama Orang Tua</label>
                            <input name="nama_ortu" value="{{ $siswa->nama_ortu }}" class="block w-full rounded-md border-slate-300 focus:border-blue-600 focus:ring-blue-600" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">No HP Ortu</label>
                            <input name="no_hp_ortu" value="{{ $siswa->no_hp_ortu }}" class="block w-full rounded-md border-slate-300 focus:border-blue-600 focus:ring-blue-600" />
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-slate-700">Email Ortu</label>
                            <input name="email_ortu" type="email" value="{{ $siswa->email_ortu }}" class="block w-full rounded-md border-slate-300 focus:border-blue-600 focus:ring-blue-600" />
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-slate-700">Alamat</label>
                            <textarea name="alamat" rows="3" class="block w-full rounded-md border-slate-300 focus:border-blue-600 focus:ring-blue-600">{{ $siswa->alamat }}</textarea>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" data-modal-close="edit-siswa-modal-{{ $siswa->id }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm text-slate-700">Batal</button>
                        <button type="submit" class="rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        <div id="delete-siswa-modal-{{ $siswa->id }}" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 px-4">
            <div class="w-full max-w-xl rounded-xl bg-white p-5 shadow-xl">
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="text-2xl font-semibold text-slate-900">Hapus Data Siswa</h2>
                    <button type="button" data-modal-close="delete-siswa-modal-{{ $siswa->id }}" class="text-slate-500 hover:text-slate-700">&times;</button>
                </div>
                <p class="mb-5 text-sm text-slate-600">
                    Apakah Anda yakin ingin menghapus data siswa "{{ $siswa->nama_siswa }}"?
                    Tindakan ini tidak dapat dibatalkan.
                </p>
                <div class="flex justify-end gap-2">
                    <button type="button" data-modal-close="delete-siswa-modal-{{ $siswa->id }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm text-slate-700">
                        Batal
                    </button>
                    {{-- Hapus permanen data siswa setelah konfirmasi user. --}}
                    <form method="POST" action="{{ route('tu.siswa.destroy', $siswa) }}">
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
        // Generic modal handler: buka modal berdasarkan atribut data-modal-open.
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

        // Generic modal handler: tutup modal berdasarkan atribut data-modal-close.
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

        // Tampilkan nama file terpilih agar user yakin file yang diupload sudah benar.
        const fileInput = document.getElementById('siswa-file-input');
        const fileName = document.getElementById('siswa-file-name');
        const dropzone = document.getElementById('siswa-dropzone');
        if (fileInput && fileName) {
            fileInput.addEventListener('change', () => {
                fileName.textContent = fileInput.files?.[0]?.name ?? 'No file chosen';
            });
        }

        // Drag & drop handler agar file tidak dibuka browser, tapi masuk ke input upload.
        if (dropzone && fileInput && fileName) { // Pastikan elemen ada sebelum pasang event listener
            ['dragenter', 'dragover'].forEach((eventName) => {
                dropzone.addEventListener(eventName, (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    dropzone.classList.add('border-blue-500', 'bg-blue-50');
                });
            });

            ['dragleave', 'drop'].forEach((eventName) => { // Hilangkan efek highlight saat file keluar dari dropzone atau sudah dijatuhkan
                dropzone.addEventListener(eventName, (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    dropzone.classList.remove('border-blue-500', 'bg-blue-50');
                });
            });

            dropzone.addEventListener('drop', (event) => { // Tangani file yang dijatuhkan ke dropzone
                const files = event.dataTransfer?.files;
                if (!files || files.length === 0) {
                    return;
                }

                const file = files[0]; // Ambil file pertama jika ada multiple file
                const isAllowed = /\.(csv|txt)$/i.test(file.name);
                if (!isAllowed) {
                    fileName.textContent = 'Format file tidak didukung. Gunakan .csv atau .txt';
                    return;
                }

                const dataTransfer = new DataTransfer(); // Buat objek DataTransfer untuk memasukkan file ke input
                dataTransfer.items.add(file);
                fileInput.files = dataTransfer.files;
                fileName.textContent = file.name;
            });
        }

        // Jika validasi import gagal, modal upload dibuka lagi agar user langsung lihat error.
        @if ($errors->has('file'))
            const modal = document.getElementById('upload-siswa-modal');
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
        @endif

        // Fitur tambahan: tombol clear keyword untuk memudahkan pengguna menghapus filter pencarian dengan cepat.
        const siswaFilterForm = document.querySelector('form[action="{{ route('tu.siswa.index') }}"]');
        const siswaKeywordInput = document.getElementById('siswa-keyword-input');
        const siswaClearKeyword = document.getElementById('siswa-clear-keyword');

        siswaClearKeyword?.addEventListener('click', () => {
            if (!siswaKeywordInput || !siswaFilterForm) return;
            siswaKeywordInput.value = '';
            siswaFilterForm.submit();
        });

        // Auto-search dengan debounce + minimal 2 karakter.
        // const siswaFilterForm = document.getElementById('siswa-filter-form'); 
        // const siswaKeywordInput = document.getElementById('siswa-keyword-input');
        // let siswaSearchTimer = null;
        // if (siswaFilterForm && siswaKeywordInput) {
        //     siswaKeywordInput.addEventListener('input', () => {
        //         const value = siswaKeywordInput.value.trim();

        //         clearTimeout(siswaSearchTimer);
        //         siswaSearchTimer = setTimeout(() => {
        //             // Submit jika kosong (reset) atau minimal 2 huruf.
        //             if (value.length === 0 || value.length >= 2) {
        //                 siswaFilterForm.submit();
        //             }
        //         }, 400);
        //     });
        // }
    </script>
</x-app-layout>
