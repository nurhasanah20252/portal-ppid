# Implementation Plan: Portal PPID Backend

## Overview

Implementasi REST API backend untuk Portal PPID Pengadilan Agama Penajam menggunakan Laravel 13, MySQL, Sanctum, dan Queue. Plan ini mencakup database schema, autentikasi, endpoint publik (permohonan, keberatan, informasi publik, FAQ), endpoint admin (manajemen, statistik, export), notifikasi email async, file upload, dan rate limiting. Semua endpoint mengembalikan JSON response dengan timezone Asia/Makassar (WITA).

## Tasks

- [x] 1. Setup database schema, models, factories, dan seeders
  - [x] 1.1 Buat migrations untuk semua tabel (permohonan, keberatan, informasi_publik, status_log, faq) dan modifikasi tabel users
    - Jalankan `php artisan make:migration` untuk setiap tabel
    - Definisikan kolom sesuai spesifikasi: enums, foreign keys, indexes, nullable fields
    - Tambahkan kolom `role` (enum: super_admin, ppid_staff) dan `last_login_at` pada tabel users
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 1.6_

  - [x] 1.2 Buat Eloquent models dengan relasi, casts, dan scopes
    - Permohonan: hasMany StatusLog, hasOne Keberatan, scopes (byStatus, search, dateRange)
    - Keberatan: belongsTo Permohonan
    - StatusLog: belongsTo Permohonan, belongsTo User
    - InformasiPublik: scopes untuk published, kategori, tahun
    - Faq: scope untuk active, ordering
    - User: tambah role cast dan relasi
    - _Requirements: 1.7_

  - [x] 1.3 Buat factories untuk setiap model dengan data faker konteks Indonesia
    - NIK 16 digit angka, format no HP Indonesia (10-15 digit), nama Indonesia
    - Enum values yang valid, format tiket_no yang benar
    - States untuk berbagai status (baru, diproses, selesai, ditolak)
    - _Requirements: 1.8_

  - [x] 1.4 Write property test untuk factory data validity
    - **Property 17: Factory data validity**
    - **Validates: Requirements 1.8**

  - [x] 1.5 Buat database seeder dengan data awal
    - 2 user admin (super_admin dan ppid_staff), 20 informasi publik, 12 FAQ, 50 permohonan dengan berbagai status
    - _Requirements: 1.9_

- [x] 2. Implementasi autentikasi admin dan middleware
  - [x] 2.1 Buat AuthController dengan login dan logout
    - POST `/api/v1/auth/login`: validasi credential, return token + user data, update last_login_at
    - POST `/api/v1/auth/logout`: hapus token, return 200
    - Error handling: 401 untuk credential salah, 500 untuk internal error
    - Buat LoginRequest form request class
    - _Requirements: 2.1, 2.2, 2.3, 2.5_

  - [x] 2.2 Setup middleware dan route protection
    - Konfigurasi Sanctum untuk token-based auth
    - Proteksi semua endpoint `/api/v1/admin/*` dengan middleware auth:sanctum
    - Buat LogAdminAction middleware untuk logging aksi admin
    - Return 401 "Unauthenticated" untuk token expired/invalid
    - _Requirements: 2.4, 2.6, 20.5_

  - [x] 2.3 Write unit tests untuk auth flow
    - Test login sukses, credential salah (401), token expired (401)
    - Test logout sukses, middleware protection
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.6_

