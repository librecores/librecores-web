var Encore = require('@symfony/webpack-encore');

Encore
    // directory where compiled assets will be stored
    .setOutputPath('web/build/')
    // public path used by the web server to access the output path
    .setPublicPath('/build')
    // only needed for CDN's or sub-directory deploy
    //.setManifestKeyPrefix('build/')
    // load classifications.yml using js-yaml-loader
    .addRule({
        test: /\.ya?ml$/,
        use: 'js-yaml-loader'
    })
    /*
     * ENTRY CONFIG
     *
     * Add 1 entry for each "page" of your app
     * (including one that's included on every page - e.g. "app")
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if you JavaScript imports CSS.
     */
    // CSS entries
    .addStyleEntry('librecores_base_css', [
        './web/assets/scss/librecores.scss',
        './web/assets/scss/bootstrap-librecores.scss',
        './web/assets/css/librecores.css'
    ])
    .addStyleEntry('librecores_project_view_css', [
        './web/assets/css/chartist.css'
    ])
    .addStyleEntry('librecores_project_settings_css', [
        './web/assets/scss/trumbowyg.scss'
    ])
    .addStyleEntry('librecores_project_new_css', [
        './web/assets/css/bootstrap-select.css'
    ])

    // JS entries
    // createSharedEntry for base template js files to reduce
    // module sizes in production
    .createSharedEntry('librecores_base_js',
        './web/assets/js/base.js')

    // Page specific JS entries
    .addEntry('librecores_search_js', [
        './web/assets/js/search.js'
    ])
    .addEntry('librecores_user_view_js', [
        './web/assets/js/user-view.js'
    ])
    .addEntry('librecores_project_view_js', [
        './web/assets/js/metrics.js',
        './web/assets/js/project-auto-refresh.js',
        './web/assets/css/chartist.css'
    ])
    .addEntry('librecores_project_settings_js', [
        './web/assets/js/classification.js'
    ])
    .addEntry('librecores_project_new_js', [
        './web/assets/js/bootstrap-select.js',
        './web/assets/js/project-new.js'
    ])
    .addEntry('librecores_planet_js', [
        './web/assets/js/planet.js'
    ])

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
