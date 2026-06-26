import { ChevronLeft, ChevronRight, ChevronsUpDown, ChevronUp, ChevronDown } from 'lucide-react';
import * as React from 'react';

import { Button } from '@/components/ui/button';
import { Skeleton } from '@/components/ui/skeleton';
import { cn } from '@/lib/utils';
import type { PaginationMeta } from '@/types/ppid';

// ============================================================
// Interfaces
// ============================================================

export interface Column<T> {
    /** Kunci field untuk mengakses data */
    key: string;
    /** Label kolom yang ditampilkan di header */
    label: string;
    /** Apakah kolom ini mendukung sorting */
    sortable?: boolean;
    /** Custom render function untuk konten sel */
    render?: (item: T) => React.ReactNode;
    /** Class CSS tambahan untuk kolom */
    className?: string;
}

export interface DataTableProps<T> {
    /** Definisi kolom tabel */
    columns: Column<T>[];
    /** Data yang ditampilkan dalam tabel */
    data: T[];
    /** Metadata pagination dari server */
    pagination?: PaginationMeta;
    /** Callback saat halaman berubah */
    onPageChange?: (page: number) => void;
    /** Callback saat sorting berubah */
    onSort?: (key: string, direction: 'asc' | 'desc') => void;
    /** Pesan saat data kosong */
    emptyMessage?: string;
    /** Status loading data */
    loading?: boolean;
}

// ============================================================
// Komponen DataTable
// ============================================================

/**
 * Komponen tabel data yang responsif dengan dukungan sorting dan pagination.
 * Menampilkan tabel HTML semantik pada desktop dan card view pada mobile.
 */
