import { Head, router, useForm } from '@inertiajs/react';
import { Pencil, Plus, Trash2 } from 'lucide-react';
import { useEffect, useState } from 'react';
import { toast } from 'sonner';

import ConfirmModal from '@/components/confirm-modal';
import { DataTable, type Column } from '@/components/data-table';
import FileUpload from '@/components/file-upload';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { InformasiPublik, KategoriInformasi, PaginatedResponse } from '@/types/ppid';

// ============================================================
// Tipe data halaman
// ============================================================

interface AdminInformasiPublikPageProps {
    informasi: PaginatedResponse<InformasiPublik>;
}

/** Data form untuk operasi CRUD informasi publik */
interface InformasiFormData {
    judul: string;
    kategori: KategoriInformasi | '';
    sub_kategori: string;
    deskripsi: string;
    file: File | null;
    tahun: string;
    nomor_perkara: string;
}

/** Label mapping untuk kategori informasi */
const KATEGORI_LABELS: Record<KategoriInformasi, string> = {
    berkala: 'Berkala',
    serta_merta: 'Serta Merta',
    setiap_saat: 'Setiap Saat',
};

/** Opsi kategori untuk select dropdown */
const KATEGORI_OPTIONS: Array<{ value: KategoriInformasi; label: string }> = [
    { value: 'berkala', label: 'Berkala' },
    { value: 'serta_merta', label: 'Serta Merta' },
    { value: 'setiap_saat', label: 'Setiap Saat' },
];

// ============================================================
// Komponen Form Dialog
// ============================================================

interface FormDialogProps {
    open: boolean;
    onClose: () => void;
    informasi: InformasiPublik | null;
    onSuccess: () => void;
}

/**
 * Dialog form untuk menambah atau mengedit informasi publik.
 * Form berisi field: judul, kategori, sub-kategori, deskripsi, file PDF, tahun, nomor perkara.
 */
