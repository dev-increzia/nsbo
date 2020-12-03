<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Community;
use AppBundle\Form\EventSettingsType;
use AppBundle\Repository\EventRepository;
use Doctrine\Common\Util\Debug;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use AppBundle\Form\EventFilterType;
use AppBundle\Form\EventType;
use AppBundle\Entity\Event;
use UserBundle\Entity\User;
use UserBundle\Repository\UserRepository;

class EventController extends Controller
{
    public function indexAction()
    {
        $community = $this->getAllowedCommunity();
        if ($community === false) {
            return $this->render('AppBundle:Event:no_access.html.twig');
        }

        $community = $this->container->get('session.community')->getCommunity();

        /** @var Form $form */
        $form = $this->get('form.factory')->create(EventFilterType::class);
        return $this->render('AppBundle:Event:index.html.twig', array(
            'form' => $form->createView(),
            'community' => $community
        ));
    }

    public function indexGridAction(Request $request)
    {
        $community = $this->getAllowedCommunity();
        if ($community === false) {
            return new JsonResponse(array());
        }

        $em = $this->getDoctrine()->getManager();
        $page = (int)$request->get('page');

        /** @var Community $cityhall */
        $cityhall = $this->container->get('session.community')->getCommunity();

        /** @var EventRepository $eventRepository */
        $eventRepository = $em->getRepository('AppBundle:Event');

        $entities = $eventRepository->search($page, array('createAt' => 'DESC'), $cityhall, $request->get('type'), $request->get('title'), $request->get('enabled'), $request->get('moderate'), $request->get('wait'), $request->get('dateBefore'), $request->get('dateAfter'), $request->get('startAt'), $request->get('endAt'));
        $content = $this->renderView('AppBundle:Event:events.html.twig', array(
            'events' => $entities,
            'community' => $community
        ));

        return new JsonResponse(array('content' => $content, 'count' => count($entities)));
    }

    public function selectElementsAction(Request $request)
    {   $search = $request->get('search');
        $sql = "SELECT id AS id, CONCAT(name,' (',zipcode,')') AS value FROM city WHERE CONCAT(name,' (',zipcode,')') LIKE '%".$search."%' ORDER BY name Limit 100";
        $em = $this->getDoctrine()->getManager();
        $statement = $em->getConnection()->prepare($sql);
        $statement->execute();
        $results = $statement->fetchAll();
        //$results = array_column($results,'selectOption');
        //$results = implode($results);
        return new JsonResponse($results);
    }

