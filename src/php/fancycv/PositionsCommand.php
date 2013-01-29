<?php
namespace fancycv;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class PositionsCommand extends Command
{
    private $_config;
    private $_positionObject;
    protected function configure()
    {
        $this
            ->setName('positions')
            ->setDescription('Process your positions')
            ->addOption(
               'list-types',
               null,
               InputOption::VALUE_NONE,
               'Lists existing position types',
               null
            )
            ->addOption(
               'list-positions',
               null,
               InputOption::VALUE_NONE,
               'Lists existing positions',
               null
            )
            ->addOption(
               'new-type',
               null,
               InputOption::VALUE_NONE,
               'Creates a new position type',
               null
            )
            ->addOption(
               'process-linkedin',
               null,
               InputOption::VALUE_NONE,
               'Process LinkedIn Positions'
            )
            ->addOption(
               'new-position',
               null,
               InputOption::VALUE_NONE,
               'Adds a new Position',
               null
            )
        ;
        $this->_positionObject = new Positions();
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
              $this->processLinkedinPositions($input, $output);
            }

            if ($input->getOption('new-type')) {
              if ($input->getOption('new-type') === TRUE) {
                $this->createNewType($input, $output);
              } else {
                $this->createNewType($input, $output, $input->getOption('new-type'));
              }
            }

            if ($input->getOption('list-types')) {
              $this->listTypes($input, $output);
            }
            if ($input->getOption('list-positions')) {
              if ($input->getOption('list-positions') === TRUE) {
                $this->listPositions($input, $output);
              } else {
                $this->listPositions($input, $output, $input->getOption('list-positions'));
              }
            }

