var Encore = require('@symfony/webpack-encore');

Encore
    // directory where compiled assets will be stored
    .setOutputPath('web/build/')
    // public path used by the web server to access the output path
    .setPublicPath('/build')
    // only needed for CDN's or sub-directory deploy
    //.setManifestKeyPrefix('build/')

    // Enable loading of YAML files
    .addRule({
        test: /\.ya?ml$/,
        use: 'js-yaml-loader'
    })

    // Static assets
     .copyFiles({
         from: './assets/images',

         // optional target path, relative to the output dir
         // if versioning is enabled, add the file hash too
         to: Encore.isProduction() ? 'images/[path][name].[hash:8].[ext]' : 'images/[path][name].[ext]',

         // only copy files matching this pattern
         //pattern: /\.(png|jpg|jpeg)$/
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
    // JS entries
    // createSharedEntry for base template js files to reduce
    // module sizes in production
    .addEntry('app', [
        './assets/js/app.js'
    ])

    // Page specific JS entries (CSS is included from the JS files)
    .addEntry('home', [
      './assets/js/home.js'
    ])
    .addEntry('search', [
        './assets/js/search.js'
    ])
    .addEntry('user_view', [
        './assets/js/user_view.js'
    ])
    .addEntry('project_view', [
        './assets/js/project_view.js',
    ])
    .addEntry('project_settings', [
        './assets/js/project_settings.js'
    ])
    .addEntry('project_new', [
        './assets/js/project_new.js'
    ])
    .addEntry('planet', [
        './assets/js/planet.js'
    ])
    .addEntry('notification_list', [
        './assets/js/notification_list.js'
    ])
    .addEntry('notification_inbox', [
        './assets/js/notification_inbox.js'
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

    .splitEntryChunks()

    .enablePostCssLoader()
;

const config = Encore.getWebpackConfig();

// Poll for changes only if running under a VM
const shouldPoll = process.env.ENCORE_ON_LOCAL !== 'true';
config.watchOptions = {
    poll: shouldPoll,
};

module.exports = config;
