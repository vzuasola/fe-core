var path = require("path");

module.exports = function (options) {
    var webpack = require(options.modules + "webpack");
    var StyleLintPlugin = require(options.modules + "stylelint-webpack-plugin");

    return {
        // Entry --------------------
        entry: options.entrypoint.entry,

        // Output --------------------
        output: {
            filename: "js/[name].bundle.js",
            path: options.entrypoint.paths.dist,
            publicPath: ""
        },

        module: {
            rules: [
                {
                    test: /\.scss$/,
                    use: [{
                        loader: "style-loader"
                    }, {
                        loader: "css-loader"
                    }, {
                        loader: "sass-loader"
                    }]
                },
                {
                    test: /\.(jpe?g|png|gif|svg)$/i,
                    use: "file-loader?name=/images/[name].[ext]"
                },
                {
                    test: /\.handlebars$/,
                    loader: "handlebars-loader",
                    query: {
                        helperDirs: [
                            path.resolve(options.site, "./../assets/js/components/handlebars-helpers"),
                            path.resolve(__dirname, "../../core/core/assets/js/components/handlebars-helpers")
                        ]
                    }
                },
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
                    'NODE_ENV': JSON.stringify('lint')
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
