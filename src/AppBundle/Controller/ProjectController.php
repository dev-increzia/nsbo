<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Community;
use AppBundle\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use AppBundle\Form\ProjectFilterType;
use AppBundle\Form\ProjectType;
use AppBundle\Entity\Article;

class ProjectController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $community = $this->getAllowedCommunity();
        if ($community === false) {
            return $this->render('AppBundle:Project:no_access.html.twig');
        }
        $user= $this->getUser();
        $hasArticleHeadingCount = 0;
        $hasArticleHeading = false;
        if($community) {
            /** @var ArticleHeading[] $headings */
            $headings = $community->getArticleHeadings();
            foreach ($headings as $heading) {
                if ($heading->getEmailAdmin() == $user->getEmail() && $heading->getEnabled()) {
                    $hasArticleHeadingCount++;
                }
            }
            if ($hasArticleHeadingCount > 0) {
                $hasArticleHeading = true;
            }


        if(!$community->hasSetting('activate_articles') ||
            (!$this->container->get('security.authorization_checker')->isGranted('ROLE_COMMUNITY_ADMIN') && $this->container->get('security.authorization_checker')->isGranted('ROLE_COMMUNITY_ADMIN') && !$hasArticleHeading))
        {
            $this->get('session')->getFlashBag()->add('danger', 'Vous n\'avez pas accès à cette fonctionnalité' );
            return $this->redirect($this->generateUrl('app_homepage'));
        }
    }
        /** @var Form $form */
        $form = $this->get('form.factory')->create(ProjectFilterType::class);
        return $this->render('AppBundle:Project:index.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function indexGridAction(Request $request)
    {
        $community = $this->getAllowedCommunity();
        if ($community === false) {
            return new JsonResponse(array());
        }

        $em = $this->getDoctrine()->getManager();
        $page = (int)$request->get('page');
        $cityhall = $this->container->get('session.community')->getCommunity();

        /** @var ArticleRepository $articleRepository */
        $articleRepository = $em->getRepository('AppBundle:Article');

        $entities = $articleRepository->search($page, array('createAt' => 'DESC'), $cityhall, $request->get('dateBefore'), $request->get('dateAfter'), $request->get('title'), 'community', $request->get('enabled'));
        $content = $this->renderView('AppBundle:Project:projects.html.twig', array(
            'projects' => $entities,
        ));

        return new JsonResponse(array('content' => $content, 'count' => count($entities)));
    }

    public function addAction(Request $request)
    {
        $entity = new Article();
        $entity->setType('cityhall');

        $community = $this->getAllowedCommunity(null, true);
        if ($community === false) {
            return $this->redirect($this->generateUrl('app_project'));
        }

        if(!$this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN'))
        {
            $this->get('session')->getFlashBag()->add('danger', 'Vous n\'avez pas accès à cette fonctionnalité' );
            return $this->redirect($this->generateUrl('app_homepage'));
        }


        /** @var Form $form */
        $form = $this->get('form.factory')->create(ProjectType::class, $entity, array('community' => $community));

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $em = $this->getDoctrine()->getManager();
            $city = $em->getRepository('AppBundle:City')->findOneById($form->get('city')->getData());
            $entity->setCity($city);
        }
        if ($form->isValid()) {
            $entity->setCreateBy($this->getUser());
            $entity->setUser($this->getUser());
            $entity->setUpdateBy($this->getUser());
            $entity->setCommunity($community);
            $entity->setType('community');
            $entity->setPublicAt(new \DateTime('now'));
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'Projet ajouté avec succès');
            return $this->redirect($this->generateUrl('app_project'));
        }

        return $this->render('AppBundle:Project:add.html.twig', array(
            'form' => $form->createView(),
            'entity' => null,
            'community' => $community
        ));
    }

    public function viewAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Article $entity */
        $entity = $em->getRepository('AppBundle:Article')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Article entity.');
        }

        $community = $this->getAllowedCommunity($entity->getCommunity(), true);
        if ($community === false) {
            $this->get('session')->getFlashBag()->add('danger', "Cet article n'existe plus");
            return $this->redirect($this->generateUrl('app_project'));
        }

        if ($request->isXmlHttpRequest()) {
            $content = $this->renderView('AppBundle:Project:view.html.twig', array(
                'entity' => $entity,
                'community' => $community
            ));

            return new JsonResponse(array('content' => $content));
        } else {
            throw $this->createNotFoundException('');
        }
    }

    /**
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function updateAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        if(!$this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN'))
        {
            $this->get('session')->getFlashBag()->add('danger', 'Vous n\'avez pas accès à cette fonctionnalité' );
            return $this->redirect($this->generateUrl('app_homepage'));
        }
        /** @var Article $entity */
        $entity = $em->getRepository('AppBundle:Article')->find($id);
        if (!$entity) {
            $this->get('session')->getFlashBag()->add('danger', "Cet article n'existe plus");
            return $this->redirect($this->generateUrl('app_project'));
        }

        $community = $this->getAllowedCommunity($entity->getCommunity());
        if ($community === false) {
            return $this->redirect($this->generateUrl('app_project'));
        }

        if ($entity->getType() != 'community') {
            return $this->redirect($this->generateUrl('app_article_update', array('id' => $entity->getId())));
        }

        /** @var Form $form */
        $form = $this->get('form.factory')->create(ProjectType::class, $entity, array('community' => $entity->getCommunity()));

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $em = $this->getDoctrine()->getManager();
            $city = $em->getRepository('AppBundle:City')->findOneById($form->get('city')->getData());
            $entity->setCity($city);
        }
        if ($form->isValid()) {
            $entity->setUpdateBy($this->getUser());
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', "Projet modifié avec succès");
            return $this->redirect($this->generateUrl('app_project'));
        }

        return $this->render('AppBundle:Project:update.html.twig', array(
            'form' => $form->createView(),
            'entity' => $entity,
            'community' => $community
        ));
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        if(!$this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN'))
        {
            $this->get('session')->getFlashBag()->add('danger', 'Vous n\'avez pas accès à cette fonctionnalité' );
            return $this->redirect($this->generateUrl('app_homepage'));
        }
        /** @var Article $entity */
        $entity = $em->getRepository('AppBundle:Article')->find($id);
        if (!$entity) {
            $this->get('session')->getFlashBag()->add('danger', "Cet article n'existe plus");
            return $this->redirect($this->generateUrl('app_project'));
        }

        $community = $this->getAllowedCommunity($entity->getCommunity());
        if ($community === false) {
            return $this->redirect($this->generateUrl('app_project'));
        }

        if ($entity->getType() != 'community') {
            return $this->redirect($this->generateUrl('app_article_delete', array('id' => $entity->getId())));
        }

        $em->remove($entity);
        $em->flush();
        $this->get('session')->getFlashBag()->add('success', "Projet supprimé avec succès");
        return $this->redirect($this->generateUrl('app_project'));
    }

    /**
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function activateAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        if(!$this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN') && !$this->container->get('security.authorization_checker')->isGranted('ROLE_COMMUNITY_SU_ADMIN'))
        {
            $this->get('session')->getFlashBag()->add('danger', 'Vous n\'avez pas accès à cette fonctionnalité' );
            return $this->redirect($this->generateUrl('app_homepage'));
        }
        /** @var Article $entity */
        $entity = $em->getRepository('AppBundle:Article')->find($id);
        if (!$entity) {
            $this->get('session')->getFlashBag()->add('danger', "Cet article n'existe plus");
            return $this->redirect($this->generateUrl('app_project'));
        }

        if ($entity->getType() != 'community') {
            return $this->redirect($this->generateUrl('app_article_activate', array('id' => $entity->getId())));
        }

        $community = $this->getAllowedCommunity($entity->getCommunity());
        if ($community === false) {
            return $this->redirect($this->generateUrl('app_project'));
        }

        $enabled = $entity->getEnabled();
        $entity->setEnabled($enabled ? false : true);
        $em->flush();


        if ($enabled) {
            $this->get('session')->getFlashBag()->add('success', "Projet désactivé avec succès");
        } else {
            $this->get('session')->getFlashBag()->add('success', "Projet activé avec succès");
        }

        $referer = $request->headers->get('referer');
        if ($referer) {
            return $this->redirect($referer);
        }
        return $this->redirect($this->generateUrl('app_project'));
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

        return !$community || $community && $community->hasSetting('activate_articles') ? $community : false;
    }
}
