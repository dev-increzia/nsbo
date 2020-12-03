<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Community;
use AppBundle\Repository\ArticleRepository;
use AppBundle\Repository\AssociationRepository;
use AppBundle\Repository\CategoryRepository;
use AppBundle\Repository\CommentRepository;
use AppBundle\Repository\EventRepository;
use AppBundle\Repository\MerchantRepository;
use AppBundle\Repository\PushRepository;
use AppBundle\Repository\ReportingRepository;
use AppBundle\Repository\WorkRepository;
use AppBundle\Service\GoogleAnalytics;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form\StatFilterType;
use UserBundle\Repository\UserRepository;

class StatController extends Controller
{
    public function generalAction()
    {
        /** @var Form $form */
        $form = $this->get('form.factory')->create(StatFilterType::class);
        $em = $this->getDoctrine()->getManager();
        $cityhall = $this->container->get('session.community')->getCommunity();
        $categories = $em->getRepository('AppBundle:Category')->findAll();

        /** @var AssociationRepository $associationRepository */
        $associationRepository = $em->getRepository('AppBundle:Association');
        /** @var EventRepository $eventRepository */
        $eventRepository = $em->getRepository('AppBundle:Event');
        /** @var MerchantRepository $merchantRepository */
        $merchantRepository = $em->getRepository('AppBundle:Merchant');

        $associations = $associationRepository->search(false, array(), $cityhall, null, null, null, null, null, null);
        $merchants = $merchantRepository->search(false, array(), $cityhall, null, null, null, null, null, null);
        $events = $eventRepository->search(false, array(), $cityhall, null, null, null, null, null, null, null, null, null, null, null, null, null);
        return $this->render('AppBundle:Stat:general.html.twig', array(
            'form' => $form->createView(),
            'categories' => $categories,
            'associations' => $associations,
            'merchants' => $merchants,
            'events' => $events,
        ));
    }

    public function generalChart1Action(Request $request)
    {
        /** @var GoogleAnalytics $analyticsService */
        $analyticsService = $this->get('googleAnalytics');
        $rows = $analyticsService->getSessionsAndMedium($this->_getProfileId(false, true), $request->get('dateBefore'), $request->get('dateAfter'));
        $sites = array();
        foreach ($rows as $row) {
            if ($row[0] == '(none)') {
                if (!isset($sites['Naturel'])) {
                    $sites['Naturel'] = 0;
                }
                $sites['Naturel'] = $sites['Naturel'] + $row[1];
            } elseif ($row[0] == 'organic') {
                if (!isset($sites['Moteur de recherche'])) {
                    $sites['Moteur de recherche'] = 0;
                }
                $sites['Moteur de recherche'] = $sites['Moteur de recherche'] + $row[1];
            } elseif ($row[0] == 'referral') {
                if (!isset($sites['Lien d\'un autre site'])) {
                    $sites['Lien d\'un autre site'] = 0;
                }
                $sites['Lien d\'un autre site'] = $sites['Lien d\'un autre site'] + $row[1];
            }
        }

        $datas = array();
        foreach ($sites as $k => $v) {
            $datas[] = array('type' => $k, 'quantity' => $v);
        }

        return new JsonResponse($datas);
    }

    public function generalChart2Action(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $cityhall = $this->container->get('session.community')->getCommunity();

        /** @var UserRepository $userRepository */
        $userRepository = $em->getRepository('UserBundle:User');

        $entities = $userRepository->search(false, array(), $cityhall, array('ROLE_CITIZEN'), null, null, null, null, null, null, $request->get('dateBefore'), $request->get('dateAfter'));
        $granularity = $request->request->get('granularity');
        $datas = $this->_getDatas($entities, $granularity, 'getCreateAt', $request->get('dateBefore'), $request->get('dateAfter'));
        return new JsonResponse($datas);
    }

    public function generalChart3Action(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var Community $cityhall */
        $cityhall = $this->container->get('session.community')->getCommunity();

        /** @var UserRepository $userRepository */
        $userRepository = $em->getRepository('UserBundle:User');

        $entities = $userRepository->search(false, array(), $cityhall, array('ROLE_CITIZEN'), null, null, null, null, null, null, $request->get('dateBefore'), $request->get('dateAfter'));
        $granularity = $request->request->get('granularity');
        $datasUser = $this->_getDatas($entities, $granularity, 'getCreateAt', $request->get('dateBefore'), $request->get('dateAfter'));
        $analyticsService = $this->get('googleAnalytics');
        $rows = $analyticsService->getSessions($this->_getProfileId(), $request->get('dateBefore'), $request->get('dateAfter'));
        $datasGranularity = $this->_getDatas(array(), $granularity, 'getCreateAt', $request->get('dateBefore'), $request->get('dateAfter'), $rows);
        $datasSession = array();
        foreach ($datasGranularity as $v) {
            if (!is_array($v['quantity'])) {
                $datasSession[] = array(
                    'value' => $v['value'],
                    'quantity' => $v['quantity'],
                );
            } else {
                $sessions = 0;
                foreach ($v['quantity'] as $d) {
                    if (isset($d[1])) {
                        $sessions = $sessions + $d[1];
                    } else {
                        if (is_array($d)) {
                            foreach ($d as $dd) {
                                $sessions = $sessions + $dd[1];
                            }
                        } else {
                            $sessions = $sessions + $d;
                        }
                    }
                }
                $datasSession[] = array(
                    'value' => $v['value'],
                    'quantity' => $sessions,
                );
            }
        }

        $datas = array();
        foreach ($datasUser as $dataUser) {
            $datas[$dataUser['value']] = array(
                'value' => $dataUser['value'],
                'quantityUser' => $dataUser['quantity'],
                'quantitySession' => 0,
            );
        }
        foreach ($datasSession as $dataSession) {
            if (isset($datas[$dataSession['value']])) {
                $datas[$dataSession['value']]['quantitySession'] = $dataSession['quantity'];
            } else {
                $datas[$dataSession['value']] = array(
                    'value' => $dataSession['value'],
                    'quantityUser' => 0,
                    'quantitySession' => $dataSession['quantity'],
                );
            }
        }
        ksort($datas);
        $datasReturn = array();
        foreach ($datas as $data) {
            $datasReturn[] = $data;
        }
        return new JsonResponse($datasReturn);
    }

    public function generalChart4Action(Request $request)
    {
        $analyticsService = $this->get('googleAnalytics');
        $granularity = $request->request->get('granularity');
        $rows = $analyticsService->getSessionsAndDuration($this->_getProfileId(), $request->get('dateBefore'), $request->get('dateAfter'));
        $datasGranularity = $this->_getDatas(array(), $granularity, 'getCreateAt', $request->get('dateBefore'), $request->get('dateAfter'), $rows);
        $datas = array();
        foreach ($datasGranularity as $v) {
            if (!is_array($v['quantity'])) {
                $datas[] = array(
                    'value' => $v['value'],
                    'quantity' => $v['quantity'],
                );
            } else {
                $sessions = 0;
                $durations = 0;
                foreach ($v['quantity'] as $d) {
                    if (isset($d[1]) && isset($d[2])) {
                        $sessions = $sessions + $d[1];
                        $durations = $durations + $d[2];
                    } else {
                        //if (is_array($d)) {
                        foreach ($d as $dd) {
                            $sessions = $sessions + $dd[1];
                            $durations = $durations + $dd[2];
                        }
                        //}
                    }
                }
                $datas[] = array(
                    'value' => $v['value'],
                    'quantity' => $sessions > 0 ? round(($durations / 60) / $sessions) : 0,
                );
            }
        }
        return new JsonResponse($datas);
    }

    public function generalChart5Action()
    {
        $datas = array();
        return new JsonResponse($datas);
    }

