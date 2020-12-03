<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Community;
use AppBundle\Entity\Event;
use AppBundle\Entity\Push;
use AppBundle\Repository\ArticleRepository;
use AppBundle\Repository\AssociationRepository;
use AppBundle\Repository\CommentRepository;
use AppBundle\Repository\EventRepository;
use AppBundle\Repository\MerchantRepository;
use AppBundle\Repository\PushRepository;
use Google\GeolocationBundle\Geolocation\GeolocationApi;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use AppBundle\Form\ProfilPasswordType;
use AppBundle\Form\ProfilType;
use UserBundle\Entity\User;
use UserBundle\Repository\UserRepository;

class DefaultController extends Controller
{
    public function indexAction()
    {

        $em = $this->getDoctrine()->getManager();
        $cityhall = $this->container->get('session.community')->getCommunity();

        /** @var EventRepository $eventRepository */
        $eventRepository = $em->getRepository('AppBundle:Event');
        /** @var CommentRepository $commentRepository */
        $commentRepository = $em->getRepository('AppBundle:Comment');
        /** @var AssociationRepository $associationRepository */
        $associationRepository = $em->getRepository('AppBundle:Association');
        /** @var ArticleRepository $articleRepository */
        $articleRepository = $em->getRepository('AppBundle:Article');
        /** @var UserRepository $userRepository */
        $userRepository = $em->getRepository('UserBundle:User');
        /** @var MerchantRepository $merchantRepository */
        $merchantRepository = $em->getRepository('AppBundle:Merchant');

        $eventsCityhall = $eventRepository->search(false, null, $cityhall, null, null, true, 'accepted', null, null, null, null, null);
        $countParticipant = 0;

        /** @var Event $event */
        foreach ($eventsCityhall as $event) {
            $countParticipant += count($event->getParticipants());
        }
        $countComment = $commentRepository->count($cityhall);
        $countEvent = $eventRepository->count($cityhall);

        $countUser = $userRepository->count($cityhall, array(), null, null, null, null, null, null, null, null);
        $date = new \DateTime();
        $date->modify('-30 day');
        $dateBefore = $date->format('d-m-Y');
        $countEvents = $eventRepository->count($cityhall, null, null, null, null, null, $dateBefore, null, null, null);
        $countArticle = $articleRepository->count($cityhall, $dateBefore, null, null, null, null);

        $countEventsWait = $eventRepository->count($cityhall, null, null, null, 'wait', null, null, null, null, null);
        $eventsWait = $eventRepository->search(0, array('createAt' => 'DESC'), $cityhall, null, null, null, 'wait', null, null, null, null, null, 3);
        $countAssociationsWait = $associationRepository->count($cityhall, null, null, null, null, 'wait', null);
        $associationsWait = $associationRepository->search(0, array('createAt' => 'DESC'), $cityhall, null, null, null, null, 'wait', null, 3);
        $countMerchantsWait = $merchantRepository->count($cityhall, null, null, null, null, 'wait', null);
        $merchantsWait = $merchantRepository->search(0, array('createAt' => 'DESC'), $cityhall, null, null, null, null, 'wait', null, 3);

        $comments = $commentRepository->search(false, array('createAt' => 'DESC'), $cityhall, null, null, null, null, null, null, null);
        $commentsUnread = array();
        foreach ($comments as $c) {
            if (!$this->getUser()->isReadComment($c->getId())) {
                $commentsUnread[] = $c;
            }
        }


        $commentReturn = array();
        foreach ($commentsUnread as $cc) {
            $match = false;
            if ($cc->getEvent()) {
                if (!empty($commentReturn)) {
                    foreach ($commentReturn as $k => $v) {
                        if ($v['type'] == 'event' && $v['entityId'] == $cc->getEvent()->getId()) {
                            $match = true;
                            break;
                        }
                    }
                }
                if ($match) {
                    continue;
                }


                if (count($commentReturn) < 5) {
                    $data = array('countComment' => 0, 'entity' => $cc->getEvent(), 'entityId' => $cc->getEvent()->getId(), 'type' => 'event', 'comments' => array());
                    foreach ($commentsUnread as $ccc) {
                        if ($ccc->getEvent() && $ccc->getEvent()->getId() == $cc->getEvent()->getId()) {
                            $data['countComment'] ++;
                            if (count($data['comments']) < 5) {
                                $data['comments'][] = $ccc;
                            }
                        }
                    }
                    $commentReturn[] = $data;
                }
            } elseif ($cc->getArticle()) {
                if (!empty($commentReturn)) {
                    foreach ($commentReturn as $k => $v) {
                        if ($v['type'] == 'article' && $v['entityId'] == $cc->getArticle()->getId()) {
                            $match = true;
                            break;
                        }
                    }
                }
                if ($match) {
                    continue;
                }


                if (count($commentReturn) < 5) {
                    $data = array('countComment' => 0, 'entity' => $cc->getArticle(), 'entityId' => $cc->getArticle()->getId(), 'type' => 'article', 'comments' => array());
                    foreach ($commentsUnread as $ccc) {
                        if ($ccc->getArticle() && $ccc->getArticle()->getId() == $cc->getArticle()->getId()) {
                            $data['countComment'] ++;
                            if (count($data['comments']) < 5) {
                                $data['comments'][] = $ccc;
                            }
                        }
                    }
                    $commentReturn[] = $data;
                }
            }
        }
        //todo set read by user ?

        $dateStart = new \DateTime('now');
        $dateStart->setTime(00, 00, 00);
        $dateEnd = new \DateTime('now');
        $dateEnd->modify('+7 days');
        $dateEnd->setTime(23, 59, 59);

        /** @var PushRepository $pushRepository */
        $pushRepository = $em->getRepository('AppBundle:Push');

        $pushs = $pushRepository->findAllByDate($cityhall, $dateStart, $dateEnd);
        $pushsData = array();
        /** @var Push $push */
        foreach ($pushs as $push) {
            if (!isset($pushsData[$push->getSendAt()->format('d/m/Y')])) {
                $pushsData[$push->getSendAt()->format('d/m/Y')] = array();
            }
            $pushsData[$push->getSendAt()->format('d/m/Y')][] = $push;
        }
        ksort($pushsData);


       
        return $this->render('AppBundle:Default:index.html.twig', array(
                    'countParticipant' => $countParticipant,
                    'countComment' => $countComment,
                    'countEvent' => $countEvent,
                    'countUser' => $countUser,
                    'countEvents' => $countEvents,
                    'countArticle' => $countArticle,
                    'countEventsWait' => $countEventsWait,
                    'eventsWait' => $eventsWait,
                    'countAssociationsWait' => $countAssociationsWait,
                    'associationsWait' => $associationsWait,
                    'countMerchantsWait' => $countMerchantsWait,
                    'merchantsWait' => $merchantsWait,
                    'comments' => $commentReturn,
                    'countCommentsUnread' => count($commentsUnread),
                    'pushs' => $pushsData,
                    'community'=>$cityhall
        ));
    }

