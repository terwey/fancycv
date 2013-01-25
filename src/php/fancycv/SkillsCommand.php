<?php
namespace fancycv;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class SkillsCommand extends Command
{
    private $config;
    protected function configure()
    {
        $this
            ->setName('skills')
            ->setDescription('Process your skills')
            ->addOption(
               'new-profile',
               null,
               InputOption::VALUE_OPTIONAL,
               'Creates a new skills profile'
            )
            ->addOption(
               'list-categories',
               null,
               InputOption::VALUE_NONE,
               'Lists existing skills categories'
            )
            ->addOption(
               'new-category',
               null,
               InputOption::VALUE_OPTIONAL,
               'Creates a new skills category'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!file_exists(JSON_FILE)) {
            $output->writeln('The LinkedIn data is not downloaded. Executing the fetch command first.');
            $command = $this->getApplication()->find('fetch');
            $returnCode = $command->run($input, $output);
        } else {
            $dialog = $this->getHelperSet()->get('dialog');
            $decode = json_decode(file_get_contents(JSON_FILE), TRUE);
            foreach ($decode['skills']['values'] as $key => $value) {
                // print $value['skill']['name']."\n";
            }

            $category = $dialog->ask(
                $output,
                '<question>Please enter the name of a new category:</question> ',
                null
            );

            $output->writeln('New category name: '.$category);
            $this->newCategory($category, $input, $output);
            // $this->createDirectory('profiles', DATA_DIR.'profiles', $input, $output);            
        }
    }

    protected function newCategory($categoryName, InputInterface $input, OutputInterface $output) {
        $skillsFile = DATA_DIR.'skills.yml';
        if (file_exists($skillsFile)) {
            $skills = Yaml::parse($skillsFile);
        } else {
            $skills = array();
        }

        $skills[] = $categoryName;

        $this->createFile(Yaml::dump($skills), 'skills.yml', $skillsFile, $input, $output);
    }

    protected function createDirectory($directoryName, $directoryPath, InputInterface $input, OutputInterface $output) {
        if (!file_exists($directoryPath)) {
            // attempt to create it
            if (!mkdir($directoryPath)) {
                $output->writeln('<error>The '.$directoryName.' dir does not exist and could not be created: '.$directoryPath.'</error>');
                exit(0);
            } else {
                return true;
            }
        }
    }

    protected function createFile($fileContents, $fileName, $filePath, InputInterface $input, OutputInterface $output) {
        if (file_put_contents($filePath, $fileContents)) {
            $output->writeln('<info>File '.$fileName.' successfully written: '.$filePath.'</info>');
            return true;
        } else {
            $output->writeln('<error>Could not write file: '.$filePath.'</error>');
            return false;
        }
    }
}

?>