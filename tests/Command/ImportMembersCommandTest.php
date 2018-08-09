<?php

namespace App\Tests\Command;

use App\Command\GenerateCsvCommand;
use App\Command\ImportMembersCommand;
use App\Service\HelperService;
use App\Service\MemberService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\Log\Logger;
use ReflectionClass;

class ImportMembersCommandTest extends WebTestCase
{
    private $helperService;

    protected function setUp()
    {
        self::bootKernel();
        $container = self::$kernel->getContainer();

        $this->helperService = self::$container->get('App\Service\HelperService');
    }

    protected static function getMethod($class, $name)
    {
        $class = new ReflectionClass($class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }

    public function testIfCsvIsParsed()
    {
        $csv = 'test_csv_parser.csv';

        $generateCommand = new GenerateCsvCommand(null, new HelperService());
        $method = self::getMethod('App\Command\GenerateCsvCommand', 'generateCsv');

        $method->invokeArgs($generateCommand, array($csv));

        $this->assertFileExists($csv);

        $result = $this->helperService->parseCsv($csv);

        $this->assertInternalType('array', $result);
        $this->assertNotEmpty($result);

        unlink($csv);
    }
}