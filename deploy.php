<?php

namespace Deployer;

require 'recipe/laravel.php';

// Config

// Project name
set('application', 'Barocco');

// Project repository
set('repository', 'https://github.com/dmitrakovich/shop.test.git');

add('shared_files', [
    '.env'
]);
add('shared_dirs', [
    'storage/app',
    'storage/framework/sessions',
    'storage/logs',
    'public/uploads',
    'database/files',
    'database/sxgeo',
]);
add('writable_dirs', [
    'storage',
    'bootstrap/cache'
]);

// Hosts

host('production')
    ->hostname(getenv('DEPLOY_HOST'))
    ->user(getenv('DEPLOY_USER'))
    ->port((int)getenv('DEPLOY_PORT'))
    ->set('deploy_path', getenv('DEPLOY_PATH'))
    ->addSshOption('StrictHostKeyChecking', 'no')
    ->addSshOption('UserKnownHostsFile', '/dev/null')
    ->identityFile('~/.ssh/key.pem');
    // ->set('remote_user', 'deployer')
    // ->set('deploy_path', '~/shop.test');

task('deploy:upload', function () {
    upload('src/', '{{release_path}}/');
});

// Tasks

// task('build', function () {
//     cd('{{release_path}}');
//     run('npm run build');
//     // run('cd {{release_path}} && build');
// });

// task('deploy:writable', function () {
//     within('{{release_path}}', function () {
//         $dirs = get('writable_dirs');
//         foreach ($dirs as $dir) {
//             run("chmod -R -f 775 {$dir}");
//         }
//     });
// });

// Migrate database before symlink new release.

// task('deploy', [
//     'deploy:info',
//     'deploy:prepare',
//     'deploy:lock',
//     'deploy:release',
//     'deploy:upload',
//     // 'deploy:update_code',
//     'deploy:shared',
//     'deploy:writable',
//     // 'deploy:vendors',
//     // 'deploy:clear_paths',
//     'deploy:symlink',
//     'deploy:unlock',
//     'cleanup',
//     'success'
// ]);

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// before('deploy:symlink', 'artisan:migrate');

// Deployer config
// set('keep_releases', 4); // default 10
// set('allow_anonymous_stats', false);
