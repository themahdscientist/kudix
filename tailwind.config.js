/** @type {import('tailwindcss').Config} */
import preset from './vendor/filament/support/tailwind.config.preset'

export default {
  presets: [preset],
  content: [
    './app/Filament/**/*.php',
    './resources/**/*.{blade.php,js,vue}',
    './vendor/filament/**/*.blade.php',
  ],
  theme: {
    extend: {
      colors: {
        'dark': '#000000',
        'light': '#FFFFFF',
        'primary': '#3730A3',
        'secondary': '#E5E5E5',
        'accent': '#FCA311',
      }
    },
  },
  plugins: [],
}