    public function addAction(Request $request, $personnalized = null)
    {
        $now = new \DateTime('now');

        $entity = new Event();

        $community = $this->getAllowedCommunity(null, true);
        if ($community === false) {
            return $this->redirect($this->generateUrl('app_event'));
        }



        $entity->setModerate('accepted');
        $entity->setModerateAt(new \DateTime('now'));
        $entity->setType('community');
        $isAdmin = ($this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN') or $this->container->get('security.authorization_checker')->isGranted('ROLE_COMMUNITY_SU_ADMIN') or $this->container->get('security.authorization_checker')->isGranted('ROLE_COMMUNITY_ADMIN')) ? true : false;

        /** @var Form $form */
        $form = $this->get('form.factory')->create(EventType::class, $entity, array(
            'isAdmin' => $isAdmin,
            'cityhall' => $community
        ));

        /*if ($personnalized) {
            if (!$this->get('session')->get('personalized')) {
                return $this->redirect($this->generateUrl('app_event_personalized'));
            }
            $entity->setPersonalized(true);
            $entity->setAgeFrom($this->get('session')->get('ageFrom'));
            $entity->setAgeTo($this->get('session')->get('ageTo'));
            $entity->setMonday($this->get('session')->get('monday'));
            $entity->setTuesday($this->get('session')->get('tuesday'));
            $entity->setWednesday($this->get('session')->get('wednesday'));
            $entity->setThursday($this->get('session')->get('thursday'));
            $entity->setFriday($this->get('session')->get('friday'));
            $entity->setSaturday($this->get('session')->get('saturday'));
            $entity->setSunday($this->get('session')->get('sunday'));
            $entity->setLessThanSix($this->get('session')->get('lessThanSix'));
            $entity->setBetweenSixTwelve($this->get('session')->get('betweenSixTwelve'));
            $entity->setBetweenTwelveEighteen($this->get('session')->get('betweenTwelveEighteen'));
            $entity->setAllChildrens($this->get('session')->get('allChildrens'));
        }*/

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $em = $this->getDoctrine()->getManager();
            $city = $em->getRepository('AppBundle:City')->findOneById($form->get('city')->getData());
            $entity->setCity($city);
        }
        if ($form->isValid()) {

            $entity->setCreateBy($this->getUser());
            $entity->setUpdateBy($this->getUser());
            $entity->setCommunity($community);

            if ($this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
                if (!$entity->getType()) {
                    $entity->setAssociation(null);
                    $entity->setType('community');
                    $entity->setArticle(null);
                    $entity->setToCity(false);
                }
            }
            if($entity->getType() == ""){
                $entity->setType('community');
            }

            if ($entity->getPushEnabled()) {
                $entity->getPush()->setCommunity($community);
                $entity->getPush()->setEvent($entity);
                $entity->getPush()->setCreateBy($this->getUser());
                $entity->getPush()->setUpdateBy($this->getUser());

                $dateAt = $form['push']['dateAt']->getData();
                $hourAt = explode(':', $form['push']['hourAt']->getData());
                $date = $dateAt->setTime($hourAt[0], $hourAt[1]);
                $entity->getPush()->setSendAt($date);
            } else {
                $entity->setPush(null);
            }

            if ($community->getAutoModEvent()) {
                $entity->setModerate('accepted');
                $entity->setModerateAt($now);
            }


            $em->persist($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'Evénement ajouté avec succès');

            //remove session
            $this->get('session')->remove('ageFrom');
            $this->get('session')->remove('ageTo');
            $this->get('session')->remove('monday');
            $this->get('session')->remove('tuesday');
            $this->get('session')->remove('wednesday');
            $this->get('session')->remove('thursday');
            $this->get('session')->remove('friday');
            $this->get('session')->remove('saturday');
            $this->get('session')->remove('sunday');
            $this->get('session')->remove('lessThanSix');
            $this->get('session')->remove('betweenSixTwelve');
            $this->get('session')->remove('betweenTwelveEighteen');
            $this->get('session')->remove('allChildrens');
            $this->get('session')->remove('personalized');

            return $this->redirect($this->generateUrl('app_event'));
        }

        return $this->render('AppBundle:Event:add.html.twig', array(
            'form' => $form->createView(),
            'entity' => null,
            'autoMod' => $community->getAutoModEvent(),
            'cityhall' => $community
        ));
    }

    /**
     * @param $id
     * @param Request $request
     * @return JsonResponse
     */
    public function viewAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Event $entity */
        $entity = $em->getRepository('AppBundle:Event')->find($id);
        if (!$entity) {
            return new JsonResponse(array());
        }

        $community = $this->getAllowedCommunity($entity->getCommunity());
        if ($community === false) {
            return new JsonResponse(array());
        }

