<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form\ReportingCategoryType;
use AppBundle\Entity\ReportingCategory;

class ReportingCategoryController extends Controller
{
    public function indexAction()
    {
        return $this->render('AppBundle:ReportingCategory:index.html.twig', array(
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
        $entities = $em->getRepository('AppBundle:ReportingCategory')->search($page, $order);
        $countEntities = intval($em->getRepository('AppBundle:ReportingCategory')->count());
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
                'actions' => $outputScreen->outPutUpdate($this->generateUrl('app_reportingCategory_update', array('id' => $entity->getId())))
                . $outputScreen->outPutDelete($this->generateUrl('app_reportingCategory_delete', array('id' => $entity->getId()))),
            ];
        }

        return new JsonResponse($output);
    }

    public function addAction(Request $request)
    {
        $entity = new ReportingCategory();
        $form = $this->get('form.factory')->create(ReportingCategoryType::class, $entity);
        if ($form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'Catégorie ajoutée avec succès');
            return $this->redirect($this->generateUrl('app_reportingCategory'));
        }

        return $this->render('AppBundle:ReportingCategory:add.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    public function updateAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:ReportingCategory')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ReportingCategory entity.');
        }

        $form = $this->get('form.factory')->create(ReportingCategoryType::class, $entity);
        if ($form->handleRequest($request)->isValid()) {
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', "Categorie modifiée avec succès");
            return $this->redirect($this->generateUrl('app_reportingCategory'));
        }

        return $this->render('AppBundle:ReportingCategory:update.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:ReportingCategory')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ReportingCategory entity.');
        }
        $em->remove($entity);
        $em->flush();
        $this->get('session')->getFlashBag()->add('success', "Categorie supprimée avec succès");
        return $this->redirect($this->generateUrl('app_reportingCategory'));
    }
}