    public function generalChart6Action(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $cityhall = $this->container->get('session.community')->getCommunity();

        /** @var EventRepository $eventRepository */
        $eventRepository =$em->getRepository('AppBundle:Event');

        $events = $eventRepository->search(false, array(), $cityhall, $request->get('type'), null, null, null, null, $request->get('dateBefore'), $request->get('dateAfter'), null, null, null, $request->get('association'), $request->get('merchant'), null);
        $granularity = $request->request->get('granularity');
        $eventId = $request->request->get('event');
        $analyticsService = $this->get('googleAnalytics');
        $rows = $analyticsService->getCustomEvent($this->_getProfileId(), $request->get('dateBefore'), $request->get('dateAfter'));
        $rowsClickDetails = array();
        $rowsClickAgenda = array();
        $datasDetails = array();
        $datasAgenda = array();
        foreach ($rows as $row) {
            if (stripos($row[1], 'eventDetails-') !== false) {
                $explode = explode('-', $row[1]);
                if (isset($explode[1])) {
                    if ($eventId) {
                        if ($explode[1] == $eventId) {
                            $rowsClickDetails[] = $row;
                        }
                    } else {
                        foreach ($events as $event) {
                            if ($event->getId() == $explode[1]) {
                                $rowsClickDetails[] = $row;
                                break;
                            }
                        }
                    }
                }
            } elseif ($row[1] == 'agenda') {
                $rowsClickAgenda[] = $row;
            }
        }
        $datasGranularityDetails = $this->_getDatas(array(), $granularity, 'getCreateAt', $request->get('dateBefore'), $request->get('dateAfter'), $rowsClickDetails);
        $datasGranularityAgenda = $this->_getDatas(array(), $granularity, 'getCreateAt', $request->get('dateBefore'), $request->get('dateAfter'), $rowsClickAgenda);


        foreach ($datasGranularityDetails as $v) {
            if (!is_array($v['quantity'])) {
                $datasDetails[] = array(
                    'value' => $v['value'],
                    'quantity' => $v['quantity'],
                );
            } else {
                $clicks = 0;
                foreach ($v['quantity'] as $d) {
                    if (isset($d[2])) {
                        $clicks = $clicks + $d[2];
                    } else {
                        if (is_array($d)) {
                            foreach ($d as $dd) {
                                $clicks = $clicks + $dd[2];
                            }
                        } else {
                            $clicks = $clicks + $d;
                        }
                    }
                }
                $datasDetails[] = array(
                    'value' => $v['value'],
                    'quantity' => $clicks,
                );
            }
        }
        foreach ($datasGranularityAgenda as $v) {
            if (!is_array($v['quantity'])) {
                $datasAgenda[] = array(
                    'value' => $v['value'],
                    'quantity' => $v['quantity'],
                );
            } else {
                $clicks = 0;
                foreach ($v['quantity'] as $d) {
                    if (isset($d[2])) {
                        $clicks = $clicks + $d[2];
                    } else {
                        if (is_array($d)) {
                            foreach ($d as $dd) {
                                $clicks = $clicks + $dd[2];
                            }
                        } else {
                            $clicks = $clicks + $d;
                        }
                    }
                }
                $datasAgenda[] = array(
                    'value' => $v['value'],
                    'quantity' => $clicks,
                );
            }
        }


        $datas = array();
        foreach ($datasDetails as $dataDetail) {
            $datas[$dataDetail['value']] = array(
                'value' => $dataDetail['value'],
                'quantityClickAgenda' => 0,
                'quantityClickDetail' => $dataDetail['quantity'],
            );
        }
        foreach ($datasAgenda as $dataAgenda) {
            if (isset($datas[$dataAgenda['value']])) {
                $datas[$dataAgenda['value']]['quantityClickAgenda'] = $dataAgenda['quantity'];
            } else {
                $datas[$dataAgenda['value']] = array(
                    'value' => $dataAgenda['value'],
                    'quantityClickAgenda' => $dataAgenda['quantity'],
                    'quantityClickDetail' => 0,
                );
            }
        }
        ksort($datas);
        $datasReturn = array();
        foreach ($datas as $data) {
            $datasReturn[] = $data;
        }
        return new JsonResponse($datasReturn);
    }

    public function generalChart7Action()
    {
        $datas = array();
        return new JsonResponse($datas);
    }

    public function generalChart8Action(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $cityhall = $this->container->get('session.community')->getCommunity();

        /** @var UserRepository $userRepository */
        $userRepository = $em->getRepository('UserBundle:User');

        $users = $userRepository->search(false, array(), $cityhall, array('ROLE_CITIZEN'), null, null, null, null, null, null, $request->get('dateBefore'), $request->get('dateAfter'));
        $roles = array();
        foreach ($users as $user) {
            $countRole = 0;
            foreach ($user->getAssociationsAdmin() as $associationAdmin) {
                if (is_object($associationAdmin)) {
                    $countRole++;
                }
            }
            foreach ($user->getAssociationsSuAdmin() as $associationSuAdmin) {
                if (is_object($associationSuAdmin)) {
                    $countRole++;
                }
            }
            foreach ($user->getMerchantsAdmin() as $merchantAdmin) {
                if (is_object($merchantAdmin)) {
                    $countRole++;
                }
            }
            foreach ($user->getMerchantsSuAdmin() as $merchantSuAdmin) {
                if (is_object($merchantSuAdmin)) {
                    $countRole++;
                }
            }

            if (!isset($roles[$countRole])) {
                $roles[$countRole] = 1;
            } else {
                $roles[$countRole] = $roles[$countRole] + 1;
            }
        }
        $datas = array();
        foreach ($roles as $k => $v) {
            $datas[] = array('value' => $k . ' roles', 'quantity' => $v);
        }


        return new JsonResponse($datas);
    }

    public function generalChart9Action(Request $request)
    {
        $analyticsService = $this->get('googleAnalytics');
        $rows = $analyticsService->getDeviceType($this->_getProfileId(), $request->get('dateBefore'), $request->get('dateAfter'));
        $datas = array();
        foreach ($rows as $row) {
            $datas[] = array(
                'value' => $row[0],
                'quantity' => $row[1],
            );
        }
        return new JsonResponse($datas);
    }

    public function generalChart10Action(Request $request)
    {
        $analyticsService = $this->get('googleAnalytics');
        $granularity = $request->request->get('granularity');
        $rows = $analyticsService->getSessionsAndUser($this->_getProfileId(), $request->get('dateBefore'), $request->get('dateAfter'));
        $datasGranularity = $this->_getDatas(array(), $granularity, 'getCreateAt', $request->get('dateBefore'), $request->get('dateAfter'), $rows);
        $datas = array();
        foreach ($datasGranularity as $v) {
            if (!is_array($v['quantity'])) {
                $datas[] = array(
                    'value' => $v['value'],
                    'quantity' => $v['quantity'],
                );
            } else {
                $sessions = 0;
                $users = 0;
                foreach ($v['quantity'] as $d) {
                    if (isset($d[1]) && isset($d[2])) {
                        $sessions = $sessions + $d[1];
                        $users = $users + $d[2];
                    } else {
                        if (is_array($d)) {
                            foreach ($d as $dd) {
                                $sessions = $sessions + $dd[1];
                                $users = $users + $dd[2];
                            }
                        }
                    }
                }
                $datas[] = array(
                    'value' => $v['value'],
                    'quantity' => $users > 0 ? round($sessions / $users) : 0,
                );
            }
        }
        return new JsonResponse($datas);
    }

