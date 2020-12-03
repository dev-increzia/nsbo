<?php

namespace AppBundle\Controller;

use AppBundle\Entity\AccessAdminCommunity;
use AppBundle\Entity\Community;
use AppBundle\Entity\CommunityUsers;
use AppBundle\Form\AddAdminCommunityAccessType;
use AppBundle\Form\AddSuAdminCommunityType;
use AppBundle\Form\ddAdminCommunityAccessType;
use AppBundle\Form\AddCityhallAdminType;
use AppBundle\Form\AdminCommunityAccessType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use AppBundle\Form\UserFilterType;
use AppBundle\Form\UserCityhallFilterType;
use AppBundle\Form\UserCommunityType;
use AppBundle\Form\UserType;
use AppBundle\Form\UserReportType;
use UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\StreamedResponse;
use UserBundle\Repository\UserRepository;

class UserController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {

        /** @var Community $cityhall */
        $cityhall = $this->container->get('session.community')->getCommunity();

        /** @var User $user */
        $user = $this->getUser();
        if($cityhall && $cityhall->getIsPrivate() )
        {
            if($user->isCommunityAdmin($cityhall))
            {
                if(!$user->hasRight('user_aprove',$cityhall)){
                    $this->get('session')->getFlashBag()->add('danger', 'Vous n\'avez pas accès à cette fonctionnalité' );
                    return $this->redirect($this->generateUrl('app_homepage'));
                }
            }
        }else{
            if($user->hasRole('ROLE_COMMUNITY_ADMIN'))
            {
                $this->get('session')->getFlashBag()->add('danger', 'Vous n\'avez pas accès à cette fonctionnalité' );
                return $this->redirect($this->generateUrl('app_homepage'));
            }
        }

