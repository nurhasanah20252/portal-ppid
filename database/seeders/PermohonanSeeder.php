<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PermohonanSeeder extends Seeder
{
    /**
     * Seed 50 permohonan dengan berbagai status, status_log, dan keberatan.
     *
     * Distribusi status:
     * - baru: 15
     * - diproses: 15
     * - selesai: 10
     * - ditolak: 10
     */
    public function run(): void
    {
        $admin = User::where('role', 'super_admin')->first();
        $staff = User::where('role', 'ppid_staff')->first();

        $namaIndonesia = [
            'Ahmad Fauzi', 'Siti Nurhaliza', 'Muhammad Rizki', 'Dewi Lestari',
            'Bambang Sutrisno', 'Rina Wulandari', 'Agus Prasetyo', 'Nur Aini',
            'Hendra Gunawan', 'Fitri Handayani', 'Wahyu Setiawan', 'Sri Mulyani',
            'Doni Firmansyah', 'Eka Putri', 'Joko Widodo', 'Ratna Sari',
            'Budi Santoso', 'Yuni Astuti', 'Andi Saputra', 'Mega Wati',
            'Rizal Ramadhan', 'Lina Marlina', 'Arif Hidayat', 'Nisa Ulhaq',
            'Fajar Nugroho', 'Dian Puspita', 'Rudi Hartono', 'Winda Sari',
            'Irfan Hakim', 'Putri Ayu', 'Surya Dharma', 'Ani Yudhoyono',
            'Galih Pratama', 'Maya Sari', 'Taufik Rahman', 'Indah Permata',
            'Bayu Aji', 'Sari Dewi', 'Umar Said', 'Lestari Ningrum',
            'Dimas Anggara', 'Aisyah Putri', 'Hendri Kusuma', 'Fatimah Zahra',
            'Yoga Pratama', 'Nadia Safitri', 'Rizky Maulana', 'Intan Permatasari',
            'Eko Prasetyo', 'Kartika Sari',
        ];

        $kotaKalimantan = [
            'Penajam', 'Balikpapan', 'Samarinda', 'Kutai Kartanegara',
            'Bontang', 'Berau', 'Paser', 'Tenggarong',
        ];

        $jenisInformasi = ['salinan_putusan', 'laporan_kinerja', 'lainnya'];

        $tujuan = [
            'Untuk keperluan pribadi dan arsip keluarga',
            'Untuk keperluan penelitian akademis',
            'Untuk keperluan pengurusan administrasi',
            'Untuk keperluan pembuktian hukum',
            'Untuk bahan referensi dan studi pustaka',
        ];

        $uraianInformasi = [
            'Memohon salinan putusan perkara cerai untuk keperluan pengurusan administrasi kependudukan.',
            'Memohon data laporan kinerja tahunan pengadilan untuk bahan penelitian skripsi.',
            'Memohon informasi terkait prosedur pendaftaran perkara di Pengadilan Agama Penajam.',
            'Memohon salinan putusan untuk keperluan balik nama sertifikat tanah.',
            'Memohon informasi statistik perkara yang ditangani pengadilan tahun berjalan.',
        ];

        $alasanTolak = [
            'Informasi yang dimohon termasuk dalam kategori informasi yang dikecualikan sesuai UU KIP Pasal 17.',
            'Pemohon tidak memenuhi syarat sebagai pemohon informasi publik.',
            'Dokumen yang dimohon masih dalam proses minutasi dan belum dapat diberikan.',
            'Informasi yang dimohon tidak tersedia atau tidak dimiliki oleh Pengadilan Agama Penajam.',
            'Permohonan ditolak karena identitas pemohon tidak dapat diverifikasi.',
        ];

        // Buat 50 permohonan dengan distribusi status
        $permohonanData = [];
        $statusDistribusi = array_merge(
            array_fill(0, 15, 'baru'),
            array_fill(0, 15, 'diproses'),
            array_fill(0, 10, 'selesai'),
            array_fill(0, 10, 'ditolak'),
        );

        // Shuffle untuk distribusi acak
        shuffle($statusDistribusi);

        foreach ($statusDistribusi as $index => $status) {
            $jenis = $jenisInformasi[array_rand($jenisInformasi)];
            $createdAt = now()->subDays(rand(1, 90))->subHours(rand(1, 23));

            $permohonan = [
                'tiket_no' => sprintf('PPID-%s-%04d', $createdAt->format('Ymd'), $index + 1),
                'nik' => $this->generateNik(),
                'nama_lengkap' => $namaIndonesia[$index],
                'alamat' => 'Jl. '.fake()->streetName().' No. '.rand(1, 200).', RT '.sprintf('%03d', rand(1, 15)).'/RW '.sprintf('%03d', rand(1, 10)),
                'kota' => $kotaKalimantan[array_rand($kotaKalimantan)],
                'provinsi' => 'Kalimantan Timur',
                'no_hp' => '08'.rand(10, 99).rand(1000000, 9999999),
                'email' => Str::slug($namaIndonesia[$index], '.').'@gmail.com',
                'ktp_path' => 'uploads/ktp/'.Str::random(40).'.jpg',
                'jenis_informasi' => $jenis,
                'nomor_perkara' => $jenis === 'salinan_putusan' ? rand(1, 500).'/Pdt.G/'.rand(2022, 2024).'/PA.Pnj' : null,
                'tujuan' => $tujuan[array_rand($tujuan)],
                'uraian_informasi' => $uraianInformasi[array_rand($uraianInformasi)],
                'status' => $status,
                'catatan_admin' => in_array($status, ['diproses', 'selesai']) ? 'Permohonan sedang diverifikasi dan diproses oleh petugas PPID.' : null,
                'dokumen_balasan' => $status === 'selesai' ? 'uploads/dokumen/'.Str::random(40).'.pdf' : null,
                'alasan_tolak' => $status === 'ditolak' ? $alasanTolak[array_rand($alasanTolak)] : null,
                'processed_at' => in_array($status, ['diproses', 'selesai', 'ditolak']) ? $createdAt->copy()->addDays(rand(1, 3)) : null,
                'completed_at' => in_array($status, ['selesai', 'ditolak']) ? $createdAt->copy()->addDays(rand(4, 10)) : null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ];

            $permohonanData[] = $permohonan;
        }

        // Insert semua permohonan
        foreach ($permohonanData as $data) {
            DB::table('permohonan')->insert($data);
        }

        // Buat status_log untuk setiap permohonan
        $permohonanRecords = DB::table('permohonan')->get();

        foreach ($permohonanRecords as $permohonan) {
            $this->createStatusLogs($permohonan, $admin, $staff);
        }

        // Buat keberatan untuk beberapa permohonan yang ditolak
        $ditolakPermohonan = DB::table('permohonan')
            ->where('status', 'ditolak')
            ->get();

        // Ambil 5 permohonan ditolak untuk dibuat keberatan
        $permohonanDenganKeberatan = $ditolakPermohonan->take(5);

        foreach ($permohonanDenganKeberatan as $permohonan) {
            $this->createKeberatan($permohonan);
        }
    }

    /**
     * Generate NIK 16 digit yang realistis.
     */
    private function generateNik(): string
    {
        // Format: PPRRKKDDMMYY0001
        // PP: kode provinsi (64 = Kaltim)
        // RR: kode kab/kota
        // KK: kode kecamatan
        $provinsi = '64';
        $kabkota = sprintf('%02d', rand(1, 10));
        $kecamatan = sprintf('%02d', rand(1, 15));
        $tanggal = sprintf('%02d', rand(1, 28));
        $bulan = sprintf('%02d', rand(1, 12));
        $tahun = sprintf('%02d', rand(70, 99));
        $urut = sprintf('%04d', rand(1, 9999));

        return $provinsi.$kabkota.$kecamatan.$tanggal.$bulan.$tahun.$urut;
    }

    /**
     * Buat status_log untuk satu permohonan sesuai statusnya.
     */
    private function createStatusLogs(object $permohonan, ?User $admin, ?User $staff): void
    {
        $createdBy = $admin?->id ?? $staff?->id;
        $baseTime = $permohonan->created_at;

        // Status log awal (saat dibuat)
        DB::table('status_log')->insert([
            'permohonan_id' => $permohonan->id,
            'status_lama' => null,
            'status_baru' => 'baru',
            'catatan' => 'Permohonan diterima oleh sistem.',
            'created_by' => null,
            'created_at' => $baseTime,
        ]);

        if (in_array($permohonan->status, ['diproses', 'selesai', 'ditolak'])) {
            DB::table('status_log')->insert([
                'permohonan_id' => $permohonan->id,
                'status_lama' => 'baru',
                'status_baru' => 'diproses',
                'catatan' => 'Permohonan mulai diproses oleh petugas PPID.',
                'created_by' => $createdBy,
                'created_at' => $permohonan->processed_at,
            ]);
        }

        if ($permohonan->status === 'selesai') {
            DB::table('status_log')->insert([
                'permohonan_id' => $permohonan->id,
                'status_lama' => 'diproses',
                'status_baru' => 'selesai',
                'catatan' => 'Permohonan selesai, dokumen balasan telah diunggah.',
                'created_by' => $createdBy,
                'created_at' => $permohonan->completed_at,
            ]);
        }

        if ($permohonan->status === 'ditolak') {
            DB::table('status_log')->insert([
                'permohonan_id' => $permohonan->id,
                'status_lama' => 'diproses',
                'status_baru' => 'ditolak',
                'catatan' => $permohonan->alasan_tolak,
                'created_by' => $createdBy,
                'created_at' => $permohonan->completed_at,
            ]);
        }
    }

    /**
     * Buat keberatan untuk permohonan yang ditolak.
     */
    private function createKeberatan(object $permohonan): void
    {
        $statusOptions = ['dikirim', 'diproses', 'selesai'];
        $status = $statusOptions[array_rand($statusOptions)];

        $alasanKeberatan = [
            'Penolakan permohonan tidak disertai alasan yang jelas sesuai ketentuan UU KIP.',
            'Informasi yang dimohon seharusnya termasuk informasi publik yang wajib disediakan.',
            'Pemohon merasa alasan penolakan tidak berdasar dan meminta peninjauan ulang.',
            'Dokumen yang dimohon bukan termasuk informasi yang dikecualikan.',
            'Pemohon memiliki kepentingan hukum yang sah atas informasi yang dimohonkan.',
        ];

        $createdAt = $permohonan->completed_at
            ? Carbon::parse($permohonan->completed_at)->addDays(rand(1, 7))
            : now()->subDays(rand(1, 30));

        DB::table('keberatan')->insert([
            'permohonan_id' => $permohonan->id,
            'nama_pemohon' => $permohonan->nama_lengkap,
            'alasan' => $alasanKeberatan[array_rand($alasanKeberatan)],
            'status' => $status,
            'tanggapan_admin' => $status === 'selesai' ? 'Keberatan telah ditinjau. Berdasarkan pertimbangan atasan PPID, permohonan informasi akan diproses kembali.' : null,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
            'resolved_at' => $status === 'selesai' ? $createdAt->copy()->addDays(rand(3, 14)) : null,
        ]);
    }
}
