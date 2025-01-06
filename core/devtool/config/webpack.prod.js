var path = require("path");

module.exports = function (options) {
    var baseConfig = require(path.resolve(__dirname, './webpack.base.js'))(options);
    var webpack = require(options.modules + "webpack");
    var OptimizeCssAssetsPlugin = require(options.modules + "optimize-css-assets-webpack-plugin");
    var webpackMerge = require(options.modules + "webpack-merge");
    var cssNano = require(options.modules + "cssnano");

    return webpackMerge(baseConfig, {
        // DevTool --------------------
        devtool: "hidden-source-map",

        // Plugins --------------------
        plugins: [
            new webpack.DefinePlugin({
                'process.env': {
                    'NODE_ENV': JSON.stringify('prod')
                }
            }),
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
        ]
    });
};
