import { cn } from '@/lib/utils';

interface ProgressBarProps {
    /** Langkah saat ini (dimulai dari 1) */
    currentStep: number;
    /** Total langkah dalam form */
    totalSteps: number;
    /** Label untuk setiap langkah */
    labels?: string[];
    /** Class tambahan untuk container */
    className?: string;
}

/**
 * Komponen indikator progres untuk form multi-step.
 * Menampilkan bar horizontal dengan label langkah dan animasi transisi.
 */
export default function ProgressBar({ currentStep, totalSteps, labels, className }: ProgressBarProps) {
    // Hitung persentase progress
    const percentage = Math.min(Math.max((currentStep / totalSteps) * 100, 0), 100);

    return (
        <div className={cn('w-full', className)}>
            {/* Bar progres dengan role progressbar untuk aksesibilitas */}
            <div
                role="progressbar"
                aria-label={`Langkah ${currentStep} dari ${totalSteps}`}
                aria-valuenow={currentStep}
                aria-valuemin={1}
                aria-valuemax={totalSteps}
                className="h-2.5 w-full overflow-hidden rounded-full bg-gray-200"
            >
                {/* Fill bar dengan animasi transisi 300ms */}
                <div
                    className="h-full rounded-full bg-hijau transition-[width] duration-300 ease-in-out"
                    style={{ width: `${percentage}%` }}
                />
            </div>

            {/* Label langkah di bawah bar */}
            {labels && labels.length > 0 && (
                <div className="mt-2 flex justify-between">
                    {labels.map((label, index) => (
                        <span
                            key={index}
                            className={cn(
                                'text-xs font-medium',
                                index + 1 <= currentStep ? 'text-hijau' : 'text-gray-400',
                            )}
                        >
                            {label}
                        </span>
                    ))}
                </div>
            )}
        </div>
    );
}
