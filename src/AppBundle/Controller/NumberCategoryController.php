<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form\NumberCategoryType;
use AppBundle\Entity\NumberCategory;

class NumberCategoryController extends Controller
{
    public function indexAction()
    {
        return $this->render('AppBundle:NumberCategory:index.html.twig', array(
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
        $entities = $em->getRepository('AppBundle:NumberCategory')->search($page, $order);
        $countEntities = intval($em->getRepository('AppBundle:NumberCategory')->count());
        $output = array(
            'data' => array(),
            'recordsFiltered' => $countEntities,
            'recordsTotal' => $countEntities
        );

        $outputScreen = $this->container->get('outputScreen');
        foreach ($entities as $entity) {
            $output['data'][] = [
                'id' => $entity->getId(),
                'name' => $entity->getName(),
                'createAt' => $entity->getCreateAt() ? $entity->getCreateAt()->format('d/m/Y H:i') : '',
                'updateAt' => $entity->getUpdateAt() ? $entity->getUpdateAt()->format('d/m/Y H:i') : '',
                'actions' => $outputScreen->outPutUpdate($this->generateUrl('app_numberCategory_update', array('id' => $entity->getId())))
                . $outputScreen->outPutDelete($this->generateUrl('app_numberCategory_delete', array('id' => $entity->getId()))),
            ];
        }

        return new JsonResponse($output);
    }

    public function addAction(Request $request)
    {
        $entity = new NumberCategory();
        $form = $this->get('form.factory')->create(NumberCategoryType::class, $entity);
        if ($form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'Catégorie ajoutée avec succès');
            return $this->redirect($this->generateUrl('app_numberCategory'));
        }

        return $this->render('AppBundle:NumberCategory:add.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    public function updateAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:NumberCategory')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find NumberCategory entity.');
        }

        $form = $this->get('form.factory')->create(NumberCategoryType::class, $entity);
        if ($form->handleRequest($request)->isValid()) {
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', "Categorie modifiée avec succès");
            return $this->redirect($this->generateUrl('app_numberCategory'));
        }

        return $this->render('AppBundle:NumberCategory:update.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:NumberCategory')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find NumberCategory entity.');
        }
        $em->remove($entity);
        $em->flush();
        $this->get('session')->getFlashBag()->add('success', "Categorie supprimée avec succès");
        return $this->redirect($this->generateUrl('app_numberCategory'));
    }
}
