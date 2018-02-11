var Encore = require('@symfony/webpack-encore');

Encore
    // the project directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // the public path used by the web server to access the previous directory
    .setPublicPath('/build')
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    // uncomment to create hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

    // uncomment to define the assets of the project
    .addEntry('app', [
      './assets/js/app.js',
      './vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.js',
      './assets/js/_fos_js_routes.js'
    ])
    .addEntry('double-column', './assets/js/doubleColumn.js')
    .addEntry('wiki', [
        './assets/js/wiki.js',
        'symfony-collection/jquery.collection.js'
    ])

    .createSharedEntry('vendor', [
      'jquery',
      'jquery-contextmenu',
      'jquery-contextmenu/dist/jquery.contextMenu.css',
      'popper.js',
      'bootstrap',
      'bootstrap-3-typeahead',
      'font-awesome/css/font-awesome.css',
      'd3',

      // you can also extract CSS - this will create a 'vendor.css' file
      // this CSS will *not* be included in page1.css or page2.css anymore
      './assets/css/vendor.scss'
    ])

    // uncomment if you use Sass/SCSS files
    .enableSassLoader()

    // uncomment for legacy applications that require $/jQuery as a global variable
    .autoProvidejQuery()

    // Provide popper global var for bootstrap
    .autoProvideVariables({
      Popper: ['popper.js', 'default']
    })
;

module.exports = Encore.getWebpackConfig();
