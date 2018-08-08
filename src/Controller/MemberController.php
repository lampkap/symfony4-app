<?php

namespace App\Controller;

use App\Entity\Gift;
use App\Entity\Member;
use App\Form\GiftType;
use App\Form\MemberType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class MemberController
 * @package App\Controller
 */
class MemberController extends Controller
{
    /**
     * @Route("/", name="index")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request)
    {
        $form = $this->createForm(MemberType::class);
        $form->handleRequest($request);

        $data = $request->request->get('member');
        $errors = array();

        // if there are flash messages from the next page, return them as error messages and remove them from the session
        $session = $request->getSession();
        if($session->get('flash') !== null) {
            $errors[] = $session->get('flash');
            $session->remove('flash');
        }

        if($form->isSubmitted()) {

            $errors = $this->validateMember($data);

            if($form->isValid()) {

                $member = $this->getMember($data);

                if(!$member) {

                    $errors[] = array('message' => 'Er werd geen lid teruggevonden met ingevulde gegevens');

                } else {
                    if($member->getGift() !== null) {

                        $errors[] = array('message' => 'U heeft reeds een geschenk gekozen');

                    } else {
                        // store the values in the session so we check if the user didn't go directly to "/gift"
                        $this->storeDataInSession($request);
                        return new RedirectResponse('/gift');
                    }

                }
            }
        }

        return $this->render('index.html.twig', array(
            'form' => $form->createView(),
            'errors' => $errors,
        ));
    }

    /**
     * @Route("/gift", name="gift")
     * @param Request $request
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function gift(Request $request)
    {
        // get the data of the index form which is stored in the session
        $data = $request->getSession()->get('member');

        // if the user did not enter the previous form, we'll redirect him to the front and ask him to verify his membership
        if($data === null) {
            $this->setFlashMessage('Verifieer uw lidmaatschap', $request);
            return new RedirectResponse('/');
        }

        $em = $this->getDoctrine()->getManager();
        $gifts = $em->getRepository(Gift::class)->findAll();
        // create form with the data of the member as hidden fields
        // so we can verify that the previous form has been submitted
        $form = $this->createForm(GiftType::class, array(
            'number' => $data['number'],
            'birthdate' => $data['birthdate'],
        ));

        $values = $request->request->get('gift');
        $errors = array();

        $form->handleRequest($request);

        if($form->isSubmitted()) {

            // check if the data has been tempered with
            $errors = $this->validateMember($data);
            if(!isset($values['gift']) || empty($values['gift'])) {
                $errors[] = array('message' => 'Gelieve een geschenk aan te duiden');
            }

            if($form->isValid()) {

                $member = $this->getMember($data);

                if(!$member) {
                    $this->setFlashMessage('Er werd geen lid teruggevonden. Probeer het opnieuw', $request);
                    return new RedirectResponse('/');
                }

                if($member->getGift() !== null) {
                    $this->setFlashMessage('U heeft reeds een geschenk gekozen', $request);
                    return new RedirectResponse('/');
                }

                $gift = $em->getRepository(Gift::class)->find($values['gift']);

                if($gift !== null) {
                    $this->addGiftToMember($member, $gift);
                    return new RedirectResponse('/thanks');
                } else {
                    if(!empty($values['gift'])) {
                        $errors[] = array('message' => 'Er ging iets mis met het ophalen van het geschenk');
                    }
                }
            }
        }

        return $this->render('gift.html.twig', array(
            'form' => $form->createView(),
            'gifts' => $gifts,
            'errors' => $errors
        ));
    }

    /**
     * @Route("/thanks", name="thanks")
     * @param Request $request
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function thanks(Request $request)
    {
        // we can only access the thanks page after submitting previous forms
        if($request->getSession()->get('member') === null) {
            return new RedirectResponse('/');
        }

        $request->getSession()->remove('member');
        return $this->render('thanks.html.twig');
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
        if(!self::formatBirthdate($data['birthdate'])) {
            $errors[] = array('message' => 'Gelieve een geldig geboortedatum in te vullen');
        }

        return $errors;
    }

    // format the date to a DateTime object if possible. Return false instead
    public static function formatBirthdate($dateString)
    {
        if(trim($dateString) === '') {
            return false;
        }

        // change the date dividers so that strtotime function won't use the american date notation
        $dateString = trim(str_replace('/', '-', $dateString));
        $unix = strtotime($dateString);

        if(!$unix) {
            return false;
        }

        $date = new \DateTime(date('d-m-Y', $unix));
        return $date;
    }

    public function getMember($data)
    {
        $em = $this->getDoctrine()->getManager();

        $number = $data['number'];
        $birthdate = self::formatBirthdate($data['birthdate']);

        $member = $em->getRepository(Member::class)->findOneBy(
            array('number' => $number, 'birthdate' => $birthdate)
        );

        return (!empty($member)) ? $member : false;
    }

    // store number and date in session
    public function storeDataInSession(Request $request)
    {
        $session = $request->getSession();
        $data = $request->request->get('member');
        $session->set('member', $data);
    }

    public function setFlashMessage($message, Request $request)
    {
        $flashError = array('message' => $message);
        $request->getSession()->set('flash', $flashError);
    }

    public function addGiftToMember(Member $member, Gift $gift)
    {
        $em = $this->getDoctrine()->getManager();
        $member->setGift($gift);
        $em->persist($member);
        $em->flush();
    }
}
