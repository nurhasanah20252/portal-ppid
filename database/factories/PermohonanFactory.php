<?php

namespace Database\Factories;

use App\Models\Permohonan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Permohonan>
 */
class PermohonanFactory extends Factory
{
    protected $model = Permohonan::class;

    /**
     * Daftar provinsi Indonesia untuk data faker.
     *
     * @var array<int, string>
     */
    private const PROVINSI = [
        'Kalimantan Timur', 'DKI Jakarta', 'Jawa Barat', 'Jawa Tengah',
        'Jawa Timur', 'Sumatera Utara', 'Sulawesi Selatan', 'Bali',
        'Kalimantan Selatan', 'Kalimantan Barat', 'Papua', 'Aceh',
        'Sumatera Barat', 'Riau', 'Lampung', 'Banten',
    ];

    /**
     * Daftar kota Indonesia untuk data faker.
     *
     * @var array<int, string>
     */
    private const KOTA = [
        'Penajam', 'Balikpapan', 'Samarinda', 'Jakarta Pusat',
        'Bandung', 'Surabaya', 'Semarang', 'Medan',
        'Makassar', 'Denpasar', 'Banjarmasin', 'Pontianak',
        'Yogyakarta', 'Malang', 'Bogor', 'Tangerang',
    ];

    /**
     * Daftar nama depan Indonesia untuk data faker.
     *
     * @var array<int, string>
     */
    private const NAMA_DEPAN = [
        'Ahmad', 'Muhammad', 'Siti', 'Dewi', 'Budi', 'Andi',
        'Rizki', 'Putri', 'Agus', 'Sri', 'Wahyu', 'Dian',
        'Bambang', 'Eko', 'Tri', 'Nur', 'Wati', 'Yuni',
    ];

    /**
     * Daftar nama belakang Indonesia untuk data faker.
     *
     * @var array<int, string>
     */
    private const NAMA_BELAKANG = [
        'Pratama', 'Santoso', 'Wijaya', 'Hidayat', 'Kusuma',
        'Saputra', 'Rahayu', 'Wibowo', 'Setiawan', 'Lestari',
        'Nugroho', 'Susanto', 'Utami', 'Permana', 'Ramadhan',
    ];

    /**
     * Definisi state default model.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $jenisInformasi = fake()->randomElement(['salinan_putusan', 'laporan_kinerja', 'lainnya']);
        $tanggal = fake()->dateTimeBetween('-6 months', 'now');
        $urutanHarian = fake()->numberBetween(1, 9999);

        return [
            'tiket_no' => sprintf('PPID-%s-%04d', $tanggal->format('Ymd'), $urutanHarian),
            'nik' => $this->generateNik(),
            'nama_lengkap' => $this->generateNamaIndonesia(),
            'alamat' => $this->generateAlamatIndonesia(),
            'kota' => fake()->randomElement(self::KOTA),
            'provinsi' => fake()->randomElement(self::PROVINSI),
            'no_hp' => $this->generateNoHp(),
            'email' => fake()->unique()->safeEmail(),
            'ktp_path' => null,
            'jenis_informasi' => $jenisInformasi,
            'nomor_perkara' => $jenisInformasi === 'salinan_putusan'
                ? $this->generateNomorPerkara()
                : null,
            'tujuan' => fake()->randomElement([
                'Untuk kepentingan penelitian akademis',
                'Untuk kebutuhan administrasi pribadi',
                'Untuk pengurusan dokumen resmi',
                'Untuk kepentingan hukum',
                'Untuk bahan kajian dan analisis',
            ]),
            'uraian_informasi' => fake()->randomElement([
                'Mohon diberikan salinan dokumen terkait perkara yang telah diputuskan',
                'Membutuhkan informasi laporan kinerja tahunan instansi',
                'Perlu data statistik layanan publik untuk penelitian',
                'Meminta informasi mengenai prosedur dan mekanisme pelayanan',
                'Membutuhkan salinan dokumen untuk keperluan administrasi',
            ]),
            'status' => 'baru',
            'catatan_admin' => null,
            'dokumen_balasan' => null,
            'alasan_tolak' => null,
            'processed_at' => null,
            'completed_at' => null,
        ];
    }

    /**
     * State: status baru (default).
     */
    public function baru(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'baru',
            'processed_at' => null,
            'completed_at' => null,
        ]);
    }

    /**
     * State: status diproses.
     */
    public function diproses(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'diproses',
            'processed_at' => now('Asia/Makassar'),
            'completed_at' => null,
        ]);
    }

    /**
     * State: status selesai.
     */
    public function selesai(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'selesai',
            'processed_at' => now('Asia/Makassar')->subDays(3),
            'completed_at' => now('Asia/Makassar'),
            'catatan_admin' => 'Permohonan telah diproses dan dokumen telah disiapkan.',
        ]);
    }

    /**
     * State: status ditolak.
     */
    public function ditolak(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'ditolak',
            'processed_at' => now('Asia/Makassar')->subDays(2),
            'completed_at' => now('Asia/Makassar'),
            'alasan_tolak' => 'Informasi yang diminta termasuk kategori informasi yang dikecualikan berdasarkan undang-undang.',
        ]);
    }

    /**
     * State: jenis informasi salinan putusan (dengan nomor perkara).
     */
    public function salinanPutusan(): static
    {
        return $this->state(fn (array $attributes) => [
            'jenis_informasi' => 'salinan_putusan',
            'nomor_perkara' => $this->generateNomorPerkara(),
        ]);
    }

    /**
     * Generate NIK 16 digit angka.
     */
    private function generateNik(): string
    {
        return fake()->numerify('################');
    }

    /**
     * Generate nama lengkap Indonesia (min 3 karakter).
     */
    private function generateNamaIndonesia(): string
    {
        return fake()->randomElement(self::NAMA_DEPAN).' '.fake()->randomElement(self::NAMA_BELAKANG);
    }

    /**
     * Generate alamat bergaya Indonesia.
     */
    private function generateAlamatIndonesia(): string
    {
        $jalan = fake()->randomElement(['Jl.', 'Gang', 'Komp.']);
        $nama = fake()->randomElement(['Merdeka', 'Sudirman', 'Gatot Subroto', 'Diponegoro', 'Ahmad Yani', 'Kartini']);
        $nomor = fake()->numberBetween(1, 200);
        $rt = fake()->numberBetween(1, 20);
        $rw = fake()->numberBetween(1, 10);

        return "{$jalan} {$nama} No. {$nomor}, RT {$rt}/RW {$rw}";
    }

    /**
     * Generate nomor HP Indonesia (10-15 digit, diawali 08).
     */
    private function generateNoHp(): string
    {
        $panjang = fake()->numberBetween(10, 13);

        return '08'.fake()->numerify(str_repeat('#', $panjang - 2));
    }

    /**
     * Generate nomor perkara pengadilan.
     */
    private function generateNomorPerkara(): string
    {
        $nomor = fake()->numberBetween(1, 500);
        $jenis = fake()->randomElement(['Pdt.G', 'Pdt.P', 'Pdt']);
        $tahun = fake()->numberBetween(2020, 2025);

        return "{$nomor}/{$jenis}/{$tahun}/PA.Pnj";
    }
}
