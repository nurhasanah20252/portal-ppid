<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FaqSeeder extends Seeder
{
    /**
     * Seed 12 FAQ terkait layanan PPID dalam Bahasa Indonesia.
     */
    public function run(): void
    {
        $faqs = [
            [
                'pertanyaan' => 'Apa itu PPID Pengadilan Agama Penajam?',
                'jawaban' => 'PPID (Pejabat Pengelola Informasi dan Dokumentasi) adalah pejabat yang bertanggung jawab di bidang penyimpanan, pendokumentasian, penyediaan, dan/atau pelayanan informasi di Pengadilan Agama Penajam sesuai Undang-Undang No. 14 Tahun 2008 tentang Keterbukaan Informasi Publik.',
                'urutan' => 1,
                'is_active' => true,
            ],
            [
                'pertanyaan' => 'Bagaimana cara mengajukan permohonan informasi publik?',
                'jawaban' => 'Pemohon dapat mengajukan permohonan informasi publik melalui portal PPID ini dengan mengisi formulir permohonan secara online. Pastikan melampirkan KTP dan mengisi data diri lengkap. Setelah permohonan diajukan, Anda akan menerima nomor tiket untuk memantau status permohonan.',
                'urutan' => 2,
                'is_active' => true,
            ],
            [
                'pertanyaan' => 'Apa saja jenis informasi yang dapat dimohonkan?',
                'jawaban' => 'Jenis informasi yang dapat dimohonkan meliputi: salinan putusan pengadilan, laporan kinerja pengadilan, dan informasi lainnya yang berkaitan dengan pelayanan Pengadilan Agama Penajam. Setiap jenis informasi memiliki persyaratan yang berbeda.',
                'urutan' => 3,
                'is_active' => true,
            ],
            [
                'pertanyaan' => 'Berapa lama waktu pemrosesan permohonan informasi?',
                'jawaban' => 'Sesuai UU KIP, permohonan informasi publik harus ditanggapi paling lambat 10 hari kerja sejak diterimanya permohonan. Jangka waktu ini dapat diperpanjang paling lama 7 hari kerja dengan pemberitahuan tertulis kepada pemohon.',
                'urutan' => 4,
                'is_active' => true,
            ],
            [
                'pertanyaan' => 'Bagaimana cara memantau status permohonan saya?',
                'jawaban' => 'Anda dapat memantau status permohonan menggunakan nomor tiket yang diberikan saat pengajuan. Masukkan nomor tiket pada halaman cek status di portal PPID. Status yang tersedia antara lain: baru, diproses, selesai, atau ditolak.',
                'urutan' => 5,
                'is_active' => true,
            ],
            [
                'pertanyaan' => 'Apa yang harus dilakukan jika permohonan ditolak?',
                'jawaban' => 'Jika permohonan informasi Anda ditolak, Anda berhak mengajukan keberatan kepada atasan PPID paling lambat 30 hari kerja sejak penolakan. Pengajuan keberatan dapat dilakukan melalui portal ini dengan menyertakan alasan keberatan yang jelas.',
                'urutan' => 6,
                'is_active' => true,
            ],
            [
                'pertanyaan' => 'Apa saja persyaratan dokumen untuk mengajukan permohonan?',
                'jawaban' => 'Persyaratan dokumen meliputi: foto/scan KTP yang masih berlaku (format JPG/PNG, maksimal 2MB), data identitas lengkap (NIK, nama, alamat, nomor HP, email), serta uraian informasi yang dimohonkan. Untuk permohonan salinan putusan, wajib menyertakan nomor perkara.',
                'urutan' => 7,
                'is_active' => true,
            ],
            [
                'pertanyaan' => 'Apakah ada biaya untuk mengajukan permohonan informasi?',
                'jawaban' => 'Pengajuan permohonan informasi publik tidak dikenakan biaya (gratis). Namun, untuk penggandaan atau pengiriman dokumen fisik, pemohon mungkin dikenakan biaya sesuai ketentuan yang berlaku.',
                'urutan' => 8,
                'is_active' => true,
            ],
            [
                'pertanyaan' => 'Informasi apa saja yang dikecualikan?',
                'jawaban' => 'Informasi yang dikecualikan antara lain: informasi yang dapat membahayakan negara, informasi terkait perlindungan usaha, informasi terkait kepentingan privasi individu, serta rahasia jabatan. Penolakan atas informasi yang dikecualikan akan disertai alasan tertulis.',
                'urutan' => 9,
                'is_active' => true,
            ],
            [
                'pertanyaan' => 'Bagaimana cara mengunduh dokumen informasi publik?',
                'jawaban' => 'Dokumen informasi publik yang tersedia dapat diunduh langsung melalui halaman Informasi Publik di portal ini. Pilih kategori informasi yang diinginkan, kemudian klik tombol unduh pada dokumen yang tersedia. Semua dokumen dalam format PDF.',
                'urutan' => 10,
                'is_active' => true,
            ],
            [
                'pertanyaan' => 'Siapa saja yang dapat mengajukan permohonan informasi?',
                'jawaban' => 'Setiap Warga Negara Indonesia dan/atau Badan Hukum Indonesia berhak mengajukan permohonan informasi publik sesuai UU No. 14 Tahun 2008. Pemohon wajib memiliki identitas yang valid (KTP/identitas lainnya).',
                'urutan' => 11,
                'is_active' => true,
            ],
            [
                'pertanyaan' => 'Bagaimana cara menghubungi PPID Pengadilan Agama Penajam?',
                'jawaban' => 'PPID Pengadilan Agama Penajam dapat dihubungi melalui portal ini, atau langsung mengunjungi kantor Pengadilan Agama Penajam pada jam kerja (Senin-Jumat, 08:00-16:00 WITA). Anda juga dapat mengirim email melalui alamat resmi yang tertera di website.',
                'urutan' => 12,
                'is_active' => true,
            ],
        ];

        $now = now();

        foreach ($faqs as $faq) {
            DB::table('faq')->insert(array_merge($faq, [
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }
    }
}
