import { cn } from '@/lib/utils';

interface SkeletonCardProps {
    className?: string;
    lines?: number;
}

/** Skeleton loading card dengan shimmer effect sesuai UX.md */
export function SkeletonCard({ className, lines = 3 }: SkeletonCardProps) {
    return (
        <div className={cn('rounded-xl border border-gray-100 bg-white p-6 shadow-sm', className)}>
            <div className="mb-4 h-5 w-1/3 animate-shimmer rounded-md" />
            {Array.from({ length: lines }).map((_, i) => (
                <div
                    key={i}
                    className={cn(
                        'h-3 animate-shimmer rounded-md',
                        i === lines - 1 ? 'w-2/3' : 'w-full',
                    )}
                    style={{ marginTop: i === 0 ? 0 : 8 }}
                />
            ))}
        </div>
    );
}

/** Skeleton untuk statistik */
export function SkeletonStat() {
    return (
        <div className="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
            <div className="h-4 w-20 animate-shimmer rounded-md" />
            <div className="mt-2 h-8 w-16 animate-shimmer rounded-md" />
        </div>
    );
}
