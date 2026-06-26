# Requirements Document

## Introduction

Dokumen ini mendefinisikan kebutuhan backend untuk Portal PPID Pengadilan Agama Penajam. Backend dibangun menggunakan Laravel 13 (PHP 8.4) dengan MySQL, mencakup REST API untuk permohonan informasi, keberatan, informasi publik, FAQ, dashboard admin, autentikasi, notifikasi email async, file upload, dan rate limiting. Implementasi ini bersifat backend-only tanpa integrasi frontend/Inertia — semua endpoint mengembalikan response JSON. Timezone yang digunakan adalah Asia/Makassar (WITA).

## Glossary

- **API**: REST API backend Portal PPID yang mengembalikan response JSON
- **Pemohon**: Pengguna publik yang mengajukan permohonan informasi melalui API
- **Admin_PPID**: Petugas PPID yang terautentikasi untuk mengelola permohonan dan konten
- **Permohonan**: Pengajuan permintaan informasi publik dari pemohon ke PPID
- **Keberatan**: Pengajuan ketidaksetujuan pemohon terhadap penolakan permohonan
- **Tiket**: Nomor unik permohonan dengan format PPID-YYYYMMDD-XXXX
- **Informasi_Publik**: Dokumen publik yang dikategorikan dan dapat diunduh masyarakat
- **Status_Log**: Catatan riwayat setiap perubahan status permohonan
- **Queue_Worker**: Laravel queue yang memproses job secara async (notifikasi email)
- **Rate_Limiter**: Mekanisme pembatasan jumlah request per IP dalam periode waktu tertentu

## Requirements

### Requirement 1: Database Schema dan Model

**User Story:** Sebagai developer, saya ingin database schema yang terstruktur dengan migrations, models, factories, dan seeders, sehingga data Portal PPID tersimpan secara konsisten dan dapat di-seed untuk testing.

#### Acceptance Criteria

1. THE API SHALL menyediakan migration untuk tabel `permohonan` dengan kolom: id, tiket_no (unique, VARCHAR 30), nik (VARCHAR 16, indexed), nama_lengkap, alamat, kota, provinsi, no_hp, email, ktp_path (nullable), jenis_informasi (enum: salinan_putusan, laporan_kinerja, lainnya), nomor_perkara (nullable), tujuan, uraian_informasi, status (enum: baru, diproses, selesai, ditolak, default baru), catatan_admin (nullable), dokumen_balasan (nullable), alasan_tolak (nullable), processed_at (nullable datetime), completed_at (nullable datetime), created_at, updated_at
2. THE API SHALL menyediakan migration untuk tabel `keberatan` dengan kolom: id, permohonan_id (foreign key ke permohonan.id), nama_pemohon, alasan, status (enum: dikirim, diproses, selesai, default dikirim), tanggapan_admin (nullable), created_at, updated_at, resolved_at (nullable datetime)
3. THE API SHALL menyediakan migration untuk tabel `informasi_publik` dengan kolom: id, judul, kategori (enum: berkala, serta_merta, setiap_saat), sub_kategori, deskripsi, file_path, tahun (year), nomor_perkara (nullable), is_published (boolean, default true), published_at (datetime), created_at, updated_at
4. THE API SHALL menyediakan migration untuk tabel `status_log` dengan kolom: id, permohonan_id (foreign key ke permohonan.id), status_lama, status_baru, catatan (nullable), created_by (foreign key ke users.id), created_at
5. THE API SHALL menyediakan migration untuk tabel `faq` dengan kolom: id, pertanyaan, jawaban, urutan (integer, default 0), is_active (boolean, default true), created_at, updated_at
6. THE API SHALL menyediakan migration yang menambahkan kolom `role` (enum: super_admin, ppid_staff, default ppid_staff) dan `last_login_at` (nullable datetime) pada tabel `users`
7. THE API SHALL menyediakan Eloquent model untuk setiap tabel dengan relasi yang benar: Permohonan hasMany StatusLog, Permohonan hasOne Keberatan, StatusLog belongsTo Permohonan, StatusLog belongsTo User, Keberatan belongsTo Permohonan
8. THE API SHALL menyediakan factory untuk setiap model dengan data faker yang realistis sesuai konteks Indonesia (NIK 16 digit, format nomor HP, nama Indonesia)
9. THE API SHALL menyediakan seeder yang mengisi data awal: 2 user admin (super_admin dan ppid_staff), 20 informasi publik, 12 FAQ, dan 50 permohonan dengan berbagai status

