/* eslint-disable */
const autoprefixer = require('gulp-autoprefixer'),
  browserSync = require('browser-sync'),
  cssImporter = require('node-sass-css-importer')({
    import_paths: ['./scss']
  }),
  del = require('del'),
  eslint = require('gulp-eslint'),
  gulp = require('gulp'),
  newer = require('gulp-newer'),
  path = require('path'),
  reload = browserSync.reload,
  rollup = require('rollup'),
  rollupBabel = require('rollup-plugin-babel'),
  rollupCommonjs = require('rollup-plugin-commonjs'),
  rollupResolve = require('rollup-plugin-node-resolve'),
  rollupUglify = require('rollup-plugin-uglify').uglify,
  sass = require('gulp-sass'),
  themeYaml = './theme.yml',
  year = new Date().getFullYear(),
  yaml = require('yamljs');

let theme = yaml.load(themeYaml);

const babelConfig = {
  presets: [
    [
      '@babel/env',
      {
        loose: true,
        modules: false,
        exclude: ['transform-typeof-symbol']
      }
    ]
  ],
  plugins: [
    '@babel/plugin-proposal-object-rest-spread'
  ],
  env: {
    test: {
      plugins: ['istanbul']
    }
  },
  exclude: 'node_modules/**', // Only transpile our source code
  externalHelpersWhitelist: [ // Include only required helpers
    'defineProperties',
    'createClass',
    'inheritsLoose',
    'defineProperty',
    'objectSpread2'
  ],
};

getPaths = () => {
  return {
    pages: {
      folder: 'pages',
      all: ['pages/**/*'],
      html: 'pages/*.html',
    },
    js: {
      mrare: {
        all: "js/mrare/**/*.js",
        index: "js/mrare/index.js",
      }
    },
    scss: {
      all: 'scss/**/*',
      themeScss: ['scss/theme.scss', '!scss/user.scss', '!scss/user-variables.scss'],
    },
    assets: {
      all: 'pages/assets/**/*',
      folder: 'pages/assets',
      allFolders: ['pages/assets/img', 'pages/assets/js'],
    },
    dist: {
      folder: 'dist',
      all: 'dist/**/*',
      assets: 'dist/assets',
      css: 'dist/assets/css',
      js: 'dist/assets/js',
    },
  }
};

let paths = getPaths();

// DEFINE TASKS

gulp.task('clean:dist', function (done) {
  del.sync(paths.dist.all, {
    force: true
  });
  done();
});

// Copy html files to dist
gulp.task('html', function () {
  return gulp.src(paths.pages.all, {
      base: paths.pages.folder
    })
    .pipe(newer(paths.dist.folder))
    .pipe(gulp.dest(paths.dist.folder))
    .pipe(reload({
      stream: true
    }));
});

gulp.task('sass', function () {
  return gulp.src(paths.scss.themeScss)
    .pipe(sass({
      importer: [cssImporter]
    }).on('error', sass.logError))
    .pipe(autoprefixer())
    .pipe(gulp.dest(paths.dist.css))
    .pipe(browserSync.stream({
      match: "**/theme*.css"
    }));
});

gulp.task('mrarejs', async (done) => {
  gulp.src(paths.js.mrare.all)
    .pipe(eslint())
    .pipe(eslint.format());

  let fileDest = 'theme.js';
  const banner = `/*!
  * ${theme.name}
  * Copyright 2018-${year} Medium Rare (${theme.purchase_link})
  */`;
  const external = [...theme.scripts.external];
  const plugins = [
    rollupCommonjs(),
    rollupResolve({
      browser: true,
    }),
    rollupBabel(babelConfig),
    theme.minify_scripts === true ? rollupUglify({
      output: {
        comments: "/^!/"
      }
    }) : null,
  ];
  const globals = theme.scripts.globals;

  const bundle = await rollup.rollup({
    input: paths.js.mrare.index,
    external,
    plugins,
    onwarn: function (warning) {
      // Skip certain warnings
      if (warning.code === 'THIS_IS_UNDEFINED') {
        return;
      }
      // console.warn everything else
      console.warn(warning.message);
    }
  });

  await bundle.write({
    file: path.resolve(__dirname, `./${paths.dist.js}${path.sep}${fileDest}`),
    banner,
    globals,
    format: 'umd',
    name: 'theme',
  });
  // Reload Browsersync clients
  reload();
  done();
});

// Assets
gulp.task('copy-assets', function () {
  return gulp.src(paths.assets.all, {
      base: paths.assets.folder
    })
    .pipe(newer(paths.dist.assets))
    .pipe(gulp.dest(paths.dist.assets))
    .pipe(reload({
      stream: true
    }));
});

// watch files for changes and reload
gulp.task('serve', function (done) {
  browserSync({
    server: {
      baseDir: './dist',
      index: "index.html"
    }
  });
  done();
});

gulp.task('watch', function (done) {

  // PAGES
  // Watch only .html pages as they can be recompiled individually
  gulp.watch([paths.pages.html], {
    cwd: './'
  }, gulp.series('html', function reloadPage(done) {
    reload();
    done();
  }));

  // SCSS
  // Any .scss file change will trigger a sass rebuild
  gulp.watch([paths.scss.all], {
    cwd: './'
  }, gulp.series('sass'));

  // JS
  // Rebuild mrare js if files change
  gulp.watch([paths.js.mrare.all], {
    cwd: './'
  }, gulp.series('mrarejs'));

  // Copy assets if files change
  const assetsWatcher = gulp.watch([paths.assets.all, ...paths.assets.allFolders], {
    cwd: './'
  }, gulp.series('copy-assets'));

  assetsWatcher.on('change', function (filePath) {
    console.log('File ' + filePath + ' was changed');
  });

  assetsWatcher.on('unlink', function (filePath) {
    const changedDistFile = path.resolve(paths.dist.assets, path.relative(path.resolve(paths.assets.folder), filePath));
    console.log('File ' + filePath + ' was removed');
    del.sync(changedDistFile, { force: true });
  });

  done();
  // End watch task

});

gulp.task('default', gulp.series('clean:dist', 'copy-assets', gulp.series('html', 'sass', 'mrarejs'), gulp.series('serve', 'watch')));

gulp.task('build', gulp.series('clean:dist', 'copy-assets', gulp.series('html', 'sass', 'mrarejs')));
