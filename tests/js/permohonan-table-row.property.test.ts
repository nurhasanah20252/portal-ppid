// @vitest-environment jsdom
/**
 * Property-Based Test untuk rendering baris tabel permohonan admin.
 * Memverifikasi bahwa setiap objek Permohonan yang valid, ketika di-render
 * sebagai baris tabel, menampilkan: nomor tiket, nama pemohon, jenis informasi,
 * badge status, dan tombol aksi "Proses".
 *
 * **Validates: Requirements 13.1**
 */
import { cleanup, render } from '@testing-library/react';
import * as fc from 'fast-check';
import React from 'react';
import { afterEach, describe, expect, it, vi } from 'vitest';

import { DataTable, type Column } from '@/components/data-table';
import StatusBadge from '@/components/status-badge';
import { Button } from '@/components/ui/button';
import type { JenisInformasi, Permohonan, StatusPermohonan } from '@/types/ppid';

// Mock Inertia router agar tidak error saat render Button dengan onClick
vi.mock('@inertiajs/react', () => ({
    Head: ({ children }: { children?: React.ReactNode }) => children ?? null,
    Link: ({ href, children }: { href: string; children?: React.ReactNode }) =>
        React.createElement('a', { href }, children),
    router: { get: vi.fn(), visit: vi.fn() },
    useForm: vi.fn(() => ({ data: {}, setData: vi.fn(), post: vi.fn(), processing: false, reset: vi.fn() })),
}));

// Bersihkan DOM setelah setiap test
afterEach(() => {
    cleanup();
});

// ============================================================
// Konstanta sesuai implementasi di admin/permohonan.tsx
// ============================================================

/** Label jenis informasi untuk tampilan (sama dengan admin/permohonan.tsx) */
const JENIS_INFORMASI_LABELS: Record<string, string> = {
    salinan_putusan: 'Salinan Putusan',
    laporan_kinerja: 'Laporan Kinerja',
    lainnya: 'Lainnya',
};

/** Label status untuk badge (sesuai status-badge.tsx) */
const STATUS_LABELS: Record<StatusPermohonan, string> = {
    baru: 'Baru',
    diproses: 'Diproses',
    selesai: 'Selesai',
    ditolak: 'Ditolak',
};

// ============================================================
// Definisi kolom tabel (meniru admin/permohonan.tsx)
// ============================================================

/**
 * Kolom-kolom DataTable sama persis dengan yang didefinisikan di admin/permohonan.tsx.
 * onClick pada tombol Proses menggunakan no-op karena kita hanya menguji rendering.
 */
const columns: Column<Permohonan>[] = [
    {
        key: 'tiket_no',
        label: 'Tiket',
        sortable: true,
        render: (item) =>
            React.createElement('span', { className: 'font-mono text-xs' }, item.tiket_no),
    },
    {
        key: 'nama_lengkap',
        label: 'Pemohon',
        sortable: true,
    },
    {
        key: 'jenis_informasi',
        label: 'Jenis Informasi',
        render: (item) =>
            React.createElement('span', null, JENIS_INFORMASI_LABELS[item.jenis_informasi] ?? item.jenis_informasi),
    },
    {
        key: 'status',
        label: 'Status',
        render: (item) => React.createElement(StatusBadge, { status: item.status }),
    },
    {
        key: 'aksi',
        label: 'Aksi',
        render: (item) =>
            React.createElement(
                Button,
                {
                    size: 'sm',
                    variant: 'outline',
                    'aria-label': `Proses permohonan ${item.tiket_no}`,
                } as React.ComponentProps<typeof Button>,
                'Proses',
            ),
    },
];

// ============================================================
// Generator fast-check untuk Permohonan
// ============================================================

/** Daftar jenis informasi valid */
const JENIS_VALUES: JenisInformasi[] = ['salinan_putusan', 'laporan_kinerja', 'lainnya'];

/** Daftar status permohonan valid */
const STATUS_VALUES: StatusPermohonan[] = ['baru', 'diproses', 'selesai', 'ditolak'];

/** Generator tanggal ISO 8601 */
const arbIsoDate = fc
    .tuple(
        fc.integer({ min: 2020, max: 2030 }),
        fc.integer({ min: 1, max: 12 }),
        fc.integer({ min: 1, max: 28 }),
        fc.integer({ min: 0, max: 23 }),
        fc.integer({ min: 0, max: 59 }),
        fc.integer({ min: 0, max: 59 }),
    )
    .map(([year, month, day, hour, min, sec]) => {
        const d = new Date(Date.UTC(year, month - 1, day, hour, min, sec));
        return d.toISOString();
    });

/** Generator nomor tiket format PPID-YYYYMMDD-XXX */
const arbTiketNo = fc
    .tuple(
        fc.integer({ min: 2020, max: 2030 }),
        fc.integer({ min: 1, max: 12 }),
        fc.integer({ min: 1, max: 28 }),
        fc.integer({ min: 1, max: 999 }),
    )
    .map(([year, month, day, seq]) => {
        const mm = String(month).padStart(2, '0');
        const dd = String(day).padStart(2, '0');
        const xxx = String(seq).padStart(3, '0');
        return `PPID-${year}${mm}${dd}-${xxx}`;
    });

