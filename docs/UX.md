## 1. Event Tracking (Analytics & User Behavior)

Gunakan **Google Analytics 4 (GA4)** atau **Matomo**. Semua event dikirim via `dataLayer.push()` atau fungsi custom `trackEvent()`.

### 1.1 Daftar Event yang Harus Dilacak

| Kategori Event | Action | Label | Trigger | Contoh Payload |
|----------------|--------|-------|---------|----------------|
| `navigation` | `click_menu` | `{menu_name}` | Klik setiap item navigasi (Beranda, Profil, dll) | `{ menu_name: "Permohonan Informasi" }` |
| `cta_button` | `click_ajukan` | `hero` atau `form_page` | Klik tombol "Ajukan Permohonan" | `{ location: "hero_banner" }` |
| `cta_button` | `click_cek_status` | `hero` atau `status_page` | Klik tombol "Cek Status" | `{ location: "hero_banner" }` |
| `form_interaction` | `start_permohonan` | - | Pengguna mulai mengisi form (fokus input pertama) | `{ timestamp }` |
| `form_interaction` | `submit_permohonan` | `success` / `error` | Submit form permohonan | `{ status: "success", ticket_id: "PPID-..." }` |
| `status_check` | `search_tiket` | `found` / `not_found` | Klik "Cek Status" setelah input tiket | `{ result: "found" }` |
| `download` | `download_dokumen` | `{dokumen_type}` | Klik unduh putusan, laporan, atau arsip | `{ doc_type: "putusan", doc_id: "123" }` |
| `admin_action` | `update_status` | `{old_status} -> {new_status}` | Admin ubah status permohonan (dashboard) | `{ ticket_id: "PPID-...", from: "baru", to: "diproses" }` |
| `error` | `form_validation` | `{field_name}` | Validasi form gagal (NIK, email, dll) | `{ field: "nik", reason: "invalid_length" }` |
| `error` | `api_failed` | `{endpoint}` | Request API gagal (timeout, 500) | `{ endpoint: "/submit-permohonan", status: 500 }` |
| `engagement` | `faq_click` | `{faq_question_id}` | Klik "Lihat semua FAQ" atau ekspansi jawaban | `{ faq_id: 1 }` |
| `engagement` | `share_media` | `{platform}` | Klik tombol share ke FB, WA, Twitter | `{ platform: "whatsapp" }` |

### 1.2 Implementasi Kode (Contoh)

```javascript
// utils/tracking.js
export function trackEvent(category, action, label, value = null) {
  if (typeof gtag !== 'undefined') {
    gtag('event', action, {
      event_category: category,
      event_label: label,
      value: value
    });
  }
  // Fallback ke console log di development
  console.log(`[GA] ${category} | ${action} | ${label}`, value);
}

// Contoh penggunaan di tombol
<button onClick={() => {
  trackEvent('cta_button', 'click_ajukan', 'hero_banner');
  // ... logic redirect
}}>Ajukan Permohonan</button>
```

---

## 2. Micro-interactions (Animasi & Feedback Halus)

Semua durasi transisi: **200–300ms**, easing: `ease-in-out` atau `cubic-bezier(0.2, 0.9, 0.4, 1.1)`.

### 2.1 Hover & Focus State

| Elemen | Efek | CSS |
|--------|------|-----|
| Tombol Primary (hijau) | Scale 1.02, bayangan membesar | `transform: scale(1.02); box-shadow: 0 6px 14px rgba(0,0,0,0.1);` |
| Tombol CTA (orange) | Scale 1.02 + background lebih terang | `background: #EF6C00; transform: scale(1.02);` |
| Tombol Secondary (ungu outline) | Background ungu 10%, border tetap | `background: rgba(106,27,154,0.1);` |
| Card Informasi | Angkat sedikit (translateY -2px) | `transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,0.08);` |
| Input field | Border berubah warna hijau + shadow hijau tipis | `border-color: #1B5E20; box-shadow: 0 0 0 2px rgba(27,94,32,0.2);` |

### 2.2 Loading & Progress Indikator

**a) Saat Submit Form Permohonan**  
- Tombol berubah teks menjadi *"Mengirim..."* + spinner SVG kecil (warna orange).  
- Spinner berputar infinite.  
- Input lain di-disable (disabled) sampai response datang.

```html
<button id="submitBtn" class="btn-orange">
  <span class="btn-text">Ajukan Permohonan</span>
  <span class="spinner hidden"></span>
</button>
```

**b) Progress Bar Multi-step** (Form Permohonan)  
- Progress bar hijau diisi secara horizontal sesuai langkah (1/3, 2/3, 3/3).  
- Transisi lebar 300ms.

