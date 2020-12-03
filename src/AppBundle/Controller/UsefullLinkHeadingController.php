<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Community;
use AppBundle\Entity\UsefullLinkHeading;
use AppBundle\Form\UsefullLinkHeadingFilterType;
use AppBundle\Form\UsefullLinkHeadingType;
use AppBundle\Repository\UsefullLinkHeadingRepository;
use AppBundle\Service\OutPutScreen;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Entity\User;

/**
 * Class UsefullLinkHeadingController
 * @package AppBundle\Controller
 */
class UsefullLinkHeadingController extends Controller
{
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $community = $this->getAllowedCommunity();
        if ($community === false) {
            return $this->render('AppBundle:UsefullLinkHeading:no_access.html.twig');
        }
        /** @var User $user */
        $user= $this->getUser();

        if($user->isCommunityAdmin($community) && !$user->hasRight('usefull_links_create',$community)
            ){
            $this->get('session')->getFlashBag()->add('danger', 'Vous n\'avez pas accès à cette fonctionnalité' );
            return $this->redirect($this->generateUrl('app_homepage'));

        }

        /** @var Form $form */
        $form = $this->get('form.factory')->create(UsefullLinkHeadingFilterType::class);
        return $this->render('AppBundle:UsefullLinkHeading:index.html.twig', array(
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
        $start = $request->get('start');
        $length = $request->get('length');
        $enabled = $request->get('enabled');
        $page = ($start != 0) ? $start / $length : 0;
        $orders = $request->get('order');
        $order = array('createAt' => 'DESC');
        if (is_array($orders)) {
            foreach ($orders as $v) {
                if (isset($v['column']) && isset($v['dir'])) {
                    if ($v['column'] == '0') {
                        $order = array('id' => strtoupper($v['dir']));
                    } elseif ($v['column'] == '1') {
                        $order = array('name' => strtoupper($v['dir']));
                    } elseif ($v['column'] == '2') {
                        $order = array('createAt' => strtoupper($v['dir']));
                    }
                }
            }
        }

        /** @var UsefullLinkHeadingRepository $usefullLinkHeadingRepository */
        $usefullLinkHeadingRepository = $em->getRepository('AppBundle:UsefullLinkHeading');
        /** @var UsefullLinkHeading[] $entities */
        $entities = $usefullLinkHeadingRepository->search($page, $order, $community, $request->get('dateBefore'), $request->get('dateAfter'), $request->get('enabled'));
        $countEntities = intval($usefullLinkHeadingRepository->count($community, $request->get('dateBefore'), $request->get('dateAfter'), $request->get('enabled')));
        $output = array(
            'data' => array(),
            'recordsFiltered' => $countEntities,
            'recordsTotal' => $countEntities
        );
        /** @var OutPutScreen $outputScreen */
        $outputScreen = $this->container->get('outputScreen');
        foreach ($entities as $entity) {
            $output['data'][] = [
                'id' => $entity->getId(),
                'title' => $entity->getTitle(),
                'enabled' => $entity->getEnabled() ? 'Actif' : 'Inactif',
                'createAt' => $entity->getCreateAt() ? $entity->getCreateAt()->format('d/m/Y H:i') : '',
                'updateAt' => $entity->getUpdateAt() ? $entity->getUpdateAt()->format('d/m/Y H:i') : '',
                'actions' => $outputScreen->outPutUpdate($this->generateUrl('app_usefulllinkheading_update', array('id' => $entity->getId())))
                    . $outputScreen->outPutDelete($this->generateUrl('app_usefulllinkheading_delete', array('id' => $entity->getId()))),
            ];
        }

        return new JsonResponse($output);
    }


    public function addAction(Request $request)
    {
        $entity = new UsefullLinkHeading();
        $community = $this->getAllowedCommunity();
        /** @var User $user */
        $user= $this->getUser();
        if($user->isCommunityAdmin($community) && !$user->hasRight('usefull_links_create',$community)
        ){
            $this->get('session')->getFlashBag()->add('danger', 'Vous n\'avez pas accès à cette fonctionnalité' );
            return $this->redirect($this->generateUrl('app_homepage'));

        }
        $community = $this->getAllowedCommunity(null, true);
        if ($community === false) {
            return $this->redirect($this->generateUrl('app_usefulllinkheading'));
        }

        $em = $this->getDoctrine()->getManager();

        /** @var Form $form */
        $form = $this->get('form.factory')->create(UsefullLinkHeadingType::class, $entity);

        if ($form->handleRequest($request)->isValid()) {

            $entity->setCommunity($community);

            $em->persist($entity);

            try{
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', 'Lien ajouté avec succès');
                return $this->redirect($this->generateUrl('app_usefulllinkheading'));
            }catch(\Exception $e){
                $this->get('session')->getFlashBag()->add('danger', 'Erreur lors de mises à jour de base de données');
            }

        }

        return $this->render('AppBundle:UsefullLinkHeading:add.html.twig', array(
            'form' => $form->createView(),
            'entity' => $entity,
        ));
    }

    public function updateAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $community = $this->container->get('session.community')->getCommunity();

        /** @var User $user */
        $user= $this->getUser();
        if($user->isCommunityAdmin($community) && !$user->hasRight('usefull_links_create',$community)
        ){
            $this->get('session')->getFlashBag()->add('danger', 'Vous n\'avez pas accès à cette fonctionnalité' );
            return $this->redirect($this->generateUrl('app_homepage'));

        }
        /** @var UsefullLinkHeading $entity */
        $entity = $em->getRepository('AppBundle:UsefullLinkHeading')->find($id);
        if (!$entity) {
            $this->get('session')->getFlashBag()->add('danger', "Ce lien utile n'existe plus");
            return $this->redirect($this->generateUrl('app_usefulllinkheading'));
        }

        $community = $this->getAllowedCommunity($entity->getCommunity());
        if ($community === false) {
            return $this->redirect($this->generateUrl('app_usefulllinkheading'));
        }

        /** @var Form $form */
        $form = $this->get('form.factory')->create(UsefullLinkHeadingType::class, $entity);
        if ($form->handleRequest($request)->isValid()) {
            try{
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', 'Lien modifié avec succès');
                return $this->redirect($this->generateUrl('app_usefulllinkheading'));
            }catch(\Exception $e){
                $this->get('session')->getFlashBag()->add('danger', 'Erreur lors de mises à jour de base de données');
            }
        }

        return $this->render('AppBundle:UsefullLinkHeading:update.html.twig', array(
            'form' => $form->createView(),
            'entity' => $entity,
        ));
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction($id)
    {
        /** @var Community $community */
        $community = $this->container->get('session.community')->getCommunity();
        $em = $this->getDoctrine()->getManager();
        /** @var User $user */
        $user= $this->getUser();
        if($user->isCommunityAdmin($community) && !$user->hasRight('usefull_links_create',$community)
        ){
            $this->get('session')->getFlashBag()->add('danger', 'Vous n\'avez pas accès à cette fonctionnalité' );
            return $this->redirect($this->generateUrl('app_homepage'));

        }
        /** @var UsefullLinkHeading $entity */
        $entity = $em->getRepository('AppBundle:UsefullLinkHeading')->find($id);
        if (!$entity) {
            $this->get('session')->getFlashBag()->add('danger', "Ce lien utile n'existe plus");
            return $this->redirect($this->generateUrl('app_usefulllinkheading'));
        }

        $community = $this->getAllowedCommunity($entity->getCommunity());
        if ($community === false) {
            return $this->redirect($this->generateUrl('app_usefulllinkheading'));
        }

        $em->remove($entity);
        try{
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'Lien supprimé avec succès');
        }catch(\Exception $e){
            $this->get('session')->getFlashBag()->add('danger', 'Erreur lors de mises à jour de base de données');
        }
        return $this->redirect($this->generateUrl('app_usefulllinkheading'));
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

        return !$community || $community && $community->hasSetting('activate_usefulllinks') ? $community : false;
    }

}
