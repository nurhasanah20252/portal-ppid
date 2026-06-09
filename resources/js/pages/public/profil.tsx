import { Head } from '@inertiajs/react';
import { ArrowLeft, Building, Scale, Users, BookOpen, Shield } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

export default function ProfilIndex() {
    return (
        <>
            <Head title="Profil PPID" />

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
                        <Building className="h-7 w-7 text-hijau" />
                    </div>
                    <h1 className="font-heading text-2xl font-bold text-ungu sm:text-3xl">
                        Profil PPID
                    </h1>
                    <p className="mt-2 text-sm text-gray-500">
                        Pejabat Pengelola Informasi dan Dokumentasi Pengadilan Agama Penajam
                    </p>
                </div>

                <div className="grid gap-6 lg:grid-cols-2">
                    {/* Struktur Organisasi */}
                    <Card className="border-t-4 border-t-hijau">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 font-heading text-lg text-hijau">
                                <Users className="h-5 w-5" />
                                Struktur Organisasi
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {[
                                    { jabatan: 'Ketua PPID', nama: 'Dr. H. Ahmad Fauzi, S.H.', unit: 'Ketua Pengadilan' },
                                    { jabatan: 'Sekretaris PPID', nama: 'Lina Marlina, S.Sos', unit: 'Sekretaris' },
                                    { jabatan: 'Anggota PPID', nama: 'H. Bambang Suryanto, S.H., M.H.', unit: 'Hakim' },
                                    { jabatan: 'Operator PPID', nama: 'Rina Wati, A.Md.', unit: 'Kepaniteraan' },
                                ].map((person, index) => (
                                    <div
                                        key={index}
                                        className="flex items-start gap-3 rounded-lg border border-gray-100 p-3 transition-colors hover:bg-gray-50"
                                    >
                                        <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-hijau/10 text-sm font-bold text-hijau">
                                            {person.nama.charAt(0)}
                                        </div>
                                        <div>
                                            <p className="text-sm font-semibold text-gray-800">{person.nama}</p>
                                            <p className="text-xs text-gray-500">{person.jabatan}</p>
                                            <p className="text-xs text-gray-400">{person.unit}</p>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Dasar Hukum */}
                    <Card className="border-t-4 border-t-emas">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 font-heading text-lg text-ungu">
                                <Scale className="h-5 w-5" />
                                Dasar Hukum
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <ul className="space-y-3">
                                {[
                                    'Undang-Undang Nomor 14 Tahun 2008 tentang Keterbukaan Informasi Publik',
                                    'Peraturan Mahkamah Agung Nomor 1 Tahun 2015 tentang Pejabat Pengelola Informasi dan Dokumentasi di Lingkungan Mahkamah Agung dan Badan Peradilan di Bawahnya',
                                    'Peraturan Komisi Informasi Nomor 1 Tahun 2010 tentang Standar Layanan Informasi Publik',
                                    'Keputusan Ketua Pengadilan Agama Penajam tentang Penetapan PPID',
                                ].map((item, index) => (
                                    <li
                                        key={index}
                                        className="flex items-start gap-2 text-sm text-gray-700"
                                    >
                                        <BookOpen className="mt-0.5 h-4 w-4 shrink-0 text-emas" />
                                        {item}
                                    </li>
                                ))}
                            </ul>
                        </CardContent>
                    </Card>

                    {/* Tugas & Fungsi */}
                    <Card className="border-t-4 border-t-ungu lg:col-span-2">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 font-heading text-lg text-ungu">
                                <Shield className="h-5 w-5" />
                                Tugas & Fungsi PPID
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="grid gap-4 sm:grid-cols-2">
                                {[
                                    {
                                        title: 'Penyediaan Informasi',
                                        desc: 'Menyediakan informasi publik yang akurat, terkini, dan mudah diakses oleh masyarakat.',
                                    },
                                    {
                                        title: 'Pemrosesan Permohonan',
                                        desc: 'Memproses permohonan informasi publik sesuai dengan ketentuan peraturan perundang-undangan.',
                                    },
                                    {
                                        title: 'Penanganan Keberatan',
                                        desc: 'Menangani keberatan atas penolakan atau ketidakpuasan terhadap informasi yang diberikan.',
                                    },
                                    {
                                        title: 'Pelaporan',
                                        desc: 'Melaporkan pelaksanaan pelayanan informasi publik secara berkala kepada atasan.',
                                    },
                                ].map((tugas, index) => (
                                    <div
                                        key={index}
                                        className="rounded-lg border border-gray-100 p-4"
                                    >
                                        <h4 className="text-sm font-semibold text-hijau">{tugas.title}</h4>
                                        <p className="mt-1 text-xs leading-relaxed text-gray-600">{tugas.desc}</p>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </section>
        </>
    );
}
