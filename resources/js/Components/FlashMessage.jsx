import { useEffect, useState } from 'react';
import { usePage } from '@inertiajs/react';

export default function FlashMessage() {
    const { flash } = usePage().props;
    const [visible, setVisible] = useState(false);
    const message = flash?.success || flash?.error;

    useEffect(() => {
        if (message) {
            setVisible(true);
            const t = setTimeout(() => setVisible(false), 5000);
            return () => clearTimeout(t);
        }
    }, [message]);

    if (!visible || !message) return null;
    return (
        <div className={`fixed bottom-6 right-6 z-50 max-w-sm rounded-xl px-5 py-3 text-sm font-medium shadow-cardHover ${flash?.error ? 'bg-red-700 text-white' : 'bg-brand text-brand-cream'}`} role="status">
            {message}
        </div>
    );
}
