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
            ->addOption(
                'positions',
                null,
                InputOption::VALUE_NONE,
                'Process your positions'
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
            $decode = json_decode(file_get_contents(JSON_FILE), TRUE);
            $format = new Format('tex');
            // print_r($decode['positions']['values']);
            $positionsTex = $this->positions($format, $decode['positions']['values']);
            print $format->documentOpen();
            print $positionsTex;
            print $format->documentClose();
        }
    }

    protected function positions(Format $format, array $positions) {
        $tex = $format->sectionTitle('Positions');
        foreach ($positions as $position) {
            $period = date('F Y', mktime(0,0,0,$position['startDate']['month'],0,$position['startDate']['year'])).' --- ';
            $period .= ($position['isCurrent']) ? 'Present' : date('F Y', mktime(0,0,0,$position['endDate']['month'],0,$position['endDate']['year']));
            $tableContents = array(
                array(
                    'Period', $period
                ),
                array(
                    'Employer', $position['company']['name']
                ),
                array(
                    'Job Title', $position['title']
                ),
                array(
                    'Summary', preg_replace("/[\\n\\r]+/", "\n\n", $position['summary'])
                )
            );
            $table = new Table($format, $tableContents);
            $tex .= $table->table();
        }
        return $tex;
    }
}

?>