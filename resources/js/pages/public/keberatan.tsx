import { Head, useForm } from '@inertiajs/react';
import { ArrowLeft, AlertTriangle, CheckCircle, Send } from 'lucide-react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

export default function KeberatanCreate() {
    const { data, setData, post, processing, errors, reset } = useForm({
        permohonan_tiket: '',
        nama_pemohon: '',
        alasan: '',
    });

    const [submitted, setSubmitted] = useState(false);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        post('/keberatan', {
            onSuccess: () => {
                setSubmitted(true);
            },
        });
    };

    /** Halaman sukses */
    if (submitted) {
        return (
            <>
                <Head title="Keberatan Terkirim" />
                <section className="mx-auto max-w-2xl px-4 py-16 sm:px-6">
                    <Card className="border-t-4 border-t-hijau text-center">
                        <CardContent className="pt-8">
                            <div className="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-green-100">
                                <CheckCircle className="h-8 w-8 text-green-600" />
                            </div>
                            <h2 className="font-heading text-2xl font-bold text-hijau">
                                Keberatan Telah Dikirim!
                            </h2>
                            <p className="mt-4 text-sm text-gray-600">
                                Keberatan Anda telah direkam. Petugas akan menghubungi Anda
                                maksimal 3 hari kerja.
                            </p>
                            <Button
                                onClick={() => {
                                    reset();
                                    setSubmitted(false);
                                }}
                                variant="outline"
                                className="mt-6 border-ungu text-ungu hover:bg-ungu/10"
                            >
                                Ajukan Keberatan Lain
                            </Button>
                        </CardContent>
                    </Card>
                </section>
            </>
        );
    }

    return (
        <>
            <Head title="Ajukan Keberatan" />

            <section className="mx-auto max-w-2xl px-4 py-8 sm:px-6 lg:py-12">
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
                        <AlertTriangle className="h-7 w-7 text-orange" />
                    </div>
                    <h1 className="font-heading text-2xl font-bold text-ungu sm:text-3xl">
                        Formulir Keberatan
                    </h1>
                    <p className="mt-2 text-sm text-gray-500">
                        Ajukan keberatan jika permohonan informasi Anda ditolak atau tidak sesuai.
                    </p>
                </div>

                <Card className="border-t-4 border-t-orange">
                    <CardHeader>
                        <CardTitle className="font-heading text-lg text-gray-800">
                            Data Keberatan
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-4">
                            <div>
                                <Label htmlFor="permohonan_tiket">Nomor Tiket Permohonan *</Label>
                                <Input
                                    id="permohonan_tiket"
                                    value={data.permohonan_tiket}
                                    onChange={(e) => setData('permohonan_tiket', e.target.value)}
                                    placeholder="PPID-20260609-001"
                                    required
                                    className={errors.permohonan_tiket ? 'border-orange animate-shake' : ''}
                                />
                                <InputError message={errors.permohonan_tiket} />
                                <p className="mt-1 text-xs text-gray-400">
                                    Masukkan nomor tiket permohonan yang ingin Anda keberatkan.
                                </p>
                            </div>

                            <div>
                                <Label htmlFor="nama_pemohon">Nama Pemohon *</Label>
                                <Input
                                    id="nama_pemohon"
                                    value={data.nama_pemohon}
                                    onChange={(e) => setData('nama_pemohon', e.target.value)}
                                    placeholder="Nama lengkap sesuai permohonan"
                                    required
                                    className={errors.nama_pemohon ? 'border-orange animate-shake' : ''}
                                />
                                <InputError message={errors.nama_pemohon} />
                            </div>

                            <div>
                                <Label htmlFor="alasan">Alasan Keberatan *</Label>
                                <textarea
                                    id="alasan"
                                    value={data.alasan}
                                    onChange={(e) => setData('alasan', e.target.value)}
                                    placeholder="Jelaskan secara detail alasan keberatan Anda..."
                                    rows={5}
                                    required
                                    className={`border-input w-full rounded-md border bg-transparent px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] ${
                                        errors.alasan ? 'border-orange animate-shake' : ''
                                    }`}
                                />
                                <InputError message={errors.alasan} />
                            </div>

                            <div className="flex items-center justify-between border-t border-gray-100 pt-6">
                                <a
                                    href="/status"
                                    className="text-sm text-ungu hover:underline"
                                >
                                    Cek status permohonan dulu
                                </a>
                                <Button
                                    type="submit"
                                    disabled={processing}
                                    className="bg-orange text-white hover:bg-orange-light"
                                >
                                    {processing ? (
                                        <>
                                            <span className="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent" />
                                            Mengirim...
                                        </>
                                    ) : (
                                        <>
                                            <Send className="h-4 w-4" />
                                            Kirim Keberatan
                                        </>
                                    )}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </section>
        </>
    );
}
