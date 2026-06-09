# DOKUMEN TEKNIS DEVELOPER
**Proyek:** Portal PPID Pengadilan Agama Penajam  
**Versi API:** 1.0  
**Database:** MySQL 8.0 / PostgreSQL 14+  
**Backend:** Laravel 11 (REST API) atau Node.js + Express  
**Tanggal:** 9 Juni 2026  

---

## 1. User Story Mapping (Prioritas Pengembangan)

Berikut adalah **user story mapping** berdasarkan fitur MVP (P0) dan pendukung (P1/P2). Setiap story ditulis sebagai **“Sebagai [role], saya ingin [action] sehingga [benefit]”**.

### Epic 1: Manajemen Permohonan Informasi

| ID | User Story | Prioritas | Estimasi (hari) |
|----|------------|-----------|----------------|
| US-01 | Sebagai **pemohon**, saya ingin mengisi formulir permohonan informasi online dengan data diri dan detail informasi yang diminta, sehingga saya tidak perlu datang ke kantor. | P0 | 2 |
| US-02 | Sebagai **pemohon**, saya ingin mendapat nomor tiket unik setelah submit, sehingga saya bisa melacak status permohonan saya. | P0 | 1 |
| US-03 | Sebagai **pemohon**, saya ingin menerima email notifikasi otomatis ketika status permohonan berubah, sehingga saya tidak perlu mengecek terus menerus. | P1 | 1 |
| US-04 | Sebagai **admin PPID**, saya ingin melihat daftar semua permohonan yang masuk, diurutkan dari yang terbaru, sehingga saya bisa memprosesnya secara efisien. | P0 | 1 |
| US-05 | Sebagai **admin PPID**, saya ingin mengubah status permohonan (baru → diproses → selesai → ditolak) dan menambahkan catatan/balasan, sehingga pemohon tahu perkembangan. | P0 | 1 |
| US-06 | Sebagai **admin PPID**, saya ingin mengupload dokumen balasan (PDF) untuk permohonan yang selesai, sehingga pemohon bisa mengunduhnya. | P0 | 1 |

### Epic 2: Tracking & Keberatan

| ID | User Story | Prioritas | Estimasi |
|----|------------|-----------|----------|
| US-07 | Sebagai **pemohon**, saya ingin memasukkan nomor tiket dan melihat status terbaru serta riwayat proses, sehingga saya tahu kapan dokumen siap. | P0 | 1 |
| US-08 | Sebagai **pemohon**, saya ingin mengajukan keberatan jika permohonan saya ditolak, dengan mengisi formulir yang merujuk pada tiket sebelumnya. | P0 | 2 |
| US-09 | Sebagai **admin PPID**, saya ingin menerima pemberitahuan keberatan baru dan dapat memberikan tanggapan/resolusi. | P1 | 1 |

### Epic 3: Informasi Publik & Arsip

| ID | User Story | Prioritas | Estimasi |
|----|------------|-----------|----------|
| US-10 | Sebagai **pengunjung**, saya ingin melihat daftar informasi berkala (putusan, laporan keuangan, renstra) yang dapat diunduh. | P0 | 2 |
| US-11 | Sebagai **pengunjung**, saya ingin mencari putusan berdasarkan nomor perkara atau kata kunci. | P1 | 2 |
| US-12 | Sebagai **admin PPID**, saya ingin mengelola (tambah/edit/hapus) dokumen informasi publik melalui dashboard. | P0 | 1.5 |

### Epic 4: Laporan & Statistik

| ID | User Story | Prioritas | Estimasi |
|----|------------|-----------|----------|
| US-13 | Sebagai **admin PPID**, saya ingin mengexport laporan permohonan per bulan ke Excel/CSV, sehingga mudah melaporkan ke pimpinan. | P1 | 1 |
| US-14 | Sebagai **pimpinan pengadilan**, saya ingin melihat statistik real-time (jumlah permohonan, waktu respon rata-rata) di dashboard publik. | P1 | 1 |

---

## 2. Database Schema

### 2.1 Tabel `users` (untuk admin PPID)

