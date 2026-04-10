<?php

namespace App\Http\Controllers\Ortu;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\TuFaktur;
use App\Models\PenyerahanFaktur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FakturController extends Controller
{
    /**
     * Memunculkan Dashboard Faktur / Daftar tagihan Faktur untuk akun ini
     */
    public function index()
    {
        $user = Auth::user();
        
        // Cari data Siswa anak yang bersangkutan
        $siswa = Siswa::where('nisn', $user->nisn)->first();

        if (!$siswa) {
            abort(404, 'Data siswa tidak ditemukan.');
        }

        // Cari faktur-faktur yang dikhususkan (target_type) mengenai Siswa ini.
        $fakturs = TuFaktur::with(['masterFaktur'])
            // Status harus belum selesai
            ->where('status', '!=', 'Selesai')
            ->where(function ($query) use ($siswa) {
                // Semua
                $query->where('target_type', 'semua')
                
                // Berdasarkan angkatan
                ->orWhere(function ($q) use ($siswa) {
                    $q->where('target_type', 'angkatan')->where('target_value', $siswa->tahun_angkatan);
                })

                // Berdasarkan kelas
                ->orWhere(function ($q) use ($siswa) {
                    $q->where('target_type', 'kelas')->where('target_value', $siswa->kelas);
                })

                // Berdasarkan identitas personal (NISN/Nama)
                ->orWhere(function ($q) use ($siswa) {
                    $q->where('target_type', 'siswa')->where('target_value', 'like', '%' . $siswa->nisn . '%')
                      ->orWhere('target_value', 'like', '%' . $siswa->nama_siswa . '%');
                });
            })
            ->latest()
            ->get();

        // Cari status riwayat Penyerahan Faktur yang sudah kita buat jika ada
        $riwayats = PenyerahanFaktur::where('siswa_id', $siswa->id)->get()->keyBy('tu_faktur_id');

        return view('ortu.faktur.index', compact('fakturs', 'riwayats', 'siswa'));
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
            'berkas_file' => ['required', 'file', 'image', 'max:2048'], // Wajib file gambar max 2MB
        ], [
            'berkas_file.required' => 'Gagal Submit: Mohon melampirkan berkas bukti dokumen.',
            'berkas_file.file' => 'Berkas gagal diunggah. Hal ini biasanya terjadi jika file Anda (seperti gambar dari Unsplash) berukuran terlalu besar melebihi batas server komputer lokal.',
            'berkas_file.image' => 'Berkas harus berbentuk gambar (JPG/PNG).',
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
            ]
        );

        return back()->with('success', 'Faktur berhasil diserahkan dan saat ini Menunggu Verifikasi Tata Usaha.');
    }
}
