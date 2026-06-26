/**
 * Property-Based Test untuk rendering item informasi publik.
 * Memverifikasi bahwa setiap item menampilkan judul, kategori, tahun,
 * dan tautan unduh saat di-render.
 *
 * **Validates: Requirements 5.3**
 */
// @vitest-environment jsdom
import { cleanup, render } from '@testing-library/react';
import * as fc from 'fast-check';
import { afterEach, describe, expect, it, vi } from 'vitest';

import type { InformasiPublik, KategoriInformasi } from '@/types/ppid';

// Mock module Inertia agar tidak error saat render
vi.mock('@inertiajs/react', () => ({
    Head: ({ children }: { children?: React.ReactNode }) => <>{children}</>,
    Link: ({ href, children, ...props }: { href: string; children?: React.ReactNode }) => (
        <a href={href} {...props}>
            {children}
        </a>
    ),
    router: { get: vi.fn() },
}));

// Mock tracking agar tidak mengganggu test
vi.mock('@/lib/tracking', () => ({
    trackEvent: vi.fn(),
}));

// Import komponen setelah mock terdaftar
import InformasiPublikIndex from '@/pages/public/informasi-publik';

// Bersihkan DOM setelah setiap test
afterEach(() => {
    cleanup();
});

// Mapping label kategori sesuai implementasi komponen
const KATEGORI_LABELS: Record<KategoriInformasi, string> = {
    berkala: 'Berkala',
    serta_merta: 'Serta Merta',
    setiap_saat: 'Setiap Saat',
};

/**
 * Generator arbitrary untuk objek InformasiPublik valid.
 * Memastikan judul non-empty, kategori valid, tahun valid, dan file_url ada.
 */
const informasiPublikArbitrary: fc.Arbitrary<InformasiPublik> = fc.record({
    id: fc.integer({ min: 1, max: 99999 }),
    judul: fc.string({ minLength: 1, maxLength: 100 }).filter((s) => s.trim().length > 0),
    kategori: fc.constantFrom<KategoriInformasi>('berkala', 'serta_merta', 'setiap_saat'),
    sub_kategori: fc.string({ minLength: 0, maxLength: 30 }),
    deskripsi: fc.string({ minLength: 0, maxLength: 200 }),
    file_path: fc.constant('/storage/files/doc.pdf'),
    file_url: fc.webUrl(),
    tahun: fc.integer({ min: 2000, max: 2099 }),
    nomor_perkara: fc.option(fc.string({ minLength: 5, maxLength: 20 }), { nil: null }),
    is_published: fc.constant(true),
    published_at: fc.constant('2024-01-01T00:00:00.000Z'),
    created_at: fc.constant('2024-01-01T00:00:00.000Z'),
});

describe('Property 2: Rendering item informasi publik menampilkan semua field yang diperlukan', () => {
    it('menampilkan judul, kategori, tahun, dan tautan unduh untuk setiap item', () => {
        fc.assert(
            fc.property(informasiPublikArbitrary, (item) => {
                // Render komponen halaman dengan satu item informasi publik
                const { container } = render(
                    <InformasiPublikIndex
                        informasi={{
                            items: [item],
                            pagination: { current_page: 1, last_page: 1, per_page: 10, total: 1 },
                        }}
                        filters={{}}
                        tahunList={[item.tahun]}
                    />,
                );

                // Verifikasi: judul ditampilkan dalam elemen h3
                const headings = container.querySelectorAll('h3');
                const judulFound = Array.from(headings).some((h3) => h3.textContent?.includes(item.judul));
                expect(judulFound).toBe(true);

                // Verifikasi: label kategori ditampilkan
                const expectedLabel = KATEGORI_LABELS[item.kategori];
                expect(container.textContent).toContain(expectedLabel);

                // Verifikasi: tahun ditampilkan
                expect(container.textContent).toContain(String(item.tahun));

                // Verifikasi: tautan unduh ada dengan href yang benar
                // Menggunakan querySelectorAll karena querySelector tidak mendukung karakter spesial di URL
                const allLinks = container.querySelectorAll('a');
                const downloadLink = Array.from(allLinks).find(
                    (link) => link.getAttribute('href') === item.file_url,
                );
                expect(downloadLink).toBeDefined();

                // Bersihkan setelah setiap iterasi
                cleanup();
            }),
            { numRuns: 100 },
        );
    });
});
