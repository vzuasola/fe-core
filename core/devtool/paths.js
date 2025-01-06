var path = require("path");

module.exports = function (options) {
    return {
        baseDevtool: path.resolve(__dirname, "./") + "/",
        baseSrc: path.resolve(__dirname, "./../assets") + "/",
        src: path.resolve(options.site, "./../assets") + "/",
        dist: path.resolve(options.site, "./../web") + "/",
        devtool: path.resolve(options.site, "./devtool") + "/",
        modules: path.resolve(options.site, "./devtool/node_modules") + "/"
    };
};
