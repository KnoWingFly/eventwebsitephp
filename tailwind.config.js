/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './admin/*.{html,js,php}',
    './user/*.{html,js,php}',,
    './css/*.{html,js,php,css}'
  ],
  theme: {
    extend: {},
  },
  plugins: [
    require('daisyui'),
  ],
  daisyui: {
    themes: ["dark", "light"],
  },
}

