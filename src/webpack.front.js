const mix = require('laravel-mix');
const { sentryWebpackPlugin } = require('@sentry/webpack-plugin');
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

mix.js('resources/js/app.js', 'public/js')
  .sass('resources/sass/app.scss', 'public/css')
  .extract(['lodash'], 'public/js/lodash.js')
  .extract(['jquery'], 'public/js/jquery.js')
  .extract(['@fancyapps/fancybox'], 'public/js/fancybox.js')
  .extract(['bootstrap', 'slick-carousel', 'sortablejs', 'popper.js'], 'public/js/ui.js')
  .extract(['axios', 'mustache', 'libphonenumber-js', 'recaptcha-v3'], 'public/js/utils.js')
  .extract();

if (mix.inProduction()) {
  mix.disableNotifications().version();
}

mix.webpackConfig({
  devtool: 'source-map',
  plugins: [
    sentryWebpackPlugin({
      org: 'baroccostyle',
      project: 'javascript',
      authToken: process.env.SENTRY_AUTH_TOKEN,
      telemetry: false,
    }),
  ],
});

mix.mergeManifest();
