import { Head, useForm } from '@inertiajs/react';
import { ArrowLeft, AlertTriangle, CheckCircle, Send } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { trackEvent } from '@/lib/tracking';
import { validateField } from '@/lib/validation';

/** Tipe untuk menyimpan error validasi client-side per field */
type FieldErrors = Partial<Record<'permohonan_tiket' | 'nama_pemohon' | 'alasan', string>>;

export default function KeberatanCreate() {
    const { data, setData, post, processing, errors, reset } = useForm({
        permohonan_tiket: '',
        nama_pemohon: '',
        alasan: '',
    });

    const [submitted, setSubmitted] = useState(false);
    const [clientErrors, setClientErrors] = useState<FieldErrors>({});

    /**
     * Validasi satu field secara client-side.
     * Mengembalikan true jika valid, false jika tidak.
     */
    const validateSingleField = (field: keyof FieldErrors, value: string): boolean => {
        let errorMessage = '';

        switch (field) {
            case 'permohonan_tiket':
                if (!value.trim()) {
                    errorMessage = 'Nomor tiket permohonan wajib diisi';
                }
                break;
            case 'nama_pemohon':
                if (!value.trim()) {
                    errorMessage = 'Nama pemohon wajib diisi';
                }
                break;
            case 'alasan': {
                // Gunakan rule uraianInformasi (min 10 karakter) dari lib/validation.ts
                const result = validateField('uraianInformasi', value);
                if (!value.trim()) {
                    errorMessage = 'Alasan keberatan wajib diisi';
                } else if (!result.valid) {
                    errorMessage = 'Alasan keberatan minimal 10 karakter';
                }
                break;
            }
        }

        setClientErrors((prev) => ({ ...prev, [field]: errorMessage }));
        return !errorMessage;
    };

    /** Handler blur untuk validasi inline */
    const handleBlur = (field: keyof FieldErrors) => {
        validateSingleField(field, data[field]);
    };

    /**
     * Validasi semua field sebelum submit.
     * Mengembalikan true jika semua field valid.
     */
    const validateAll = (): boolean => {
        const fields: (keyof FieldErrors)[] = ['permohonan_tiket', 'nama_pemohon', 'alasan'];
        let allValid = true;

        for (const field of fields) {
            const valid = validateSingleField(field, data[field]);
            if (!valid) allValid = false;
        }

        return allValid;
    };

    /** Mendapatkan pesan error gabungan (client + server) untuk satu field */
    const getFieldError = (field: keyof FieldErrors): string | undefined => {
        return errors[field] || clientErrors[field] || undefined;
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        // Cegah submit jika validasi client-side gagal
        if (!validateAll()) {
            return;
        }

        post('/keberatan', {
            onSuccess: () => {
                setSubmitted(true);
                trackEvent('form_interaction', 'submit_keberatan', 'success');
                toast.success('Keberatan berhasil dikirim. Petugas akan menghubungi Anda.');
            },
            onError: (responseErrors) => {
                // Error 422 otomatis di-handle oleh Inertia (mengisi `errors`)
                // Tampilkan toast error untuk memberi tahu pengguna
                if (Object.keys(responseErrors).length > 0) {
                    trackEvent('form_interaction', 'submit_keberatan', 'validation_error');
                    toast.error('Terdapat kesalahan pada data yang Anda kirim. Periksa kembali formulir.');
                } else {
                    trackEvent('error', 'api_error', '/keberatan');
                    toast.error('Terjadi kesalahan pada server. Silakan coba lagi.');
                }
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
                                    setClientErrors({});
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
                        <form onSubmit={handleSubmit} className="space-y-4" noValidate>
                            {/* Field: Nomor Tiket Permohonan */}
                            <div>
                                <Label htmlFor="permohonan_tiket">Nomor Tiket Permohonan *</Label>
                                <Input
                                    id="permohonan_tiket"
                                    value={data.permohonan_tiket}
                                    onChange={(e) => {
                                        setData('permohonan_tiket', e.target.value);
                                        // Hapus error client saat pengguna mengetik
                                        if (clientErrors.permohonan_tiket) {
                                            setClientErrors((prev) => ({ ...prev, permohonan_tiket: '' }));
                                        }
                                    }}
                                    onBlur={() => handleBlur('permohonan_tiket')}
                                    placeholder="PPID-20260609-001"
                                    aria-describedby={getFieldError('permohonan_tiket') ? 'permohonan_tiket-error' : undefined}
                                    className={getFieldError('permohonan_tiket') ? 'border-orange animate-shake' : ''}
                                />
                                <InputError
                                    id="permohonan_tiket-error"
                                    message={getFieldError('permohonan_tiket')}
                                />
                                <p className="mt-1 text-xs text-gray-400">
                                    Masukkan nomor tiket permohonan yang ingin Anda keberatkan.
                                </p>
                            </div>

                            {/* Field: Nama Pemohon */}
                            <div>
                                <Label htmlFor="nama_pemohon">Nama Pemohon *</Label>
                                <Input
                                    id="nama_pemohon"
                                    value={data.nama_pemohon}
                                    onChange={(e) => {
                                        setData('nama_pemohon', e.target.value);
                                        if (clientErrors.nama_pemohon) {
                                            setClientErrors((prev) => ({ ...prev, nama_pemohon: '' }));
                                        }
                                    }}
                                    onBlur={() => handleBlur('nama_pemohon')}
                                    placeholder="Nama lengkap sesuai permohonan"
                                    aria-describedby={getFieldError('nama_pemohon') ? 'nama_pemohon-error' : undefined}
                                    className={getFieldError('nama_pemohon') ? 'border-orange animate-shake' : ''}
                                />
                                <InputError
                                    id="nama_pemohon-error"
                                    message={getFieldError('nama_pemohon')}
                                />
                            </div>

                            {/* Field: Alasan Keberatan */}
                            <div>
                                <Label htmlFor="alasan">Alasan Keberatan *</Label>
                                <textarea
                                    id="alasan"
                                    value={data.alasan}
                                    onChange={(e) => {
                                        setData('alasan', e.target.value);
                                        if (clientErrors.alasan) {
                                            setClientErrors((prev) => ({ ...prev, alasan: '' }));
                                        }
                                    }}
                                    onBlur={() => handleBlur('alasan')}
                                    placeholder="Jelaskan secara detail alasan keberatan Anda..."
                                    rows={5}
                                    aria-describedby={getFieldError('alasan') ? 'alasan-error' : undefined}
                                    className={`border-input w-full rounded-md border bg-transparent px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] ${
                                        getFieldError('alasan') ? 'border-orange animate-shake' : ''
                                    }`}
                                />
                                <p className="mt-1 text-xs text-gray-400">
                                    Minimal 10 karakter. Saat ini: {data.alasan.length} karakter
                                </p>
                                <InputError
                                    id="alasan-error"
                                    message={getFieldError('alasan')}
                                />
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
