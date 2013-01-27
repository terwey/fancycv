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
            ->addOption(
               'process-linkedin-skills',
               'pLs',
               InputOption::VALUE_NONE,
               'Process LinkedIn Skills'
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

            if ($input->getOption('process-linkedin-skills')) {
              $count = 0;
              $output->writeln("<info>Type LIST for a list of available Categories.\nType NEW to add a new Category.</info>\n");
              foreach ($decode['skills']['values'] as $key => $value) {
                $question = sprintf("<question>Skill:</question><comment> %s </comment><question>found, to which Category number do you want to add this?</question>: ", $value['skill']['name']);
                // ask the question where it goes
                $targetCategoryNumber = $this->skillFound($question, $input, $output);

                if ($targetCategoryNumber == 'LIST') {
                  $this->listCategories($input, $output);
                  // ask the question again
                  $targetCategoryNumber = $this->skillFound($question, $input, $output);
                }
                if ($targetCategoryNumber == 'NEW') {
                  $targetCategoryName = $this->getHelperSet()->get('dialog')->ask(
                    $output,
                    '<question>New category name</question>: ',
                    false
                  );
                  $this->createNewCategory($targetCategoryName, $input, $output);
                  $targetCategoryNumber = array_search($targetCategoryName, $this->_categoryObject->listCategories());
                }
                if (is_numeric($targetCategoryNumber)) {
                  $targetCategoryName = $this->_categoryObject->listCategories()[$targetCategoryNumber];
                  $this->addSkillToCategory(array('name' => $value['skill']['name'], 'desc' =>NULL), $targetCategoryName, $input, $output);
                }
              }
            }

            if ($input->getOption('new-category')) {
              $this->createNewCategory($input->getOption('new-category'), $input, $output);
            }

            if ($input->getOption('list-categories')) {
              $this->listCategories($input, $output);
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

    function createNewCategory($categoryName, InputInterface $input, OutputInterface $output) {
      $categoryDesc = $this->getHelperSet()->get('dialog')->ask(
        $output,
        '<question>Please enter a Category description:</question> ',
        NULL
      );
      if ($this->_categoryObject->newCategory($categoryName, $categoryDesc)) {
        $output->writeln(sprintf('<info>New category created with name: "%s" and description: "%s"</info>', $categoryName, $categoryDesc));
      }
    }

    function listCategories(InputInterface $input, OutputInterface $output) {
      $count = 0;
      $categoriesList = '';
      foreach ($this->_categoryObject->listCategories() as $key) {
        $categoriesList .= $count.': '.$key."\n";
        $count++;
      }
      $output->writeln(sprintf("<info>Available categories: \n%s</info>", $categoriesList));
    }

    function addSkillToCategory(array $skill, $categoryName, InputInterface $input, OutputInterface $output) {
      if ($this->_categoryObject->addSkillToCategory($skill['name'], $categoryName, $skill['desc'])) {
        $output->writeln(sprintf("Skill: <comment>%s</comment> added to Category: <comment>%s</comment>.\n", $skill['name'], $categoryName));
      } else {
        $output->writeln(sprintf("<error>Failed to add Skill: <comment>%s</comment> to Category: <comment>%s</comment>.</error>\n\n", $skill['name'], $categoryName));
      }
    }

    function skillFound($question, InputInterface $input, OutputInterface $output) {
      $targetCategoryNumber = $this->getHelperSet()->get('dialog')->askAndValidate(
        $output,
        $question,
        function ($targetCategoryNumber) {
          if (is_numeric($targetCategoryNumber)) {
            return $targetCategoryNumber;
          } else if ($targetCategoryNumber == 'LIST' || $targetCategoryNumber == 'NEW') {
            return $targetCategoryNumber;
          } else {
            throw new \InvalidArgumentException(sprintf('"%s" is not a valid option.', $targetCategoryNumber));
          }
        }
      );
      return $targetCategoryNumber;
    }
}

?>