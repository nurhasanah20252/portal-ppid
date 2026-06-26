// @vitest-environment jsdom
/**
 * Property-Based Test untuk rendering hasil cek status permohonan.
 * Memverifikasi bahwa setiap StatusCheckResult yang valid menampilkan
 * semua elemen wajib: badge status, tanggal pengajuan, dan timeline riwayat.
 *
 * **Validates: Requirements 7.3**
 */
import { render, within, cleanup } from '@testing-library/react';
import * as fc from 'fast-check';
import React from 'react';
import { afterEach, describe, expect, it } from 'vitest';

import StatusBadge from '@/components/status-badge';
import TimelineStatus from '@/components/timeline-status';
import type { StatusCheckResult, StatusLog, StatusPermohonan } from '@/types/ppid';

// Bersihkan DOM setelah setiap test
afterEach(() => {
    cleanup();
});

// ============================================================
// Komponen wrapper untuk testing (meniru bagian hasil di status.tsx)
// ============================================================

/**
 * Komponen sederhana yang merender bagian hasil cek status.
 * Meniru struktur rendering dari pages/public/status.tsx
 * tanpa membutuhkan konteks Inertia.
 * Menggunakan React.createElement agar file tetap .ts (tanpa JSX).
 */
function StatusResultCard({ result }: { result: StatusCheckResult }) {
    const h = React.createElement;

    // Elemen-elemen description list
    const dlChildren: React.ReactNode[] = [
        h('div', { key: 'tiket' },
            h('dt', null, 'Nomor Tiket:'),
            h('dd', null, result.tiket_no),
        ),
        h('div', { key: 'tanggal' },
            h('dt', null, 'Tanggal Ajuan:'),
            h('dd', null, result.created_at),
        ),
    ];

    if (result.processed_at) {
        dlChildren.push(
            h('div', { key: 'processed' },
                h('dt', null, 'Diproses:'),
                h('dd', null, result.processed_at),
            ),
        );
    }

    if (result.catatan_admin) {
        dlChildren.push(
            h('div', { key: 'catatan' },
                h('dt', null, 'Catatan Admin:'),
                h('dd', null, result.catatan_admin),
            ),
        );
    }

    if (result.dokumen_balasan_url) {
        dlChildren.push(
            h('div', { key: 'dokumen' },
                h('dt', null, 'Dokumen:'),
                h('dd', null, h('a', { href: result.dokumen_balasan_url }, 'Unduh Dokumen Balasan')),
            ),
        );
    }

    return h('div', { 'data-testid': 'status-result-card' },
        h('div', { className: 'flex items-center justify-between' },
            h('h3', null, 'Status Permohonan'),
            h(StatusBadge, { status: result.status }),
        ),
        h('dl', null, ...dlChildren),
        h('div', { 'data-testid': 'timeline-section' },
            h('h4', null, 'Riwayat Proses'),
            h(TimelineStatus, { riwayat: result.riwayat }),
        ),
    );
}

// ============================================================
// Generator fast-check untuk StatusCheckResult
// ============================================================

/** Daftar status permohonan yang valid */
const STATUS_VALUES: StatusPermohonan[] = ['baru', 'diproses', 'selesai', 'ditolak'];

/** Label yang ditampilkan oleh StatusBadge untuk setiap status */
const STATUS_LABELS: Record<StatusPermohonan, string> = {
    baru: 'Baru',
    diproses: 'Diproses',
    selesai: 'Selesai',
    ditolak: 'Ditolak',
};

/**
 * Generator tanggal ISO 8601 yang aman.
 * Membuat tanggal dari komponen individu untuk menghindari invalid date di fast-check v4.
 */
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

/** Generator nomor tiket dengan format PPID-YYYYMMDD-XXX */
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

/** Generator untuk satu item StatusLog */
const arbStatusLog: fc.Arbitrary<StatusLog> = fc.record({
    id: fc.integer({ min: 1, max: 10000 }),
    permohonan_id: fc.integer({ min: 1, max: 10000 }),
    status_lama: fc.constantFrom(...STATUS_VALUES),
    status_baru: fc.constantFrom(...STATUS_VALUES),
    catatan: fc.option(fc.string({ minLength: 1, maxLength: 50 }), { nil: null }),
    created_by: fc.option(fc.integer({ min: 1, max: 100 }), { nil: null }),
    created_at: arbIsoDate,
});

/** Generator untuk StatusCheckResult lengkap */
const arbStatusCheckResult: fc.Arbitrary<StatusCheckResult> = fc.record({
    tiket_no: arbTiketNo,
    status: fc.constantFrom(...STATUS_VALUES),
    created_at: arbIsoDate,
    processed_at: fc.option(arbIsoDate, { nil: null }),
    completed_at: fc.option(arbIsoDate, { nil: null }),
    catatan_admin: fc.option(fc.string({ minLength: 1, maxLength: 100 }), { nil: null }),
    dokumen_balasan_url: fc.option(
        fc.constant('https://example.com/dokumen.pdf'),
        { nil: null },
    ),
    riwayat: fc.array(arbStatusLog, { minLength: 0, maxLength: 5 }),
});

