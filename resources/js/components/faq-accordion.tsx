import { useState } from 'react';
import { ChevronDown } from 'lucide-react';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { cn } from '@/lib/utils';

interface FaqAccordionItem {
    pertanyaan: string;
    jawaban: string;
}

interface FaqAccordionProps {
    items: FaqAccordionItem[];
    /** Mengizinkan lebih dari satu item terbuka secara bersamaan */
    allowMultiple?: boolean;
    className?: string;
}

/**
 * Komponen accordion untuk menampilkan daftar FAQ.
 * Menggunakan Radix UI Collapsible dengan animasi expand/collapse yang halus.
 * Secara default hanya satu item yang terbuka pada satu waktu (accordion behavior).
 */
export default function FaqAccordion({ items, allowMultiple = false, className }: FaqAccordionProps) {
    // Menyimpan indeks item yang sedang terbuka
    const [openIndices, setOpenIndices] = useState<Set<number>>(new Set());

    // Menangani toggle item accordion
    function handleToggle(index: number, isOpen: boolean) {
        setOpenIndices((prev) => {
            const next = new Set(allowMultiple ? prev : []);
            if (isOpen) {
                next.add(index);
            } else {
                next.delete(index);
            }
            return next;
        });
    }

    if (items.length === 0) {
        return null;
    }

    return (
        <div className={cn('space-y-3', className)} role="region" aria-label="Pertanyaan Umum">
            {items.map((item, index) => {
                const isOpen = openIndices.has(index);

                return (
                    <Collapsible
                        key={index}
                        open={isOpen}
                        onOpenChange={(open) => handleToggle(index, open)}
                    >
                        <div
                            className={cn(
                                'rounded-xl border border-gray-100 bg-white shadow-sm transition-all duration-200',
                                'hover:border-emas/30 hover:shadow-md',
                                isOpen && 'border-emas/40 shadow-md',
                            )}
                        >
                            <CollapsibleTrigger
                                className={cn(
                                    'flex w-full cursor-pointer items-center gap-3 p-5 text-left text-sm font-medium text-gray-800 transition-colors',
                                    'hover:text-hijau focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-hijau/50 focus-visible:ring-offset-2',
                                    'rounded-xl',
                                )}
                                aria-expanded={isOpen}
                            >
                                {/* Nomor urut */}
                                <span className="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-orange/10 text-xs font-bold text-orange">
                                    {index + 1}
                                </span>
                                <span className="flex-1">{item.pertanyaan}</span>
                                {/* Ikon chevron dengan animasi rotasi */}
                                <ChevronDown
                                    className={cn(
                                        'h-4 w-4 shrink-0 text-gray-400 transition-transform duration-300',
                                        isOpen && 'rotate-180',
                                    )}
                                    aria-hidden="true"
                                />
                            </CollapsibleTrigger>

                            <CollapsibleContent
                                className={cn(
                                    'overflow-hidden transition-all duration-300 ease-in-out',
                                    'data-[state=closed]:animate-collapsible-up data-[state=open]:animate-collapsible-down',
                                )}
                            >
                                <div className="border-t border-gray-50 px-5 pb-5 pl-[3.25rem] pt-3 text-sm leading-relaxed text-gray-600">
                                    {item.jawaban}
                                </div>
                            </CollapsibleContent>
                        </div>
                    </Collapsible>
                );
            })}
        </div>
    );
}
