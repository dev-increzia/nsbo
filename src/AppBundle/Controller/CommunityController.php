<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Entity\CommunitySetting;
use AppBundle\Entity\CommunityUsers;
use AppBundle\Repository\CommentRepository;
use Doctrine\Common\Util\Debug;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use AppBundle\Form\CityhallFilterType;
use AppBundle\Form\CommunityType;
use AppBundle\Form\CommunityUpdateType;
use AppBundle\Entity\Community;
use AppBundle\Form\CommunityGeneralSettingsType;
use UserBundle\Entity\User;
use AppBundle\Form\SecurityType;

class CommunityController extends Controller
{

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        if(!$this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN'))
        {
            $this->get('session')->getFlashBag()->add('danger', "Vous n'avez pas accès à cette fonctionnalité");
            return $this->redirect($this->generateUrl('app_homepage'));
        }
        /** @var Form $form */
        $form = $this->get('form.factory')->create(CityhallFilterType::class);
        $community = $this->container->get('session.community')->getCommunity();
        return $this->render('AppBundle:Community:index.html.twig', array(
            'form' => $form->createView(),
            'community'=>$community
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
        $order = array('id' => 'DESC');
        if (is_array($orders)) {
            foreach ($orders as $v) {
                if (isset($v['column']) && isset($v['dir'])) {
                    if ($v['column'] == '0') {
                        $order = array('id' => strtoupper($v['dir']));
                    } elseif ($v['column'] == '1') {
                        $order = array('createAt' => strtoupper($v['dir']));
                    }
                }
            }
        }

        /** @var CommentRepository $communityRepository */
        $communityRepository = $em->getRepository('AppBundle:Community');

        $entities = $communityRepository->search($page, $order, $request->get('dateBefore'), $request->get('dateAfter'), $request->get('name'), $request->get('city'), $request->get('intercommunal'));
        $countEntities = intval($communityRepository->count($request->get('dateBefore'), $request->get('dateAfter'), $request->get('name'), $request->get('city'), $request->get('intercommunal')));
        $output = array(
            'data' => array(),
            'recordsFiltered' => $countEntities,
            'recordsTotal' => $countEntities
        );

        $outputScreen = $this->container->get('outputScreen');

        /** @var Community $entity */
        foreach ($entities as $entity) {
            $output['data'][] = [
                'id' => $entity->getId(),
                'name' => $entity->getName(),
                'type' => $entity->getIsPrivate() ? 'Privée' : 'Publique',
                
                'enabled' => $entity->getEnabled() ? 'Actif' : 'Inactif',
                'contact' => $outputScreen->outPutSuAdmins($entity),
                'createAt' => $entity->getCreateAt() ? $entity->getCreateAt()->format('d/m/Y H:i') : '',
                'updateAt' => $entity->getUpdateAt() ? $entity->getUpdateAt()->format('d/m/Y H:i') : '',
                'actions' => $outputScreen->outPutUpdate($this->generateUrl('app_community_update', array('id' => $entity->getId())))
                . $outputScreen->outPutDelete($this->generateUrl('app_community_delete', array('id' => $entity->getId())))
                . $outputScreen->outPutAccess($this->generateUrl('app_community_access', array('id' => $entity->getId()))),
            ];
        }

        return new JsonResponse($output);
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function addAction(Request $request)
    {
        if(!$this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN'))
        {
            $this->get('session')->getFlashBag()->add('danger', "Vous n'avez pas accès à cette fonctionnalité");
            return $this->redirect($this->generateUrl('app_homepage'));
        }
        $entity = new Community();
        $suAdmin = new User();
        $suAdmin->setCommunitySuAdmin($entity);
        $suAdmin->setRoles(array('ROLE_COMMUNITY_SU_ADMIN'));
        $entity->setSuAdmin($suAdmin);
        $suAdmin->setCommunityAdmin($entity);
        $suAdmin->setCreateBy($this->getUser());
        $suAdmin->setUpdateBy($this->getUser());
        $isAdmin = $this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN') ? true : false;
        $em = $this->getDoctrine()->getManager();

        /** @var Form $form */
        $form = $this->get('form.factory')->create(CommunityType::class, $entity, array(
            'isAdmin' => $isAdmin,

        ));
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $em = $this->getDoctrine()->getManager();
            $city = $em->getRepository('AppBundle:City')->findOneById($form->get('city')->getData());
            $entity->setCity($city);
            $city = $em->getRepository('AppBundle:City')->findOneById($form->get('suAdmin')->get('city')->getData());
            $entity->getSuAdmin()->setCity($city);
        }
        if ($form->isValid()) {

            $error = $this->hasValidThemes($entity);

            if (!$error) {

                $existUsername = $em->getRepository('UserBundle:User')->findOneBy(array('username' => $entity->getSuAdmin()->getUsername()));
                $exisEmail = $em->getRepository('UserBundle:User')->findOneBy(array('username' => $entity->getSuAdmin()->getEmail()));
                $existUsernameAlt = $em->getRepository('UserBundle:User')->findOneBy(array('email' => $entity->getSuAdmin()->getUsername()));
                $exisEmailAlt = $em->getRepository('UserBundle:User')->findOneBy(array('email' => $entity->getSuAdmin()->getEmail()));

                if ($existUsername || $exisEmail || $existUsernameAlt || $exisEmailAlt) {
                    $this->get('session')->getFlashBag()->add('danger', "Le nom d'utilisateur ou l'email existe");
                    return $this->render('AppBundle:Community:add.html.twig', array(
                        'form' => $form->createView(),
                        'entity' => $entity,
                    ));

                }
                if ($form->get("isPrivate")->getData()) {
                    $communityPassword = rand(0, 9) . chr(rand(65, 90)) . rand(0, 9) . chr(rand(65, 90)) . rand(0, 9) . chr(rand(65, 90)) . rand(0, 9) . chr(rand(65, 90)) . rand(0, 9) . chr(rand(65, 90));
                    $entity->setPassword($communityPassword);
                    $validity = $this->getParameter('community_validity');
                    $expirationDate = new \DateTime('now');
                    $expirationDate->modify('+' . $validity . ' day');
                    $entity->setExpirationDate($expirationDate);
                }
                if (isset($request->get('appbundle_community')['settings']['settings'])) {
                    $settings = $request->get('appbundle_community')['settings']['settings'];
                    foreach ($settings as $settingId) {
                        /** @var CommunitySetting $setting */
                        $setting = $em->getRepository('AppBundle:CommunitySetting')->find($settingId);
                        $entity->addSetting($setting);
                    }
                }


                $password = rand(0, 9) . chr(rand(65, 90)) . rand(0, 9) . chr(rand(65, 90)) . rand(0, 9) . chr(rand(65, 90)) . rand(0, 9) . chr(rand(65, 90)) . rand(0, 9) . chr(rand(65, 90));
                $suAdmin->setPassword($this->get('security.password_encoder')->encodePassword($suAdmin, $password));
                $suAdmin->setEnabled(true);
                $content = $this->renderView('AppBundle:Mail:suAdminCityhallAccess.html.twig', array(
                    'user' => $suAdmin,
                    'password' => $password
                ));
                $this->container->get('mail')->suAdminCityhallAccess($suAdmin, $content);
                $categories = $em->getRepository('AppBundle:Category')->findAll();
                foreach ($categories as $category) {
                    $suAdmin->addInterest($category);
                }
                $entity->setSuAdmin($suAdmin);
                $entity->addCommunitySuadmin($suAdmin);
                //$suAdmin->setCity($entity->getCity());


                $em->persist($suAdmin);
                $em->persist($entity);
                $communityUser = new CommunityUsers();
                $communityUser->setUser($suAdmin);
                $communityUser->setCommunity($entity);
                $communityUser->setType('approved')
                    ->setFollow(0);
                $em->persist($communityUser);
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', 'Communauté ajoutée avec succès');
                return $this->redirect($this->generateUrl('app_community'));
            } else {
                $this->get('session')->getFlashBag()->add('danger', $error);
            }
        }

        return $this->render('AppBundle:Community:add.html.twig', array(
                    'form' => $form->createView(),
                    'entity' => $entity,
                    'isAdmin' => $isAdmin,
        ));
    }

    /**
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function updateAction($id, Request $request)
    {
        if(!$this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN'))
        {
            $this->get('session')->getFlashBag()->add('danger', "Vous n'avez pas accès à cette fonctionnalité");
            return $this->redirect($this->generateUrl('app_homepage'));
        }
        $em = $this->getDoctrine()->getManager();

        /** @var Community $entity */
        $entity = $em->getRepository('AppBundle:Community')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Cityhall entity.');
        }

        $showMdp = false;

        //check
        if (false === $this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            if (!$this->getUser()->getCommunitySuAdmin()) {
                throw new AccessDeniedException();
            }

            if ($entity != $this->container->get('session.community')->getCommunity()) {
                throw new AccessDeniedException();
            }
        }
        if($entity->getCommunitySuadmins()->contains($this->getUser())) {
            $showMdp = true;
        }

        $isAdmin = ($this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN') || $showMdp) ? true : false;



        /** @var Form $form */
        $form = $this->get('form.factory')->create(CommunityUpdateType::class, $entity, array(
            'isAdmin' => $isAdmin,

        ));
        
        $isPrivate = $entity->getIsPrivate();
        if ($form->handleRequest($request)->isValid()) {
            if ($entity->getSuAdmin()) {
                $entity->getSuAdmin()->setUpdateBy($this->getUser());
            }

            $error = $this->hasValidThemes($entity);

            if (!$error) {
                if ($form->get("isPrivate")->getData() && !$isPrivate) {
                    $communityPassword = rand(0, 9) . chr(rand(65, 90)) . rand(0, 9) . chr(rand(65, 90)) . rand(0, 9) . chr(rand(65, 90)) . rand(0, 9) . chr(rand(65, 90)) . rand(0, 9) . chr(rand(65, 90));
                    $entity->setPassword($communityPassword);
                    $validity = $this->getParameter('community_validity');
                    $expirationDate = new \DateTime('now');
                    $expirationDate->modify('+' . $validity . ' day');
                    $entity->setExpirationDate($expirationDate);
                } elseif (!$form->get("isPrivate")->getData()) {
                    $entity->setPassword(null);
                    $entity->setExpirationDate(null);
                }
                foreach ($entity->getSettings() as $setting) {
                    $entity->removeSetting($setting);
                }
                $em->flush();
                $settings = array();
                if (isset($request->get('appbundle_community')['settings']['settings'])) {
                    $settings = $request->get('appbundle_community')['settings']['settings'];
                }

                $comunitySettings = $entity->getSettings();

                foreach ($comunitySettings as $communitySetting) {
                    $em->remove($communitySetting);
                }
                $em->flush();
                foreach ($settings as $settingId) {
                    $setting = $em->getRepository('AppBundle:CommunitySetting')->find($settingId);
                    $entity->addSetting($setting);
                }

                $em->flush();
                $this->get('session')->getFlashBag()->add('success', "Communauté modifiée avec succès");

                if (false === $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
                    return $this->redirect($this->generateUrl('app_community_update', array('id' => $id)));
                }
                return $this->redirect($this->generateUrl('app_community'));
            } else {
                $this->get('session')->getFlashBag()->add('danger', $error);
            }
        }

        return $this->render('AppBundle:Community:update.html.twig', array(
                    'form' => $form->createView(),
                    'entity' => $entity,
                    'isAdmin' => $isAdmin,
        ));
    }

    public function viewAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:Community')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Cityhall entity.');
        }

