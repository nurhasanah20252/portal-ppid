# Requirements Document

## Introduction

Dokumen ini mendefinisikan kebutuhan frontend untuk Portal PPID Pengadilan Agama Penajam. Frontend dibangun menggunakan Inertia.js v3 + React 19 dengan Tailwind CSS v4, mencakup halaman-halaman publik (Beranda, Profil PPID, Informasi Publik, Permohonan, Cek Status, Keberatan, Kontak, FAQ) dan dashboard admin (Login, Dashboard, Manajemen Permohonan, Kelola Informasi Publik, Keberatan, Laporan, Statistik). Semua halaman harus responsif (320px–1920px), aksesibel (WCAG 2.1 AA), dan memiliki performa optimal (page load ≤ 3 detik pada mobile 3G).

## Glossary

- **Portal**: Aplikasi web frontend Portal PPID yang dibangun dengan React via Inertia.js
- **Pemohon**: Pengguna publik yang mengajukan permohonan informasi melalui Portal
- **Admin_PPID**: Petugas PPID yang mengelola permohonan dan konten melalui dashboard admin
- **Tiket**: Nomor unik permohonan dengan format PPID-YYYYMMDD-XXXX
- **Inertia_Page**: Komponen React yang di-render oleh Inertia.js sebagai halaman penuh
- **Toast**: Komponen notifikasi yang muncul dari pojok kanan atas layar
- **Skeleton_Loading**: Placeholder animasi shimmer yang ditampilkan saat data sedang dimuat
- **Design_System**: Kumpulan warna, tipografi, dan komponen UI standar yang digunakan secara konsisten

## Requirements

### Requirement 1: Design System dan Komponen Shared

**User Story:** Sebagai developer, saya ingin memiliki design system dan komponen UI yang konsisten, sehingga seluruh halaman Portal memiliki tampilan dan perilaku yang seragam.

#### Acceptance Criteria