- [x] 3. Implementasi core services, queue jobs stub, dan exception handling
  - [x] 3.1 Buat TiketGeneratorService
    - Format PPID-YYYYMMDD-XXXX dengan timezone Asia/Makassar
    - Database locking (FOR UPDATE) untuk thread-safety
    - Auto-increment nomor urut harian dimulai dari 0001
    - _Requirements: 3.2_

  - [x] 3.2 Write property test untuk tiket generation
    - **Property 1: Tiket number format invariant**
    - **Validates: Requirements 3.2**

  - [x] 3.3 Write property test untuk tiket uniqueness
    - **Property 2: Tiket number uniqueness dan sequential ordering**
    - **Validates: Requirements 3.2**

  - [x] 3.4 Buat FileUploadService
    - Handle upload KTP (max 2MB, jpg/jpeg/png), dokumen balasan (min 1KB, max <10MB, PDF), informasi publik (max 20MB, PDF)
    - Hashed filename untuk semua upload
    - Validasi ukuran dan format per tipe file
    - _Requirements: 4.1, 4.2, 13.1, 13.2, 13.6_

  - [x] 3.5 Buat PermohonanService dengan status transition logic
    - Method `updateStatus()` dengan DB::transaction dan email dispatch
    - Method `validatePermohonanTransition()` dengan self-transition support
    - Rollback jika email dispatch gagal
    - _Requirements: 12.1, 12.2, 12.3, 12.7_

  - [x] 3.6 Buat KeberatanService dengan status transition logic
    - Method `update()` mendukung dua mode: update status dan update tanggapan_admin saja
    - Method `validateKeberatanTransition()` — self-transition DITOLAK
    - _Requirements: 18.2, 18.3_

  - [x] 3.7 Buat InvalidStatusTransitionException dan global exception handler
    - Konsisten JSON error format: `{"status": "error", "message": "...", "errors": {...}}`
    - Custom rendering untuk NotFoundHttpException, ThrottleRequestsException, ValidationException
    - _Requirements: 20.3_

  - [x] 3.8 Buat stub queue job classes dan konfigurasi route file
    - Buat SendPermohonanCreatedNotification, SendStatusChangedNotification, SendKeberatanNotification sebagai stub classes (implementasi lengkap di task 14)
    - Pastikan queue driver = database, jalankan migration jobs table
    - Daftarkan semua routes di `routes/api.php` dengan prefix `v1`
    - Group admin routes dengan middleware auth:sanctum dan LogAdminAction
    - Group publik routes tanpa auth
    - _Requirements: 19.4, 20.1, 20.2, 20.4_

  - [x] 3.9 Write property test untuk consistent error response format
    - **Property 20: Consistent error response format**
    - **Validates: Requirements 20.3**

- [x] 4. Checkpoint - Pastikan semua tests pass
  - Pastikan semua tests pass, tanyakan user jika ada pertanyaan.

- [x] 5. Implementasi endpoint publik permohonan
  - [x] 5.1 Buat StorePermohonanRequest form request
    - Validasi: nik (16 digit), nama_lengkap (min 3), alamat, kota, provinsi, no_hp (10-15 digit), email, jenis_informasi (enum), tujuan, uraian_informasi
    - Conditional: nomor_perkara wajib jika jenis_informasi = salinan_putusan
    - Validasi file KTP: max 2MB, mimes jpg/jpeg/png
    - _Requirements: 3.3, 3.4, 4.2, 4.3, 4.4_

  - [x] 5.2 Buat PermohonanController (store dan show)
    - POST `/api/v1/permohonan`: buat permohonan, generate tiket, upload KTP, dispatch email job, buat status_log
    - GET `/api/v1/permohonan/{tiket_no}`: return status, riwayat, dokumen_balasan_url
    - Buat PermohonanResource dan StatusLogResource
    - _Requirements: 3.1, 3.6, 3.7, 5.1, 5.2, 5.3, 5.4_

  - [x] 5.3 Write property test untuk input validation
    - **Property 3: Input validation rejects invalid data**
    - **Validates: Requirements 3.3**

  - [x] 5.4 Write property test untuk conditional validation nomor perkara
    - **Property 4: Conditional validation nomor perkara**
    - **Validates: Requirements 3.4**

  - [x] 5.5 Write property test untuk status log creation
    - **Property 5: Status log creation on every status change**
    - **Validates: Requirements 3.6, 12.1, 12.8**

  - [x] 5.6 Write property test untuk email notification dispatch
    - **Property 9: Email notification dispatch on status change**
    - **Validates: Requirements 3.7, 12.7, 19.1, 19.2**

  - [x] 5.7 Write property test untuk file upload hashed filename
    - **Property 10: File upload always uses hashed filename**
    - **Validates: Requirements 4.1, 4.5, 13.6**

