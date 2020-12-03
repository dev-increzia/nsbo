<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Community;
use AppBundle\Repository\AssociationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use AppBundle\Form\AssociationFilterType;
use AppBundle\Form\AssociationType;
use AppBundle\Entity\Association;
use AppBundle\Form\AdminType;
use UserBundle\Entity\User;
use UserBundle\Repository\UserRepository;

class AssociationController extends Controller
{

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $community = $this->container->get('session.community')->getCommunity();
        /** @var User $user */
        $user= $this->getUser();
        if($user->isCommunityAdmin($community) && !$user->hasRight('group_aprove',$community)
        ){
            $this->get('session')->getFlashBag()->add('danger', 'Vous n\'avez pas accès à cette fonctionnalité' );
            return $this->redirect($this->generateUrl('app_homepage'));

        }
        /** @var Form $form */
        $form = $this->get('form.factory')->create(AssociationFilterType::class);
        return $this->render('AppBundle:Association:index.html.twig', array(
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
        $start = $request->get('start');
        $length = $request->get('length');
        $page = ($start != 0) ? $start / $length : 0;
        $orders = $request->get('order');
        $order = array('createAt' => 'DESC');
        if (is_array($orders)) {
            foreach ($orders as $v) {
                if (isset($v['column']) && isset($v['dir'])) {
                    if ($v['column'] == '1') {
                        $order = array('name' => strtoupper($v['dir']));
                    } elseif ($v['column'] == '2') {
                        $order = array('category' => strtoupper($v['dir']));
                    } elseif ($v['column'] == '3') {
                        $order = array('enabled' => strtoupper($v['dir']));
                    } elseif ($v['column'] == '4') {
                        $order = array('createAt' => strtoupper($v['dir']));
                    }
                }
            }
        }

        $community = $this->container->get('session.community')->getCommunity();

        /** @var AssociationRepository $associationRepository */
        $associationRepository = $em->getRepository('AppBundle:Association');

        $entities = $associationRepository->search($page, $order, $community, $request->get('dateBefore'), $request->get('dateAfter'), $request->get('name'), $request->get('enabled'), $request->get('moderate'), $request->get('wait'));
        $countEntities = intval($associationRepository->count($community, $request->get('dateBefore'), $request->get('dateAfter'), $request->get('name'), $request->get('enabled'), $request->get('moderate'), $request->get('wait')));
        $output = array(
            'data' => array(),
            'recordsFiltered' => $countEntities,
            'recordsTotal' => $countEntities
        );

        $outputScreen = $this->container->get('outputScreen');
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');

        /** @var Association $entity */
        foreach ($entities as $entity) {
            $output['data'][] = [
                'logo' => $entity->getImage() && $entity->getImage()->getFile() ? $outputScreen->outPutImage($request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . $helper->asset($entity->getImage(), 'file'), $entity->getImage()->getId()) : '',
                'name' => $outputScreen->outPutInfo($this->generateUrl('app_association_view', array('id' => $entity->getId())), 'associationView' . $entity->getId(), $entity->getName()),
                'category' => $entity->getCategory() ? $entity->getCategory()->getName() : '',
                'enabled' => $entity->getEnabled() ? 'Actif' : 'Inactif',
                'createAt' => $entity->getCreateAt() ? $entity->getCreateAt()->format('d/m/Y H:i') : '',
                'updateAt' => $entity->getUpdateAt() ? $entity->getUpdateAt()->format('d/m/Y H:i') : '',
                'actions' => $outputScreen->outPutUpdate($this->generateUrl('app_association_update', array('id' => $entity->getId())))
                . $outputScreen->outPutDelete($this->generateUrl('app_association_delete', array('id' => $entity->getId())))
                . $outputScreen->outAddAdmin($this->generateUrl('app_association_add_admin', array('id' => $entity->getId()))),
            ];
        }

        return new JsonResponse($output);
    }

    /**
     * @param $id
     * @param Request $request
     * @return JsonResponse
     */
    public function viewAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $community = $this->container->get('session.community')->getCommunity();
        /** @var User $user */
        $user= $this->getUser();
        if($user->isCommunityAdmin($community) && !$user->hasRight('group_aprove',$community)
        ){
            $this->get('session')->getFlashBag()->add('danger', 'Vous n\'avez pas accès à cette fonctionnalité' );
            return $this->redirect($this->generateUrl('app_homepage'));

        }
        /** @var Association $entity */
        $entity = $em->getRepository('AppBundle:Association')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Association entity.');
        }

