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
                'brand-red': 'var(--color-brand-red)',
                'brand-orange': 'var(--color-brand-orange)',
                'brand-success': 'var(--color-success)',
                'brand-danger': 'var(--color-danger)',
                'brand-warning': 'var(--color-warning)',
                'brand-info': 'var(--color-info)',
                'dark-bg': 'var(--color-dark-bg)', // kBackgroundColorDark
                'light-bg': 'var(--color-light-bg)', // kBackgroundColorLight
            },
        },
    },

    plugins: [forms],
};