- [x] 6. Implementasi rate limiting permohonan
  - [x] 6.1 Konfigurasi rate limiter untuk endpoint permohonan
    - Daftarkan rate limiter `permohonan` di AppServiceProvider: 3 request/jam per IP
    - Daftarkan rate limiter `api` global: 60 request/menit per IP
    - Apply middleware throttle:permohonan pada POST /permohonan
    - Custom response 429 dengan pesan Bahasa Indonesia
    - Sertakan header X-RateLimit-Limit, X-RateLimit-Remaining, Retry-After
    - _Requirements: 6.1, 6.2, 6.3, 20.4_

  - [x] 6.2 Write property test untuk rate limit headers
    - **Property 21: Rate limit headers always present**
    - **Validates: Requirements 6.3**

- [x] 7. Implementasi endpoint keberatan
  - [x] 7.1 Buat StoreKeberatanRequest dan KeberatanController
    - POST `/api/v1/keberatan`: validasi tiket exists, status ditolak, belum ada keberatan sebelumnya
    - Validasi: permohonan_tiket (exists), nama_pemohon (min 3), alasan (min 10)
    - Dispatch email notifikasi ke admin saat keberatan dibuat
    - Buat KeberatanResource
    - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5, 7.6_

  - [x] 7.2 Write property test untuk keberatan eligibility
    - **Property 11: Keberatan eligibility validation**
    - **Validates: Requirements 7.4, 7.5**

- [x] 8. Implementasi endpoint informasi publik dan FAQ publik
  - [x] 8.1 Buat InformasiPublikController (index dan download)
    - GET `/api/v1/informasi-publik`: list published items dengan pagination, filter (kategori, tahun, search)
    - GET `/api/v1/informasi-publik/{id}/download`: file download dengan Content-Type
    - Default 10 per halaman, max 50 per halaman
    - Buat InformasiPublikResource
    - _Requirements: 8.1, 8.2, 8.3, 8.4, 8.5, 9.1, 9.2, 9.3_

  - [x] 8.2 Buat FaqController
    - GET `/api/v1/faq`: list FAQ aktif diurutkan berdasarkan kolom urutan ascending
    - Buat FaqResource
    - _Requirements: 10.1, 10.2_

  - [x] 8.3 Write property test untuk public endpoints published/active filter
    - **Property 13: Public endpoints only return published/active items**
    - **Validates: Requirements 8.5, 10.1**

  - [x] 8.4 Write property test untuk informasi publik filter consistency
    - **Property 14: Informasi publik filter consistency**
    - **Validates: Requirements 8.2**

  - [x] 8.5 Write property test untuk pagination bounds
    - **Property 15: Pagination bounds enforcement**
    - **Validates: Requirements 8.4**

  - [x] 8.6 Write property test untuk status history ordering
    - **Property 16: Status history chronological ordering**
    - **Validates: Requirements 5.2**

- [x] 9. Checkpoint - Pastikan semua tests pass
  - Pastikan semua tests pass, tanyakan user jika ada pertanyaan.