        //check
        if (false === $this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            if ($entity->getCommunity() != $this->container->get('session.community')->getCommunity()) {
                throw new AccessDeniedException();
            }
        }


        if ($request->isXmlHttpRequest()) {
            $content = $this->renderView('AppBundle:Association:view.html.twig', array(
                'entity' => $entity,
            ));

            return new JsonResponse(array('content' => $content));
        } else {
            throw $this->createNotFoundException('');
        }
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function addAction(Request $request)
    {
        $entity = new Association();
        $entity->setModerate('accepted');
        $entity->setModerateAt(new \DateTime('now'));
        $community = $this->container->get('session.community')->getCommunity();
        if($community === null){
            $this->get('session')->getFlashBag()->add('danger', 'Vous devez choisir une communauté' );
            return $this->redirect($this->generateUrl('app_homepage'));
        }
        /** @var User $user */
        $user= $this->getUser();
        if($user->isCommunityAdmin($community) && !$user->hasRight('group_aprove',$community)
        ){
            $this->get('session')->getFlashBag()->add('danger', 'Vous n\'avez pas accès à cette fonctionnalité' );
            return $this->redirect($this->generateUrl('app_homepage'));

        }
        /** @var Community $community */
        $community = $this->container->get('session.community')->getCommunity(true);

        /** @var Form $form */
        $form = $this->get('form.factory')->create(AssociationType::class, $entity, array(
            'community' => $community
        ));

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $em = $this->getDoctrine()->getManager();
            $city = $em->getRepository('AppBundle:City')->findOneById($form->get('city')->getData());
            $entity->setCity($city);
        }
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $entity->setCommunity($community);
            $entity->setCreateBy($this->getUser());
            $entity->setUpdateBy($this->getUser());

            //suadmin
            $suAdminEmail = $form["suAdminEmail"]->getData();

            /** @var UserRepository $userRepository */
            $userRepository = $em->getRepository('UserBundle:User');

