<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api")
 */
class ApiController extends AbstractController
{
    /**
     * @Route("/participants")
     */
    public function participants(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $memcached = new \Memcached();
        $memcached->addServer('localhost', '11211');

        $startedKey = 'wod_%d_partifipant_%d_started';

        if ($request->get('participant')) {
            $participant = $em->find('App:Participant', $request->get('participant'));
            $participant->setLastSeenAt(new \DateTime());

            $em->flush();

            if ($request->get('isStarted') == 'true') {
                $key = sprintf($startedKey,
                    $request->get('wod'),
                    $request->get('participant')
                );
                $memcached->set($key, $request->get('isStarted'), 30);
            }
        }

        $wod = $em->find('App:Wod', $request->get('wod'));
        $from = new \DateTime();
        $from->modify('-15 minutes');

        $participants = $em->getRepository('App:Participant')
            ->createQueryBuilder('p')
            ->andWhere('p.wod = :wod')
            ->andWhere('p.lastSeenAt > :from')
            ->addOrderBy('p.name', 'ASC')
            ->setParameter('wod', $wod)
            ->setParameter('from', $from)
            ->getQuery()
            ->getResult()
            ;

        $key = sprintf('wod_%d_participant', $wod->getId());

        $user = ($request->getSession()->get($key))
            ? $em->find('App:Participant', $request->getSession()->get($key))
            : null;

        $res = [];
        foreach ($participants as $p) {
            $params = [
                'name' => $p->getName(),
                'time_ago' => $this->timeAgo($p->getLastSeenAt()),
                'is_me' => false,
                'is_started' => $memcached->get(sprintf($startedKey, $wod->getId(), $p->getId())),
            ];

            if ($user && $user->getId() == $p->getId()) {
                $params['is_me'] = true;
            }

            $res[] = $params;
        }

        return new JsonResponse([
            'participants' => $res,
        ]);
    }

    /**
     * @Route("/ready")
     */
    public function ready(Request $request)
    {
        $memcached = new \Memcached();
        $memcached->addServer('localhost', '11211');

        $status = $memcached->get('wod_status_'.$request->get('wod'));

        return new JsonResponse([
            'start' => $status,
        ]);
    }

    public function timeago(\DateTime $date)
    {
        $timestamp = strtotime($date->format('Y-m-d H:i:s'));

        $strTime = array("second", "minute", "hour", "day", "month", "year");
        $length = array("60","60","24","30","12","10");

        $currentTime = time();
        if($currentTime >= $timestamp) {
            $diff     = time()- $timestamp;
            for($i = 0; $diff >= $length[$i] && $i < count($length)-1; $i++) {
                $diff = $diff / $length[$i];
            }

            $diff = round($diff);
            return $diff . " " . $strTime[$i] . "(s) ago ";
        }
    }
}
