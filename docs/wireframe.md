## 1. Desain System (Style Guide)

### Palet Warna

| Peran | Nama Warna | Kode Hex | Penggunaan |
|-------|------------|----------|-------------|
| **Primary** | Hijau Daun | `#1B5E20` | Header utama, navigasi active, tombol utama, footer |
| **Secondary 1** | Emas | `#FFC107` | Hover state, highlight informasi, border card, ikon statistik |
| **Secondary 2** | Orange | `#F57C00` | Tombol CTA (Ajukan, Cek Status), notifikasi, badge "Urgent" |
| **Secondary 3** | Ungu | `#6A1B9A` | Aksen untuk judul section, link, tombol sekunder, dashboard admin |
| **Neutral** | Putih | `#FFFFFF` | Latar belakang utama, teks di atas warna gelap |
| **Neutral** | Abu-abu muda | `#F5F5F5` | Latar belakang sekunder (card, footer), input field background |
| **Neutral** | Abu-abu sedang | `#9E9E9E` | Placeholder, teks sekunder |
| **Neutral** | Abu-abu gelap | `#212121` | Teks utama |

### Tipografi

| Elemen | Font | Ukuran | Berat | Warna |
|--------|------|--------|-------|-------|
| Heading 1 (Hero) | Poppins | 36px (desktop) / 28px (mobile) | Bold | `#1B5E20` |
| Heading 2 (Section) | Poppins | 24px | Semibold | `#6A1B9A` (Ungu) |
| Heading 3 (Card title) | Poppins | 20px | Medium | `#212121` |
| Body text | Inter | 16px | Regular | `#212121` |
| Small text (caption, footer) | Inter | 14px | Regular | `#616161` |
| Button label | Poppins | 16px | Medium | Putih (atau sesuai kontras) |

### Komponen UI

- **Tombol Primary:** Background `#1B5E20` (hijau), teks putih, border-radius 8px, padding 12px 24px, hover jadi `#2E7D32`.
- **Tombol CTA (Ajukan, Cek Status):** Background `#F57C00` (orange), teks putih, bayangan lembut, hover `#EF6C00`.
- **Tombol Secondary:** Outline `#6A1B9A` (ungu), teks ungu, background transparan.
- **Card:** Background putih, border-radius 16px, shadow: 0 4px 12px rgba(0,0,0,0.05), border-top 4px solid `#FFC107` (emas).
- **Input field:** Border `#E0E0E0`, border-radius 8px, padding 12px, focus border menjadi `#1B5E20` (hijau).

---

## 2. High-Fidelity Mockup – Halaman Beranda (Desktop)

### Layout (1200px width, center)