    public function generalChart11Action(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $cityhall = $this->container->get('session.community')->getCommunity();
        $categories = $request->get('categories');

        /** @var AssociationRepository $associationRepository */
        $associationRepository = $em->getRepository('AppBundle:Association');

        $associations = $associationRepository->search(false, array(), $cityhall, $request->get('dateBefore'), $request->get('dateAfter'), null, null, null, null, null, $categories);
        $granularity = $request->request->get('granularity');
        $datas = $this->_getDatas($associations, $granularity, 'getCreateAt', $request->get('dateBefore'), $request->get('dateAfter'));
        return new JsonResponse($datas);
    }

    public function generalChart12Action(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $cityhall = $this->container->get('session.community')->getCommunity();
        $categories = $request->get('categories');

        /** @var MerchantRepository $merchantRepository */
        $merchantRepository = $em->getRepository('AppBundle:Merchant');

        $merchants = $merchantRepository->search(false, array(), $cityhall, $request->get('dateBefore'), $request->get('dateAfter'), null, null, null, null, null, $categories);
        $granularity = $request->request->get('granularity');
        $datas = $this->_getDatas($merchants, $granularity, 'getCreateAt', $request->get('dateBefore'), $request->get('dateAfter'));
        return new JsonResponse($datas);
    }

    public function generalChart13Action()
    {
        $datas = array();
        return new JsonResponse($datas);
    }

    public function generalChart14Action(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $cityhall = $this->container->get('session.community')->getCommunity();

        /** @var EventRepository $eventRepository */
        $eventRepository = $em->getRepository('AppBundle:Event');

        $events = $eventRepository->count($cityhall, null, null, null, null, null, $request->get('dateBefore'), $request->get('dateAfter'), null, null);
        $analyticsService = $this->get('googleAnalytics');
        $rows = $analyticsService->getCustomEvent($this->_getProfileId(), $request->get('dateBefore'), $request->get('dateAfter'));
        $customEventCreate = 0;
        foreach ($rows as $row) {
            if ($row[1] == 'createEvent') {
                $customEventCreate = $customEventCreate + $row[2];
            }
        }
        $percent = $events > 0 ? $customEventCreate / $events : 0;
        return new JsonResponse(array('percent' => $percent));
    }

    public function contentAction()
    {
        $cityhall = $this->container->get('session.community')->getCommunity();

        /** @var Form $form */
        $form = $this->get('form.factory')->create(StatFilterType::class);
        $em = $this->getDoctrine()->getManager();

        /** @var EventRepository $eventRepository */
        $eventRepository = $em->getRepository('AppBundle:Event');
        /** @var CategoryRepository $categoryRepository */
        $categoryRepository = $em->getRepository('AppBundle:Category');
        /** @var AssociationRepository $associationRepository */
        $associationRepository = $em->getRepository('AppBundle:Association');
        /** @var ArticleRepository $articleRepository */
        $articleRepository = $em->getRepository('AppBundle:Article');
        /** @var MerchantRepository $merchantRepository */
        $merchantRepository = $em->getRepository('AppBundle:Merchant');

        $categories = $categoryRepository->findAll();
        $associations = $associationRepository->search(false, array(), $cityhall, null, null, null, null, null, null);
        $merchants = $merchantRepository->search(false, array(), $cityhall, null, null, null, null, null, null);
        $events = $eventRepository->search(false, array(), $cityhall, null, null, null, null, null, null, null, null, null, null, null, null, null);
        $articles = $articleRepository->search(false, array(), $cityhall, null, null, null, null, null, null, null, null);
        return $this->render('AppBundle:Stat:content.html.twig', array(
            'form' => $form->createView(),
            'categories' => $categories,
            'associations' => $associations,
            'merchants' => $merchants,
            'events' => $events,
            'articles' => $articles,
        ));
    }

    public function contentChart1Action(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $cityhall = $this->container->get('session.community')->getCommunity();

        /** @var EventRepository $eventRepository */
        $eventRepository = $em->getRepository('AppBundle:Event');

        $events = $eventRepository->search(false, array(), $cityhall, $request->get('type'), null, null, null, null, $request->get('dateBefore'), $request->get('dateAfter'), null, null, null, $request->get('association'), $request->get('merchant'), $request->request->get('volunteer'));
        $granularity = $request->request->get('granularity');
        $datas = $this->_getDatas($events, $granularity, 'getCreateAt', $request->get('dateBefore'), $request->get('dateAfter'));
        return new JsonResponse($datas);
    }

    public function contentChart2Action(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $cityhall = $this->container->get('session.community')->getCommunity();
        $startAt = $request->get('dateBefore');
        if ($startAt == null) {
            $d = new \DateTime('now');
            $startAt = $d->format('d/m/Y H:i');
        }


        /** @var EventRepository $eventRepository */
        $eventRepository = $em->getRepository('AppBundle:Event');

        $events = $eventRepository->search(false, array(), $cityhall, $request->get('type'), null, null, null, null, null, null, $startAt, $request->get('dateAfter'), null, $request->get('association'), $request->get('merchant'), $request->request->get('volunteer'));
        $granularity = $request->request->get('granularity');
        $endAt = null;
        if ($request->get('dateAfter') == null) {
            $maxDate = 0;
            foreach ($events as $event) {
                if ($event->getStartAt() && $event->getStartAt()->getTimestamp() > $maxDate) {
                    $maxDate = $event->getStartAt()->getTimestamp();
                }
            }
            if ($maxDate) {
                $endAt = \DateTime::createFromFormat('d/m/Y H:i', $maxDate);
            }
        }
        $datas = $this->_getDatas($events, $granularity, 'getStartAt', $startAt, $endAt);
        return new JsonResponse($datas);
    }

    public function contentChart3Action(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $cityhall = $this->container->get('session.community')->getCommunity();


        /** @var EventRepository $eventRepository */
        $eventRepository = $em->getRepository('AppBundle:Event');

        $events = $eventRepository->search(false, array(), $cityhall, $request->get('type'), null, null, null, null, $request->get('dateBefore'), $request->get('dateAfter'), null, null, null, $request->get('association'), $request->get('merchant'), null);
        $granularity = $request->request->get('granularity');
        $eventId = $request->request->get('event');
        $analyticsService = $this->get('googleAnalytics');
        $rows = $analyticsService->getCustomEvent($this->_getProfileId(), $request->get('dateBefore'), $request->get('dateAfter'));
        $rowsClickDetails = array();
        foreach ($rows as $row) {
            if (stripos($row[1], 'eventDetails-') !== false) {
                $explode = explode('-', $row[1]);
                if (isset($explode[1])) {
                    if ($eventId) {
                        if ($explode[1] == $eventId) {
                            $rowsClickDetails [] = $row;
                        }
                    } else {
                        foreach ($events as $event) {
                            if ($event->getId() == $explode[1]) {
                                $rowsClickDetails [] = $row;
                                break;
                            }
                        }
                    }
                }
            }
        }
        $datasGranularity = $this->_getDatas(array(), $granularity, 'getCreateAt', $request->get('dateBefore'), $request->get('dateAfter'), $rowsClickDetails);
        $datas = array();
        foreach ($datasGranularity as $v) {
            if (!is_array($v['quantity'])) {
                $datas[] = array(
                    'value' => $v['value'],
                    'quantity' => $v['quantity'],
                );
            } else {
                $clicks = 0;
                foreach ($v['quantity'] as $d) {
                    if (isset($d[2])) {
                        $clicks = $clicks + $d[2];
                    } else {
                        if (is_array($d)) {
                            foreach ($d as $dd) {
                                $clicks = $clicks + $dd[2];
                            }
                        } else {
                            $clicks = $clicks + $d;
                        }
                    }
                }
                $datas[] = array(
                    'value' => $v['value'],
                    'quantity' => $clicks,
                );
            }
        }
        return new JsonResponse($datas);
    }

