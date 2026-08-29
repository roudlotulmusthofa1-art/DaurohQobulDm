/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Plus Jakarta Sans', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                arabic: ['Amiri', 'serif'],
            },
            colors: {
                teal: {
                    700: '#0F766E',
                    800: '#0C5C55',
                },
            },
        },
    },
    plugins: [],
};
