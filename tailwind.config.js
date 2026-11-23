import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                uch: {
                    navy: '#1B3668',
                    'navy-dark': '#13274D',
                    blue: '#0066B3',
                    cyan: '#00AEEF',
                    teal: '#00B4A6',
                    amber: '#F59E0B',
                    coral: '#E63946',
                    'coral-dark': '#C92A37',
                    bg: '#F4F6F9',
                    surface: '#FFFFFF',
                    text: '#1E293B',
                    muted: '#64748B',
                    border: '#DCE3EC',
                },
            },
            fontFamily: {
                sans: ['Open Sans', ...defaultTheme.fontFamily.sans],
                rubik: ['Rubik', ...defaultTheme.fontFamily.sans],
                open: ['Open Sans', ...defaultTheme.fontFamily.sans],
                headline: ['Rubik', ...defaultTheme.fontFamily.sans],
                body: ['Open Sans', ...defaultTheme.fontFamily.sans],
            },
            fontSize: {
                'headline-lg': ['36px', { lineHeight: '44px', letterSpacing: '-0.02em', fontWeight: '700' }],
                'headline-lg-mobile': ['28px', { lineHeight: '34px', letterSpacing: '-0.01em', fontWeight: '700' }],
                'headline-md': ['24px', { lineHeight: '32px', letterSpacing: '-0.01em', fontWeight: '700' }],
                'headline-sm': ['20px', { lineHeight: '28px', fontWeight: '700' }],
                'title-dish': ['18px', { lineHeight: '24px', fontWeight: '700' }],
                'price-tag': ['18px', { lineHeight: '24px', fontWeight: '700' }],
                'label-md': ['12px', { lineHeight: '16px', letterSpacing: '0.02em', fontWeight: '600' }],
                'label-lg': ['14px', { lineHeight: '20px', fontWeight: '600' }],
                'body-lg': ['16px', { lineHeight: '24px', fontWeight: '400' }],
                'body-md': ['14px', { lineHeight: '20px', fontWeight: '400' }],
                'body-sm': ['12px', { lineHeight: '18px', fontWeight: '400' }],
            },
            spacing: {
                'space-xs': '0.25rem',
                'space-sm': '0.5rem',
                'space-md': '1rem',
                'space-lg': '1.5rem',
                'space-xl': '2rem',
                gutter: '1.5rem',
            },
            borderRadius: {
                card: '14px',
            },
            boxShadow: {
                'uch-card': '0 4px 12px rgba(27, 54, 104, 0.06)',
                'uch-card-hover': '0 12px 28px rgba(27, 54, 104, 0.14)',
            },
        },
    },

    plugins: [forms],
};