    public function contentChart4Action(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $cityhall = $this->container->get('session.community')->getCommunity();


        /** @var EventRepository $eventRepository */
        $eventRepository = $em->getRepository('AppBundle:Event');

        $events = $eventRepository->search(false, array(), $cityhall, $request->get('type'), null, null, null, null, $request->get('dateBefore'), $request->get('dateAfter'), null, null, null, $request->get('association'), $request->get('merchant'), null);
        $granularity = $request->request->get('granularity');
        $eventId = $request->request->get('event');
        $analyticsService = $this->get('googleAnalytics');
        $rows = $analyticsService->getCustomEvent($this->_getProfileId(), $request->get('dateBefore'), $request->get('dateAfter'));
        $rowsClickParticipation = array();
        foreach ($rows as $row) {
            if (stripos($row[1], 'participation-') !== false) {
                $explode = explode('-', $row[1]);
                if (isset($explode[1])) {
                    if ($eventId) {
                        if ($explode[1] == $eventId) {
                            $rowsClickParticipation[] = $row;
                        }
                    } else {
                        foreach ($events as $event) {
                            if ($event->getId() == $explode[1]) {
                                $rowsClickParticipation[] = $row;
                                break;
                            }
                        }
                    }
                }
            }
        }
        $datasGranularity = $this->_getDatas(array(), $granularity, 'getCreateAt', $request->get('dateBefore'), $request->get('dateAfter'), $rowsClickParticipation);
        $datas = array();
        foreach ($datasGranularity as $v) {
            if (!is_array($v['quantity'])) {
                $datas[] = array(
                    'value' => $v['value'],
                    'quantity' => $v['quantity'],
                );
            } else {
                $clicks = 0;
                foreach ($v['quantity'] as $d) {
                    if (isset($d[2])) {
                        $clicks = $clicks + $d[2];
                    } else {
                        if (is_array($d)) {
                            foreach ($d as $dd) {
                                $clicks = $clicks + $dd[2];
                            }
                        } else {
                            $clicks = $clicks + $d;
                        }
                    }
                }
                $datas[] = array(
                    'value' => $v['value'],
                    'quantity' => $clicks,
                );
            }
        }
        return new JsonResponse($datas);
    }

    public function contentChart5Action(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $cityhall = $this->container->get('session.community')->getCommunity();


        /** @var EventRepository $eventRepository */
        $eventRepository = $em->getRepository('AppBundle:Event');

        $events = $eventRepository->search(false, array(), $cityhall, $request->get('type'), null, null, null, null, $request->get('dateBefore'), $request->get('dateAfter'), null, null, null, $request->get('association'), $request->get('merchant'), null);
        $granularity = $request->request->get('granularity');
        $eventId = $request->request->get('event');
        $analyticsService = $this->get('googleAnalytics');
        $rows = $analyticsService->getCustomEvent($this->_getProfileId(), $request->get('dateBefore'), $request->get('dateAfter'));
        $rowsClickVolunteer = array();
        foreach ($rows as $row) {
            if (stripos($row[1], 'volunteer-') !== false) {
                $explode = explode('-', $row[1]);
                if (isset($explode[1])) {
                    if ($eventId) {
                        if ($explode[1] == $eventId) {
                            $rowsClickVolunteer[] = $row;
                        }
                    } else {
                        foreach ($events as $event) {
                            if ($event->getId() == $explode[1]) {
                                $rowsClickVolunteer[] = $row;
                                break;
                            }
                        }
                    }
                }
            }
        }
        $datasGranularity = $this->_getDatas(array(), $granularity, 'getCreateAt', $request->get('dateBefore'), $request->get('dateAfter'), $rowsClickVolunteer);
        $datas = array();
        foreach ($datasGranularity as $v) {
            if (!is_array($v['quantity'])) {
                $datas[] = array(
                    'value' => $v['value'],
                    'quantity' => $v['quantity'],
                );
            } else {
                $clicks = 0;
                foreach ($v['quantity'] as $d) {
                    if (isset($d[2])) {
                        $clicks = $clicks + $d[2];
                    } else {
                        if (is_array($d)) {
                            foreach ($d as $dd) {
                                $clicks = $clicks + $dd[2];
                            }
                        } else {
                            $clicks = $clicks + $d;
                        }
                    }
                }
                $datas[] = array(
                    'value' => $v['value'],
                    'quantity' => $clicks,
                );
            }
        }
        return new JsonResponse($datas);
    }

    public function contentChart6Action(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $cityhall = $this->container->get('session.community')->getCommunity();

        /** @var ArticleRepository $articleRepository */
        $articleRepository = $em->getRepository('AppBundle:Article');

        $articles = $articleRepository->search(false, array(), $cityhall, $request->get('dateBefore'), $request->get('dateAfter'), null, $request->get('type'), $request->get('enabled'), $request->get('association'), $request->get('merchant'), $request->get('user'));
        $granularity = $request->request->get('granularity');
        $datas = $this->_getDatas($articles, $granularity, 'getCreateAt', $request->get('dateBefore'), $request->get('dateAfter'));
        return new JsonResponse($datas);
    }

    public function contentChart7Action(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $cityhall = $this->container->get('session.community')->getCommunity();

        /** @var CommentRepository $commentRepository */
        $commentRepository = $em->getRepository('AppBundle:Comment');

        $comments = $commentRepository->search(false, array(), $cityhall, null, $request->get('typeComment'), null, $request->get('event'), $request->get('article'), $request->get('association'), $request->get('merchant'), $request->get('dateBefore'), $request->get('dateAfter'));
        $granularity = $request->request->get('granularity');
        $datas = $this->_getDatas($comments, $granularity, 'getCreateAt', $request->get('dateBefore'), $request->get('dateAfter'));
        return new JsonResponse($datas);
    }

    public function contentChart8Action(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $cityhall = $this->container->get('session.community')->getCommunity();

        /** @var CommentRepository $commentRepository */
        $commentRepository = $em->getRepository('AppBundle:Comment');

        $comments = $commentRepository->search(false, array(), $cityhall, null, $request->get('typeComment'), null, $request->get('event'), $request->get('article'), $request->get('association'), $request->get('merchant'), $request->get('dateBefore'), $request->get('dateAfter'));
        $granularity = $request->request->get('granularity');
        $datasComment = $this->_getDatas($comments, $granularity, 'getCreateAt', $request->get('dateBefore'), $request->get('dateAfter'));
        $analyticsService = $this->get('googleAnalytics');
        $rows = $analyticsService->getCustomEvent($this->_getProfileId(), $request->get('dateBefore'), $request->get('dateAfter'));
        $rowsClick = array();
        foreach ($rows as $row) {
            if (stripos($row[1], 'commentDeleted-') !== false) {
                $rowsClick[] = $row; //todo only in comment ? But we need set delete : true|false not remove in bdd
            }
        }
        $datasGranularity = $this->_getDatas(array(), $granularity, 'getCreateAt', $request->get('dateBefore'), $request->get('dateAfter'), $rowsClick);
        $datasDelete = array();
        foreach ($datasGranularity as $v) {
            if (!is_array($v['quantity'])) {
                $datasDelete[] = array(
                    'value' => $v['value'],
                    'quantity' => $v['quantity'],
                );
            } else {
                $clicks = 0;
                foreach ($v['quantity'] as $d) {
                    if (isset($d[2])) {
                        $clicks = $clicks + $d[2];
                    } else {
                        if (is_array($d)) {
                            foreach ($d as $dd) {
                                $clicks = $clicks + $dd[2];
                            }
                        } else {
                            $clicks = $clicks + $d;
                        }
                    }
                }
                $datasDelete[] = array(
                    'value' => $v['value'],
                    'quantity' => $clicks,
                );
            }
        }


        $datas = array();
        foreach ($datasComment as $dataComment) {
            $datas[$dataComment['value']] = array(
                'value' => $dataComment['value'],
                'quantityComment' => $dataComment['quantity'],
                'quantityCommentDelete' => 0,
            );
        }
        foreach ($datasDelete as $dataDelete) {
            if (isset($datas[$dataDelete['value']])) {
                $datas[$dataDelete['value']]['quantityCommentDelete'] = $dataDelete['quantity'];
            } else {
                $datas[$dataDelete['value']] = array(
                    'value' => $dataDelete['value'],
                    'quantityComment' => 0,
                    'quantityCommentDelete' => $dataDelete['quantity'],
                );
            }
        }
        ksort($datas);
        $datasReturn = array();
        foreach ($datas as $data) {
            $datasReturn[] = $data;
        }
        return new JsonResponse($datasReturn);
    }

