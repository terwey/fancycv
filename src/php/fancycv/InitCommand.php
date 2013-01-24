<?php
namespace fancycv;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InitCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('init')
            ->setDescription('Initialize this application')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getHelperSet()->get('dialog');
        if (!file_exists(CONFIG_FILE)) {
            $this->getKeys($input, $output);
        } else {
            $answers = array('yes', 'no');
            // ask and validate the answer
            $answer = $dialog->askAndValidate($output, 'A config.yml is already present. Do you want to overwrite? (default to no): ', function ($answer) use ($answers) {
                if (!in_array($answer, array_values($answers))) {
                    throw new \InvalidArgumentException(sprintf('Answer "%s" is invalid.', $answer));
                }

                return $answer;
            }, false, 'no');
            if ($answer == 'yes') {
                $this->getKeys($input, $output);
            }
        }
    }

    protected function getKeys(InputInterface $input, OutputInterface $output) {
        $dialog = $this->getHelperSet()->get('dialog');

        $url = 'https://www.linkedin.com/secure/developer';
        $output->writeln('<info>Please visit: '. $url."\nAnd register yourself as a Developer with LinkedIn</info>");

        $newConfig['appKey'] = $dialog->ask($output, 'Enter your Application key: ');
        $newConfig['appSecret'] = $dialog->ask($output, 'Enter your Application Secret key: ');

        $fileSaved = file_put_contents(CONFIG_FILE, yaml_emit($newConfig));
        if ($fileSaved === FALSE) { 
            $output->writeln('<error>Something went wrong writing the file</error>');
        } else {
            $output->writeln('Your config has now been updated. You can now use the auth command.');
        }
    }
}

?>