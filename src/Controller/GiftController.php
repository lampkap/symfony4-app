<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class GiftController extends Controller
{
    /**
     * @Route("/", name="index")
     */
    public function index()
    {
        return $this->render('index.html.twig');
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
