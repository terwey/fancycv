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
    private $_config;
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
               'Lists existing skills categories',
               null
            )
            ->addOption(
               'list-skills',
               null,
               InputOption::VALUE_NONE,
               'Lists existing skills',
               null
            )
            ->addOption(
               'new-category',
               null,
               InputOption::VALUE_NONE,
               'Creates a new skills category',
               null
            )
            ->addOption(
               'process-linkedin',
               null,
               InputOption::VALUE_NONE,
               'Process LinkedIn Skills'
            )
            ->addOption(
               'new-skill',
               null,
               InputOption::VALUE_NONE,
               'Adds a new Skill',
               null
            )
        ;
        $this->_categoryObject = new Categories();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!file_exists(CONFIG_FILE)) {
            $output->writeln('The config file is not yet present. Executing the init first.');
            $command = $this->getApplication()->find('init');
            $returnCode = $command->run($input, $output);
        } else {
            $this->_config = Yaml::parse(CONFIG_FILE);
        }
        if (!file_exists(JSON_FILE)) {
            $output->writeln('The LinkedIn data is not downloaded. Executing the fetch command first.');
            $command = $this->getApplication()->find('fetch');
            $returnCode = $command->run($input, $output);
        } else {
            $dialog = $this->getHelperSet()->get('dialog');

            if ($input->getOption('process-linkedin')) {
              $this->processLinkedinSkills($input, $output);
            }

            if ($input->getOption('new-category')) {
              if ($input->getOption('new-category') === TRUE) {
                $this->createNewCategory($input, $output);
              } else {
                $this->createNewCategory($input, $output, $input->getOption('new-category'));
              }
            }

            if ($input->getOption('list-categories')) {
              $this->listCategories($input, $output);
            }
            if ($input->getOption('list-skills')) {
              if ($input->getOption('list-skills') === TRUE) {
                $this->listSkills($input, $output);
              } else {
                $this->listSkills($input, $output, $input->getOption('list-skills'));
              }
            }

            if ($input->getOption('new-skill')) {
              if ($input->getOption('new-skill') === TRUE) {
                $skillName = $this->getHelperSet()->get('dialog')->askAndValidate(
                  $output,
                  '<question>Please enter a Skill name</question>: ',
                  function ($skillName) {
                    if (!empty($skillName)) {
                      return $skillName;
                    } else {
                      throw new \InvalidArgumentException(sprintf('"%s" is not a valid option.', $skillName));
                    }
                  },
                  false
                );
              } else {
                $skillName = $input->getOption('new-skill');
              }
              $output->writeln("<info>Type <comment>LIST</comment> for a list of available Categories.\nType <comment>NEW</comment> to add a new Category.</info>\n");
              $question = sprintf("<question>Skill:<comment> %s </comment>found, enter Category number</question>: ", $skillName);
              $this->newSkill($input, $output, $question, $skillName);
            }   
        }
    }

    function processLinkedinSkills(InputInterface $input, OutputInterface $output) {
      if (isset($this->_config['linkedinSkillsProcessed']) && $this->_config['linkedinSkillsProcessed'] == TRUE) {
        $overwrite = $this->getHelperSet()->get('dialog')->askConfirmation(
        $output,
        '<question>Your LinkedIn Skills have been processed before, do you want to process them again?</question> [no]: ',
        false
        );

        if ($overwrite == 'yes' || $overwrite == 'y') {
          $output->writeln("<info>Type <comment>LIST</comment> for a list of available Categories.\nType <comment>NEW</comment> to add a new Category.</info>\n");
          $decode = json_decode(file_get_contents(JSON_FILE), TRUE);
          foreach ($decode['skills']['values'] as $key => $value) {
            $question = sprintf("<question>Skill:<comment> %s </comment>found, enter Category number</question>: ", $value['skill']['name']);
            $this->newSkill($input, $output, $question, $value['skill']['name']);
          }
          $this->_config['linkedinSkillsProcessed'] = TRUE;
          $fileSaved = file_put_contents(CONFIG_FILE, Yaml::dump($this->_config));
          if ($fileSaved === FALSE) {
              $output->writeln('<error>Something went wrong writing the file</error>');
          } else {
              $output->writeln('Your LinkedIn Skills have been processed.');
          }
        }
      }
    }

    function createNewCategory(InputInterface $input, OutputInterface $output, $categoryName=NULL) {
      if ($categoryName == NULL) {
        $categoryName = $this->getHelperSet()->get('dialog')->askAndValidate(
          $output,
          '<question>Please enter a Category name:</question> ',
          function ($categoryName) {
            if (!empty($categoryName)) {
              return $categoryName;
            } else {
              throw new \InvalidArgumentException(sprintf('"%s" is not a valid option.', $categoryName));
            }
          },
          false
        );
      }
      $categoryDesc = $this->getHelperSet()->get('dialog')->ask(
        $output,
        '<question>Please enter a Category description:</question> ',
        NULL
      );
      if ($this->_categoryObject->newCategory($categoryName, $categoryDesc)) {
        $output->writeln(sprintf('<info>New category created with name: "%s" and description: "%s"</info>', $categoryName, $categoryDesc));
      }
      return $categoryName;
    }

    function listCategories(InputInterface $input, OutputInterface $output) {
      $count = 0;
      $categoriesList = '';
      foreach ($this->_categoryObject->listCategories() as $key) {
        $categoriesList .= '['.$count.'] <comment>'.$key."</comment>\n";
        $count++;
      }
      $output->writeln(sprintf("<info>Available categories: \n%s</info>", $categoriesList));
    }

    function listSkills(InputInterface $input, OutputInterface $output, $categoryName=NULL) {
      if ($categoryName != NULL) {
        $num = array_search($categoryName, $this->_categoryObject->listCategories());
        $skillList = "\n";
        foreach ($this->_categoryObject->listSkillsInCategory($categoryName) as $key => $skill) {
          $skillList .= " - [".$key.'] <comment>'.$skill."</comment>\n";
        }
        $output->writeln(sprintf("<info>Available Skills in <comment>%s</comment>: %s</info>", $categoryName, $skillList));
      } else {
        $count = 0;
        $skillList = '';
        foreach ($this->_categoryObject->listCategories() as $key) {
          $skillList .= '['.$count.'] '.$key.": \n";
          foreach ($this->_categoryObject->listSkillsInCategory($key) as $key => $skill) {
            $skillList .= " - [".$key.'] <comment>'.$skill."</comment>\n";
          }
          $skillList .= "\n";
          $count++;
        }
        $output->writeln(sprintf("<info>Available skills: \n%s</info>", $skillList));
      }
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
        },
        false
      );
      return $targetCategoryNumber;
    }

    function newSkill(InputInterface $input, OutputInterface $output, $question, $skillName=NULL, $skillDesc=NULL) {
      // ask the question where it goes
      $targetCategoryNumber = $this->skillFound($question, $input, $output);

      if ($targetCategoryNumber == 'LIST') {
        $this->listCategories($input, $output);
        // ask the question again
        $targetCategoryNumber = $this->skillFound($question, $input, $output);
      }
      if ($targetCategoryNumber == 'NEW') {
        $targetCategoryName = $this->createNewCategory($input, $output);
        $targetCategoryNumber = array_search($targetCategoryName, $this->_categoryObject->listCategories());
      }
      if ($skillDesc == NULL) {
        $skillDesc = $this->getHelperSet()->get('dialog')->ask(
          $output,
          '<question>Please enter a Skill description:</question> ',
          NULL
        );
      }
      if (is_numeric($targetCategoryNumber)) {
        $targetCategoryName = $this->_categoryObject->listCategories()[$targetCategoryNumber];
        $this->addSkillToCategory(array('name' => $skillName, 'desc' => $skillDesc), $targetCategoryName, $input, $output);
      }
    }
}

?>