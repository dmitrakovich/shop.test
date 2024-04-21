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

set('release_name', getenv('GITHUB_SHA'));

set('shared_files', ['.env']);
set('shared_dirs', [
    'storage',
    'public/uploads',
    'database/sxgeo',
]);
set('writable_dirs', [
    'bootstrap/cache',
    'storage/logs',
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

// Tasks

task('deploy:release:git-sha', function () {
    within('{{deploy_path}}', function () {
        run('echo ' . get('release_name') . ' > .dep/latest_release');
    });
});

task('deploy:upload', function () {
    upload('./', '{{release_path}}');
});

task('deploy:writable', function () {
    within('{{release_path}}', function () {
        $dirs = implode(' ', get('writable_dirs'));
        run(command: "chmod -R 0755 $dirs", no_throw: true);
    });
});

desc('Caching Blade Icons');
task('artisan:icons:cache', artisan('icons:cache'));

desc('Clear OPCache');
task('artisan:opcache:clear', artisan('opcache:clear'));

desc('Pre-compile application code');
task('artisan:opcache:compile', artisan('opcache:compile --force'));

desc('Generate permissions/policies for all entities');
task('artisan:shield:generate', artisan('shield:generate --all'));

task('deploy', [
    'deploy:info',
    'deploy:setup',
    'deploy:lock',
    'deploy:release',
    'deploy:release:git-sha',
    'deploy:upload',
    'deploy:shared',
    'deploy:writable',
    'artisan:storage:link',
    'artisan:migrate',
    'artisan:shield:generate',
    'artisan:cache:clear',
    'artisan:config:cache',
    'artisan:route:cache',
    'artisan:view:cache',
    'artisan:event:cache',
    'artisan:icons:cache',
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
