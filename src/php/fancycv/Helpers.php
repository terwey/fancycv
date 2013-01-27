<?php
namespace fancycv;

class Helpers
{
    public static function createDirectory($directoryName, $directoryPath, InputInterface $input=NULL, OutputInterface $output=NULL) {
        if (!file_exists($directoryPath)) {
            // attempt to create it
            if (!mkdir($directoryPath)) {
                if ($output instanceof OutputInterface) {
                    $output->writeln('<error>The '.$directoryName.' dir does not exist and could not be created: '.$directoryPath.'</error>');
                }
                exit(0);
            } else {
                return true;
            }
        }
    }

    public static function createFile($fileContents, $fileName, $filePath, InputInterface $input=NULL, OutputInterface $output=NULL) {
        if (file_put_contents($filePath, $fileContents)) {
            if ($output instanceof OutputInterface) {
                $output->writeln('<info>File '.$fileName.' successfully written: '.$filePath.'</info>');
            }
            return true;
        } else {
            if ($output instanceof OutputInterface) {
                $output->writeln('<error>Could not write file: '.$filePath.'</error>');
            }
            return false;
        }
    }
}

?>
