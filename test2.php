#!/usr/bin/env php
<?php

function render()
{
    $variables = parse_ini_file(__DIR__ . '/scripts/search_sphinx_services/variables/prod.ini');
    if (!$variables) {
        echo 'Unable to parse ini', PHP_EOL;
        exit;
    }

    $configuration_files = [
        '/opt/www/search_sphinx_services/.env.template',
    ];

    foreach ($configuration_files as $filename) {
        if (!file_exists($filename)) {
            continue;
        }

        echo 'Rendering ' . $filename . ' ...', PHP_EOL;

        $ctx = file_get_contents($filename);

        foreach ($variables as $variable => $value) {
            $ctx = str_replace('@@' . $variable . '@@', $value, $ctx);
        }

        file_put_contents($filename, $ctx);
    }

    //cp .env.template to .env
    $cmd = 'sudo mv /opt/www/search_sphinx_services/.env.template /opt/www/search_sphinx_services/.env';
    system($cmd);
}

function deploy_local()
{
    $folders = [
        '/opt/www/search_sphinx_services' => '/opt/app/nginx/html/search2',
    ];

    $cmd = 'sudo -u apache rsync -avz --delete-excluded';

    foreach ($folders as $src => $dest) {
        system($cmd . ' ' . $src . ' ' . $dest);
    }
}

function deploy_remote()
{
    $servers = include 'remote.php';
    $folders = [
        '/opt/www/search_sphinx_services' => '/opt/app/nginx/html',
    ];

    $cmd = 'rsync -avz --delete-excluded -e "ssh -p 1022 -l apache"';
    foreach ($folders as $src => $dest) {
        foreach ($servers as $server) {
            echo $server, PHP_EOL;
            system($cmd . ' ' . $src . ' ' . $server . ':' . $dest);
        }
    }
}

render();
deploy_local();
//deploy_remote();

//system('/opt/bin/remote/all_machines.sh /etc/init.d/php-fpm reload');