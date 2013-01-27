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
    private $_categoryObject;
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
               InputOption::VALUE_REQUIRED,
               'Creates a new skills category'
            )
        ;
        $this->_categoryObject = new Categories();
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
            // foreach ($decode['skills']['values'] as $key => $value) {
                // $this->_categoryObject->addSkillToCategory($value['skill']['name'], 'Unsorted');
            // }

            if ($input->getOption('new-category')) {
              $categoryDesc = $dialog->ask(
                $output,
                '<question>Please enter a Category description:</question> ',
                NULL
              );
              if ($this->_categoryObject->newCategory($input->getOption('new-category'), $categoryDesc)) {
                $output->writeln(sprintf('<info>New category created with name: "%s" and description: "%s"</info>', $input->getOption('new-category'), $categoryDesc));
              }
            }

            // $category = $dialog->ask(
            //     $output,
            //     '<question>Please enter the name of a new category:</question> ',
            //     null
            // );

            // $output->writeln('New category name: '.$category);
            // $this->newCategory($category, $input, $output);
            // $this->createDirectory('profiles', DATA_DIR.'profiles', $input, $output);            
        }
    }
}

?>