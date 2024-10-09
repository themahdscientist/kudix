import preset from '../../../../vendor/filament/filament/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        './app/Filament/Admin/**/*.php',
        './resources/views/filament/admin/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
        // plugins
        './vendor/jaocero/radio-deck/resources/views/**/*.blade.php',
        './vendor/awcodes/recently/resources/**/*.blade.php',
    ],
}
