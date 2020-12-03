<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Community;
use AppBundle\Entity\GoodPlan;
use AppBundle\Entity\Push;
use AppBundle\Entity\PushLog;
use AppBundle\Form\GoodPlanFilterType;
use AppBundle\Form\GoodPlanSettingsType;
use AppBundle\Form\GoodPlanType;
use AppBundle\Repository\GoodPlanRepository;
use DateTime;
use Doctrine\Common\Util\Debug;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use UserBundle\Entity\User;

/**
 * Class GoodPlanController
 * @package AppBundle\Controller
 */
class GoodPlanController extends Controller
{

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        /** @var Form $form */
        $form = $this->get('form.factory')->create(GoodPlanFilterType::class);
        return $this->render('AppBundle:GoodPlan:index.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function indexGridAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $page = (int)$request->get('page');

        /** @var Community $community */
        $community = $this->container->get('session.community')->getCommunity();

        /** @var GoodPlanRepository $goodPlanRepository */
        $goodPlanRepository = $em->getRepository('AppBundle:GoodPlan');

        $entities = $goodPlanRepository->search($page, array('createAt' => 'DESC'), $community, $request->get('title'), $request->get('enabled'), $request->get('moderate'), $request->get('wait'), $request->get('dateBefore'), $request->get('dateAfter'), $request->get('startAt'), $request->get('endAt'));
        $content = $this->renderView('AppBundle:GoodPlan:good_plans.html.twig', array(
            'goodPlans' => $entities,
            'community' => $community
        ));

        return new JsonResponse(array('content' => $content, 'count' => count($entities)));
    }

    /**
     * @param Request $request
     * @param null $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function updateAction(Request $request, GoodPlan $entity = null)
    {
        $now = new \DateTime('now');
        $user= $this->getUser();
        
        if(false === $this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){
            $this->get('session')->getFlashBag()->add('danger', 'Vous n\'avez pas accès à cette fonctionnalité' );
            return $this->redirect($this->generateUrl('app_homepage'));

        }
        $mode="edit";
        if (is_null($entity)) {
            $entity = new GoodPlan();
            $entity->setCreateAt($now);
            $entity->setCreateBy($this->getUser());

            $mode="new";
        }

        $entity->setModerate('accepted');
        $entity->setModerateAt(new \DateTime('now'));
        $isAdmin = $this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN') ? true : false;

        /** @var Community $community */
        $community = $this->container->get('session.community')->getCommunity();

        if (!$community) {
            $this->get('session')->getFlashBag()->add('danger', "L'action n'a pas été effectuée. Vous devez sélectionner une communauté!");
            return $this->redirect($this->generateUrl('app_goodplan_index'));
        }

        /** @var Form $form */
        $form = $this->get('form.factory')->create(GoodPlanType::class, $entity, array(
            'isAdmin' => $isAdmin,
            'cityhall' => $community
        ));