    public function contentChart9Action(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $cityhall = $this->container->get('session.community')->getCommunity();

        /** @var PushRepository $pushRepository */
        $pushRepository = $em->getRepository('AppBundle:Push');

        $entities = $pushRepository->search(false, array(), $cityhall, $request->get('dateBefore'), $request->get('dateAfter'), $request->get('category'), 'event', $request->get('typeEvent'), $request->get('event'));
        $granularity = $request->request->get('granularity');
        $datasPush = $this->_getDatas($entities, $granularity, 'getCreateAt', $request->get('dateBefore'), $request->get('dateAfter'));
        $analyticsService = $this->get('googleAnalytics');
        $rows = $analyticsService->getSessions($this->_getProfileId(), $request->get('dateBefore'), $request->get('dateAfter'));
        $datasGranularity = $this->_getDatas(array(), $granularity, 'getCreateAt', $request->get('dateBefore'), $request->get('dateAfter'), $rows);
        $datasSession = array();
        foreach ($datasGranularity as $v) {
            if (!is_array($v['quantity'])) {
                $datasSession[] = array(
                    'value' => $v['value'],
                    'quantity' => $v['quantity'],
                );
            } else {
                $sessions = 0;
                foreach ($v['quantity'] as $d) {
                    if (isset($d[1])) {
                        $sessions = $sessions + $d[1];
                    } else {
                        if (is_array($d)) {
                            foreach ($d as $dd) {
                                $sessions = $sessions + $dd[1];
                            }
                        } else {
                            $sessions = $sessions + $d;
                        }
                    }
                }
                $datasSession[] = array(
                    'value' => $v['value'],
                    'quantity' => $sessions,
                );
            }
        }

        $datas = array();
        foreach ($datasPush as $dataPush) {
            $datas[$dataPush['value']] = array(
                'value' => $dataPush['value'],
                'quantityPush' => $dataPush['quantity'],
                'quantitySession' => 0,
            );
        }
        foreach ($datasSession as $dataSession) {
            if (isset($datas[$dataSession['value']])) {
                $datas[$dataSession['value']]['quantitySession'] = $dataSession['quantity'];
            } else {
                $datas[$dataSession['value']] = array(
                    'value' => $dataSession['value'],
                    'quantityPush' => 0,
                    'quantitySession' => $dataSession['quantity'],
                );
            }
        }
        ksort($datas);
        $datasReturn = array();
        foreach ($datas as $data) {
            $datasReturn[] = $data;
        }
        return new JsonResponse($datasReturn);
    }

    public function contentChart10Action(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $cityhall = $this->container->get('session.community')->getCommunity();
        $granularity = $request->request->get('granularity');
        $eventId = $request->request->get('event');

        /** @var PushRepository $pushRepository */
        $pushRepository = $em->getRepository('AppBundle:Push');

        $pushs = $pushRepository->search(false, array(), $cityhall, $request->get('dateBefore'), $request->get('dateAfter'), $request->get('category'), 'event', $request->get('typeEvent'), $eventId);
        $datasPush = $this->_getDatas($pushs, $granularity, 'getCreateAt', $request->get('dateBefore'), $request->get('dateAfter'));

        /** @var EventRepository $eventRepository */
        $eventRepository = $em->getRepository('AppBundle:Event');

        $events = $eventRepository->search(false, array(), $cityhall, $request->get('typeEvent'), null, null, null, null, $request->get('dateBefore'), $request->get('dateAfter'), null, null, null, null, null, null, $request->get('category'));
        $analyticsService = $this->get('googleAnalytics');
        $rows = $analyticsService->getCustomEvent($this->_getProfileId(), $request->get('dateBefore'), $request->get('dateAfter'));
        $rowsClickDetail = array();
        $rowsClickParticipation = array();
        $datasDetails = array();
        $datasParticipation = array();
        foreach ($rows as $row) {
            if (stripos($row[1], 'eventDetails-') !== false) {
                $explode = explode('-', $row[1]);
                if (isset($explode[1])) {
                    if ($eventId) {
                        if ($explode[1] == $eventId) {
                            $rowsClickDetail[] = $row;
                        }
                    } else {
                        foreach ($events as $event) {
                            if ($event->getId() == $explode[1]) {
                                $rowsClickDetail[] = $row;
                                break;
                            }
                        }
                    }
                }
            } elseif (stripos($row[1], 'participation-') !== false) {
                $explode = explode('-', $row[1]);
                if (isset($explode[1])) {
                    if ($eventId) {
                        if ($explode[1] == $eventId) {
                            $rowsClickParticipation[] = $row;
                        }
                    } else {
                        foreach ($events as $event) {
                            if ($event->getId() == $explode[1]) {
                                $rowsClickParticipation[] = $row;
                                break;
                            }
                        }
                    }
                }
            }
        }

        $datasGranularityDetail = $this->_getDatas(array(), $granularity, 'getCreateAt', $request->get('dateBefore'), $request->get('dateAfter'), $rowsClickDetail);
        $datasGranularityParticipation = $this->_getDatas(array(), $granularity, 'getCreateAt', $request->get('dateBefore'), $request->get('dateAfter'), $rowsClickParticipation);
        foreach ($datasGranularityDetail as $v) {
            if (!is_array($v['quantity'])) {
                $datasDetails[] = array(
                    'value' => $v['value'],
                    'quantity' => $v['quantity'],
                );
            } else {
                $clicks = 0;
                foreach ($v['quantity'] as $d) {
                    if (isset($d[2])) {
                        $clicks = $clicks + $d[2];
                    } else {
                        if (is_array($d)) {
                            foreach ($d as $dd) {
                                $clicks = $clicks + $dd[2];
                            }
                        } else {
                            $clicks = $clicks + $d;
                        }
                    }
                }
                $datasDetails[] = array(
                    'value' => $v['value'],
                    'quantity' => $clicks,
                );
            }
        }
        foreach ($datasGranularityParticipation as $v) {
            if (!is_array($v['quantity'])) {
                $datasParticipation[] = array(
                    'value' => $v['value'],
                    'quantity' => $v['quantity'],
                );
            } else {
                $clicks = 0;
                foreach ($v['quantity'] as $d) {
                    if (isset($d[2])) {
                        $clicks = $clicks + $d[2];
                    } else {
                        if (is_array($d)) {
                            foreach ($d as $dd) {
                                $clicks = $clicks + $dd[2];
                            }
                        } else {
                            $clicks = $clicks + $d;
                        }
                    }
                }
                $datasParticipation[] = array(
                    'value' => $v['value'],
                    'quantity' => $clicks,
                );
            }
        }


        $datas = array();
        foreach ($datasPush as $dataPush) {
            $datas[$dataPush['value']] = array(
                'value' => $dataPush['value'],
                'quantityPush' => $dataPush['quantity'],
                'quantityDetail' => 0,
                'quantityParticipation' => 0,
            );
        }
        foreach ($datasDetails as $dataDetails) {
            if (isset($datas[$dataDetails['value']])) {
                $datas[$dataDetails['value']]['quantityDetail'] = $dataDetails['quantity'];
            } else {
                $datas[$dataDetails['value']] = array(
                    'value' => $dataDetails['value'],
                    'quantityPush' => 0,
                    'quantityDetail' => $dataDetails['quantity'],
                    'quantityParticipation' => 0,
                );
            }
        }
        foreach ($datasParticipation as $dataParticipation) {
            if (isset($datas[$dataParticipation['value']])) {
                $datas[$dataParticipation['value']]['quantityParticipation'] = $dataParticipation['quantity'];
            } else {
                $datas[$dataParticipation['value']] = array(
                    'value' => $dataDetails['value'],
                    'quantityPush' => 0,
                    'quantityDetail' => 0,
                    'quantityParticipation' => $dataParticipation['quantity'],
                );
            }
        }
        ksort($datas);
        $datasReturn = array();
        foreach ($datas as $data) {
            $datasReturn[] = $data;
        }
        return new JsonResponse($datasReturn);
    }

