import { LoaderCircle } from 'lucide-react';

import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { cn } from '@/lib/utils';

interface ConfirmModalProps {
    open: boolean;
    onConfirm: () => void;
    onCancel: () => void;
    title: string;
    description?: string;
    confirmLabel?: string;
    cancelLabel?: string;
    variant?: 'default' | 'danger' | 'warning';
    loading?: boolean;
}

/** Konfigurasi style tombol konfirmasi berdasarkan varian */
const variantStyles: Record<string, string> = {
    default: 'bg-green-600 text-white hover:bg-green-700 focus-visible:ring-green-600/20',
    danger: 'bg-red-600 text-white hover:bg-red-700 focus-visible:ring-red-600/20',
    warning: 'bg-amber-500 text-white hover:bg-amber-600 focus-visible:ring-amber-500/20',
};

/**
 * Modal konfirmasi untuk aksi destruktif atau penting.
 * Menggunakan Dialog dari shadcn/ui dengan animasi fade-in dan overlay gelap.
 */
export default function ConfirmModal({
    open,
    onConfirm,
    onCancel,
    title,
    description,
    confirmLabel = 'Konfirmasi',
    cancelLabel = 'Batal',
    variant = 'default',
    loading = false,
}: ConfirmModalProps) {
    return (
        <Dialog open={open} onOpenChange={(isOpen) => !isOpen && onCancel()}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{title}</DialogTitle>
                    {description && (
                        <DialogDescription>{description}</DialogDescription>
                    )}
                </DialogHeader>

                <DialogFooter>
                    {/* Tombol batal */}
                    <Button
                        type="button"
                        variant="outline"
                        onClick={onCancel}
                        disabled={loading}
                    >
                        {cancelLabel}
                    </Button>

                    {/* Tombol konfirmasi dengan style sesuai varian */}
                    <Button
                        type="button"
                        className={cn(variantStyles[variant])}
                        onClick={onConfirm}
                        disabled={loading}
                    >
                        {loading && (
                            <LoaderCircle className="animate-spin" />
                        )}
                        {confirmLabel}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
