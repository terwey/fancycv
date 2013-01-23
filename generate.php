<?php
require_once('vendor/linkedin_3.2.0.class.php');

$config = yaml_parse_file('config.yml');
$config['callbackUrl'] = NULL;

$linkedin = new LinkedIn($config);
$linkedin->setTokenAccess($config);

$infoWeWant = array(
	// basic profile
	'first-name',
	'last-name',
	'maiden-name',
	'formatted-name',

	'location:(country:(code))',
	'industry',


	'headline',
	'summary',
	'specialties',
	'positions',

	'skills'
);


$profile = $linkedin->profile('~:('.implode(',', $infoWeWant).')?format=json');
print_r($profile['linkedin']);

?>