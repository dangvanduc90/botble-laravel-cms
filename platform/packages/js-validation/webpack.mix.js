let mix = require('laravel-mix');

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

const resourcePath = 'platform/packages/js-validation';
const publicPath = 'public/vendor/core';

mix
    .scripts(
        [
            resourcePath + '/resources/assets/js/jquery-validation/jquery.validate.js',
            resourcePath + '/resources/assets/js/phpjs/strlen.js',
            resourcePath + '/resources/assets/js/phpjs/array_diff.js',
            resourcePath + '/resources/assets/js/phpjs/strtotime.js',
            resourcePath + '/resources/assets/js/phpjs/is_numeric.js',
            resourcePath + '/resources/assets/js/php-date-formatter/php-date-formatter.js',
            resourcePath + '/resources/assets/js/js-validation.js',
            resourcePath + '/resources/assets/js/helpers.js',
            resourcePath + '/resources/assets/js/timezones.js',
            resourcePath + '/resources/assets/js/validations.js'
        ], publicPath + '/js/js-validation.js')
    .copy(publicPath + '/js/js-validation.js', resourcePath + '/public/js');
