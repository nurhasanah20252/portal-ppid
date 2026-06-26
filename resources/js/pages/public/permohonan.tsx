import { Head, useForm } from '@inertiajs/react';
import { ArrowLeft, ArrowRight, Send, CheckCircle, User, FileText, Shield } from 'lucide-react';
import { useState, useCallback } from 'react';
import FileUpload from '@/components/file-upload';
import InputError from '@/components/input-error';
import ProgressBar from '@/components/progress-bar';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { trackEvent } from '@/lib/tracking';
import { validateField } from '@/lib/validation';
import type { JenisInformasi } from '@/types/ppid';

/** Total langkah form permohonan */
const TOTAL_STEPS = 3;

/** Label untuk setiap langkah di ProgressBar */
const STEP_LABELS = ['Data Pemohon', 'Detail Informasi', 'Persetujuan'];

/** Tipe state error validasi inline */
type FieldErrors = Record<string, string>;

export default function PermohonanCreate() {
    const [step, setStep] = useState(1);

    // State error validasi inline (client-side)
    const [fieldErrors, setFieldErrors] = useState<FieldErrors>({});

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

    /**
     * Validasi satu field saat blur dan simpan error ke state lokal.
     * Mengembalikan true jika field valid.
     */
    const handleBlur = useCallback(
        (fieldName: string, value: unknown) => {
            const result = validateField(fieldName, value);

            setFieldErrors((prev) => {
                if (result.valid) {
                    const updated = { ...prev };

                    delete updated[fieldName];

                    return updated;
                }

                return { ...prev, [fieldName]: result.message };
            });

            return result.valid;
        },
        [],
    );

    /**
     * Mendapatkan pesan error gabungan (prioritas: server error > client error).
     * Server error dari useForm errors memiliki prioritas lebih tinggi.
     */
    const getFieldError = useCallback(
        (fieldName: string): string => {
            // Error dari server (422) diprioritaskan
            if (errors[fieldName as keyof typeof errors]) {
                return errors[fieldName as keyof typeof errors] as string;
            }

            // Error dari validasi client-side
            return fieldErrors[fieldName] ?? '';
        },
        [errors, fieldErrors],
    );

    /** Cek apakah field sedang dalam keadaan error */
    const hasError = useCallback(
        (fieldName: string): boolean => {
            return !!getFieldError(fieldName);
        },
        [getFieldError],
    );

    /**
     * Validasi field wajib yang tidak memiliki rule khusus di validation.ts.
     * Digunakan untuk field alamat, kota, provinsi, tujuan.
     */
    const handleRequiredBlur = useCallback(
        (fieldName: string, value: string, message: string) => {
            setFieldErrors((prev) => {
                if (value.trim()) {
                    const updated = { ...prev };

                    delete updated[fieldName];

                    return updated;
                }

                return { ...prev, [fieldName]: message };
            });
        },
        [],
    );

    /**
     * Validasi semua field pada langkah tertentu.
     * Mengembalikan true jika semua field valid.
     */
    const validateAllFieldsInStep = (currentStep: number): boolean => {
        let allValid = true;
        const newErrors: FieldErrors = { ...fieldErrors };

        if (currentStep === 1) {
            // Validasi field langkah 1
            const step1Fields: Array<{ name: string; value: unknown }> = [
                { name: 'nik', value: data.nik },
                { name: 'namaLengkap', value: data.nama_lengkap },
                { name: 'email', value: data.email },
                { name: 'noHp', value: data.no_hp },
            ];

            for (const field of step1Fields) {
                const result = validateField(field.name, field.value);

                if (!result.valid) {
                    newErrors[field.name] = result.message;
                    allValid = false;
                } else {
                    delete newErrors[field.name];
                }
            }

            // Validasi field wajib tanpa rule khusus di validation.ts
            if (!data.alamat.trim()) {
                newErrors['alamat'] = 'Alamat wajib diisi';
                allValid = false;
            } else {
                delete newErrors['alamat'];
            }

            if (!data.kota.trim()) {
                newErrors['kota'] = 'Kota/Kabupaten wajib diisi';
                allValid = false;
            } else {
                delete newErrors['kota'];
            }

            if (!data.provinsi.trim()) {
                newErrors['provinsi'] = 'Provinsi wajib diisi';
                allValid = false;
            } else {
                delete newErrors['provinsi'];
            }

            // Validasi KTP file jika ada
            if (data.ktp_file) {
                const ktpResult = validateField('ktpFile', data.ktp_file);

                if (!ktpResult.valid) {
                    newErrors['ktpFile'] = ktpResult.message;
                    allValid = false;
                } else {
                    delete newErrors['ktpFile'];
                }
            }
        }

        if (currentStep === 2) {
            // Validasi field langkah 2
            if (!data.jenis_informasi) {
                newErrors['jenis_informasi'] = 'Jenis informasi wajib dipilih';
                allValid = false;
            } else {
                delete newErrors['jenis_informasi'];
            }

            if (!data.tujuan.trim()) {
                newErrors['tujuan'] = 'Tujuan permohonan wajib diisi';
                allValid = false;
            } else {
                delete newErrors['tujuan'];
            }

            const uraianResult = validateField('uraianInformasi', data.uraian_informasi);

            if (!uraianResult.valid) {
                newErrors['uraianInformasi'] = uraianResult.message;
                allValid = false;
            } else {
                delete newErrors['uraianInformasi'];
            }
        }

        if (currentStep === 3) {
            // Validasi persetujuan
            if (!data.setuju_data) {
                newErrors['setuju_data'] = 'Anda harus menyetujui pernyataan ini';
                allValid = false;
            } else {
                delete newErrors['setuju_data'];
            }

            if (!data.setuju_dokumen) {
                newErrors['setuju_dokumen'] = 'Anda harus menyetujui pernyataan ini';
                allValid = false;
            } else {
                delete newErrors['setuju_dokumen'];
            }
        }

        setFieldErrors(newErrors);

        return allValid;
    };

    /** Navigasi ke langkah berikutnya dengan validasi */
    const handleNext = () => {
        if (step < TOTAL_STEPS && validateAllFieldsInStep(step)) {
            setStep(step + 1);
        }
    };

    /** Navigasi ke langkah sebelumnya */
    const handleBack = () => {
        if (step > 1) {
            setStep(step - 1);
        }
    };

    /** Submit form dengan validasi semua langkah terlebih dahulu */
    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        // Validasi semua langkah sebelum submit
        const step1Valid = validateAllFieldsInStep(1);
        const step2Valid = validateAllFieldsInStep(2);
        const step3Valid = validateAllFieldsInStep(3);

        if (!step1Valid || !step2Valid || !step3Valid) {
            // Arahkan ke langkah pertama yang punya error
            if (!step1Valid) {
                setStep(1);
            } else if (!step2Valid) {
                setStep(2);
            }

            return;
        }

        trackEvent('form_interaction', 'submit_permohonan', 'attempt');

        post('/permohonan', {
            onSuccess: (page) => {
                const response = page.props as Record<string, unknown>;
                const tiket = (response.data as Record<string, string>)?.tiket_no ?? '';

                setTiketNo(tiket);
                setSubmitted(true);
                setFieldErrors({});
                trackEvent('form_interaction', 'submit_permohonan', 'success', { ticket_id: tiket });
            },
            onError: (responseErrors) => {
                trackEvent('form_interaction', 'submit_permohonan', 'error');
                // Tracking error API jika bukan error validasi biasa (422)
                if (Object.keys(responseErrors).length === 0) {
                    trackEvent('error', 'api_error', '/permohonan');
                }
                // Server 422 errors ditampilkan otomatis melalui getFieldError()
                // karena useForm.errors diupdate oleh Inertia
            },
        });
    };

    /** Halaman sukses setelah permohonan berhasil dikirim */
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

    /** Label dan ikon langkah */
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

                {/* Progress bar menggunakan komponen ProgressBar */}
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

                    {/* Komponen ProgressBar menggantikan garis progress manual */}
                    <ProgressBar
                        currentStep={step}
                        totalSteps={TOTAL_STEPS}
                        labels={STEP_LABELS}
                        className="mt-2"
                    />
                </div>

                {/* Form */}
                <Card className="border-t-4 border-t-emas">
                    <CardHeader>
                        <CardTitle className="font-heading text-xl text-hijau">
                            Formulir Permohonan Informasi
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} noValidate>
                            {/* Langkah 1: Data Pemohon */}
                            {step === 1 && (
                                <div className="space-y-4">
                                    <h3 className="font-heading flex items-center gap-2 text-lg font-semibold text-gray-800">
                                        <User className="h-5 w-5 text-orange" />
                                        Data Pemohon
                                    </h3>

                                    <div className="grid gap-4 sm:grid-cols-2">
                                        {/* Field NIK */}
                                        <div className="sm:col-span-2">
                                            <Label htmlFor="nik">NIK *</Label>
                                            <Input
                                                id="nik"
                                                value={data.nik}
                                                onChange={(e) => setData('nik', e.target.value.replace(/\D/g, '').slice(0, 16))}
                                                onBlur={() => handleBlur('nik', data.nik)}
                                                placeholder="16 digit NIK"
                                                maxLength={16}
                                                aria-describedby={hasError('nik') ? 'nik-error' : undefined}
                                                aria-invalid={hasError('nik') || undefined}
                                                className={hasError('nik') ? 'border-orange animate-shake' : ''}
                                            />
                                            <InputError id="nik-error" message={getFieldError('nik')} />
                                            <p className="mt-1 text-xs text-gray-400">Masukkan 16 digit NIK sesuai KTP</p>
                                        </div>

                                        {/* Field Nama Lengkap */}
                                        <div className="sm:col-span-2">
                                            <Label htmlFor="nama_lengkap">Nama Lengkap *</Label>
                                            <Input
                                                id="nama_lengkap"
                                                value={data.nama_lengkap}
                                                onChange={(e) => setData('nama_lengkap', e.target.value)}
                                                onBlur={() => handleBlur('namaLengkap', data.nama_lengkap)}
                                                placeholder="Nama lengkap sesuai KTP"
                                                aria-describedby={(hasError('namaLengkap') || hasError('nama_lengkap')) ? 'nama_lengkap-error' : undefined}
                                                aria-invalid={(hasError('namaLengkap') || hasError('nama_lengkap')) || undefined}
                                                className={(hasError('namaLengkap') || hasError('nama_lengkap')) ? 'border-orange animate-shake' : ''}
                                            />
                                            <InputError id="nama_lengkap-error" message={getFieldError('namaLengkap') || getFieldError('nama_lengkap')} />
                                        </div>

                                        {/* Field Alamat */}
                                        <div className="sm:col-span-2">
                                            <Label htmlFor="alamat">Alamat *</Label>
                                            <Input
                                                id="alamat"
                                                value={data.alamat}
                                                onChange={(e) => setData('alamat', e.target.value)}
                                                onBlur={() => handleRequiredBlur('alamat', data.alamat, 'Alamat wajib diisi')}
                                                placeholder="Jl. Merdeka No. 45, RT 02"
                                                aria-describedby={hasError('alamat') ? 'alamat-error' : undefined}
                                                aria-invalid={hasError('alamat') || undefined}
                                                className={hasError('alamat') ? 'border-orange animate-shake' : ''}
                                            />
                                            <InputError id="alamat-error" message={getFieldError('alamat')} />
                                        </div>

                                        {/* Field Kota */}
                                        <div>
                                            <Label htmlFor="kota">Kota/Kabupaten *</Label>
                                            <Input
                                                id="kota"
                                                value={data.kota}
                                                onChange={(e) => setData('kota', e.target.value)}
                                                onBlur={() => handleRequiredBlur('kota', data.kota, 'Kota/Kabupaten wajib diisi')}
                                                placeholder="Penajam Paser Utara"
                                                aria-describedby={hasError('kota') ? 'kota-error' : undefined}
                                                aria-invalid={hasError('kota') || undefined}
                                                className={hasError('kota') ? 'border-orange animate-shake' : ''}
                                            />
                                            <InputError id="kota-error" message={getFieldError('kota')} />
                                        </div>

                                        {/* Field Provinsi */}
                                        <div>
                                            <Label htmlFor="provinsi">Provinsi *</Label>
                                            <Input
                                                id="provinsi"
                                                value={data.provinsi}
                                                onChange={(e) => setData('provinsi', e.target.value)}
                                                onBlur={() => handleRequiredBlur('provinsi', data.provinsi, 'Provinsi wajib diisi')}
                                                placeholder="Kalimantan Timur"
                                                aria-describedby={hasError('provinsi') ? 'provinsi-error' : undefined}
                                                aria-invalid={hasError('provinsi') || undefined}
                                                className={hasError('provinsi') ? 'border-orange animate-shake' : ''}
                                            />
                                            <InputError id="provinsi-error" message={getFieldError('provinsi')} />
                                        </div>

                                        {/* Field No. HP */}
                                        <div>
                                            <Label htmlFor="no_hp">No. HP *</Label>
                                            <Input
                                                id="no_hp"
                                                value={data.no_hp}
                                                onChange={(e) => setData('no_hp', e.target.value.replace(/\D/g, '').slice(0, 13))}
                                                onBlur={() => handleBlur('noHp', data.no_hp)}
                                                placeholder="081234567890"
                                                maxLength={13}
                                                aria-describedby={(hasError('noHp') || hasError('no_hp')) ? 'no_hp-error' : undefined}
                                                aria-invalid={(hasError('noHp') || hasError('no_hp')) || undefined}
                                                className={(hasError('noHp') || hasError('no_hp')) ? 'border-orange animate-shake' : ''}
                                            />
                                            <InputError id="no_hp-error" message={getFieldError('noHp') || getFieldError('no_hp')} />
                                        </div>

                                        {/* Field Email */}
                                        <div>
                                            <Label htmlFor="email">Email *</Label>
                                            <Input
                                                id="email"
                                                type="email"
                                                value={data.email}
                                                onChange={(e) => setData('email', e.target.value)}
                                                onBlur={() => handleBlur('email', data.email)}
                                                placeholder="nama@domain.com"
                                                aria-describedby={hasError('email') ? 'email-error' : undefined}
                                                aria-invalid={hasError('email') || undefined}
                                                className={hasError('email') ? 'border-orange animate-shake' : ''}
                                            />
                                            <InputError id="email-error" message={getFieldError('email')} />
                                        </div>

                                        {/* Field Upload KTP menggunakan FileUpload */}
                                        <div className="sm:col-span-2">
                                            <FileUpload
                                                id="ktp_file"
                                                label="Upload KTP (opsional)"
                                                accept="image/jpeg,image/png"
                                                maxSize={2 * 1024 * 1024}
                                                onChange={(file) => setData('ktp_file', file)}
                                                error={getFieldError('ktpFile') || getFieldError('ktp_file')}
                                            />
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

                                    {/* Field Jenis Informasi */}
                                    <div>
                                        <Label>Jenis Informasi *</Label>
                                        <div className="mt-2 space-y-2" role="radiogroup" aria-label="Jenis Informasi">
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
                                        <InputError id="jenis_informasi-error" message={getFieldError('jenis_informasi')} />
                                    </div>

                                    {/* Field Nomor Perkara (kondisional) */}
                                    {data.jenis_informasi === 'salinan_putusan' && (
                                        <div>
                                            <Label htmlFor="nomor_perkara">Nomor Perkara</Label>
                                            <Input
                                                id="nomor_perkara"
                                                value={data.nomor_perkara}
                                                onChange={(e) => setData('nomor_perkara', e.target.value)}
                                                placeholder="123/Pdt.G/2026/PA.Pjm"
                                                aria-describedby={hasError('nomor_perkara') ? 'nomor_perkara-error' : undefined}
                                                aria-invalid={hasError('nomor_perkara') || undefined}
                                            />
                                            <InputError id="nomor_perkara-error" message={getFieldError('nomor_perkara')} />
                                        </div>
                                    )}

                                    {/* Field Tujuan */}
                                    <div>
                                        <Label htmlFor="tujuan">Tujuan Permohonan *</Label>
                                        <Input
                                            id="tujuan"
                                            value={data.tujuan}
                                            onChange={(e) => setData('tujuan', e.target.value)}
                                            onBlur={() => handleRequiredBlur('tujuan', data.tujuan, 'Tujuan permohonan wajib diisi')}
                                            placeholder="Keperluan banding, penelitian, dll."
                                            aria-describedby={hasError('tujuan') ? 'tujuan-error' : undefined}
                                            aria-invalid={hasError('tujuan') || undefined}
                                            className={hasError('tujuan') ? 'border-orange animate-shake' : ''}
                                        />
                                        <InputError id="tujuan-error" message={getFieldError('tujuan')} />
                                    </div>

                                    {/* Field Uraian Informasi */}
                                    <div>
                                        <Label htmlFor="uraian_informasi">Uraian Informasi *</Label>
                                        <textarea
                                            id="uraian_informasi"
                                            value={data.uraian_informasi}
                                            onChange={(e) => setData('uraian_informasi', e.target.value)}
                                            onBlur={() => handleBlur('uraianInformasi', data.uraian_informasi)}
                                            placeholder="Jelaskan secara detail informasi yang Anda butuhkan..."
                                            rows={4}
                                            aria-describedby={(hasError('uraianInformasi') || hasError('uraian_informasi')) ? 'uraian_informasi-error' : undefined}
                                            aria-invalid={(hasError('uraianInformasi') || hasError('uraian_informasi')) || undefined}
                                            className={`border-input w-full rounded-md border bg-transparent px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] ${
                                                (hasError('uraianInformasi') || hasError('uraian_informasi')) ? 'border-orange animate-shake' : ''
                                            }`}
                                        />
                                        <p className="mt-1 text-xs text-gray-400">
                                            Minimal 10 karakter. Saat ini: {data.uraian_informasi.length} karakter
                                        </p>
                                        <InputError id="uraian_informasi-error" message={getFieldError('uraianInformasi') || getFieldError('uraian_informasi')} />
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

                                    {/* Ringkasan data permohonan */}
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

                                    {/* Checkbox persetujuan */}
                                    <div className="space-y-3">
                                        <label className="flex items-start gap-3 text-sm">
                                            <input
                                                type="checkbox"
                                                checked={data.setuju_data}
                                                onChange={(e) => {
                                                    setData('setuju_data', e.target.checked);

                                                    if (e.target.checked) {
                                                        setFieldErrors((prev) => {
                                                            const updated = { ...prev };

                                                            delete updated['setuju_data'];

                                                            return updated;
                                                        });
                                                    }
                                                }}
                                                aria-describedby={hasError('setuju_data') ? 'setuju_data-error' : undefined}
                                                aria-invalid={hasError('setuju_data') || undefined}
                                                className="mt-0.5 h-4 w-4 rounded text-hijau focus:ring-hijau"
                                            />
                                            <span className="text-gray-700">
                                                Saya menyatakan data ini benar dan siap mematuhi ketentuan PPID.
                                            </span>
                                        </label>
                                        <InputError id="setuju_data-error" message={getFieldError('setuju_data')} />

                                        <label className="flex items-start gap-3 text-sm">
                                            <input
                                                type="checkbox"
                                                checked={data.setuju_dokumen}
                                                onChange={(e) => {
                                                    setData('setuju_dokumen', e.target.checked);

                                                    if (e.target.checked) {
                                                        setFieldErrors((prev) => {
                                                            const updated = { ...prev };

                                                            delete updated['setuju_dokumen'];

                                                            return updated;
                                                        });
                                                    }
                                                }}
                                                aria-describedby={hasError('setuju_dokumen') ? 'setuju_dokumen-error' : undefined}
                                                aria-invalid={hasError('setuju_dokumen') || undefined}
                                                className="mt-0.5 h-4 w-4 rounded text-hijau focus:ring-hijau"
                                            />
                                            <span className="text-gray-700">
                                                Saya tidak akan menyebarluaskan dokumen tanpa izin.
                                            </span>
                                        </label>
                                        <InputError id="setuju_dokumen-error" message={getFieldError('setuju_dokumen')} />
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
                                        className="bg-orange text-white hover:bg-orange-light"
                                    >
                                        Selanjutnya
                                        <ArrowRight className="h-4 w-4" />
                                    </Button>
                                ) : (
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
