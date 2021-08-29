const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js')
    // .extract() // crashed admin js script :(
    .sass('resources/sass/app.scss', 'public/css')
    // for admin panel
    .js('resources/js/admin/admin.js', 'public/js')
    .sass('resources/sass/admin/admin.scss', 'public/css')
    .version();

if (mix.inProduction()) {
    mix.disableNotifications();
} else {
    mix.browserSync({
        proxy: 'shop.test',
    });
    mix.sourceMaps(true, 'source-map');
}
