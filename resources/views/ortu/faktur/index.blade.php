<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dasbor Faktur') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Menampilkan pemberitahuan/sukses -->
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-bold mb-4">Profil Siswa</h3>
                <div class="mb-6 bg-slate-50 p-4 rounded-md border border-slate-200">
                    <p><strong>Nama Siswa:</strong> {{ $siswa->nama_siswa }}</p>
                    <p><strong>NISN:</strong> {{ $siswa->nisn }}</p>
                    <p><strong>Kelas:</strong> {{ $siswa->kelas ?? '-' }}</p>
                </div>

                <h3 class="text-lg font-bold mb-4 border-b pb-2">Daftar Faktur Berjalan</h3>
                
                @if($fakturs->isEmpty())
                    <p class="text-gray-500">Saat ini tidak ada penagihan faktur yang ditujukan untuk Anda.</p>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($fakturs as $faktur)
                            @php
                                $riwayat = $riwayats->get($faktur->id);
                                $statusLabel = 'Belum Diserahkan';
                                $statusBadge = 'bg-gray-100 text-gray-800';

                                if ($riwayat) {
                                    if ($riwayat->status === 'menunggu_verifikasi') {
                                        $statusLabel = 'Menunggu Verifikasi TU';
                                        $statusBadge = 'bg-yellow-100 text-yellow-800';
                                    } elseif ($riwayat->status === 'diverifikasi') {
                                        $statusLabel = 'Dokumen Diterima';
                                        $statusBadge = 'bg-green-100 text-green-800';
                                    } elseif ($riwayat->status === 'ditolak') {
                                        $statusLabel = 'Ditolak: ' . ($riwayat->catatan_penolakan ?? 'Harap lampirkan ulang');
                                        $statusBadge = 'bg-red-100 text-red-800';
                                    }
                                }
                            @endphp

                            <div class="border rounded-md p-4 shadow-sm bg-white">
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="font-bold text-md text-blue-600">{{ $faktur->masterFaktur->nama_faktur }}</h4>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $statusBadge }}">
                                        {{ $statusLabel }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600 mb-1">
                                    <strong>Nominal:</strong> Rp {{ number_format($faktur->masterFaktur->nominal, 0, ',', '.') }}
                                </p>
                                <p class="text-sm text-gray-600 mb-3">
                                    <strong>Batas Waktu:</strong> {{ \Carbon\Carbon::parse($faktur->tenggat_waktu)->format('d F Y') ?? '-' }}
                                </p>

                                <hr class="my-3 hover:border-gray-300">

                                <!-- Form Submit Laporan (Penyerahan) -->
                                <form action="{{ route('ortu.faktur.submit', $faktur->id) }}" method="POST" enctype="multipart/form-data" 
                                    x-data="{ fileChosen: false }">
                                    @csrf

                                    <div class="mb-3">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Unggah Bukti Dokumen (Wajib):</label>
                                        <!-- x-on:change mengatur state fileChosen menjadi true jika ada file -->
                                        <input type="file" name="berkas_file" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" accept="image/*" @change="fileChosen = $event.target.files.length > 0">
                                        @error('berkas_file')
                                            <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- Tombol menggunakan state x-bind:disabled dari Alpine JS -->
                                    <template x-if="!fileChosen">
                                        <p class="text-xs text-orange-500 mb-2 italic">*Pilih berkas untuk mengaktifkan tombol</p>
                                    </template>
                                    
                                    <button type="submit" 
                                        :disabled="!fileChosen" 
                                        :class="fileChosen ? 'bg-blue-600 hover:bg-blue-700 cursor-pointer' : 'bg-gray-400 cursor-not-allowed'"
                                        class="w-full text-white font-bold py-2 px-4 rounded transition">
                                        Kirim / Serahkan Berkas
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
