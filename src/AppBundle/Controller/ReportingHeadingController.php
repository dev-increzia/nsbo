<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Community;
use AppBundle\Entity\ReportingHeading;
use AppBundle\Form\ReportingHeadingFilterType;
use AppBundle\Form\ReportingHeadingType;
use AppBundle\Repository\ReportingHeadingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ReportingHeadingController extends Controller
{

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $community = $this->getAllowedCommunity();
        if ($community === false) {
            return $this->render('AppBundle:ReportingHeading:no_access.html.twig');
        }
        if (!$this->container->get('security.authorization_checker')->isGranted('ROLE_COMMUNITY_SU_ADMIN')) {
            $this->get('session')->getFlashBag()->add('danger', 'Vous n\'avez pas accès à cette fonctionnalité');
            return $this->redirect($this->generateUrl('app_homepage'));

        }

        /** @var Form $form */
        $form = $this->get('form.factory')->create(ReportingHeadingFilterType::class);
        return $this->render('AppBundle:ReportingHeading:index.html.twig', array(
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

        /** @var ReportingHeadingRepository $reportingHeadingRepository */
        $reportingHeadingRepository = $em->getRepository('AppBundle:ReportingHeading');

        $entities = $reportingHeadingRepository->search($page, $order, $community, $request->get('dateBefore'), $request->get('dateAfter'), $request->get('enabled'));
        $countEntities = intval($reportingHeadingRepository->count($community, $request->get('dateBefore'), $request->get('dateAfter'), $request->get('enabled')));
        $output = array(
            'data' => array(),
            'recordsFiltered' => $countEntities,
            'recordsTotal' => $countEntities
        );

        $outputScreen = $this->container->get('outputScreen');

        /** @var ReportingHeading $entity */
        foreach ($entities as $entity) {
            $output['data'][] = [
                'id' => $entity->getId(),
                'title' => $entity->getTitle(),

                'Objets' => $outputScreen->outPutObject($entity),
                'enabled' => $entity->getEnabled() ? 'Activé' : 'Désactivé',
                'createAt' => $entity->getCreateAt() ? $entity->getCreateAt()->format('d/m/Y H:i') : '',
                'updateAt' => $entity->getUpdateAt() ? $entity->getUpdateAt()->format('d/m/Y H:i') : '',
                'actions' => $outputScreen->outPutUpdate($this->generateUrl('app_reportingheading_update', array('id' => $entity->getId())))
                    . $outputScreen->outPutDelete($this->generateUrl('app_reportingheading_delete', array('id' => $entity->getId()))),
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
        $entity = new ReportingHeading();
        $community = $this->getAllowedCommunity(null, true);
        if ($community === false) {
            return $this->redirect($this->generateUrl('app_reportingheading'));
        }
        if (!$this->container->get('security.authorization_checker')->isGranted('ROLE_COMMUNITY_SU_ADMIN')) {
            $this->get('session')->getFlashBag()->add('danger', 'Vous n\'avez pas accès à cette fonctionnalité');
            return $this->redirect($this->generateUrl('app_homepage'));
        }
        $em = $this->getDoctrine()->getManager();
        /** @var Form $form */
        $form = $this->get('form.factory')->create(ReportingHeadingType::class, $entity);
        if ($form->handleRequest($request)->isValid()) {
            $entity->setCommunity($community);
            $em->persist($entity);
            foreach ($entity->getObjects() as $object) {
                $object->setReportingHeading($entity);
                $em->persist($object);
            }
            try {
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', 'Formulaire ajouté avec succès');
                return $this->redirect($this->generateUrl('app_reportingheading'));
            } catch (\Exception $e) {
                $this->get('session')->getFlashBag()->add('danger', 'Erreur lors de mises à jour de base de données');
            }
        }
        return $this->render('AppBundle:ReportingHeading:add.html.twig', array(
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
        /** @var ReportingHeading $entity */
        $entity = $em->getRepository('AppBundle:ReportingHeading')->find($id);
        if (!$entity) {
            $this->get('session')->getFlashBag()->add('danger', "Ce formulaire n'existe plus");
            return $this->redirect($this->generateUrl('app_reportingheading'));
        }
        $community = $this->getAllowedCommunity($entity->getCommunity());
        if ($community === false) {
            return $this->redirect($this->generateUrl('app_reportingheading'));
        }
        if (!$this->container->get('security.authorization_checker')->isGranted('ROLE_COMMUNITY_SU_ADMIN')) {
            $this->get('session')->getFlashBag()->add('danger', 'Vous n\'avez pas accès à cette fonctionnalité');
            return $this->redirect($this->generateUrl('app_homepage'));
        }
        $originalObjects = new ArrayCollection();
        foreach ($entity->getObjects() as $object) {
            $originalObjects->add($object);
        }
        /** @var Form $form */
        $form = $this->get('form.factory')->create(ReportingHeadingType::class, $entity);
        if ($form->handleRequest($request)->isValid()) {
            foreach ($originalObjects as $tag) {
                if (false === $entity->getObjects()->contains($tag)) {
                    $entity->removeObject($tag);
                    $tag->setReportingHeading(NULL);
                }
            }
            foreach ($entity->getObjects() as $item) {
                if ($item->getObjet() == null) {
                    $em->remove($item);
                }
                $item->setReportingHeading($entity);
            }
            try {
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', 'Formulaire modifié avec succès');
                return $this->redirect($this->generateUrl('app_reportingheading'));
            } catch (\Exception $e) {
                $this->get('session')->getFlashBag()->add('danger', 'Erreur lors de mises à jour de base de données');
            }
        }
        return $this->render('AppBundle:ReportingHeading:update.html.twig', array(
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
        if (!$this->container->get('security.authorization_checker')->isGranted('ROLE_COMMUNITY_SU_ADMIN')) {
            $this->get('session')->getFlashBag()->add('danger', 'Vous n\'avez pas accès à cette fonctionnalité');
            return $this->redirect($this->generateUrl('app_homepage'));
        }
        /** @var ReportingHeading $entity */
        $entity = $em->getRepository('AppBundle:ReportingHeading')->find($id);
        if (!$entity) {
            $this->get('session')->getFlashBag()->add('danger', "Ce formulaire n'existe plus");
            return $this->redirect($this->generateUrl('app_reportingheading'));
        }
        $community = $this->getAllowedCommunity($entity->getCommunity());
        if ($community === false) {
            return $this->redirect($this->generateUrl('app_reportingheading'));
        }
        $em->remove($entity);
        try {
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'Formulaire supprimé avec succès');
        } catch (\Exception $e) {
            $this->get('session')->getFlashBag()->add('danger', 'Erreur lors de mises à jour de base de données');
        }
        return $this->redirect($this->generateUrl('app_reportingheading'));
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

        return !$community || $community && $community->hasSetting('activate_contact') ? $community : false;
    }


}
