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
                    hover: '#f0faf4',
                    active: '#e8f5ee',
                    border: '#e5e7eb',
                    text: '#4b5563',
                    muted: '#9ca3af',
                    accent: '#166534',
                },
                brand: {
                    green: '#166534',
                    light: '#4ade80',
                    accent: '#16a34a',
                },
            },
        },
    },

    plugins: [forms],
};
