<?php
require_once('vendor/linkedin_3.2.0.class.php');

$config = yaml_parse_file('config.yml');
$config['callbackUrl'] = NULL;

$linkedin = new LinkedIn($config);
$tokenResponse = $linkedin->retrieveTokenRequest('r_fullprofile');
if($tokenResponse['success'] === TRUE) {
	print "Please visit: ". LINKEDIN::_URL_AUTH . $tokenResponse['linkedin']['oauth_token'] ."\n";

	$valid_pin = false;
	while(!$valid_pin) {
		print "What is the PIN? ";
		$oauth_verifier = trim(fgets(fopen("php://stdin", "r")));
		$valid_pin = is_numeric($oauth_verifier);
	}

	$accessResponse = $linkedin->retrieveTokenAccess($tokenResponse['linkedin']['oauth_token'], $tokenResponse['linkedin']['oauth_token_secret'], $oauth_verifier);
	if($accessResponse['success'] === TRUE) {
		$newConfig = $config;
		$newConfig['oauth_token'] = $accessResponse['linkedin']['oauth_token'];
		$newConfig['oauth_token_secret'] = $accessResponse['linkedin']['oauth_token_secret'];
		unset($newConfig['callbackUrl']);

		$fp = fopen ('config.yml', 'w');
		fwrite ($fp, yaml_emit($newConfig));
		fclose ($fp);
		print "Your config has now been updated. You can now use the generator.\n";
		// print "Please replace the file config.yml with the following contents: \n";
		// print yaml_emit($newConfig);
		// print "\n";
	} else {
		print "Request access failed.\n" . print_r($accessResponse, TRUE) . "Linkedin Object: \n". print_r($linkedin, TRUE);
	}
} else {
	print "Request token failed.\n" . print_r($tokenResponse, TRUE) . "Linkedin Object: \n". print_r($linkedin, TRUE);

}

?>