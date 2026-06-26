/**
 * Property-Based Test untuk validasi form Portal PPID.
 * Menggunakan Vitest + fast-check untuk memverifikasi bahwa
 * aturan validasi menolak input tidak valid dan menerima input valid.
 *
 * **Validates: Requirements 6.3, 6.4**
 */
import * as fc from 'fast-check';
import { describe, expect, it } from 'vitest';

import { rules, validateField } from '@/lib/validation';

/**
 * Helper: Membuat generator string dari array karakter dengan panjang tertentu.
 * Menggantikan fc.stringOf yang tidak tersedia di fast-check v4.
 */
function stringFromChars(chars: string[], minLength: number, maxLength: number): fc.Arbitrary<string> {
    return fc.array(fc.constantFrom(...chars), { minLength, maxLength }).map((arr) => arr.join(''));
}

// Karakter digit
const DIGITS = '0123456789'.split('');
// Karakter huruf kecil
const LOWERCASE = 'abcdefghijklmnopqrstuvwxyz'.split('');
// Karakter huruf + spasi
const ALPHA_SPACE = 'abcdefghijklmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split('');

describe('Property 1: Validasi form menolak input tidak valid dan mengembalikan pesan error yang tepat', () => {
    // --- NIK ---
    describe('NIK', () => {
        it('menerima string 16 digit angka', () => {
            fc.assert(
                fc.property(
                    // Generator: string tepat 16 karakter digit
                    stringFromChars(DIGITS, 16, 16),
                    (nik) => {
                        const result = validateField('nik', nik);
                        expect(result.valid).toBe(true);
                        expect(result.message).toBe('');
                    },
                ),
                { numRuns: 100 },
            );
        });

        it('menolak string yang bukan 16 digit angka', () => {
            fc.assert(
                fc.property(
                    // Generator: string sembarang yang TIDAK cocok dengan pola 16 digit
                    fc.string().filter((s) => !/^\d{16}$/.test(s)),
                    (invalidNik) => {
                        const result = validateField('nik', invalidNik);
                        expect(result.valid).toBe(false);
                        expect(result.message).toBe(rules.nik.message);
                    },
                ),
                { numRuns: 100 },
            );
        });
    });

    // --- Email ---
    describe('Email', () => {
        it('menerima email dengan format valid (local@domain.ext)', () => {
            fc.assert(
                fc.property(
                    // Generator: komponen email yang valid
                    fc.tuple(
                        // local part: minimal 1 karakter alfanumerik
                        stringFromChars([...LOWERCASE, ...DIGITS], 1, 10),
                        // domain: minimal 1 karakter alfanumerik
                        stringFromChars([...LOWERCASE, ...DIGITS], 1, 8),
                        // extension: minimal 2 karakter huruf
                        stringFromChars(LOWERCASE, 2, 4),
                    ),
                    ([local, domain, ext]) => {
                        const email = `${local}@${domain}.${ext}`;
                        const result = validateField('email', email);
                        expect(result.valid).toBe(true);
                        expect(result.message).toBe('');
                    },
                ),
                { numRuns: 100 },
            );
        });

        it('menolak string tanpa @ atau tanpa domain yang benar', () => {
            fc.assert(
                fc.property(
                    fc.oneof(
                        // String tanpa karakter @
                        fc.string().filter((s) => !s.includes('@')),
                        // String hanya dengan @ tapi tanpa bagian setelahnya yang valid
                        fc.tuple(fc.string({ minLength: 1 }), fc.constantFrom('', '.', 'a', '.b')).map(
                            ([local, domain]) => `${local.replace(/@/g, '')}@${domain}`,
                        ),
                    ),
                    (invalidEmail) => {
                        // Pastikan input memang tidak lolos validasi regex
                        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(invalidEmail)) {
                            const result = validateField('email', invalidEmail);
                            expect(result.valid).toBe(false);
                            expect(result.message).toBe(rules.email.message);
                        }
                    },
                ),
                { numRuns: 100 },
            );
        });
    });

    // --- No HP ---
    describe('No HP', () => {
        it('menerima string 10-13 digit angka', () => {
            fc.assert(
                fc.property(
                    // Generator: string digit dengan panjang 10-13
                    fc.integer({ min: 10, max: 13 }).chain((len) => stringFromChars(DIGITS, len, len)),
                    (noHp) => {
                        const result = validateField('noHp', noHp);
                        expect(result.valid).toBe(true);
                        expect(result.message).toBe('');
                    },
                ),
                { numRuns: 100 },
            );
        });

        it('menolak string dengan karakter non-digit atau panjang salah', () => {
            fc.assert(
                fc.property(
                    fc.string().filter((s) => !/^\d{10,13}$/.test(s)),
                    (invalidNoHp) => {
                        const result = validateField('noHp', invalidNoHp);
                        expect(result.valid).toBe(false);
                        expect(result.message).toBe(rules.noHp.message);
                    },
                ),
                { numRuns: 100 },
            );
        });
    });

    // --- Nama Lengkap ---
    describe('Nama Lengkap', () => {
        it('menerima string >= 3 karakter yang bukan hanya digit', () => {
            fc.assert(
                fc.property(
                    // Generator: string alfabet+spasi minimal 3 karakter (bukan hanya digit)
                    stringFromChars(ALPHA_SPACE, 3, 50).filter((s) => s.trim().length >= 3),
                    (nama) => {
                        const result = validateField('namaLengkap', nama);
                        expect(result.valid).toBe(true);
                        expect(result.message).toBe('');
                    },
                ),
                { numRuns: 100 },
            );
        });

        it('menolak string < 3 karakter setelah trim', () => {
            fc.assert(
                fc.property(
                    // Generator: string pendek (0-2 karakter setelah trim)
                    stringFromChars(LOWERCASE, 0, 2),
                    (shortNama) => {
                        const result = validateField('namaLengkap', shortNama);
                        expect(result.valid).toBe(false);
                        expect(result.message).toBe(rules.namaLengkap.message);
                    },
                ),
                { numRuns: 100 },
            );
        });

        it('menolak string yang hanya berisi digit', () => {
            fc.assert(
                fc.property(
                    // Generator: string digit minimal 3 karakter
                    stringFromChars(DIGITS, 3, 20),
                    (digitOnly) => {
                        const result = validateField('namaLengkap', digitOnly);
                        expect(result.valid).toBe(false);
                        expect(result.message).toBe(rules.namaLengkap.message);
                    },
                ),
                { numRuns: 100 },
            );
        });
    });

    // --- Uraian Informasi ---
    describe('Uraian Informasi', () => {
        it('menerima string >= 10 karakter setelah trim', () => {
            fc.assert(
                fc.property(
                    // Generator: string alfanumerik minimal 10 karakter
                    fc.string({ minLength: 10, maxLength: 200 }).filter((s) => s.trim().length >= 10),
                    (uraian) => {
                        const result = validateField('uraianInformasi', uraian);
                        expect(result.valid).toBe(true);
                        expect(result.message).toBe('');
                    },
                ),
                { numRuns: 100 },
            );
        });

        it('menolak string < 10 karakter setelah trim', () => {
            fc.assert(
                fc.property(
                    // Generator: string pendek (0-9 karakter setelah trim)
                    stringFromChars([...LOWERCASE, ' '], 0, 9).filter((s) => s.trim().length < 10),
                    (shortUraian) => {
                        const result = validateField('uraianInformasi', shortUraian);
                        expect(result.valid).toBe(false);
                        expect(result.message).toBe(rules.uraianInformasi.message);
                    },
                ),
                { numRuns: 100 },
            );
        });
    });

    // --- validateField: pesan error dan field tidak dikenal ---
    describe('validateField', () => {
        it('mengembalikan valid: true dan pesan kosong untuk field yang tidak dikenal', () => {
            fc.assert(
                fc.property(
                    // Generator: nama field yang pasti tidak ada di rules
                    fc.string({ minLength: 1 }).filter((s) => !(s in rules)),
                    fc.anything(),
                    (unknownField, value) => {
                        const result = validateField(unknownField, value);
                        expect(result.valid).toBe(true);
                        expect(result.message).toBe('');
                    },
                ),
                { numRuns: 100 },
            );
        });

        it('mengembalikan pesan error yang tepat saat validasi gagal', () => {
            fc.assert(
                fc.property(
                    // Generator: pilih salah satu field yang ada
                    fc.constantFrom('nik', 'email', 'noHp', 'namaLengkap', 'uraianInformasi'),
                    (fieldName) => {
                        // Gunakan empty string sebagai input tidak valid untuk semua field
                        const result = validateField(fieldName, '');
                        expect(result.valid).toBe(false);
                        expect(result.message).toBe(rules[fieldName].message);
                    },
                ),
                { numRuns: 100 },
            );
        });
    });
});