/** Generator untuk objek Permohonan lengkap */
const arbPermohonan: fc.Arbitrary<Permohonan> = fc.record({
    id: fc.integer({ min: 1, max: 99999 }),
    tiket_no: arbTiketNo,
    nik: fc.stringMatching(/^\d{16}$/),
    nama_lengkap: fc.string({ minLength: 3, maxLength: 50 }).filter((s) => s.trim().length >= 3),
    alamat: fc.string({ minLength: 5, maxLength: 100 }),
    kota: fc.string({ minLength: 2, maxLength: 30 }),
    provinsi: fc.string({ minLength: 2, maxLength: 30 }),
    no_hp: fc.stringMatching(/^\d{10,13}$/),
    email: fc.emailAddress(),
    ktp_path: fc.option(fc.constant('/storage/ktp/sample.jpg'), { nil: null }),
    jenis_informasi: fc.constantFrom(...JENIS_VALUES),
    nomor_perkara: fc.option(fc.string({ minLength: 5, maxLength: 20 }), { nil: null }),
    tujuan: fc.string({ minLength: 5, maxLength: 100 }),
    uraian_informasi: fc.string({ minLength: 10, maxLength: 200 }),
    status: fc.constantFrom(...STATUS_VALUES),
    catatan_admin: fc.option(fc.string({ minLength: 1, maxLength: 100 }), { nil: null }),
    dokumen_balasan: fc.option(fc.constant('/storage/balasan/doc.pdf'), { nil: null }),
    alasan_tolak: fc.option(fc.string({ minLength: 1, maxLength: 100 }), { nil: null }),
    created_at: arbIsoDate,
    updated_at: arbIsoDate,
    processed_at: fc.option(arbIsoDate, { nil: null }),
    completed_at: fc.option(arbIsoDate, { nil: null }),
});

// ============================================================
// Property Tests
// ============================================================

describe('Property 4: Rendering baris tabel permohonan admin menampilkan semua kolom', () => {
    it('menampilkan nomor tiket di dalam baris tabel', () => {
        fc.assert(
            fc.property(arbPermohonan, (permohonan) => {
                cleanup();
                const { container, unmount } = render(
                    React.createElement(DataTable, {
                        columns,
                        data: [permohonan],
                    }),
                );

                // Nomor tiket harus tampil dalam span font-mono
                const tiketSpan = container.querySelector('span.font-mono');
                expect(tiketSpan).not.toBeNull();
                expect(tiketSpan!.textContent).toBe(permohonan.tiket_no);

                unmount();
            }),
            { numRuns: 100 },
        );
    });

    it('menampilkan nama pemohon di dalam baris tabel', () => {
        fc.assert(
            fc.property(arbPermohonan, (permohonan) => {
                cleanup();
                const { container, unmount } = render(
                    React.createElement(DataTable, {
                        columns,
                        data: [permohonan],
                    }),
                );

                // Nama lengkap harus muncul di dalam teks container
                expect(container.textContent).toContain(permohonan.nama_lengkap);

                unmount();
            }),
            { numRuns: 100 },
        );
    });

    it('menampilkan label jenis informasi yang benar (Salinan Putusan/Laporan Kinerja/Lainnya)', () => {
        fc.assert(
            fc.property(arbPermohonan, (permohonan) => {
                cleanup();
                const { container, unmount } = render(
                    React.createElement(DataTable, {
                        columns,
                        data: [permohonan],
                    }),
                );

                // Label jenis informasi harus ditampilkan sesuai mapping
                const expectedLabel = JENIS_INFORMASI_LABELS[permohonan.jenis_informasi];
                expect(container.textContent).toContain(expectedLabel);

                unmount();
            }),
            { numRuns: 100 },
        );
    });

    it('menampilkan badge status yang sesuai (Baru/Diproses/Selesai/Ditolak)', () => {
        fc.assert(
            fc.property(arbPermohonan, (permohonan) => {
                cleanup();
                const { container, unmount } = render(
                    React.createElement(DataTable, {
                        columns,
                        data: [permohonan],
                    }),
                );

                // StatusBadge merender Badge dengan data-slot="badge"
                const badge = container.querySelector('[data-slot="badge"]');
                expect(badge).not.toBeNull();

                // Teks badge harus sesuai label status
                const expectedLabel = STATUS_LABELS[permohonan.status];
                expect(badge!.textContent).toBe(expectedLabel);

                unmount();
            }),
            { numRuns: 100 },
        );
    });

    it('menampilkan tombol aksi "Proses"', () => {
        fc.assert(
            fc.property(arbPermohonan, (permohonan) => {
                cleanup();
                const { container, unmount } = render(
                    React.createElement(DataTable, {
                        columns,
                        data: [permohonan],
                    }),
                );

                // Tombol "Proses" harus ada di baris tabel
                const buttons = container.querySelectorAll('button');
                const prosesButton = Array.from(buttons).find(
                    (btn) => btn.textContent?.includes('Proses'),
                );
                expect(prosesButton).toBeDefined();

                // Tombol harus memiliki aria-label yang sesuai
                expect(prosesButton!.getAttribute('aria-label')).toBe(
                    `Proses permohonan ${permohonan.tiket_no}`,
                );

                unmount();
            }),
            { numRuns: 100 },
        );
    });
});
