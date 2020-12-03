<?php

namespace AppBundle\Controller;

use AppBundle\Repository\InterestCategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form\InterestCategoryType;
use AppBundle\Entity\InterestCategory;

class InterestCategoryController extends Controller
{
    public function indexAction()
    {
        return $this->render('AppBundle:InterestCategory:index.html.twig', array(
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

        /** @var InterestCategoryRepository $interestCategoryRepository */
        $interestCategoryRepository = $em->getRepository('AppBundle:InterestCategory');

        $entities = $interestCategoryRepository->search($page, $order);
        $countEntities = intval($interestCategoryRepository->count());
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
                'actions' => $outputScreen->outPutUpdate($this->generateUrl('app_interestCategory_update', array('id' => $entity->getId())))
                . $outputScreen->outPutDelete($this->generateUrl('app_interestCategory_delete', array('id' => $entity->getId()))),
            ];
        }

        return new JsonResponse($output);
    }

    public function addAction(Request $request)
    {
        $entity = new InterestCategory();
        /** @var Form $form */
        $form = $this->get('form.factory')->create(InterestCategoryType::class, $entity);
        if ($form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'Catégorie ajoutée avec succès');
            return $this->redirect($this->generateUrl('app_interestCategory'));
        }

        return $this->render('AppBundle:InterestCategory:add.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    public function updateAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:InterestCategory')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InterestCategory entity.');
        }

        /** @var Form $form */
        $form = $this->get('form.factory')->create(InterestCategoryType::class, $entity);
        if ($form->handleRequest($request)->isValid()) {
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', "Categorie modifiée avec succès");
            return $this->redirect($this->generateUrl('app_interestCategory'));
        }

        return $this->render('AppBundle:InterestCategory:update.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:InterestCategory')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InterestCategory entity.');
        }
        $em->remove($entity);
        $em->flush();
        $this->get('session')->getFlashBag()->add('success', "Categorie supprimée avec succès");
        return $this->redirect($this->generateUrl('app_interestCategory'));
    }
}
