var dotenv           = require('dotenv').config();
var gulp             = require('gulp');
var watch            = require('gulp-watch');
var through          = require('through2');
var rev              = require('gulp-rev');
var revDel           = require('rev-del');
var revReplace       = require('gulp-rev-replace');
var rename           = require('gulp-rename');
var rimraf           = require('rimraf');
var awspublish       = require('gulp-awspublish');
var publisher        = awspublish.create(awsconfig());
var headers          = { 'Cache-Control': 'public, max-age=31536000' };
var currentTimestamp = (new Date).getTime().toString();

gulp.task('revision', function () {
    return gulp.src('resources/styles/**/*.css')
        .pipe(rev())
        .pipe(rename(function(file) { file.dirname = currentTimestamp; }))
        .pipe(revReplace())
        .pipe(gulp.dest('public/styles'))
        .pipe(rev.manifest())
        .pipe(revDel({ dest: 'public/styles' }))
        .pipe(deleteEmptyFolders())
        .pipe(gulp.dest('public/styles'));
});

gulp.task('upload', ['revision'], function () {
    return gulp.src('public/styles/**/*.css')
        .pipe(publisher.publish(headers))
        .pipe(publisher.sync(currentTimestamp))
        .pipe(awspublish.reporter());
});

gulp.task('watch', function () {
    gulp.watch(['resources/styles/**/*.css'], ['upload']);
});

/////////

function awsconfig() {
    return {
        "key": process.env.AWS_KEY,
        "secret": process.env.AWS_SECRET,
        "bucket": "oliver-uploads"
    }
}

function deleteEmptyFolders() {
    return through.obj(function(file, enc, cb) {
        file.revDeleted.forEach(function (deletedFile) {
            var deletedFolder         = deletedFile.substring(0, deletedFile.lastIndexOf('/'));
            var folderHasOldTimestamp = (deletedFolder.indexOf(currentTimestamp) === -1);

            if (folderHasOldTimestamp) rimraf(deletedFolder, function(){});
        });
        cb(null, file);
    });
};
