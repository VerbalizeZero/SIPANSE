<?php

namespace App\Http\Controllers\Ortu;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\TuFaktur;
use App\Models\PenyerahanFaktur;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FakturController extends Controller
{
    /**
     * Memunculkan Dashboard Faktur / Daftar tagihan Faktur untuk akun ini
     */
    public function index()
    {
        $user = Auth::user();
        $todayJakarta = Carbon::now('Asia/Jakarta')->toDateString();
        
        // Cari data Siswa anak yang bersangkutan
        $siswa = Siswa::where('nisn', $user->nisn)->first();

        if (!$siswa) {
            abort(404, 'Data siswa tidak ditemukan.');
        }

        // Cari faktur-faktur yang dikhususkan (target_type) mengenai Siswa ini.
        $fakturs = TuFaktur::with(['masterFaktur'])
            // Faktur "selesai" tetap boleh tampil jika siswa ini memang punya record verifikasi.
            ->where(function ($statusQuery) use ($siswa) {
                $statusQuery->whereRaw('LOWER(status) != ?', ['selesai'])
                    ->orWhereExists(function ($subQuery) use ($siswa) {
                        $subQuery->select(DB::raw(1))
                            ->from('penyerahan_fakturs')
                            ->whereColumn('penyerahan_fakturs.tu_faktur_id', 'tu_fakturs.id')
                            ->where('penyerahan_fakturs.siswa_id', $siswa->id);
                    });
            })
            // Faktur baru tampil ketika tanggal ketersediaannya sudah masuk (WIB/Jakarta).
            ->whereDate('tersedia_pada', '<=', $todayJakarta)
            ->where(function ($query) use ($siswa) {
                // Semua siswa: mendukung dua penamaan agar kompatibel dengan data lama/baru.
                $query->whereIn('target_type', ['semua', 'semua_siswa'])
                
                // Berdasarkan angkatan
                ->orWhere(function ($q) use ($siswa) {
                    $q->where('target_type', 'angkatan')->where('target_value', $siswa->tahun_angkatan);
                })

                // Berdasarkan kelas (mendukung format `D` dan format gabungan `2027|D`).
                ->orWhere(function ($q) use ($siswa) {
                    $q->where('target_type', 'kelas')
                        ->where(function ($kelasQuery) use ($siswa) {
                            $kelasQuery->where('target_value', $siswa->kelas)
                                ->orWhere('target_value', $siswa->tahun_angkatan . '|' . $siswa->kelas);
                        });
                })

                // Berdasarkan identitas personal (NISN/Nama)
                ->orWhere(function ($q) use ($siswa) {
                    $q->where('target_type', 'siswa')->where('target_value', 'like', '%' . $siswa->nisn . '%')
                      ->orWhere('target_value', 'like', '%' . $siswa->nama_siswa . '%');
                });
            })
            ->latest()
            ->paginate(10);

        // Cari status riwayat Penyerahan Faktur yang sudah kita buat jika ada
        $riwayats = PenyerahanFaktur::with('verifiedBy')
            ->where('siswa_id', $siswa->id)
            ->whereIn('tu_faktur_id', $fakturs->pluck('id'))
            ->get()
            ->keyBy('tu_faktur_id');

        $tuContacts = User::query()
            ->where('role', 'tu')
            ->select('name', 'email')
            ->orderBy('name')
            ->get();

        $tuContactPhone = (string) config('app.tu_contact_phone', env('TU_CONTACT_PHONE', 'Belum tersedia'));

        return view('ortu.faktur.index', compact('fakturs', 'riwayats', 'siswa', 'tuContacts', 'tuContactPhone'));
    }

    /**
     * Mengelola proses Submit (Unggah File)
     */
    public function submit(Request $request, TuFaktur $faktur)
    {
        $user = Auth::user();
        $siswa = Siswa::where('nisn', $user->nisn)->first();

        if (!$siswa) {
            abort(404, 'Data siswa tidak valid.');
        }

        // Tadi kita sepakat form wajib punya berkas gambar/file
        $request->validate([
            'berkas_file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ], [
            'berkas_file.required' => 'Gagal Submit: Mohon melampirkan berkas bukti dokumen.',
            'berkas_file.file' => 'Berkas gagal diunggah. Hal ini biasanya terjadi jika file Anda (seperti gambar dari Unsplash) berukuran terlalu besar melebihi batas server komputer lokal.',
            'berkas_file.mimes' => 'Berkas harus berbentuk JPG, JPEG, PNG, atau PDF.',
            'berkas_file.max' => 'Gagal Submit: Ukuran berkas tidak boleh melebihi 2MB.',
        ]);

        // Simpan file tersebut ke disk publik di folder 'penyerahan_faktur'
        $path = $request->file('berkas_file')->store('penyerahan_faktur', 'public');

        // Buat atau Update penyerahan laporan
        PenyerahanFaktur::updateOrCreate(
            [
                'tu_faktur_id' => $faktur->id,
                'siswa_id' => $siswa->id,
            ],
            [
                'berkas_file' => $path,
                'status' => 'menunggu_verifikasi',
                'catatan_penolakan' => null, // Reset status tolak jika di-update lagi
                'verified_by' => null,
                'verified_at' => null,
            ]
        );

        return back()->with('success', 'Faktur berhasil diserahkan dan saat ini Menunggu Verifikasi Tata Usaha.');
    }
}
