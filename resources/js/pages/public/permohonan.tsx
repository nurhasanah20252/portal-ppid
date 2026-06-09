import { Head, useForm } from '@inertiajs/react';
import { ArrowLeft, ArrowRight, Send, CheckCircle, User, FileText, Shield } from 'lucide-react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { trackEvent } from '@/lib/tracking';
import type { JenisInformasi } from '@/types/ppid';

/** Total langkah form permohonan */
const TOTAL_STEPS = 3;

export default function PermohonanCreate() {
    const [step, setStep] = useState(1);

    const { data, setData, post, processing, errors } = useForm({
        nik: '',
        nama_lengkap: '',
        alamat: '',
        kota: '',
        provinsi: '',
        no_hp: '',
        email: '',
        ktp_file: null as File | null,
        jenis_informasi: 'salinan_putusan' as JenisInformasi,
        nomor_perkara: '',
        tujuan: '',
        uraian_informasi: '',
        setuju_data: false,
        setuju_dokumen: false,
    });

    const [submitted, setSubmitted] = useState(false);
    const [tiketNo, setTiketNo] = useState('');

    /** Validasi per langkah */
    const validateStep = (currentStep: number): boolean => {
        switch (currentStep) {
            case 1:
                return (
                    data.nik.length === 16 &&
                    data.nama_lengkap.length >= 3 &&
                    data.email.includes('@') &&
                    data.no_hp.length >= 10 &&
                    data.alamat.length > 0
                );
            case 2:
                return (
                    data.jenis_informasi.length > 0 &&
                    data.tujuan.length > 0 &&
                    data.uraian_informasi.length >= 10
                );
            case 3:
                return data.setuju_data && data.setuju_dokumen;
            default:
                return false;
        }
    };

    const handleNext = () => {
        if (step < TOTAL_STEPS && validateStep(step)) {
            setStep(step + 1);
        }
    };

    const handleBack = () => {
        if (step > 1) {
            setStep(step - 1);
        }
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        trackEvent('form_interaction', 'submit_permohonan', 'attempt');

        post('/permohonan', {
            onSuccess: (page) => {
                const response = page.props as Record<string, unknown>;
                const tiket = (response.data as Record<string, string>)?.tiket_no ?? '';
                setTiketNo(tiket);
                setSubmitted(true);
                trackEvent('form_interaction', 'submit_permohonan', 'success', { ticket_id: tiket });
            },
            onError: () => {
                trackEvent('form_interaction', 'submit_permohonan', 'error');
            },
        });
    };

    /** Halaman sukses */
    if (submitted) {
        return (
            <>
                <Head title="Permohonan Berhasil" />
                <section className="mx-auto max-w-2xl px-4 py-16 sm:px-6">
                    <Card className="border-t-4 border-t-green-500 text-center">
                        <CardContent className="pt-8">
                            <div className="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-green-100">
                                <CheckCircle className="h-8 w-8 text-green-600" />
                            </div>
                            <h2 className="font-heading text-2xl font-bold text-hijau">
                                Permohonan Berhasil Dikirim!
                            </h2>
                            <p className="mt-2 text-gray-600">
                                Nomor tiket Anda:
                            </p>
                            <p className="mt-2 inline-block rounded-lg bg-hijau/10 px-6 py-3 font-heading text-2xl font-bold tracking-wider text-hijau">
                                {tiketNo}
                            </p>
                            <p className="mt-4 text-sm text-gray-500">
                                Simpan nomor tiket ini untuk melacak status permohonan Anda.
                                Cek email Anda untuk detail lebih lanjut.
                            </p>
                        </CardContent>
                    </Card>
                </section>
            </>
        );
    }

    /** Label langkah */
    const stepLabels = [
        { icon: User, label: 'Data Pemohon' },
        { icon: FileText, label: 'Detail Informasi' },
        { icon: Shield, label: 'Persetujuan' },
    ];

    return (
        <>
            <Head title="Ajukan Permohonan Informasi" />

            <section className="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:py-12">
                {/* Tombol kembali */}
                <a
                    href="/"
                    className="mb-6 inline-flex items-center gap-1 text-sm text-gray-500 transition-colors hover:text-hijau"
                >
                    <ArrowLeft className="h-4 w-4" />
                    Kembali ke Beranda
                </a>

                {/* Progress bar */}
                <div className="mb-8">
                    <div className="flex items-center justify-between">
                        {stepLabels.map((s, i) => {
                            const stepNum = i + 1;
                            const isActive = step === stepNum;
                            const isCompleted = step > stepNum;

                            return (
                                <div key={stepNum} className="flex flex-1 flex-col items-center">
                                    <div
                                        className={`flex h-10 w-10 items-center justify-center rounded-full border-2 transition-colors ${
                                            isCompleted
                                                ? 'border-hijau bg-hijau text-white'
                                                : isActive
                                                    ? 'border-hijau bg-white text-hijau'
                                                    : 'border-gray-300 bg-white text-gray-400'
                                        }`}
                                    >
                                        {isCompleted ? (
                                            <CheckCircle className="h-5 w-5" />
                                        ) : (
                                            <s.icon className="h-5 w-5" />
                                        )}
                                    </div>
                                    <p
                                        className={`mt-2 text-xs font-medium ${
                                            isActive ? 'text-hijau' : 'text-gray-400'
                                        }`}
                                    >
                                        {s.label}
                                    </p>
                                </div>
                            );
                        })}
                    </div>
                    {/* Garis progress */}
                    <div className="relative mt-2 h-1.5 rounded-full bg-gray-200">
                        <div
                            className="absolute left-0 top-0 h-full rounded-full bg-hijau transition-all duration-300"
                            style={{ width: `${((step - 1) / (TOTAL_STEPS - 1)) * 100}%` }}
                        />
                    </div>
                    <p className="mt-2 text-center text-sm text-gray-500">
                        Langkah {step} dari {TOTAL_STEPS}
                    </p>
                </div>

                {/* Form */}
                <Card className="border-t-4 border-t-emas">
                    <CardHeader>
                        <CardTitle className="font-heading text-xl text-hijau">
                            Formulir Permohonan Informasi
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit}>
                            {/* Langkah 1: Data Pemohon */}
                            {step === 1 && (
                                <div className="space-y-4">
                                    <h3 className="font-heading flex items-center gap-2 text-lg font-semibold text-gray-800">
                                        <User className="h-5 w-5 text-orange" />
                                        Data Pemohon
                                    </h3>

                                    <div className="grid gap-4 sm:grid-cols-2">
                                        <div className="sm:col-span-2">
                                            <Label htmlFor="nik">NIK *</Label>
                                            <Input
                                                id="nik"
                                                value={data.nik}
                                                onChange={(e) => setData('nik', e.target.value.replace(/\D/g, '').slice(0, 16))}
                                                placeholder="16 digit NIK"
                                                maxLength={16}
                                                className={errors.nik ? 'border-orange animate-shake' : ''}
                                            />
                                            <InputError message={errors.nik} />
                                            <p className="mt-1 text-xs text-gray-400">Masukkan 16 digit NIK sesuai KTP</p>
                                        </div>

                                        <div className="sm:col-span-2">
                                            <Label htmlFor="nama_lengkap">Nama Lengkap *</Label>
                                            <Input
                                                id="nama_lengkap"
                                                value={data.nama_lengkap}
                                                onChange={(e) => setData('nama_lengkap', e.target.value)}
                                                placeholder="Nama lengkap sesuai KTP"
                                                className={errors.nama_lengkap ? 'border-orange animate-shake' : ''}
                                            />
                                            <InputError message={errors.nama_lengkap} />
                                        </div>

                                        <div className="sm:col-span-2">
                                            <Label htmlFor="alamat">Alamat *</Label>
                                            <Input
                                                id="alamat"
                                                value={data.alamat}
                                                onChange={(e) => setData('alamat', e.target.value)}
                                                placeholder="Jl. Merdeka No. 45, RT 02"
                                                className={errors.alamat ? 'border-orange animate-shake' : ''}
                                            />
                                            <InputError message={errors.alamat} />
                                        </div>

                                        <div>
                                            <Label htmlFor="kota">Kota/Kabupaten *</Label>
                                            <Input
                                                id="kota"
                                                value={data.kota}
                                                onChange={(e) => setData('kota', e.target.value)}
                                                placeholder="Penajam Paser Utara"
                                            />
                                            <InputError message={errors.kota} />
                                        </div>

                                        <div>
                                            <Label htmlFor="provinsi">Provinsi *</Label>
                                            <Input
                                                id="provinsi"
                                                value={data.provinsi}
                                                onChange={(e) => setData('provinsi', e.target.value)}
                                                placeholder="Kalimantan Timur"
                                            />
                                            <InputError message={errors.provinsi} />
                                        </div>

                                        <div>
                                            <Label htmlFor="no_hp">No. HP *</Label>
                                            <Input
                                                id="no_hp"
                                                value={data.no_hp}
                                                onChange={(e) => setData('no_hp', e.target.value.replace(/\D/g, '').slice(0, 13))}
                                                placeholder="081234567890"
                                                maxLength={13}
                                                className={errors.no_hp ? 'border-orange animate-shake' : ''}
                                            />
                                            <InputError message={errors.no_hp} />
                                        </div>

                                        <div>
                                            <Label htmlFor="email">Email *</Label>
                                            <Input
                                                id="email"
                                                type="email"
                                                value={data.email}
                                                onChange={(e) => setData('email', e.target.value)}
                                                placeholder="nama@domain.com"
                                                className={errors.email ? 'border-orange animate-shake' : ''}
                                            />
                                            <InputError message={errors.email} />
                                        </div>

                                        <div className="sm:col-span-2">
                                            <Label htmlFor="ktp_file">Upload KTP (opsional)</Label>
                                            <Input
                                                id="ktp_file"
                                                type="file"
                                                accept="image/jpeg,image/png"
                                                onChange={(e) => setData('ktp_file', e.target.files?.[0] ?? null)}
                                                className="cursor-pointer"
                                            />
                                            <p className="mt-1 text-xs text-gray-400">Format: JPG/PNG, maksimal 2MB</p>
                                            <InputError message={errors.ktp_file} />
                                        </div>
                                    </div>
                                </div>
                            )}

                            {/* Langkah 2: Detail Informasi */}
                            {step === 2 && (
                                <div className="space-y-4">
                                    <h3 className="font-heading flex items-center gap-2 text-lg font-semibold text-gray-800">
                                        <FileText className="h-5 w-5 text-orange" />
                                        Detail Informasi yang Dimohon
                                    </h3>

                                    <div>
                                        <Label>Jenis Informasi *</Label>
                                        <div className="mt-2 space-y-2">
                                            {[
                                                { value: 'salinan_putusan', label: 'Salinan Putusan' },
                                                { value: 'laporan_kinerja', label: 'Laporan Kinerja' },
                                                { value: 'lainnya', label: 'Lainnya' },
                                            ].map((option) => (
                                                <label
                                                    key={option.value}
                                                    className="flex items-center gap-2 text-sm"
                                                >
                                                    <input
                                                        type="radio"
                                                        name="jenis_informasi"
                                                        value={option.value}
                                                        checked={data.jenis_informasi === option.value}
                                                        onChange={() =>
                                                            setData('jenis_informasi', option.value as JenisInformasi)
                                                        }
                                                        className="h-4 w-4 text-hijau focus:ring-hijau"
                                                    />
                                                    {option.label}
                                                </label>
                                            ))}
                                        </div>
                                        <InputError message={errors.jenis_informasi} />
                                    </div>

                                    {data.jenis_informasi === 'salinan_putusan' && (
                                        <div>
                                            <Label htmlFor="nomor_perkara">Nomor Perkara</Label>
                                            <Input
                                                id="nomor_perkara"
                                                value={data.nomor_perkara}
                                                onChange={(e) => setData('nomor_perkara', e.target.value)}
                                                placeholder="123/Pdt.G/2026/PA.Pjm"
                                            />
                                            <InputError message={errors.nomor_perkara} />
                                        </div>
                                    )}

                                    <div>
                                        <Label htmlFor="tujuan">Tujuan Permohonan *</Label>
                                        <Input
                                            id="tujuan"
                                            value={data.tujuan}
                                            onChange={(e) => setData('tujuan', e.target.value)}
                                            placeholder="Keperluan banding, penelitian, dll."
                                            className={errors.tujuan ? 'border-orange animate-shake' : ''}
                                        />
                                        <InputError message={errors.tujuan} />
                                    </div>

                                    <div>
                                        <Label htmlFor="uraian_informasi">Uraian Informasi *</Label>
                                        <textarea
                                            id="uraian_informasi"
                                            value={data.uraian_informasi}
                                            onChange={(e) => setData('uraian_informasi', e.target.value)}
                                            placeholder="Jelaskan secara detail informasi yang Anda butuhkan..."
                                            rows={4}
                                            className={`border-input w-full rounded-md border bg-transparent px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] ${
                                                errors.uraian_informasi ? 'border-orange animate-shake' : ''
                                            }`}
                                        />
                                        <p className="mt-1 text-xs text-gray-400">
                                            Minimal 10 karakter. Saat ini: {data.uraian_informasi.length} karakter
                                        </p>
                                        <InputError message={errors.uraian_informasi} />
                                    </div>
                                </div>
                            )}

                            {/* Langkah 3: Persetujuan */}
                            {step === 3 && (
                                <div className="space-y-4">
                                    <h3 className="font-heading flex items-center gap-2 text-lg font-semibold text-gray-800">
                                        <Shield className="h-5 w-5 text-orange" />
                                        Persetujuan & Verifikasi
                                    </h3>

                                    <div className="rounded-lg border border-gray-200 bg-gray-50 p-4">
                                        <h4 className="text-sm font-medium text-gray-700">Ringkasan Permohonan</h4>
                                        <dl className="mt-3 space-y-2 text-sm">
                                            <div className="flex justify-between">
                                                <dt className="text-gray-500">Nama:</dt>
                                                <dd className="font-medium text-gray-800">{data.nama_lengkap}</dd>
                                            </div>
                                            <div className="flex justify-between">
                                                <dt className="text-gray-500">NIK:</dt>
                                                <dd className="font-medium text-gray-800">{data.nik}</dd>
                                            </div>
                                            <div className="flex justify-between">
                                                <dt className="text-gray-500">Jenis Informasi:</dt>
                                                <dd className="font-medium text-gray-800">
                                                    {data.jenis_informasi === 'salinan_putusan'
                                                        ? 'Salinan Putusan'
                                                        : data.jenis_informasi === 'laporan_kinerja'
                                                            ? 'Laporan Kinerja'
                                                            : 'Lainnya'}
                                                </dd>
                                            </div>
                                            {data.nomor_perkara && (
                                                <div className="flex justify-between">
                                                    <dt className="text-gray-500">Nomor Perkara:</dt>
                                                    <dd className="font-medium text-gray-800">{data.nomor_perkara}</dd>
                                                </div>
                                            )}
                                        </dl>
                                    </div>

                                    <div className="space-y-3">
                                        <label className="flex items-start gap-3 text-sm">
                                            <input
                                                type="checkbox"
                                                checked={data.setuju_data}
                                                onChange={(e) => setData('setuju_data', e.target.checked)}
                                                className="mt-0.5 h-4 w-4 rounded text-hijau focus:ring-hijau"
                                            />
                                            <span className="text-gray-700">
                                                Saya menyatakan data ini benar dan siap mematuhi ketentuan PPID.
                                            </span>
                                        </label>
                                        <InputError message={errors.setuju_data} />

                                        <label className="flex items-start gap-3 text-sm">
                                            <input
                                                type="checkbox"
                                                checked={data.setuju_dokumen}
                                                onChange={(e) => setData('setuju_dokumen', e.target.checked)}
                                                className="mt-0.5 h-4 w-4 rounded text-hijau focus:ring-hijau"
                                            />
                                            <span className="text-gray-700">
                                                Saya tidak akan menyebarluaskan dokumen tanpa izin.
                                            </span>
                                        </label>
                                        <InputError message={errors.setuju_dokumen} />
                                    </div>
                                </div>
                            )}

                            {/* Navigasi tombol */}
                            <div className="mt-8 flex items-center justify-between border-t border-gray-100 pt-6">
                                {step > 1 ? (
                                    <Button
                                        type="button"
                                        variant="outline"
                                        onClick={handleBack}
                                        className="border-ungu text-ungu hover:bg-ungu/10"
                                    >
                                        <ArrowLeft className="h-4 w-4" />
                                        Kembali
                                    </Button>
                                ) : (
                                    <div />
                                )}

                                {step < TOTAL_STEPS ? (
                                    <Button
                                        type="button"
                                        onClick={handleNext}
                                        disabled={!validateStep(step)}
                                        className="bg-orange text-white hover:bg-orange-light"
                                    >
                                        Selanjutnya
                                        <ArrowRight className="h-4 w-4" />
                                    </Button>
                                ) : (
                                    <Button
                                        type="submit"
                                        disabled={processing || !validateStep(step)}
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
                                                Ajukan Permohonan
                                            </>
                                        )}
                                    </Button>
                                )}
                            </div>
                        </form>

                        <p className="mt-4 text-center text-xs text-gray-400">
                            * Wajib diisi &bull; Informasi akan diproses maksimal 5 hari kerja.
                        </p>
                    </CardContent>
                </Card>
            </section>
        </>
    );
}
