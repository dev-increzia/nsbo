<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Community;
use AppBundle\Entity\InterestCategory;
use AppBundle\Repository\MapHeadingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use AppBundle\Form\MapHeadingType;

use AppBundle\Entity\MapHeading;

/**
 * Class MapHeadingController
 * @package AppBundle\Controller
 */
class MapHeadingController extends Controller
{

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $community = $this->getAllowedCommunity();
        if ($community === false) {
            return $this->render('AppBundle:MapHeading:no_access.html.twig');
        }

        /** @var Form $form */
        $form = $this->get('form.factory')->create(\AppBundle\Form\MapHeadingFilterType::class);
        return $this->render('AppBundle:MapHeading:index.html.twig', array(
            'form' => $form->createView(),
        ));

    }

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

        $community = $this->container->get('session.community')->getCommunity();

        /** @var MapHeadingRepository $mapHeadingRepository */
        $mapHeadingRepository = $em->getRepository('AppBundle:MapHeading');

        $entities = $mapHeadingRepository->search($page, $order, $community, $request->get('dateBefore'), $request->get('dateAfter'), $request->get('enabled'));
        $countEntities = intval($mapHeadingRepository->count($community, $request->get('dateBefore'), $request->get('dateAfter'), $request->get('enabled')));
        $output = array(
            'data' => array(),
            'recordsFiltered' => $countEntities,
            'recordsTotal' => $countEntities
        );

        $outputScreen = $this->container->get('outputScreen');
        foreach ($entities as $entity) {
            $output['data'][] = [
                'id' => $entity->getId(),
                'title' => $entity->getTitle(),
                'enabled' => $entity->getEnabled() ? 'Actif' : 'Inactif',
                'createAt' => $entity->getCreateAt() ? $entity->getCreateAt()->format('d/m/Y H:i') : '',
                'updateAt' => $entity->getUpdateAt() ? $entity->getUpdateAt()->format('d/m/Y H:i') : '',
                'actions' => $outputScreen->outPutUpdate($this->generateUrl('app_mapheading_update', array('id' => $entity->getId())))
                    . $outputScreen->outPutDelete($this->generateUrl('app_mapheading_delete', array('id' => $entity->getId()))),
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
        $entity = new MapHeading();

        $community = $this->getAllowedCommunity(null, true);
        if ($community === false) {
            return $this->redirect($this->generateUrl('app_mapheading'));
        }

        $em = $this->getDoctrine()->getManager();

        /** @var Form $form */
        $form = $this->get('form.factory')->create(MapHeadingType::class, $entity);

        if ($form->handleRequest($request)->isValid()) {

            $cats = $request->get('map_heading')['interestCategories'];
            $entity->setCommunity($this->container->get('session.community')->getCommunity());

            foreach ($cats as $c) {

                /** @var InterestCategory $cat */
                $cat = $em->getRepository('AppBundle:InterestCategory')->find($c);
                $cat->setMapHeading($entity);
                //$em->persist($cat);

            }
            $em->persist($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'Rubrique ajoutée avec succès');
            return $this->redirect($this->generateUrl('app_mapheading'));

        }

        return $this->render('AppBundle:MapHeading:add.html.twig', array(
            'form' => $form->createView(),
            'entity' => $entity,
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

        /** @var MapHeading $entity */
        $entity = $em->getRepository('AppBundle:MapHeading')->find($id);
        if (!$entity) {
            $this->get('session')->getFlashBag()->add('danger', "Cette rubrique n'existe plus");
            return $this->redirect($this->generateUrl('app_mapheading'));
        }

        $community = $this->getAllowedCommunity($entity->getCommunity(), true);
        if ($community === false) {
            return $this->redirect($this->generateUrl('app_mapheading'));
        }

        /** @var Form $form */
        $form = $this->get('form.factory')->create(MapHeadingType::class, $entity, array(
            'mapHeading' => $entity
        ));

        if ($form->handleRequest($request)->isValid()) {

            $cats = $request->get('map_heading')['interestCategories'];

            foreach ($entity->getInterestCategories() as $item) {
                $entity->addInterestCategory($item);
            }

            $entity->setCommunity($this->container->get('session.community')->getCommunity());
            foreach ($cats as $c) {

                /** @var InterestCategory $cat */
                $cat = $em->getRepository('AppBundle:InterestCategory')->find($c);
                $cat->setMapHeading($entity);
                $entity->addInterestCategory($cat);
                //$em->persist($cat);

            }
            $em->persist($entity);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'Rubrique modifiée avec succès');
            return $this->redirect($this->generateUrl('app_mapheading'));

        }

        return $this->render('AppBundle:MapHeading:update.html.twig', array(
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
        $em = $this->getDoctrine()->getManager();

        /** @var MapHeading $entity */
        $entity = $em->getRepository('AppBundle:MapHeading')->find($id);
        if (!$entity) {
            $this->get('session')->getFlashBag()->add('danger', "Cette rubrique n'existe plus");
            return $this->redirect($this->generateUrl('app_mapheading'));
        }

        $community = $this->getAllowedCommunity($entity->getCommunity(), true);
        if ($community === false) {
            return $this->redirect($this->generateUrl('app_mapheading'));
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
    public function addAjaxAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        if ($request->isXmlHttpRequest()) {
            $cat = new \AppBundle\Entity\InterestCategory();

            $item = $request->get('item');
            $cat->setName($item);
            $em->persist($cat);
            $em->flush();
            return new JsonResponse(array('id' => $cat->getId(), 'title' => $cat->getName()));
        }
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

        return !$community || $community && $community->hasSetting('activate_map') ? $community : false;
    }

}
