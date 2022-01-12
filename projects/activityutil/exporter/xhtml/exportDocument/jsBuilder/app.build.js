({
    baseUrl: "./",
    out: '../templates/js/build.js',
    name: 'main',
    paths: {
        requireLib: 'require'
    },
    include: 'requireLib',
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
