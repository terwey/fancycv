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
            ->addOption(
                'format',
                '-f',
                InputOption::VALUE_REQUIRED,
                'Specifies in which format it should be generated.',
                'tex'
            )
            ->addOption(
                'output',
                '-o',
                InputOption::VALUE_REQUIRED,
                'Name of generated output.',
                'generated'
            )
            ->addOption(
                'directory',
                '-d',
                InputOption::VALUE_REQUIRED,
                'Name of output directory.',
                'output'
            )
            ->addOption(
                'generateOnly',
                '-g',
                InputOption::VALUE_NONE,
                'Generate only, disabled post-processor (e.g. pdflatex)'
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
            $format = new Format($input->getOption('format'));
            // print_r($decode['positions']['values']);
            $positionsTex = $this->positions($format, $decode['positions']['values']);
            $file  = $format->documentOpen();
            $file .= $positionsTex;
            $file .= $format->documentClose();
            $outputDir = BASE_DIR.'/'.$input->getOption('directory');
            if (!file_exists($outputDir)) {
                // attempt to create it
                if (!mkdir($outputDir)) {
                    $output->writeln('<error>The output dir does not exist and could not be created: '.$outputDir.'</error>');
                    exit(0);
                }
            }
            $fileLocation = $outputDir.'/'.$input->getOption('output').'.'.$input->getOption('format');
            if (file_put_contents($fileLocation, $file)) {
                $output->writeln('<info>File successfully generated: '.$fileLocation.'</info>');
            } else {
                $output->writeln('<error>Could not write file.</error>');
            }
            if (!$input->getOption('generateOnly') && $input->getOption('format') == 'tex') {
                $execOutput = array();
                $execReturn;
                exec('pdflatex -halt-on-error -interaction errorstopmode -output-directory '.$outputDir.' '.$fileLocation, $execOutput, $execReturn);
                if ($execReturn == 0) {
                    $lines = count($execOutput);
                    $output->writeln('<info>'.$execOutput[$lines-4].'</info>');
                } else {
                    $output->writeln('<error>Executing pdflatex failed. Output: '.implode("\n", $execOutput).'</error>');
                }
            }
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