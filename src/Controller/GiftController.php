<?php

namespace App\Controller;

use App\Form\MemberType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class GiftController extends Controller
{
    /**
     * @Route("/", name="index")
     */
    public function index()
    {
        $form = $this->createForm(MemberType::class);

        return $this->render('index.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/gift", name="gift")
     */
    public function gift()
    {
        return $this->render('gift.html.twig');
    }

    /**
     * @Route("/thanks", name="thanks")
     */
    public function thanks()
    {
        return $this->render('thanks.html.twig');
    }
}
