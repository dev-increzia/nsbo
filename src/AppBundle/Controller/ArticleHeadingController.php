<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ArticleHeading;
use AppBundle\Entity\Community;
use AppBundle\Repository\ArticleHeadingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form\ArticleHeadingFilterType;
use AppBundle\Form\ArticleHeadingType;
use UserBundle\Repository\UserRepository;


/**
 * Class ArticleHeadingController
 * @package AppBundle\Controller
 */
class ArticleHeadingController extends Controller
{

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $community = $this->getAllowedCommunity();
        if ($community === false) {
            return $this->render('AppBundle:ArticleHeading:no_access.html.twig');
        }

        /** @var Form $form */
        $form = $this->get('form.factory')->create(ArticleHeadingFilterType::class);
        return $this->render('AppBundle:ArticleHeading:index.html.twig', array(
            'form' => $form->createView(),
            'community' => $community,
            'is_comment_active' => $community && $community->getIsCommentArticleHeadingActive()
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
                        $order = array('createAt' => strtoupper($v['dir']));
                    }
                }
            }
        }

        /** @var ArticleHeadingRepository $articleHeadingRepository */
        $articleHeadingRepository = $em->getRepository('AppBundle:ArticleHeading');

        $entities = $articleHeadingRepository->search($page, $order, $community, $request->get('dateBefore'), $request->get('dateAfter'), $request->get('enabled'));
        $countEntities = intval($articleHeadingRepository->count($community, $request->get('dateBefore'), $request->get('dateAfter'), $request->get('enabled')));
        $output = array(
            'data' => array(),
            'recordsFiltered' => $countEntities,
            'recordsTotal' => $countEntities
        );

        $outputScreen = $this->container->get('outputScreen');

        /** @var ArticleHeading $entity */
        foreach ($entities as $entity) {
            $html_admins = "<ul>";
            foreach ($entity->getAdmins() as $admin) {
                $html_admins .= "<li>".$admin->getEmail()."</li>";
            }
            $html_admins .= "</ul>";
            $output['data'][] = [
                'id' => $entity->getId(),
                'title' => $entity->getTitle(),
                'enabled' => $entity->getEnabled() ? 'Activé' : 'Désactivé',
                'emailAdmin' => $html_admins,
                'comment' => $entity->getCommunity()->getIsCommentActive() ? 'Activé' : 'Désactivé',
                'createAt' => $entity->getCreateAt() ? $entity->getCreateAt()->format('d/m/Y H:i') : '',
                'updateAt' => $entity->getUpdateAt() ? $entity->getUpdateAt()->format('d/m/Y H:i') : '',
                'actions' => $outputScreen->outPutUpdate($this->generateUrl('app_articleheading_update', array('id' => $entity->getId())))
                    . $outputScreen->outPutDelete($this->generateUrl('app_articleheading_delete', array('id' => $entity->getId()))),
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
        $entity = new ArticleHeading();

        $community = $this->getAllowedCommunity(null, true);
        if ($community === false) {
            return $this->redirect($this->generateUrl('app_articleheading'));
        }

        $em = $this->getDoctrine()->getManager();

        /** @var Form $form */
        $form = $this->get('form.factory')->create(ArticleHeadingType::class, $entity, array('update' => false,'community'=>$community));

        if ($form->handleRequest($request)->isValid()) {

            $entity->setCommunity($this->container->get('session.community')->getCommunity());
            $em->persist($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'Rubrique ajoutée avec succès');
            if ($form->get('saveAndAdd')->isClicked()) {
                return $this->redirect($this->generateUrl('app_articleheading_add'));
            }
            return $this->redirect($this->generateUrl('app_articleheading'));

        }

        return $this->render('AppBundle:ArticleHeading:add.html.twig', array(
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

        /** @var ArticleHeading $entity */
        $entity = $em->getRepository('AppBundle:ArticleHeading')->find($id);
        if (!$entity) {
            $this->get('session')->getFlashBag()->add('danger', "Cette rubrique n'existe plus");
            return $this->redirect($this->generateUrl('app_articleheading'));
        }

        $community = $this->getAllowedCommunity($entity->getCommunity());
        if ($community === false) {
            return $this->redirect($this->generateUrl('app_articleheading'));
        }

        /** @var Form $form */
        $form = $this->get('form.factory')->create(ArticleHeadingType::class, $entity, array('update' => true,'community'=>$community));
        if ($form->handleRequest($request)->isValid()) {

            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'Rubrique modifiée avec succès');
            return $this->redirect($this->generateUrl('app_articleheading'));

        }

        return $this->render('AppBundle:ArticleHeading:update.html.twig', array(
            'form' => $form->createView(),
            'entity' => $entity,
            'community' => $community != null ? $community->getId() : null
        ));
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var ArticleHeading $entity */
        $entity = $em->getRepository('AppBundle:ArticleHeading')->find($id);
        if (!$entity) {
            $this->get('session')->getFlashBag()->add('danger', "Cette rubrique n'existe plus");
            return $this->redirect($this->generateUrl('app_articleheading'));
        }

        $community = $this->getAllowedCommunity($entity->getCommunity());
        if ($community === false) {
            return $this->redirect($this->generateUrl('app_articleheading'));
        }

        $em->remove($entity);
        $em->flush();
        $this->get('session')->getFlashBag()->add('success', 'Centre d\'intérêt supprimé avec succès');
        return $this->redirect($this->generateUrl('app_mapheading'));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function isCommentActiveAction(Request $request)
    {
        $community = $this->getAllowedCommunity(null, true);
        if ($community === false) {
            return new JsonResponse(array('message' => 'failure'));
        }

        $em = $this->getDoctrine()->getManager();
        if ($request->isXmlHttpRequest()) {
            $value = $request->get('item');
            $community->setIsCommentArticleHeadingActive($value);
            $em->flush();
            return new JsonResponse(array('message' => 'success'));

        }
        return new JsonResponse(array('message' => 'failure'));
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


    public function getAdminsCommunityAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();
            $community = $em->getRepository('AppBundle:Community')->find($request->request->get('cityhall'));

            /** @var UserRepository $userRepository */
            $userRepository = $em->getRepository('UserBundle:User');

            $users = $userRepository->findAdminsByAutocomplete($community, $request->request->get('search'));
            return new JsonResponse(json_encode($users));
        } else {
            throw $this->createNotFoundException();
        }
    }

}
