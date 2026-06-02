import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                sidebar: {
                    DEFAULT: '#ffffff',
                    hover: '#eff6ff',
                    active: '#dbeafe',
                    border: '#e5e7eb',
                    text: '#4b5563',
                    muted: '#9ca3af',
                    accent: '#1d4ed8',
                },
                brand: {
                    blue: '#1d4ed8',
                    light: '#60a5fa',
                    accent: '#2563eb',
                },
            },
        },
    },

    plugins: [forms],
};
