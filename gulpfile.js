var gulp = require('gulp');

gulp.task('default', function () {
    return gulp.src('node_modules/jquery/**/*')
        .pipe(gulp.dest('public/vendor/jquery'));
});
