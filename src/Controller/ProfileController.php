<?php

namespace App\Controller;

use App\Entity\Organization;
use App\Entity\Participant;
use App\Entity\Wod;
use App\Form\WodType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/profile")
 */
class ProfileController extends AbstractController
{
    /**
     * @Route("")
     */
    public function index(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $participants = $em->getRepository('App:Participant')->findBy(
            ['user' => $this->getUser()],
            ['updatedAt' => 'DESC'],
            20
        );

        return $this->render('profile/index.html.twig', [
            'participants' => $participants,
        ]);
    }
}
