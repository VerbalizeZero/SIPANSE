<?php

namespace App\Observers;

use App\Models\Siswa;
use App\Models\User;

class SiswaObserver
{
    /**
     * Dijalankan otomatis ketika ada data Siswa baru berhasil disimpan
     *
     * @param  \App\Models\Siswa  $siswa
     * @return void
     */
    public function created(Siswa $siswa)
    {
        // Fungsi updateOrCreate akan mencari User berdasarkan NISN.
        // Jika belum ada, dia akan membuat baru. Jika ada, dia tidak merusak data lama.
        User::updateOrCreate(
            ['nisn' => $siswa->nisn],
            [
                'name' => $siswa->nama_siswa,
                'role' => 'orang_tua',
                // Tetap mengisi password default karena regulasi Auth database (tidak akan dicek tapi wajib ada)
                'password' => bcrypt($siswa->nisn),
                'email' => strtolower($siswa->nisn) . '@student.local' // Dummy email unik
            ]
        );
    }

    /**
     * Dijalankan setiap kali data Siswa diupdate (contoh: Koreksi nama / koreksi NISN)
     *
     * @param  \App\Models\Siswa  $siswa
     * @return void
     */
    public function updated(Siswa $siswa)
    {
        // Jika nama_siswa diubah dari TU, maka Nama Akun Orang Tua ikut direvisi
        User::updateOrCreate(
            ['nisn' => $siswa->nisn],
            [
                'name' => $siswa->nama_siswa,
                'role' => 'orang_tua',
                'email' => strtolower($siswa->nisn) . '@student.local'
            ]
        );
    }
}
