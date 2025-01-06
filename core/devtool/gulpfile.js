var path = require("path"),
    paths = require(path.resolve(__dirname, "./paths"))({
    site: __dirname + './../../../'
});

var gulp = require(paths.modules + 'gulp'),
    del = require(paths.modules + 'del'),
    imagemin = require(paths.modules + 'gulp-imagemin'),
    uglify = require(paths.modules + 'gulp-uglify'),
    pump = require(paths.modules + 'pump'),
    rename = require(paths.modules + 'gulp-rename');

// Watch SASS, JS and images from "src" folWder
gulp.task('watch', function () {
    gulp.watch(paths.baseSrc + 'js/**/*.js', ['compress-detection', 'compress-es5-polyfill']);
});

gulp.task('clean', function () {
    return del(
        [
            paths.dist + "js",
            paths.dist + "css",
            paths.dist + "images"
        ],
        {
            force: true
        }
    );
});

// Image compression
gulp.task('imagemin', function () {
    return  gulp.src([paths.src + 'images/**/*.+(png|jpg|gif|svg|ico)', paths.baseSrc + 'images/**/*.+(png|jpg|gif|svg|ico)'])
        .pipe(imagemin({ progressive: true }))
        .pipe(gulp.dest(`${paths.dist}` + 'images'));
});

// Compress feature detection/outdated browser script
gulp.task('compress-detection', function (cb) {
    pump([
        gulp.src(paths.baseSrc + 'js/outdated-browser.js'),
        uglify({
            mangle: false,
            compress: false,
            ie8: true,
        }),
        rename('outdated-browser.min.js'),
        gulp.dest(paths.baseSrc + 'js')
    ],
    cb
    );
});

// Compress es5 polyfill
gulp.task('compress-es5-polyfill', function (cb) {
    pump([
        gulp.src(paths.baseSrc + 'js/vendor/es5.js'),
        uglify({
            mangle: false,
            compress: false,
            ie8: true,
        }),
        rename('es5.min.js'),
        gulp.dest(paths.baseSrc + 'js/vendor')
    ],
    cb
    );
});

gulp.task('default', ['compress']);
gulp.task('compress', ['compress-detection', 'compress-es5-polyfill']);

module.exports = gulp;
