<?php

namespace Database\Factories;

use App\Models\InformasiPublik;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InformasiPublik>
 */
class InformasiPublikFactory extends Factory
{
    protected $model = InformasiPublik::class;

    /**
     * Judul dokumen informasi publik yang realistis.
     *
     * @var array<int, string>
     */
    private const JUDUL = [
        'Laporan Kinerja Tahunan Pengadilan Agama Penajam',
        'Rekapitulasi Perkara Masuk dan Putus',
        'Laporan Keuangan Semester',
        'Standar Operasional Prosedur Pelayanan',
        'Struktur Organisasi dan Tata Kerja',
        'Program Kerja Tahunan',
        'Laporan Pelaksanaan Anggaran',
        'Data Statistik Perkara',
        'Profil Pengadilan Agama Penajam',
        'Rencana Strategis Pengadilan',
        'Laporan Survei Kepuasan Masyarakat',
        'Daftar Informasi Publik yang Wajib Disediakan',
        'Hasil Monitoring dan Evaluasi',
        'Laporan Pengaduan Masyarakat',
        'Pedoman Teknis Administrasi Perkara',
    ];

    /**
     * Sub kategori berdasarkan kategori.
     *
     * @var array<string, array<int, string>>
     */
    private const SUB_KATEGORI = [
        'berkala' => ['Laporan Tahunan', 'Laporan Keuangan', 'Laporan Kinerja', 'Statistik Perkara'],
        'serta_merta' => ['Pengumuman', 'Informasi Darurat', 'Peringatan Publik'],
        'setiap_saat' => ['SOP', 'Struktur Organisasi', 'Profil', 'Regulasi', 'Prosedur'],
    ];

    /**
     * Definisi state default model.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $kategori = fake()->randomElement(['berkala', 'serta_merta', 'setiap_saat']);
        $tahun = fake()->numberBetween(2020, 2025);

        return [
            'judul' => fake()->randomElement(self::JUDUL).' '.$tahun,
            'kategori' => $kategori,
            'sub_kategori' => fake()->randomElement(self::SUB_KATEGORI[$kategori]),
            'deskripsi' => fake()->randomElement([
                'Dokumen ini berisi informasi mengenai kinerja dan capaian lembaga selama periode berjalan.',
                'Laporan yang memuat data statistik dan analisis perkara yang ditangani.',
                'Informasi publik yang wajib disediakan dan diumumkan secara berkala.',
                'Dokumen prosedur operasional standar untuk pelayanan publik.',
                'Data dan informasi yang tersedia untuk diakses oleh masyarakat.',
            ]),
            'file_path' => 'uploads/informasi_publik/'.fake()->sha256().'.pdf',
            'tahun' => $tahun,
            'nomor_perkara' => null,
            'is_published' => true,
            'published_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }

    /**
     * State: informasi publik yang belum dipublikasikan.
     */
    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => false,
            'published_at' => null,
        ]);
    }

    /**
     * State: informasi dengan kategori berkala.
     */
    public function berkala(): static
    {
        return $this->state(fn (array $attributes) => [
            'kategori' => 'berkala',
            'sub_kategori' => fake()->randomElement(self::SUB_KATEGORI['berkala']),
        ]);
    }

    /**
     * State: informasi dengan kategori serta merta.
     */
    public function sertaMerta(): static
    {
        return $this->state(fn (array $attributes) => [
            'kategori' => 'serta_merta',
            'sub_kategori' => fake()->randomElement(self::SUB_KATEGORI['serta_merta']),
        ]);
    }

    /**
     * State: informasi dengan kategori setiap saat.
     */
    public function setiapSaat(): static
    {
        return $this->state(fn (array $attributes) => [
            'kategori' => 'setiap_saat',
            'sub_kategori' => fake()->randomElement(self::SUB_KATEGORI['setiap_saat']),
        ]);
    }
}
