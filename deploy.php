<?php
namespace Deployer;

require 'recipe/common.php';

// Project name
set('application', 'eant.romanov.online');

// Project repository
set('repository', 'git@github.com:dmromanov/telegram-eant-bot.git');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', false);

// Shared files/dirs between deploys 
set('shared_files', []);
set('shared_dirs', []);

// Writable dirs by web server 
set('writable_dirs', []);
set('allow_anonymous_stats', false);

// Hosts
host('eant.romanov.online')
    ->stage('prod')
    ->roles('all', 'prod')
    ->user(getenv('SSH_USER'))
    ->set('deploy_path', '/srv/web/eant.romanov.online');

// Custom tasks

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

task('deploy:update_code', function () {
    $options = [
        '--delete-after',
        '--exclude deploy.php',
        '--exclude composer.json',
        '--exclude composer.lock',

        '--exclude=.git',
        '--exclude=.idea',
        '--exclude=.gitattributes',
        '--exclude=.gitignore',
        '--exclude=README.md',

        '--no-owner',
        '--no-group',
    ];

    upload('.', '{{deploy_path}}', [
        'options' => $options
    ]);
});

// Workflow changes & customizations
task('deploy', [
    'deploy:info',
    'deploy:update_code',
    'cleanup',
])->desc('Deploy your project');
