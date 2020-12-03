<?php

namespace AppBundle\Controller;

use AppBundle\Entity\CategoryPhoneBookHeading;
use AppBundle\Entity\Community;
use AppBundle\Entity\PhoneBookHeading;
use AppBundle\Repository\CategoryPhoneBookHeadingRepository;
use AppBundle\Repository\NumberRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form\NumberFilterType;
use AppBundle\Form\NumberType;
use AppBundle\Entity\Number;

class NumberController extends Controller
{

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $community = $this->getAllowedCommunity();
        if ($community === false) {
            return $this->render('AppBundle:Number:no_access.html.twig');
        }

        /** @var Form $form */
        $form = $this->get('form.factory')->create(NumberFilterType::class);
        return $this->render('AppBundle:Number:index.html.twig', array(
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
                        $order = array('category' => strtoupper($v['dir']));
                    } elseif ($v['column'] == '6') {
                        $order = array('createAt' => strtoupper($v['dir']));
                    }
                }
            }
        }
        $cityhall = $this->container->get('session.community')->getCommunity();

        /** @var NumberRepository $numberRepository */
        $numberRepository = $em->getRepository('AppBundle:Number');

        $entities = $numberRepository->search($page, $order, $cityhall, $request->get('dateBefore'), $request->get('dateAfter'), $request->get('title'));
        $countEntities = intval($numberRepository->count($cityhall, $request->get('dateBefore'), $request->get('dateAfter'), $request->get('title')));
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
                'phone' => $entity->getPhone(),
                'category' => $entity->getCategoryPhoneBookHeading() ? $entity->getCategoryPhoneBookHeading()->getName() : '',
                'createAt' => $entity->getCreateAt() ? $entity->getCreateAt()->format('d/m/Y H:i') : '',
                'updateAt' => $entity->getUpdateAt() ? $entity->getUpdateAt()->format('d/m/Y H:i') : '',
                'actions' => $outputScreen->outPutUpdate($this->generateUrl('app_number_update', array('id' => $entity->getId())))
                    . $outputScreen->outPutDelete($this->generateUrl('app_number_delete', array('id' => $entity->getId()))),
            ];
        }

        return new JsonResponse($output);
    }

    public function addAction(Request $request)
    {
        $entity = new Number();

        $community = $this->getAllowedCommunity(null, true);
        if ($community === false) {
            return $this->redirect($this->generateUrl('app_number'));
        }
        $community = $this->container->get('session.community')->getCommunity();
        $em = $this->getDoctrine()->getManager();
        /** @var Form $form */
        $form = $this->get('form.factory')->create(NumberType::class, $entity,array('em'=>$em,'community'=>$community));

        if ($form->handleRequest($request)->isValid()) {

            $entity->setCreateBy($this->getUser());
            $entity->setUpdateBy($this->getUser());
            $em->persist($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'Numéro utile ajouté avec succès');
            return $this->redirect($this->generateUrl('app_number'));
        }

        return $this->render('AppBundle:Number:add.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function updateAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Number $entity */
        $entity = $em->getRepository('AppBundle:Number')->find($id);
        if (!$entity) {
            $this->get('session')->getFlashBag()->add('danger', "Ce numéro utile n'existe plus");
            return $this->redirect($this->generateUrl('app_number'));
        }

        $community = $this->getAllowedCommunity($entity->getCategoryPhoneBookHeading()->getPhoneBookHeading()->getCommunity());
        if ($community === false) {
            return $this->redirect($this->generateUrl('app_number'));
        }
        $community = $this->container->get('session.community')->getCommunity();

        /** @var Form $form */
        $form = $this->get('form.factory')->create(NumberType::class, $entity,array('em'=>$em,'community'=>$community));
        if ($form->handleRequest($request)->isValid()) {
            $entity->setUpdateBy($this->getUser());
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', "Numéro utile modifié avec succès");
            return $this->redirect($this->generateUrl('app_number'));
        }

        return $this->render('AppBundle:Number:update.html.twig', array(
            'form' => $form->createView(),
            'entity' => $entity,
        ));
    }

    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Number $entity */
        $entity = $em->getRepository('AppBundle:Number')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Number entity.');
        }

        $community = $this->getAllowedCommunity($entity->getCategoryPhoneBookHeading()->getPhoneBookHeading()->getCommunity());
        if ($community === false) {
            return $this->redirect($this->generateUrl('app_number'));
        }

        $em->remove($entity);
        $em->flush();
        $this->get('session')->getFlashBag()->add('success', "Numéro utile supprimé avec succès");
        return $this->redirect($this->generateUrl('app_number'));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getCategoriesByPhoneBookHeadingAction(Request $request)
    {
        $phoneBookHeadingId = $request->query->get('phoneBookHeadingid');
        if (!$phoneBookHeadingId) {
            return new JsonResponse(array());
        }

        // Get Entity manager and repository
        $em = $this->getDoctrine()->getManager();

        /** @var PhoneBookHeading $phoneBookHeading */
        $phoneBookHeading = $em->getRepository('AppBundle:PhoneBookHeading')->find($phoneBookHeadingId);
        if (!$phoneBookHeadingId) {
            return new JsonResponse(array());
        }

        $community = $this->getAllowedCommunity($phoneBookHeading->getCommunity());
        if ($community === false) {
            return new JsonResponse(array());
        }

        /** @var CategoryPhoneBookHeadingRepository $categoriesRepository */
        $categoriesRepository = $em->getRepository("AppBundle:CategoryPhoneBookHeading");

        // Search the neighborhoods that belongs to the city with the given id as GET parameter "cityid"
        /** @var CategoryPhoneBookHeading[] $categories */
        $categories = $categoriesRepository->createQueryBuilder("q")
            ->where("q.phoneBookHeading = :phoneBookHeadingid")
            ->setParameter("phoneBookHeadingid", $phoneBookHeadingId)
            ->andWhere('q.name != :com')->setParameter('com','Commerces')
            ->andWhere('q.name != :asso')->setParameter('asso','Associations')
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

        return !$community || $community && $community->hasSetting('activate_phonebook') ? $community : false;
    }
}
