'use strict';
const gulpfile = require('gulp');
const sass = require('gulp-sass')(require('sass'));
const sourcemaps = require('gulp-sourcemaps');
const autoprefixer = require('gulp-autoprefixer');
const concat = require('gulp-concat');
const uglify = require('gulp-uglify');
sass.compiler = require('node-sass');

gulpfile.task('sass', function () {
    return gulpfile.src('platform/themes/stories/assets/sass/app.scss')
        .pipe(sourcemaps.init())
        .pipe(sass({
            outputStyle: 'compressed'
        }))
        .pipe(autoprefixer({
            overrideBrowserslist: ['last 2 versions']
        }))
        .pipe(concat('style.css'))
        .pipe(sourcemaps.write('.'))
        .pipe(gulpfile.dest('./public/themes/stories/css'));
});


gulpfile.task('scripts', function () {
    return gulpfile.src('platform/themes/stories/assets/js/**/*.js')
        .pipe(concat('script.js'))
        .pipe(uglify())
        .pipe(gulpfile.dest('./public/themes/stories/js/'))
});

//Gulp 4 phai su dung cu phap gulp series
gulpfile.task('watch', function () {
    gulpfile.watch('platform/themes/stories/assets/sass/**/*.scss', gulpfile.series('sass'));
    gulpfile.watch('platform/themes/stories/assets/js/**/*.js', gulpfile.series('scripts'));
});
