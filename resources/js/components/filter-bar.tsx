import { cn } from '@/lib/utils';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { KategoriInformasi } from '@/types/ppid';

/** Konfigurasi tab kategori */
const kategoriTabs: Array<{ key: KategoriInformasi | 'semua'; label: string }> = [
    { key: 'semua', label: 'Semua' },
    { key: 'berkala', label: 'Berkala' },
    { key: 'serta_merta', label: 'Serta Merta' },
    { key: 'setiap_saat', label: 'Setiap Saat' },
];

interface FilterBarProps {
    /** Filter aktif saat ini */
    filters: {
        kategori?: KategoriInformasi;
        tahun?: number;
    };
    /** Daftar tahun yang tersedia untuk filter */
    tahunList: number[];
    /** Callback saat filter berubah */
    onFilterChange: (filters: { kategori?: KategoriInformasi; tahun?: number }) => void;
    /** Class tambahan untuk container */
    className?: string;
}

/**
 * Komponen filter bar untuk halaman Informasi Publik.
 * Menampilkan tab kategori dan dropdown tahun.
 * Perubahan filter dilakukan tanpa full page reload (via Inertia router).
 */
export default function FilterBar({ filters, tahunList, onFilterChange, className }: FilterBarProps) {
    // Menentukan tab aktif berdasarkan filter kategori saat ini
    const activeTab: KategoriInformasi | 'semua' = filters.kategori ?? 'semua';

    // Menentukan nilai tahun aktif untuk select
    const activeTahun: string = filters.tahun ? String(filters.tahun) : 'semua';

    // Menangani perubahan tab kategori
    function handleKategoriChange(tab: KategoriInformasi | 'semua') {
        onFilterChange({
            kategori: tab === 'semua' ? undefined : tab,
            tahun: filters.tahun,
        });
    }

    // Menangani perubahan filter tahun
    function handleTahunChange(value: string) {
        onFilterChange({
            kategori: filters.kategori,
            tahun: value === 'semua' ? undefined : Number(value),
        });
    }

    return (
        <div
            role="region"
            aria-label="Filter informasi publik"
            className={cn('flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between', className)}
        >
            {/* Tab kategori */}
            <div className="flex flex-wrap items-center gap-2" role="tablist" aria-label="Kategori informasi">
                {kategoriTabs.map((tab) => (
                    <button
                        key={tab.key}
                        role="tab"
                        aria-selected={activeTab === tab.key}
                        onClick={() => handleKategoriChange(tab.key)}
                        className={cn(
                            'rounded-lg px-4 py-2 text-sm font-medium transition-colors',
                            'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-hijau/50 focus-visible:ring-offset-2',
                            activeTab === tab.key
                                ? 'bg-hijau text-white shadow-sm'
                                : 'bg-white text-gray-600 hover:bg-gray-100',
                        )}
                    >
                        {tab.label}
                    </button>
                ))}
            </div>

            {/* Dropdown filter tahun */}
            <div className="flex items-center gap-2">
                <label htmlFor="filter-tahun" className="text-sm font-medium text-gray-600">
                    Tahun:
                </label>
                <Select value={activeTahun} onValueChange={handleTahunChange}>
                    <SelectTrigger id="filter-tahun" aria-label="Filter berdasarkan tahun">
                        <SelectValue placeholder="Semua Tahun" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="semua">Semua Tahun</SelectItem>
                        {tahunList.map((tahun) => (
                            <SelectItem key={tahun} value={String(tahun)}>
                                {tahun}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
            </div>
        </div>
    );
}
