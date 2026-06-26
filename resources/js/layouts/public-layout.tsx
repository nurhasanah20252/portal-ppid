import PublicFooter from '@/components/public-footer';
import PublicHeader from '@/components/public-header';
import SkipToContent from '@/components/skip-to-content';

interface PublicLayoutProps {
    children: React.ReactNode;
}

/**
 * Layout untuk halaman publik portal PPID.
 * Menggunakan header hijau dengan navigasi dan footer.
 */
export default function PublicLayout({ children }: PublicLayoutProps) {
    return (
        <div className="flex min-h-screen flex-col bg-gray-50">
            <SkipToContent />
            <PublicHeader />
            <main id="content" className="flex-1">
                {children}
            </main>
            <PublicFooter />
        </div>
    );
}
