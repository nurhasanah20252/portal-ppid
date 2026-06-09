Berikut adalah **Product Requirements Document (PRD)** untuk pengembangan **Website Portal PPID Pengadilan Agama Penajam**, berdasarkan diskusi fitur sebelumnya.

---

# PRODUCT REQUIREMENTS DOCUMENT  
**Project:** Website Portal PPID Pengadilan Agama Penajam  
**Versi:** 1.0  
**Tanggal:** 9 Juni 2026  
**Dibuat oleh:** PRD Planner  
**Status:** Draft

---

## 1. Ringkasan Eksekutif

Website Portal PPID (Pejabat Pengelola Informasi dan Dokumentasi) Pengadilan Agama Penajam bertujuan untuk memenuhi kewajiban keterbukaan informasi publik sesuai UU No. 14/2008 serta standar peradilan. Portal ini menyediakan layanan permohonan informasi online, pelacakan status, keberatan, dan arsip informasi publik. Target pengguna adalah masyarakat pencari keadilan, peneliti, advokat, dan publik umum.

---

## 2. Tujuan Produk (OKRs)

| Tujuan | Key Result |
|--------|------------|
| **Transparansi informasi** | 100% informasi berkala (laporan kinerja, putusan) tersedia online dalam 3 bulan setelah launching. |
| **Kemudahan akses** | 80% permohonan informasi diajukan melalui kanal online (vs offline) dalam 6 bulan. |
| **Kecepatan layanan** | Rata-rata waktu respon ≤ 2 hari kerja untuk permohonan informasi. |
| **Kepuasan pengguna** | Skor CSAT ≥ 4,5 dari 5 melalui survei on-page. |

---

## 3. Ruang Lingkup

### Di dalam cakupan (In-scope)
- Pengembangan front-end & back-end portal PPID.
- Fitur permohonan informasi + tracking tiket.
- Fitur keberatan informasi.
- Dashboard admin untuk PPID.
- Migrasi konten profil dan arsip awal (minimal 20 dokumen).
- Pelatihan internal untuk 3 admin PPID.

### Di luar cakupan (Out-of-scope)
- Integrasi dengan SIPP (Sistem Informasi Penelusuran Perkara) – akan dijadikan tahap 2.
- Aplikasi mobile native (cukup responsif web).
- Layanan pembayaran elektronik (saat ini hanya tarif manual).

---

## 4. Persona Pengguna

| Persona | Kebutuhan Utama |
|---------|----------------|
| **Ali (warga Penajam)** | Ingin mengajukan salinan putusan cerai – cepat, tidak perlu datang ke pengadilan. |
| **Dewi (jurnalis)** | Mengakses laporan kinerja tahunan dan statistik perkara. |
| **Bambang (advokat)** | Melacak status permohonan informasi klien, mengajukan keberatan jika ditolak. |
| **Ibu Siti (difabel netra)** | Membaca prosedur layanan dengan screen reader. |
| **Petugas PPID (Admin)** | Menerima notifikasi, mengupdate status, menambah arsip, mencetak laporan. |

---

## 5. Fitur Produk (Prioritas)

### P0 – Wajib ada (MVP)

| ID | Fitur | Deskripsi |
|----|-------|------------|
| F-01 | Beranda | Menampilkan profil singkat, statistik permohonan, dan 3 berita terbaru. |
| F-02 | Profil PPID | Struktur organisasi, dasar hukum, tugas pejabat PPID. |
| F-03 | Daftar Informasi Publik | Klasifikasi berkala, serta merta, setiap saat. Masing-masing berisi daftar dokumen/downtload. |
| F-04 | Form Permohonan Online | Field: NIK, nama, alamat, email, nomor HP, tujuan, jenis informasi, upload KTP (opsional). Auto generate tiket. |
| F-05 | Cek Status Permohonan | Halaman input nomor tiket, menampilkan status (diterima/diproses/selesai/ditolak). |
| F-06 | Form Keberatan Online | Field: nomor tiket permohonan sebelumnya, alasan keberatan, data pelapor. |
| F-07 | Dashboard Admin | Manajemen permohonan (ubah status, balas pesan), kelola konten halaman, export data ke Excel, log aktivitas. |
| F-08 | Kontak & Helpdesk | Alamat, peta Google Maps, email, nomor telepon, jam layanan. |

### P1 – Pendukung (Tahap 2)

| ID | Fitur | Deskripsi |
|----|-------|------------|
| F-09 | Arsip Hasil Permohonan | Menampilkan dokumen/informasi yang pernah diberikan (anonim). Bisa dicari. |
| F-10 | FAQ | Minimal 12 pertanyaan umum tentang prosedur PPID. |
| F-11 | Pencarian Dokumen & Putusan | Search box untuk mencari putusan berdasarkan nomor perkara, tahun, atau kata kunci. |
| F-12 | Notifikasi Otomatis | Email ke pemohon saat status berubah (via SMTP). |
| F-13 | Laporan Tahunan PPID | Halaman statis berisi laporan keterbukaan informasi (diupdate per tahun). |