            if ($input->getOption('new-position')) {
              if ($input->getOption('new-position') === TRUE) {
                $positionName = $this->getString('Please enter a Position name', $output);
              } else {
                $positionName = $input->getOption('new-position');
              }
              $output->writeln("<info>Type <comment>LIST</comment> for a list of available Types.\nType <comment>NEW</comment> to add a new Type.</info>\n");
              $question = sprintf("<question>Position:<comment> %s </comment>found, enter Type number</question>: ", $positionName);
              $this->newPosition($input, $output, $question, array('employer' => $positionName));
            }   
        }
    }

    function processLinkedinPositions(InputInterface $input, OutputInterface $output) {
      $processed = (isset($this->_config['linkedinPositionsProcessed'])) ? $this->_config['linkedinPositionsProcessed'] : FALSE ;
      if ($processed == TRUE) {
        $overwrite = $this->getHelperSet()->get('dialog')->askConfirmation(
        $output,
        '<question>Your LinkedIn Positions have been processed before, do you want to process them again?</question> [no]: ',
        false
        );
      } else {
        $overwrite = 'yes';
      }

      if ($overwrite == 'yes' || $overwrite == 'y') {
        $output->writeln("<info>Type <comment>LIST</comment> for a list of available Types.\nType <comment>NEW</comment> to add a new Type.</info>\n");
        $decode = json_decode(file_get_contents(JSON_FILE), TRUE);

        // var_dump($decode['positions']['values']);
        foreach ($decode['positions']['values'] as $position) {
          $positionArray = array();
          foreach ($position as $key => $value) {
            if ($key == 'company') {
              $positionArray['employer'] = $value['name'];
            }
            if ($key == 'startDate') {
              $positionArray['periodFrom'] = mktime(0,0,0,$value['month'],0,$value['year']);
            }
            if ($key == 'endDate') {
              $positionArray['periodTo'] = mktime(0,0,0,$value['month'],0,$value['year']);
            }
            if ($key == 'isCurrent') {
              $positionArray['periodTo'] = 'PRESENT';
            }
            if ($key == 'summary') {
              $positionArray['summary'] = $value;
            }
            if ($key == 'title') {
              $positionArray['title'] = $value;
            }
          }
          $question = sprintf("<question>Position:<comment> %s </comment>found, enter Type number</question>: ", $positionArray['employer']);
          $this->newPosition($input, $output, $question, $positionArray);
        }
        $this->_config['linkedinPositionsProcessed'] = TRUE;
        $fileSaved = file_put_contents(CONFIG_FILE, Yaml::dump($this->_config));
        if ($fileSaved === FALSE) {
            $output->writeln('<error>Something went wrong writing the file</error>');
        } else {
            $output->writeln('Your LinkedIn Positions have been processed.');
        }
      }
    }

    function createNewType(InputInterface $input, OutputInterface $output, $typeName=NULL) {
      if ($typeName == NULL) {
        $typeName = $this->getHelperSet()->get('dialog')->askAndValidate(
          $output,
          '<question>Please enter a Type name:</question> ',
          function ($typeName) {
            if (!empty($typeName)) {
              return $typeName;
            } else {
              throw new \InvalidArgumentException(sprintf('"%s" is not a valid option.', $typeName));
            }
          },
          false
        );
      }
      $typeDesc = $this->getHelperSet()->get('dialog')->ask(
        $output,
        '<question>Please enter a Type description:</question> ',
        NULL
      );
      if ($this->_positionObject->newType($typeName, $typeDesc)) {
        $output->writeln(sprintf('<info>New type created with name: "%s" and description: "%s"</info>', $typeName, $typeDesc));
      }
      return $typeName;
    }

    function listTypes(InputInterface $input, OutputInterface $output) {
      $count = 0;
      $typesList = '';
      foreach ($this->_positionObject->listTypes() as $key) {
        $typesList .= '['.$count.'] <comment>'.$key."</comment>\n";
        $count++;
      }
      $output->writeln(sprintf("<info>Available types: \n%s</info>", $typesList));
    }

    function listPositions(InputInterface $input, OutputInterface $output, $typeName=NULL) {
      if ($typeName != NULL) {
        $num = array_search($typeName, $this->_positionObject->listTypes());
        $positionList = "\n";
        foreach ($this->_positionObject->listPositionsInType($typeName) as $key => $position) {
          $positionList .= " - [".$key.'] <comment>'.$position."</comment>\n";
        }
        $output->writeln(sprintf("<info>Available Positions in <comment>%s</comment>: %s</info>", $typeName, $positionList));
      } else {
        $count = 0;
        $positionList = '';
        foreach ($this->_positionObject->listTypes() as $key) {
          $positionList .= '['.$count.'] '.$key.": \n";
          foreach ($this->_positionObject->listPositionsInType($key) as $key => $position) {
            $positionArray = $this->_positionObject->getPosition($position);
            $positionList .= " - [".$key.'] <comment>'.$position."</comment>\n";
            $positionList .= '       - <comment>Title: '.$positionArray['title']."</comment>\n";
            $positionList .= '       - <comment>From: '.date('F Y',$positionArray['periodFrom'])."</comment>\n";
            if (is_numeric($positionArray['periodTo'])) {
              $periodTo = date('F Y',$positionArray['periodTo']);
            } else {
              $periodTo = $positionArray['periodTo'];
            }
            $positionList .= '       - <comment>Till: '.$periodTo."</comment>\n";
          }
          $positionList .= "\n";
          $count++;
        }
        $output->writeln(sprintf("<info>Available positions: \n%s</info>", $positionList));
      }
    }

    function addPositionToType($typeName, array $positionArray, InputInterface $input, OutputInterface $output) {
      if ($this->_positionObject->addPositionToTypeWithArray($typeName, $positionArray)) {
        $output->writeln(sprintf("Position: <comment>%s</comment> added to Type: <comment>%s</comment>.\n", $positionArray['employer'], $typeName));
      } else {
        $output->writeln(sprintf("<error>Failed to add Position: <comment>%s</comment> to Type: <comment>%s</comment>.</error>\n\n", $position['employer'], $typeName));
      }
    }

    function positionFound($question, InputInterface $input, OutputInterface $output) {
      $targetTypeNumber = $this->getHelperSet()->get('dialog')->askAndValidate(
        $output,
        $question,
        function ($targetTypeNumber) {
          if (is_numeric($targetTypeNumber)) {
            return $targetTypeNumber;
          } else if ($targetTypeNumber == 'LIST' || $targetTypeNumber == 'NEW') {
            return $targetTypeNumber;
          } else {
            throw new \InvalidArgumentException(sprintf('"%s" is not a valid option.', $targetTypeNumber));
          }
        },
        false
      );
      return $targetTypeNumber;
    }

    function newPosition(InputInterface $input, OutputInterface $output, $question, array $position) {
      // ask the question where it goes
      $targetTypeNumber = $this->positionFound($question, $input, $output);

      if ($targetTypeNumber == 'LIST') {
        $this->listTypes($input, $output);
        // ask the question again
        $targetTypeNumber = $this->positionFound($question, $input, $output);
      }
      if ($targetTypeNumber == 'NEW') {
        $targetTypeName = $this->createNewType($input, $output);
        $targetTypeNumber = array_search($targetTypeName, $this->_positionObject->listTypes());
      }

      // position stuff

      if (!isset($position['periodFrom']) || $position['periodFrom'] == NULL) {
        $month = $this->getNumber('Please enter starting month', $output);
        $year = $this->getNumber('Please enter starting year', $output);
        $position['periodFrom'] = mktime(0,0,0,$month,0,$year);
      }
      if (!isset($position['periodTo']) || $position['periodTo'] == NULL) {
        $month = $this->getHelperSet()->get('dialog')->askAndValidate(
          $output,
          '<question>Please enter ending month (numeric) or PRESENT</question>: ',
          function ($month) {
            if (is_numeric($month)) {
              return $month;
            } else if ($month == 'PRESENT') {
              return $month;
            } else {
              throw new \InvalidArgumentException(sprintf('"%s" is not a valid option.', $month));
            }
          },
          false
        );
        if ($month != 'PRESENT') {
          $year = $this->getNumber('Please enter starting year', $output);
          $position['periodTo'] = mktime(0,0,0,$month,0,$year);
        } else {
          $position['periodTo'] = 'PRESENT';
        }
      }
      if (!isset($position['employer']) || $position['employer'] == NULL) {
        $position['employer'] = $this->getString('Please enter the position\'s Employer', $output);
      }
      if (!isset($position['title']) || $position['title'] == NULL) {
        $position['title'] = $this->getString('Please enter the position Title', $output);
      }
      if (!isset($position['summary']) || $position['summary'] == NULL) {
        $position['summary'] = $this->getString('Please enter a Summary', $output);
      }

      // end of position stuff

      if (is_numeric($targetTypeNumber)) {
        $targetTypeName = $this->_positionObject->listTypes()[$targetTypeNumber];
        $this->addPositionToType($targetTypeName, $position, $input, $output);
      }
    }

    function getNumber($question, OutputInterface $output) {
      $var = $this->getHelperSet()->get('dialog')->askAndValidate(
        $output,
        '<question>'.$question.' (numeric)</question>: ',
        function ($var) {
          if (is_numeric($var)) {
            return $var;
          } else {
            throw new \InvalidArgumentException(sprintf('"%s" is not a valid number.', $var));
          }
        },
        false
      );
      return $var;
    }

    function getString($question, OutputInterface $output) {
      $var = $this->getHelperSet()->get('dialog')->askAndValidate(
        $output,
        '<question>'.$question.'</question>: ',
        function ($var) {
          if (!empty($var)) {
            return $var;
          } else {
            throw new \InvalidArgumentException(sprintf('"%s" is not a valid string.', $var));
          }
        },
        false
      );
      return $var;
    }
}

?>