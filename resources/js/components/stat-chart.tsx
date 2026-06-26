import { useMemo, useState } from 'react';
import { cn } from '@/lib/utils';

interface StatChartProps {
    data: Array<{ bulan: string; total: number }>;
    className?: string;
}

/**
 * Komponen bar chart SVG ringan untuk statistik permohonan per bulan.
 * Menampilkan grafik batang responsif dengan sumbu X (bulan) dan Y (jumlah).
 * Menyediakan tabel data tersembunyi sebagai alternatif untuk screen reader.
 *
 * @param data - Array data permohonan per bulan
 * @param className - Kelas CSS tambahan untuk container
 */
export default function StatChart({ data, className }: StatChartProps) {
    const [hoveredIndex, setHoveredIndex] = useState<number | null>(null);

    // Hitung nilai maksimum untuk skala Y-axis
    const maxValue = useMemo(() => {
        if (data.length === 0) {
            return 0;
        }

        return Math.max(...data.map((d) => d.total));
    }, [data]);

    // Hitung garis grid Y-axis (4 baris)
    const yAxisTicks = useMemo(() => {
        if (maxValue === 0) {
            return [0];
        }

        const step = Math.ceil(maxValue / 4);
        const ticks: number[] = [];

        for (let i = 0; i <= 4; i++) {
            ticks.push(step * i);
        }

        return ticks;
    }, [maxValue]);

    // Dimensi chart SVG
    const chartConfig = {
        paddingLeft: 40,
        paddingRight: 16,
        paddingTop: 16,
        paddingBottom: 40,
        barGap: 4,
    };

    // Jika tidak ada data, tampilkan pesan kosong
    if (data.length === 0) {
        return (
            <div
                className={cn(
                    'flex min-h-[200px] items-center justify-center rounded-xl border border-gray-100 bg-white p-6',
                    className,
                )}
                role="img"
                aria-label="Grafik permohonan per bulan - tidak ada data"
            >
                <p className="text-sm text-gray-400">
                    Belum ada data statistik
                </p>
            </div>
        );
    }

    // Hitung tinggi bar relatif terhadap nilai maksimum
    const getBarHeight = (value: number, chartHeight: number): number => {
        if (maxValue === 0) {
            return 0;
        }

        return (value / maxValue) * chartHeight;
    };

    // Dimensi SVG internal
    const svgWidth = 100;
    const svgHeight = 100;
    const chartAreaWidth =
        svgWidth - chartConfig.paddingLeft - chartConfig.paddingRight;
    const chartAreaHeight =
        svgHeight - chartConfig.paddingTop - chartConfig.paddingBottom;
    const barWidth = Math.max(
        2,
        (chartAreaWidth - chartConfig.barGap * (data.length - 1)) / data.length,
    );

    return (
        <div className={cn('w-full', className)}>
            {/* Chart SVG responsif */}
            <div
                className="relative w-full"
                role="img"
                aria-label={`Grafik batang permohonan per bulan. Nilai tertinggi: ${maxValue} permohonan.`}
            >
                <svg
                    viewBox={`0 0 ${svgWidth} ${svgHeight}`}
                    preserveAspectRatio="xMidYMid meet"
                    className="h-auto w-full"
                    aria-hidden="true"
                >
                    {/* Garis grid horizontal */}
                    {yAxisTicks.map((tick) => {
                        const y =
                            chartConfig.paddingTop +
                            chartAreaHeight -
                            getBarHeight(tick, chartAreaHeight);

                        return (
                            <g key={`grid-${tick}`}>
                                <line
                                    x1={chartConfig.paddingLeft}
                                    y1={y}
                                    x2={svgWidth - chartConfig.paddingRight}
                                    y2={y}
                                    stroke="#E5E7EB"
                                    strokeWidth="0.2"
                                    strokeDasharray="1,1"
                                />
                                {/* Label sumbu Y */}
                                <text
                                    x={chartConfig.paddingLeft - 3}
                                    y={y + 1}
                                    textAnchor="end"
                                    className="fill-gray-500"
                                    fontSize="3"
                                    fontFamily="Inter, sans-serif"
                                >
                                    {tick}
                                </text>
                            </g>
                        );
                    })}

                    {/* Bar chart */}
                    {data.map((item, index) => {
                        const barHeight = getBarHeight(
                            item.total,
                            chartAreaHeight,
                        );
                        const x =
                            chartConfig.paddingLeft +
                            index * (barWidth + chartConfig.barGap);
                        const y =
                            chartConfig.paddingTop +
                            chartAreaHeight -
                            barHeight;
                        const isHovered = hoveredIndex === index;

                        return (
                            <g
                                key={`bar-${index}`}
                                onMouseEnter={() => setHoveredIndex(index)}
                                onMouseLeave={() => setHoveredIndex(null)}
                                onFocus={() => setHoveredIndex(index)}
                                onBlur={() => setHoveredIndex(null)}
                                tabIndex={0}
                                role="graphics-symbol"
                                aria-label={`${item.bulan}: ${item.total} permohonan`}
                            >
                                {/* Bar */}
                                <rect
                                    x={x}
                                    y={y}
                                    width={barWidth}
                                    height={Math.max(barHeight, 0.5)}
                                    rx="0.8"
                                    ry="0.8"
                                    fill={isHovered ? '#2E7D32' : '#1B5E20'}
                                    className="transition-all duration-200"
                                />

                                {/* Tooltip saat hover */}
                                {isHovered && (
                                    <g>
                                        <rect
                                            x={x + barWidth / 2 - 6}
                                            y={y - 7}
                                            width="12"
                                            height="5"
                                            rx="1"
                                            fill="#212121"
                                        />
                                        <text
                                            x={x + barWidth / 2}
                                            y={y - 4}
                                            textAnchor="middle"
                                            fill="white"
                                            fontSize="2.5"
                                            fontFamily="Inter, sans-serif"
                                            fontWeight="600"
                                        >
                                            {item.total}
                                        </text>
                                    </g>
                                )}

                                {/* Label sumbu X (nama bulan) */}
                                <text
                                    x={x + barWidth / 2}
                                    y={
                                        svgHeight -
                                        chartConfig.paddingBottom +
                                        6
                                    }
                                    textAnchor="middle"
                                    className="fill-gray-600"
                                    fontSize="2.5"
                                    fontFamily="Inter, sans-serif"
                                >
                                    {item.bulan.length > 3
                                        ? item.bulan.substring(0, 3)
                                        : item.bulan}
                                </text>
                            </g>
                        );
                    })}

                    {/* Garis dasar sumbu X */}
                    <line
                        x1={chartConfig.paddingLeft}
                        y1={chartConfig.paddingTop + chartAreaHeight}
                        x2={svgWidth - chartConfig.paddingRight}
                        y2={chartConfig.paddingTop + chartAreaHeight}
                        stroke="#9CA3AF"
                        strokeWidth="0.3"
                    />
                </svg>
            </div>

            {/* Tabel data tersembunyi untuk aksesibilitas screen reader */}
            <table className="sr-only" aria-label="Data permohonan per bulan">
                <caption>Statistik permohonan informasi per bulan</caption>
                <thead>
                    <tr>
                        <th scope="col">Bulan</th>
                        <th scope="col">Jumlah Permohonan</th>
                    </tr>
                </thead>
                <tbody>
                    {data.map((item, index) => (
                        <tr key={`row-${index}`}>
                            <td>{item.bulan}</td>
                            <td>{item.total}</td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}