    public function userAction()
    {
        /** @var Form $form */
        $form = $this->get('form.factory')->create(StatFilterType::class);
        $em = $this->getDoctrine()->getManager();
        $categories = $em->getRepository('AppBundle:Category')->findAll();
        return $this->render('AppBundle:Stat:user.html.twig', array(
            'form' => $form->createView(),
            'categories' => $categories,
        ));
    }

    public function userChart1Action(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $cityhall = $this->container->get('session.community')->getCommunity();
        $categories = $request->get('categories');

        /** @var UserRepository $userRepository */
        $userRepository = $em->getRepository('UserBundle:User');

        $users = $userRepository->search(false, array(), $cityhall, array('ROLE_CITIZEN'), null, null, null, null, null, null, $request->get('dateBefore'), $request->get('dateAfter'));
        $days = array('Lundi' => 0, 'Mardi' => 0, 'Mercredi' => 0, 'Jeudi' => 0, 'Vendredi' => 0, 'Samedi' => 0, 'Dimanche' => 0);
        foreach ($users as $user) {
            if (!empty($categories)) {
                $failedCategory = false;
                foreach ($categories as $category) {
                    $match = false;
                    foreach ($user->getInterests() as $interest) {
                        if ($interest->getId() == $category) {
                            $match = true;
                            break;
                        }
                    }
                    if (!$match) {
                        $failedCategory = true;
                        break;
                    }
                }
                if ($failedCategory) {
                    continue;
                }
            }
            if ($user->getMonday()) {
                $days['Lundi']++;
            }
            if ($user->getTuesday()) {
                $days['Mardi']++;
            }
            if ($user->getWednesday()) {
                $days['Mercredi']++;
            }
            if ($user->getThursday()) {
                $days['Jeudi']++;
            }
            if ($user->getFriday()) {
                $days['Vendredi']++;
            }
            if ($user->getSaturday()) {
                $days['Samedi']++;
            }
            if ($user->getSunday()) {
                $days['Dimanche']++;
            }
        }

        $datas = array();
        foreach ($days as $k => $v) {
            $datas[] = array('day' => $k, 'quantity' => $v);
        }
        return new JsonResponse($datas);
    }

    public function userChart2Action(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $cityhall = $this->container->get('session.community')->getCommunity();

        /** @var UserRepository $userRepository */
        $userRepository = $em->getRepository('UserBundle:User');

        $users = $userRepository->search(false, array(), $cityhall, array('ROLE_CITIZEN'), null, null, null, null, null, null, $request->get('dateBefore'), $request->get('dateAfter'));
        $interests = array();
        foreach ($users as $user) {
            foreach ($user->getInterests() as $interest) {
                if (!isset($interests[$interest->getName()])) {
                    $interests[$interest->getName()] = 1;
                } else {
                    $interests[$interest->getName()] = $interests[$interest->getName()] + 1;
                }
            }
        }

        $datas = array();
        foreach ($interests as $k => $v) {
            $datas[] = array('interest' => $k, 'quantity' => $v);
        }

        return new JsonResponse($datas);
    }

    public function userChart3Action(Request $request)
    {
        $datas = array();
        return new JsonResponse($datas);
    }

    public function userChart4Action(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $cityhall = $this->container->get('session.community')->getCommunity();

        /** @var UserRepository $userRepository */
        $userRepository = $em->getRepository('UserBundle:User');

        $users = $userRepository->search(false, array(), $cityhall, array('ROLE_CITIZEN'), null, null, null, null, null, null, $request->get('dateBefore'), $request->get('dateAfter'));
        $granularity = $request->request->get('granularity');
        $analyticsService = $this->get('googleAnalytics');
        $rows = $analyticsService->getCustomEvent($this->_getProfileId(), $request->get('dateBefore'), $request->get('dateAfter'));
        $rowsClick = array();
        foreach ($rows as $row) {
            if (stripos($row[1], 'cityChanged-') !== false) {
                $explode = explode('-', $row[1]);
                if (isset($explode[1])) {
                    foreach ($users as $user) {
                        if ($user->getId() == $explode[1]) {
                            $rowsClick[] = $row;
                            break;
                        }
                    }
                }
            }
        }
        $datasGranularity = $this->_getDatas(array(), $granularity, 'getCreateAt', $request->get('dateBefore'), $request->get('dateAfter'), $rowsClick);
        $datas = array();
        foreach ($datasGranularity as $v) {
            if (!is_array($v['quantity'])) {
                $datas[] = array(
                    'value' => $v['value'],
                    'quantity' => $v['quantity'],
                );
            } else {
                $clicks = 0;
                foreach ($v['quantity'] as $d) {
                    if (isset($d[2])) {
                        $clicks = $clicks + $d[2];
                    } else {
                        if (is_array($d)) {
                            foreach ($d as $dd) {
                                $clicks = $clicks + $dd[2];
                            }
                        } else {
                            $clicks = $clicks + $d;
                        }
                    }
                }
                $datas[] = array(
                    'value' => $v['value'],
                    'quantity' => $clicks,
                );
            }
        }
        return new JsonResponse($datas);
    }

    public function userChart5Action(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $cityhall = $this->container->get('session.community')->getCommunity();


        /** @var UserRepository $userRepository */
        $userRepository = $em->getRepository('UserBundle:User');

        $users = $userRepository->search(false, array(), $cityhall, array('ROLE_CITIZEN'), null, null, null, null, null, null, $request->get('dateBefore'), $request->get('dateAfter'));
        $cities = array();
        foreach ($users as $user) {
            if (!isset($cities[count($user->getSecondaryCities())])) {
                $cities[count($user->getSecondaryCities())] = 1;
            } else {
                $cities[count($user->getSecondaryCities())] = $cities[count($user->getSecondaryCities())] + 1;
            }
        }

        $datas = array();
        foreach ($cities as $k => $v) {
            $datas[] = array('city' => $k . ' villes secondaires', 'quantity' => $v);
        }

        return new JsonResponse($datas);
    }

    public function cityhallAction()
    {
        $em = $this->getDoctrine()->getManager();
        $cityhall = $this->container->get('session.community')->getCommunity();

        /** @var AssociationRepository $associationRepository */
        $associationRepository = $em->getRepository('AppBundle:Association');
        $associations = $associationRepository->search(false, array(), $cityhall, null, null, null, null, null, null);

        /** @var MerchantRepository $merchantRepository */
        $merchantRepository = $em->getRepository('AppBundle:Merchant');
        $merchants = $merchantRepository->search(false, array(), $cityhall, null, null, null, null, null, null);

        /** @var Form $form */
        $form = $this->get('form.factory')->create(StatFilterType::class);
        return $this->render('AppBundle:Stat:cityhall.html.twig', array(
            'form' => $form->createView(),
            'associations' => $associations,
            'merchants' => $merchants,
        ));
    }

