import { Badge } from '@/components/ui/badge';
import { cn } from '@/lib/utils';
import type { StatusPermohonan, StatusKeberatan } from '@/types/ppid';

/** Konfigurasi tampilan badge berdasarkan status */
const statusConfig: Record<string, { label: string; className: string }> = {
    baru: { label: 'Baru', className: 'bg-blue-100 text-blue-800 border-blue-200' },
    diproses: { label: 'Diproses', className: 'bg-yellow-100 text-yellow-800 border-yellow-200' },
    selesai: { label: 'Selesai', className: 'bg-green-100 text-green-800 border-green-200' },
    ditolak: { label: 'Ditolak', className: 'bg-red-100 text-red-800 border-red-200' },
    dikirim: { label: 'Dikirim', className: 'bg-blue-100 text-blue-800 border-blue-200' },
};

interface StatusBadgeProps {
    status: StatusPermohonan | StatusKeberatan;
    className?: string;
}

export default function StatusBadge({ status, className }: StatusBadgeProps) {
    const config = statusConfig[status] ?? { label: status, className: 'bg-gray-100 text-gray-800' };

    return (
        <Badge
            variant="outline"
            className={cn('font-medium', config.className, className)}
        >
            {config.label}
        </Badge>
    );
}
