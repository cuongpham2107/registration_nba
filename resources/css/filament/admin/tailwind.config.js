import preset from '../../../../vendor/filament/filament/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        './app/Filament/Clusters/Registration/**/*.php',
        './app/Filament/Resources/**/*.php',
        './app/Filament/**/*.php',
        './resources/css/filament/admin/**/*.css',
        './resources/views/filament/clusters/registration/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
    ],
}
