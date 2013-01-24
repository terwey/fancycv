<?php
namespace fancycv;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AuthCommand extends Command
{
    private $linkedin;
    private $config;
    protected function configure()
    {
        $this
            ->setName('auth')
            ->setDescription('Authorize yourself with Linkedin')
        ;
        
        $this->config = yaml_parse_file(CONFIG_FILE);
        $this->config['callbackUrl'] = NULL;

        $this->linkedin = new \LinkedIn($this->config);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tokenResponse = $this->getToken($input, $output);
        $accessResponse = $this->getAccess($input, $output, $tokenResponse);
    }

    protected function getToken(InputInterface $input, OutputInterface $output) {
        $dialog = $this->getHelperSet()->get('dialog');
        
        $tokenResponse = $this->linkedin->retrieveTokenRequest('r_fullprofile');
        if($tokenResponse['success'] === TRUE) {
            $url = \LINKEDIN::_URL_AUTH . $tokenResponse['linkedin']['oauth_token'];
            $output->writeln('<info>Please visit: '. $url.'</info>');
            $pin = $dialog->askAndValidate($output, '<question>Please type in the PIN:</question> ', function ($pin) {
                if (!is_numeric($pin)) {
                    throw new \InvalidArgumentException(sprintf('"%s" is not a valid PIN.', $pin));
                }
                return $pin;
            });
            $output->writeln('You have just entered: '.$pin);
            $returnValues = array(
                'oauth_token' => $tokenResponse['linkedin']['oauth_token'],
                'oauth_token_secret' => $tokenResponse['linkedin']['oauth_token_secret'],
                'oauth_verifier' => $pin
                );
            return $returnValues;
        } else {
            $error = "Request token failed.\n" . print_r($tokenResponse, TRUE) . "Linkedin Object: \n". print_r($linkedin, TRUE);
            $output->writeln('<error>'.$error.'</error>');
        }
    }

    protected function getAccess(InputInterface $input, OutputInterface $output, array $tokenResponse) {
        $accessResponse = $this->linkedin->retrieveTokenAccess($tokenResponse['oauth_token'], $tokenResponse['oauth_token_secret'], $tokenResponse['oauth_verifier']);
        if($accessResponse['success'] === TRUE) {
            $newConfig = $this->config;
            $newConfig['oauth_token'] = $accessResponse['linkedin']['oauth_token'];
            $newConfig['oauth_token_secret'] = $accessResponse['linkedin']['oauth_token_secret'];
            unset($newConfig['callbackUrl']);

            $fp = fopen (CONFIG_FILE, 'w');
            fwrite ($fp, yaml_emit($newConfig));
            fclose ($fp);
            $output->writeln('Your config has now been updated. You can now use the generator.');
        } else {
            $error = "Request access failed.\n" . print_r($accessResponse, TRUE) . "Linkedin Object: \n". print_r($linkedin, TRUE);
            $output->writeln('<error>'.$error.'</error>');
        }
    }

    protected function updateConfig($newConfig) {

    }
}

?>