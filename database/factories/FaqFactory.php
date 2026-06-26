<?php

namespace Database\Factories;

use App\Models\Faq;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Faq>
 */
class FaqFactory extends Factory
{
    protected $model = Faq::class;

    /**
     * Daftar FAQ realistis dalam Bahasa Indonesia.
     *
     * @var array<int, array{pertanyaan: string, jawaban: string}>
     */
    private const FAQ_DATA = [
        [
            'pertanyaan' => 'Bagaimana cara mengajukan permohonan informasi publik?',
            'jawaban' => 'Anda dapat mengajukan permohonan melalui portal ini dengan mengisi formulir permohonan informasi yang tersedia.',
        ],
        [
            'pertanyaan' => 'Berapa lama waktu yang dibutuhkan untuk memproses permohonan?',
            'jawaban' => 'Permohonan informasi akan diproses dalam waktu maksimal 10 hari kerja setelah permohonan diterima.',
        ],
        [
            'pertanyaan' => 'Apa saja dokumen yang diperlukan untuk mengajukan permohonan?',
            'jawaban' => 'Anda perlu menyiapkan KTP atau identitas diri lainnya serta mengisi formulir permohonan dengan lengkap.',
        ],
        [
            'pertanyaan' => 'Bagaimana cara mengecek status permohonan saya?',
            'jawaban' => 'Gunakan nomor tiket yang diberikan saat pengajuan untuk mengecek status permohonan melalui halaman cek status.',
        ],
        [
            'pertanyaan' => 'Apa yang dimaksud dengan informasi yang dikecualikan?',
            'jawaban' => 'Informasi yang dikecualikan adalah informasi yang tidak dapat diberikan karena dilindungi oleh undang-undang.',
        ],
        [
            'pertanyaan' => 'Bagaimana jika permohonan saya ditolak?',
            'jawaban' => 'Anda dapat mengajukan keberatan dalam waktu 30 hari kerja setelah menerima pemberitahuan penolakan.',
        ],
        [
            'pertanyaan' => 'Apakah ada biaya untuk mengajukan permohonan informasi?',
            'jawaban' => 'Tidak ada biaya untuk mengajukan permohonan informasi. Namun biaya penggandaan dokumen ditanggung pemohon.',
        ],
        [
            'pertanyaan' => 'Siapa yang berhak mengajukan permohonan informasi publik?',
            'jawaban' => 'Setiap warga negara Indonesia berhak mengajukan permohonan informasi publik sesuai UU Keterbukaan Informasi Publik.',
        ],
        [
            'pertanyaan' => 'Apa itu PPID dan apa fungsinya?',
            'jawaban' => 'PPID adalah Pejabat Pengelola Informasi dan Dokumentasi yang bertanggung jawab mengelola layanan informasi publik.',
        ],
        [
            'pertanyaan' => 'Bagaimana cara menghubungi petugas PPID?',
            'jawaban' => 'Anda dapat menghubungi petugas PPID melalui email resmi atau datang langsung ke kantor layanan informasi.',
        ],
        [
            'pertanyaan' => 'Apakah informasi yang diberikan bersifat resmi?',
            'jawaban' => 'Ya, semua informasi yang diberikan melalui portal ini bersifat resmi dan dapat dipertanggungjawabkan.',
        ],
        [
            'pertanyaan' => 'Bagaimana prosedur pengajuan keberatan atas penolakan?',
            'jawaban' => 'Keberatan diajukan secara tertulis melalui portal ini dengan menyebutkan alasan keberatan yang jelas dan lengkap.',
        ],
    ];

    /**
     * Definisi state default model.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $faq = fake()->randomElement(self::FAQ_DATA);

        return [
            'pertanyaan' => $faq['pertanyaan'],
            'jawaban' => $faq['jawaban'],
            'urutan' => fake()->numberBetween(1, 100),
            'is_active' => true,
        ];
    }

    /**
     * State: FAQ tidak aktif.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
