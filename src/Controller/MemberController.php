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
        // Clear the member number and birthdate from the session
        $session = $request->getSession();
        // Create the form and handle the submission
        $form = $this->createForm(MemberType::class);
        $form->handleRequest($request);
        // get the submitted data
        $data = $request->request->get('member');

        $errors = array();

        // if there are flash messages, return them as error messages
        if($session->get('flash') !== null) {
            $errors[] = $session->get('flash');
            $session->remove('flash');
        }

        // if everything went alright
        if($form->isSubmitted()) {

            $errors = $this->validateMember($data);

            if($form->isValid()) {
                // check to see if there's a member with the entered data
                $member = $this->getMember($data);
                // if not, throw an error
                if(!$member) {

                    $errors[] = array('message' => 'Er werd geen lid teruggevonden met ingevulde gegevens');

                } else {
                    // if there is but has already chosen a gift, throw an error
                    if($member->getGift() !== null) {

                        $errors[] = array('message' => 'U heeft reeds een geschenk gekozen');

                    } else {
                        // else store the values in the session and redirect to the gift selection page
                        $this->storeDataInSession($request);
                        // redirect to gift page
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
        // get the date of the member stored in the session
        $data = $request->getSession()->get('member');

        // if the user did not enter the previous form, we'll redirect him to the front and ask him to verify his membership
        if($data === null) {
            $this->setFlashMessage('Verifieer uw lidmaatschap', $request);
            return new RedirectResponse('/');
        }

        $em = $this->getDoctrine()->getManager();
        // get all gifts
        $gifts = $em->getRepository(Gift::class)->findAll();
        // create form with the data of the member as hidden fields so we can check the values
        $form = $this->createForm(GiftType::class, array(
            'number' => $data['number'],
            'birthdate' => $data['birthdate'],
        ));

        $values = $request->request->get('gift');

        $form->handleRequest($request);

        $errors = array();

        if($form->isSubmitted()) {

            // check if the data has been tempered with
            $errors = $this->validateMember($data);
            // check to see if a gift has been selected
            if(!isset($values['gift']) || empty($values['gift'])) {
                $errors[] = array('message' => 'Gelieve een geschenk aan te duiden');
            }

            if($form->isValid()) {
                // check if the member exists
                $member = $this->getMember($data);
                // if not, set an error message and redirect to the index
                if(!$member) {
                    $this->setFlashMessage('Er werd geen lid teruggevonden. Probeer het opnieuw', $request);
                    return new RedirectResponse('/');
                } else {
                    // member exists but has already chosen a gift, redirect to homepage with error message
                    if($member->getGift() !== null) {
                        $this->setFlashMessage('U heeft reeds een geschenk gekozen', $request);
                        return new RedirectResponse('/');
                    } else {
                        $gift = $em->getRepository(Gift::class)->find($values['gift']);
                        if($gift !== null) {
                            $this->addGiftToMember($member, $gift);
                            return new RedirectResponse('/thanks');
                        } else {
                            // If no gift could be found for a certain id but the id exists, throw an error
                            if(!empty($values['gift'])) {
                                $errors[] = array('message' => 'Er ging iets mis met het ophalen van het geschenk');
                            }
                        }
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
     */
    public function thanks(Request $request)
    {
        // we can only access the thanks page after submitting previous forms
        if($request->getSession()->get('member') === null) {
            return new RedirectResponse('/');
        } else {
            // remove the member values from the session
            $request->getSession()->remove('member');
            return $this->render('thanks.html.twig');
        }
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

    // format date in the way symfony wants it
    public static function formatBirthdate($dateString)
    {
        if(trim($dateString) === '') {
            return false;
        }

        $dateString = trim(str_replace('/', '-', $dateString));
        $unix = strtotime($dateString);
        if(!$unix) {
            return false;
        } else {
            $date = new \DateTime(date('d-m-Y', $unix));
            return $date;
        }
    }

    // check to see if a member exists for given number and date
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
        // set the gift
        $member->setGift($gift);
        // update
        $em->persist($member);
        $em->flush();
    }
}
