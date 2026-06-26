import { Head, Deferred } from '@inertiajs/react';
import { ArrowLeft, HelpCircle } from 'lucide-react';
import { Card, CardContent } from '@/components/ui/card';
import { Skeleton } from '@/components/ui/skeleton';
import FaqAccordion from '@/components/faq-accordion';
import type { Faq } from '@/types/ppid';

interface Props {
    faqs?: Faq[];
}

/**
 * Komponen skeleton loading untuk item FAQ.
 * Menampilkan 4 blok shimmer yang menyerupai item accordion.
 */
function FaqSkeleton() {
    return (
        <div className="space-y-3" aria-label="Memuat data FAQ" role="status">
            {Array.from({ length: 4 }).map((_, index) => (
                <div
                    key={index}
                    className="rounded-xl border border-gray-100 bg-white p-5 shadow-sm"
                >
                    <div className="flex items-center gap-3">
                        {/* Nomor urut skeleton */}
                        <Skeleton className="h-7 w-7 rounded-full" />
                        {/* Teks pertanyaan skeleton */}
                        <Skeleton className="h-4 flex-1" />
                        {/* Ikon chevron skeleton */}
                        <Skeleton className="h-4 w-4" />
                    </div>
                </div>
            ))}
        </div>
    );
}

/**
 * Komponen konten FAQ yang menampilkan accordion atau empty state.
 */
function FaqContent({ faqs }: { faqs?: Faq[] }) {
    if (faqs && faqs.length > 0) {
        return <FaqAccordion items={faqs} />;
    }

    return (
        <Card className="border-dashed">
            <CardContent className="flex flex-col items-center justify-center py-12">
                <HelpCircle className="h-12 w-12 text-gray-300" />
                <p className="mt-4 text-sm text-gray-500">
                    Belum ada pertanyaan yang tersedia saat ini.
                </p>
            </CardContent>
        </Card>
    );
}

export default function FaqIndex({ faqs }: Props) {
    return (
        <>
            <Head title="Pertanyaan Umum (FAQ)" />

            <section className="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:py-12">
                {/* Tombol kembali */}
                <a
                    href="/"
                    className="mb-6 inline-flex items-center gap-1 text-sm text-gray-500 transition-colors hover:text-hijau"
                >
                    <ArrowLeft className="h-4 w-4" />
                    Kembali ke Beranda
                </a>

                <div className="mb-8 text-center">
                    <div className="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-orange/10">
                        <HelpCircle className="h-7 w-7 text-orange" />
                    </div>
                    <h1 className="font-heading text-2xl font-bold text-ungu sm:text-3xl">
                        Pertanyaan Umum (FAQ)
                    </h1>
                    <p className="mt-2 text-sm text-gray-500">
                        Jawaban atas pertanyaan yang sering diajukan tentang layanan PPID.
                    </p>
                </div>

                {/* Konten FAQ dengan deferred loading */}
                <Deferred data="faqs" fallback={<FaqSkeleton />}>
                    <FaqContent faqs={faqs} />
                </Deferred>

                {/* Kontak bantuan */}
                <div className="mt-8 rounded-xl border border-hijau/20 bg-hijau/5 p-6 text-center">
                    <p className="text-sm text-gray-700">
                        Tidak menemukan jawaban yang Anda cari?
                    </p>
                    <p className="mt-1 text-sm">
                        Hubungi kami di{' '}
                        <a
                            href="mailto:ppid@pa-penajam.go.id"
                            className="font-medium text-hijau hover:underline"
                        >
                            ppid@pa-penajam.go.id
                        </a>{' '}
                        atau telepon{' '}
                        <a
                            href="tel:0542-123456"
                            className="font-medium text-hijau hover:underline"
                        >
                            0542-123456
                        </a>
                    </p>
                </div>
            </section>
        </>
    );
}
