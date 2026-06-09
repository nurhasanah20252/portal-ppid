import { Head } from '@inertiajs/react';
import { ArrowLeft, HelpCircle, ChevronDown } from 'lucide-react';
import { Card, CardContent } from '@/components/ui/card';
import { trackEvent } from '@/lib/tracking';
import type { Faq } from '@/types/ppid';

interface Props {
    faqs: Faq[];
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

                {faqs.length > 0 ? (
                    <div className="space-y-3">
                        {faqs.map((faq, index) => (
                            <details
                                key={faq.id}
                                className="group rounded-xl border border-gray-100 bg-white shadow-sm transition-all duration-200 hover:border-emas/30 hover:shadow-md"
                                onToggle={(e) => {
                                    if ((e.target as HTMLDetailsElement).open) {
                                        trackEvent('engagement', 'faq_click', String(faq.id));
                                    }
                                }}
                            >
                                <summary className="flex cursor-pointer items-center gap-3 p-5 text-sm font-medium text-gray-800 transition-colors hover:text-hijau">
                                    <span className="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-orange/10 text-xs font-bold text-orange">
                                        {index + 1}
                                    </span>
                                    <span className="flex-1">{faq.pertanyaan}</span>
                                    <ChevronDown className="h-4 w-4 shrink-0 text-gray-400 transition-transform group-open:rotate-180" />
                                </summary>
                                <div className="border-t border-gray-50 px-5 pb-5 pl-[3.25rem] pt-3 text-sm leading-relaxed text-gray-600">
                                    {faq.jawaban}
                                </div>
                            </details>
                        ))}
                    </div>
                ) : (
                    <Card className="border-dashed">
                        <CardContent className="flex flex-col items-center justify-center py-12">
                            <HelpCircle className="h-12 w-12 text-gray-300" />
                            <p className="mt-4 text-sm text-gray-500">
                                Belum ada pertanyaan yang tersedia saat ini.
                            </p>
                        </CardContent>
                    </Card>
                )}

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
