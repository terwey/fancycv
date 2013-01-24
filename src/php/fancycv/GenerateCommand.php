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
            // print_r($decode['positions']['values']);
            $positionsTex = $this->positions($decode['positions']['values']);
            print '\documentclass{article}
\usepackage{tabularx,colortbl}
\newcommand{\gray}{\rowcolor[gray]{.90}}
\begin{document}
\begin{center}'."\n";
            print $positionsTex;
            print '\end{center}
\end{document}';
        }
    }

    protected function positions(array $positions) {
        $tex = '\section{Positions}'."\n";
        foreach ($positions as $position) {
            $tex .= '\begin{tabularx}{0.97\linewidth}{>{\raggedleft\scshape}p{2cm}X}'."\n";
            // period
            $tex .= '\gray Period & '.date('F Y', mktime(0,0,0,$position['startDate']['month'],0,$position['startDate']['year'])).' --- ';
            if ($position['isCurrent'] == 1) {
                $tex .= 'Present\\\\'."\n";
            } else {
                $tex .= date('F Y', mktime(0,0,0,$position['endDate']['month'],0,$position['endDate']['year'])).'\\\\'."\n";
            }
            // employer
            $tex .= '\gray Employer & '. $position['company']['name'] .'\\\\'."\n";
            // job title
            $tex .= '\gray Job Title & '. $position['title'] .'\\\\'."\n";
            // summary
            $tex .= '\gray Summary & '. preg_replace("/[\\n\\r]+/", "\n\n", $position['summary']).'\\\\'."\n";
            $tex .= '\end{tabularx}'."\n";
            $tex .= '\vspace{12pt}'."\n\n";
        }
        return $tex;
    }
}

?>