/** Generator StatusCheckResult dengan riwayat minimal 1 item */
const arbWithRiwayat: fc.Arbitrary<StatusCheckResult> = fc.record({
    tiket_no: arbTiketNo,
    status: fc.constantFrom(...STATUS_VALUES),
    created_at: arbIsoDate,
    processed_at: fc.option(arbIsoDate, { nil: null }),
    completed_at: fc.option(arbIsoDate, { nil: null }),
    catatan_admin: fc.option(fc.string({ minLength: 1, maxLength: 100 }), { nil: null }),
    dokumen_balasan_url: fc.option(
        fc.constant('https://example.com/dokumen.pdf'),
        { nil: null },
    ),
    riwayat: fc.array(arbStatusLog, { minLength: 1, maxLength: 5 }),
});

/** Generator StatusCheckResult dengan riwayat kosong */
const arbEmptyRiwayat: fc.Arbitrary<StatusCheckResult> = fc.record({
    tiket_no: arbTiketNo,
    status: fc.constantFrom(...STATUS_VALUES),
    created_at: arbIsoDate,
    processed_at: fc.option(arbIsoDate, { nil: null }),
    completed_at: fc.option(arbIsoDate, { nil: null }),
    catatan_admin: fc.option(fc.string({ minLength: 1, maxLength: 100 }), { nil: null }),
    dokumen_balasan_url: fc.option(
        fc.constant('https://example.com/dokumen.pdf'),
        { nil: null },
    ),
    riwayat: fc.constant([] as StatusLog[]),
});

// ============================================================
// Property Tests
// ============================================================

describe('Property 3: Rendering hasil cek status menampilkan semua elemen yang diperlukan', () => {
    it('menampilkan nomor tiket dalam output', () => {
        fc.assert(
            fc.property(arbStatusCheckResult, (result) => {
                cleanup();
                const { container, unmount } = render(
                    React.createElement(StatusResultCard, { result }),
                );

                // Tiket_no harus terlihat di dalam container
                const card = within(container);
                expect(card.getByText(result.tiket_no)).toBeDefined();

                unmount();
            }),
            { numRuns: 100 },
        );
    });

    it('menampilkan badge status yang sesuai (Baru/Diproses/Selesai/Ditolak)', () => {
        fc.assert(
            fc.property(arbStatusCheckResult, (result) => {
                cleanup();
                const { container, unmount } = render(
                    React.createElement(StatusResultCard, { result }),
                );

                // Badge harus menampilkan label status yang sesuai
                const expectedLabel = STATUS_LABELS[result.status];
                const badge = container.querySelector('[data-slot="badge"]');
                expect(badge).not.toBeNull();
                expect(badge!.textContent).toBe(expectedLabel);

                unmount();
            }),
            { numRuns: 100 },
        );
    });

    it('menampilkan tanggal pengajuan (created_at)', () => {
        fc.assert(
            fc.property(arbStatusCheckResult, (result) => {
                cleanup();
                const { container, unmount } = render(
                    React.createElement(StatusResultCard, { result }),
                );

                // created_at harus ditampilkan di dalam card
                const card = within(container);
                expect(card.getByText(result.created_at)).toBeDefined();

                unmount();
            }),
            { numRuns: 100 },
        );
    });

    it('menampilkan section timeline riwayat', () => {
        fc.assert(
            fc.property(arbStatusCheckResult, (result) => {
                cleanup();
                const { container, unmount } = render(
                    React.createElement(StatusResultCard, { result }),
                );

                // Section timeline harus selalu ada
                const card = within(container);
                expect(card.getByTestId('timeline-section')).toBeDefined();
                expect(card.getByText('Riwayat Proses')).toBeDefined();

                unmount();
            }),
            { numRuns: 100 },
        );
    });

    it('menampilkan item timeline jika riwayat tidak kosong', () => {
        fc.assert(
            fc.property(arbWithRiwayat, (result) => {
                cleanup();
                const { container, unmount } = render(
                    React.createElement(StatusResultCard, { result }),
                );

                // Jika riwayat ada, TimelineStatus harus merender ordered list
                const timelineList = container.querySelector('ol[aria-label="Riwayat status permohonan"]');
                expect(timelineList).not.toBeNull();

                // Jumlah item list harus sesuai dengan jumlah riwayat
                const listItems = timelineList!.querySelectorAll('li');
                expect(listItems.length).toBe(result.riwayat.length);

                unmount();
            }),
            { numRuns: 100 },
        );
    });

    it('menampilkan pesan kosong jika riwayat kosong', () => {
        fc.assert(
            fc.property(arbEmptyRiwayat, (result) => {
                cleanup();
                const { container, unmount } = render(
                    React.createElement(StatusResultCard, { result }),
                );

                // TimelineStatus menampilkan pesan jika tidak ada riwayat
                const card = within(container);
                expect(card.getByText('Belum ada riwayat status.')).toBeDefined();

                unmount();
            }),
            { numRuns: 100 },
        );
    });
});
