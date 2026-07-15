/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./app/Filament/Clusters/Registration/**/*.php",
        "./app/Filament/Pages/**/*.php",
        "./app/Filament/Resources/**/*.php",
        "./resources/views/filament/clusters/registration/**/*.blade.php",
        "./resources/views/filament/resources/**/*.blade.php",
        "./resources/views/filament/pages/**/*.blade.php",
        "./vendor/filament/**/*.blade.php",
        "./vendor/guava/tutorials/resources/**/*.php",
        "./resources/views/livewire/**/*.blade.php",
        "./resources/views/forms/**/*.blade.php",
        "./app/Livewire/**/*.php",
        "./app/Forms/**/*.php",
    ],
    theme: {
        extend: {},
    },
    plugins: [],
};