1. THE Portal SHALL menggunakan palet warna Primary (#1B5E20 hijau), Secondary (#FFC107 emas, #F57C00 orange, #6A1B9A ungu), dan Neutral (#FFFFFF, #F5F5F5, #9E9E9E, #212121) sesuai design system
2. THE Portal SHALL menggunakan font Poppins untuk heading dan Inter untuk body text
3. THE Portal SHALL menyediakan komponen Button dengan varian Primary (hijau), CTA (orange), dan Secondary (ungu outline)
4. WHEN pengguna melakukan hover pada tombol, THE Portal SHALL menerapkan efek scale 1.02 dengan transisi 200ms ease
5. THE Portal SHALL menyediakan komponen Card dengan background putih, border-radius 16px, shadow lembut, dan border-top 4px solid emas
6. THE Portal SHALL menyediakan komponen Input dengan border abu-abu, border-radius 8px, dan focus state berupa border hijau dengan shadow hijau tipis
7. THE Portal SHALL menyediakan komponen Toast yang muncul dari pojok kanan atas dengan animasi slide-in, hilang otomatis setelah 5 detik, dan memiliki varian sukses (hijau), error (orange), info (ungu), dan warning (emas)
8. THE Portal SHALL menyediakan komponen Skeleton_Loading dengan efek shimmer untuk placeholder saat data sedang dimuat
9. THE Portal SHALL menyediakan komponen Modal dengan overlay gelap dan animasi fade-in untuk konfirmasi aksi admin

### Requirement 2: Layout dan Navigasi

**User Story:** Sebagai pengunjung, saya ingin navigasi yang jelas dan konsisten di seluruh halaman, sehingga saya dapat berpindah antar halaman dengan mudah.

#### Acceptance Criteria

1. THE Portal SHALL menampilkan header dengan logo PPID PA Penajam dan menu navigasi berisi: Beranda, Profil, Info Publik, Permohonan, Status, Kontak
2. THE Portal SHALL menampilkan footer dengan background hijau tua (#1B5E20), berisi informasi copyright, tautan kebijakan privasi, dan kontak
3. WHEN lebar layar kurang dari 768px, THE Portal SHALL menampilkan hamburger menu yang membuka navigasi secara vertikal
4. THE Portal SHALL menyediakan tautan "Langsung ke konten utama" (skip-to-content) tersembunyi yang muncul saat menerima fokus keyboard
5. WHEN pengguna mengklik item menu navigasi, THE Portal SHALL menavigasi ke halaman tujuan menggunakan Inertia router tanpa full page reload

### Requirement 3: Halaman Beranda

**User Story:** Sebagai pengunjung, saya ingin melihat ringkasan informasi penting di halaman beranda, sehingga saya dapat langsung mengakses layanan utama PPID.

#### Acceptance Criteria

1. THE Portal SHALL menampilkan Hero Banner dengan background gradien hijau-ke-ungu, judul "Portal Keterbukaan Informasi Publik", subtitle "Pengadilan Agama Penajam", tombol CTA "Ajukan Permohonan" (orange), dan tombol "Cek Status" (outline putih)
2. THE Portal SHALL menampilkan section Statistik Cepat berisi 3 card yang menunjukkan jumlah permohonan bulan ini, jumlah sedang diproses, dan jumlah selesai bulan ini
3. THE Portal SHALL menampilkan section Informasi Terbaru berisi daftar pengumuman terkini dengan tautan ke detail
4. THE Portal SHALL menampilkan section Informasi Publik dengan tab Berkala, Serta Merta, dan Setiap Saat yang menampilkan daftar dokumen per kategori
5. THE Portal SHALL menampilkan section FAQ dengan daftar pertanyaan dan jawaban yang dapat di-expand
6. THE Portal SHALL menampilkan section Kontak dengan alamat, nomor telepon, email, dan jam layanan
7. WHEN data statistik sedang dimuat, THE Portal SHALL menampilkan Skeleton_Loading pada card statistik

### Requirement 4: Halaman Profil PPID

**User Story:** Sebagai pengunjung, saya ingin melihat profil PPID Pengadilan Agama Penajam, sehingga saya mengetahui struktur organisasi dan dasar hukum layanan PPID.

#### Acceptance Criteria

1. THE Portal SHALL menampilkan halaman Profil PPID berisi nama pejabat PPID, struktur organisasi, dasar hukum, dan tugas pokok PPID
2. THE Portal SHALL menampilkan informasi kontak PPID (email, telepon) di halaman profil

### Requirement 5: Halaman Daftar Informasi Publik

**User Story:** Sebagai pengunjung, saya ingin menelusuri daftar informasi publik berdasarkan kategori, sehingga saya dapat menemukan dan mengunduh dokumen yang saya butuhkan.

#### Acceptance Criteria

1. THE Portal SHALL menampilkan daftar informasi publik yang dapat difilter berdasarkan kategori (berkala, serta_merta, setiap_saat) dan tahun
2. WHEN pengguna memilih kategori atau tahun filter, THE Portal SHALL memperbarui daftar informasi tanpa full page reload
3. THE Portal SHALL menampilkan setiap item informasi dengan judul, kategori, tahun, dan tautan unduh file PDF
4. THE Portal SHALL menerapkan pagination pada daftar informasi publik
5. WHEN data informasi publik sedang dimuat, THE Portal SHALL menampilkan Skeleton_Loading pada area daftar

### Requirement 6: Form Permohonan Informasi Online

**User Story:** Sebagai pemohon, saya ingin mengisi formulir permohonan informasi secara online, sehingga saya tidak perlu datang ke kantor pengadilan.

#### Acceptance Criteria

1. THE Portal SHALL menampilkan form permohonan dengan field: NIK (16 digit), Nama Lengkap, Alamat, Kota, Provinsi, No. HP, Email, Upload KTP (max 2MB, jpg/png), Jenis Informasi (radio: salinan_putusan, laporan_kinerja, lainnya), Nomor Perkara (jika salinan_putusan), Tujuan Permohonan, dan Uraian Informasi
2. THE Portal SHALL menampilkan progress bar yang menunjukkan langkah pengisian form saat ini
3. WHEN pengguna meninggalkan field (blur), THE Portal SHALL melakukan validasi inline dan menampilkan pesan error di bawah field yang tidak valid dengan animasi shake
4. WHEN pengguna mengirim form dengan field wajib yang kosong atau tidak valid, THE Portal SHALL mencegah pengiriman dan menampilkan semua pesan error secara inline
5. WHEN form berhasil dikirim, THE Portal SHALL menampilkan Toast sukses berisi nomor Tiket yang diterima dan mengosongkan form
6. WHEN form sedang dikirim, THE Portal SHALL menampilkan spinner pada tombol submit dan mendisable semua input field
7. IF server mengembalikan error 429 (rate limit), THEN THE Portal SHALL menampilkan pesan "Anda telah mencapai batas pengajuan. Silakan coba lagi 1 jam kemudian atau hubungi petugas."
8. IF server mengembalikan error 422 (validasi), THEN THE Portal SHALL menampilkan pesan error per field sesuai response server
9. THE Portal SHALL menyediakan checkbox persetujuan yang wajib dicentang sebelum form dapat dikirim
10. WHEN pengguna memilih file KTP, THE Portal SHALL menampilkan preview thumbnail dari file yang dipilih

### Requirement 7: Halaman Cek Status Permohonan

**User Story:** Sebagai pemohon, saya ingin mengecek status permohonan saya menggunakan nomor tiket, sehingga saya mengetahui perkembangan permohonan tanpa harus menelepon.

#### Acceptance Criteria

1. THE Portal SHALL menampilkan input field untuk nomor Tiket dengan format placeholder "PPID-YYYYMMDD-XXXX" dan tombol "Cek Status"
2. WHEN pemohon memasukkan nomor Tiket dan mengklik tombol Cek Status, THE Portal SHALL mengirim request ke server dan menampilkan hasil status
3. WHEN status ditemukan, THE Portal SHALL menampilkan badge status (Baru/Diproses/Selesai/Ditolak), tanggal pengajuan, perkiraan selesai, catatan admin, dan timeline riwayat status
4. IF nomor Tiket tidak ditemukan (404), THEN THE Portal SHALL menampilkan pesan "Nomor tiket tidak ditemukan" dengan saran menghubungi helpdesk di area hasil pencarian
5. WHEN pencarian status sedang diproses, THE Portal SHALL menampilkan indikator loading pada area hasil

### Requirement 8: Form Keberatan Online

**User Story:** Sebagai pemohon, saya ingin mengajukan keberatan jika permohonan saya ditolak, sehingga saya dapat menempuh jalur resmi untuk mendapatkan informasi yang saya butuhkan.

#### Acceptance Criteria

1. THE Portal SHALL menampilkan form keberatan dengan field: Nomor Tiket permohonan sebelumnya, Nama Pemohon, dan Alasan Keberatan
2. WHEN pengguna mengirim form keberatan dengan data valid, THE Portal SHALL menampilkan Toast sukses berisi konfirmasi keberatan telah direkam
3. WHEN pengguna mengirim form keberatan dengan field wajib kosong, THE Portal SHALL mencegah pengiriman dan menampilkan pesan error inline
4. IF server mengembalikan error pada submit keberatan, THEN THE Portal SHALL menampilkan Toast error dengan pesan yang sesuai

### Requirement 9: Halaman Kontak dan Helpdesk

**User Story:** Sebagai pengunjung, saya ingin melihat informasi kontak dan lokasi kantor PPID, sehingga saya dapat menghubungi atau mengunjungi kantor jika diperlukan.

#### Acceptance Criteria

1. THE Portal SHALL menampilkan informasi kontak lengkap: alamat kantor, nomor telepon, email, dan jam layanan
2. THE Portal SHALL menampilkan embedded Google Maps yang menunjukkan lokasi kantor Pengadilan Agama Penajam

### Requirement 10: Halaman FAQ

**User Story:** Sebagai pengunjung, saya ingin membaca pertanyaan yang sering diajukan, sehingga saya mendapat jawaban cepat tanpa harus menghubungi petugas.

#### Acceptance Criteria

1. THE Portal SHALL menampilkan daftar FAQ dalam format accordion (klik untuk expand/collapse jawaban)
2. WHEN pengguna mengklik pertanyaan, THE Portal SHALL menampilkan jawaban dengan animasi expand yang halus
3. WHEN data FAQ sedang dimuat, THE Portal SHALL menampilkan Skeleton_Loading

### Requirement 11: Halaman Login Admin

**User Story:** Sebagai Admin_PPID, saya ingin login ke dashboard admin dengan aman, sehingga saya dapat mengelola permohonan dan konten.

#### Acceptance Criteria

1. THE Portal SHALL menampilkan form login dengan field email dan password
2. WHEN Admin_PPID mengirim credential valid, THE Portal SHALL menavigasi ke halaman dashboard admin
3. IF credential tidak valid (401), THEN THE Portal SHALL menampilkan pesan error "Email atau password salah" di halaman login
4. WHEN form login sedang diproses, THE Portal SHALL menampilkan spinner pada tombol login dan mendisable input

### Requirement 12: Dashboard Admin - Beranda dan Statistik

**User Story:** Sebagai Admin_PPID, saya ingin melihat ringkasan statistik permohonan di dashboard, sehingga saya dapat memantau kinerja layanan PPID.

#### Acceptance Criteria

1. THE Portal SHALL menampilkan layout dashboard admin dengan sidebar navigasi berwarna ungu berisi menu: Beranda, Semua Permohonan, Proses, Tolak, Arsip, Kelola Konten, Laporan, Setelan
2. THE Portal SHALL menampilkan card statistik berisi: total permohonan bulan ini, sedang diproses, selesai bulan ini, dan rata-rata waktu respon
3. THE Portal SHALL menampilkan grafik bar chart permohonan per bulan menggunakan library chart
4. WHEN data statistik sedang dimuat, THE Portal SHALL menampilkan Skeleton_Loading pada card dan chart

### Requirement 13: Dashboard Admin - Manajemen Permohonan

**User Story:** Sebagai Admin_PPID, saya ingin melihat dan mengelola daftar permohonan yang masuk, sehingga saya dapat memproses permohonan secara efisien.

#### Acceptance Criteria

1. THE Portal SHALL menampilkan tabel daftar permohonan dengan kolom: Tiket, Pemohon, Jenis Informasi, Status, dan Aksi
2. THE Portal SHALL menyediakan filter status (baru, diproses, selesai, ditolak) pada daftar permohonan
3. WHEN Admin_PPID mengklik tombol "Proses" pada baris permohonan, THE Portal SHALL menampilkan Modal detail berisi data pemohon lengkap, uraian permohonan, file KTP, dropdown ubah status, textarea balasan, dan upload dokumen balasan
4. WHEN Admin_PPID mengubah status dan mengklik "Kirim & Update Status", THE Portal SHALL mengirim update ke server dan memperbarui tabel tanpa full page reload
5. THE Portal SHALL menampilkan konfirmasi Modal sebelum Admin_PPID mengubah status permohonan
6. THE Portal SHALL menerapkan pagination pada tabel permohonan

### Requirement 14: Dashboard Admin - Kelola Informasi Publik

**User Story:** Sebagai Admin_PPID, saya ingin menambah, mengedit, dan menghapus dokumen informasi publik, sehingga masyarakat selalu mendapat informasi terkini.

#### Acceptance Criteria

1. THE Portal SHALL menampilkan tabel daftar informasi publik dengan kolom: Judul, Kategori, Tahun, Status Publikasi, dan Aksi (Edit, Hapus)
2. WHEN Admin_PPID mengklik tombol "Tambah Informasi", THE Portal SHALL menampilkan form berisi field: Judul, Kategori, Sub-kategori, Deskripsi, Upload File PDF, Tahun, dan Nomor Perkara (opsional)
3. WHEN Admin_PPID mengklik tombol "Hapus", THE Portal SHALL menampilkan konfirmasi Modal sebelum menghapus data
4. WHEN operasi CRUD berhasil, THE Portal SHALL menampilkan Toast sukses dan memperbarui tabel

### Requirement 15: Dashboard Admin - Laporan dan Export

**User Story:** Sebagai Admin_PPID, saya ingin mengexport data permohonan ke Excel, sehingga saya dapat membuat laporan bulanan untuk pimpinan.

#### Acceptance Criteria

1. THE Portal SHALL menyediakan halaman laporan dengan filter bulan/tahun dan tombol "Export ke Excel"
2. WHEN Admin_PPID mengklik tombol "Export ke Excel", THE Portal SHALL memicu download file Excel berisi data permohonan sesuai filter

### Requirement 16: Responsivitas

**User Story:** Sebagai pengunjung yang mengakses dari perangkat mobile, saya ingin tampilan Portal yang optimal di semua ukuran layar, sehingga saya dapat menggunakan semua fitur dengan nyaman.

#### Acceptance Criteria

1. THE Portal SHALL menampilkan layout yang optimal pada rentang lebar layar 320px hingga 1920px
2. WHEN lebar layar kurang dari 768px, THE Portal SHALL menumpuk elemen secara vertikal dan membuat tombol CTA lebar penuh
3. WHEN lebar layar kurang dari 768px, THE Portal SHALL menampilkan tabel admin dalam format card yang dapat di-scroll

### Requirement 17: Aksesibilitas

**User Story:** Sebagai pengguna dengan disabilitas, saya ingin Portal yang aksesibel, sehingga saya dapat menggunakan semua fitur dengan bantuan screen reader atau keyboard.

#### Acceptance Criteria

1. THE Portal SHALL menampilkan focus outline 2px berwarna emas (#FFC107) pada semua elemen interaktif saat menerima fokus keyboard
2. THE Portal SHALL menyediakan atribut aria-label pada tombol ikon yang tidak memiliki teks
3. THE Portal SHALL menghubungkan pesan error pada form dengan input terkait menggunakan atribut aria-describedby
4. THE Portal SHALL menggunakan role="status" dan aria-live="polite" pada komponen Toast
5. THE Portal SHALL memastikan rasio kontras teks minimal 4.5:1 sesuai WCAG 2.1 AA

### Requirement 18: Event Tracking

**User Story:** Sebagai pengelola Portal, saya ingin melacak interaksi pengguna, sehingga saya dapat menganalisis penggunaan dan meningkatkan layanan.

#### Acceptance Criteria

1. WHEN pengguna mengklik item navigasi, THE Portal SHALL mengirim event tracking dengan kategori "navigation" dan label nama menu
2. WHEN pengguna mengklik tombol CTA "Ajukan Permohonan" atau "Cek Status", THE Portal SHALL mengirim event tracking dengan kategori "cta_button" dan lokasi tombol
3. WHEN pengguna berhasil atau gagal mengirim form permohonan, THE Portal SHALL mengirim event tracking dengan kategori "form_interaction" dan status hasil
4. WHEN pengguna mengunduh dokumen informasi publik, THE Portal SHALL mengirim event tracking dengan kategori "download" dan tipe dokumen
5. WHEN terjadi error API, THE Portal SHALL mengirim event tracking dengan kategori "error" dan endpoint yang gagal
