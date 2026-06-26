/**
 * Komponen SkipToContent untuk aksesibilitas keyboard navigation.
 * Link tersembunyi yang muncul saat menerima fokus keyboard,
 * memungkinkan pengguna melewati navigasi langsung ke konten utama.
 */
export default function SkipToContent() {
    return (
        <a
            href="#content"
            className="fixed top-0 left-0 z-50 -translate-y-full bg-hijau px-4 py-2 text-sm font-medium text-white transition-transform duration-200 focus:translate-y-0 focus:outline-2 focus:outline-offset-2 focus:outline-emas"
        >
            Langsung ke konten utama
        </a>
    );
}
