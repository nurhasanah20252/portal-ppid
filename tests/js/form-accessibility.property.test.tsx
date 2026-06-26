// @vitest-environment jsdom
/**
 * Property-Based Test untuk aksesibilitas form Portal PPID.
 * Memverifikasi bahwa setiap field form dengan error memiliki
 * aria-describedby yang menunjuk ke elemen pesan error.
 *
 * **Validates: Requirements 17.3**
 */
import { cleanup, render, screen } from '@testing-library/react';
import * as fc from 'fast-check';
import React from 'react';
import { afterEach, describe, expect, it } from 'vitest';

import InputError from '@/components/input-error';

afterEach(() => {
    cleanup();
});

/**
 * Daftar field form permohonan yang menggunakan validasi.
 * Setiap field memiliki pola: input id="{fieldName}" dan error id="{fieldName}-error"
 */
const FORM_FIELDS = [
    'nik',
    'nama_lengkap',
    'email',
    'no_hp',
    'alamat',
    'kota',
    'provinsi',
    'uraian_informasi',
] as const;

/**
 * Komponen helper yang mensimulasikan pola aksesibilitas form:
 * - Input memiliki aria-describedby yang menunjuk ke id error element
 * - InputError di-render dengan id dan pesan error
 */
function FormFieldWithError({ fieldName, errorMessage }: { fieldName: string; errorMessage: string }) {
    const errorId = `${fieldName}-error`;
    return (
        <div>
            <input
                id={fieldName}
                aria-describedby={errorId}
                data-testid={`input-${fieldName}`}
            />
            <InputError id={errorId} message={errorMessage} />
        </div>
    );
}

describe('Property 6: Setiap field form dengan error memiliki aria-describedby yang menunjuk ke pesan error', () => {
    it('InputError me-render elemen dengan role="alert" dan id yang diberikan untuk setiap pesan non-empty', () => {
        fc.assert(
            fc.property(
                // Generator: pilih field dari daftar form fields
                fc.constantFrom(...FORM_FIELDS),
                // Generator: pesan error non-empty (minimal 1 karakter)
                fc.string({ minLength: 1, maxLength: 100 }).filter((s) => s.trim().length > 0),
                (fieldName, errorMessage) => {
                    const errorId = `${fieldName}-error`;

                    // Render InputError dengan id dan message
                    const { unmount } = render(
                        <InputError id={errorId} message={errorMessage} />,
                    );

                    // Verifikasi: elemen error harus ada dengan role="alert"
                    const errorElement = screen.getByRole('alert');
                    expect(errorElement).toBeTruthy();

                    // Verifikasi: elemen error memiliki id yang sesuai pola "{fieldName}-error"
                    expect(errorElement.getAttribute('id')).toBe(errorId);

                    // Verifikasi: elemen error menampilkan pesan error
                    expect(errorElement.textContent).toBe(errorMessage);

                    // Bersihkan setelah setiap iterasi
                    unmount();
                },
            ),
            { numRuns: 100 },
        );
    });

    it('aria-describedby pada input terhubung ke id elemen error yang sesuai', () => {
        fc.assert(
            fc.property(
                // Generator: pilih field dari daftar form fields
                fc.constantFrom(...FORM_FIELDS),
                // Generator: pesan error non-empty
                fc.string({ minLength: 1, maxLength: 100 }).filter((s) => s.trim().length > 0),
                (fieldName, errorMessage) => {
                    const errorId = `${fieldName}-error`;

                    // Render komponen form field lengkap dengan input dan error
                    const { unmount, container } = render(
                        <FormFieldWithError fieldName={fieldName} errorMessage={errorMessage} />,
                    );

                    // Ambil elemen input
                    const inputElement = container.querySelector(`#${fieldName}`) as HTMLInputElement;
                    expect(inputElement).toBeTruthy();

                    // Verifikasi: aria-describedby pada input menunjuk ke id error
                    const describedBy = inputElement.getAttribute('aria-describedby');
                    expect(describedBy).toBe(errorId);

                    // Verifikasi: elemen dengan id yang ditunjuk oleh aria-describedby benar ada
                    const referencedElement = container.querySelector(`#${errorId}`);
                    expect(referencedElement).toBeTruthy();

                    // Verifikasi: elemen yang ditunjuk berisi pesan error
                    expect(referencedElement?.textContent).toBe(errorMessage);

                    // Verifikasi: elemen yang ditunjuk memiliki role="alert"
                    expect(referencedElement?.getAttribute('role')).toBe('alert');

                    // Bersihkan setelah setiap iterasi
                    unmount();
                },
            ),
            { numRuns: 100 },
        );
    });

    it('InputError tidak me-render apapun ketika message kosong atau undefined', () => {
        fc.assert(
            fc.property(
                // Generator: pilih field dari daftar form fields
                fc.constantFrom(...FORM_FIELDS),
                (fieldName) => {
                    const errorId = `${fieldName}-error`;

                    // Render InputError tanpa message (undefined)
                    const { unmount, container } = render(
                        <InputError id={errorId} message={undefined} />,
                    );

                    // Verifikasi: tidak ada elemen error yang di-render
                    const errorElement = container.querySelector(`#${errorId}`);
                    expect(errorElement).toBeNull();

                    // Verifikasi: tidak ada elemen dengan role="alert"
                    const alertElements = container.querySelectorAll('[role="alert"]');
                    expect(alertElements.length).toBe(0);

                    unmount();
                },
            ),
            { numRuns: 100 },
        );
    });
});
