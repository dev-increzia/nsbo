<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Community;
use AppBundle\Entity\InterestCategory;
use AppBundle\Entity\MapHeading;
use AppBundle\Repository\InterestCategoryRepository;
use AppBundle\Repository\InterestRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Form\FormError;
use AppBundle\Form\InterestFilterType;
use AppBundle\Form\InterestType;
use AppBundle\Entity\Interest;

/**
 * Class InterestController
 * @package AppBundle\Controller
 */
class InterestController extends Controller
{

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $community = $this->getAllowedCommunity();
        if ($community === false) {
            return $this->render('AppBundle:Interest:no_access.html.twig');
        }

        /** @var Form $form */
        $form = $this->get('form.factory')->create(InterestFilterType::class, null, array('community' => $community));
        return $this->render('AppBundle:Interest:index.html.twig', array(
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
        $page = ($start != 0) ? $start / $length : 0;
        $orders = $request->get('order');
        $order = array('createAt' => 'DESC');
        if (is_array($orders)) {
            foreach ($orders as $v) {
                if (isset($v['column']) && isset($v['dir'])) {
                    if ($v['column'] == '0') {
                        $order = array('id' => strtoupper($v['dir']));
                    } elseif ($v['column'] == '1') {
                        $order = array('title' => strtoupper($v['dir']));
                    } elseif ($v['column'] == '2') {
                        $order = array('address' => strtoupper($v['dir']));
                    } elseif ($v['column'] == '3') {
                        $order = array('description' => strtoupper($v['dir']));
                    } elseif ($v['column'] == '4') {
                        $order = array('phone' => strtoupper($v['dir']));
                    } elseif ($v['column'] == '5') {
                        $order = array('createAt' => strtoupper($v['dir']));
                    }
                }
            }
        }
        $community = $this->container->get('session.community')->getCommunity();

        /** @var InterestRepository $interestRepository */
        $interestRepository = $em->getRepository('AppBundle:Interest');

        $entities = $interestRepository->search($page, $order, $community, $request->get('dateBefore'), $request->get('dateAfter'), $request->get('title'), $request->get('enabled'));
        $countEntities = intval($interestRepository->count($community, $request->get('dateBefore'), $request->get('dateAfter'), $request->get('title'), $request->get('enabled')));
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
                'address' => $entity->getAddress(),
                'description' => $entity->getDescription(),
                'enabled' => $entity->getEnabled() ? 'Actif' : 'Inactif',
                'category' => $entity->getCategory() ? $entity->getCategory()->getName() : '',
                'createAt' => $entity->getCreateAt() ? $entity->getCreateAt()->format('d/m/Y H:i') : '',
                'updateAt' => $entity->getUpdateAt() ? $entity->getUpdateAt()->format('d/m/Y H:i') : '',
                'actions' => $outputScreen->outPutUpdate($this->generateUrl('app_interest_update', array('id' => $entity->getId())))
                    . $outputScreen->outPutDelete($this->generateUrl('app_interest_delete', array('id' => $entity->getId()))),
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
        $entity = new Interest();

        $community = $this->getAllowedCommunity(null, true);
        if ($community === false) {
            return $this->redirect($this->generateUrl('app_interest'));
        }

        $em = $this->getDoctrine()->getManager();
        //default
        $entity->setLatitude($community->getCity()->getLatitude());
        $entity->setLongitude($community->getCity()->getLongitude());
        $entity->setAddress($community->getCity()->getAddress());

        /** @var Form $form */
        $form = $this->get('form.factory')->create(InterestType::class, $entity, array('em' => $em, 'community' => $community));

        if ($form->handleRequest($request)->isValid()) {
            if (empty($form->get('longitude')->getData()) || empty($form->get('latitude')->getData())) {
                $this->get('session')->getFlashBag()->add('danger', 'Une adresse est requise pour le centre d\'intérêt');
            } else {

                //$entity->setCommunity($community);
                $entity->setCreateBy($this->getUser());
                $entity->setUpdateBy($this->getUser());
                $em->persist($entity);
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', 'Centre d\'intérêt ajouté avec succès');
                return $this->redirect($this->generateUrl('app_interest'));
            }
        }

        return $this->render('AppBundle:Interest:add.html.twig', array(
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

        /** @var Interest $entity */
        $entity = $em->getRepository('AppBundle:Interest')->find($id);
        if (!$entity) {
            $this->get('session')->getFlashBag()->add('danger', "Ce centre d'intérêt n'existe plus");
            return $this->redirect($this->generateUrl('app_interest'));
        }

        $community = $this->getAllowedCommunity($entity->getCategory()->getMapHeading()->getCommunity());
        if ($community === false) {
            return $this->redirect($this->generateUrl('app_interest'));
        }

        /** @var Form $form */
        $form = $this->get('form.factory')->create(InterestType::class, $entity, array('em' => $em, 'community' => $entity->getCategory()->getMapHeading()->getCommunity()));
        if ($form->handleRequest($request)->isValid()) {
            if (empty($form->get('longitude')->getData()) || empty($form->get('latitude')->getData())) {
                $this->get('session')->getFlashBag()->add('danger', 'Une adresse est requise pour le centre d\'intérêt');
            } else {
                $entity->setUpdateBy($this->getUser());
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', 'Centre d\'intérêt modifié avec succès');
                return $this->redirect($this->generateUrl('app_interest'));
            }
        }

        return $this->render('AppBundle:Interest:update.html.twig', array(
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
        /** @var Interest $entity */
        $entity = $em->getRepository('AppBundle:Interest')->find($id);
        if (!$entity) {
            $this->get('session')->getFlashBag()->add('danger', "Ce centre d'intérêt n'existe pas");
            return $this->redirect($this->generateUrl('app_interest'));
        }

        $community = $this->getAllowedCommunity($entity->getCategory()->getMapHeading()->getCommunity());
        if ($community === false) {
            return $this->redirect($this->generateUrl('app_interest'));
        }

        $em->remove($entity);
        $em->flush();
        $this->get('session')->getFlashBag()->add('success', 'Centre d\'intérêt supprimé avec succès');
        return $this->redirect($this->generateUrl('app_interest'));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getCategoriesByMapHeadingAction(Request $request)
    {

        $mapHeadingId = $request->query->get('mapHeadingid');
        if (!$mapHeadingId) {
            return new JsonResponse(array());
        }

        // Get Entity manager and repository
        $em = $this->getDoctrine()->getManager();

        /** @var MapHeading $mapHeading */
        $mapHeading = $em->getRepository('AppBundle:MapHeading')->find($mapHeadingId);
        if (!$mapHeading) {
            return new JsonResponse(array());
        }

        $community = $this->getAllowedCommunity($mapHeading->getCommunity());
        if ($community === false) {
            return new JsonResponse(array());
        }

        /** @var InterestCategoryRepository $categoriesRepository */
        $categoriesRepository = $em->getRepository("AppBundle:InterestCategory");

        // Search the neighborhoods that belongs to the city with the given id as GET parameter "cityid"
        /** @var InterestCategory[] $categories */
        $categories = $categoriesRepository->createQueryBuilder("q")
            ->where("q.mapHeading = :mapHeadingid")
            ->setParameter("mapHeadingid", $mapHeadingId)
            ->getQuery()
            ->getResult();

        // Serialize into an array the data that we need, in this case only name and id
        // Note: you can use a serializer as well, for explanation purposes, we'll do it manually
        $responseArray = array();
        foreach($categories as $category){
            $responseArray[] = array(
                "id" => $category->getId(),
                "name" => $category->getName()
            );
        }

        // Return array with structure of the neighborhoods of the providen city id
        return new JsonResponse($responseArray);
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
