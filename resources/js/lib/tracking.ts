/**
 * Utilitas event tracking untuk analytics (GA4).
 * Sesuai spesifikasi di UX.md section 1.
 */

type EventCategory = 'navigation' | 'cta_button' | 'form_interaction' | 'status_check' | 'download' | 'admin_action' | 'error' | 'engagement';

/** Deklarasi global untuk gtag */
declare global {
    interface Window {
        gtag?: (...args: unknown[]) => void;
    }
}

/**
 * Mengirim event tracking ke Google Analytics 4.
 * Fallback ke console log di development.
 */
export function trackEvent(
    category: EventCategory,
    action: string,
    label?: string,
    value?: Record<string, unknown> | string | number,
): void {
    // Kirim ke GA4 jika tersedia
    if (typeof window !== 'undefined' && typeof window.gtag === 'function') {
        window.gtag('event', action, {
            event_category: category,
            event_label: label,
            value: value,
        });
    }

    // Log di development
    if (import.meta.env.DEV) {
        console.log(`[GA] ${category} | ${action} | ${label}`, value);
    }
}
