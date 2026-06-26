import { Head, router, useForm } from '@inertiajs/react';
import { Eye, Filter } from 'lucide-react';
import { useEffect, useState } from 'react';
import { toast } from 'sonner';

import ConfirmModal from '@/components/confirm-modal';
import { DataTable, type Column } from '@/components/data-table';
import FileUpload from '@/components/file-upload';
import StatusBadge from '@/components/status-badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { PaginatedResponse, Permohonan, StatusPermohonan } from '@/types/ppid';

// ============================================================
// Tipe data halaman
// ============================================================

interface AdminPermohonanPageProps {
    permohonan: PaginatedResponse<Permohonan>;
    filters: {
        status?: StatusPermohonan;
    };
}

/** Opsi filter status permohonan */
const STATUS_OPTIONS: Array<{ value: string; label: string }> = [
    { value: 'semua', label: 'Semua' },
    { value: 'baru', label: 'Baru' },
    { value: 'diproses', label: 'Diproses' },
    { value: 'selesai', label: 'Selesai' },
    { value: 'ditolak', label: 'Ditolak' },
];

/** Label jenis informasi untuk tampilan */
const JENIS_INFORMASI_LABELS: Record<string, string> = {
    salinan_putusan: 'Salinan Putusan',
    laporan_kinerja: 'Laporan Kinerja',
    lainnya: 'Lainnya',
};

// ============================================================
// Komponen Detail Dialog
// ============================================================

interface DetailDialogProps {
    open: boolean;
    onClose: () => void;
    permohonan: Permohonan | null;
    onStatusUpdated: () => void;
}

/**
 * Dialog detail permohonan untuk admin.
 * Menampilkan data pemohon, uraian, file KTP, dan form update status.
 */
