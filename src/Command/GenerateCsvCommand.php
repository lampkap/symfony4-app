<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GenerateCsvCommand extends Command
{
    protected static $defaultName = 'generate:csv';

    protected function configure()
    {
        $this
            ->setDescription('Genereer een CSV bestand voor het importeren van leden')
            ->addArgument('path', InputArgument::REQUIRED, 'Het pad waar het bestand moet aangemaakt of aangepast worden')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $file = $input->getArgument('path');

        if ($file) {
            $info = pathinfo($file);
            // check if file is op type csv
            if($info['extension'] === 'csv') {
                // everything checks out so let's generate

                // check if file exists. If so, give the choice to delete and create a new one or to append to it
                if(file_exists($file)) {
                    $handleFile = $io->choice(
                        'Het bestand ' . $file . ' bestaat al. Wilt u deze opnieuw laten genereren of het bestand uitbreiden?',
                        array('Opnieuw laten genereren', 'bijvoegen'));

                    if($handleFile === 'Opnieuw laten genereren') {
                        unlink($file);
                    }
                } else {
                    $handleFile = 'create';
                }

                // generate the csv
                $this->generateCsv($file, $handleFile);

                // return success or error messages
                if(file_exists($file)) {
                    if($handleFile === 'create' || $handleFile === 'Opnieuw laten genereren') {
                        $io->success('Er werden 1000 leden gegenereerd en toegevoegd aan het bestand ' . $file);
                    } else {
                        $io->success('Er werden 1000 extra leden gegenereerd en toegevoegd aan het bestand ' . $file);
                    }
                } else {
                    $io->error('Het bestand ' . $file . ' kon niet worden aangemaakt');
                }

            } else {
                $io->error('Het bestand moet van het type CSV zijn');
            }
        } else {
            $io->error('Geef een bestandspad mee als argument');
        }
    }

    protected function generateCsv($file, $handleFile)
    {
        // check if the directories of the path exist, thet should be created if they don't exist
        $directories = dirname($file);

        if(!is_dir($directories)) {
            mkdir($directories, 0777, true);
        }

        $records = array();

        for($i = 0; $i < 1000; $i++) {
            $number = $this->generateNumber();
            $date = $this->generateDate();

            $record = $date.';'.$number;

            // Only add unique records to the csv file
            if(!in_array($record, $records)) {
                $records[] = $record;

                file_put_contents($file, $record . PHP_EOL, FILE_APPEND);
            }
        }


    }


    protected function generateNumber()
    {
        $numbers = range(1000, 30000);
        $i = rand(0, count($numbers) - 1);
        
        return $numbers[$i];
    }

    protected function generateDate()
    {
        $days = range(1, 28);
        $months = range(1, 12);
        $years = range(1930, 2000);

        $daysI = rand(0, count($days) - 1);
        $monthsI = rand(0, count($months) - 1);
        $yearsI = rand(0, count($years) - 1);

        return $days[$daysI] . '/' . $months[$monthsI] . '/' . $years[$yearsI];
    }
}