```
+-----------------------------------------------------------------------------------+
| [LOGO: PPID PA Penajam] (hijau)   | Beranda | Profil | Info Publik | Permohonan | Status | Kontak |
| (ikon gavel + teks)                                                               |
+-----------------------------------------------------------------------------------+
|                                                                                   |
|  HERO BANNER (background gradien hijau ke ungu)                                   |
|  "Portal Keterbukaan Informasi Publik" (putih)                                    |
|  "Pengadilan Agama Penajam" (emas)                                                |
|  [ AJUKAN PERMOHONAN ] (orange)   [ CEK STATUS ] (outline putih)                  |
|                                                                                   |
+-----------------------------------------------------------------------------------+
| STATISTIK CEPAT (3 card dengan border-top emas)                                   |
| +------------------+  +------------------+  +------------------+                 |
| | 📄 Permohonan    |  | ⏳ Diproses      |  | ✅ Selesai       |                 |
| | Bulan ini: 24    |  | saat ini: 3     |  | bulan ini: 21    |                 |
| | (warna emas ikon)|  | (warna emas ikon)|  | (warna emas ikon)|                 |
| +------------------+  +------------------+  +------------------+                 |
+-----------------------------------------------------------------------------------+
| INFORMASI TERBARU (kiri)                        |  PROFIL PPID (kanan)            |
| +----------------------------------+            | +-----------------------------+ |
| | > Pengumuman jam layanan Idul    |            | | Ketua PPID:                 | |
| |   Adha - 10 Juni 2026            |            | | Dr. H. Ahmad Fauzi, S.H.    | |
| | > Laporan kinerja 2025 telah     |            | | Sekretaris:                 | |
| |   tersedia (unduh PDF)           |            | | Lina Marlina, S.Sos         | |
| | > Sosialisasi PPID untuk wartawan|            | | Kontak: ppid@pa-penajam.go.id| |
| |   - 15 Juni 2026                 |            | | [Selengkapnya] (tombol ungu) | |
| +----------------------------------+            | +-----------------------------+ |
+-----------------------------------------------------------------------------------+
| INFORMASI PUBLIK (tiga tab dengan warna orange untuk tab aktif)                  |
| [ Berkala ] [ Serta Merta ] [ Setiap Saat ]                                      |
| +-----------------------------------------------------------------------------+   |
| | √ Putusan Pengadilan Agama Penajam (2025) - unduh                           |   |
| | √ Laporan Keuangan Semester I 2025                                          |   |
| | √ Rencana Strategis 2025-2029                                               |   |
| | [Lihat semua berkala] (tautan ungu)                                         |   |
| +-----------------------------------------------------------------------------+   |
+-----------------------------------------------------------------------------------+
| FAQ & KONTAK (2 kolom)                                                           |
| +------------------------------+  +------------------------------------------+   |
| | FAQ (warna judul ungu)       |  | KONTAK & PETA                            |   |
| | Q: Bagaimana cara mengajukan |  | 📍 Jl. Imam Bonjol No. 12, Penajam       |   |
| |   permohonan informasi?      |  | 📞 0542-123456                            |   |
| | A: Isi formulir online atau  |  | ✉️ ppid@pa-penajam.go.id                 |   |
| |   datang ke kantor...        |  | 🕒 Senin-Jumat: 08.00-15.00               |   |
| | Q: Berapa biaya salinan?     |  | [Tampilkan Peta] (embed Google Maps)     |   |
| | A: Gratis untuk 10 halaman   |  |                                          |   |
| |   pertama, selanjutnya...    |  |                                          |   |
| | [Lihat semua FAQ] (ungu)     |  |                                          |   |
| +------------------------------+  +------------------------------------------+   |
+-----------------------------------------------------------------------------------+
| FOOTER (background hijau tua #1B5E20, teks putih)                                 |
| © 2026 PPID Pengadilan Agama Penajam | Kebijakan Privasi | Kontak | Dibangun untuk  |
| keterbukaan informasi publik                                                      |
+-----------------------------------------------------------------------------------+
```

### Contoh Konten Asli (text)

- **Hero title:** *Portal Keterbukaan Informasi Publik*  
- **Subtitle:** *Pengadilan Agama Penajam – Melayani dengan Transparan*  
- **Pengumuman:** *"Sehubungan dengan Idul Adha 1447 H, layanan PPID tutup pukul 12.00 WITA pada 10 Juni 2026"*  
- **Putusan contoh:** *Putusan Nomor 123/Pdt.G/2026/PA.Pjm tentang izin poligami*

---

## 3. High-Fidelity Mockup – Form Permohonan Informasi

### Layout dengan progress bar

```
+-----------------------------------------------------------------------------------+
| [LOGO] PPID PA Penajam                                            [Ikuti langkah] |
+-----------------------------------------------------------------------------------+
| < Kembali ke Beranda                                                              |
|                                                                                   |
|   Langkah 1 dari 3: Data Pemohon                                                  |
|   [████████░░░░░░░░░░]  (progress bar hijau, background abu)                      |
|                                                                                   |
| FORMULIR PERMOHONAN INFORMASI (card dengan border emas)                           |
| +---------------------------------------------------------------------------------+
| | 👤 Data Pemohon                                                                 |
| | NIK*            [   [____]      ] (tooltip: 16 digit)                          |
| | Nama Lengkap*   [ Ahmad Ridwan, S.H. ]                                         |
| | Alamat*         [ Jl. Merdeka No. 45, RT 02, Penajam ]                         |
| | Kota/Kab        [ Penajam Paser Utara ]   Provinsi [ Kalimantan Timur ]        |
| | No. HP*         [ 081234567890 ]                                                |
| | Email*          [ ahmad.ridwan@email.com ]                                     |
| | Upload KTP*     [ Pilih file ] (max 2MB, jpg/png) + preview thumbnail          |
| +---------------------------------------------------------------------------------+
| | 📄 Detail Informasi yang Dimohon                                                |
| | Jenis Informasi*   ● Salinan putusan   ○ Laporan kinerja   ○ Lainnya: ______   |
| | Nomor Perkara (jika putusan) [ 123/Pdt.G/2026/PA.Pjm ]                          |
| | Tujuan Permohonan* [ Untuk kepentingan banding di Pengadilan Tinggi ]          |
| | Uraian Informasi*  [ Saya memohon salinan putusan cerai talak atas nama ... ]   |
| |                   (textarea 4 baris)                                            |
| +---------------------------------------------------------------------------------+
| | 🔒 Persetujuan & Verifikasi                                                    |
| | [✓] Saya menyatakan data ini benar dan siap mematuhi ketentuan PPID.          |
| | [✓] Saya tidak akan menyebarluaskan dokumen tanpa izin.                       |
| +---------------------------------------------------------------------------------+
|                                                                                   |
| [ BATAL ] (tombol outline ungu)              [ AJUKAN PERMOHONAN ] (tombol orange)|
|                                                                                   |
| *Wajib diisi | Informasi akan diproses maksimal 5 hari kerja.                    |
+-----------------------------------------------------------------------------------+
```

