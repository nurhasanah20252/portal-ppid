import { Link } from '@inertiajs/react';
import { MapPin, Phone, Mail, Clock } from 'lucide-react';

export default function PublicFooter() {
    return (
        <footer className="bg-hijau text-white">
            <div className="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <div className="grid gap-8 md:grid-cols-2 lg:grid-cols-4">
                    {/* Kolom 1: Tentang */}
                    <div>
                        <div className="mb-4 flex items-center gap-3">
                            <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-white/20">
                                <span className="text-xl font-bold text-white">⚖</span>
                            </div>
                            <div>
                                <p className="font-heading text-sm font-semibold text-white">
                                    PPID PA Penajam
                                </p>
                                <p className="text-xs text-white/70">Pengadilan Agama Penajam</p>
                            </div>
                        </div>
                        <p className="text-sm leading-relaxed text-white/80">
                            Portal Keterbukaan Informasi Publik Pengadilan Agama Penajam.
                            Melayani dengan Transparan.
                        </p>
                    </div>

                    {/* Kolom 2: Tautan Cepat */}
                    <div>
                        <h3 className="font-heading mb-4 text-sm font-semibold text-emas">
                            Tautan Cepat
                        </h3>
                        <ul className="space-y-2">
                            {[
                                { label: 'Beranda', href: '/' },
                                { label: 'Profil PPID', href: '/profil' },
                                { label: 'Informasi Publik', href: '/informasi-publik' },
                                { label: 'Permohonan Informasi', href: '/permohonan' },
                                { label: 'Cek Status', href: '/status' },
                            ].map((link) => (
                                <li key={link.href}>
                                    <Link
                                        href={link.href}
                                        className="text-sm text-white/70 transition-colors hover:text-white"
                                    >
                                        {link.label}
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    </div>

                    {/* Kolom 3: Kontak */}
                    <div>
                        <h3 className="font-heading mb-4 text-sm font-semibold text-emas">
                            Kontak Kami
                        </h3>
                        <ul className="space-y-3">
                            <li className="flex items-start gap-2">
                                <MapPin className="mt-0.5 h-4 w-4 shrink-0 text-emas" />
                                <span className="text-sm text-white/80">
                                    Jl. Imam Bonjol No. 12, Penajam, Kalimantan Timur
                                </span>
                            </li>
                            <li className="flex items-center gap-2">
                                <Phone className="h-4 w-4 shrink-0 text-emas" />
                                <span className="text-sm text-white/80">0542-123456</span>
                            </li>
                            <li className="flex items-center gap-2">
                                <Mail className="h-4 w-4 shrink-0 text-emas" />
                                <span className="text-sm text-white/80">ppid@pa-penajam.go.id</span>
                            </li>
                            <li className="flex items-center gap-2">
                                <Clock className="h-4 w-4 shrink-0 text-emas" />
                                <span className="text-sm text-white/80">Senin-Jumat: 08.00-15.00 WITA</span>
                            </li>
                        </ul>
                    </div>

                    {/* Kolom 4: Dasar Hukum */}
                    <div>
                        <h3 className="font-heading mb-4 text-sm font-semibold text-emas">
                            Dasar Hukum
                        </h3>
                        <ul className="space-y-2 text-sm text-white/80">
                            <li>UU No. 14/2008 tentang KIP</li>
                            <li>Perma No. 1/2015 tentang PPID</li>
                            <li>PerkomInfo No. 1/2010</li>
                        </ul>
                    </div>
                </div>

                {/* Divider & Copyright */}
                <div className="mt-10 border-t border-white/20 pt-6 text-center">
                    <p className="text-xs text-white/60">
                        &copy; {new Date().getFullYear()} PPID Pengadilan Agama Penajam. Hak cipta
                        dilindungi. Dibangun untuk keterbukaan informasi publik.
                    </p>
                </div>
            </div>
        </footer>
    );
}
