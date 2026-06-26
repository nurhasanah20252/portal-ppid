import { cn } from '@/lib/utils';
import type { StatusLog } from '@/types/ppid';

interface TimelineStatusProps {
    /** Riwayat perubahan status permohonan */
    riwayat: StatusLog[];
    /** Class tambahan untuk container */
    className?: string;
}

/**
 * Komponen timeline vertikal untuk menampilkan riwayat status permohonan.
 * Status terbaru ditampilkan dengan warna hijau aktif, status lama berwarna abu-abu.
 */
export default function TimelineStatus({ riwayat, className }: TimelineStatusProps) {
    if (riwayat.length === 0) {
        return (
            <p className="text-sm text-gray-500" role="status">
                Belum ada riwayat status.
            </p>
        );
    }

    // Urutkan riwayat dari terbaru ke terlama
    const sortedRiwayat = [...riwayat].sort(
        (a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime(),
    );

    return (
        <ol
            aria-label="Riwayat status permohonan"
            className={cn('relative space-y-6', className)}
        >
            {sortedRiwayat.map((log, index) => {
                const isLatest = index === 0;

                return (
                    <li key={log.id} className="relative flex gap-4">
                        {/* Garis penghubung vertikal */}
                        {index < sortedRiwayat.length - 1 && (
                            <div
                                aria-hidden="true"
                                className={cn(
                                    'absolute top-5 left-[9px] h-[calc(100%+8px)] w-0.5',
                                    isLatest ? 'bg-hijau/30' : 'bg-gray-200',
                                )}
                            />
                        )}

                        {/* Dot indikator */}
                        <div
                            aria-hidden="true"
                            className={cn(
                                'relative z-10 mt-1 h-[18px] w-[18px] shrink-0 rounded-full border-2',
                                isLatest
                                    ? 'border-hijau bg-hijau shadow-[0_0_0_3px_rgba(27,94,32,0.2)]'
                                    : 'border-gray-300 bg-white',
                            )}
                        />

                        {/* Konten status */}
                        <div className="flex-1 pb-1">
                            <p
                                className={cn(
                                    'text-sm font-semibold',
                                    isLatest ? 'text-hijau' : 'text-gray-700',
                                )}
                            >
                                {formatStatusLabel(log.status_baru)}
                            </p>

                            {log.catatan && (
                                <p className="mt-0.5 text-sm text-gray-600">
                                    {log.catatan}
                                </p>
                            )}

                            <time
                                dateTime={log.created_at}
                                className="mt-1 block text-xs text-gray-400"
                            >
                                {formatTanggal(log.created_at)}
                            </time>
                        </div>
                    </li>
                );
            })}
        </ol>
    );
}

/** Mapping label status agar lebih mudah dibaca */
const statusLabels: Record<string, string> = {
    baru: 'Baru',
    diproses: 'Diproses',
    selesai: 'Selesai',
    ditolak: 'Ditolak',
};

/**
 * Mengubah nilai status menjadi label yang mudah dibaca.
 * Jika status tidak dikenali, gunakan kapitalisasi otomatis.
 */
function formatStatusLabel(status: string): string {
    return statusLabels[status] ?? status.charAt(0).toUpperCase() + status.slice(1);
}

/**
 * Memformat tanggal ke format Indonesia (contoh: 15 Juni 2025, 14:30).
 * Menggunakan locale id-ID untuk konsistensi tampilan.
 */
function formatTanggal(dateString: string): string {
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'long',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    } catch {
        return dateString;
    }
}
