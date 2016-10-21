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
        WATCH: 'js-watch',
        BUILD: 'js-build'
    },
    CSS : {
        DEBUG: 'scss-debug',
        WATCH: 'scss-watch',
        BUILD: 'scss-build'
    }
};

var DIST = {
    CSS : __dirname + '/css/',
    JS : __dirname + '/js/',
};

var SRC = {
    SCSS : __dirname + '/src/scss/**/*.scss',
    JS: __dirname + '/src/js/',
};

/**
 * Adds externals to the browserify bundler
 *
 * @private
 * @param {browserify} bundler
 */
function _addExternals(bundler)
{
    if (this.expose) {
        this.expose.forEach(function(expose) {
            bundler.require(expose, {expose: expose});
        });
    }

    if (this.externals) {
        this.externals.forEach(function(external) {
            bundler.external(external);
        });
    }
};

var bundles = {
    app: {
        src: 'app.js',
        bundle: 'app.js',
        expose: [ 'angular', 'nexus.ui.core/constants/Constants' ],
        add: _addExternals
    },
    ansible: {
        src: 'nexus.ui.ansible/module.js',
        bundle: 'nexus.ui.ansible.js',
        externals: [ 'angular', 'nexus.ui.core/constants/Constants' ],
        add: _addExternals
    }
}

/**
 * Creates a watchify JS build task
 *
 * @param {string} taskName
 * @param {Object} bundleInfo
 */
function createWatchifyTask(taskName, bundleInfo)
{
    // add custom browserify options here
    var customOpts = {
        entries: [ SRC.JS + bundleInfo.src ],
        paths: ['./node_modules', SRC.JS],
        debug: true
    };

    var opts = assign({}, watchify.args, customOpts);
    var bundler = watchify(browserify(opts));

    bundleInfo.add(bundler);

    function jsWatchBundle()
    {
        return bundler
            //.transform('babelify', {presets: ['es2015']})
            .bundle()
            // log errors if they happen
            .on('error', gutil.log.bind(gutil, 'Browserify Error'))
            .pipe(source(bundleInfo.bundle))
            // optional, remove if you don't need to buffer file contents
            .pipe(buffer())
            // optional, remove if you dont want sourcemaps
            .pipe(sourcemaps.init({loadMaps: true})) // loads map from browserify file
            // Add transformation tasks to the pipeline here.
            .pipe(sourcemaps.write()) // writes .map file
            .pipe(gulp.dest(DIST.JS));
    }

    // add transformations here
    //  i.e. b.transform(coffeeify);
    gulp.task(taskName, jsWatchBundle); // so you can run `gulp js` to build the file
    bundler.on('update', jsWatchBundle); // on any dep update, runs the bundler
    bundler.on('log', gutil.log); // output build logs to terminal
}

/**
 * Creates a browserify build task
 *
 * @param taskName
 * @param {Object} bundleInfo
 */
function createBrowserifyTask(taskName, bundleInfo)
{
    gulp.task(taskName, function () {
        // set up the browserify instance on a task basis
        var b = browserify({
            entries: SRC.JS + bundleInfo.src,
            paths: ['./node_modules', SRC.JS],
            debug: false
        });

        bundleInfo.add(b);

        return b
            //.transform('babelify', {presets: ['es2015']})
            .bundle()
            .pipe(source(DIST.JS + bundleInfo.bundle))
            .pipe(buffer())
            .pipe(uglify({compress: { drop_console: true }}))
            .on('error', gutil.log)
            .pipe(gulp.dest(DIST.JS));
    });
}

var jsWatchDeps = [];
var jsBuildDeps = [];

Object.getOwnPropertyNames(bundles).forEach(function(name) {
    var bundle = bundles[name];
    var taskName = 'js-' + name;

    createWatchifyTask(taskName + '-watch', bundle);
    createBrowserifyTask(taskName + '-build', bundle);

    jsWatchDeps.push(taskName + '-watch');
    jsBuildDeps.push(taskName + '-build');
});

gulp.task(TASKS.JS.BUILD, jsBuildDeps);
gulp.task(TASKS.JS.WATCH, jsWatchDeps);

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