| Kolom | Tipe | Deskripsi |
|-------|------|------------|
| id | INT (PK, auto_increment) | |
| name | VARCHAR(100) | Nama admin |
| email | VARCHAR(100) | Email login, unique |
| password | VARCHAR(255) | Hash bcrypt |
| role | ENUM('super_admin','ppid_staff') | Level akses |
| last_login_at | DATETIME | |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

### 2.2 Tabel `permohonan`

| Kolom | Tipe | Deskripsi |
|-------|------|------------|
| id | INT (PK) | |
| tiket_no | VARCHAR(30) | Format: PPID-YYYYMMDD-XXXX (unique index) |
| nik | VARCHAR(16) | NIK pemohon, index |
| nama_lengkap | VARCHAR(150) | |
| alamat | TEXT | |
| kota | VARCHAR(50) | |
| provinsi | VARCHAR(50) | |
| no_hp | VARCHAR(15) | |
| email | VARCHAR(100) | Untuk notifikasi |
| ktp_path | VARCHAR(255) | Path file KTP yang diupload |
| jenis_informasi | ENUM('salinan_putusan','laporan_kinerja','lainnya') | |
| nomor_perkara | VARCHAR(50) | Nullable, hanya jika jenis = salinan_putusan |
| tujuan | TEXT | Tujuan permohonan |
| uraian_informasi | TEXT | Deskripsi detail |
| status | ENUM('baru','diproses','selesai','ditolak') | Default 'baru' |
| catatan_admin | TEXT | Nullable, balasan ke pemohon |
| dokumen_balasan | VARCHAR(255) | Path PDF hasil permohonan |
| alasan_tolak | TEXT | Nullable, jika status ditolak |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |
| processed_at | DATETIME | Nullable, saat status berubah menjadi diproses |
| completed_at | DATETIME | Nullable, saat status selesai/ditolak |

### 2.3 Tabel `keberatan`

| Kolom | Tipe | Deskripsi |
|-------|------|------------|
| id | INT (PK) | |
| permohonan_id | INT (FK ke permohonan.id) | Tiket yang dikeberatkan |
| nama_pemohon | VARCHAR(150) | |
| alasan | TEXT | Alasan keberatan |
| status | ENUM('dikirim','diproses','selesai') | Default 'dikirim' |
| tanggapan_admin | TEXT | Nullable |
| created_at | TIMESTAMP | |
| resolved_at | DATETIME | Nullable |

### 2.4 Tabel `informasi_publik`

| Kolom | Tipe | Deskripsi |
|-------|------|------------|
| id | INT (PK) | |
| judul | VARCHAR(200) | Contoh: "Putusan Nomor 123/Pdt.G/2026" |
| kategori | ENUM('berkala','serta_merta','setiap_saat') | |
| sub_kategori | VARCHAR(50) | 'putusan', 'laporan_keuangan', 'renstra', dll |
| deskripsi | TEXT | |
| file_path | VARCHAR(255) | Path file PDF |
| tahun | YEAR | Tahun publikasi, untuk filtering |
| nomor_perkara | VARCHAR(50) | Nullable, khusus putusan |
| is_published | BOOLEAN | Default true |
| published_at | DATETIME | |
| created_at | TIMESTAMP | |

### 2.5 Tabel `status_log` (Riwayat perubahan status permohonan)

| Kolom | Tipe | Deskripsi |
|-------|------|------------|
| id | INT (PK) | |
| permohonan_id | INT (FK) | |
| status_lama | VARCHAR(20) | |
| status_baru | VARCHAR(20) | |
| catatan | TEXT | Nullable, opsional |
| created_by | INT (FK ke users.id) | Admin yang mengubah |
| created_at | TIMESTAMP | |

### 2.6 Tabel `faq`

| Kolom | Tipe |
|-------|------|
| id | INT PK |
| pertanyaan | TEXT |
| jawaban | TEXT |
| urutan | INT | Default 0 |
| is_active | BOOLEAN |

### 2.7 Relasi Diagram (ERD)