```css
.progress-fill {
  width: 0%;
  transition: width 0.3s ease-in-out;
  background: #1B5E20;
}
```

### 2.3 Notifikasi (Toast) – Desain & Animasi

- Muncul dari **pojok kanan atas**, slide-in dari kanan, opacity 0→1.  
- Hilang otomatis setelah 5 detik, slide-out ke kanan.  
- Warna background sesuai jenis: sukses (hijau), error (orange), info (ungu), warning (emas).

```css
.toast {
  position: fixed;
  top: 20px;
  right: 20px;
  animation: slideIn 0.3s ease forwards;
}
@keyframes slideIn {
  from { transform: translateX(100%); opacity: 0; }
  to { transform: translateX(0); opacity: 1; }
}
```

### 2.4 Efek Skeleton Loading

Saat halaman pertama kali dimuat (statistik, daftar permohonan admin):  
- Tampilkan skeleton abu-abu dengan shimmer effect (gradien bergerak).

```css
.skeleton {
  background: linear-gradient(90deg, #e0e0e0 25%, #f0f0f0 50%, #e0e0e0 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}
@keyframes shimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }
```

### 2.5 Micro-feedback pada Validasi Inline

Ketika pengguna meninggalkan field (blur), validasi dijalankan:  
- **Sukses:** ikon centang hijau muncul di sebelah kanan input.  
- **Error:** border merah, ikon seru merah, pesan error di bawah field.  
- Animasi getar (shake) pada field error.

```css
.input-error {
  border-color: #F57C00; /* orange */
  animation: shake 0.3s ease-in-out 0s 2;
}
@keyframes shake {
  0%,100% { transform: translateX(0); }
  25% { transform: translateX(-4px); }
  75% { transform: translateX(4px); }
}
```

---

## 3. Error Handling (User-Friendly)

### 3.1 Validasi Form Front-End (Sebelum Submit)

| Field | Aturan | Pesan Error (warna orange, font 14px) |
|-------|--------|----------------------------------------|
| NIK | 16 digit angka | "NIK harus 16 digit angka" |
| Nama Lengkap | Minimal 3 karakter, tidak boleh angka saja | "Nama lengkap tidak valid" |
| Email | Format email standar | "Masukkan alamat email yang benar (contoh: nama@domain.com)" |
| No. HP | Minimal 10, maksimal 13 digit | "Nomor HP tidak valid" |
| Upload KTP | Ukuran max 2MB, tipe jpg/png | "File terlalu besar (maks 2MB) atau format salah" |
| Uraian Informasi | Minimal 10 karakter | "Harap berikan uraian yang jelas (minimal 10 karakter)" |

**Implementasi contoh (NIK):**
```javascript
function validateNIK(value) {
  if (!/^\d{16}$/.test(value)) {
    showError('nik', 'NIK harus 16 digit angka');
    return false;
  }
  clearError('nik');
  return true;
}
```

### 3.2 Error API / Server

**Skenario & Tampilan ke User:**

| HTTP Status | Pesan yang Ditampilkan (toast error) | Aksi |
|-------------|----------------------------------------|------|
| 400 (Bad Request) | "Data yang dikirim tidak lengkap. Periksa kembali." | Kembali ke form, data tetap terisi. |
| 401 (Unauthorized) | "Sesi Anda habis. Silakan login ulang." | Redirect ke halaman login admin. |
| 404 (Not Found) | "Nomor tiket tidak ditemukan." | Tampilkan di halaman Cek Status (bukan toast). |
| 422 (Validation Error) | Tampilkan setiap field error secara inline. | - |
| 500 (Internal Server) | "Terjadi gangguan pada server. Tim kami sedang memperbaiki. Coba lagi nanti." | Tombol retry muncul. |
| Timeout/Network Error | "Koneksi terputus. Periksa internet Anda." | Tombol "Coba Lagi" pada area yang gagal. |

**Contoh handler global:**
```javascript
async function submitForm(data) {
  try {
    const response = await fetch('/api/permohonan', { method: 'POST', body: data });
    if (!response.ok) throw new Error(`HTTP ${response.status}`);
    // sukses
  } catch (error) {
    if (error.message.includes('Failed to fetch')) {
      showToast('Koneksi bermasalah. Cek koneksi internet Anda.', 'error');
    } else if (error.message.includes('500')) {
      showToast('Server sibuk. Coba beberapa saat lagi.', 'error');
    } else {
      showToast('Terjadi kesalahan. Silakan ulangi.', 'error');
    }
    // Log error ke tracking
    trackEvent('error', 'api_failed', '/permohonan', error.message);
  }
}
```