function DetailDialog({ open, onClose, permohonan, onStatusUpdated }: DetailDialogProps) {
    const [showConfirm, setShowConfirm] = useState(false);

    // Form untuk update status permohonan
    const { data, setData, post, processing, reset } = useForm<{
        status: StatusPermohonan;
        catatan_admin: string;
        dokumen_balasan: File | null;
    }>({
        status: permohonan?.status ?? 'baru',
        catatan_admin: permohonan?.catatan_admin ?? '',
        dokumen_balasan: null,
    });

    // Sinkronisasi form data saat permohonan yang dipilih berubah
    useEffect(() => {
        if (permohonan) {
            setData({
                status: permohonan.status,
                catatan_admin: permohonan.catatan_admin ?? '',
                dokumen_balasan: null,
            });
        }
    // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [permohonan?.id]);

    /** Handler klik tombol update - tampilkan konfirmasi dulu */
    function handleUpdateClick() {
        setShowConfirm(true);
    }

    /** Handler konfirmasi update status */
    function handleConfirmUpdate() {
        if (!permohonan) return;

        post(`/admin/permohonan/${permohonan.id}/update-status`, {
            forceFormData: true,
            onSuccess: () => {
                toast.success('Status permohonan berhasil diperbarui.');
                setShowConfirm(false);
                reset();
                onClose();
                onStatusUpdated();
            },
            onError: (errors) => {
                setShowConfirm(false);
                const firstError = Object.values(errors)[0];
                toast.error(firstError ?? 'Terjadi kesalahan saat memperbarui status.');
            },
        });
    }

    if (!permohonan) return null;

    return (
        <>
            <Dialog open={open} onOpenChange={(isOpen) => !isOpen && onClose()}>
                <DialogContent className="max-h-[90vh] overflow-y-auto sm:max-w-2xl">
                    <DialogHeader>
                        <DialogTitle>Detail Permohonan #{permohonan.tiket_no}</DialogTitle>
                        <DialogDescription>
                            Kelola permohonan informasi dari pemohon.
                        </DialogDescription>
                    </DialogHeader>

                    <div className="space-y-6">
                        {/* Data Pemohon */}
                        <section>
                            <h3 className="mb-3 text-sm font-semibold text-gray-700">
                                Data Pemohon
                            </h3>
                            <dl className="grid gap-2 text-sm sm:grid-cols-2">
                                <DataItem label="Nama Lengkap" value={permohonan.nama_lengkap} />
                                <DataItem label="NIK" value={permohonan.nik} />
                                <DataItem label="Email" value={permohonan.email} />
                                <DataItem label="No. HP" value={permohonan.no_hp} />
                                <DataItem label="Alamat" value={permohonan.alamat} />
                                <DataItem label="Kota" value={permohonan.kota} />
                                <DataItem label="Provinsi" value={permohonan.provinsi} />
                            </dl>
                        </section>

                        {/* Informasi Permohonan */}
                        <section>
                            <h3 className="mb-3 text-sm font-semibold text-gray-700">
                                Informasi Permohonan
                            </h3>
                            <dl className="grid gap-2 text-sm">
                                <DataItem
                                    label="Jenis Informasi"
                                    value={JENIS_INFORMASI_LABELS[permohonan.jenis_informasi] ?? permohonan.jenis_informasi}
                                />
                                {permohonan.nomor_perkara && (
                                    <DataItem label="Nomor Perkara" value={permohonan.nomor_perkara} />
                                )}
                                <DataItem label="Tujuan" value={permohonan.tujuan} />
                                <div>
                                    <dt className="font-medium text-gray-500">Uraian Informasi</dt>
                                    <dd className="mt-1 whitespace-pre-wrap text-gray-900">
                                        {permohonan.uraian_informasi}
                                    </dd>
                                </div>
                            </dl>
                        </section>

                        {/* File KTP */}
                        {permohonan.ktp_path && (
                            <section>
                                <h3 className="mb-3 text-sm font-semibold text-gray-700">
                                    File KTP
                                </h3>
                                <div className="overflow-hidden rounded-lg border">
                                    <img
                                        src={`/storage/${permohonan.ktp_path}`}
                                        alt={`KTP ${permohonan.nama_lengkap}`}
                                        className="max-h-48 w-auto object-contain"
                                    />
                                </div>
                            </section>
                        )}

                        {/* Form Update Status */}
                        <section className="border-t pt-4">
                            <h3 className="mb-3 text-sm font-semibold text-gray-700">
                                Update Status
                            </h3>
                            <div className="space-y-4">
                                {/* Dropdown status */}
                                <div>
                                    <Label htmlFor="status-select">Status</Label>
                                    <Select
                                        value={data.status}
                                        onValueChange={(value) => setData('status', value as StatusPermohonan)}
                                    >
                                        <SelectTrigger id="status-select" className="mt-1 w-full">
                                            <SelectValue placeholder="Pilih status" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="baru">Baru</SelectItem>
                                            <SelectItem value="diproses">Diproses</SelectItem>
                                            <SelectItem value="selesai">Selesai</SelectItem>
                                            <SelectItem value="ditolak">Ditolak</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                {/* Textarea catatan/balasan admin */}
                                <div>
                                    <Label htmlFor="catatan-admin">Catatan / Balasan</Label>
                                    <textarea
                                        id="catatan-admin"
                                        value={data.catatan_admin}
                                        onChange={(e) => setData('catatan_admin', e.target.value)}
                                        rows={4}
                                        placeholder="Tulis catatan atau balasan untuk pemohon..."
                                        className="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm placeholder:text-gray-400 focus:border-green-600 focus:ring-1 focus:ring-green-600 focus:outline-none"
                                    />
                                </div>

                                {/* Upload dokumen balasan */}
                                <FileUpload
                                    label="Dokumen Balasan (opsional)"
                                    accept="application/pdf,image/jpeg,image/png"
                                    maxSize={10 * 1024 * 1024}
                                    onChange={(file) => setData('dokumen_balasan', file)}
                                />
                            </div>
                        </section>
                    </div>

                    <DialogFooter>
                        <Button variant="outline" onClick={onClose} disabled={processing}>
                            Tutup
                        </Button>
                        <Button onClick={handleUpdateClick} disabled={processing}>
                            Kirim &amp; Update Status
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            {/* Modal konfirmasi sebelum update status */}
            <ConfirmModal
                open={showConfirm}
                onConfirm={handleConfirmUpdate}
                onCancel={() => setShowConfirm(false)}
                title="Konfirmasi Update Status"
                description={`Apakah Anda yakin ingin mengubah status permohonan #${permohonan.tiket_no} menjadi "${data.status}"?`}
                confirmLabel="Ya, Update"
                variant="warning"
                loading={processing}
            />
        </>
    );
}

/** Komponen kecil untuk menampilkan pasangan label-value */
function DataItem({ label, value }: { label: string; value: string }) {
    return (
        <div>
            <dt className="font-medium text-gray-500">{label}</dt>
            <dd className="text-gray-900">{value}</dd>
        </div>
    );
}

// ============================================================
// Halaman Admin Permohonan
// ============================================================

/**
 * Halaman admin untuk mengelola daftar permohonan informasi.
 * Fitur: tabel data, filter status, dialog detail, dan update status.
 */
export default function AdminPermohonan({ permohonan, filters }: AdminPermohonanPageProps) {
    const [selectedPermohonan, setSelectedPermohonan] = useState<Permohonan | null>(null);
    const [dialogOpen, setDialogOpen] = useState(false);
    const [isLoading, setIsLoading] = useState(false);

    // Status filter aktif (default: semua)
    const activeFilter = filters.status ?? 'semua';

    /** Handler perubahan filter status */
    function handleFilterChange(status: string) {
        const params: Record<string, string> = {};

        if (status !== 'semua') {
            params.status = status;
        }

        // Navigasi dengan Inertia router tanpa full page reload
        router.get('/admin/permohonan', params, {
            preserveState: true,
            preserveScroll: true,
            onStart: () => setIsLoading(true),
            onFinish: () => setIsLoading(false),
        });
    }

    /** Handler klik tombol Proses pada baris tabel */
    function handleProcessClick(item: Permohonan) {
        setSelectedPermohonan(item);
        setDialogOpen(true);
    }

    /** Handler pagination */
    function handlePageChange(page: number) {
        const params: Record<string, string> = { page: String(page) };

        if (activeFilter !== 'semua') {
            params.status = activeFilter;
        }

        router.get('/admin/permohonan', params, {
            preserveState: true,
            preserveScroll: true,
            onStart: () => setIsLoading(true),
            onFinish: () => setIsLoading(false),
        });
    }

    /** Refresh data tabel setelah update status */
    function handleStatusUpdated() {
        router.reload({
            only: ['permohonan'],
            onStart: () => setIsLoading(true),
            onFinish: () => setIsLoading(false),
        });
    }

    // Definisi kolom tabel
    const columns: Column<Permohonan>[] = [
        {
            key: 'tiket_no',
            label: 'Tiket',
            sortable: true,
            render: (item) => (
                <span className="font-mono text-xs">{item.tiket_no}</span>
            ),
        },
        {
            key: 'nama_lengkap',
            label: 'Pemohon',
            sortable: true,
        },
        {
            key: 'jenis_informasi',
            label: 'Jenis Informasi',
            render: (item) => (
                <span>{JENIS_INFORMASI_LABELS[item.jenis_informasi] ?? item.jenis_informasi}</span>
            ),
        },
        {
            key: 'status',
            label: 'Status',
            render: (item) => <StatusBadge status={item.status} />,
        },
        {
            key: 'aksi',
            label: 'Aksi',
            render: (item) => (
                <Button
                    size="sm"
                    variant="outline"
                    onClick={() => handleProcessClick(item)}
                    aria-label={`Proses permohonan ${item.tiket_no}`}
                >
                    <Eye className="mr-1 size-4" />
                    Proses
                </Button>
            ),
        },
    ];

    return (
        <>
            <Head title="Manajemen Permohonan" />

            <div className="flex flex-col gap-6 p-4 md:p-6">
                {/* Heading halaman */}
                <div>
                    <h1 className="font-heading text-2xl font-bold text-gray-900">
                        Manajemen Permohonan
                    </h1>
                    <p className="mt-1 text-sm text-gray-500">
                        Kelola permohonan informasi yang masuk
                    </p>
                </div>

                {/* Filter status */}
                <div className="flex flex-wrap items-center gap-2">
                    <Filter className="size-4 text-gray-500" aria-hidden="true" />
                    {STATUS_OPTIONS.map((option) => (
                        <Button
                            key={option.value}
                            variant={activeFilter === option.value ? 'default' : 'outline'}
                            size="sm"
                            onClick={() => handleFilterChange(option.value)}
                            aria-pressed={activeFilter === option.value}
                        >
                            {option.label}
                        </Button>
                    ))}
                </div>

                {/* Tabel data permohonan */}
                <DataTable<Permohonan>
                    columns={columns}
                    data={permohonan.items}
                    pagination={permohonan.pagination}
                    onPageChange={handlePageChange}
                    emptyMessage="Tidak ada permohonan untuk ditampilkan"
                    loading={isLoading}
                />
            </div>

            {/* Dialog detail permohonan */}
            <DetailDialog
                open={dialogOpen}
                onClose={() => {
                    setDialogOpen(false);
                    setSelectedPermohonan(null);
                }}
                permohonan={selectedPermohonan}
                onStatusUpdated={handleStatusUpdated}
            />
        </>
    );
}
