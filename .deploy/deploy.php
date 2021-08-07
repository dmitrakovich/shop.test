<?php

namespace Deployer;

require 'recipe/laravel.php';

// Project name
set('application', 'Barocco');

// Project repository
set('repository', 'https://github.com/default-089/shop.test.git');

// Hosts
host('barocco.by')
    ->user('user2099049')
    ->identityFile('~/.ssh/key.pem')
    ->set('deploy_path', '~/public_html')
    ->addSshOption('StrictHostKeyChecking', 'no')
    ->addSshOption('UserKnownHostsFile', '/dev/null');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true); 

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
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
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
