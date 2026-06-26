import { useFlashToast } from '@/hooks/use-flash-toast';
import { useAppearance } from '@/hooks/use-appearance';
import { Toaster as Sonner, type ToasterProps } from 'sonner';

function Toaster({ ...props }: ToasterProps) {
    const { appearance } = useAppearance();

    useFlashToast();

    // Wrapper role="status" dan aria-live="polite" memenuhi Requirement 17.4
    // Sonner secara internal sudah menggunakan aria-live="polite" pada section-nya
    return (
        <div role="status" aria-live="polite" aria-atomic="false">
            <Sonner
                theme={appearance}
                className="toaster group"
                position="bottom-right"
                style={
                    {
                        '--normal-bg': 'var(--popover)',
                        '--normal-text': 'var(--popover-foreground)',
                        '--normal-border': 'var(--border)',
                    } as React.CSSProperties
                }
                {...props}
            />
        </div>
    );
}

export { Toaster };
