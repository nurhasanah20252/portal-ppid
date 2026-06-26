import { Deferred, Head } from '@inertiajs/react';
import { CheckCircle, Clock, FileText, Timer } from 'lucide-react';

import { SkeletonStat } from '@/components/skeleton-card';
import StatCard from '@/components/stat-card';
import StatChart from '@/components/stat-chart';
import { Skeleton } from '@/components/ui/skeleton';
import type { StatistikDashboard } from '@/types/ppid';

interface Props {
    statistik?: StatistikDashboard;
}

/**
 * Skeleton loading untuk area grafik statistik.
 * Ditampilkan saat data deferred belum tersedia.
 */
function ChartSkeleton() {
    return (
        <div
            className="rounded-xl border border-gray-100 bg-white p-6 shadow-sm"
            role="status"
            aria-label="Memuat grafik statistik"
        >
            <Skeleton className="mb-4 h-5 w-48" />
            <Skeleton className="h-[200px] w-full rounded-lg" />
        </div>
    );
}

/**
 * Skeleton loading untuk 4 card statistik.
 * Ditampilkan saat data deferred belum tersedia.
 */
function StatisticsSkeleton() {
    return (
        <>
            {/* Skeleton untuk 4 card statistik */}
            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                {Array.from({ length: 4 }).map((_, index) => (
                    <SkeletonStat key={index} />
                ))}
            </div>

            {/* Skeleton untuk grafik */}
            <ChartSkeleton />
        </>
    );
}

/**
 * Konten dashboard yang ditampilkan setelah data dimuat.
 * Menampilkan 4 card statistik dan grafik permohonan per bulan.
 */
function DashboardContent({ statistik }: { statistik?: StatistikDashboard }) {
    // Nilai default jika data belum tersedia
    const stats = statistik ?? {
        total_permohonan_bulan_ini: 0,
        sedang_diproses: 0,
        selesai_bulan_ini: 0,
        rata_rata_waktu_respon_hari: 0,
        permohonan_per_bulan: [],
    };

    return (
        <>
            {/* Grid 4 card statistik */}
            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <StatCard
                    icon={FileText}
                    label="Total Permohonan"
                    value={stats.total_permohonan_bulan_ini}
                    description="Bulan ini"
                />
                <StatCard
                    icon={Clock}
                    label="Sedang Diproses"
                    value={stats.sedang_diproses}
                    description="Menunggu tindakan"
                />
                <StatCard
                    icon={CheckCircle}
                    label="Selesai"
                    value={stats.selesai_bulan_ini}
                    description="Bulan ini"
                />
                <StatCard
                    icon={Timer}
                    label="Rata-rata Respon"
                    value={`${stats.rata_rata_waktu_respon_hari} hari`}
                    description="Waktu penyelesaian"
                />
            </div>

            {/* Grafik permohonan per bulan */}
            <div className="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                <h2 className="mb-4 font-heading text-lg font-semibold text-gray-800">
                    Permohonan per Bulan
                </h2>
                <StatChart data={stats.permohonan_per_bulan} />
            </div>
        </>
    );
}

/**
 * Halaman dashboard admin PPID.
 * Menampilkan statistik permohonan dan grafik permohonan per bulan.
 * Data dimuat secara deferred (lazy-loading) untuk performa optimal.
 */
export default function AdminDashboard({ statistik }: Props) {
    return (
        <>
            <Head title="Dashboard PPID" />

            <div className="flex flex-col gap-6 p-4 md:p-6">
                {/* Heading halaman */}
                <div>
                    <h1 className="font-heading text-2xl font-bold text-gray-900">
                        Dashboard PPID
                    </h1>
                    <p className="mt-1 text-sm text-gray-500">
                        Ringkasan statistik permohonan informasi publik
                    </p>
                </div>

                {/* Konten dengan deferred loading */}
                <Deferred data="statistik" fallback={<StatisticsSkeleton />}>
                    <DashboardContent statistik={statistik} />
                </Deferred>
            </div>
        </>
    );
}
