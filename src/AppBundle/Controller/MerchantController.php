<?php

namespace AppBundle\Controller;

use AppBundle\Repository\MerchantRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use AppBundle\Form\MerchantFilterType;
use AppBundle\Form\MerchantType;
use AppBundle\Entity\Merchant;
use AppBundle\Form\AdminType;
use UserBundle\Entity\User;
use UserBundle\Repository\UserRepository;

class MerchantController extends Controller
{
    public function indexAction()
    {
        $cityhall = $this->container->get('session.community')->getCommunity();
        /** @var User $user */
        $user= $this->getUser();
        if($user->isCommunityAdmin($cityhall) && !$user->hasRight('merchant_aprove',$cityhall)
        ){
            $this->get('session')->getFlashBag()->add('danger', 'Vous n\'avez pas accès à cette fonctionnalité' );
            return $this->redirect($this->generateUrl('app_homepage'));

        }
        /** @var Form $form */
        $form = $this->get('form.factory')->create(MerchantFilterType::class);
        return $this->render('AppBundle:Merchant:index.html.twig', array(
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
        $cityhall = $this->container->get('session.community')->getCommunity();

        /** @var MerchantRepository $merchantRepository */
        $merchantRepository = $em->getRepository('AppBundle:Merchant');

        $entities = $merchantRepository->search($page, $order, $cityhall, $request->get('dateBefore'), $request->get('dateAfter'), $request->get('name'), $request->get('enabled'), $request->get('moderate'), $request->get('wait'));
        $countEntities = intval($merchantRepository->count($cityhall, $request->get('dateBefore'), $request->get('dateAfter'), $request->get('name'), $request->get('enabled'), $request->get('moderate'), $request->get('wait')));
        $output = array(
            'data' => array(),
            'recordsFiltered' => $countEntities,
            'recordsTotal' => $countEntities
        );

        $outputScreen = $this->container->get('outputScreen');
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
        foreach ($entities as $entity) {
            $output['data'][] = [
                'logo' => $entity->getImage() && $entity->getImage()->getFile() ? $outputScreen->outPutImage($request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . $helper->asset($entity->getImage(), 'file'), $entity->getImage()->getId()) : '',
                'name' => $outputScreen->outPutInfo($this->generateUrl('app_merchant_view', array('id' => $entity->getId())), 'merchantView' . $entity->getId(), $entity->getName()),
                'category' => $entity->getCategory() ? $entity->getCategory()->getName() : '',
                'enabled' => $entity->getEnabled() ? 'Actif' : 'Inactif',
                'createAt' => $entity->getCreateAt() ? $entity->getCreateAt()->format('d/m/Y H:i') : '',
                'updateAt' => $entity->getUpdateAt() ? $entity->getUpdateAt()->format('d/m/Y H:i') : '',
                'actions' => $outputScreen->outPutUpdate($this->generateUrl('app_merchant_update', array('id' => $entity->getId())))
                    . $outputScreen->outPutDelete($this->generateUrl('app_merchant_delete', array('id' => $entity->getId())))
                    . $outputScreen->outAddAdmin($this->generateUrl('app_merchant_add_admin', array('id' => $entity->getId()))),
            ];
        }

        return new JsonResponse($output);
    }

    public function viewAction($id, Request $request)
    {
        $cityhall = $this->container->get('session.community')->getCommunity();
        $em = $this->getDoctrine()->getManager();
        /** @var User $user */
        $user= $this->getUser();
        if($user->isCommunityAdmin($cityhall) && !$user->hasRight('merchant_aprove',$cityhall)
        ){
            $this->get('session')->getFlashBag()->add('danger', 'Vous n\'avez pas accès à cette fonctionnalité' );
            return $this->redirect($this->generateUrl('app_homepage'));

        }

        /** @var Merchant $entity */
        $entity = $em->getRepository('AppBundle:Merchant')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Merchant entity.');
        }

        //check
        if (false === $this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            if ($entity->getCommunity() != $this->container->get('session.community')->getCommunity()) {
                throw new AccessDeniedException();
            }
        }


        if ($request->isXmlHttpRequest()) {
            $content = $this->renderView('AppBundle:Merchant:view.html.twig', array(
                'entity' => $entity,
            ));

            return new JsonResponse(array('content' => $content));
        } else {
            throw $this->createNotFoundException('');
        }
    }

    public function addAction(Request $request)
    {
        $entity = new Merchant();
        $community = $this->container->get('session.community')->getCommunity(true);
        /** @var User $user */
        $user= $this->getUser();
        if($user->isCommunityAdmin($community) && !$user->hasRight('merchant_aprove',$community)
        ){
            $this->get('session')->getFlashBag()->add('danger', 'Vous n\'avez pas accès à cette fonctionnalité' );
            return $this->redirect($this->generateUrl('app_homepage'));

        }

        /** @var Form $form */
        $form = $this->get('form.factory')->create(MerchantType::class, $entity,array('community'=>$community));
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

            /** @var User $suAdmin */
            $suAdmin = $em->getRepository('UserBundle:User')->findOneByEmail($suAdminEmail);
            if (!$suAdmin) {
                $this->get('session')->getFlashBag()->add('danger', "Aucun compte super-admin valide associé");
            } else {
                $entity->setSuAdmin($suAdmin);
                $em->persist($entity);
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', 'Commerçant ajouté avec succès');

                $message = "Vous êtes désormais le superadmin du partenaire " . $entity->getName() . ". ";
                $this->container->get('notification')->notify($suAdmin, 'admin', $message, false);
                $this->container->get('mobile')->pushNotification($suAdmin, 'NOUS-ENSEMBLE ', "", false, false, 'on');
                $content = $this->renderView('AppBundle:Mail:addSuAdminMerchant.html.twig', array(
                    'entity' => $entity,
                    'sender' => $this->getUser(),
                ));
                $this->container->get('mail')->addSuAdminMerchant($suAdmin, $content, $entity);

                return $this->redirect($this->generateUrl('app_merchant'));
            }
        }

        return $this->render('AppBundle:Merchant:add.html.twig', array(
            'form' => $form->createView(),
            'entity' => $entity,
            'cityhall' => $community->getId()
        ));
    }

