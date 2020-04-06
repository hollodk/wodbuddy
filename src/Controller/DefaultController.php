<?php

namespace App\Controller;

use App\Entity\Organization;
use App\Entity\Participant;
use App\Entity\Wod;
use App\Form\WodType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("", name="homepage")
     */
    public function index(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $organizationForm = $this->get('form.factory')
            ->createNamedBuilder('organization_form')
            ->add('name')
            ->add('email')
            ->getForm()
            ;

        $joinForm = $this->get('form.factory')
            ->createNamedBuilder('join_form')
            ->add('name')
            ->getForm()
            ;

        $organizationForm->handleRequest($request);
        $joinForm->handleRequest($request);

        if ($organizationForm->isSubmitted() && $organizationForm->isValid()) {
            $data = $organizationForm->getData();

            $organization = $em->getRepository('App:Organization')->findOneByName($data['name']);

            if ($organization) {
                die('organization with this name already exists, try again');
            }

            $organization = new Organization();
            $organization->setName($data['name']);
            $organization->setHash(uniqid());

            $em->persist($organization);
            $em->flush();

            return $this->redirectToRoute('app_default_organization', [
                'id' => $organization->getId(),
            ]);
        }

        if ($joinForm->isSubmitted() && $joinForm->isValid()) {
            $data = $joinForm->getData();

            $organization = $em->getRepository('App:Organization')->findOneByName($data['name']);

            if (!$organization) {
                die('no such organization, go back');
            }

            return $this->redirectToRoute('app_default_organization', [
                'id' => $organization->getId(),
            ]);
        }

        return $this->render('default/index.html.twig', [
            'join_form' => $joinForm->createView(),
            'organization_form' => $organizationForm->createView(),
        ]);
    }

    /**
     * @Route("/{id}/organization")
     */
    public function organization(Request $request, Organization $organization)
    {
        $em = $this->getDoctrine()->getManager();

        $wods = $em->getRepository('App:Wod')->findBy(
            ['organization' => $organization],
            ['startAt' => 'DESC'],
            10
        );

        $wod = new Wod();
        $wod->setOrganization($organization);

        $start = new \DateTime();
        $start->modify('+1 day');
        $start->modify('set time 17:00');
        $wod->setStartAt($start);

        $form = $this->createForm(WodType::class, $wod);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($wod);
            $em->flush();

            return $this->redirectToRoute('app_default_organization', [
                'id' => $organization->getId(),
            ]);
        }

        return $this->render('default/organization.html.twig', [
            'organization' => $organization,
            'wod_form' => $form->createView(),
            'wods' => $wods,
        ]);
    }

    /**
     * @Route("/{id}/wod")
     */
    public function wod(Request $request, Wod $wod)
    {
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();

        $key = sprintf('wod_%d_participant', $wod->getId());

        $participant = ($session->get($key))
            ? $em->find('App:Participant', $session->get($key))
            : null;

        $participants = $em->getRepository('App:Participant')->findBy(
            ['wod' => $wod],
            ['name' => 'ASC']
        );

        $form = $this->createFormBuilder()
            ->add('name')
            ->getForm()
            ;

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $participant = new Participant();
            $participant->setWod($wod);
            $participant->setName($data['name']);
            $participant->setLastSeenAt(new \DateTime());

            $em->persist($participant);
            $em->flush();

            $request->getSession()->set($key, $participant->getId());

            return $this->redirectToRoute('app_default_wod', [
                'id' => $wod->getId(),
                'participant' => $participant,
            ]);
        }

        return $this->render('default/wod.html.twig', [
            'wod' => $wod,
            'organization' => $wod->getOrganization(),
            'form' => $form->createView(),
            'participants' => $participants,
            'participant' => $participant,
        ]);
    }
}
