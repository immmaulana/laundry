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
                sans: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    50: 'rgb(var(--brand-50) / <alpha-value>)',
                    100: 'rgb(var(--brand-100) / <alpha-value>)',
                    200: 'rgb(var(--brand-200) / <alpha-value>)',
                    300: 'rgb(var(--brand-300) / <alpha-value>)',
                    400: 'rgb(var(--brand-400) / <alpha-value>)',
                    500: 'rgb(var(--brand-500) / <alpha-value>)',
                    600: 'rgb(var(--brand-600) / <alpha-value>)',
                    700: 'rgb(var(--brand-700) / <alpha-value>)',
                    800: 'rgb(var(--brand-800) / <alpha-value>)',
                    900: 'rgb(var(--brand-900) / <alpha-value>)',
                },
                soft: {
                    50: 'rgb(var(--soft-50) / <alpha-value>)',
                    100: 'rgb(var(--soft-100) / <alpha-value>)',
                    200: 'rgb(var(--soft-200) / <alpha-value>)',
                    300: 'rgb(var(--soft-300) / <alpha-value>)',
                    400: 'rgb(var(--soft-400) / <alpha-value>)',
                    500: 'rgb(var(--soft-500) / <alpha-value>)',
                    600: 'rgb(var(--soft-600) / <alpha-value>)',
                    700: 'rgb(var(--soft-700) / <alpha-value>)',
                    800: 'rgb(var(--soft-800) / <alpha-value>)',
                    900: 'rgb(var(--soft-900) / <alpha-value>)',
                },
            },
            boxShadow: {
                soft: '0 1px 2px rgba(61, 69, 80, 0.04), 0 8px 24px rgba(61, 69, 80, 0.06)',
                'soft-lg': '0 4px 12px rgba(61, 69, 80, 0.05), 0 16px 40px rgba(61, 69, 80, 0.08)',
            },
            backgroundImage: {
                'soft-mesh': 'var(--soft-mesh)',
            },
        },
    },

    plugins: [forms],
};