### Requirement 2: Autentikasi Admin

**User Story:** Sebagai Admin_PPID, saya ingin login ke sistem menggunakan email dan password, sehingga saya dapat mengakses endpoint admin yang terproteksi.

#### Acceptance Criteria

1. WHEN Admin_PPID mengirim POST request ke `/api/v1/auth/login` dengan email dan password yang valid, THE API SHALL mengembalikan response 200 berisi token autentikasi dan data user (name, email, role)
2. IF Admin_PPID mengirim credential yang tidak valid, THEN THE API SHALL mengembalikan response 401 dengan pesan "Email atau password salah". IF terjadi internal error selama validasi credential, THEN THE API SHALL mengembalikan response 500 dan memreservasi 401 hanya untuk credential yang definitif salah
3. WHEN Admin_PPID mengirim POST request ke `/api/v1/auth/logout` dengan Bearer token yang valid, THE API SHALL menghapus token dan mengembalikan response 200. IF penghapusan token gagal di sisi server, THEN THE API SHALL mengembalikan response 500 dengan pesan error
4. THE API SHALL memproteksi semua endpoint dengan prefix `/api/v1/admin` menggunakan middleware autentikasi yang memvalidasi Bearer token
5. WHEN Admin_PPID berhasil login, THE API SHALL memperbarui kolom `last_login_at` pada record user
6. IF token yang dikirim sudah expired atau tidak valid, THEN THE API SHALL mengembalikan response 401 dengan pesan "Unauthenticated"

### Requirement 3: Submit Permohonan Informasi

**User Story:** Sebagai pemohon, saya ingin mengirim permohonan informasi melalui API, sehingga saya mendapat nomor tiket dan permohonan saya tercatat dalam sistem.

#### Acceptance Criteria

1. WHEN pemohon mengirim POST request ke `/api/v1/permohonan` dengan data valid, THE API SHALL membuat record permohonan baru dengan status "baru" dan mengembalikan response 201 berisi tiket_no, status, dan created_at
2. THE API SHALL menghasilkan tiket_no dengan format PPID-YYYYMMDD-XXXX di mana YYYY adalah tahun (dibatasi rentang 2000-2099), MM bulan, DD tanggal (timezone Asia/Makassar), dan XXXX adalah nomor urut 4 digit dimulai dari 0001 per hari
3. THE API SHALL memvalidasi field wajib: nik (16 digit angka), nama_lengkap (min 3 karakter), alamat, kota, provinsi, no_hp (10-15 digit), email (format email valid), jenis_informasi (salah satu: salinan_putusan, laporan_kinerja, lainnya), tujuan, uraian_informasi
4. WHEN jenis_informasi bernilai "salinan_putusan", THE API SHALL memvalidasi field nomor_perkara sebagai field wajib
5. IF validasi gagal, THEN THE API SHALL mengembalikan response 422 dengan detail error per field dalam format JSON
6. THE API SHALL membuat record status_log dengan status_lama null dan status_baru "baru" setiap kali permohonan baru dibuat
7. WHEN permohonan berhasil dibuat, THE API SHALL mendispatch job email notifikasi ke queue berisi konfirmasi dan nomor tiket ke email pemohon

### Requirement 4: Upload File KTP

**User Story:** Sebagai pemohon, saya ingin mengunggah foto KTP bersama permohonan, sehingga identitas saya dapat diverifikasi oleh petugas PPID.

#### Acceptance Criteria

1. WHEN pemohon menyertakan file KTP pada submit permohonan, THE API SHALL menyimpan file ke direktori `storage/app/uploads/ktp` dengan nama file yang di-hash (tidak menggunakan nama asli)
2. THE API SHALL memvalidasi file KTP: ukuran maksimal 2MB, format yang diterima hanya jpg, jpeg, dan png
3. IF file KTP melebihi 2MB, THEN THE API SHALL mengembalikan response 422 dengan pesan "Ukuran file KTP maksimal 2MB"
4. IF format file KTP bukan jpg, jpeg, atau png, THEN THE API SHALL mengembalikan response 422 dengan pesan "Format file KTP harus JPG atau PNG"
5. THE API SHALL menyimpan path relatif file KTP pada kolom `ktp_path` di record permohonan

