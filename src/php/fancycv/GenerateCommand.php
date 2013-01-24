<?php
namespace fancycv;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateCommand extends Command
{
    private $linkedin;
    private $config;
    protected function configure()
    {
        $this
            ->setName('generate')
            ->setDescription('Generates your CV')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!file_exists(CONFIG_FILE)) {
            $output->writeln('The config file is not yet present. Executing the auth and then init first.');
            $command = $this->getApplication()->find('auth');
            $returnCode = $command->run($input, $output);
        } else {
            $this->config = yaml_parse_file(CONFIG_FILE);
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
        print_r($profile['linkedin']);

        // $dialog = $this->getHelperSet()->get('dialog');
        // if (!file_exists(CONFIG_FILE)) {
        //     $this->getKeys($input, $output);
        // } else {
        //     $answers = array('yes', 'no');
        //     // ask and validate the answer
        //     $answer = $dialog->askAndValidate($output, 'A config.yml is already present. Do you want to overwrite? (default to no): ', function ($answer) use ($answers) {
        //         if (!in_array($answer, array_values($answers))) {
        //             throw new \InvalidArgumentException(sprintf('Answer "%s" is invalid.', $answer));
        //         }

        //         return $answer;
        //     }, false, 'no');
        //     if ($answer == 'yes') {
        //         $this->getKeys($input, $output);
        //     }
        // }
    }
}

?>