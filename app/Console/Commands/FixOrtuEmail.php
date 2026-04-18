<?php

namespace App\Console\Commands;

use App\Models\Siswa;
use App\Models\User;
use Illuminate\Console\Command;

class FixOrtuEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ortu:fix-email';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perbaiki email user Orang Tua yang salah dengan email dari tabel Siswa';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Memperbaiki email user Orang Tua...');

        // Ambil semua user dengan role orang_tua
        $users = User::where('role', 'orang_tua')->get();
        $updated = 0;
        $errors = 0;
        $skipped = 0;

        foreach ($users as $user) {
            if (empty($user->nisn)) {
                $this->warn("User ID {$user->id} tidak memiliki NISN, melewati...");
                $skipped++;
                continue;
            }

            // Cari data siswa berdasarkan NISN
            $siswa = Siswa::where('nisn', $user->nisn)->first();

            if (!$siswa) {
                $this->warn("User ID {$user->id} dengan NISN {$user->nisn} tidak ditemukan di tabel Siswa, melewati...");
                $errors++;
                continue;
            }

            // Cek apakah email_siswa sudah benar (sama dengan email_ortu)
            $correctEmail = $siswa->email_ortu;

            if (empty($correctEmail)) {
                $this->warn("Siswa dengan NISN {$siswa->nisn} tidak memiliki email_ortu, melewati...");
                $skipped++;
                continue;
            }

            // Cek apakah email user sudah benar
            if ($user->email === $correctEmail) {
                $this->line("User ID {$user->id} (NISN: {$siswa->nisn}) email sudah benar, melewati...");
                $skipped++;
                continue;
            }

            // Update email user
            $oldEmail = $user->email;
            $user->email = $correctEmail;

            // Jika email yang baru sudah ada di user lain, tambahkan suffix
            if (User::where('email', $correctEmail)->where('id', '!=', $user->id)->exists()) {
                // Jika email duplikat, tambahkan suffix berdasarkan NISN
                $emailParts = explode('@', $correctEmail);
                $user->email = $emailParts[0] . '+nisn' . substr($siswa->nisn, -4) . '@' . $emailParts[1];
                $this->warn("Email {$correctEmail} duplikat, menggunakan alternatif: {$user->email}");
            }

            $user->save();
            $updated++;

            // Update juga nama user jika masih nama siswa
            if ($siswa->nama_ortu && $user->name !== $siswa->nama_ortu) {
                $user->name = $siswa->nama_ortu;
                $user->save();
            }

            $this->info("✓ User ID {$user->id} (NISN: {$siswa->nisn}) - Email diperbaiki: {$oldEmail} → {$user->email}");
        }

        $this->newLine();
        $this->info('=== Summary ===');
        $this->info("Berhasil diperbarui: {$updated}");
        $this->info("Dilewati (sudah benar/tidak ada email_ortu): {$skipped}");
        $this->info("Gagal/error: {$errors}");
        $this->info("Total user orang_tua: {$users->count()}");

        return Command::SUCCESS;
    }
}