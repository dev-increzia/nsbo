<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Community;
use AppBundle\Repository\WorkRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form\WorkFilterType;
use AppBundle\Form\WorkType;
use AppBundle\Entity\Work;

class WorkController extends Controller
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
        $form = $this->get('form.factory')->create(WorkFilterType::class);
        return $this->render('AppBundle:Work:index.html.twig', array(
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
        $cityhall = $this->container->get('session.community')->getCommunity();

        /** @var WorkRepository $workRepository */
        $workRepository = $em->getRepository('AppBundle:Work');

        $entities = $workRepository->search($page, $order, $cityhall, $request->get('dateBefore'), $request->get('dateAfter'), $request->get('title'), $request->get('enabled'));
        $countEntities = intval($workRepository->count($cityhall, $request->get('dateBefore'), $request->get('dateAfter'), $request->get('title'), $request->get('enabled')));
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
                'createAt' => $entity->getCreateAt() ? $entity->getCreateAt()->format('d/m/Y H:i') : '',
                'updateAt' => $entity->getUpdateAt() ? $entity->getUpdateAt()->format('d/m/Y H:i') : '',
                'actions' => $outputScreen->outPutUpdate($this->generateUrl('app_work_update', array('id' => $entity->getId())))
                    . $outputScreen->outPutDelete($this->generateUrl('app_work_delete', array('id' => $entity->getId()))),
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
        $entity = new Work();

        $community = $this->getAllowedCommunity(null, true);
        if ($community === false) {
            return $this->redirect($this->generateUrl('app_work'));
        }

        $entity->setLatitude($community->getCity()->getLatitude());
        $entity->setLongitude($community->getCity()->getLongitude());
        $entity->setAddress($community->getCity()->getAddress());

        /** @var Form $form */
        $form = $this->get('form.factory')->create(WorkType::class, $entity, array('community' => $community));
        if ($form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            //$entity->setCommunity($community);
            $entity->setCreateBy($this->getUser());
            $entity->setUpdateBy($this->getUser());
            $em->persist($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'Travaux ajouté avec succès');
            return $this->redirect($this->generateUrl('app_work'));
        }

        return $this->render('AppBundle:Work:add.html.twig', array(
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

        /** @var Work $entity */
        $entity = $em->getRepository('AppBundle:Work')->find($id);
        if (!$entity) {
            $this->get('session')->getFlashBag()->add('danger', "Ces travaux n'existent plus");
            return $this->redirect($this->generateUrl('app_work'));
        }

        $community = $this->getAllowedCommunity($entity->getMapHeading()->getCommunity());
        if ($community === false) {
            return $this->redirect($this->generateUrl('app_work'));
        }

        /** @var Form $form */
        $form = $this->get('form.factory')->create(WorkType::class, $entity, array('community' => $entity->getMapHeading()->getCommunity()));
        if ($form->handleRequest($request)->isValid()) {
            $entity->setUpdateBy($this->getUser());
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', "Travaux modifié avec succès");
            return $this->redirect($this->generateUrl('app_work'));
        }

        return $this->render('AppBundle:Work:update.html.twig', array(
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

        /** @var Work $entity */
        $entity = $em->getRepository('AppBundle:Work')->find($id);
        if (!$entity) {
            $this->get('session')->getFlashBag()->add('danger', "Ces travaux n'existent plus");
            return $this->redirect($this->generateUrl('app_work'));
        }

        $community = $this->getAllowedCommunity($entity->getMapHeading()->getCommunity());
        if ($community === false) {
            return $this->redirect($this->generateUrl('app_work'));
        }

        $em->remove($entity);
        $em->flush();
        $this->get('session')->getFlashBag()->add('success', "Travaux supprimé avec succès");
        return $this->redirect($this->generateUrl('app_work'));
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
