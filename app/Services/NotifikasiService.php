<?php

namespace App\Services;

use App\Models\Notifikasi;
use App\Models\Siswa;
use App\Models\TuFaktur;
use App\Models\User;

class NotifikasiService
{
    /**
     * Kirim notifikasi ke semua ortu yang siswanya masuk target TuFaktur.
     */
    public static function notifyOrtuForNewFaktur(TuFaktur $tuFaktur): void
    {
        $masterFaktur = $tuFaktur->masterFaktur;
        if (!$masterFaktur) {
            return;
        }

        $siswaQuery = Siswa::query();

        switch ($tuFaktur->target_type) {
            case 'semua':
            case 'semua_siswa':
                // semua siswa
                break;
            case 'angkatan':
                $siswaQuery->where('tahun_angkatan', $tuFaktur->target_value);
                break;
            case 'kelas':
                $parts = explode('|', $tuFaktur->target_value);
                if (count($parts) === 2) {
                    $siswaQuery->where('tahun_angkatan', $parts[0])->where('kelas', $parts[1]);
                } else {
                    $siswaQuery->where('kelas', $tuFaktur->target_value);
                }
                break;
            case 'siswa':
                $siswaQuery->where(function ($q) use ($tuFaktur) {
                    $q->where('nisn', 'like', "%{$tuFaktur->target_value}%")
                      ->orWhere('nama_siswa', 'like', "%{$tuFaktur->target_value}%");
                });
                break;
            default:
                return;
        }

        $nisnList = $siswaQuery->pluck('nisn');

        if ($nisnList->isEmpty()) {
            return;
        }

        $ortuUsers = User::whereIn('role', ['orang_tua', 'ortu'])
            ->whereIn('nisn', $nisnList)
            ->get();

        $title = 'Faktur Baru Tersedia';
        $message = "Faktur {$masterFaktur->nama_faktur} telah tersedia untuk siswa Anda.";
        $url = route('ortu.faktur.index');

        $data = [];
        $now = now();
        foreach ($ortuUsers as $user) {
            $data[] = [
                'user_id' => $user->id,
                'title' => $title,
                'message' => $message,
                'url' => $url,
                'read_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (!empty($data)) {
            Notifikasi::insert($data);
        }
    }

    /**
     * Kirim notifikasi ke ortu bahwa faktur telah diverifikasi (diterima/ditolak).
     */
    public static function notifyOrtuForVerifikasi(TuFaktur $tuFaktur, Siswa $siswa, string $status): void
    {
        $masterFaktur = $tuFaktur->masterFaktur;
        if (!$masterFaktur) {
            return;
        }

        $ortu = User::whereIn('role', ['orang_tua', 'ortu'])
            ->where('nisn', $siswa->nisn)
            ->first();

        if (!$ortu) {
            return;
        }

        $title = $status === 'diverifikasi' ? 'Faktur Diterima' : 'Faktur Ditolak';
        $message = $status === 'diverifikasi'
            ? "Faktur {$masterFaktur->nama_faktur} untuk {$siswa->nama_siswa} telah diterima."
            : "Faktur {$masterFaktur->nama_faktur} untuk {$siswa->nama_siswa} telah ditolak.";
        $url = route('ortu.faktur.index');

        Notifikasi::create([
            'user_id' => $ortu->id,
            'title' => $title,
            'message' => $message,
            'url' => $url,
            'read_at' => null,
        ]);
    }

    /**
     * Hapus notifikasi yang sudah lebih dari X hari.
     */
    public static function pruneOldNotifications(int $days = 3): int
    {
        return Notifikasi::where('created_at', '<', now()->subDays($days))->delete();
    }
}
