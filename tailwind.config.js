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
            fontFamily: {
                sans: ['Plus Jakarta Sans', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    50:  '#fff4ee',
                    100: '#ffe8d6',
                    200: '#ffd0b0',
                    300: '#ffa97a',
                    400: '#fd7a42',
                    500: '#f25c2e',
                    600: '#e33f14',
                    700: '#bc2f0f',
                    800: '#952913',
                    900: '#782514',
                    950: '#410f07',
                },
            },
            boxShadow: {
                'brand':      '0 20px 60px -10px rgba(242, 92, 46, 0.45)',
                'brand-sm':   '0 4px 20px 0px rgba(242, 92, 46, 0.30)',
                'brand-xs':   '0 2px 8px 0px rgba(242, 92, 46, 0.20)',
                'card':       '0 4px 24px 0px rgba(0, 0, 0, 0.08)',
                'card-lg':    '0 12px 48px 0px rgba(0, 0, 0, 0.14)',
                'card-dark':  '0 8px 32px 0px rgba(0, 0, 0, 0.35)',
                'inner-sm':   'inset 0 1px 3px 0px rgba(0,0,0,0.06)',
                'glow-brand': '0 0 20px 4px rgba(242, 92, 46, 0.18)',
            },
            animation: {
                'float':       'float 4s ease-in-out infinite',
                'float-slow':  'float 6s ease-in-out infinite',
                'fade-up':     'fadeUp 0.6s ease-out forwards',
                'pulse-glow':  'pulseGlow 2.4s ease-in-out infinite',
                'shimmer':     'shimmer 2.2s infinite',
            },
            keyframes: {
                float: {
                    '0%, 100%': { transform: 'translateY(0px)' },
                    '50%':      { transform: 'translateY(-10px)' },
                },
                fadeUp: {
                    '0%':   { opacity: '0', transform: 'translateY(24px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                pulseGlow: {
                    '0%, 100%': { boxShadow: '0 0 0 0 rgba(242, 92, 46, 0.3)' },
                    '50%':      { boxShadow: '0 0 0 8px rgba(242, 92, 46, 0)' },
                },
                shimmer: {
                    '0%':   { backgroundPosition: '-1000px 0' },
                    '100%': { backgroundPosition: '1000px 0' },
                },
            },
        },
    },

    plugins: [forms],
};