function FormDialog({ open, onClose, informasi, onSuccess }: FormDialogProps) {
    const isEditing = informasi !== null;

    // Form state menggunakan useForm Inertia
    const { data, setData, post, put, processing, errors, reset, clearErrors } = useForm<InformasiFormData>({
        judul: '',
        kategori: '',
        sub_kategori: '',
        deskripsi: '',
        file: null,
        tahun: String(new Date().getFullYear()),
        nomor_perkara: '',
    });

    // Sinkronisasi form data saat informasi yang dipilih berubah
    useEffect(() => {
        if (informasi) {
            setData({
                judul: informasi.judul,
                kategori: informasi.kategori,
                sub_kategori: informasi.sub_kategori ?? '',
                deskripsi: informasi.deskripsi ?? '',
                file: null,
                tahun: String(informasi.tahun),
                nomor_perkara: informasi.nomor_perkara ?? '',
            });
        } else {
            reset();
        }
        clearErrors();
    // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [informasi?.id, open]);

    /** Handler submit form */
    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();

        const options = {
            forceFormData: true,
            onSuccess: () => {
                toast.success(
                    isEditing
                        ? 'Informasi publik berhasil diperbarui.'
                        : 'Informasi publik berhasil ditambahkan.',
                );
                reset();
                onClose();
                onSuccess();
            },
            onError: (formErrors: Record<string, string>) => {
                const firstError = Object.values(formErrors)[0];
                if (firstError) {
                    toast.error(firstError);
                }
            },
        };

        if (isEditing) {
            put(`/admin/informasi-publik/${informasi.id}`, options);
        } else {
            post('/admin/informasi-publik', options);
        }
    }

    return (
        <Dialog open={open} onOpenChange={(isOpen) => !isOpen && onClose()}>
            <DialogContent className="max-h-[90vh] overflow-y-auto sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>
                        {isEditing ? 'Edit Informasi Publik' : 'Tambah Informasi Publik'}
                    </DialogTitle>
                    <DialogDescription>
                        {isEditing
                            ? 'Perbarui data informasi publik yang sudah ada.'
                            : 'Isi form berikut untuk menambah informasi publik baru.'}
                    </DialogDescription>
                </DialogHeader>

                <form onSubmit={handleSubmit} className="space-y-4">
                    {/* Field: Judul */}
                    <div>
                        <Label htmlFor="judul">Judul *</Label>
                        <Input
                            id="judul"
                            value={data.judul}
                            onChange={(e) => setData('judul', e.target.value)}
                            placeholder="Masukkan judul informasi"
                            aria-invalid={!!errors.judul}
                            aria-describedby={errors.judul ? 'judul-error' : undefined}
                            className="mt-1"
                        />
                        <InputError id="judul-error" message={errors.judul} className="mt-1" />
                    </div>

                    {/* Field: Kategori */}
                    <div>
                        <Label htmlFor="kategori">Kategori *</Label>
                        <Select
                            value={data.kategori}
                            onValueChange={(value) => setData('kategori', value as KategoriInformasi)}
                        >
                            <SelectTrigger
                                id="kategori"
                                className="mt-1 w-full"
                                aria-invalid={!!errors.kategori}
                                aria-describedby={errors.kategori ? 'kategori-error' : undefined}
                            >
                                <SelectValue placeholder="Pilih kategori" />
                            </SelectTrigger>
                            <SelectContent>
                                {KATEGORI_OPTIONS.map((option) => (
                                    <SelectItem key={option.value} value={option.value}>
                                        {option.label}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        <InputError id="kategori-error" message={errors.kategori} className="mt-1" />
                    </div>

                    {/* Field: Sub-kategori */}
                    <div>
                        <Label htmlFor="sub_kategori">Sub-kategori</Label>
                        <Input
                            id="sub_kategori"
                            value={data.sub_kategori}
                            onChange={(e) => setData('sub_kategori', e.target.value)}
                            placeholder="Masukkan sub-kategori (opsional)"
                            className="mt-1"
                        />
                    </div>

                    {/* Field: Deskripsi */}
                    <div>
                        <Label htmlFor="deskripsi">Deskripsi</Label>
                        <textarea
                            id="deskripsi"
                            value={data.deskripsi}
                            onChange={(e) => setData('deskripsi', e.target.value)}
                            rows={4}
                            placeholder="Masukkan deskripsi informasi..."
                            className="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm placeholder:text-gray-400 focus:border-green-600 focus:ring-1 focus:ring-green-600 focus:outline-none"
                        />
                    </div>

                    {/* Field: Upload File PDF */}
                    <FileUpload
                        label="File PDF"
                        accept="application/pdf"
                        maxSize={10 * 1024 * 1024}
                        onChange={(file) => setData('file', file)}
                        error={errors.file}
                    />

                    {/* Field: Tahun */}
                    <div>
                        <Label htmlFor="tahun">Tahun *</Label>
                        <Input
                            id="tahun"
                            type="number"
                            value={data.tahun}
                            onChange={(e) => setData('tahun', e.target.value)}
                            placeholder="Contoh: 2024"
                            min={2000}
                            max={new Date().getFullYear() + 1}
                            aria-invalid={!!errors.tahun}
                            aria-describedby={errors.tahun ? 'tahun-error' : undefined}
                            className="mt-1"
                        />
                        <InputError id="tahun-error" message={errors.tahun} className="mt-1" />
                    </div>

                    {/* Field: Nomor Perkara (opsional) */}
                    <div>
                        <Label htmlFor="nomor_perkara">Nomor Perkara</Label>
                        <Input
                            id="nomor_perkara"
                            value={data.nomor_perkara}
                            onChange={(e) => setData('nomor_perkara', e.target.value)}
                            placeholder="Masukkan nomor perkara (opsional)"
                            className="mt-1"
                        />
                    </div>

                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={onClose} disabled={processing}>
                            Batal
                        </Button>
                        <Button type="submit" disabled={processing}>
                            {processing ? 'Menyimpan...' : isEditing ? 'Simpan Perubahan' : 'Tambah Informasi'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

// ============================================================
// Halaman Admin Informasi Publik
// ============================================================

/**
 * Halaman admin untuk mengelola daftar informasi publik.
 * Fitur: tabel data, tambah, edit, hapus, dan pagination.
 */
export default function AdminInformasiPublik({ informasi }: AdminInformasiPublikPageProps) {
    const [formDialogOpen, setFormDialogOpen] = useState(false);
    const [editItem, setEditItem] = useState<InformasiPublik | null>(null);
    const [deleteItem, setDeleteItem] = useState<InformasiPublik | null>(null);
    const [isLoading, setIsLoading] = useState(false);
    const [isDeleting, setIsDeleting] = useState(false);

    /** Buka dialog tambah informasi baru */
    function handleTambahClick() {
        setEditItem(null);
        setFormDialogOpen(true);
    }

    /** Buka dialog edit dengan data terisi */
    function handleEditClick(item: InformasiPublik) {
        setEditItem(item);
        setFormDialogOpen(true);
    }

    /** Buka modal konfirmasi hapus */
    function handleDeleteClick(item: InformasiPublik) {
        setDeleteItem(item);
    }

    /** Handler konfirmasi hapus */
    function handleConfirmDelete() {
        if (!deleteItem) return;

        setIsDeleting(true);

        router.delete(`/admin/informasi-publik/${deleteItem.id}`, {
            onSuccess: () => {
                toast.success('Informasi publik berhasil dihapus.');
                setDeleteItem(null);
            },
            onError: () => {
                toast.error('Gagal menghapus informasi publik.');
            },
            onFinish: () => {
                setIsDeleting(false);
            },
        });
    }

    /** Handler pagination */
    function handlePageChange(page: number) {
        router.get('/admin/informasi-publik', { page: String(page) }, {
            preserveState: true,
            preserveScroll: true,
            onStart: () => setIsLoading(true),
            onFinish: () => setIsLoading(false),
        });
    }

    /** Handler sorting kolom */
    function handleSort(key: string, direction: 'asc' | 'desc') {
        router.get('/admin/informasi-publik', { sort: key, direction }, {
            preserveState: true,
            preserveScroll: true,
            onStart: () => setIsLoading(true),
            onFinish: () => setIsLoading(false),
        });
    }

    /** Refresh data tabel setelah operasi CRUD */
    function handleSuccess() {
        router.reload({
            only: ['informasi'],
            onStart: () => setIsLoading(true),
            onFinish: () => setIsLoading(false),
        });
    }

    // Definisi kolom tabel
    const columns: Column<InformasiPublik>[] = [
        {
            key: 'judul',
            label: 'Judul',
            sortable: true,
        },
        {
            key: 'kategori',
            label: 'Kategori',
            render: (item) => (
                <Badge variant="secondary">
                    {KATEGORI_LABELS[item.kategori] ?? item.kategori}
                </Badge>
            ),
        },
        {
            key: 'tahun',
            label: 'Tahun',
        },
        {
            key: 'is_published',
            label: 'Status Publikasi',
            render: (item) => (
                <Badge variant={item.is_published ? 'default' : 'outline'}>
                    {item.is_published ? 'Published' : 'Draft'}
                </Badge>
            ),
        },
        {
            key: 'aksi',
            label: 'Aksi',
            render: (item) => (
                <div className="flex items-center gap-2">
                    {/* Tombol Edit */}
                    <Button
                        size="sm"
                        variant="outline"
                        onClick={() => handleEditClick(item)}
                        aria-label={`Edit informasi ${item.judul}`}
                    >
                        <Pencil className="mr-1 size-4" />
                        Edit
                    </Button>

                    {/* Tombol Hapus */}
                    <Button
                        size="sm"
                        variant="outline"
                        onClick={() => handleDeleteClick(item)}
                        aria-label={`Hapus informasi ${item.judul}`}
                        className="text-red-600 hover:bg-red-50 hover:text-red-700"
                    >
                        <Trash2 className="mr-1 size-4" />
                        Hapus
                    </Button>
                </div>
            ),
        },
    ];

    return (
        <>
            <Head title="Kelola Informasi Publik" />

            <div className="flex flex-col gap-6 p-4 md:p-6">
                {/* Heading halaman dan tombol tambah */}
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 className="font-heading text-2xl font-bold text-gray-900">
                            Kelola Informasi Publik
                        </h1>
                        <p className="mt-1 text-sm text-gray-500">
                            Tambah, edit, dan hapus dokumen informasi publik
                        </p>
                    </div>

                    <Button onClick={handleTambahClick} className="w-full sm:w-auto">
                        <Plus className="mr-1 size-4" />
                        Tambah Informasi
                    </Button>
                </div>

                {/* Tabel data informasi publik */}
                <DataTable<InformasiPublik>
                    columns={columns}
                    data={informasi.items}
                    pagination={informasi.pagination}
                    onPageChange={handlePageChange}
                    onSort={handleSort}
                    emptyMessage="Belum ada informasi publik. Klik 'Tambah Informasi' untuk menambahkan."
                    loading={isLoading}
                />
            </div>

            {/* Dialog form tambah/edit */}
            <FormDialog
                open={formDialogOpen}
                onClose={() => {
                    setFormDialogOpen(false);
                    setEditItem(null);
                }}
                informasi={editItem}
                onSuccess={handleSuccess}
            />

            {/* Modal konfirmasi hapus */}
            <ConfirmModal
                open={deleteItem !== null}
                onConfirm={handleConfirmDelete}
                onCancel={() => setDeleteItem(null)}
                title="Hapus Informasi Publik"
                description={`Apakah Anda yakin ingin menghapus "${deleteItem?.judul}"? Tindakan ini tidak dapat dibatalkan.`}
                confirmLabel="Ya, Hapus"
                variant="danger"
                loading={isDeleting}
            />
        </>
    );
}
