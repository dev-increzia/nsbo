<?php

namespace AppBundle\Controller;

use AppBundle\Entity\CategoryPhoneBookHeading;
use AppBundle\Entity\Community;
use AppBundle\Entity\PhoneBookHeading;
use AppBundle\Form\PhoneBookHeadingFilterType;
use AppBundle\Form\PhoneBookHeadingType;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Phonebookheading controller.
 *
 */
class PhoneBookHeadingController extends Controller
{
    public function indexAction(Request $request)
    {
        $community = $this->getAllowedCommunity();
        if ($community === false) {
            return $this->render('AppBundle:phonebookheading:no_access.html.twig');
        }

        $form = $this->get('form.factory')->create(PhoneBookHeadingFilterType::class);
        return $this->render('AppBundle:phonebookheading:index.html.twig', array(
            'form' => $form->createView()
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

        $community = $this->container->get('session.community')->getCommunity();
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
                        $order = array('createAt' => strtoupper($v['dir']));
                    }
                }
            }
        }
        $entities = $em->getRepository('AppBundle:PhoneBookHeading')->search($page, $order, $community, $request->get('dateBefore'), $request->get('dateAfter'), $request->get('enabled'));
        $countEntities = intval($em->getRepository('AppBundle:PhoneBookHeading')->count($community, $request->get('dateBefore'), $request->get('dateAfter'), $request->get('enabled')));
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

                'Objets' => $outputScreen->outPutObjectPhoneBook($entity),
                'enabled' => $entity->getEnabled() ? 'Activé' : 'Désactivé',
                'createAt' => $entity->getCreateAt() ? $entity->getCreateAt()->format('d/m/Y H:i') : '',
                'updateAt' => $entity->getUpdateAt() ? $entity->getUpdateAt()->format('d/m/Y H:i') : '',
                'actions' => $outputScreen->outPutUpdate($this->generateUrl('app_phonebookheading_update', array('id' => $entity->getId())))
                    . $outputScreen->outPutDelete($this->generateUrl('app_phonebookheading_delete', array('id' => $entity->getId()))),
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
        $entity = new PhoneBookHeading();

        $community = $this->getAllowedCommunity(null, true);
        if ($community === false) {
            return $this->redirect($this->generateUrl('app_phonebookheading_index'));
        }

        $em = $this->getDoctrine()->getManager();

        /** @var Form $form */
        $form = $this->get('form.factory')->create(PhoneBookHeadingType::class, $entity,
            array('communityHavePredefinedObjects' => $community->havePredefinedObjects(),
                'headingHavePredefinedObjects' => $entity->havePredefinedObjects()));

        if ($form->handleRequest($request)->isValid()) {

            $entity->setCommunity($community);
            if ($form['havePredefinesdObjects']->getData()) {
                $commerces = new CategoryPhoneBookHeading();
                $commerces->setName('Commerces/Partenaires');
                $commerces->setPhoneBookHeading($entity);
                $em->persist($commerces);
                $associations = new CategoryPhoneBookHeading();
                $associations->setName('Groupes/Associations');
                $associations->setPhoneBookHeading($entity);
                $em->persist($associations);
                $entity->addObject($commerces);
                $entity->addObject($associations);
            }

            $em->persist($entity);
            foreach ($entity->getObjects() as $object) {
                $object->setPhoneBookHeading($entity);
                $em->persist($object);
            }
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'Formulaire ajouté avec succès');

            return $this->redirect($this->generateUrl('app_phonebookheading_index'));

        }

        return $this->render('AppBundle:phonebookheading:add.html.twig', array(
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

        /** @var PhoneBookHeading $entity */
        $entity = $em->getRepository('AppBundle:PhoneBookHeading')->find($id);
        if (!$entity) {
            $this->get('session')->getFlashBag()->add('danger', "Cette rubrique n'existe plus");
            return $this->redirect($this->generateUrl('app_phonebookheading_index'));
        }

        $community = $this->getAllowedCommunity($entity->getCommunity());
        if ($community === false) {
            return $this->redirect($this->generateUrl('app_phonebookheading_index'));
        }

        $originalObjects = new ArrayCollection();

        foreach ($entity->getObjects() as $object) {
            $originalObjects->add($object);
        }

        /** @var Form $form */
        $form = $this->get('form.factory')->create(PhoneBookHeadingType::class, $entity,
            array('communityHavePredefinedObjects' => $entity->getCommunity()->havePredefinedObjects(),
                'headingHavePredefinedObjects' => $entity->havePredefinedObjects()));

        if ($form->handleRequest($request)->isValid()) {

            foreach ($originalObjects as $tag) {

                if (false === $entity->getObjects()->contains($tag)) {
                    if($tag->getName() != 'Commerces' && $tag->getName() != 'Associations')
                    {
                        $entity->removeObject($tag);
                        $tag->setPhoneBookHeading(NULL);
                    }

                }
            }

            foreach ($entity->getObjects() as $item) {
                if ($item->getName() == null) {
                    $em->remove($item);
                }
                $item->setPhoneBookHeading($entity);
            }
            if ($form['havePredefinesdObjects']->getData()) {
                $commerces = $em->getRepository('AppBundle:CategoryPhoneBookHeading')->findOneBy(array('name'=>'Commerces/Partenaires','phoneBookHeading'=>$entity));
                if(!$commerces){
                    $commerces = new CategoryPhoneBookHeading();
                    $commerces->setName('Commerces/Partenaires');
                    $commerces->setPhoneBookHeading($entity);
                    $em->persist($commerces);
                    $entity->addObject($commerces);
                }
                $associations = $em->getRepository('AppBundle:CategoryPhoneBookHeading')->findOneBy(array('name'=>'Groupes/Associations','phoneBookHeading'=>$entity));
                if(!$associations){
                    $associations = new CategoryPhoneBookHeading();
                    $associations->setName('Groupes/Associations');
                    $associations->setPhoneBookHeading($entity);
                    $entity->addObject($associations);
                }


            }else{
                $commerces = $em->getRepository('AppBundle:CategoryPhoneBookHeading')->findOneBy(array('name'=>'Commerces/Partenaires','phoneBookHeading'=>$entity));
                if($commerces){
                    $entity->removeObject($commerces);
                }
                $associations = $em->getRepository('AppBundle:CategoryPhoneBookHeading')->findOneBy(array('name'=>'Groupes/Associations','phoneBookHeading'=>$entity));
                if($associations){
                    $entity->removeObject($associations);
                }
            }

            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'Formulaire modifié avec succès');
            return $this->redirect($this->generateUrl('app_phonebookheading_index'));

        }

        return $this->render('AppBundle:phonebookheading:update.html.twig', array(
            'form' => $form->createView(),
            'entity' => $entity,
        ));
    }

    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var PhoneBookHeading $entity */
        $entity = $em->getRepository('AppBundle:PhoneBookHeading')->find($id);
        if (!$entity) {
            $this->get('session')->getFlashBag()->add('danger', "Cette rubrique n'existe plus");
            return $this->redirect($this->generateUrl('app_phonebookheading_index'));
        }

        $community = $this->getAllowedCommunity($entity->getCommunity());
        if ($community === false) {
            return $this->redirect($this->generateUrl('app_phonebookheading_index'));
        }

        $em->remove($entity);
        $em->flush();
        $this->get('session')->getFlashBag()->add('success', 'Rubrique annuaire supprimé avec succès');
        return $this->redirect($this->generateUrl('app_phonebookheading_index'));
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