- [x] 10. Implementasi admin permohonan management
  - [x] 10.1 Buat AdminPermohonanController (index, show, updateStatus, uploadDokumen)
    - GET `/api/v1/admin/permohonan`: list semua permohonan dengan pagination, filter (status, jenis_informasi, tanggal, search)
    - GET `/api/v1/admin/permohonan/{tiket_no}`: detail lengkap termasuk data pemohon, riwayat status, URL KTP
    - PUT `/api/v1/admin/permohonan/{tiket_no}/status`: update status via PermohonanService
    - POST `/api/v1/admin/permohonan/{tiket_no}/dokumen`: upload dokumen balasan PDF
    - Buat UpdateStatusPermohonanRequest, UploadDokumenBalasanRequest
    - Buat PermohonanAdminResource, PermohonanCollection
    - _Requirements: 11.1, 11.2, 11.3, 12.1, 12.4, 12.5, 12.6, 12.8, 13.1, 13.2, 13.3, 13.4, 13.5_

  - [x] 10.2 Write property test untuk status transition validation
    - **Property 6: Permohonan status transition validation**
    - **Validates: Requirements 12.2, 12.3**

  - [x] 10.3 Write property test untuk timestamp update on status transition
    - **Property 7: Timestamp update on status transition**
    - **Validates: Requirements 12.4, 12.5**

  - [x] 10.4 Write property test untuk alasan tolak wajib
    - **Property 8: Alasan tolak wajib saat status ditolak**
    - **Validates: Requirements 12.6**

  - [x] 10.5 Write property test untuk admin filter consistency
    - **Property 18: Admin filter consistency for permohonan**
    - **Validates: Requirements 11.2**

- [x] 11. Implementasi admin keberatan management
  - [x] 11.1 Buat AdminKeberatanController (index, update)
    - GET `/api/v1/admin/keberatan`: list semua keberatan dengan pagination, termasuk data permohonan
    - PUT `/api/v1/admin/keberatan/{id}`: update status/tanggapan via KeberatanService
    - Buat UpdateKeberatanRequest (status optional, tanggapan_admin required_if selesai)
    - Set resolved_at saat status berubah ke selesai
    - _Requirements: 18.1, 18.2, 18.3, 18.4_

  - [x] 11.2 Write property test untuk keberatan status transition
    - **Property 12: Keberatan status transition validation**
    - **Validates: Requirements 18.3**

- [x] 12. Implementasi admin informasi publik dan FAQ management
  - [x] 12.1 Buat AdminInformasiPublikController (CRUD)
    - POST `/api/v1/admin/informasi-publik`: create dengan file PDF upload (max 20MB)
    - PUT `/api/v1/admin/informasi-publik/{id}`: update data, replace file jika disertakan
    - DELETE `/api/v1/admin/informasi-publik/{id}`: hapus record dan file fisik
    - Toggle is_published tanpa hapus record
    - Buat StoreInformasiPublikRequest, UpdateInformasiPublikRequest
    - _Requirements: 14.1, 14.2, 14.3, 14.4, 14.5_

  - [x] 12.2 Buat AdminFaqController (CRUD)
    - GET `/api/v1/admin/faq`: semua FAQ termasuk inactive
    - POST, PUT, DELETE `/api/v1/admin/faq/{id}`
    - Buat StoreFaqRequest, UpdateFaqRequest (pertanyaan min 10, jawaban min 10)
    - _Requirements: 15.1, 15.2, 15.3, 15.4, 15.5_

- [x] 13. Implementasi admin statistik dan export laporan
  - [x] 13.1 Buat StatistikService dan AdminStatistikController
    - GET `/api/v1/admin/statistik`: total_permohonan_bulan_ini, sedang_diproses, selesai_bulan_ini, rata_rata_waktu_respon_hari, permohonan_per_bulan (12 bulan)
    - Hitung rata_rata berdasarkan selisih created_at dan completed_at
    - Buat StatistikResource
    - _Requirements: 17.1, 17.2_

  - [x] 13.2 Write property test untuk statistik accuracy
    - **Property 19: Statistik rata-rata waktu respon accuracy**
    - **Validates: Requirements 17.2**

  - [x] 13.3 Buat LaporanService dan AdminLaporanController
    - GET `/api/v1/admin/laporan/permohonan`: export Excel dengan PhpSpreadsheet
    - Filter: bulan (YYYY-MM), status
    - Kolom Excel: Tiket No, Nama Pemohon, NIK, Jenis Informasi, Status, Tanggal Pengajuan, Tanggal Selesai, Catatan Admin
    - Buat ExportLaporanRequest
    - _Requirements: 16.1, 16.2, 16.3_

  - [x] 13.4 Write property test untuk export filter consistency
    - **Property 22: Export laporan filter consistency**
    - **Validates: Requirements 16.3**

