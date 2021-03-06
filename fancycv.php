#!/usr/bin/env php
<?php
DEFINE('CONFIG_FILE', __DIR__.'/config.yml');
DEFINE('JSON_FILE', __DIR__.'/linkedin.json');
DEFINE('FORMATTERS', __DIR__.'/src/yml/fancycv/formatters/');
DEFINE('BASE_DIR', __DIR__);
DEFINE('DATA_DIR', __DIR__.'/data/');
DEFINE('LOG_DIR', __DIR__.'/logs/');
require_once('vendor/autoload.php');
use fancycv\InitCommand;
use fancycv\AuthCommand;
use fancycv\FetchCommand;
use fancycv\GenerateCommand;
use fancycv\SkillsCommand;
use fancycv\PositionsCommand;
use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new InitCommand);
$application->add(new AuthCommand);
$application->add(new FetchCommand);
$application->add(new SkillsCommand);
$application->add(new PositionsCommand);
$application->add(new GenerateCommand);
$application->run();
?>
