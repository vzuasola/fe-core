var path = require("path");

module.exports = function (options) {
    var ExtractTextPlugin = require(options.modules + "extract-text-webpack-plugin");
    var StyleLintPlugin = require(options.modules + 'stylelint-webpack-plugin');
    var OptimizeCssAssetsPlugin = require(options.modules + "optimize-css-assets-webpack-plugin");
    var webpack = require(options.modules + "webpack");
    var cssNano = require(options.modules + "cssnano");

    return {
        // Entry --------------------
        entry: options.entryStatic.entry,

        // Output --------------------
        output: {
            filename: "js/[name].js",
            path: options.entryStatic.paths.dist
        },

        // Module --------------------
        module: {
            rules: [
                {
                    enforce: "pre",
                    test: /\.js$/,
                    exclude: [/node_modules/, /vendor/],
                    loader: "eslint-loader",
                    options: {
                        failOnError: false,
                        failOnWarning: false,
                        emitError: false,
                        emitWarning: false,
                    }
                },
                {
                    test: /\.scss$/,
                    loader: ExtractTextPlugin.extract({
                        fallback: "style-loader",
                        use: "css-loader?sourceMap!sass-loader?sourceMap",
                    })
                },
                {
                    test: /\.(jpe?g|png|gif|svg)$/i,
                    use: "file-loader?name=/images/[name].[ext]"
                }
            ]
        },

        // Plugins --------------------
        plugins: [
            new StyleLintPlugin({
                context: './../',
                configFile: './../core/core/devtool/config/.stylelintrc',
                files: ['core/core/assets/sass/**/*.scss', 'assets/sass/**/*.scss']
            }),
            new webpack.DefinePlugin({
                'process.env': {
                    'NODE_ENV': JSON.stringify('static')
                }
            }),
            new ExtractTextPlugin("css/[name].css"),
            new OptimizeCssAssetsPlugin({
                assetNameRegExp: /\.css$/g,
                cssProcessor: cssNano,
                cssProcessorOptions: { discardComments: {removeAll: true }, zindex: false},
                canPrint: true
            }),
            new webpack.optimize.UglifyJsPlugin({
                mangle: {
                    screw_ie8: false,
                },
                compress: {
                    screw_ie8: false,
                },
                output: {
                    screw_ie8: false
                }
            })
        ],

        // Resolve --------------------
        resolve: {
            alias: {
                // Base
                Base: path.resolve(__dirname, "./../../assets/js/components"),
                BaseVendor: path.resolve(__dirname, "./../../assets/js/vendor"),
                BaseTemplate: path.resolve(__dirname, "./../../templates"),

                // Site Specific
                Site: path.resolve(options.site, "./../assets/js/components"),
                Vendor: path.resolve(options.site, "./../assets/js/vendor"),
                SiteTemplate: path.resolve(options.site, "./../templates"),
            },
        }
    };
};