### Requirement 5: Cek Status Permohonan

**User Story:** Sebagai pemohon, saya ingin mengecek status permohonan menggunakan nomor tiket, sehingga saya mengetahui perkembangan permohonan tanpa harus datang ke kantor.

#### Acceptance Criteria

1. WHEN pemohon mengirim GET request ke `/api/v1/permohonan/{tiket_no}`, THE API SHALL mengembalikan response 200 berisi data status permohonan: tiket_no, status, created_at, processed_at, completed_at, catatan_admin, dokumen_balasan_url, dan riwayat status
2. THE API SHALL menyertakan array riwayat status yang berisi setiap perubahan status (status, created_at, catatan) diurutkan dari yang paling lama
3. WHEN permohonan berstatus "selesai" dan memiliki dokumen balasan, THE API SHALL menyertakan URL download dokumen balasan yang valid. IF URL dokumen balasan invalid untuk permohonan berstatus "selesai", THEN THE API SHALL mengembalikan response 500
4. IF tiket_no tidak ditemukan, THEN THE API SHALL mengembalikan response 404 dengan pesan "Tiket tidak ditemukan"

### Requirement 6: Rate Limiting Permohonan

**User Story:** Sebagai pengelola sistem, saya ingin membatasi jumlah permohonan per IP address, sehingga sistem terlindungi dari penyalahgunaan dan spam.

#### Acceptance Criteria

1. THE API SHALL membatasi endpoint POST `/api/v1/permohonan` maksimal 3 request per jam per IP address
2. IF pemohon melebihi batas rate limit, THEN THE API SHALL mengembalikan response 429 dengan pesan "Terlalu banyak permintaan. Coba lagi dalam 1 jam."
3. THE API SHALL menyertakan header `X-RateLimit-Limit`, `X-RateLimit-Remaining`, dan `Retry-After` pada response endpoint permohonan

### Requirement 7: Submit Keberatan

**User Story:** Sebagai pemohon, saya ingin mengajukan keberatan jika permohonan saya ditolak, sehingga saya dapat menempuh jalur resmi untuk mendapatkan informasi yang diminta.

#### Acceptance Criteria

1. WHEN pemohon mengirim POST request ke `/api/v1/keberatan` dengan tiket_no permohonan yang berstatus "ditolak", THE API SHALL membuat record keberatan dan mengembalikan response 201 dengan pesan konfirmasi
2. THE API SHALL memvalidasi field wajib: permohonan_tiket (tiket_no yang valid dan ada di database), nama_pemohon (min 3 karakter), alasan (min 10 karakter)
3. IF permohonan_tiket tidak ditemukan di database, THEN THE API SHALL mengembalikan response 404 dengan pesan "Tiket permohonan tidak ditemukan"
4. IF permohonan yang dirujuk tidak berstatus "ditolak", THEN THE API SHALL mengembalikan response 422 dengan pesan "Keberatan hanya dapat diajukan untuk permohonan yang ditolak"
5. IF keberatan sudah pernah diajukan untuk permohonan yang sama (dicek hanya saat POST request ke endpoint keberatan), THEN THE API SHALL mengembalikan response 422 dengan pesan "Keberatan sudah pernah diajukan untuk permohonan ini"
6. WHEN keberatan berhasil dibuat, THE API SHALL mendispatch job email notifikasi ke queue untuk menginformasikan admin tentang keberatan baru

### Requirement 8: Daftar Informasi Publik

**User Story:** Sebagai pengunjung, saya ingin melihat daftar informasi publik yang tersedia, sehingga saya dapat menemukan dan mengunduh dokumen yang saya butuhkan.

#### Acceptance Criteria

1. WHEN pengunjung mengirim GET request ke `/api/v1/informasi-publik`, THE API SHALL mengembalikan response 200 berisi daftar informasi publik yang berstatus published dengan pagination
2. THE API SHALL mendukung query parameter filter: kategori (berkala, serta_merta, setiap_saat), tahun, dan pencarian judul (search)
3. THE API SHALL mengembalikan setiap item informasi dengan field: id, judul, kategori, sub_kategori, tahun, deskripsi, file_url, dan published_at
4. THE API SHALL menerapkan pagination dengan default 10 item per halaman dan mendukung parameter page dan per_page (maksimal 50)
5. THE API SHALL hanya mengembalikan informasi publik yang memiliki is_published bernilai true pada endpoint publik, mencegah item yang unpublished dari disertakan dalam response API publik

