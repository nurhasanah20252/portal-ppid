<?php

namespace Database\Factories;

use App\Models\Permohonan;
use App\Models\StatusLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StatusLog>
 */
class StatusLogFactory extends Factory
{
    protected $model = StatusLog::class;

    /**
     * Status yang valid untuk permohonan.
     *
     * @var array<int, string>
     */
    private const STATUS_VALID = ['baru', 'diproses', 'selesai', 'ditolak'];

    /**
     * Definisi state default model.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'permohonan_id' => Permohonan::factory(),
            'status_lama' => null,
            'status_baru' => 'baru',
            'catatan' => null,
            'created_by' => User::factory(),
        ];
    }

    /**
     * State: log transisi dari baru ke diproses.
     */
    public function baruKeDiproses(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_lama' => 'baru',
            'status_baru' => 'diproses',
            'catatan' => 'Permohonan sedang ditinjau oleh petugas PPID.',
        ]);
    }

    /**
     * State: log transisi dari diproses ke selesai.
     */
    public function diprosesKeSelesai(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_lama' => 'diproses',
            'status_baru' => 'selesai',
            'catatan' => 'Dokumen telah disiapkan dan dapat diunduh oleh pemohon.',
        ]);
    }

    /**
     * State: log transisi dari diproses ke ditolak.
     */
    public function diprosesKeDitolak(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_lama' => 'diproses',
            'status_baru' => 'ditolak',
            'catatan' => 'Informasi yang diminta termasuk kategori yang dikecualikan.',
        ]);
    }

    /**
     * State: log pembuatan permohonan baru (status_lama null).
     */
    public function pembuatanBaru(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_lama' => null,
            'status_baru' => 'baru',
            'catatan' => null,
        ]);
    }
}