export function DataTable<T>({
    columns,
    data,
    pagination,
    onPageChange,
    onSort,
    emptyMessage = 'Tidak ada data untuk ditampilkan',
    loading = false,
}: DataTableProps<T>) {
    // State internal untuk sorting
    const [sortKey, setSortKey] = React.useState<string | null>(null);
    const [sortDirection, setSortDirection] = React.useState<'asc' | 'desc'>('asc');

    // Handler klik header untuk sorting
    const handleSort = (key: string) => {
        let newDirection: 'asc' | 'desc' = 'asc';

        if (sortKey === key) {
            // Toggle arah jika kolom yang sama diklik
            newDirection = sortDirection === 'asc' ? 'desc' : 'asc';
        }

        setSortKey(key);
        setSortDirection(newDirection);
        onSort?.(key, newDirection);
    };

    // Render ikon sorting pada header
    const renderSortIcon = (column: Column<T>) => {
        if (!column.sortable) {
            return null;
        }

        if (sortKey === column.key) {
            return sortDirection === 'asc' ? (
                <ChevronUp className="size-4" aria-hidden="true" />
            ) : (
                <ChevronDown className="size-4" aria-hidden="true" />
            );
        }

        return <ChevronsUpDown className="size-4 opacity-50" aria-hidden="true" />;
    };

    // Mengambil nilai dari item berdasarkan key (mendukung nested key)
    const getCellValue = (item: T, key: string): React.ReactNode => {
        const value = (item as Record<string, unknown>)[key];

        if (value === null || value === undefined) {
            return '-';
        }

        return String(value);
    };

    // Render skeleton loading
    if (loading) {
        return <DataTableSkeleton columns={columns} />;
    }

    // Render pesan kosong
    if (data.length === 0) {
        return (
            <div className="flex flex-col items-center justify-center rounded-lg border border-dashed p-8 text-center">
                <p className="text-muted-foreground text-sm">{emptyMessage}</p>
            </div>
        );
    }

    return (
        <div className="space-y-4">
            {/* Tampilan tabel desktop */}
            <div className="hidden overflow-x-auto rounded-lg border md:block">
                <table className="w-full text-sm">
                    <thead className="bg-muted/50 border-b">
                        <tr>
                            {columns.map((column) => (
                                <th
                                    key={column.key}
                                    scope="col"
                                    className={cn(
                                        'px-4 py-3 text-left font-medium text-muted-foreground',
                                        column.sortable && 'cursor-pointer select-none hover:text-foreground',
                                        column.className,
                                    )}
                                    onClick={column.sortable ? () => handleSort(column.key) : undefined}
                                    aria-sort={
                                        sortKey === column.key
                                            ? sortDirection === 'asc'
                                                ? 'ascending'
                                                : 'descending'
                                            : undefined
                                    }
                                >
                                    <div className="flex items-center gap-1">
                                        <span>{column.label}</span>
                                        {renderSortIcon(column)}
                                    </div>
                                </th>
                            ))}
                        </tr>
                    </thead>
                    <tbody className="divide-y">
                        {data.map((item, rowIndex) => (
                            <tr
                                key={rowIndex}
                                className="hover:bg-muted/30 transition-colors"
                            >
                                {columns.map((column) => (
                                    <td
                                        key={column.key}
                                        className={cn('px-4 py-3', column.className)}
                                    >
                                        {column.render
                                            ? column.render(item)
                                            : getCellValue(item, column.key)}
                                    </td>
                                ))}
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>

            {/* Tampilan card mobile */}
            <div className="space-y-3 md:hidden">
                {data.map((item, rowIndex) => (
                    <div
                        key={rowIndex}
                        className="rounded-lg border bg-card p-4 shadow-sm"
                    >
                        <dl className="space-y-2">
                            {columns.map((column) => (
                                <div key={column.key} className="flex items-start justify-between gap-2">
                                    <dt className="text-muted-foreground text-xs font-medium">
                                        {column.label}
                                    </dt>
                                    <dd className="text-right text-sm font-medium">
                                        {column.render
                                            ? column.render(item)
                                            : getCellValue(item, column.key)}
                                    </dd>
                                </div>
                            ))}
                        </dl>
                    </div>
                ))}
            </div>

            {/* Pagination */}
            {pagination && pagination.last_page > 1 && (
                <DataTablePagination
                    pagination={pagination}
                    onPageChange={onPageChange}
                />
            )}
        </div>
    );
}

// ============================================================
// Komponen Pagination
// ============================================================

interface DataTablePaginationProps {
    pagination: PaginationMeta;
    onPageChange?: (page: number) => void;
}

/**
 * Komponen pagination untuk navigasi halaman tabel.
 */
function DataTablePagination({ pagination, onPageChange }: DataTablePaginationProps) {
    const { current_page, last_page, total } = pagination;

    // Hitung range item yang ditampilkan
    const from = (current_page - 1) * pagination.per_page + 1;
    const to = Math.min(current_page * pagination.per_page, total);

    return (
        <div className="flex flex-col items-center justify-between gap-3 sm:flex-row">
            {/* Informasi jumlah data */}
            <p className="text-muted-foreground text-sm">
                Menampilkan {from}–{to} dari {total} data
            </p>

            {/* Tombol navigasi halaman */}
            <div className="flex items-center gap-1">
                <Button
                    variant="outline"
                    size="sm"
                    onClick={() => onPageChange?.(current_page - 1)}
                    disabled={current_page <= 1}
                    aria-label="Halaman sebelumnya"
                >
                    <ChevronLeft className="size-4" />
                </Button>

                {/* Nomor halaman */}
                {generatePageNumbers(current_page, last_page).map((page, index) =>
                    page === '...' ? (
                        <span
                            key={`ellipsis-${index}`}
                            className="text-muted-foreground px-2 text-sm"
                        >
                            …
                        </span>
                    ) : (
                        <Button
                            key={page}
                            variant={page === current_page ? 'default' : 'outline'}
                            size="sm"
                            onClick={() => onPageChange?.(page as number)}
                            aria-label={`Halaman ${page}`}
                            aria-current={page === current_page ? 'page' : undefined}
                        >
                            {page}
                        </Button>
                    ),
                )}

                <Button
                    variant="outline"
                    size="sm"
                    onClick={() => onPageChange?.(current_page + 1)}
                    disabled={current_page >= last_page}
                    aria-label="Halaman berikutnya"
                >
                    <ChevronRight className="size-4" />
                </Button>
            </div>
        </div>
    );
}

// ============================================================
// Komponen Skeleton Loading
// ============================================================

interface DataTableSkeletonProps<T> {
    columns: Column<T>[];
    /** Jumlah baris skeleton yang ditampilkan */
    rows?: number;
}

/**
 * Skeleton loading untuk tabel saat data sedang dimuat.
 */
function DataTableSkeleton<T>({ columns, rows = 5 }: DataTableSkeletonProps<T>) {
    return (
        <div className="space-y-4">
            {/* Skeleton tabel desktop */}
            <div className="hidden overflow-hidden rounded-lg border md:block">
                <div className="bg-muted/50 border-b px-4 py-3">
                    <div className="flex gap-4">
                        {columns.map((column) => (
                            <Skeleton key={column.key} className="h-4 w-24" />
                        ))}
                    </div>
                </div>
                <div className="divide-y">
                    {Array.from({ length: rows }).map((_, rowIndex) => (
                        <div key={rowIndex} className="flex gap-4 px-4 py-3">
                            {columns.map((column) => (
                                <Skeleton key={column.key} className="h-4 w-20" />
                            ))}
                        </div>
                    ))}
                </div>
            </div>

            {/* Skeleton card mobile */}
            <div className="space-y-3 md:hidden">
                {Array.from({ length: rows }).map((_, rowIndex) => (
                    <div key={rowIndex} className="rounded-lg border p-4">
                        <div className="space-y-3">
                            {columns.map((column) => (
                                <div key={column.key} className="flex items-center justify-between">
                                    <Skeleton className="h-3 w-16" />
                                    <Skeleton className="h-4 w-24" />
                                </div>
                            ))}
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
}

// ============================================================
// Utilitas
// ============================================================

/**
 * Menghasilkan array nomor halaman dengan ellipsis untuk pagination.
 * Menampilkan halaman pertama, terakhir, dan halaman di sekitar halaman aktif.
 */
function generatePageNumbers(
    currentPage: number,
    lastPage: number,
): (number | '...')[] {
    // Jika total halaman sedikit, tampilkan semua
    if (lastPage <= 7) {
        return Array.from({ length: lastPage }, (_, i) => i + 1);
    }

    const pages: (number | '...')[] = [];

    // Selalu tampilkan halaman pertama
    pages.push(1);

    if (currentPage > 3) {
        pages.push('...');
    }

    // Halaman di sekitar current page
    const start = Math.max(2, currentPage - 1);
    const end = Math.min(lastPage - 1, currentPage + 1);

    for (let i = start; i <= end; i++) {
        pages.push(i);
    }

    if (currentPage < lastPage - 2) {
        pages.push('...');
    }

    // Selalu tampilkan halaman terakhir
    pages.push(lastPage);

    return pages;
}