    public function profilAction(Request $request)
    {
        $entity = $this->getUser();
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        /** @var Form $form */
        $form = $this->get('form.factory')->create(ProfilType::class, $entity);

        if ($form->handleRequest($request)->isValid()) {
            $userManager = $this->container->get('fos_user.user_manager');
            $userManager->updateUser($entity);
            $this->get('session')->getFlashBag()->add('success', "Modifications enregistrées avec succès");
            return $this->redirect($this->generateUrl('app_profil'));
        }

        return $this->render('AppBundle:Default:profil.html.twig', array(
                    'user' => $entity,
                    'form' => $form->createView(),
        ));
    }

    public function passwordAction(Request $request)
    {
        $entity = $this->getUser();
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        /** @var Form $formPass */
        $formPass = $this->get('form.factory')->create(ProfilPasswordType::class, $entity);
        if ($formPass->handleRequest($request)->isValid()) {
            $userManager = $this->container->get('fos_user.user_manager');
            $userManager->updateUser($entity);
            $this->get('session')->getFlashBag()->add('success', "Modifications enregistrées avec succès");
            return $this->redirect($this->generateUrl('app_password'));
        }

        return $this->render('AppBundle:Default:password.html.twig', array(
                    'user' => $entity,
                    'formPass' => $formPass->createView()
        ));
    }

