/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        'lovecraft-purple': '#6B46C1',
        'lovecraft-dark': '#1A0B2E',
      },
    },
  },
  plugins: [],
}
