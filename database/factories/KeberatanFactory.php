<?php

namespace Database\Factories;

use App\Models\Keberatan;
use App\Models\Permohonan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Keberatan>
 */
class KeberatanFactory extends Factory
{
    protected $model = Keberatan::class;

    /**
     * Daftar alasan keberatan yang realistis.
     *
     * @var array<int, string>
     */
    private const ALASAN = [
        'Informasi yang diminta merupakan hak publik dan seharusnya dapat diakses oleh masyarakat umum',
        'Penolakan tidak disertai dasar hukum yang jelas dan memadai sesuai peraturan yang berlaku',
        'Alasan penolakan tidak sesuai dengan ketentuan UU Keterbukaan Informasi Publik',
        'Informasi yang diminta bukan termasuk informasi yang dikecualikan menurut undang-undang',
        'Badan publik tidak memberikan pertimbangan tertulis yang memadai atas penolakan permohonan',
    ];

    /**
     * Definisi state default model.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'permohonan_id' => Permohonan::factory()->ditolak(),
            'nama_pemohon' => $this->generateNamaPemohon(),
            'alasan' => fake()->randomElement(self::ALASAN),
            'status' => 'dikirim',
            'tanggapan_admin' => null,
            'resolved_at' => null,
        ];
    }

    /**
     * State: status dikirim (default).
     */
    public function dikirim(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'dikirim',
            'tanggapan_admin' => null,
            'resolved_at' => null,
        ]);
    }

    /**
     * State: status diproses.
     */
    public function diproses(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'diproses',
            'tanggapan_admin' => null,
            'resolved_at' => null,
        ]);
    }

    /**
     * State: status selesai.
     */
    public function selesai(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'selesai',
            'tanggapan_admin' => 'Keberatan telah ditinjau dan diputuskan bahwa informasi dapat diberikan kepada pemohon.',
            'resolved_at' => now('Asia/Makassar'),
        ]);
    }

    /**
     * Generate nama pemohon Indonesia (min 3 karakter).
     */
    private function generateNamaPemohon(): string
    {
        $namaDepan = fake()->randomElement(['Ahmad', 'Siti', 'Budi', 'Dewi', 'Andi', 'Rizki', 'Putri', 'Agus']);
        $namaBelakang = fake()->randomElement(['Pratama', 'Santoso', 'Wijaya', 'Hidayat', 'Kusuma', 'Saputra']);

        return "{$namaDepan} {$namaBelakang}";
    }
}
