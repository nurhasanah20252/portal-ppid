/**
 * Tipe data untuk domain Portal PPID Pengadilan Agama Penajam.
 * Berdasarkan spesifikasi database di spec-design.md.
 */

// ============================================================
// Enums
// ============================================================

export type StatusPermohonan = 'baru' | 'diproses' | 'selesai' | 'ditolak';

export type JenisInformasi = 'salinan_putusan' | 'laporan_kinerja' | 'lainnya';

export type KategoriInformasi = 'berkala' | 'serta_merta' | 'setiap_saat';

export type StatusKeberatan = 'dikirim' | 'diproses' | 'selesai';

export type RoleUser = 'super_admin' | 'ppid_staff';

// ============================================================
// Model: Permohonan
// ============================================================

export interface Permohonan {
    id: number;
    tiket_no: string;
    nik: string;
    nama_lengkap: string;
    alamat: string;
    kota: string;
    provinsi: string;
    no_hp: string;
    email: string;
    ktp_path: string | null;
    jenis_informasi: JenisInformasi;
    nomor_perkara: string | null;
    tujuan: string;
    uraian_informasi: string;
    status: StatusPermohonan;
    catatan_admin: string | null;
    dokumen_balasan: string | null;
    alasan_tolak: string | null;
    created_at: string;
    updated_at: string;
    processed_at: string | null;
    completed_at: string | null;
}

export interface PermohonanFormData {
    nik: string;
    nama_lengkap: string;
    alamat: string;
    kota: string;
    provinsi: string;
    no_hp: string;
    email: string;
    ktp_file?: File | null;
    jenis_informasi: JenisInformasi;
    nomor_perkara?: string;
    tujuan: string;
    uraian_informasi: string;
}

export interface StatusLog {
    id: number;
    permohonan_id: number;
    status_lama: string;
    status_baru: string;
    catatan: string | null;
    created_by: number | null;
    created_at: string;
}

export interface StatusCheckResult {
    tiket_no: string;
    status: StatusPermohonan;
    created_at: string;
    processed_at: string | null;
    completed_at: string | null;
    catatan_admin: string | null;
    dokumen_balasan_url: string | null;
    riwayat: StatusLog[];
}

// ============================================================
// Model: Keberatan
// ============================================================

export interface Keberatan {
    id: number;
    permohonan_id: number;
    nama_pemohon: string;
    alasan: string;
    status: StatusKeberatan;
    tanggapan_admin: string | null;
    created_at: string;
    resolved_at: string | null;
}

export interface KeberatanFormData {
    permohonan_tiket: string;
    nama_pemohon: string;
    alasan: string;
}

// ============================================================
// Model: Informasi Publik
// ============================================================

export interface InformasiPublik {
    id: number;
    judul: string;
    kategori: KategoriInformasi;
    sub_kategori: string;
    deskripsi: string;
    file_path: string | null;
    file_url: string | null;
    tahun: number;
    nomor_perkara: string | null;
    is_published: boolean;
    published_at: string | null;
    created_at: string;
}

// ============================================================
// Model: FAQ
// ============================================================

export interface Faq {
    id: number;
    pertanyaan: string;
    jawaban: string;
    urutan: number;
    is_active: boolean;
}

// ============================================================
// Model: User (Admin)
// ============================================================

export interface AdminUser {
    id: number;
    name: string;
    email: string;
    role: RoleUser;
    last_login_at: string | null;
}

// ============================================================
// Statistik
// ============================================================

export interface StatistikDashboard {
    total_permohonan_bulan_ini: number;
    sedang_diproses: number;
    selesai_bulan_ini: number;
    rata_rata_waktu_respon_hari: number;
    permohonan_per_bulan: Array<{ bulan: string; total: number }>;
}

// ============================================================
// Pagination
// ============================================================

export interface PaginationMeta {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

export interface PaginatedResponse<T> {
    items: T[];
    pagination: PaginationMeta;
}

// ============================================================
// API Response
// ============================================================

export interface ApiResponse<T> {
    status: 'success' | 'error';
    data?: T;
    message?: string;
    errors?: Record<string, string[]>;
}
