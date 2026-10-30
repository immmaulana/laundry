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
                    50: '#f0faf8',
                    100: '#d8f3ee',
                    200: '#b3e6dc',
                    300: '#82d3c5',
                    400: '#4fb8a8',
                    500: '#349f8f',
                    600: '#278074',
                    700: '#23675e',
                    800: '#20534c',
                    900: '#1e4540',
                },
                soft: {
                    50: '#f7f8fa',
                    100: '#eef1f5',
                    200: '#dde3eb',
                    300: '#c3cdd9',
                    400: '#9aabbd',
                    500: '#7a8fa5',
                    600: '#627589',
                    700: '#516070',
                    800: '#46515e',
                    900: '#3d4550',
                },
            },
            boxShadow: {
                soft: '0 1px 2px rgba(61, 69, 80, 0.04), 0 8px 24px rgba(61, 69, 80, 0.06)',
                'soft-lg': '0 4px 12px rgba(61, 69, 80, 0.05), 0 16px 40px rgba(61, 69, 80, 0.08)',
            },
            backgroundImage: {
                'soft-mesh':
                    'radial-gradient(ellipse 80% 50% at 0% -20%, rgba(52, 159, 143, 0.12), transparent), radial-gradient(ellipse 60% 40% at 100% 0%, rgba(122, 143, 165, 0.1), transparent), linear-gradient(180deg, #f7f8fa 0%, #eef1f5 100%)',
            },
        },
    },

    plugins: [forms],
};