```
users (1) ----< (N) status_log
permohonan (1) ----< (N) status_log
permohonan (1) ---- (0..1) keberatan
permohonan (1) ----< (N) status_log
informasi_publik (independen)
faq (independen)
```

**Indeks yang direkomendasikan:**  
- `permohonan.tiket_no` (unique)  
- `permohonan.nik` (index)  
- `permohonan.status` (index)  
- `permohonan.created_at` (index untuk sorting)  
- `informasi_publik.kategori`, `tahun`  
- `status_log.permohonan_id`  

---

## 3. API Specification (RESTful)

**Base URL:** `https://api.ppid.pa-penajam.go.id/v1`  
**Format Request/Response:** JSON  
**Authentication:** Bearer token (untuk endpoint admin)  
**Timezone:** Asia/Makassar (WITA)

### 3.1 Endpoint Publik (Tanpa Auth)

#### 3.1.1 Submit Permohonan
- **URL:** `POST /permohonan`
- **Request Body:**
```json
{
  "nik": "6472010101010001",
  "nama_lengkap": "Ahmad Ridwan",
  "alamat": "Jl. Merdeka No. 45, Penajam",
  "kota": "Penajam Paser Utara",
  "provinsi": "Kalimantan Timur",
  "no_hp": "081234567890",
  "email": "ahmad@example.com",
  "ktp_base64": "data:image/jpeg;base64,...", // opsional, max 2MB
  "jenis_informasi": "salinan_putusan",
  "nomor_perkara": "123/Pdt.G/2026/PA.Pjm", // jika jenis = salinan_putusan
  "tujuan": "Keperluan banding",
  "uraian_informasi": "Saya mohon salinan putusan cerai..."
}
```
- **Response 201:**
```json
{
  "status": "success",
  "data": {
    "tiket_no": "PPID-20260609-001",
    "status": "baru",
    "created_at": "2026-06-09T10:30:00+08:00"
  }
}
```
- **Response 400 (validasi):**
```json
{
  "status": "error",
  "errors": {
    "nik": ["NIK harus 16 digit angka"],
    "email": ["Format email tidak valid"]
  }
}
```
- **Response 429:** `{"status": "error", "message": "Too many requests. Try again in 1 hour."}`

#### 3.1.2 Cek Status Permohonan
- **URL:** `GET /permohonan/{tiket_no}`
- **Response 200:**
```json
{
  "status": "success",
  "data": {
    "tiket_no": "PPID-20260609-001",
    "status": "diproses",
    "created_at": "2026-06-09T10:30:00+08:00",
    "processed_at": "2026-06-09T14:20:00+08:00",
    "catatan_admin": "Sedang diverifikasi oleh panitera",
    "dokumen_balasan_url": null,
    "riwayat": [
      {"status": "baru", "created_at": "2026-06-09T10:30:00", "catatan": "Permohonan diterima"},
      {"status": "diproses", "created_at": "2026-06-09T14:20:00", "catatan": "Diverifikasi PPID"}
    ]
  }
}
```
- **Response 404:** `{"status": "error", "message": "Tiket tidak ditemukan"}`

#### 3.1.3 Daftar Informasi Publik
- **URL:** `GET /informasi-publik?kategori=berkala&tahun=2025&page=1&limit=10`
- **Response 200:**
```json
{
  "status": "success",
  "data": {
    "items": [
      {
        "id": 1,
        "judul": "Putusan Nomor 123/Pdt.G/2026/PA.Pjm",
        "kategori": "berkala",
        "sub_kategori": "putusan",
        "tahun": 2026,
        "file_url": "https://.../putusan_123.pdf",
        "published_at": "2026-05-01"
      }
    ],
    "pagination": {"current_page": 1, "total": 45}
  }
}
```

#### 3.1.4 Submit Keberatan
- **URL:** `POST /keberatan`
- **Request Body:**
```json
{
  "permohonan_tiket": "PPID-20260609-001",
  "nama_pemohon": "Ahmad Ridwan",
  "alasan": "Saya tidak setuju dengan penolakan karena ..."
}
```
- **Response 201:**
```json
{
  "status": "success",
  "message": "Keberatan telah direkam. Petugas akan menghubungi Anda maksimal 3 hari."
}
```