### 3.3 Error pada Cek Status

Jika tiket tidak ditemukan, tampilkan di **dalam area hasil** (bukan toast):

```
+---------------------------------------------+
| ⚠️  Tiket tidak ditemukan.                  |
| Pastikan nomor tiket benar (format: PPID-...)|
| Hubungi helpdesk: 0542-123456              |
+---------------------------------------------+
```

### 3.4 Fallback untuk Konten Dinamis

Jika daftar informasi publik gagal dimuat, tampilkan skeleton + tombol "Coba Muat Ulang".

```html
<div class="error-fallback">
  <span>🔌 Gagal memuat data.</span>
  <button class="btn-retry" onclick="loadInformasiPublik()">Muat Ulang</button>
</div>
```

### 3.5 Rate Limiting & Spam Protection

- Form permohonan: maksimal **3 submit per IP per jam**.  
- Jika melebihi, tampilkan pesan: *"Anda telah mencapai batas pengajuan. Silakan coba lagi 1 jam kemudian atau hubungi petugas."*  
- Implementasi di backend, tapi front-end menangkap error 429 dan menampilkan toast.

---

## 4. Aksesibilitas (WCAG 2.1 AA) – Ringkasan untuk Developer

| Persyaratan | Implementasi |
|-------------|---------------|
| Fokus terlihat | Outline 2px `#FFC107` (emas) pada elemen interaktif, jangan dihilangkan. |
| ARIA labels | `aria-label` pada ikon tombol tanpa teks. |
| Skip to content | Tautan tersembunyi di awal halaman: "Langsung ke konten utama". |
| Pesan error | Terhubung dengan input via `aria-describedby`. |
| Status toast | Gunakan `role="status"` dan `aria-live="polite"`. |

Contoh pesan error aksesibel:
```html
<div class="error-message" id="error-nik" role="alert">NIK harus 16 digit</div>
<input aria-describedby="error-nik" ... />
```

---

## 5. Tabel Ringkas untuk Developer (Cheat Sheet)

| Aspek | Metode | Library Pendukung (opsional) |
|-------|--------|-------------------------------|
| Event tracking | Custom events + GA4 | `react-ga4`, `vue-gtag` |
| Animasi mikro | CSS transition/transform | Tailwind `transition` classes |
| Loading spinner | SVG atau CSS | `lucide-react`, `heroicons` |
| Toast notifikasi | Komponen stateful | `react-hot-toast`, `vue-toastification` |
| Validasi form | JavaScript native + regex | `VeeValidate`, `Formik` |
| Error boundary | React error boundary / Vue error handler | `react-error-boundary` |
| Skeleton loading | CSS + conditional rendering | `react-loading-skeleton` |

---

## 6. Contoh Implementasi Kode Gabungan (React + Tailwind)

```jsx
// FormPermohonan.jsx
import { trackEvent } from '../utils/tracking';
import { useState } from 'react';
import toast from 'react-hot-toast';

export default function FormPermohonan() {
  const [loading, setLoading] = useState(false);
  const [formData, setFormData] = useState({ nik: '', nama: '', email: '' });
  const [errors, setErrors] = useState({});

  const handleSubmit = async (e) => {
    e.preventDefault();
    // validasi front-end
    if (!validateForm()) return;
    setLoading(true);
    trackEvent('form_interaction', 'submit_permohonan', 'attempt');
    try {
      const res = await fetch('/api/permohonan', { method: 'POST', body: JSON.stringify(formData) });
      if (!res.ok) throw new Error('Submit failed');
      const data = await res.json();
      trackEvent('form_interaction', 'submit_permohonan', 'success', data.ticket);
      toast.success(`Permohonan berhasil! Nomor tiket: ${data.ticket}`);
      // reset form
    } catch (err) {
      trackEvent('error', 'api_failed', '/permohonan');
      toast.error('Gagal mengirim. Coba lagi.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-4">
      <input
        type="text"
        placeholder="NIK"
        className={`border rounded p-2 ${errors.nik ? 'border-orange-500' : 'border-gray-300'}`}
        onChange={(e) => setFormData({...formData, nik: e.target.value})}
        onBlur={() => validateNIK(formData.nik, setErrors)}
      />
      {errors.nik && <p className="text-orange-600 text-sm">{errors.nik}</p>}
      <button
        type="submit"
        disabled={loading}
        className="bg-orange-500 hover:bg-orange-600 text-white px-6 py-2 rounded transition-transform transform hover:scale-105"
      >
        {loading ? <Spinner /> : 'Ajukan Permohonan'}
      </button>
    </form>
  );
}
```