    public function updateAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $community = $this->container->get('session.community')->getCommunity(true);
        /** @var User $user */
        $user= $this->getUser();
        if($user->isCommunityAdmin($community) && !$user->hasRight('merchant_aprove',$community)
        ){
            $this->get('session')->getFlashBag()->add('danger', 'Vous n\'avez pas accès à cette fonctionnalité' );
            return $this->redirect($this->generateUrl('app_homepage'));

        }

        /** @var Merchant $entity */
        $entity = $em->getRepository('AppBundle:Merchant')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Merchant entity.');
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
        $form = $this->get('form.factory')->create(MerchantType::class, $entity,array('community'=>$community));
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

            /** @var User $suAdmin */
            $suAdmin = $em->getRepository('UserBundle:User')->findOneByEmail($suAdminEmail);
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
                    $message = "Vous êtes désormais le superadmin du partenaire " . $entity->getName() . ". ";
                    $this->container->get('notification')->notify($suAdmin, 'admin', $message, false);
                    $this->container->get('mobile')->pushNotification($suAdmin, 'NOUS-ENSEMBLE ', "", false, false, 'on');
                    $content = $this->renderView('AppBundle:Mail:addSuAdminMerchant.html.twig', array(
                        'entity' => $entity,
                        'sender' => $this->getUser(),
                    ));
                    $this->container->get('mail')->addSuAdminMerchant($suAdmin, $content, $entity);