### Requirement 9: Download File Informasi Publik

**User Story:** Sebagai pengunjung, saya ingin mengunduh file dokumen informasi publik, sehingga saya dapat membaca dokumen tersebut secara offline.

#### Acceptance Criteria

1. WHEN pengunjung mengirim GET request ke `/api/v1/informasi-publik/{id}/download`, THE API SHALL mengembalikan response file download dengan Content-Type yang sesuai (application/pdf)
2. IF informasi publik dengan id tersebut tidak ditemukan atau tidak published, THEN THE API SHALL mengembalikan response 404
3. IF file fisik tidak ditemukan di storage, THEN THE API SHALL mengembalikan response 404 dengan pesan "File tidak ditemukan"

### Requirement 10: FAQ Publik

**User Story:** Sebagai pengunjung, saya ingin melihat daftar FAQ, sehingga saya mendapat jawaban cepat tanpa harus menghubungi petugas.

#### Acceptance Criteria

1. WHEN pengunjung mengirim GET request ke `/api/v1/faq`, THE API SHALL mengembalikan response 200 berisi daftar FAQ yang berstatus aktif (is_active = true) diurutkan berdasarkan kolom urutan secara ascending
2. THE API SHALL mengembalikan setiap FAQ dengan field: id, pertanyaan, dan jawaban

### Requirement 11: Admin - Manajemen Permohonan

**User Story:** Sebagai Admin_PPID, saya ingin melihat dan memfilter daftar permohonan yang masuk, sehingga saya dapat memprosesnya secara efisien.

#### Acceptance Criteria

1. WHEN Admin_PPID mengirim GET request ke `/api/v1/admin/permohonan`, THE API SHALL mengembalikan response 200 berisi daftar semua permohonan dengan pagination, diurutkan dari yang terbaru
2. THE API SHALL mendukung query parameter filter: status (baru, diproses, selesai, ditolak), jenis_informasi, tanggal_mulai, tanggal_akhir, dan pencarian (nama, tiket_no, nik)
3. WHEN Admin_PPID mengirim GET request ke `/api/v1/admin/permohonan/{tiket_no}`, THE API SHALL mengembalikan response 200 berisi detail lengkap permohonan termasuk data pemohon, riwayat status, dan URL file KTP

### Requirement 12: Admin - Update Status Permohonan

**User Story:** Sebagai Admin_PPID, saya ingin mengubah status permohonan dan menambahkan catatan, sehingga pemohon mengetahui perkembangan permohonannya.

#### Acceptance Criteria

1. WHEN Admin_PPID mengirim PUT request ke `/api/v1/admin/permohonan/{tiket_no}/status` dengan status baru yang valid, THE API SHALL memperbarui status permohonan dan membuat record status_log. THE API SHALL mengizinkan self-transition (status sama) untuk keperluan menambah catatan atau memperbarui timestamp
2. THE API SHALL memvalidasi transisi status yang valid: baru hanya dapat berubah ke diproses, diproses dapat berubah ke selesai atau ditolak. Self-transition diizinkan untuk semua status
3. IF transisi status tidak valid (bukan self-transition dan bukan transisi yang diizinkan), THEN THE API SHALL mengembalikan response 422 dengan pesan "Transisi status tidak valid"
4. WHEN status berubah ke "diproses", THE API SHALL memperbarui kolom processed_at dengan waktu saat ini (timezone Asia/Makassar)
5. WHEN status berubah ke "selesai" atau "ditolak", THE API SHALL memperbarui kolom completed_at dengan waktu saat ini (timezone Asia/Makassar)
6. WHEN status berubah ke "ditolak", THE API SHALL memvalidasi field alasan_tolak sebagai field wajib (min 10 karakter)
7. WHEN status permohonan berhasil diperbarui, THE API SHALL mendispatch job email notifikasi ke queue untuk menginformasikan pemohon tentang perubahan status. IF dispatch job email gagal, THEN THE API SHALL melakukan rollback perubahan status dan mengembalikan response 500
8. THE API SHALL menyimpan ID Admin_PPID yang melakukan perubahan pada kolom created_by di status_log

