var path = require("path");

module.exports = function (options) {
    var baseConfig = require(path.resolve(__dirname, './webpack.base.js'))(options);
    var webpack = require(options.modules + "webpack");
    var webpackMerge = require(options.modules + 'webpack-merge');
    var StyleLintPlugin = require(options.modules + 'stylelint-webpack-plugin');

    return webpackMerge(baseConfig, {
        // Devtool --------------------
        devtool: "source-map",

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
                    'NODE_ENV': JSON.stringify('dev')
                }
            })
        ]
    });
};