### P2 – Nice to have (Tahap 3)

| ID | Fitur | Deskripsi |
|----|-------|------------|
| F-14 | Aksesibilitas (WAI-ARIA) | Mode kontras tinggi, teks besar, label untuk screen reader. |
| F-15 | Statistik Real-time Dashboard | Grafik jumlah permohonan per bulan, waktu tanggap rata-rata. |
| F-16 | Multi-bahasa (Inggris) | Toggle Bahasa Indonesia/Inggris. |
| F-17 | Integrasi Media Sosial | Tombol share informasi ke FB, X, WA. |

---

## 6. Persyaratan Non-Fungsional

| Aspek | Persyaratan |
|-------|--------------|
| **Kinerja** | Waktu muat halaman ≤ 3 detik (mobile 3G). |
| **Keamanan** | SSL mandatory, hash password admin, proteksi CSRF di semua form. |
| **Ketersediaan** | Uptime ≥ 99,5% (hosting dengan SLA). |
| **Skalabilitas** | Mampu menampung 5.000 permohonan/tahun. |
| **Kepatuhan** | Sesuai Perma No. 1/2015 tentang PPID di pengadilan. |
| **Responsif** | Tampilan optimal di Chrome, Firefox, Safari (iOS/Android), ukuran layar 320px – 1920px. |
| **Backup** | Backup otomatis database & file setiap 24 jam. |

---

## 7. User Flow Sederhana (Contoh Permohonan)

```mermaid
flowchart LR
    A[Buka Portal] --> B[Klik "Permohonan Informasi"]
    B --> C[Isi formulir & upload KTP]
    C --> D[Terima nomor tiket via email]
    D --> E[Admin proses permohonan]
    E --> F{Status tersedia}
    F --> G[Pemohon cek status dengan tiket]
    G --> H[Dokumen diberikan via email / unduh]
```

---

## 8. Spesifikasi Teknis (Rekomendasi)

| Lapisan | Teknologi |
|---------|------------|
| **Front-end** | HTML5, Tailwind CSS, Alpine.js / Vue.js (untuk form dinamis) |
| **Back-end** | Laravel 11 (disarankan) atau WordPress + custom plugin PPID |
| **Database** | MySQL 8.0 |
| **Hosting** | Cloud server (misal: DigitalOcean, VPS minimal 2 vCPU, 4GB RAM, 50GB SSD) |
| **Email** | SMTP (Gmail Workspace / SendGrid) untuk notifikasi |
| **Keamanan** | Cloudflare (WAF), reCAPTCHA v3 |
| **Repo kode** | Git (GitHub/GitLab) + deployment pipeline sederhana |

---

## 9. Kriteria Penerimaan (Contoh untuk F-04 Form Permohonan)

| Test Case | Diharapkan |
|-----------|------------|
| NIK tidak diisi | Muncul error "NIK wajib diisi" |
| Email tidak valid | Error "Format email salah" |
| Upload KTP (max 2MB, jpg/png) | File tersimpan di server, nama file diacak |
| Submit sukses | Muncul notifikasi "Nomor tiket: PPID-20260609-001" dan email terkirim |
| Duplicate submit dalam 5 menit (data sama) | Dicegah dengan rate limiting |

---

## 10. Milestone & Timeline

| Tahap | Durasi | Output |
|-------|--------|--------|
| **Kick-off & desain UI** | 1 minggu | Wireframe disetujui, mockup Figma |
| **Tahap 1 (MVP)** | 4 minggu | F-01 s.d F-08 live di staging, testing internal |
| **Tahap 2** | 2 minggu | F-09 s.d F-13 live di production |
| **Tahap 3 (opsional)** | 2 minggu | F-14 s.d F-17 jika anggaran memadai |
| **UAT & pelatihan admin** | 1 minggu | Admin bisa mengelola, dokumen SOP |
| **Go-live** | 1 hari | Domain resmi ppid.pa-penajam.go.id aktif |

---

## 11. Risiko & Mitigasi

| Risiko | Dampak | Mitigasi |
|--------|--------|-----------|
| Admin PPID kurang melek IT | Permohonan tidak terproses | Pelatihan intensif + manual SOP video |
| Hosting down saat banyak permohonan | Layanan terganggu | Gunakan cloud hosting + auto-scaling |
| Perubahan regulasi PPID | Fitur tidak sesuai | Desain modular, mudah update halaman statis |
| Penolakan dari pimpinan pengadilan | Proyek batal | Libatkan ketua pengadilan sejak kick-off |

---

## 12. Lampiran (Dokumen pendukung yang perlu disiapkan)

- Daftar informasi berkala yang wajib diumumkan (sesuai PerkomInfo No. 1/2010)
- Template surat keputusan PPID PA Penajam
- Data awal: putusan terbaru (minimal 50 perkara dalam bentuk PDF anonim)
- Copy logo dan identitas visual Pengadilan Agama Penajam

---