### Requirement 13: Admin - Upload Dokumen Balasan

**User Story:** Sebagai Admin_PPID, saya ingin mengunggah dokumen balasan PDF untuk permohonan yang selesai, sehingga pemohon dapat mengunduh hasilnya.

#### Acceptance Criteria

1. WHEN Admin_PPID mengirim POST request ke `/api/v1/admin/permohonan/{tiket_no}/dokumen` dengan file PDF, THE API SHALL menyimpan file ke direktori `storage/app/uploads/dokumen` dan memperbarui kolom dokumen_balasan pada permohonan
2. THE API SHALL memvalidasi file: format wajib PDF, ukuran minimal 1KB dan maksimal kurang dari 10MB (file tepat 10MB ditolak)
3. IF format file bukan PDF, THEN THE API SHALL mengembalikan response 422 dengan pesan "Format file harus PDF"
4. IF ukuran file kurang dari 1KB, THEN THE API SHALL mengembalikan response 422 dengan pesan "File terlalu kecil, minimal 1KB"
5. IF ukuran file 10MB atau lebih, THEN THE API SHALL mengembalikan response 422 dengan pesan "Ukuran file maksimal 10MB"
5. THE API SHALL menyimpan file dengan nama yang di-hash untuk keamanan

### Requirement 14: Admin - Kelola Informasi Publik

**User Story:** Sebagai Admin_PPID, saya ingin menambah, mengedit, dan menghapus dokumen informasi publik, sehingga masyarakat selalu mendapat informasi terkini.

#### Acceptance Criteria

1. WHEN Admin_PPID mengirim POST request ke `/api/v1/admin/informasi-publik` dengan data dan file PDF valid, THE API SHALL membuat record informasi publik baru dan menyimpan file ke `storage/app/uploads/informasi_publik`
2. THE API SHALL memvalidasi field wajib: judul, kategori (berkala, serta_merta, setiap_saat), sub_kategori, deskripsi, file (PDF, maks 20MB), tahun
3. WHEN Admin_PPID mengirim PUT request ke `/api/v1/admin/informasi-publik/{id}` dengan ID yang valid, THE API SHALL memperbarui data informasi publik dan mengganti file jika file baru disertakan. IF ID tidak valid atau tidak ditemukan, THEN THE API SHALL mengembalikan response 404
4. WHEN Admin_PPID mengirim DELETE request ke `/api/v1/admin/informasi-publik/{id}` dengan ID yang valid, THE API SHALL menghapus record dan file fisik dari storage. IF ID tidak valid atau tidak ditemukan, THEN THE API SHALL mengembalikan response 404
5. THE API SHALL mendukung toggle is_published untuk publish/unpublish informasi publik tanpa menghapus record

### Requirement 15: Admin - Kelola FAQ

**User Story:** Sebagai Admin_PPID, saya ingin mengelola daftar FAQ, sehingga informasi yang sering ditanyakan selalu akurat dan terkini.

#### Acceptance Criteria

1. WHEN Admin_PPID mengirim GET request ke `/api/v1/admin/faq`, THE API SHALL mengembalikan response 200 berisi semua FAQ (termasuk yang inactive) diurutkan berdasarkan urutan
2. WHEN Admin_PPID mengirim POST request ke `/api/v1/admin/faq` dengan data valid, THE API SHALL membuat record FAQ baru
3. WHEN Admin_PPID mengirim PUT request ke `/api/v1/admin/faq/{id}`, THE API SHALL memperbarui record FAQ
4. WHEN Admin_PPID mengirim DELETE request ke `/api/v1/admin/faq/{id}`, THE API SHALL menghapus record FAQ
5. THE API SHALL memvalidasi field wajib: pertanyaan (min 10 karakter), jawaban (min 10 karakter)

### Requirement 16: Admin - Export Laporan

**User Story:** Sebagai Admin_PPID, saya ingin mengexport data permohonan ke format Excel, sehingga saya dapat membuat laporan bulanan untuk pimpinan.

#### Acceptance Criteria

