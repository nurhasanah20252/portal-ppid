import { Link, usePage } from '@inertiajs/react';
import { Menu, X, Search } from 'lucide-react';
import { useState } from 'react';
import { trackEvent } from '@/lib/tracking';
import { cn } from '@/lib/utils';

/** Item navigasi utama portal publik */
const navItems = [
    { label: 'Beranda', href: '/' },
    { label: 'Profil PPID', href: '/profil' },
    { label: 'Informasi Publik', href: '/informasi-publik' },
    { label: 'Permohonan', href: '/permohonan' },
    { label: 'Cek Status', href: '/status' },
    { label: 'Kontak', href: '/kontak' },
];

export default function PublicHeader() {
    const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
    const { url } = usePage();

    const isActive = (href: string) => {
        if (href === '/') {
return url === '/';
}

        return url.startsWith(href);
    };

    const handleNavClick = (label: string) => {
        trackEvent('navigation', 'click_menu', label.toLowerCase());
        setMobileMenuOpen(false);
    };

    return (
        <header className="sticky top-0 z-50 bg-hijau shadow-md">
            <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div className="flex h-16 items-center justify-between">
                    {/* Logo */}
                    <Link href="/" className="flex items-center gap-3">
                        <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-white/20">
                            <span className="text-xl font-bold text-white">⚖</span>
                        </div>
                        <div className="hidden sm:block">
                            <p className="font-heading text-sm font-semibold leading-tight text-white">
                                PPID PA Penajam
                            </p>
                            <p className="text-xs text-white/70">Pengadilan Agama Penajam</p>
                        </div>
                    </Link>

                    {/* Navigasi desktop */}
                    <nav className="hidden items-center gap-1 lg:flex">
                        {navItems.map((item) => (
                            <Link
                                key={item.href}
                                href={item.href}
                                onClick={() => handleNavClick(item.label)}
                                className={cn(
                                    'rounded-md px-3 py-2 text-sm font-medium transition-colors duration-200',
                                    isActive(item.href)
                                        ? 'bg-white/20 text-white'
                                        : 'text-white/80 hover:bg-white/10 hover:text-white',
                                )}
                            >
                                {item.label}
                            </Link>
                        ))}
                    </nav>

                    {/* Aksi kanan */}
                    <div className="flex items-center gap-2">
                        <Link
                            href="/status"
                            className="rounded-md p-2 text-white/80 transition-colors hover:bg-white/10 hover:text-white"
                            aria-label="Cek Status"
                        >
                            <Search className="h-5 w-5" />
                        </Link>

                        {/* Hamburger mobile */}
                        <button
                            type="button"
                            className="rounded-md p-2 text-white/80 hover:bg-white/10 hover:text-white lg:hidden"
                            onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
                            aria-label={mobileMenuOpen ? 'Tutup menu' : 'Buka menu'}
                        >
                            {mobileMenuOpen ? <X className="h-6 w-6" /> : <Menu className="h-6 w-6" />}
                        </button>
                    </div>
                </div>
            </div>

            {/* Menu mobile */}
            {mobileMenuOpen && (
                <div className="border-t border-white/10 bg-hijau-dark lg:hidden">
                    <nav className="space-y-1 px-4 py-3">
                        {navItems.map((item) => (
                            <Link
                                key={item.href}
                                href={item.href}
                                onClick={() => handleNavClick(item.label)}
                                className={cn(
                                    'block rounded-md px-3 py-2.5 text-sm font-medium transition-colors',
                                    isActive(item.href)
                                        ? 'bg-white/20 text-white'
                                        : 'text-white/80 hover:bg-white/10 hover:text-white',
                                )}
                            >
                                {item.label}
                            </Link>
                        ))}
                    </nav>
                </div>
            )}
        </header>
    );
}