### Contoh Notifikasi Sukses (toast muncul di pojok kanan atas)

```
+---------------------------------------------+
| ✅ Permohonan berhasil dikirim!             |
| Nomor tiket: PPID-20260609-001             |
| Cek email Anda untuk detail.               |
| [Tutup]                                    |
+---------------------------------------------+
```

---

## 4. High-Fidelity Mockup – Cek Status Permohonan

```
+-----------------------------------------------------------------------------------+
| [LOGO] PPID PA Penajam                                                         |
+-----------------------------------------------------------------------------------+
|                                                                                   |
|   🔍 CEK STATUS PERMOHONAN (heading ungu)                                        |
|                                                                                   |
|   Masukkan nomor tiket yang Anda terima:                                          |
|   [ PPID-20260609-001                ] (input dengan ikon tiket)                 |
|                                                                                   |
|   [ CEK STATUS ] (tombol orange, lebar penuh pada mobile)                        |
|                                                                                   |
|   (Hasil pencarian – contoh jika ditemukan)                                      |
|   +---------------------------------------------------------------------------+   |
|   | 🟢 STATUS: DIPROSES (badge hijau)                                        |   |
|   | -----------------------------------------------------------------------   |   |
|   | 📅 Tanggal ajuan: 5 Juni 2026                                            |   |
|   | ⏳ Perkiraan selesai: 9 Juni 2026                                        |   |
|   | 📝 Catatan admin: Dokumen sedang diverifikasi oleh panitera.             |   |
|   |                                                                          |   |
|   | 📜 Riwayat:                                                              |   |
|   | • 5/6/2026 10:00 - Permohonan diterima (status: Baru)                   |   |
|   | • 6/6/2026 08:30 - Diproses oleh PPID (status: Verifikasi dokumen)      |   |
|   |                                                                          |   |
|   | [ Kirim ulang notifikasi ke email ] (tombol sekunder ungu)              |   |
|   +---------------------------------------------------------------------------+   |
|                                                                                   |
|   Belum mendapat tiket? [Ajukan permohonan baru] (tautan orange)                |
|                                                                                   |
+-----------------------------------------------------------------------------------+
```

**Jika tiket tidak ditemukan:**
```
+---------------------------------------------+
| ⚠️ Nomor tiket tidak ditemukan.             |
| Periksa kembali atau hubungi helpdesk.     |
| Kontak: 0542-123456                        |
+---------------------------------------------+
```

---

## 5. High-Fidelity Mockup – Dashboard Admin (Desktop)

### Layout dengan sidebar ungu

```
+-----------------------------------------------------------------------------------+
|  PPID Admin Panel (logo hijau)                      [Lina Marlina] v | 🔔 | ⚙️ | Logout |
+----------+------------------------------------------------------------------------+
|          |                                                                        |
| (ungu)   |  📋 DAFTAR PERMOHONAN MASUK (Belum diproses: 3)                       |
| 🏠 Beranda|  +--------+----------------+------------------+----------+------------+ |
| 📄 Semua |  | Tiket  | Pemohon        | Jenis Informasi  | Status   | Aksi       | |
|   Permoh. |  +--------+----------------+------------------+----------+------------+ |
| ✅ Proses |  | #001   | Ahmad R.       | Salinan putusan  | Baru     | [Proses]   | |
| ❌ Tolak |  | #002   | Siti N.        | Laporan kinerja  | Baru     | [Proses]   | |
| 📦 Arsip |  | #003   | Budi (NIK...)  | Keberatan        | Baru     | [Proses]   | |
| 📝 Kelola |  +--------+----------------+------------------+----------+------------+ |
|   Konten |                                                                        |
| 📊 Laporan|  Detail Permohonan #001 (modal yang muncul saat klik Proses)          |
| ⚙️ Setelan|  +------------------------------------------------------------------+   |
|          |  | Data Pemohon: Ahmad Ridwan, NIK 647201...                       |   |
|          |  | Upload KTP: [Lihat file]                                        |   |
|          |  | Uraian: "Mohon salinan putusan cerai talak Nomor 123/..."       |   |
|          |  |                                                                  |   |
|          |  | Ubah status: [ Menunggu verifikasi ▼ ] (dropdown)               |   |
|          |  | Balasan ke pemohon: [ Isi pesan (textarea) ]                     |   |
|          |  | Upload dokumen balasan (PDF): [Pilih file]                        |   |
|          |  |                                                                  |   |
|          |  | [ Batalkan ]            [ Kirim & Update Status ] (tombol orange) |   |
|          |  +------------------------------------------------------------------+   |
|          |                                                                        |
|          |  📎 EXPORT DATA: [ Export ke Excel ] (tombol outline ungu)             |
|          |  🖨️  Cetak Laporan Bulanan: [ Cetak ]                                   |
|          |                                                                        |
+----------+------------------------------------------------------------------------+
```