#### 3.1.5 FAQ
- **URL:** `GET /faq`
- **Response 200:** `{"status": "success", "data": [{"id":1,"pertanyaan":"...","jawaban":"..."}]}`

### 3.2 Endpoint Admin (Bearer Token)

Semua endpoint admin membutuhkan header `Authorization: Bearer <token>`.

#### 3.2.1 Login Admin
- **URL:** `POST /auth/login`
- **Request:** `{"email": "admin@pa-penajam.go.id", "password": "..."}`
- **Response 200:** `{"token": "eyJ0eXAiOiJKV1Qi...", "user": {"name":"Lina Marlina","role":"ppid_staff"}}`

#### 3.2.2 Daftar Semua Permohonan (dengan filter)
- **URL:** `GET /admin/permohonan?status=baru&page=1&limit=20`
- **Response:** List permohonan dengan data lengkap pemohon (tanpa KTP base64).

#### 3.2.3 Update Status Permohonan
- **URL:** `PUT /admin/permohonan/{tiket_no}/status`
- **Request Body:**
```json
{
  "status": "selesai",
  "catatan_admin": "Dokumen terlampir",
  "dokumen_base64": "data:application/pdf;base64,..." // jika selesai
}
```
- **Response 200:** `{"status": "updated"}`

#### 3.2.4 Tambah/Edit/Hapus Informasi Publik
- **URL:** `POST /admin/informasi-publik`, `PUT /admin/informasi-publik/{id}`, `DELETE /admin/informasi-publik/{id}`

#### 3.2.5 Export Laporan Permohonan (Excel)
- **URL:** `GET /admin/laporan/permohonan?bulan=2026-05&format=excel`
- **Response:** File `.xlsx` download.

#### 3.2.6 Statistik Dashboard
- **URL:** `GET /admin/statistik`
- **Response:**
```json
{
  "total_permohonan_bulan_ini": 24,
  "sedang_diproses": 3,
  "selesai_bulan_ini": 21,
  "rata_rata_waktu_respon_hari": 2.4,
  "permohonan_per_bulan": [{"bulan":"2026-01","total":12}, ...]
}
```

### 3.3 Webhook / Notifikasi Email
- Sistem akan mengirim email secara async via queue (Laravel queue / Bull) ke pemohon ketika:
  - Submit berhasil (kirim tiket)
  - Status berubah (baru → diproses → selesai/ditolak)
- Gunakan template email yang responsif.

---

## 4. Deployment & Environment Variables

### 4.1 Environment (.env)
```ini
APP_NAME="PPID PA Penajam"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://ppid.pa-penajam.go.id

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ppid_pa
DB_USERNAME=ppid_user
DB_PASSWORD=securepass

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=ppid@pa-penajam.go.id
MAIL_PASSWORD=app_password
MAIL_ENCRYPTION=tls

RATE_LIMIT_PER_HOUR=3
JWT_SECRET=random_string
```

### 4.2 Folder Storage
```
storage/
  uploads/
    ktp/        # file KTP (dihapus otomatis setelah 30 hari)
    dokumen/    # file balasan PDF
    informasi_publik/   # file PDF informasi berkala
```

---

## 5. Testing & Quality Assurance

| Jenis Test | Tools | Cakupan Minimal |
|------------|-------|----------------|
| Unit Test (models, helpers) | PHPUnit / Jest | 80% |
| Integration Test (API endpoints) | Postman/Newman, Supertest | Semua endpoint kritis |
| Load Test | K6 | 100 concurrent users, response time < 2s |
| Security Test | OWASP ZAP | SQL injection, XSS, CSRF, rate limiting |

---

## 6. API Documentation (OpenAPI 3.0)

File `openapi.yaml` akan disediakan terpisah. Berikut cuplikan:

```yaml
paths:
  /permohonan:
    post:
      summary: Submit permohonan informasi
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/PermohonanInput'
      responses:
        '201':
          description: Berhasil, mengembalikan tiket
```

---
