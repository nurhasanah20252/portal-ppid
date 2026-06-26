import { Head } from '@inertiajs/react';
import { Download, FileSpreadsheet } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';

import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

// ============================================================
// Konstanta
// ============================================================

/** Daftar nama bulan dalam Bahasa Indonesia */
const NAMA_BULAN: Array<{ value: string; label: string }> = [
    { value: '1', label: 'Januari' },
    { value: '2', label: 'Februari' },
    { value: '3', label: 'Maret' },
    { value: '4', label: 'April' },
    { value: '5', label: 'Mei' },
    { value: '6', label: 'Juni' },
    { value: '7', label: 'Juli' },
    { value: '8', label: 'Agustus' },
    { value: '9', label: 'September' },
    { value: '10', label: 'Oktober' },
    { value: '11', label: 'November' },
    { value: '12', label: 'Desember' },
];

/**
 * Menghasilkan daftar tahun untuk pilihan dropdown.
 * Rentang: 2 tahun sebelumnya sampai tahun sekarang.
 */
function generateTahunOptions(): Array<{ value: string; label: string }> {
    const tahunSekarang = new Date().getFullYear();
    const options: Array<{ value: string; label: string }> = [];

    for (let tahun = tahunSekarang; tahun >= tahunSekarang - 2; tahun--) {
        options.push({ value: String(tahun), label: String(tahun) });
    }

    return options;
}

// ============================================================
// Halaman Admin Laporan
// ============================================================

/**
 * Halaman laporan admin PPID.
 * Menyediakan filter bulan/tahun dan tombol export ke Excel.
 * File Excel dihasilkan di sisi server.
 */
export default function AdminLaporan() {
    const tahunSekarang = new Date().getFullYear();
    const bulanSekarang = new Date().getMonth() + 1;

    // State filter bulan dan tahun
    const [bulan, setBulan] = useState<string>(String(bulanSekarang));
    const [tahun, setTahun] = useState<string>(String(tahunSekarang));

    // Opsi tahun yang tersedia
    const tahunOptions = generateTahunOptions();

    /**
     * Handler untuk export data ke Excel.
     * Memicu download file dari endpoint server.
     */
    function handleExport() {
        const url = `/admin/laporan/export?bulan=${bulan}&tahun=${tahun}`;

        // Gunakan window.location.href untuk memicu download file
        window.location.href = url;

        // Tampilkan toast sebagai feedback ke pengguna
        const namaBulan = NAMA_BULAN.find((b) => b.value === bulan)?.label ?? bulan;
        toast.success(`Mengunduh laporan ${namaBulan} ${tahun}...`);
    }

    return (
        <>
            <Head title="Laporan Permohonan" />

            <div className="flex flex-col gap-6 p-4 md:p-6">
                {/* Heading halaman */}
                <div>
                    <h1 className="font-heading text-2xl font-bold text-gray-900">
                        Laporan Permohonan
                    </h1>
                    <p className="mt-1 text-sm text-gray-500">
                        Export data permohonan informasi ke format Excel
                    </p>
                </div>

                {/* Section filter dan export */}
                <div className="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                    <div className="flex items-center gap-2 mb-4">
                        <FileSpreadsheet className="size-5 text-green-700" aria-hidden="true" />
                        <h2 className="font-heading text-lg font-semibold text-gray-800">
                            Filter Laporan
                        </h2>
                    </div>

                    {/* Form filter bulan dan tahun */}
                    <div className="flex flex-col gap-4 sm:flex-row sm:items-end">
                        {/* Filter bulan */}
                        <div className="flex flex-col gap-1.5">
                            <Label htmlFor="filter-bulan">Bulan</Label>
                            <Select value={bulan} onValueChange={setBulan}>
                                <SelectTrigger id="filter-bulan" className="w-full sm:w-40">
                                    <SelectValue placeholder="Pilih bulan" />
                                </SelectTrigger>
                                <SelectContent>
                                    {NAMA_BULAN.map((item) => (
                                        <SelectItem key={item.value} value={item.value}>
                                            {item.label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>

                        {/* Filter tahun */}
                        <div className="flex flex-col gap-1.5">
                            <Label htmlFor="filter-tahun">Tahun</Label>
                            <Select value={tahun} onValueChange={setTahun}>
                                <SelectTrigger id="filter-tahun" className="w-full sm:w-32">
                                    <SelectValue placeholder="Pilih tahun" />
                                </SelectTrigger>
                                <SelectContent>
                                    {tahunOptions.map((item) => (
                                        <SelectItem key={item.value} value={item.value}>
                                            {item.label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>

                        {/* Tombol Export ke Excel — full width pada mobile */}
                        <Button onClick={handleExport} className="w-full sm:ml-4 sm:w-auto">
                            <Download className="size-4" />
                            Export ke Excel
                        </Button>
                    </div>

                    {/* Keterangan tambahan */}
                    <p className="mt-4 text-xs text-gray-500">
                        File Excel akan berisi data permohonan informasi pada periode yang dipilih.
                    </p>
                </div>
            </div>
        </>
    );
}