            $suAdmin = $userRepository->findOneByEmail($suAdminEmail);
            if (!$suAdmin) {
                $this->get('session')->getFlashBag()->add('danger', "Aucun compte super-admin valide associé");
            } else {
                $entity->setSuAdmin($suAdmin);
                $em->persist($entity);
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', 'Association ajoutée avec succès');

                $message = "Vous êtes désormais le superadmin du groupe " . $entity->getName() . ". ";
                $this->container->get('notification')->notify($suAdmin, 'admin', $message, false);
                $this->container->get('mobile')->pushNotification($suAdmin, 'NOUS-ENSEMBLE ', "", false, false, 'on');
                $content = $this->renderView('AppBundle:Mail:addSuAdminAssociation.html.twig', array(
                    'entity' => $entity,
                    'sender' => $this->getUser(),
                ));
                $this->container->get('mail')->addSuAdminAssociation($suAdmin, $content, $entity);

                return $this->redirect($this->generateUrl('app_association'));
            }
        }

        return $this->render('AppBundle:Association:add.html.twig', array(
                    'form' => $form->createView(),
                    'entity' => $entity,
                    'community' => $community != null ? $community->getId() : null
        ));
    }

    /**
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function updateAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $community = $this->container->get('session.community')->getCommunity();
        if($community === null){
            $this->get('session')->getFlashBag()->add('danger', 'Vous devez choisir une communauté' );
            return $this->redirect($this->generateUrl('app_homepage'));
        }
        /** @var User $user */
        $user= $this->getUser();
        if($user->isCommunityAdmin($community) && !$user->hasRight('group_aprove',$community)
        ){
            $this->get('session')->getFlashBag()->add('danger', 'Vous n\'avez pas accès à cette fonctionnalité' );
            return $this->redirect($this->generateUrl('app_homepage'));

        }
        /** @var Association $entity */
        $entity = $em->getRepository('AppBundle:Association')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Association entity.');
        }

        //check
        if (false === $this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            if ($entity->getCommunity() != $this->container->get('session.community')->getCommunity()) {
                throw new AccessDeniedException();
            }
        }

        $enabled = $entity->getEnabled();
        $isWait = $entity->getModerate() == 'wait' ? true : false;
        $suAdminOld = $entity->getSuAdmin();

        /** @var Form $form */
        $form = $this->get('form.factory')->create(AssociationType::class, $entity, array(
            'community' => $entity->getCommunity()
        ));

        if ($entity->getSuAdmin()) {
            $form->get('suAdminEmail')->setData($entity->getSuAdmin()->getEmail());
        }

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $em = $this->getDoctrine()->getManager();
            $city = $em->getRepository('AppBundle:City')->findOneById($form->get('city')->getData());
            $entity->setCity($city);
        }
        if ($form->isValid()) {
            $suAdminEmail = $form["suAdminEmail"]->getData();

            /** @var UserRepository $userRepository */
            $userRepository = $em->getRepository('UserBundle:User');

            /** @var User $suAdmin */
            $suAdmin = $userRepository->findOneByEmail($suAdminEmail);
            if (!$suAdmin) {
                $this->get('session')->getFlashBag()->add('danger', "Aucun compte super-admin valide associé");
            } else {
                $entity->setUpdateBy($this->getUser());


                if ($isWait && ($entity->getModerate() == 'accepted' || $entity->getModerate() == 'refuse')) {
                    $entity->setModerateAt(new \DateTime('now'));
                    $this->_moderate($entity);
                }

                if ($enabled != $entity->getEnabled()) {
                    $this->_activate($entity);
                }

                $entity->setSuAdmin($suAdmin);

                if ($suAdmin->getId() != $suAdminOld->getId()) {
                    $message = "Vous êtes désormais le superadmin du groupe " . $entity->getName() . ". ";
                    $this->container->get('notification')->notify($suAdmin, 'admin', $message, false);
                    $this->container->get('mobile')->pushNotification($suAdmin, 'NOUS-ENSEMBLE ', "", false, false, 'on');
                    $content = $this->renderView('AppBundle:Mail:addSuAdminAssociation.html.twig', array(
                        'entity' => $entity,
                        'sender' => $this->getUser(),
                    ));
                    $this->container->get('mail')->addSuAdminAssociation($suAdmin, $content, $entity);

                    if ($suAdminOld) {
                        //notifi old admin
                        $message = "Vous n'êtes plus le superadmin du groupe " . $entity->getName() . ". ";
                        $this->container->get('notification')->notify($suAdminOld, 'admin', $message, false);
                        $this->container->get('mobile')->pushNotification($suAdminOld, 'NOUS-ENSEMBLE ', "", false, false, 'on');
                        $content2 = $this->renderView('AppBundle:Mail:removeSuAdminAssociation.html.twig', array(
                            'entity' => $entity,
                            'sender' => $this->getUser(),
                        ));
                        $this->container->get('mail')->removeSuAdminAssociation($suAdminOld, $content2, $entity);
                    }
                }


                $em->flush();
                $this->get('session')->getFlashBag()->add('success', "Association modifiée avec succès");
                return $this->redirect($this->generateUrl('app_association'));
            }
        }

        return $this->render('AppBundle:Association:update.html.twig', array(
                    'form' => $form->createView(),
                    'entity' => $entity,
                    'community' => $entity->getCommunity()->getId()
        ));
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $community = $this->container->get('session.community')->getCommunity();
        if($community === null){
            $this->get('session')->getFlashBag()->add('danger', 'Vous devez choisir une communauté' );
            return $this->redirect($this->generateUrl('app_homepage'));
        }
        /** @var User $user */
        $user= $this->getUser();
        if($user->isCommunityAdmin($community) && !$user->hasRight('group_aprove',$community)
        ){
            $this->get('session')->getFlashBag()->add('danger', 'Vous n\'avez pas accès à cette fonctionnalité' );
            return $this->redirect($this->generateUrl('app_homepage'));

        }
        $entity = $em->getRepository('AppBundle:Association')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Association entity.');
        }

        //check
        if (false === $this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            if ($entity->getCommunity() != $this->container->get('session.community')->getCommunity()) {
                throw new AccessDeniedException();
            }
        }
        $em->remove($entity);
        $em->flush();
        $this->get('session')->getFlashBag()->add('success', "Association supprimée avec succès");
        return $this->redirect($this->generateUrl('app_association'));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function suAdminAutocompleteAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();

            /** @var UserRepository $userRepository */
            $userRepository = $em->getRepository('UserBundle:User');

            $users = $userRepository->findAllCitizensByCityhallAutocomplete($request->request->get('cityhall'), $request->request->get('search'), true, false);
            return new JsonResponse(json_encode($users));
        } else {
            throw $this->createNotFoundException();
        }
    }

    /**
     * @param $id
     * @param Request $request
     * @return JsonResponse
     */
    public function moderateAction($id, Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();

            /** @var Association $entity */
            $entity = $em->getRepository('AppBundle:Association')->find($id);
            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Association entity.');
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
            $this->get('session')->getFlashBag()->add('success', "Association modérée avec succès");
            return new JsonResponse(array());
        } else {
            throw $this->createNotFoundException('');
        }
    }

    /**
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function activateAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Association $entity */
        $entity = $em->getRepository('AppBundle:Association')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Association entity.');
        }

        //check
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
            $this->get('session')->getFlashBag()->add('success', "Association désactivée avec succès");
        } else {
            $this->get('session')->getFlashBag()->add('success', "Association activée avec succès");
        }


        $referer = $request->headers->get('referer');
        if ($referer) {
            return $this->redirect($referer);
        }
        return $this->redirect($this->generateUrl('app_association'));
    }

    /**
     * @param Association $entity
     */
    private function _activate($entity)
    {
        $content = $this->renderView('AppBundle:Mail:enableAssociation.html.twig', array(
            'entity' => $entity,
            'sender' => $this->getUser(),
        ));
        $this->container->get('mail')->enableAssociation($entity->getSuAdmin(), $content, $entity->getEnabled());

        $message = "Votre groupe " . $entity->getName() . ' a été ' . ($entity->getEnabled() ? 'activé' : 'désactivé') . '';
        $this->container->get('notification')->notify($entity->getSuAdmin(), 'association', $message, false, $entity);
        $this->container->get('mobile')->pushNotification($entity->getSuAdmin(), 'NOUS-ENSEMBLE ', "", false, false, 'on');
        $this->container->get('mobile')->pushNotification($entity->getSuAdmin(), 'NOUS-ENSEMBLE ', $message, false, false, 'off', ($entity->getEnabled() == 'accepted' ? $entity->getId() : false));
    }

    /**
     * @param Association $entity
     */
    private function _moderate($entity)
    {
        $content = $this->renderView('AppBundle:Mail:moderateAssociation.html.twig', array(
            'entity' => $entity,
            'sender' => $this->getUser(),
        ));
        $this->container->get('mail')->moderateAssociation($entity->getSuAdmin(), $content);

        $message = "Votre groupe / association " . $entity->getName() . ' a été ' . ($entity->getModerate() == 'accepted' ? 'accepté' : 'refusé') . '';
        if ($entity->getModerate() == 'accepted') {
            $this->container->get('notification')->notify($entity->getSuAdmin(), 'association', $message, false, $entity);
        } else {
            $this->container->get('notification')->notify($entity->getSuAdmin(), 'associationRefused', $message, false, $entity);
        }

        $this->container->get('mobile')->pushNotification($entity->getSuAdmin(), 'NOUS-ENSEMBLE ', "", false, false, 'on');

        //push mobile
        $this->container->get('mobile')->pushNotification($entity->getSuAdmin(), 'NOUS-ENSEMBLE ', "Votre groupe " . $entity->getName() . ' a été ' . ($entity->getModerate() == 'accepted' ? 'accepté' : 'refusé') . '', false, false, 'off', ($entity->getModerate() == 'accepted' ? $entity->getId() : false));
    }

    /**
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function addAdminAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Association $entity */
        $entity = $em->getRepository('AppBundle:Association')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Association entity.');
        }

        //check
        if (false === $this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            if ($entity->getCommunity() != $this->container->get('session.community')->getCommunity()) {
                throw new AccessDeniedException();
            }
        }

        $community = $this->container->get('session.community')->getCommunity(true);

        /** @var Form $form */
        $form = $this->get('form.factory')->create(AdminType::class, $entity);

        if ($form->handleRequest($request)->isValid()) {
            $adminEmail = $form["adminEmail"]->getData();
            $admin = $em->getRepository('UserBundle:User')->findOneByEmail($adminEmail);
            if (!$admin) {
                $this->container->get('mail')->sendInvitationMail($adminEmail, $this->getUser());
                $this->get('session')->getFlashBag()->add('success', "Cet utilisateur ne fait pas partie de NOUS-Ensemble. Un email a été envoyée pour lui conseiller de nous rejoindre.");
            } else {
                if (!$entity->getAdmins()->contains($admin)) {
                    $entity->addAdmin($admin);
                    $message = "Vous êtes désormais un administrateur du groupe " . $entity->getName() . ". ";
                    $this->container->get('notification')->notify($admin, 'admin', $message, false);
                    $this->container->get('mobile')->pushNotification($admin, 'NOUS-ENSEMBLE ', "", false, false, 'on');
                    $em->flush();
                    $this->container->get('mail')->sendInfoAdminMail($adminEmail, $this->getUser(), 'association', $entity);
                    $this->get('session')->getFlashBag()->add('success', "Cet utilisateur est désormais un administrateur du groupe " . $entity->getName() . ".");
                } else {
                    $this->get('session')->getFlashBag()->add('danger', "Cet utilisateur est déja un administrateur du groupe " . $entity->getName() . ".");
                }
            }
            return $this->redirect($this->generateUrl('app_association_add_admin', array('id' => $entity->getId())));
        }

        return $this->render('AppBundle:Association:admins.html.twig', array(
                'entity' => $entity,
                'form' => $form->createView(),
                'cityhall' => $community->getId()
        ));
    }

    public function deleteAdminAction($association, $id)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var Association $entity */
        $entity = $em->getRepository('AppBundle:Association')->find($association);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Association entity.');
        }

        //check
        if (false === $this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            if ($entity->getCommunity() != $this->container->get('session.community')->getCommunity()) {
                throw new AccessDeniedException();
            }
        }

        /** @var User $admin */
        $admin = $em->getRepository('UserBundle:User')->find($id);
        if ($admin) {
            if (!$entity->getAdmins()->contains($admin)) {
                $this->get('session')->getFlashBag()->add('danger', "Cet utilisateur n'est pas un administrateur du groupe " . $entity->getName() . ".");
            } else {
                $entity->removeAdmin($admin);
                $em->flush();
                $message = "Vous n'êtes plus un administrateur du groupe " . $entity->getName() . ". ";
                $this->container->get('notification')->notify($admin, 'admin', $message, false);
                $this->container->get('mobile')->pushNotification($admin, 'NOUS-ENSEMBLE ', "", false, false, 'on');
                $this->get('session')->getFlashBag()->add('success', "Cet utilisateur n'est plus un administrateur du groupe " . $entity->getName() . ".");
            }
        }
        return $this->redirect($this->generateUrl('app_association_add_admin', array('id' => $entity->getId())));
    }
}
