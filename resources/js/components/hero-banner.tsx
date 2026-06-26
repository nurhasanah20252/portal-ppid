import { Link } from '@inertiajs/react';
import { FileText, Search } from 'lucide-react';
import { trackEvent } from '@/lib/tracking';

export default function HeroBanner() {
    return (
        <section className="relative overflow-hidden bg-gradient-to-br from-hijau via-hijau-light to-ungu">
            {/* Pola dekoratif */}
            <div className="absolute inset-0 opacity-10">
                <div className="absolute -right-20 -top-20 h-96 w-96 rounded-full bg-white/20" />
                <div className="absolute -bottom-10 -left-10 h-64 w-64 rounded-full bg-emas/20" />
            </div>

            <div className="relative mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-24 lg:px-8 lg:py-32">
                <div className="max-w-3xl">
                    <h1 className="font-heading text-3xl font-bold leading-tight text-white sm:text-4xl lg:text-5xl">
                        Portal Keterbukaan
                        <br />
                        Informasi Publik
                    </h1>
                    <p className="mt-2 font-heading text-lg font-medium text-emas sm:text-xl">
                        Pengadilan Agama Penajam &ndash; Melayani dengan Transparan
                    </p>
                    <p className="mt-4 max-w-xl text-base leading-relaxed text-white/80 sm:text-lg">
                        Ajukan permohonan informasi secara online, lacak status permohonan Anda,
                        dan akses dokumen publik dengan mudah dan cepat.
                    </p>

                    <div className="mt-8 flex flex-col gap-4 sm:flex-row">
                        <Link
                            href="/permohonan"
                            onClick={() => trackEvent('cta_button', 'click_ajukan', 'hero_banner')}
                            className="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-orange px-6 py-3 font-heading text-sm font-semibold text-white shadow-lg transition-all duration-200 hover:scale-[1.02] hover:bg-orange-light hover:shadow-xl sm:w-auto sm:text-base"
                        >
                            <FileText className="h-5 w-5" />
                            Ajukan Permohonan
                        </Link>
                        <Link
                            href="/status"
                            onClick={() => trackEvent('cta_button', 'click_cek_status', 'hero_banner')}
                            className="inline-flex w-full items-center justify-center gap-2 rounded-lg border-2 border-white/30 bg-white/10 px-6 py-3 font-heading text-sm font-semibold text-white backdrop-blur-sm transition-all duration-200 hover:border-white/50 hover:bg-white/20 sm:w-auto sm:text-base"
                        >
                            <Search className="h-5 w-5" />
                            Cek Status
                        </Link>
                    </div>
                </div>
            </div>
        </section>
    );
}
