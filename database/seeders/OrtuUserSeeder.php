<?php

namespace Database\Seeders;

use App\Models\Siswa;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrtuUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil semua siswa yang belum memiliki akun orang tua
        $siswas = Siswa::all();
        $count = 0;

        foreach ($siswas as $siswa) {
            if (empty($siswa->nisn)) {
                continue;
            }

            $user = User::firstOrCreate(
                ['nisn' => $siswa->nisn],
                [
                    'name' => $siswa->nama_siswa,
                    'role' => 'orang_tua',
                    'password' => bcrypt($siswa->nisn),
                    'email' => strtolower($siswa->nisn) . '@student.local'
                ]
            );

            if ($user->wasRecentlyCreated) {
                $count++;
            }
        }

        $this->command->info("Berhasil men-generate $count akun Orang Tua baru untuk data Siswa yang sidah ada.");
    }
}
