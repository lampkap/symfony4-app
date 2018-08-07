<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GenerateCsvCommand extends Command
{
    protected static $defaultName = 'generate:csv';

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $file = $input->getArgument('arg1');

        if ($file) {
            $info = pathinfo($file);
            // check if file is op type csv
            if($info['extension'] === 'csv') {
                // everything checks out so let's import
                $this->generateCsv($file);
            } else {
                $io->error('Het bestand moet van het type CSV zijn');
            }
        } else {
            $io->error('Geef een bestandspad mee als argument');
        }
    }

    protected function generateCsv($file)
    {
        $records = array();
        for($i = 0; $i <= 1000; $i++) {
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
