import { Head, Link } from '@inertiajs/react';
import {
    FileText,
    Clock,
    CheckCircle,
    ArrowRight,
    Download,
    ChevronRight,
    HelpCircle,
    MapPin,
    Phone,
    Mail,
    ExternalLink,
} from 'lucide-react';
import HeroBanner from '@/components/hero-banner';
import StatCard from '@/components/stat-card';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { trackEvent } from '@/lib/tracking';
import type { Faq, InformasiPublik, StatistikDashboard } from '@/types/ppid';

interface Props {
    statistik?: StatistikDashboard;
    informasiTerbaru?: InformasiPublik[];
    faqPopuler?: Faq[];
}

export default function Home({
    statistik = {
        total_permohonan_bulan_ini: 0,
        sedang_diproses: 0,
        selesai_bulan_ini: 0,
        rata_rata_waktu_respon_hari: 0,
        permohonan_per_bulan: [],
    },
    informasiTerbaru = [],
    faqPopuler = [],
}: Props) {
    return (
        <>
            <Head title="Beranda">
                <meta
                    name="description"
                    content="Portal Keterbukaan Informasi Publik Pengadilan Agama Penajam. Ajukan permohonan informasi secara online."
                />
            </Head>

            {/* Hero Banner */}
            <HeroBanner />

            {/* Statistik Cepat */}
            <section className="relative -mt-8 z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div className="grid gap-4 sm:grid-cols-3">
                    <StatCard
                        icon={FileText}
                        label="Permohonan Bulan Ini"
                        value={statistik.total_permohonan_bulan_ini}
                    />
                    <StatCard
                        icon={Clock}
                        label="Sedang Diproses"
                        value={statistik.sedang_diproses}
                    />
                    <StatCard
                        icon={CheckCircle}
                        label="Selesai Bulan Ini"
                        value={statistik.selesai_bulan_ini}
                        description={`Rata-rata ${statistik.rata_rata_waktu_respon_hari} hari`}
                    />
                </div>
            </section>

            {/* Konten utama: Informasi Terbaru + Profil PPID */}
            <section className="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <div className="grid gap-8 lg:grid-cols-3">
                    {/* Kolom kiri: Informasi Terbaru */}
                    <div className="lg:col-span-2">
                        <h2 className="font-heading text-xl font-semibold text-ungu sm:text-2xl">
                            Informasi Terbaru
                        </h2>
                        <div className="mt-4 space-y-3">
                            {informasiTerbaru.length > 0 ? (
                                informasiTerbaru.map((item) => (
                                    <div
                                        key={item.id}
                                        className="group flex items-start gap-3 rounded-lg border border-gray-100 bg-white p-4 transition-all duration-200 hover:border-emas/30 hover:shadow-sm"
                                    >
                                        <div className="mt-0.5 rounded-md bg-emas/10 p-1.5 text-emas">
                                            <FileText className="h-4 w-4" />
                                        </div>
                                        <div className="flex-1 min-w-0">
                                            <p className="text-sm font-medium text-gray-800 group-hover:text-hijau">
                                                {item.judul}
                                            </p>
                                            <p className="mt-0.5 text-xs text-gray-500">
                                                {item.sub_kategori} &bull; {item.tahun}
                                            </p>
                                        </div>
                                        {item.file_url && (
                                            <a
                                                href={item.file_url}
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                onClick={() =>
                                                    trackEvent('download', 'download_dokumen', item.sub_kategori, {
                                                        doc_id: item.id,
                                                    })
                                                }
                                                className="shrink-0 rounded-md p-1.5 text-gray-400 transition-colors hover:bg-hijau/10 hover:text-hijau"
                                                aria-label={`Unduh ${item.judul}`}
                                            >
                                                <Download className="h-4 w-4" />
                                            </a>
                                        )}
                                    </div>
                                ))
                            ) : (
                                <p className="rounded-lg border border-dashed border-gray-200 p-8 text-center text-sm text-gray-400">
                                    Belum ada informasi terbaru.
                                </p>
                            )}
                        </div>
                        <Link
                            href="/informasi-publik"
                            className="mt-4 inline-flex items-center gap-1 text-sm font-medium text-ungu transition-colors hover:text-ungu-light"
                        >
                            Lihat semua informasi
                            <ArrowRight className="h-4 w-4" />
                        </Link>
                    </div>

                    {/* Kolom kanan: Profil PPID */}
                    <div>
                        <Card className="border-t-4 border-t-emas">
                            <CardHeader>
                                <CardTitle className="font-heading text-lg text-ungu">
                                    Profil PPID
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4 text-sm">
                                    <div>
                                        <p className="font-medium text-gray-500">Ketua PPID</p>
                                        <p className="text-gray-800">Dr. H. Ahmad Fauzi, S.H.</p>
                                    </div>
                                    <div>
                                        <p className="font-medium text-gray-500">Sekretaris</p>
                                        <p className="text-gray-800">Lina Marlina, S.Sos</p>
                                    </div>
                                    <div>
                                        <p className="font-medium text-gray-500">Kontak</p>
                                        <p className="text-gray-800">ppid@pa-penajam.go.id</p>
                                    </div>
                                </div>
                                <Link
                                    href="/profil"
                                    className="mt-4 inline-flex w-full items-center justify-center gap-1 rounded-lg border border-ungu bg-transparent px-4 py-2 text-sm font-medium text-ungu transition-all duration-200 hover:bg-ungu/10"
                                >
                                    Selengkapnya
                                    <ChevronRight className="h-4 w-4" />
                                </Link>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </section>

            {/* FAQ & Kontak */}
            <section className="bg-white">
                <div className="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                    <div className="grid gap-8 lg:grid-cols-2">
                        {/* FAQ */}
                        <div>
                            <h2 className="font-heading text-xl font-semibold text-ungu sm:text-2xl">
                                Pertanyaan Umum (FAQ)
                            </h2>
                            <div className="mt-4 space-y-3">
                                {faqPopuler.length > 0 ? (
                                    faqPopuler.map((item) => (
                                        <details
                                            key={item.id}
                                            className="group rounded-lg border border-gray-100 bg-gray-50"
                                            onToggle={(e) => {
                                                if ((e.target as HTMLDetailsElement).open) {
                                                    trackEvent('engagement', 'faq_click', String(item.id));
                                                }
                                            }}
                                        >
                                            <summary className="flex cursor-pointer items-center gap-2 p-4 text-sm font-medium text-gray-800 transition-colors hover:text-hijau">
                                                <HelpCircle className="h-4 w-4 shrink-0 text-orange" />
                                                {item.pertanyaan}
                                            </summary>
                                            <div className="px-4 pb-4 pt-0 pl-10 text-sm leading-relaxed text-gray-600">
                                                {item.jawaban}
                                            </div>
                                        </details>
                                    ))
                                ) : (
                                    <p className="text-sm text-gray-400">Belum ada FAQ tersedia.</p>
                                )}
                            </div>
                            <Link
                                href="/faq"
                                className="mt-4 inline-flex items-center gap-1 text-sm font-medium text-ungu transition-colors hover:text-ungu-light"
                            >
                                Lihat semua FAQ
                                <ArrowRight className="h-4 w-4" />
                            </Link>
                        </div>

                        {/* Kontak & Peta */}
                        <div>
                            <h2 className="font-heading text-xl font-semibold text-ungu sm:text-2xl">
                                Kontak & Peta
                            </h2>
                            <Card className="mt-4 border-t-4 border-t-emas">
                                <CardContent className="pt-6">
                                    <ul className="space-y-4 text-sm">
                                        <li className="flex items-start gap-3">
                                            <MapPin className="mt-0.5 h-5 w-5 shrink-0 text-orange" />
                                            <span className="text-gray-700">
                                                Jl. Imam Bonjol No. 12, Penajam, Kalimantan Timur
                                            </span>
                                        </li>
                                        <li className="flex items-center gap-3">
                                            <Phone className="h-5 w-5 shrink-0 text-orange" />
                                            <span className="text-gray-700">0542-123456</span>
                                        </li>
                                        <li className="flex items-center gap-3">
                                            <Mail className="h-5 w-5 shrink-0 text-orange" />
                                            <a
                                                href="mailto:ppid@pa-penajam.go.id"
                                                className="text-gray-700 hover:text-hijau"
                                            >
                                                ppid@pa-penajam.go.id
                                            </a>
                                        </li>
                                    </ul>

                                    {/* Placeholder Google Maps */}
                                    <div className="mt-4 overflow-hidden rounded-lg border border-gray-200 bg-gray-100">
                                        <div className="flex h-48 items-center justify-center">
                                            <div className="text-center">
                                                <MapPin className="mx-auto h-8 w-8 text-gray-400" />
                                                <p className="mt-2 text-xs text-gray-500">
                                                    Peta Lokasi Pengadilan Agama Penajam
                                                </p>
                                                <a
                                                    href="https://maps.google.com/?q=Pengadilan+Agama+Penajam"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    className="mt-2 inline-flex items-center gap-1 text-xs font-medium text-ungu hover:underline"
                                                >
                                                    Buka di Google Maps
                                                    <ExternalLink className="h-3 w-3" />
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                </div>
            </section>
        </>
    );
}