### Grafik Statistik di halaman Beranda Admin (menggunakan Chart.js)

```
+----------------------------------+  +----------------------------------+
| 📈 Permohonan per bulan (2026)   |  | ⏱️ Rata-rata waktu respon       |
| (bar chart hijau, emas, orange)  |  | 2.4 hari (target ≤ 5 hari)      |
| Jan:12 Feb:15 Mar:24 ...         |  | ✅ 87% permohonan selesai tepat  |
+----------------------------------+  +----------------------------------+
```

---

## 6. Mobile Responsive (Contoh Beranda – 375px)

Navigasi menggunakan hamburger menu (warna hijau). Tombol-tombol ditumpuk vertikal.

```
+---------------------------------+
| [≡]  PPID PA Penajam    🔍     |
+---------------------------------+
|    (background hijau-ungu)      |
| Portal Keterbukaan Informasi    |
| [ AJUKAN ] [ CEK STATUS ]       |
+---------------------------------+
| 📊 24 permohonan bulan ini       |
| 3 diproses, 21 selesai          |
+---------------------------------+
| 🆕 Info terbaru:                |
| > Pengumuman Idul Adha          |
| > Laporan kinerja 2025          |
+---------------------------------+
| 📚 Informasi Publik:            |
| [Berkala] [Serta Merta] [Setiap Saat] |
| - Putusan PA Penajam            |
| - Laporan keuangan              |
+---------------------------------+
| ❓ FAQ                           |
| Q: Bagaimana cara mengajukan?   |
| A: ...                          |
+---------------------------------+
| 📍 Kontak & Peta                |
| Jl. Imam Bonjol 12              |
+---------------------------------+
```

**Hamburger menu terbuka:**
```
+---------------------------------+
| Beranda                         |
| Profil PPID                     |
| Informasi Publik                |
| Permohonan Informasi            |
| Cek Status                      |
| Kontak                          |
| Dashboard (jika admin login)    |
| Logout                          |
+---------------------------------+
```

---

## 7. Interaksi & Micro-interactions (Spesifikasi untuk Developer)

- **Hover tombol:** Scale 1.02, transisi 0.2s ease.
- **Focus input:** Outline `#FFC107` (emas) ketebalan 2px.
- **Loading state setelah submit form:** Spinner berwarna orange di tengah tombol, teks berubah menjadi "Mengirim...".
- **Toast notification:** Muncul dari kanan atas, hilang otomatis 5 detik.
- **Dashboard admin** menggunakan konfirmasi modal sebelum mengubah status atau menghapus.

---

## 8. Contoh Aset Ikon (Font Awesome 6 / Material Icons)

- Beranda: `fa-home`
- Profil: `fa-building`
- Info Publik: `fa-folder-open`
- Permohonan: `fa-pen-alt`
- Cek Status: `fa-search`
- Kontak: `fa-envelope`
- Admin: `fa-user-shield`

---

## 9. Implementasi Tips (CSS & Framework)

- Gunakan **Tailwind CSS** dengan konfigurasi warna custom:
  ```js
  colors: {
    'hijau': '#1B5E20',
    'emas': '#FFC107',
    'orange': '#F57C00',
    'ungu': '#6A1B9A',
  }
  ```
- Atau **CSS variables**:
  ```css
  :root {
    --hijau: #1B5E20;
    --emas: #FFC107;
    --orange: #F57C00;
    --ungu: #6A1B9A;
  }
  ```

---

## 10. File Output untuk Tim Pengembang

Saya akan menyediakan (dalam proyek nyata):

- **Figma file** dengan high-fidelity prototype interaktif.
- **CSS/SCSS** modul komponen.
- **Storybook** untuk komponen UI (tombol, card, input).
- **Asset gambar** (logo, ikon, background hero gradien).

---