1. WHEN Admin_PPID mengirim GET request ke `/api/v1/admin/laporan/permohonan` dengan parameter bulan dan tahun, THE API SHALL mengembalikan file Excel (.xlsx) berisi data permohonan sesuai filter
2. THE API SHALL menyertakan kolom pada file Excel: Tiket No, Nama Pemohon, NIK, Jenis Informasi, Status, Tanggal Pengajuan, Tanggal Selesai, Catatan Admin
3. THE API SHALL mendukung filter berdasarkan bulan (format YYYY-MM) dan status pada endpoint export

### Requirement 17: Admin - Statistik Dashboard

**User Story:** Sebagai Admin_PPID, saya ingin melihat statistik permohonan, sehingga saya dapat memantau kinerja layanan PPID.

#### Acceptance Criteria

1. WHEN Admin_PPID mengirim GET request ke `/api/v1/admin/statistik`, THE API SHALL mengembalikan response 200 berisi: total_permohonan_bulan_ini, sedang_diproses, selesai_bulan_ini, rata_rata_waktu_respon_hari, dan permohonan_per_bulan (data 12 bulan terakhir)
2. THE API SHALL menghitung rata_rata_waktu_respon_hari berdasarkan selisih antara created_at dan completed_at untuk permohonan yang selesai dalam bulan berjalan

### Requirement 18: Admin - Manajemen Keberatan

**User Story:** Sebagai Admin_PPID, saya ingin melihat dan menanggapi keberatan yang masuk, sehingga saya dapat memberikan resolusi sesuai prosedur.

#### Acceptance Criteria

1. WHEN Admin_PPID mengirim GET request ke `/api/v1/admin/keberatan`, THE API SHALL mengembalikan response 200 berisi daftar semua keberatan dengan pagination, termasuk data permohonan terkait
2. WHEN Admin_PPID mengirim PUT request ke `/api/v1/admin/keberatan/{id}` dengan status dan tanggapan, THE API SHALL memperbarui record keberatan. THE API SHALL mengizinkan update field non-status (tanggapan_admin) tanpa mengharuskan perubahan status
3. THE API SHALL memvalidasi transisi status keberatan: dikirim hanya dapat berubah ke diproses, diproses dapat berubah ke selesai. THE API SHALL menolak update di mana status baru sama dengan status saat ini (self-transition ditolak)
4. WHEN status keberatan berubah ke "selesai", THE API SHALL memperbarui kolom resolved_at dengan waktu saat ini

### Requirement 19: Notifikasi Email Async

**User Story:** Sebagai pemohon, saya ingin menerima notifikasi email saat permohonan saya berubah status, sehingga saya tidak perlu mengecek secara manual.

#### Acceptance Criteria

1. WHEN permohonan baru berhasil dibuat, THE API SHALL mendispatch job PermohonanCreatedNotification ke queue yang mengirim email berisi nomor tiket dan instruksi cek status
2. WHEN status permohonan berubah, THE API SHALL mendispatch job StatusChangedNotification ke queue yang mengirim email berisi status baru dan catatan admin (jika ada)
3. WHEN status permohonan berubah ke "selesai" dan memiliki dokumen balasan, THE API SHALL menyertakan informasi download dokumen dalam email notifikasi hanya jika job notifikasi berhasil didispatch
4. THE API SHALL memproses semua notifikasi email melalui Laravel queue (driver: database) secara async sehingga tidak memblokir response API
5. IF pengiriman email gagal, THEN THE API SHALL mencatat error ke log dan melakukan retry maksimal 3 kali dengan delay exponential

### Requirement 20: Keamanan dan Validasi Global

**User Story:** Sebagai pengelola sistem, saya ingin API yang aman dan tervalidasi, sehingga data terlindungi dari penyalahgunaan.

#### Acceptance Criteria

1. THE API SHALL menerapkan CSRF protection pada semua endpoint yang menerima data (POST, PUT, DELETE) melalui Sanctum token-based authentication
2. THE API SHALL memvalidasi semua input menggunakan Laravel Form Request classes yang terpisah per endpoint
3. THE API SHALL mengembalikan response error dalam format JSON yang konsisten: `{"status": "error", "message": "...", "errors": {...}}` untuk semua tipe error
4. THE API SHALL menerapkan rate limiting global 60 request per menit per IP untuk semua endpoint API
5. THE API SHALL mencatat semua aksi admin (login, update status, upload, delete) pada Laravel log dengan konteks user dan timestamp
6. THE API SHALL menggunakan Eloquent API Resources untuk format response yang konsisten pada semua endpoint
