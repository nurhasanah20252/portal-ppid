// @vitest-environment jsdom
/**
 * Property-Based Test untuk server validation errors (422) pada form Portal PPID.
 * Memverifikasi bahwa setiap error dari server ditampilkan pada field yang sesuai
 * menggunakan pola InputError dengan id=`{fieldName}-error`.
 *
 * **Validates: Requirements 6.8**
 */
import { cleanup, render, screen } from '@testing-library/react';
import * as fc from 'fast-check';
import React from 'react';
import { afterEach, describe, expect, it } from 'vitest';

import InputError from '@/components/input-error';

// Daftar field yang bisa memiliki error dari server (response 422)
const KNOWN_FIELDS = [
    'nik',
    'email',
    'nama_lengkap',
    'no_hp',
    'alamat',
    'kota',
    'provinsi',
    'tujuan',
    'uraian_informasi',
    'jenis_informasi',
] as const;

type FieldName = (typeof KNOWN_FIELDS)[number];

/**
 * Komponen test yang mensimulasikan pola error display pada form permohonan.
 * Menerima objek errors (seperti yang dikembalikan oleh server 422 via Inertia)
 * dan menampilkan InputError untuk setiap field yang memiliki error.
 */
function ServerErrorDisplay({ errors }: { errors: Record<string, string> }) {
    return (
        <div>
            {Object.entries(errors).map(([fieldName, message]) => (
                <InputError key={fieldName} id={`${fieldName}-error`} message={message} />
            ))}
        </div>
    );
}

afterEach(() => {
    cleanup();
});

describe('Property 5: Server validation errors (422) ditampilkan pada field yang sesuai', () => {
    it('setiap error dari server ditampilkan dengan id dan pesan yang benar', () => {
        fc.assert(
            fc.property(
                // Generator: objek error dengan 1-10 field dari daftar field yang dikenal
                fc.uniqueArray(fc.constantFrom(...KNOWN_FIELDS), { minLength: 1, maxLength: 10 }).chain(
                    (fields) =>
                        fc.tuple(
                            fc.constant(fields),
                            // Generate pesan error non-kosong untuk setiap field
                            fc.array(
                                fc.string({ minLength: 3, maxLength: 100 }).filter((s) => s.trim().length > 0),
                                { minLength: fields.length, maxLength: fields.length },
                            ),
                        ),
                ),
                ([fields, messages]) => {
                    // Buat objek errors seperti yang dikembalikan oleh server 422
                    const errors: Record<string, string> = {};

                    fields.forEach((field, index) => {
                        errors[field] = messages[index];
                    });

                    // Render komponen yang menampilkan server errors
                    const { unmount } = render(<ServerErrorDisplay errors={errors} />);

                    // Verifikasi: setiap field yang memiliki error harus ditampilkan
                    for (const [fieldName, message] of Object.entries(errors)) {
                        // Cek elemen error ada di DOM dengan id yang benar
                        const errorElement = document.getElementById(`${fieldName}-error`);

                        expect(errorElement, `Elemen error untuk field "${fieldName}" harus ada di DOM`).not.toBeNull();

                        // Cek pesan error ditampilkan dengan benar
                        expect(
                            errorElement!.textContent,
                            `Pesan error untuk field "${fieldName}" harus sesuai`,
                        ).toBe(message);

                        // Cek role="alert" untuk aksesibilitas
                        expect(
                            errorElement!.getAttribute('role'),
                            `Error element untuk field "${fieldName}" harus memiliki role="alert"`,
                        ).toBe('alert');
                    }

                    // Bersihkan DOM setelah setiap iterasi
                    unmount();
                },
            ),
            { numRuns: 100 },
        );
    });

    it('field tanpa error tidak menampilkan elemen error di DOM', () => {
        fc.assert(
            fc.property(
                // Generator: subset field yang memiliki error (minimal 1)
                fc.uniqueArray(fc.constantFrom(...KNOWN_FIELDS), { minLength: 1, maxLength: 5 }),
                fc.uniqueArray(fc.constantFrom(...KNOWN_FIELDS), { minLength: 1, maxLength: 5 }),
                (fieldsWithError, fieldsToCheck) => {
                    // Buat objek errors hanya untuk subset field tertentu
                    const errors: Record<string, string> = {};

                    fieldsWithError.forEach((field) => {
                        errors[field] = `Error untuk ${field}`;
                    });

                    const { unmount } = render(<ServerErrorDisplay errors={errors} />);

                    // Verifikasi: field yang TIDAK ada di errors TIDAK menampilkan error
                    for (const field of fieldsToCheck) {
                        const errorElement = document.getElementById(`${field}-error`);

                        if (field in errors) {
                            // Field dengan error harus ada
                            expect(errorElement).not.toBeNull();
                        } else {
                            // Field tanpa error tidak boleh ada
                            expect(
                                errorElement,
                                `Field "${field}" tanpa error tidak boleh menampilkan elemen error`,
                            ).toBeNull();
                        }
                    }

                    unmount();
                },
            ),
            { numRuns: 100 },
        );
    });

    it('pesan error dari server ditampilkan apa adanya tanpa modifikasi', () => {
        fc.assert(
            fc.property(
                // Generator: satu field dengan pesan error yang bervariasi
                fc.constantFrom(...KNOWN_FIELDS),
                // Pesan error bisa berisi karakter khusus, angka, unicode, dll
                fc.string({ minLength: 1, maxLength: 200 }).filter((s) => s.trim().length > 0),
                (fieldName, errorMessage) => {
                    const errors: Record<string, string> = {
                        [fieldName]: errorMessage,
                    };

                    const { unmount } = render(<ServerErrorDisplay errors={errors} />);

                    const errorElement = document.getElementById(`${fieldName}-error`);

                    expect(errorElement).not.toBeNull();
                    // Pesan harus ditampilkan persis seperti yang diterima dari server
                    expect(errorElement!.textContent).toBe(errorMessage);

                    unmount();
                },
            ),
            { numRuns: 100 },
        );
    });
});
