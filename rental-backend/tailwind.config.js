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
                'brand-red': '#E21608',
                'brand-orange': '#FF4520',
                'dark-bg': '#111827', // kBackgroundColorDark
                'light-bg': '#1F2937', // kBackgroundColorLight
            },
        },
    },

    plugins: [forms],
};
