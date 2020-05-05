const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

 /**
  * @todo убедиться что все работает, после extract
  */
mix.js('resources/js/app.js', 'public/js').extract(['jquery', 'bootstrap'])
    .sass('resources/sass/app.scss', 'public/css')
    .version()
    .sourceMaps();

if (mix.inProduction()) {
    mix.disableNotifications();
} else {
    mix.browserSync({
        proxy: 'shop.test',
    });
}
