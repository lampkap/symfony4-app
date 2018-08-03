var gulp      = require('gulp'),
    sass      = require('gulp-sass'),
    minifyCSS = require('gulp-csso');

gulp.task('sass', function(){
  return gulp.src(['./public/scss/*.scss', './public/scss/_*.scss', './public/scss/**/_*.scss'])
    .pipe(sass().on('error', sass.logError))
    .pipe(minifyCSS())
    .pipe(gulp.dest('./public/css'))
});

gulp.task('sass:watch', function () {
    gulp.watch('./public/scss/**/*.scss', ['sass']);
});

gulp.task('default', ['sass:watch']);