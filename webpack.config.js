var Encore = require('@symfony/webpack-encore');

Encore
    // the project directory where compiled assets will be stored
    .setOutputPath('web/build/')
    // the public path used by the web server to access the previous directory
    .setPublicPath('/build')
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    // uncomment to create hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

    // will output as web/build/app.js
    .addEntry('js/app', './assets/js/app.js')

    // will output as web/build/global.css
    .addStyleEntry('css/app', './assets/scss/global.scss')

    // uncomment if you use Sass/SCSS files
    .enableSassLoader()

    // .enableSassLoader(function(sassOptions) {}, {
    //     resolveUrlLoader: false
    // })

    // uncomment for legacy applications that require $/jQuery as a global variable
    .autoProvidejQuery()
;

module.exports = Encore.getWebpackConfig();