        //check
        if (false === $this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            if ($entity != $this->container->get('session.community')->getCommunity()) {
                throw new AccessDeniedException();
            }
        }

        return $this->render('AppBundle:Community:view.html.twig', array(
                    'entity' => $entity,
        ));
    }

    public function deleteAction($id)
    {
        if(!$this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN'))
        {
            $this->get('session')->getFlashBag()->add('danger', "Vous n'avez pas accès à cette fonctionnalité");
            return $this->redirect($this->generateUrl('app_homepage'));
        }
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:Community')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Community entity.');
        }
        $em->remove($entity);
        $em->flush();
        $this->get('session')->getFlashBag()->add('success', "Communauté supprimée avec succès");
        return $this->redirect($this->generateUrl('app_community'));
    }

    public function accessAction($id, Request $request)
    {
        if(!$this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN'))
        {
            $this->get('session')->getFlashBag()->add('danger', "Vous n'avez pas accès à cette fonctionnalité");
            return $this->redirect($this->generateUrl('app_homepage'));
        }
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:Community')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Community entity.');
        }

        if (!$entity->getSuAdmin()) {
            throw $this->createNotFoundException('Unable to find Community admin entity.');
        }

        $password = rand(0, 9) . chr(rand(65, 90)) . rand(0, 9) . chr(rand(65, 90)) . rand(0, 9) . chr(rand(65, 90)) . rand(0, 9) . chr(rand(65, 90)) . rand(0, 9) . chr(rand(65, 90));
        $entity->getSuAdmin()->setPassword($this->get('security.password_encoder')->encodePassword($entity->getSuAdmin(), $password));
        $content = $this->renderView('AppBundle:Mail:suAdminCityhallAccess.html.twig', array(
            'user' => $entity->getSuAdmin(),
            'password' => $password
        ));
        $this->container->get('mail')->suAdminCityhallAccess($entity->getSuAdmin(), $content);
        $em->persist($entity->getSuAdmin());
        $em->flush();

        $this->get('session')->getFlashBag()->add('success', "Un email contenant les nouveaux accès de connexion vient d'être envoyé au super-admin Communautée");
        return $this->redirect($this->generateUrl('app_community'));
    }

    public function securityAction(Request $request)
    {

        /** @var Community $community */
        $community = $this->container->get('session.community')->getCommunity();
        /** @var User $user */
        $user = $this->getUser();
        if (false === $this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            if ($this->container->get('security.authorization_checker')->isGranted('ROLE_COMMUNITY_SU_ADMIN') && !$this->getUser()->isSuAdminCommunity($community)) {


            }
            if ($this->container->get('security.authorization_checker')->isGranted('ROLE_COMMUNITY_SU_ADMIN') && $this->getUser()->isSuAdminCommunity($community)) {
                if (!$community->getIsPrivate()) {
                    $this->get('session')->getFlashBag()->add('danger', "La communautée est publique");
                    return $this->redirect($this->generateUrl('app_community'));
                }
            }
            if (!$this->container->get('security.authorization_checker')->isGranted('ROLE_COMMUNITY_SU_ADMIN') && !$this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){
                if(!$community->getIsPrivate()){
                    $this->get('session')->getFlashBag()->add('danger', "La communautée est publique");

                    return $this->redirect($this->generateUrl('app_homepage'));
                }else{
                    if(!$user->hasRight('password_manage',$community))
                    {
                        $this->get('session')->getFlashBag()->add('danger', "Vous n'avez pas accés à cette fonctionnalité");
                        return $this->redirect($this->generateUrl('app_homepage'));
                    }
                }


            }

        }
        $em = $this->getDoctrine()->getManager();

        /** @var Form $form */
        $form = $this->get('form.factory')->create(SecurityType::class);
        if ($form->handleRequest($request)->isValid()) {
            if (!$community) {
                $this->get('session')->getFlashBag()->add('danger', "L'action n'a pas été effectuée. Vous devez sélectionner une communautée!");
                return $this->redirect($this->generateUrl('app_community_security'));
            }
            if (!$community->getIsPrivate()) {
                $this->get('session')->getFlashBag()->add('danger', "La communautée est publique");
                return $this->redirect($this->generateUrl('app_community_security'));
            }
            $communityPassword = rand(0, 9) . chr(rand(65, 90)) . rand(0, 9) . chr(rand(65, 90)) . rand(0, 9) . chr(rand(65, 90)) . rand(0, 9) . chr(rand(65, 90)) . rand(0, 9) . chr(rand(65, 90));
            $community->setPassword($communityPassword);
            $validity = $this->getParameter('community_validity');
            $expirationDate = new \DateTime('now');
            $expirationDate->modify('+' . $validity . ' day');
            $community->setExpirationDate($expirationDate);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', "Le mot de passe est modifié avec succès.");
            return $this->redirect($this->generateUrl('app_community_security'));
        }



        return $this->render('AppBundle:Community:security.html.twig', array(
                    'form' => $form->createView(),
                    'community' => $community
        ));
    }
    
    
    public function settingsAction(Request $request)
    {
        $community = $this->container->get('session.community')->getCommunity();
        
        $em = $this->getDoctrine()->getManager();
        
        if (!$community) {
            $this->get('session')->getFlashBag()->add('danger', 'Vous devez selectionner une Communauté');
            $referer = $request->headers->get('referer');
            return $this->redirect($referer);
        }
            
        /** @var Form $form */
        $form = $this->get('form.factory')->create(CommunityGeneralSettingsType::class, $community);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $em = $this->getDoctrine()->getManager();
            $city = $em->getRepository('AppBundle:City')->findOneById($form->get('city')->getData());
            $community->setCity($city);
        }
        if ($form->isValid()) {
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'Informations Communauté modifiées avec succès');
            return $this->redirect($this->generateUrl('app_community_settings'));
        }
        
        return $this->render('AppBundle:Community:settings.html.twig', array(
                    'form'=>$form->createView(),
                    'community' => $community,
        ));
    }

    /**
     * @param Community $entity
     */
    protected function hasValidThemes($entity) {

        $error = null;

        // Verification si la communaute a un moins 1 categorie de chaque
        $categories = array();
        if ($entity->getCategories()) {
            /** @var Category $category */
            foreach ($entity->getCategories() as $category) {
                $categories[$category->getType()][] = $category;
            }
        }

        if (count($categories) < 2) {
            $error = 'Votre communauté doit au moins avoir deux thèmes : un thème "Pour les écrans A la Une et Agenda" et un thème "Pour l\'écran des Bons Plans"';
        }

        return $error;
    }
}