        if ($entity->getPushEnabled() && $entity->getPush() && $entity->getPush()->getSendAt()) {
            $form->get('push')['dateAt']->setData($entity->getPush()->getSendAt());
            $form->get('push')['hourAt']->setData($entity->getPush()->getSendAt()->format('H:i'));
        }
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $em = $this->getDoctrine()->getManager();
            $city = $em->getRepository('AppBundle:City')->findOneById($form->get('city')->getData());
            $entity->setCity($city);
        }
        if ($form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $entity->setUpdateAt($now);
            $entity->setUpdateBy($this->getUser());
            $entity->setCommunity($community);

            $entity->setPrivate(false);

            if (!$this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
                $entity->setMerchant($entity->getMerchant());
            }

            if ($entity->getPushEnabled()) {
                $pushObj = $em->getRepository('AppBundle:Push')->findOneByGoodPlan($entity);
                if(!$pushObj) {
                    $pushObj = new Push();
                    $em->persist($pushObj);
                }
                $entity->setPush($pushObj);
                $pushObj->setGoodPlan($entity);
                $pushObj->setType('goodPlan');
                $pushObj->setCreateBy($user);
                $pushObj->setUpdateBy($user);
                $pushObj->setCommunity($community);
                $pushObj->setContent($form['push']['content']->getData());
                $dateAt = $form['push']['dateAt']->getData();
                $hourAt = explode(':', $form['push']['hourAt']->getData());
                $date = $dateAt->setTime($hourAt[0], $hourAt[1]);
                $pushObj->setSendAt($date);
            } else {
                if ($entity->getPush()) {
                    $em->remove($entity->getPush());
                }
                $entity->setPush(null);
            }
            if ($community->getAutoModGoodPlan()) {
                $entity->setModerate('accepted');
                $entity->setModerateAt($now);
            }
            if($mode == "new") {
                $this->notifyUsers($entity);
            }
            $em->persist($entity);
            $em->flush();

            if($mode == "new") {
                $this->get('session')->getFlashBag()->add('success', 'Bon plan ajouté avec succès');
            } else {
                $this->get('session')->getFlashBag()->add('success', 'Bon plan modifié avec succès');
            }
            return $this->redirect($this->generateUrl('app_goodplan_index'));
        }

        return $this->render($entity->getId() ? 'AppBundle:GoodPlan:update.html.twig' : 'AppBundle:GoodPlan:add.html.twig', array(
            'form' => $form->createView(),
            'entity' => $entity,
            'autoMod' => $community->getAutoModGoodPlan(),
            'cityhall' => $community
        ));
    }

    private function notifyUsers($goodPlan)
    {
        if ($goodPlan && $goodPlan->getEnabled()) {
            foreach ($goodPlan->getParticipants() as $user) {
                $this->container->get('mobile')->pushNotification($user, 'NOUS-Ensemble-Counter', 'goodPlanCounter', false);
                $this->container->get('notification')->notify($user, 'NOUS-Ensemble-Counter', 'articleCounter',false , $goodPlan);
            }
        }
    }


    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var User $user */
        $user= $this->getUser();

        if(!$user->isGa
        ){
            $this->get('session')->getFlashBag()->add('danger', 'Vous n\'avez pas accès à cette fonctionnalité' );
            return $this->redirect($this->generateUrl('app_homepage'));

        }
        /** @var GoodPlan $entity */
        $entity = $em->getRepository('AppBundle:GoodPlan')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Good Plan entity.');
        }

        if (false === $this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            if ($entity->getCommunity() != $this->container->get('session.community')->getCommunity()) {
                throw new AccessDeniedException();
            }
        }

        if ($entity->getPush()) {
            $em->remove($entity->getPush());
        }

        $em->remove($entity);
        $em->flush();
        $this->get('session')->getFlashBag()->add('success', "Bon plan supprimé avec succès");
        return $this->redirect($this->generateUrl('app_goodplan_index'));
    }

    /**
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function activateAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user= $this->getUser();
        if(false === $this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')  
        ){
            $this->get('session')->getFlashBag()->add('danger', 'Vous n\'avez pas accès à cette fonctionnalité' );
            return $this->redirect($this->generateUrl('app_homepage'));

        }
        /** @var GoodPlan $entity */
        $entity = $em->getRepository('AppBundle:GoodPlan')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Good Plan entity.');
        }

        if (false === $this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            if ($entity->getCommunity() != $this->container->get('session.community')->getCommunity()) {
                throw new AccessDeniedException();
            }
        }

        $enabled = $entity->getEnabled();
        $entity->setEnabled($enabled ? false : true);
        $this->_activate($entity);
        $em->flush();
        if ($enabled) {
            $this->get('session')->getFlashBag()->add('success', "Bon plan désactivé avec succès");
        } else {
            $this->get('session')->getFlashBag()->add('success', "Bon plan activé avec succès");
        }


        $referer = $request->headers->get('referer');
        if ($referer) {
            return $this->redirect($referer);
        }
        return $this->redirect($this->generateUrl('app_goodplan_index'));
    }

    /**
     * @param GoodPlan $entity
     */
    private function _activate($entity)
    {
        $content = $this->renderView('AppBundle:Mail:enableGoodPlan.html.twig', array(
            'entity' => $entity,
            'sender' => $this->getUser(),
        ));

        if ($entity->getCreateBy()) {
            $this->container->get('mail')->enableGoodPlan($entity->getCreateBy(), $content, $entity->getEnabled());
        }

        $message = "Votre bon plan " . $entity->getTitle() . ' a été ' . ($entity->getEnabled() ? 'activé' : 'désactivé') . '';
        $this->container->get('notification')->notify($entity->getCreateBy(), 'goodPlan', $message, false, $entity);
        $this->container->get('mobile')->pushNotification($entity->getCreateBy(), 'NOUS-ENSEMBLE ', "", false, false, 'on');
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

        $user= $this->getUser();
        if($user->isCommunityAdmin($community) && !$user->hasRight('event_aprove',$community)
        ){
            return new JsonResponse(array());

        }
        if ($request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();

            /** @var GoodPlan $entity */
            $entity = $em->getRepository('AppBundle:GoodPlan')->find($id);
            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Good Plan entity.');
            }

            //check
            if (false === $this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
                if ($entity->getCommunity() != $this->container->get('session.community')->getCommunity()) {
                    throw new AccessDeniedException();
                }
            }

            $entity->setModerateAt(new \DateTime('now'));
            $entity->setModerate($request->request->get('moderate'));
            $this->_moderate($entity);

            $em->flush();
            $this->get('session')->getFlashBag()->add('success', "Bon plan modéré avec succès");
            return new JsonResponse(array());
        } else {
            throw $this->createNotFoundException('');
        }
    }

    /**
     * @param GoodPlan $entity
     */
    private function _moderate($entity)
    {
        $content = $this->renderView('AppBundle:Mail:moderateGoodPlan.html.twig', array(
            'entity' => $entity,
            'sender' => $this->getUser(),
        ));

        if ($entity->getCreateBy()) {
            $this->container->get('mail')->moderateGoodPlan($entity->getCreateBy(), $content);
        }

        $message = "Votre bon plan " . $entity->getTitle() . ' a été ' . ($entity->getModerate() == 'accepted' ? 'accepté' : 'refusé') . '';
        $this->container->get('notification')->notify($entity->getCreateBy(), 'goodPlan', $message, false, $entity);
        $this->container->get('mobile')->pushNotification($entity->getCreateBy(), 'NOUS-ENSEMBLE ', "", false, false, 'on');
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

            /** @var GoodPlan $entity */
            $entity = $em->getRepository('AppBundle:GoodPlan')->find($id);
            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Good Plan entity.');
            }

            //check
            if (false === $this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
                if ($entity->getCommunity() != $this->container->get('session.community')->getCommunity()) {
                    throw new AccessDeniedException();
                }
            }


            $entity->setModerateSecondaryCommunity($request->request->get('moderate'));
            $this->_moderateSecondary($entity);

            $em->flush();
            $this->get('session')->getFlashBag()->add('success', "Bon plan modéré avec succès");
            return new JsonResponse(array());
        } else {
            throw $this->createNotFoundException('');
        }
    }

    /**
     * @param GoodPlan $entity
     */
    private function _moderateSecondary($entity)
    {
        $content = $this->renderView('AppBundle:Mail:moderateGoodPlan.html.twig', array(
            'entity' => $entity,
            'sender' => $this->getUser(),
        ));

        if ($entity->getCreateBy()) {
            $this->container->get('mail')->moderateGoodPlan($entity->getCreateBy(), $content);
        }

        $message = "Votre bon plan " . $entity->getTitle() . ' a été ' . ($entity->getModerate() == 'accepted' ? 'accepté' : 'refusé') . ' par '.$entity->getSecondaryCommunity()->getName().'';
        $this->container->get('notification')->notify($entity->getCreateBy(), 'goodPlan', $message, false, $entity);
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
            return $this->render('AppBundle:GoodPlan:settings_no_community.html.twig');
        }

        if (!$this->_isAllowed()) {
            return $this->render('AppBundle:GoodPlan:settings_no_access.html.twig');
        }

        /** @var Form $form */
        $form = $this->get('form.factory')->create(GoodPlanSettingsType::class, $community);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($community);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'Vos paramètres ont été enregistrés');

            return $this->redirect($this->generateUrl('app_goodplan_settings'));
        }

        return $this->render('AppBundle:GoodPlan:settings.html.twig', array(
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

        return $user->hasRole('ROLE_SUPER_ADMIN') || $user->hasRole('ROLE_COMMUNITY_SU_ADMIN') || ($user->isCommunityAdmin($community) && $user->hasRight('good_plan_manage',$community));
    }

}