- [x] 14. Implementasi queue jobs lengkap untuk notifikasi email
  - [x] 14.1 Lengkapi implementasi queue jobs dan mail classes
    - SendPermohonanCreatedNotification: email konfirmasi + tiket ke pemohon
    - SendStatusChangedNotification: email status baru + catatan admin + link download (jika selesai)
    - SendKeberatanNotification: email notifikasi ke admin
    - Semua jobs: retry 3x dengan exponential backoff, log error saat failure
    - Buat Mailable classes untuk setiap notifikasi
    - _Requirements: 19.1, 19.2, 19.3, 19.4, 19.5_

- [x] 15. Implementasi validasi global dan API resources
  - [x] 15.1 Setup Eloquent API Resources dan response format konsisten
    - Pastikan semua controllers menggunakan API Resources
    - Format response konsisten: `{"status": "success|error", "message": "...", "data": {...}, "errors": {...}}`
    - _Requirements: 20.3, 20.6_

- [x] 16. Checkpoint - Pastikan semua tests pass
  - Pastikan semua tests pass, tanyakan user jika ada pertanyaan.

- [x] 17. Final integration dan wiring
  - [x] 17.1 Wire semua komponen, verifikasi route list, dan jalankan full test suite
    - Verifikasi `php artisan route:list` menampilkan semua endpoint
    - Jalankan full test suite `php artisan test --compact`
    - Verifikasi seeder berjalan tanpa error `php artisan db:seed`
    - Pastikan migrations bersih `php artisan migrate:fresh`
    - Jalankan Pint formatter `vendor/bin/pint --dirty --format agent`
    - _Requirements: All_

  - [x] 17.2 Write integration tests end-to-end flow
    - Test full flow: submit permohonan → cek status → admin update → email dispatch
    - Test full flow: admin tolak → pemohon keberatan → admin resolve
    - Test full flow: admin upload informasi publik → publik download
    - _Requirements: All_

- [x] 18. Final checkpoint - Pastikan semua tests pass
  - Pastikan semua tests pass, tanyakan user jika ada pertanyaan.

## Notes

- Tasks bertanda `*` bersifat optional dan dapat dilewati untuk MVP lebih cepat
- Setiap task merujuk ke requirements spesifik untuk traceability
- Checkpoints memastikan validasi inkremental
- Property tests memvalidasi correctness properties universal dari design document
- Unit/feature tests memvalidasi skenario spesifik dan edge cases
- Semua timestamp menggunakan timezone Asia/Makassar (WITA)
- Gunakan `Queue::fake()` dan `Storage::fake()` dalam tests
- Jalankan `vendor/bin/pint --dirty --format agent` setelah setiap perubahan PHP

## Task Dependency Graph

```json
{
  "waves": [
    { "id": 0, "tasks": ["1.1"] },
    { "id": 1, "tasks": ["1.2", "1.5"] },
    { "id": 2, "tasks": ["1.3", "2.1", "3.7"] },
    { "id": 3, "tasks": ["1.4", "2.2", "3.1", "3.4"] },
    { "id": 4, "tasks": ["2.3", "3.2", "3.3", "3.5", "3.6", "3.8"] },
    { "id": 5, "tasks": ["3.9", "5.1", "6.1"] },
    { "id": 6, "tasks": ["5.2", "7.1", "8.1", "8.2"] },
    { "id": 7, "tasks": ["5.3", "5.4", "5.5", "5.6", "5.7", "6.2", "7.2", "8.3", "8.4", "8.5", "8.6"] },
    { "id": 8, "tasks": ["10.1", "11.1", "12.1", "12.2"] },
    { "id": 9, "tasks": ["10.2", "10.3", "10.4", "10.5", "11.2", "13.1", "13.3"] },
    { "id": 10, "tasks": ["13.2", "13.4", "14.1"] },
    { "id": 11, "tasks": ["15.1"] },
    { "id": 12, "tasks": ["17.1"] },
    { "id": 13, "tasks": ["17.2"] }
  ]
}
```
