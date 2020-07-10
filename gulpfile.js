'use strict';

const gulp = require('gulp');
const gutil = require('gulp-util');
const concat = require('gulp-concat');
const less = require('gulp-less');
const sass = require('gulp-sass');
const terser = require('gulp-terser');
const cleanCSS = require('gulp-clean-css');
const autoprefixer = require('gulp-autoprefixer');
const plumber = require('gulp-plumber');
const rename = require('gulp-rename');
const gulpSequence = require('gulp-sequence');
const browserSync = require('browser-sync');
const argv = require('yargs').argv;
const fs = require('fs');
const del = require('del');
const runSequence = require('run-sequence');

const frontendConfig = require('./gulpfile.frontend.conf.json');
const sitemgrConfig = require('./gulpfile.sitemgr.conf.json');

const themeFolders = readFolders(frontendConfig.paths.themes);

const autoprefix = [
    "last 15 version",
    "> 0.5%"
]

/*
 * If you create a new theme, make a new key on the host object
 * name-of-folder-of-theme: 'url-of-edirectory'
 */
if (fs.existsSync('./gulpfile.hosts.conf.json')) {
    var hosts = require('./gulpfile.hosts.conf.json');
} else {
    var hosts = {
        default: 'edirectory.arcasolutions.com',
        doctor: 'edirectory-doctor.arcasolutions.com',
        restaurant: 'edirectory-restaurant.arcasolutions.com',
        wedding: 'edirectory-wedding.arcasolutions.com'
    };
}

/*
 * Scripts files
 */
const scripts = [
    'node_modules/jquery/dist/jquery.js',
    'node_modules/js-cookie/src/js.cookie.js',
    'node_modules/select2/dist/js/select2.full.js',
    'node_modules/jsviews/jsrender.js',
    'node_modules/vanilla-lazyload/dist/lazyload.js',
    './web/assets/js/lib/smartbanner/jquery.smartbanner.js',
    './web/assets/js/modal/modal.js',
    './web/assets/js/utility/main.js'
];

// vendor styles
const vendorStyles = [
    'node_modules/flickity/dist/flickity.css',
    'node_modules/select2/dist/css/select2.css',
    'node_modules/bootstrap-datepicker/dist/css/bootstrap-datepicker3.standalone.css',
    'node_modules/@fancyapps/fancybox/dist/jquery.fancybox.css',
    'node_modules/social-likes/dist/social-likes_birman.css',
];

// =========================================== //
//               General Tasks                 //
// =========================================== //

/*
 * Task: Handler Error
 * Function: Print on console the error
 * @err Error
 */
const onError = function (err) {
    gutil.beep();
    gutil.log(gutil.colors.red.italic.bold(err + '\n'));
};

/*
 * Task: BrowserSync
 * Function: Open a local server and refresh browser automatic on each change
 */
gulp.task('browserSync', function () {
    var themeUrls = Object.values(hosts);
    var proxyUrl = hosts[argv.theme] || themeUrls[0];

    browserSync({
        proxy: proxyUrl,
        notify: false
    });
});

gulp.task('bs-reload', function () {
    browserSync.reload();
});

// refatorar e trocar para gulp-clean
gulp.task('clean-assets', function () {
    return del([
        './app/Resources/assets/styles/*.css',
        './app/Resources/themes/**/assets/*.css',
        './web/assets/**/styles/*.css',
        './web/assets/**/scripts/*.js'
    ]);
});

/*
 * Task: Read all folders from a PATH
 */

function readFolders(path) {
    var dirs = fs.readdirSync(path);

    for (var j = 0; j < dirs.length;) {
        (/(.*)\.(.*)/).test(dirs[j]) ? dirs.splice(j, 1) : j++;
    }

    return dirs;
}

// =========================================== //
//             Site Manager Tasks              //
// =========================================== //

/*
* Task: Compile Less
* Function: Compile less file and generate build file
*/
gulp.task('sitemgr-less', function () {
    return gulp.src(sitemgrConfig.src.styles.paths)
        .pipe(plumber({ errorHandler: onError }))
        .pipe(less())
        .pipe(concat('build.css'))
        .pipe(gulp.dest(sitemgrConfig.paths.styles.src));
});

/*
* Task: Minify css
* Function: Minify, add auto prefix and concat to minified file
*/
gulp.task('sitemgr-style', function () {
    return gulp.src(sitemgrConfig.dist.styles.paths)
        .pipe(plumber({ errorHandler: onError }))
        .pipe(autoprefixer({ browsers: autoprefix, cascade: true }))
        .pipe(concat('styles.css'))
        .pipe(gulp.dest(sitemgrConfig.paths.styles.dist))
        .pipe(rename({ suffix: '.min' }))
        .pipe(cleanCSS())
        .pipe(gulp.dest(sitemgrConfig.paths.styles.dist));
});

/*
* Task: Sitemgr Default
* Function: run in sequence the less compile task and minify task
*/
gulp.task('sitemgr', function (cb) {
    gulpSequence('sitemgr-less', 'sitemgr-style')(cb);
});

