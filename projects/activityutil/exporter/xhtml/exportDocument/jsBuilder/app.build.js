({
    baseUrl: "./",
    out: '../templates/js/build.js',
    optimize: "uglify2",
    name: 'main',
    paths: {
        requireLib: 'require',
        "jquery.ioc-tools": "../../../../../../../../lib_ioc/wikiiocmodel/templates/jquery.ioc-tools"
    },
    include: ['requireLib', 'jquery.ioc-tools'],
    shim: {
        'jquery-ui.min': {
            deps: ['jquery.min']
        },
        'jquery.imagesloaded': {
            deps: ['jquery.min'],
            exports: 'jQuery.fn.imagesLoaded'
        }
    }
})
