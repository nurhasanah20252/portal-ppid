import type {LucideIcon} from 'lucide-react';
import { cn } from '@/lib/utils';

interface StatCardProps {
    icon: LucideIcon;
    label: string;
    value: string | number;
    description?: string;
    className?: string;
}

export default function StatCard({ icon: Icon, label, value, description, className }: StatCardProps) {
    return (
        <div
            className={cn(
                'group rounded-xl border border-gray-100 bg-white p-6 shadow-sm transition-all duration-200',
                'hover:-translate-y-0.5 hover:shadow-md',
                'border-t-4 border-t-emas',
                className,
            )}
        >
            <div className="flex items-start justify-between">
                <div>
                    <p className="text-sm font-medium text-gray-500">{label}</p>
                    <p className="mt-1 font-heading text-3xl font-bold text-hijau">{value}</p>
                    {description && <p className="mt-1 text-xs text-gray-400">{description}</p>}
                </div>
                <div className="rounded-lg bg-emas/10 p-2.5 text-emas transition-colors group-hover:bg-emas/20">
                    <Icon className="h-6 w-6" />
                </div>
            </div>
        </div>
    );
}