/*
* Task: Sitemgr Watch
* Function: Listen all modify and reload the browser automatic
*/
gulp.task('sitemgr-watch', ['browserSync'], function () {
    gulp.watch(sitemgrConfig.src.styles.watch, ['sitemgr']);
    gulp.watch(sitemgrConfig.dist.styles.watch, ['bs-reload']);
    gulp.watch(sitemgrConfig.watch, ['bs-reload']);
});


// =========================================== //
//               Front-end Tasks               //
// =========================================== //

gulp.task('build-core', function () {
    const streams = themeFolders
        .filter((f) => argv.theme !== undefined ? f === argv.theme : true)
        .map((f) => {
            const stream = gulp.src(frontendConfig.paths.themes + f + frontendConfig.src.styles.paths.themes)
                .pipe(plumber({ errorHandler: onError }))
                .pipe(sass())
                .pipe(rename('main.css'))
                .pipe(gulp.dest(frontendConfig.dist.styles.paths.themes + f + '/assets/'));

            return new Promise((resolve, reject) => {
                stream.on('end', () => resolve(stream.read()));
                stream.on('error', reject);
            });
        });
    return Promise.all(streams);
});

/*
* Task: Build vendor file
* Function: Compile the vendor files and output the vendor css
*/
gulp.task('build-vendor', function () {
    return gulp.src(vendorStyles)
        .pipe(plumber({ errorHandler: onError }))
        .pipe(concat('vendor.css'))
        .pipe(gulp.dest(frontendConfig.dist.styles.paths.assets));
});

/*
* Task: Build vendor file
* Function: Compile the vendor files and output the vendor css
*/
gulp.task('build-fontawesome', function () {
    const fontAwesomePath = 'app/Resources/base/styles/libs/font-awesome/font-awesome.scss';

    const streams = themeFolders.map((f) => {
        const stream = gulp.src(fontAwesomePath)
            .pipe(plumber({ errorHandler: onError }))
            .pipe(sass())
            .pipe(rename('fontawesome.min.css'))
            .pipe(cleanCSS())
            .pipe(gulp.dest(frontendConfig.paths.assets + f + '/styles'));
    })

    return Promise.all(streams);
});

/*
* Task: Build Styles
* Function: Compile all the build files and otput the style css
*/

gulp.task('build-style', function () {
    const vendorFile = frontendConfig.dist.styles.paths.assets + 'vendor.css';

    const streams = themeFolders
        .filter((f) => argv.theme !== undefined ? f === argv.theme : true)
        .map((f) => {
            const coreFile = frontendConfig.dist.styles.paths.themes + f + '/assets/main.css';
            const stream = gulp.src([vendorFile, coreFile])
                .pipe(plumber({ errorHandler: onError }))
                .pipe(autoprefixer({ browsers: autoprefix, cascade: true }))
                .pipe(concat('style.css'))
                .pipe(gulp.dest(frontendConfig.paths.assets + f + '/styles'))
                .pipe(rename({ suffix: '.min' }))
                .pipe(cleanCSS())
                .pipe(gulp.dest(frontendConfig.paths.assets + f + '/styles'));

            return new Promise((resolve, reject) => {
                stream.on('end', () => resolve(stream.read()));
                stream.on('error', reject);
            });
        });
    return Promise.all(streams);
});

/*
* Task: Build Scripts
* Function: Compile all the build files and otput the scripts js
*/

gulp.task('build-scripts', function () {
    const streams = themeFolders
        .filter((f) => argv.theme !== undefined ? f === argv.theme : true)
        .map((f) => {
            const stream = gulp.src(scripts)
                .pipe(plumber({ errorHandler: onError }))
                .pipe(concat('main.js'))
                .pipe(gulp.dest(frontendConfig.paths.assets + f + '/scripts'))
                .pipe(rename({ suffix: '.min' }))
                .pipe(terser())
                .pipe(gulp.dest(frontendConfig.paths.assets + f + '/scripts'));

            return new Promise((resolve, reject) => {
                stream.on('end', () => resolve(stream.read()));
                stream.on('error', reject);
            });
        });
    return Promise.all(streams);
});

/*
* Task: Task Runner
* Function: Run all styles tasks
*/
gulp.task('frontend-style', function (cb) {
    gulpSequence('build-vendor', 'build-core', 'build-style')(cb);
});

/*
* Task: Task Runner
* Function: Run all the build taks
*/
gulp.task('frontend-build', function (cb) {
    gulpSequence('build-vendor', 'build-core', 'build-style', 'build-scripts', 'browserSync')(cb);
});

/*
* Task: Task Runner
* Function: Run all the build taks
*/
gulp.task('frontend', function (cb) {
    const hasFontawesome = fs.existsSync(frontendConfig.paths.assets + themeFolders[0] + '/styles/fontawesome.min.css');
    gulpSequence('build-vendor', 'build-core', 'build-style', 'build-scripts', (!hasFontawesome ? 'build-fontawesome' : ''))(cb);
});

/*
* Task: Task Runner watch
* Function: Watch the modified files and reload browser
*/
gulp.task('frontend-watch', ['frontend-build'], function () {
    gulp.watch(frontendConfig.src.styles.watch, function () { runSequence('frontend-style', 'bs-reload') });
    gulp.watch(frontendConfig.src.scripts.watch, function () { runSequence('build-scripts', 'bs-reload') });
    gulp.watch(frontendConfig.watch, ['bs-reload']);
});