    public function lockAction(Request $request)
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirect($this->generateUrl('app_homepage'));
        }

        if ($request->isMethod('POST')) {
            $password = $request->get('password');

            /** @var EncoderFactory $encoder_service */
            $encoder_service = $this->get('security.encoder_factory');
            $encoder = $encoder_service->getEncoder($user);
            if ($encoder->isPasswordValid($user->getPassword(), $password, $user->getSalt())) {
                $response = new RedirectResponse($this->generateUrl('app_homepage'));
                $response->headers->clearCookie('lock');
                return $response->send();
            } else {
                return $this->render('AppBundle:Default:lock.html.twig');
            }
        }

        $cookies = $request->cookies;
        if (!$cookies->has('lock')) {
            $response = new RedirectResponse($this->generateUrl('app_lock'));
            $cookie = new Cookie('lock', true, time() + (3600 * 24 * 7));
            $response->headers->setCookie($cookie);
            return $response->send();
        }
        return $this->render('AppBundle:Default:lock.html.twig');
    }

    public function communitiesAction(Request $request)
    {
        /*if (false === $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }*/
        $em = $this->getDoctrine()->getManager();
        /** @var User $user */
        $user= $this->getUser();
        if($user->hasRole('ROLE_COMMUNITY_ADMIN')){
            $communities = $user->getAdminCommunities();
        }elseif ($user->hasRole('ROLE_COMMUNITY_SU_ADMIN')){
            $communities = $user->getSuAdminCommunities();
        }else{
            $communities = $em->getRepository('AppBundle:Community')->findBy(array(), array('name' => 'ASC'));

        }
        if ($request->isXmlHttpRequest()) {
            $communityId = $request->request->get('cityhall');
            
            $request->getSession()->set('communityId', $communityId);
            
            return new JsonResponse();
        }

        /** @var Community $community */
        $community = $this->container->get('session.community')->getCommunity();
        

        if ($community) {
            $request->getSession()->set('cityhallReporting', $community->getReportings());
        } else {
            $request->getSession()->remove('cityhallReporting');
        }
        
        return $this->render('AppBundle:Default:cityhalls.html.twig', array(
                    'coumunities' => $communities,
                    'community' => $community,
        ));
    }

    public function cityhallGAAction(Request $request)
    {
        /** @var Community $community */
        $community = $this->container->get('session.community')->getCommunity();
        $ga = $community && $community->getGaBackoffice() ? $community->getGaBackoffice() : null;
        return $this->render('AppBundle:Default:cityhallGA.html.twig', array(
                    'ga' => $ga,
        ));
    }

    public function locateAddressAction(Request $request)
    {
        $locationName = $request->get('locationName');

        /** @var GeolocationApi $geolocationApi */
        $geolocationApi = $this->get('google_geolocation.geolocation_api');
        $location = $geolocationApi->locateAddress($locationName);
        $lat = 0;
        $lng = 0;
        if ($location->getMatches() > 0) {
            $latLng = $location->getLatLng(0);
            $lat = $latLng['lat'];
            $lng = $latLng['lng'];
        }
        return new JsonResponse(array('lat' => $lat, 'lng' => $lng, 'address' => $this->_getDataLatLng($lat, $lng)));
    }

    public function latLngToAddressAction(Request $request)
    {
        $lat = $request->get('lat');
        $lng = $request->get('lng');

        return new JsonResponse($this->_getDataLatLng($lat, $lng));
    }

    private function _getDataLatLng($lat, $lng)
    {
        $geolocationApi = $this->get('google_geolocation.geolocation_api');
        $addressMap = $geolocationApi->latLngToAddress($lat, $lng, true);
        $data = array('address' => '', 'city' => '', 'cp' => '');
        if (isset($addressMap[0]) && $addressMap[0]['long_name']) {
            $data['address'] = $addressMap[0]['long_name'];
        }
        if (isset($addressMap[1]) && $addressMap[1]['long_name']) {
            $data['address'] .= ' ' . $addressMap[1]['long_name'];
        }
        if (isset($addressMap[2]) && $addressMap[2]['long_name']) {
            $data['city'] = $addressMap[2]['long_name'];
        }
        if (isset($addressMap[6]) && $addressMap[6]['long_name']) {
            $data['cp'] = $addressMap[6]['long_name'];
        }

        return $data;
    }
}
