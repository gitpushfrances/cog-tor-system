import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

const forest = {
    50: '#f3f6f1',
    100: '#e3eadd',
    200: '#c7d5bc',
    300: '#a3b893',
    400: '#7f9a6c',
    500: '#658354',
    600: '#4b6043',
    700: '#3f5238',
    800: '#384a32',
    900: '#2a3826',
    950: '#1a2318',
};

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
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            // One brand color for the whole app: the ESSU forest green used by the sidebar and
            // sign-in page. Pages written with Tailwind's blue/indigo classes pick it up from here.
            colors: {
                blue: forest,
                indigo: forest,
            },
        },
    },

    plugins: [forms],
};
