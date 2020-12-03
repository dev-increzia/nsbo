<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Community;
use AppBundle\Repository\CategoryRepository;
use Doctrine\Common\Util\Debug;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form\CategoryType;
use AppBundle\Entity\Category;

class CategoryController extends Controller
{

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        return $this->render('AppBundle:Category:index.html.twig', array(
        ));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
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
        $community=$this->container->get('session.community')->getCommunity();
        /** @var CategoryRepository $categoryRepository */
        $categoryRepository = $em->getRepository('AppBundle:Category');

        $entities = $categoryRepository->search($page, $order,$community);
        $countEntities = intval($categoryRepository->count($community));
        $output = array(
            'data' => array(),
            'recordsFiltered' => $countEntities,
            'recordsTotal' => $countEntities
        );

        $outputScreen = $this->container->get('outputScreen');

        /** @var Category $entity */
        foreach ($entities as $entity) {
            $output['data'][] = [
                'id' => $entity->getId(),
                'name' => $entity->getName(),
                'type' => $entity->getType() == 'Activité groupe / association' ?  'Pour les écrans A la Une et Agenda'  : 'Pour l’écran des Bons Plans',
                'createAt' => $entity->getCreateAt() ? $entity->getCreateAt()->format('d/m/Y H:i') : '',
                'updateAt' => $entity->getUpdateAt() ? $entity->getUpdateAt()->format('d/m/Y H:i') : '',
                'actions' => $outputScreen->outPutUpdate($this->generateUrl('app_category_update', array('id' => $entity->getId())))
                    . $outputScreen->outPutDelete($this->generateUrl('app_category_delete', array('id' => $entity->getId()))),
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
        $entity = new Category();
        $em = $this->getDoctrine()->getManager();
        $community = $this->container->get('session.community')->getCommunity();
        if ($community === null) {

            $this->get('session')->getFlashBag()->add('danger', 'Vous devez selectionner une Communauté');
            $referer = $request->headers->get('referer');
            return $this->redirect($referer);

        }


        /** @var Form $form */
        $form = $this->get('form.factory')->create(CategoryType::class, $entity);
        if ($form->handleRequest($request)->isValid()) {

            $check = count($em->getRepository('AppBundle:Category')
                ->findBy(array('name' => $form->getData()->getName(), 'type' => $form->getData()->getType(), 'community' => $community)));
            if ($check > 0) {
                $this->get('session')->getFlashBag()->add('danger', 'le thème <strong>' . $form->getData()->getName() . '</strong> de type <strong>' . ($form->getData()->getType() == 'Activité groupe / association' ? 'Pour les écrans A la Une et Agenda' : 'Pour l’écran des Bons Plans') . '</strong> existe déjà dans la communauté <strong>' . $community->getName() . '</strong>');
                return $this->redirect($this->generateUrl('app_category_add'));
            }
            $entity->setCommunity($community);
            $em->persist($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'Thème ajouté avec succès');
            return $this->redirect($this->generateUrl('app_category'));

        }

        return $this->render('AppBundle:Category:add.html.twig', array(
            'form' => $form->createView(),
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
        $community = $this->container->get('session.community')->getCommunity();
        if ($community === null) {

            $this->get('session')->getFlashBag()->add('danger', 'Vous devez selectionner une Communauté');
            $referer = $request->headers->get('referer');
            return $this->redirect($referer);

        }
        /** @var Category $entity */
        $entity = $em->getRepository('AppBundle:Category')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Thèmes entity.');
        }

        /** @var Form $form */
        $form = $this->get('form.factory')->create(CategoryType::class, $entity);
        if ($form->handleRequest($request)->isValid()) {

            $error = $this->hasValidThemes($entity->getCommunity());
            if (!$error) {

                $resuts = $em->getRepository('AppBundle:Category')
                    ->findBy(array('name' => $form->getData()->getName(), 'type' => $form->getData()->getType(), 'community' => $community));

                $check = count($resuts);
                if ($check > 0) {
                    foreach ($resuts as $resut) {
                        if ($entity->getId() != $resut->getId()) {
                            $this->get('session')->getFlashBag()->add('danger', 'le thème <strong>' . $form->getData()->getName() . '</strong> de type <strong>' . ($form->getData()->getType() == 'Activité groupe / association' ? 'Pour les écrans A la Une et Agenda' : 'Pour l’écran des Bons Plans') . '</strong> existe déjà dans la communauté <strong>' . $community->getName() . '</strong>');
                            return $this->redirect($this->generateUrl('app_category_update', array('id' => $entity->getId())));
                        }
                    }

                }
                $entity->setCommunity($community);
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', "Thèmes modifié avec succès");
                return $this->redirect($this->generateUrl('app_category'));
            }  else {
                $this->get('session')->getFlashBag()->add('danger', $error);
            }
        }

        return $this->render('AppBundle:Category:update.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Category $entity */
        $entity = $em->getRepository('AppBundle:Category')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Thèmes entity.');
        }

        $em->remove($entity);

        $error = $this->hasValidThemes($entity->getCommunity(), $entity->getId());
        if ($error) {
            $this->get('session')->getFlashBag()->add('danger', $error);
            return $this->redirect($this->generateUrl('app_category'));
        }

        $em->flush();
        $this->get('session')->getFlashBag()->add('success', "Thème supprimé avec succès");
        return $this->redirect($this->generateUrl('app_category'));
    }

    /**
     * @param Community $entity
     * @param $id
     */
    protected function hasValidThemes($entity, $id = null) {

        $error = null;

        // Verification si la communaute a un moins 1 categorie de chaque
        $categories = array();
        if ($entity->getCategories()) {
            /** @var Category $category */
            foreach ($entity->getCategories() as $category) {
                if ($id && $category->getId() == $id)
                    continue;

                $categories[$category->getType()][] = $category;
            }
        }

        if (count($categories) < 2) {
            $error = 'Votre communauté doit au moins avoir deux thèmes : un thème "Pour les écrans A la Une et Agenda" et un thème "Pour l\'écran des Bons Plans"';
        }

        return $error;
    }
}