    public function cityhallChart1Action(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $cityhall = $this->container->get('session.community')->getCommunity();


        /** @var WorkRepository $workRepository */
        $workRepository = $em->getRepository('AppBundle:Work');

        $works = $workRepository->search(false, array(), $cityhall, $request->get('dateBefore'), $request->get('dateAfter'), null, null);
        $granularity = $request->request->get('granularity');
        $datas = $this->_getDatas($works, $granularity, 'getCreateAt', $request->get('dateBefore'), $request->get('dateAfter'));
        return new JsonResponse($datas);
    }

    public function cityhallChart2Action(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $cityhall = $this->container->get('session.community')->getCommunity();

        /** @var EventRepository $eventRepository */
        $eventRepository = $em->getRepository('AppBundle:Event');

        $events = $eventRepository->search(false, array(), $cityhall, 'cityhall', null, null, null, null, null, null, $request->get('dateBefore'), $request->get('dateAfter'));
        $granularity = $request->request->get('granularity');
        $datas = $this->_getDatas($events, $granularity, 'getStartAt', $request->get('dateBefore'), $request->get('dateAfter'));
        return new JsonResponse($datas);
    }

    public function cityhallChart3Action(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $cityhall = $this->container->get('session.community')->getCommunity();

        /** @var PushRepository $pushRepository */
        $pushRepository = $em->getRepository('AppBundle:Push');

        $pushs = $pushRepository->search(false, array(), $cityhall, $request->get('dateBefore'), $request->get('dateAfter'), null, 'cityhall', null);
        $granularity = $request->request->get('granularity');
        $datas = $this->_getDatas($pushs, $granularity, 'getSendAt', $request->get('dateBefore'), $request->get('dateAfter'));
        return new JsonResponse($datas);
    }

    public function cityhallChart4Action(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $cityhall = $this->container->get('session.community')->getCommunity();

        /** @var ArticleRepository $articleRepository */
        $articleRepository = $em->getRepository('AppBundle:Article');

        $projects = $articleRepository->search(false, array(), $cityhall, $request->get('dateBefore'), $request->get('dateAfter'), null, 'cityhall', null);
        $granularity = $request->request->get('granularity');
        $datas = $this->_getDatas($projects, $granularity, 'getCreateAt', $request->get('dateBefore'), $request->get('dateAfter'));
        return new JsonResponse($datas);
    }

    public function cityhallChart5Action(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $cityhall = $this->container->get('session.community')->getCommunity();


        /** @var EventRepository $eventRepository */
        $eventRepository = $em->getRepository('AppBundle:Event');

        $events = $eventRepository->search(false, array(), $cityhall, $request->get('type'), null, null, null, null, $request->get('dateBefore'), $request->get('dateAfter'), null, null, null, $request->get('association'), $request->get('merchant'), null);
        $eventsModerateTimeTotal = 0;
        $eventsModerateCount = 0;
        foreach ($events as $event) {
            if (($event->getModerate() == 'accepted' || $event->getModerate() == 'refuse') && $event->getModerateAt()) {
                $eventsModerateTimeTotal = $eventsModerateTimeTotal + ($event->getModerateAt()->getTimestamp() - $event->getCreateAt()->getTimestamp());
                $eventsModerateCount++;
            }
        }
        $averageTime = 0;
        if ($eventsModerateTimeTotal > 0) {
            $averageTime = $eventsModerateTimeTotal / $eventsModerateCount;
            switch ($request->get('time')) {
                case 'second':
                    $averageTime = round($averageTime) . ' seconde' . (round($averageTime > 1) ? 's' : '');
                    break;
                case 'minute':
                    $averageTime = round($averageTime / 60) . ' minute' . (round(($averageTime / 60)) > 1 ? 's' : '');
                    break;
                case 'hour':
                    $averageTime = round($averageTime / 3600) . ' heure' . (round(($averageTime / 60)) > 1 ? 's' : '');
                    break;
                case 'day':
                    $averageTime = round($averageTime / 86400) . ' jour' . (round(($averageTime / 60)) > 1 ? 's' : '');
                    break;
                case 'month':
                    $averageTime = round($averageTime / 2627999) . ' mois';
                    break;
                case 'year':
                    $averageTime = round($averageTime / 31535965) . ' année' . (round(($averageTime / 60)) > 1 ? 's' : '');
                    break;
                default:
                    break;
            }
        }
        return new JsonResponse(array('time' => $averageTime));
    }

    public function cityhallChart6Action(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $cityhall = $this->container->get('session.community')->getCommunity();

        /** @var ReportingRepository $reportingRepository */
        $reportingRepository = $em->getRepository('AppBundle:Reporting');

        $entities = $reportingRepository->search(false, array(), $cityhall, $request->get('dateBefore'), $request->get('dateAfter'), null, null, null);
        $moderateTimeTotal = 0;
        $moderateCount = 0;
        foreach ($entities as $entity) {
            if (($entity->getModerate() == 'on' || $entity->getModerate() == 'off') && $entity->getModerateAt()) {
                $moderateTimeTotal = $moderateTimeTotal + ($entity->getModerateAt()->getTimestamp() - $entity->getCreateAt()->getTimestamp());
                $moderateCount++;
            }
        }
        $averageTime = 0;
        if ($moderateTimeTotal > 0) {
            $averageTime = $moderateTimeTotal / $moderateCount;
            switch ($request->get('time')) {
                case 'second':
                    $averageTime = round($averageTime) . ' seconde' . (round($averageTime > 1) ? 's' : '');
                    break;
                case 'minute':
                    $averageTime = round($averageTime / 60) . ' minute' . (round(($averageTime / 60)) > 1 ? 's' : '');
                    break;
                case 'hour':
                    $averageTime = round($averageTime / 3600) . ' heure' . (round(($averageTime / 60)) > 1 ? 's' : '');
                    break;
                case 'day':
                    $averageTime = round($averageTime / 86400) . ' jour' . (round(($averageTime / 60)) > 1 ? 's' : '');
                    break;
                case 'month':
                    $averageTime = round($averageTime / 2627999) . ' mois';
                    break;
                case 'year':
                    $averageTime = round($averageTime / 31535965) . ' année' . (round(($averageTime / 60)) > 1 ? 's' : '');
                    break;
                default:
                    break;
            }
        }
        return new JsonResponse(array('time' => $averageTime));
    }

    public function cityhallChart7Action(Request $request)
    {
        $analyticsService = $this->get('googleAnalytics');
        $granularity = $request->request->get('granularity');
        $rows = $analyticsService->getSessionsAndDuration($this->_getProfileId(true), $request->get('dateBefore'), $request->get('dateAfter'));
        $datasGranularity = $this->_getDatas(array(), $granularity, 'getCreateAt', $request->get('dateBefore'), $request->get('dateAfter'), $rows);
        $datas = array();
        foreach ($datasGranularity as $v) {
            if (!is_array($v['quantity'])) {
                $datas[] = array(
                    'value' => $v['value'],
                    'quantitySessions' => 0,
                    'quantitySessionsDuration' => 0,
                );
            } else {
                $sessions = 0;
                $durations = 0;
                foreach ($v['quantity'] as $d) {
                    if (isset($d[1]) && isset($d[2])) {
                        $sessions = $sessions + $d[1];
                        $durations = $durations + $d[2];
                    } else {
                        foreach ($d as $dd) {
                            $sessions = $sessions + $dd[1];
                            $durations = $durations + $dd[2];
                        }
                    }
                }
                $datas[] = array(
                    'value' => $v['value'],
                    'quantitySessions' => $sessions,
                    'quantitySessionsDuration' => $sessions > 0 ? round(($durations / 60) / $sessions) : 0,
                );
            }
        }
        return new JsonResponse($datas);
    }

