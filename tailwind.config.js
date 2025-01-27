/** @type {import('tailwindcss').Config} */
import preset from './vendor/filament/support/tailwind.config.preset'

export default {
  presets: [preset],
  content: [
    './app/Filament/**/*.php',
    './resources/**/*.{blade.php,js,vue}',
    './vendor/filament/**/*.blade.php',
    './vendor/jaocero/radio-deck/resources/views/**/*.blade.php',
  ],
  theme: {
    extend: {
      colors: {
        'dark': '#000000',
        'light': '#FFFFFF',
        'primary': '#4338CA',
        'secondary': '#E5E5E5',
        'accent': '#FCA311',
      },
    },
  },
  plugins: [],
}