        if ($request->isXmlHttpRequest()) {
            $content = $this->renderView('AppBundle:Event:view.html.twig', array(
                'entity' => $entity,
            ));

            return new JsonResponse(array('content' => $content));
        } else {
            return new JsonResponse(array());
        }
    }

    /**
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function updateAction($id, Request $request)
    {
        $now = new \DateTime('now');

        $em = $this->getDoctrine()->getManager();

        /** @var Event $entity */
        $entity = $em->getRepository('AppBundle:Event')->find($id);
        if (!$entity) {
            $this->get('session')->getFlashBag()->add('danger', "Cet événement n'existe plus");
            return $this->redirect($this->generateUrl('app_event'));
        }

        $community = $this->getAllowedCommunity($entity->getCommunity());
        if ($community === false) {
            return $this->redirect($this->generateUrl('app_event'));
        }



        $isAdmin = ($this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN') or $this->container->get('security.authorization_checker')->isGranted('ROLE_COMMUNITY_SU_ADMIN') or $this->container->get('security.authorization_checker')->isGranted('ROLE_COMMUNITY_ADMIN')) ? true : false;

        /** @var Form $form */
        $form = $this->get('form.factory')->create(EventType::class, $entity, array(
            'isAdmin' => $isAdmin,
            'cityhall' => $entity->getCommunity()
        ));

        if ($entity->getPushEnabled() && $entity->getPush() && $entity->getPush()->getSendAt()) {
            $form->get('push')['dateAt']->setData($entity->getPush()->getSendAt());
            $form->get('push')['hourAt']->setData($entity->getPush()->getSendAt()->format('H:i'));
        }

        $enabled = $entity->getEnabled();
        $isWait = $entity->getModerate() == 'wait' ? true : false;
        $startAt = $entity->getStartAt();
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $city = $em->getRepository('AppBundle:City')->findOneById($form->get('city')->getData());
            $entity->setCity($city);
        }
        if ($form->isValid()) {
            $entity->setUpdateBy($this->getUser());


            if ($isWait && ($entity->getModerate() == 'accepted' || $entity->getModerate() == 'refuse')) {
                $entity->setModerateAt(new \DateTime('now'));
                $this->_moderate($entity);
            }

            if ($enabled != $entity->getEnabled()) {
                $this->_activate($entity);
            }


            if ($entity->getPushEnabled()) {
                $entity->getPush()->setCommunity($entity->getCommunity());
                $entity->getPush()->setEvent($entity);
                $entity->getPush()->setUpdateBy($this->getUser());

                $dateAt = $form['push']['dateAt']->getData();
                $hourAt = explode(':', $form['push']['hourAt']->getData());
                $date = $dateAt->setTime($hourAt[0], $hourAt[1]);

                $entity->getPush()->setSendAt($date);
            } else {
                if ($entity->getPush()) {
                    $em->remove($entity->getPush());
                }
                $entity->setPush(null);
            }

            //date de début changé, on push aux participants
            $date = new \DateTime('now');
            if ($startAt->format('d/m/Y H:i') != $entity->getStartAt()->format('d/m/Y H:i') && ($entity->getStartAt()->format('d/m/Y H:i') < $date->format('d/m/Y H:i'))) {
                foreach ($entity->getParticipants() as $participant) {
                    $this->container->get('mobile')->pushNotification($participant, 'NOUS-ENSEMBLE ', 'Attention, ' . $entity->getStartAt()->format('d/m/Y H:i') . ' de ' . $entity->getTitle() . ' a été modifié. Venez le consulter.');
                }
            }

            $content = $this->renderView('AppBundle:Mail:updateEvent.html.twig', array(
                'entity' => $entity,
                'sender' => $this->getUser(),
            ));
            if ($entity->getCreateBy()) {
                $this->container->get('mail')->updateEvent($entity->getCreateBy(), $content, $entity);
            }

            if ($entity->getCommunity()->getAutoModEvent()) {
                $entity->setModerate('accepted');
                $entity->setModerateAt($now);
            }

            $em->flush();
            $this->get('session')->getFlashBag()->add('success', "Evénement modifié avec succès");
            return $this->redirect($this->generateUrl('app_event'));
        }

        //dump($form->get('city')->getData());die;
        return $this->render('AppBundle:Event:update.html.twig', array(
            'form' => $form->createView(),
            'entity' => $entity,
            'autoMod' => $entity->getCommunity()->getAutoModEvent(),
            'cityhall' => $community
        ));
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();


        /** @var Event $entity */
        $entity = $em->getRepository('AppBundle:Event')->find($id);
        if (!$entity) {
            $this->get('session')->getFlashBag()->add('danger', "Cet événement n'existe plus");
            return $this->redirect($this->generateUrl('app_event'));
        }

        $community = $this->getAllowedCommunity($entity->getCommunity());
        if ($community === false) {
            return $this->redirect($this->generateUrl('app_event'));
        }

        if ($entity->getPush()) {
            $em->remove($entity->getPush());
        }
        $em->remove($entity);
        $em->flush();
        $this->get('session')->getFlashBag()->add('success', "Evénement supprimé avec succès");
        return $this->redirect($this->generateUrl('app_event'));
    }

    /**
     * @param Request $request
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function personalizedAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $ageFrom = (int)$request->get('ageFrom', 0);
            $ageTo = (int)$request->get('ageTo', 0);
            $monday = $request->get('monday') == 'true' ? true : false;
            $tuesday = $request->get('tuesday') == 'true' ? true : false;
            $wednesday = $request->get('wednesday') == 'true' ? true : false;
            $thursday = $request->get('thursday') == 'true' ? true : false;
            $friday = $request->get('friday') == 'true' ? true : false;
            $saturday = $request->get('saturday') == 'true' ? true : false;
            $sunday = $request->get('sunday') == 'true' ? true : false;
            $lessThanSix = $request->get('lessThanSix') == 'true' ? true : false;
            $betweenSixTwelve = $request->get('betweenSixTwelve') == 'true' ? true : false;
            $betweenTwelveEighteen = $request->get('betweenTwelveEighteen') == 'true' ? true : false;
            $allChildrens = $request->get('allChildrens') == 'true' ? true : false;

            //save in session
            $this->get('session')->set('personalized', true);
            $this->get('session')->set('ageFrom', $ageFrom);
            $this->get('session')->set('ageTo', $ageTo);
            $this->get('session')->set('monday', $monday);
            $this->get('session')->set('tuesday', $tuesday);
            $this->get('session')->set('wednesday', $wednesday);
            $this->get('session')->set('thursday', $thursday);
            $this->get('session')->set('friday', $friday);
            $this->get('session')->set('saturday', $saturday);
            $this->get('session')->set('sunday', $sunday);
            $this->get('session')->set('lessThanSix', $lessThanSix);
            $this->get('session')->set('betweenSixTwelve', $betweenSixTwelve);
            $this->get('session')->set('betweenTwelveEighteen', $betweenTwelveEighteen);
            $this->get('session')->set('allChildrens', $allChildrens);

            $community = $this->container->get('session.community')->getCommunity(true);
            $em = $this->getDoctrine()->getManager();

            /** @var UserRepository $userRepository */
            $userRepository = $em->getRepository('UserBundle:User');
            $usersReturn = $userRepository->findCitizenForEventPersonalized($community, $ageFrom, $ageTo, $monday, $tuesday, $wednesday, $thursday, $friday, $saturday, $sunday, $lessThanSix, $betweenSixTwelve, $betweenTwelveEighteen, $allChildrens);
            return new JsonResponse(array(
                'countUsers' => count($usersReturn)
            ));
        }
        return $this->render('AppBundle:Event:personalized.html.twig');
    }

    /**
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function activateAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Event $entity */
        $entity = $em->getRepository('AppBundle:Event')->find($id);
        if (!$entity) {
            $this->get('session')->getFlashBag()->add('danger', "Cet événement n'existe plus");
            return $this->redirect($this->generateUrl('app_event'));
        }

        $community = $this->getAllowedCommunity($entity->getCommunity());
        if ($community === false) {
            return $this->redirect($this->generateUrl('app_project'));
        }



        $enabled = $entity->getEnabled();
        $entity->setEnabled($enabled ? false : true);
        $this->_activate($entity);
        $em->flush();
        if ($enabled) {
            $this->get('session')->getFlashBag()->add('success', "Evénement désactivé avec succès");
        } else {
            $this->get('session')->getFlashBag()->add('success', "Evénement activé avec succès");
        }


        $referer = $request->headers->get('referer');
        if ($referer) {
            return $this->redirect($referer);
        }
        return $this->redirect($this->generateUrl('app_event'));
    }

    /**
     * @param $id
     * @param Request $request
     * @return JsonResponse
     */
    public function moderateAction($id, Request $request)
    {
        /** @var Community $community */
        $community = $this->container->get('session.community')->getCommunity();
        /** @var User $user */
        $user= $this->getUser();
        if($user->isCommunityAdmin($community) && !$user->hasRight('event_aprove',$community)
        ){
            return new JsonResponse(array());

        }
        if ($request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();

            /** @var Event $entity */
            $entity = $em->getRepository('AppBundle:Event')->find($id);
            if (!$entity) {
                $this->get('session')->getFlashBag()->add('danger', "Cet événement n'existe plus");
                return new JsonResponse(array());
            }

            $community = $this->getAllowedCommunity($entity->getCommunity());
            if ($community === false) {
                return new JsonResponse(array());
            }

            $entity->setModerateAt(new \DateTime('now'));
            $entity->setModerate($request->request->get('moderate'));
            $this->_moderate($entity);

            $em->flush();
            $this->get('session')->getFlashBag()->add('success', "Evénement modéré avec succès");
            return new JsonResponse(array());
        } else {
            return new JsonResponse(array());
        }
    }

    /**
     * @param $id
     * @param Request $request
     * @return JsonResponse
     */
    public function moderateSecondaryAction($id, Request $request)
    {
        /** @var Community $community */
        $community = $this->container->get('session.community')->getCommunity();
        /** @var User $user */
        $user= $this->getUser();
        if($user->isCommunityAdmin($community) && !$user->hasRight('event_aprove',$community)
        ){
            return new JsonResponse(array());

        }
        if ($request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();

            /** @var Event $entity */
            $entity = $em->getRepository('AppBundle:Event')->find($id);
            if (!$entity) {
                $this->get('session')->getFlashBag()->add('danger', "Cet événement n'existe plus");
                return new JsonResponse(array());
            }

            /** @var Community $community */
            $community = $this->container->get('session.community')->getCommunity();
            if ($community === false) {
                return new JsonResponse(array());
            }

            //$entity->setModerateAt(new \DateTime('now'));
            $entity->setModerateSecondaryCommunity($request->request->get('moderate'));
            $this->_moderateSecondary($entity);

            $em->flush();
            $this->get('session')->getFlashBag()->add('success', "Evénement modéré avec succès");
            return new JsonResponse(array());
        } else {
            return new JsonResponse(array());
        }
    }


    /**
     * @param Event $entity
     */
    private function _activate($entity)
    {
        $content = $this->renderView('AppBundle:Mail:enableEvent.html.twig', array(
            'entity' => $entity,
            'sender' => $this->getUser(),
        ));

        if ($entity->getCreateBy()) {
            $this->container->get('mail')->enableEvent($entity->getCreateBy(), $content, $entity->getEnabled());
        }

        $message = "Votre événement " . $entity->getTitle() . ' a été ' . ($entity->getEnabled() ? 'activé' : 'désactivé') . '';
        $this->container->get('notification')->notify($entity->getCreateBy(), 'event', $message, false, $entity);
        $this->container->get('mobile')->pushNotification($entity->getCreateBy(), 'NOUS-ENSEMBLE ', "", false, false, 'on');
    }

    /**
     * @param Event $entity
     */
    private function _moderate($entity)
    {
        $content = $this->renderView('AppBundle:Mail:moderateEvent.html.twig', array(
            'entity' => $entity,
            'sender' => $this->getUser(),
        ));

        if ($entity->getCreateBy()) {
            $this->container->get('mail')->moderateEvent($entity->getCreateBy(), $content, $entity);
        }

        $message = "Votre événement " . $entity->getTitle() . ' a été ' . ($entity->getModerate() == 'accepted' ? 'accepté' : 'refusé') . '';
        $this->container->get('notification')->notify($entity->getCreateBy(), 'event', $message, false, $entity);
        $this->container->get('mobile')->pushNotification($entity->getCreateBy(), 'NOUS-ENSEMBLE ', "$message", false, false, 'on');
    }

    /**
     * @param Event $entity
     */
    private function _moderateSecondary($entity)
    {
        if (!$entity->getSecondaryCommunity()) {
            return;
        }

        $content = $this->renderView('AppBundle:Mail:moderateEvent.html.twig', array(
            'entity' => $entity,
            'sender' => $this->getUser(),
        ));
        if ($entity->getCreateBy()) {
            $this->container->get('mail')->moderateEvent($entity->getCreateBy(), $content,$entity);
        }

        $message = "Votre événement " . $entity->getTitle() . ' a été ' . ($entity->getModerate() == 'accepted' ? 'accepté' : 'refusé') . ' par '.$entity->getSecondaryCommunity()->getName().'';
        $this->container->get('notification')->notify($entity->getCreateBy(), 'event', $message, false, $entity);
        $this->container->get('mobile')->pushNotification($entity->getCreateBy(), 'NOUS-ENSEMBLE ', "", false, false, 'on');
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function settingsAction(Request $request)
    {
        /** @var Community $community */
        $community = $this->container->get('session.community')->getCommunity();

        if (!$community) {
            return $this->render('AppBundle:Event:settings_no_community.html.twig');
        }

        $community = $this->getAllowedCommunity();
        if ($community === false) {
            return $this->render('AppBundle:Event:no_access.html.twig');
        }

        if (!$this->_isAllowed()) {
            return $this->render('AppBundle:Event:settings_no_access.html.twig');
        }


        /** @var Form $form */
        $form = $this->get('form.factory')->create(EventSettingsType::class, $community);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();

            //$em->persist($community);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'Vos paramètres ont été enregistrés');

            return $this->redirect($this->generateUrl('app_event_settings'));
        }

        return $this->render('AppBundle:Event:settings.html.twig', array(
            'form' => $form->createView()
        ));

    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        /** @var Community $community */
        $community = $this->container->get('session.community')->getCommunity();
        /** @var User $user */
        $user = $this->getUser();

        return $user->hasRole('ROLE_SUPER_ADMIN') || $user->hasRole('ROLE_COMMUNITY_SU_ADMIN') || ($user->isCommunityAdmin($community) && $user->hasRight('event_manage',$community));
    }

    /**
     * @param Community|null $entityCommunity
     * @param bool $communityRequired
     * @return Community|bool
     */
    protected function getAllowedCommunity(Community $entityCommunity = null, $communityRequired = false)
    {
        /** @var Community $community */
        $community = $this->container->get('session.community')->getCommunity();

        if (!$community && $communityRequired) {
            $this->get('session')->getFlashBag()->add('danger', "Vous devez sélectionner une communauté afin d'accéder à cette page");
            return false;
        }

        if ($this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN') === false) {
            if ($entityCommunity && $entityCommunity !== $community) {
                $this->get('session')->getFlashBag()->add('danger', "Vous n'avez pas accès à cette page");
                return false;
            }
        }

        return $community;
    }

}
