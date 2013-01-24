#!/usr/bin/env php
<?php
DEFINE('CONFIG_FILE', __DIR__.'/config.yml');
DEFINE('JSON_FILE', __DIR__.'/linkedin.json');
require_once('vendor/autoload.php');
use fancycv\InitCommand;
use fancycv\AuthCommand;
use fancycv\FetchCommand;
use fancycv\GenerateCommand;
use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new InitCommand);
$application->add(new AuthCommand);
$application->add(new FetchCommand);
$application->add(new GenerateCommand);
$application->run();
?>
