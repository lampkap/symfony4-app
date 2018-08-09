<?php

namespace App\Service;

use App\Entity\Gift;
use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class MemberService
{
    private $em;
    private $helperService;

    public function __construct(EntityManagerInterface $em, HelperService $helperService)
    {
        $this->em = $em;
        $this->helperService = $helperService;
    }

    // basic validation on user input
    public function validateMember($data)
    {
        $errors = array();

        if(trim($data['number']) === '') {
            $errors[] = array('message' => 'Gelieve jouw lidnummer in te vullen');
        }
        if(!is_numeric(trim($data['number']))) {
            $errors[] = array('message' => 'Het lidnummer mag enkel nummers bevatten');
        }
        if(trim($data['birthdate']) === '') {
            $errors[] = array('message' => 'Gelieve jouw geboortedatum in te vullen');
        }
        if(!$this->helperService->formatBirthdate($data['birthdate'])) {
            $errors[] = array('message' => 'Gelieve een geldig geboortedatum in te vullen');
        }

        return $errors;
    }

    public function getMember($data)
    {
        $number = $data['number'];
        $birthdate = $this->helperService->formatBirthdate($data['birthdate']);

        $member = $this->em->getRepository(Member::class)->findOneBy(
            array('number' => $number, 'birthdate' => $birthdate)
        );

        return (!empty($member)) ? $member : false;
    }

    /**
     * @param $date
     * @param $number
     */
    public function createMember($date, $number)
    {
        $member = new Member();

        $member->setBirthdate($date);
        $member->setNumber($number);

        $this->em->persist($member);
        $this->em->flush();
    }

    /**
     * @param $member
     * @param $date
     * @return bool
     */
    public function updateMember(Member $member, $date)
    {
        if($member->getBirthdate() != $date) {
            $member->setBirthdate($date);

            $this->em->persist($member);
            $this->em->flush();

            return true;

        } else {
            return false;
        }
    }

    public function addGiftToMember(Member $member, Gift $gift)
    {
        $member->setGift($gift);
        $this->em->persist($member);
        $this->em->flush();
    }

    // store number and date in session
    public function storeDataInSession(Request $request)
    {
        $session = $request->getSession();
        $data = $request->request->get('member');
        $session->set('member', $data);
    }
}