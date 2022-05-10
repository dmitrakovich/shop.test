<?php

namespace Deployer;

require 'recipe/laravel.php';

// Project name
set('application', 'Barocco');

// Project repository
// set('repository', 'https://github.com/dmitrakovich/shop.test.git');

var_dump(getenv());

// var_dump(getenv('TEST_ENV_VAR'));
var_dump(getenv('DEPLOY_HOST'));
var_dump(getenv('DEPLOY_USERNAME'));
// var_dump(getenv('USERNAME'));
var_dump(getenv('DEPLOY_PORT'));
var_dump(getenv('DEPLOY_PATH'));

// Hosts
host('production')
    ->hostname(getenv('PRODUCTION_HOST'))
    ->user(getenv('PRODUCTION_USERNAME'))
    ->port((int)getenv('PRODUCTION_PORT'))
    ->set('deploy_path', getenv('PRODUCTION_PATH'))
    ->addSshOption('StrictHostKeyChecking', 'no')
    ->addSshOption('UserKnownHostsFile', '/dev/null')
    ->identityFile('~/.ssh/key.pem');

task('deploy:upload', function () {
    upload('src/', '{{release_path}}/');
});

// Shared files/dirs between deploys
add('shared_files', [
    '.env'
]);
add('shared_dirs', [
    'storage/app/public',
    'storage/app/temp',
    'storage/framework/sessions',
    'storage/logs',
    'public/uploads',
    'database/files',
    'database/sxgeo',
]);

// Writable dirs by web server
add('writable_dirs', [
    'storage',
    'bootstrap/cache'
]);


// Tasks

// task('build', function () {
//     run('cd {{release_path}} && build');
// });

task('deploy:update_code', function () {
    upload('src/', '{{release_path}}/');
});

// Migrate database before symlink new release.

task('deploy', [
    'deploy:info',
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:upload',
    // 'deploy:update_code',
    'deploy:shared',
    'deploy:writable',
    // 'deploy:vendors',
    // 'deploy:clear_paths',
    'deploy:symlink',
    'deploy:unlock',
    'cleanup',
    'success'
]);

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// before('deploy:symlink', 'artisan:migrate');

// Deployer config
set('keep_releases', 4);
set('allow_anonymous_stats', false);
