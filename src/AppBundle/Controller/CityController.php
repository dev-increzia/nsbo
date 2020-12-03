<?php

namespace AppBundle\Controller;

use AppBundle\Repository\CityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form\CityFilterType;
use AppBundle\Form\CityType;
use AppBundle\Entity\City;

class CityController extends Controller
{

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        /** @var Form $form */
        $form = $this->get('form.factory')->create(CityFilterType::class);
        return $this->render('AppBundle:City:index.html.twig', array(
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
                        $order = array('name' => strtoupper($v['dir']));
                    } elseif ($v['column'] == '2') {
                        $order = array('createAt' => strtoupper($v['dir']));
                    }
                }
            }
        }

        /** @var CityRepository $cityRepository */
        $cityRepository = $em->getRepository('AppBundle:City');

        $entities = $cityRepository->search($page, $order, $request->get('dateBefore'), $request->get('dateAfter'), $request->get('name'));
        $countEntities = intval($cityRepository->count($request->get('dateBefore'), $request->get('dateAfter'), $request->get('name')));
        $output = array(
            'data' => array(),
            'recordsFiltered' => $countEntities,
            'recordsTotal' => $countEntities
        );

        $outputScreen = $this->container->get('outputScreen');

        /** @var City $entity */
        foreach ($entities as $entity) {
            $output['data'][] = [
                'id' => $entity->getId(),
                'name' => $entity->getName(),
                'zipcode' => $entity->getZipcode(),
                'createAt' => $entity->getCreateAt() ? $entity->getCreateAt()->format('d/m/Y H:i') : '',
                'updateAt' => $entity->getUpdateAt() ? $entity->getUpdateAt()->format('d/m/Y H:i') : '',
                'actions' => $outputScreen->outPutUpdate($this->generateUrl('app_city_update', array('id' => $entity->getId()))) . $outputScreen->outPutDelete($this->generateUrl('app_city_delete', array('id' => $entity->getId()))),
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
        $entity = new City();
        $entity->setLatitude(48.85837009999999);
        $entity->setLongitude(2.2944813000000295);
        $entity->setAddress('Champ de Mars, 5 Avenue Anatole France, 75007 Paris, France');

        /** @var Form $form */
        $form = $this->get('form.factory')->create(CityType::class, $entity);
        if ($form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'Ville ajoutée avec succès');
            return $this->redirect($this->generateUrl('app_city'));
        }

        return $this->render('AppBundle:City:add.html.twig', array(
                    'form' => $form->createView(),
                    'entity' => $entity,
        ));
    }

    public function updateAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var City $entity */
        $entity = $em->getRepository('AppBundle:City')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find City entity.');
        }

        /** @var Form $form */
        $form = $this->get('form.factory')->create(CityType::class, $entity);
        if ($form->handleRequest($request)->isValid()) {
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', "Ville modifiée avec succès");
            return $this->redirect($this->generateUrl('app_city'));
        }

        return $this->render('AppBundle:City:update.html.twig', array(
            'form' => $form->createView(),
            'entity' => $entity,
        ));
    }

    public function deleteAction($id)
    {

        $this->get('session')->getFlashBag()->add('danger', "Attention vous ne pouvez pas supprimer une ville");
        return $this->redirect($this->generateUrl('app_city'));
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:City')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find City entity.');
        }
        $em->remove($entity);
        $em->flush();
        $this->get('session')->getFlashBag()->add('success', "Ville supprimée avec succès");
        return $this->redirect($this->generateUrl('app_city'));
    }
}
