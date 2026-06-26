/**
 * Aturan validasi client-side untuk form Portal PPID.
 * Digunakan untuk validasi inline (on blur) dan sebelum submit.
 */

/** Interface untuk setiap aturan validasi */
export interface ValidationRule {
    validate: (value: unknown) => boolean;
    message: string;
}

/** Hasil dari fungsi validateField */
export interface ValidationResult {
    valid: boolean;
    message: string;
}

/** Kumpulan aturan validasi per field */
export const rules: Record<string, ValidationRule> = {
    nik: {
        validate: (v: unknown) => typeof v === 'string' && /^\d{16}$/.test(v),
        message: 'NIK harus 16 digit angka',
    },
    email: {
        validate: (v: unknown) => typeof v === 'string' && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v),
        message: 'Masukkan alamat email yang benar (contoh: nama@domain.com)',
    },
    noHp: {
        validate: (v: unknown) => typeof v === 'string' && /^\d{10,13}$/.test(v),
        message: 'Nomor HP tidak valid (10-13 digit)',
    },
    namaLengkap: {
        validate: (v: unknown) => typeof v === 'string' && v.trim().length >= 3 && !/^\d+$/.test(v.trim()),
        message: 'Nama lengkap tidak valid',
    },
    uraianInformasi: {
        validate: (v: unknown) => typeof v === 'string' && v.trim().length >= 10,
        message: 'Harap berikan uraian yang jelas (minimal 10 karakter)',
    },
    ktpFile: {
        validate: (value: unknown) => {
            // Validasi file KTP: hanya jpg/png, maksimal 2MB
            if (!(value instanceof File)) return false;
            const validTypes = ['image/jpeg', 'image/png'];
            return validTypes.includes(value.type) && value.size <= 2 * 1024 * 1024;
        },
        message: 'File terlalu besar (maks 2MB) atau format salah (hanya jpg/png)',
    },
};

/**
 * Memvalidasi satu field berdasarkan nama field dan nilainya.
 * Mengembalikan objek dengan status valid dan pesan error jika tidak valid.
 *
 * @param fieldName - Nama field yang akan divalidasi (harus sesuai key di rules)
 * @param value - Nilai yang akan divalidasi
 * @returns Hasil validasi berupa { valid, message }
 */
export function validateField(fieldName: string, value: unknown): ValidationResult {
    const rule = rules[fieldName];

    // Jika rule tidak ditemukan, anggap valid (field tanpa validasi khusus)
    if (!rule) {
        return { valid: true, message: '' };
    }

    const valid = rule.validate(value);

    return {
        valid,
        message: valid ? '' : rule.message,
    };
}
