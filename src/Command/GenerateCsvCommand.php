<?php

namespace App\Command;

use App\Service\HelperService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GenerateCsvCommand extends Command
{
    private $helperService;

    public function __construct(?string $name = null, HelperService $helperService)
    {
        parent::__construct($name);
        $this->helperService = $helperService;
    }

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

        if(!$file) {
            $io->error('Geef een bestandspad mee als argument');
        }

        $info = pathinfo($file);

        if($info['extension'] !== 'csv') {
            $io->error('Het bestand moet van het type CSV zijn');
        }

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

        $this->generateCsv($file);

        if(!file_exists($file)) {
            $io->error('Het bestand ' . $file . ' kon niet worden aangemaakt');
        }

        if($handleFile === 'create' || $handleFile === 'Opnieuw laten genereren') {
            $io->success('Er werden 1000 leden gegenereerd en toegevoegd aan het bestand ' . $file);
        } else {
            $io->success('Er werden 1000 extra leden gegenereerd en toegevoegd aan het bestand ' . $file);
        }
    }

    protected function generateCsv($file)
    {
        // create the directories of the path if they don't exist
        $directories = dirname($file);

        if(!is_dir($directories)) {
            mkdir($directories, 0777, true);
        }

        $numbers = array();
        $i = 0;

        while($i < 1000) {
            $number = $this->helperService->generateNumber();
            $date = $this->helperService->generateDate();

            $record = $date.';'.$number;

            // Only add unique records to the csv file
            if(!in_array($number, $numbers)) {
                $numbers[] = $number;
                file_put_contents($file, $record . PHP_EOL, FILE_APPEND);
                $i++;
            }
        }
    }
}
