/**
 * Copyright (c) 2016 Axel Helmert
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author    Axel Helmert
 * @copyright Copyright (c) 2016 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

'use strict';

var watchify = require('watchify');
var browserify = require('browserify');
var gulp = require('gulp');
var gutil = require('gulp-util');
var uglify = require('gulp-uglify');
var sourcemaps = require('gulp-sourcemaps');
var sass = require('gulp-sass');
var del = require('del');
var source = require('vinyl-source-stream');
var buffer = require('vinyl-buffer');
var transform = require('vinyl-transform');
var assign = require('lodash').assign;

var TASKS = {
    WATCH: 'watch',
    BUILD: 'build',
    CLEAN: 'clean',
    JS: {
        WATCH : 'js-watch',
        BUILD: 'js-build',
    },
    CSS : {
        DEBUG: 'scss-debug',
        WATCH: 'scss-watch',
        BUILD: 'scss-build'
    }
};

var DIST = {
    CSS : './css',
    JS : './js',
    BUNDLES: {
        APP: 'app.js'
    }
};

var SRC = {
    SCSS : './src/scss/**/*.scss',
    JS: './src/js/',
    BUNDLES: {
        APP: './src/js/app.js'
    }
};

/**
 * Creates a watchify JS build task
 *
 * @param taskName
 * @param srcFile
 * @param targetFile
 * @param targetDirectory
 */
function createWatchifyTask(taskName, srcFile, targetFile, targetDirectory)
{
    // add custom browserify options here
    var customOpts = {
        entries: [srcFile],
        paths: ['./node_modules', SRC.JS],
        debug: true
    };

    var opts = assign({}, watchify.args, customOpts);
    var b = watchify(browserify(opts));

    function jsWatchBundle()
    {
        return b.bundle()
            // log errors if they happen
            .on('error', gutil.log.bind(gutil, 'Browserify Error'))
            .pipe(source(targetFile))
            // optional, remove if you don't need to buffer file contents
            .pipe(buffer())
            // optional, remove if you dont want sourcemaps
            .pipe(sourcemaps.init({loadMaps: true})) // loads map from browserify file
            // Add transformation tasks to the pipeline here.
            .pipe(sourcemaps.write()) // writes .map file
            .pipe(gulp.dest(targetDirectory));
    }

    // add transformations here
    //  i.e. b.transform(coffeeify);
    gulp.task(taskName, jsWatchBundle); // so you can run `gulp js` to build the file
    b.on('update', jsWatchBundle); // on any dep update, runs the bundler
    b.on('log', gutil.log); // output build logs to terminal
}

/**
 * Creates a browserify build task
 *
 * @param taskName
 * @param srcEntries
 * @param bundleFile
 * @param targetDirectory
 */
function gulpBrowserifyBuild(taskName, srcEntries, bundleFile, targetDirectory)
{
    gulp.task(taskName, function () {
        // set up the browserify instance on a task basis
        var b = browserify({
            entries: srcEntries,
            paths: ['./node_modules', SRC.JS],
            debug: true
        });

        return b.bundle()
            .pipe(source(bundleFile))
            .pipe(buffer())
            .pipe(uglify({compress: { drop_console: true }}))
            .on('error', gutil.log)
            .pipe(gulp.dest(targetDirectory));
    });
}

// Create the JS build and wtach tasks:
createWatchifyTask(TASKS.JS.WATCH, SRC.BUNDLES.APP, DIST.BUNDLES.APP, DIST.JS)
gulpBrowserifyBuild(TASKS.JS.BUILD, SRC.BUNDLES.APP, DIST.BUNDLES.APP, DIST.JS);


// ================ CSS TASKS =================

gulp.task(TASKS.CSS.DEBUG, function () {
    return gulp.src(SRC.SCSS)
        .pipe(sourcemaps.init())
        .pipe(sass().on('error', sass.logError))
        .pipe(sourcemaps.write('./'))
        .pipe(gulp.dest(DIST.CSS));
});

gulp.task(TASKS.CSS.BUILD, function () {
    return gulp.src(SRC.SCSS)
        .pipe(sass().on('error', sass.logError))
        .pipe(gulp.dest(DIST.CSS));
});

gulp.task(TASKS.CSS.WATCH, [TASKS.CSS.DEBUG], function () {
    gulp.watch(SRC.SCSS, [TASKS.CSS.DEBUG]);
});

// ================ CSS TASKS END =================


// ================ CLEAR TASK  =================

gulp.task(TASKS.CLEAN, function () {
    return del( [DIST.JS, DIST.CSS], {force: true} );
});

// ================  THIS ARE THE IMPORTANT TASKS !!! ================

// WATCH TASK for js and scss
gulp.task(TASKS.WATCH, [TASKS.CLEAN, TASKS.JS.WATCH, TASKS.CSS.WATCH]);

// BUILD TASK for js and scss
gulp.task(TASKS.BUILD, [TASKS.CLEAN, TASKS.JS.BUILD, TASKS.CSS.BUILD]);

// ====================================================================
