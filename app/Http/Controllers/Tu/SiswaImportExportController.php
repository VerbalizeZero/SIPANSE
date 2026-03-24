<?php

namespace App\Http\Controllers\Tu;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class SiswaImportExportController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'keyword' => ['nullable', 'string', 'max:100'],
            'angkatan' => ['nullable', 'string', 'max:20'],
            'kelas' => ['nullable', 'string', 'max:50'],
        ]);

        $keyword = trim((string) ($validated['keyword'] ?? ''));
        $angkatan = trim((string) ($validated['angkatan'] ?? ''));
        $kelas = trim((string) ($validated['kelas'] ?? ''));

        // $keyword = trim((string) $request->query('keyword', '')); // Ambil keyword pencarian dari query string, default kosong jika tidak ada. Trim untuk membersihkan spasi ekstra.
        // $angkatan = trim((string) $request->query('angkatan', '')); // Ambil filter angkatan dari query string, default kosong jika tidak ada. Trim untuk membersihkan spasi ekstra.
        // $kelas = trim((string) $request->query('kelas', '')); // Ambil filter kelas dari query string, default kosong jika tidak ada. Trim untuk membersihkan spasi ekstra

        // Tampilkan data terbaru duluan agar entri baru langsung terlihat di tabel.
        //$siswas = Siswa::query()->latest()->get(); // Urutkan berdasarkan created_at DESC untuk menampilkan data terbaru di atas.

        // SEARCHING
        // Bangun query dengan kondisi pencarian dan filter yang fleksibel. Gunakan when() untuk menambahkan kondisi hanya jika parameter ada.
        $siswas = Siswa::query()
            ->when($keyword !== '', function ($query) use ($keyword) { // Jika ada keyword pencarian, tambahkan kondisi pencarian di kolom nisn, nama_siswa, atau nama_ortu.
                $query->where(function ($subQuery) use ($keyword) { // Bungkus kondisi pencarian dalam sub-query untuk memastikan logika OR bekerja dengan benar.
                    $subQuery->where('nisn', 'like', "%{$keyword}%")
                        ->orWhere('nama_siswa', 'like', "%{$keyword}%")
                        ->orWhere('nama_ortu', 'like', "%{$keyword}%");
                });
            })

            // FILTERING
            ->when($angkatan !=='', function ($query) use ($angkatan) { // Jika ada filter angkatan, tambahkan kondisi filter berdasarkan tahun_angkatan.
                return $query->where('tahun_angkatan', $angkatan); // Filter tahun_angkatan harus cocok persis dengan input filter.
            })
            ->when($kelas !=='', function ($query) use ($kelas) { // Jika ada filter kelas, tambahkan kondisi filter berdasarkan kelas.
                return $query->where('kelas', $kelas); // Filter kelas harus cocok persis dengan input filter.
            });
            
            // PAGINATION
            $siswas = $siswas
                ->latest()
                ->paginate(1)
                ->withQueryString(); // Bawa query filter ke link pagination.

            // DROPDOWN OPTIONS
            $angkatanOptions = Siswa::query()
                ->select('tahun_angkatan')
                ->distinct()
                ->whereNotNull('tahun_angkatan')
                ->where('tahun_angkatan', '!=', '')
                ->orderByDesc('tahun_angkatan')
                ->pluck('tahun_angkatan'); // Ambil daftar tahun_angkatan unik untuk opsi filter dropdown.

            $kelasOptions = Siswa::query()
                ->select('kelas')
                ->distinct()
                ->whereNotNull('kelas')
                ->where('kelas', '!=', '')
                ->orderBy('kelas')
                ->pluck('kelas'); // Ambil daftar kelas unik untuk opsi filter dropdown.


        return view('tu.siswa.index', compact(
            'siswas',
            'keyword',
            'angkatan',
            'kelas',
            'angkatanOptions',
            'kelasOptions'
        )); // Kirim data siswa ke view untuk ditampilkan di tabel. View akan menangani format tampilan, termasuk tanggal lahir dan jenis kelamin.
    }

    public function downloadTemplate(): Response
    {
        // Ambil kolom tabel aktual agar template selalu sinkron dengan schema DB.
        $columns = collect(Schema::getColumnListing('siswas'))
            ->reject(fn (string $column) => in_array($column, ['id', 'created_at', 'updated_at'], true))
            ->values()
            ->all();

        $csv = implode(',', $columns); // Header CSV dengan kolom dari DB, dipisahkan koma.

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template-data-siswa.csv"',
        ]);
    }

    public function import(Request $request): RedirectResponse
    {
        // Batasi tipe file agar parser hanya memproses file teks terstruktur.
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $file = $validated['file'];
        $handle = fopen($file->getRealPath(), 'r'); // Buka file untuk dibaca.

        if ($handle === false) {
            return back()->withErrors(['file' => 'File tidak dapat dibaca.']);
        }

        $header = fgetcsv($handle);

        if (!is_array($header) || empty($header)) {
            fclose($handle);
            return back()->withErrors(['file' => 'Header file tidak valid.']);
        }

        // Minimal kolom identitas untuk membuat/memperbarui record.
        $requiredColumns = ['nisn', 'nama_siswa'];
        foreach ($requiredColumns as $requiredColumn) {
            if (!in_array($requiredColumn, $header, true)) {
                fclose($handle);
                return back()->withErrors(['file' => 'Format file template tidak sesuai.']);
            }
        }

        $rowNumber = 1;
        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;

            // Abaikan baris rusak yang jumlah kolomnya tidak sesuai header.
            if (count($row) !== count($header)) {
                continue;
            }

            $rowData = array_combine($header, $row);

            if (!is_array($rowData)) {
                continue;
            }

            $nisn = trim((string) ($rowData['nisn'] ?? ''));
            $namaSiswa = trim((string) ($rowData['nama_siswa'] ?? ''));

            if ($nisn === '' || $namaSiswa === '') {
                fclose($handle);
                return back()->withErrors(['file' => 'Data siswa wajib berisi NISN dan Nama Siswa.']);
            }

            $rawTanggalLahir = $rowData['tanggal_lahir'] ?? null;
            $normalizedTanggalLahir = $this->normalizeDate($rawTanggalLahir);
            // Jika user mengisi tanggal tapi formatnya tidak dikenali, hentikan import dengan error yang jelas.
            if ($this->nullableTrim($rawTanggalLahir) !== null && $normalizedTanggalLahir === null) {
                fclose($handle);
                return back()->withErrors([
                    'file' => "Format tanggal_lahir tidak valid pada baris {$rowNumber}. Gunakan DD/MM/YYYY.",
                ]);
            }

            // Semua field dibersihkan/normalisasi dulu sebelum persist ke DB.
            $payload = [
                'tahun_angkatan' => $this->nullableTrim($rowData['tahun_angkatan'] ?? null),
                'nama_siswa' => $namaSiswa,
                // DB siswas.jenis_kelamin disimpan 1 karakter: L/P.
                'jenis_kelamin' => $this->normalizeJenisKelamin($rowData['jenis_kelamin'] ?? null),
                'kelas' => $this->nullableTrim($rowData['kelas'] ?? null),
                'tanggal_lahir' => $normalizedTanggalLahir,
                'alamat' => $this->nullableTrim($rowData['alamat'] ?? null),
                'nama_ortu' => $this->nullableTrim($rowData['nama_ortu'] ?? null),
                'no_hp_ortu' => $this->nullableTrim($rowData['no_hp_ortu'] ?? null),
                'email_ortu' => $this->nullableTrim($rowData['email_ortu'] ?? null),
            ];

            try {
                // Kunci sinkronisasi data siswa ada di NISN (unik).
                Siswa::updateOrCreate(
                    ['nisn' => $nisn],
                    $payload
                );
            } catch (\Throwable $exception) {
                fclose($handle);

                return back()->withErrors([
                    'file' => "Data pada baris {$rowNumber} gagal diimport. Cek format data, terutama tanggal_lahir.",
                ]);
            }
        }

        fclose($handle);

        return redirect()->route('tu.siswa.index');
    }

    public function update(Request $request, Siswa $siswa): RedirectResponse
    {
        // Validasi disamakan dengan batas kolom DB untuk mencegah QueryException "data too long".
        $validated = $request->validate([
            'tahun_angkatan' => ['nullable', 'string', 'max:255'],
            // Saat edit, NISN milik record saat ini tetap dianggap valid.
            'nisn' => ['required', 'string', 'max:20', Rule::unique('siswas', 'nisn')->ignore($siswa->id)],
            'nama_siswa' => ['required', 'string', 'max:255'],
            'jenis_kelamin' => ['nullable', 'string', 'max:20'],
            'kelas' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir' => ['nullable', 'date'],
            'alamat' => ['nullable', 'string'],
            'nama_ortu' => ['nullable', 'string', 'max:255'],
            'no_hp_ortu' => ['nullable', 'string', 'max:20'],
            'email_ortu' => ['nullable', 'email', 'max:255'],
        ]);

        // Normalisasi akhir sebelum update untuk menjamin format tersimpan konsisten.
        $validated['jenis_kelamin'] = $this->normalizeJenisKelamin($validated['jenis_kelamin'] ?? null);

        $siswa->update($validated);

        return redirect()->route('tu.siswa.index');
    }

    public function destroy(Siswa $siswa): RedirectResponse
    {
        $siswa->delete();

        return redirect()->route('tu.siswa.index');
    }

    private function nullableTrim(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        // Bersihkan input copy-paste (termasuk non-breaking space) agar tidak menyisakan karakter "tak terlihat".
        $trimmed = trim((string) $value);
        $trimmed = str_replace("\u{00A0}", ' ', $trimmed);
        $trimmed = trim($trimmed, " \t\n\r\0\x0B'\"");

        return $trimmed === '' ? null : $trimmed;
    }

    private function normalizeDate(mixed $value): ?string
    {
        $date = $this->nullableTrim($value);

        if ($date === null) {
            return null;
        }

        // Terima variasi pemisah tanggal yang umum dari CSV manual.
        $date = str_replace(['.', '\\'], ['-', '/'], $date);

        // Dukungan serial date dari Excel.
        if (is_numeric($date)) {
            $serial = (int) $date;
            if ($serial > 0) {
                return now()->setDate(1899, 12, 30)->addDays($serial)->format('Y-m-d');
            }
        }

        if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $date, $matches) === 1) {
            [$full, $year, $month, $day] = $matches;
            if (checkdate((int) $month, (int) $day, (int) $year)) {
                return sprintf('%04d-%02d-%02d', (int) $year, (int) $month, (int) $day);
            }
        }

        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $date, $matches) === 1) {
            [$full, $day, $month, $year] = $matches;
            if (checkdate((int) $month, (int) $day, (int) $year)) {
                return sprintf('%04d-%02d-%02d', (int) $year, (int) $month, (int) $day);
            }
        }

        return null;
    }

    private function normalizeJenisKelamin(mixed $value): ?string
    {
        $jenisKelamin = $this->nullableTrim($value);

        if ($jenisKelamin === null) {
            return null;
        }

        // Simpan sebagai kode 1 karakter untuk sesuai schema: L/P.
        $normalized = strtolower($jenisKelamin);

        if (in_array($normalized, ['l', 'laki', 'laki-laki', 'laki laki', 'male', 'pria'], true)) {
            return 'L';
        }

        if (in_array($normalized, ['p', 'perempuan', 'female', 'wanita'], true)) {
            return 'P';
        }

        return strtoupper(substr($jenisKelamin, 0, 1));
    }
}
