const mix = require('laravel-mix')

const autoload = {
   jquery: [ '$', 'jQuery', 'jquery']
}
mix.autoload(autoload)

mix.setPublicPath('public')

mix.sass('./resources/sass/app.scss', 'public/css')
   .js('./resources/js/app.js', 'public/js')
   .version()

// Copy all compiled files into main project (auto publishing)
mix.copyDirectory('public', '../../../public/vendor/jerome-savin/uccello-email-client');