var path = require("path");

module.exports = function (options) {
    var ExtractTextPlugin = require(options.modules + "extract-text-webpack-plugin");
    var CommonsChunkPlugin = require(options.modules + "webpack/lib/optimize/CommonsChunkPlugin");
    var CopyWebpackPlugin = require(options.modules + "copy-webpack-plugin");
    var ImageminPlugin = require(options.modules + 'imagemin-webpack-plugin').default;
    var CleanWebpackPlugin = require(options.modules + 'clean-webpack-plugin');
    var ManifestPlugin = require(options.modules + 'webpack-manifest-plugin');
    var md5 = require(options.modules + 'md5');

    // Use date timestamp as hash
    var customHash = md5(Date.now());

    return {
        // Entry --------------------
        entry: options.entrypoint.entry,

        // Output --------------------
        output: {
            filename: "js/[name]." + customHash + ".bundle.js",
            path: options.entrypoint.paths.dist,
            publicPath: ""
        },

        // Module --------------------
        module: {
            rules: [
                {
                    test: /\.scss$/,
                    loader: ExtractTextPlugin.extract({
                        fallback: "style-loader",
                        use: "css-loader?sourceMap!sass-loader?sourceMap",
                    })
                },
                {
                    test: /\.(jpe?g|png|gif|svg)$/i,
                    use: "file-loader?name=/images/[name]." + customHash + ".[ext]"
                },
                {
                    test: /\.handlebars$/,
                    loader: "handlebars-loader",
                    query: {
                        helperDirs: [
                            path.resolve(options.site, "./../assets/js/components/handlebars"),
                            path.resolve(__dirname, "./../../assets/js/components/handlebars")
                        ]
                    }
                }
            ]
        },

        // Plugins --------------------
        plugins: [
            new ManifestPlugin(),
            // Delete/remove all recently generated files
            new CleanWebpackPlugin(["css", "images", "js", "manifest.json"], {
                root: path.resolve(__dirname, "../../../../web")
            }),
            new ExtractTextPlugin("css/[name]." + customHash + ".css"),
            new CommonsChunkPlugin({
                name: ["vendor", "manifest"],
                minChunks: 3
            }),
            new CopyWebpackPlugin([
                // Copy Site images
                {
                    from: options.entrypoint.paths.src + "images",
                    to: options.entrypoint.paths.dist + "images"
                },
                // Copy Core Images
                {
                    from: options.entrypoint.paths.baseSrc + "images",
                    to: options.entrypoint.paths.dist + "images"
                },
                // Copy Japan Images
                {
                    from: options.entrypoint.paths.baseSrc + "japan/images",
                    to: options.entrypoint.paths.dist + "images"
                },
                // HTML5 shiv (HTML5 tags for IE8 and below)
                {
                    from: options.entrypoint.paths.baseSrc + "js/vendor/html5shiv.min.js",
                    to: options.entrypoint.paths.dist + "js/html5shiv.min.js"
                },
                // Polyfill for ES5 (Fix for webpack IE8)
                {
                    from: options.entrypoint.paths.baseSrc + "js/vendor/es5.min.js",
                    to: options.entrypoint.paths.dist + "js/es5.min.js"
                },
                // Polyfill for xdomain in IE8
                {
                    from: options.entrypoint.paths.baseSrc + "js/vendor/xdomain.min.js",
                    to: options.entrypoint.paths.dist + "js/xdomain.min.js"
                },
                // IE8 event listener
                {
                    from: options.entrypoint.paths.baseSrc + "js/vendor/ie8-eventlistener.js",
                    to: options.entrypoint.paths.dist + "js/ie8-eventlistener.min.js"
                },
                // Outdated browser script (feature detection)
                {
                    from: options.entrypoint.paths.baseSrc + "js/outdated-browser.min.js",
                    to: options.entrypoint.paths.dist + "js/outdated-browser.min.js"
                },
                // IE 8 normalizer
                {
                    from: options.entrypoint.paths.baseSrc + "js/vendor/ie8-normalizer.js",
                    to: options.entrypoint.paths.dist + "js/ie8-normalizer.min.js"
                },
                // EasyXDM
                {
                    from: options.entrypoint.paths.baseSrc + "js/vendor/easyXDM.min.js",
                    to: options.entrypoint.paths.dist + "js/easyXDM.min.js"
                },
                // Polyfill for browser history
                {
                    from: options.entrypoint.paths.baseSrc + "js/vendor/history.min.js",
                    to: options.entrypoint.paths.dist + "js/history.min.js"
                },
                // Respondjs, polyfill min/max media queries
                {
                    from: options.entrypoint.paths.baseSrc + "js/vendor/respond.min.js",
                    to: options.entrypoint.paths.dist + "js/respond.min.js"
                },
                // Picturefill, polyfill for html5 picture tag
                {
                    from: options.entrypoint.paths.baseSrc + "js/vendor/picturefill.min.js",
                    to: options.entrypoint.paths.dist + "js/picturefill.min.js"
                },
                // ClassList polyfill (for flexible menu)
                {
                    from: options.entrypoint.paths.baseSrc + "js/vendor/classList.min.js",
                    to: options.entrypoint.paths.dist + "js/classList.min.js"
                },
                // iovation config
                {
                    from: options.entrypoint.paths.baseSrc + "js/vendor/iovation-config.js",
                    to: options.entrypoint.paths.dist + "js/iovation-config.js"
                },
                // iovation Loader
                {
                    from: options.entrypoint.paths.baseSrc + "js/vendor/iovation-loader.js",
                    to: options.entrypoint.paths.dist + "js/iovation-loader.js"
                },
            ]),
            new ImageminPlugin({ test: /\.(jpe?g|png|gif)$/i }),
        ],

        // Resolve --------------------
        resolve: {
            alias: {
                // Base
                Base: path.resolve(__dirname, "./../../assets/js/components"),
                BaseVendor: path.resolve(__dirname, "./../../assets/js/vendor"),
                BaseTemplate: path.resolve(__dirname, "./../../templates/dafabet"),

                // Japan
                Japan: path.resolve(__dirname, "./../../assets/japan/js/components"),
                JapanVendor: path.resolve(__dirname, "./../../assets/japan/js/vendor"),
                JapanTemplate: path.resolve(__dirname, "./../../templates/japan"),

                // Site Specific
                Site: path.resolve(options.site, "./../assets/js/components"),
                Vendor: path.resolve(options.site, "./../assets/js/vendor"),
                SiteTemplate: path.resolve(options.site, "./../templates"),
                SiteRoot: path.resolve(options.site, "./../assets/js"),
            }
        }
    };
};