    public function cityhallChart8Action()
    {
        $datas = array();
        return new JsonResponse($datas);
    }

    /**
     * @param $entities
     * @param $granularity
     * @param string $getterName
     * @param null $startAt
     * @param null $endAt
     * @param bool|array $rowsGoogle
     * @return array
     */
    private function _getDatas($entities, $granularity, $getterName = 'getCreateAt', $startAt = null, $endAt = null, $rowsGoogle = false)
    {
        $dates = array();
        foreach ($entities as $entity) {
            if ($granularity == 'day' || $granularity == 'month') {
                if (!isset($dates[$entity->{$getterName}()->format('Y-m-d')])) {
                    $dates[$entity->{$getterName}()->format('Y-m-d')] = 1;
                } else {
                    $dates[$entity->{$getterName}()->format('Y-m-d')] = $dates[$entity->{$getterName}()->format('Y-m-d')] + 1;
                }
            } elseif ($granularity == 'year') {
                if (!isset($dates[$entity->{$getterName}()->format('Y')])) {
                    $dates[$entity->{$getterName}()->format('Y')] = 1;
                } else {
                    $dates[$entity->{$getterName}()->format('Y')] = $dates[$entity->{$getterName}()->format('Y')] + 1;
                }
            }
        }
        if ($rowsGoogle) {
            foreach ($rowsGoogle as $rowGoogle) {
                if ($granularity == 'day' || $granularity == 'month') {
                    $d = \DateTime::createFromFormat('Ymd', $rowGoogle[0]);
                    if (!isset($dates[$d->format('Y-m-d')])) {
                        $dates[$d->format('Y-m-d')] = array();
                    }
                    $dates[$d->format('Y-m-d')][] = $rowGoogle;
                } elseif ($granularity == 'year') {
                    $d = \DateTime::createFromFormat('Ymd', $rowGoogle[0]);
                    if (!isset($dates[$d->format('Y')])) {
                        $dates[$d->format('Y')] = array();
                    }
                    $dates[$d->format('Y')][] = $rowGoogle;
                }
            }
        }

        $min = 0;
        $max = 0;
        foreach ($dates as $k => $v) {
            if ($granularity == 'day' || $granularity == 'month') {
                $d = \DateTime::createFromFormat('Y-m-d', $k);
                if ($d) {
                    if ($min > $d->getTimestamp() || $min == 0) {
                        $min = $d->getTimestamp();
                    }
                    if ($max < $d->getTimestamp() || $max == 0) {
                        $max = $d->getTimestamp();
                    }
                }
            } elseif ($granularity == 'year') {
                $d = \DateTime::createFromFormat('Y', $k);
                if ($d) {
                    if ($min > $d->getTimestamp() || $min == 0) {
                        $min = $d->getTimestamp();
                    }
                    if ($max < $d->getTimestamp() || $max == 0) {
                        $max = $d->getTimestamp();
                    }
                }
            }
        }
        if ($startAt == null) {
            $startAt = '01/09/2017 00:00';
            if (\DateTime::createFromFormat('d/m/Y H:i', $startAt)->getTimestamp() > $min && $min != 0) {
                $startAt = \DateTime::createFromFormat('U', $min)->format('d/m/Y H:i');
            }
        }
        if ($endAt == null) {
            $date = new \DateTime('now');
            $endAt = $date->format('d/m/Y H:i');
            if (\DateTime::createFromFormat('d/m/Y H:i', $endAt)->getTimestamp() < $max && $max != 0) {
                $endAt = \DateTime::createFromFormat('U', $max)->format('d/m/Y H:i');
            }
        }


        $minDate = \DateTime::createFromFormat('d/m/Y H:i', $startAt);
        $maxDate = \DateTime::createFromFormat('d/m/Y H:i', $endAt);
        if ($granularity == 'day' || $granularity == 'month') {
            if (!isset($dates[$minDate->format('Y-m-d')])) {
                $dates[$minDate->format('Y-m-d')] = 0;
            }
        } elseif ($granularity == 'year') {
            if (!isset($dates[$minDate->format('Y')])) {
                $dates[$minDate->format('Y')] = 0;
            }
        }
        $dateCurrent = $minDate;
        $i = 0;
        while ($minDate->format('Y-m-d') < $maxDate->format('Y-m-d')) {
            $dateCurrent->modify('+1 day');
            if ($granularity == 'day' || $granularity == 'month') {
                if (!isset($dates[$dateCurrent->format('Y-m-d')])) {
                    $dates[$dateCurrent->format('Y-m-d')] = 0;
                }
            } elseif ($granularity == 'year') {
                if (!isset($dates[$dateCurrent->format('Y')])) {
                    $dates[$dateCurrent->format('Y')] = 0;
                }
            }

            $i++;
        }
        ksort($dates);


        if ($granularity == 'day') {
            ksort($dates);
        } elseif ($granularity == 'month') {
            ksort($dates);
            $months = array();
            foreach ($dates as $date => $count) {
                $dateObject = new \DateTime($date);
                $firstDayOfMonth = $dateObject->modify('first day of this month');
                $dateObjectBis = new \DateTime($date);
                $lastDayOfMonth = $dateObjectBis->modify('last day of this month');
                if (!isset($months[$firstDayOfMonth->format('Y-m-d') . ' - ' . $lastDayOfMonth->format('Y-m-d')])) {
                    if (is_array($count) || $rowsGoogle != false) {
                        $months[$firstDayOfMonth->format('Y-m-d') . ' - ' . $lastDayOfMonth->format('Y-m-d')] = array();
                    } else {
                        $months[$firstDayOfMonth->format('Y-m-d') . ' - ' . $lastDayOfMonth->format('Y-m-d')] = 0;
                    }
                }

                if (is_array($count) || $rowsGoogle != false) {
                    $months[$firstDayOfMonth->format('Y-m-d') . ' - ' . $lastDayOfMonth->format('Y-m-d')][] = $count;
                } else {
                    $months[$firstDayOfMonth->format('Y-m-d') . ' - ' . $lastDayOfMonth->format('Y-m-d')] = $months[$firstDayOfMonth->format('Y-m-d') . ' - ' . $lastDayOfMonth->format('Y-m-d')] + $count;
                }
            }
            ksort($months);
            $dates = $months;
        } elseif ($granularity == 'year') {
            ksort($dates);
        }


        $datas = array();
        foreach ($dates as $k => $v) {
            $datas[] = array('value' => $k, 'quantity' => $v);
        }
        return $datas;
    }

    private function _getProfileId($bo = false, $web = false)
    {
        $profileId = $web ? $this->container->getParameter('googleanalytics_application_profile_id_website') : $this->container->getParameter('googleanalytics_application_profile_id_mobile');
        if ($bo) {
            $profileId = $this->container->getParameter('googleanalytics_backoffice_profile_id');
        }

        /** @var Community $cityhall */
        $cityhall = $this->container->get('session.community')->getCommunity();
        if ($cityhall) {
            if ($bo) {
                $profileId = $cityhall->getGaBackofficeProfileID();
            } else {
                if ($web) {
                    $profileId = $cityhall->getGaApplicationProfileIDWEB();
                } else {
                    $profileId = $cityhall->getGaApplicationProfileIDMOBILE();
                }
            }
        }
        return $profileId;
    }
}