                    //notifi old admin
                    if ($suAdminOld) {
                        $message = "Vous n'êtes plus le super-administrateur du partenaire " . $entity->getName() . ". ";
                        $this->container->get('notification')->notify($suAdminOld, 'admin', $message, false);
                        $this->container->get('mobile')->pushNotification($suAdminOld, 'NOUS-ENSEMBLE ', "", false, false, 'on');
                        $content2 = $this->renderView('AppBundle:Mail:removeSuAdminMerchant.html.twig', array(
                            'entity' => $entity,
                            'sender' => $this->getUser(),
                        ));
                        $this->container->get('mail')->removeSuAdminMerchant($suAdminOld, $content2, $entity);
                    }
                }

                $em->flush();
                $this->get('session')->getFlashBag()->add('success', "Commerçant modifié avec succès");
                return $this->redirect($this->generateUrl('app_merchant'));
            }
        }

        return $this->render('AppBundle:Merchant:update.html.twig', array(
            'form' => $form->createView(),
            'entity' => $entity,
            'cityhall' => $entity->getCommunity()->getId()
        ));
    }

    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $community = $this->container->get('session.community')->getCommunity();
        $user= $this->getUser();
        if($user->isCommunityAdmin($community) && !$user->hasRight('merchant_aprove',$community)
        ){
            $this->get('session')->getFlashBag()->add('danger', 'Vous n\'avez pas accès à cette fonctionnalité' );
            return $this->redirect($this->generateUrl('app_homepage'));

        }

        /** @var Merchant $entity */
        $entity = $em->getRepository('AppBundle:Merchant')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Merchant entity.');
        }

        //check
        if (false === $this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            if ($entity->getCommunity() != $this->container->get('session.community')->getCommunity()) {
                throw new AccessDeniedException();
            }
        }
        $em->remove($entity);
        $em->flush();
        $this->get('session')->getFlashBag()->add('success', "Commerçant supprimé avec succès");
        return $this->redirect($this->generateUrl('app_merchant'));
    }

    public function suAdminAutocompleteAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();

            /** @var UserRepository $userRepository */
            $userRepository = $em->getRepository('UserBundle:User');

            $users = $userRepository->findAllCitizensByCityhallAutocomplete($request->request->get('cityhall'), $request->request->get('search'), false, true);
            return new JsonResponse(json_encode($users));
        } else {
            throw $this->createNotFoundException();
        }
    }

    public function moderateAction($id, Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();

            /** @var Merchant $entity */
            $entity = $em->getRepository('AppBundle:Merchant')->find($id);
            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Merchant entity.');
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
            $this->get('session')->getFlashBag()->add('success', "Commerçant modéré avec succès");
            return new JsonResponse(array());
        } else {
            throw $this->createNotFoundException('');
        }
    }

    public function activateAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Merchant $entity */
        $entity = $em->getRepository('AppBundle:Merchant')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Merchant entity.');
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
            $this->get('session')->getFlashBag()->add('success', "Commerçant désactivé avec succès");
        } else {
            $this->get('session')->getFlashBag()->add('success', "Commerçant activé avec succès");
        }


        $referer = $request->headers->get('referer');
        if ($referer) {
            return $this->redirect($referer);
        }
        return $this->redirect($this->generateUrl('app_merchant'));
    }

    /**
     * @param Merchant $entity
     */
    private function _activate($entity)
    {
        $content = $this->renderView('AppBundle:Mail:enableMerchant.html.twig', array(
            'entity' => $entity,
            'sender' => $this->getUser(),
        ));
        $this->container->get('mail')->enableMerchant($entity->getSuAdmin(), $content, $entity->getEnabled());

        $message = "Votre partenaire " . $entity->getName() . ' a été ' . ($entity->getEnabled() ? 'activé' : 'désactivé') . '';
        $this->container->get('notification')->notify($entity->getSuAdmin(), 'merchant', $message, false, $entity);
        $this->container->get('mobile')->pushNotification($entity->getSuAdmin(), 'NOUS-ENSEMBLE ', "", false, false, 'on');
        $this->container->get('mobile')->pushNotification($entity->getSuAdmin(), 'NOUS-ENSEMBLE ', $message, false, false, 'off', false, ($entity->getEnabled() == 'accepted' ? $entity->getId() : false));
    }

    /**
     * @param Merchant $entity
     */
    private function _moderate($entity)
    {
        $content = $this->renderView('AppBundle:Mail:moderateMerchant.html.twig', array(
            'entity' => $entity,
            'sender' => $this->getUser(),
        ));
        $this->container->get('mail')->moderateMerchant($entity->getSuAdmin(), $content);

        $message = "Votre commerce / partenaire " . $entity->getName() . ' a été ' . ($entity->getModerate() == 'accepted' ? 'accepté' : 'refusé') . '';
        if ($entity->getModerate() == 'accepted') {
            $this->container->get('notification')->notify($entity->getSuAdmin(), 'merchant', $message, false, $entity);
        } else {
            $this->container->get('notification')->notify($entity->getSuAdmin(), 'merchantRefused', $message, false, $entity);
        }

        $this->container->get('mobile')->pushNotification($entity->getSuAdmin(), 'NOUS-ENSEMBLE ', "", false, false, 'on');

        $this->container->get('mobile')->pushNotification($entity->getSuAdmin(), 'NOUS-ENSEMBLE ', "Votre partenaire " . $entity->getName() . ' a été ' . ($entity->getModerate() == 'accepted' ? 'accepté' : 'refusé') . '', false, false, 'off', ($entity->getModerate() == 'accepted' ? $entity->getId() : false));
    }

    public function addAdminAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Merchant $entity */
        $entity = $em->getRepository('AppBundle:Merchant')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Merchant entity.');
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

            /** @var User $admin */
            $admin = $em->getRepository('UserBundle:User')->findOneByEmail($adminEmail);
            if (!$admin) {
                $this->container->get('mail')->sendInvitationMail($adminEmail, $this->getUser());
                $this->get('session')->getFlashBag()->add('success', "Cet utilisateur ne fait pas partie de NOUS-Ensemble. Un email a été envoyée pour lui conseiller de nous rejoindre.");
            } else {
                if (!$entity->getAdmins()->contains($admin)) {
                    $entity->addAdmin($admin);
                    $message = "Vous êtes désormais un administrateur du partenaire " . $entity->getName() . ". ";
                    $this->container->get('notification')->notify($admin, 'admin', $message, false);
                    $this->container->get('mobile')->pushNotification($admin, 'NOUS-ENSEMBLE ', "", false, false, 'on');
                    $em->flush();
                    $this->container->get('mail')->sendInfoAdminMail($adminEmail, $this->getUser(), 'merchant', $entity);
                    $this->get('session')->getFlashBag()->add('success', "Cet utilisateur est désormais un administrateur du partenaire " . $entity->getName() . ".");
                } else {
                    $this->get('session')->getFlashBag()->add('danger', "Cet utilisateur est déja un administrateur du partenaire " . $entity->getName() . ".");
                }
            }
            return $this->redirect($this->generateUrl('app_merchant_add_admin', array('id' => $entity->getId())));
        }

        return $this->render('AppBundle:Merchant:admins.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView(),
            'cityhall' => $community->getId()
        ));
    }

    public function deleteAdminAction($merchant, $id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Merchant $entity */
        $entity = $em->getRepository('AppBundle:Merchant')->find($merchant);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Merchant entity.');
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
                $this->get('session')->getFlashBag()->add('danger', "Cet utilisateur n'est pas un administrateur du partenaire " . $entity->getName() . ".");
            } else {
                $entity->removeAdmin($admin);
                $em->flush();
                $message = "Vous n'êtes plus un administrateur du partenaire " . $entity->getName() . ". ";
                $this->container->get('notification')->notify($admin, 'admin', $message, false);
                $this->container->get('mobile')->pushNotification($admin, 'NOUS-ENSEMBLE ', "", false, false, 'on');
                $this->get('session')->getFlashBag()->add('success', "Cet utilisateur n'est plus un administrateur du partenaire " . $entity->getName() . ".");
            }
        }
        return $this->redirect($this->generateUrl('app_merchant_add_admin', array('id' => $entity->getId())));
    }
}
