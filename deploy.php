<?php

namespace Deployer;

require 'recipe/laravel.php';

// Config

// Number of releases to preserve in releases folder.
set('keep_releases', 3); // default 10

// Project name
set('application', 'Barocco');

// Project repository
// set('repository', 'https://github.com/dmitrakovich/shop.test.git');

set('shared_files', ['.env']);
set('shared_dirs', [
    'storage',
    'public/uploads',
    'database/sxgeo',
]);
set('writable_dirs', [
    'bootstrap/cache',
    'storage',
    'public/uploads',
]);

// Hosts

host('production')
    ->setHostname(getenv('DEPLOY_HOST'))
    ->setRemoteUser(getenv('DEPLOY_USER'))
    ->setPort((int)getenv('DEPLOY_PORT'))
    ->setDeployPath(getenv('DEPLOY_PATH'))
    ->setSshArguments([
        '-o StrictHostKeyChecking=no',
        '-o UserKnownHostsFile=/dev/null',
    ])
    ->setIdentityFile('~/.ssh/deploy');

task('deploy:upload', function () {
    upload('', '{{release_path}}', ['options' => ['--bwlimit=4096']]);
});

// Tasks

task('deploy:writable', function () {
    within('{{release_path}}', function () {
        $dirs = implode(' ', get('writable_dirs'));
        run("chmod -R 0755 $dirs");
    });
});

desc('Clear OPCache');
task('artisan:opcache:clear', artisan('opcache:clear'));

desc('Pre-compile application code');
task('artisan:opcache:compile', artisan('opcache:compile --force'));

task('deploy', [
    'deploy:info',
    'deploy:setup',
    'deploy:lock',
    'deploy:release',
    'deploy:upload',
    'deploy:shared',
    'deploy:writable',
    'artisan:storage:link',
    'artisan:migrate',
    'artisan:cache:clear',
    'artisan:config:cache',
    'artisan:route:cache',
    'artisan:view:cache',
    'artisan:event:cache',
    'deploy:symlink',
    'artisan:cache:clear',
    'artisan:opcache:clear',
    'artisan:queue:restart',
    'deploy:unlock',
    'deploy:cleanup',
    'deploy:success',
]);

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Rollback settings
task('rollback:after', [
    'artisan:cache:clear',
    'artisan:opcache:clear',
]);

after('rollback', 'rollback:after');
