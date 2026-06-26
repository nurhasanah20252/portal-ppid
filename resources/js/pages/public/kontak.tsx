import { Head } from '@inertiajs/react';
import { ArrowLeft, MapPin, Phone, Mail, Clock, Building, ExternalLink } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

export default function KontakIndex() {
    return (
        <>
            <Head title="Kontak & Helpdesk" />

            <section className="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:py-12">
                {/* Tombol kembali */}
                <a
                    href="/"
                    className="mb-6 inline-flex items-center gap-1 text-sm text-gray-500 transition-colors hover:text-hijau"
                >
                    <ArrowLeft className="h-4 w-4" />
                    Kembali ke Beranda
                </a>

                <div className="mb-8 text-center">
                    <div className="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-hijau/10">
                        <Mail className="h-7 w-7 text-hijau" />
                    </div>
                    <h1 className="font-heading text-2xl font-bold text-ungu sm:text-3xl">
                        Kontak & Helpdesk
                    </h1>
                    <p className="mt-2 text-sm text-gray-500">
                        Hubungi kami untuk pertanyaan, saran, atau bantuan terkait layanan PPID.
                    </p>
                </div>

                <div className="grid gap-6 lg:grid-cols-2">
                    {/* Informasi Kontak */}
                    <Card className="border-t-4 border-t-hijau">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 font-heading text-lg text-hijau">
                                <Building className="h-5 w-5" />
                                Informasi Kontak
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <ul className="space-y-5">
                                <li className="flex items-start gap-4">
                                    <div className="rounded-lg bg-hijau/10 p-2.5 text-hijau">
                                        <MapPin className="h-5 w-5" />
                                    </div>
                                    <div>
                                        <p className="text-sm font-semibold text-gray-800">Alamat</p>
                                        <p className="mt-0.5 text-sm text-gray-600">
                                            Jl. Imam Bonjol No. 12
                                            <br />
                                            Penajam, Kab. Penajam Paser Utara
                                            <br />
                                            Kalimantan Timur 76211
                                        </p>
                                    </div>
                                </li>
                                <li className="flex items-start gap-4">
                                    <div className="rounded-lg bg-orange/10 p-2.5 text-orange">
                                        <Phone className="h-5 w-5" />
                                    </div>
                                    <div>
                                        <p className="text-sm font-semibold text-gray-800">Telepon</p>
                                        <a
                                            href="tel:0542-123456"
                                            className="mt-0.5 block text-sm text-gray-600 hover:text-hijau"
                                        >
                                            (0542) 123456
                                        </a>
                                    </div>
                                </li>
                                <li className="flex items-start gap-4">
                                    <div className="rounded-lg bg-ungu/10 p-2.5 text-ungu">
                                        <Mail className="h-5 w-5" />
                                    </div>
                                    <div>
                                        <p className="text-sm font-semibold text-gray-800">Email</p>
                                        <a
                                            href="mailto:ppid@pa-penajam.go.id"
                                            className="mt-0.5 block text-sm text-gray-600 hover:text-hijau"
                                        >
                                            ppid@pa-penajam.go.id
                                        </a>
                                    </div>
                                </li>
                                <li className="flex items-start gap-4">
                                    <div className="rounded-lg bg-emas/10 p-2.5 text-emas">
                                        <Clock className="h-5 w-5" />
                                    </div>
                                    <div>
                                        <p className="text-sm font-semibold text-gray-800">Jam Layanan</p>
                                        <p className="mt-0.5 text-sm text-gray-600">
                                            Senin &ndash; Jumat: 08.00 &ndash; 15.00 WITA
                                            <br />
                                            <span className="text-xs text-gray-400">
                                                Sabtu, Minggu & Hari Libur: Tutup
                                            </span>
                                        </p>
                                    </div>
                                </li>
                            </ul>
                        </CardContent>
                    </Card>

                    {/* Peta */}
                    <Card className="border-t-4 border-t-emas">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 font-heading text-lg text-ungu">
                                <MapPin className="h-5 w-5" />
                                Lokasi Kami
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            {/* Placeholder Google Maps */}
                            <div className="overflow-hidden rounded-lg border border-gray-200">
                                <div className="flex h-72 items-center justify-center bg-gray-100">
                                    <div className="text-center">
                                        <MapPin className="mx-auto h-10 w-10 text-gray-400" />
                                        <p className="mt-3 text-sm text-gray-500">
                                            Peta Lokasi Pengadilan Agama Penajam
                                        </p>
                                        <a
                                            href="https://maps.google.com/?q=Pengadilan+Agama+Penajam+Paser+Utara"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="mt-3 inline-flex items-center gap-1 rounded-lg bg-hijau px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-hijau-light"
                                        >
                                            <ExternalLink className="h-4 w-4" />
                                            Buka di Google Maps
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div className="mt-4 rounded-lg bg-gray-50 p-4">
                                <p className="text-xs leading-relaxed text-gray-500">
                                    <strong>Pengadilan Agama Penajam</strong> terletak di Jl. Imam Bonjol No. 12,
                                    Penajam, Kabupaten Penajam Paser Utara, Kalimantan Timur.
                                    Dapat diakses menggunakan kendaraan pribadi atau transportasi umum.
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Informasi tambahan */}
                <Card className="mt-6 border-t-4 border-t-ungu">
                    <CardContent className="pt-6">
                        <div className="text-center">
                            <h3 className="font-heading text-lg font-semibold text-gray-800">
                                Butuh Bantuan Cepat?
                            </h3>
                            <p className="mt-2 text-sm text-gray-500">
                                Untuk pertanyaan mendesak terkait permohonan informasi, silakan datang
                                langsung ke kantor PPID pada jam layanan atau hubungi nomor telepon kami.
                            </p>
                            <div className="mt-4 flex flex-col items-center justify-center gap-3 sm:flex-row">
                                <a
                                    href="tel:0542-123456"
                                    className="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-hijau px-6 py-2.5 text-sm font-medium text-white transition-colors hover:bg-hijau-light sm:w-auto"
                                >
                                    <Phone className="h-4 w-4" />
                                    Telepon Sekarang
                                </a>
                                <a
                                    href="mailto:ppid@pa-penajam.go.id"
                                    className="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-ungu px-6 py-2.5 text-sm font-medium text-ungu transition-colors hover:bg-ungu/10 sm:w-auto"
                                >
                                    <Mail className="h-4 w-4" />
                                    Kirim Email
                                </a>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </section>
        </>
    );
}
