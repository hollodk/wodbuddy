<?php

namespace App\Controller;

use App\Entity\Wod;
use App\Form\WodType;
use App\Repository\WodRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/wod")
 */
class WodController extends AbstractController
{
    /**
     * @Route("/", name="wod_index", methods={"GET"})
     */
    public function index(WodRepository $wodRepository): Response
    {
        return $this->render('wod/index.html.twig', [
            'wods' => $wodRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="wod_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $wod = new Wod();
        $form = $this->createForm(WodType::class, $wod);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($wod);
            $entityManager->flush();

            return $this->redirectToRoute('wod_index');
        }

        return $this->render('wod/new.html.twig', [
            'wod' => $wod,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="wod_show", methods={"GET"})
     */
    public function show(Wod $wod): Response
    {
        return $this->render('wod/show.html.twig', [
            'wod' => $wod,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="wod_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Wod $wod): Response
    {
        $form = $this->createForm(WodType::class, $wod);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('wod_index');
        }

        return $this->render('wod/edit.html.twig', [
            'wod' => $wod,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="wod_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Wod $wod): Response
    {
        if ($this->isCsrfTokenValid('delete'.$wod->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($wod);
            $entityManager->flush();
        }

        return $this->redirectToRoute('wod_index');
    }
}
