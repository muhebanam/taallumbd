import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.jsx',
    ],
    theme: {
        extend: {
            colors: {
                brand: {
                    DEFAULT: '#1A2E2F',   // primary dark green
                    deep:    '#102526',   // secondary dark green
                    cream:   '#FFF99A',   // cream/yellow accent
                    light:   '#F8FAF8',   // light background
                    text:    '#142425',   // dark text
                },
            },
            fontFamily: {
                bangla: ['Kalpurush', 'AdorshoLipi', 'Hind Siliguri', ...defaultTheme.fontFamily.sans],
                sans: ['Kalpurush', 'Hind Siliguri', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
            boxShadow: {
                card: '0 2px 12px rgba(16, 37, 38, 0.08)',
                cardHover: '0 8px 24px rgba(16, 37, 38, 0.16)',
            },
        },
    },
    plugins: [forms],
};
