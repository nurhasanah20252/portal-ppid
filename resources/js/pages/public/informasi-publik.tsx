import { Head, Link, router } from '@inertiajs/react';
import { Search, Download, FileText, Calendar, ArrowLeft, FolderOpen } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { trackEvent } from '@/lib/tracking';
import { cn } from '@/lib/utils';
import type { InformasiPublik, KategoriInformasi, PaginatedResponse } from '@/types/ppid';

/** Konfigurasi tab kategori */
const tabs: Array<{ key: KategoriInformasi | 'semua'; label: string }> = [
    { key: 'semua', label: 'Semua' },
    { key: 'berkala', label: 'Berkala' },
    { key: 'serta_merta', label: 'Serta Merta' },
    { key: 'setiap_saat', label: 'Setiap Saat' },
];

interface Props {
    informasi: PaginatedResponse<InformasiPublik>;
    filters: {
        kategori?: KategoriInformasi;
        tahun?: number;
        search?: string;
    };
}

export default function InformasiPublikIndex({ informasi, filters }: Props) {
    const [activeTab, setActiveTab] = useState<KategoriInformasi | 'semua'>(
        filters.kategori ?? 'semua',
    );
    const [searchQuery, setSearchQuery] = useState(filters.search ?? '');

    const handleTabChange = (tab: KategoriInformasi | 'semua') => {
        setActiveTab(tab);
        const params = new URLSearchParams();

        if (tab !== 'semua') {
            params.set('kategori', tab);
        }

        if (filters.tahun) {
            params.set('tahun', String(filters.tahun));
        }

        router.get(`/informasi-publik?${params.toString()}`);
    };

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        const params = new URLSearchParams();

        if (activeTab !== 'semua') {
            params.set('kategori', activeTab);
        }

        if (searchQuery) {
            params.set('search', searchQuery);
        }

        if (filters.tahun) {
            params.set('tahun', String(filters.tahun));
        }

        router.get(`/informasi-publik?${params.toString()}`);
    };

    /** Label kategori */
    const kategoriLabels: Record<string, string> = {
        berkala: 'Berkala',
        serta_merta: 'Serta Merta',
        setiap_saat: 'Setiap Saat',
    };

    return (
        <>
            <Head title="Informasi Publik" />

            <section className="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:py-12">
                {/* Tombol kembali */}
                <a
                    href="/"
                    className="mb-6 inline-flex items-center gap-1 text-sm text-gray-500 transition-colors hover:text-hijau"
                >
                    <ArrowLeft className="h-4 w-4" />
                    Kembali ke Beranda
                </a>

                {/* Header */}
                <div className="mb-8 text-center">
                    <div className="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-ungu/10">
                        <FolderOpen className="h-7 w-7 text-ungu" />
                    </div>
                    <h1 className="font-heading text-2xl font-bold text-ungu sm:text-3xl">
                        Informasi Publik
                    </h1>
                    <p className="mt-2 text-sm text-gray-500">
                        Daftar informasi publik yang tersedia untuk diunduh.
                    </p>
                </div>

                {/* Tab kategori */}
                <div className="mb-6 flex flex-wrap items-center gap-2">
                    {tabs.map((tab) => (
                        <button
                            key={tab.key}
                            onClick={() => handleTabChange(tab.key)}
                            className={cn(
                                'rounded-lg px-4 py-2 text-sm font-medium transition-colors',
                                activeTab === tab.key
                                    ? 'bg-orange text-white shadow-sm'
                                    : 'bg-white text-gray-600 hover:bg-gray-100',
                            )}
                        >
                            {tab.label}
                        </button>
                    ))}
                </div>

                {/* Pencarian */}
                <form onSubmit={handleSearch} className="mb-6 flex gap-2">
                    <div className="relative flex-1">
                        <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                        <Input
                            value={searchQuery}
                            onChange={(e) => setSearchQuery(e.target.value)}
                            placeholder="Cari berdasarkan judul, nomor perkara..."
                            className="pl-10"
                        />
                    </div>
                    <Button type="submit" className="bg-hijau text-white hover:bg-hijau-light">
                        Cari
                    </Button>
                </form>

                {/* Daftar informasi */}
                {informasi.items.length > 0 ? (
                    <div className="space-y-3">
                        {informasi.items.map((item) => (
                            <Card
                                key={item.id}
                                className="group border-gray-100 transition-all duration-200 hover:border-emas/30 hover:shadow-sm"
                            >
                                <CardContent className="flex items-start gap-4 pt-4 sm:pt-6">
                                    <div className="rounded-lg bg-hijau/10 p-2.5 text-hijau">
                                        <FileText className="h-5 w-5" />
                                    </div>
                                    <div className="flex-1 min-w-0">
                                        <h3 className="font-heading text-sm font-semibold text-gray-800 group-hover:text-hijau sm:text-base">
                                            {item.judul}
                                        </h3>
                                        <div className="mt-1 flex flex-wrap items-center gap-2 text-xs text-gray-500">
                                            <span className="rounded bg-ungu/10 px-2 py-0.5 text-ungu">
                                                {kategoriLabels[item.kategori] ?? item.kategori}
                                            </span>
                                            {item.sub_kategori && (
                                                <span className="text-gray-400">&bull; {item.sub_kategori}</span>
                                            )}
                                            <span className="flex items-center gap-1">
                                                <Calendar className="h-3 w-3" />
                                                {item.tahun}
                                            </span>
                                        </div>
                                        {item.deskripsi && (
                                            <p className="mt-2 line-clamp-2 text-xs text-gray-500 sm:text-sm">
                                                {item.deskripsi}
                                            </p>
                                        )}
                                    </div>
                                    {item.file_url && (
                                        <a
                                            href={item.file_url}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            onClick={() =>
                                                trackEvent('download', 'download_dokumen', item.sub_kategori, {
                                                    doc_type: item.sub_kategori,
                                                    doc_id: item.id,
                                                })
                                            }
                                            className="shrink-0 rounded-lg bg-hijau/10 p-2.5 text-hijau transition-colors hover:bg-hijau hover:text-white"
                                            aria-label={`Unduh ${item.judul}`}
                                        >
                                            <Download className="h-5 w-5" />
                                        </a>
                                    )}
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                ) : (
                    <Card className="border-dashed">
                        <CardContent className="flex flex-col items-center justify-center py-12">
                            <FolderOpen className="h-12 w-12 text-gray-300" />
                            <p className="mt-4 text-sm text-gray-500">
                                Belum ada informasi publik tersedia untuk kategori ini.
                            </p>
                        </CardContent>
                    </Card>
                )}

                {/* Pagination sederhana */}
                {informasi.pagination.last_page > 1 && (
                    <div className="mt-8 flex items-center justify-center gap-2">
                        {Array.from({ length: informasi.pagination.last_page }, (_, i) => i + 1).map(
                            (page) => (
                                <Link
                                    key={page}
                                    href={`/informasi-publik?page=${page}${activeTab !== 'semua' ? `&kategori=${activeTab}` : ''}`}
                                    className={cn(
                                        'flex h-9 w-9 items-center justify-center rounded-md text-sm font-medium transition-colors',
                                        page === informasi.pagination.current_page
                                            ? 'bg-hijau text-white'
                                            : 'bg-white text-gray-600 hover:bg-gray-100',
                                    )}
                                >
                                    {page}
                                </Link>
                            ),
                        )}
                    </div>
                )}
            </section>
        </>
    );
}
