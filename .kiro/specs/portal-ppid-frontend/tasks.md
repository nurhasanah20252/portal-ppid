# Implementation Plan: Portal PPID Frontend

## Overview

Implementasi frontend Portal PPID menggunakan Laravel 13 + Inertia.js v3 + React 19 + Tailwind CSS v4. Proyek sudah memiliki halaman publik dasar, komponen UI (shadcn/ui), layout, dan tipe data. Fokus implementasi ini adalah: komponen baru yang belum dibuat, halaman admin, validasi client-side, aksesibilitas, dan testing.

## Tasks

- [x] 1. Membuat library validasi client-side dan komponen shared baru
  - [x] 1.1 Implementasi `lib/validation.ts` dengan aturan validasi yang dapat diuji
    - Buat file `resources/js/lib/validation.ts` dengan validation rules: nik, email, noHp, namaLengkap, uraianInformasi, ktpFile
    - Setiap rule memiliki fungsi `validate()` yang mengembalikan boolean dan properti `message` untuk pesan error
    - Export fungsi `validateField(fieldName, value)` yang mengembalikan `{ valid: boolean, message: string }`
    - _Requirements: 6.3, 6.4_

  - [x] 1.2 Write property test untuk validasi form (Property 1)
    - **Property 1: Validasi form menolak input tidak valid dan mengembalikan pesan error yang tepat**
    - Buat file `tests/js/validation.property.test.ts` menggunakan Vitest + fast-check
    - Generate arbitrary strings dan verifikasi bahwa rule menolak input tidak valid dan menerima input valid
    - **Validates: Requirements 6.3, 6.4**

  - [x] 1.3 Implementasi komponen `SkipToContent`
    - Buat file `resources/js/components/skip-to-content.tsx`
    - Link tersembunyi yang muncul saat menerima fokus keyboard
    - Mengarahkan fokus ke elemen `#content` (main)
    - Integrasikan ke `PublicLayout` dan `AppLayout`
    - _Requirements: 2.4, 17.1_

  - [x] 1.4 Implementasi komponen `ProgressBar`
    - Buat file `resources/js/components/progress-bar.tsx`
    - Props: `currentStep`, `totalSteps`, `labels`
    - Animasi transisi lebar 300ms
    - Warna hijau (#1B5E20) untuk progress fill
    - _Requirements: 6.2, 1.4_

  - [x] 1.5 Implementasi komponen `FileUpload` dengan preview thumbnail
    - Buat file `resources/js/components/file-upload.tsx`
    - Props: `accept`, `maxSize`, `onChange`, `error`
    - Menampilkan preview thumbnail saat file dipilih
    - Validasi tipe file dan ukuran secara client-side
    - _Requirements: 6.10, 6.1_

  - [x] 1.6 Implementasi komponen `FaqAccordion`
    - Buat file `resources/js/components/faq-accordion.tsx`
    - Menggunakan Radix UI Collapsible yang sudah tersedia
    - Animasi expand/collapse yang halus
    - Props: `items: Array<{ pertanyaan: string, jawaban: string }>`
    - _Requirements: 10.1, 10.2_

  - [x] 1.7 Implementasi komponen `TimelineStatus`
    - Buat file `resources/js/components/timeline-status.tsx`
    - Props: `riwayat: StatusLog[]`
    - Menampilkan timeline vertikal dengan dot dan garis penghubung
    - Status terbaru ditampilkan dengan warna hijau aktif
    - _Requirements: 7.3_

  - [x] 1.8 Implementasi komponen `FilterBar`
    - Buat file `resources/js/components/filter-bar.tsx`
    - Props: `filters`, `tahunList`, `onFilterChange`
    - Tab kategori + select tahun
    - Update filter tanpa full page reload (via Inertia router)
    - _Requirements: 5.1, 5.2_

  - [x] 1.9 Implementasi komponen `ConfirmModal`
    - Buat file `resources/js/components/confirm-modal.tsx`
    - Menggunakan Dialog dari shadcn/ui
    - Props: `open`, `onConfirm`, `onCancel`, `title`, `description`, `confirmLabel`, `variant`
    - Animasi fade-in, overlay gelap
    - _Requirements: 1.9, 13.5_

- [x] 2. Checkpoint - Pastikan semua komponen shared berhasil dibuat
  - Ensure all tests pass, ask the user if questions arise.

- [x] 3. Memperbaiki halaman publik yang sudah ada sesuai requirements
  - [x] 3.1 Integrasi validasi inline pada form Permohonan menggunakan `lib/validation.ts`
    - Import dan gunakan `validateField()` pada event `onBlur` setiap field
    - Tambahkan animasi shake pada field error
    - Hubungkan `aria-describedby` pada setiap input ke pesan error-nya
    - Ganti komponen file input dengan `FileUpload` baru (preview thumbnail)
    - Ganti progress indicator dengan komponen `ProgressBar` baru
    - _Requirements: 6.3, 6.4, 6.8, 6.10, 17.3_

  - [x] 3.2 Write property test untuk aksesibilitas form (Property 6)
    - **Property 6: Setiap field form dengan error memiliki aria-describedby yang menunjuk ke pesan error**
    - Buat file `tests/js/form-accessibility.property.test.ts`
    - Gunakan fast-check untuk generate field names, verifikasi bahwa aria-describedby terhubung ke error message DOM element
    - **Validates: Requirements 17.3**

  - [x] 3.3 Write property test untuk server validation errors (Property 5)
    - **Property 5: Server validation errors (422) ditampilkan pada field yang sesuai**
    - Buat file `tests/js/server-validation-errors.property.test.ts`
    - Generate arbitrary error objects `{ [fieldName]: string[] }` dan verifikasi bahwa setiap error ditampilkan di bawah field yang sesuai
    - **Validates: Requirements 6.8**

  - [x] 3.4 Perbaiki halaman Informasi Publik dengan komponen `FilterBar` dan Skeleton_Loading
    - Ganti tab filter manual dengan komponen `FilterBar`
    - Tambahkan Skeleton loading saat data sedang dimuat (deferred props)
    - _Requirements: 5.1, 5.2, 5.5_

  - [x] 3.5 Write property test untuk rendering item informasi publik (Property 2)
    - **Property 2: Rendering item informasi publik menampilkan semua field yang diperlukan**
    - Buat file `tests/js/informasi-publik-item.property.test.ts`
    - Generate arbitrary `InformasiPublik` objects, render, dan verifikasi bahwa judul, kategori, tahun, dan tautan unduh ada di output
    - **Validates: Requirements 5.3**

  - [x] 3.6 Perbaiki halaman Status dengan komponen `TimelineStatus`
    - Ganti implementasi timeline manual dengan komponen `TimelineStatus`
    - Pastikan badge status, tanggal pengajuan, dan riwayat lengkap ditampilkan
    - _Requirements: 7.3_

  - [x] 3.7 Write property test untuk rendering hasil cek status (Property 3)
    - **Property 3: Rendering hasil cek status menampilkan semua elemen yang diperlukan**
    - Buat file `tests/js/status-check-result.property.test.ts`
    - Generate arbitrary `StatusCheckResult`, render, dan verifikasi keberadaan elemen wajib
    - **Validates: Requirements 7.3**

  - [x] 3.8 Perbaiki halaman FAQ dengan komponen `FaqAccordion`
    - Ganti implementasi `<details>` manual dengan `FaqAccordion`
    - Tambahkan Skeleton loading saat data dimuat
    - _Requirements: 10.1, 10.2, 10.3_

  - [x] 3.9 Perbaiki halaman Keberatan - tambahkan validasi inline dan handling error
    - Integrasikan `lib/validation.ts` untuk validasi alasan (min 10 karakter)
    - Tambahkan handling error 422 dan toast sukses/error
    - _Requirements: 8.1, 8.2, 8.3, 8.4_

  - [x] 3.10 Integrasi event tracking pada semua halaman publik
    - Pastikan navigasi menu mengirim event `navigation`
    - Pastikan CTA buttons mengirim event `cta_button`
    - Pastikan download dokumen mengirim event `download`
    - Pastikan error API mengirim event `error`
    - _Requirements: 18.1, 18.2, 18.3, 18.4, 18.5_

- [x] 4. Checkpoint - Pastikan semua halaman publik berfungsi sesuai requirements
  - Ensure all tests pass, ask the user if questions arise.

- [x] 5. Implementasi komponen `DataTable` dan `StatChart` untuk admin
  - [x] 5.1 Implementasi komponen `DataTable`
    - Buat file `resources/js/components/data-table.tsx`
    - Props: `columns`, `data`, `pagination`, `onPageChange`
    - Support sorting, responsive (card view pada mobile)
    - Gunakan tabel HTML semantik dengan proper headers
    - _Requirements: 13.1, 13.6, 16.3_

  - [x] 5.2 Implementasi komponen `StatChart`
    - Buat file `resources/js/components/stat-chart.tsx`
    - Props: `data: Array<{ bulan: string, total: number }>`
    - Bar chart sederhana menggunakan Recharts atau library chart ringan
    - Responsif dan aksesibel
    - _Requirements: 12.3_

- [x] 6. Implementasi halaman Admin Dashboard
  - [x] 6.1 Buat halaman admin dashboard `pages/admin/dashboard.tsx`
    - Gunakan `AppLayout` dengan sidebar ungu
    - Tampilkan card statistik: total permohonan, diproses, selesai, rata-rata respon
    - Integrasikan `StatChart` untuk grafik permohonan per bulan
    - Gunakan Skeleton_Loading saat data dimuat (deferred props)
    - _Requirements: 12.1, 12.2, 12.3, 12.4_

  - [x] 6.2 Buat halaman admin permohonan `pages/admin/permohonan.tsx`
    - Gunakan `DataTable` untuk menampilkan daftar permohonan
    - Filter berdasarkan status (baru, diproses, selesai, ditolak)
    - Tombol "Proses" membuka Dialog detail permohonan
    - Dialog berisi: data pemohon, uraian, file KTP, dropdown status, textarea balasan, upload dokumen
    - Gunakan `ConfirmModal` sebelum update status
    - Update tabel tanpa full page reload setelah perubahan
    - _Requirements: 13.1, 13.2, 13.3, 13.4, 13.5, 13.6_

  - [x] 6.3 Write property test untuk rendering baris tabel permohonan admin (Property 4)
    - **Property 4: Rendering baris tabel permohonan admin menampilkan semua kolom**
    - Buat file `tests/js/permohonan-table-row.property.test.ts`
    - Generate arbitrary `Permohonan` objects, render baris tabel, verifikasi keberadaan tiket, nama, jenis, status, dan aksi
    - **Validates: Requirements 13.1**

  - [x] 6.4 Buat halaman admin informasi publik `pages/admin/informasi-publik.tsx`
    - Gunakan `DataTable` untuk daftar informasi publik
    - Tombol "Tambah Informasi" membuka form dialog (judul, kategori, sub-kategori, deskripsi, file PDF, tahun, nomor perkara)
    - Tombol "Edit" membuka form dialog dengan data terisi
    - Tombol "Hapus" menggunakan `ConfirmModal`
    - Toast sukses setelah operasi CRUD berhasil
    - _Requirements: 14.1, 14.2, 14.3, 14.4_

  - [x] 6.5 Buat halaman admin laporan `pages/admin/laporan.tsx`
    - Filter bulan/tahun
    - Tombol "Export ke Excel" memicu download file
    - _Requirements: 15.1, 15.2_

- [x] 7. Checkpoint - Pastikan semua halaman admin berfungsi
  - Ensure all tests pass, ask the user if questions arise.

- [x] 8. Responsivitas dan aksesibilitas
  - [x] 8.1 Implementasi responsivitas mobile untuk semua halaman
    - Verifikasi layout pada 320px–768px–1920px
    - Hamburger menu pada `PublicHeader` untuk layar < 768px
    - Tombol CTA full-width pada mobile
    - Tabel admin dalam format card pada mobile
    - _Requirements: 16.1, 16.2, 16.3, 2.3_

  - [x] 8.2 Implementasi aksesibilitas lengkap
    - Focus outline emas 2px pada semua elemen interaktif
    - `aria-label` pada tombol ikon tanpa teks
    - `aria-describedby` menghubungkan error dengan input
    - `role="status"` dan `aria-live="polite"` pada Toast (Sonner)
    - Kontras minimal 4.5:1
    - _Requirements: 17.1, 17.2, 17.3, 17.4, 17.5_

  - [x] 8.3 Write unit test untuk aksesibilitas komponen
    - Test fokus outline, aria-labels, dan kontras warna pada komponen utama
    - _Requirements: 17.1, 17.2, 17.5_

- [x] 9. Final checkpoint - Pastikan semua tests pass dan fitur terintegrasi
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks dengan tanda `*` bersifat opsional dan dapat dilewati untuk MVP lebih cepat
- Setiap task mereferensikan requirements spesifik untuk traceability
- Proyek sudah memiliki banyak komponen dan halaman dasar — fokus pada penyempurnaan dan fitur yang belum ada
- Gunakan komponen shadcn/ui yang sudah tersedia (Button, Card, Input, Dialog, Badge, Skeleton, Sonner)
- Komponen domain yang sudah ada: HeroBanner, StatCard, StatusBadge, PublicHeader, PublicFooter, SkeletonCard, InputError
- Property tests menggunakan Vitest + fast-check sesuai design document
- Semua code comment harus dalam Bahasa Indonesia

## Task Dependency Graph

```json
{
  "waves": [
    { "id": 0, "tasks": ["1.1", "1.3", "1.4", "1.5", "1.6", "1.7", "1.8", "1.9"] },
    { "id": 1, "tasks": ["1.2", "5.1", "5.2"] },
    { "id": 2, "tasks": ["3.1", "3.4", "3.6", "3.8", "3.9", "3.10"] },
    { "id": 3, "tasks": ["3.2", "3.3", "3.5", "3.7", "6.1", "6.2"] },
    { "id": 4, "tasks": ["6.3", "6.4", "6.5"] },
    { "id": 5, "tasks": ["8.1", "8.2"] },
    { "id": 6, "tasks": ["8.3"] }
  ]
}
```
