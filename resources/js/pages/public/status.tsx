import { Head, useForm } from '@inertiajs/react';
import { Search, ArrowLeft, Ticket, Clock, FileText, AlertTriangle, CheckCircle } from 'lucide-react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import StatusBadge from '@/components/status-badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { trackEvent } from '@/lib/tracking';
import type { StatusCheckResult } from '@/types/ppid';

export default function StatusIndex() {
    const { data, setData, post, processing, errors } = useForm({
        tiket_no: '',
    });

    const [result, setResult] = useState<StatusCheckResult | null>(null);
    const [notFound, setNotFound] = useState(false);

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        setResult(null);
        setNotFound(false);

        post('/status/check', {
            onSuccess: (page) => {
                const response = page.props as Record<string, unknown>;

                if (response.data) {
                    setResult(response.data as StatusCheckResult);
                    trackEvent('status_check', 'search_tiket', 'found');
                } else {
                    setNotFound(true);
                    trackEvent('status_check', 'search_tiket', 'not_found');
                }
            },
            onError: () => {
                setNotFound(true);
                trackEvent('status_check', 'search_tiket', 'not_found');
            },
        });
    };

    /** Label status untuk ditampilkan */
    const statusLabels: Record<string, string> = {
        baru: 'Baru',
        diproses: 'Diproses',
        selesai: 'Selesai',
        ditolak: 'Ditolak',
    };

    return (
        <>
            <Head title="Cek Status Permohonan" />

            <section className="mx-auto max-w-2xl px-4 py-8 sm:px-6 lg:py-12">
                {/* Tombol kembali */}
                <a
                    href="/"
                    className="mb-6 inline-flex items-center gap-1 text-sm text-gray-500 transition-colors hover:text-hijau"
                >
                    <ArrowLeft className="h-4 w-4" />
                    Kembali ke Beranda
                </a>

                <div className="text-center">
                    <div className="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-ungu/10">
                        <Search className="h-8 w-8 text-ungu" />
                    </div>
                    <h1 className="font-heading text-2xl font-bold text-ungu sm:text-3xl">
                        Cek Status Permohonan
                    </h1>
                    <p className="mt-2 text-sm text-gray-500">
                        Masukkan nomor tiket yang Anda terima untuk melihat status permohonan.
                    </p>
                </div>

                {/* Form pencarian */}
                <Card className="mt-8 border-t-4 border-t-emas">
                    <CardContent className="pt-6">
                        <form onSubmit={handleSearch} className="space-y-4">
                            <div>
                                <Label htmlFor="tiket_no">Nomor Tiket</Label>
                                <div className="relative mt-1">
                                    <Ticket className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                                    <Input
                                        id="tiket_no"
                                        value={data.tiket_no}
                                        onChange={(e) => setData('tiket_no', e.target.value)}
                                        placeholder="PPID-20260609-001"
                                        className="pl-10"
                                        required
                                    />
                                </div>
                                <InputError message={errors.tiket_no} />
                            </div>

                            <Button
                                type="submit"
                                disabled={processing}
                                className="w-full bg-orange text-white hover:bg-orange-light"
                            >
                                {processing ? (
                                    <>
                                        <span className="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent" />
                                        Mencari...
                                    </>
                                ) : (
                                    <>
                                        <Search className="h-4 w-4" />
                                        Cek Status
                                    </>
                                )}
                            </Button>
                        </form>
                    </CardContent>
                </Card>

                {/* Hasil: Tiket tidak ditemukan */}
                {notFound && (
                    <Card className="mt-6 border-orange/30 bg-orange/5">
                        <CardContent className="flex items-start gap-3 pt-6">
                            <AlertTriangle className="mt-0.5 h-5 w-5 shrink-0 text-orange" />
                            <div>
                                <p className="font-medium text-orange">Tiket tidak ditemukan.</p>
                                <p className="mt-1 text-sm text-gray-600">
                                    Pastikan nomor tiket benar (format: PPID-YYYYMMDD-XXXX).
                                    <br />
                                    Hubungi helpdesk: <strong>0542-123456</strong>
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                )}

                {/* Hasil: Tiket ditemukan */}
                {result && (
                    <Card className="mt-6 border-t-4 border-t-hijau">
                        <CardHeader>
                            <div className="flex items-center justify-between">
                                <CardTitle className="font-heading text-lg text-hijau">
                                    Status Permohonan
                                </CardTitle>
                                <StatusBadge status={result.status} />
                            </div>
                        </CardHeader>
                        <CardContent>
                            <dl className="space-y-3 text-sm">
                                <div className="flex items-center gap-3">
                                    <Ticket className="h-4 w-4 shrink-0 text-gray-400" />
                                    <dt className="w-32 text-gray-500">Nomor Tiket:</dt>
                                    <dd className="font-semibold text-hijau">{result.tiket_no}</dd>
                                </div>
                                <div className="flex items-center gap-3">
                                    <Clock className="h-4 w-4 shrink-0 text-gray-400" />
                                    <dt className="w-32 text-gray-500">Tanggal Ajuan:</dt>
                                    <dd className="text-gray-800">{result.created_at}</dd>
                                </div>
                                {result.processed_at && (
                                    <div className="flex items-center gap-3">
                                        <Clock className="h-4 w-4 shrink-0 text-gray-400" />
                                        <dt className="w-32 text-gray-500">Diproses:</dt>
                                        <dd className="text-gray-800">{result.processed_at}</dd>
                                    </div>
                                )}
                                {result.catatan_admin && (
                                    <div className="flex items-start gap-3">
                                        <FileText className="mt-0.5 h-4 w-4 shrink-0 text-gray-400" />
                                        <dt className="w-32 text-gray-500">Catatan Admin:</dt>
                                        <dd className="text-gray-800">{result.catatan_admin}</dd>
                                    </div>
                                )}
                                {result.dokumen_balasan_url && (
                                    <div className="flex items-center gap-3">
                                        <CheckCircle className="h-4 w-4 shrink-0 text-green-500" />
                                        <dt className="w-32 text-gray-500">Dokumen:</dt>
                                        <dd>
                                            <a
                                                href={result.dokumen_balasan_url}
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                className="font-medium text-hijau underline underline-offset-2 hover:text-hijau-light"
                                            >
                                                Unduh Dokumen Balasan
                                            </a>
                                        </dd>
                                    </div>
                                )}
                            </dl>

                            {/* Riwayat status */}
                            {result.riwayat.length > 0 && (
                                <div className="mt-6 border-t border-gray-100 pt-4">
                                    <h4 className="mb-3 font-heading text-sm font-semibold text-gray-700">
                                        Riwayat Proses
                                    </h4>
                                    <div className="relative space-y-3 pl-4">
                                        {/* Garis timeline */}
                                        <div className="absolute bottom-0 left-[7px] top-0 w-0.5 bg-gray-200" />

                                        {result.riwayat.map((log, index) => (
                                            <div key={log.id ?? index} className="relative flex items-start gap-3">
                                                <div
                                                    className={`relative z-10 mt-1 h-3.5 w-3.5 shrink-0 rounded-full border-2 ${
                                                        index === 0
                                                            ? 'border-hijau bg-hijau'
                                                            : 'border-gray-300 bg-white'
                                                    }`}
                                                />
                                                <div>
                                                    <p className="text-xs text-gray-400">
                                                        {log.created_at}
                                                    </p>
                                                    <p className="text-sm font-medium text-gray-700">
                                                        {statusLabels[log.status_baru] ?? log.status_baru}
                                                        {log.catatan && (
                                                            <span className="ml-1 font-normal text-gray-500">
                                                                &ndash; {log.catatan}
                                                            </span>
                                                        )}
                                                    </p>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            )}
                        </CardContent>
                    </Card>
                )}

                {/* Link ke permohonan baru */}
                <p className="mt-6 text-center text-sm text-gray-500">
                    Belum mendapat tiket?{' '}
                    <a
                        href="/permohonan"
                        className="font-medium text-orange underline-offset-2 hover:underline"
                    >
                        Ajukan permohonan baru
                    </a>
                </p>
            </section>
        </>
    );
}
