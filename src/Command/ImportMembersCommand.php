<?php

namespace App\Command;

use App\Controller\GiftController;
use App\Controller\MemberController;
use App\Entity\Member;
use App\Service\HelperService;
use App\Service\MemberService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ImportMembersCommand extends ContainerAwareCommand
{
    private $logger;
    private $memberService;
    private $helperService;

    public function __construct(?string $name = null, LoggerInterface $logger, MemberService $memberService, HelperService $helperService)
    {
        parent::__construct($name);
        $this->logger = $logger;
        $this->memberService = $memberService;
        $this->helperService = $helperService;
    }

    /**
     * @var string
     */
    protected static $defaultName = 'import:members';

    protected function configure()
    {
        $this
            ->setDescription('Importeer leden vanuit een CSV bestand')
            ->addArgument('file', InputArgument::REQUIRED, 'Het CSV bestand met daarin de gegevens van de leden');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $file = $input->getArgument('file');

        if(!$file) {
            $io->error('Geef een bestandspad mee als argument');
        }

        if(!file_exists($file)) {
            $io->error(sprintf('Er werd geen bestand %s gevonden', $file));
        }

        $info = pathinfo($file);

        if($info['extension'] !== 'csv') {
            $io->error('Het bestand moet van het type CSV zijn');
        }

        $this->importMembers($file, $io);
    }

    /**
     * @param $file
     * @param SymfonyStyle $io
     */
    protected function importMembers($file, SymfonyStyle $io)
    {
        // create an array from the csv values
        $members = $this->helperService->parseCsv($file);
        if(!$members) {
            $io->error('Het csv bestand kon niet worden verwerkt of heeft geen inhoud');
            exit;
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach($members as $memberValues) {
            // check if the date is correct and can be imported
            $date = $this->helperService->formatBirthdate($memberValues[0]);
            if(!$date) {
                $io->error('De datum ' . $memberValues[0] . ' is niet correct. Het importeren is onderbroken');
                exit;
            }

            $em = $this->getContainer()->get('doctrine')->getManager();

            $member = $em->getRepository(Member::class)->findOneBy(array('number' => $memberValues[1]));

            if(empty($member)) {

                $this->memberService->createMember($date, $memberValues[1]);
                $this->logger->info('Lid ' . $memberValues[1] . ', geboren op ' . $date->format('d-m-Y') . ' geïmporteerd');
                $created++;

            } else {
                // check if the member should be updated. If so, the member will be updated and function will return true
                $hasUpdated = $this->memberService->updateMember($member, $date);

                if($hasUpdated) {
                    $this->logger->info('Lid ' . $memberValues[1] . ', geboren op ' . $date->format('d-m-Y') . ' geüpdate');
                    $updated++;
                } else {
                    $this->logger->info('Lid ' . $memberValues[1] . ' is niet gewijzigd.');
                    $skipped++;
                }

            }
        }

        $io->success('Import doorgevoerd');
        $io->table(array('Aangemaakt', 'Aangepast', 'Overgeslagen'), array(array($created, $updated, $skipped)));

    }
}