        /** @var Form $form */
        $form = $this->get('form.factory')->create(UserFilterType::class, null, array(
            'cityhall' => $cityhall
        ));
        return $this->render('AppBundle:User:index.html.twig', array(
            'form' => $form->createView(),
        ));
    }

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
                    if ($v['column'] == '0') {
                        $order = array('id' => strtoupper($v['dir']));
                    } elseif ($v['column'] == '1') {
                        $order = array('lastname' => strtoupper($v['dir']));
                    } elseif ($v['column'] == '2') {
                        $order = array('firstname' => strtoupper($v['dir']));
                    } elseif ($v['column'] == '4') {
                        $order = array('enabled' => strtoupper($v['dir']));
                    } elseif ($v['column'] == '5') {
                        $order = array('createAt' => strtoupper($v['dir']));
                    }
                }
            }
        }

        /** @var Community $cityhall */
        $cityhall = $this->container->get('session.community')->getCommunity();

        /** @var UserRepository $userRepository */
        $userRepository = $em->getRepository('UserBundle:User');

        $entities = $userRepository->search($page, $order, $cityhall, array('ROLE_CITIZEN'), $request->get('lastname'), $request->get('firstname'), $request->get('enabled'), $request->get('role'), $request->get('association'), $request->get('merchant'));
        $countEntities = intval($userRepository->count($cityhall, array('ROLE_CITIZEN'), $request->get('lastname'), $request->get('firstname'), $request->get('enabled'), $request->get('role'), $request->get('association'), $request->get('merchant')));
        $output = array(
            'data' => array(),
            'recordsFiltered' => $countEntities,
            'recordsTotal' => $countEntities
        );
        $typeArray = array(
            '' => '',
            'pending' => 'En attente',
            'approved' => 'Accepté',
            'refused' => 'Refusé');
        $outputScreen = $this->container->get('outputScreen');

        foreach ($entities as $entity) {
            $type = "";
            $delRel = "";
            $outputSendRefuse = "";
            $outputSendPassword = "";
            $outputDelete = "";
            $outputUpdate = "";
            foreach ($entity->getCommunities() as $item) {
                if ($cityhall != null) {
                    if ($cityhall->getId() === $item->getCommunity()->getId() || $this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
                        if ($this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
                            $type .= $item->getCommunity()->getName() . ' : ' . $typeArray[$item->getType()] . '<br>';
                        } else {
                            $type .= $typeArray[$item->getType()] . '<br>';
                        }


                        $delRel .= $outputScreen->outPutEnabled($this->generateUrl('app_user_del_rel', array('id' => $entity->getId(), 'id_community' => $item->getCommunity()->getId())), $item->getCommunity()->getName());
                    }
                } else {
                    $type .= $item->getCommunity()->getName() . ' : ' . $typeArray[$item->getType()] . '<br>';
                    $delRel .= $outputScreen->outPutEnabled($this->generateUrl('app_user_del_rel', array('id' => $entity->getId(), 'id_community' => $item->getCommunity()->getId())), $item->getCommunity()->getName());
                }
            }


            if ($this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
                $outputDelete = $outputScreen->outPutDelete($this->generateUrl('app_user_delete', array('id' => $entity->getId())));
                $outputUpdate = $outputScreen->outPutUpdate($this->generateUrl('app_user_update', array('id' => $entity->getId())));
            }
            if (($this->getUser()->hasRight('user_aprove',$cityhall) &&
                    $this->container->get('security.authorization_checker')->isGranted('ROLE_COMMUNITY_ADMIN') &&
                    $this->getUser()->isCommunityAdmin($cityhall) && $cityhall != null && count($entity->getCommunities()) > 0) ||
                ($this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN') && $cityhall != null && count($entity->getCommunities()) > 0) ||
                ($this->container->get('security.authorization_checker')->isGranted('ROLE_COMMUNITY_SU_ADMIN') &&
                    $this->getUser()->isCommunitySuAdmin($cityhall) && $cityhall != null && count($entity->getCommunities()) > 0)) {

                if ($cityhall->getIsPrivate()) {
                    $outputSendPassword = $outputScreen->outPutAccessUser($this->generateUrl('app_user_send_password_community', array('id' => $entity->getId(), 'id_community' => $cityhall->getId())));

                    $outputSendRefuse = $outputScreen->outPutRefuseUser($this->generateUrl('app_user_send_refuse', array('id' => $entity->getId(), 'id_community' => $cityhall->getId())));
                }

            }
            $output['data'][] = [
                'id' => $entity->getId(),
                'lastname' => $entity->getLastname(),
                'firstname' => $entity->getFirstname(),
                'type' => $type,
                'role' => $outputScreen->outPutRoles($entity),
                'enabled' => $delRel,
                'createAt' => $entity->getCreateAt() ? $entity->getCreateAt()->format('d/m/Y H:i') : '',
                'updateAt' => $entity->getUpdateAt() ? $entity->getUpdateAt()->format('d/m/Y H:i') : '',
                'actions' => $outputScreen->outPutUserMail($entity->getEmail())
                    . $outputUpdate
                    . $outputDelete
                    . $outputSendPassword
                    . $outputSendRefuse,
            ];
        }

        return new JsonResponse($output);
    }

    public function sendCommunityPasswordAction($id, $id_community, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('UserBundle:User')->find($id);
        $community = $em->getRepository('AppBundle:Community')->find($id_community);
        $userCommuity = $em->getRepository('AppBundle:CommunityUsers')->findOneBy(array('user' => $entity, 'community' => $community));
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        if (!$community) {
            throw $this->createNotFoundException('Unable to find Community entity.');
        }

        if (!$userCommuity) {
            throw $this->createNotFoundException('Unable to find CommunityUsers entity.');
        }

        if (false === $this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            if (!$this->getUser()->isCommunityAdmin($community)) {
                if (!$this->getUser()->isCommunitySuAdmin($community)) {
                    throw new AccessDeniedException();
                }
            }
        }

        $content = $this->renderView('AppBundle:Mail:sendCommunityPassword.html.twig', array(
            'user' => $entity,
            'community' => $community,

        ));


        $this->container->get('mail')->acceptUser($entity, $content);
        $message = "La communauté  " . $community->getName() . ' a accepté votre demande de liaison. Le mot de passe vous a été envoyé par email. ';
        $this->container->get('mobile')->pushNotification($entity, 'NOUS-ENSEMBLE ', $message, false, false, 'off', false, false, 'off', 'no',  $community->getId());
        $this->get('session')->getFlashBag()->add('success', "Mot de passe Communauté envoyé avec succès");
        return $this->redirect($this->generateUrl('app_user'));
    }

    public function sendCommunityRefuseAction($id, $id_community, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('UserBundle:User')->find($id);
        $community = $em->getRepository('AppBundle:Community')->find($id_community);
        $userCommuity = $em->getRepository('AppBundle:CommunityUsers')->findOneBy(array('user' => $entity, 'community' => $community));
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        if (!$community) {
            throw $this->createNotFoundException('Unable to find Community entity.');
        }

        if (!$userCommuity) {
            throw $this->createNotFoundException('Unable to find CommunityUsers entity.');
        }

        if (false === $this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            if (!$this->getUser()->isCommunityAdmin($community)) {
                if (!$this->getUser()->isCommunitySuAdmin($community)) {
                    throw new AccessDeniedException();
                }
            }
        }

        $content = $this->renderView('AppBundle:Mail:refuseUser.html.twig', array(
            'user' => $entity,
            'community' => $community,
            'sender' => $this->getUser()
        ));

        $userCommuity->setType('refused');
        $em->flush();
        $this->container->get('mail')->refuseUser($entity, $content);
        $this->get('session')->getFlashBag()->add('success', "Email de Refus de la liaison à la Communauté envoyé avec succès");
        return $this->redirect($this->generateUrl('app_user'));
    }

    public function delRelationAction($id, $id_community, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('UserBundle:User')->find($id);
        $community = $em->getRepository('AppBundle:Community')->find($id_community);
        $userCommuity = $em->getRepository('AppBundle:CommunityUsers')->findOneBy(array('user' => $entity, 'community' => $community));

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        if (!$community) {
            throw $this->createNotFoundException('Unable to find Community entity.');
        }

        if (!$userCommuity) {
            throw $this->createNotFoundException('Unable to find CommunityUsers entity.');
        }

        $entity->removeCommunity($userCommuity);
        $community->removeUser($userCommuity);

        $em->flush();
        $this->get('session')->getFlashBag()->add('success', "Liaison supprimée avec succès");
        return $this->redirect($this->generateUrl('app_user'));
    }

    public function updateAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var User $entity */
        $entity = $em->getRepository('UserBundle:User')->find($id);

        /** @var Community $community */
        $community = $this->container->get('session.community')->getCommunity();
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }
        /*if (!$community) {
            $this->get('session')->getFlashBag()->add('danger', "L'action n'a pas été effectuée. Vous devez sélectionner une communauté!");
            return $this->redirect($this->generateUrl('app_user'));
        }*/

        //check

        if (false === $this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            if (!$this->container->get('security.authorization_checker')->isGranted('ROLE_COMMUNITY_ADMIN')) {
                throw new AccessDeniedException();
            }

            if (!$entity->getCommunities()->contains($community)) {
                throw new AccessDeniedException();
            }
        }

        /** @var Form $form */
        $form = $this->get('form.factory')->create(UserType::class, $entity);

        if ($form->handleRequest($request)->isValid()) {
            $entity->setUpdateBy($this->getUser());
            if ($this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
                $em->flush();
            }

            $this->get('session')->getFlashBag()->add('success', "Compte utilisateur modifié avec succès");
            return $this->redirect($this->generateUrl('app_user'));
        }

        return $this->render('AppBundle:User:update.html.twig', array(
            'form' => $form->createView(),
            'entity' => $entity,
        ));
    }

    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var User $entity */
        $entity = $em->getRepository('UserBundle:User')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }
        $community = $this->container->get('session.community')->getCommunity();

        //check
        if (false === $this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            if (!$this->getUser()->isCommunitySuAdmin($community)) {
                throw new AccessDeniedException();
            }

            if (!$this->isAllowedInCommunity($entity)) {
                throw new AccessDeniedException();
            }
        }

        $em->remove($entity);
        $em->flush();
        $this->get('session')->getFlashBag()->add('success', "Compte utilisateur supprimé avec succès");
        return $this->redirect($this->generateUrl('app_user'));
    }

    public function deleteRelationshipAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $community = $this->container->get('session.community')->getCommunity();

        /** @var User $entity */
        $entity = $em->getRepository('UserBundle:User')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        //check
        if (false === $this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            if (!$this->getUser()->isCommunitySuAdmin($community)) {
                throw new AccessDeniedException();
            }

            if (!$this->isAllowedInCommunity($entity)) {
                throw new AccessDeniedException();
            }
        }

        $em->remove($entity);
        $em->flush();
        //$this->container->get('mail')->enableUser($entity, $content, $entity->getEnabled());
        $this->get('session')->getFlashBag()->add('success', "Compte utilisateur supprimé avec succès");
        return $this->redirect($this->generateUrl('app_user'));
    }

    public function enableUserAction($id, Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();

            /** @var User $entity */
            $entity = $em->getRepository('UserBundle:User')->find($id);
            if (!$entity) {
                throw $this->createNotFoundException('Unable to find User entity.');
            }

            //check
            if (false === $this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
                if (!$this->isAllowedInCommunity($entity)) {
                    throw new AccessDeniedException();
                }
            }

            $enabled = $request->request->get('enabled') == 'true' ? true : false;
            $entity->setEnabled($enabled);

            $content = $this->renderView('AppBundle:Mail:enableUser.html.twig', array(
                'entity' => $entity,
                'sender' => $this->getUser(),
            ));
            $this->container->get('mail')->enableUser($entity, $content, $entity->getEnabled());


            $em->flush();
            return new JsonResponse();
        } else {
            return $this->redirect($this->generateUrl('app_user'));
        }
    }

    public function reportAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var User $entity */
        $entity = $em->getRepository('UserBundle:User')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        //check
        if (false === $this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            if (!$this->isAllowedInCommunity($entity)) {
                throw new AccessDeniedException();
            }
        }

        /** @var Form $form */
        $form = $this->get('form.factory')->create(UserReportType::class);
        if ($request->isXmlHttpRequest()) {
            $content = $this->renderView('AppBundle:User:report.html.twig', array(
                'user' => $entity,
                'form' => $form->createView(),
            ));

            return new JsonResponse(array('content' => $content));
        } else {
            if ($request->isMethod('POST')) {
                if ($form->handleRequest($request)->isValid()) {
                    //mail
                    $content = $this->renderView('AppBundle:Mail:userReport.html.twig', array(
                        'message' => $form->get('message')->getData(),
                        'sender' => $this->getUser(),
                        'entity' => $entity,
                    ));

                    // TODO CHANGE getCityHall by correct Community
                    //$this->container->get('mail')->userReport($content, $form->get('object')->getData(), $entity->getCommunity()->getEmail());

                    $this->get('session')->getFlashBag()->add('success', "Signalement envoyé avec succès");
                    return $this->redirect($this->generateUrl('app_user'));
                } else {
                    $this->get('session')->getFlashBag()->add('danger', "Une erreur est survenue");
                    return $this->redirect($this->generateUrl('app_user'));
                }
            } else {
                return $this->redirect($this->generateUrl('app_user'));
            }
        }
    }

    public function indexCityhallAction()
    {
        $cityhall = $this->container->get('session.community')->getCommunity();

        //check
        if (false === $this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            if (!$this->getUser()->isCommunitySuAdmin($cityhall)) {
                throw new AccessDeniedException();
            }
        }

        /** @var Form $form */
        $form = $this->get('form.factory')->create(UserCityhallFilterType::class);
        return $this->render('AppBundle:User:indexCityhall.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function indexCityhallGridAction(Request $request)
    {
        //check
        /** @var Comm $cityhall */
        $cityhall = $this->container->get('session.community')->getCommunity();
        if (false === $this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            if (!$this->getUser()->isCommunitySuAdmin($cityhall)) {
                throw new AccessDeniedException();
            }
        }

        $em = $this->getDoctrine()->getManager();
        $start = $request->get('start');
        $length = $request->get('length');
        $page = ($start != 0) ? $start / $length : 0;
        $orders = $request->get('order');
        $order = array('createAt' => 'DESC');
        if (is_array($orders)) {
            foreach ($orders as $v) {
                if (isset($v['column']) && isset($v['dir'])) {
                    if ($v['column'] == '0') {
                        $order = array('id' => strtoupper($v['dir']));
                    }
                }
            }
        }


        /** @var UserRepository $userRepository */
        $userRepository = $em->getRepository('UserBundle:User');

        $entities = $userRepository->search($page, $order, $cityhall, array('ROLE_COMMUNITY_ADMIN'), $request->get('lastname'), $request->get('firstname'), $request->get('enabled'));
        $countEntities = intval($userRepository->count($cityhall, array('ROLE_COMMUNITY_ADMIN'), $request->get('lastname'), $request->get('firstname'), $request->get('enabled')));
        $output = array(
            'data' => array(),
            'recordsFiltered' => $countEntities,
            'recordsTotal' => $countEntities
        );

        $outputScreen = $this->container->get('outputScreen');
        foreach ($entities as $entity) {
            //todo if me ? $this->getUser()
            $output['data'][] = [
                'id' => $entity->getId(),
                'lastname' => $entity->getLastname(),
                'firstname' => $entity->getFirstname(),
                'enabled' => $entity->getEnabled() ? 'Actif' : 'Inactif',
                'suAdmin' => $entity->hasRole('ROLE_COMMUNITY_SU_ADMIN') ? 'Oui' : '',
                'createAt' => $entity->getCreateAt() ? $entity->getCreateAt()->format('d/m/Y H:i') : '',
                'updateAt' => $entity->getUpdateAt() ? $entity->getUpdateAt()->format('d/m/Y H:i') : '',
                'actions' => $outputScreen->outPutUpdate($this->generateUrl('app_user_cityhall_update', array('id' => $entity->getId())))
                    . $outputScreen->outPutDelete($this->generateUrl('app_user_cityhall_delete', array('id' => $entity->getId())))
                    . $outputScreen->outPutAccess($this->generateUrl('app_user_cityhall_access', array('id' => $entity->getId()))),
            ];
        }

        return new JsonResponse($output);
    }

    public function addCityhallAction(Request $request)
    {
        /** @var Community $community */
        $community = $this->container->get('session.community')->getCommunity();
        //check
        if (false === $this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            if (!$this->getUser()->isCommunitySuAdmin($community)) {
                throw new AccessDeniedException();
            }
        }



        if (!$community) {
            $this->get('session')->getFlashBag()->add('danger', "L'action n'a pas été effectuée. Vous devez sélectionner une communauté!");
            return $this->redirect($this->generateUrl('app_user_cityhall'));
        }

        $entity = new User();
        //$entity->setCommunityAdmin($cityhall);
        $entity->setCommunityAdmin($community);
        $entity->addAdminCommunity($community);
        $entity->setRoles(array('ROLE_COMMUNITY_ADMIN'));
        $entity->setCreateBy($this->getUser());
        $entity->setUpdateBy($this->getUser());

        /** @var Form $form */
        $form = $this->get('form.factory')->create(UserCommunityType::class, $entity,array('isSuAdmin'=>$entity->isCommunitySuAdmin($community),'community'=>$community));
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $em = $this->getDoctrine()->getManager();
            $city = $em->getRepository('AppBundle:City')->findOneById($form->get('city')->getData());
            $entity->setCity($city);
        }
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $existUsername = $em->getRepository('UserBundle:User')->findOneBy(array('username' => $entity->getUsername()));
            $exisEmail = $em->getRepository('UserBundle:User')->findOneBy(array('username' => $entity->getEmail()));
            $existUsernameAlt = $em->getRepository('UserBundle:User')->findOneBy(array('email' => $entity->getUsername()));
            $exisEmailAlt = $em->getRepository('UserBundle:User')->findOneBy(array('email' => $entity->getEmail()));

            if ($existUsername || $exisEmail || $existUsernameAlt || $exisEmailAlt) {
                $this->get('session')->getFlashBag()->add('danger', "Le nom d'utilisateur ou l'email existe");
                return $this->render('AppBundle:User:addCityhall.html.twig', array(
                    'form' => $form->createView(),
                    'entity' => $entity,
                    'community' => $community
                ));

            }
            $password = rand(0, 9) . chr(rand(65, 90)) . rand(0, 9) . chr(rand(65, 90)) . rand(0, 9) . chr(rand(65, 90)) . rand(0, 9) . chr(rand(65, 90)) . rand(0, 9) . chr(rand(65, 90));
            $entity->setPassword($this->get('security.password_encoder')->encodePassword($entity, $password));
            $entity->setEnabled(true);

            $accesses = [];

            if (isset($request->get('user_community')['accessAdmin']['accessAdmin'])) {
                $accesses = $request->get('user_community')['accessAdmin']['accessAdmin'];
            }

            //$community = $entity->getCommunityAdmin();
            foreach ($accesses as $ac) {
                $acTmp = $em->getRepository('AppBundle:AdminAccess')->find($ac);

                $adminAccess = new \AppBundle\Entity\AccessAdminCommunity();
                $adminAccess->setAccess($acTmp);
                $adminAccess->setAccessUsers($entity);
                $adminAccess->setCommunity($community);
                $em->persist($adminAccess);

                $entity->addAccess($adminAccess);
                $em->flush();
            }
            $categories = $em->getRepository('AppBundle:Category')->findAll();
            foreach ($categories as $category) {
                $entity->addInterest($category);
            }

            $content = $this->renderView('AppBundle:Mail:adminCityhallAccess.html.twig', array(
                'user' => $entity,
                'password' => $password
            ));
            $this->container->get('mail')->adminCityhallAccess($entity, $content);

            $community->addAdmin($entity);
            $community->addCommunityAdmin($entity);
            $em->persist($community);

            $em->persist($entity);

            $communityUser = new CommunityUsers();
            $communityUser->setUser($entity);
            $communityUser->setCommunity($community);
            $communityUser->setType('approved')
                ->setFollow(0);
            $em->persist($communityUser);

            $em->flush();
            $this->get('session')->getFlashBag()->add('success', "Compte communauté ajouté avec succès");
            return $this->redirect($this->generateUrl('app_user_cityhall'));
        }

        return $this->render('AppBundle:User:addCityhall.html.twig', array(
            'form' => $form->createView(),
            'entity' => $entity,
            'community' => $community
        ));
    }


    public function addAdminCityhallAction(Request $request)
    {
        /** @var Community $community */
        $community = $this->container->get('session.community')->getCommunity();
        //check
        if (false === $this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {

                throw new AccessDeniedException();

        }



        if (!$community) {
            $this->get('session')->getFlashBag()->add('danger', "L'action n'a pas été effectuée. Vous devez sélectionner une communauté!");
            return $this->redirect($this->generateUrl('app_user_cityhall'));
        }

        $entity = new User();
        //$entity->setCommunityAdmin($cityhall);
        //$entity->setCommunitySuAdmin($community);
        $entity->addSuAdminCommunity($community);
        $entity->setRoles(array('ROLE_COMMUNITY_SU_ADMIN'));
        $entity->setCreateBy($this->getUser());
        $entity->setUpdateBy($this->getUser());

        /** @var Form $form */
        $form = $this->get('form.factory')->create(AddCityhallAdminType::class, $entity);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $em = $this->getDoctrine()->getManager();
            $city = $em->getRepository('AppBundle:City')->findOneById($form->get('city')->getData());
            $entity->setCity($city);
        }
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $existUsername = $em->getRepository('UserBundle:User')->findOneBy(array('username' => $entity->getUsername()));
            $exisEmail = $em->getRepository('UserBundle:User')->findOneBy(array('username' => $entity->getEmail()));
            $existUsernameAlt = $em->getRepository('UserBundle:User')->findOneBy(array('email' => $entity->getUsername()));
            $exisEmailAlt = $em->getRepository('UserBundle:User')->findOneBy(array('email' => $entity->getEmail()));

            if ($existUsername || $exisEmail || $existUsernameAlt || $exisEmailAlt) {
                $this->get('session')->getFlashBag()->add('danger', "Le nom d'utilisateur ou l'email existe");
                return $this->render('AppBundle:User:addCityhall.html.twig', array(
                    'form' => $form->createView(),
                    'entity' => $entity,
                    'community' => $community
                ));

            }
            $password = rand(0, 9) . chr(rand(65, 90)) . rand(0, 9) . chr(rand(65, 90)) . rand(0, 9) . chr(rand(65, 90)) . rand(0, 9) . chr(rand(65, 90)) . rand(0, 9) . chr(rand(65, 90));
            $entity->setPassword($this->get('security.password_encoder')->encodePassword($entity, $password));
            $entity->setEnabled(true);
            //$em = $this->getDoctrine()->getManager();



            $content = $this->renderView('AppBundle:Mail:adminCityhallAccess.html.twig', array(
                'user' => $entity,
                'password' => $password
            ));
            $this->container->get('mail')->adminCityhallAccess($entity, $content);

            //$community->addAdmin($entity);
            $community->addCommunitySuadmin($entity);
            $em->persist($community);

            $em->persist($entity);

            $communityUser = new CommunityUsers();
            $communityUser->setUser($entity);
            $communityUser->setCommunity($community);
            $communityUser->setType('approved')
                ->setFollow(0);
            $em->persist($communityUser);

            $em->flush();
            $this->get('session')->getFlashBag()->add('success', "Compte Super Administrateur ajouté avec succès");
            return $this->redirect($this->generateUrl('app_user_cityhall'));
        }

        return $this->render('AppBundle:User:addSuAdmin.html.twig', array(
            'form' => $form->createView(),
            'entity' => $entity,
            'community' => $community
        ));
    }


    public function addAdminCommunityAccessAction(Request $request)
    {
        /** @var Community $community */
        $community = $this->container->get('session.community')->getCommunity();
        //check
        if (false === $this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            if (!$this->getUser()->isCommunitySuAdmin($community)) {
                throw new AccessDeniedException();
            }
        }



        if (!$community) {
            $this->get('session')->getFlashBag()->add('danger', "L'action n'a pas été effectuée. Vous devez sélectionner une communauté!");
            return $this->redirect($this->generateUrl('app_user_cityhall'));
        }


        /** @var Form $form */
        $form = $this->get('form.factory')->create(AddAdminCommunityAccessType::class, null , array('community'=>$community));
        if ($form->handleRequest($request)->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $accesses = [];
            if (isset($request->get('add_admin_community_access')['user'])) {
                /** @var User $user */
                $user = $em->getRepository('UserBundle:User')->find($request->get('add_admin_community_access')['user']);;
            }

            if (isset($request->get('add_admin_community_access')['accessAdmin']['accessAdmin'])) {
                $accesses = $request->get('add_admin_community_access')['accessAdmin']['accessAdmin'];
            }

            foreach ($accesses as $ac) {
                $acTmp = $em->getRepository('AppBundle:AdminAccess')->find($ac);

                $adminAccess = new \AppBundle\Entity\AccessAdminCommunity();
                $adminAccess->setAccess($acTmp);
                $adminAccess->setAccessUsers($user);
                $adminAccess->setCommunity($community);
                $em->persist($adminAccess);

                $user->addAccess($adminAccess);
                $em->flush();

            }
            //$categories = $em->getRepository('AppBundle:Category')->findAll();



            $user->setCommunityAdmin($community);
            $user->addAdminCommunity($community);
            $community->addAdmin($user);
            $community->addCommunityAdmin($user);
            if(!$user->hasRole('ROLE_COMMUNITY_ADMIN')){
                $user->addRole('ROLE_COMMUNITY_ADMIN');
            }

            $em->flush();
            $this->get('session')->getFlashBag()->add('success', "Compte admin ajouté avec succès");
            return $this->redirect($this->generateUrl('app_user_cityhall'));
        }

        return $this->render('AppBundle:User:addAdminCommunityAccess.html.twig', array(
            'form' => $form->createView(),

        ));
    }

    public function addSuAdminCommunityAction(Request $request)
    {
        /** @var Community $community */
        $community = $this->container->get('session.community')->getCommunity();
        $em = $this->getDoctrine()->getManager();
        //check
        if (false === $this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {

                throw new AccessDeniedException();

        }



        if (!$community) {
            $this->get('session')->getFlashBag()->add('danger', "L'action n'a pas été effectuée. Vous devez sélectionner une communauté!");
            return $this->redirect($this->generateUrl('app_user_cityhall'));
        }




        /** @var Form $form */
        $form = $this->get('form.factory')->create(AddSuAdminCommunityType::class, null , array('community'=>$community));
        if ($form->handleRequest($request)->isValid()) {
            //$password = rand(0, 9) . chr(rand(65, 90)) . rand(0, 9) . chr(rand(65, 90)) . rand(0, 9) . chr(rand(65, 90)) . rand(0, 9) . chr(rand(65, 90)) . rand(0, 9) . chr(rand(65, 90));

            $em = $this->getDoctrine()->getManager();

            if (isset($request->get('add_su_admin_community')['user'])) {

                /** @var User $user */
                $user = $em->getRepository('UserBundle:User')->find($request->get('add_su_admin_community')['user']);;
            }




            //$user->setCommunitySuAdmin($community);
            $user->addSuAdminCommunity($community);
            //$community->addAdmin($user);
            $community->addCommunitySuadmin($user);

            if(!$user->hasRole('ROLE_COMMUNITY_SU_ADMIN')){
                $user->addRole('ROLE_COMMUNITY_SU_ADMIN');
            }


            $em->flush();
            $this->get('session')->getFlashBag()->add('success', "Compte admin ajouté avec succès");
            return $this->redirect($this->generateUrl('app_user_cityhall'));
        }

        return $this->render('AppBundle:User:addSuAdminCommunity.html.twig', array(
            'form' => $form->createView(),

        ));
    }


    public function updateCityhallAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var Community $community */
        $community = $this->container->get('session.community')->getCommunity();

        /** @var User $entity */
        $entity = $em->getRepository('UserBundle:User')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        //check
        if (false === $this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            if (!$this->getUser()->isCommunitySuAdmin($community)) {
                throw new AccessDeniedException();
            }

            if (!$this->isAllowedInCommunity($entity)) {
                throw new AccessDeniedException();
            }
        }
        if (!$community) {
            $this->get('session')->getFlashBag()->add('danger', "L'action n'a pas été effectuée. Vous devez sélectionner une communauté!");
            return $this->redirect($this->generateUrl('app_user_cityhall'));
        }
        $tabAccess = [];
        /** @var AccessAdminCommunity[] $userAcces */
        $userAcces = $community->getAccess();
        foreach ($userAcces as $ua) {
            if($ua->getAccessUsers() === $entity) {
                $tabAccess[]=$ua->getAccess()->getId();
            }
        }



        /** @var Form $form */
        $form = $this->get('form.factory')->create(UserCommunityType::class, $entity,array('isSuAdmin'=>$entity->isCommunitySuAdmin($community),'community'=>$community));
        $enabled = $entity->getEnabled();
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $em = $this->getDoctrine()->getManager();
            $city = $em->getRepository('AppBundle:City')->findOneById($form->get('city')->getData());
            $entity->setCity($city);
        }
        if ($form->isValid()) {
            $accesses = [];

            if (isset($request->get('user_community')['accessAdmin']['accessAdmin'])) {
                $accesses = $request->get('user_community')['accessAdmin']['accessAdmin'];
            }

            /** @var AccessAdminCommunity[] $adminAccess */
            $adminAccess = $entity->getAccess();

            foreach ($adminAccess as $a) {
                if($a->getCommunity() === $community) {
                    $em->remove($a);
                }

            }

            $em->flush();
            //$community = $entity->getCommunityAdmin();
            foreach ($accesses as $ac) {
                $acTmp = $em->getRepository('AppBundle:AdminAccess')->find($ac);

                $adminAccess = new \AppBundle\Entity\AccessAdminCommunity();
                $adminAccess->setAccess($acTmp);
                $adminAccess->setAccessUsers($entity);
                $adminAccess->setCommunity($community);
                $em->persist($adminAccess);

                $entity->addAccess($adminAccess);
                $em->flush();
            }


            $entity->setUpdateBy($this->getUser());

            if ($enabled != $entity->getEnabled()) {
                $content = $this->renderView('AppBundle:Mail:enableUserAdmin.html.twig', array(
                    'entity' => $entity,
                    'sender' => $this->getUser(),
                ));
                $this->container->get('mail')->enableUserAdmin($entity, $content, $entity->getEnabled());
            }


            $em->flush();
            $this->get('session')->getFlashBag()->add('success', "Compte communauté modifié avec succès");
            return $this->redirect($this->generateUrl('app_user_cityhall'));
        }

        return $this->render('AppBundle:User:updateCityhall.html.twig', array(
            'form' => $form->createView(),
            'entity' => $entity,
            'community' => $community,
            'tabAccess'=>$tabAccess
        ));
    }

    public function deleteCityhallAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $cityhall = $this->container->get('session.community')->getCommunity();

        /** @var User $entity */
        $entity = $em->getRepository('UserBundle:User')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        //check
        if (false === $this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            if (!$this->getUser()->isCommunitySuAdmin($cityhall)) {
                throw new AccessDeniedException();
            }

            if (!$this->isAllowedInCommunity($entity)) {
                throw new AccessDeniedException();
            }
        }

        $em->remove($entity);
        $em->flush();
        $this->get('session')->getFlashBag()->add('success', "Compte communauté supprimé avec succès");
        return $this->redirect($this->generateUrl('app_user_cityhall'));
    }

    public function accessCityhallhallAction($id, Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $cityhall = $this->container->get('session.community')->getCommunity();

        /** @var User $entity */
        $entity = $em->getRepository('UserBundle:User')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        //check
        if (false === $this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            if (!$this->getUser()->isCommunitySuAdmin($cityhall)) {
                throw new AccessDeniedException();
            }

            if (!$this->isAllowedInCommunity($entity)) {
                throw new AccessDeniedException();
            }
        }


        $password = rand(0, 9) . chr(rand(65, 90)) . rand(0, 9) . chr(rand(65, 90)) . rand(0, 9) . chr(rand(65, 90)) . rand(0, 9) . chr(rand(65, 90)) . rand(0, 9) . chr(rand(65, 90));
        $entity->setPassword($this->get('security.password_encoder')->encodePassword($entity, $password));
        $content = $this->renderView('AppBundle:Mail:adminCityhallAccess.html.twig', array(
            'user' => $entity,
            'password' => $password
        ));
        $this->container->get('mail')->adminCityhallAccess($entity, $content);
        $em->persist($entity);
        $em->flush();

        $this->get('session')->getFlashBag()->add('success', "Un email contenant les nouveaux accès de connexion vient d'être envoyé à l'admin communauté");
        return $this->redirect($this->generateUrl('app_user_cityhall'));
    }

    public function exportUsersCsvAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $cityhall = $this->container->get('session.community')->getCommunity();

        /** @var UserRepository $userRepository */
        $userRepository = $em->getRepository('UserBundle:User');

        $results = $userRepository->search(false, false, $cityhall, array('ROLE_CITIZEN'), $request->get('lastname'), $request->get('firstname'), $request->get('enabled'), $request->get('role'), $request->get('association'), $request->get('merchant'));

        $response = new StreamedResponse();
        $outputScreen = $this->container->get('outputScreen');
        $response->setCallback(function () use ($results, $outputScreen) {
            $handle = fopen('php://output', 'w+');
            fputcsv($handle, ["Prénom", "Nom", "Email", "Rôle", "Ville principale", "Centre d'interêt"], ';');
            foreach ($results as $user) {
                $firstname = $user->getFirstname();
                $lastname = $user->getLastname();
                $email = $user->getEmail();
                $role = $outputScreen->outPutRolesCsv($user);

                $city = '';
                if ($user->getCity()) {
                    $city = $user->getCity()->getName();
                }
                $interests = "";
                foreach ($user->getInterests() as $interest) {
                    $interests = $interests . ", " . $interest->getName();
                }

                fputcsv(
                    $handle,
                    [$firstname, $lastname, $email, $role, $city, $interests],
                    ';'
                );
            }

            fclose($handle);
        });
        // dump($response);die();
        $response->setStatusCode(200);
        $response->headers->set('Content-Encoding', 'UTF-8');
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="export-users.csv"');
        echo "\xEF\xBB\xBF";

        return $response;
    }


    public function migrateAccountAction(Request $request) {
        return;
    }

    /**
     * @param User $user
     */
    protected function isAllowedInCommunity($user)
    {
        $inCommunity = false;
        $currentCommunity = $this->container->get('session.community')->getCommunity();
        if ($user->isCommunityAdmin($currentCommunity) || $user->isSuAdminCommunity($currentCommunity)) {
            $inCommunity = true;
        }

        return $inCommunity;
    }

    public function exportLogsCsvAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $results = $em->getRepository('AppBundle:NavigationLog')->findAll();

        $response = new StreamedResponse();
        $outputScreen = $this->container->get('outputScreen');
        $response->setCallback(function () use ($results, $outputScreen) {
            $handle = fopen('php://output', 'w+');
            fputcsv($handle, ["user_id", "user_firstname", "user_lastname", "communities", "date"], ';');
            foreach ($results as $log) {
                $firstname = $log->getUserFirstname();
                $lastname = $log->getUserLastname();
                $id = $log->getUserId();
                $communities = $log->getCommunities();
                $date = $log->getDate()->format('Y-m-d H:i');

                fputcsv(
                    $handle,
                    [$id, $firstname, $lastname, $communities, $date],
                    ';'
                );
            }

            fclose($handle);
        });

        $response->setStatusCode(200);
        $response->headers->set('Content-Encoding', 'UTF-8');
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="export-logs.csv"');
        echo "\xEF\xBB\xBF";

        return $response;
    }

}
