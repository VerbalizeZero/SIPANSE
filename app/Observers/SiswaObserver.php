<?php

namespace App\Observers;

use App\Models\Siswa;
use App\Models\User;

class SiswaObserver
{
    /**
     * Handle the Siswa "created" event.
     */
    public function created(Siswa $siswa): void
    {
        $this->createOrUpdateOrtuUser($siswa);
    }

    /**
     * Handle the Siswa "updated" event.
     */
    public function updated(Siswa $siswa): void
    {
        $this->createOrUpdateOrtuUser($siswa);
    }

    /**
     * Handle the Siswa "deleted" event.
     */
    public function deleted(Siswa $siswa): void
    {
        // Optional: Hapus juga user orang_tua jika siswa dihapus
        // Uncomment jika ingin menghapus user orang_tua saat siswa dihapus
        // $user = User::where('nisn', $siswa->nisn)->where('role', 'orang_tua')->first();
        // if ($user) {
        //     $user->delete();
        // }
    }

    /**
     * Create or update Orang Tua user based on Siswa data
     */
    private function createOrUpdateOrtuUser(Siswa $siswa): void
    {
        if (empty($siswa->nisn) || empty($siswa->email_ortu)) {
            return;
        }

        $user = User::where('nisn', $siswa->nisn)
            ->where('role', 'orang_tua')
            ->first();

        $userData = [
            'name' => $siswa->nama_ortu ?: $siswa->nama_siswa,
            'email' => $siswa->email_ortu,
        ];

        if ($user) {
            // Update user yang sudah ada
            $user->update($userData);
        } else {
            // Create user baru jika belum ada
            User::create([
                'nisn' => $siswa->nisn,
                'name' => $siswa->nama_ortu ?: $siswa->nama_siswa,
                'email' => $siswa->email_ortu,
                'role' => 'orang_tua',
                'password' => bcrypt($siswa->nisn), // Default password adalah NISN
            ]);
        }
    }
}