<?php

namespace Deployer;

require 'recipe/laravel.php';

// Config

// Number of releases to preserve in releases folder.
set('keep_releases', 4); // default 10

// Project name
set('application', 'Barocco');

// Project repository
// set('repository', 'https://github.com/dmitrakovich/shop.test.git');

set('shared_files', ['.env']);
add('shared_dirs', [
    'storage',
    'public/uploads',
    'database/files',
    'database/sxgeo',
]);
add('writable_dirs', [
    'bootstrap/cache',
    'storage',
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
    ->setIdentityFile('~/.ssh/key.pem');

task('deploy:upload', function () {
    upload('', '{{release_path}}', ['options' => ['--bwlimit=4096']]);
});

// Tasks

// task('build', function () {
//     cd('{{release_path}}');
//     run('npm run build');
//     // run('cd {{release_path}} && build');
// });

task('deploy:writable', function () {
    within('{{release_path}}', function () {
        $dirs = get('writable_dirs');
        foreach ($dirs as $dir) {
            run("chmod -R -f 775 {$dir}");
        }
    });
});

task('deploy', [
    'deploy:info',
    'deploy:setup',
    'deploy:lock',
    'deploy:release',
    'deploy:upload',
    'deploy:shared',
    'deploy:writable',
    'deploy:symlink',
    'artisan:storage:link',
    'artisan:config:cache',
    'artisan:route:cache',
    'artisan:view:cache',
    'artisan:event:cache',
    'deploy:unlock',
    'deploy:cleanup',
    'deploy:success',
]);

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// before('deploy:symlink', 'artisan:migrate');

// Deployer config
// set('keep_releases', 4); // default 10
// set('allow_anonymous_stats', false);
