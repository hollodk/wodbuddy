<?php

namespace App\Controller;

use App\Entity\Image;
use App\Entity\Organization;
use App\Entity\Participant;
use App\Entity\Track;
use App\Entity\User;
use App\Entity\UserOrganization;
use App\Entity\Wod;
use App\Form\WodType;
use App\Security\AppCustomAuthenticator;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class DefaultController extends AbstractController
{
    /**
     * @Route("", name="homepage")
     */
    public function index(Request $request, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, AppCustomAuthenticator $authenticator)
    {
        $em = $this->getDoctrine()->getManager();

        $wods = $em->getRepository('App:Wod')->findBy(
            ['isFeatured' => true],
            ['name' => 'ASC']
        );

        $organizationForm = $this->get('form.factory')
            ->createNamedBuilder('organization_form')
            ->add('organization', null, [
                'attr' => [
                    'placeholder' => 'My Gym',
                ],
            ])
            ->add('name', null, [
                'attr' => [
                    'placeholder' => 'Your Name',
                ],
            ])
            ->add('email', null, [
                'attr' => [
                    'placeholder' => 'Your Email',
                ],
            ])
            ->add('password', PasswordType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        'max' => 4096,
                    ]),
                ],
            ])
            ->getForm()
            ;

        $joinForm = $this->get('form.factory')
            ->createNamedBuilder('join_form')
            ->add('name', null, [
                'attr' => [
                    'placeholder' => 'Name of organization',
                ],
            ])
            ->getForm()
            ;

        $wod = new Wod();
        $wod->setOwnerSession($request->getSession()->getId());
        $wod->setUser($this->getUser());
        $wod->setName('My WOD');

        $wodForm = $this->createForm(WodType::class, $wod);
        $wodForm->remove('startAt');
        $wodForm->remove('stream');

        $wodForm->handleRequest($request);

        if ($wodForm->isSubmitted() && $wodForm->isValid()) {
            $attr = [
                'delay' => 5,
                'type' => $wod->getTimer(),
            ];

            switch ($wod->getTimer()) {
            case 'timer':
                $attr['time'] = '10:00';
                break;

            case 'emom':
                $attr['emomtime'] = '01:00';
                $attr['round'] = 12;
                break;
            }

            $wod->setAttribute(json_encode($attr));

            $em->persist($wod);
            $em->flush();

            return $this->redirectToRoute('app_default_wod', [
                'id' => $wod->getId(),
            ]);
        }

        $organizationForm->handleRequest($request);
        $joinForm->handleRequest($request);

        if ($organizationForm->isSubmitted() && $organizationForm->isValid()) {
            $data = $organizationForm->getData();

            $organization = new Organization();
            $organization->setName($data['organization']);
            $organization->setHash(uniqid());

            $em->persist($organization);

            $user = new User();
            $user->setOrganization($organization);
            $user->setEmail($data['email']);
            $user->setName($data['name']);

            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $data['password']
                )
            );

            $organization->setUser($user);

            $em->persist($user);
            $em->flush();

            $guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $authenticator,
                'main'
            );

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
            'wod_form' => $wodForm->createView(),
            'wods' => $wods,
        ]);
    }

    /**
     * @Route("feedback")
     */
    public function feedback()
    {
        return $this->render('default/feedback.html.twig');
    }

    /**
     * @Route("/{id}/organization")
     */
    public function organization(Request $request, Organization $organization)
    {
        $em = $this->getDoctrine()->getManager();

        $request->getSession()->set('organization', $organization->getId());

        $wods = $em->getRepository('App:Wod')->findBy(
            ['organization' => $organization],
            ['startAt' => 'DESC'],
            20
        );

        if ($this->getUser()) {
            $uo = $em->getRepository('App:UserOrganization')->findOneBy([
                'user' => $this->getUser(),
                'organization' => $organization,
            ]);

            if (!$uo) {
                $uo = new UserOrganization();
                $uo->setUser($this->getUser());
                $uo->setOrganization($organization);

                $em->persist($uo);
            }

            $uo->setLastVisitAt(new \DateTime());

            $em->flush();
        }

        $wod = new Wod();
        $wod->setOwnerSession($request->getSession()->getId());
        $wod->setUser($this->getUser());
        $wod->setOrganization($organization);

        $start = new \DateTime();
        $start->modify('+1 day');
        $start->modify('set time 17:00');
        $wod->setStartAt($start);

        $form = $this->createForm(WodType::class, $wod);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $attr = [
                'delay' => 5,
                'type' => $wod->getTimer(),
            ];

            switch ($wod->getTimer()) {
            case 'timer':
                $attr['time'] = '10:00';
                break;

            case 'emom':
                $attr['emomtime'] = '01:00';
                $attr['round'] = 12;
                break;
            }

            $wod->setAttribute(json_encode($attr));

            $em->persist($wod);
            $em->flush();

            return $this->redirectToRoute('app_default_wod', [
                'id' => $wod->getId(),
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

        if ($wod->getOrganization()) {
            $session->set('organization', $wod->getOrganization()->getId());
        }

        $key = sprintf('wod_%d_participant', $wod->getId());

        $participant = ($session->get($key))
            ? $em->find('App:Participant', $session->get($key))
            : null;

        $participants = $em->getRepository('App:Participant')->findBy(
            ['wod' => $wod],
            ['name' => 'ASC']
        );

        $tracks = $em->getRepository('App:Track')->findBy(
            ['wod' => $wod],
            ['score' => 'DESC'],
            20
        );

        $imageForm = $this->createFormBuilder()
            ->add('image', FileType::class, [
                'attr' => [
                    'capture' => 'camera',
                    'accept' => 'image/*',
                    'class' => '',
                ],
            ])
            ->getForm()
            ;

        $imageForm->handleRequest($request);

        if ($imageForm->isSubmitted() && $imageForm->isValid()) {
            $data = $imageForm->getData();

            $filter = new \Imagine\Filter\Basic\WebOptimization();
            $imagine = new \Imagine\Gd\Imagine();

            $dimentions = @getimagesize($data['image']->getRealPath());

            if ($dimentions[0] > $dimentions[1]) {
                $width = 800;
                $height = 600;
            } else {
                $width = 600;
                $height = 800;
            }

            $img = $filter->apply(
                $imagine->open($data['image']->getRealPath())
                ->thumbnail(
                    new \Imagine\Image\Box($width, $height),
                    \Imagine\Image\ImageInterface::THUMBNAIL_OUTBOUND
                )
            );

            $filename = null;
            switch ($data['image']->getMimeType()) {
            case 'image/jpeg':
                $filename = '/tmp/upload.jpg';
                break;

            case 'image/png':
                $filename = '/tmp/upload.png';
                break;
            }

            $img->save($filename);

            $image = new Image();
            $image->setUser($this->getUser());
            $image->setWod($wod);
            $image->setMimeType($data['image']->getMimeType());
            $image->setContent(base64_encode(file_get_contents($filename)));

            $em->persist($image);
            $em->flush();

            return $this->redirectToRoute('app_default_wod', [
                'id' => $wod->getId(),
            ]);
        }

        $wodForm = $this->createForm(WodType::class, $wod);
        $wodForm->remove('timer');

        if (!$wod->getOrganization()) {
            $wodForm->remove('startAt');
        }

        $data = [];
        if ($this->getUser()) {
            $data['name'] = $this->getUser()->getName();
        }

        $form = $this->createFormBuilder($data)
            ->add('name')
            ->getForm()
            ;

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            if ($this->getUser()) {
                $participant = $em->getRepository('App:Participant')->findOneBy([
                    'user' => $this->getUser(),
                    'wod' => $wod,
                ]);
            }

            if (!$participant) {
                $participant = new Participant();
                $participant->setUser($this->getUser());
                $participant->setWod($wod);
                $participant->setLastSeenAt(new \DateTime());

                $em->persist($participant);
            }

            $participant->setName($data['name']);

            $em->flush();

            $request->getSession()->set($key, $participant->getId());

            return $this->redirectToRoute('app_default_wod', [
                'id' => $wod->getId(),
                'participant' => $participant,
            ]);
        }

        $participantId = ($participant) ? $participant->getId() : null;

        $images = $em->getRepository('App:Image')->findBy(
            ['wod' => $wod],
            ['id' => 'DESC'],
            20
        );

        return $this->render('default/wod.html.twig', [
            'wod' => $wod,
            'attribute' => json_decode($wod->getAttribute()),
            'organization' => $wod->getOrganization(),
            'form' => $form->createView(),
            'wod_form' => $wodForm->createView(),
            'image_form' => $imageForm->createView(),
            'participants' => $participants,
            'participant' => $participant,
            'participant_id' => $participantId,
            'tracks' => $tracks,
            'images' => $images,
        ]);
    }

    /**
     * @Route("/{id}/profile")
     */
    public function profile(Request $request, User $user)
    {
        $em = $this->getDoctrine()->getManager();

        $tracks = $em->getRepository('App:Track')->findBy(
            ['user' => $user],
            ['workoutAt' => 'DESC'],
            20
        );

        return $this->render('default/profile.html.twig', [
            'user' => $user,
            'tracks' => $tracks,
        ]);
    }

    /**
     * @Route("/{id}/image-delete")
     */
    public function imagedelete(Request $request, Image $image)
    {
        $em = $this->getDoctrine()->getManager();

        $em->remove($image);
        $em->flush();

        return $this->redirectToRoute('app_default_wod', [
            'id' => $image->getWod()->getId(),
        ]);
    }

    /**
     * @Route("/{id}/wod-delete")
     */
    public function woddelete(Request $request, Wod $wod)
    {
        $em = $this->getDoctrine()->getManager();

        foreach ($wod->getTracks() as $t) {
            $t->setWod(null);
        }

        foreach ($wod->getParticipants() as $p) {
            $em->remove($p);
        }

        $em->remove($wod);
        $em->flush();

        return $this->redirectToRoute('app_default_organization', [
            'id' => $wod->getOrganization()->getId(),
        ]);
    }

    /**
     * @Route("/{id}/track")
     */
    public function track(Request $request, Wod $wod)
    {
        $track = new Track();
        $track->setWod($wod);
        $track->setUser($this->getUser());
        $track->setWodDescription($wod->getDescription());
        $track->setWorkoutAt(new \DateTime());

        if ($request->get('wod-rating')) {
            $track->setWodRating($request->get('wod-rating'));
        }
        if ($request->get('feeling')) {
            $track->setFeeling($request->get('feeling'));
        }
        $track->setNote($request->get('note'));
        $track->setType($request->get('type'));
        $track->setRxOrScaled($request->get('rx-scaled'));

        switch ($request->get('type')) {
        case 'time':
            $time = 0;
            if ($request->get('time-min')) {
                $time = $request->get('time-min')*60;
            }
            if ($request->get('time-sec')) {
                $time += $request->get('time-sec');
            }
            $track->setScore($time);

            break;

        case 'rounds-reps':
            $result = sprintf('%d.%d',
                $request->get('rounds-reps-rounds'),
                $request->get('rounds-reps-reps')
            );

            $track->setScore($result);

            break;

        case 'reps':
            $track->setScore($request->get('reps'));
            break;

        case 'load':
            $track->setScore($request->get('load'));
            break;

        case 'calories':
            $track->setScore($request->get('calories'));
            break;

        case 'points':
            $track->setScore($request->get('points'));
            break;

        case 'meters':
            $track->setScore($request->get('meters'));
            break;
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($track);
        $em->flush();

        return $this->redirect($request->server->get('HTTP_REFERER'));
    }

    /**
     * @Route("/{id}/timer")
     */
    public function timer(Request $request, Wod $wod)
    {
        $data = $request->request->all();

        $wod->setTimer($data['type']);
        $wod->setAttribute(json_encode($data));

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return $this->redirectToRoute('app_default_wod', [
            'id' => $wod->getId(),
        ]);
    }

    /**
     * @Route("/{id}/edit")
     */
    public function edit(Request $request, Wod $wod)
    {
        $form = $this->createForm(WodType::class, $wod);
        $form->remove('timer');

        if (!$wod->getOrganization()) {
            $form->remove('startAt');
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($wod);
            $em->flush();
        }

        return $this->redirectToRoute('app_default_wod', [
            'id' => $wod->getId(),
        ]);
    }

    /**
     * @Route("/{id}/start")
     */
    public function start(Request $request, Wod $wod)
    {
        $memcached = new \Memcached();
        $memcached->addServer('localhost', '11211');

        $memcached->set('wod_status_'.$wod->getId(), true, 15);

        return $this->redirectToRoute('app_default_wod', [
            'id' => $wod->getId(),
        ]);
    }

    /**
     * @Route("/{id}/image")
     */
    public function image(Request $request, Image $image)
    {
        $response = new Response();
        $disposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $image->getFilename());
        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Type', $image->getMimeType());

        $response->setContent(base64_decode($image->getContent()));

        return $response;
    }
}
