<?php

namespace App\Controller;

use App\Entity\Gift;
use App\Form\GiftType;
use App\Form\MemberType;
use App\Service\HelperService;
use App\Service\MemberService;
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
     * @param MemberService $memberService
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request, MemberService $memberService)
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

            $errors = $memberService->validateMember($data);

            if($form->isValid()) {

                $member = $memberService->getMember($data);

                if(!$member) {

                    $errors[] = array('message' => 'Er werd geen lid teruggevonden met ingevulde gegevens');

                } else {
                    if($member->getGift() !== null) {

                        $errors[] = array('message' => 'U heeft reeds een geschenk gekozen');

                    } else {
                        // store the values in the session so we check if the user didn't go directly to "/gift"
                        $memberService->storeDataInSession($request);
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
     * @param MemberService $memberService
     * @param HelperService $helperService
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function gift(Request $request, MemberService $memberService, HelperService $helperService)
    {
        // get the data of the index form which is stored in the session
        $data = $request->getSession()->get('member');

        // if the user did not enter the previous form, we'll redirect him to the front and ask him to verify his membership
        if($data === null) {
            $helperService->setFlashMessage('Verifieer uw lidmaatschap', $request);
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
            $errors = $memberService->validateMember($data);
            if(!isset($values['gift']) || empty($values['gift'])) {
                $errors[] = array('message' => 'Gelieve een geschenk aan te duiden');
            }

            if($form->isValid()) {

                $member = $memberService->getMember($data);

                if(!$member) {
                    $helperService->setFlashMessage('Er werd geen lid teruggevonden. Probeer het opnieuw', $request);
                    return new RedirectResponse('/');
                }

                if($member->getGift() !== null) {
                    $helperService->setFlashMessage('U heeft reeds een geschenk gekozen', $request);
                    return new RedirectResponse('/');
                }

                $gift = $em->getRepository(Gift::class)->find($values['gift']);

                if($gift !== null) {
                    $memberService->addGiftToMember($member, $gift);
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
}
