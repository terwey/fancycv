<?php
namespace fancycv;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class FetchCommand extends Command
{
    private $linkedin;
    private $config;
    protected function configure()
    {
        $this
            ->setName('fetch')
            ->setDescription('Fetches your data from LinkedIn')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!file_exists(CONFIG_FILE)) {
            $output->writeln('The config file is not yet present. Executing the auth and then init first.');
            $command = $this->getApplication()->find('auth');
            $returnCode = $command->run($input, $output);
        } else {
            $this->config = Yaml::parse(CONFIG_FILE);
            $this->config['callbackUrl'] = NULL;

            $this->linkedin = new \LinkedIn($this->config);
            $this->linkedin->setTokenAccess($this->config);
        }

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

        $profile = $this->linkedin->profile('~:('.implode(',', $infoWeWant).')?format=json');
        if (file_put_contents(JSON_FILE, $profile['linkedin'])) {
            $output->writeln('Data has been fetched and saved locally.');
        }
    }
}

?>
