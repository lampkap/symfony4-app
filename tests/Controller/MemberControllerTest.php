<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MemberControllerTest extends WebTestCase
{
    private $memberService;
    private $helperService;

    protected function setUp()
    {
        self::bootKernel();
        $container = self::$kernel->getContainer();

        $this->memberService = self::$container->get('App\Service\MemberService');
        $this->helperService = self::$container->get('App\Service\HelperService');
    }

    public function testIfDateStringCanBeFormattedToDate()
    {
        $dateString = '17-05-1993 ';
        $result = $this->helperService->formatBirthdate($dateString);

        $this->assertEquals(trim($dateString), $result->format('d-m-Y'));
    }

    public function testIfDateStringCanNotBeFormattedToDate()
    {
        $dateString = ' ';
        $result = $this->helperService->formatBirthdate($dateString);

        $this->assertFalse($result);
    }

    public function testIfMemberIsValid()
    {
        $data = array('number' => '12345 ', 'birthdate' => '17-05-1993');
        $result = $this->memberService->validateMember($data);

        $this->assertEmpty($result);
    }

    public function testIfMemberHasNoNumber()
    {
        $data = array('number' => ' ', 'birthdate' => '17-05-1993');
        $result = $this->memberService->validateMember($data);

        $this->assertNotEmpty($result);
        $this->assertEquals($result[0]['message'], 'Gelieve jouw lidnummer in te vullen');
    }

    public function testIfMemberHasNoValidNumber()
    {
        $data = array('number' => 'string', 'birthdate' => '17-05-1993');
        $result = $this->memberService->validateMember($data);

        $this->assertNotEmpty($result);
        $this->assertEquals($result[0]['message'], 'Het lidnummer mag enkel nummers bevatten');
    }

    public function testIfMemberHasNoDate()
    {
        $data = array('number' => 12345, 'birthdate' => ' ');
        $result = $this->memberService->validateMember($data);

        $this->assertNotEmpty($result);
        $this->assertEquals($result[0]['message'], 'Gelieve jouw geboortedatum in te vullen');
    }

    public function testIfMemberHasNoValidDate()
    {
        $data = array('number' => '12345', 'birthdate' => 'test');
        $result = $this->memberService->validateMember($data);

        $this->assertNotEmpty($result);
        $this->assertEquals($result[0]['message'], 'Gelieve een geldig geboortedatum in te vullen');
    }
}