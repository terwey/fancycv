#!/usr/bin/env php
<?php
DEFINE('CONFIG_FILE', __DIR__.'/config.yml');
require_once('vendor/autoload.php');
use fancycv\AuthCommand;
use fancycv\InitCommand;
use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new AuthCommand);
$application->add(new InitCommand);
$application->run();
?>