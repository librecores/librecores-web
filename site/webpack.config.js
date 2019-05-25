var Encore = require('@symfony/webpack-encore');

Encore
// directory where compiled assets will be stored
    .setOutputPath('web/build/')
    // public path used by the web server to access the output path
    .setPublicPath('/build')
    // only needed for CDN's or sub-directory deploy
    //.setManifestKeyPrefix('build/')

    /*
     * ENTRY CONFIG
     *
     * Add 1 entry for each "page" of your app
     * (including one that's included on every page - e.g. "app")
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if you JavaScript imports CSS.
     */
    //css entries
    .addStyleEntry('app-css', ['./web/assets/scss/librecores.scss',
        './web/assets/scss/bootstrap-librecores.scss',
        './web/assets/css/bootstrap-social.css',
        './web/assets/css/font-awesome.min.css',
        './web/assets/css/librecores.css'])
    .addStyleEntry('trumbowyg', ['./web/assets/scss/trumbowyg.scss'])
    .addStyleEntry('project_new_css','./web/assets/css/bootstrap-select.css')

    //js entries
    .addEntry('app-js',['./web/assets/js/livestamp.js'])

    //page specific js
    .addEntry('search', ['./web/assets/js/search.js'])
    .addEntry('project-view', ['./web/assets/js/metrics.js',
        './web/assets/js/project-auto-refresh.js',
        './web/assets/css/chartist.css'])
    .addEntry('project_settings', './web/assets/js/classification.js')
    .addEntry('project_new', ['./web/assets/js/bootstrap-select.js',
        './web/assets/js/project_new.js'])


    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    .enableSingleRuntimeChunk()

    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

// uncomment if you use TypeScript
//.enableTypeScriptLoader()

// uncomment if you use Sass/SCSS files
    .enableSassLoader()

// uncomment if you're having problems with a jQuery plugin
    .autoProvidejQuery()
;

const config = Encore.getWebpackConfig();
config.watchOptions = {
    poll: true,
};

module.exports = config;