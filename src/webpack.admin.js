const mix = require('laravel-mix');
require('laravel-mix-merge-manifest');

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

mix.js('resources/js/admin/admin.js', 'public/js')
  .sass('resources/sass/admin/admin.scss', 'public/css');

if (mix.inProduction()) {
  mix.disableNotifications().version();
} else {
  mix.sourceMaps(true, 'source-map');
}

mix.mergeManifest();
